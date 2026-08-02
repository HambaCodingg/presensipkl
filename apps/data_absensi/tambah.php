<?php
session_start();
if (isset($_POST['simpan_absensi'])) {

    include '../../config/database.php';

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $id_siswa = $_POST["id_siswa"];
    $tanggal = $_POST["tanggal"];
    $waktu = $_POST["waktu"];
    $status = $_POST["status"];
    $alasan = $_POST["alasan"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $query = "SELECT * FROM tbl_absensi WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal'";
        $result = mysqli_query($kon, $query);

        if (mysqli_num_rows($result) > 0) {
            $simpan_absensi = true;
        } else {
            // Menambahkan data ke tabel absensi
            $sql = "INSERT INTO tbl_absensi (id_siswa,status,waktu,tanggal) VALUES 
            ('$id_siswa','$status','$waktu','$tanggal')";
            $simpan_absensi = mysqli_query($kon, $sql);
        }


        if ($status == "2") {
            $sql = "INSERT INTO tbl_alasan (id_siswa,alasan,tanggal) VALUES 
            ('$id_siswa', '$alasan', '$tanggal')";
            $simpan_izin = mysqli_query($kon, $sql);
        } else {
            $simpan_izin = true;
        }


        // validasi data
        if ($simpan_absensi and $simpan_izin) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else if ($simpan_absensi) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else {
            mysqli_query($kon, "ROlLBACK");
            header("Location:../../index.php?page=data_absensi&mulai=gagal");
        }
    }
}
?>


<form action="apps/data_absensi/tambah.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama siswa :</label>
                <select class="form-control" id="id_siswa" name="id_siswa" required>
                    <?php
                    include '../../config/database.php';
                    $query = "SELECT id_siswa, nama FROM tbl_siswa WHERE mulai_pkl <= CURDATE() AND akhir_pkl >= CURDATE();";
                    $result = mysqli_query($kon, $query);
                    while ($data = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $data['id_siswa'] . "'>" . $data['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status :</label>
                <div class="select-wrap" style="position:relative;">
                    <select class="form-control" id="status" name="status" required style="color:transparent;">
                        <option value="" disabled selected>Pilih</option>
                        <option value="1">Hadir</option>
                        <option value="2">Izin</option>
                        <option value="3">Tidak Hadir</option>
                    </select>
                    <span id="status-display" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#000;">Pilih</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Absensi :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Absensi :</label>
                <input type="time" name="waktu" id="waktu" class="form-control" value="">
            </div>
        </div>
        <div class="col-sm-12" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" value="" placeholder="Masukkan Alasan Kenapa Izin">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_absensi" id="simpan_absensi" class="btn btn-success" style="position:relative;z-index:99999;pointer-events:auto;cursor:pointer;" onclick="return checkTimeAndSubmit(this);"> <i class="fa fa-plus"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>

<style>
    /* Ensure modal form buttons are clickable even if template adds overlays */
    .modal,
    .modal.fade,
    .modal-dialog,
    .modal-content,
    .modal-body,
    .modal-footer,
    .modal-header {
        pointer-events: auto !important;
    }

    .modal-backdrop {
        z-index: 20040 !important;
    }

    .modal {
        z-index: 20050 !important;
    }

    .modal.show .modal-dialog,
    .modal.show .modal-content {
        z-index: 20060 !important;
    }

    #simpan_absensi,
    #simpan_absensi *,
    .modal-footer button,
    .modal-footer * {
        position: relative !important;
        z-index: 20099 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .modal-backdrop,
    .modal-backdrop.show {
        pointer-events: none !important;
    }

    .select-wrap span {
        pointer-events: none;
    }
</style>

<?php
// Ambil setting jam absen jika tersedia
include '../../config/database.php';
$mulai_absen = '07:00:00';
$akhir_absen = '07:59:59';
$q = mysqli_query($kon, "SELECT mulai_absen, akhir_absen FROM tbl_setting_absensi LIMIT 1");
if ($q && mysqli_num_rows($q) > 0) {
    $r = mysqli_fetch_assoc($q);
    $mulai_absen = $r['mulai_absen'] ?? $mulai_absen;
    $akhir_absen = $r['akhir_absen'] ?? $akhir_absen;
}
?>

<script>
    function checkTimeAndSubmit(button) {
        var mulaiAbsen = '<?php echo $mulai_absen; ?>';
        var akhirAbsen = '<?php echo $akhir_absen; ?>';

        function timeToSeconds(t) {
            var p = t.split(':');
            return (+p[0]) * 3600 + (+p[1]) * 60 + (+p[2] || 0);
        }
        var now = new Date();
        var hh = now.getHours().toString().padStart(2, '0');
        var mm = now.getMinutes().toString().padStart(2, '0');
        var ss = now.getSeconds().toString().padStart(2, '0');
        var nowS = timeToSeconds(hh + ':' + mm + ':' + ss);
        var mulaiS = timeToSeconds(mulaiAbsen);
        var akhirS = timeToSeconds(akhirAbsen);
        if (nowS < mulaiS || nowS > akhirS) {
            alert('Absen hanya bisa dilakukan antara ' + mulaiAbsen + ' dan ' + akhirAbsen + '.');
            return false;
        }
        if (button && button.form) {
            button.form.submit();
            return false;
        }
        return true;
    }
</script>

<script>
    var mulaiAbsen = '<?php echo $mulai_absen; ?>';
    var akhirAbsen = '<?php echo $akhir_absen; ?>';

    function timeToSeconds(t) {
        var p = t.split(':');
        return (+p[0]) * 3600 + (+p[1]) * 60 + (+p[2] || 0);
    }

    $(document).ready(function() {
        function updateStatusDisplay() {
            var txt = $('#status option:selected').text() || 'Pilih';
            $('#status-display').text(txt);
        }
        updateStatusDisplay();

        $("#status").change(function() {
            // Menampilkan input teks jika opsi "izin" dipilih
            if ($(this).val() == "2") {
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#alasan").attr("required", false);
            }
            updateStatusDisplay();
        });

        // Pastikan tombol bisa diklik - tidak ada disable otomatis di sini

        // Periksa waktu saat submit: hanya izinkan jika sekarang di antara mulaiAbsen dan akhirAbsen
        $('#simpan_absensi').closest('form').on('submit', function(e) {
            var now = new Date();
            var hh = now.getHours().toString().padStart(2, '0');
            var mm = now.getMinutes().toString().padStart(2, '0');
            var ss = now.getSeconds().toString().padStart(2, '0');
            var nowS = timeToSeconds(hh + ':' + mm + ':' + ss);
            var mulaiS = timeToSeconds(mulaiAbsen);
            var akhirS = timeToSeconds(akhirAbsen);
            if (nowS < mulaiS || nowS > akhirS) {
                e.preventDefault();
                alert('Absen hanya bisa dilakukan antara ' + mulaiAbsen + ' dan ' + akhirAbsen + '.');
                return false;
            }
            return true;
        });
    });
</script>