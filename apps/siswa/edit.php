<?php
session_start();
if (isset($_POST['edit_siswa'])) {
    include '../../config/database.php';

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        $id_siswa = input($_POST["id_siswa"]);
        $nama = input($_POST["nama"]);
        $perusahaan = input($_POST["perusahaan"]);
        $jurusan = input($_POST["jurusan"]);
        $nis = input($_POST["nis"]);
        $mulai_pkl = input($_POST["mulai_pkl"]);
        $akhir_pkl = input($_POST["akhir_pkl"]);
        $no_telp = input($_POST["no_telp"]);
        $alamat = input($_POST["alamat"]);

        // Foto
        $foto_saat_ini = $_POST['foto_saat_ini'];
        $foto_baru = $_FILES['foto_baru']['name'] ?? '';
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'gif');

        if (!empty($foto_baru)) {
            $x = explode('.', $foto_baru);
            $ekstensi = strtolower(end($x));
            $ukuran = $_FILES['foto_baru']['size'] ?? 0;
            $file_tmp = $_FILES['foto_baru']['tmp_name'] ?? '';
            $file_error = $_FILES['foto_baru']['error'] ?? UPLOAD_ERR_NO_FILE;

            if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=siswa&edit=gagal_format");
                exit;
            }

            if ($file_error !== UPLOAD_ERR_OK || !is_uploaded_file($file_tmp)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=siswa&edit=gagal_upload");
                exit;
            }

            $upload_dir = __DIR__ . '/foto/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Buat nama file unik untuk mencegah overwrite
            $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($foto_baru));
            $foto_baru_unik = time() . '_' . $safe_name;

            $dest = $upload_dir . $foto_baru_unik;
            if (move_uploaded_file($file_tmp, $dest)) {
                // Hapus file lama jika bukan default
                $old_path = __DIR__ . '/foto/' . $foto_saat_ini;
                if ($foto_saat_ini != 'foto_default.png' && file_exists($old_path)) {
                    @unlink($old_path);
                }

                $sql = "UPDATE tbl_siswa SET
                        nama='$nama',
                        perusahaan='$perusahaan',
                        jurusan='$jurusan',
                        nis='$nis',
                        mulai_pkl='$mulai_pkl',
                        akhir_pkl='$akhir_pkl',
                        alamat='$alamat',
                        no_telp='$no_telp',
                        foto='$foto_baru_unik'
                        WHERE id_siswa=$id_siswa";
            } else {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=siswa&edit=gagal_upload");
                exit;
            }
        } else {
            // Jika tidak upload foto baru, update tanpa foto
            $sql = "UPDATE tbl_siswa SET
                    nama='$nama',
                    perusahaan='$perusahaan',
                    jurusan='$jurusan',
                    nis='$nis',
                    mulai_pkl='$mulai_pkl',
                    akhir_pkl='$akhir_pkl',
                    no_telp='$no_telp',
                    alamat='$alamat'
                    WHERE id_siswa=$id_siswa";
        }

        $edit_siswa = mysqli_query($kon, $sql);
        if ($edit_siswa) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=siswa&edit=berhasil");
            exit;
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=siswa&edit=gagal");
            exit;
        }
    }
}
?>


<?php
include '../../config/database.php';
$id_siswa = $_POST["id_siswa"];
$sql = "select * from tbl_siswa where id_siswa=$id_siswa limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/siswa/edit.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="hidden" name="id_siswa" class="form-control" value="<?php echo $data['id_siswa']; ?>">
                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>" placeholder="Masukan Nama siswa" required>

            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Perusahaan :</label>
                <input type="text" name="perusahaan" class="form-control" value="<?php echo $data['perusahaan']; ?>" placeholder="Masukan Nama perusahaan" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" value="<?php echo $data['jurusan']; ?>" placeholder="Masukan Nama Jurusan" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nomor Induk siswa :</label>
                <input type="text" name="nis" class="form-control" value="<?php echo $data['nis']; ?>" placeholder="Masukan Nomor Induk siswa" required>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Mulai PKL :</label>
                <input type="date" name="mulai_pkl" class="form-control" value="<?php echo $data['mulai_pkl']; ?>" required>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Akhir PKL :</label>
                <input type="date" name="akhir_pkl" class="form-control" value="<?php echo $data['akhir_pkl']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp" value="<?php echo $data['no_telp']; ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <label>Alamat :</label>
                <textarea class="form-control" name="alamat" rows="4" id="alamat"><?php echo $data['alamat']; ?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label>Foto :</label><br>
            <img src="apps/siswa/foto/<?php echo $data['foto']; ?>" id="preview" width="90%" class="rounded" alt="Cinque Terre">
            <input type="hidden" name="foto_saat_ini" value="<?php echo $data['foto']; ?>" class="form-control" />
        </div>
        <div class="col-sm-4">
            <div id="msg"></div>
            <label>Upload Foto Baru:</label>
            <input type="file" name="foto_baru" class="file">
            <div class="input-group my-3">
                <input type="text" class="form-control" disabled placeholder="Upload File" id="file">
                <div class="input-group-append">
                    <button type="button" id="pilih_foto" class="browse btn btn-info"><i class="fa fa-search"></i> Pilih Foto</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_siswa" id="Submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update</button>
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

<script>
    $(document).on("click", "#pilih_foto", function() {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });
    $('input[type="file"]').change(function(e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>