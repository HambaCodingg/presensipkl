<?php
include '../../config/database.php';
include '../../config/function.php';
$id_siswa = $_POST["id_siswa"];
$sql = "SELECT * FROM tbl_siswa WHERE id_siswa=$id_siswa LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
?>

<table class="table">
    <tbody>
        <tr>
            <td>Nama Lengkap</td>
            <td width="75%">: <?php echo $data['nama']; ?></td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa</td>
            <td width="75%">: <?php echo $data['nis']; ?></td>
        </tr>
        <tr>
            <td>Perusahaan</td>
            <td width="75%">: <?php echo $data['perusahaan']; ?></td>
        </tr>
        <tr>
            <td>Jurusan</td>
            <td width="75%">: <?php echo $data['jurusan']; ?></td>
        </tr>
        <tr>
            <td>Mulai PKL</td>
            <td width="75%">: <?php $tgl = date("d", strtotime($data['mulai_pkl']));
                                $bulan = date("m", strtotime($data['mulai_pkl']));
                                $tahun = date("Y", strtotime($data['mulai_pkl']));
                                echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun ?></td>
        </tr>
        <tr>
            <td>Akhir PKL</td>
            <td width="75%">: <?php $tgl = date("d", strtotime($data['akhir_pkl']));
                                $bulan = date("m", strtotime($data['akhir_pkl']));
                                $tahun = date("Y", strtotime($data['akhir_pkl']));
                                echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun ?></td>
        </tr>
        <tr>
            <td>No Telp</td>
            <td width="75%">: <?php echo $data['no_telp']; ?></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td width="75%">: <?php echo $data['alamat']; ?></td>
        </tr>
    </tbody>
</table>