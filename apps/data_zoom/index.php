<?php
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') {
    echo '<div class="alert alert-danger">Tidak Memiliki Hak Akses</div>';
    exit;
}
include 'config/database.php';
$zoom_query = mysqli_query($kon, "SELECT z.* FROM tbl_zoom z ORDER BY z.tanggal DESC, z.waktu_awal DESC");
?>
<div class="row"><ol class="breadcrumb"><li><a href="index.php?page=beranda"><em class="fa fa-home"></em></a></li><li class="active">Data Zoom</li></ol></div>
<div class="row"><div class="col-md-12"><div class="panel panel-default"><div class="panel-heading"><i class="fa fa-video-camera"></i> Jadwal Zoom Internal</div><div class="panel-body">
    <p class="text-muted">Buat ruang video meeting internal untuk siswa. Jurnal kegiatan PKL tetap dikelola di menu terpisah.</p>
    <button type="button" class="btn btn-success" id="tambah_zoom"><i class="fa fa-plus"></i> Buat Meeting</button>
    <div class="table-responsive" style="margin-top:20px"><table class="table table-bordered"><thead><tr><th>No</th><th>Peserta</th><th>Judul</th><th>Tanggal</th><th>Waktu</th><th>Aksi</th></tr></thead><tbody>
    <?php $no = 0; while ($zoom_query && $zoom = mysqli_fetch_assoc($zoom_query)): $no++; ?>
        <tr><td><?php echo $no; ?></td><td>Semua siswa</td><td><?php echo htmlspecialchars($zoom['judul'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($zoom['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars(substr($zoom['waktu_awal'], 0, 5) . ' - ' . substr($zoom['waktu_akhir'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></td><td><a class="btn btn-primary btn-sm" href="apps/meeting.php?id_zoom=<?php echo (int) $zoom['id_zoom']; ?>" target="_blank"><i class="fa fa-video-camera"></i> Buka</a> <a class="btn btn-danger btn-sm" href="apps/data_zoom/hapus.php?id_zoom=<?php echo (int) $zoom['id_zoom']; ?>" onclick="return confirm('Hapus jadwal Zoom ini?')"><i class="fa fa-trash"></i></a></td></tr>
    <?php endwhile; ?>
    </tbody></table></div>
</div></div></div></div>
<div class="modal fade" id="modal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 id="judul" class="modal-title"></h4><button type="button" class="close" data-dismiss="modal">&times;</button></div><div id="tampil_data" class="modal-body"></div></div></div></div>
<script>$('#tambah_zoom').on('click',function(){ $.get('apps/data_zoom/tambah.php',function(data){ $('#judul').text('Buat Meeting Zoom Internal'); $('#tampil_data').html(data); $('#modal').modal('show'); }); });</script>
