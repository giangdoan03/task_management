-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2024 at 07:23 AM
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
-- Database: `task_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `sub_tasks`
--

CREATE TABLE `sub_tasks` (
  `id` int NOT NULL,
  `task_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_tasks`
--

INSERT INTO `sub_tasks` (`id`, `task_id`, `title`, `description`, `is_completed`) VALUES
(1, 1, 'Sub-task mới', 'Mô tả của sub-task', 1),
(14, 1, 'vvv', 'nnnnn', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assigned_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completion_percentage` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `due_date`, `created_at`, `completion_percentage`) VALUES
(1, 'Task đã cập nhật', 'Mô tả mới', '3', '2024-12-15', '2024-10-18 04:56:44', '50.00'),
(2, 'xxx', 'xxxxx', '1', '2024-10-19', '2024-10-18 08:06:52', '0.00'),
(3, '333', 'ssss', '2', '2024-10-19', '2024-10-18 08:32:28', '0.00'),
(4, 'Task lớn 2', 'Mô tả task lớn', '3', '2024-12-01', '2024-10-18 08:39:38', '0.00'),
(5, 'Task lớn 2', 'Mô tả task lớn', '3', '2024-12-01', '2024-10-18 08:39:49', '0.00'),
(6, 'Task lớn 5', 'Mô tả task lớn 5', '3', '2024-12-01', '2024-10-20 11:34:29', '0.00'),
(7, 'Task lớn 5', 'Mô tả task lớn 5', '3', '2024-12-01', '2024-10-20 11:55:27', '0.00'),
(8, '444', '4444', '3', '2024-10-21', '2024-10-21 14:53:30', '0.00'),
(9, '444', '4444', '3', '2024-10-21', '2024-10-21 14:53:43', '0.00'),
(10, 'Task lớn 5', 'Mô tả task lớn 5', '3', '2024-12-01', '2024-10-21 14:54:15', '0.00'),
(11, '444', '4444', '3', '2024-10-21', '2024-10-21 14:55:24', '0.00'),
(12, 'Task lớn 5', 'Mô tả task lớn 5', '3', '2024-12-01', '2024-10-21 15:03:00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'John Doe', 'john@example.com', '$2y$10$njRspjn42lKkUMezOWpfcuj8tfxOxCaW.0YFW5kEG5Um20jBwcPle', '2024-10-17 07:04:25'),
(2, 'John Doe', 'john@example1.com', '$2y$10$vWbY7Jlhc/GmeLgIJOORf.CFSRgpkTRSvJ2HQO1PjQgh92bgA8/ly', '2024-10-17 09:11:57'),
(3, 'John Doe', 'john@example2.com', '$2y$10$KlU.pVq1Ncvue3W0B.gPNegkthjL/2dMPKTFPDPK5FiJo5VivVQti', '2024-10-20 12:09:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sub_tasks`
--
ALTER TABLE `sub_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sub_tasks`
--
ALTER TABLE `sub_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sub_tasks`
--
ALTER TABLE `sub_tasks`
  ADD CONSTRAINT `sub_tasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
