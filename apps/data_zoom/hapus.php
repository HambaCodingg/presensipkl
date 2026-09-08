<?php
session_start();
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') { exit; }
include '../../config/database.php';
$id_zoom = (int) ($_GET['id_zoom'] ?? 0);
$stmt = $kon->prepare('DELETE FROM tbl_zoom WHERE id_zoom = ?');
$stmt->bind_param('i', $id_zoom);
$stmt->execute();
header('Location:../../index.php?page=data_zoom');
