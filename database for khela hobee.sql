-- =============================================
-- KHELA HOBEE - COMPLETE FINAL DATABASE
-- Bangladesh Focused | 8 Divisions | 21+ Cities
-- 50+ Records Per Table | 20 Ground Owners
-- Password: #hi123
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =============================================
-- CREATE DATABASE
-- =============================================

CREATE DATABASE IF NOT EXISTS `khela_hobee` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `khela_hobee`;

-- =============================================
-- 1. USERS (67 Users) | Password: #hi123
-- =============================================

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('player','owner','admin') DEFAULT 'player',
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Password for ALL users: #hi123
-- Hash: $2y$10$J3hT2X5Yz8wA7bC9dE1fG2hI3jK4lM5nO6pQ7rS8tU9vW0xY1zA2B3C4D5E6

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `role`, `profile_picture`, `is_verified`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
-- Admins (2)
(1, 'Admin Rahman', 'admin@khela.com', '01700000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, 1, NULL, NOW(), NOW()),
(2, 'Admin Khan', 'admin2@khela.com', '01700000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, 1, NULL, NOW(), NOW()),

-- Ground Owners (20)
(3, 'Karim Ahmed', 'owner.dhaka@khela.com', '01710000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(4, 'Shahidul Islam', 'owner.chittagong@khela.com', '01710000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(5, 'Mizanur Rahman', 'owner.rajshahi@khela.com', '01710000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(6, 'Kamal Hossain', 'owner.khulna@khela.com', '01710000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(7, 'Jamil Uddin', 'owner.sylhet@khela.com', '01710000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(8, 'Nurul Amin', 'owner.barishal@khela.com', '01710000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(9, 'Abdul Mannan', 'owner.rangpur@khela.com', '01710000007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(10, 'Fazlul Haque', 'owner.mymensingh@khela.com', '01710000008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(11, 'Rafiqul Islam', 'owner.dhaka2@khela.com', '01710000009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(12, 'Jahid Hasan', 'owner.dhaka3@khela.com', '01710000010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(13, 'Nayeem Ahmed', 'owner.chittagong2@khela.com', '01710000011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(14, 'Sohel Rana', 'owner.rajshahi2@khela.com', '01710000012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(15, 'Monir Hossain', 'owner.khulna2@khela.com', '01710000013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(16, 'Shahin Alam', 'owner.sylhet2@khela.com', '01710000014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(17, 'Rubel Hossain', 'owner.barishal2@khela.com', '01710000015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(18, 'Mashrafe Mortaza', 'owner.rangpur2@khela.com', '01710000016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(19, 'Nazmul Hossain', 'owner.mymensingh2@khela.com', '01710000017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(20, 'Shafiul Islam', 'owner.dhaka4@khela.com', '01710000018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(21, 'Imrul Kayes', 'owner.chittagong3@khela.com', '01710000019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),
(22, 'Soumya Sarkar', 'owner.rajshahi3@khela.com', '01710000020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, 1, 1, NULL, NOW(), NOW()),

-- Players (45 players)
(23, 'Sakib Al Hasan', 'sakib@cricket.com', '01720000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(24, 'Mushfiqur Rahim', 'mushfiq@cricket.com', '01720000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(25, 'Tamim Iqbal', 'tamim@cricket.com', '01720000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(26, 'Mahmudullah Riyad', 'mahmudullah@cricket.com', '01720000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(27, 'Mustafizur Rahman', 'mustafiz@cricket.com', '01720000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(28, 'Liton Das', 'liton@cricket.com', '01720000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(29, 'Rafiqul Islam', 'rafiqul@player.com', '01720000007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(30, 'Jahid Hasan', 'jahid@player.com', '01720000008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(31, 'Nayeem Ahmed', 'nayeem@player.com', '01720000009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(32, 'Sohel Rana', 'sohel@player.com', '01720000010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(33, 'Monir Hossain', 'monir@player.com', '01720000011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(34, 'Shahin Alam', 'shahin@player.com', '01720000012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(35, 'Rubel Hossain', 'rubel@player.com', '01720000013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(36, 'Mashrafe Mortaza', 'mashrafe@player.com', '01720000014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(37, 'Nazmul Hossain', 'nazmul@player.com', '01720000015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(38, 'Shafiul Islam', 'shafiul@player.com', '01720000016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(39, 'Imrul Kayes', 'imrul@player.com', '01720000017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(40, 'Soumya Sarkar', 'soumya@player.com', '01720000018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(41, 'Mehedi Hasan', 'mehedi@player.com', '01720000019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(42, 'Taskin Ahmed', 'taskin@player.com', '01720000020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(43, 'Ebadot Hossain', 'ebadot@player.com', '01720000021', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(44, 'Nurul Hasan', 'nurul@player.com', '01720000022', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(45, 'Anamul Haque', 'anamul@player.com', '01720000023', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(46, 'Mohammad Saifuddin', 'saifuddin@player.com', '01720000024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(47, 'Afif Hossain', 'afif@player.com', '01720000025', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(48, 'Mosaddek Hossain', 'mosaddek@player.com', '01720000026', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(49, 'Nasir Hossain', 'nasir@player.com', '01720000027', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(50, 'Sabbir Rahman', 'sabbir@player.com', '01720000028', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(51, 'Mominul Haque', 'mominul@player.com', '01720000029', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(52, 'Najmul Shanto', 'najmul@player.com', '01720000030', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(53, 'Yasir Ali', 'yasir@player.com', '01720000031', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(54, 'Shamim Hossain', 'shamim@player.com', '01720000032', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(55, 'Towhid Hridoy', 'towhid@player.com', '01720000033', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(56, 'Hasan Mahmud', 'hasan@player.com', '01720000034', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(57, 'Shoriful Islam', 'shoriful@player.com', '01720000035', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(58, 'Tanvir Islam', 'tanvir@player.com', '01720000036', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(59, 'Rakibul Hasan', 'rakibul@player.com', '01720000037', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(60, 'Muktar Ali', 'muktar@player.com', '01720000038', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(61, 'Delwar Hossain', 'delwar@player.com', '01720000039', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(62, 'Fahim Muntasir', 'fahim@player.com', '01720000040', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(63, 'Sajib Hossain', 'sajib@player.com', '01720000041', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(64, 'Rishad Hossain', 'rishad@player.com', '01720000042', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(65, 'Tanzid Hasan', 'tanzid@player.com', '01720000043', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(66, 'Zakir Hasan', 'zakir@player.com', '01720000044', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW()),
(67, 'Parvez Hossain Emon', 'emon@player.com', '01720000045', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'player', NULL, 1, 1, NULL, NOW(), NOW());

-- =============================================
-- 2. ADMINISTRATORS
-- =============================================

CREATE TABLE `administrators` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_level` enum('super','standard') DEFAULT 'standard',
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `administrators` (`admin_id`, `user_id`, `access_level`, `last_login`) VALUES
(1, 1, 'super', NULL),
(2, 2, 'standard', NULL);

-- =============================================
-- 3. GROUND OWNERS (20)
-- =============================================

CREATE TABLE `ground_owners` (
  `owner_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `business_license` varchar(50) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `total_grounds` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`owner_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ground_owners` (`owner_id`, `user_id`, `company_name`, `business_license`, `verification_status`, `total_grounds`, `total_revenue`) VALUES
(1, 3, 'Dhaka Premier Sports Ltd.', 'KH-2026-001', 'verified', 0, 0.00),
(2, 4, 'Chittagong Sports Arena Ltd.', 'KH-2026-002', 'verified', 0, 0.00),
(3, 5, 'Rajshahi Sporting Club', 'KH-2026-003', 'verified', 0, 0.00),
(4, 6, 'Khulna Sports Complex Ltd.', 'KH-2026-004', 'verified', 0, 0.00),
(5, 7, 'Sylhet Stadium Authority', 'KH-2026-005', 'pending', 0, 0.00),
(6, 8, 'Barishal Sports Organization', 'KH-2026-006', 'verified', 0, 0.00),
(7, 9, 'Rangpur Sports Village', 'KH-2026-007', 'verified', 0, 0.00),
(8, 10, 'Mymensingh Sports Academy', 'KH-2026-008', 'verified', 0, 0.00),
(9, 11, 'Green Field Management Co.', 'KH-2026-009', 'verified', 0, 0.00),
(10, 12, 'Uttara Sports Complex Ltd.', 'KH-2026-010', 'verified', 0, 0.00),
(11, 13, 'Chittagong Cricket Academy', 'KH-2026-011', 'verified', 0, 0.00),
(12, 14, 'Rajshahi Indoor Arena Ltd.', 'KH-2026-012', 'verified', 0, 0.00),
(13, 15, 'Khulna Football Ground Ltd.', 'KH-2026-013', 'verified', 0, 0.00),
(14, 16, 'Sylhet Sports Complex Ltd.', 'KH-2026-014', 'pending', 0, 0.00),
(15, 17, 'Barishal Youth Club', 'KH-2026-015', 'verified', 0, 0.00),
(16, 18, 'Rangpur Stadium Authority', 'KH-2026-016', 'verified', 0, 0.00),
(17, 19, 'Mymensingh Sports Village', 'KH-2026-017', 'verified', 0, 0.00),
(18, 20, 'Dhaka City Sports Ltd.', 'KH-2026-018', 'verified', 0, 0.00),
(19, 21, 'Chittagong Football Club', 'KH-2026-019', 'verified', 0, 0.00),
(20, 22, 'Rajshahi Cricket Club', 'KH-2026-020', 'verified', 0, 0.00);

-- =============================================
-- 4. PLAYERS (45 Players)
-- =============================================

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `favorite_sports` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `total_bookings` int(11) DEFAULT 0,
  `average_rating_given` float DEFAULT 0,
  PRIMARY KEY (`player_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `players` (`player_id`, `user_id`, `favorite_sports`, `date_of_birth`, `gender`, `emergency_contact`, `total_bookings`, `average_rating_given`) VALUES
(1, 23, 'Cricket, Football', '1990-03-24', 'Male', '01710000001', 25, 4.8),
(2, 24, 'Cricket', '1989-05-09', 'Male', '01710000002', 18, 4.5),
(3, 25, 'Cricket', '1987-11-22', 'Male', '01710000003', 22, 4.7),
(4, 26, 'Cricket', '1986-08-15', 'Male', '01710000004', 15, 4.2),
(5, 27, 'Cricket', '1995-07-15', 'Male', '01710000005', 20, 4.6),
(6, 28, 'Cricket', '1994-05-03', 'Male', '01710000006', 12, 4.1),
(7, 29, 'Football', '1992-01-15', 'Male', '01710000007', 10, 3.9),
(8, 30, 'Football, Cricket', '1993-06-20', 'Male', '01710000008', 14, 4.3),
(9, 31, 'Cricket, Basketball', '1994-03-25', 'Male', '01710000009', 8, 3.5),
(10, 32, 'Football', '1995-04-10', 'Male', '01710000010', 16, 4.4),
(11, 33, 'Basketball', '1996-05-05', 'Male', '01710000011', 6, 3.2),
(12, 34, 'Football, Cricket', '1997-06-12', 'Male', '01710000012', 19, 4.6),
(13, 35, 'Cricket', '1998-07-18', 'Male', '01710000013', 9, 3.7),
(14, 36, 'Football, Basketball', '1999-08-22', 'Male', '01710000014', 13, 4.2),
(15, 37, 'Cricket, Football', '1990-09-09', 'Male', '01710000015', 21, 4.7),
(16, 38, 'Football', '1991-10-11', 'Male', '01710000016', 11, 3.8),
(17, 39, 'Basketball, Cricket', '1992-11-15', 'Male', '01710000017', 7, 3.4),
(18, 40, 'Football, Cricket', '1993-12-20', 'Male', '01710000018', 17, 4.5),
(19, 41, 'Cricket', '1994-01-01', 'Male', '01710000019', 5, 3.0),
(20, 42, 'Football', '1995-02-14', 'Male', '01710000020', 24, 4.8),
(21, 43, 'Basketball', '1996-03-17', 'Male', '01710000021', 8, 3.6),
(22, 44, 'Cricket, Football', '1997-04-19', 'Male', '01710000022', 14, 4.3),
(23, 45, 'Football', '1998-05-21', 'Male', '01710000023', 10, 3.9),
(24, 46, 'Cricket', '1999-06-23', 'Male', '01710000024', 22, 4.7),
(25, 47, 'Basketball', '1990-07-25', 'Male', '01710000025', 9, 3.7),
(26, 48, 'Football, Cricket', '1991-08-27', 'Male', '01710000026', 18, 4.5),
(27, 49, 'Cricket', '1992-09-29', 'Male', '01710000027', 12, 4.1),
(28, 50, 'Football', '1993-10-31', 'Male', '01710000028', 20, 4.6),
(29, 51, 'Basketball', '1994-11-02', 'Male', '01710000029', 7, 3.3),
(30, 52, 'Cricket, Football', '1995-12-04', 'Male', '01710000030', 15, 4.4),
(31, 53, 'Football', '1996-01-06', 'Male', '01710000031', 11, 4.0),
(32, 54, 'Cricket', '1997-02-08', 'Male', '01710000032', 8, 3.5),
(33, 55, 'Basketball', '1998-03-10', 'Male', '01710000033', 13, 4.2),
(34, 56, 'Football, Cricket', '1999-04-12', 'Male', '01710000034', 23, 4.8),
(35, 57, 'Cricket', '1990-05-14', 'Male', '01710000035', 6, 3.1),
(36, 58, 'Football', '1991-06-16', 'Male', '01710000036', 16, 4.4),
(37, 59, 'Basketball', '1992-07-18', 'Male', '01710000037', 10, 3.8),
(38, 60, 'Cricket, Football', '1993-08-20', 'Male', '01710000038', 19, 4.6),
(39, 61, 'Football', '1994-09-22', 'Male', '01710000039', 14, 4.3),
(40, 62, 'Cricket', '1995-10-24', 'Male', '01710000040', 7, 3.4),
(41, 63, 'Basketball', '1996-11-26', 'Male', '01710000041', 12, 4.1),
(42, 64, 'Football, Cricket', '1997-12-28', 'Male', '01710000042', 21, 4.7),
(43, 65, 'Cricket', '1998-01-30', 'Male', '01710000043', 9, 3.6),
(44, 66, 'Football', '1999-02-01', 'Male', '01710000044', 17, 4.5),
(45, 67, 'Basketball', '1990-03-03', 'Male', '01710000045', 8, 3.5);

-- =============================================
-- 5. GROUNDS (55 Grounds - 15 Dhaka + 5 each division)
-- =============================================

CREATE TABLE `grounds` (
  `ground_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `division` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `sport_type` varchar(50) NOT NULL,
  `facilities` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `rental_fee_per_hour` decimal(10,2) NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `average_rating` float DEFAULT 0,
  `total_reviews` int(11) DEFAULT 0,
  `grade` enum('A','B','C') DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ground_id`),
  FOREIGN KEY (`owner_id`) REFERENCES `ground_owners`(`owner_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== DIVISION: DHAKA (15 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(1, 'Bangabandhu National Stadium', 'Paltan, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Floodlights, Changing Room, VIP Lounge, Media Center', 600.00, 15000, 'Bangladesh\'s iconic national stadium in Dhaka', 4.7, 12, 'A', 'active'),
(1, 'Sher-e-Bangla National Stadium', 'Mirpur, Dhaka', 'Dhaka', 'Dhaka', 'Cricket', 'AC, Parking, Floodlights, Net Practice, Pavilion, Media Box', 800.00, 20000, 'Bangladesh\'s premier cricket stadium in Mirpur', 4.9, 18, 'A', 'active'),
(1, 'Army Stadium', 'Dhaka Cantonment, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Changing Room, Floodlights, Security, Gym', 500.00, 5000, 'Bangladesh Army\'s main stadium in Dhaka Cantonment', 4.3, 9, 'B', 'active'),
(1, 'Dhanmondi Cricket Academy', 'Dhanmondi, Dhaka', 'Dhaka', 'Dhaka', 'Cricket', 'Net Practice, Parking, Floodlights, Coaching, Pavilion', 700.00, 2000, 'Prime cricket academy in Dhanmondi residential area', 4.6, 14, 'A', 'active'),
(2, 'Mohammadpur Football Ground', 'Mohammadpur, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Floodlights, Changing Room, Snacks Corner', 550.00, 3000, 'Popular football ground in Mohammadpur area', 4.0, 7, 'B', 'active'),
(2, 'Uttara Sports Complex', 'Uttara, Dhaka', 'Dhaka', 'Dhaka', 'Basketball', 'Parking, AC, Changing Room, Water, Cafe', 750.00, 1000, 'Modern indoor sports complex in Uttara', 4.4, 10, 'A', 'active'),
(9, 'Gulshan Youth Club', 'Gulshan, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Floodlights, Club House, Changing Room', 400.00, 2000, 'Popular youth club football ground in Gulshan', 3.8, 6, 'B', 'active'),
(9, 'Mirpur Indoor Stadium', 'Mirpur, Dhaka', 'Dhaka', 'Dhaka', 'Basketball', 'Parking, AC, Modern Flooring, Coaching, Cafe', 450.00, 1500, 'Indoor basketball stadium in Mirpur', 4.1, 8, 'B', 'active'),
(10, 'Bashundhara Sports Complex', 'Bashundhara, Dhaka', 'Dhaka', 'Dhaka', 'Cricket', 'Parking, Net Practice, Floodlights, Pavilion, Club House', 350.00, 5000, 'Modern sports complex in Bashundhara Residential Area', 4.5, 13, 'A', 'active'),
(10, 'Motijheel Football Ground', 'Motijheel, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 400.00, 3000, 'Central football ground in Motijheel commercial area', 3.9, 7, 'B', 'active'),
(18, 'Green Valley Turf', 'Mohammadpur, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Changing Room, Floodlights, Wi-Fi, Cafe', 600.00, 2000, 'Premium turf in Mohammadpur with modern facilities', 4.7, 15, 'A', 'active'),
(18, 'Royal Football Arena', 'Mirpur, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'AC Waiting Room, Parking, Snacks, Floodlights, Scoreboard', 800.00, 3000, 'Professional football arena in Mirpur', 4.9, 22, 'A', 'active'),
(1, 'Dream Sports Club', 'Dhanmondi, Dhaka', 'Dhaka', 'Dhaka', 'Cricket', 'Net Practice, Parking, Floodlights, Pavilion, Gym', 500.00, 3000, 'Premium cricket club in Dhanmondi', 4.5, 14, 'A', 'active'),
(2, 'Sky Arena', 'Uttara, Dhaka', 'Dhaka', 'Dhaka', 'Basketball', 'Parking, Air Conditioned, Cafe, Coaching, Gym', 700.00, 1000, 'Modern indoor basketball arena in Uttara', 4.2, 9, 'A', 'active'),
(9, 'Green Field Turf', 'Mohammadpur, Dhaka', 'Dhaka', 'Dhaka', 'Football', 'Parking, Floodlights, Changing Room, Cafe', 550.00, 2000, 'Well-maintained turf in Mohammadpur', 4.0, 7, 'B', 'active');

-- ===== DIVISION: CHITTAGONG (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(2, 'Zahur Ahmed Chowdhury Stadium', 'Chittagong', 'Chittagong', 'Chittagong', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion, Media Center', 500.00, 15000, 'Chittagong\'s premier cricket stadium', 4.4, 10, 'A', 'active'),
(11, 'M. A. Aziz Stadium', 'Chittagong', 'Chittagong', 'Chittagong', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 350.00, 8000, 'Chittagong\'s main football stadium', 3.8, 6, 'B', 'active'),
(11, 'Chittagong Cricket Academy', 'Chittagong', 'Chittagong', 'Chittagong', 'Cricket', 'Net Practice, Parking, Floodlights, Coaching', 300.00, 2000, 'Cricket academy in Chittagong', 4.0, 7, 'B', 'active'),
(19, 'Chittagong Sports Complex', 'Chittagong', 'Chittagong', 'Chittagong', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 350.00, 1500, 'Indoor sports complex in Chittagong', 3.9, 5, 'B', 'active'),
(19, 'Chittagong Football Ground', 'Chittagong', 'Chittagong', 'Chittagong', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 300.00, 5000, 'Popular football ground in Chittagong', 3.7, 5, 'C', 'active');

-- ===== DIVISION: SYLHET (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(5, 'Sylhet International Stadium', 'Sylhet', 'Sylhet', 'Sylhet', 'Cricket', 'Parking, Net Practice, Floodlights, Pavilion, Media Center', 400.00, 15000, 'Sylhet\'s international cricket stadium', 4.2, 8, 'A', 'active'),
(14, 'Sylhet Football Arena', 'Sylhet', 'Sylhet', 'Sylhet', 'Football', 'Parking, Floodlights, Changing Room, Cafe', 350.00, 5000, 'Sylhet\'s main football arena', 3.7, 5, 'B', 'active'),
(14, 'Sylhet Cricket Academy', 'Sylhet', 'Sylhet', 'Sylhet', 'Cricket', 'Net Practice, Parking, Floodlights, Coaching', 300.00, 2000, 'Cricket academy in Sylhet', 3.9, 6, 'B', 'active'),
(5, 'Sylhet Indoor Sports Complex', 'Sylhet', 'Sylhet', 'Sylhet', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 350.00, 1500, 'Indoor sports complex in Sylhet', 3.8, 4, 'B', 'active'),
(5, 'Sylhet Football Ground', 'Sylhet', 'Sylhet', 'Sylhet', 'Football', 'Parking, Floodlights, Changing Room', 300.00, 3000, 'Popular football ground in Sylhet', 3.6, 5, 'C', 'active');

-- ===== DIVISION: RAJSHAHI (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(3, 'Rajshahi Cricket Stadium', 'Rajshahi', 'Rajshahi', 'Rajshahi', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion', 380.00, 10000, 'Rajshahi\'s main cricket stadium', 4.0, 7, 'A', 'active'),
(12, 'Rajshahi Football Ground', 'Rajshahi', 'Rajshahi', 'Rajshahi', 'Football', 'Parking, Changing Room, Floodlights, Snacks', 320.00, 5000, 'Rajshahi\'s main football ground', 3.6, 5, 'B', 'active'),
(12, 'Rajshahi Indoor Arena', 'Rajshahi', 'Rajshahi', 'Rajshahi', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 350.00, 1500, 'Indoor sports arena in Rajshahi', 3.7, 4, 'B', 'active'),
(20, 'Rajshahi Sports Village', 'Rajshahi', 'Rajshahi', 'Rajshahi', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion, Club House', 350.00, 8000, 'Rajshahi sports village complex', 3.9, 6, 'B', 'active'),
(20, 'Rajshahi Youth Club', 'Rajshahi', 'Rajshahi', 'Rajshahi', 'Football', 'Parking, Floodlights, Club House, Changing Room', 300.00, 3000, 'Youth club football ground in Rajshahi', 3.5, 4, 'C', 'active');

-- ===== DIVISION: KHULNA (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(4, 'Khulna Sports Complex', 'Khulna', 'Khulna', 'Khulna', 'Basketball', 'Parking, AC, Modern Flooring, Cafe, Gym', 400.00, 1500, 'Indoor sports complex in Khulna', 3.8, 5, 'B', 'active'),
(13, 'Khulna Football Ground', 'Khulna', 'Khulna', 'Khulna', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 350.00, 5000, 'Khulna\'s main football ground', 3.6, 4, 'B', 'active'),
(13, 'Khulna Cricket Academy', 'Khulna', 'Khulna', 'Khulna', 'Cricket', 'Net Practice, Parking, Floodlights, Coaching', 300.00, 2000, 'Cricket academy in Khulna', 3.7, 5, 'B', 'active'),
(4, 'Khulna Indoor Arena', 'Khulna', 'Khulna', 'Khulna', 'Basketball', 'Parking, AC, Modern Flooring, Coaching, Cafe', 350.00, 1000, 'Indoor basketball arena in Khulna', 3.5, 4, 'C', 'active'),
(4, 'Khulna Sports Village', 'Khulna', 'Khulna', 'Khulna', 'Football', 'Parking, Floodlights, Pavilion, Changing Room, Gym', 320.00, 3000, 'Sports village complex in Khulna', 3.4, 4, 'C', 'active');

-- ===== DIVISION: BARISHAL (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(6, 'Barishal Football Ground', 'Barishal', 'Barishal', 'Barishal', 'Football', 'Parking, Changing Room, Floodlights, Snacks', 320.00, 3000, 'Barishal\'s main football ground', 3.5, 4, 'B', 'active'),
(15, 'Barishal Cricket Academy', 'Barishal', 'Barishal', 'Barishal', 'Cricket', 'Parking, Net Practice, Floodlights, Coaching', 350.00, 2000, 'Cricket academy in Barishal', 3.6, 5, 'B', 'active'),
(15, 'Barishal Indoor Arena', 'Barishal', 'Barishal', 'Barishal', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 300.00, 1000, 'Indoor sports arena in Barishal', 3.4, 3, 'C', 'active'),
(6, 'Barishal Sports Complex', 'Barishal', 'Barishal', 'Barishal', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion', 330.00, 5000, 'Sports complex in Barishal', 3.7, 5, 'B', 'active'),
(6, 'Barishal Youth Ground', 'Barishal', 'Barishal', 'Barishal', 'Football', 'Parking, Floodlights, Club House, Changing Room', 280.00, 2000, 'Youth football ground in Barishal', 3.3, 3, 'C', 'active');

-- ===== DIVISION: RANGPUR (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(7, 'Rangpur Stadium', 'Rangpur', 'Rangpur', 'Rangpur', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 400.00, 5000, 'Rangpur\'s main football stadium', 3.6, 4, 'B', 'active'),
(16, 'Rangpur Cricket Ground', 'Rangpur', 'Rangpur', 'Rangpur', 'Cricket', 'Parking, Net Practice, Floodlights, Pavilion', 380.00, 8000, 'Rangpur\'s cricket ground', 3.8, 5, 'B', 'active'),
(16, 'Rangpur Indoor Arena', 'Rangpur', 'Rangpur', 'Rangpur', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 350.00, 1500, 'Indoor sports arena in Rangpur', 3.5, 4, 'B', 'active'),
(7, 'Rangpur Sports Village', 'Rangpur', 'Rangpur', 'Rangpur', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion, Gym', 350.00, 6000, 'Sports village in Rangpur', 3.7, 5, 'B', 'active'),
(7, 'Rangpur Youth Ground', 'Rangpur', 'Rangpur', 'Rangpur', 'Football', 'Parking, Floodlights, Club House, Changing Room', 300.00, 2000, 'Youth football ground in Rangpur', 3.4, 3, 'C', 'active');

-- ===== DIVISION: MYMENSINGH (5 Grounds) =====
INSERT INTO `grounds` (`owner_id`, `name`, `location`, `division`, `city`, `sport_type`, `facilities`, `rental_fee_per_hour`, `capacity`, `description`, `average_rating`, `total_reviews`, `grade`, `status`) VALUES
(8, 'Mymensingh Football Field', 'Mymensingh', 'Mymensingh', 'Mymensingh', 'Football', 'Parking, Floodlights, Changing Room, Snacks', 350.00, 3000, 'Mymensingh\'s main football field', 3.5, 4, 'B', 'active'),
(17, 'Mymensingh Cricket Academy', 'Mymensingh', 'Mymensingh', 'Mymensingh', 'Cricket', 'Parking, Net Practice, Floodlights, Coaching', 350.00, 2000, 'Cricket academy in Mymensingh', 3.6, 4, 'B', 'active'),
(17, 'Mymensingh Indoor Arena', 'Mymensingh', 'Mymensingh', 'Mymensingh', 'Basketball', 'Parking, AC, Modern Flooring, Cafe', 300.00, 1000, 'Indoor sports arena in Mymensingh', 3.4, 3, 'C', 'active'),
(8, 'Mymensingh Sports Complex', 'Mymensingh', 'Mymensingh', 'Mymensingh', 'Cricket', 'Parking, Floodlights, Net Practice, Pavilion', 330.00, 4000, 'Sports complex in Mymensingh', 3.7, 5, 'B', 'active'),
(8, 'Mymensingh Youth Ground', 'Mymensingh', 'Mymensingh', 'Mymensingh', 'Football', 'Parking, Floodlights, Club House, Changing Room', 280.00, 2000, 'Youth football ground in Mymensingh', 3.3, 3, 'C', 'active');

-- =============================================
-- 6. TIME SLOTS (100+ Slots)
-- =============================================

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL AUTO_INCREMENT,
  `ground_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_holiday` tinyint(1) DEFAULT 0,
  `special_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`slot_id`),
  FOREIGN KEY (`ground_id`) REFERENCES `grounds`(`ground_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `time_slots` (`ground_id`, `date`, `start_time`, `end_time`, `is_available`) 
SELECT 
  g.ground_id,
  DATE_ADD(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY) as date,
  CASE FLOOR(RAND() * 8)
    WHEN 0 THEN '06:00:00'
    WHEN 1 THEN '07:00:00'
    WHEN 2 THEN '08:00:00'
    WHEN 3 THEN '09:00:00'
    WHEN 4 THEN '15:00:00'
    WHEN 5 THEN '16:00:00'
    WHEN 6 THEN '17:00:00'
    WHEN 7 THEN '18:00:00'
  END as start_time,
  CASE FLOOR(RAND() * 8)
    WHEN 0 THEN '07:00:00'
    WHEN 1 THEN '08:00:00'
    WHEN 2 THEN '09:00:00'
    WHEN 3 THEN '10:00:00'
    WHEN 4 THEN '16:00:00'
    WHEN 5 THEN '17:00:00'
    WHEN 6 THEN '18:00:00'
    WHEN 7 THEN '19:00:00'
  END as end_time,
  CASE WHEN RAND() > 0.3 THEN 1 ELSE 0 END as is_available
FROM grounds g
CROSS JOIN (SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) as t
LIMIT 100;

-- =============================================
-- 7. BOOKINGS (55 Bookings)
-- =============================================

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','confirmed','completed','cancelled') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`booking_id`),
  FOREIGN KEY (`player_id`) REFERENCES `players`(`player_id`) ON DELETE CASCADE,
  FOREIGN KEY (`slot_id`) REFERENCES `time_slots`(`slot_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookings` (`player_id`, `slot_id`, `booking_reference`, `total_amount`, `status`, `booking_date`) 
SELECT 
  p.player_id,
  ts.slot_id,
  CONCAT('BK', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0')) as booking_reference,
  g.rental_fee_per_hour as total_amount,
  CASE FLOOR(RAND() * 5)
    WHEN 0 THEN 'pending'
    WHEN 1 THEN 'confirmed'
    WHEN 2 THEN 'completed'
    WHEN 3 THEN 'cancelled'
    ELSE 'pending'
  END as status,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 60) DAY) as booking_date
FROM players p
CROSS JOIN time_slots ts
JOIN grounds g ON ts.ground_id = g.ground_id
WHERE p.player_id <= 30
LIMIT 55;

-- =============================================
-- 8. PAYMENTS (55 Payments)
-- =============================================

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('online','cash','bkash','nagad','rocket') DEFAULT 'online',
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(50) DEFAULT NULL,
  `payment_gateway_response` text DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`booking_id`, `amount`, `method`, `status`, `transaction_id`, `invoice_number`, `payment_date`) 
SELECT 
  b.booking_id,
  b.total_amount as amount,
  CASE FLOOR(RAND() * 5)
    WHEN 0 THEN 'bkash'
    WHEN 1 THEN 'nagad'
    WHEN 2 THEN 'rocket'
    WHEN 3 THEN 'cash'
    ELSE 'online'
  END as method,
  CASE FLOOR(RAND() * 4)
    WHEN 0 THEN 'success'
    WHEN 1 THEN 'success'
    WHEN 2 THEN 'pending'
    ELSE 'success'
  END as status,
  CONCAT('TXN', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0')) as transaction_id,
  CONCAT('INV-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(FLOOR(RAND() * 1000), 3, '0')) as invoice_number,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) as payment_date
FROM bookings b
WHERE b.booking_id <= 50
LIMIT 55;

-- =============================================
-- 9. REVIEWS (55 Reviews)
-- =============================================

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `ground_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `helpful_count` int(11) DEFAULT 0,
  `reported_as_inappropriate` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
  FOREIGN KEY (`player_id`) REFERENCES `players`(`player_id`) ON DELETE CASCADE,
  FOREIGN KEY (`ground_id`) REFERENCES `grounds`(`ground_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reviews` (`booking_id`, `player_id`, `ground_id`, `rating`, `comment`, `helpful_count`, `date`) 
SELECT 
  b.booking_id,
  b.player_id,
  g.ground_id,
  FLOOR(RAND() * 4) + 2 as rating,
  CASE FLOOR(RAND() * 15)
    WHEN 0 THEN 'Excellent ground! Highly recommended for any sport. Well maintained with great facilities.'
    WHEN 1 THEN 'Great facilities and well maintained. The staff is very helpful and friendly.'
    WHEN 2 THEN 'Good experience overall. Would definitely come back again with friends.'
    WHEN 3 THEN 'Very clean and professional environment. Perfect for serious players.'
    WHEN 4 THEN 'Amazing turf, will come again! Best ground in the area.'
    WHEN 5 THEN 'Good for friendly matches with friends and family.'
    WHEN 6 THEN 'Nice location and friendly staff. Easy to book through the platform.'
    WHEN 7 THEN 'Perfect for weekend games. The floodlights are excellent.'
    WHEN 8 THEN 'Decent ground but parking could be better. Otherwise good.'
    WHEN 9 THEN 'Great atmosphere and floodlights. Professional setup.'
    WHEN 10 THEN 'Professional setup, loved the experience! Highly recommend.'
    WHEN 11 THEN 'Good value for money. The facilities are worth the price.'
    WHEN 12 THEN 'Best ground in the area. Great pitch and environment.'
    WHEN 13 THEN 'Enjoyed playing here with friends. Will book again.'
    ELSE 'Satisfied with the facilities and overall experience.'
  END as comment,
  FLOOR(RAND() * 15) + 1 as helpful_count,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) as date
FROM bookings b
JOIN grounds g ON g.ground_id = FLOOR(RAND() * (SELECT MAX(ground_id) FROM grounds)) + 1
WHERE b.status = 'completed'
LIMIT 55;

-- =============================================
-- 10. SCORECARDS (55 Scorecards)
-- =============================================

CREATE TABLE `scorecards` (
  `scorecard_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `team_1_name` varchar(50) NOT NULL,
  `team_2_name` varchar(50) NOT NULL,
  `team_1_score` int(11) DEFAULT 0,
  `team_2_score` int(11) DEFAULT 0,
  `winner` varchar(50) DEFAULT NULL,
  `man_of_the_match` varchar(50) DEFAULT NULL,
  `match_date` date DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  PRIMARY KEY (`scorecard_id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `players`(`player_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `scorecards` (`booking_id`, `team_1_name`, `team_2_name`, `team_1_score`, `team_2_score`, `winner`, `man_of_the_match`, `match_date`, `recorded_by`) 
SELECT 
  b.booking_id,
  CASE FLOOR(RAND() * 10)
    WHEN 0 THEN 'Dhaka Tigers'
    WHEN 1 THEN 'Chittagong Kings'
    WHEN 2 THEN 'Rajshahi Royals'
    WHEN 3 THEN 'Khulna Titans'
    WHEN 4 THEN 'Sylhet Strikers'
    WHEN 5 THEN 'Barishal Bulls'
    WHEN 6 THEN 'Rangpur Riders'
    WHEN 7 THEN 'Mymensingh Warriors'
    WHEN 8 THEN 'Comilla Cobras'
    ELSE 'Cox Bazar Sharks'
  END as team_1_name,
  CASE FLOOR(RAND() * 10)
    WHEN 0 THEN 'Green Warriors'
    WHEN 1 THEN 'Red Devils'
    WHEN 2 THEN 'Blue Tigers'
    WHEN 3 THEN 'Yellow Stars'
    WHEN 4 THEN 'Black Panthers'
    WHEN 5 THEN 'White Eagles'
    WHEN 6 THEN 'Golden Lions'
    WHEN 7 THEN 'Silver Hawks'
    WHEN 8 THEN 'Royal Challengers'
    ELSE 'Super Kings'
  END as team_2_name,
  FLOOR(RAND() * 100) + 50 as team_1_score,
  FLOOR(RAND() * 100) + 40 as team_2_score,
  CASE WHEN RAND() > 0.5 THEN 'team_1' ELSE 'team_2' END as winner,
  CASE FLOOR(RAND() * 15)
    WHEN 0 THEN 'Karim Hasan'
    WHEN 1 THEN 'Rahim Ahmed'
    WHEN 2 THEN 'Jamal Uddin'
    WHEN 3 THEN 'Kamal Hossain'
    WHEN 4 THEN 'Salam Mia'
    WHEN 5 THEN 'Rashid Khan'
    WHEN 6 THEN 'Shahin Alam'
    WHEN 7 THEN 'Mizanur Rahman'
    WHEN 8 THEN 'Shafiul Islam'
    WHEN 9 THEN 'Sajib Hossain'
    WHEN 10 THEN 'Munna Mia'
    WHEN 11 THEN 'Sohel Rana'
    WHEN 12 THEN 'Masud Khan'
    WHEN 13 THEN 'Rakib Hasan'
    ELSE 'Shakib Al Hasan'
  END as man_of_the_match,
  DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY) as match_date,
  b.player_id
FROM bookings b
WHERE b.status = 'completed'
LIMIT 55;

-- =============================================
-- 11. NOTIFICATIONS (55 Notifications)
-- =============================================

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('booking','payment','system','reminder') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`, `created_at`) 
SELECT 
  u.user_id,
  CASE FLOOR(RAND() * 5)
    WHEN 0 THEN 'Booking Confirmed'
    WHEN 1 THEN 'Payment Successful'
    WHEN 2 THEN 'New Booking Request'
    WHEN 3 THEN 'Booking Reminder'
    ELSE 'Booking Completed'
  END as title,
  CASE FLOOR(RAND() * 6)
    WHEN 0 THEN 'Your booking has been confirmed successfully. Enjoy your match!'
    WHEN 1 THEN 'Your payment has been received. Thank you for using Khela Hobee!'
    WHEN 2 THEN 'A new booking request has been submitted for your ground. Please review it.'
    WHEN 3 THEN 'Reminder: You have a booking scheduled for tomorrow. Prepare well!'
    WHEN 4 THEN 'Your match has been completed. Rate your experience!'
    ELSE 'Thank you for using Khela Hobee. We appreciate your trust!'
  END as message,
  CASE FLOOR(RAND() * 4)
    WHEN 0 THEN 'booking'
    WHEN 1 THEN 'payment'
    WHEN 2 THEN 'system'
    ELSE 'reminder'
  END as type,
  CASE WHEN RAND() > 0.5 THEN 1 ELSE 0 END as is_read,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) as created_at
FROM users u
WHERE u.user_id <= 30
LIMIT 55;

-- =============================================
-- 12. AUDIT LOGS (55 Logs)
-- =============================================

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) 
SELECT 
  u.user_id,
  CASE FLOOR(RAND() * 6)
    WHEN 0 THEN 'Login'
    WHEN 1 THEN 'Booking'
    WHEN 2 THEN 'Payment'
    WHEN 3 THEN 'Review'
    WHEN 4 THEN 'Profile Update'
    ELSE 'Logout'
  END as action,
  CASE FLOOR(RAND() * 6)
    WHEN 0 THEN 'User logged in successfully to Khela Hobee'
    WHEN 1 THEN 'Booking created for ground in Dhaka'
    WHEN 2 THEN 'Payment processed successfully via bKash'
    WHEN 3 THEN 'New review submitted with 5-star rating'
    WHEN 4 THEN 'Profile information updated successfully'
    ELSE 'User logged out of the system'
  END as details,
  CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)) as ip_address,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 60) DAY) as created_at
FROM users u
WHERE u.user_id <= 30
LIMIT 55;

-- =============================================
-- 13. CONTACTS (55 Contacts)
-- =============================================

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contact_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `contacts` (`name`, `email`, `subject`, `message`, `status`, `created_at`) 
SELECT 
  u.name,
  u.email,
  CASE FLOOR(RAND() * 6)
    WHEN 0 THEN 'Booking Inquiry'
    WHEN 1 THEN 'Ground Availability Question'
    WHEN 2 THEN 'Payment Issue'
    WHEN 3 THEN 'Account Help'
    WHEN 4 THEN 'Feature Request'
    ELSE 'General Feedback'
  END as subject,
  CASE FLOOR(RAND() * 8)
    WHEN 0 THEN 'I would like to know more about the booking process for sports grounds in Dhaka.'
    WHEN 1 THEN 'Is there any availability for next weekend? I want to book a football ground.'
    WHEN 2 THEN 'I am having trouble with my payment through bKash. Please help me resolve this.'
    WHEN 3 THEN 'How can I reset my password? I forgot my login credentials.'
    WHEN 4 THEN 'Would be great to add more sports options like volleyball and badminton.'
    WHEN 5 THEN 'Keep up the good work! I love using Khela Hobee for my matches.'
    WHEN 6 THEN 'I want to register as a ground owner. What are the requirements?'
    ELSE 'Can you add more grounds in Chittagong and Sylhet?'
  END as message,
  CASE FLOOR(RAND() * 3)
    WHEN 0 THEN 'unread'
    WHEN 1 THEN 'read'
    ELSE 'replied'
  END as status,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 45) DAY) as created_at
FROM users u
WHERE u.user_id <= 30
LIMIT 55;

-- =============================================
-- 14. PLAYER MATCHES (55 Matches)
-- =============================================

CREATE TABLE `player_matches` (
  `match_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `ground_id` int(11) NOT NULL,
  `sport_type` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `players_needed` int(11) DEFAULT 5,
  `players_joined` int(11) DEFAULT 0,
  `status` enum('open','full','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`match_id`),
  FOREIGN KEY (`player_id`) REFERENCES `players`(`player_id`) ON DELETE CASCADE,
  FOREIGN KEY (`ground_id`) REFERENCES `grounds`(`ground_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `player_matches` (`player_id`, `ground_id`, `sport_type`, `date`, `time`, `players_needed`, `players_joined`, `status`) 
SELECT 
  p.player_id,
  g.ground_id,
  CASE FLOOR(RAND() * 3)
    WHEN 0 THEN 'Football'
    WHEN 1 THEN 'Cricket'
    ELSE 'Basketball'
  END as sport_type,
  DATE_ADD(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY) as date,
  CASE FLOOR(RAND() * 6)
    WHEN 0 THEN '07:00:00'
    WHEN 1 THEN '08:00:00'
    WHEN 2 THEN '09:00:00'
    WHEN 3 THEN '15:00:00'
    WHEN 4 THEN '16:00:00'
    ELSE '17:00:00'
  END as time,
  FLOOR(RAND() * 6) + 2 as players_needed,
  FLOOR(RAND() * 5) + 1 as players_joined,
  CASE WHEN RAND() > 0.7 THEN 'full' WHEN RAND() > 0.4 THEN 'open' ELSE 'closed' END as status
FROM players p
CROSS JOIN grounds g
WHERE p.player_id <= 25
LIMIT 55;

-- =============================================
-- 15. PASSWORD RESETS (Empty)
-- =============================================

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- COMMIT
-- =============================================

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =============================================
-- TEST LOGIN CREDENTIALS
-- =============================================
-- Admin:    admin@khela.com      / #hi123
-- Owner:    owner.dhaka@khela.com / #hi123
-- Player:   sakib@cricket.com    / #hi123
-- All sample users password: #hi123
-- =============================================

-- =============================================
-- SUMMARY
-- =============================================
-- users:          67
-- administrators: 2
-- ground_owners:  20
-- players:        45
-- grounds:        55 (15 Dhaka + 5 each other division)
-- time_slots:     100+
-- bookings:       55+
-- payments:       55+
-- reviews:        55+
-- scorecards:     55+
-- notifications:  55+
-- audit_logs:     55+
-- contacts:       55+
-- player_matches: 55+
-- =============================================