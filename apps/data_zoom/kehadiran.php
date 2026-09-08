<?php
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') { exit('Tidak Memiliki Hak Akses'); }
include '../../config/database.php';
$id_zoom = (int) ($_GET['id_zoom'] ?? 0);
$zoom_stmt = $kon->prepare('SELECT judul, tanggal, waktu_awal, waktu_akhir FROM tbl_zoom WHERE id_zoom = ?');
$zoom_stmt->bind_param('i', $id_zoom);
$zoom_stmt->execute();
$zoom = $zoom_stmt->get_result()->fetch_assoc();
$hadir_stmt = $kon->prepare('SELECT nama, kode_pengguna, level, joined_at, last_seen, left_at FROM tbl_zoom_kehadiran WHERE id_zoom = ? ORDER BY joined_at ASC');
$hadir_stmt->bind_param('i', $id_zoom);
$hadir_stmt->execute();
$hadir = $hadir_stmt->get_result();
?>
<div class="row"><ol class="breadcrumb"><li><a href="index.php?page=data_zoom"><em class="fa fa-video-camera"></em></a></li><li class="active">Daftar Hadir Zoom</li></ol></div>
<div class="panel panel-default"><div class="panel-heading">Daftar Hadir: <?php echo htmlspecialchars($zoom['judul'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div><div class="panel-body">
<p class="text-muted"><?php echo htmlspecialchars(($zoom['tanggal'] ?? '') . ' | ' . ($zoom['waktu_awal'] ?? '') . ' - ' . ($zoom['waktu_akhir'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>No</th><th>Nama</th><th>Kode</th><th>Level</th><th>Masuk</th><th>Aktif Terakhir</th><th>Status</th></tr></thead><tbody>
<?php $no = 0; while ($row = $hadir->fetch_assoc()): $no++; ?><tr><td><?php echo $no; ?></td><td><?php echo htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['kode_pengguna'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['level'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['joined_at'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['last_seen'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo empty($row['left_at']) ? '<span class="label label-success">Aktif</span>' : '<span class="label label-default">Keluar</span>'; ?></td></tr><?php endwhile; ?>
</tbody></table></div></div></div>