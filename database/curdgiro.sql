-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2024 at 02:48 AM
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
-- Database: `curdgiro`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_giro`
--

CREATE TABLE `data_giro` (
  `id` int(11) NOT NULL,
  `nogiro` varchar(50) DEFAULT NULL,
  `namabank` varchar(100) DEFAULT NULL,
  `ac_number` varchar(50) DEFAULT NULL,
  `statusgiro` varchar(20) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_giro`
--

INSERT INTO `data_giro` (`id`, `nogiro`, `namabank`, `ac_number`, `statusgiro`, `created_by`, `created_at`) VALUES
(1, '0896788', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(2, '896789', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(3, '896790', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(4, '896791', 'BCA', '289999', 'Used', 'system', '2024-09-20 14:35:43'),
(5, '896792', 'BCA', '289999', 'Used', 'system', '2024-09-20 14:35:43'),
(6, '896793', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(7, '896794', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(8, '896795', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(9, '896796', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(10, '896797', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(11, '896798', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(12, '896799', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(13, '896800', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(14, '896801', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(15, '896802', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(16, '896803', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(17, '896804', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(18, '896805', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(19, '896806', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(20, '896807', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(21, '896808', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(22, '896809', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(23, '896810', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(24, '896811', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(25, '896812', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(26, '896813', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(27, '896814', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(28, '896815', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(29, '896816', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43'),
(30, '896817', 'BCA', '289999', 'Unused', 'system', '2024-09-20 14:35:43');

-- --------------------------------------------------------

--
-- Table structure for table `detail_giro`
--

CREATE TABLE `detail_giro` (
  `id` int(11) NOT NULL,
  `nogiro` varchar(20) NOT NULL,
  `tanggal_giro` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `Nominal` int(11) NOT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `bank_penerima` varchar(50) NOT NULL,
  `ac_penerima` int(20) NOT NULL,
  `StatGiro` varchar(15) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_giro`
--

INSERT INTO `detail_giro` (`id`, `nogiro`, `tanggal_giro`, `tanggal_jatuh_tempo`, `Nominal`, `nama_penerima`, `bank_penerima`, `ac_penerima`, `StatGiro`, `created_by`, `created_at`) VALUES
(1, '896791', '2024-09-20', '2024-09-26', 1000000, 'weqwe', '12312', 2147483647, 'Issued', 'system', '2024-09-20 15:02:59'),
(2, '896792', '2024-09-10', '2024-09-18', 1000000, 'sadas', 'dasdsas', 1231232131, 'Issued', 'system', '2024-09-20 15:03:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_giro`
--
ALTER TABLE `data_giro`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_giro`
--
ALTER TABLE `detail_giro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nogiro` (`nogiro`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_giro`
--
ALTER TABLE `data_giro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `detail_giro`
--
ALTER TABLE `detail_giro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
