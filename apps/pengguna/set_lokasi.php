<?php
session_start();
if (strtolower($_SESSION['level']) !== 'siswa') {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

include '../../config/database.php';

$id_siswa = $_SESSION['id_siswa'] ?? null;
$latitude = isset($_POST['latitude']) ? mysqli_real_escape_string($kon, $_POST['latitude']) : '';
$longitude = isset($_POST['longitude']) ? mysqli_real_escape_string($kon, $_POST['longitude']) : '';
$location_allowed = isset($_POST['location_allowed']) ? 1 : 0;

if (!$id_siswa) {
    http_response_code(400);
    exit;
}

if ($location_allowed === 1) {
    $_SESSION['location_allowed'] = 1;
}

if ($latitude !== '' && $longitude !== '') {
    $create_table = "CREATE TABLE IF NOT EXISTS tbl_lokasi_siswa (
        id_lokasi INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        latitude VARCHAR(50) DEFAULT NULL,
        longitude VARCHAR(50) DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_siswa) REFERENCES tbl_siswa(id_siswa) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($kon, $create_table);

    $query = "SELECT id_lokasi FROM tbl_lokasi_siswa WHERE id_siswa = '$id_siswa' LIMIT 1";
    $result = mysqli_query($kon, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $query = "UPDATE tbl_lokasi_siswa SET latitude = '$latitude', longitude = '$longitude', updated_at = NOW() WHERE id_siswa = '$id_siswa'";
    } else {
        $query = "INSERT INTO tbl_lokasi_siswa (id_siswa, latitude, longitude, updated_at) VALUES ('$id_siswa', '$latitude', '$longitude', NOW())";
    }
    mysqli_query($kon, $query);
}

http_response_code(200);
