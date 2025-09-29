-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 29, 2025 at 06:17 AM
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
-- Database: `checkin_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

CREATE TABLE `checkins` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `region_id` int NOT NULL,
  `checkin_date` date NOT NULL,
  `session` enum('morning','evening') COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkin_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `distance_meters` decimal(8,2) DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'success',
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`id`, `user_id`, `region_id`, `checkin_date`, `session`, `checkin_time`, `latitude`, `longitude`, `distance_meters`, `ip_address`, `status`, `notes`) VALUES
(7, 3, 1, '2025-09-22', 'evening', '2025-09-22 09:55:00', 10.02373120, 105.79148800, 0.02, '::1', 'success', NULL),
(8, 3, 1, '2025-09-22', 'morning', '2025-09-22 09:56:28', 10.02373100, 105.79148800, 0.00, '::1', 'success', 'Sửa lỗi bởi Manager Ki???n Th??nh (manager) - Ghi chú: fghnmrdhm');

-- --------------------------------------------------------

--
-- Table structure for table `gps_requests`
--

CREATE TABLE `gps_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `region_id` int NOT NULL,
  `request_date` date NOT NULL,
  `distance_meters` decimal(8,2) NOT NULL,
  `gps_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gps_requests`
--

INSERT INTO `gps_requests` (`id`, `user_id`, `region_id`, `request_date`, `distance_meters`, `gps_code`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2025-09-22', 923.57, '3CE172AF', 'pending', NULL, '2025-09-22 08:45:14', '2025-09-22 08:45:14'),
(2, 4, 1, '2025-09-22', 2452.36, '28731B27', 'pending', NULL, '2025-09-22 08:33:31', '2025-09-22 08:33:31');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_meters` int NOT NULL DEFAULT '200',
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `latitude`, `longitude`, `radius_meters`, `address`, `created_at`) VALUES
(1, 'Cần Thơ', 10.02373100, 105.79148800, 200, 'Hải Phương', '2025-09-22 07:50:27'),
(2, 'V??n ph??ng H?? N???i', 21.02850000, 105.85420000, 200, 'H?? N???i, Vi???t Nam', '2025-09-22 07:50:27'),
(3, 'V??n ph??ng TP.HCM', 10.82310000, 106.62970000, 200, 'TP. H??? Ch?? Minh, Vi???t Nam', '2025-09-22 07:50:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator - Full access'),
(2, 'manager', 'Manager - Regional management'),
(3, 'employee', 'Employee - Check-in only');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  `region_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role_id`, `region_id`, `is_active`, `created_at`) VALUES
(1, 'admin', 'admin@system.com', '$2y$10$Y/WjnJoh0XaU4e8.CmR8yOX/J7wxj/Et1Z7JSCwFT7IlpfZGykmaG', 'Administrator', 1, NULL, 1, '2025-09-22 07:50:27'),
(2, 'manager1', 'manager1@system.com', '$2y$10$Y/WjnJoh0XaU4e8.CmR8yOX/J7wxj/Et1Z7JSCwFT7IlpfZGykmaG', 'Quản lý', 2, 1, 1, '2025-09-22 07:50:27'),
(3, 'emp001', 'emp001@system.com', '$2y$10$Y/WjnJoh0XaU4e8.CmR8yOX/J7wxj/Et1Z7JSCwFT7IlpfZGykmaG', 'Nhân viên A', 3, 1, 1, '2025-09-22 07:50:27'),
(4, 'emp002', 'emp002@system.com', '$2y$10$Y/WjnJoh0XaU4e8.CmR8yOX/J7wxj/Et1Z7JSCwFT7IlpfZGykmaG', 'Nhân viên B', 3, 1, 1, '2025-09-22 07:50:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_checkin` (`user_id`,`checkin_date`,`session`),
  ADD KEY `idx_checkins_user_date` (`user_id`,`checkin_date`),
  ADD KEY `idx_checkins_region` (`region_id`);

--
-- Indexes for table `gps_requests`
--
ALTER TABLE `gps_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`user_id`,`request_date`),
  ADD KEY `region_id` (`region_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_region` (`region_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gps_requests`
--
ALTER TABLE `gps_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `checkins_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `gps_requests`
--
ALTER TABLE `gps_requests`
  ADD CONSTRAINT `gps_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `gps_requests_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
