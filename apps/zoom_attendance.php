<?php
session_start();
header('Content-Type: application/json');
if (empty($_SESSION['kode_pengguna'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesi login tidak valid']);
    exit;
}
include '../config/database.php';
$id_zoom = (int) ($_POST['id_zoom'] ?? $_GET['id_zoom'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? 'checkin';
$kode = $_SESSION['kode_pengguna'];
$nama = $_SESSION['nama_siswa'] ?? $_SESSION['nama_admin'] ?? $_SESSION['username'];
$level = $_SESSION['level'] ?? '';
if ($id_zoom <= 0) { http_response_code(400); echo json_encode(['ok' => false]); exit; }

if ($action === 'leave') {
    $stmt = $kon->prepare('UPDATE tbl_zoom_kehadiran SET left_at = NOW(), last_seen = NOW() WHERE id_zoom = ? AND kode_pengguna = ?');
    $stmt->bind_param('is', $id_zoom, $kode);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

$stmt = $kon->prepare('INSERT INTO tbl_zoom_kehadiran (id_zoom, kode_pengguna, nama, level) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nama = VALUES(nama), level = VALUES(level), last_seen = NOW(), left_at = NULL');
if (!$stmt) { http_response_code(503); echo json_encode(['ok' => false, 'message' => 'Tabel kehadiran belum dimigrasikan']); exit; }
$stmt->bind_param('isss', $id_zoom, $kode, $nama, $level);
$stmt->execute();
echo json_encode(['ok' => true]);