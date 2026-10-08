<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$level = strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '');
if (!in_array($level, ['admin', 'siswa'], true)) {
    http_response_code(403);
    exit('Akses tidak diizinkan. Silakan masuk sebagai admin atau siswa.');
}

header('Content-Type: text/plain; charset=UTF-8');

$id_siswa = filter_input(INPUT_GET, 'id_siswa', FILTER_VALIDATE_INT);
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
if ($level === 'siswa') {
    $id_siswa = filter_var(isset($_SESSION['id_siswa']) ? $_SESSION['id_siswa'] : null, FILTER_VALIDATE_INT);
}

echo "Diagnostik laporan PKL\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "mysqli: " . (extension_loaded('mysqli') ? 'tersedia' : 'TIDAK TERSEDIA') . "\n";
echo "mysqli_stmt_bind_result: " . (function_exists('mysqli_stmt_bind_result') ? 'tersedia' : 'TIDAK TERSEDIA') . "\n";
echo "iconv: " . (function_exists('iconv') ? 'tersedia' : 'TIDAK TERSEDIA') . "\n\n";

require_once '../../config/database.php';
if (!isset($kon) || !$kon) {
    echo "GAGAL: koneksi database tidak tersedia.\n";
    exit;
}
echo "Koneksi database: berhasil\n";

$required_columns = [
    'tbl_site' => ['nama_instansi', 'website', 'logo'],
    'tbl_siswa' => ['id_siswa', 'nama', 'nis', 'perusahaan', 'pembimbing'],
    'tbl_laporan_kegiatan_ttd' => [
        'id_siswa', 'tanggal_awal', 'tanggal_akhir', 'nama_user',
        'ttd_user', 'nama_pembimbing', 'ttd_pembimbing', 'nama_siswa', 'ttd_siswa'
    ],
    'tbl_kegiatan' => ['id_kegiatan', 'id_siswa', 'tanggal', 'kegiatan', 'tempat', 'waktu_awal']
];

foreach ($required_columns as $table => $columns) {
    $result = mysqli_query($kon, "SHOW COLUMNS FROM `{$table}`");
    if (!$result) {
        echo "GAGAL membaca {$table}: " . mysqli_error($kon) . "\n";
        continue;
    }

    $available = [];
    while ($column = mysqli_fetch_assoc($result)) {
        $available[] = $column['Field'];
    }
    $missing = array_values(array_diff($columns, $available));
    if ($missing) {
        echo "GAGAL {$table}, kolom belum ada: " . implode(', ', $missing) . "\n";
    } else {
        echo "OK struktur {$table}\n";
    }
    mysqli_free_result($result);
}

if (!$id_siswa || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggal_awal) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggal_akhir) ||
    $tanggal_awal > $tanggal_akhir) {
    echo "\nID siswa/tanggal tidak valid. Buka halaman ini dengan id_siswa, tanggal_awal, dan tanggal_akhir dari URL laporan.\n";
    exit;
}

$student_statement = mysqli_prepare(
    $kon,
    'SELECT nama, nis, perusahaan, pembimbing FROM tbl_siswa WHERE id_siswa = ? LIMIT 1'
);
if (!$student_statement) {
    echo "GAGAL menyiapkan query siswa: " . mysqli_error($kon) . "\n";
    exit;
}
mysqli_stmt_bind_param($student_statement, 'i', $id_siswa);
if (!mysqli_stmt_execute($student_statement)) {
    echo "GAGAL menjalankan query siswa: " . mysqli_stmt_error($student_statement) . "\n";
    mysqli_stmt_close($student_statement);
    exit;
}
mysqli_stmt_bind_result($student_statement, $student_name, $student_nis, $student_company, $student_advisor);
$student_found = mysqli_stmt_fetch($student_statement);
mysqli_stmt_close($student_statement);
echo $student_found ? "OK query siswa\n" : "GAGAL: siswa dengan ID tersebut tidak ditemukan\n";

$signature_statement = mysqli_prepare(
    $kon,
    'SELECT nama_user, ttd_user, nama_pembimbing, ttd_pembimbing, nama_siswa, ttd_siswa
     FROM tbl_laporan_kegiatan_ttd
     WHERE id_siswa = ? AND tanggal_awal = ? AND tanggal_akhir = ?
     LIMIT 1'
);
if (!$signature_statement) {
    echo "GAGAL menyiapkan query tanda tangan: " . mysqli_error($kon) . "\n";
    exit;
}
mysqli_stmt_bind_param($signature_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($signature_statement)) {
    echo "GAGAL menjalankan query tanda tangan: " . mysqli_stmt_error($signature_statement) . "\n";
    mysqli_stmt_close($signature_statement);
    exit;
}
mysqli_stmt_bind_result(
    $signature_statement,
    $signature_user_name,
    $signature_user,
    $signature_advisor_name,
    $signature_advisor,
    $signature_student_name,
    $signature_student
);
mysqli_stmt_fetch($signature_statement);
mysqli_stmt_close($signature_statement);
echo "OK query tanda tangan\n";

$activity_statement = mysqli_prepare(
    $kon,
    'SELECT tanggal, kegiatan, tempat
     FROM tbl_kegiatan
     WHERE id_siswa = ? AND tanggal BETWEEN ? AND ?
     ORDER BY tanggal ASC, waktu_awal ASC, id_kegiatan ASC'
);
if (!$activity_statement) {
    echo "GAGAL menyiapkan query kegiatan: " . mysqli_error($kon) . "\n";
    exit;
}
mysqli_stmt_bind_param($activity_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($activity_statement)) {
    echo "GAGAL menjalankan query kegiatan: " . mysqli_stmt_error($activity_statement) . "\n";
    mysqli_stmt_close($activity_statement);
    exit;
}
mysqli_stmt_close($activity_statement);
echo "OK query kegiatan\n";
echo "\nDiagnostik selesai. Jika halaman laporan masih error, kirim seluruh hasil diagnostik ini.\n";
