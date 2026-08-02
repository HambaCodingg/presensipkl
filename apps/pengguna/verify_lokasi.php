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
                Verifikasi Lokasi
            </div>
            <div class="panel-body">
                <p>Untuk melanjutkan, aktifkan layanan lokasi di perangkat Anda.</p>
                <div id="status-lokasi" class="alert alert-info">
                    Menunggu persetujuan lokasi...</div>
                <button id="btn-permohonan-lokasi" class="btn btn-primary">Izinkan Lokasi</button>
                <button id="btn-ulang" class="btn btn-secondary d-none">Coba Lagi</button>
            </div>
        </div>
    </div>
</div>

<script>
    function handleDenied() {
        window.location.href = 'index.php?page=lokasi_denied';
    }

    function requestLocation() {
        if (!navigator.geolocation) {
            $('#status-lokasi').removeClass('alert-info').addClass('alert-danger').text('Perangkat tidak mendukung lokasi.');
            $('#btn-permohonan-lokasi').hide();
            return;
        }

        $('#status-lokasi').text('Meminta akses lokasi...');
        navigator.geolocation.getCurrentPosition(function(position) {
            $.post('apps/pengguna/set_lokasi.php', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                location_allowed: 1
            }, function() {
                window.location.href = 'index.php?page=beranda';
            }).fail(function() {
                $('#status-lokasi').removeClass('alert-info').addClass('alert-danger').text('Gagal menyimpan lokasi.');
                $('#btn-ulang').removeClass('d-none');
            });
        }, function(error) {
            $('#status-lokasi').removeClass('alert-info').addClass('alert-danger');
            if (error.code === error.PERMISSION_DENIED) {
                $('#status-lokasi').text('Lokasi ditolak. Anda tidak dapat melanjutkan.');
            } else {
                $('#status-lokasi').text('Gagal mendapatkan lokasi. Pastikan GPS hidup.');
            }
            $('#btn-permohonan-lokasi').hide();
            $('#btn-ulang').removeClass('d-none');
        }, {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 10000
        });
    }

    $('#btn-permohonan-lokasi').on('click', function() {
        requestLocation();
    });

    $('#btn-ulang').on('click', function() {
        $(this).addClass('d-none');
        $('#btn-permohonan-lokasi').show();
        $('#status-lokasi').removeClass('alert-danger').addClass('alert-info').text('Menunggu persetujuan lokasi...');
        requestLocation();
    });
</script>
