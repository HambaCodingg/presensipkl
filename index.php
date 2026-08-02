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
        :root {
            --bg: #f8fbff;
            --surface: #ffffff;
            --surface-soft: #f5f3ff;
            --text: #111827;
            --muted: #64748b;
            --border: #e7e5ff;
            --primary: #8b5cf6;
            --accent: #38bdf8;
            --success: #4ade80;
            --warning: #fbcfe8;
            --danger: #fb7185;
            --shadow: 0 30px 80px rgba(15, 23, 42, .08);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: Inter, "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #f3efff 100%);
            color: var(--text);
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

        .se-pre-con {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #fff;
        }

        .navbar-custom {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 72px;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 24px 40px rgba(15, 23, 42, .06);
            z-index: 1030;
        }

        .navbar-custom .brand-title {
            color: #111827;
            font-weight: 800;
            margin: 0;
            font-size: clamp(18px, 2.2vw, 24px);
            letter-spacing: .02em;
        }

        #sidebarToggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border: 0;
            background: transparent;
            color: #334155;
            cursor: pointer;
        }

        #sidebarToggle .bar {
            display: block;
            width: 24px;
            height: 2px;
            background: #334155;
            margin: 4px 0;
            transition: .25s;
        }

        @media (min-width: 992px) {
            #sidebarToggle {
                display: none;
            }
        }

        .layout {
            display: block;
            min-height: 100%;
            padding-top: 72px;
        }

        @media (max-width: 575.98px) {
            .layout {
                padding-top: 64px;
            }
        }

        #sidebar {
            position: fixed;
            top: 72px;
            left: 0;
            bottom: 0;
            width: 280px;
            background: rgba(255, 255, 255, .96);
            color: #334155;
            border-right: 1px solid rgba(15, 23, 42, .08);
            overflow-y: auto;
            backdrop-filter: blur(14px);
        }

        @media (max-width: 575.98px) {
            #sidebar {
                top: 64px;
            }
        }

        #sidebar .profile {
            padding: 24px 18px;
            text-align: center;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
            background: linear-gradient(180deg, rgba(139, 92, 246, .1), rgba(56, 189, 248, .06));
        }

        #sidebar .profile img {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(139, 92, 246, .2);
            margin-bottom: 12px;
        }

        #sidebar .profile .name {
            margin: 0;
            font-weight: 700;
            color: #111827;
            font-size: 1rem;
        }

        #sidebar .profile .role {
            margin-top: 6px;
            color: #64748b;
            font-size: .85rem;
        }

        #sidebar .divider {
            height: 1px;
            margin: 20px 0;
            background: rgba(139, 92, 246, .14);
        }

        #sidebar ul.menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #sidebar ul.menu li {
            margin-bottom: 10px;
        }

        #sidebar ul.menu li a {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #475569;
            text-decoration: none;
            padding: 14px 18px;
            font-weight: 600;
            border-radius: 18px;
            background: rgba(248, 250, 255, .95);
            border: 1px solid transparent;
            transition: all .25s ease;
        }

        #sidebar ul.menu li a em.fa {
            width: 22px;
            text-align: center;
            color: #8b5cf6;
        }

        #sidebar ul.menu li a:hover,
        #sidebar ul.menu li.active>a {
            background: linear-gradient(135deg, rgba(139, 92, 246, .18), rgba(56, 189, 248, .14));
            color: #111827;
            border-color: rgba(139, 92, 246, .16);
        }

        #sidebar ul.menu li.active>a em.fa {
            color: #4338ca;
        }

        #sidebar a#keluar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 18px;
            color: #c2410c;
            margin-top: 18px;
            background: rgba(251, 113, 133, .14);
            font-weight: 600;
        }

        #sidebar a#keluar:hover {
            background: rgba(251, 113, 133, .24);
        }

        .main {
            padding: 30px 30px 44px;
            min-height: calc(100vh - 72px);
        }

        @media (min-width: 992px) {
            .main {
                margin-left: 280px;
            }
        }

        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform .28s ease;
                width: 280px;
                top: 64px;
                z-index: 1052;
            }

            #sidebar.open {
                transform: translateX(0);
            }

            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .35);
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

        .content-wrapper {
            width: 100%;
            margin: 0 auto;
        }

        .hero-img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .08);
        }

        .page-title {
            font-weight: 800;
            font-size: clamp(20px, 2.6vw, 28px);
            color: #111827;
            margin: 0 0 18px;
            line-height: 1.15;
        }

        .card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .08);
            padding: 24px;
            margin-bottom: 22px;
            border: 1px solid rgba(139, 92, 246, .12);
        }

        .card:hover {
            transform: translateY(-2px);
            transition: transform .2s ease;
        }

        .btn,
        .button {
            border-radius: 18px;
            font-weight: 700;
            padding: 14px 22px;
            transition: all .2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #c4b5fd 0%, #8b5cf6 100%);
            border-color: transparent;
            color: #ffffff;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: #ffffff;
        }

        .table {
            background: transparent;
        }

        .table thead th {
            border-bottom: 2px solid rgba(226, 232, 240, .95);
            font-weight: 700;
            color: #475569;
            background: #f8f5ff;
        }

        .table tbody tr {
            border-bottom: 1px solid rgba(226, 232, 240, .75);
        }

        .table td,
        .table th {
            vertical-align: middle;
            padding: 18px 16px;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #faf5ff;
        }

        .table-responsive {
            border-radius: 24px;
            overflow: hidden;
        }

        .form-control {
            border: 1px solid rgba(139, 92, 246, .18);
            border-radius: 16px;
            padding: 14px 16px;
            background: #fff;
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(56, 189, 248, .8);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, .16);
        }

        a:focus,
        button:focus {
            outline: 2px dashed rgba(139, 92, 246, .35);
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