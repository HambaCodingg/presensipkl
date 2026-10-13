<?php
session_start();
$level = strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '');
if (!in_array($level, ['admin', 'siswa'], true)) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$id_siswa = filter_input(INPUT_GET, 'id_siswa', FILTER_VALIDATE_INT);
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
if ($level === 'siswa') {
    $id_siswa = filter_var(isset($_SESSION['id_siswa']) ? $_SESSION['id_siswa'] : null, FILTER_VALIDATE_INT);
}
$valid_date = static function ($value) {
    if (!is_string($value)) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
};
if (!$id_siswa || !$valid_date($tanggal_awal) || !$valid_date($tanggal_akhir) || $tanggal_awal > $tanggal_akhir) {
    http_response_code(400);
    exit('Siswa dan rentang tanggal yang valid harus dipilih.');
}

require('../../source/plugin/fpdf/fpdf.php');
include '../../config/database.php';
include '../../config/function.php';

// Data site dan siswa hanya dibaca setelah sesi dan ID tujuan diverifikasi.
$query = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$row = $query ? mysqli_fetch_assoc($query) : null;
if (!$row) {
    http_response_code(500);
    exit('Data instansi tidak dapat dimuat.');
}
$pembimbing = $row['pembimbing'];

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();

// ===== HEADER =====

// Logo di kiri atas
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 15, 10, 20, 20);

// Nama instansi
$pdf->SetXY(40, 10);
$pdf->SetFont('Arial', 'B', 21);
$pdf->Cell(0, 8, strtoupper($row['nama_instansi']), 0, 1, 'L');

// Alamat & telepon (pakai MultiCell biar tidak kepotong)
$pdf->SetXY(40, 18);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $row['alamat'] . ', Telp ' . $row['no_telp'], 0, 'L');

// Website (tetap di bawah alamat)
$pdf->SetX(40);
$pdf->Cell(0, 6, $row['website'], 0, 1, 'L');

// Garis pemisah
$pdf->Ln(2);
$pdf->SetLineWidth(1);
$pdf->Line(10, $pdf->GetY(), 206, $pdf->GetY());
$pdf->SetLineWidth(0);
$pdf->Line(10, $pdf->GetY() + 1, 206, $pdf->GetY() + 1);
$pdf->Ln(6);

// ===== DATA SISWA =====
$student_statement = mysqli_prepare($kon, 'SELECT * FROM tbl_siswa WHERE id_siswa = ? LIMIT 1');
if (!$student_statement) {
    http_response_code(500);
    exit('Data siswa tidak dapat dimuat.');
}
mysqli_stmt_bind_param($student_statement, 'i', $id_siswa);
mysqli_stmt_execute($student_statement);
$student_result = mysqli_stmt_get_result($student_statement);
$data = mysqli_fetch_assoc($student_result);
mysqli_stmt_close($student_statement);
if (!$data) {
    http_response_code(404);
    exit('Data siswa tidak ditemukan.');
}

$awal_pkl = $data['mulai_pkl'];
$akhir_pkl = $data['akhir_pkl'];
$mulai_bulan = date("m", strtotime($awal_pkl));
$akhir_bulan = date("m", strtotime($akhir_pkl));
$mulai_hari = date("d", strtotime($awal_pkl));
$akhir_hari = date("d", strtotime($akhir_pkl));
$akhir_tahun = date("Y", strtotime($akhir_pkl));

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'DAFTAR HADIR SISWA PKL', 0, 1, 'C');
$pdf->Cell(0, 7, 'PERIODE ' . $mulai_hari . ' ' . MendapatkanAwalBulan($mulai_bulan) . ' - ' . $akhir_hari . ' ' . MendapatkanAkhirBulan($akhir_bulan) . ' ' . $akhir_tahun, 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Nama ', 0, 0);
$pdf->Cell(80, 6, ': ' . $data['nama'], 0, 1);
$pdf->Cell(30, 6, 'NIS ', 0, 0);
$pdf->Cell(80, 6, ': ' . $data['nis'], 0, 1);
$pdf->Cell(30, 6, 'Perusahaan ', 0, 0);
$pdf->Cell(80, 6, ': ' . $data['perusahaan'], 0, 1);
$pdf->Cell(30, 6, 'Jurusan ', 0, 0);
$pdf->Cell(80, 6, ': ' . $data['jurusan'], 0, 1);

$pdf->Ln(6);

// ===== TABEL ABSENSI =====
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 7, 'No', 1, 0, 'C');
$pdf->Cell(40, 7, 'Hari', 1, 0, 'C');
$pdf->Cell(50, 7, 'Tanggal', 1, 0, 'C');
$pdf->Cell(47, 7, 'Waktu', 1, 0, 'C');
$pdf->Cell(48, 7, 'Keterangan', 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);

$no = 0;
$attendance_statement = mysqli_prepare(
    $kon,
    "SELECT id_absensi, id_siswa, status, tanggal, waktu,
            DATE_FORMAT(tanggal, '%W') AS hari
     FROM tbl_absensi
     WHERE id_siswa = ? AND tanggal BETWEEN ? AND ?
     ORDER BY tanggal ASC"
);
if (!$attendance_statement) {
    http_response_code(500);
    exit('Data absensi tidak dapat dimuat.');
}
mysqli_stmt_bind_param($attendance_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($attendance_statement)) {
    mysqli_stmt_close($attendance_statement);
    http_response_code(500);
    exit('Data absensi tidak dapat dimuat.');
}
$hasil = mysqli_stmt_get_result($attendance_statement);

while ($data = mysqli_fetch_assoc($hasil)) {
    $waktu = date("H:i", strtotime($data['waktu']));
    $status = $data['status'];
    $hari = $data['hari'];
    $tgl = date("d", strtotime($data['tanggal']));
    $bulan = date("m", strtotime($data['tanggal']));
    $tahun = date("Y", strtotime($data['tanggal']));

    $no++;
    $pdf->Cell(10, 6, $no, 1, 0, 'C');
    $pdf->Cell(40, 6, MendapatkanHari($hari), 1, 0, 'C');
    $pdf->Cell(50, 6, $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun, 1, 0, 'C');
    $pdf->Cell(47, 6, $waktu, 1, 0, 'C');
    $pdf->Cell(48, 6, StatusAbsensi($status), 1, 1, 'C');
}
mysqli_stmt_close($attendance_statement);

// ===== TANDA TANGAN =====
$pdf->Ln(15);
$pdf->Cell(0, 6, 'Pembimbing PKL', 0, 1, 'C');
$pdf->Ln(18);
$pdf->Cell(0, 6, $pembimbing, 0, 1, 'C');

// ===== OUTPUT PDF =====
$safe_name = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $data['nama']);
$namafile = 'Absensi-' . trim($safe_name, '-') . '-' . date('YmdHis') . '.pdf';
$pdf->Output('I', $namafile);
