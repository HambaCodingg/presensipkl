-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Agu 2026 pada 05.23
-- Versi server: 8.0.30
-- Versi PHP: 8.3.28

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
  `id_absensi` int NOT NULL,
  `id_siswa` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `longitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_absen_asrama`
--

CREATE TABLE `tbl_absen_asrama` (
  `id_absen_asrama` int NOT NULL,
  `id_siswa` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `longitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_absen_asrama`
--

INSERT INTO `tbl_absen_asrama` (`id_absen_asrama`, `id_siswa`, `status`, `foto`, `latitude`, `longitude`, `waktu`, `tanggal`) VALUES
(1, 26, 1, '20260802_102818_26.png', '-6.57444212982899', '106.68914109527375', '10:28:18', '2026-08-02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int NOT NULL,
  `kode_admin` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nip` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `kode_admin`, `nama`, `nip`, `email`) VALUES
(1, 'A001', 'Muhamad Dzikri Fauzan', '0002', 'muhammaddzikrifauzan20001105@gmail.com'),
(2, 'A002', 'Administrator', '0001', 'administrator@gmail.com'),
(3, 'A003', 'Muhamad Dzikri Fauzan,S.Kom', '22013', 'muhammaddzikrifauzan20001105@gmail.com\r\n'),
(4, 'A004', 'Nur Yusuf Ferdiansyah', '00910', 'yusufftibazma@gmail.com'),
(5, 'A005', 'Ucup', '0000', 'ucup@example.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_alasan`
--

CREATE TABLE `tbl_alasan` (
  `id_alasan` int NOT NULL,
  `id_siswa` int DEFAULT NULL,
  `alasan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_alasan_asrama`
--

CREATE TABLE `tbl_alasan_asrama` (
  `id_alasan_asrama` int NOT NULL,
  `id_siswa` int DEFAULT NULL,
  `alasan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kegiatan`
--

CREATE TABLE `tbl_kegiatan` (
  `id_kegiatan` int NOT NULL,
  `id_siswa` int DEFAULT NULL,
  `kegiatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu_awal` time DEFAULT NULL,
  `waktu_akhir` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_lokasi_siswa`
--

CREATE TABLE `tbl_lokasi_siswa` (
  `id_lokasi` int NOT NULL,
  `id_siswa` int NOT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tbl_lokasi_siswa`
--

INSERT INTO `tbl_lokasi_siswa` (`id_lokasi`, `id_siswa`, `latitude`, `longitude`, `updated_at`) VALUES
(1, 26, '-6.57442536407164', '106.68908663011361', '2026-08-02 11:51:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_setting_absensi`
--

CREATE TABLE `tbl_setting_absensi` (
  `id_waktu` int DEFAULT NULL,
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
  `id_siswa` int NOT NULL,
  `kode_siswa` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perusahaan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jurusan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nis` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mulai_pkl` date DEFAULT NULL,
  `akhir_pkl` date DEFAULT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_siswa`
--

INSERT INTO `tbl_siswa` (`id_siswa`, `kode_siswa`, `nama`, `perusahaan`, `jurusan`, `nis`, `mulai_pkl`, `akhir_pkl`, `alamat`, `no_telp`, `foto`) VALUES
(26, 'M075', 'Nur Yusuf Ferdiansyah', 'Bazma', 'SIJA', '2324019', '2026-07-20', '2026-07-21', 'CIrebon', '083833944848', 'Untitled design (10).png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_site`
--

CREATE TABLE `tbl_site` (
  `id_site` int DEFAULT NULL,
  `nama_instansi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pimpinan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pembimbing` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
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
  `id_user` int NOT NULL,
  `kode_pengguna` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
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
(89, 'M074', 'hambaallah@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Siswa'),
(90, 'A004', 'nyusufadmin', '7582d448120453d0021c61f8cb347858', 'Admin'),
(91, 'M075', 'nyusufansyah', '7582d448120453d0021c61f8cb347858', 'Siswa'),
(92, 'A005', 'ucup', '7582d448120453d0021c61f8cb347858', 'Admin');

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
-- Indeks untuk tabel `tbl_absen_asrama`
--
ALTER TABLE `tbl_absen_asrama`
  ADD PRIMARY KEY (`id_absen_asrama`);

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
-- Indeks untuk tabel `tbl_alasan_asrama`
--
ALTER TABLE `tbl_alasan_asrama`
  ADD PRIMARY KEY (`id_alasan_asrama`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indeks untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`),
  ADD KEY `tbl_kegiatan_ibfk1_1` (`id_siswa`);

--
-- Indeks untuk tabel `tbl_lokasi_siswa`
--
ALTER TABLE `tbl_lokasi_siswa`
  ADD PRIMARY KEY (`id_lokasi`),
  ADD KEY `id_siswa` (`id_siswa`);

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
  MODIFY `id_absensi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT untuk tabel `tbl_absen_asrama`
--
ALTER TABLE `tbl_absen_asrama`
  MODIFY `id_absen_asrama` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  MODIFY `id_alasan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `tbl_alasan_asrama`
--
ALTER TABLE `tbl_alasan_asrama`
  MODIFY `id_alasan_asrama` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  MODIFY `id_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT untuk tabel `tbl_lokasi_siswa`
--
ALTER TABLE `tbl_lokasi_siswa`
  MODIFY `id_lokasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

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
-- Ketidakleluasaan untuk tabel `tbl_alasan_asrama`
--
ALTER TABLE `tbl_alasan_asrama`
  ADD CONSTRAINT `tbl_alasan_asrama_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD CONSTRAINT `tbl_kegiatan_ibfk1_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_lokasi_siswa`
--
ALTER TABLE `tbl_lokasi_siswa`
  ADD CONSTRAINT `tbl_lokasi_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `tbl_siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  ADD CONSTRAINT `tbl_siswa_ibfk_1` FOREIGN KEY (`kode_siswa`) REFERENCES `tbl_user` (`kode_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
