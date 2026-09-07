<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (
    $_SESSION["level"] != 'Admin' &&
    $_SESSION["level"] != 'admin' &&
    $_SESSION["level"] != 'Siswa'
) {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}

include 'config/database.php';

// Ambil profil aplikasi
$query = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$row = mysqli_fetch_array($query);

// Statistik presensi PKL untuk siswa yang masih berada dalam periode PKL.
$statistik_query = mysqli_query($kon, "
    SELECT
        COUNT(s.id_siswa) AS total_siswa,
        COALESCE(SUM(CASE WHEN a.status = 1 THEN 1 ELSE 0 END), 0) AS hadir,
        COALESCE(SUM(CASE WHEN a.status = 2 THEN 1 ELSE 0 END), 0) AS izin,
        COALESCE(SUM(CASE WHEN a.status = 3 THEN 1 ELSE 0 END), 0) AS tidak_hadir,
        COALESCE(SUM(CASE WHEN a.id_absensi IS NULL THEN 1 ELSE 0 END), 0) AS belum_absen
    FROM tbl_siswa s
    LEFT JOIN tbl_absensi a
        ON a.id_siswa = s.id_siswa
        AND a.tanggal = CURDATE()
    WHERE s.mulai_pkl <= CURDATE()
        AND s.akhir_pkl >= CURDATE()
");
$statistik = mysqli_fetch_assoc($statistik_query) ?: [
    'total_siswa' => 0,
    'hadir' => 0,
    'izin' => 0,
    'tidak_hadir' => 0,
    'belum_absen' => 0
];

$awal_query = mysqli_query($kon, "
    SELECT s.nama, s.perusahaan, a.waktu
    FROM tbl_absensi a
    INNER JOIN tbl_siswa s ON s.id_siswa = a.id_siswa
    WHERE a.tanggal = CURDATE()
        AND a.status = 1
        AND a.waktu IS NOT NULL
        AND s.mulai_pkl <= CURDATE()
        AND s.akhir_pkl >= CURDATE()
    ORDER BY a.waktu ASC, s.nama ASC
    LIMIT 5
");
?>

<div class="container-fluid px-3">

    <!-- BREADCRUMB -->
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li>
                <a href="index.php?page=beranda">
                    <em class="fa fa-home"></em> Beranda
                </a>
            </li>
            <li class="active"></li>
        </ol>
    </div>

    <!-- PANEL UTAMA -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default shadow-sm rounded">
                <div class="panel-body">

                    <!-- Salam -->
                    <h4 class="fw-bold mb-3">
                        Selamat Datang,
                        <span class="text-primary">
                            <?php
                            if ($_SESSION['level'] == 'Admin' || $_SESSION['level'] == 'admin') {
                                echo isset($_SESSION["nama_admin"]) ? $_SESSION["nama_admin"] : '';
                            } else {
                                echo isset($_SESSION["nama_siswa"]) ? $_SESSION["nama_siswa"] : '';
                            }
                            ?>
                        </span> 👋
                    </h4>

                    <!-- Info Sistem Informasi -->
                    <p>
                        Selamat Datang di <strong>Sistem Informasi Absensi dan Kegiatan Harian Siswa</strong> berbasis Web.
                        Sistem ini digunakan untuk mencatat kehadiran dan aktivitas harian siswa selama menjalani PKL di
                        Dunia Usaha / Dunia Industri (DU/DI) mitra <strong><?php echo $row['nama_instansi']; ?></strong>.
                        Gunakan dengan tertib dan sesuai prosedur.
                    </p>

                    <!-- STATISTIK PRESENSI HARI INI -->
                    <div class="dashboard-section">
                        <div class="dashboard-section-heading">
                            <div>
                                <h5><i class="fa fa-bar-chart"></i> Statistik Siswa</h5>
                                <small>Presensi PKL hari ini, <?php echo date('d/m/Y'); ?></small>
                            </div>
                        </div>
                        <div class="row dashboard-stats">
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-card stat-total">
                                    <span class="stat-icon"><i class="fa fa-users"></i></span>
                                    <span class="stat-label">Siswa Aktif</span>
                                    <strong><?php echo (int) $statistik['total_siswa']; ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-card stat-present">
                                    <span class="stat-icon"><i class="fa fa-check"></i></span>
                                    <span class="stat-label">Hadir</span>
                                    <strong><?php echo (int) $statistik['hadir']; ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-card stat-permission">
                                    <span class="stat-icon"><i class="fa fa-file-text-o"></i></span>
                                    <span class="stat-label">Izin</span>
                                    <strong><?php echo (int) $statistik['izin']; ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-card stat-pending">
                                    <span class="stat-icon"><i class="fa fa-clock-o"></i></span>
                                    <span class="stat-label">Belum Absen</span>
                                    <strong><?php echo (int) $statistik['belum_absen']; ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-card stat-absent">
                                    <span class="stat-icon"><i class="fa fa-times"></i></span>
                                    <span class="stat-label">Tidak Hadir</span>
                                    <strong><?php echo (int) $statistik['tidak_hadir']; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="early-arrivals">
                            <div class="early-arrivals-heading">
                                <h5><i class="fa fa-trophy"></i> Datang Paling Awal</h5>
                                <span><?php echo (int) $statistik['hadir']; ?> siswa hadir</span>
                            </div>
                            <?php if ($awal_query && mysqli_num_rows($awal_query) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover early-arrivals-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Siswa</th>
                                                <th>Perusahaan</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $nomor_awal = 0; ?>
                                            <?php while ($awal = mysqli_fetch_assoc($awal_query)): ?>
                                                <?php $nomor_awal++; ?>
                                                <tr>
                                                    <td><span class="arrival-rank"><?php echo $nomor_awal; ?></span></td>
                                                    <td><?php echo htmlspecialchars($awal['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($awal['perusahaan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><strong><?php echo htmlspecialchars($awal['waktu'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-arrivals"><i class="fa fa-info-circle"></i> Belum ada siswa yang melakukan presensi hari ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>


                    <!-- Info PKL -->
                    <div class="alert alert-info d-flex align-items-center py-3 px-4 mb-4 rounded-3" role="alert" style="background: linear-gradient(135deg, rgba(56, 189, 248, .16), rgba(139, 92, 246, .14)); color: #111827; border: 1px solid rgba(139, 92, 246, .16);">
                        <i class="fa fa-bullhorn me-2" style="margin-right:10px;"></i>
                        <strong>Info PKL:</strong>&nbsp; Siswa SMK TI BAZMA sedang melaksanakan Praktik Kerja Lapangan di berbagai DU/DI mitra. Tetap semangat dan jaga profesionalisme!🧑‍💼🔥
                    </div>

                    <!-- Adab Saat PKL -->
                    <div class="info-box">
                        <p>🙏 Adab Saat PKL:</p>
                        <ul>
                            <li class="mb-2">Datang tepat waktu sesuai aturan perusahaan.</li>
                            <li class="mb-2">Gunakan pakaian rapi & sopan sesuai ketentuan.</li>
                            <li class="mb-2">Sopan santun terhadap pembimbing, karyawan, dan teman.</li>
                            <li class="mb-2">Jaga nama baik sekolah dan perusahaan mitra.</li>
                        </ul>
                    </div>

                    <!-- Panduan PKL -->
                    <div class="info-box">
                        <p>📘 Panduan PKL untuk Siswa:</p>
                        <ul>
                            <li class="mb-2">Lakukan absensi harian melalui sistem ini sebelum memulai kegiatan.</li>
                            <li class="mb-2">Catat kegiatan harian secara ringkas dan jelas di menu <strong>Kegiatan</strong>.</li>
                            <li class="mb-2">Ikuti aturan & SOP perusahaan mitra tempat PKL.</li>
                            <li class="mb-2">Jika ada kendala teknis sistem, segera laporkan ke pembimbing atau admin.</li>
                            <li class="mb-2">Setiap akhir minggu, lakukan rekap kegiatan dan konfirmasi ke pembimbing.</li>
                        </ul>
                        <div class="mt-3">
                            <a href="assets/panduan_pkl.pdf" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fa fa-download"></i> Download Panduan Lengkap
                            </a>
                        </div>
                    </div>


                    <!-- PANEL FOTO PERUSAHAAN PKL -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading"><i class="fa fa-building"></i> Dokumentasi Perusahaan PKL Mitra</div>
                                <div class="panel-subheading">
                                    SMK TI BAZMA
                                </div>
                                <div class="panel-body text-center">
                                    <div class="running-gallery-wrapper">
                                        <div class="running-gallery">
                                            <?php
                                            $perusahaan = [
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785657669/images_2_yps6fa.png", "nama" => "PT. Patra Jasa"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1784534525/ss-shared-services-logorz_znomwq.png", "nama" => "Pertamina Shared Services"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658137/logo-dark.ed10142_zyabzs.png", "nama" => "PT. Asuransi Tugu Pratama Indonesia"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785657792/pertamina-logo_otuq3r.png", "nama" => "PT. Pertamina Geothermal Energy"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658216/logo_1_sl3qe5.png", "nama" => "PT. Pertamina Nusantara Regas"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785657857/PT_Pertamina_Patra_Niaga.svg_idpzx5.webp", "nama" => "PT. Pertamina Patra Niaga"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658301/download_zrircc.svg", "nama" => "PT. Pertamina EP"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658354/LOGO-PNRE-2-Well-Vira-Dela-1024x724_h4thqd.png", "nama" => "PT. Pertamina Power Indonesia"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785657898/logo_ptc_2023_FC_rntell.png", "nama" => "PT. Pertamina Training Consulting"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785657965/logo-pertalife_uzhwwz.svg", "nama" => "PT. Pertalife Insurance"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658028/yakes-menyamping-scaled_haakwt.webp", "nama" => "PT. Yayasan Kesehatan Pertamina"],
                                                ["logo" => "https://res.cloudinary.com/dnzhewrrx/image/upload/v1785658496/logo-14052026_tdzvsz.png", "nama" => "PT. ASNET"],
                                            ];

                                            foreach ($perusahaan as $p):
                                            ?>
                                                <div class="perusahaan-item">
                                                    <img src="<?php echo $p['logo']; ?>" alt="<?php echo $p['nama']; ?>" class="img-thumbnail">
                                                    <div class="caption"><?php echo $p['nama']; ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- STYLE -->
                <style>
                    .info-box {
                        background: #ffffff;
                        padding: 1.75rem;
                        border-radius: 24px;
                        box-shadow: 0 24px 64px rgba(15, 23, 42, .08);
                        border-left: 6px solid rgba(139, 92, 246, .55);
                        margin-top: 1.75rem;
                        margin-bottom: 1.75rem;
                    }

                    .info-box p {
                        font-size: 1rem;
                        font-weight: 700;
                        color: #4338ca;
                        margin: 0 0 1rem;
                    }

                    .info-box ul {
                        font-size: .95rem;
                        color: #475569;
                        margin-top: .75rem;
                        padding-left: 1.25rem;
                    }

                    .panel {
                        border: 1px solid rgba(139, 92, 246, .14);
                        border-radius: 28px;
                        box-shadow: 0 26px 76px rgba(15, 23, 42, .08);
                        overflow: hidden;
                    }

                    .panel-heading {
                        background: linear-gradient(135deg, rgba(139, 92, 246, .18), rgba(56, 189, 248, .14));
                        color: #111827;
                        padding: 1.5rem 1.35rem;
                        font-weight: 700;
                        font-size: 1.05rem;
                        text-align: center;
                    }

                    .panel-subheading {
                        text-align: center;
                        font-size: 1rem;
                        font-weight: 700;
                        color: #475569;
                        margin: 1rem 0 0.75rem;
                        border-bottom: 1px solid rgba(226, 232, 240, .95);
                        padding-bottom: 0.75rem;
                    }

                    .panel-body {
                        background: #ffffff;
                        padding: 2rem;
                        color: #475569;
                    }

                    .running-gallery-wrapper {
                        overflow: hidden;
                        position: relative;
                        width: 100%;
                        border-radius: 24px;
                        padding: 1rem 0;
                        background: rgba(236, 245, 255, .85);
                    }

                    .running-gallery {
                        display: flex;
                        animation: scrollGallery 35s linear infinite;
                    }

                    .perusahaan-item {
                        flex: 0 0 auto;
                        width: 200px;
                        margin: 0 18px;
                        text-align: center;
                    }

                    .perusahaan-item img {
                        width: 100%;
                        height: 140px;
                        object-fit: contain;
                        border-radius: 18px;
                        box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
                        transition: transform .35s ease;
                        background: #ffffff;
                        padding: 12px;
                    }

                    .perusahaan-item img:hover {
                        transform: translateY(-4px);
                    }

                    .perusahaan-item .caption {
                        margin-top: 12px;
                        font-size: .95rem;
                        color: #334155;
                        font-weight: 600;
                    }

                    .text-primary {
                        color: #4338ca !important;
                    }

                    .dashboard-section {
                        margin: 2rem 0;
                        padding: 1.25rem;
                        border: 1px solid rgba(56, 189, 248, .18);
                        border-radius: 18px;
                        background: linear-gradient(135deg, #f8fbff, #ffffff);
                    }

                    .dashboard-section-heading,
                    .early-arrivals-heading {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 1rem;
                    }

                    .dashboard-section-heading h5,
                    .early-arrivals-heading h5 {
                        margin: 0;
                        color: #1e293b;
                        font-weight: 700;
                    }

                    .dashboard-section-heading small,
                    .early-arrivals-heading span {
                        color: #64748b;
                    }

                    .dashboard-stats {
                        display: flex;
                        flex-wrap: wrap;
                        margin-top: 1rem;
                    }

                    .stat-card {
                        min-height: 118px;
                        margin-bottom: 1rem;
                        padding: 1rem;
                        border-radius: 14px;
                        color: #ffffff;
                        box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
                    }

                    .stat-card .stat-icon {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 34px;
                        height: 34px;
                        margin-bottom: .6rem;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, .2);
                    }

                    .stat-card .stat-label,
                    .stat-card strong {
                        display: block;
                    }

                    .stat-card .stat-label {
                        font-size: .85rem;
                        opacity: .9;
                    }

                    .stat-card strong {
                        margin-top: .2rem;
                        font-size: 1.7rem;
                    }

                    .stat-total { background: #2563eb; }
                    .stat-present { background: #059669; }
                    .stat-permission { background: #d97706; }
                    .stat-pending { background: #64748b; }
                    .stat-absent { background: #dc2626; }

                    .early-arrivals {
                        margin-top: .75rem;
                        padding: 1rem;
                        border-top: 1px solid #e2e8f0;
                    }

                    .early-arrivals-table {
                        margin: .75rem 0 0;
                        background: #ffffff;
                    }

                    .early-arrivals-table th {
                        color: #64748b;
                        font-size: .8rem;
                        text-transform: uppercase;
                    }

                    .arrival-rank {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 26px;
                        height: 26px;
                        border-radius: 50%;
                        background: #e0f2fe;
                        color: #0369a1;
                        font-weight: 700;
                    }

                    .empty-arrivals {
                        padding: 1rem 0 .25rem;
                        color: #64748b;
                    }

                    .me-2 {
                        margin-right: 0.5rem;
                    }

                    @keyframes scrollGallery {
                        0% {
                            transform: translateX(0%);
                        }

                        100% {
                            transform: translateX(-100%);
                        }
                    }

                    .panel-heading,
                    .panel-subheading {
                        white-space: normal;
                        word-wrap: break-word;
                    }

                    @media screen and (max-width: 768px) {
                        h4.fw-bold {
                            font-size: 1.35rem;
                            text-align: center;
                        }

                        .panel-body p,
                        .alert,
                        .bg-white ul li {
                            font-size: .95rem;
                            text-align: justify;
                        }

                        .alert {
                            flex-direction: column;
                            align-items: flex-start;
                        }

                        .alert i {
                            margin-bottom: .5rem;
                        }

                        .perusahaan-item {
                            width: 130px;
                            margin: 0 8px;
                        }

                        .perusahaan-item img {
                            height: 88px;
                        }

                        .perusahaan-item .caption {
                            font-size: .85rem;
                        }

                        .panel-heading {
                            font-size: .95rem;
                            padding: 1rem;
                        }

                        .panel-subheading {
                            font-size: .95rem;
                        }

                        .panel-body {
                            padding: 1rem;
                        }

                        .dashboard-section {
                            padding: .85rem;
                        }

                        .dashboard-section-heading,
                        .early-arrivals-heading {
                            align-items: flex-start;
                            flex-direction: column;
                        }

                        .breadcrumb {
                            font-size: .9rem;
                        }

                        .running-gallery {
                            animation-duration: 55s;
                        }
                    }

                    @media screen and (max-width: 400px) {
                        .panel-heading {
                            font-size: .85rem;
                            padding: .75rem;
                            line-height: 1.2;
                        }

                        .panel-subheading {
                            font-size: .8rem;
                            line-height: 1.2;
                            margin-bottom: .5rem;
                        }

                        .panel-body {
                            font-size: .9rem;
                            padding: .75rem;
                        }

                        .breadcrumb {
                            font-size: .8rem;
                        }
                    }
                </style>

                <!-- FOOTER -->
                <div class="text-center mt-4 mb-3" style="font-size: 13px; color: #666; font-weight: normal;">
                    Developed by MDF X NYF | SMK TI BAZMA
                </div>