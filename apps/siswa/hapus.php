<?php
session_start();
include '../../config/database.php';
mysqli_query($kon, "START TRANSACTION");

$id_siswa = $_GET['id_siswa'];
$kode_siswa = $_GET['kode_siswa'];

$hapus_admin = mysqli_query($kon, "DELETE FROM tbl_siswa WHERE id_siswa='$id_siswa'");
$hapus_pengguna = mysqli_query($kon, "DELETE FROM tbl_user WHERE kode_pengguna='$kode_admin'");

if ($hapus_admin and $hapus_pengguna) {
    mysqli_query($kon, "COMMIT");
    header("Location:../../index.php?page=siswa&hapus=berhasil");
} else {
    mysqli_query($kon, "ROLLBACK");
    header("Location:../../index.php?page=siswa&hapus=gagal");
}
