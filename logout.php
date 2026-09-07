<?php
//Memulai session
session_start();
require_once 'config/database.php';

$presence_session_id = session_id();
$presence_stmt = $kon->prepare("DELETE FROM tbl_pengunjung WHERE session_id = ?");
$presence_stmt->bind_param("s", $presence_session_id);
$presence_stmt->execute();

//Set session
$id_pengguna = $_SESSION['id_pengguna'];
$_SESSION['id_pengguna'] = '';
$_SESSION['kode_pengguna'] = '';
$_SESSION['nama_pengguna'] = '';
$_SESSION['username'] = '';
$_SESSION['level'] = '';
$_SESSION['foto'] = '';

//Hapus session
unset($_SESSION['id_pengguna']);
unset($_SESSION['kode_pengguna']);
unset($_SESSION['nama_pengguna']);
unset($_SESSION['username']);
unset($_SESSION['level']);
unset($_SESSION['foto']);

session_unset();
session_destroy();

header('Location:login.php');
