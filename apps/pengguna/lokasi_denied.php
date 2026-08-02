<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (strtolower($_SESSION['level']) !== 'siswa') {
    header('Location: ../../index.php');
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Akses Lokasi Ditolak
            </div>
            <div class="panel-body">
                <p>Website ini membutuhkan akses lokasi agar Anda dapat menggunakan sistem.</p>
                <p>Silakan aktifkan lokasi di perangkat Anda lalu muat ulang halaman.</p>
                <button id="btn-kembali-lokasi" class="btn btn-primary">Coba Lagi</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#btn-kembali-lokasi').on('click', function() {
        window.location.href = 'index.php?page=verify_lokasi';
    });
</script>