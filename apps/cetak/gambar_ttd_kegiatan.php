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
$role = $_GET['role'] ?? '';
if ($level === 'siswa') {
    $id_siswa = filter_var($_SESSION['id_siswa'] ?? null, FILTER_VALIDATE_INT);
}
$signature_columns = [
    'user' => 'ttd_user',
    'pembimbing' => 'ttd_pembimbing',
    'siswa' => 'ttd_siswa'
];
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
    $tanggal_awal > $tanggal_akhir ||
    !isset($signature_columns[$role])
) {
    http_response_code(400);
    exit('Permintaan gambar tanda tangan tidak valid.');
}

include '../../config/database.php';
$column = $signature_columns[$role];
$statement = mysqli_prepare(
    $kon,
    "SELECT {$column} AS nama_file
     FROM tbl_laporan_kegiatan_ttd
     WHERE id_siswa = ? AND tanggal_awal = ? AND tanggal_akhir = ?
     LIMIT 1"
);
if (!$statement) {
    http_response_code(500);
    exit('Tanda tangan tidak dapat dimuat.');
}
mysqli_stmt_bind_param($statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($statement)) {
    http_response_code(500);
    exit('Tanda tangan tidak dapat dimuat.');
}
$result = mysqli_stmt_get_result($statement);
$signature = mysqli_fetch_assoc($result);
mysqli_stmt_close($statement);

$filename = basename((string) ($signature['nama_file'] ?? ''));
$path = __DIR__ . '/ttd_kegiatan/' . $filename;
if ($filename === '' || !is_file($path)) {
    http_response_code(404);
    exit('Tanda tangan tidak ditemukan.');
}
$image = getimagesize($path);
if (!$image || !in_array($image[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
    http_response_code(415);
    exit('Format tanda tangan tidak didukung.');
}

header('Content-Type: ' . $image['mime']);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
readfile($path);
