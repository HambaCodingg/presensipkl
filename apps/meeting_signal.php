<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['kode_pengguna'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesi login tidak valid']);
    exit;
}

include '../config/database.php';
$session_id = session_id();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$room_id = (int) ($_POST['room_id'] ?? $_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Room tidak valid']);
    exit;
}

$level = strtolower($_SESSION['level'] ?? '');
$id_siswa = (int) ($_SESSION['id_siswa'] ?? 0);
$access_stmt = $kon->prepare('SELECT id_siswa FROM tbl_kegiatan WHERE id_kegiatan = ? AND meeting_enabled = 1 LIMIT 1');
if (!$access_stmt) {
    http_response_code(503);
    echo json_encode(['error' => 'Fitur meeting belum dimigrasikan']);
    exit;
}
$access_stmt->bind_param('i', $room_id);
$access_stmt->execute();
$meeting_access = $access_stmt->get_result()->fetch_assoc();
if (!$meeting_access || ($level === 'siswa' && (int) $meeting_access['id_siswa'] !== $id_siswa)) {
    http_response_code(403);
    echo json_encode(['error' => 'Tidak memiliki akses ke meeting ini']);
    exit;
}

if ($action === 'send') {
    $recipient_id = $_POST['recipient_id'] ?? null;
    $signal_type = $_POST['signal_type'] ?? '';
    $payload = $_POST['payload'] ?? '';
    $allowed_types = ['join', 'offer', 'answer', 'ice', 'leave'];

    if (!in_array($signal_type, $allowed_types, true) || $payload === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Signal tidak valid']);
        exit;
    }

    $stmt = $kon->prepare('INSERT INTO tbl_meeting_signals (room_id, sender_id, recipient_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        http_response_code(503);
        echo json_encode(['error' => 'Tabel meeting belum tersedia']);
        exit;
    }
    $stmt->bind_param('issss', $room_id, $session_id, $recipient_id, $signal_type, $payload);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'list') {
    $after_id = (int) ($_GET['after_id'] ?? 0);
    $stmt = $kon->prepare('SELECT id_signal, sender_id, signal_type, payload FROM tbl_meeting_signals WHERE room_id = ? AND id_signal > ? AND sender_id <> ? AND (recipient_id IS NULL OR recipient_id = ?) ORDER BY id_signal ASC LIMIT 100');
    if (!$stmt) {
        echo json_encode(['signals' => []]);
        exit;
    }
    $stmt->bind_param('iiss', $room_id, $after_id, $session_id, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $signals = [];
    while ($signal = $result->fetch_assoc()) {
        $signals[] = $signal;
    }
    echo json_encode(['signals' => $signals]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Aksi tidak dikenal']);
