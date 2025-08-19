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

        $id_siswa   = $_POST['id_siswa'];
        $id_absensi = $_POST['id_absensi'];
        $id_alasan  = $_POST['id_alasan'];
        $status     = $_POST["status"];
        $tanggal    = $_POST["tanggal"];
        $waktu      = $_POST["waktu"];
        $alasan     = $_POST["alasan"];

        // latitude & longitude dari form
        $latitude   = $_POST["latitude"] ?? '';
        $longitude  = $_POST["longitude"] ?? '';

        // --- Proses upload foto ---
        $foto = '';
        if (!empty($_FILES['foto']['name'])) {
            $namaFile  = time() . "_" . basename($_FILES['foto']['name']);
            $targetDir = "../../uploads/absensi/";
            $targetFile = $targetDir . $namaFile;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                $foto = $namaFile;
            }
        }

        // --- INSERT BARU ---
        if (empty($id_absensi)) {
            $sql = "INSERT INTO tbl_absensi (id_siswa, status, tanggal, waktu, foto, latitude, longitude)
                    VALUES ('$id_siswa', '$status', '$tanggal', '$waktu', '$foto', '$latitude', '$longitude')";
        } else {
            // --- UPDATE LAMA ---
            // jika tidak upload foto baru, ambil foto lama
            if (empty($foto)) {
                $cek = mysqli_query($kon, "SELECT foto FROM tbl_absensi WHERE id_absensi='$id_absensi' LIMIT 1");
                if ($cek && mysqli_num_rows($cek) > 0) {
                    $row = mysqli_fetch_assoc($cek);
                    $foto = $row['foto'];
                }
            }

            $sql = "UPDATE tbl_absensi SET 
                        id_siswa  = '$id_siswa', 
                        status    = '$status', 
                        tanggal   = '$tanggal', 
                        waktu     = '$waktu',
                        foto      = '$foto',
                        latitude  = '$latitude',
                        longitude = '$longitude'
                    WHERE id_absensi = '$id_absensi'";
        }
        $simpan_absensi = mysqli_query($kon, $sql);

        // --- SIMPAN ALASAN ---
        if (empty($id_alasan)) {
            $sql = "INSERT INTO tbl_alasan (id_siswa, alasan, tanggal) 
                    VALUES ('$id_siswa', '$alasan', '$tanggal')";
        } else {
            $sql = "UPDATE tbl_alasan SET
                        id_siswa = '$id_siswa', 
                        alasan   = '$alasan', 
                        tanggal  = '$tanggal' 
                    WHERE id_alasan = '$id_alasan'";
        }
        $simpan_izin = mysqli_query($kon, $sql);

        if ($simpan_absensi && $simpan_izin) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else if ($simpan_absensi) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=data_absensi&mulai=gagal");
        }
        exit;
    }
}


// --------- TAMPIL DATA ----------
$id_absensi = $_POST['id_absensi'] ?? '';
include '../../config/database.php';
include '../../config/function.php';

$sql    = EditAbsensi($id_absensi); // mungkin belum include kolom foto/lokasi
$result = $kon->query($sql);

