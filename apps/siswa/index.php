<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Siswa</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Data Siswa
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="siswa" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="cari" id="cari" class="form-control" value="" placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
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

                <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user menambah admin
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Disimpan</div>";
                    } else if ($_GET['add'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Disimpan</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user mengedit admin
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Diupdate</div>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Diupdate</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user menghapus admin
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Setting Data siswa Berhasil</div>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Setting Data siswa Gagal</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user menghapus admin
                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Dihapus</div>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Dihapus</div>";
                    }
                }
                ?>
                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah"><i class="fa fa-plus"></i> Tambah</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>NIS</th>
                                <th>Mulai PKL</th>
                                <th>Akhir PKL</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            include 'config/database.php';
                            if (isset($_GET['cari']) and $_GET['cari'] != "") {
                                $cari = trim($_GET["cari"]);
                                $sql = "SELECT * FROM tbl_siswa WHERE 
                                nama LIKE '%$cari%' OR 
                                nis LIKE '%$cari%' OR 
                                perusahaan LIKE '%$cari%' 
                                OR jurusan LIKE '%$cari%';";
                            } else {
                                $sql = "SELECT * FROM tbl_siswa";
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
                                    <td><?php echo $data['nis']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data["mulai_pkl"])); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data["akhir_pkl"])); ?></td>
                                    <td><img src="apps/siswa/foto/<?php echo $data["foto"]; ?>" width="120"></td>
                                    <td>
                                        <button id_siswa="<?php echo $data['id_siswa']; ?>" class="tombol_detail btn btn-success btn-circle"><i class="fa fa-mouse-pointer"></i></button>
                                        <button kode_siswa="<?php echo $data['kode_siswa']; ?>" class="tombol_setting btn btn-primary btn-circle"><i class="fa fa-user"></i></button>
                                        <button id_siswa="<?php echo $data['id_siswa']; ?>" class="tombol_edit btn btn-warning btn-circle"><i class="fa fa-edit"></i></button>
                                        <a href="apps/siswa/hapus.php?id_siswa=<?php echo $data['id_siswa']; ?>&kode_siswa=<?php echo $data['kode_siswa']; ?>" class="btn-hapus-siswa btn btn-danger btn-circle"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <!-- bagian akhir (penutup) while -->
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

<!-- Data akan di load menggunakan AJAX -->
<script>
    // Tambah admin
    $('#tombol_tambah').on('click', function() {
        $.ajax({
            url: 'apps/siswa/tambah.php',
            method: 'post',
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah siswa';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Detail siswa
    $('.tombol_detail').on('click', function() {
        var id_siswa = $(this).attr("id_siswa");
        $.ajax({
            url: 'apps/siswa/detail.php',
            method: 'post',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Detail siswa';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Setting siswa
    $('.tombol_setting').on('click', function() {
        var kode_siswa = $(this).attr("kode_siswa");
        $.ajax({
            url: 'apps/siswa/pengguna.php',
            method: 'post',
            data: {
                kode_siswa: kode_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Setting siswa';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Edit siswa
    $('.tombol_edit').on('click', function() {
        var id_siswa = $(this).attr("id_siswa");
        $.ajax({
            url: 'apps/siswa/edit.php',
            method: 'post',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit siswa';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Hapus admin
    $('.btn-hapus-siswa').on('click', function() {
        konfirmasi = confirm("Konfirmasi Sebelum Menghapus siswa?")
        if (konfirmasi) {
            return true;
        } else {
            return false;
        }
    });
</script>
<!-- Data akan di load menggunakan AJAX -->