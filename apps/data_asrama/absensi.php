<?php
session_start();

if (isset($_POST['submit_absensi'])) {
    include '../../config/database.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        function input($data)
        {
            return htmlspecialchars(stripslashes(trim($data)));
        }

        $id_siswa           = $_POST['id_siswa'];
        $id_absen_asrama    = $_POST['id_absen_asrama'];
        $id_alasan_asrama   = $_POST['id_alasan_asrama'];
        $status             = $_POST["status"];
        $tanggal            = $_POST["tanggal"];
        $waktu              = $_POST["waktu"];
        $alasan             = input($_POST["alasan"] ?? '');

        $latitude           = $_POST["latitude"] ?? '';
        $longitude          = $_POST["longitude"] ?? '';

        $foto = '';
        if (!empty($_FILES['foto']['name'])) {
            $namaFile  = time() . "_" . basename($_FILES['foto']['name']);
            $targetDir = "../../uploads/asrama/";
            $targetFile = $targetDir . $namaFile;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                $foto = $namaFile;
            }
        }

        if (empty($id_absen_asrama)) {
            $sql = "INSERT INTO tbl_absen_asrama (id_siswa, status, tanggal, waktu, foto, latitude, longitude)
                    VALUES ('$id_siswa', '$status', '$tanggal', '$waktu', '$foto', '$latitude', '$longitude')";
        } else {
            if (empty($foto)) {
                $cek = mysqli_query($kon, "SELECT foto FROM tbl_absen_asrama WHERE id_absen_asrama='$id_absen_asrama' LIMIT 1");
                if ($cek && mysqli_num_rows($cek) > 0) {
                    $row = mysqli_fetch_assoc($cek);
                    $foto = $row['foto'];
                }
            }

            $sql = "UPDATE tbl_absen_asrama SET 
                        id_siswa  = '$id_siswa', 
                        status    = '$status', 
                        tanggal   = '$tanggal', 
                        waktu     = '$waktu',
                        foto      = '$foto',
                        latitude  = '$latitude',
                        longitude = '$longitude'
                    WHERE id_absen_asrama = '$id_absen_asrama'";
        }
        $simpan_absensi = mysqli_query($kon, $sql);

        if (empty($id_alasan_asrama)) {
            $sql = "INSERT INTO tbl_alasan_asrama (id_siswa, alasan, tanggal) 
                    VALUES ('$id_siswa', '$alasan', '$tanggal')";
        } else {
            $sql = "UPDATE tbl_alasan_asrama SET
                        id_siswa = '$id_siswa', 
                        alasan   = '$alasan', 
                        tanggal  = '$tanggal' 
                    WHERE id_alasan_asrama = '$id_alasan_asrama'";
        }
        $simpan_izin = mysqli_query($kon, $sql);

        if ($simpan_absensi && $simpan_izin) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_asrama&mulai=berhasil");
        } else if ($simpan_absensi) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_asrama&mulai=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=data_asrama&mulai=gagal");
        }
        exit;
    }
}

$id_absen_asrama = $_POST['id_absen_asrama'] ?? '';
include '../../config/database.php';
include '../../config/function.php';

$sql    = EditAbsensiAsrama($id_absen_asrama);
$result = $kon->query($sql);

if ($result && $result->num_rows > 0) {
    $row       = $result->fetch_assoc();
    $id_siswa  = $row['id_siswa'] ?? '';
    $status    = $row['status'] ?? '';
    $tanggal   = $row['tanggal'] ?? '';
    $waktu     = $row['waktu'] ?? '';
    $foto      = $row['foto'] ?? '';
    $latitude  = $row['latitude'] ?? '';
    $longitude = $row['longitude'] ?? '';

    if ($foto === '' && $latitude === '' && $longitude === '' && !empty($id_absen_asrama)) {
        $id_absen_asrama_safe = mysqli_real_escape_string($kon, $id_absen_asrama);
        $q2 = mysqli_query($kon, "SELECT foto, latitude, longitude 
                                  FROM tbl_absen_asrama 
                                  WHERE id_absen_asrama = '$id_absen_asrama_safe' 
                                  LIMIT 1");
        if ($q2 && mysqli_num_rows($q2) > 0) {
            $r2        = mysqli_fetch_assoc($q2);
            $foto      = $r2['foto'] ?? '';
            $latitude  = $r2['latitude'] ?? '';
            $longitude = $r2['longitude'] ?? '';
        }
    }
} else {
    $id_siswa  = $_POST['id_siswa'] ?? '';
    $status    = '';
    $foto      = '';
    $latitude  = '';
    $longitude = '';
    date_default_timezone_set("Asia/Jakarta");
    $tanggal   = date("Y-m-d");
    $waktu     = date("H:i:s");
}

$id_alasan_asrama = '';
$alasan    = '';
if (!empty($id_siswa) && !empty($tanggal)) {
    $q_alasan = $kon->query("SELECT id_alasan_asrama, alasan 
                             FROM tbl_alasan_asrama 
                             WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal' 
                             LIMIT 1");
    if ($q_alasan && $q_alasan->num_rows > 0) {
        $r_alasan = $q_alasan->fetch_assoc();
        $id_alasan_asrama = $r_alasan['id_alasan_asrama'] ?? '';
        $alasan    = $r_alasan['alasan'] ?? '';
    }
}
?>

<form action="apps/data_asrama/absensi.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <input type="hidden" name="id_siswa" value="<?php echo htmlspecialchars($_POST['id_siswa'] ?? $id_siswa); ?>">
            <input type="hidden" name="id_absen_asrama" value="<?php echo htmlspecialchars($_POST['id_absen_asrama'] ?? $id_absen_asrama); ?>">
            <input type="hidden" name="id_alasan_asrama" value="<?php echo htmlspecialchars($id_alasan_asrama); ?>">
            <input type="file" name="foto" accept="image/*" class="form-control">
            <input type="hidden" name="latitude" value="<?php echo $latitude; ?>">
            <input type="hidden" name="longitude" value="<?php echo $longitude; ?>">

            <div class="form-group">
                <label>Tanggal Presensi Asrama :</label>
                <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Presensi Asrama :</label>
                <input type="time" name="waktu" class="form-control" value="<?php echo htmlspecialchars($waktu); ?>" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Status Presensi Asrama :</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="0" <?php if ($status == 0 || $status === '') echo 'selected'; ?>>Pilih</option>
                    <option value="1" <?php if ($status == 1) echo 'selected'; ?>>Hadir</option>
                    <option value="2" <?php if ($status == 2) echo 'selected'; ?>>Izin</option>
                    <option value="3" <?php if ($status == 3) echo 'selected'; ?>>Tidak Hadir</option>
                </select>
            </div>
        </div>

        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control"
                    value="<?php echo htmlspecialchars($alasan); ?>"
                    placeholder="Masukkan alasan">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <label>Foto Presensi Asrama:</label><br>
            <?php if (!empty($foto)) {
                $imgPath = "uploads/asrama/" . rawurlencode($foto);
            ?>
                <a href="<?php echo $imgPath; ?>" target="_blank" title="Klik untuk lihat ukuran penuh">
                    <img src="<?php echo $imgPath; ?>" alt="Foto Presensi Asrama"
                        style="max-width:150px; border:1px solid #ccc; border-radius:5px;">
                </a>
            <?php } else { ?>
                <p><i>Tidak ada foto</i></p>
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit_absensi" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
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

        if ($("#status").val() == "2") {
            $("#text_alasan").show();
            $("#alasan").attr("required", true);
        }
    });
</script>