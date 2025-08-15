<?php
session_start();
if (isset($_POST['ubah_aplikasi'])) {
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

        $id_site = (int)$_POST["id"]; // cast ke int untuk keamanan
        $nama_instansi = input($_POST["nama_instansi"]);
        $pimpinan = input($_POST["pimpinan"]);
        $pembimbing = input($_POST["pembimbing"]);
        $no_telp = input($_POST["no_telp"]);
        $alamat = input($_POST["alamat"]);
        $website = input($_POST["website"]);
        $logo_sebelumnya = input($_POST["logo_sebelumnya"]);

        $logo = $_FILES['logo']['name'] ?? '';
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg'];
        $file_tmp = $_FILES['logo']['tmp_name'] ?? '';
        $ukuran = $_FILES['logo']['size'] ?? 0;

        if (!empty($logo)) {
            $x = explode('.', $logo);
            $ekstensi = strtolower(end($x));

            if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                // Upload file baru
                if (move_uploaded_file($file_tmp, 'logo/' . $logo)) {
                    // Hapus file lama jika ada dan file tersebut ada
                    if (!empty($logo_sebelumnya) && file_exists("logo/" . $logo_sebelumnya)) {
                        unlink("logo/" . $logo_sebelumnya);
                    }

                    $sql = "UPDATE tbl_site SET
                        nama_instansi = ?,
                        pimpinan = ?,
                        pembimbing = ?,
                        no_telp = ?,
                        alamat = ?,
                        website = ?,
                        logo = ?
                        WHERE id_site = ?";
                    $stmt = $kon->prepare($sql);
                    $stmt->bind_param("sssssssi", $nama_instansi, $pimpinan, $pembimbing, $no_telp, $alamat, $website, $logo, $id_site);
                } else {
                    mysqli_query($kon, "ROLLBACK");
                    die("Gagal mengupload logo.");
                }
            } else {
                mysqli_query($kon, "ROLLBACK");
                die("Format file logo tidak diperbolehkan.");
            }
        } else {
            $sql = "UPDATE tbl_site SET
                    nama_instansi = ?,
                    pimpinan = ?,
                    pembimbing = ?,
                    no_telp = ?,
                    alamat = ?,
                    website = ?
                    WHERE id_site = ?";
            $stmt = $kon->prepare($sql);
            $stmt->bind_param("ssssssi", $nama_instansi, $pimpinan, $pembimbing, $no_telp, $alamat, $website, $id_site);
        }

        if ($stmt->execute()) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=pengaturan&edit=berhasil");
            exit;
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=pengaturan&edit=gagal");
            exit;
        }
    }
}
    