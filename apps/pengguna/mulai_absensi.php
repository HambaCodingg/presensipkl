<?php
session_start();
if (isset($_POST['submit'])) {
    include '../../config/database.php';

    function input($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Ambil data dari session & form
    $id_siswa  = $_SESSION["id_siswa"];
    $status    = input($_POST["status"]);
    $latitude  = input($_POST["latitude"]);
    $longitude = input($_POST["longitude"]);
    date_default_timezone_set("Asia/Jakarta");
    $tanggal   = date("Y-m-d");
    $waktu     = date("H:i:s");
    $alasan    = isset($_POST["alasan"]) ? input($_POST["alasan"]) : "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        // Upload foto absensi (support normal file upload or blob sent via AJAX)
        $foto_baru = null;
        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        // If PHP received a file in $_FILES (standard upload)
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $foto_baru = date("Ymd_His") . "_{$id_siswa}." . $ext;
            $upload_dir = "../../uploads/absensi/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_baru)) {
                mysqli_query($kon, "ROLLBACK");
                if ($is_ajax) {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah foto']);
                } else {
                    header("Location:../../index.php?page=absen&mulai=gagal_upload");
                }
                exit;
            }
        }

        // Cek jam absensi dari setting
        $cek_waktu = "
            SELECT 
                CONCAT(CURDATE(), ' ', mulai_absen) as mulai_absen, 
                CONCAT(CURDATE(), ' ', akhir_absen) as akhir_absen, 
                NOW() as waktu_sekarang 
            FROM tbl_setting_absensi 
            LIMIT 1
        ";
        $query   = mysqli_query($kon, $cek_waktu);
        $setting = mysqli_fetch_array($query);
        $mulai_absen     = $setting["mulai_absen"];
        $akhir_absen     = $setting["akhir_absen"];
        $waktu_sekarang  = $setting["waktu_sekarang"];

        // Simpan absensi
        if ($waktu_sekarang >= $mulai_absen && $waktu_sekarang <= $akhir_absen) {
            $sql_absen = "
                INSERT INTO tbl_absensi 
                    (id_siswa, status, foto, latitude, longitude, waktu, tanggal) 
                VALUES 
                    ('$id_siswa', '$status', '$foto_baru', '$latitude', '$longitude', '$waktu', '$tanggal')
            ";
            $simpan_absensi = mysqli_query($kon, $sql_absen);
        } else {
            $simpan_absensi = false;
        }

        // Jika status izin, simpan alasan
        if ($status == "2") {
            $sql_izin = "
                INSERT INTO tbl_alasan (id_siswa, alasan, tanggal) 
                VALUES ('$id_siswa', '$alasan', '$tanggal')
            ";
            $simpan_izin = mysqli_query($kon, $sql_izin);
        } else {
            $simpan_izin = true;
        }

        // Commit / Rollback transaksi
        if ($simpan_absensi && $simpan_izin) {
            mysqli_query($kon, "COMMIT");
            if ($is_ajax) {
                echo json_encode(['status' => 'ok', 'redirect' => '../../index.php?page=absen&mulai=berhasil']);
            } else {
                header("Location:../../index.php?page=absen&mulai=berhasil");
            }
        } else {
            mysqli_query($kon, "ROLLBACK");
            if ($is_ajax) {
                echo json_encode(['status' => 'error', 'redirect' => '../../index.php?page=absen&mulai=gagal']);
            } else {
                header("Location:../../index.php?page=absen&mulai=gagal");
            }
        }
    }
}
?>

<?php
// Cek apakah sudah absen hari ini
$id_siswa = $_SESSION["id_siswa"];
$tanggal_sekarang = date("Y-m-d");
include '../../config/database.php';
$query  = "SELECT COUNT(*) as jml FROM tbl_absensi WHERE tanggal = '$tanggal_sekarang' AND id_siswa = '$id_siswa'";
$result = mysqli_query($kon, $query);
$data   = mysqli_fetch_assoc($result);
$absensi_sudah = ($data['jml'] > 0) ? "disabled" : "";
?>

<style>
    /* Force select visibility: placeholder grey, selected option black */
    #absenForm #status {
        color: #6c757d !important;
        background-color: #fff !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    #absenForm #status option {
        color: #000 !important;
    }
</style>

