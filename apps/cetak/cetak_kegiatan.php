<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$level = strtolower($_SESSION['level'] ?? '');
if (!in_array($level, ['admin', 'siswa'], true)) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$id_siswa = filter_input(INPUT_GET, 'id_siswa', FILTER_VALIDATE_INT);
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
if ($level === 'siswa') {
    $id_siswa = filter_var($_SESSION['id_siswa'] ?? null, FILTER_VALIDATE_INT);
}

$valid_date = static function ($value) {
    if (!is_string($value)) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
};

if (
    !$id_siswa ||
    !$valid_date($tanggal_awal) ||
    !$valid_date($tanggal_akhir) ||
    $tanggal_awal > $tanggal_akhir
) {
    http_response_code(400);
    exit('Siswa dan rentang tanggal yang valid harus dipilih.');
}

require('../../source/plugin/fpdf/fpdf.php');
include '../../config/database.php';
include '../../config/function.php';

$site_query = mysqli_query($kon, 'SELECT * FROM tbl_site LIMIT 1');
if (!$site_query || !($site = mysqli_fetch_assoc($site_query))) {
    http_response_code(500);
    exit('Data instansi tidak dapat dimuat.');
}

$student_statement = mysqli_prepare(
    $kon,
    'SELECT nama, nis, perusahaan, jurusan FROM tbl_siswa WHERE id_siswa = ? LIMIT 1'
);
if (!$student_statement) {
    http_response_code(500);
    exit('Data siswa tidak dapat dimuat.');
}
mysqli_stmt_bind_param($student_statement, 'i', $id_siswa);
if (!mysqli_stmt_execute($student_statement)) {
    http_response_code(500);
    exit('Data siswa tidak dapat dimuat.');
}
$student_result = mysqli_stmt_get_result($student_statement);
$student = mysqli_fetch_assoc($student_result);
mysqli_stmt_close($student_statement);
if (!$student) {
    http_response_code(404);
    exit('Data siswa tidak ditemukan.');
}

$activity_statement = mysqli_prepare(
    $kon,
    'SELECT tanggal, DAYNAME(tanggal) AS hari, waktu_awal, waktu_akhir, kegiatan
     FROM tbl_kegiatan
     WHERE id_siswa = ? AND tanggal BETWEEN ? AND ?
     ORDER BY tanggal ASC, waktu_awal ASC, id_kegiatan ASC'
);
if (!$activity_statement) {
    http_response_code(500);
    exit('Data kegiatan tidak dapat dimuat.');
}
mysqli_stmt_bind_param($activity_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($activity_statement)) {
    http_response_code(500);
    exit('Data kegiatan tidak dapat dimuat.');
}
$activity_result = mysqli_stmt_get_result($activity_statement);
if (!$activity_result) {
    http_response_code(500);
    exit('Data kegiatan tidak dapat dimuat.');
}

$format_tanggal = static function ($tanggal) {
    return date('d', strtotime($tanggal)) . ' ' .
        MendapatkanBulan((int) date('m', strtotime($tanggal))) . ' ' .
        date('Y', strtotime($tanggal));
};
$pdf_text = static function ($text) {
    $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT', (string) $text);
    if ($converted === false) {
        http_response_code(500);
        exit('Teks laporan tidak dapat dikodekan.');
    }
    return $converted;
};

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$logo_path = '../../apps/pengaturan/logo/' . basename((string) ($site['logo'] ?? ''));
if (!empty($site['logo']) && is_file($logo_path)) {
    $pdf->Image($logo_path, 12, 10, 20, 20);
}

