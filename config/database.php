<?php
$host = "localhost";
$user = "root";
$password = ""; // password kosong
$db = "db_presensi";

$kon = mysqli_connect($host, $user, $password, $db);

if (!$kon) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Menjaga database lama tetap kompatibel saat kolom jam masuk belum tersedia.
mysqli_query($kon, "ALTER TABLE tbl_siswa ADD COLUMN IF NOT EXISTS jam_masuk TIME DEFAULT '08:00:00' AFTER akhir_pkl");
