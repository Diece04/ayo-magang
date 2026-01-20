-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 03:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ayo_magang`
--

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `fakultas` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id`, `user_id`, `nama`, `fakultas`) VALUES
(2, 6, 'Teknologi Informasi', 'Politeknik Negeri Padang'),
(3, 10, 'jurusan', 'fakultas'),
(4, 17, 'jurusan', 'jurusan');

-- --------------------------------------------------------

--
-- Table structure for table `kerja_sama`
--

CREATE TABLE `kerja_sama` (
  `id` int(11) NOT NULL,
  `jurusan_id` int(11) NOT NULL,
  `perusahaan_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'proposed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kerja_sama`
--

INSERT INTO `kerja_sama` (`id`, `jurusan_id`, `perusahaan_id`, `status`) VALUES
(5, 4, 2, 'approved'),
(6, 2, 2, 'proposed'),
(7, 2, 3, 'approved'),
(8, 2, 3, 'proposed');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jurusan_id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(16) NOT NULL,
  `status_verifikasi` enum('pending','approved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `nama`, `jurusan_id`, `nim`, `alamat`, `telp`, `status_verifikasi`) VALUES
(2, 7, 'Muhamad Soleh', 2, '2401093018', 'Jl. Jawa Gadut, Limau Manis, Pauh, Padang, Sumatera Barat', '', 'approved'),
(3, 8, 'Halen Razzaq Adean', 2, '2401092012', 'Padang, Sumatera Barat', '', 'approved'),
(4, 12, 'Rizky Zahran Ramadhan', 2, '2401091020', 'Jl. Apapun Itu, Akan Ku Lalui, Sumatera Barat', '', 'pending'),
(5, 13, 'budiman', 2, '2401092099', 'rumah budi', '', 'pending'),
(6, 14, 'Randi', 2, '2401092088', 'rumah randi', '', 'pending'),
(7, 18, 'Intan Apriliani Putri', 2, '2401091031', 'Cemara 1', '', 'approved'),
(8, 19, 'Revan Alif Safero', 2, '2401092022', 'Bukit Tinggi', '', 'pending'),
(9, 20, 'Dinda Fauziah Citra', 2, '2401092023', 'Agam', '2401092022', 'pending'),
(10, 21, 'dummy', 2, '1231231234', 'dummy', '081212341234', 'approved'),
(11, 22, 'dummy2', 2, '124135235', 'dummy2', '13204523452', 'approved'),
(12, 23, 'dummy3', 2, '125323463', 'dummy3', '1421352135', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_magang`
--

CREATE TABLE `pengajuan_magang` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `perusahaan_id` int(11) NOT NULL,
  `status` enum('pengajuan','permohonan','diterima','ditolak') DEFAULT 'pengajuan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_magang`
--

INSERT INTO `pengajuan_magang` (`id`, `mahasiswa_id`, `perusahaan_id`, `status`, `tanggal_pengajuan`) VALUES
(2, 3, 2, 'ditolak', '2025-12-29 19:50:27'),
(3, 2, 3, 'diterima', '2025-12-30 10:10:08'),
(4, 4, 3, 'ditolak', '2025-12-30 10:35:33'),
(7, 5, 2, 'ditolak', '2026-01-02 10:46:09'),
(8, 6, 2, 'ditolak', '2026-01-04 11:09:51'),
(18, 3, 4, 'diterima', '2026-01-19 14:36:49'),
(21, 10, 3, 'diterima', '2026-01-20 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `perusahaan`
--

CREATE TABLE `perusahaan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `NPWP` varchar(15) NOT NULL,
  `tahun_berdiri` varchar(4) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(12) NOT NULL,
  `kode_pos` varchar(5) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perusahaan`
--

INSERT INTO `perusahaan` (`id`, `user_id`, `nama`, `NPWP`, `tahun_berdiri`, `alamat`, `telp`, `kode_pos`, `deskripsi`, `status`) VALUES
(2, 9, 'perusahaan', '123456789123456', '2000', 'alamat', '081265437890', '21512', '', 'approved'),
(3, 11, 'PT. Micew Sejahtera', '24356789', '1990', 'Jl. Gatot Subroto, No.5, Jawa Tengah', '083367893452', '00876', '', 'approved'),
(4, 15, 'perusahaan2', '', '', 'pabrik', '', '', 'ok', 'approved'),
(5, 16, 'perusahaan3', '', '', 'pabrik', '', '', 'ok', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int(25) NOT NULL,
  `mahasiswa_id` int(25) NOT NULL,
  `perusahaan_id` int(25) NOT NULL,
  `no_surat` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `prodi` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `waktu_magang` varchar(50) NOT NULL,
  `perusahaan` varchar(255) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `status` enum('sudah dibuat','belum dibuat') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `mahasiswa_id`, `perusahaan_id`, `no_surat`, `nama`, `nim`, `prodi`, `no_hp`, `waktu_magang`, `perusahaan`, `tanggal_surat`, `status`) VALUES
(1, 12, 3, '232 /DST/PL9/PP.02.10/2026', 'dummy3', '125323463', 'Teknologi Informasi', '1421352135', 'Maret 2026 s/d Mei 2026', 'PT. Micew Sejahtera', '2026-01-19', 'sudah dibuat'),
(2, 10, 3, '232 /DST/PL9/PP.02.10/2026', 'dummy', '1231231234', 'Teknologi Informasi', '38457683465', 'Maret 2026 s/d Mei 2026', 'PT. Micew Sejahtera', '2026-01-20', 'sudah dibuat');

-- --------------------------------------------------------

--
-- Table structure for table `surat_pelaksanaan`
--

CREATE TABLE `surat_pelaksanaan` (
  `id` int(25) NOT NULL,
  `mahasiswa_id` int(25) NOT NULL,
  `perusahaan_id` int(25) NOT NULL,
  `no_surat` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `prodi` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `waktu_magang` varchar(50) NOT NULL,
  `perusahaan` varchar(255) NOT NULL,
  `status` enum('sudah dibuat','belum dibuat') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) NOT NULL CHECK (`role` in (1,2,3,4)),
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `created_at`) VALUES
(5, 'admin', '$2y$10$Q8Ut7ETl3kM9VvrFAuDfKeT0osQMjqWl5VzN7uKZttY57/pmp8G7O', 1, 'admin@admin.com', '2025-12-29 12:01:20'),
(6, 'tekinfo', '$2y$10$VoGYSFfO49TPxdyhK4R2/.0d3Z0arvFf6EiH1nubdVC/HN/FaG1j6', 4, 'teknologiinformasipnp@gmail.com', '2025-12-29 12:24:46'),
(7, 'soleh1', '$2y$10$mhywqUos0Iq5tcUKhwvTKuMN35eEpMTZ5i252MZYqOpfH9ygR3K1W', 2, 'soleh1@gmail.com', '2025-12-29 12:28:05'),
(8, 'halen1', '$2y$10$PETHK9QTmbsD.ebOU6QonuWKJY15osifskTMohsU/XUrzqJtfktKO', 2, 'halen@gmail.com', '2025-12-29 12:35:27'),
(9, 'username', '$2y$10$Ov4IpqEU1O2b3Oj1M/YMg.zAZol1I95fxtxt/EgpIdq6XzaK/TAge', 3, 'perusahaan@gmail.com', '2025-12-29 19:27:05'),
(10, 'testjurusan', '$2y$10$OJPDAzZaKzoEjsLmzz8w5eZF4ZzgIOugb4ynbv0ABhQQesDBFvmPq', 4, 'dummy@gmail.com', '2025-12-29 19:48:45'),
(11, 'micewsejahtera', '$2y$10$29UwlBqiCagVdzZtLUG7juo012oI2PrMtaej7UtkeHVzIUiSvEwRC', 3, 'micewsejahtera@gmail.com', '2025-12-30 10:06:47'),
(12, 'zahran', '$2y$10$i0BccIKhFb3Cz858dfeV.e5uTPRbuE5PC9fxebA6I9MIONSTyrpWu', 2, 'zahran@gmail.com', '2025-12-30 10:34:45'),
(13, 'budi', '$2y$10$fojRJ75TlK8rrEBeOfdj0.XytTxMT94pHR3PYRcMgVuNOCd93IBm6', 2, 'budi@gmail.com', '2026-01-01 20:11:16'),
(14, 'andi', '$2y$10$Bm7MIhXGyWr7tIupx9MvxeplicOk7N3E3K5y5GuudsyS/sfc2OWCq', 2, 'andi@gmail.com', '2026-01-01 20:23:50'),
(15, 'perusahaan', '$2y$10$m4lN.WcARMXjw0Rx0iaiIuq2x826TI0LWXR8rnrrR.FfTSrrrsAA2', 3, 'perusahaan@gmail.com', '2026-01-02 10:33:09'),
(16, 'perusahaan3', '$2y$10$duGVM.4O8lhHB9PBEoc57.b44JkGGw91s7Ei.PjkY35R1obD3dZBO', 3, 'perusahaan3@gmail.com', '2026-01-02 10:40:15'),
(17, 'jurusan2', '$2y$10$VyC9O/1YGF2wJvLxyG9VC.FQUnW1pwaGltOg.Ji.CXneb2M1xW9Bi', 4, 'jurusan@gmail.com', '2026-01-02 10:41:50'),
(18, 'intan', '$2y$10$Kew84J1PvApc2Kp4u.OFz..ZT46WM6XVUzOHczCMcQM.GWD5uklly', 2, 'intan@gmail.com', '2026-01-17 03:07:52'),
(19, 'revan', '$2y$10$PCMpbXdDKxl25ccFemwzB.JQh1JU8UoNNWmQulKsZyjGChQr/UVJe', 2, 'revanalifsafero@gmail.com', '2026-01-19 05:07:52'),
(20, 'citra', '$2y$10$37SNax9UdTZS30vNBJC9FenDkh/3C30fAeOGD6qOCcLvr.by/9A7G', 2, 'citra@gmail.com', '2026-01-19 05:12:12'),
(21, 'dummy', '$2y$10$W2tCmsu3P8r.BZ3qTOhGEer9DKxisEBVdbv7MQdt8vL61bYrFa9W.', 2, 'dummy@gmail.com', '2026-01-19 12:14:17'),
(22, 'dummy2', '$2y$10$1xSF7QWWtGkBwUg9/YsqyuMML9DhgBHtmzZ68XWqc2tMrMs264XVu', 2, 'dummy2@gmail.com', '2026-01-19 16:53:38'),
(23, 'dummy3', '$2y$10$jR6vQiH8MBl.bMGmjAGg.ON4bbiRBl1dd4XXyLBHntFtn6NpVxFs6', 2, 'dummy3@gmail.com', '2026-01-19 16:54:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kerja_sama`
--
ALTER TABLE `kerja_sama`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `perusahaan_id` (`perusahaan_id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `jurusan_id` (`jurusan_id`);

--
-- Indexes for table `pengajuan_magang`
--
ALTER TABLE `pengajuan_magang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `perusahaan_id` (`perusahaan_id`);

--
-- Indexes for table `perusahaan`
--
ALTER TABLE `perusahaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `surat_pelaksanaan`
--
ALTER TABLE `surat_pelaksanaan`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD UNIQUE KEY `perusahaan_id` (`perusahaan_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kerja_sama`
--
ALTER TABLE `kerja_sama`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengajuan_magang`
--
ALTER TABLE `pengajuan_magang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `perusahaan`
--
ALTER TABLE `perusahaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD CONSTRAINT `jurusan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kerja_sama`
--
ALTER TABLE `kerja_sama`
  ADD CONSTRAINT `kerja_sama_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kerja_sama_ibfk_2` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_magang`
--
ALTER TABLE `pengajuan_magang`
  ADD CONSTRAINT `pengajuan_magang_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengajuan_magang_ibfk_2` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `perusahaan`
--
ALTER TABLE `perusahaan`
  ADD CONSTRAINT `perusahaan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `surat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `surat_ibfk_2` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`);

--
-- Constraints for table `surat_pelaksanaan`
--
ALTER TABLE `surat_pelaksanaan`
  ADD CONSTRAINT `surat_pelaksanaan_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `surat_pelaksanaan_ibfk_2` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
