<?php
session_start();

// Reset session jika sebelumnya sudah ada login
if (isset($_SESSION["id_pengguna"])) {
    session_unset();
    session_destroy();
}

$pesan = "";

// Fungsi sanitasi input
function input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "config/database.php";

    $username = input($_POST["username"]);
    $password = md5(input($_POST["password"])); // langsung di-md5 tanpa input() dua kali

    // ==================== Cek Admin ====================
    $sql_admin = "SELECT * FROM tbl_user p
        INNER JOIN tbl_admin k ON k.kode_admin=p.kode_pengguna
        WHERE username=? AND password=? LIMIT 1";
    $stmt_admin = $kon->prepare($sql_admin);
    $stmt_admin->bind_param("ss", $username, $password);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    // ==================== Cek Siswa ====================
    $sql_siswa = "SELECT * FROM tbl_user p
        INNER JOIN tbl_siswa m ON m.kode_siswa=p.kode_pengguna
        WHERE username=? AND password=? LIMIT 1";
    $stmt_siswa = $kon->prepare($sql_siswa);
    $stmt_siswa->bind_param("ss", $username, $password);
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();

    // ==================== Login Berhasil ====================
    if ($result_admin->num_rows > 0) {
        $row = $result_admin->fetch_assoc();
        $_SESSION["id_pengguna"]  = $row["id_user"];
        $_SESSION["kode_pengguna"] = $row["kode_pengguna"];
        $_SESSION["nama_admin"]   = $row["nama"];
        $_SESSION["username"]     = $row["username"];
        $_SESSION["level"]        = $row["level"];
        $_SESSION["nip"]          = $row["nip"];

        header("Location:index.php?page=beranda");
        exit;
    } elseif ($result_siswa->num_rows > 0) {
        $row = $result_siswa->fetch_assoc();
        $_SESSION["id_pengguna"]  = $row["id_user"];
        $_SESSION["kode_pengguna"] = $row["kode_pengguna"];
        $_SESSION["id_siswa"]     = $row["id_siswa"];
        $_SESSION["nama_siswa"]   = $row["nama"];
        $_SESSION["username"]     = $row["username"];
        $_SESSION["perusahaan"]   = $row["perusahaan"];
        $_SESSION["level"]        = $row["level"];
        $_SESSION["foto"]         = $row["foto"];
        $_SESSION["nis"]          = $row["nis"];

        header("Location:index.php?page=verify_lokasi");
        exit;
    } else {
        $pesan = "<div class='alert alert-danger'>Username atau Password salah.</div>";
    }
}

// ==================== Ambil Data Site ====================
include 'config/database.php';
$query = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$row = mysqli_fetch_assoc($query);
$nama_instansi = $row['nama_instansi'] ?? 'ABSENSI & KEGIATAN';
$logo          = $row['logo'] ?? 'logo.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | <?php echo htmlspecialchars($nama_instansi); ?></title>
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo htmlspecialchars($logo); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #004080, #cc0000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            padding: 30px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 1s ease-in-out;
        }

        /* Logo dengan animasi */
        .logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 15px;
        }

        .animated-logo {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Fade in */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            color: #004080;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .subtitle {
            text-align: center;
            font-size: 0.95rem;
            color: #cc0000;
            margin-bottom: 20px;
            font-weight: 500;
        }

        label {
            font-weight: 600;
            color: #004080;
        }

        .btn-primary {
            background-color: #cc0000;
            border-color: #cc0000;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #800000;
            border-color: #800000;
        }

        .alert {
            font-size: 0.9rem;
            width: 100%;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 0.85rem;
            color: #555;
            text-align: center;
            width: 100%;
            font-style: italic;
            user-select: none;
        }
    </style>
</head>

<body>
    <div class="login-container shadow">
        <img src="apps/pengaturan/logo/<?php echo htmlspecialchars($logo); ?>"
            alt="Logo" class="logo animated-logo" />

        <h1>SMART PKL</h1>
        <h2 class="subtitle">Sistem Manajemen Report Tugas PKL</h2>

        <form action="" method="post" autocomplete="off" novalidate style="width:100%;">
            <div class="form-group">
                <label for="username">Username</label>
                <input autofocus required type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input required type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" />
            </div>
            <?php if ($pesan) echo $pesan; ?>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <div class="footer-text">
            © 2025 SMK TI BAZMA — Team IT
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>