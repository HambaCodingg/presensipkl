<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Presensi</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Data Presensi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_absensi" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Nama Siswa :</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="<?php echo htmlspecialchars($_GET['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Opsional - cari siswa">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal :</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="<?php echo htmlspecialchars($_GET['tanggal_awal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir :</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="<?php echo htmlspecialchars($_GET['tanggal_akhir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                </br>
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">



                <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user update pengaturan aplikasi                
                if (isset($_GET['mulai'])) {
                    if ($_GET['mulai'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Daya Absensi Berhasil Ditambah</div>";
                    } else if ($_GET['mulai'] == 'gagal') {
                        echo "<div class='alert alert-warning'><strong>Maaf!</strong> Data Absensi Sudah Ada</div>";
                    }
                }
                ?>

                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tambah_absensi"><i class="tambah_absensi fa fa-plus"></i> Absensi</button>
                </div>
                <?php
                $is_filtered = !empty($_GET['nama']) || !empty($_GET['tanggal_awal']) || !empty($_GET['tanggal_akhir']);
                $matrix_dates = [];
                $matrix_rows = [];
                if ($is_filtered) {
                    $matrix_start = $_GET['tanggal_awal'] ?? $_GET['tanggal_akhir'] ?? date('Y-m-d');
                    $matrix_end = $_GET['tanggal_akhir'] ?? $_GET['tanggal_awal'] ?? $matrix_start;
                    if ($matrix_start > $matrix_end) {
                        [$matrix_start, $matrix_end] = [$matrix_end, $matrix_start];
                    }

                    $period = new DatePeriod(
                        new DateTime($matrix_start),
                        new DateInterval('P1D'),
                        (new DateTime($matrix_end))->modify('+1 day')
                    );
                    foreach ($period as $matrix_date) {
                        if ((int) $matrix_date->format('N') <= 5) {
                            $matrix_dates[] = $matrix_date->format('Y-m-d');
                        }
                    }

                    $matrix_name = mysqli_real_escape_string($kon, trim($_GET['nama'] ?? ''));
                    $matrix_name_filter = $matrix_name !== '' ? "AND s.nama LIKE '%$matrix_name%'" : '';
                    $matrix_query = mysqli_query($kon, "
                        SELECT s.id_siswa, s.nama, a.tanggal, a.status AS status_code,
                            a.foto, a.waktu
                        FROM tbl_siswa s
                        LEFT JOIN tbl_absensi a
                            ON a.id_siswa = s.id_siswa
                            AND a.tanggal BETWEEN '" . mysqli_real_escape_string($kon, $matrix_start) . "'
                                AND '" . mysqli_real_escape_string($kon, $matrix_end) . "'
                        WHERE s.mulai_pkl <= '" . mysqli_real_escape_string($kon, $matrix_end) . "'
                            AND s.akhir_pkl >= '" . mysqli_real_escape_string($kon, $matrix_start) . "'
                            $matrix_name_filter
                        ORDER BY s.nama ASC, a.tanggal ASC
                    ");
                    while ($matrix_query && $matrix_data = mysqli_fetch_assoc($matrix_query)) {
                        $id_matrix = $matrix_data['id_siswa'];
                        if (!isset($matrix_rows[$id_matrix])) {
                            $matrix_rows[$id_matrix] = [
                                'nama' => $matrix_data['nama'],
                                'absensi' => []
                            ];
                        }
                        if (!empty($matrix_data['tanggal'])) {
                            $matrix_rows[$id_matrix]['absensi'][$matrix_data['tanggal']] = $matrix_data;
                        }
                    }
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <?php if ($is_filtered): ?>
                            <thead class="attendance-matrix-head">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <?php foreach ($matrix_dates as $matrix_date): ?>
                                        <th><?php echo (int) date('j', strtotime($matrix_date)); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="attendance-matrix-body">
                                <?php $matrix_no = 0; ?>
                                <?php foreach ($matrix_rows as $matrix_row): ?>
                                    <?php $matrix_no++; ?>
                                    <tr>
                                        <td><?php echo $matrix_no; ?></td>
                                        <td class="matrix-student-name"><?php echo htmlspecialchars($matrix_row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php foreach ($matrix_dates as $matrix_date): ?>
                                            <?php
                                            $cell = $matrix_row['absensi'][$matrix_date] ?? null;
                                            $status_code = (int) ($cell['status_code'] ?? 0);
                                            $status_label = $status_code === 1 ? 'Hadir' : ($status_code === 2 ? 'Izin' : 'Alfa');
                                            $status_class = $status_code === 1 ? 'matrix-hadir' : ($status_code === 2 ? 'matrix-izin' : 'matrix-alfa');
                                            ?>
                                            <td>
                                                <button type="button" class="matrix-status <?php echo $status_class; ?> <?php echo !empty($cell['foto']) ? 'view-attendance-photo' : ''; ?>"
                                                    <?php if (!empty($cell['foto'])): ?>
                                                        data-photo="<?php echo htmlspecialchars('uploads/absensi/' . $cell['foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-name="<?php echo htmlspecialchars($matrix_row['nama'] . ' - ' . $status_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php endif; ?>
                                                    title="<?php echo !empty($cell['foto']) ? 'Klik untuk melihat foto' : $status_label; ?>">
                                                    <?php echo $status_label; ?>
                                                </button>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Hari</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            include 'config/database.php';
                            include 'config/function.php';
                            if (!empty($_GET['nama']) || !empty($_GET['tanggal_awal']) || !empty($_GET['tanggal_akhir'])) {
                                $nama = trim($_GET['nama'] ?? '');
                                $tanggal_awal = !empty($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : ($_GET['tanggal_akhir'] ?? '');
                                $tanggal_akhir = !empty($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : ($_GET['tanggal_awal'] ?? '');
                                $sql = PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir);
                            } else {
                                $sql = AbsensiOtomatis('');
                            }
                            $hasil = mysqli_query($kon, $sql);
                            $no = 0;
                            //Menampilkan data dengan perulangan while
                            while ($data = mysqli_fetch_array($hasil)):
                                $no++;
                            ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['perusahaan']; ?></td>
                                    <td>
                                        <?php if (!empty($data['foto'])): ?>
                                            <img src="<?php echo htmlspecialchars('uploads/absensi/' . $data['foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="Foto absensi <?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                                                class="attendance-photo-thumb view-attendance-photo"
                                                data-photo="<?php echo htmlspecialchars('uploads/absensi/' . $data['foto'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-name="<?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Klik untuk melihat foto">
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int) ($data['status_code'] ?? 0) === 1): ?>
                                            <span class="label label-success"><i class="fa fa-check"></i> Hadir</span>
                                        <?php else: ?>
                                            <span><?php echo htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $data['waktu']; ?></td>
                                    <td>
                                        <?php
                                        $hari = $data["hari"];
                                        echo MendapatkanHari($hari);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tgl = date("d", strtotime($data['tanggal']));
                                        $bulan = date("m", strtotime($data['tanggal']));
                                        $tahun = date("Y", strtotime($data['tanggal']));
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun
                                        ?>
                                    </td>
                                    <td>
                                        <button id_siswa="<?php echo $data['id_siswa']; ?>" id_absensi="<?php echo $data['id_absensi']; ?>" class="absensi btn btn-success btn-circle"><i class="fa fa-clock-o"></i> Absensi</button>
                                        <button id_siswa="<?php echo $data['id_siswa']; ?>" class="cetak btn btn-primary btn-circle"><i class="fa fa-print"></i> Cetak</button>
                                    </td>
                                </tr>
                                <!-- bagian akhir (penutup) while -->
                            <?php endwhile; ?>
                        </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<style>
    .attendance-photo-thumb {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        object-fit: cover;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .attendance-photo-thumb:hover {
        transform: scale(1.04);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .2);
    }

    .attendance-matrix-head th {
        min-width: 72px;
        padding: 8px 6px !important;
        color: #111827;
        background: #fff900;
        text-align: center;
    }

    .attendance-matrix-head th:nth-child(2) {
        min-width: 250px;
        text-align: left;
    }

    .attendance-matrix-body td {
        padding: 4px !important;
        vertical-align: middle;
        text-align: center;
    }

    .attendance-matrix-body td:first-child,
    .attendance-matrix-body .matrix-student-name {
        text-align: left;
    }

    .attendance-matrix-body td:first-child {
        width: 42px;
        text-align: center;
    }

    .matrix-student-name {
        min-width: 250px;
        padding-left: 8px !important;
        color: #111827;
        font-weight: 500;
    }

    .matrix-status {
        display: block;
        width: 100%;
        min-width: 64px;
        padding: 5px 4px;
        border: 0;
        border-radius: 3px;
        font-size: .8rem;
        font-weight: 600;
        cursor: default;
    }

    .matrix-status.view-attendance-photo {
        cursor: pointer;
    }

    .matrix-hadir {
        color: #166534;
        background: #d9f3c5;
    }

    .matrix-izin {
        color: #075985;
        background: #b9ddf6;
    }

    .matrix-alfa {
        color: #991b1b;
        background: #fee2e2;
    }
</style>

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
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    // Satu tanggal boleh dipakai sebagai filter tanggal tunggal.
    $('form[action="#"]').on('submit', function(e) {
        var tanggalAwal = $('#tanggal_awal').val();
        var tanggalAkhir = $('#tanggal_akhir').val();
        if (!tanggalAwal && !tanggalAkhir) {
            e.preventDefault();
            alert('Isi minimal satu tanggal untuk melakukan pencarian.');
            return false;
        }
        if (!tanggalAwal) {
            $('#tanggal_awal').val(tanggalAkhir);
        }
        if (!tanggalAkhir) {
            $('#tanggal_akhir').val(tanggalAwal);
        }
    });

    $(document).on('click', '.view-attendance-photo', function() {
        var photo = $(this).data('photo');
        var name = $(this).data('name');
        $('#judul').text('Foto Absensi - ' + name);
        $('#tampil_data').html('<div class="text-center"><img src="' + photo + '" alt="Foto absensi ' + name + '" style="max-width:100%;max-height:65vh;border-radius:8px;object-fit:contain;"></div>');
        $('#modal').modal('show');
    });

    //Menambahkan absensi oleh admin
    $('#tambah_absensi').on('click', function() {
        $.ajax({
            url: 'apps/data_absensi/tambah.php',
            method: 'post',
            success: function(data) {
                $('#tampil_data').html(data);
                $('#tampil_data').find('script').each(function() {
                    $.globalEval(this.text || this.textContent || this.innerHTML || '');
                });
                document.getElementById("judul").innerHTML = 'Tambah Absensi';
                $('#modal').modal('show');
            }
        });
    });
    $(document).on('click', '#simpan_absensi', function(e) {
        e.stopPropagation();
        e.preventDefault();
        if (this.disabled) {
            return false;
        }
        var mulaiAbsen = '07:00:00';
        var akhirAbsen = '07:59:59';
        var now = new Date();
        var hh = now.getHours().toString().padStart(2, '0');
        var mm = now.getMinutes().toString().padStart(2, '0');
        var ss = now.getSeconds().toString().padStart(2, '0');
        var nowS = (parseInt(hh, 10) * 3600) + (parseInt(mm, 10) * 60) + parseInt(ss, 10);
        var mulaiS = (parseInt(mulaiAbsen.split(':')[0], 10) * 3600) + (parseInt(mulaiAbsen.split(':')[1], 10) * 60);
        var akhirS = (parseInt(akhirAbsen.split(':')[0], 10) * 3600) + (parseInt(akhirAbsen.split(':')[1], 10) * 60) + (parseInt(akhirAbsen.split(':')[2] || '0', 10));
        if (nowS < mulaiS || nowS > akhirS) {
            alert('Absen hanya bisa dilakukan antara ' + mulaiAbsen + ' dan ' + akhirAbsen + '.');
            return false;
        }
        if (this.form) {
            this.form.submit();
        }
        return false;
    });
</script>

<script>
    //Mengubah absensi oleh admin
    $('.absensi').on('click', function() {
        var id_siswa = $(this).attr("id_siswa");
        var id_absensi = $(this).attr("id_absensi");
        $.ajax({
            url: 'apps/data_absensi/absensi.php',
            method: 'POST',
            data: {
                id_siswa: id_siswa,
                id_absensi: id_absensi
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Mulai Absensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    //Cetak Absensi
    $('.cetak').on('click', function() {
        var id_siswa = $(this).attr("id_siswa");
        $.ajax({
            url: 'apps/data_absensi/cetak.php',
            method: 'POST',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Cetak Absensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>