<form id="absenForm" action="apps/pengguna/mulai_absensi.php" method="post" enctype="multipart/form-data">
    <div class="row">
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
        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" placeholder="Masukkan Alasan Kenapa Izin?">
            </div>
        </div>
    </div>

    <!-- Foto Absensi: kamera capture + fallback file -->
    <div class="form-group">
        <label>Foto Absensi (Selfie):</label>
        <div id="camera-area">
            <video id="cam" playsinline autoplay style="width:100%;max-width:320px;border:1px solid #ccc;border-radius:6px;"></video>
            <canvas id="cv" style="display:none;"></canvas>
            <div style="margin-top:8px;">
                <button type="button" id="btn-capture" class="btn btn-secondary">Ambil Selfie</button>
                <button type="button" id="btn-retake" class="btn btn-warning d-none">Ulangi</button>
            </div>
            <div style="margin-top:8px;">
                <img id="preview" src="" alt="Preview" style="display:none;max-width:320px;border:1px solid #ccc;border-radius:6px;" />
            </div>
        </div>
        <div style="margin-top:10px;">
            <small class="text-muted">Pastikan kamera aktif dan beri izin ketika diminta.</small>
        </div>
    </div>

    <!-- Lokasi -->
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit" id="tombol_hari"
                    class="simpan_absensi btn btn-primary"
                    <?php echo $absensi_sudah; ?>>
                    <i class="fa fa-clock-o"></i> Absensi
                </button>
                <br>
                <?php if ($absensi_sudah == "disabled") { ?>
                    <small style="color: green;">
                        👍 Anda sudah melakukan absensi hari ini
                    </small>
                <?php } else { ?>
                    <small style="color: #555;">
                        👉 Klik tombol di atas untuk melakukan absensi
                    </small>
                <?php } ?>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Tampilkan alasan jika status = izin
        // Atur warna teks select: placeholder abu, setelah pilih jadi hitam
        $('#status').css('color', $('#status').val() === '' ? 'transparent' : 'transparent');
        // initialize overlay text
        function updateStatusDisplay() {
            var txt = $('#status option:selected').text() || 'Pilih';
            $('#status-display').text(txt);
        }
        updateStatusDisplay();
        $("#status").change(function() {
            if ($(this).val() == "2") {
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#alasan").attr("required", false);
            }
            updateStatusDisplay();
        });

        // Disable tombol di hari Sabtu / Minggu
        var hari = new Date().getDay();
        if (hari == 0 || hari == 6) {
            $('#tombol_hari').attr('disabled', true);
        }

        // Ambil lokasi otomatis
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                $('#latitude').val(position.coords.latitude);
                $('#longitude').val(position.coords.longitude);
            }, function() {
                alert('Gagal mengambil lokasi. Pastikan GPS aktif.');
            });
        }
    });

    // Camera capture + AJAX submit
    (function() {
        var video = document.getElementById('cam');
        var canvas = document.getElementById('cv');
        var preview = document.getElementById('preview');
        var btnCapture = document.getElementById('btn-capture');
        var btnRetake = document.getElementById('btn-retake');
        var capturedBlob = null;

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    },
                    audio: false
                })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function() {
                    // camera not available
                    video.style.display = 'none';
                    btnCapture.style.display = 'none';
                });
        }

        btnCapture.addEventListener('click', function() {
            // capture frame
            canvas.width = video.videoWidth || 320;
            canvas.height = video.videoHeight || 240;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(function(blob) {
                capturedBlob = blob;
                preview.src = URL.createObjectURL(blob);
                preview.style.display = 'block';
                btnRetake.classList.remove('d-none');
            }, 'image/jpeg', 0.9);
        });

        btnRetake.addEventListener('click', function() {
            capturedBlob = null;
            preview.src = '';
            preview.style.display = 'none';
            btnRetake.classList.add('d-none');
        });

        // intercept form submit, build FormData and send via fetch
        document.getElementById('absenForm').addEventListener('submit', function(ev) {
            if (!confirm('Konfirmasi sebelum absen?')) {
                ev.preventDefault();
                return;
            }
            ev.preventDefault();
            var form = this;
            var fd = new FormData(form);

            // require capturedBlob (no file input fallback)
            if (capturedBlob) {
                fd.set('foto', capturedBlob, 'selfie.jpg');
            } else {
                alert('Silakan ambil selfie terlebih dahulu.');
                return;
            }

            // send via fetch
            fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(function(resp) {
                return resp.json();
            }).then(function(json) {
                if (json && json.redirect) {
                    window.location = json.redirect;
                } else if (json && json.status === 'ok') {
                    window.location = '../../index.php?page=absen&mulai=berhasil';
                } else {
                    alert('Gagal melakukan absen.');
                }
            }).catch(function(err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengirim absen.');
            });
        });

        startCamera();
    })();
</script>