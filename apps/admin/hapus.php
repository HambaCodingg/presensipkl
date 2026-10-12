<?php
session_start();

if (strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'admin') {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

$csrf_token = isset($_SESSION['csrf_admin_action']) ? (string) $_SESSION['csrf_admin_action'] : '';
if ($csrf_token === '' || !hash_equals($csrf_token, isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '')) {
    http_response_code(400);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}

$id_admin = filter_input(INPUT_POST, 'id_admin', FILTER_VALIDATE_INT);
$kode_admin = trim(isset($_POST['kode_admin']) ? (string) $_POST['kode_admin'] : '');
$session_code = isset($_SESSION['kode_pengguna']) ? (string) $_SESSION['kode_pengguna'] : '';
if (!$id_admin || $kode_admin === '' || hash_equals($session_code, $kode_admin)) {
    http_response_code(400);
    exit('Admin yang dipilih tidak valid atau tidak dapat menghapus akunnya sendiri.');
}

require_once '../../config/database.php';
if (!mysqli_begin_transaction($kon)) {
    http_response_code(500);
    exit('Penghapusan admin tidak dapat dimulai.');
}

$admin_statement = mysqli_prepare(
    $kon,
    'DELETE FROM tbl_admin WHERE id_admin = ? AND kode_admin = ?'
);
$user_statement = mysqli_prepare(
    $kon,
    'DELETE FROM tbl_user WHERE kode_pengguna = ?'
);
if (!$admin_statement || !$user_statement) {
    mysqli_rollback($kon);
    http_response_code(500);
    exit('Query penghapusan admin tidak dapat disiapkan.');
}

mysqli_stmt_bind_param($admin_statement, 'is', $id_admin, $kode_admin);
$deleted_admin = mysqli_stmt_execute($admin_statement) && mysqli_stmt_affected_rows($admin_statement) === 1;
mysqli_stmt_bind_param($user_statement, 's', $kode_admin);
$deleted_user = $deleted_admin && mysqli_stmt_execute($user_statement);
mysqli_stmt_close($admin_statement);
mysqli_stmt_close($user_statement);

if ($deleted_admin && $deleted_user && mysqli_commit($kon)) {
    header('Location:../../index.php?page=admin&hapus=berhasil');
} else {
    mysqli_rollback($kon);
    header('Location:../../index.php?page=admin&hapus=gagal');
}
exit;
