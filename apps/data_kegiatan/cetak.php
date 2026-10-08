<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_siswa = $_POST['id_siswa'] ?? '';
?>

<form id="cetak-kegiatan-form" action="apps/cetak/cetak_kegiatan.php" method="GET" target="_blank">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <input type="hidden" name="id_siswa" value="<?php echo htmlspecialchars((string) $id_siswa, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="tanggal-awal-laporan">Tanggal Awal :</label>
                <input type="date" id="tanggal-awal-laporan" name="tanggal_awal" class="form-control" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tanggal-akhir-laporan">Tanggal Akhir :</label>
                <input type="date" id="tanggal-akhir-laporan" name="tanggal_akhir" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="cetak" class="btn btn-primary"><i class="fa fa-print"></i> Cetak Laporan</button>
            </div>
        </div>
    </div>
</form>

<script>
    (function() {
        var $form = $('#cetak-kegiatan-form');
        var $tanggalAwal = $form.find('[name="tanggal_awal"]');
        var $tanggalAkhir = $form.find('[name="tanggal_akhir"]');

        $tanggalAwal.on('change', function() {
            $tanggalAkhir.attr('min', this.value);
        });
        $tanggalAkhir.on('change', function() {
            $tanggalAwal.attr('max', this.value);
        });
        $form.on('submit', function(event) {
            var tanggalAwal = this.elements['tanggal_awal'].value;
            var tanggalAkhir = this.elements['tanggal_akhir'].value;
            if (tanggalAwal > tanggalAkhir) {
                event.preventDefault();
                alert('Tanggal awal tidak boleh melewati tanggal akhir.');
            }
        });
    })();
</script>