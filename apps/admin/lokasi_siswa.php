<?php
if (strtolower($_SESSION['level']) !== 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda"><em class="fa fa-home"></em></a></li>
        <li class="active">Lokasi Siswa</li>
    </ol>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Lokasi Siswa Saat Ini
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Waktu Update</th>
                                <th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';

                            $sql = "SELECT s.nama, s.perusahaan, l.latitude, l.longitude, l.updated_at
                                    FROM tbl_siswa s
                                    LEFT JOIN tbl_lokasi_siswa l ON s.id_siswa = l.id_siswa
                                    ORDER BY l.updated_at DESC, s.nama ASC";
                            $result = mysqli_query($kon, $sql);
                            $no = 1;

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $latitude = $row['latitude'] ?: '-';
                                    $longitude = $row['longitude'] ?: '-';
                                    $updated_at = $row['updated_at'] ?: '-';
                                    $map_link = ($row['latitude'] && $row['longitude']) ?
                                        "https://www.google.com/maps?q={$row['latitude']},{$row['longitude']}" : '#';
                            ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['perusahaan']); ?></td>
                                        <td><?php echo htmlspecialchars($latitude); ?></td>
                                        <td><?php echo htmlspecialchars($longitude); ?></td>
                                        <td><?php echo htmlspecialchars($updated_at); ?></td>
                                        <td>
                                            <?php if ($row['latitude'] && $row['longitude']): ?>
                                                <a href="<?php echo $map_link; ?>" target="_blank" class="btn btn-xs btn-info">Lihat</a>
                                            <?php else: ?>
                                                <span class="text-muted">Belum tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="7" class="text-center">Data lokasi siswa belum tersedia.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>