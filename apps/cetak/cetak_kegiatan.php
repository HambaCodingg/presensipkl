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

$site_query = mysqli_query($kon, 'SELECT nama_instansi, website, logo FROM tbl_site LIMIT 1');
if (!$site_query || !($site = mysqli_fetch_assoc($site_query))) {
    http_response_code(500);
    exit('Data instansi tidak dapat dimuat.');
}

$student_statement = mysqli_prepare(
    $kon,
    'SELECT nama, nis, perusahaan, pembimbing FROM tbl_siswa WHERE id_siswa = ? LIMIT 1'
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
$student = null;
mysqli_stmt_bind_result($student_statement, $student_name, $student_nis, $student_company, $student_advisor);
if (mysqli_stmt_fetch($student_statement)) {
    $student = [
        'nama' => $student_name,
        'nis' => $student_nis,
        'perusahaan' => $student_company,
        'pembimbing' => $student_advisor
    ];
}
mysqli_stmt_close($student_statement);
if (!$student) {
    http_response_code(404);
    exit('Data siswa tidak ditemukan.');
}

$signature_statement = mysqli_prepare(
    $kon,
    'SELECT nama_user, ttd_user, nama_pembimbing, ttd_pembimbing, nama_siswa, ttd_siswa
     FROM tbl_laporan_kegiatan_ttd
     WHERE id_siswa = ? AND tanggal_awal = ? AND tanggal_akhir = ?
     LIMIT 1'
);
if (!$signature_statement) {
    http_response_code(500);
    exit('Data tanda tangan tidak dapat dimuat.');
}
mysqli_stmt_bind_param($signature_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($signature_statement)) {
    http_response_code(500);
    exit('Data tanda tangan tidak dapat dimuat.');
}
$signatures = [];
mysqli_stmt_bind_result(
    $signature_statement,
    $signature_user_name,
    $signature_user,
    $signature_advisor_name,
    $signature_advisor,
    $signature_student_name,
    $signature_student
);
if (mysqli_stmt_fetch($signature_statement)) {
    $signatures = [
        'nama_user' => $signature_user_name,
        'ttd_user' => $signature_user,
        'nama_pembimbing' => $signature_advisor_name,
        'ttd_pembimbing' => $signature_advisor,
        'nama_siswa' => $signature_student_name,
        'ttd_siswa' => $signature_student
    ];
}
mysqli_stmt_close($signature_statement);

$activity_statement = mysqli_prepare(
    $kon,
    'SELECT tanggal, kegiatan, tempat
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
$activity_date = $activity_name = $activity_place = null;
mysqli_stmt_bind_result($activity_statement, $activity_date, $activity_name, $activity_place);
$activities_by_date = [];
while (mysqli_stmt_fetch($activity_statement)) {
    $activity = [
        'tanggal' => $activity_date,
        'kegiatan' => $activity_name,
        'tempat' => $activity_place
    ];
    $activities_by_date[$activity['tanggal']][] = $activity;
}
mysqli_stmt_close($activity_statement);

$pdf_text = static function ($text) {
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
    if ($converted === false) {
        http_response_code(500);
        exit('Teks laporan tidak dapat dikodekan.');
    }
    return $converted;
};
$month_name = static function ($month) {
    return MendapatkanBulan((int) $month);
};
$format_tanggal = static function ($tanggal) use ($month_name) {
    return date('j', strtotime($tanggal)) . ' ' .
        $month_name(date('m', strtotime($tanggal))) . ' ' .
        date('Y', strtotime($tanggal));
};
$period_start = new DateTimeImmutable($tanggal_awal);
$period_end = new DateTimeImmutable($tanggal_akhir);
$period_label = $month_name($period_start->format('m')) . ' ' . $period_start->format('Y');
if ($period_start->format('Y-m') !== $period_end->format('Y-m')) {
    $period_label .= ' - ' . $month_name($period_end->format('m')) . ' ' . $period_end->format('Y');
}

class LaporanKegiatanPDF extends FPDF
{
    public $website = '';

    function Footer()
    {
        $this->SetY(-12);
        $this->SetFillColor(37, 91, 163);
        $this->Rect(0, $this->GetY(), 210, 12, 'F');
        $this->SetXY(7, -9);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 5, $this->website, 0, 0, 'L');
        $this->SetTextColor(0, 0, 0);
    }
}

$pdf = new LaporanKegiatanPDF('P', 'mm', 'A4');
$pdf->SetMargins(20, 10, 20);
$pdf->SetAutoPageBreak(true, 16);
$pdf->website = $pdf_text($site['website'] ?? '');
$pdf->AddPage();

$logo_path = '../../apps/pengaturan/logo/' . basename((string) ($site['logo'] ?? ''));
if (!empty($site['logo']) && is_file($logo_path)) {
    $pdf->Image($logo_path, 165, 8, 32, 20);
}

$pdf->SetY(27);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'LAPORAN HASIL PRAKTIK KERJA LAPANGAN', 0, 1, 'C');
$pdf->Cell(0, 7, 'SISWA PKL ' . strtoupper($pdf_text($site['nama_instansi'] ?? '')), 0, 1, 'C');
$pdf->SetFillColor(255, 255, 0);
$pdf->Cell(0, 7, strtoupper($pdf_text($student['perusahaan'] ?? '')), 0, 1, 'C', true);
$pdf->Ln(11);

