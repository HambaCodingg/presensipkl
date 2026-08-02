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