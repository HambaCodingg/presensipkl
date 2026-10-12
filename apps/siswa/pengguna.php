<?php
session_start();

if (strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'admin') {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

require_once '../../config/database.php';
$csrf_token = isset($_SESSION['csrf_admin_action'])
    ? $_SESSION['csrf_admin_action']
    : bin2hex(random_bytes(32));
$_SESSION['csrf_admin_action'] = $csrf_token;

if (isset($_POST['submit'])) {
    if (!hash_equals($csrf_token, isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '')) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }

    $kode_siswa = trim(isset($_POST['kode_siswa']) ? (string) $_POST['kode_siswa'] : '');
    $username = trim(isset($_POST['username']) ? (string) $_POST['username'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    if ($kode_siswa === '' || $username === '' || strlen($username) > 255 || strlen($password) < 12 || strlen($password) > 255) {
        header('Location:../../index.php?page=siswa&pengguna=gagal');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        http_response_code(500);
        exit('Password tidak dapat diproses.');
    }
    $level = 'siswa';
    $update_statement = mysqli_prepare(
        $kon,
        'UPDATE tbl_user u
         INNER JOIN tbl_siswa s ON s.kode_siswa = u.kode_pengguna
         SET u.username = ?, u.password = ?, u.level = ?
         WHERE u.kode_pengguna = ?'
    );
    if (!$update_statement) {
        header('Location:../../index.php?page=siswa&pengguna=gagal');
        exit;
    }
    mysqli_stmt_bind_param($update_statement, 'ssss', $username, $password_hash, $level, $kode_siswa);
    $updated = mysqli_stmt_execute($update_statement);
    mysqli_stmt_close($update_statement);

    header('Location:../../index.php?page=siswa&pengguna=' . ($updated ? 'berhasil' : 'gagal'));
    exit;
}

$kode_siswa = trim(isset($_POST['kode_siswa']) ? (string) $_POST['kode_siswa'] : '');
if ($kode_siswa === '') {
    http_response_code(400);
    exit('Kode siswa tidak valid.');
}
$student_statement = mysqli_prepare(
    $kon,
    'SELECT u.username
     FROM tbl_user u
     INNER JOIN tbl_siswa s ON s.kode_siswa = u.kode_pengguna
     WHERE u.kode_pengguna = ?
     LIMIT 1'
);
if (!$student_statement) {
    http_response_code(500);
    exit('Data akun siswa tidak dapat dimuat.');
}
mysqli_stmt_bind_param($student_statement, 's', $kode_siswa);
if (!mysqli_stmt_execute($student_statement)) {
    mysqli_stmt_close($student_statement);
    http_response_code(500);
    exit('Data akun siswa tidak dapat dimuat.');
}
mysqli_stmt_bind_result($student_statement, $username);
$student_found = mysqli_stmt_fetch($student_statement);
mysqli_stmt_close($student_statement);
if (!$student_found) {
    http_response_code(404);
    exit('Akun siswa tidak ditemukan.');
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<form action="apps/siswa/pengguna.php" method="post">
    <input name="kode_siswa" type="hidden" value="<?php echo $escape($kode_siswa); ?>">
    <input name="csrf_token" type="hidden" value="<?php echo $escape($csrf_token); ?>">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo $escape($username); ?>" maxlength="255" required>
                <div id="info_username"></div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password baru (minimal 12 karakter) :</label>
                <input name="password" type="password" class="form-control" minlength="12" maxlength="255" required autocomplete="new-password">
            </div>
        </div>
    </div>
    <button type="submit" name="submit" class="btn-setting btn btn-success"><i class="fa fa-edit"></i> Simpan</button>
</form>

<script>
    $("#username").on('keyup', function() {
        $.ajax({
            url: 'apps/pengguna/cek_username.php',
            method: 'POST',
            data: { username: $(this).val() },
            success: function(data) {
                $('#info_username').show().html(data);
            }
        });
    });
    $('.btn-setting').on('click', function() {
        return confirm("Konfirmasi menyimpan username dan password?");
    });
</script>