$pdf->SetFont('Arial', '', 11);
$student_rows = [
    ['Nama / NIS', $pdf_text($student['nama'] . ' / ' . $student['nis'])],
    ['Tempat Praktik Kerja Lapangan', $pdf_text($student['perusahaan'])],
    ['Bulan Pelaksanaan', $pdf_text($period_label)]
];
foreach ($student_rows as [$label, $value]) {
    $pdf->SetX(25);
    $pdf->Cell(63, 8, $label, 0, 0, 'L');
    $pdf->Cell(5, 8, ':', 0, 0, 'C');
    $pdf->SetFillColor(255, 255, 0);
    $value_width = min(
        $pdf->GetPageWidth() - 25 - 63 - 5 - 25,
        max(1, $pdf->GetStringWidth($value) + 2)
    );
    $pdf->Cell($value_width, 8, $value, 0, 1, 'L', true);
}
$pdf->Ln(9);

$column_widths = [10, 42, 73, 40];
$table_x = 25;
$draw_table_header = static function () use ($pdf, $column_widths, $table_x) {
    $pdf->SetX($table_x);
    $pdf->SetFont('Arial', 'B', 10);
    foreach (['No', 'Tanggal', 'Kegiatan yang dilakukan', 'Tempat'] as $index => $label) {
        $pdf->Cell($column_widths[$index], 9, $label, 1, 0, 'L');
    }
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
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

$dates = new DatePeriod($period_start, new DateInterval('P1D'), $period_end->modify('+1 day'));
$row_number = 0;
foreach ($dates as $date) {
    $date_string = $date->format('Y-m-d');
    if ((int) $date->format('N') >= 6) {
        $activities_text = 'Libur';
        $places_text = '-';
    } else {
        $day_activities = $activities_by_date[$date_string] ?? [];
        $activity_lines = [];
        $place_lines = [];
        foreach ($day_activities as $activity) {
            $activity_name = trim((string) ($activity['kegiatan'] ?? ''));
            if ($activity_name !== '') {
                $activity_lines[] = '- ' . $activity_name;
            }
            $place = trim((string) ($activity['tempat'] ?? ''));
            if ($place === '') {
                $place = trim((string) ($student['perusahaan'] ?? ''));
            }
            if ($place !== '' && !in_array($place, $place_lines, true)) {
                $place_lines[] = $place;
            }
        }
        $activities_text = $activity_lines ? implode("\n", $activity_lines) : '-';
        $places_text = $place_lines ? implode("\n", $place_lines) : '-';
    }

    $values = [
        (string) (++$row_number),
        $pdf_text($format_tanggal($date_string)),
        $pdf_text($activities_text),
        $pdf_text($places_text)
    ];
    $wrapped_values = [];
    $line_count = 1;
    foreach ($values as $index => $value) {
        $wrapped_values[$index] = $wrap_text($value, $column_widths[$index] - 6);
        $line_count = max($line_count, substr_count($wrapped_values[$index], "\n") + 1);
    }
    $row_height = max(9, $line_count * 5);
    if ($pdf->GetY() + $row_height > 267) {
        $pdf->AddPage();
        $draw_table_header();
    }

    $row_y = $pdf->GetY();
    $x = $table_x;
    foreach ($wrapped_values as $index => $value) {
        $pdf->Rect($x, $row_y, $column_widths[$index], $row_height);
        $lines = explode("\n", $value);
        $text_offset = max(1, ($row_height - ($line_count * 5)) / 2);
        foreach ($lines as $line_index => $line) {
            $pdf->Text($x + 2, $row_y + $text_offset + 4 + ($line_index * 5), $line);
        }
        $x += $column_widths[$index];
    }
    $pdf->SetXY($table_x, $row_y + $row_height);
}

if ($pdf->GetY() > 179) {
    $pdf->AddPage();
}
$signature_top = 183;
$pdf->SetXY(25, $signature_top);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(160, 8, 'Mengetahui,', 0, 1, 'C');
$signature_columns = [
    ['User DU/DI', $signatures['nama_user'] ?? '', $signatures['ttd_user'] ?? ''],
    ['Pembimbing PKL', $student['pembimbing'] ?? '', $signatures['ttd_pembimbing'] ?? ''],
    ['Peserta PKL', $student['nama'], $signatures['ttd_siswa'] ?? '']
];
$signature_width = 53;
foreach ($signature_columns as $index => [$label, $name, $image]) {
    $x = $table_x + ($index * $signature_width);
    $pdf->SetXY($x, $signature_top + 11);
    $pdf->Cell($signature_width, 7, $pdf_text($label), 0, 0, 'C');
    $image_path = __DIR__ . '/ttd_kegiatan/' . basename((string) $image);
    if ($image !== '' && is_file($image_path)) {
        $image_info = getimagesize($image_path);
        if ($image_info) {
            $image_width = 38;
            $image_height = min(15, $image_width * $image_info[1] / max(1, $image_info[0]));
            $pdf->Image(
                $image_path,
                $x + (($signature_width - $image_width) / 2),
                $signature_top + 28 + ((15 - $image_height) / 2),
                $image_width,
                $image_height
            );
        }
    }
    $pdf->SetXY($x, $signature_top + 61);
    $pdf->SetFillColor(255, 255, 0);
    $signature_name = $pdf_text($name);
    $name_width = min($signature_width, max(1, $pdf->GetStringWidth($signature_name) + 2));
    $pdf->SetX($x + (($signature_width - $name_width) / 2));
    $pdf->Cell($name_width, 7, $signature_name, 0, 0, 'C', $name !== '');
}

$safe_name = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $student['nama']);
$filename = 'Laporan-PKL-' . trim($safe_name, '-') . '-' . date('YmdHis') . '.pdf';
$pdf->Output('I', $filename);
