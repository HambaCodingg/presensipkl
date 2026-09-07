<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$password = ""; // password kosong
$db = "db_presensi";

$kon = mysqli_connect($host, $user, $password, $db);

if (!$kon) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Struktur database dijalankan melalui database/smartpkl.sql, bukan pada setiap request.
