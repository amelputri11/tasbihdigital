-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 19, 2025 at 05:30 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tasbih_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasbih_records`
--

CREATE TABLE `tasbih_records` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `count` int NOT NULL,
  `date` date NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `thayyibah_text` varchar(255) DEFAULT 'Subhanallah',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tasbih_records`
--

INSERT INTO `tasbih_records` (`id`, `user_id`, `count`, `date`, `start_time`, `end_time`, `thayyibah_text`, `created_at`, `updated_at`) VALUES
(1, 1, 7, '2025-11-16', '2025-11-16 07:38:48', '2025-11-16 07:38:48', 'Subhanallah', '2025-11-16 07:38:48', '2025-11-16 07:38:48'),
(2, 1, 3, '2025-11-16', '2025-11-16 07:39:11', '2025-11-16 07:44:00', 'Subhanallah', '2025-11-16 07:39:11', '2025-11-16 07:44:00'),
(3, 1, 6, '2025-11-16', '2025-11-16 07:44:08', '2025-11-16 07:47:09', 'Subhanallah', '2025-11-16 07:44:08', '2025-11-16 07:47:09'),
(4, 1, 8, '2025-11-17', '2025-11-17 04:42:34', '2025-11-17 04:42:34', 'Subhanallah', '2025-11-17 04:42:34', '2025-11-17 04:42:34'),
(5, 2, 9, '2025-11-17', '2025-11-17 04:53:46', '2025-11-17 04:54:32', 'Subhanallah', '2025-11-17 04:53:46', '2025-11-17 04:54:32'),
(6, 2, 15, '2025-11-17', '2025-11-17 05:36:29', '2025-11-17 05:38:01', 'Subhanallah', '2025-11-17 05:36:29', '2025-11-17 05:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$tjc3z84rZ564b7Q/CPH0N.K39DQRxp2e.9kpBfltq6DASjGm7i8.e', '2025-11-16 06:45:34'),
(2, 'fiqoh', '$2y$10$fjfcgAhkK8jAKufhicAns.HkjDDa3cBoz3uMmhxxnyGpaBbqQuxYC', '2025-11-17 04:53:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_custom_thayyibah`
--

CREATE TABLE `user_custom_thayyibah` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `thayyibah_text` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_custom_thayyibah`
--

INSERT INTO `user_custom_thayyibah` (`id`, `user_id`, `thayyibah_text`, `created_at`) VALUES
(4, 1, 'allah', '2025-11-19 03:34:45'),
(5, 1, 'subhanallah', '2025-11-19 03:36:36'),
(6, 1, 'dxegfvdz', '2025-11-19 03:57:32'),
(7, 1, 'abc', '2025-11-19 04:29:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasbih_records`
--
ALTER TABLE `tasbih_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_custom_thayyibah`
--
ALTER TABLE `user_custom_thayyibah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_thayyibah` (`user_id`,`thayyibah_text`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasbih_records`
--
ALTER TABLE `tasbih_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_custom_thayyibah`
--
ALTER TABLE `user_custom_thayyibah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasbih_records`
--
ALTER TABLE `tasbih_records`
  ADD CONSTRAINT `tasbih_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_custom_thayyibah`
--
ALTER TABLE `user_custom_thayyibah`
  ADD CONSTRAINT `user_custom_thayyibah_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
