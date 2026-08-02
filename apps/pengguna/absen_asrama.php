<?php
if ($_SESSION["level"] != 'Siswa' and $_SESSION["level"] != 'Siswa') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<?php
include 'config/database.php';
include 'config/function.php';

$id_siswa = $_SESSION["id_siswa"];
$sql = "select * from tbl_siswa where id_siswa=$id_siswa limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
$nama = $data['nama'];
$perusahaan = $data['perusahaan'];
$nis = $data['nis'];
$mulai_pkl = $data['mulai_pkl'];
$akhir_pkl = $data['akhir_pkl'];
$foto = $data['foto'];

$tanggal_sekarang = date("Y-m-d");
$query = "SELECT COUNT(*) as jml FROM tbl_absen_asrama WHERE tanggal = '$tanggal_sekarang' AND id_siswa = '$id_siswa'";
$result = mysqli_query($kon, $query);
$data_absen = mysqli_fetch_assoc($result);
$absensi_sudah = ($data_absen['jml'] > 0) ? "disabled" : "";
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=absen_asrama">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Presensi Asrama</li>
    </ol>
</div>
<!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Presensi Asrama</div>
            <div class="panel-body">
                <div class="row">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Nama siswa</td>
                                <td width="80%">: <?php echo $nama; ?></td>
                            </tr>
                            <tr>
                                <td>Nomor Induk siswa</td>
                                <td width="80%">: <?php echo $nis; ?></td>
                            </tr>
                            <tr>
                                <td>Perusahaan</td>
                                <td width="80%">: <?php echo $perusahaan; ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td width="80%">: <?php echo date("d") . ' ' . MendapatkanBulan(date("m")) . ' ' . date("Y"); ?></td>
                            </tr>
                            <tr>
                                <td>Waktu</td>
                                <td width="80%">:
                                    <?php
                                    if ($absensi_sudah) {
                                        $kueri = "SELECT waktu FROM tbl_absen_asrama WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal_sekarang'";
                                        $result = mysqli_query($kon, $kueri);
                                        $data = mysqli_fetch_assoc($result);
                                        echo $data['waktu'];
                                    } else {
                                        echo "Belum Absensi";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td width="80%">:
                                    <?php
                                    if ($absensi_sudah) {
                                        $kueri = "SELECT status FROM tbl_absen_asrama WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal_sekarang'";
                                        $result = mysqli_query($kon, $kueri);
                                        $data = mysqli_fetch_assoc($result);
                                        echo StatusAbsensi($data['status']);
                                    } else {
                                        echo "Belum Absensi";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button id_siswa="<?php echo $id_siswa; ?>" class="mulai_absensi_asrama btn btn-success btn-circle" <?php echo $absensi_sudah; ?>><i class="fa fa-clock-o"></i> Absensi Asrama</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

<script>
    $('.mulai_absensi_asrama').on('click', function() {
        var id_siswa = $(this).attr('id_siswa');
        $.ajax({
            url: 'apps/pengguna/mulai_absen_asrama.php',
            method: 'post',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById('judul').innerHTML = 'Mulai Absensi Asrama';
            }
        });
        $('#modal').modal('show');
    });
</script>