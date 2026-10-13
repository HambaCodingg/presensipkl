<?php
session_start();
if (strtolower(isset($_SESSION['level']) ? (string) $_SESSION['level'] : '') !== 'admin') {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

if (isset($_POST['edit_admin'])) {

    //Include file koneksi, untuk koneksikan ke database
    include '../../config/database.php';

    //Fungsi untuk mencegah inputan karakter yang tidak sesuai
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //Cek apakah ada kiriman form dari method post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //Memulai transaksi
        mysqli_query($kon, "START TRANSACTION");
        $id_admin = filter_var($_POST["id_admin"] ?? null, FILTER_VALIDATE_INT);
        $nama = input($_POST["nama"]);
        $nip = input($_POST["nip"]);
        $email = input($_POST["email"]);

        $statement = $id_admin ? mysqli_prepare(
            $kon,
            'UPDATE tbl_admin SET nama = ?, nip = ?, email = ? WHERE id_admin = ?'
        ) : false;
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'sssi', $nama, $nip, $email, $id_admin);
            $edit_admin = mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
        } else {
            $edit_admin = false;
        }

        //validasi jika data admin berhasil di update
        if ($edit_admin) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=admin&edit=berhasil");
        } else {
            //validasi jika data admin berhasil di update
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=admin&edit=gagal");
        }
    }
}
?>

<?php
include '../../config/database.php';
$id_admin = filter_var($_POST["id_admin"] ?? null, FILTER_VALIDATE_INT);
$statement = $id_admin ? mysqli_prepare($kon, 'SELECT * FROM tbl_admin WHERE id_admin = ? LIMIT 1') : false;
if (!$statement) {
    http_response_code(400);
    exit('ID admin tidak valid.');
}
mysqli_stmt_bind_param($statement, 'i', $id_admin);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($statement);
if (!$data) {
    http_response_code(404);
    exit('Data admin tidak ditemukan.');
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<form action="apps/admin/edit.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-7">
            <input type="hidden" name="id_admin" class="form-control" value="<?php echo (int) $data['id_admin']; ?>">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" value="<?php echo $escape($data['nama']); ?>" placeholder="Masukan Nama Lengkap" required>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control" value="<?php echo $escape($data['nip']); ?>" placeholder="Masukan Nomor Induk Pegawai" required>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" value="<?php echo $escape($data['email']); ?>" placeholder="Masukan Email" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_admin" id="Submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update</button>
            </div>
        </div>
    </div>
</form>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>