$pdf->SetXY(35, 10);
$pdf->SetFont('Arial', 'B', 16);
$pdf->MultiCell(171, 7, strtoupper($pdf_text($site['nama_instansi'] ?? '')), 0, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetX(35);
$pdf->MultiCell(171, 4, $pdf_text(($site['alamat'] ?? '') . ', Telp: ' . ($site['no_telp'] ?? '')), 0, 'C');
$pdf->SetX(35);
$pdf->Cell(171, 5, $pdf_text($site['website'] ?? ''), 0, 1, 'C');
$header_bottom = $pdf->GetY() + 2;
$pdf->SetLineWidth(0.8);
$pdf->Line(10, $header_bottom, 206, $header_bottom);
$pdf->SetLineWidth(0.2);
$pdf->Line(10, $header_bottom + 1, 206, $header_bottom + 1);
$pdf->SetY($header_bottom + 7);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'JURNAL KEGIATAN HARIAN', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'PERIODE ' . $format_tanggal($tanggal_awal) . ' - ' . $format_tanggal($tanggal_akhir), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
foreach ([
    'Nama' => $student['nama'],
    'NIS' => $student['nis'],
    'Perusahaan' => $student['perusahaan'],
    'Jurusan' => $student['jurusan']
] as $label => $value) {
    $pdf->Cell(30, 6, $label, 0, 0);
    $pdf->Cell(0, 6, ': ' . $pdf_text($value), 0, 1);
}
$pdf->Ln(5);

$column_widths = [10, 22, 35, 30, 98];
$draw_table_header = static function () use ($pdf, $column_widths) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 249, 0);
    foreach (['No', 'Hari', 'Tanggal', 'Jam', 'Kegiatan'] as $index => $label) {
        $pdf->Cell($column_widths[$index], 8, $label, 1, 0, 'C', true);
    }
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 9);
};
$wrap_text = static function ($text, $width) use ($pdf) {
    $lines = [];
    foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", (string) $text)) as $paragraph) {
        $line = '';
        foreach (preg_split('/\s+/', trim($paragraph)) as $word) {
            if ($word === '') {
                continue;
            }
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && $pdf->GetStringWidth($candidate) > $width) {
                $lines[] = $line;
                $line = '';
            }
            if ($pdf->GetStringWidth($word) > $width) {
                foreach (str_split($word) as $character) {
                    if ($line !== '' && $pdf->GetStringWidth($line . $character) > $width) {
                        $lines[] = $line;
                        $line = '';
                    }
                    $line .= $character;
                }
            } else {
                $line = $line === '' ? $word : $line . ' ' . $word;
            }
        }
        $lines[] = $line;
    }
    return implode("\n", $lines ?: ['']);
};
$draw_table_header();

$no = 0;
while ($activity = mysqli_fetch_assoc($activity_result)) {
    $no++;
    $waktu_awal = $activity['waktu_awal'] ? date('H:i', strtotime($activity['waktu_awal'])) : '-';
    $waktu_akhir = $activity['waktu_akhir'] ? date('H:i', strtotime($activity['waktu_akhir'])) : '-';
    $values = [
        (string) $no,
        MendapatkanHari($activity['hari']),
        $format_tanggal($activity['tanggal']),
        $waktu_awal . ' - ' . $waktu_akhir,
        $pdf_text($activity['kegiatan'])
    ];
    $wrapped_values = [];
    $line_count = 1;
    foreach ($values as $index => $value) {
        $wrapped_values[$index] = $wrap_text($value, $column_widths[$index] - 3);
        $line_count = max($line_count, substr_count($wrapped_values[$index], "\n") + 1);
    }
    $row_height = max(6, $line_count * 5);
    if ($pdf->GetY() + $row_height > 250) {
        $pdf->AddPage();
        $draw_table_header();
    }

    $row_y = $pdf->GetY();
    $x = 10;
    foreach ($wrapped_values as $index => $value) {
        $pdf->Rect($x, $row_y, $column_widths[$index], $row_height);
        $pdf->SetXY($x + 1.5, $row_y + max(0, ($row_height - ($line_count * 5)) / 2));
        $pdf->MultiCell($column_widths[$index] - 3, 5, $value, 0, $index === 4 ? 'L' : 'C');
        $x += $column_widths[$index];
    }
    $pdf->SetXY(10, $row_y + $row_height);
}
mysqli_stmt_close($activity_statement);

if ($no === 0) {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(array_sum($column_widths), 10, 'Tidak ada kegiatan pada rentang tanggal ini.', 1, 1, 'C');
}

if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}
$pdf->Ln(12);
$signature_width = 88;
$signature_gap = 19;
$left_x = 10;
$right_x = $left_x + $signature_width + $signature_gap;
$pdf->SetFont('Arial', '', 10);
$pdf->SetX($left_x);
$pdf->Cell($signature_width, 6, 'Pembimbing PKL', 0, 0, 'C');
$pdf->SetX($right_x);
$pdf->Cell($signature_width, 6, 'Peserta PKL', 0, 1, 'C');
$pdf->Ln(20);
$pdf->SetX($left_x);
$pdf->Cell($signature_width, 6, $pdf_text($site['pembimbing'] ?? ''), 0, 0, 'C');
$pdf->SetX($right_x);
$pdf->Cell($signature_width, 6, $pdf_text($student['nama']), 0, 1, 'C');

$safe_name = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $student['nama']);
$filename = 'Jurnal-Kegiatan-' . trim($safe_name, '-') . '-' . date('YmdHis') . '.pdf';
mysqli_stmt_close($student_statement);
$pdf->Output('I', $filename);
