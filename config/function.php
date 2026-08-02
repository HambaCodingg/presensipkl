<?php
function MendapatkanBulan($bulan)
{
    switch ($bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function MendapatkanHari($hari)
{
    switch ($hari) {
        case "Monday":
            return "Senin";
            break;
        case "Tuesday":
            return "Selasa";
            break;
        case "Wednesday":
            return "Rabu";
            break;
        case "Thursday":
            return "Kamis";
            break;
        case "Friday":
            return "Jumat";
            break;
        case "Saturday":
            return "Sabtu";
            break;
        case "Sunday":
            return "Minggu";
            break;
    }
}
?>

<?php
function AbsensiOtomatis($sql)
{
    include 'database.php';
    $sql = "SELECT tbl_siswa.id_siswa, tbl_siswa.nama, tbl_siswa.perusahaan, 
        tbl_siswa.mulai_pkl, tbl_siswa.akhir_pkl, tbl_absensi.id_absensi, 
        (CASE
            WHEN tbl_absensi.status IS NULL THEN 'Belum Absensi'
            WHEN tbl_absensi.status = 1 THEN 'Hadir'
            WHEN tbl_absensi.status = 2 THEN 'Izin'
        ELSE 'Tidak Hadir' END) AS status, 
        (CASE
            WHEN tbl_absensi.waktu IS NULL THEN 'Belum'
            ELSE tbl_absensi.waktu END) AS waktu,
        DATE_FORMAT(CURDATE(), '%W') AS hari,
        DATE_FORMAT(CURDATE(), '%Y-%m-%d') AS tanggal
        FROM tbl_siswa LEFT JOIN tbl_absensi ON 
            tbl_absensi.id_siswa = tbl_siswa.id_siswa 
        AND tbl_absensi.tanggal = CURDATE() 
        WHERE DAYNAME(CURDATE()) NOT IN ('Saturday', 'Sunday') AND 
            tbl_siswa.mulai_pkl <= CURDATE() AND
            tbl_siswa.akhir_pkl >= CURDATE()
            ORDER BY tbl_siswa.nama ASC;";
    return $sql;
}
?>

<?php
function PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_siswa, 
    COALESCE(CASE tbl_absensi.status 
        WHEN 1 THEN 'Hadir' 
        WHEN 2 THEN 'Izin' 
    ELSE 'Tidak Hadir' END) as status,
    DATE_FORMAT(tbl_absensi.tanggal, '%W') AS hari, 
        tbl_absensi.tanggal, 
        tbl_absensi.waktu, tbl_siswa.nama, tbl_siswa.perusahaan, 
        tbl_siswa.mulai_pkl, tbl_siswa.akhir_pkl 
    FROM tbl_siswa LEFT JOIN tbl_absensi 
        ON tbl_absensi.id_siswa = tbl_siswa.id_siswa 
    WHERE tbl_siswa.mulai_pkl <= CURDATE() AND 
        tbl_siswa.akhir_pkl >= CURDATE() AND 
    DAYNAME(tbl_absensi.tanggal) NOT IN ('Saturday', 'Sunday') AND 
        tbl_siswa.nama LIKE '%$nama%' AND
        tbl_absensi.tanggal >= '$tanggal_awal' AND
        tbl_absensi.tanggal <= '$tanggal_akhir'
    ORDER BY tbl_absensi.tanggal DESC;";
    return $sql;
}
?>

<?php
function EditAbsensi($id_absensi)
{
    $id_absensi = intval($id_absensi); // keamanan sederhana
    $sql = "
        SELECT 
            a.id_absensi,
            a.id_siswa,
            a.status,
            a.waktu,
            a.tanggal,
            a.foto,
            a.latitude,
            a.longitude,
            COALESCE(al.tanggal, a.tanggal) AS tanggal_alasan
        FROM tbl_absensi a
        LEFT JOIN tbl_alasan al ON a.id_absensi = al.id_alasan
        WHERE (a.tanggal = al.tanggal OR al.tanggal IS NULL)
          AND a.id_absensi = '$id_absensi'
        LIMIT 1
    ";
    return $sql;
}

function AbsensiAsramaOtomatis($sql)
{
    include 'database.php';
    $sql = "SELECT tbl_siswa.id_siswa, tbl_siswa.nama, tbl_siswa.perusahaan,
        tbl_absen_asrama.id_absen_asrama,
        (CASE
            WHEN tbl_absen_asrama.status IS NULL THEN 'Belum Absensi'
            WHEN tbl_absen_asrama.status = 1 THEN 'Hadir'
            WHEN tbl_absen_asrama.status = 2 THEN 'Izin'
        ELSE 'Tidak Hadir' END) AS status,
        (CASE
            WHEN tbl_absen_asrama.waktu IS NULL THEN 'Belum'
            ELSE tbl_absen_asrama.waktu END) AS waktu,
        DATE_FORMAT(CURDATE(), '%W') AS hari,
        DATE_FORMAT(CURDATE(), '%Y-%m-%d') AS tanggal
        FROM tbl_siswa LEFT JOIN tbl_absen_asrama ON 
            tbl_absen_asrama.id_siswa = tbl_siswa.id_siswa 
        AND tbl_absen_asrama.tanggal = CURDATE()
        ORDER BY tbl_siswa.nama ASC;";
    return $sql;
}

function PencarianAbsensiAsrama($nama, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_absen_asrama.id_absen_asrama, tbl_absen_asrama.id_siswa,
    COALESCE(CASE tbl_absen_asrama.status 
        WHEN 1 THEN 'Hadir' 
        WHEN 2 THEN 'Izin' 
    ELSE 'Tidak Hadir' END) as status,
    DATE_FORMAT(tbl_absen_asrama.tanggal, '%W') AS hari, 
        tbl_absen_asrama.tanggal, 
        tbl_absen_asrama.waktu, tbl_siswa.nama, tbl_siswa.perusahaan 
    FROM tbl_siswa LEFT JOIN tbl_absen_asrama 
        ON tbl_absen_asrama.id_siswa = tbl_siswa.id_siswa 
    WHERE tbl_siswa.nama LIKE '%$nama%' AND
        tbl_absen_asrama.tanggal >= '$tanggal_awal' AND
        tbl_absen_asrama.tanggal <= '$tanggal_akhir'
    ORDER BY tbl_absen_asrama.tanggal DESC;";
    return $sql;
}

function EditAbsensiAsrama($id_absen_asrama)
{
    $id_absen_asrama = intval($id_absen_asrama); // keamanan sederhana
    $sql = "
        SELECT 
            a.id_absen_asrama,
            a.id_siswa,
            a.status,
            a.waktu,
            a.tanggal,
            a.foto,
            a.latitude,
            a.longitude,
            COALESCE(al.tanggal, a.tanggal) AS tanggal_alasan
        FROM tbl_absen_asrama a
        LEFT JOIN tbl_alasan_asrama al ON a.id_absen_asrama = al.id_alasan_asrama
        WHERE (a.tanggal = al.tanggal OR al.tanggal IS NULL)
          AND a.id_absen_asrama = '$id_absen_asrama'
        LIMIT 1
    ";
    return $sql;
}

?>

<?php
function DataKegiatan($sql)
{
    include 'database.php';
    $sql = "SELECT tbl_siswa.id_siswa, tbl_siswa.nama, 
    tbl_siswa.perusahaan, tbl_kegiatan.id_kegiatan, 
    tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
    CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
    FROM tbl_siswa JOIN tbl_kegiatan ON 
    tbl_siswa.id_siswa = tbl_kegiatan.id_siswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function CariKegiatan($nama, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_siswa.id_siswa, tbl_siswa.nama, 
    tbl_siswa.perusahaan, tbl_kegiatan.id_kegiatan, 
    tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
    CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
    FROM tbl_siswa JOIN tbl_kegiatan ON 
    tbl_siswa.id_siswa = tbl_kegiatan.id_siswa
    WHERE tbl_siswa.nama = '$nama' AND
    tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function MenampilkanKegiatan($id_siswa)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_kegiatan, tbl_kegiatan.id_siswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_siswa = '$id_siswa' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_siswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function MencarikanKegiatan($id_siswa, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_kegiatan, tbl_kegiatan.id_siswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_siswa = '$id_siswa'
    AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_siswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function WaktuKegiatan($string_kegiatan)
{
    $array_kegiatan = explode(", ", $string_kegiatan);
    $kegiatan = array();
    foreach ($array_kegiatan as $kgt) {
        $kgt_array = explode(" (", $kgt);
        $nama_kegiatan = trim($kgt_array[0]);
        $waktu_kegiatan = trim($kgt_array[1], ")");
        $waktu_array = explode(" - ", $waktu_kegiatan);
        $waktu_awal = trim($waktu_array[0]);
        $waktu_akhir = trim($waktu_array[1]);
        $waktu_awal = date('H:i', strtotime($waktu_awal));
        $waktu_akhir = date('H:i', strtotime($waktu_akhir));
        $kegiatan[] = $waktu_awal . " - " . $waktu_akhir;
    }
    foreach ($kegiatan as $kegiatan) {
        echo $kegiatan . ' </br>';
    }
}
?>

<?php
function BarisKegiatan($string_kegiatan)
{
    $array_kegiatan = explode(", ", $string_kegiatan);
    $kegiatan = array();
    foreach ($array_kegiatan as $kgt) {
        $kgt_array = explode(" (", $kgt);
        $nama_kegiatan = trim($kgt_array[0]);
        $kegiatan[] = $nama_kegiatan;
    }

    $no1 = 1;
    $max_kegiatan = count($kegiatan);
    for ($i = 0; $i < $max_kegiatan; $i++) {
        echo $no1 . ". ";
        echo $kegiatan[$i] . " </br>";
        $no1++;
    }
}
?>

<?php
function MendapatkanAwalBulan($mulai_bulan)
{
    switch ($mulai_bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function MendapatkanAkhirBulan($akhir_bulan)
{
    switch ($akhir_bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function StatusAbsensi($status)
{
    switch ($status) {
        case 1:
            $status = "Hadir";
            break;
        case 2:
            $status = "Izin";
            break;
        case 3:
            $status = "Tidak Hadir";
            break;
    }
    return $status;
}
?>