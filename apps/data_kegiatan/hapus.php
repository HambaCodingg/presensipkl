<?php
session_start();
if (!isset($_SESSION["level"]) || (strtolower($_SESSION["level"]) !== 'admin')) {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
include '../../config/database.php';
mysqli_query($kon, "START TRANSACTION");

$id_kegiatan = $_GET['id_kegiatan'];

$hapus_kegiatan = mysqli_query($kon, "DELETE from tbl_kegiatan where id_kegiatan='$id_kegiatan'");

if ($hapus_kegiatan) {
    mysqli_query($kon, "COMMIT");
    header("Location:../../index.php?page=data_kegiatan&hapus=berhasil");
} else {
    mysqli_query($kon, "ROLLBACK");
    header("Location:../../index.php?page=data_kegiatan&hapus=gagal");
}
