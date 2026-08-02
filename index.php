<?php
session_start();

// ========================= Guard: Login check =========================
if (empty($_SESSION["kode_pengguna"])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// ========================= Session & user validation =========================
$kode_pengguna = $_SESSION["kode_pengguna"];
$username      = $_SESSION["username"] ?? '';

// Validasi username dari database
$stmt = $kon->prepare("SELECT username FROM tbl_user WHERE kode_pengguna = ?");
$stmt->bind_param("s", $kode_pengguna);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$username_db = $data['username'] ?? '';

if ($username !== $username_db) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// ========================= Site data (logo, etc) =========================
$site = $kon->query("SELECT * FROM tbl_site LIMIT 1")->fetch_assoc();
$logo          = $site['logo'] ?? 'logo.png';

// ========================= Whitelist routing =========================
$allowed_pages = [
    'beranda'       => "apps/beranda/index.php",
    'admin'         => "apps/admin/index.php",
    'siswa'         => "apps/siswa/index.php",
    'data_absensi'  => "apps/data_absensi/index.php",
    'data_asrama'   => "apps/data_asrama/index.php",
    'data_kegiatan' => "apps/data_kegiatan/index.php",
    'pengaturan'    => "apps/pengaturan/index.php",
    'absen'         => "apps/pengguna/absen.php",
    'absen_asrama'  => "apps/pengguna/absen_asrama.php",
    'riwayat'       => "apps/data_absensi/riwayat.php",
    'kegiatan'      => "apps/data_kegiatan/kegiatan.php",
    'profil'        => "apps/pengguna/profil.php"
];

// ========================= Helper for active state =========================
$current = $_GET['page'] ?? 'beranda';
function is_active($key, $current)
{
    return $key === $current ? 'active' : '';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo htmlspecialchars($logo); ?>" />
    <title>SMART PKL | Sistem Manajemen Report Tugas PKL</title>

    <!-- ============ CSS ============ -->
    <link href="template/css/bootstrap.min.css" rel="stylesheet" />
    <link href="template/css/font-awesome.min.css" rel="stylesheet" />
    <link href="template/css/datepicker3.css" rel="stylesheet" />
    <link href="template/css/styles.css" rel="stylesheet" />
    <link href="source/font/font.css" rel="stylesheet" />

    <!-- ============ jQuery (must be before Bootstrap) ============ -->
    <script src="template/js/jquery-2.2.3.min.js"></script>

    <style>
        /* ----------------------------------------------
           RESET / UTILITY
        ---------------------------------------------- */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: #f7f9fc;
            color: #222;
        }

        .hide {
            display: none !important;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 1px, 1px);
            border: 0;
        }

        /* ----------------------------------------------
           PRELOADER
        ---------------------------------------------- */
        .se-pre-con {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #fff;
        }

        /* ----------------------------------------------
           NAVBAR (Brand fixed as requested)
        ---------------------------------------------- */
        .navbar-custom {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: linear-gradient(90deg, #e10600, #004aad);
            box-shadow: 0 3px 8px rgba(0, 0, 0, .18);
            z-index: 1030;
            /* below modal */
        }

        .navbar-custom .brand-title {
            color: #fff;
            font-weight: 800;
            line-height: 1.2;
            margin: 0;
            font-size: clamp(16px, 2.2vw, 22px);
            letter-spacing: .2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Hamburger (exactly 3 bars). Hidden on desktop, visible on mobile */
        #sidebarToggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 36px;
            border: 0;
            background: transparent;
            color: #fff;
            cursor: pointer;
        }

        #sidebarToggle .bar {
            display: block;
            width: 24px;
            height: 2px;
            background: #fff;
            margin: 4px 0;
            transition: .25s;
        }

        /* Show only on mobile/tablet */
        @media (min-width: 992px) {
            #sidebarToggle {
                display: none;
            }
        }

        /* ----------------------------------------------
           LAYOUT
        ---------------------------------------------- */
        .layout {
            display: block;
            min-height: 100%;
            padding-top: 64px;
            /* navbar height */
        }

        @media (max-width: 575.98px) {
            .layout {
                padding-top: 56px;
            }
        }

        /* Sidebar base */
        #sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #003a75;
            color: #fff;
            border-right: 3px solid #e10600;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 575.98px) {
            #sidebar {
                top: 56px;
            }
        }

        /* Sidebar content */
        #sidebar .profile {
            padding: 16px 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
        }

        #sidebar .profile img {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e10600;
        }

        #sidebar .profile .name {
            margin-top: 10px;
            font-weight: 700;
            color: #fff;
            font-size: 1.05rem;
        }

        #sidebar .profile .role {
            color: #c7c7c7;
            font-size: .85rem;
        }

        #sidebar .divider {
            height: 2px;
            margin: 14px 0;
            background: #e10600;
        }

        #sidebar ul.menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #sidebar ul.menu li {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        #sidebar ul.menu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #eaeaea;
            text-decoration: none;
            padding: 12px 18px;
            font-weight: 600;
        }

        #sidebar ul.menu li a:hover,
        #sidebar ul.menu li.active>a {
            background: #e10600;
            color: #fff;
        }

        #sidebar ul.menu li a em.fa {
            width: 20px;
            text-align: center;
        }

        /* Desktop: sidebar is always visible, main content shifted right */
        .main {
            padding: 16px;
            min-height: calc(100vh - 64px);
        }

        @media (min-width: 992px) {
            .main {
                margin-left: 250px;
            }
        }

        /* Mobile/Tablet: sidebar becomes offcanvas from left (hidden by default) */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform .28s ease;
                width: 260px;
                top: 56px;
                z-index: 1052;
                /* above overlay */
            }

            #sidebar.open {
                transform: translateX(0);
            }

            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, .45);
                display: none;
                z-index: 1051;
            }

            .overlay.active {
                display: block;
            }

            body.offcanvas-open {
                overflow: hidden;
            }
        }

        /* ----------------------------------------------
           MODAL FIX (always over navbar/offcanvas)
        ---------------------------------------------- */
        .modal-backdrop {
            z-index: 20040 !important;
        }

        .modal {
            z-index: 20050 !important;
        }

        .modal+.modal {
            z-index: 20060 !important;
        }

        .modal+.modal .modal-backdrop {
            z-index: 20055 !important;
        }

        /* ----------------------------------------------
           RESPONSIVE IMAGES / CARDS AREA
           (supaya "Dokumentasi Perusahaan" tidak bentrok)
        ---------------------------------------------- */
        .content-wrapper {
            width: 100%;
            margin: 0 auto;
        }

        .hero-img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .page-title {
            font-weight: 800;
            font-size: clamp(18px, 2.6vw, 26px);
            color: #003a75;
            margin: 8px 0 16px;
            line-height: 1.25;
            word-break: break-word;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .06);
            padding: 16px;
            margin-bottom: 18px;
        }

        /* ----------------------------------------------
           ACCESSIBILITY + FOCUS
        ---------------------------------------------- */
        a:focus,
        button:focus {
            outline: 2px dashed #004aad;
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <div class="se-pre-con" aria-hidden="true"></div>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar-custom" role="navigation" aria-label="Top Navigation">
        <h1 class="brand-title">SMART PKL | Sistem Manajemen Report Tugas PKL</h1>
        <!-- Hamburger (3 bars) — visible only on mobile/tablet -->
        <button id="sidebarToggle" class="btn-hamburger" aria-expanded="false" aria-controls="sidebar" aria-label="Buka menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </nav>

    <div class="layout">
        <!-- ===================== SIDEBAR / OFFCANVAS ===================== -->
        <aside id="sidebar" aria-hidden="true">
            <?php if (strtolower($_SESSION['level']) === 'admin'): ?>
                <div class="profile">
                    <img src="source/img/profile.png" alt="Foto Profil Admin">
                    <div class="name"><?php echo htmlspecialchars(substr($_SESSION['nama_admin'], 0, 24)); ?></div>
                    <div class="role">Administrator</div>
                </div>
            <?php elseif (strtolower($_SESSION['level']) === 'siswa'): ?>
                <div class="profile">
                    <img src="apps/siswa/foto/<?php echo htmlspecialchars($_SESSION['foto']); ?>" alt="Foto Profil Siswa">
                    <div class="name"><?php echo htmlspecialchars(substr($_SESSION['nama_siswa'], 0, 24)); ?></div>
                    <div class="role">Siswa</div>
                </div>
            <?php endif; ?>

            <div class="divider"></div>

            <ul class="menu">
                <li class="<?php echo is_active('beranda', $current); ?>">
                    <a href="?page=beranda"><em class="fa fa-home"></em><span>Beranda</span></a>
                </li>

                <?php if (strtolower($_SESSION['level']) === 'admin'): ?>
                    <li class="<?php echo is_active('siswa', $current); ?>">
                        <a href="?page=siswa"><em class="fa fa-users"></em><span>Data Siswa</span></a>
                    </li>
                    <li class="<?php echo is_active('data_absensi', $current); ?>">
                        <a href="?page=data_absensi"><em class="fa fa-calendar"></em><span>Data Presensi</span></a>
                    </li>
                    <li class="<?php echo is_active('data_asrama', $current); ?>">
                        <a href="?page=data_asrama"><em class="fa fa-bed"></em><span>Data Presensi Asrama</span></a>
                    </li>
                    <li class="<?php echo is_active('data_kegiatan', $current); ?>">
                        <a href="?page=data_kegiatan"><em class="fa fa-book"></em><span>Jurnal Kegiatan</span></a>
                    </li>
                    <li class="<?php echo is_active('admin', $current); ?>">
                        <a href="?page=admin"><em class="fa fa-user"></em><span>Administrator</span></a>
                    </li>
                    <li class="<?php echo is_active('pengaturan', $current); ?>">
                        <a href="?page=pengaturan"><em class="fa fa-gear"></em><span>Pengaturan</span></a>
                    </li>
                <?php elseif (strtolower($_SESSION['level']) === 'siswa'): ?>
                    <li class="<?php echo is_active('absen', $current); ?>">
                        <a href="?page=absen"><em class="fa fa-calendar-check-o"></em><span>Presensi PKL</span></a>
                    </li>
                    <li class="<?php echo is_active('absen_asrama', $current); ?>">
                        <a href="?page=absen_asrama"><em class="fa fa-bed"></em><span>Presensi Asrama</span></a>
                    </li>
                    <li class="<?php echo is_active('riwayat', $current); ?>">
                        <a href="?page=riwayat"><em class="fa fa-history"></em><span>Riwayat Presensi</span></a>
                    </li>
                    <li class="<?php echo is_active('kegiatan', $current); ?>">
                        <a href="?page=kegiatan"><em class="fa fa-book"></em><span>Kegiatan Harian</span></a>
                    </li>
                    <li class="<?php echo is_active('profil', $current); ?>">
                        <a href="?page=profil"><em class="fa fa-user-circle-o"></em><span>Profil</span></a>
                    </li>
                <?php endif; ?>

                <li><a href="#" id="keluar"><em class="fa fa-sign-out"></em><span>Keluar</span></a></li>
            </ul>
        </aside>

        <!-- Overlay for mobile offcanvas -->
        <div id="overlay" class="overlay" aria-hidden="true"></div>

        <!-- ===================== MAIN CONTENT ===================== -->
        <main id="content" class="main" role="main">
            <div class="content-wrapper">
                <?php
                // Render routed page
                if (!empty($_GET['page']) && isset($allowed_pages[$_GET['page']])) {
                    include $allowed_pages[$_GET['page']];
                } else {
                    include "apps/beranda/index.php";
                }
                ?>
            </div>
        </main>
    </div>

    <!-- ===================== MODALS ===================== -->
    <div class="modal fade" id="modalLogout" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Logout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin keluar?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger">Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGlobal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content"><!-- konten via AJAX --></div>
        </div>
    </div>

    <!-- ============ JS (Bootstrap after jQuery) ============ -->
    <script src="template/js/bootstrap.min.js"></script>
    <script>
        // ===== Preloader & fix modal stuck =====
        $(window).on('load', function() {
            $(".se-pre-con").fadeOut("slow");
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });

        (function() {
            var $sidebar = $('#sidebar');
            var $overlay = $('#overlay');
            var $toggle = $('#sidebarToggle');

            function openOffcanvas() {
                $sidebar.addClass('open').attr('aria-hidden', 'false');
                $overlay.addClass('active').attr('aria-hidden', 'false');
                $('body').addClass('offcanvas-open');
                $toggle.attr('aria-expanded', 'true');
            }

            function closeOffcanvas() {
                $sidebar.removeClass('open').attr('aria-hidden', 'true');
                $overlay.removeClass('active').attr('aria-hidden', 'true');
                $('body').removeClass('offcanvas-open');
                $toggle.attr('aria-expanded', 'false');
            }

            // Show/hide only on mobile
            $toggle.on('click', function(e) {
                e.preventDefault();
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    if ($sidebar.hasClass('open')) {
                        closeOffcanvas();
                    } else {
                        openOffcanvas();
                    }
                }
            });

            // Close on overlay click
            $overlay.on('click', function() {
                closeOffcanvas();
            });

            // Close when resizing to desktop
            $(window).on('resize', function() {
                if (window.matchMedia('(min-width: 992px)').matches) {
                    closeOffcanvas();
                }
            });

            // Close offcanvas after clicking a link (mobile UX)
            $('#sidebar a').on('click', function() {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    closeOffcanvas();
                }
            });

            // ===== Logout modal =====
            $('#keluar').on('click', function(e) {
                e.preventDefault();
                $('#modalLogout').modal('show');
            });

            // ===== Global AJAX modal loader =====
            $(document).on('click', '[data-toggle="modal-ajax"]', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var target = $($(this).data('target') || '#modalGlobal');
                if (!url) return;

                target.find('.modal-content').html('<div class="p-4 text-center">Memuat...</div>');

                $.get(url, function(resp) {
                    target.find('.modal-content').html(resp);
                    target.modal('show');

                    var firstInput = target.find('input, textarea, select').filter(':visible:first');
                    if (firstInput.length) firstInput.focus();
                }).fail(function() {
                    target.find('.modal-content').html('<div class="p-4 text-center text-danger">Gagal memuat konten.</div>');
                });
            });

            // Cleanup after modal hidden
            $('#modalGlobal').on('hidden.bs.modal', function() {
                $(this).find('.modal-content').html('');
            });

            // Ensure modals are appended to body
            $(document).on('show.bs.modal', '.modal', function() {
                $(this).appendTo('body');
            });
        })();
    </script>
</body>

</html>