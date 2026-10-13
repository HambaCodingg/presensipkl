<?php
session_start();
if (
    strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'siswa' ||
    empty($_SESSION['id_siswa'])
) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

if (isset($_POST['simpan_kegiatan'])) {

    include '../../config/database.php';

    $id_siswa = $_SESSION["id_siswa"];
    date_default_timezone_set("Asia/Jakarta");
    $kegiatan = trim(isset($_POST["kegiatan"]) ? (string) $_POST["kegiatan"] : '');
    $waktu_awal = isset($_POST["waktu_awal"]) ? (string) $_POST["waktu_awal"] : '';
    $waktu_akhir = isset($_POST["waktu_akhir"]) ? (string) $_POST["waktu_akhir"] : '';
    $tempat = trim(isset($_POST["tempat"]) ? (string) $_POST["tempat"] : '');
    $tanggal = date("Y-m-d");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $statement = mysqli_prepare(
            $kon,
            'INSERT INTO tbl_kegiatan (id_siswa, kegiatan, tempat, waktu_awal, waktu_akhir, tanggal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'isssss', $id_siswa, $kegiatan, $tempat, $waktu_awal, $waktu_akhir, $tanggal);
            $simpan_kegiatan = mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
        } else {
            $simpan_kegiatan = false;
        }


        // validasi data
        if ($simpan_kegiatan) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=kegiatan&tambah=berhasil");
        } else {
            mysqli_query($kon, "ROlLBACK");
            header("Location:../../index.php?page=kegiatan&tambah=gagal");
        }
    }
}
?>

<form action="apps/pengguna/mulai_kegiatan.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" class="form-control" value="" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan :</label>
                <input type="time" name="waktu_akhir" class="form-control" value="" required>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Kegiatan :</label>
                <input type="text" name="kegiatan" class="form-control" value="" placeholder="Masukkan Kegiatan Anda?" required>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Tempat :</label>
                <input type="text" name="tempat" class="form-control" value="" placeholder="Masukkan tempat kegiatan" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_kegiatan" id="simpan_kegiatan" class="simpan_kegiatan btn btn-success"><i class="fa fa-plus"></i> Simpan</button>
                <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Hapus</button>
            </div>
        </div>
    </div>
</form>

<script>
    $('#simpan_kegiatan').on('click', function() {
        konfirmasi = confirm("Yakin ingin menyimpan kegiatan ini?")
        if (konfirmasi) {
            return true;
        } else {
            return false;
        }
    });
</script>