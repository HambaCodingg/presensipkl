<?php
// Ambil parameter
$id_siswa      = $_GET["id_siswa"];
$tanggal_awal  = $_GET["tanggal_awal"];
$tanggal_akhir = $_GET["tanggal_akhir"];

// Include library dan koneksi
require('../../source/plugin/fpdf/fpdf.php');
include '../../config/database.php';
include '../../config/function.php';

// Ambil data site
$query      = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$row        = mysqli_fetch_array($query);
$pembimbing = $row['pembimbing'];

// Ambil nama siswa untuk nama file
$kueri     = "SELECT nama FROM tbl_siswa WHERE id_siswa=$id_siswa";
$hasilsql  = mysqli_query($kon, $kueri);
$hasilnama = mysqli_fetch_array($hasilsql);
$nama      = $hasilnama['nama'];
$namafile  = 'Kegiatan Harian - ' . $nama . ' - ' . date('YmdHis') . '.pdf';

// Header PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $namafile . '"');

// Buat PDF
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();

// Logo (posisi kiri atas, ukuran proporsional)
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 12, 10, 20, 20);

// Geser pointer Y lebih jauh supaya teks tidak menimpa logo
$pdf->SetY(12);

// Nama Instansi
$pdf->SetFont('Arial', 'B', 21);
$pdf->Cell(0, 8, strtoupper($row['nama_instansi']), 0, 1, 'C');

// Tambahkan sedikit jarak
$pdf->Ln(2);

// Alamat & Telepon (pakai MultiCell supaya otomatis pindah baris)
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $row['alamat'] . ', Telp: ' . $row['no_telp'], 0, 'C');

// Website
$pdf->Cell(0, 6, $row['website'], 0, 1, 'C');

// Garis pemisah
$pdf->SetLineWidth(1);
$pdf->Line(10, 38, 206, 38);
$pdf->SetLineWidth(0);
$pdf->Line(10, 39, 206, 39);

// Ambil data siswa
$sql   = "SELECT * FROM tbl_siswa WHERE id_siswa=$id_siswa";
$hasil = mysqli_query($kon, $sql);
$data  = mysqli_fetch_array($hasil);

// Judul
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'JURNAL KEGIATAN HARIAN', 0, 1, 'C');
$pdf->Ln(5);

// Data Siswa
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Nama', 0, 0);
$pdf->Cell(0, 6, ': ' . $data['nama'], 0, 1);
$pdf->Cell(30, 6, 'NIS', 0, 0);
$pdf->Cell(0, 6, ': ' . $data['nis'], 0, 1);
$pdf->Cell(30, 6, 'Perusahaan', 0, 0);
$pdf->Cell(0, 6, ': ' . $data['perusahaan'], 0, 1);
$pdf->Cell(30, 6, 'Jurusan', 0, 0);
$pdf->Cell(0, 6, ': ' . $data['jurusan'], 0, 1);

$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 6, 'No', 1, 0, 'C');
$pdf->Cell(20, 6, 'Hari', 1, 0, 'C');
$pdf->Cell(30, 6, 'Tanggal', 1, 0, 'C');
$pdf->Cell(30, 6, 'Jam', 1, 0, 'C');
$pdf->Cell(105, 6, 'Kegiatan', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);

// Ambil kegiatan
$sql = "
    SELECT 
        DATE_FORMAT(tbl_kegiatan.tanggal, '%Y-%m-%d') AS tgl_full,
        DAYNAME(tbl_kegiatan.tanggal) AS hari, 
        tbl_kegiatan.waktu_awal, 
        tbl_kegiatan.waktu_akhir,
        tbl_kegiatan.kegiatan
    FROM tbl_kegiatan 
    WHERE tbl_kegiatan.id_siswa = '$id_siswa'
    AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    ORDER BY tbl_kegiatan.tanggal ASC
";
$hasil = mysqli_query($kon, $sql);

$no = 0;
while ($data = mysqli_fetch_assoc($hasil)) {
    $no++;
    $tgl         = date("d", strtotime($data['tgl_full']));
    $bulan       = date("m", strtotime($data['tgl_full']));
    $tahun       = date("Y", strtotime($data['tgl_full']));
    $waktu_awal  = date("H:i", strtotime($data['waktu_awal']));
    $waktu_akhir = date("H:i", strtotime($data['waktu_akhir']));

    $pdf->Cell(10, 6, $no, 1, 0, 'C');
    $pdf->Cell(20, 6, MendapatkanHari($data['hari']), 1, 0, 'C');
    $pdf->Cell(30, 6, $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun, 1, 0, 'C');
    $pdf->Cell(30, 6, $waktu_awal . ' - ' . $waktu_akhir, 1, 0, 'C');
    $pdf->Cell(105, 6, $data["kegiatan"], 1, 1);
}

// Tanda tangan
$pdf->Ln(15);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Pembimbing PKL', 0, 1, 'R');
$pdf->Ln(15);
$pdf->Cell(0, 6, $pembimbing, 0, 1, 'R');

// Output PDF
$pdf->Output('files/' . $namafile, 'F');
readfile('files/' . $namafile);
