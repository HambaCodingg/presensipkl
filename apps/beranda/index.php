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
                    <h4 class="fw-bold mb-3" style="color: #333;">
                        Selamat Datang,
                        <span style="color:#005BAA">
                            <?php
                            if ($_SESSION['level'] == 'Admin' || $_SESSION['level'] == 'admin') {
                                echo isset($_SESSION["nama_admin"]) ? $_SESSION["nama_admin"] : '';
                            } else {
                                echo isset($_SESSION["nama_siswa"]) ? $_SESSION["nama_siswa"] : '';
                            }
                            ?>
                        </span> 👋
                    </h4>

                    <!-- Info Aplikasi -->
                    <p>
                        Selamat Datang di Aplikasi Absensi dan Kegiatan Harian siswa berbasis Web.
                        Sistem ini digunakan untuk mencatat kehadiran dan aktivitas harian siswa selama menjalani PKL di
                        Dunia Usaha / Dunia Industri (DU/DI) mitra <strong><?php echo $row['nama_instansi']; ?></strong>.
                        Gunakan dengan tertib dan sesuai prosedur. Jika mengalami kendala, segera hubungi admin atau pembimbing masing-masing.
                    </p>

                    <!-- Info PKL -->
                    <div class="alert alert-info d-flex align-items-center py-3 px-4 mb-4" role="alert" style="background-color: #009FE3; color: white; border-radius: 8px;">
                        <i class="fa fa-bullhorn me-2" style="margin-right:10px;"></i>
                        <strong>Info PKL:</strong>&nbsp; Siswa SMK TI BAZMA sedang melaksanakan Praktik Kerja Lapangan di berbagai DU/DI mitra. Tetap semangat dan jaga profesionalisme!🧑‍💼🔥
                    </div>

                    <!-- Adab Saat PKL -->
                    <div class="bg-white p-4 rounded shadow-sm border-start border-4 border-warning mt-4">
                        <p class="mb-3 fw-semibold" style="font-size: 16px; color: #FF8C00;">
                            ✨ <span style="font-weight: 600;">Adab Saat PKL:</span>
                        </p>
                        <ul class="ps-3" style="font-size: 15px; color: #444;">
                            <li class="mb-2">Datang tepat waktu & berpakaian rapi.</li>
                            <li class="mb-2">Gunakan bahasa yang sopan saat berkomunikasi.</li>
                            <li class="mb-2">Jaga nama baik sekolah dan pribadi.</li>
                            <li class="mb-2">Jangan lupa absen setiap hari di sistem ini.</li>
                        </ul>
                    </div>

                </div>
            </div>
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
                                ["logo" => "https://smktibazma.com/partner/partner-7.webp", "nama" => "PT. El Nusa Petrofin"],
                                ["logo" => "https://best.smktibazma.com/src/assets/pgn.png", "nama" => "PT. PGN Com"],
                                ["logo" => "https://best.smktibazma.com/src/assets/pertamina.png", "nama" => "PT. Pertamina"],
                                ["logo" => "https://best.smktibazma.com/src/assets/pertaminageo.png", "nama" => "PT. Pertamina Geothermal Energy"],
                                ["logo" => "https://smktibazma.com/partner/partner-5.webp", "nama" => "PT. Pertamina Hulu Rokan"],
                                ["logo" => "https://smktibazma.com/partner/partner-6.webp", "nama" => "PT. Pertamina Patra Niaga"],
                                ["logo" => "https://smktibazma.com/partner/partner-4.webp", "nama" => "PT. Pertamina Retail"],
                                ["logo" => "https://smktibazma.com/partner/partner-1.webp", "nama" => "PT. Pertamina SVP SS"],
                                ["logo" => "https://smktibazma.com/partner/partner-3.webp", "nama" => "PT. Pertamina Training Consulting"],
                                ["logo" => "https://smktibazma.com/partner/partner-2.webp", "nama" => "PT. Perta Life Insurance"],
                                ["logo" => "https://smktibazma.com/partner/partner-9.webp", "nama" => "PT. Yayasan Kesehatan Pertamina"]
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
    .fw-bold {
        font-weight: 700;
    }

    .fw-semibold {
        font-weight: 600;
    }

    .me-2 {
        margin-right: 0.5rem;
    }

    .border-start {
        border-left-width: 4px !important;
        border-left-style: solid !important;
    }

    .panel {
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 114, 206, 0.15);
    }

    .panel-heading {
        background: linear-gradient(90deg, #005BAA, #009FE3, #00BCD4);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 18px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        text-align: center;
    }

    .panel-subheading {
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        color: #003a75;
        margin: 4px 0 10px;
        /* lebih rapat */
        border-bottom: 1px solid #e6f0fa;
        /* tipis biar elegan */
        padding-bottom: 4px;
    }


    .panel-body {
        background-color: #ffffff;
        padding: 25px;
        font-size: 15px;
        color: #333;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .running-gallery-wrapper {
        overflow: hidden;
        position: relative;
        width: 100%;
        border-radius: 12px;
        padding: 10px 0;
        background: #f3f9ff;
    }

    .running-gallery {
        display: flex;
        animation: scrollGallery 40s linear infinite;
    }

    .perusahaan-item {
        flex: 0 0 auto;
        width: 200px;
        margin: 0 20px;
        text-align: center;
    }

    .perusahaan-item img {
        width: 100%;
        height: 140px;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 114, 206, 0.2);
        transition: transform 0.4s ease;
        background: white;
        padding: 10px;
    }

    .perusahaan-item img:hover {
        transform: scale(1.05);
    }

    .perusahaan-item .caption {
        margin-top: 10px;
        font-size: 14px;
        color: #005BAA;
        font-weight: normal;
    }

    @keyframes scrollGallery {
        0% {
            transform: translateX(0%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    /* RESPONSIVE SECTION */
    @media screen and (max-width: 768px) {
        h4.fw-bold {
            font-size: 20px;
            text-align: center;
        }

        .panel-body p,
        .alert,
        .bg-white ul li {
            font-size: 14px;
            text-align: justify;
        }

        .alert {
            flex-direction: column;
            align-items: flex-start;
        }

        .alert i {
            margin-bottom: 5px;
        }

        .bg-white {
            padding: 15px;
        }

        .perusahaan-item {
            width: 120px;
            margin: 0 8px;
        }

        .perusahaan-item img {
            height: 80px;
        }

        .perusahaan-item .caption {
            font-size: 12px;
        }

        .panel-heading {
            font-size: 16px;
            text-align: center;
        }

        .panel-body {
            padding: 15px;
        }

        .breadcrumb {
            font-size: 14px;
        }

        .running-gallery {
            animation-duration: 60s;
        }
    }
</style>

<!-- FOOTER -->
<div class="text-center mt-4 mb-3" style="font-size: 13px; color: #666; font-weight: normal;">
    Developed by MDF | SMK TI BAZMA
</div>