<?php
session_start();
if (isset($_POST['submit'])) {
    include '../../config/database.php';

    function input($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $id_siswa = $_SESSION["id_siswa"];
    $status   = input($_POST["status"]);
    $latitude = input($_POST["latitude"]);
    $longitude = input($_POST["longitude"]);
    date_default_timezone_set("Asia/Jakarta");
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");
    $alasan = isset($_POST["alasan"]) ? input($_POST["alasan"]) : "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        $foto_baru = null;
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $foto_baru = date("Ymd_His") . "_{$id_siswa}." . $ext;
            $upload_dir = "../../uploads/asrama/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_baru)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=absen_asrama&mulai=gagal_upload");
                exit;
            }
        }

        $sql_absen = "INSERT INTO tbl_absen_asrama 
                (id_siswa, status, foto, latitude, longitude, waktu, tanggal) 
            VALUES 
                ('$id_siswa', '$status', '$foto_baru', '$latitude', '$longitude', '$waktu', '$tanggal')";
        $simpan_absensi = mysqli_query($kon, $sql_absen);

        if ($status == "2") {
            $sql_izin = "INSERT INTO tbl_alasan_asrama (id_siswa, alasan, tanggal) 
                VALUES ('$id_siswa', '$alasan', '$tanggal')";
            $simpan_izin = mysqli_query($kon, $sql_izin);
        } else {
            $simpan_izin = true;
        }

        if ($simpan_absensi && $simpan_izin) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=absen_asrama&mulai=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=absen_asrama&mulai=gagal");
        }
        exit;
    }
}

$id_siswa = $_SESSION["id_siswa"];
$tanggal_sekarang = date("Y-m-d");
include '../../config/database.php';
$query = "SELECT COUNT(*) as jml FROM tbl_absen_asrama WHERE tanggal = '$tanggal_sekarang' AND id_siswa = '$id_siswa'";
$result = mysqli_query($kon, $query);
$data = mysqli_fetch_assoc($result);
$absensi_sudah = ($data['jml'] > 0) ? "disabled" : "";
?>

<form action="apps/pengguna/mulai_absen_asrama.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status :</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="">Pilih</option>
                    <option value="1">Hadir</option>
                    <option value="2">Izin</option>
                    <option value="3">Tidak Hadir</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" placeholder="Masukkan Alasan Izin">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Foto Presensi Asrama:</label>
        <input type="file" name="foto" class="form-control" accept="image/*" capture="camera" required>
    </div>

    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit" id="tombol_absensi" class="simpan_absensi btn btn-primary" <?php echo $absensi_sudah; ?>>
                    <i class="fa fa-clock-o"></i> Absensi Asrama
                </button>
                <br>
                <?php if ($absensi_sudah == "disabled") { ?>
                    <small style="color: green;">👍 Anda sudah melakukan absensi asrama hari ini</small>
                <?php } else { ?>
                    <small style="color: #555;">👉 Klik tombol di atas untuk melakukan absensi asrama</small>
                <?php } ?>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        $("#status").change(function() {
            if ($(this).val() == "2") {
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#alasan").attr("required", false);
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                $('#latitude').val(position.coords.latitude);
                $('#longitude').val(position.coords.longitude);
            }, function() {
                alert('Gagal mengambil lokasi. Pastikan GPS aktif.');
            });
        }
    });

    $('.simpan_absensi').on('click', function() {
        return confirm("Konfirmasi sebelum absen asrama?");
    });
</script>