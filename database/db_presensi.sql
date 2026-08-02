-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Agu 2025 pada 10.59
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_presensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_absensi`
--

CREATE TABLE `tbl_absensi` (
  `id_absensi` int(15) NOT NULL,
  `id_siswa` int(15) DEFAULT NULL,
  `status` int(15) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_absensi`
--

INSERT INTO `tbl_absensi` (`id_absensi`, `id_siswa`, `status`, `foto`, `latitude`, `longitude`, `waktu`, `tanggal`) VALUES
(81, 7, 1, NULL, NULL, NULL, '17:47:58', '2025-08-14'),
(82, 8, 0, NULL, NULL, NULL, '23:59:05', '2025-08-14'),
(83, 1, 0, NULL, NULL, NULL, '23:59:24', '2025-08-14'),
(84, 11, 1, NULL, NULL, NULL, '23:59:55', '2025-08-14'),
(85, 1, 0, NULL, NULL, NULL, '00:00:12', '2025-08-15'),
(87, 20, 1, '20250815_081420_20.jpg', '', '', '08:14:20', '2025-08-15'),
(88, 22, 1, '20250815_081448_22.jpg', '', '', '08:14:48', '2025-08-15'),
(89, 19, 1, '20250815_081826_19.jpg', '', '', '08:18:26', '2025-08-15'),
(90, 18, 1, '20250815_081911_18.jpg', '', '', '08:19:11', '2025-08-15'),
(91, 16, 1, '20250815_082402_16.jpg', '', '', '08:24:02', '2025-08-15'),
(93, 12, 1, '20250815_082739_12.jpg', '', '', '08:27:39', '2025-08-15'),
(94, 10, 1, '20250815_083057_10.jpg', '', '', '08:30:57', '2025-08-15'),
(95, 9, 1, '20250815_083138_9.jpg', '', '', '08:31:38', '2025-08-15'),
(96, 8, 1, '20250815_083305_8.jpg', '', '', '08:33:05', '2025-08-15'),
(97, 7, 1, '20250815_083414_7.jpg', '', '', '08:34:14', '2025-08-15'),
(98, 3, 1, '20250815_083526_3.jpg', '', '', '08:35:26', '2025-08-15'),
(99, 21, 1, '20250815_083851_21.jpg', '', '', '08:38:51', '2025-08-15'),
(100, 13, 1, '20250815_083955_13.jpg', '', '', '08:39:55', '2025-08-15'),
(101, 7, 1, NULL, NULL, NULL, '17:58:36', '2025-08-18'),
(102, 25, 1, '20250819_084256_25.png', '-6.5613624', '106.7131557', '08:42:56', '2025-08-19'),
(103, 20, 1, '20250819_100402_20.png', '-6.5806344', '106.6880956', '10:04:02', '2025-08-19'),
(104, 22, 1, '20250819_100541_22.png', '-6.5806344', '106.6880956', '10:05:41', '2025-08-19'),
(105, 21, 1, '20250819_100955_21.png', '-6.5806344', '106.6880956', '10:09:55', '2025-08-19'),
(106, 15, 1, '20250819_104552_15.png', '-6.5806344', '106.6880956', '10:45:52', '2025-08-19'),
(107, 11, 2, '20250819_105238_11.jpg', '-6.5806344', '106.6880956', '10:52:38', '2025-08-19'),
(108, 19, 2, '20250819_105401_19.jpg', '-6.5806344', '106.6880956', '10:54:01', '2025-08-19'),
(109, 16, 2, '20250819_105458_16.jpeg', '-6.5806344', '106.6880956', '10:54:58', '2025-08-19'),
(110, 3, 1, '20250819_112000_3.png', '-6.5806344', '106.6880956', '11:20:00', '2025-08-19'),
(111, 23, 1, '20250819_115202_23.png', '-6.5806344', '106.6880956', '11:52:02', '2025-08-19'),
(112, 10, 1, '20250819_120057_10.png', '-6.5806344', '106.6880956', '12:00:57', '2025-08-19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int(15) NOT NULL,
  `kode_admin` varchar(4) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `kode_admin`, `nama`, `nip`, `email`) VALUES
(1, 'A001', 'Muhamad Dzikri Fauzan', '0002', 'muhammaddzikrifauzan20001105@gmail.com'),
(2, 'A002', 'Administrator', '0001', 'administrator@gmail.com'),
(3, 'A003', 'Muhamad Dzikri Fauzan,S.Kom', '22013', 'muhammaddzikrifauzan20001105@gmail.com\r\n');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_alasan`
--

CREATE TABLE `tbl_alasan` (
  `id_alasan` int(15) NOT NULL,
  `id_siswa` int(15) DEFAULT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_alasan`
