<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Presensi Asrama</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Data Presensi Asrama
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_asrama" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Nama Siswa :</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="" placeholder="Cari siswa" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal :</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir :</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                </br>
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tambah_absen_asrama"><i class="fa fa-plus"></i> Absensi Asrama</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Hari</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            include 'config/function.php';
                            if (isset($_GET['nama']) and $_GET['nama'] != "") {
                                $nama = trim($_GET["nama"]);
                                $tanggal_awal = $_GET["tanggal_awal"];
                                $tanggal_akhir = $_GET["tanggal_akhir"];
                                $sql = PencarianAbsensiAsrama($nama, $tanggal_awal, $tanggal_akhir);
                            } else {
                                $sql = AbsensiAsramaOtomatis('');
                            }
                            $hasil = mysqli_query($kon, $sql);
                            $no = 0;
                            while ($data = mysqli_fetch_array($hasil)):
                                $no++;
                            ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['perusahaan']; ?></td>
                                    <td><?php echo $data['status']; ?></td>
                                    <td><?php echo $data['waktu']; ?></td>
                                    <td>
                                        <?php
                                        $hari = $data["hari"];
                                        echo MendapatkanHari($hari);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tgl = date("d", strtotime($data['tanggal']));
                                        $bulan = date("m", strtotime($data['tanggal']));
                                        $tahun = date("Y", strtotime($data['tanggal']));
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun
                                        ?>
                                    </td>
                                    <td>
                                        <button id_siswa="<?php echo $data['id_siswa']; ?>" id_absen_asrama="<?php echo $data['id_absen_asrama']; ?>" class="absen_asrama btn btn-success btn-circle"><i class="fa fa-clock-o"></i> Absensi</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div id="tampil_data">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    $('#tambah_absen_asrama').on('click', function() {
        $.ajax({
            url: 'apps/data_asrama/tambah.php',
            method: 'post',
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Absensi Asrama';
            }
        });
        $('#modal').modal('show');
    });

    $('.absen_asrama').on('click', function() {
        var id_siswa = $(this).attr("id_siswa");
        var id_absen_asrama = $(this).attr("id_absen_asrama");
        $.ajax({
            url: 'apps/data_asrama/absensi.php',
            method: 'POST',
            data: {
                id_siswa: id_siswa,
                id_absen_asrama: id_absen_asrama
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Mulai Absensi Asrama';
            }
        });
        $('#modal').modal('show');
    });
</script>