-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 30, 2025 at 04:51 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pendakian`
--

-- --------------------------------------------------------

--
-- Table structure for table `alat`
--

CREATE TABLE `alat` (
  `alat_id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga_per_hari` decimal(10,2) DEFAULT '0.00',
  `stok` int DEFAULT '0',
  `keterangan` text,
  `gambar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `alat`
--

INSERT INTO `alat` (`alat_id`, `nama`, `harga_per_hari`, `stok`, `keterangan`, `gambar`) VALUES
(1, 'Tenda', 40000.00, 5, 'Tenda Eiger Kapasitas 4 orang', 'https://down-id.img.susercontent.com/file/id-11134207-7qula-ljguzwud17he1b'),
(2, 'Sepatu Gunung', 35000.00, 5, 'Sepatu gunung arei', 'https://th.bing.com/th/id/OIP.uAPmWS23Q-N7X7S3p0V2ZQHaHa?w=177&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3'),
(3, 'Sleeping Bag', 10000.00, 10, 'Sleeping bag mengurangi rasa dingin saat tidur', 'https://m.media-amazon.com/images/I/61TnDPfzDfL._SL1500_.jpg'),
(4, 'Nesting', 10000.00, 10, 'Nesting untuk memasak di area camp', 'https://th.bing.com/th/id/OIP.LT_DnM5KGyL1Zlb6_tFQZQHaHa?w=156&h=150&c=6&o=7&dpr=1.3&pid=1.7&rm=3'),
(5, 'Jaket Gunung', 25000.00, 6, 'Jaket Gunung TNF', 'https://tse4.mm.bing.net/th/id/OIP.LamxhGzdjs223wLWDsq5KgHaJD?pid=ImgDet&w=184&h=225&c=7&dpr=1,3&o=7&rm=3'),
(6, 'Gas portable', 5000.00, 10, '', 'https://siplah-oss.tokoladang.co.id/merchant/50810/product/ikCEI6wdePI3bmN8UHsttJFPcgVikncqINQ57GNn.jpg'),
(8, 'Flysheet', 20000.00, 5, 'FLYSHEET TENDA 4x6 UKURAN 6x4 M/4x3 M/3x2 M CONSINA-PENUTUP TENDA', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSm-Zb7xaKSsAPqYQmah_SSuhgCyEoVDWjjZg&s'),
(9, 'Senter Kepala', 5000.00, 10, 'SENTER KEPALA 25W PUTIH (VHL-0125AW) – Visero', 'https://visero.co.id/wp-content/uploads/2020/05/Jepretan-Layar-2020-05-09-pada-9.12.33-PM-min.png'),
(10, 'Tripod', 15000.00, 2, 'JOBY GripTight PRO TelePod Tripod', 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/HLXF2?wid=1144&hei=1144&fmt=jpeg&qlt=95&.v=WjZMVFZ5dkp1YUFtVnhqak5tY3Rtd2tuVHYzMERCZURia3c5SzJFOTlPaTRVVEhTckxsMndhWmcxNCtWaDRFa1k2Y0lVMnNzaWN4Y2J6SERRd3p4blE'),
(11, 'Tenda Kap 2', 25000.00, 3, 'Tenda Naturehike - Tenda Camping 2P Ultralight Double', 'https://down-id.img.susercontent.com/file/c38422349320607d050005d68a0a65d9'),
(12, 'Set Meja', 15000.00, 3, 'Kursi Meja Lipat Camping', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSBf9k8bvvJmbRu52CJWMSR_HQoRGrP0SJvfp6htRV7w66-znGW2r-RpZi2vOPIL9QQeGw&usqp=CAU'),
(17, 'Sarung tangan', 5000.00, 3, 'SCALDINO 1.0 Eiger', 'https://d1yutv2xslo29o.cloudfront.net/product/variant/photo/910005455_BLACK_1_b93c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_id` int NOT NULL,
  `registrasi_id` int NOT NULL,
  `kode_pembayaran` varchar(50) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `status` enum('Menunggu','Lunas','Gagal') DEFAULT 'Menunggu',
  `tanggal_bayar` datetime DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sania_admin`
--

CREATE TABLE `sania_admin` (
  `id` int NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `level` enum('admin','user','','') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sania_admin`
--

INSERT INTO `sania_admin` (`id`, `email`, `password`, `nama`, `level`) VALUES
(1, 'admin123@gmail.com', 'd6a9a933c8aafc51e55ac0662b6e4d4a', 'admin', 'admin'),
(2, 'user@gmail.com', '24c9e15e52afc47c225b757e7bee1f9d', 'User', 'user'),
(5, 'san@gmail.com', 'd6a9a933c8aafc51e55ac0662b6e4d4a', 'Sania', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `sania_pendaki`
--

CREATE TABLE `sania_pendaki` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `usia` int NOT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sania_pendaki`
--

INSERT INTO `sania_pendaki` (`id`, `user_id`, `nik`, `nama_lengkap`, `usia`, `no_hp`, `alamat`) VALUES
(2, 0, '1234561234561236', 'Sania', 22, '085624158974', 'Medan'),
(23, 0, '0124575312895412', 'Eren', 22, '087412654895', 'jwndejdei'),
(28, 2, '0987654567876543', 'Sania', 22, '085281820951', 'Medan'),
(29, 5, '0546210258786120', 'Ibrahim', 21, '085261607015', 'Medan');

-- --------------------------------------------------------

--
-- Table structure for table `sania_registrasi`
--

CREATE TABLE `sania_registrasi` (
  `id_regis` int NOT NULL,
  `id_pendaki` int NOT NULL,
  `tgl_naik` datetime NOT NULL,
  `tgl_turun` datetime NOT NULL,
  `jumlah_anggota` int NOT NULL,
  `metode_bayar` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'cash',
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_tim` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Pending','Disetujui','Ditolak','Selesai') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sania_registrasi`
--

INSERT INTO `sania_registrasi` (`id_regis`, `id_pendaki`, `tgl_naik`, `tgl_turun`, `jumlah_anggota`, `metode_bayar`, `bukti_bayar`, `foto_tim`, `status`) VALUES
(12, 23, '2026-12-01 06:00:00', '2026-01-02 18:00:00', 6, 'cash', NULL, 'uploads/foto_tim/tim_1763891143_351.jpg', 'Disetujui'),
(17, 28, '2025-12-31 13:00:00', '2026-01-01 18:00:00', 2, 'cash', '', 'uploads/foto_tim/tim_1763951979_3426.jpg', 'Disetujui'),
(18, 23, '2025-11-25 07:00:00', '2025-11-25 18:00:00', 1, 'cash', NULL, NULL, 'Pending'),
(19, 29, '2025-11-25 08:00:00', '2025-11-25 18:00:00', 1, 'cash', '', 'uploads/foto_tim/tim_1764040107_5227.jpg', 'Disetujui');

-- --------------------------------------------------------

--
-- Table structure for table `sewa_alat`
--

CREATE TABLE `sewa_alat` (
  `sewa_id` int NOT NULL,
  `registrasi_id` int DEFAULT NULL,
  `alat_id` int NOT NULL,
  `jumlah` int DEFAULT '1',
  `hari` int DEFAULT '1',
  `harga_total` decimal(10,2) DEFAULT '0.00',
  `status_pembayaran` enum('belum_bayar','menunggu_verifikasi','lunas','ditolak') NOT NULL DEFAULT 'belum_bayar',
  `bukti_pembayaran` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sewa_alat`
--

INSERT INTO `sewa_alat` (`sewa_id`, `registrasi_id`, `alat_id`, `jumlah`, `hari`, `harga_total`, `status_pembayaran`, `bukti_pembayaran`) VALUES
(8, 17, 6, 2, 2, 20000.00, 'lunas', 'bayar_1763963711_6268.jpg'),
(9, 17, 5, 1, 1, 25000.00, 'lunas', 'bayar_1763963711_6268.jpg'),
(10, 17, 2, 1, 2, 70000.00, 'lunas', 'bayar_1763963711_6268.jpg'),
(11, 19, 10, 2, 1, 30000.00, 'belum_bayar', NULL),
(12, 19, 4, 1, 2, 20000.00, 'lunas', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alat`
--
ALTER TABLE `alat`
  ADD PRIMARY KEY (`alat_id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`);

--
-- Indexes for table `sania_admin`
--
ALTER TABLE `sania_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `sania_pendaki`
--
ALTER TABLE `sania_pendaki`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sania_registrasi`
--
ALTER TABLE `sania_registrasi`
  ADD PRIMARY KEY (`id_regis`),
  ADD KEY `fk_id_pendaki` (`id_pendaki`);

--
-- Indexes for table `sewa_alat`
--
ALTER TABLE `sewa_alat`
  ADD PRIMARY KEY (`sewa_id`),
  ADD KEY `alat_id` (`alat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alat`
--
ALTER TABLE `alat`
  MODIFY `alat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `pembayaran_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sania_admin`
--
ALTER TABLE `sania_admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sania_pendaki`
--
ALTER TABLE `sania_pendaki`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `sania_registrasi`
--
ALTER TABLE `sania_registrasi`
  MODIFY `id_regis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `sewa_alat`
--
ALTER TABLE `sewa_alat`
  MODIFY `sewa_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sania_registrasi`
--
ALTER TABLE `sania_registrasi`
  ADD CONSTRAINT `fk_id_pendaki` FOREIGN KEY (`id_pendaki`) REFERENCES `sania_pendaki` (`id`);

--
-- Constraints for table `sewa_alat`
--
ALTER TABLE `sewa_alat`
  ADD CONSTRAINT `sewa_alat_ibfk_1` FOREIGN KEY (`alat_id`) REFERENCES `alat` (`alat_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
