<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Profil</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Profil
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">

                <?php
                //Menghubungkan database
                include 'config/database.php';
                //Mengambil kode_pengguna dari session
                $kode_pengguna = $_SESSION["kode_pengguna"];
                //Query untuk menampilkan data siswa dari tbl_siswa
                $sql = "SELECT * FROM tbl_siswa WHERE kode_siswa='$kode_pengguna' LIMIT 1";
                //Menyimpan hasil query
                $hasil = mysqli_query($kon, $sql);
                //Menyimpan hasil jadi array
                $data = mysqli_fetch_array($hasil);
                ?>

                <?php
                //Validasi Untuk menampilkan memberitahuan saat siswa mengubah password
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Ubah Password berhasil</div>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Ubah Password gagal</div>";
                    }
                }
                ?>

                <table class="table">
                    <tbody>
                        <tr>
                            <td>Nama</td>
                            <td width="75%">: <?php echo $data['nama']; ?></td>
                        </tr>
                        <tr>
                            <td>Nomor Induk siswa</td>
                            <td width="75%">: <?php echo $data['nis']; ?></td>
                        </tr>
                        <tr>
                            <td>Perusahaan</td>
                            <td width="75%">: <?php echo $data['perusahaan']; ?></td>
                        </tr>
                        <tr>
                            <td>Jurusan</td>
                            <td width="75%">: <?php echo $data['jurusan']; ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Masuk</td>
                            <td width="75%">: <?php echo date('d/m/Y', strtotime($data["mulai_pkl"])); ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Selesai</td>
                            <td width="75%">: <?php echo date('d/m/Y', strtotime($data["akhir_pkl"])); ?></td>
                        </tr>
                        <tr>
                            <td>No Telp</td>
                            <td width="75%">: <?php echo $data['no_telp']; ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td width="75%">: <?php echo $data['alamat']; ?></td>
                        </tr>
                        <tr>
                            <td>Foto</td>
                            <td width="20%">: <img src="apps/siswa/foto/<?php echo $data['foto']; ?>" id="preview" width="25%" class="rounded" alt="Cinque Terre"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group">
                    <button kode_siswa="<?php echo $data['kode_siswa']; ?>" class="password btn btn-info btn-circle"><i class="fa fa-key"></i>Password</button>
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
                    <!-- Data akan di load menggunakan AJAX -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>

        </div>
    </div>
</div>
<!-- Modal -->

<script>
    // Setting password siswa
    $('.password').on('click', function() {
        var kode_siswa = $(this).attr("kode_siswa");
        $.ajax({
            url: 'apps/pengguna/ubah_password.php',
            method: 'post',
            data: {
                kode_siswa: kode_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Ubah Password';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>