if ($result && $result->num_rows > 0) {
    $row       = $result->fetch_assoc();
    $id_siswa  = $row['id_siswa'] ?? '';
    $status    = $row['status'] ?? '';
    $tanggal   = $row['tanggal'] ?? '';
    $waktu     = $row['waktu'] ?? '';

    // aman dari warning walau query tidak select kolom ini
    $foto      = $row['foto'] ?? '';
    $latitude  = $row['latitude'] ?? '';
    $longitude = $row['longitude'] ?? '';

    // Fallback: kalau kolom tidak tersedia di SELECT, ambil langsung
    if ($foto === '' && $latitude === '' && $longitude === '' && !empty($id_absensi)) {
        $id_absensi_safe = mysqli_real_escape_string($kon, $id_absensi);
        $q2 = mysqli_query($kon, "SELECT foto, latitude, longitude 
                                  FROM tbl_absensi 
                                  WHERE id_absensi = '$id_absensi_safe' 
                                  LIMIT 1");
        if ($q2 && mysqli_num_rows($q2) > 0) {
            $r2        = mysqli_fetch_assoc($q2);
            $foto      = $r2['foto'] ?? '';
            $latitude  = $r2['latitude'] ?? '';
            $longitude = $r2['longitude'] ?? '';
        }
    }
} else {
    // data baru
    $id_siswa  = $_POST['id_siswa'] ?? '';
    $status    = '';
    $foto      = '';
    $latitude  = '';
    $longitude = '';
    date_default_timezone_set("Asia/Jakarta");
    $tanggal   = date("Y-m-d");
    $waktu     = date("H:i:s");
}

// ambil alasan (jika ada) sesuai id_siswa & tanggal
$id_alasan = '';
$alasan    = '';
if (!empty($id_siswa) && !empty($tanggal)) {
    $q_alasan = $kon->query("SELECT id_alasan, alasan 
                             FROM tbl_alasan 
                             WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal' 
                             LIMIT 1");
    if ($q_alasan && $q_alasan->num_rows > 0) {
        $r_alasan = $q_alasan->fetch_assoc();
        $id_alasan = $r_alasan['id_alasan'] ?? '';
        $alasan    = $r_alasan['alasan'] ?? '';
    }
}
?>

<form action="apps/data_absensi/absensi.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <!-- hidden keys -->
            <input type="hidden" name="id_siswa" value="<?php echo htmlspecialchars($_POST['id_siswa'] ?? $id_siswa); ?>">
            <input type="hidden" name="id_absensi" value="<?php echo htmlspecialchars($_POST['id_absensi'] ?? $id_absensi); ?>">
            <input type="hidden" name="id_alasan" value="<?php echo htmlspecialchars($id_alasan); ?>">
            <input type="file" name="foto" accept="image/*" class="form-control">
            <input type="hidden" name="latitude" value="<?php echo $latitude; ?>">
            <input type="hidden" name="longitude" value="<?php echo $longitude; ?>">

            <div class="form-group">
                <label>Tanggal Presensi :</label>
                <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Presensi :</label>
                <input type="time" name="waktu" class="form-control" value="<?php echo htmlspecialchars($waktu); ?>" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Status Presensi :</label>
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

    <!-- FOTO & LOKASI (READ-ONLY) -->
    <div class="row">
        <div class="col-sm-6">
            <label>Foto Presensi:</label><br>
            <?php if (!empty($foto)) {
                // path URL langsung (bisa diakses browser)
                $imgPath = "uploads/absensi/" . rawurlencode($foto);
            ?>
                <a href="<?php echo $imgPath; ?>" target="_blank" title="Klik untuk lihat ukuran penuh">
                    <img src="<?php echo $imgPath; ?>" alt="Foto Presensi"
                        style="max-width:150px; border:1px solid #ccc; border-radius:5px;">
                </a>
            <?php } else { ?>
                <p><i>Tidak ada foto</i></p>
            <?php } ?>

        </div>

        <div class="col-sm-6">
            <label>Lokasi GPS:</label><br>
            <?php if (!empty($latitude) && !empty($longitude)) {
                $maps = "https://www.google.com/maps?q=" . urlencode($latitude . "," . $longitude);
            ?>
                <p style="margin-bottom:6px;"><?php echo htmlspecialchars($latitude . ", " . $longitude); ?></p>
                <a href="<?php echo $maps; ?>" target="_blank" class="btn btn-info btn-sm">
                    Lihat di Google Maps
                </a>
            <?php } else { ?>
                <p><i>Tidak ada lokasi</i></p>
            <?php } ?>
        </div>
    </div>

    <div class="row" style="margin-top:14px;">
        <div class="col-sm-4">
            <button type="submit" name="submit_absensi" id="submit_absensi" class="btn btn-success">
                <i class="fa fa-save"></i> Simpan
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        function toggleAlasan() {
            if ($('#status').val() == "2") {
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#alasan").attr("required", false);
            }
        }
        $('#status').on('change', toggleAlasan);
        toggleAlasan(); // set awal
    });
</script>