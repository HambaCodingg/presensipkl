<?php
session_start();

if (
    strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'siswa' ||
    empty($_SESSION['kode_pengguna'])
) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

require_once '../../config/database.php';
$session_code = (string) $_SESSION['kode_pengguna'];
$csrf_token = isset($_SESSION['csrf_self_password'])
    ? $_SESSION['csrf_self_password']
    : bin2hex(random_bytes(32));
$_SESSION['csrf_self_password'] = $csrf_token;

if (isset($_POST['submit'])) {
    if (!hash_equals($csrf_token, isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '')) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }

    $posted_code = trim(isset($_POST['kode_siswa']) ? (string) $_POST['kode_siswa'] : '');
    $new_password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    if (!hash_equals($session_code, $posted_code) || strlen($new_password) < 12 || strlen($new_password) > 255) {
        header('Location:../../index.php?page=profil&pengguna=gagal');
        exit;
    }

    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        http_response_code(500);
        exit('Password tidak dapat diproses.');
    }
    $update_statement = mysqli_prepare(
        $kon,
        'UPDATE tbl_user SET password = ?, remember_token_hash = NULL WHERE kode_pengguna = ? AND LOWER(level) = ?'
    );
    if (!$update_statement) {
        header('Location:../../index.php?page=profil&pengguna=gagal');
        exit;
    }
    $level = 'siswa';
    mysqli_stmt_bind_param($update_statement, 'sss', $password_hash, $session_code, $level);
    $updated = mysqli_stmt_execute($update_statement);
    mysqli_stmt_close($update_statement);

    if ($updated) {
        header('Location:../../logout.php');
    } else {
        header('Location:../../index.php?page=profil&pengguna=gagal');
    }
    exit;
}

$posted_code = trim(isset($_POST['kode_siswa']) ? (string) $_POST['kode_siswa'] : '');
if (!hash_equals($session_code, $posted_code)) {
    http_response_code(403);
    exit('Anda hanya dapat mengubah password akun sendiri.');
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<form action="apps/pengguna/ubah_password.php" method="post">
    <input name="kode_siswa" type="hidden" value="<?php echo $escape($session_code); ?>">
    <input name="csrf_token" type="hidden" value="<?php echo $escape($csrf_token); ?>">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password baru (minimal 12 karakter) :</label>
                <input name="password" type="password" class="form-control" minlength="12" maxlength="255" required autocomplete="new-password">
            </div>
        </div>
    </div>
    <button type="submit" name="submit" class="btn-password btn btn-primary"><i class="fa fa-key"></i> Simpan</button>
</form>

<script>
    $('.btn-password').on('click', function() {
        return confirm("Konfirmasi mengubah password?");
    });
</script>
