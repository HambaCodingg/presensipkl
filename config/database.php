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
mysqli_query($kon, "ALTER TABLE tbl_user ADD COLUMN IF NOT EXISTS remember_token_hash CHAR(64) DEFAULT NULL AFTER password");

mysqli_query($kon, "
    CREATE TABLE IF NOT EXISTS tbl_pengunjung (
        session_id varchar(128) NOT NULL,
        kode_pengguna varchar(4) DEFAULT NULL,
        username varchar(255) DEFAULT NULL,
        level varchar(50) DEFAULT NULL,
        halaman varchar(100) DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        last_seen datetime NOT NULL,
        PRIMARY KEY (session_id),
        KEY idx_pengunjung_last_seen (last_seen)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");
