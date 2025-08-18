<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda"><em class="fa fa-home"></em></a></li>
        <li class="active">Data Siswa</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Data Siswa
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="siswa" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="cari" id="cari" class="form-control" value="" placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tambahan filter & sorting -->
                    <div class="col-sm-3">
                        <div class="form-group">
                            <select id="filterPerusahaan" class="form-control">
                                <option value="all">Semua Perusahaan</option>
                                <?php
                                // ambil nama perusahaan unik
                                $qPerusahaan = mysqli_query($kon, "SELECT DISTINCT perusahaan FROM tbl_siswa ORDER BY perusahaan ASC");
                                while ($row = mysqli_fetch_assoc($qPerusahaan)) {
                                    if (!empty($row['perusahaan'])) {
                                        echo "<option value='" . htmlspecialchars(strtolower($row['perusahaan'])) . "'>" . htmlspecialchars($row['perusahaan']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <select id="sortPerusahaan" class="form-control">
                                <option value="nama_az">Nama A → Z</option>
                                <option value="nama_za">Nama Z → A</option>
                                <option value="nis_asc">NIS Kecil → Besar</option>
                                <option value="nis_desc">NIS Besar → Kecil</option>
                                <option value="perusahaan_az">Perusahaan A → Z</option>
                                <option value="perusahaan_za">Perusahaan Z → A</option>
                            </select>
                        </div>
                    </div>
                    <!-- end tambahan -->
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
                // Notifikasi CRUD
                if (isset($_GET['add'])) {
                    echo $_GET['add'] == 'berhasil'
                        ? "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Disimpan</div>"
                        : "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Disimpan</div>";
                }
                if (isset($_GET['edit'])) {
                    echo $_GET['edit'] == 'berhasil'
                        ? "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Diupdate</div>"
                        : "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Diupdate</div>";
                }
                if (isset($_GET['pengguna'])) {
                    echo $_GET['pengguna'] == 'berhasil'
                        ? "<div class='alert alert-success'><strong>Berhasil!</strong> Setting Data siswa Berhasil</div>"
                        : "<div class='alert alert-danger'><strong>Gagal!</strong> Setting Data siswa Gagal</div>";
                }
                if (isset($_GET['hapus'])) {
                    echo $_GET['hapus'] == 'berhasil'
                        ? "<div class='alert alert-success'><strong>Berhasil!</strong> Data siswa Telah Dihapus</div>"
                        : "<div class='alert alert-danger'><strong>Gagal!</strong> Data siswa Gagal Dihapus</div>";
                }
                ?>
                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah"><i class="fa fa-plus"></i> Tambah</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>NIS</th>
                                <th>Mulai PKL</th>
                                <th>Akhir PKL</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            include 'config/database.php';
                            if (isset($_GET['cari']) and $_GET['cari'] != "") {
                                $cari = trim($_GET["cari"]);
                                $sql = "SELECT * FROM tbl_siswa WHERE 
                                nama LIKE '%$cari%' OR 
                                nis LIKE '%$cari%' OR 
                                perusahaan LIKE '%$cari%' 
                                OR jurusan LIKE '%$cari%';";
                            } else {
                                $sql = "SELECT * FROM tbl_siswa";
                            }
                            $hasil = mysqli_query($kon, $sql);
                            while ($data = mysqli_fetch_array($hasil)):
                            ?>
                                <tr>
                                    <td></td> <!-- nomor urut by JS -->
                                    <td><?= htmlspecialchars($data['nama']); ?></td>
                                    <td><?= htmlspecialchars($data['perusahaan']); ?></td>
                                    <td><?= htmlspecialchars($data['nis']); ?></td>
                                    <td><?= date('d-m-Y', strtotime($data["mulai_pkl"])); ?></td>
                                    <td><?= date('d-m-Y', strtotime($data["akhir_pkl"])); ?></td>
                                    <td><img src="apps/siswa/foto/<?= $data["foto"]; ?>" width="120"></td>
                                    <td>
                                        <button id_siswa="<?= $data['id_siswa']; ?>" class="tombol_detail btn btn-success btn-circle"><i class="fa fa-mouse-pointer"></i></button>
                                        <button kode_siswa="<?= $data['kode_siswa']; ?>" class="tombol_setting btn btn-primary btn-circle"><i class="fa fa-user"></i></button>
                                        <button id_siswa="<?= $data['id_siswa']; ?>" class="tombol_edit btn btn-warning btn-circle"><i class="fa fa-edit"></i></button>
                                        <a href="apps/siswa/hapus.php?id_siswa=<?= $data['id_siswa']; ?>&kode_siswa=<?= $data['kode_siswa']; ?>"
                                            class="btn-hapus-siswa btn btn-danger btn-circle">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data siswa ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a href="#" id="btn-confirm-hapus" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="tampil_data"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Tambah
    $(document).on('click', '#tombol_tambah', function() {
        $.ajax({
            url: 'apps/siswa/tambah.php',
            method: 'post',
            success: function(data) {
                $('#tampil_data').html(data);
                $('#judul').text('Tambah Siswa');
                $('#modal').modal('show');
            }
        });
    });

    // Detail
    $(document).on('click', '.tombol_detail', function() {
        var id_siswa = $(this).attr("id_siswa");
        $.ajax({
            url: 'apps/siswa/detail.php',
            method: 'post',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                $('#judul').text('Detail Siswa');
                $('#modal').modal('show');
            }
        });
    });

    // Edit
    $(document).on('click', '.tombol_edit', function() {
        var id_siswa = $(this).attr("id_siswa");
        $.ajax({
            url: 'apps/siswa/edit.php',
            method: 'post',
            data: {
                id_siswa: id_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                $('#judul').text('Edit Siswa');
                $('#modal').modal('show');
            }
        });
    });

    // Setting
    $(document).on('click', '.tombol_setting', function() {
        var kode_siswa = $(this).attr("kode_siswa");
        $.ajax({
            url: 'apps/siswa/pengguna.php',
            method: 'post',
            data: {
                kode_siswa: kode_siswa
            },
            success: function(data) {
                $('#tampil_data').html(data);
                $('#judul').text('Setting Siswa');
                $('#modal').modal('show');
            }
        });
    });
</script>

<script>
    // --- FILTER & SORT + NOMOR URUT ---
    function updateTable() {
        let filter = document.getElementById("filterPerusahaan").value.toLowerCase();
        let order = document.getElementById("sortPerusahaan").value;
        let tbody = document.querySelector("#dataTable tbody");
        let rows = Array.from(tbody.querySelectorAll("tr"));

        // filter perusahaan
        rows.forEach(row => {
            let perusahaan = row.cells[2].textContent.toLowerCase();
            row.style.display = (filter === "all" || perusahaan === filter) ? "" : "none";
        });

        let visibleRows = rows.filter(r => r.style.display !== "none");

        // sorting
        visibleRows.sort(function(a, b) {
            let namaA = a.cells[1].textContent.toLowerCase();
            let namaB = b.cells[1].textContent.toLowerCase();
            let perusahaanA = a.cells[2].textContent.toLowerCase();
            let perusahaanB = b.cells[2].textContent.toLowerCase();
            let nisA = parseInt(a.cells[3].textContent) || 0;
            let nisB = parseInt(b.cells[3].textContent) || 0;

            switch (order) {
                case "nama_az":
                    return namaA.localeCompare(namaB);
                case "nama_za":
                    return namaB.localeCompare(namaA);
                case "nis_asc":
                    return nisA - nisB;
                case "nis_desc":
                    return nisB - nisA;
                case "perusahaan_az":
                    return perusahaanA.localeCompare(perusahaanB);
                case "perusahaan_za":
                    return perusahaanB.localeCompare(perusahaanA);
            }
        });

        // re-append + nomor urut
        visibleRows.forEach((row, i) => {
            row.cells[0].textContent = i + 1;
            tbody.appendChild(row);
        });
    }

    document.getElementById("filterPerusahaan").addEventListener("change", updateTable);
    document.getElementById("sortPerusahaan").addEventListener("change", updateTable);

    // initial numbering
    updateTable();
</script>

<script>
    $(document).on('click', '.btn-hapus-siswa', function(e) {
        e.preventDefault();
        var link = $(this).attr("href");
        $("#btn-confirm-hapus").attr("href", link); // set link ke tombol hapus di modal
        $("#modalHapus").modal('show'); // tampilkan modal
    });
</script>