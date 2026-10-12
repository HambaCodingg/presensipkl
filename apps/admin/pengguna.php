<?php
session_start();

if (strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'admin') {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

require_once '../../config/database.php';

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$csrf_token = isset($_SESSION['csrf_admin_action'])
    ? $_SESSION['csrf_admin_action']
    : bin2hex(random_bytes(32));
$_SESSION['csrf_admin_action'] = $csrf_token;

if (isset($_POST['submit'])) {
    if (!hash_equals($csrf_token, isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '')) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }

    $kode_admin = trim(isset($_POST['kode_admin']) ? (string) $_POST['kode_admin'] : '');
    $username = trim(isset($_POST['username']) ? (string) $_POST['username'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $session_code = isset($_SESSION['kode_pengguna']) ? (string) $_SESSION['kode_pengguna'] : '';

    if ($kode_admin === '' || $username === '' || strlen($username) > 255 || strlen($password) < 12) {
        header('Location:../../index.php?page=admin&pengguna=gagal');
        exit;
    }

    $target_statement = mysqli_prepare(
        $kon,
        'SELECT u.username
         FROM tbl_user u
         INNER JOIN tbl_admin a ON a.kode_admin = u.kode_pengguna
         WHERE u.kode_pengguna = ?
         LIMIT 1'
    );
    if (!$target_statement) {
        http_response_code(500);
        exit('Data akun admin tidak dapat diperiksa.');
    }
    mysqli_stmt_bind_param($target_statement, 's', $kode_admin);
    if (!mysqli_stmt_execute($target_statement)) {
        mysqli_stmt_close($target_statement);
        http_response_code(500);
        exit('Data akun admin tidak dapat diperiksa.');
    }
    mysqli_stmt_bind_result($target_statement, $existing_username);
    $target_found = mysqli_stmt_fetch($target_statement);
    mysqli_stmt_close($target_statement);

    if (!$target_found || ($kode_admin !== $session_code && trim((string) $existing_username) !== '')) {
        http_response_code(403);
        exit('Anda hanya dapat mengatur akun sendiri atau membuat kredensial awal admin.');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($password_hash === false) {
        http_response_code(500);
        exit('Password tidak dapat diproses.');
    }
    $update_statement = mysqli_prepare(
        $kon,
        'UPDATE tbl_user
         SET username = ?, password = ?, level = ?, remember_token_hash = NULL
         WHERE kode_pengguna = ?'
    );
    if (!$update_statement) {
        header('Location:../../index.php?page=admin&pengguna=gagal');
        exit;
    }
    $level = 'Admin';
    mysqli_stmt_bind_param($update_statement, 'ssss', $username, $password_hash, $level, $kode_admin);
    $updated = mysqli_stmt_execute($update_statement);
    mysqli_stmt_close($update_statement);

    header('Location:../../index.php?page=admin&pengguna=' . ($updated ? 'berhasil' : 'gagal'));
    exit;
}

$kode_admin = trim(isset($_POST['kode_admin']) ? (string) $_POST['kode_admin'] : '');
if ($kode_admin === '') {
    http_response_code(400);
    exit('Kode admin tidak valid.');
}
$target_statement = mysqli_prepare(
    $kon,
    'SELECT u.username
     FROM tbl_user u
     INNER JOIN tbl_admin a ON a.kode_admin = u.kode_pengguna
     WHERE u.kode_pengguna = ?
     LIMIT 1'
);
if (!$target_statement) {
    http_response_code(500);
    exit('Data akun admin tidak dapat dimuat.');
}
mysqli_stmt_bind_param($target_statement, 's', $kode_admin);
if (!mysqli_stmt_execute($target_statement)) {
    mysqli_stmt_close($target_statement);
    http_response_code(500);
    exit('Data akun admin tidak dapat dimuat.');
}
mysqli_stmt_bind_result($target_statement, $username);
$target_found = mysqli_stmt_fetch($target_statement);
mysqli_stmt_close($target_statement);
if (!$target_found) {
    http_response_code(404);
    exit('Akun admin tidak ditemukan.');
}

$can_edit = $kode_admin === (isset($_SESSION['kode_pengguna']) ? (string) $_SESSION['kode_pengguna'] : '') ||
    trim((string) $username) === '';
?>

<form action="apps/admin/pengguna.php" method="post">
    <input type="hidden" name="kode_admin" value="<?php echo $escape($kode_admin); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $escape($csrf_token); ?>">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo $escape($username); ?>"
                    <?php echo $can_edit ? '' : 'disabled'; ?>
                    placeholder="Buat Username" maxlength="255" required>
                <div id="info_username"></div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password (minimal 12 karakter) :</label>
                <input name="password" type="password" class="form-control"
                    <?php echo $can_edit ? '' : 'disabled'; ?>
                    placeholder="Buat Password" minlength="12" maxlength="255" required>
            </div>
        </div>
    </div>
    <?php if ($can_edit): ?>
        <button type="submit" name="submit" class="btn-setting btn btn-success"><i class="fa fa-edit"></i> Simpan</button>
    <?php else: ?>
        <div class="alert alert-info">Akun admin yang sudah memiliki username hanya dapat diubah oleh pemilik akun tersebut.</div>
    <?php endif; ?>
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
