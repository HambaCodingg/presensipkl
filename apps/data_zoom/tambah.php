<?php
session_start();
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') { exit; }
include '../../config/database.php';
if (isset($_POST['simpan_zoom'])) {
    $judul = mysqli_real_escape_string($kon, trim($_POST['judul']));
    $tanggal = mysqli_real_escape_string($kon, $_POST['tanggal']);
    $waktu_awal = mysqli_real_escape_string($kon, $_POST['waktu_awal']);
    $waktu_akhir = mysqli_real_escape_string($kon, $_POST['waktu_akhir']);
    $stmt = $kon->prepare('INSERT INTO tbl_zoom (judul, tanggal, waktu_awal, waktu_akhir, dibuat_oleh) VALUES (?, ?, ?, ?, ?)');
    $admin_id = (int) $_SESSION['id_pengguna'];
    $stmt->bind_param('ssssi', $judul, $tanggal, $waktu_awal, $waktu_akhir, $admin_id);
    if ($stmt->execute()) { header('Location:../../index.php?page=data_zoom'); } else { echo '<div class="alert alert-danger">Gagal menyimpan meeting. Pastikan migrasi database sudah dijalankan.</div>'; }
    exit;
}
?>
<form action="apps/data_zoom/tambah.php" method="post">
    <div class="alert alert-info"><i class="fa fa-users"></i> Meeting ini otomatis dapat diikuti oleh semua siswa.</div>
    <div class="form-group"><label>Judul Meeting</label><input type="text" name="judul" class="form-control" placeholder="Contoh: Pembekalan PKL" required></div>
    <div class="row"><div class="col-sm-4"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" required></div><div class="col-sm-4"><label>Mulai</label><input type="time" name="waktu_awal" class="form-control" required></div><div class="col-sm-4"><label>Selesai</label><input type="time" name="waktu_akhir" class="form-control" required></div></div>
    <button type="submit" name="simpan_zoom" class="btn btn-success" style="margin-top:18px"><i class="fa fa-save"></i> Simpan Meeting</button>
</form>
