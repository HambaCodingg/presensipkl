<?php
session_start();
if (strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'admin') {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Metode tidak diizinkan.');
}

$id_siswa = filter_input(INPUT_GET, 'id_siswa', FILTER_VALIDATE_INT);
$kode_siswa = isset($_GET['kode_siswa']) ? trim((string) $_GET['kode_siswa']) : '';
if (!$id_siswa || $kode_siswa === '') {
    http_response_code(400);
    exit('Data siswa yang dipilih tidak valid.');
}

include '../../config/database.php';
mysqli_begin_transaction($kon);
$student_statement = mysqli_prepare($kon, 'DELETE FROM tbl_siswa WHERE id_siswa = ? AND kode_siswa = ?');
$user_statement = mysqli_prepare($kon, 'DELETE FROM tbl_user WHERE kode_pengguna = ?');
if (!$student_statement || !$user_statement) {
    mysqli_rollback($kon);
    http_response_code(500);
    exit('Query penghapusan siswa tidak dapat disiapkan.');
}
mysqli_stmt_bind_param($student_statement, 'is', $id_siswa, $kode_siswa);
$student_deleted = mysqli_stmt_execute($student_statement) && mysqli_stmt_affected_rows($student_statement) === 1;
mysqli_stmt_bind_param($user_statement, 's', $kode_siswa);
$user_deleted = $student_deleted && mysqli_stmt_execute($user_statement);
mysqli_stmt_close($student_statement);
mysqli_stmt_close($user_statement);

if ($student_deleted && $user_deleted && mysqli_commit($kon)) {
    header("Location:../../index.php?page=siswa&hapus=berhasil");
} else {
    mysqli_rollback($kon);
    header("Location:../../index.php?page=siswa&hapus=gagal");
}
exit;