--

INSERT INTO `tbl_alasan` (`id_alasan`, `id_siswa`, `alasan`, `tanggal`) VALUES
(5, 7, '', '2025-08-14'),
(6, 1, 'sakit', '2025-08-14'),
(7, 3, 'jjj', '2025-08-14'),
(8, 8, '', '2025-08-14'),
(9, 1, '', '2025-08-14'),
(10, 11, '', '2025-08-14'),
(11, 1, '', '2025-08-15'),
(13, 7, '', '2025-08-18'),
(14, 20, '', '2025-08-19'),
(15, 11, 'Sakit', '2025-08-19'),
(16, 19, 'Sakit Gigi', '2025-08-19'),
(17, 16, 'Sakit Demam', '2025-08-19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_absen_asrama`
--

CREATE TABLE `tbl_absen_asrama` (
  `id_absen_asrama` int(15) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(15) DEFAULT NULL,
  `status` int(15) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  PRIMARY KEY (`id_absen_asrama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_absen_asrama`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_alasan_asrama`
--

CREATE TABLE `tbl_alasan_asrama` (
  `id_alasan_asrama` int(15) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(15) DEFAULT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  PRIMARY KEY (`id_alasan_asrama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_alasan_asrama`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kegiatan`
--

CREATE TABLE `tbl_kegiatan` (
  `id_kegiatan` int(15) NOT NULL,
  `id_siswa` int(15) DEFAULT NULL,
  `kegiatan` varchar(255) DEFAULT NULL,
  `waktu_awal` time DEFAULT NULL,
  `waktu_akhir` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_kegiatan`
--

INSERT INTO `tbl_kegiatan` (`id_kegiatan`, `id_siswa`, `kegiatan`, `waktu_awal`, `waktu_akhir`, `tanggal`) VALUES
(157, 3, 'Induction', '08:00:00', '16:00:00', '2025-08-14'),
(158, 1, 'Develop sistem PTC', '08:00:00', '16:00:00', '2025-08-14'),
(161, 15, 'Apa aja', '10:00:00', '12:00:00', '2025-08-18'),
(162, 15, 'pusinggg', '10:11:00', '14:00:00', '2025-08-18'),
(163, 15, 'dajdhasd', '10:00:00', '13:00:00', '2025-08-18'),
(164, 15, 'DHAHDA', '08:59:00', '03:13:00', '2025-08-18'),
(165, 25, 'Setting Jaringan ruang Meeting', '08:50:00', '16:00:00', '2025-08-19'),
(166, 25, 'Setting Jaringan ruang server PPN', '10:03:00', '12:05:00', '2025-08-19'),
(167, 20, 'Lomba 17 Agustus bersama rekan-rekan kantor', '07:00:00', '12:00:00', '2025-08-19'),
(168, 22, 'Memeriahkan 17 Agustus bersama seluruh karyawan PTC', '10:05:00', '12:07:00', '2025-08-19'),
(169, 21, 'Menjadi Volunteer berbagi bersama Yayasan BAZMA', '10:10:00', '16:10:00', '2025-08-19'),
(170, 15, 'Lomba 17 Agustus bersama rekan-rekan kantor PT Gheotermal Energy', '10:46:00', '15:46:00', '2025-08-19'),
(171, 3, 'Setting Jaringan ruang Aula Yayasan Bazma', '11:20:00', '16:20:00', '2025-08-19'),
(172, 23, 'Memeriahkan 17 Agustus bersama seluruh karyawan PGE', '11:52:00', '12:52:00', '2025-08-19'),
(173, 10, 'Lomba 17 Agustus bersama rekan-rekan kantor PT Gheotermal Energy', '12:01:00', '15:01:00', '2025-08-19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_setting_absensi`
--

CREATE TABLE `tbl_setting_absensi` (
  `id_waktu` int(15) DEFAULT NULL,
  `mulai_absen` time DEFAULT NULL,
  `akhir_absen` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_setting_absensi`
--

INSERT INTO `tbl_setting_absensi` (`id_waktu`, `mulai_absen`, `akhir_absen`) VALUES
(1, '08:00:00', '16:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_siswa`
--

CREATE TABLE `tbl_siswa` (
  `id_siswa` int(15) NOT NULL,
  `kode_siswa` varchar(4) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `perusahaan` varchar(255) DEFAULT NULL,
  `jurusan` varchar(255) DEFAULT NULL,
  `nis` varchar(255) DEFAULT NULL,
  `mulai_pkl` date DEFAULT NULL,
  `akhir_pkl` date DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_siswa`
--

INSERT INTO `tbl_siswa` (`id_siswa`, `kode_siswa`, `nama`, `perusahaan`, `jurusan`, `nis`, `mulai_pkl`, `akhir_pkl`, `alamat`, `no_telp`, `foto`) VALUES
(1, 'M034', 'Attar Rifai', 'PT Pertamina Training dan Consulting', 'Sistem Informatika Jaringan dan Aplikasi', '2223003', '2025-08-01', '2026-04-30', 'Oil Centre, Jl. M.H. Thamrin Lantai 1 - Lantai 4, RT.9/RW.5, Gondangdia, Menteng, Central Jakarta City, Jakarta 10350', '0812-9164-7020', '3.png'),
(3, 'M029', 'Ahmad Tauhid', 'Yayasan BAZMA', 'Sistem Informatika Jaringan dan Aplikasi', '2223002', '2025-08-01', '2026-04-30', 'Gedung Kwarnas, Jl. Medan Merdeka Tim. No.6 Lantai 3, RT.2/RW.1, Gambir, Kecamatan Gambir, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10110', '0822-9034-2042', '2.png'),
(6, 'M045', 'Gemi Widodo', 'PT Perta Life Insurance', 'Sistem Informatika Jaringan dan Aplikasi', '2223008', '2025-08-01', '2026-03-01', 'Jl. K.H. Wahid Hasyim No.84-88, RT.15/RW.3, Kb. Sirih, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10340', '0812-2601-9172', '8.png'),
(7, 'M035', 'Dhiaraqi Ahmad Khaizuran', 'PT Pertamina Geothermal Energy', 'Sistem Informatika Jaringan dan Aplikasi', '2223004', '2025-08-01', '2026-04-30', 'Jl. Medan Merdeka Tim. No.11-13 6, RT.6/RW.1, Gambir, Kecamatan Gambir, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10110', '-', '4.png'),
(8, 'M036', 'Diandra Vieri Dwi Airlangga', 'PT Pertamina Shared Service Center', 'Sistem, Informatika, Jaringan, dan Aplikasi', '2223005', '2025-08-01', '2026-04-30', 'Gd. Sopo Del, Tower A, Lt. 52, Jl. Mega Kuningan Barat III Lot 10. 1-6, Desa/Kelurahan Kuningan Timur, Kec. Setiabudi, Kota Adm. Jakarta Selatan', '-', '5.png'),
(9, 'M038', 'Fadhil Rabbani', 'PT Pertamina', 'Sistem Informatika Jaringan dan Aplikasi', '2223006', '2025-08-01', '2026-04-30', 'Gedung Sopo Del, Tower A, Lantai 53 Jl. Mega Kuningan Barat III Lot 10. 1-6 Jakarta Selatan', '0895-1456-3365', '6.png'),
(10, 'M040', 'Fayyadh Rantisi', 'PT Pertamina Gas Negara', 'Sistem Informatika Jaringan dan Aplikasi', '2223007', '2025-08-01', '2026-04-30', 'Jl. K.H. Zainul Arifin No. 20, Jakarta Barat, DKI Jakarta, 11140, Indonesia', '0812-6623-0848', '7.png'),
(11, 'M046', 'Hafith Muhammad Fauzan', 'PT Asuransi Tugu Pratama Indonesia', 'Sistem Informatika Jaringan dan Aplikasi', '2223009', '2025-08-01', '2026-04-30', 'Wisma Tugu I, Jl. H. R. Rasuna Said No.Kav. C8-9, Karet, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12920', '0852-1661-3352', '9.png'),
(12, 'M051', 'Hanif Gibran Sidik', 'PT Pertamina Gas Negara', 'Sistem Informatika Jaringan dan Aplikasi', '2223010', '2025-08-01', '2026-04-30', '', '0819-4334-0629', '10.png'),
(13, 'M062', 'Ibrahim', 'PT Pertamina Retail', 'Sistem Informatika Jaringan dan Aplikasi', '2223011', '2025-08-01', '2026-04-30', 'Gedung Grha Pertamina Lantai 10-11, Jalan Medan Merdeka Timur No.11-13, Jakarta Pusat', '-', '11.png'),
(15, 'M061', 'Muhammad Abdullah Al Aziz', 'PT Pertamina Geothermal Energy', 'Sistem Informatika Jaringan dan Aplikasi', '2223013', '2025-08-01', '2026-04-30', 'Jl. Medan Merdeka Tim. No.11-13 6, RT.6/RW.1, Gambir, Kecamatan Gambir, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10110', '0812-2743-1167', '14.png'),
(16, 'M063', 'Muhammad Faiq Mustanir', 'PT Pertamina Patra Niaga', 'Sistem Informatika Jaringan dan Aplikasi', '2223014', '2025-08-01', '2026-04-30', 'Wisma Tugu II 2nd Floor, Jl. H. R. Rasuna Said No.9 Kavling C7, Kuningan, Daerah Khusus Ibukota Jakarta 12920', '0895-6295-08100', '13.png'),
(17, 'M033', 'Muhammad Ibrahim', 'PT Yayasan Kesehatan Pertamina', 'Sistem Informatika Jaringan dan Aplikasi', '2223015', '2025-08-01', '2026-04-30', 'Wisma Tugu Wahid Hasyim, Jl. K.H. Wahid Hasyim No.100-102, Kb. Sirih, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10340', '0889-7627-7505', '15.png'),
(18, 'M064', 'Muhammad Saeful Ramadhan', 'PT Pertamina', 'Sistem Informatika Jaringan dan Aplikasi', '2223016', '2025-08-01', '2026-04-30', '', '0859-7184-8769', '16.png'),
(19, 'M065', 'Radid Aditia Renaldi', 'PT Pertamina Shared Service Center', 'Sistem Informatika Jaringan dan Aplikasi', '2223017', '2025-08-01', '2026-04-30', 'Sopo Del Tower A lantai 52, Jl. Mega Kuningan Barat III Lot 10. 1-6, Jakarta Selatan, 12950', '0857-6497-1863', '17.png'),
(20, 'M073', 'Rofi Dzaki Abdul Aziz', 'PT Pertamina Patra Niaga', 'Sistem Informatika Jaringan dan Aplikasi', '2223018', '2025-08-01', '2026-04-30', 'Wisma Tugu II 2nd Floor, Jl. H. R. Rasuna Said No.9 Kavling C7, Kuningan, Daerah Khusus Ibukota Jakarta 12920', '0895-0367-6300', '18.png'),
(21, 'M069', 'Sahrul Romadhon', 'PT Yayasan Kesehatan Pertamina', 'Sistem Informatika Jaringan dan Aplikasi', '2223019', '2025-08-01', '2026-04-30', 'Wisma Tugu II 2nd Floor, Jl. H. R. Rasuna Said No.9 Kavling C7, Kuningan, Daerah Khusus Ibukota Jakarta 12920', '0858-8169-5644', '19.png'),
(22, 'M072', 'Syahban Syaputra', 'PT Pertamina Training dan Consulting', 'Sistem Informatika Jaringan dan Aplikasi', '2223020', '2025-08-01', '2026-04-30', 'Oil Centre, Jl. M.H. Thamrin Lantai 1 - Lantai 4, RT.9/RW.5, Gondangdia, Menteng, Central Jakarta City, Jakarta 10350', '0856-5941-0983', '20.png'),
(23, 'M023', 'Adli Fathi Rayhan', 'PT Pertamina Gas Negara', 'Sistem Informatika Jaringan dan Aplikasi', '2203001', '2025-08-01', '2026-04-30', 'Jl. K.H. Zainul Arifin No. 20, Jakarta Barat, DKI Jakarta, 11140, Indonesia', '-', '1.png'),
(24, 'M028', 'Mufiz Ihsanulhaq', 'PT Asuransi Tugu Pratama Indonesia', 'Sistem Informatika Jaringan dan Aplikasi', '2223012', '2025-08-01', '2026-04-30', 'Wisma Tugu I, Jl. H. R. Rasuna Said No.Kav. C8-9, Karet, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12920', '0818-0634-6570', '12.png'),
(25, 'M074', 'hambaAllah', 'PT Kilang Pertamina Internasional (KPI)', 'Sistem Informatika Jaringan dan Aplikasi', '2203025', '2025-08-01', '2026-04-30', 'Tower Fastron, Gedung Grha Pertamina, Jl. Medan Merdeka Tim. No.2 Lantai 9, RT.6/RW.1, Gambir, Kecamatan Gambir, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10110', '085759089227', 'PAS FOTO PAK DZIKRI 4x6.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_site`
--

CREATE TABLE `tbl_site` (
  `id_site` int(15) DEFAULT NULL,
  `nama_instansi` varchar(255) DEFAULT NULL,
  `pimpinan` varchar(255) DEFAULT NULL,
  `pembimbing` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_site`
--

INSERT INTO `tbl_site` (`id_site`, `nama_instansi`, `pimpinan`, `pembimbing`, `no_telp`, `alamat`, `website`, `logo`) VALUES
(1, 'SMK TI BAZMA', 'Ahmad Dahlan, S.Ag', 'Muhamad Dzikri Fauzan, S.Kom', '0811-1144-339', 'Jl. Raya Cikampak Cicadas, RT.1/RW.1, Cicadas, Kec. Ciampea, Kabupaten Bogor, Jawa Barat 16620', 'smktibazma.com', 'Gambar_WhatsApp_2025-08-14_pukul_22.12.14_6c551257-removebg-preview.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int(15) NOT NULL,
  `kode_pengguna` varchar(4) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `kode_pengguna`, `username`, `password`, `level`) VALUES
(1, 'A001', 'Administrator', 'd41d8cd98f00b204e9800998ecf8427e', 'Admin'),
(2, 'A002', 'Admin', 'eca40019c6cc968ddfd83c388c33973f', 'Admin'),
(9, 'M009', '2223006', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(10, 'M010', '2223007', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(11, 'M011', '2223009\r\n', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(12, 'M012', '2223010', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(13, 'M013', '2223011', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(14, 'M014', '2223012', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(15, 'M015', '2223013\r\n', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(16, 'M016', '2223014', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(17, 'M017', '2223015\r\n', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(18, 'M018', '2223016', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(19, 'M019', '2223017', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(20, 'M020', '2223018', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(21, 'M021', '2223019', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(22, 'M022', '2223020', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(24, 'M024', '2223008', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(27, 'M025', '2223009', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(28, 'M026', '2223013', 'e10adc3949ba59abbe56e057f20f883e', 'Siswa'),
(31, 'M023', 'adlifathirayhan@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(33, 'M028', 'infomufizihsanulhaq@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(34, 'M029', 'ahmadtauhidsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(38, 'M033', 'glorymuim@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(41, 'M034', 'attarrifaismktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(42, 'M035', 'dhiaraqiahmadksmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(43, 'M036', 'diandravieridwiasmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(45, 'M038', 'hellofadhilr@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(47, 'M040', 'rantisifayyadh@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(56, 'M045', 'gemiwidodosmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(57, 'M046', 'hafithmuhammadsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(63, 'M051', 'sourcegibran@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(73, 'M061', 'abdoelzmail@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(74, 'M062', 'ibrahimsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(75, 'M063', 'muhammadfaiqsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(76, 'M064', 'muhammadsaefulsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(77, 'M065', 'radidaditiarenaldi@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(83, 'M069', 'sahrulromadhonsmktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(86, 'M072', 'syahbansyahputrati@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(87, 'M073', 'rofidzakismktibazma@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(88, 'A003', '085759089226', '25d55ad283aa400af464c76d713c07ad', 'Admin'),
(89, 'M074', 'hambaallah@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `tbl_absensi_ibfk1_1` (`id_siswa`);

--
-- Indeks untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `kode_admin` (`kode_admin`);

--
-- Indeks untuk tabel `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD PRIMARY KEY (`id_alasan`),
  ADD KEY `tbl_alasan_ibfk1_1` (`id_siswa`);

--
-- Indeks untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`),
  ADD KEY `tbl_kegiatan_ibfk1_1` (`id_siswa`);

--
-- Indeks untuk tabel `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD KEY `kode_mahasiswa` (`kode_siswa`);

--
-- Indeks untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `kode_pengguna` (`kode_pengguna`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  MODIFY `id_absensi` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  MODIFY `id_alasan` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  MODIFY `id_kegiatan` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT untuk tabel `tbl_absen_asrama`
--
ALTER TABLE `tbl_absen_asrama`
  MODIFY `id_absen_asrama` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `tbl_alasan_asrama`
--
ALTER TABLE `tbl_alasan_asrama`
  MODIFY `id_alasan_asrama` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT untuk tabel `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  MODIFY `id_siswa` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD CONSTRAINT `tbl_absensi_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD CONSTRAINT `tbl_admin_ibfk_1` FOREIGN KEY (`kode_admin`) REFERENCES `tbl_user` (`kode_pengguna`);

--
-- Ketidakleluasaan untuk tabel `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD CONSTRAINT `tbl_alasan_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD CONSTRAINT `tbl_kegiatan_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_absen_asrama`
--
ALTER TABLE `tbl_absen_asrama`
  ADD CONSTRAINT `tbl_absen_asrama_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_alasan_asrama`
--
ALTER TABLE `tbl_alasan_asrama`
  ADD CONSTRAINT `tbl_alasan_asrama_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  ADD CONSTRAINT `tbl_siswa_ibfk_1` FOREIGN KEY (`kode_siswa`) REFERENCES `tbl_user` (`kode_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
