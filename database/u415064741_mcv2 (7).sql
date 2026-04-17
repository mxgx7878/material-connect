-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 17, 2026 at 08:48 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u415064741_mcv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_logs`
--

CREATE TABLE `action_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `action_logs`
--

INSERT INTO `action_logs` (`id`, `action`, `details`, `order_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Order Item Marked as Paid', 'Order Item ID 32 in Order ID 21 marked as paid', 21, 1, '2026-01-21 10:41:05', '2026-01-21 10:41:05'),
(2, 'Order Item Marked as Paid', 'Order Item ID 33 in Order ID 21 marked as paid', 21, 109, '2026-01-22 03:54:51', '2026-01-22 03:54:51'),
(3, 'Order Archived', 'Client Aiza chan archived order #22', 22, 110, '2026-01-22 04:29:27', '2026-01-22 04:29:27'),
(4, 'Order Created', 'Order ID 23 created by Client Aiza chan', 23, 110, '2026-01-23 02:32:00', '2026-01-23 02:32:00'),
(5, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 37', 23, 109, '2026-01-23 02:32:25', '2026-01-23 02:32:25'),
(6, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 38', 23, 109, '2026-01-23 02:32:28', '2026-01-23 02:32:28'),
(7, 'Order Archived', 'Client Aiza chan archived order #23', 23, 110, '2026-01-23 03:06:19', '2026-01-23 03:06:19'),
(8, 'Order Archived', 'Client Aiza chan archived order #21', 21, 110, '2026-01-23 03:06:22', '2026-01-23 03:06:22'),
(9, 'Order Created', 'Order ID 24 created by Client Aiza chan', 24, 110, '2026-01-23 05:45:14', '2026-01-23 05:45:14'),
(10, 'Order Created', 'Order ID 25 created by Client Aiza chan', 25, 110, '2026-01-27 01:08:08', '2026-01-27 01:08:08'),
(11, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 43', 25, 109, '2026-01-27 01:09:26', '2026-01-27 01:09:26'),
(12, 'Order Created', 'Order ID 26 created by Client Aiza chan', 26, 110, '2026-01-27 01:29:08', '2026-01-27 01:29:08'),
(13, 'Supplier Assigned to Order', 'Supplier Marie Low assigned to item ID 44', 26, 109, '2026-01-27 01:30:32', '2026-01-27 01:30:32'),
(14, 'Supplier Assigned to Order', 'Supplier Kane Hauser assigned to item ID 45', 26, 109, '2026-01-27 01:30:38', '2026-01-27 01:30:38'),
(15, 'Order Archived', 'Order ID 24 archived by admin', 24, 109, '2026-01-27 01:36:54', '2026-01-27 01:36:54'),
(16, 'Order Created', 'Order ID 27 created by Client Aiza chan', 27, 110, '2026-02-02 01:13:53', '2026-02-02 01:13:53'),
(17, 'Supplier Assigned to Order', 'Supplier Zoe Muscat assigned to item ID 46', 27, 109, '2026-02-02 01:15:20', '2026-02-02 01:15:20'),
(18, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 47', 27, 109, '2026-02-02 01:15:29', '2026-02-02 01:15:29'),
(19, 'Order Item Pricing Updated By Supplier', 'Supplier ID 60 updated pricing for Order Item ID 46 in Order ID 27', 27, 60, '2026-02-02 01:23:45', '2026-02-02 01:23:45'),
(20, 'Order Item Pricing Updated By Supplier', 'Supplier ID 70 updated pricing for Order Item ID 47 in Order ID 27', 27, 70, '2026-02-02 01:29:49', '2026-02-02 01:29:49'),
(21, 'Order Archived', 'Client Aiza chan archived order #26', 26, 110, '2026-02-02 01:30:55', '2026-02-02 01:30:55'),
(22, 'Order Archived', 'Client Aiza chan archived order #25', 25, 110, '2026-02-02 01:30:59', '2026-02-02 01:30:59'),
(23, 'Order Item Pricing Updated By Supplier', 'Supplier ID 70 updated pricing for Order Item ID 47 in Order ID 27', 27, 70, '2026-02-02 01:38:49', '2026-02-02 01:38:49'),
(24, 'Order Created', 'Order ID 28 created by Client Client', 28, 13, '2026-02-02 11:01:20', '2026-02-02 11:01:20'),
(25, 'Order Created', 'Order ID 29 created by Client Aiza chan', 29, 110, '2026-02-03 03:15:44', '2026-02-03 03:15:44'),
(26, 'Order Archived', 'Order ID 29 archived by admin', 29, 109, '2026-02-03 05:19:20', '2026-02-03 05:19:20'),
(27, 'Order Archived', 'Order ID 28 archived by admin', 28, 109, '2026-02-03 05:19:24', '2026-02-03 05:19:24'),
(28, 'Order Archived', 'Order ID 27 archived by admin', 27, 109, '2026-02-03 05:19:30', '2026-02-03 05:19:30'),
(29, 'Order Created', 'Order ID 30 created by Client Aiza chan', 30, 110, '2026-02-03 05:29:05', '2026-02-03 05:29:05'),
(30, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 53', 30, 1, '2026-02-03 05:35:58', '2026-02-03 05:35:58'),
(31, 'Order Pricing Updated', 'Updated pricing for Order Item ID 52 in Order ID 30', 30, 1, '2026-02-03 05:36:37', '2026-02-03 05:36:37'),
(32, 'Order Pricing Updated', 'Updated pricing for Order Item ID 53 in Order ID 30', 30, 1, '2026-02-03 05:37:04', '2026-02-03 05:37:04'),
(33, 'Order Pricing Updated', 'Updated pricing for Order Item ID 54 in Order ID 30', 30, 1, '2026-02-03 05:37:13', '2026-02-03 05:37:13'),
(34, 'Order Pricing Updated', 'Updated pricing for Order Item ID 54 in Order ID 30', 30, 1, '2026-02-03 05:37:29', '2026-02-03 05:37:29'),
(35, 'Order Created', 'Order ID 31 created by Client Client', 31, 13, '2026-02-03 05:46:28', '2026-02-03 05:46:28'),
(36, 'Order Item Pricing Updated By Supplier', 'Supplier ID 65 updated pricing for Order Item ID 52 in Order ID 30', 30, 65, '2026-02-03 05:53:10', '2026-02-03 05:53:10'),
(37, 'Order Archived', 'Order ID 31 archived by admin', 31, 1, '2026-02-03 06:00:45', '2026-02-03 06:00:45'),
(38, 'Order Item Pricing Updated By Supplier', 'Supplier ID 70 updated pricing for Order Item ID 53 in Order ID 30', 30, 70, '2026-02-03 06:04:22', '2026-02-03 06:04:22'),
(39, 'Order Item Marked as Paid', 'Order Item ID 48 in Order ID 28 marked as paid', 28, 1, '2026-02-03 06:04:52', '2026-02-03 06:04:52'),
(40, 'Order Item Pricing Updated By Supplier', 'Supplier ID 62 updated pricing for Order Item ID 54 in Order ID 30', 30, 62, '2026-02-03 06:06:59', '2026-02-03 06:06:59'),
(41, 'Order Created', 'Order ID 32 created by Client Client', 32, 13, '2026-02-03 08:41:18', '2026-02-03 08:41:18'),
(42, 'Order Pricing Updated', 'Updated pricing for Order Item ID 58 in Order ID 32', 32, 1, '2026-02-03 08:42:16', '2026-02-03 08:42:16'),
(43, 'Order Pricing Updated', 'Updated pricing for Order Item ID 59 in Order ID 32', 32, 1, '2026-02-03 08:42:26', '2026-02-03 08:42:26'),
(44, 'Order Pricing Updated', 'Updated pricing for Order Item ID 60 in Order ID 32', 32, 1, '2026-02-03 08:42:36', '2026-02-03 08:42:36'),
(45, 'Order Created', 'Order ID 33 created by Client Client', 33, 13, '2026-02-03 08:57:13', '2026-02-03 08:57:13'),
(46, 'Order Created', 'Order ID 34 created by Client John Doe', 34, 111, '2026-02-04 06:26:32', '2026-02-04 06:26:32'),
(47, 'Order Payment Status Updated', 'Payment status for Order ID 34 updated to Paid', 34, 1, '2026-02-04 06:28:25', '2026-02-04 06:28:25'),
(48, 'Order Status Updated', 'Client John Doe updated order #34 status to Cancelled', 34, 111, '2026-02-04 06:29:44', '2026-02-04 06:29:44'),
(49, 'Order Archived', 'Client John Doe archived order #34', 34, 111, '2026-02-04 06:30:02', '2026-02-04 06:30:02'),
(50, 'Order Created', 'Order ID 35 created by Client John Doe', 35, 111, '2026-02-04 06:30:52', '2026-02-04 06:30:52'),
(51, 'Supplier Assigned to Order', 'Supplier Marco Keith assigned to item ID 64', 35, 1, '2026-02-04 06:31:11', '2026-02-04 06:31:11'),
(52, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 65', 35, 1, '2026-02-04 06:31:13', '2026-02-04 06:31:13'),
(53, 'Order Pricing Updated', 'Updated pricing for Order Item ID 64 in Order ID 35', 35, 1, '2026-02-04 06:31:29', '2026-02-04 06:31:29'),
(54, 'Order Pricing Updated', 'Updated pricing for Order Item ID 64 in Order ID 35', 35, 1, '2026-02-04 06:31:46', '2026-02-04 06:31:46'),
(55, 'Order Pricing Updated', 'Updated pricing for Order Item ID 65 in Order ID 35', 35, 1, '2026-02-04 06:32:00', '2026-02-04 06:32:00'),
(56, 'Order Item Marked as Paid', 'Order Item ID 64 in Order ID 35 marked as paid', 35, 1, '2026-02-04 06:32:33', '2026-02-04 06:32:33'),
(57, 'Order Item Marked as Paid', 'Order Item ID 65 in Order ID 35 marked as paid', 35, 1, '2026-02-04 06:32:36', '2026-02-04 06:32:36'),
(58, 'Order Payment Status Updated', 'Payment status for Order ID 35 updated to Paid', 35, 1, '2026-02-04 06:33:30', '2026-02-04 06:33:30'),
(59, 'Order Created', 'Order ID 36 created by Client Client', 36, 13, '2026-02-04 14:14:20', '2026-02-04 14:14:20'),
(60, 'Order Created', 'Order ID 37 created by Client John Doe', 37, 111, '2026-02-05 00:20:21', '2026-02-05 00:20:21'),
(61, 'Supplier Assigned to Order', 'Supplier Marco Keith assigned to item ID 68', 37, 1, '2026-02-05 00:23:49', '2026-02-05 00:23:49'),
(62, 'Order Item Pricing Updated By Supplier', 'Supplier ID 66 updated pricing for Order Item ID 67 in Order ID 30', 30, 66, '2026-02-05 00:24:00', '2026-02-05 00:24:00'),
(63, 'Order Pricing Updated', 'Updated pricing for Order Item ID 68 in Order ID 37', 37, 1, '2026-02-05 00:24:06', '2026-02-05 00:24:06'),
(64, 'Order Created', 'Order ID 38 created by Client Aiza chan', 38, 110, '2026-02-05 04:51:55', '2026-02-05 04:51:55'),
(65, 'Supplier Assigned to Order', 'Supplier Zoe Muscat assigned to item ID 70', 38, 109, '2026-02-05 04:53:40', '2026-02-05 04:53:40'),
(66, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 71', 38, 109, '2026-02-05 04:53:49', '2026-02-05 04:53:49'),
(67, 'Order Item Pricing Updated By Supplier', 'Supplier ID 60 updated pricing for Order Item ID 70 in Order ID 38', 38, 60, '2026-02-05 04:59:11', '2026-02-05 04:59:11'),
(68, 'Order Item Pricing Updated By Supplier', 'Supplier ID 65 updated pricing for Order Item ID 71 in Order ID 38', 38, 65, '2026-02-05 05:03:05', '2026-02-05 05:03:05'),
(69, 'Order Item Marked as Paid', 'Order Item ID 70 in Order ID 38 marked as paid', 38, 1, '2026-02-05 05:06:02', '2026-02-05 05:06:02'),
(70, 'Order Item Marked as Paid', 'Order Item ID 71 in Order ID 38 marked as paid', 38, 1, '2026-02-05 05:06:05', '2026-02-05 05:06:05'),
(71, 'Order Payment Status Updated', 'Payment status for Order ID 38 updated to Paid', 38, 1, '2026-02-05 05:06:18', '2026-02-05 05:06:18'),
(72, 'Invoice Created', 'Invoice INV-2026-0001 created for 2 deliveries. Total: $245.58', 38, 1, '2026-02-06 17:02:56', '2026-02-06 17:02:56'),
(73, 'Invoice Status Updated', 'Invoice INV-2026-0001 status changed from Draft to Partially Paid', 38, 109, '2026-02-08 22:48:05', '2026-02-08 22:48:05'),
(74, 'Invoice Status Updated', 'Invoice INV-2026-0001 status changed from Partially Paid to Draft', 38, 109, '2026-02-08 22:48:09', '2026-02-08 22:48:09'),
(75, 'Invoice Created', 'Invoice INV-2026-0002 created for 2 deliveries. Total: $649.00', 38, 109, '2026-02-09 05:14:45', '2026-02-09 05:14:45'),
(76, 'Order Created', 'Order ID 39 created by Client Aiza chan', 39, 110, '2026-02-09 23:26:25', '2026-02-09 23:26:25'),
(77, 'Order Status Updated', 'Client Aiza chan updated order #39 status to Cancelled', 39, 110, '2026-02-09 23:38:01', '2026-02-09 23:38:01'),
(78, 'Order Archived', 'Client Aiza chan archived order #39', 39, 110, '2026-02-09 23:38:09', '2026-02-09 23:38:09'),
(79, 'Order Created', 'Order ID 40 created by Client John Doe', 40, 111, '2026-02-09 23:38:17', '2026-02-09 23:38:17'),
(80, 'Order Created', 'Order ID 41 created by Client Aiza chan', 41, 110, '2026-02-09 23:39:25', '2026-02-09 23:39:25'),
(81, 'Supplier Assigned to Order', 'Supplier Marie Low assigned to item ID 73', 40, 1, '2026-02-09 23:39:32', '2026-02-09 23:39:32'),
(82, 'Supplier Assigned to Order', 'Supplier Darby Lee assigned to item ID 74', 40, 1, '2026-02-09 23:39:35', '2026-02-09 23:39:35'),
(83, 'Order Pricing Updated', 'Updated pricing for Order Item ID 73 in Order ID 40', 40, 1, '2026-02-09 23:40:21', '2026-02-09 23:40:21'),
(84, 'Order Pricing Updated', 'Updated pricing for Order Item ID 73 in Order ID 40', 40, 1, '2026-02-09 23:40:29', '2026-02-09 23:40:29'),
(85, 'Order Pricing Updated', 'Updated pricing for Order Item ID 74 in Order ID 40', 40, 1, '2026-02-09 23:40:45', '2026-02-09 23:40:45'),
(86, 'Order Pricing Updated', 'Updated pricing for Order Item ID 74 in Order ID 40', 40, 1, '2026-02-09 23:40:51', '2026-02-09 23:40:51'),
(87, 'Order Payment Status Updated', 'Payment status for Order ID 40 updated to Paid', 40, 1, '2026-02-09 23:41:38', '2026-02-09 23:41:38'),
(88, 'Invoice Created', 'Invoice INV-2026-0003 created for 2 deliveries. Total: $1442.65', 40, 1, '2026-02-09 23:45:05', '2026-02-09 23:45:05'),
(89, 'Invoice Status Updated', 'Invoice INV-2026-0003 status changed from Draft to Paid', 40, 1, '2026-02-09 23:45:13', '2026-02-09 23:45:13'),
(90, 'Order Item Marked as Paid', 'Order Item ID 73 in Order ID 40 marked as paid', 40, 1, '2026-02-09 23:45:30', '2026-02-09 23:45:30'),
(91, 'Order Item Marked as Paid', 'Order Item ID 74 in Order ID 40 marked as paid', 40, 1, '2026-02-09 23:45:33', '2026-02-09 23:45:33'),
(92, 'Order Item Pricing Updated By Supplier', 'Supplier ID 65 updated pricing for Order Item ID 75 in Order ID 41', 41, 65, '2026-02-09 23:48:10', '2026-02-09 23:48:10'),
(93, 'Order Archived', 'Client John Doe archived order #37', 37, 111, '2026-02-10 01:06:01', '2026-02-10 01:06:01'),
(94, 'Admin Updated Order Items', 'Admin  updated items in Order #41: 1/4 Minus ( Fine road base ) 1 tonne: qty 4 → 4', 41, 1, '2026-02-10 11:26:31', '2026-02-10 11:26:31'),
(95, 'Order Pricing Updated', 'Updated pricing for Order Item ID 75 in Order ID 41', 41, 109, '2026-02-12 01:41:52', '2026-02-12 01:41:52'),
(96, 'Order Created', 'Order ID 42 created by Client Client', 42, 13, '2026-02-12 05:08:06', '2026-02-12 05:08:06'),
(97, 'Order Pricing Updated', 'Updated pricing for Order Item ID 76 in Order ID 42', 42, 1, '2026-02-12 05:13:14', '2026-02-12 05:13:14'),
(98, 'Admin Updated Order Items', 'Admin  updated items in Order #42: 1/4 Minus ( Fine road base ) 1 tonne: qty 3 → 3', 42, 1, '2026-02-12 05:15:25', '2026-02-12 05:15:25'),
(99, 'Admin Updated Order Items', 'Admin  updated items in Order #42: 1/4 Minus ( Fine road base ) 1/2 tonne: qty 3 → 3; 1/4 Minus ( Fine road base ) 1/4 tonne: qty 3 → 3', 42, 1, '2026-02-12 05:17:32', '2026-02-12 05:17:32'),
(100, 'Order Pricing Updated', 'Updated pricing for Order Item ID 77 in Order ID 42', 42, 1, '2026-02-12 05:18:07', '2026-02-12 05:18:07'),
(101, 'Order Pricing Updated', 'Updated pricing for Order Item ID 78 in Order ID 42', 42, 1, '2026-02-12 05:18:34', '2026-02-12 05:18:34'),
(102, 'Invoice Created', 'Invoice INV-2026-0004 created for 3 deliveries. Total: $833.43', 42, 1, '2026-02-12 05:26:51', '2026-02-12 05:26:51'),
(103, 'Invoice Status Updated', 'Invoice INV-2026-0004 status changed from Draft to Paid', 42, 1, '2026-02-12 05:27:07', '2026-02-12 05:27:07'),
(104, 'Admin Updated Order Items', 'Admin  updated items in Order #42: 1/4 Minus ( Fine road base ) 3/4 tonne: qty 6 → 6', 42, 1, '2026-02-12 05:31:02', '2026-02-12 05:31:02'),
(105, 'Order Pricing Updated', 'Updated pricing for Order Item ID 79 in Order ID 42', 42, 1, '2026-02-12 05:31:16', '2026-02-12 05:31:16'),
(106, 'Admin Updated Order Items', 'Admin  updated items in Order #42: 1/4 Minus ( Fine road base ) 3/4 tonne: qty 6 → 5', 42, 1, '2026-02-12 05:36:38', '2026-02-12 05:36:38'),
(107, 'Admin Updated Order Items', 'Admin  updated items in Order #42: 1/4 Minus ( Fine road base ) 1/4 tonne: qty 3 → 2; 1/4 Minus ( Fine road base ) 1/2 tonne: qty 3 → 2', 42, 1, '2026-02-12 05:37:08', '2026-02-12 05:37:08'),
(108, 'Admin Updated Order Items', 'Admin  updated items in Order #41: 1/4 Minus ( Fine road base ) 1 tonne: qty 4 → 4', 41, 109, '2026-02-12 05:40:18', '2026-02-12 05:40:18'),
(109, 'Order Created', 'Order ID 43 created by Client Client', 43, 13, '2026-02-13 19:41:32', '2026-02-13 19:41:32'),
(110, 'Order Archived', 'Order ID 30 archived by admin', 30, 109, '2026-02-16 01:14:41', '2026-02-16 01:14:41'),
(111, 'Order Archived', 'Order ID 32 archived by admin', 32, 109, '2026-02-16 01:14:48', '2026-02-16 01:14:48'),
(112, 'Order Archived', 'Order ID 33 archived by admin', 33, 109, '2026-02-16 01:14:57', '2026-02-16 01:14:57'),
(113, 'Order Archived', 'Order ID 35 archived by admin', 35, 109, '2026-02-16 01:15:01', '2026-02-16 01:15:01'),
(114, 'Order Archived', 'Order ID 36 archived by admin', 36, 109, '2026-02-16 01:15:06', '2026-02-16 01:15:06'),
(115, 'Order Archived', 'Order ID 38 archived by admin', 38, 109, '2026-02-16 01:15:09', '2026-02-16 01:15:09'),
(116, 'Order Archived', 'Order ID 40 archived by admin', 40, 109, '2026-02-16 01:15:13', '2026-02-16 01:15:13'),
(117, 'Order Payment Status Updated', 'Payment status for Order ID 42 updated to Paid', 42, 1, '2026-02-17 03:45:28', '2026-02-17 03:45:28'),
(118, 'Order Created', 'Order ID 44 created by Client Client', 44, 13, '2026-02-17 21:34:43', '2026-02-17 21:34:43'),
(119, 'Order Created', 'Order ID 45 created by Client Client', 45, 13, '2026-02-17 22:40:22', '2026-02-17 22:40:22'),
(120, 'Order Created', 'Order ID 46 created by Client John Doe', 46, 111, '2026-02-17 22:41:11', '2026-02-17 22:41:11'),
(121, 'Order Created', 'Order ID 47 created by Client Client', 47, 13, '2026-02-17 22:42:28', '2026-02-17 22:42:28'),
(122, 'Order Created', 'Order ID 48 created by Client John Doe', 48, 111, '2026-02-17 22:43:52', '2026-02-17 22:43:52'),
(123, 'Order Created', 'Order ID 49 created by Client John Doe', 49, 111, '2026-02-17 22:44:40', '2026-02-17 22:44:40'),
(124, 'Order Pricing Updated', 'Updated pricing for Order Item ID 93 in Order ID 49', 49, 1, '2026-02-17 22:45:51', '2026-02-17 22:45:51'),
(125, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 94', 49, 1, '2026-02-17 22:46:04', '2026-02-17 22:46:04'),
(126, 'Order Pricing Updated', 'Updated pricing for Order Item ID 94 in Order ID 49', 49, 1, '2026-02-17 22:46:19', '2026-02-17 22:46:19'),
(127, 'Order Created', 'Order ID 50 created by Client Aiza chan', 50, 110, '2026-02-17 22:46:19', '2026-02-17 22:46:19'),
(128, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 95', 50, 109, '2026-02-17 22:47:48', '2026-02-17 22:47:48'),
(129, 'Order Payment Status Updated', 'Payment status for Order ID 49 updated to Paid', 49, 1, '2026-02-17 22:49:57', '2026-02-17 22:49:57'),
(130, 'Invoice Created', 'Invoice INV-2026-0005 created for 2 deliveries. Total: $36965.50', 49, 1, '2026-02-17 22:51:33', '2026-02-17 22:51:33'),
(131, 'Order Item Pricing Updated By Supplier', 'Supplier ID 65 updated pricing for Order Item ID 95 in Order ID 50', 50, 65, '2026-02-17 22:52:46', '2026-02-17 22:52:46'),
(132, 'Order Item Pricing Updated By Supplier', 'Supplier ID 65 updated pricing for Order Item ID 95 in Order ID 50', 50, 65, '2026-02-17 22:52:54', '2026-02-17 22:52:54'),
(133, 'Invoice Status Updated', 'Invoice INV-2026-0005 status changed from Draft to Paid', 49, 1, '2026-02-17 22:53:20', '2026-02-17 22:53:20'),
(134, 'Admin Updated Order Items', 'Admin  updated items in Order #50: 1/4 Minus ( Fine road base ) 1 tonne: qty 100 → 100; 10mm Bluemetal 1/2 m3: qty 100 → 100; 20mm Blue Metal: qty 100 → 100', 50, 109, '2026-02-18 05:14:56', '2026-02-18 05:14:56'),
(135, 'Order Pricing Updated', 'Updated pricing for Order Item ID 96 in Order ID 50', 50, 109, '2026-02-18 05:15:56', '2026-02-18 05:15:56'),
(136, 'Order Pricing Updated', 'Updated pricing for Order Item ID 97 in Order ID 50', 50, 109, '2026-02-18 05:16:02', '2026-02-18 05:16:02'),
(137, 'Order Archived', 'Order ID 41 archived by admin', 41, 109, '2026-02-19 01:19:03', '2026-02-19 01:19:03'),
(138, 'Order Archived', 'Order ID 42 archived by admin', 42, 109, '2026-02-19 01:19:07', '2026-02-19 01:19:07'),
(139, 'Order Archived', 'Order ID 43 archived by admin', 43, 109, '2026-02-19 01:19:10', '2026-02-19 01:19:10'),
(140, 'Order Archived', 'Order ID 44 archived by admin', 44, 109, '2026-02-19 01:19:14', '2026-02-19 01:19:14'),
(141, 'Order Archived', 'Order ID 45 archived by admin', 45, 109, '2026-02-19 01:19:18', '2026-02-19 01:19:18'),
(142, 'Order Archived', 'Order ID 46 archived by admin', 46, 109, '2026-02-19 01:19:20', '2026-02-19 01:19:20'),
(143, 'Order Created', 'Order ID 1 created by Client Client', 1, 13, '2026-02-19 17:52:54', '2026-02-19 17:52:54'),
(144, 'Order Pricing Updated', 'Updated pricing for Order Item ID 1 in Order ID 1', 1, 1, '2026-02-19 17:55:26', '2026-02-19 17:55:26'),
(145, 'Admin Updated Order Items', 'Admin  updated items in Order #1: 1/4 Minus ( Fine road base ) 1 tonne: qty 5 → 5; 10/7 mm Aggregate: qty 5 → 5', 1, 1, '2026-02-19 17:57:25', '2026-02-19 17:57:25'),
(146, 'Invoice Created', 'Invoice INV-2026-0006 created for 2 deliveries. Total: $355.80', 1, 1, '2026-02-19 17:59:23', '2026-02-19 17:59:23'),
(147, 'Order Created', 'Order ID 2 created by Client John Doe', 2, 111, '2026-02-23 06:55:16', '2026-02-23 06:55:16'),
(148, 'Order Pricing Updated', 'Updated pricing for Order Item ID 3 in Order ID 2', 2, 1, '2026-02-23 06:55:57', '2026-02-23 06:55:57'),
(149, 'Order Pricing Updated', 'Updated pricing for Order Item ID 4 in Order ID 2', 2, 1, '2026-02-23 06:56:06', '2026-02-23 06:56:06'),
(150, 'Order Payment Status Updated', 'Payment status for Order ID 2 updated to Paid', 2, 1, '2026-02-23 06:56:33', '2026-02-23 06:56:33'),
(151, 'Invoice Created', 'Invoice INV-2026-0007 created for 2 deliveries. Total: $175395.00', 2, 1, '2026-02-23 06:57:09', '2026-02-23 06:57:09'),
(152, 'Order Pricing Updated', 'Updated pricing for Order Item ID 3 in Order ID 2', 2, 1, '2026-02-23 07:10:12', '2026-02-23 07:10:12'),
(153, 'Order Pricing Updated', 'Updated pricing for Order Item ID 4 in Order ID 2', 2, 1, '2026-02-23 07:10:17', '2026-02-23 07:10:17'),
(154, 'Invoice Created', 'Invoice INV-2026-0008 created for 2 deliveries. Total: $87697.50', 2, 1, '2026-02-24 01:01:27', '2026-02-24 01:01:27'),
(155, 'Order Pricing Updated', 'Updated pricing for Order Item ID 3 in Order ID 2', 2, 1, '2026-02-24 05:54:23', '2026-02-24 05:54:23'),
(156, 'Order Pricing Updated', 'Updated pricing for Order Item ID 4 in Order ID 2', 2, 1, '2026-02-24 05:54:29', '2026-02-24 05:54:29'),
(157, 'Order Created', 'Order ID 3 created by Client John Doe', 3, 111, '2026-02-25 05:56:01', '2026-02-25 05:56:01'),
(158, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 5', 3, 1, '2026-02-25 05:56:27', '2026-02-25 05:56:27'),
(159, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 6', 3, 1, '2026-02-25 05:56:31', '2026-02-25 05:56:31'),
(160, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 7', 3, 1, '2026-02-25 05:56:34', '2026-02-25 05:56:34'),
(161, 'Order Pricing Updated', 'Updated pricing for Order Item ID 5 in Order ID 3', 3, 1, '2026-02-25 05:57:11', '2026-02-25 05:57:11'),
(162, 'Order Pricing Updated', 'Updated pricing for Order Item ID 6 in Order ID 3', 3, 1, '2026-02-25 05:57:16', '2026-02-25 05:57:16'),
(163, 'Order Pricing Updated', 'Updated pricing for Order Item ID 7 in Order ID 3', 3, 1, '2026-02-25 05:57:20', '2026-02-25 05:57:20'),
(164, 'Order Pricing Updated', 'Updated pricing for Order Item ID 7 in Order ID 3', 3, 1, '2026-02-25 05:57:24', '2026-02-25 05:57:24'),
(165, 'Invoice Created', 'Invoice INV-2026-0009 created for 8 deliveries. Total: $99660.00', 3, 1, '2026-02-25 05:57:59', '2026-02-25 05:57:59'),
(166, 'Invoice Status Updated', 'Invoice INV-2026-0009 status changed from Paid to Draft', 3, 1, '2026-02-25 06:01:51', '2026-02-25 06:01:51'),
(167, 'Order Created', 'Order ID 4 created by Client Client', 4, 13, '2026-02-26 03:41:06', '2026-02-26 03:41:06'),
(168, 'Order Pricing Updated', 'Updated pricing for Order Item ID 8 in Order ID 4', 4, 1, '2026-02-26 03:41:37', '2026-02-26 03:41:37'),
(169, 'Order Pricing Updated', 'Updated pricing for Order Item ID 9 in Order ID 4', 4, 1, '2026-02-26 03:41:40', '2026-02-26 03:41:40'),
(170, 'Invoice Created', 'Invoice INV-2026-0001 created for 3 deliveries. Total: $247.50', 4, 1, '2026-02-26 03:55:03', '2026-02-26 03:55:03'),
(171, 'Invoice Created', 'Invoice INV-2026-0001 created for 3 deliveries. Total: $247.50', 4, 1, '2026-02-26 03:57:47', '2026-02-26 03:57:47'),
(172, 'Invoice Created', 'Invoice INV-2026-0001 created for 3 deliveries. Total: $247.50', 4, 1, '2026-02-26 03:58:51', '2026-02-26 03:58:51'),
(173, 'Invoice Created', 'Invoice INV-2026-0001 created for 3 deliveries. Total: $247.50', 4, 1, '2026-02-26 04:01:10', '2026-02-26 04:01:10'),
(174, 'Order Created', 'Order ID 5 created by Client John Doe', 5, 111, '2026-02-26 04:14:52', '2026-02-26 04:14:52'),
(175, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 11', 5, 1, '2026-02-26 04:15:21', '2026-02-26 04:15:21'),
(176, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 12', 5, 1, '2026-02-26 04:15:24', '2026-02-26 04:15:24'),
(177, 'Supplier Assigned to Order', 'Supplier Kane Hauser assigned to item ID 13', 5, 1, '2026-02-26 04:15:26', '2026-02-26 04:15:26'),
(178, 'Order Pricing Updated', 'Updated pricing for Order Item ID 11 in Order ID 5', 5, 1, '2026-02-26 04:15:33', '2026-02-26 04:15:33'),
(179, 'Order Pricing Updated', 'Updated pricing for Order Item ID 12 in Order ID 5', 5, 1, '2026-02-26 04:15:36', '2026-02-26 04:15:36'),
(180, 'Order Pricing Updated', 'Updated pricing for Order Item ID 13 in Order ID 5', 5, 1, '2026-02-26 04:15:40', '2026-02-26 04:15:40'),
(181, 'Order Pricing Updated', 'Updated pricing for Order Item ID 11 in Order ID 5', 5, 1, '2026-02-26 04:18:41', '2026-02-26 04:18:41'),
(182, 'Order Pricing Updated', 'Updated pricing for Order Item ID 12 in Order ID 5', 5, 1, '2026-02-26 04:18:45', '2026-02-26 04:18:45'),
(183, 'Order Pricing Updated', 'Updated pricing for Order Item ID 13 in Order ID 5', 5, 1, '2026-02-26 04:18:49', '2026-02-26 04:18:49'),
(184, 'Order Created', 'Order ID 6 created by Client Client', 6, 13, '2026-02-26 04:25:25', '2026-02-26 04:25:25'),
(185, 'Order Pricing Updated', 'Updated pricing for Order Item ID 14 in Order ID 6', 6, 1, '2026-02-26 04:25:58', '2026-02-26 04:25:58'),
(186, 'Invoice Created', 'Invoice INV-2026-0002 created for 3 deliveries. Total: $35970.00', 5, 1, '2026-02-26 04:27:02', '2026-02-26 04:27:02'),
(187, 'Invoice Created', 'Invoice INV-2026-0003 created for 1 deliveries. Total: $135.30', 6, 1, '2026-02-26 04:27:02', '2026-02-26 04:27:02'),
(188, 'Admin Added Order Items', 'Admin  added items to Order #6: 10/7 mm Aggregate (qty: 3)', 6, 1, '2026-02-26 04:33:23', '2026-02-26 04:33:23'),
(189, 'Order Created', 'Order ID 7 created by Client Client', 7, 13, '2026-02-26 04:51:02', '2026-02-26 04:51:02'),
(190, 'Order Pricing Updated', 'Updated pricing for Order Item ID 16 in Order ID 7', 7, 1, '2026-02-26 04:51:26', '2026-02-26 04:51:26'),
(191, 'Invoice Created', 'Invoice INV-2026-0004 created for 1 deliveries. Total: $56.10', 7, 1, '2026-02-26 04:52:02', '2026-02-26 04:52:02'),
(192, 'Admin Updated Order Items', 'Admin  updated items in Order #7: 10/7 mm Aggregate: qty 3 → 1', 7, 1, '2026-02-26 05:03:00', '2026-02-26 05:03:00'),
(193, 'Admin Updated Order Items', 'Admin  updated items in Order #6: 1/4 Minus ( Fine road base ) 1 tonne: qty 2 → 3', 6, 1, '2026-02-26 05:04:15', '2026-02-26 05:04:15'),
(194, 'Order Created', 'Order ID 8 created by Client Client', 8, 13, '2026-02-26 05:07:16', '2026-02-26 05:07:16'),
(195, 'Order Pricing Updated', 'Updated pricing for Order Item ID 17 in Order ID 8', 8, 1, '2026-02-26 05:07:27', '2026-02-26 05:07:27'),
(196, 'Invoice Created', 'Invoice INV-2026-0001 created for 1 deliveries. Total: $135.30', 8, 1, '2026-02-26 05:08:02', '2026-02-26 05:08:02'),
(197, 'Admin Updated Order Items', 'Admin  updated items in Order #8: 1/4 Minus ( Fine road base ) 1 tonne: qty 3 → 2', 8, 1, '2026-02-26 05:08:32', '2026-02-26 05:08:32'),
(198, 'Admin Updated Order Items', 'Admin  updated items in Order #8: 1/4 Minus ( Fine road base ) 1 tonne: qty 2 → 3', 8, 1, '2026-02-26 05:09:08', '2026-02-26 05:09:08'),
(199, 'Admin Updated Order Items', 'Admin  updated items in Order #8: 1/4 Minus ( Fine road base ) 1 tonne: qty 3 → 2', 8, 1, '2026-02-26 05:12:37', '2026-02-26 05:12:37'),
(200, 'Admin Updated Order Items', 'Admin  updated items in Order #8: 1/4 Minus ( Fine road base ) 1 tonne: qty 2 → 3', 8, 1, '2026-02-26 05:13:16', '2026-02-26 05:13:16'),
(201, 'Order Created', 'Order ID 9 created by Client Aiza chan', 9, 110, '2026-02-26 06:20:55', '2026-02-26 06:20:55'),
(202, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 18', 9, 109, '2026-02-26 06:21:34', '2026-02-26 06:21:34'),
(203, 'Admin Updated Order Items', 'Admin  updated items in Order #9: 10mm Bluemetal 1/2 m3: qty 1000 → 1000', 9, 109, '2026-02-26 06:22:55', '2026-02-26 06:22:55'),
(204, 'Order Pricing Updated', 'Updated pricing for Order Item ID 18 in Order ID 9', 9, 109, '2026-02-26 06:23:17', '2026-02-26 06:23:17'),
(205, 'Invoice Created', 'Invoice INV-2026-0002 created for 1 deliveries. Total: $44770.00', 9, 1, '2026-02-26 06:24:03', '2026-02-26 06:24:03'),
(206, 'Order Created', 'Order ID 10 created by Client Client', 10, 13, '2026-02-26 23:48:11', '2026-02-26 23:48:11'),
(207, 'Supplier Assigned to Order', 'Supplier Kane Hauser assigned to item ID 19', 10, 1, '2026-02-26 23:49:20', '2026-02-26 23:49:20'),
(208, 'Order Payment Status Updated', 'Payment status for Order ID 10 updated to Paid', 10, 1, '2026-02-26 23:50:11', '2026-02-26 23:50:11'),
(209, 'Invoice Created', 'Invoice INV-2026-0003 created for 1 deliveries. Total: $9405.00', 10, 1, '2026-02-26 23:50:39', '2026-02-26 23:50:39'),
(210, 'Invoice Created', 'Invoice INV-2026-0004 created for 1 deliveries. Total: $135.30', 8, 1, '2026-02-27 00:00:13', '2026-02-27 00:00:13'),
(211, 'Order Created', 'Order ID 11 created by Client Client', 11, 13, '2026-03-03 23:55:00', '2026-03-03 23:55:00'),
(212, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 20', 11, 1, '2026-03-03 23:55:18', '2026-03-03 23:55:18'),
(213, 'Order Pricing Updated', 'Updated pricing for Order Item ID 20 in Order ID 11', 11, 1, '2026-03-03 23:56:09', '2026-03-03 23:56:09'),
(214, 'Order Pricing Updated', 'Updated pricing for Order Item ID 21 in Order ID 11', 11, 1, '2026-03-03 23:56:14', '2026-03-03 23:56:14'),
(215, 'Invoice Created', 'Invoice INV-2026-0005 created for 2 deliveries. Total: $51810.00', 11, 1, '2026-03-03 23:57:03', '2026-03-03 23:57:03'),
(216, 'Invoice Created', 'Invoice INV-2026-0006 created for 1 deliveries. Total: $135.30', 8, 1, '2026-03-06 00:00:04', '2026-03-06 00:00:04'),
(217, 'Order Status Updated', 'Client Client updated order #11 status to Cancelled', 11, 13, '2026-03-06 05:38:58', '2026-03-06 05:38:58'),
(218, 'Order Created', 'Order ID 12 created by Client Client', 12, 13, '2026-03-07 08:02:24', '2026-03-07 08:02:24'),
(219, 'Supplier Assigned to Order', 'Supplier Leigh Cootes assigned to item ID 22', 12, 1, '2026-03-07 10:11:13', '2026-03-07 10:11:13'),
(220, 'Invoice Created', 'Invoice INV-2026-0007 created for 1 deliveries. Total: $102877.50', 12, 1, '2026-03-07 11:34:21', '2026-03-07 11:34:21'),
(221, 'Order Item Pricing Updated By Supplier', 'Supplier ID 84 updated pricing for Order Item ID 22 in Order ID 12', 12, 84, '2026-03-07 11:51:54', '2026-03-07 11:51:54'),
(222, 'Order Created', 'Order ID 13 created by Client Client', 13, 13, '2026-03-09 06:50:29', '2026-03-09 06:50:29'),
(223, 'Supplier Assigned to Order', 'Supplier Leigh Cootes assigned to item ID 23', 13, 1, '2026-03-09 06:51:02', '2026-03-09 06:51:02'),
(224, 'Order Pricing Updated', 'Updated pricing for Order Item ID 23 in Order ID 13', 13, 1, '2026-03-09 06:51:22', '2026-03-09 06:51:22'),
(225, 'Order Created', 'Order ID 14 created by Client Client', 14, 13, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(226, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 25', 14, 1, '2026-03-10 03:34:41', '2026-03-10 03:34:41'),
(227, 'Order Pricing Updated', 'Updated pricing for Order Item ID 24 in Order ID 14', 14, 1, '2026-03-10 03:34:54', '2026-03-10 03:34:54'),
(228, 'Order Pricing Updated', 'Updated pricing for Order Item ID 25 in Order ID 14', 14, 1, '2026-03-10 03:35:00', '2026-03-10 03:35:00'),
(229, 'Invoice Created', 'Invoice INV-2026-0008 created for 1 deliveries. Total: $21615.00', 14, 1, '2026-03-10 03:35:03', '2026-03-10 03:35:03'),
(230, 'Invoice Created', 'Invoice INV-2026-0009 created for 5 deliveries. Total: $30195.00', 14, 1, '2026-03-10 03:36:12', '2026-03-10 03:36:12'),
(231, 'Order Archived', 'Client Client archived order #14', 14, 13, '2026-03-10 03:40:48', '2026-03-10 03:40:48'),
(232, 'Order Archived', 'Client Client archived order #13', 13, 13, '2026-03-10 03:40:52', '2026-03-10 03:40:52'),
(233, 'Order Created', 'Order ID 15 created by Client Client', 15, 13, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(234, 'Supplier Assigned to Order', 'Supplier Kevin Dowling | Tayne Wilson assigned to item ID 26', 15, 1, '2026-03-10 05:38:49', '2026-03-10 05:38:49'),
(235, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 27', 15, 1, '2026-03-10 05:38:53', '2026-03-10 05:38:53'),
(236, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 28', 15, 1, '2026-03-10 05:38:57', '2026-03-10 05:38:57'),
(237, 'Order Pricing Updated', 'Updated pricing for Order Item ID 26 in Order ID 15', 15, 1, '2026-03-10 05:39:07', '2026-03-10 05:39:07'),
(238, 'Order Pricing Updated', 'Updated pricing for Order Item ID 27 in Order ID 15', 15, 1, '2026-03-10 05:39:10', '2026-03-10 05:39:10'),
(239, 'Order Pricing Updated', 'Updated pricing for Order Item ID 28 in Order ID 15', 15, 1, '2026-03-10 05:39:14', '2026-03-10 05:39:14'),
(240, 'Invoice Created', 'Invoice INV-2026-0010 created for 4 deliveries. Total: $61545.00', 15, 1, '2026-03-10 05:40:54', '2026-03-10 05:40:54'),
(241, 'Order Created', 'Order ID 16 created by Client Client', 16, 13, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(242, 'Supplier Assigned to Order', 'Supplier Kevin Dowling | Tayne Wilson assigned to item ID 29', 16, 1, '2026-03-11 05:32:03', '2026-03-11 05:32:03'),
(243, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 30', 16, 1, '2026-03-11 05:32:06', '2026-03-11 05:32:06'),
(244, 'Order Pricing Updated', 'Updated pricing for Order Item ID 29 in Order ID 16', 16, 1, '2026-03-11 05:32:55', '2026-03-11 05:32:55'),
(245, 'Order Pricing Updated', 'Updated pricing for Order Item ID 30 in Order ID 16', 16, 1, '2026-03-11 05:33:02', '2026-03-11 05:33:02'),
(246, 'Invoice Created', 'Invoice INV-2026-0011 created for 5 deliveries. Total: $51397.50', 16, 1, '2026-03-11 05:33:37', '2026-03-11 05:33:37'),
(247, 'Order Archived', 'Client Client archived order #16', 16, 13, '2026-03-12 02:46:14', '2026-03-12 02:46:14'),
(248, 'Order Archived', 'Client Client archived order #15', 15, 13, '2026-03-12 02:46:17', '2026-03-12 02:46:17'),
(249, 'Order Archived', 'Client Client archived order #12', 12, 13, '2026-03-12 02:46:22', '2026-03-12 02:46:22'),
(250, 'Order Archived', 'Client Client archived order #11', 11, 13, '2026-03-12 02:46:26', '2026-03-12 02:46:26'),
(251, 'Order Created', 'Order ID 17 created by Client Client', 17, 13, '2026-03-17 02:42:03', '2026-03-17 02:42:03'),
(252, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 31', 17, 1, '2026-03-17 02:57:13', '2026-03-17 02:57:13'),
(253, 'Order Pricing Updated', 'Updated pricing for Order Item ID 31 in Order ID 17', 17, 1, '2026-03-25 03:46:02', '2026-03-25 03:46:02'),
(254, 'Order Created', 'Order ID 18 created by Client Client', 18, 13, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(255, 'Order Created', 'Order ID 19 created by Client Client', 19, 13, '2026-03-26 04:53:58', '2026-03-26 04:53:58'),
(256, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 33', 19, 1, '2026-03-26 05:00:12', '2026-03-26 05:00:12'),
(257, 'Order Pricing Updated', 'Updated pricing for Order Item ID 33 in Order ID 19', 19, 1, '2026-03-26 05:01:01', '2026-03-26 05:01:01'),
(258, 'Admin Updated Order Items', 'Admin  updated items in Order #19: Concrete premix 10mm: qty 120 → 120', 19, 1, '2026-03-26 05:05:13', '2026-03-26 05:05:13'),
(259, 'Invoice Created', 'Invoice INV-2026-0012 created for 1 deliveries. Total: $23900.00', 19, 1, '2026-03-26 05:06:47', '2026-03-26 05:06:47'),
(260, 'Invoice Created', 'Invoice INV-2026-0013 created for 1 deliveries. Total: $0.00', 18, 1, '2026-03-26 05:55:37', '2026-03-26 05:55:37'),
(261, 'Invoice Created', 'Invoice INV-2026-0001 created for 1 deliveries. Total: $0.00', 18, 1, '2026-03-26 06:00:56', '2026-03-26 06:00:56'),
(262, 'Invoice Created', 'Invoice INV-2026-0001 created for 1 deliveries. Total: $0.00', 18, 1, '2026-03-26 06:03:21', '2026-03-26 06:03:21'),
(263, 'Invoice Created', 'Invoice INV-2026-0001 created for 1 deliveries. Total: $0.00', 18, 1, '2026-03-26 06:09:40', '2026-03-26 06:09:40'),
(264, 'Order Created', 'Order ID 20 created by Client Client', 20, 13, '2026-03-26 07:54:48', '2026-03-26 07:54:48'),
(265, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 34', 20, 1, '2026-03-26 07:58:02', '2026-03-26 07:58:02'),
(266, 'Order Created', 'Order ID 21 created by Client Client', 21, 13, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(267, 'Invoice Created', 'Invoice INV-2026-0002 created for 1 deliveries. Total: $24200.00', 19, 1, '2026-03-27 00:00:04', '2026-03-27 00:00:04'),
(268, 'Order Created', 'Order ID 22 created by Client Client', 22, 13, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(269, 'Admin Updated Order Items', 'Admin  updated items in Order #22: Concrete premix 10mm: qty 1 → 30', 22, 1, '2026-03-31 07:42:17', '2026-03-31 07:42:17'),
(270, 'Admin Updated Order Items', 'Admin  updated items in Order #22: Concrete Mix 10mm: qty 1 → 30', 22, 1, '2026-03-31 07:43:26', '2026-03-31 07:43:26'),
(271, 'Order Created', 'Order ID 23 created by Client Client', 23, 13, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(272, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 39', 23, 1, '2026-04-01 04:26:59', '2026-04-01 04:26:59'),
(273, 'Admin Updated Order Items', 'Admin  updated items in Order #23: Concrete premix 10mm: qty 30 → 30', 23, 1, '2026-04-01 07:50:22', '2026-04-01 07:50:22'),
(274, 'Admin Updated Order Items', 'Admin  updated items in Order #23: Concrete premix 10mm: qty 30 → 30', 23, 1, '2026-04-01 07:51:01', '2026-04-01 07:51:01'),
(275, 'Invoice Created', 'Invoice INV-2026-0003 created for 6 deliveries. Total: $6490.00', 23, 1, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(276, 'Order Created', 'Order ID 24 created by Client Client', 24, 13, '2026-04-09 05:10:30', '2026-04-09 05:10:30'),
(277, 'Supplier Assigned to Order', 'Supplier Tim Mcloughlin assigned to item ID 41', 24, 1, '2026-04-09 06:40:51', '2026-04-09 06:40:51'),
(278, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 42', 24, 1, '2026-04-09 06:40:53', '2026-04-09 06:40:53'),
(279, 'Admin Updated Order Items', 'Admin  updated items in Order #24: 10/7 mm Aggregate: qty 10 → 10', 24, 1, '2026-04-14 06:53:47', '2026-04-14 06:53:47'),
(280, 'Admin Updated Order Items', 'Admin  updated items in Order #24: 10/7 mm Aggregate: qty 10 → 10', 24, 1, '2026-04-14 06:53:55', '2026-04-14 06:53:55'),
(281, 'Admin Updated Order Items', 'Admin  updated items in Order #24: 10/7 mm Aggregate: qty 10 → 10', 24, 1, '2026-04-14 06:56:59', '2026-04-14 06:56:59'),
(282, 'Admin Updated Order Items', 'Admin  updated items in Order #24: 10/7 mm Aggregate: qty 10 → 10', 24, 1, '2026-04-14 06:57:21', '2026-04-14 06:57:21'),
(283, 'Admin Updated Order Items', 'Admin  updated items in Order #24: 10/7 mm Aggregate: qty 10 → 10', 24, 1, '2026-04-14 06:58:23', '2026-04-14 06:58:23'),
(284, 'Order Created', 'Order ID 25 created by Client Client', 25, 13, '2026-04-15 07:57:35', '2026-04-15 07:57:35'),
(285, 'Order Created', 'Order ID 26 created by Client Client', 26, 13, '2026-04-15 22:26:25', '2026-04-15 22:26:25'),
(286, 'Supplier Assigned to Order', 'Supplier Kevin Dowling | Tayne Wilson assigned to item ID 44', 26, 1, '2026-04-15 22:27:29', '2026-04-15 22:27:29'),
(287, 'Order Pricing Updated', 'Updated pricing for Order Item ID 44 in Order ID 26', 26, 1, '2026-04-15 22:27:56', '2026-04-15 22:27:56'),
(288, 'Invoice Created', 'Invoice INV-2026-0004 created for 1 deliveries. Total: $9372.00', 26, 1, '2026-04-15 22:30:04', '2026-04-15 22:30:04'),
(289, 'Order Created', 'Order ID 27 created by Client Client', 27, 13, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(290, 'Invoice Created', 'Invoice INV-2026-0005 created for 1 deliveries. Total: $9372.00', 26, 1, '2026-04-16 00:00:04', '2026-04-16 00:00:04'),
(291, 'Admin Updated Order Items', 'Admin  updated items in Order #25: Hydrocell 40 Podium Soil (Bulka Bag): qty 5 → 5', 25, 1, '2026-04-16 05:32:32', '2026-04-16 05:32:32'),
(292, 'Order Created', 'Order ID 28 created by Client Client', 28, 13, '2026-04-16 06:29:53', '2026-04-16 06:29:53'),
(293, 'Supplier Assigned to Order', 'Supplier Internal Sales assigned to item ID 46', 28, 1, '2026-04-16 06:31:27', '2026-04-16 06:31:27'),
(294, 'Admin Updated Order Items', 'Admin  updated items in Order #28: 10mm Birralee Cream Pebble: qty 10 → 10', 28, 1, '2026-04-16 06:33:01', '2026-04-16 06:33:01'),
(295, 'Invoice Created', 'Invoice INV-2026-0006 created for 1 deliveries. Total: $4686.00', 26, 1, '2026-04-17 00:00:03', '2026-04-17 00:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Cats', '2025-10-08 16:46:14', '2025-10-08 16:53:28'),
(2, 'Category 2', '2025-10-08 16:46:43', '2025-10-08 16:46:43'),
(3, 'Category 3', '2025-10-08 16:46:47', '2025-10-08 16:46:47'),
(4, 'Category 4', '2025-10-08 16:46:49', '2025-10-08 16:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Rindean Quarry', '2026-01-09 00:35:27', '2026-01-09 00:35:27'),
(2, 'Walker Quarries', '2026-01-09 00:35:27', '2026-01-09 00:35:27'),
(3, 'WSC Quarries', '2026-01-09 00:35:27', '2026-01-09 00:35:27'),
(4, 'Gow Street Recycling Centre', '2026-01-09 00:35:27', '2026-01-09 00:35:27'),
(5, 'BC Sands', '2026-01-09 00:35:28', '2026-01-09 00:35:28'),
(6, 'Turtle Nursery & Landscape Supplies', '2026-01-09 00:35:28', '2026-01-09 00:35:28'),
(7, 'Elite Group', '2026-01-09 00:35:28', '2026-01-09 00:35:28'),
(8, 'Ace Landscapes', '2026-01-09 00:35:29', '2026-01-09 00:35:29'),
(9, 'Kincumber Sand & Soil', '2026-01-09 00:35:29', '2026-01-09 00:35:29'),
(10, 'Four Seasons Nursery', '2026-01-09 00:35:29', '2026-01-09 00:35:29'),
(11, 'Northshore Sand & Cement', '2026-01-09 00:35:29', '2026-01-09 00:35:29'),
(12, 'Darwin Block Company (Humpty Doo)', '2026-01-09 00:35:30', '2026-01-09 00:35:30'),
(13, 'Paradise Group – Landscape Supplies & Nursery', '2026-01-09 00:35:30', '2026-01-09 00:35:30'),
(14, 'Bunnings Warehouse', '2026-01-09 00:35:30', '2026-01-09 00:35:30'),
(15, 'Rural Garden Supplies Humpty Doo', '2026-01-09 00:35:30', '2026-01-09 00:35:30'),
(16, 'Shuker Farm Mulch Hay (Darwin River)', '2026-01-09 00:35:31', '2026-01-09 00:35:31'),
(17, 'NT Hauliers', '2026-01-09 00:35:31', '2026-01-09 00:35:31'),
(18, 'Peel Resource Recovery – Pinjarra', '2026-01-09 00:35:31', '2026-01-09 00:35:31'),
(19, 'Capital Recycling – Welshpool', '2026-01-09 00:35:32', '2026-01-09 00:35:32'),
(20, 'Capital Recycling – Postans', '2026-01-09 00:35:32', '2026-01-09 00:35:32'),
(21, 'HALS Tasmania (Horticultural & Landscape Supplies)', '2026-01-09 00:35:32', '2026-01-09 00:35:32'),
(22, 'Best Mix Garden Supplies', '2026-01-09 00:35:32', '2026-01-09 00:35:32'),
(23, 'Goods Landscaping & Water Deliveries', '2026-01-09 00:35:33', '2026-01-09 00:35:33'),
(24, 'Stoneman\'s Garden Centre (Glenorchy)', '2026-01-09 00:35:33', '2026-01-09 00:35:33'),
(25, 'TasMulch (Longford)', '2026-01-09 00:35:33', '2026-01-09 00:35:33'),
(26, 'Glebe Gardens Landscape Supplies (Launceston)', '2026-01-09 00:35:34', '2026-01-09 00:35:34'),
(27, 'Edwards Landscaping Supplies (Wynyard)', '2026-01-09 00:35:34', '2026-01-09 00:35:34'),
(28, 'Castella Quarries', '2026-01-09 00:35:34', '2026-01-09 00:35:34'),
(29, 'Cootes Quarry', '2026-01-09 00:35:34', '2026-01-09 00:35:34'),
(30, 'Langwarrin Quarries', '2026-01-09 00:35:35', '2026-01-09 00:35:35'),
(31, 'Mt Shadwell Quarry', '2026-01-09 00:35:35', '2026-01-09 00:35:35'),
(32, 'Werribee Sand-Soil & Mini Mix Concrete', '2026-01-09 00:35:35', '2026-01-09 00:35:35'),
(33, 'Melbourne\'s Cheapest Soils', '2026-01-09 00:35:36', '2026-01-09 00:35:36'),
(34, 'Victorian Bluestone Quarries', '2026-01-09 00:35:36', '2026-01-09 00:35:36'),
(35, 'Hy-Tec Concrete & Aggregates', '2026-01-09 00:35:36', '2026-01-09 00:35:36'),
(36, 'Boral West Burleigh Quarry', '2026-01-09 00:35:37', '2026-01-09 00:35:37'),
(37, 'NuGrow', '2026-01-09 00:35:37', '2026-01-09 00:35:37'),
(38, 'Nuway Landscape Supplies Pavers & Walls', '2026-01-09 00:35:37', '2026-01-09 00:35:37'),
(39, 'Western Landscape Supplies', '2026-01-09 00:35:38', '2026-01-09 00:35:38'),
(40, 'Redland Soil, Sand & Gravel', '2026-01-09 00:35:38', '2026-01-09 00:35:38'),
(41, 'Logan Soils & Landscape Supplies', '2026-01-09 00:35:38', '2026-01-09 00:35:38'),
(42, 'Cobble Patch', '2026-01-09 00:35:38', '2026-01-09 00:35:38'),
(43, 'SEQ Landscape Supplies', '2026-01-09 00:35:39', '2026-01-09 00:35:39'),
(44, 'HUNMAC Landscape Supplies', '2026-01-09 00:35:39', '2026-01-09 00:35:39'),
(45, 'Gardenscapes Landscape Centre', '2026-01-09 00:35:39', '2026-01-09 00:35:39'),
(46, 'Smart Stone Landscape Supplies', '2026-01-09 00:35:40', '2026-01-09 00:35:40'),
(47, 'Adelaide Hills Garden Supplies', '2026-01-09 00:35:40', '2026-01-09 00:35:40'),
(48, 'SA Landscape Supplies', '2026-01-09 00:35:40', '2026-01-09 00:35:40'),
(49, 'Buttrose Landscape & Garden Supplies', '2026-01-09 00:35:40', '2026-01-09 00:35:40'),
(50, 'Capital Recycling', '2026-01-09 00:35:41', '2026-01-09 00:35:41'),
(51, 'Canberra Sand & Gravel Landscape', '2026-01-09 00:35:41', '2026-01-09 00:35:41'),
(52, 'Corkhill Bros', '2026-01-09 00:35:41', '2026-01-09 00:35:41'),
(53, 'Stonehenge Garden & Landscape Centre (Beltana)', '2026-01-09 00:35:42', '2026-01-09 00:35:42'),
(54, 'Canberra Construction Recyclers', '2026-01-09 00:35:42', '2026-01-09 00:35:42'),
(55, 'legally blonde', '2026-01-15 05:55:50', '2026-01-15 05:55:50'),
(56, '', '2026-01-23 02:11:00', '2026-01-23 02:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `failed_invoice_logs`
--

CREATE TABLE `failed_invoice_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_item_delivery_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` text NOT NULL,
  `run_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_invoice_logs`
--

INSERT INTO `failed_invoice_logs` (`id`, `order_id`, `order_item_id`, `order_item_delivery_id`, `reason`, `run_date`, `created_at`, `updated_at`) VALUES
(10, 14, 24, 49, 'Class \"App\\Models\\XeroToken\" not found', '2026-03-10', '2026-03-10 03:35:03', '2026-03-10 03:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Draft','Sent','Paid','Partially Paid','Overdue','Cancelled','Void') NOT NULL DEFAULT 'Draft',
  `issued_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `order_id`, `client_id`, `subtotal`, `delivery_total`, `gst_tax`, `discount`, `total_amount`, `status`, `issued_date`, `due_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(32, 'INV-2026-0001', 18, 13, 0.00, 0.00, 0.00, 0.00, 0.00, 'Paid', '2026-03-26', '2026-03-28', NULL, 1, '2026-03-26 06:09:40', '2026-03-26 06:09:40'),
(33, 'INV-2026-0002', 19, 13, 21600.00, 400.00, 2200.00, 0.00, 24200.00, 'Paid', '2026-03-27', '2026-03-28', 'Generated by Bot', 1, '2026-03-27 00:00:04', '2026-03-27 00:00:04'),
(34, 'INV-2026-0003', 23, 13, 5400.00, 500.00, 590.00, 0.00, 6490.00, 'Paid', '2026-04-06', '2026-04-20', NULL, 1, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(35, 'INV-2026-0004', 26, 13, 8520.00, 0.00, 852.00, 0.00, 9372.00, 'Paid', '2026-04-15', '2026-04-16', 'Generated by Bot', 1, '2026-04-15 22:30:04', '2026-04-15 22:30:04'),
(36, 'INV-2026-0005', 26, 13, 8520.00, 0.00, 852.00, 0.00, 9372.00, 'Paid', '2026-04-16', '2026-04-17', 'Generated by Bot', 1, '2026-04-16 00:00:04', '2026-04-16 00:00:04'),
(37, 'INV-2026-0006', 26, 13, 4260.00, 0.00, 426.00, 0.00, 4686.00, 'Paid', '2026-04-17', '2026-04-18', 'Generated by Bot', 1, '2026-04-17 00:00:03', '2026-04-17 00:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_delivery_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `order_item_id`, `order_item_delivery_id`, `product_name`, `quantity`, `unit_price`, `delivery_cost`, `line_total`, `created_at`, `updated_at`) VALUES
(70, 32, 32, 65, 'N2020', 1.00, 0.00, 0.00, 0.00, '2026-03-26 06:09:40', '2026-03-26 06:09:40'),
(71, 33, 33, 66, 'Concrete premix 10mm', 120.00, 180.00, 400.00, 22000.00, '2026-03-27 00:00:04', '2026-03-27 00:00:04'),
(72, 34, 39, 76, 'Concrete premix 10mm', 10.00, 180.00, 300.00, 2100.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(73, 34, 39, 77, 'Concrete premix 10mm', 10.00, 180.00, 0.00, 1800.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(74, 34, 39, 78, 'Concrete premix 10mm', 10.00, 180.00, 200.00, 2000.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(75, 34, 40, 79, 'Concrete Mix 10mm', 10.00, 0.00, 0.00, 0.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(76, 34, 40, 80, 'Concrete Mix 10mm', 10.00, 0.00, 0.00, 0.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(77, 34, 40, 81, 'Concrete Mix 10mm', 10.00, 0.00, 0.00, 0.00, '2026-04-06 23:43:10', '2026-04-06 23:43:10'),
(78, 35, 44, 85, 'N4020', 20.00, 426.00, 0.00, 8520.00, '2026-04-15 22:30:04', '2026-04-15 22:30:04'),
(79, 36, 44, 86, 'N4020', 20.00, 426.00, 0.00, 8520.00, '2026-04-16 00:00:04', '2026-04-16 00:00:04'),
(80, 37, 44, 87, 'N4020', 10.00, 426.00, 0.00, 4260.00, '2026-04-17 00:00:03', '2026-04-17 00:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_products`
--

CREATE TABLE `master_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `added_by` int(11) UNSIGNED NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `category` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_type` varchar(120) NOT NULL,
  `specifications` text DEFAULT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `delivery_method` varchar(100) DEFAULT NULL,
  `tech_doc` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_products`
--

INSERT INTO `master_products` (`id`, `added_by`, `is_approved`, `approved_by`, `slug`, `category`, `product_name`, `product_type`, `specifications`, `unit_of_measure`, `delivery_method`, `tech_doc`, `photo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '25mm-stoodley-roadbase-zJ5Bg4', 1, '25mm Stoodley Roadbase', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:45:45'),
(2, 1, 1, 1, '25mm-birralee-cream-roadbase-EsLiEk', 1, '25mm Birralee Cream Roadbase', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:45:54'),
(3, 1, 1, 1, '25mm-lee-rock-roadbase-ipKOC0', 1, '25mm Lee Rock Roadbase', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:46:07'),
(4, 1, 1, 1, '25mm-bluemetal-roadbase-W0ldTr', 1, '25mm Bluemetal Roadbase', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:46:19'),
(5, 1, 1, 1, '10mm-bluemetal-GsyvIz', 1, '10mm Bluemetal', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:14:09'),
(6, 1, 1, 1, '20mm-bluemetal-NeSk4A', 1, '20mm Bluemetal', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:16:48'),
(7, 1, 1, 1, '25mm-stoodley-pebble-40aD0h', 1, '25mm Stoodley Pebble', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 22:46:35'),
(8, 1, 1, 1, '10mm-birralee-cream-pebble-NEVbWO', 1, '10mm Birralee Cream Pebble', 'Aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-12 23:02:58'),
(9, 1, 1, 1, '10mm-ice-rock-pebble-0PDoVS', 1, '10mm Ice Rock Pebble', 'Aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:07', '2026-04-13 05:11:45'),
(10, 1, 1, 1, '10mm-southern-gold-pebble-hmQKHb', 1, '10mm Southern Gold Pebble', 'Aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-13 05:12:01'),
(11, 1, 1, 1, '10mm-concrete-gravel-JXxPT8', 1, '10mm Concrete Gravel', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-13 05:14:54'),
(12, 1, 1, 1, 'brickies-sand-sWKZxC', 1, 'Brickies Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:34:23'),
(13, 1, 1, 1, 'certified-soft-fall-sand-dq2rnS', 1, 'Certified Soft Fall Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:00'),
(14, 1, 1, 1, 'course-washed-sand-1-2mm-ffgPeo', 1, 'Course Washed Sand 1-2mm', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:15'),
(15, 1, 1, 1, 'cracker-dust-6zJqox', 1, 'Cracker Dust', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:27'),
(16, 1, 1, 1, 'fill-sand-cESrOQ', 1, 'Fill Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:38'),
(17, 1, 1, 1, 'hornsfel-20mm-USVORC', 1, 'Hornsfel 20mm', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:49'),
(18, 1, 1, 1, 'hornsfel-40mm-xOgHEN', 1, 'Hornsfel 40mm', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:35:59'),
(19, 1, 1, 1, 'dolomite-limestone-20-40mm-EXQ4m3', 1, 'Dolomite Limestone 20-40mm', 'Aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:37:23'),
(20, 1, 1, 1, 'local-river-pebbles-150mm-lI9q3v', 1, 'Local River Pebbles 150mm', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:37:36'),
(21, 1, 1, 1, 'mount-bundy-granite-20-30mm-q5OJhi', 1, 'Mount Bundy Granite 20-30mm', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:37:48'),
(22, 1, 1, 1, 'porcelanite-small-boulders-uJtour', 1, 'Porcelanite Small Boulders', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:37:58'),
(23, 1, 1, 1, 'porcelanite-20-40mm-sUILaU', 1, 'Porcelanite 20-40mm', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:40:02'),
(24, 1, 1, 1, 'river-pebbles-10mm-DERuuv', 1, 'River Pebbles 10mm', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:40:13'),
(25, 1, 1, 1, 'mahogany-mulch-a5GTTj', 1, 'Mahogany Mulch', 'Mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:40:28'),
(26, 1, 1, 1, 'forest-mulch-F6v9Hf', 1, 'Forest Mulch', 'Mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:40:43'),
(27, 1, 1, 1, 'garden-blend-ILUzgi', 1, 'Garden Blend', 'Mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:40:57'),
(28, 1, 1, 1, 'screened-mulch-EoD8Bo', 1, 'Screened Mulch', 'Mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:41:08'),
(29, 1, 1, 1, 'general-fill', 1, 'General Fill', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(30, 1, 1, 1, 'granite-cracker-dust', 1, 'Granite Cracker Dust', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(31, 1, 1, 1, 'ultra-fine-washed-sandbrickies-sand', 1, 'Ultra Fine Washed Sand/Brickies Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(32, 1, 1, 1, 'fines-washed-sand', 1, 'Fines Washed Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(33, 1, 1, 1, 'medium-washed-sand', 1, 'Medium Washed Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(34, 1, 1, 1, 'coarse-washed-sand', 1, 'Coarse Washed Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(35, 1, 1, 1, 'fill-sand', 1, 'Fill Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(36, 1, 1, 1, 'granite-type-2-roadbase-Uvezmu', 1, 'Granite Type 2 Roadbase', 'Road Base', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-04-14 02:45:15'),
(37, 1, 1, 1, 'mt-bundy-granite-5mm', 1, 'Mt Bundy Granite 5mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(38, 1, 1, 1, 'purple-haze-10mm', 1, 'Purple Haze 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(39, 1, 1, 1, 'mt-bundy-granite-10mm', 1, 'Mt Bundy Granite 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(40, 1, 1, 1, '22mm-katherine-limestone', 1, '22mm Katherine Limestone', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(41, 1, 1, 1, 'arafura-blue-granite-20mm', 1, 'Arafura Blue Granite 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(42, 1, 1, 1, 'purple-haze-20mm', 1, 'Purple Haze 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(43, 1, 1, 1, 'mt-bundy-granite-20mm', 1, 'Mt Bundy Granite 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(44, 1, 1, 1, 'purple-haze-3050mm', 1, 'Purple Haze 30/50mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(45, 1, 1, 1, 'mt-bundy-granite-40mm', 1, 'Mt Bundy Granite 40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(46, 1, 1, 1, 'mt-bundy-granite-6575mm', 1, 'Mt Bundy Granite 65/75mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(47, 1, 1, 1, 'granite-boarder-rocks-250400', 1, 'Granite Boarder Rocks 250/400', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(48, 1, 1, 1, 'mahogany-mulch', 1, 'Mahogany Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(49, 1, 1, 1, 'mulching-hay-bales', 1, 'Mulching Hay Bales', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(50, 1, 1, 1, 'tyoe-a-topsoil', 1, 'Tyoe A Topsoil', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(51, 1, 1, 1, 'cow-manure', 1, 'Cow Manure', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(52, 1, 1, 1, 'forest-mulch', 1, 'Forest Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(53, 1, 1, 1, 'garden-blend', 1, 'Garden Blend', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(54, 1, 1, 1, 'manure-mulch', 1, 'Manure Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(55, 1, 1, 1, 'premium-potting-mix', 1, 'Premium Potting Mix', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(56, 1, 1, 1, 'standard-potting-mix', 1, 'Standard Potting Mix', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(57, 1, 1, 1, 'concrete-premix-10mm', 1, 'Concrete premix 10mm', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(58, 1, 1, 1, 'concrete-premix-20mm', 1, 'Concrete premix 20mm', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(59, 1, 1, 1, 'coarse-propagation-sand-white', 1, 'Coarse Propagation Sand - White', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(60, 1, 1, 1, 'coarse-propagation-sand-yellow', 1, 'Coarse Propagation Sand - Yellow', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(61, 1, 1, 1, 'washed-bedding-sand', 1, 'Washed Bedding Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(62, 1, 1, 1, 'washed-sharp-sand', 1, 'Washed Sharp Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(63, 1, 1, 1, 'fat-sand-yellow', 1, 'Fat Sand - Yellow', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(64, 1, 1, 1, 'screened-bedding', 1, 'Screened Bedding', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(65, 1, 1, 1, 'salamanca-stone', 1, 'Salamanca Stone', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(66, 1, 1, 1, 'limestone-gold', 1, 'Limestone Gold', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(67, 1, 1, 1, 'costal-pathway', 1, 'Costal Pathway', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(68, 1, 1, 1, 'ice-rock-20mm', 1, 'Ice Rock 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(69, 1, 1, 1, 'stoodley-rock-20mm', 1, 'Stoodley Rock 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(70, 1, 1, 1, 'river-shingle', 1, 'River Shingle', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(71, 1, 1, 1, 'scoria-volcanic-rock', 1, 'Scoria (Volcanic Rock)', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(72, 1, 1, 1, 'red-driveway-gravel', 1, 'Red Driveway Gravel', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(73, 1, 1, 1, 'concrete-mix', 1, 'Concrete mix', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(74, 1, 1, 1, 'limestone-white', 1, 'Limestone White', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(75, 1, 1, 1, 'iron-stone-12mm', 1, 'Iron Stone 12mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(76, 1, 1, 1, 'salt-and-pepper-blend', 1, 'Salt and Pepper Blend', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(77, 1, 1, 1, 'blue-metal-dust-0-5mm', 1, 'Blue Metal Dust 0-5mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(78, 1, 1, 1, 'blue-metal-clean-5mm', 1, 'Blue Metal Clean 5mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(79, 1, 1, 1, 'blue-metal-clean-7mm', 1, 'Blue Metal Clean 7mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(80, 1, 1, 1, 'blue-metal-clean-10mm', 1, 'Blue Metal Clean 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(81, 1, 1, 1, 'blue-metal-clean-14mm', 1, 'Blue Metal Clean 14mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(82, 1, 1, 1, 'blue-metal-clean-20mm', 1, 'Blue Metal Clean 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(83, 1, 1, 1, 'blue-metal-clean-40mm', 1, 'Blue Metal Clean 40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(84, 1, 1, 1, 'fcr-0-20mm', 1, 'FCR 0-20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(85, 1, 1, 1, 'road-base-40mm', 1, 'Road Base 40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(86, 1, 1, 1, 'screened-loam', 1, 'Screened Loam', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(87, 1, 1, 1, 'garden-soil-mix', 1, 'Garden Soil Mix', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(88, 1, 1, 1, 'lawn-turf-blend', 1, 'Lawn & Turf Blend', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(89, 1, 1, 1, 'screened-top-soil', 1, 'Screened Top Soil', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(90, 1, 1, 1, 'softfall-pine-bark-0-10mm', 1, 'Softfall Pine Bark 0-10mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(91, 1, 1, 1, 'boyer-tan-landscape-bark-unscreened', 1, 'Boyer Tan Landscape bark Unscreened', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(92, 1, 1, 1, 'graded-coarse-landscape-bark', 1, 'Graded Coarse Landscape Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(93, 1, 1, 1, 'screened-boyer-tan-pine-bark', 1, 'Screened Boyer Tan Pine Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(94, 1, 1, 1, 'medium-bark-15mm', 1, 'Medium Bark 15mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(95, 1, 1, 1, 'clean-eucalyptus-wood-chips', 1, 'Clean Eucalyptus Wood Chips', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(96, 1, 1, 1, 'water-saver-feeder-mulch', 1, 'Water Saver & Feeder Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(97, 1, 1, 1, 'aged-cut-gum-bark-triple-cut', 1, 'Aged Cut Gum Bark (Triple Cut)', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(98, 1, 1, 1, 'coloured-mulch-red', 1, 'Coloured Mulch Red', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(99, 1, 1, 1, 'coloured-mulch-black', 1, 'Coloured Mulch Black', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(100, 1, 1, 1, 'clean-landscape-bark-25mm', 1, 'Clean Landscape Bark 25mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(101, 1, 1, 1, 'sand-coarse-14-m3-per-scoop', 1, 'Sand Coarse 1/4 m3 Per Scoop', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(102, 1, 1, 1, 'sand-sharp-14-m3-per-scoop', 1, 'Sand Sharp 1/4 m3 Per Scoop', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(103, 1, 1, 1, 'concrete-mix-14-m3', 1, 'Concrete Mix 1/4 m3', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(104, 1, 1, 1, 'apricot-rock-20mm-14-m3-per-scoop', 1, 'Apricot Rock 20mm 1/4 m3 Per Scoop', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(105, 1, 1, 1, 'pink-deco-14-m3-per-scoop', 1, 'Pink Deco 1/4 m3 Per Scoop', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(106, 1, 1, 1, 'gum-bark-14-m3-per-scoop', 1, 'Gum Bark 1/4 m3 Per Scoop', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(107, 1, 1, 1, 'pine-bark-composted-14-m3-per-scoop', 1, 'Pine Bark Composted 1/4 m3 Per Scoop', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(108, 1, 1, 1, 'pine-bark-fine-14-m3-per-scoop', 1, 'Pine Bark Fine 1/4 m3 Per Scoop', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(109, 1, 1, 1, 'pine-bark-medium-coarse-14-per-scoop', 1, 'Pine Bark Medium Coarse 1/4 Per Scoop', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(110, 1, 1, 1, 'paving-sand', 1, 'Paving Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(111, 1, 1, 1, 'tasmulch-turf-sand', 1, 'Tasmulch Turf Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(112, 1, 1, 1, 'beach-sand', 1, 'Beach Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(113, 1, 1, 1, 'quartz-sand', 1, 'Quartz Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(114, 1, 1, 1, 'road-base', 1, 'Road Base', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(115, 1, 1, 1, 'crusher-dust', 1, 'Crusher Dust', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(116, 1, 1, 1, 'blue-metal-ballast-40mm', 1, 'Blue Metal Ballast 40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(117, 1, 1, 1, 'blue-metal', 1, 'Blue Metal', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(118, 1, 1, 1, 'quarry-rock-100mm-200mm', 1, 'Quarry Rock 100mm-200mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:08', '2026-03-06 02:58:47'),
(119, 1, 1, 1, 'white-crusher-dust', 1, 'White Crusher Dust', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(120, 1, 1, 1, 'sub-base-50-100mm', 1, 'Sub Base 50-100mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(121, 1, 1, 1, 'white-pebbles', 1, 'White Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(122, 1, 1, 1, 'birralee-cream', 1, 'Birralee Cream', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(123, 1, 1, 1, 'birralee-cream-pathroad-base', 1, 'Birralee Cream Path/Road Base', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(124, 1, 1, 1, 'river-pebbles-30mm', 1, 'River Pebbles 30mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(125, 1, 1, 1, 'pink-rock', 1, 'Pink Rock', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(126, 1, 1, 1, 'ice-rock', 1, 'Ice Rock', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(127, 1, 1, 1, 'chocolate-rock', 1, 'Chocolate Rock', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(128, 1, 1, 1, 'river-pebbles-oversize', 1, 'River Pebbles Oversize', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(129, 1, 1, 1, 'road-base-20mm', 1, 'Road Base 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(130, 1, 1, 1, 'class-3-base-20mm-scalps', 1, 'Class 3 Base 20mm (Scalps)', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(131, 1, 1, 1, 'ice-rock-road-base', 1, 'Ice Rock Road Base', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(132, 1, 1, 1, 'improved-top-soil', 1, 'Improved top Soil', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(133, 1, 1, 1, 'decomposed-granite', 1, 'Decomposed Granite', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(134, 1, 1, 1, 'ice-rock-drive', 1, 'Ice Rock Drive', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(135, 1, 1, 1, 'ice-rock-path', 1, 'Ice Rock Path', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(136, 1, 1, 1, 'raised-bed-mix', 1, 'Raised Bed Mix', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(137, 1, 1, 1, 'tasmulch-turf-topdress', 1, 'Tasmulch Turf Topdress', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(138, 1, 1, 1, 'tasmulch-compost', 1, 'Tasmulch Compost', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(139, 1, 1, 1, 'tasmulch-euchi', 1, 'Tasmulch Euchi', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(140, 1, 1, 1, 'soft-impact-bark-15mm', 1, 'Soft Impact BArk 15mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(141, 1, 1, 1, 'boyer-bark', 1, 'Boyer Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(142, 1, 1, 1, 'medium-bark', 1, 'Medium Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(143, 1, 1, 1, 'soft-bark-10mm', 1, 'Soft BArk 10mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(144, 1, 1, 1, 'potting-bark', 1, 'Potting Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(145, 1, 1, 1, 'whiteyellow', 1, 'White/Yellow', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(146, 1, 1, 1, 'white-lite-brick-sand', 1, 'White Lite Brick Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(147, 1, 1, 1, 'washed-concrete-sand', 1, 'Washed Concrete Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(148, 1, 1, 1, 'fine-white-sand', 1, 'Fine White Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(149, 1, 1, 1, 'packing-sand', 1, 'Packing Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(150, 1, 1, 1, 'kiln-dried-sand', 1, 'Kiln Dried Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(151, 1, 1, 1, 'pvc-edging-42', 1, 'PVC Edging 4.2', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(152, 1, 1, 1, 'crushed-rock-20mm', 1, 'Crushed Rock 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(153, 1, 1, 1, '7mm-plumbers-scoria', 1, '7MM Plumbers Scoria', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(154, 1, 1, 1, 'lilycan-toppings', 1, 'Lilycan Toppings', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(155, 1, 1, 1, 'tuscan-toppings', 1, 'Tuscan Toppings', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(156, 1, 1, 1, '7mm-river-pebbles', 1, '7MM River Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(157, 1, 1, 1, '14mm-river-pebbles', 1, '14MM River Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(158, 1, 1, 1, '20mm-river-pebbles', 1, '20MM River Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(159, 1, 1, 1, 'horizen-7mm', 1, 'Horizen 7MM +', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(160, 1, 1, 1, 'golden-beach-10mm', 1, 'Golden Beach 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(161, 1, 1, 1, '50mm-white-ice', 1, '50mm White Ice', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(162, 1, 1, 1, '20mm-white-ice', 1, '20mm White Ice', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(163, 1, 1, 1, '40mm-granite', 1, '40mm Granite', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(164, 1, 1, 1, '20mm-sunset-pebbles', 1, '20MM Sunset Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(165, 1, 1, 1, '40mm-snow-pebbles', 1, '40mm Snow Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(166, 1, 1, 1, 'large-sunset', 1, 'Large Sunset', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(167, 1, 1, 1, 'eden-pebbles-large', 1, 'Eden Pebbles Large', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(168, 1, 1, 1, '40-80-eden-pebbles', 1, '40-80 Eden Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(169, 1, 1, 1, '20-40-eden-pebbles', 1, '20-40 Eden Pebbles', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(170, 1, 1, 1, '40mm-tuscan-rock', 1, '40mm Tuscan Rock', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(171, 1, 1, 1, '20mm-tuscan-stone', 1, '20mm Tuscan Stone', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(172, 1, 1, 1, 'ironstone', 1, 'Ironstone', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(173, 1, 1, 1, 'sandy-loam', 1, 'Sandy Loam', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(174, 1, 1, 1, 'triple-mix', 1, 'Triple Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(175, 1, 1, 1, 'gypsum', 1, 'Gypsum', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(176, 1, 1, 1, 'blended-soil', 1, 'Blended Soil', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(177, 1, 1, 1, 'hammermill-mulch', 1, 'Hammermill Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(178, 1, 1, 1, 'homestead-bark', 1, 'Homestead Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(179, 1, 1, 1, 'cottage-bark', 1, 'Cottage Bark', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(180, 1, 1, 1, 'red-river-mulch', 1, 'Red River Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(181, 1, 1, 1, 'black-mulch', 1, 'Black Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(182, 1, 1, 1, 'mushroom-compost', 1, 'Mushroom Compost', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(183, 1, 1, 1, 'softfall-mulch', 1, 'Softfall Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(184, 1, 1, 1, 'course-sand', 1, 'Course Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(185, 1, 1, 1, 'play-pit-sand', 1, 'Play Pit Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(186, 1, 1, 1, 'fine-sand', 1, 'Fine Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(187, 1, 1, 1, 'silica-sand', 1, 'Silica Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(188, 1, 1, 1, 'top-dress-sand', 1, 'Top Dress Sand', 'Sands', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(189, 1, 1, 1, 'deco', 1, 'Deco', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(190, 1, 1, 1, 'crushed-concrete', 1, 'Crushed Concrete', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:09', '2026-03-06 02:58:47'),
(191, 1, 1, 1, 'crushes-concrete', 1, 'Crushes Concrete', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(192, 1, 1, 1, 'concrete-mix-10mm', 1, 'Concrete Mix 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(193, 1, 1, 1, 'concrete-mix-20mm', 1, 'Concrete Mix 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(194, 1, 1, 1, 'river-rock-14mm', 1, 'River Rock 14mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(195, 1, 1, 1, 'river-rock-20mm', 1, 'River Rock 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(196, 1, 1, 1, 'river-rock-2540mm', 1, 'River Rock 25/40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(197, 1, 1, 1, 'blue-crushed-10mm', 1, 'Blue Crushed 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(198, 1, 1, 1, 'blue-crushed-20mm', 1, 'Blue Crushed 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(199, 1, 1, 1, 'grey-crushed-10mm', 1, 'Grey Crushed 10mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(200, 1, 1, 1, 'grey-crushed-20mm', 1, 'Grey Crushed 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(201, 1, 1, 1, 'garden-mix', 1, 'Garden Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(202, 1, 1, 1, 'general-purpose-soil', 1, 'General Purpose Soil', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(203, 1, 1, 1, 'lawn-dressing', 1, 'Lawn Dressing', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(204, 1, 1, 1, 'potting-mix', 1, 'Potting Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(205, 1, 1, 1, 'organic-fill', 1, 'Organic fill', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(206, 1, 1, 1, 'course-mulch', 1, 'Course Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(207, 1, 1, 1, 'natural-10mm-drainage-aggregate-blue-metal', 1, 'Natural 10mm Drainage Aggregate (Blue Metal)', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(208, 1, 1, 1, 'natural-2070mm-drainage-aggregate-blue-metal', 1, 'Natural 20/70mm Drainage Aggregate (Blue Metal)', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(209, 1, 1, 1, '35-mm-washed-aggregate', 1, '3/5 mm Washed aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(210, 1, 1, 1, '107-mm-aggregate', 1, '10/7 mm Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(211, 1, 1, 1, '2014-mm-aggregate', 1, '20/14 mm Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(212, 1, 1, 1, 'agg-blend-107-2014-7030', 1, 'Agg Blend 10/7 +20/14 (70/30)', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(213, 1, 1, 1, '4010-mm-drainage-aggregate', 1, '40/10 mm Drainage Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(214, 1, 1, 1, '10mm-blue-metal-loose', 1, '10mm Blue Metal loose', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(215, 1, 1, 1, '10mm-blue-metal-bulk', 1, '10mm Blue Metal bulk', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(216, 1, 1, 1, 'veggie-blend', 1, 'Veggie Blend', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(217, 1, 1, 1, 'native-mix', 1, 'Native Mix', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(218, 1, 1, 1, 'softfall-mulch-playground-certified-eucalyptus-mulch', 1, 'Softfall Mulch – Playground Certified Eucalyptus Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(219, 1, 1, 1, 'white-woodchip', 1, 'White Woodchip', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(220, 1, 1, 1, 'pine-bark-14mm', 1, 'Pine Bark 14mm', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(221, 1, 1, 1, 'elite-eco-mulch', 1, 'Elite Eco Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(222, 1, 1, 1, 'plumbers-mix-7-10mm-1-tonne', 1, 'Plumbers Mix 7-10mm 1 tonne', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(223, 1, 1, 1, 'drainage-mix-14-20mm-1-tonne', 1, 'Drainage Mix 14-20mm 1 tonne', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(224, 1, 1, 1, '14-minus-fine-road-base-1-tonne', 1, '1/4 Minus ( Fine road base ) 1 tonne', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(225, 1, 1, 1, 'metal-slab-fill-fill-road-base-1-tonne', 1, 'Metal Slab Fill ( Fill Road Base ) 1 tonne', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(226, 1, 1, 1, 'drainage-ash-1-m3', 1, 'Drainage Ash 1 m3', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(227, 1, 1, 1, 'organic-mix-premium-1-m3', 1, 'Organic Mix Premium 1 m3', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(228, 1, 1, 1, 'organic-top-dress-nitro-top-1-tonne', 1, 'Organic Top Dress Nitro Top 1 tonne', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(229, 1, 1, 1, 'leaf-mulch-1-m3', 1, 'Leaf Mulch 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(230, 1, 1, 1, 'forest-fines-1-m3', 1, 'Forest Fines 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(231, 1, 1, 1, 'forest-fines-34-m3', 1, 'Forest Fines 3/4 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(232, 1, 1, 1, 'forest-fines-12-m3', 1, 'Forest Fines 1/2 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(233, 1, 1, 1, 'forest-fines-14-m3', 1, 'Forest Fines 1/4 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(234, 1, 1, 1, 'hardwood-chip-1-m3', 1, 'Hardwood Chip 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(235, 1, 1, 1, 'eucy-mulch-1-m3', 1, 'Eucy Mulch 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(236, 1, 1, 1, 'red-hardwood-chip-1-m3', 1, 'Red Hardwood Chip 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(237, 1, 1, 1, 'pine-bark-25mm-1-m3', 1, 'Pine Bark 25mm 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(238, 1, 1, 1, 'pine-bark-10mm-1-m3', 1, 'Pine Bark 10mm 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(239, 1, 1, 1, 'cypress-chip-1-m3', 1, 'Cypress Chip 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(240, 1, 1, 1, 'cypress-mulch-1-m3', 1, 'Cypress Mulch 1 m3', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(241, 1, 1, 1, 'crushed-concrete-0-20mm', 1, 'Crushed Concrete 0-20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(242, 1, 1, 1, 'crushed-concrete-20-40mm', 1, 'Crushed Concrete 20-40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(243, 1, 1, 1, 'metal-dust', 1, 'Metal Dust', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(244, 1, 1, 1, '20mm-blue-metal', 1, '20mm Blue Metal', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(245, 1, 1, 1, 'concrete-gravel', 1, 'Concrete Gravel', 'Gravel', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(246, 1, 1, 1, 'plain-soil', 1, 'Plain Soil', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(247, 1, 1, 1, 'lawn-mix', 1, 'Lawn Mix', 'soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(248, 1, 1, 1, 'glebe-mulch-fine', 1, 'Glebe Mulch - Fine', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(249, 1, 1, 1, 'pinebark-hammermill', 1, 'Pinebark - Hammermill', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(250, 1, 1, 1, 'pinebark-medium', 1, 'Pinebark - Medium', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(251, 1, 1, 1, 'r7-20mm-aggregate', 1, 'R7 20mm Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(252, 1, 1, 1, 'r8-14mm-aggregate', 1, 'R8 14mm Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(253, 1, 1, 1, 'r9-10mm-aggregate', 1, 'R9 10mm Aggregate', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(254, 1, 1, 1, '7mm-agg', 1, '7mm Agg', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(255, 1, 1, 1, '20mm-agg', 1, '20mm Agg', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(256, 1, 1, 1, 'drainage-aggregates-20mm', 1, 'Drainage Aggregates 20mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(257, 1, 1, 1, 'drainage-aggregates-40mm', 1, 'Drainage Aggregates 40mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(258, 1, 1, 1, 'drainage-aggregates-75mm', 1, 'Drainage Aggregates 75mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(259, 1, 1, 1, 'drainage-aggregates-100mm', 1, 'Drainage Aggregates 100mm', 'aggregates', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(260, 1, 1, 1, 'general-purpose-potting-mix', 1, 'General Purpose Potting Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(261, 1, 1, 1, 'ls-native-garden-mix', 1, 'L/S Native Garden Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(262, 1, 1, 1, 'ls-premium-garden-mix', 1, 'L/S Premium Garden Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(263, 1, 1, 1, 'ls-premium-organic-top-dress', 1, 'L/S Premium Organic Top Dress', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(264, 1, 1, 1, 'ls-turf-underlay', 1, 'L/S Turf Underlay', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(265, 1, 1, 1, 'ls-vegie-mix', 1, 'L/S Vegie Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(266, 1, 1, 1, 'planter-box-mix', 1, 'Planter Box Mix', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(267, 1, 1, 1, 'euchy-mulch', 1, 'Euchy Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(268, 1, 1, 1, 'forest-fines', 1, 'Forest Fines', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:10', '2026-03-06 02:58:47'),
(269, 1, 1, 1, 'hardwood-woodchip', 1, 'Hardwood Woodchip', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(270, 1, 1, 1, 'leaf-mulch', 1, 'Leaf Mulch', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(271, 1, 1, 1, 'pine-bark-nuggets-25mm-mini-nuggets', 1, 'Pine Bark Nuggets 25mm (Mini Nuggets)', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(272, 1, 1, 1, 'recharge-mulch-and-compost', 1, 'Recharge Mulch And Compost', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(273, 1, 1, 1, 'red-woodchip', 1, 'Red Woodchip', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(274, 1, 1, 1, 'botany-humus-20mm-soil-improver', 1, 'Botany Humus / 20Mm Soil Improver', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(275, 1, 1, 1, 'hydrocell-40-podium-soil-bulk', 1, 'Hydrocell 40 Podium Soil (Bulk)', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(276, 1, 1, 1, 'hydrocell-40-podium-soil-bulka-bag', 1, 'Hydrocell 40 Podium Soil (Bulka Bag)', 'Soil', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(277, 1, 1, 1, 'recycled-forest-mulch-single-ground', 1, 'Recycled Forest Mulch (Single Ground)', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(278, 1, 1, 1, 'recycled-forest-mulch-double-ground', 1, 'Recycled Forest Mulch (Double Ground)', 'mulch', NULL, 'tonne', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(279, 1, 1, 1, 'n2020', 1, 'N2020', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(280, 1, 1, 1, 'n2520', 1, 'N2520', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(281, 1, 1, 1, 'n3220', 1, 'N3220', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47'),
(282, 1, 1, 1, 'n4020', 1, 'N4020', 'Concrete', NULL, 'Cubic Meter (m3)', NULL, NULL, NULL, '2026-02-18 22:21:11', '2026-03-06 02:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_06_041022_create_personal_access_tokens_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_lat` decimal(10,7) DEFAULT NULL,
  `delivery_long` decimal(10,7) DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `delivery_window` enum('Morning','Afternoon','Evening') DEFAULT NULL,
  `delivery_method` enum('Other','Tipper','Agitator','Pump','Ute') DEFAULT NULL,
  `load_size` varchar(50) DEFAULT NULL,
  `contact_person_name` varchar(100) DEFAULT NULL,
  `contact_person_number` varchar(30) DEFAULT NULL,
  `special_equipment` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fuel_levy` decimal(12,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gst_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `customer_item_cost` decimal(12,2) DEFAULT NULL,
  `customer_delivery_cost` decimal(12,2) DEFAULT NULL,
  `supplier_item_cost` decimal(12,2) DEFAULT NULL,
  `supplier_delivery_cost` decimal(12,2) DEFAULT NULL,
  `profit_margin_percent` decimal(12,2) DEFAULT NULL,
  `profit_amount` decimal(12,2) DEFAULT NULL,
  `supplier_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `customer_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admin_margin` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Pending','Paid','Partially Paid','Partial Refunded','Refunded','Requested') NOT NULL DEFAULT 'Pending',
  `supplier_paid_ids` text DEFAULT NULL,
  `order_status` enum('Draft','Confirmed','Scheduled','In Transit','Delivered','Completed','Cancelled') NOT NULL DEFAULT 'Draft',
  `workflow` enum('Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered') DEFAULT 'Requested',
  `reason` text DEFAULT NULL,
  `repeat_order` tinyint(1) NOT NULL DEFAULT 0,
  `generate_invoice` tinyint(1) NOT NULL DEFAULT 0,
  `order_process` enum('Automated','Action Required') DEFAULT NULL,
  `special_notes` text DEFAULT NULL,
  `requires_testing` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(2) DEFAULT 0,
  `archived_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `po_number`, `client_id`, `project_id`, `delivery_address`, `delivery_lat`, `delivery_long`, `delivery_date`, `delivery_time`, `delivery_window`, `delivery_method`, `load_size`, `contact_person_name`, `contact_person_number`, `special_equipment`, `subtotal`, `fuel_levy`, `other_charges`, `gst_tax`, `discount`, `total_price`, `customer_item_cost`, `customer_delivery_cost`, `supplier_item_cost`, `supplier_delivery_cost`, `profit_margin_percent`, `profit_amount`, `supplier_cost`, `customer_cost`, `admin_margin`, `payment_status`, `supplier_paid_ids`, `order_status`, `workflow`, `reason`, `repeat_order`, `generate_invoice`, `order_process`, `special_notes`, `requires_testing`, `is_archived`, `archived_by`, `created_at`, `updated_at`) VALUES
(8, 'PO123', 13, 12, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, '2026-02-27 00:00:00', '08:00:00', NULL, NULL, NULL, 'asdsad', '312312312312321', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Automated', NULL, 0, 0, NULL, '2026-02-26 05:07:16', '2026-02-26 05:07:16'),
(9, NULL, 110, 11, 'Sydney Rd, Hornsby Heights NSW 2077, Australia', -33.6729361, 151.0934891, '2026-02-26 00:00:00', '08:00:00', NULL, NULL, NULL, 'aiza', '123 123 123', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-02-26 06:20:55', '2026-02-26 06:21:34'),
(10, NULL, 13, 8, 'Sydney Light Rail, New South Wales, Australia', -33.8768467, 151.1859653, '2026-02-26 00:00:00', '08:00:00', NULL, NULL, NULL, 'harry', '0488352478', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Paid', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-02-26 23:48:11', '2026-02-26 23:50:11'),
(11, '1234', 13, 12, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, '2026-03-04 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '1234567890', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Cancelled', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-03 23:55:00', '2026-03-12 02:46:26'),
(12, NULL, 13, 12, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, '2026-03-09 00:00:00', '07:00:00', NULL, NULL, NULL, 'Bob', '0000000000', NULL, 0.00, 0.00, 0.00, 9352.50, 0.00, 102877.50, 93525.00, 0.00, 62350.00, 0.00, 0.50, 31175.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Confirmed', 'Payment Requested', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-07 08:02:24', '2026-03-12 02:46:22'),
(13, '123456', 13, 12, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, '2026-03-09 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '1234567890', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-09 06:50:29', '2026-03-10 03:40:52'),
(14, '1234567', 13, 12, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, '2026-03-10 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '1234567890', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-10 03:34:02', '2026-03-10 03:40:48'),
(15, 'PO1234', 13, 9, 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, '2026-03-10 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '1234567890', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-10 05:38:16', '2026-03-12 02:46:17'),
(16, 'PO123234', 13, 9, 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, '2026-03-13 00:00:00', '05:00:00', NULL, NULL, NULL, 'Test', '1234567890', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 1, 13, '2026-03-11 05:31:29', '2026-03-12 02:46:14'),
(17, 'PO-2026-001', 13, 9, 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, '2026-03-20 00:00:00', '06:00:00', NULL, NULL, NULL, 'test', '12345678912', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-03-17 02:42:03', '2026-03-17 02:57:13'),
(18, NULL, 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-03-29 00:00:00', '08:00:00', NULL, NULL, NULL, 'asdsad', '312312312312321', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Missing', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(19, NULL, 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-03-28 00:00:00', '15:00:00', NULL, NULL, NULL, 'asd', '123123213213213', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 1, 0, NULL, '2026-03-26 04:53:58', '2026-03-26 05:00:12'),
(20, NULL, 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-03-26 00:00:00', '08:00:00', NULL, NULL, NULL, 'bob', '1124124135135', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 1, 0, NULL, '2026-03-26 07:54:48', '2026-03-26 07:58:02'),
(21, NULL, 13, 15, '8 Freds Pass Rd, Humpty Doo NT 0836, Australia', -12.5725858, 131.1005324, '2026-03-26 00:00:00', '20:00:00', NULL, NULL, NULL, 'sam', '0000000000', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Automated', NULL, 1, 0, NULL, '2026-03-26 08:10:59', '2026-03-30 07:03:59'),
(22, NULL, 13, 15, 'Humpty Doo Access, Lambells Lagoon NT 0822, Australia', -12.6216626, 131.2597998, '2026-04-02 00:00:00', '08:00:00', NULL, NULL, NULL, 'sam', '1234567800', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Missing', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-03-31 07:40:42', '2026-03-31 07:43:26'),
(23, NULL, 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-04-02 00:00:00', '08:00:00', NULL, NULL, NULL, 'bob', '1212412342356', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Missing', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(24, 'test-order-9-april', 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-04-09 00:00:00', '08:00:00', NULL, NULL, NULL, 'asdsad', '312312312312321', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-04-09 05:10:30', '2026-04-09 06:40:53'),
(25, 'TBA', 13, 12, '12 Victoria Terrace, Bowen Hills QLD 4006, Australia', -27.4490993, 153.0421818, '2026-04-15 00:00:00', '08:00:00', NULL, NULL, NULL, 'ACE', '0412312312', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Automated', NULL, 1, 0, NULL, '2026-04-15 07:57:35', '2026-04-15 07:57:35'),
(26, 'Test', 13, 9, 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, '2026-04-16 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '123456489791', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-04-15 22:26:25', '2026-04-15 22:27:29'),
(27, 'TEST3', 13, 9, 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, '2026-04-15 00:00:00', '08:00:00', NULL, NULL, NULL, 'Test', '12345678909', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Missing', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(28, NULL, 13, 15, '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, '2026-04-16 00:00:00', '08:00:00', NULL, NULL, NULL, 'asd', '123123213213213', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'Draft', 'Supplier Assigned', NULL, 0, 0, 'Action Required', NULL, 0, 0, NULL, '2026-04-16 06:29:53', '2026-04-16 06:31:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `choosen_offer_id` int(11) DEFAULT NULL,
  `custom_blend_mix` text DEFAULT NULL,
  `delivery_type` enum('Included','Supplier','ThirdParty','Fleet','None') DEFAULT NULL,
  `supplier_unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `supplier_discount` decimal(12,2) DEFAULT 0.00,
  `supplier_confirms` tinyint(1) NOT NULL DEFAULT 0,
  `client_confirms` tinyint(1) NOT NULL DEFAULT 1,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `supplier_id`, `choosen_offer_id`, `custom_blend_mix`, `delivery_type`, `supplier_unit_cost`, `supplier_discount`, `supplier_confirms`, `client_confirms`, `is_paid`, `created_at`, `updated_at`) VALUES
(17, 8, 224, 3.00, 65, 242, NULL, NULL, 82.00, 0.00, 1, 1, 0, '2026-02-26 05:07:16', '2026-02-26 05:13:16'),
(18, 9, 5, 1000.00, 82, 5, NULL, NULL, 52.00, 0.00, 1, 1, 0, '2026-02-26 06:20:55', '2026-02-26 06:23:17'),
(19, 10, 275, 50.00, 92, 295, NULL, NULL, 114.00, 0.00, 0, 1, 0, '2026-02-26 23:48:11', '2026-02-26 23:49:20'),
(20, 11, 5, 100.00, 82, 5, NULL, NULL, 52.00, 0.00, 1, 1, 0, '2026-03-03 23:55:00', '2026-03-03 23:56:09'),
(21, 11, 279, 100.00, 59, 299, NULL, NULL, 262.00, 0.00, 1, 1, 0, '2026-03-03 23:55:00', '2026-03-03 23:56:14'),
(22, 12, 254, 1450.00, 84, 274, NULL, 'Supplier', 43.00, 0.00, 1, 1, 0, '2026-03-07 08:02:24', '2026-03-07 11:51:54'),
(23, 13, 254, 20.00, 84, 274, NULL, NULL, 43.00, 0.00, 1, 1, 0, '2026-03-09 06:50:29', '2026-03-09 06:51:22'),
(24, 14, 279, 100.00, 59, 299, NULL, NULL, 262.00, 0.00, 1, 1, 0, '2026-03-10 03:34:02', '2026-03-10 03:34:54'),
(25, 14, 5, 100.00, 82, 5, NULL, NULL, 52.00, 0.00, 1, 1, 0, '2026-03-10 03:34:02', '2026-03-10 03:35:00'),
(26, 15, 280, 100.00, 59, 300, NULL, NULL, 267.00, 0.00, 1, 1, 0, '2026-03-10 05:38:16', '2026-03-10 05:39:07'),
(27, 15, 244, 100.00, 81, 263, NULL, NULL, 54.00, 0.00, 1, 1, 0, '2026-03-10 05:38:16', '2026-03-10 05:39:10'),
(28, 15, 245, 100.00, 81, 264, NULL, NULL, 52.00, 0.00, 1, 1, 0, '2026-03-10 05:38:16', '2026-03-10 05:39:14'),
(29, 16, 282, 100.00, 59, 302, NULL, NULL, 284.00, 0.00, 1, 1, 0, '2026-03-11 05:31:29', '2026-03-11 05:32:55'),
(30, 16, 2, 50.00, 82, 2, NULL, NULL, 55.00, 0.00, 1, 1, 0, '2026-03-11 05:31:29', '2026-03-11 05:33:02'),
(31, 17, 245, 100.00, 81, 264, NULL, NULL, 52.00, 0.00, 1, 1, 0, '2026-03-17 02:42:03', '2026-03-25 03:46:02'),
(32, 18, 279, 1.00, NULL, NULL, NULL, NULL, 0.00, 0.00, 0, 1, 0, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(33, 19, 57, 120.00, 71, 68, NULL, NULL, 120.00, 0.00, 1, 1, 0, '2026-03-26 04:53:58', '2026-03-26 05:01:01'),
(34, 20, 58, 20.00, 71, 69, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-03-26 07:54:48', '2026-03-26 07:58:02'),
(35, 21, 58, 1.00, 71, 69, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(36, 21, 57, 1.00, 71, 68, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(37, 22, 57, 30.00, 71, 68, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-03-31 07:40:42', '2026-03-31 07:42:17'),
(38, 22, 192, 30.00, NULL, NULL, NULL, NULL, 0.00, 0.00, 0, 1, 0, '2026-03-31 07:40:42', '2026-03-31 07:43:26'),
(39, 23, 57, 30.00, 71, 68, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-04-01 04:11:04', '2026-04-01 04:26:59'),
(40, 23, 192, 30.00, NULL, NULL, NULL, NULL, 0.00, 0.00, 0, 1, 0, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(41, 24, 210, 10.00, 58, 228, NULL, NULL, 34.00, 0.00, 0, 1, 0, '2026-04-09 05:10:30', '2026-04-09 06:40:51'),
(42, 24, 57, 10.00, 71, 68, NULL, NULL, 120.00, 0.00, 0, 1, 0, '2026-04-09 06:39:32', '2026-04-09 06:40:53'),
(43, 25, 276, 5.00, 92, 296, NULL, NULL, 181.00, 0.00, 0, 1, 0, '2026-04-15 07:57:35', '2026-04-15 07:57:35'),
(44, 26, 282, 50.00, 59, 302, NULL, NULL, 284.00, 0.00, 1, 1, 0, '2026-04-15 22:26:25', '2026-04-15 22:27:56'),
(45, 27, 281, 10.00, NULL, NULL, NULL, NULL, 0.00, 0.00, 0, 1, 0, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(46, 28, 8, 10.00, 82, 8, NULL, NULL, 80.00, 0.00, 0, 1, 0, '2026-04-16 06:29:53', '2026-04-16 06:31:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_deliveries`
--

CREATE TABLE `order_item_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_date` date NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `truck_type` varchar(50) DEFAULT NULL,
  `delivery_cost` decimal(11,2) NOT NULL DEFAULT 0.00,
  `load_size` varchar(255) DEFAULT NULL,
  `time_interval` varchar(255) DEFAULT NULL,
  `accelerator_type` enum('low','medium','high') DEFAULT NULL,
  `retarder_type` enum('low','medium','high') DEFAULT NULL,
  `aggregate_size` varchar(10) DEFAULT NULL,
  `slump_value` decimal(6,2) DEFAULT NULL,
  `oxide_fibre` tinyint(1) DEFAULT NULL,
  `paver_delivery` tinyint(1) DEFAULT NULL,
  `omc_conditioning` tinyint(1) DEFAULT NULL,
  `additional_stabiliser` tinyint(1) DEFAULT NULL,
  `supplier_confirms` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Scheduled','Delivered','Cancelled','On Hold','Pending') DEFAULT 'Pending',
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item_deliveries`
--

INSERT INTO `order_item_deliveries` (`id`, `order_id`, `order_item_id`, `supplier_id`, `quantity`, `delivery_date`, `delivery_time`, `truck_type`, `delivery_cost`, `load_size`, `time_interval`, `accelerator_type`, `retarder_type`, `aggregate_size`, `slump_value`, `oxide_fibre`, `paver_delivery`, `omc_conditioning`, `additional_stabiliser`, `supplier_confirms`, `status`, `invoice_id`, `created_at`, `updated_at`) VALUES
(36, 8, 17, 65, 1.00, '2026-02-27', '08:00:00', 'mini_mix', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-02-26 05:07:16', '2026-02-26 05:08:02'),
(37, 8, 17, 65, 1.00, '2026-02-28', '08:00:00', 'mini_mix', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-02-26 05:07:16', '2026-02-27 00:00:13'),
(40, 8, 17, NULL, 1.00, '2026-03-07', '08:00:00', 'mini_mix', 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-02-26 05:13:16', '2026-03-06 00:00:04'),
(41, 9, 18, NULL, 500.00, '2026-02-26', '08:00:00', '10_wheeler', 1700.00, '15', '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-02-26 06:20:55', '2026-02-26 06:22:55'),
(42, 9, 18, NULL, 500.00, '2026-02-27', '08:00:00', '10_wheeler', 1700.00, '15', '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-02-26 06:20:55', '2026-02-26 06:24:03'),
(43, 10, 19, NULL, 50.00, '2026-02-26', '08:00:00', '10_wheeler', 0.00, '10', '120', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-02-26 23:48:11', '2026-02-26 23:50:39'),
(44, 11, 20, NULL, 100.00, '2026-03-04', '08:00:00', '10_wheeler', 0.00, '20', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-03 23:55:00', '2026-03-03 23:57:03'),
(45, 11, 21, 59, 100.00, '2026-03-04', '13:00:00', 'truck_and_dog', 0.00, '25', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-03 23:55:00', '2026-03-03 23:57:03'),
(46, 12, 22, NULL, 1450.00, '2026-03-09', '07:00:00', 'truck_and_dog', 0.00, '32', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-07 08:02:24', '2026-03-07 11:34:21'),
(47, 13, 23, NULL, 20.00, '2026-03-09', '08:00:00', 'truck_and_dog', 0.00, '1.5', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-09 06:50:29', '2026-03-09 06:50:29'),
(48, 14, 24, 59, 50.00, '2026-03-10', '08:00:00', '10_wheeler', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:36:12'),
(49, 14, 24, 59, 50.00, '2026-03-11', '08:00:00', '10_wheeler', 0.00, '4', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:35:03'),
(50, 14, 25, NULL, 25.00, '2026-03-12', '08:00:00', 'truck_and_dog', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:36:12'),
(51, 14, 25, NULL, 25.00, '2026-03-13', '08:00:00', 'truck_and_dog', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:36:12'),
(52, 14, 25, NULL, 25.00, '2026-03-14', '08:00:00', 'truck_and_dog', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:36:12'),
(53, 14, 25, NULL, 25.00, '2026-03-15', '08:00:00', 'truck_and_dog', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 03:34:02', '2026-03-10 03:36:12'),
(54, 15, 26, NULL, 50.00, '2026-03-14', '08:00:00', '6_wheeler', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 05:38:16', '2026-03-10 05:40:54'),
(55, 15, 26, NULL, 50.00, '2026-03-10', '08:00:00', 'mini_mix', 0.00, '1', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 05:38:16', '2026-03-10 05:40:54'),
(56, 15, 27, NULL, 100.00, '2026-03-15', '08:00:00', 'small_truck', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 05:38:16', '2026-03-10 05:40:54'),
(57, 15, 28, NULL, 100.00, '2026-03-13', '08:00:00', 'small_truck', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-10 05:38:16', '2026-03-10 05:40:54'),
(58, 16, 29, NULL, 50.00, '2026-03-13', '05:00:00', '6_wheeler', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-11 05:31:29', '2026-03-11 05:33:37'),
(59, 16, 29, NULL, 10.00, '2026-03-14', '05:00:00', 'mini_mix', 0.00, '.2', '240', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-11 05:31:29', '2026-03-11 05:33:37'),
(60, 16, 29, NULL, 40.00, '2026-03-15', '05:00:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-11 05:31:29', '2026-03-11 05:33:37'),
(61, 16, 30, NULL, 25.00, '2026-03-13', '05:00:00', 'mini_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-11 05:31:29', '2026-03-11 05:33:37'),
(62, 16, 30, NULL, 25.00, '2026-03-15', '05:00:00', 'small_truck', 0.00, '3', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-11 05:31:29', '2026-03-11 05:33:37'),
(63, 17, 31, NULL, 50.00, '2026-03-20', '06:00:00', 'small_truck', 0.00, '4', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-17 02:42:03', '2026-03-17 02:42:03'),
(64, 17, 31, NULL, 50.00, '2026-03-21', '08:00:00', 'body_truck', 0.00, '5', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-17 02:42:03', '2026-03-17 02:42:03'),
(65, 18, 32, NULL, 1.00, '2026-03-29', '08:00:00', 'mini_mix', 0.00, '0.2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 32, '2026-03-25 09:56:55', '2026-03-26 06:09:40'),
(66, 19, 33, NULL, 120.00, '2026-03-28', '15:00:00', '10_wheeler', 400.00, '30', '120', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 33, '2026-03-26 04:53:58', '2026-03-27 00:00:04'),
(67, 20, 34, NULL, 20.00, '2026-03-26', '08:00:00', '10_wheeler', 0.00, '2', '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-26 07:54:48', '2026-03-26 07:54:48'),
(68, 21, 35, 71, 1.00, '2026-03-26', '20:00:00', 'mini_mix', 0.00, '0.2', '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-26 08:10:59', '2026-03-30 07:03:59'),
(69, 21, 36, 71, 1.00, '2026-03-27', '08:00:00', 'mini_mix', 0.00, '0.1', '120', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-26 08:10:59', '2026-03-30 07:01:43'),
(70, 22, 37, 71, 10.00, '2026-04-02', '08:00:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-31 07:40:42', '2026-03-31 07:42:17'),
(71, 22, 38, NULL, 10.00, '2026-04-02', '08:00:00', 'mini_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-03-31 07:40:42', '2026-03-31 07:43:26'),
(72, 22, 37, NULL, 10.00, '2026-04-03', '08:00:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-03-31 07:42:17', '2026-03-31 07:42:17'),
(73, 22, 37, NULL, 10.00, '2026-04-04', '08:00:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-03-31 07:42:17', '2026-03-31 07:42:17'),
(74, 22, 38, NULL, 10.00, '2026-04-03', '08:00:00', 'body_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-03-31 07:43:26', '2026-03-31 08:40:35'),
(75, 22, 38, NULL, 10.00, '2026-04-04', '19:00:00', 'mini_truck', 0.00, '2', '240', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-03-31 07:43:26', '2026-03-31 08:40:22'),
(76, 23, 39, NULL, 10.00, '2026-04-02', '08:00:00', 'mini_mix', 300.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(77, 23, 39, NULL, 10.00, '2026-04-03', '15:03:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(78, 23, 39, NULL, 10.00, '2026-04-04', '15:00:00', 'mini_mix', 200.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(79, 23, 40, NULL, 10.00, '2026-04-02', '08:00:00', 'body_truck', 0.00, '10', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(80, 23, 40, NULL, 10.00, '2026-04-03', '08:00:00', 'mini_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(81, 23, 40, NULL, 10.00, '2026-04-04', '20:00:00', 'mini_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 34, '2026-04-01 04:11:04', '2026-04-06 23:43:10'),
(82, 24, 41, NULL, 10.00, '2026-04-09', '08:00:00', 'mini_truck', 0.00, '0.2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-04-09 05:10:30', '2026-04-09 06:39:57'),
(83, 24, 42, NULL, 10.00, '2026-04-10', '08:00:00', NULL, 0.00, '0.2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Scheduled', NULL, '2026-04-09 06:39:32', '2026-04-09 06:39:32'),
(84, 25, 43, 92, 5.00, '2026-04-15', '08:00:00', 'mini_truck', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 0, 'Pending', NULL, '2026-04-15 07:57:35', '2026-04-16 05:32:32'),
(85, 26, 44, NULL, 20.00, '2026-04-16', '08:00:00', 'mini_mix', 0.00, '1.5', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 35, '2026-04-15 22:26:25', '2026-04-15 22:30:04'),
(86, 26, 44, NULL, 20.00, '2026-04-17', '08:00:00', 'mini_mix', 0.00, '2', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 36, '2026-04-15 22:26:25', '2026-04-16 00:00:04'),
(87, 26, 44, NULL, 10.00, '2026-04-18', '08:00:00', 'mini_mix', 0.00, '1', '60', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', 37, '2026-04-15 22:26:25', '2026-04-17 00:00:03'),
(88, 27, 45, NULL, 4.00, '2026-04-15', '08:00:00', '10_wheeler', 0.00, '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(89, 27, 45, NULL, 4.00, '2026-04-15', '08:00:00', '6_wheeler', 0.00, '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(90, 27, 45, NULL, 2.00, '2026-04-15', '08:00:00', 'mini_mix', 0.00, '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-04-15 23:02:30', '2026-04-15 23:02:30'),
(91, 28, 46, NULL, 10.00, '2026-04-16', '08:00:00', 'mini_truck', 200.00, '1', '30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Pending', NULL, '2026-04-16 06:29:53', '2026-04-16 06:33:01');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_delivery_surcharges`
--

CREATE TABLE `order_item_delivery_surcharges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_delivery_id` bigint(20) UNSIGNED NOT NULL,
  `surcharge_id` bigint(20) UNSIGNED NOT NULL,
  `amount_snapshot` decimal(10,2) NOT NULL COMMENT 'Surcharge rate at time of order creation',
  `calculated_amount` decimal(10,2) NOT NULL COMMENT 'Actual dollar amount charged for this delivery',
  `is_auto` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = auto-detected by system, 0 = manually applied by admin',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item_delivery_surcharges`
--

INSERT INTO `order_item_delivery_surcharges` (`id`, `order_item_delivery_id`, `surcharge_id`, `amount_snapshot`, `calculated_amount`, `is_auto`, `notes`, `created_at`, `updated_at`) VALUES
(1, 48, 25, 90.00, 90.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(2, 48, 17, 2.50, 7.50, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(3, 48, 30, 10.00, 30.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(4, 49, 17, 2.50, 10.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(5, 49, 30, 10.00, 40.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(6, 50, 30, 10.00, 10.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(7, 51, 30, 10.00, 10.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(8, 52, 28, 90.00, 90.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(9, 53, 3, 90.00, 90.00, 1, NULL, '2026-03-10 03:34:02', '2026-03-10 03:34:02'),
(10, 54, 25, 90.00, 90.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(11, 54, 17, 2.50, 7.50, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(12, 54, 28, 90.00, 270.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(13, 55, 25, 90.00, 270.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(14, 55, 17, 2.50, 2.50, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(15, 55, 30, 10.00, 10.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(16, 56, 3, 90.00, 90.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(17, 57, 30, 10.00, 10.00, 1, NULL, '2026-03-10 05:38:16', '2026-03-10 05:38:16'),
(18, 58, 25, 90.00, 90.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(19, 58, 17, 2.50, 7.50, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(20, 58, 30, 10.00, 30.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(21, 59, 25, 90.00, 342.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(22, 59, 17, 2.50, 0.50, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(23, 59, 28, 90.00, 18.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(24, 60, 25, 90.00, 180.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(25, 60, 17, 2.50, 5.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(26, 60, 3, 90.00, 180.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(27, 61, 30, 10.00, 10.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(28, 62, 3, 90.00, 90.00, 1, NULL, '2026-03-11 05:31:29', '2026-03-11 05:31:29'),
(29, 63, 30, 10.00, 10.00, 1, NULL, '2026-03-17 02:42:03', '2026-03-17 02:42:03'),
(30, 64, 28, 90.00, 90.00, 1, NULL, '2026-03-17 02:42:03', '2026-03-17 02:42:03'),
(31, 65, 25, 90.00, 342.00, 1, NULL, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(32, 65, 17, 2.50, 0.50, 1, NULL, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(33, 65, 3, 90.00, 18.00, 1, NULL, '2026-03-25 09:56:55', '2026-03-25 09:56:55'),
(34, 66, 17, 2.50, 75.00, 1, NULL, '2026-03-26 04:53:58', '2026-03-26 04:53:58'),
(35, 66, 28, 90.00, 2700.00, 1, NULL, '2026-03-26 04:53:58', '2026-03-26 04:53:58'),
(36, 67, 25, 90.00, 180.00, 1, NULL, '2026-03-26 07:54:48', '2026-03-26 07:54:48'),
(37, 67, 17, 2.50, 5.00, 1, NULL, '2026-03-26 07:54:48', '2026-03-26 07:54:48'),
(38, 67, 30, 10.00, 20.00, 1, NULL, '2026-03-26 07:54:48', '2026-03-26 07:54:48'),
(39, 68, 25, 90.00, 342.00, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(40, 68, 17, 2.50, 0.50, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(41, 68, 30, 10.00, 2.00, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(42, 69, 25, 90.00, 333.00, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(43, 69, 17, 2.50, 0.75, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(44, 69, 30, 10.00, 3.00, 1, NULL, '2026-03-26 08:10:59', '2026-03-26 08:10:59'),
(45, 70, 25, 90.00, 342.00, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(46, 70, 17, 2.50, 0.50, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(47, 70, 30, 10.00, 2.00, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(48, 71, 30, 10.00, 10.00, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(49, 71, 56, 0.45, 0.09, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(50, 71, 52, 2.50, 0.50, 1, NULL, '2026-03-31 07:40:42', '2026-03-31 07:40:42'),
(51, 76, 25, 90.00, 180.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(52, 76, 17, 2.50, 5.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(53, 76, 30, 10.00, 20.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(54, 77, 25, 90.00, 180.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(55, 77, 17, 2.50, 5.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(56, 77, 30, 10.00, 20.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(57, 78, 25, 90.00, 180.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(58, 78, 17, 2.50, 5.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(59, 78, 28, 90.00, 180.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(60, 79, 30, 10.00, 10.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(61, 79, 56, 0.45, 4.50, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(62, 79, 52, 2.50, 25.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(63, 80, 30, 10.00, 10.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(64, 80, 56, 0.45, 0.90, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(65, 80, 52, 2.50, 5.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(66, 81, 28, 90.00, 90.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(67, 81, 56, 0.45, 0.90, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(68, 81, 52, 2.50, 5.00, 1, NULL, '2026-04-01 04:11:04', '2026-04-01 04:11:04'),
(69, 82, 30, 10.00, 10.00, 1, NULL, '2026-04-09 05:10:30', '2026-04-09 05:10:30'),
(70, 82, 56, 0.45, 0.09, 1, NULL, '2026-04-09 05:10:30', '2026-04-09 05:10:30'),
(71, 82, 52, 2.50, 0.50, 1, NULL, '2026-04-09 05:10:30', '2026-04-09 05:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_delivery_testing_fees`
--

CREATE TABLE `order_item_delivery_testing_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_delivery_id` bigint(20) UNSIGNED NOT NULL,
  `testing_fee_id` bigint(20) UNSIGNED NOT NULL,
  `amount_snapshot` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item_delivery_testing_fees`
--

INSERT INTO `order_item_delivery_testing_fees` (`id`, `order_item_delivery_id`, `testing_fee_id`, `amount_snapshot`, `created_at`, `updated_at`) VALUES
(1, 91, 1, 215.00, '2026-04-16 06:31:52', '2026-04-16 06:31:52');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'UserApp', '661c8c3ad077f78122a1ddbcb94e48048b9f2b1e8b9e348d267c6606e8134349', '[\"*\"]', NULL, NULL, '2025-10-07 13:50:10', '2025-10-07 13:50:10'),
(2, 'App\\Models\\User', 1, 'UserApp', '2fecf6313c137b4ccf331145b105e19eb70b93f3828080784cc2adcd81f1681f', '[\"*\"]', '2025-10-20 04:04:14', NULL, '2025-10-07 13:50:54', '2025-10-20 04:04:14'),
(3, 'App\\Models\\User', 8, 'ClientApp', '971894f9376c230d332f9d2593af3f93926f62b71c63ee6e8bbdc7dd8a8b056c', '[\"*\"]', NULL, NULL, '2025-10-07 14:33:49', '2025-10-07 14:33:49'),
(4, 'App\\Models\\User', 9, 'ClientApp', '2a371020988476bfcc1bd4bb5732aa3f8ab4d32a8ca0c2216236abf66f01b388', '[\"*\"]', NULL, NULL, '2025-10-07 14:40:47', '2025-10-07 14:40:47'),
(5, 'App\\Models\\User', 10, 'ClientApp', '3af6b947c4db47723f051aaf976bf51567894e5eb9daa3404b312723602bea83', '[\"*\"]', NULL, NULL, '2025-10-07 14:42:53', '2025-10-07 14:42:53'),
(6, 'App\\Models\\User', 11, 'ClientApp', '0c6fcb6dc289c33d2e86f82a3aeaafb6140f484cc06f922ec6adf9d6221e8315', '[\"*\"]', NULL, NULL, '2025-10-07 14:44:54', '2025-10-07 14:44:54'),
(7, 'App\\Models\\User', 12, 'ClientApp', '2aae275b9d5b47a5f0bf0564d67dd03e163388c550ce101e5f0d39055ed14961', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:13', '2025-10-07 14:53:13'),
(8, 'App\\Models\\User', 13, 'ClientApp', '8710e54e5cf0d6e232ee48633cbf7345ab8e173f025c4d3aa5e02cab302805b7', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:59', '2025-10-07 14:53:59'),
(9, 'App\\Models\\User', 14, 'SupplierApp', '74473f4f05db56a419255005b10ed5a788cb10d48f3238118428175adc87d50a', '[\"*\"]', NULL, NULL, '2025-10-07 14:59:19', '2025-10-07 14:59:19'),
(10, 'App\\Models\\User', 1, 'UserApp', '7b40f53b56ffd02bdcb7284b09143f693a79a9c0eed7a9af72dc1af5d270930a', '[\"*\"]', NULL, NULL, '2025-10-07 15:18:37', '2025-10-07 15:18:37'),
(11, 'App\\Models\\User', 13, 'UserApp', 'f2c0a0fec3972cff58e4adac4f31f085d1f94f7eac104a258423e581d027f853', '[\"*\"]', '2025-10-17 11:48:11', NULL, '2025-10-07 15:27:33', '2025-10-17 11:48:11'),
(12, 'App\\Models\\User', 14, 'UserApp', '25abc8a9729ffd8e79c47581cda2e6efde059a60e272574a237cece314c31c7a', '[\"*\"]', '2025-10-17 18:27:27', NULL, '2025-10-09 15:06:35', '2025-10-17 18:27:27'),
(13, 'App\\Models\\User', 1, 'UserApp', 'c2039df7c87c8f236b16a6bb751cca7a34889249b1109d091247a6a2937891b9', '[\"*\"]', NULL, NULL, '2025-10-10 14:23:59', '2025-10-10 14:23:59'),
(14, 'App\\Models\\User', 14, 'UserApp', '2412c07694ff9747316cf278ae2c57ea126cf907c0a4033f2a6bc129f8425f6d', '[\"*\"]', NULL, NULL, '2025-10-10 14:25:19', '2025-10-10 14:25:19'),
(15, 'App\\Models\\User', 1, 'UserApp', 'd6721376608c23a8aafdff362ca92ecea1002b5177a3cc687dce35a74ef61390', '[\"*\"]', NULL, NULL, '2025-10-10 15:20:58', '2025-10-10 15:20:58'),
(17, 'App\\Models\\User', 19, 'SupplierApp', '3d9ff285bfd70ec85395cc8c77e66675a7caaeddb2ecf98f0ab96f8d7d173e12', '[\"*\"]', NULL, NULL, '2025-10-10 16:46:10', '2025-10-10 16:46:10'),
(18, 'App\\Models\\User', 19, 'UserApp', 'dccc948682d59f60d6efd3d89dae225a555b2eef863a7280cb11ee134e552d2a', '[\"*\"]', NULL, NULL, '2025-10-10 16:46:30', '2025-10-10 16:46:30'),
(19, 'App\\Models\\User', 20, 'ClientApp', '1a7a193ccbf45d38ea2eb467a113521bdd90619900fbe30a12b85eda8ae760fa', '[\"*\"]', NULL, NULL, '2025-10-10 16:50:43', '2025-10-10 16:50:43'),
(20, 'App\\Models\\User', 20, 'UserApp', '024c514659244c6581086cced7fd91f037d8994f02e51834a6f597cbaf38a385', '[\"*\"]', NULL, NULL, '2025-10-10 16:53:29', '2025-10-10 16:53:29'),
(22, 'App\\Models\\User', 21, 'SupplierApp', 'ae7ac95d60a0d4f94014652ef9348338e16c8d1a1b83c805adf9cf1cf0e1d432', '[\"*\"]', NULL, NULL, '2025-10-10 17:08:33', '2025-10-10 17:08:33'),
(30, 'App\\Models\\User', 18, 'UserApp', '2401e627a79587251772060b2983364905537c900d45a3e872ed63424d7b8c78', '[\"*\"]', NULL, NULL, '2025-10-10 19:52:52', '2025-10-10 19:52:52'),
(31, 'App\\Models\\User', 18, 'UserApp', '8a3bfddc8dc463eca9a151118e8cdc5424550b168dfa70b7f83eebe077dcd99f', '[\"*\"]', '2025-10-10 20:27:06', NULL, '2025-10-10 19:54:58', '2025-10-10 20:27:06'),
(32, 'App\\Models\\User', 18, 'UserApp', '2884e43a1ff84a9720a097272e30c42a8a90153918f4b5689e16c4307b5dc20d', '[\"*\"]', '2025-10-10 20:36:03', NULL, '2025-10-10 20:29:30', '2025-10-10 20:36:03'),
(33, 'App\\Models\\User', 20, 'UserApp', '2c5b2eb59ad823268f3ae754ba5f3a1a37b14bc44f39acf79badbd18b4e4517c', '[\"*\"]', '2025-10-10 20:39:52', NULL, '2025-10-10 20:36:32', '2025-10-10 20:39:52'),
(34, 'App\\Models\\User', 1, 'UserApp', '10f24382366e7832405fac6e2cefc72395530c59bc3c73746d922adfdf2cbd2d', '[\"*\"]', '2025-10-10 22:24:09', NULL, '2025-10-10 20:41:02', '2025-10-10 22:24:09'),
(35, 'App\\Models\\User', 1, 'UserApp', '346e6103cb68aeb77ddc567bdca0448f344f095b61b277e20c5ae1bae263eb4d', '[\"*\"]', NULL, NULL, '2025-10-10 21:33:04', '2025-10-10 21:33:04'),
(36, 'App\\Models\\User', 20, 'UserApp', 'ad75d1c2aad1d33618a3e50b2a3a522ef99d99d2fcaf2654299cb352d152a5d2', '[\"*\"]', '2025-10-11 15:29:45', NULL, '2025-10-11 14:36:58', '2025-10-11 15:29:45'),
(37, 'App\\Models\\User', 20, 'UserApp', '46282871b77c6b7bffe4950118129ed51655cb3e101eea4f1d4d29b030ddc7b3', '[\"*\"]', '2025-10-11 14:50:22', NULL, '2025-10-11 14:41:53', '2025-10-11 14:50:22'),
(38, 'App\\Models\\User', 20, 'UserApp', 'f4d4d570ce4f14108b23a3d5647964d2c261292ab3000c9bf88dd98b062e718c', '[\"*\"]', '2025-10-17 11:47:40', NULL, '2025-10-11 15:21:46', '2025-10-17 11:47:40'),
(39, 'App\\Models\\User', 18, 'UserApp', 'c3cd03d2069bfcffbff2a8ebccf17f9cdae5fa0ce973ad18b109ca061105e573', '[\"*\"]', NULL, NULL, '2025-10-11 16:10:14', '2025-10-11 16:10:14'),
(40, 'App\\Models\\User', 18, 'UserApp', '97ed3b13f7ce4350cbca1fad9958f727283812f2a92acc03ef4160256f5d72f2', '[\"*\"]', NULL, NULL, '2025-10-11 16:10:21', '2025-10-11 16:10:21'),
(41, 'App\\Models\\User', 21, 'UserApp', 'f7c2e3963ece7d4bbd9cc9e380cab3f40e56411c0eb6ae3223a6d89cac1f23fe', '[\"*\"]', NULL, NULL, '2025-10-11 16:14:17', '2025-10-11 16:14:17'),
(42, 'App\\Models\\User', 18, 'UserApp', '6807c8157bc1703251b882ff2b9dde8148f526d8a19c8d5d8f2a0f8e244f909a', '[\"*\"]', NULL, NULL, '2025-10-11 16:20:26', '2025-10-11 16:20:26'),
(43, 'App\\Models\\User', 19, 'UserApp', '32e6903fbaad9bc364ba5b9e5c72da2eb3617601def5b7b399ed92dcd6723cf2', '[\"*\"]', NULL, NULL, '2025-10-11 16:20:42', '2025-10-11 16:20:42'),
(44, 'App\\Models\\User', 14, 'UserApp', '459dc6ff682d7ac95a995e0a38d7bb0d98027c5d45b726e7dd3ea6fa4ed2cd30', '[\"*\"]', '2025-10-11 17:08:44', NULL, '2025-10-11 16:21:02', '2025-10-11 17:08:44'),
(45, 'App\\Models\\User', 1, 'UserApp', 'af6d535213ff57ba64fa571b7cf1ea0ca01c8dcba2d26ec501dbaafc94859b04', '[\"*\"]', '2025-10-11 18:49:05', NULL, '2025-10-11 17:09:23', '2025-10-11 18:49:05'),
(46, 'App\\Models\\User', 1, 'UserApp', '99efdb3ae552ee5e670939d4223ccd563dee8036e87f8ac9469eed2154341ab1', '[\"*\"]', '2025-10-13 15:42:49', NULL, '2025-10-13 13:57:50', '2025-10-13 15:42:49'),
(47, 'App\\Models\\User', 1, 'UserApp', 'd3101489746191c652d98611c386f38fd1c802b2d4c8a6c3c287de3fd56e6340', '[\"*\"]', '2025-10-13 14:53:55', NULL, '2025-10-13 14:53:44', '2025-10-13 14:53:55'),
(48, 'App\\Models\\User', 1, 'UserApp', '969336a6dbcfdbc330013c63e2a9f6034b29d4ce7c8292a5b1fde5695960bb92', '[\"*\"]', '2025-10-13 17:36:11', NULL, '2025-10-13 16:23:31', '2025-10-13 17:36:11'),
(49, 'App\\Models\\User', 14, 'UserApp', '2457c226b925c9f3982995ee8315ae6ecb7b662a12a42b68e0b735b28e4d530d', '[\"*\"]', '2025-10-13 17:48:46', NULL, '2025-10-13 16:27:07', '2025-10-13 17:48:46'),
(50, 'App\\Models\\User', 14, 'UserApp', '7d9e55fc3fcade1a83d523a4aa132257cb5a7456f6aef6bc09dc9f2a7f2a2c14', '[\"*\"]', '2025-10-13 19:18:54', NULL, '2025-10-13 17:36:25', '2025-10-13 19:18:54'),
(51, 'App\\Models\\User', 1, 'UserApp', 'c082aac4ed6c859d747982f983c33a93bd28053cc35ae137cce8e794ab46f91a', '[\"*\"]', '2025-10-13 19:03:43', NULL, '2025-10-13 17:49:23', '2025-10-13 19:03:43'),
(52, 'App\\Models\\User', 14, 'UserApp', '4053d03e40190bbef3a49e58ca82ac76999715e91308588a3468ec56f2669441', '[\"*\"]', '2025-10-14 18:04:04', NULL, '2025-10-14 11:33:15', '2025-10-14 18:04:04'),
(53, 'App\\Models\\User', 16, 'UserApp', '1f8bb27a250bf19a861fdc0a2bc56aee0f87b3d8139850da55ce8ba1b8395bad', '[\"*\"]', '2025-10-14 11:54:39', NULL, '2025-10-14 11:54:02', '2025-10-14 11:54:39'),
(54, 'App\\Models\\User', 1, 'UserApp', 'b1dfd624c16dc6b80d34af7c1c3129092e7c4bd7653578fe1743cf2872c7645b', '[\"*\"]', '2025-10-14 18:04:04', NULL, '2025-10-14 11:54:51', '2025-10-14 18:04:04'),
(55, 'App\\Models\\User', 13, 'UserApp', 'cb2a019e8e4c289022e638409203c850322ff9a887505f46f08b074f35dae886', '[\"*\"]', '2025-10-14 16:22:09', NULL, '2025-10-14 12:48:24', '2025-10-14 16:22:09'),
(56, 'App\\Models\\User', 1, 'UserApp', '91597e0588e8775c592b20ab5af7aa1c5dcb8b8db6a912fb849028e20a10e8a5', '[\"*\"]', '2025-10-15 11:57:30', NULL, '2025-10-15 11:57:26', '2025-10-15 11:57:30'),
(57, 'App\\Models\\User', 1, 'UserApp', '84d9a6e1adf4dd7e1e890cbc5f5af5217b3f838ac0bfe2da993ac12ceef99e91', '[\"*\"]', '2025-10-15 18:41:24', NULL, '2025-10-15 12:06:52', '2025-10-15 18:41:24'),
(58, 'App\\Models\\User', 14, 'UserApp', '854cb00d0821f7c1824c4b71daffd9be825eb99386aabde0e04478229b2b0bac', '[\"*\"]', '2025-10-15 18:41:24', NULL, '2025-10-15 14:38:18', '2025-10-15 18:41:24'),
(59, 'App\\Models\\User', 16, 'UserApp', '878b853307a2302f5b9a9f4c848bc2a81aa85deb36456e07fbc45b7151cb710f', '[\"*\"]', '2025-10-15 15:12:57', NULL, '2025-10-15 14:41:12', '2025-10-15 15:12:57'),
(60, 'App\\Models\\User', 13, 'UserApp', '89ca15dc1baa7a24893be8a0516aedfa87d1e8c98c4eaa0e66c602d0ad8b665d', '[\"*\"]', '2025-11-07 06:13:01', NULL, '2025-10-15 14:45:06', '2025-11-07 06:13:01'),
(61, 'App\\Models\\User', 13, 'UserApp', 'aced521ed8f05ef1db991dc23fdd55a684b6f5964997733393f4d7039d4ec98a', '[\"*\"]', '2025-12-08 03:38:38', NULL, '2025-10-15 14:45:52', '2025-12-08 03:38:38'),
(62, 'App\\Models\\User', 13, 'UserApp', '4f39fe371744a2c35bcdda6786ff53510f25d43be55761d751defff1104c6b57', '[\"*\"]', '2025-10-15 20:23:07', NULL, '2025-10-15 18:25:20', '2025-10-15 20:23:07'),
(63, 'App\\Models\\User', 13, 'UserApp', 'f30ba5a83da7116d7854e8e491a59804cb75a613982f478a51f03a29081731b5', '[\"*\"]', '2025-10-17 16:03:18', NULL, '2025-10-17 11:29:15', '2025-10-17 16:03:18'),
(64, 'App\\Models\\User', 1, 'UserApp', 'ab850cd95fe51548fdb036b619461b8fdcd1331186ce24a21dcc56f16d4b69a7', '[\"*\"]', '2025-10-17 16:09:09', NULL, '2025-10-17 11:55:12', '2025-10-17 16:09:09'),
(65, 'App\\Models\\User', 14, 'UserApp', 'ca8cfaa7571c16c7cc9f1a2dc057ee3f8842c510e9fb0fb7530f0cbde4cb8946', '[\"*\"]', NULL, NULL, '2025-10-17 18:56:17', '2025-10-17 18:56:17'),
(66, 'App\\Models\\User', 14, 'UserApp', 'af4e15cfcc2543ca6f4d6640a62f3342f7f4ec2ab12f9abb5f1b042fbde16176', '[\"*\"]', '2025-10-17 19:46:29', NULL, '2025-10-17 18:57:12', '2025-10-17 19:46:29'),
(67, 'App\\Models\\User', 13, 'UserApp', '7cc538262834f7c91c7e87f57b4dadad3d6381b94ba65809b962c2aa141f71e9', '[\"*\"]', NULL, NULL, '2025-10-20 03:54:05', '2025-10-20 03:54:05'),
(68, 'App\\Models\\User', 13, 'UserApp', 'c3b28aaae4f07a6cf26a964f865097b80b210d4057a5381c78ed278cca4be1d4', '[\"*\"]', NULL, NULL, '2025-10-20 03:56:20', '2025-10-20 03:56:20'),
(69, 'App\\Models\\User', 13, 'UserApp', 'b391d68259dde898bc1ff57499cf09cf9f362d5461a8ee7a8635ef71327cf69a', '[\"*\"]', NULL, NULL, '2025-10-20 03:57:03', '2025-10-20 03:57:03'),
(70, 'App\\Models\\User', 13, 'UserApp', 'f521a2e4adeaa1c6e5a2a90094fa2183c5d29366338ef1aa5d8aa9eb987ead8b', '[\"*\"]', NULL, NULL, '2025-10-20 04:01:19', '2025-10-20 04:01:19'),
(71, 'App\\Models\\User', 1, 'UserApp', '02643e0d71f12a0e9a8defa1135148809d60536103d054d02e4d97ab2e205f03', '[\"*\"]', '2025-10-20 04:08:43', NULL, '2025-10-20 04:02:36', '2025-10-20 04:08:43'),
(72, 'App\\Models\\User', 14, 'UserApp', '05b340d4d807bf8c85bdc6d63679a19d54a434452dac3b38618da432993d2a67', '[\"*\"]', '2025-10-20 05:18:04', NULL, '2025-10-20 04:09:12', '2025-10-20 05:18:04'),
(73, 'App\\Models\\User', 1, 'UserApp', '3fa1202dcde27dc6f91a42baa83cbdae57a07938843b3e37ca0b5ca9cf2c8370', '[\"*\"]', '2025-10-20 05:31:34', NULL, '2025-10-20 04:25:47', '2025-10-20 05:31:34'),
(74, 'App\\Models\\User', 1, 'UserApp', '4980bedccf4fdb22d3059122144aafc13c06dac20c14650249670de7f7ab32d0', '[\"*\"]', NULL, NULL, '2025-10-20 04:27:48', '2025-10-20 04:27:48'),
(75, 'App\\Models\\User', 14, 'UserApp', '049fb8084e4d5c638af590f1ed4dd42d18bface489557e607b3dd4f09f6775f6', '[\"*\"]', '2025-10-20 06:54:02', NULL, '2025-10-20 05:45:14', '2025-10-20 06:54:02'),
(76, 'App\\Models\\User', 13, 'UserApp', '2ae0014c43fe1716745787153fed86d0bbf16f0b415f82282d211ee92ff5688b', '[\"*\"]', '2025-10-20 06:31:56', NULL, '2025-10-20 05:47:47', '2025-10-20 06:31:56'),
(77, 'App\\Models\\User', 13, 'UserApp', '00de135be0165c49baebaed01dc6a2eb8488d1577ba9831f4db52862c46b31eb', '[\"*\"]', '2025-10-20 06:56:41', NULL, '2025-10-20 06:56:19', '2025-10-20 06:56:41'),
(78, 'App\\Models\\User', 14, 'UserApp', '056ad5715f769fe9cad8ee353f8cbcc24a2322057bce90b8261e32cd40ff12cb', '[\"*\"]', '2025-10-20 06:58:04', NULL, '2025-10-20 06:57:48', '2025-10-20 06:58:04'),
(79, 'App\\Models\\User', 1, 'UserApp', '0a32685f19eb7b52b0a6b18a63d0a9ffeffd1c963fa03488c475396f0751fa5d', '[\"*\"]', '2025-10-20 08:00:01', NULL, '2025-10-20 07:57:51', '2025-10-20 08:00:01'),
(80, 'App\\Models\\User', 14, 'UserApp', 'e61c20aadb71e625f6ccc332be710bf56a27c6bb9703c030cb400b8541fc2005', '[\"*\"]', '2025-10-20 08:02:00', NULL, '2025-10-20 08:01:26', '2025-10-20 08:02:00'),
(81, 'App\\Models\\User', 13, 'UserApp', '1c4e70e806b1905a2dfaab1356ae48b3ef23edd0369c200692fb37ac13632f41', '[\"*\"]', '2025-10-20 08:03:39', NULL, '2025-10-20 08:02:54', '2025-10-20 08:03:39'),
(82, 'App\\Models\\User', 13, 'UserApp', '9d0b66f129e6e14988ee11f7a7f9f80e7df8baf13175be9430e696fdd1582ba7', '[\"*\"]', '2025-10-20 09:42:29', NULL, '2025-10-20 09:42:23', '2025-10-20 09:42:29'),
(83, 'App\\Models\\User', 13, 'UserApp', 'dc7d58d8cd8550e82733366ccc94d28512f20657cc0671438ffa54e09ec529d5', '[\"*\"]', '2025-10-20 09:43:04', NULL, '2025-10-20 09:42:56', '2025-10-20 09:43:04'),
(84, 'App\\Models\\User', 13, 'UserApp', '38f7019f5c56738cc6b6a570bf848587fa19dabbfcf458ee9976ae94c4401a7c', '[\"*\"]', '2025-10-20 09:44:40', NULL, '2025-10-20 09:44:13', '2025-10-20 09:44:40'),
(85, 'App\\Models\\User', 13, 'UserApp', '628164bb7f4145dcf31488590fc5df36eb254896981aeff4892d52558587fa3d', '[\"*\"]', '2025-10-20 09:51:49', NULL, '2025-10-20 09:45:46', '2025-10-20 09:51:49'),
(86, 'App\\Models\\User', 14, 'UserApp', '3bf0d99f9220891162e0964893db0e73bb41c8de1b3cebfcdc31f087c8f87f28', '[\"*\"]', '2025-10-21 04:05:38', NULL, '2025-10-21 04:02:30', '2025-10-21 04:05:38'),
(87, 'App\\Models\\User', 1, 'UserApp', '03511c53f981570cbf69fd98187e97f69dedf64058bf3b9ccd71ff94ae3e8436', '[\"*\"]', '2025-10-21 04:09:19', NULL, '2025-10-21 04:08:02', '2025-10-21 04:09:19'),
(88, 'App\\Models\\User', 1, 'UserApp', '8b1aaca471beb0f1c0e0a0d585bfed0654a8dac4a331440fd2645f39fd78c4de', '[\"*\"]', NULL, NULL, '2025-10-21 04:09:47', '2025-10-21 04:09:47'),
(89, 'App\\Models\\User', 1, 'UserApp', 'ee7febd6a877a913b25720496c181cab12e7693431870c0a86bf860c47b698c6', '[\"*\"]', NULL, NULL, '2025-10-21 04:09:57', '2025-10-21 04:09:57'),
(90, 'App\\Models\\User', 1, 'UserApp', '7075f46f51d397294e7963774a45e2e820faa9f8d0c47a2cdda62e336fba88c6', '[\"*\"]', NULL, NULL, '2025-10-21 04:10:15', '2025-10-21 04:10:15'),
(91, 'App\\Models\\User', 13, 'UserApp', '8542051d898156b8c028bfa052bdb3fa07e0f7ee16a0fa2f617b0bec9f92c511', '[\"*\"]', '2025-10-21 04:18:56', NULL, '2025-10-21 04:10:46', '2025-10-21 04:18:56'),
(92, 'App\\Models\\User', 1, 'UserApp', 'b95e8a4121e004dd41fbbaf05f3e92bb37c2028f4ff6023f20a0b7042f12c271', '[\"*\"]', '2025-10-21 04:21:48', NULL, '2025-10-21 04:20:51', '2025-10-21 04:21:48'),
(93, 'App\\Models\\User', 1, 'UserApp', '4200d5cb0f33a92886d23c8691275b109d4bf7d302e9fc52b3a98278306e9b7c', '[\"*\"]', '2025-11-04 00:13:45', NULL, '2025-10-21 04:22:15', '2025-11-04 00:13:45'),
(94, 'App\\Models\\User', 14, 'UserApp', '7190d68ecf76769b02f1d427d2bcbb14e20686b18a55b6417021e48631ad9c83', '[\"*\"]', '2025-10-21 04:23:37', NULL, '2025-10-21 04:23:21', '2025-10-21 04:23:37'),
(95, 'App\\Models\\User', 14, 'UserApp', '454b4b311664a949bebe1a3f9cf3d6456461586156bdf98f8e268ddfcfb87fc5', '[\"*\"]', NULL, NULL, '2025-10-21 04:23:47', '2025-10-21 04:23:47'),
(96, 'App\\Models\\User', 13, 'UserApp', '4359e05bb3fb4bed7a4221e4c1984431fb9bf6eaf88e96726c8d41153919d8ff', '[\"*\"]', NULL, NULL, '2025-10-21 04:24:04', '2025-10-21 04:24:04'),
(97, 'App\\Models\\User', 1, 'UserApp', 'f01c3b6565e0f5442c1465f9fe1c2917c224e83c320fb10d36a82572bfa133f0', '[\"*\"]', NULL, NULL, '2025-10-21 04:24:18', '2025-10-21 04:24:18'),
(98, 'App\\Models\\User', 1, 'UserApp', 'bcc41c56e2aa7bed10b9c5800525b67ba39d0d2ca7efdb707d2c9f802b2f5d44', '[\"*\"]', NULL, NULL, '2025-10-21 04:25:05', '2025-10-21 04:25:05'),
(99, 'App\\Models\\User', 13, 'UserApp', 'b21da1de63c206f5ceeec42f8b45562f1d7ce5760277640c379c3eb104074ff8', '[\"*\"]', NULL, NULL, '2025-10-21 04:25:21', '2025-10-21 04:25:21'),
(100, 'App\\Models\\User', 1, 'UserApp', 'b0b3c83fdc08cdafe7d82defe132056e4681add62a6054d8dcfc77a13ceefd54', '[\"*\"]', NULL, NULL, '2025-10-21 04:25:48', '2025-10-21 04:25:48'),
(101, 'App\\Models\\User', 1, 'UserApp', 'ead38279e3e34719fc4e410559cc9a29c22956e86b5227045a6953fcddccf8ec', '[\"*\"]', NULL, NULL, '2025-10-21 04:26:30', '2025-10-21 04:26:30'),
(102, 'App\\Models\\User', 13, 'UserApp', '91ea5a4bfd3c352adb053ab58ef76bb10a40e2e2e1e06fe817e121699221e1c0', '[\"*\"]', '2025-10-21 04:27:29', NULL, '2025-10-21 04:27:14', '2025-10-21 04:27:29'),
(103, 'App\\Models\\User', 14, 'UserApp', '429d5ed5d81bb12369e9df7a6cbfdad45f49a66ef89523be588b2809803de027', '[\"*\"]', '2025-10-21 04:29:43', NULL, '2025-10-21 04:29:22', '2025-10-21 04:29:43'),
(104, 'App\\Models\\User', 13, 'UserApp', 'b344260216e3e7c7e20a65837c572290fc537b96ca73546b32b187e7a779ca95', '[\"*\"]', NULL, NULL, '2025-10-21 04:30:28', '2025-10-21 04:30:28'),
(105, 'App\\Models\\User', 14, 'UserApp', '637a0071fc38ea763f9d56cc42a3633e2e5f19e61087863b6cbd0bf6c5e4c583', '[\"*\"]', NULL, NULL, '2025-10-21 04:31:40', '2025-10-21 04:31:40'),
(106, 'App\\Models\\User', 1, 'UserApp', '9d6d14cdfe37cb6131eac6503dedcc5c1aa6d25c57b3f4dadbc1383351e5bcdc', '[\"*\"]', '2025-10-21 04:48:20', NULL, '2025-10-21 04:45:52', '2025-10-21 04:48:20'),
(107, 'App\\Models\\User', 1, 'UserApp', '96a8e9c9f310f58afcfbc23b5b478e6201c352b7a724508e20bc4ce4d7b332bd', '[\"*\"]', '2025-10-21 04:49:44', NULL, '2025-10-21 04:48:48', '2025-10-21 04:49:44'),
(108, 'App\\Models\\User', 1, 'UserApp', '8800f95695e9dd09229db1f309cdcb0b118b9ca66d6d756f67c49ae39dde65f8', '[\"*\"]', NULL, NULL, '2025-10-21 04:50:24', '2025-10-21 04:50:24'),
(109, 'App\\Models\\User', 14, 'UserApp', '033f805b0468e72b8c792c1ba94e4c807bea71b069562192cea62cbfc535c8b0', '[\"*\"]', '2025-10-21 05:23:24', NULL, '2025-10-21 04:50:49', '2025-10-21 05:23:24'),
(110, 'App\\Models\\User', 1, 'UserApp', '45ef0d96e04e13c0489cd3790f8b52f59f2cf8d2ce31e5f106bcbed392acccea', '[\"*\"]', '2025-10-21 06:41:58', NULL, '2025-10-21 06:41:45', '2025-10-21 06:41:58'),
(111, 'App\\Models\\User', 14, 'UserApp', '41bd5e80e201dc6766112c5e4af8278a971ed549e9d8bed398b02506ab6404d8', '[\"*\"]', '2025-10-21 06:50:35', NULL, '2025-10-21 06:49:44', '2025-10-21 06:50:35'),
(112, 'App\\Models\\User', 1, 'UserApp', 'dbf8c8697688c7fb50b06f368ab4b2f338867251a8bc4c0cd4d455dfef497f10', '[\"*\"]', '2025-10-21 07:15:35', NULL, '2025-10-21 07:14:55', '2025-10-21 07:15:35'),
(113, 'App\\Models\\User', 1, 'UserApp', '335233c075b7ac67241e9134ebaa38aa8d660e00f7c3d6f65fa83668fcb7e177', '[\"*\"]', '2025-10-21 07:25:47', NULL, '2025-10-21 07:25:39', '2025-10-21 07:25:47'),
(114, 'App\\Models\\User', 1, 'UserApp', 'd955b553302da0bb0402495a4e0cf6f6fc633f5948d6f9ec7c166b061fecb86c', '[\"*\"]', NULL, NULL, '2025-10-21 07:25:57', '2025-10-21 07:25:57'),
(115, 'App\\Models\\User', 1, 'UserApp', '229ea98cda8c4d8eda9a4820fa94cb65e6ec62c1d1f5804aa36e2c0df0b572a6', '[\"*\"]', NULL, NULL, '2025-10-21 07:26:11', '2025-10-21 07:26:11'),
(116, 'App\\Models\\User', 1, 'UserApp', 'e37311e91b4bd37bb996bdf3c55742a6617f97ed9198424d58dcd0c0aed297e5', '[\"*\"]', '2025-10-21 07:26:45', NULL, '2025-10-21 07:26:22', '2025-10-21 07:26:45'),
(117, 'App\\Models\\User', 14, 'UserApp', 'b2910512670fd26cbbad141f7537ffcd7cc2d9e4f9d8dab3a1c0624416d36e9b', '[\"*\"]', '2025-10-21 07:28:45', NULL, '2025-10-21 07:27:17', '2025-10-21 07:28:45'),
(118, 'App\\Models\\User', 13, 'UserApp', 'e95d58340dcc95d77ca84663bc71738a793aa7bb7a989020b39abf611c60473f', '[\"*\"]', '2025-10-21 07:34:29', NULL, '2025-10-21 07:31:57', '2025-10-21 07:34:29'),
(119, 'App\\Models\\User', 1, 'UserApp', 'c90ba19a9d5915765d30062c76d4179e888be94575a576615d2dff2caba26d3b', '[\"*\"]', NULL, NULL, '2025-10-21 07:34:50', '2025-10-21 07:34:50'),
(120, 'App\\Models\\User', 1, 'UserApp', 'f4113891d1f29d8e360823a1b55633ada28774a6df798504aad330817a6160c2', '[\"*\"]', NULL, NULL, '2025-10-21 07:35:10', '2025-10-21 07:35:10'),
(121, 'App\\Models\\User', 14, 'UserApp', '0e2977a0d373ba14bff3f4197546284e849b6f7ab9850ce774030693716c77f2', '[\"*\"]', '2025-10-21 07:39:39', NULL, '2025-10-21 07:39:21', '2025-10-21 07:39:39'),
(122, 'App\\Models\\User', 1, 'UserApp', '33d13302e115a9fea16d84b52090e8af7544ecdeb19cfaea70ad08f5f4ce7d7e', '[\"*\"]', '2025-10-21 08:17:07', NULL, '2025-10-21 08:16:29', '2025-10-21 08:17:07'),
(123, 'App\\Models\\User', 14, 'UserApp', 'e67377ba434353105ca90cba421847c5b76b6a8155a8399e183fd34ecc8bc1e6', '[\"*\"]', '2025-10-21 08:19:08', NULL, '2025-10-21 08:17:55', '2025-10-21 08:19:08'),
(124, 'App\\Models\\User', 13, 'UserApp', '686c9edfb9914f646108a715906e79dcb94ec063a252f39f594c219c681aad0b', '[\"*\"]', '2025-10-21 08:25:15', NULL, '2025-10-21 08:19:39', '2025-10-21 08:25:15'),
(125, 'App\\Models\\User', 1, 'UserApp', '27379407e3e8b97d5395719e666dc4c48bf1ab11e329f0cc1cb2618ce704e60a', '[\"*\"]', '2025-10-21 11:02:47', NULL, '2025-10-21 11:02:06', '2025-10-21 11:02:47'),
(126, 'App\\Models\\User', 13, 'UserApp', '3abf0f4e652a922dc0503be74ca4c4b07ecbf1da3a477ed02ba0a1a3333b9c26', '[\"*\"]', '2025-10-21 11:05:36', NULL, '2025-10-21 11:04:46', '2025-10-21 11:05:36'),
(127, 'App\\Models\\User', 1, 'UserApp', 'bdeaf6bdd83cdf59c9b2900e1225a795305c88a91044632c5309b7b964b3f08f', '[\"*\"]', '2025-10-21 15:25:01', NULL, '2025-10-21 15:20:04', '2025-10-21 15:25:01'),
(128, 'App\\Models\\User', 1, 'UserApp', '472fc4f8600f1c246ec9d3952e52e19a48ab321ff723244ac0e677834f94b700', '[\"*\"]', '2025-10-22 00:25:57', NULL, '2025-10-22 00:23:38', '2025-10-22 00:25:57'),
(129, 'App\\Models\\User', 14, 'UserApp', '1647bd9b09ccd250bfb7b1751275d52c51f0cd7ca44eb1623390c4cc1c8470a6', '[\"*\"]', '2025-10-22 00:26:27', NULL, '2025-10-22 00:26:17', '2025-10-22 00:26:27'),
(130, 'App\\Models\\User', 14, 'UserApp', '380f1be35a079b2aea1821d3ec302f08812794519fefda86a4b1b8d1d1dfd37b', '[\"*\"]', '2025-10-22 01:11:24', NULL, '2025-10-22 01:11:05', '2025-10-22 01:11:24'),
(131, 'App\\Models\\User', 14, 'UserApp', 'ca5f05f331121c427ccbeb2ea9fd35413a3bd41659316ad070ca36c1996240dd', '[\"*\"]', '2025-10-22 04:21:07', NULL, '2025-10-22 04:03:25', '2025-10-22 04:21:07'),
(132, 'App\\Models\\User', 1, 'UserApp', '33cab673fc70a288abbf02f1f8eca35202de516a4ce4d89ee200f75003f3afdd', '[\"*\"]', '2025-10-22 04:22:40', NULL, '2025-10-22 04:21:22', '2025-10-22 04:22:40'),
(133, 'App\\Models\\User', 13, 'UserApp', 'd5fae544d37d1d1df42f390a3e5f9539f6b2f7148a8708c76b4d1c9daf9d43d0', '[\"*\"]', '2025-10-22 04:24:06', NULL, '2025-10-22 04:21:30', '2025-10-22 04:24:06'),
(134, 'App\\Models\\User', 14, 'UserApp', '4925cebaa3b1372896a4738bc61a700c0d6f3705a035121d13632ade5dc20c06', '[\"*\"]', '2025-10-22 04:25:11', NULL, '2025-10-22 04:23:46', '2025-10-22 04:25:11'),
(135, 'App\\Models\\User', 13, 'UserApp', 'f154a2ce39e719ff0e74f59abb65b81d123573c6223b80a5e7615364564f530d', '[\"*\"]', NULL, NULL, '2025-10-22 04:24:45', '2025-10-22 04:24:45'),
(136, 'App\\Models\\User', 13, 'UserApp', '494794ef7a181c9f235451f21ee4e16fdb4b9e79cf4804c6a94afb64e4b252e1', '[\"*\"]', '2025-10-22 04:26:35', NULL, '2025-10-22 04:25:35', '2025-10-22 04:26:35'),
(137, 'App\\Models\\User', 21, 'UserApp', '2c9662445fa883dd92a647467c31d87f993b1bbd0a844e580e332411f0fdb95f', '[\"*\"]', NULL, NULL, '2025-10-22 04:26:56', '2025-10-22 04:26:56'),
(138, 'App\\Models\\User', 1, 'UserApp', '78455157fe35056524743e5c929d4c1f43df07c969ba8e391bb5018fcb9fcee9', '[\"*\"]', '2025-10-22 04:29:58', NULL, '2025-10-22 04:27:52', '2025-10-22 04:29:58'),
(139, 'App\\Models\\User', 21, 'UserApp', '9921c7bcbe1d1c70fefb44eeebdf70e4c84fcf8327a062564a5d9f664918ba43', '[\"*\"]', NULL, NULL, '2025-10-22 04:32:55', '2025-10-22 04:32:55'),
(140, 'App\\Models\\User', 14, 'UserApp', 'd2e71cb0eb9a59a7890311022137900d744929e889a274a8208552d39b83570e', '[\"*\"]', '2025-10-22 04:33:26', NULL, '2025-10-22 04:33:11', '2025-10-22 04:33:26'),
(141, 'App\\Models\\User', 14, 'UserApp', 'd1f2750cba0f177b70c8ba444e5ee790a95a4ce342b8fd656dec8db5dcc3909f', '[\"*\"]', NULL, NULL, '2025-10-22 04:33:38', '2025-10-22 04:33:38'),
(142, 'App\\Models\\User', 18, 'UserApp', '9487d8b40bca81ffa8738ad58f8bab9cca9c46ddde7a798c186f207ad6c022de', '[\"*\"]', NULL, NULL, '2025-10-22 04:34:14', '2025-10-22 04:34:14'),
(143, 'App\\Models\\User', 14, 'UserApp', '35f2332eb6ca97521abcb04a49755f6413efc5c2d55586d2b0101be45b99ae93', '[\"*\"]', '2025-10-22 06:34:13', NULL, '2025-10-22 04:34:30', '2025-10-22 06:34:13'),
(144, 'App\\Models\\User', 1, 'UserApp', '7bd90b486655eed61b968c5bcd07a79d271d53c26136cd0599f1864911d315a3', '[\"*\"]', '2025-10-23 04:18:07', NULL, '2025-10-23 04:17:11', '2025-10-23 04:18:07'),
(145, 'App\\Models\\User', 14, 'UserApp', 'cac43f679e7f22861092c6f2bc44f91f0de9e5864c633c8c4d454550ed400cf8', '[\"*\"]', '2025-10-23 04:19:25', NULL, '2025-10-23 04:19:20', '2025-10-23 04:19:25'),
(146, 'App\\Models\\User', 14, 'UserApp', '20e75e756171e163edc45a747af73bafd6fe1d6cd51e765f44950985771f2d61', '[\"*\"]', NULL, NULL, '2025-10-23 04:19:57', '2025-10-23 04:19:57'),
(147, 'App\\Models\\User', 13, 'UserApp', 'ce5f2b2b73a8bf8b670446f576c3898c5705c9deb8d8ff27c6cf589721eb5b28', '[\"*\"]', '2025-11-03 06:01:19', NULL, '2025-10-23 04:20:20', '2025-11-03 06:01:19'),
(148, 'App\\Models\\User', 1, 'UserApp', 'e76731da9985fc3c2859c0cc9c8f069466629f1a80a188e63dfa961ca3c184af', '[\"*\"]', '2025-10-24 09:03:34', NULL, '2025-10-24 06:24:27', '2025-10-24 09:03:34'),
(149, 'App\\Models\\User', 14, 'UserApp', '01d265f280677c327699301b4b8e020115a825e4edb58a32ebb82c7dea620623', '[\"*\"]', '2025-10-31 16:21:04', NULL, '2025-10-27 04:20:44', '2025-10-31 16:21:04'),
(150, 'App\\Models\\User', 1, 'UserApp', '4d26c6765345863f72a857b616d8ac7a068a7c1439e2758cd5eec1c8c1c7c1c2', '[\"*\"]', '2025-11-05 09:28:00', NULL, '2025-10-27 19:59:57', '2025-11-05 09:28:00'),
(151, 'App\\Models\\User', 1, 'UserApp', '7801b413013af7d07be5ed1c78ac4351f81a7b362b81beb416a7beb89f1dccd7', '[\"*\"]', '2025-10-29 14:49:07', NULL, '2025-10-29 14:47:33', '2025-10-29 14:49:07'),
(152, 'App\\Models\\User', 14, 'UserApp', '9219e6b276e1effd772ab7ee017f5d32090bf153039b06be21bb79442ed7d974', '[\"*\"]', '2025-10-29 14:49:27', NULL, '2025-10-29 14:49:23', '2025-10-29 14:49:27'),
(153, 'App\\Models\\User', 13, 'UserApp', '02821a60c7fe1179be1fa152b3d72cf9385e644dfde488840336be73be831306', '[\"*\"]', '2025-10-29 14:52:42', NULL, '2025-10-29 14:52:27', '2025-10-29 14:52:42'),
(154, 'App\\Models\\User', 1, 'UserApp', '365e0f606eb52d47cc4fec824253329dfc103e0ba4b3840cd70e5dca1fc7a892', '[\"*\"]', '2025-10-31 16:00:58', NULL, '2025-10-31 16:00:58', '2025-10-31 16:00:58'),
(155, 'App\\Models\\User', 1, 'UserApp', 'fe98a2599bb6aab09b609379acfc3794ed9d87e8d7109a670611fc1b8c1f3751', '[\"*\"]', NULL, NULL, '2025-10-31 16:01:35', '2025-10-31 16:01:35'),
(156, 'App\\Models\\User', 14, 'UserApp', 'a2da8d2233c8b5e3856bc1050a319a09a4cbb0e2c637ffac9da8701542d8b6ab', '[\"*\"]', NULL, NULL, '2025-10-31 16:03:16', '2025-10-31 16:03:16'),
(157, 'App\\Models\\User', 1, 'UserApp', '6c6a57cdd6527657882668bdfbf1f2e89c93c852bb237379484a79cce213d3e5', '[\"*\"]', '2025-11-13 08:47:12', NULL, '2025-10-31 16:05:43', '2025-11-13 08:47:12'),
(158, 'App\\Models\\User', 1, 'UserApp', '444c8e5dfc9e7709cef697e241f23ffab84fc6a4cfcf910b69f1f3e809947e18', '[\"*\"]', '2025-11-03 05:58:27', NULL, '2025-10-31 16:21:21', '2025-11-03 05:58:27'),
(159, 'App\\Models\\User', 13, 'UserApp', '30c43ad87cbc49be72a50c4d163ff13647da1e852bdebfc9fb59137cf0f5397b', '[\"*\"]', '2025-11-03 05:59:15', NULL, '2025-11-03 05:59:15', '2025-11-03 05:59:15'),
(160, 'App\\Models\\User', 1, 'UserApp', '22c5944b4a92a5484da21faab67bbd19c00e6539324a5eb47c2013b507dab680', '[\"*\"]', '2025-11-03 06:02:25', NULL, '2025-11-03 06:01:01', '2025-11-03 06:02:25'),
(161, 'App\\Models\\User', 1, 'UserApp', '2399a40a492424d97d14c80aa710ad22b041794d5ba027d7fe40b94e57e4a5e7', '[\"*\"]', '2025-11-03 06:02:31', NULL, '2025-11-03 06:01:31', '2025-11-03 06:02:31'),
(162, 'App\\Models\\User', 14, 'UserApp', '2baf1e5e85d629832acedf0df4e35277a7f7ebc77117baf610fc190e6fb2e196', '[\"*\"]', '2025-11-03 06:03:10', NULL, '2025-11-03 06:02:40', '2025-11-03 06:03:10'),
(163, 'App\\Models\\User', 14, 'UserApp', '1da81a1a113e260df46585c12d7e1c0d528aaa8fa259fc05c3824e97a0a08a3f', '[\"*\"]', '2025-11-03 06:03:36', NULL, '2025-11-03 06:03:02', '2025-11-03 06:03:36'),
(164, 'App\\Models\\User', 13, 'UserApp', '3b5b2e19e4b3216d1bdeb82f363d19764bcf3ea5ae1698b348e05d45c7dcb462', '[\"*\"]', '2025-11-03 06:04:08', NULL, '2025-11-03 06:03:38', '2025-11-03 06:04:08'),
(165, 'App\\Models\\User', 13, 'UserApp', '54125f19de69ec9e79986b26aad2fe0b900dce174e55321561446e77bb81a84b', '[\"*\"]', '2025-11-03 06:07:02', NULL, '2025-11-03 06:03:59', '2025-11-03 06:07:02'),
(166, 'App\\Models\\User', 1, 'UserApp', 'c658c293587aa590024a8f866deb27b9b5b5040ae5fee259bea6366d3f2e635f', '[\"*\"]', '2025-11-03 06:07:39', NULL, '2025-11-03 06:06:16', '2025-11-03 06:07:39'),
(167, 'App\\Models\\User', 1, 'UserApp', 'd5b487f5be4cb154d114d37ff63c42f32e7ea9311038487861bfc9edfff0ae37', '[\"*\"]', '2025-11-03 06:08:25', NULL, '2025-11-03 06:07:25', '2025-11-03 06:08:25'),
(168, 'App\\Models\\User', 14, 'UserApp', '1c139a0766a1fa05112b8135a55d4f7506ce3742a1a2c9afad9e9193a77677eb', '[\"*\"]', '2025-11-03 06:08:20', NULL, '2025-11-03 06:08:14', '2025-11-03 06:08:20'),
(169, 'App\\Models\\User', 1, 'UserApp', '283627d79a70573f96f037ef4a454aae4e781ecdea08539ddbe6e23b0d3cd487', '[\"*\"]', '2025-11-03 06:09:17', NULL, '2025-11-03 06:08:37', '2025-11-03 06:09:17'),
(170, 'App\\Models\\User', 13, 'UserApp', '0959b343fcb3ccc8a69a297163a4458b50cc7c637246754cb92892fda1938b7a', '[\"*\"]', '2025-11-04 00:02:40', NULL, '2025-11-03 06:09:30', '2025-11-04 00:02:40'),
(171, 'App\\Models\\User', 13, 'UserApp', '0835edead24f5d9b3975ebafc05402899608de010f30ebd81758fd38b483e7e6', '[\"*\"]', '2025-11-03 06:26:32', NULL, '2025-11-03 06:23:49', '2025-11-03 06:26:32'),
(172, 'App\\Models\\User', 1, 'UserApp', 'b982aacd5cb7f9ff8eab1ecb4887bb053bf77f20ca02dc5d127bed2eaceae67c', '[\"*\"]', '2025-11-09 23:32:23', NULL, '2025-11-03 06:26:55', '2025-11-09 23:32:23'),
(173, 'App\\Models\\User', 1, 'UserApp', '530d7f3ae715ecb0a38ce7888a73bb3bfd1f6150a74caaf58835820ae0a24566', '[\"*\"]', '2025-11-03 08:00:01', NULL, '2025-11-03 07:57:35', '2025-11-03 08:00:01'),
(174, 'App\\Models\\User', 14, 'UserApp', '7edc444daba3d203f5d55c7e0e121058f41323cfa4a4d23da35839dbeb654eff', '[\"*\"]', '2025-11-03 08:02:35', NULL, '2025-11-03 08:00:52', '2025-11-03 08:02:35'),
(175, 'App\\Models\\User', 1, 'UserApp', '124eb4e7d85a5dab59399550dbe036861f19365681cb561ddf25ad6d84624680', '[\"*\"]', '2025-11-07 03:23:26', NULL, '2025-11-03 08:10:17', '2025-11-07 03:23:26'),
(176, 'App\\Models\\User', 13, 'UserApp', 'f87cc9267a9185abd99c4f9ae8dcac6627f0e270e03cfb793942574e5f592e98', '[\"*\"]', '2025-11-03 11:01:18', NULL, '2025-11-03 11:00:36', '2025-11-03 11:01:18'),
(177, 'App\\Models\\User', 1, 'UserApp', 'b91f67e3e83a88c3e6a77eb03bcbaad5a865146d25fb21f2b9b46edb58820c97', '[\"*\"]', '2025-11-04 00:20:06', NULL, '2025-11-04 00:08:22', '2025-11-04 00:20:06'),
(178, 'App\\Models\\User', 13, 'UserApp', 'cbdb9948d9e07c2fd0389b6592eb3424bc03fd86a87d1459d5cfa2bebfe8914b', '[\"*\"]', '2025-11-07 05:06:39', NULL, '2025-11-04 00:14:34', '2025-11-07 05:06:39'),
(179, 'App\\Models\\User', 1, 'UserApp', '92f16892e1ecec79e47376fb5129137959281378ee003195626dd10e21398dc6', '[\"*\"]', '2025-11-04 00:49:14', NULL, '2025-11-04 00:15:32', '2025-11-04 00:49:14'),
(180, 'App\\Models\\User', 1, 'UserApp', 'a877fe7b9c86d8728979cbbcb6cac498f04612f054eabc7afbbc23a199f2dbce', '[\"*\"]', '2025-11-04 00:22:19', NULL, '2025-11-04 00:22:13', '2025-11-04 00:22:19'),
(181, 'App\\Models\\User', 14, 'UserApp', '2db2dffd8e022ee014e83f74fb31368c40f5f8957ced3ab8ac738f77ce26b969', '[\"*\"]', '2025-11-04 00:25:00', NULL, '2025-11-04 00:22:38', '2025-11-04 00:25:00'),
(182, 'App\\Models\\User', 1, 'UserApp', 'fc104434ed6ce1375a5d5161b617651e8b9d9d5ef024989436ff1f593d88bc7a', '[\"*\"]', '2025-11-04 00:25:43', NULL, '2025-11-04 00:25:31', '2025-11-04 00:25:43'),
(183, 'App\\Models\\User', 14, 'UserApp', '69be3b81e91a566f9289a7fde59bb2f318590c2d648bba026aa66c60436da346', '[\"*\"]', '2025-11-04 00:26:17', NULL, '2025-11-04 00:26:16', '2025-11-04 00:26:17'),
(184, 'App\\Models\\User', 16, 'UserApp', 'fa42069135b6888c52876d0c26913f27770b107dffe252c49051af947984178b', '[\"*\"]', '2025-11-04 00:26:50', NULL, '2025-11-04 00:26:49', '2025-11-04 00:26:50'),
(185, 'App\\Models\\User', 1, 'UserApp', '9e174ee1c97afd92fee19c09cabd4c60c309f68c6e4247de8d66f0f67bd37ded', '[\"*\"]', '2025-11-04 02:11:05', NULL, '2025-11-04 00:27:28', '2025-11-04 02:11:05'),
(186, 'App\\Models\\User', 14, 'UserApp', 'd317c6e15451c3b5494c56c28a9af64b30242c5f6424709d958aebe91dad1e20', '[\"*\"]', '2025-11-07 05:03:47', NULL, '2025-11-04 00:30:12', '2025-11-07 05:03:47'),
(187, 'App\\Models\\User', 13, 'UserApp', '3c7a143c69d16e8681eee06e27814bd8982b4bec68f8814cafa24b9f377692f2', '[\"*\"]', '2025-11-04 01:09:28', NULL, '2025-11-04 00:50:50', '2025-11-04 01:09:28'),
(188, 'App\\Models\\User', 14, 'UserApp', 'a19e8bf2c054227bc8154224725d4ebde5cd98e24a9f7e1a622ff3e67808a714', '[\"*\"]', '2025-11-04 01:12:05', NULL, '2025-11-04 01:11:45', '2025-11-04 01:12:05'),
(189, 'App\\Models\\User', 13, 'UserApp', 'f1e4f43666e24e55573f027334f27e4e87571568557782e756f4a7cfb55ddd5f', '[\"*\"]', '2025-11-04 01:19:03', NULL, '2025-11-04 01:12:55', '2025-11-04 01:19:03'),
(190, 'App\\Models\\User', 1, 'UserApp', 'fa2c1a8d863a955e5909fe2451f2dad5278545ab33929e7fef18b4873a8f601e', '[\"*\"]', '2025-11-16 00:54:35', NULL, '2025-11-05 09:37:15', '2025-11-16 00:54:35'),
(191, 'App\\Models\\User', 13, 'UserApp', '142694eb6e5c8abd044c16138b12dc94a87a12271825db51d54b4f6bf5a4ac2e', '[\"*\"]', '2025-12-05 06:57:54', NULL, '2025-11-07 03:23:34', '2025-12-05 06:57:54'),
(192, 'App\\Models\\User', 14, 'UserApp', 'f72c3da67880729711d44afed1a1e8df1b2c995b9e6064540f4130537431ba86', '[\"*\"]', '2025-11-27 07:01:51', NULL, '2025-11-07 03:27:30', '2025-11-27 07:01:51'),
(193, 'App\\Models\\User', 1, 'UserApp', '4e563c2dff168e1565304be8851c6e906567fc7c6be8528b9c330b520c4761ab', '[\"*\"]', '2025-12-04 06:07:13', NULL, '2025-11-07 05:04:13', '2025-12-04 06:07:13'),
(194, 'App\\Models\\User', 14, 'UserApp', '8ca0e88646b820fcae0c5bcf1b135b61184794d7b04f20f56477429963e03ca4', '[\"*\"]', '2025-11-07 05:11:19', NULL, '2025-11-07 05:10:23', '2025-11-07 05:11:19'),
(195, 'App\\Models\\User', 16, 'UserApp', 'f2bc427c22101d151e95f67f5f847033a5cc1e7ea77a06460cedafc0c9d53e38', '[\"*\"]', '2025-11-07 05:11:41', NULL, '2025-11-07 05:11:37', '2025-11-07 05:11:41'),
(196, 'App\\Models\\User', 13, 'UserApp', '1443f65f2dc9663b1ed82f61cb8e3826ec2a75b6242db703ecffd77585e1d453', '[\"*\"]', '2025-12-08 01:39:35', NULL, '2025-11-07 05:14:24', '2025-12-08 01:39:35'),
(197, 'App\\Models\\User', 1, 'UserApp', '33849ee7542acdf7656d25bf75bf2a6db4182c7f7877de7ee9efdfffae0be910', '[\"*\"]', '2026-02-06 05:01:02', NULL, '2025-11-07 06:13:09', '2026-02-06 05:01:02'),
(198, 'App\\Models\\User', 1, 'UserApp', 'f9d7f0d3d6415f654fd7109a32af39afc79a772ad0c927e6bd46622f6e465f76', '[\"*\"]', '2025-11-09 22:33:46', NULL, '2025-11-09 21:37:28', '2025-11-09 22:33:46'),
(199, 'App\\Models\\User', 14, 'UserApp', '13ab77f1355fe476e4fa25b46dda1a7cb37298ff5d30e1c928be043793acef5c', '[\"*\"]', '2025-11-09 22:41:40', NULL, '2025-11-09 22:34:11', '2025-11-09 22:41:40'),
(200, 'App\\Models\\User', 13, 'UserApp', 'f0319c0ac8a4ce4f41325639a4d34099fdb9544fbc14c3427299da39d6219011', '[\"*\"]', '2025-11-09 22:43:36', NULL, '2025-11-09 22:42:00', '2025-11-09 22:43:36'),
(201, 'App\\Models\\User', 1, 'UserApp', '5471c50f349a225f6d7353c58de23fb784d50f2913e3090e986d2a4768c6920f', '[\"*\"]', '2025-11-09 22:49:59', NULL, '2025-11-09 22:45:57', '2025-11-09 22:49:59'),
(202, 'App\\Models\\User', 13, 'UserApp', '933a7ac3372818c3715290f07ee0c2f739cc20e398c8e0a871a042365e6b8604', '[\"*\"]', '2025-11-09 23:34:50', NULL, '2025-11-09 23:32:37', '2025-11-09 23:34:50'),
(203, 'App\\Models\\User', 14, 'UserApp', 'e8837a66ceeff58ba51de4c8fc997bc88a9313b0721b63d8b3f50262b6273368', '[\"*\"]', '2025-11-10 00:28:25', NULL, '2025-11-09 23:36:01', '2025-11-10 00:28:25'),
(204, 'App\\Models\\User', 13, 'UserApp', '448c46a8e057c0cee9e356dceb8ac1e3345242a32dd732e035b4b49a8744dc88', '[\"*\"]', '2025-11-10 00:29:40', NULL, '2025-11-10 00:29:11', '2025-11-10 00:29:40'),
(205, 'App\\Models\\User', 1, 'UserApp', '6fe8f8b6c6c6447146472ac1ef9692457d88548b733fd50757c334215412136d', '[\"*\"]', '2025-11-14 03:48:06', NULL, '2025-11-10 00:30:01', '2025-11-14 03:48:06'),
(206, 'App\\Models\\User', 13, 'UserApp', '8f42af77a7aa1ac3d4a2087db2de1ed86177a561acb0c9a669729b3a59cb6577', '[\"*\"]', '2025-11-18 04:59:22', NULL, '2025-11-13 08:47:23', '2025-11-18 04:59:22'),
(207, 'App\\Models\\User', 1, 'UserApp', '1b528f5607abe3beab54429f1e82852245cdad44059427db8544a5e3b2b7d87b', '[\"*\"]', '2025-11-13 09:00:43', NULL, '2025-11-13 08:55:31', '2025-11-13 09:00:43'),
(208, 'App\\Models\\User', 13, 'UserApp', '78d833058a04c855c222a28ef91bc491db0364979e8a50c690a43cc258c8d783', '[\"*\"]', '2025-11-14 04:45:07', NULL, '2025-11-14 03:48:18', '2025-11-14 04:45:07'),
(209, 'App\\Models\\User', 1, 'UserApp', 'ec9f1336f799dd1a8474facec98d06eb615381ac44c68b081f9255642da9d807', '[\"*\"]', '2025-12-05 06:36:39', NULL, '2025-11-27 06:52:54', '2025-12-05 06:36:39'),
(210, 'App\\Models\\User', 24, 'UserApp', '5f76d640b8049d35d4759b1cfbb14dc9aaca1b0327445f6a4b5c4f62f17d46a7', '[\"*\"]', '2025-11-27 07:42:15', NULL, '2025-11-27 06:59:06', '2025-11-27 07:42:15'),
(211, 'App\\Models\\User', 23, 'UserApp', '185644ba497c1bf0168ccfba9483a71261bd45443be2b64fc78fbe915e6e7b16', '[\"*\"]', '2025-11-27 07:34:12', NULL, '2025-11-27 07:01:59', '2025-11-27 07:34:12'),
(212, 'App\\Models\\User', 23, 'UserApp', '621cefdfdf590b48804c0ef2d1d76d544b0846e0f9987431111be4067130403b', '[\"*\"]', '2025-11-27 08:30:47', NULL, '2025-11-27 08:30:38', '2025-11-27 08:30:47'),
(213, 'App\\Models\\User', 13, 'UserApp', '1a1bbbd02d6d61cd7b42c31871d7e1882125f4a2caf11cc60d07403b4d283e0c', '[\"*\"]', '2025-12-04 06:08:06', NULL, '2025-12-04 06:07:46', '2025-12-04 06:08:06'),
(214, 'App\\Models\\User', 14, 'UserApp', '822c02fba2f0549f6ea822e3bc60fedbc2a128069e01f4d9bcd23d9a3e714560', '[\"*\"]', '2025-12-04 06:08:27', NULL, '2025-12-04 06:08:20', '2025-12-04 06:08:27'),
(215, 'App\\Models\\User', 1, 'UserApp', '056479211cda1fd5677517e2c705d9198a1011e0d3f5ad71285b6a59f1ee6bb3', '[\"*\"]', '2025-12-04 06:08:44', NULL, '2025-12-04 06:08:34', '2025-12-04 06:08:44'),
(216, 'App\\Models\\User', 40, 'UserApp', 'd3429194b35758ede060efea34b0c8b456192b5a5e56b71e12284dc674d66b25', '[\"*\"]', '2025-12-05 07:48:30', NULL, '2025-12-05 06:36:44', '2025-12-05 07:48:30'),
(217, 'App\\Models\\User', 1, 'UserApp', 'e1fd76fd6010ca2c66f3b5fb518de54813e52c56a9b6a11a75defec2cd8624a5', '[\"*\"]', '2025-12-08 03:27:22', NULL, '2025-12-05 06:58:06', '2025-12-08 03:27:22'),
(218, 'App\\Models\\User', 25, 'UserApp', 'fd96f42be9d48b95e9a19cfb36cea3cfd1789d6cad887f5989187f46fae708f0', '[\"*\"]', '2025-12-05 07:49:08', NULL, '2025-12-05 07:49:01', '2025-12-05 07:49:08'),
(219, 'App\\Models\\User', 26, 'UserApp', '0c05cb61e81cf6df7961bc53ffc8e45784358ef1f8d1967b467810d16149a6a1', '[\"*\"]', '2025-12-05 07:49:58', NULL, '2025-12-05 07:49:40', '2025-12-05 07:49:58'),
(220, 'App\\Models\\User', 27, 'UserApp', '32e72067ee1a68389d21909be64a2639fe4a61dcf603cab7380d14a9943a442b', '[\"*\"]', '2025-12-05 07:50:28', NULL, '2025-12-05 07:50:23', '2025-12-05 07:50:28'),
(221, 'App\\Models\\User', 28, 'UserApp', 'f560dd75ac870dc199c4a45e207d02d4e73ee3bbba3dda93ab9a6b35da33c00d', '[\"*\"]', '2025-12-08 03:40:24', NULL, '2025-12-05 07:50:46', '2025-12-08 03:40:24'),
(222, 'App\\Models\\User', 24, 'UserApp', 'bfd67753c48a2bd6037a20594c8e77536da7ce1e73dfaea89dd3b28c21cf88cd', '[\"*\"]', '2025-12-08 01:41:10', NULL, '2025-12-08 01:36:16', '2025-12-08 01:41:10'),
(223, 'App\\Models\\User', 24, 'UserApp', '61a53853344989026679da26914610600b6849f33418044a2b621410c0673e76', '[\"*\"]', '2025-12-09 04:04:35', NULL, '2025-12-08 01:40:06', '2025-12-09 04:04:35'),
(224, 'App\\Models\\User', 23, 'UserApp', '38b7f07966ff7d40ea84a67e4cbaeae65641037b257a9f6150e971a94d8bd0b0', '[\"*\"]', '2025-12-08 01:41:45', NULL, '2025-12-08 01:41:31', '2025-12-08 01:41:45'),
(225, 'App\\Models\\User', 1, 'UserApp', '02211d44a12e3445f3a08d14d1fac105e5752ac7c88e0c268ce9f4bfd678910b', '[\"*\"]', '2025-12-08 01:44:25', NULL, '2025-12-08 01:44:09', '2025-12-08 01:44:25'),
(226, 'App\\Models\\User', 15, 'UserApp', '07e38da14e04327fb5c92f2b41324456aa867c84968c2f3384105bde98b4aefa', '[\"*\"]', '2025-12-08 01:45:29', NULL, '2025-12-08 01:45:04', '2025-12-08 01:45:29'),
(227, 'App\\Models\\User', 24, 'UserApp', '7c01ca485096ebd81b3c8a5ccf1d4fe176c6b2ed7e4e22136fe48014f0f0b6a7', '[\"*\"]', '2025-12-08 01:47:40', NULL, '2025-12-08 01:46:32', '2025-12-08 01:47:40'),
(228, 'App\\Models\\User', 13, 'UserApp', 'bf8317f12feb8d53eb08372f0b3c1fee469d5e82ae2d85fde7aa34367167881b', '[\"*\"]', '2025-12-08 01:49:23', NULL, '2025-12-08 01:47:59', '2025-12-08 01:49:23'),
(229, 'App\\Models\\User', 15, 'UserApp', '2c96200ed1f42d0aea04fe831981759b9539ea842cfc46cf150811ca0ad26460', '[\"*\"]', '2025-12-11 03:23:01', NULL, '2025-12-08 03:35:59', '2025-12-11 03:23:01'),
(230, 'App\\Models\\User', 13, 'UserApp', '05afa4301e1b007ef15ea1f659fadcef73fad2378b48688d761d9864b066331b', '[\"*\"]', '2025-12-08 05:03:31', NULL, '2025-12-08 03:36:25', '2025-12-08 05:03:31'),
(231, 'App\\Models\\User', 1, 'UserApp', '459282fe149906bfd10dc7f6ff29107aac06fe4f64a909c49711055fa9663262', '[\"*\"]', '2025-12-11 03:27:19', NULL, '2025-12-08 03:40:30', '2025-12-11 03:27:19'),
(232, 'App\\Models\\User', 24, 'UserApp', '167b3798a84dfba8f4945813a9c3389dc8c3865004dc10eb5606dce9532ea9cd', '[\"*\"]', '2025-12-10 05:31:08', NULL, '2025-12-09 23:28:51', '2025-12-10 05:31:08'),
(233, 'App\\Models\\User', 1, 'UserApp', 'b136b2c55b15da65c6daf6fb304071f4c3ba278d3062dacab7efb5d849a1acfd', '[\"*\"]', '2025-12-10 05:31:26', NULL, '2025-12-10 05:31:25', '2025-12-10 05:31:26'),
(234, 'App\\Models\\User', 1, 'UserApp', '4d24f3c90816d5e44b80dc178377259484b759a8d162228694d8ecce8286b30c', '[\"*\"]', '2025-12-22 03:44:27', NULL, '2025-12-10 22:35:49', '2025-12-22 03:44:27'),
(235, 'App\\Models\\User', 26, 'UserApp', 'c16659ebb77b6c16c7b581322f65b784af4ff2216cd644b0c89d7761634b8d3d', '[\"*\"]', '2026-01-08 23:43:21', NULL, '2025-12-10 22:41:56', '2026-01-08 23:43:21'),
(236, 'App\\Models\\User', 13, 'UserApp', '8ebe6cef0d24d7da9220b06212244eba8447c2f5e7c70bddb574069ed8b3644c', '[\"*\"]', '2025-12-22 04:01:28', NULL, '2025-12-22 03:44:38', '2025-12-22 04:01:28'),
(237, 'App\\Models\\User', 13, 'UserApp', '162e038f45150df9eb6319eaac4298a804a164ca373d5b0a331bce21de01ffe4', '[\"*\"]', '2025-12-23 00:00:25', NULL, '2025-12-22 23:52:57', '2025-12-23 00:00:25'),
(238, 'App\\Models\\User', 1, 'UserApp', '453f5a55df6bcb40095938f94ad322eadad00c7a9a7c4afcf02a43523a3075a4', '[\"*\"]', '2026-01-09 02:02:06', NULL, '2026-01-08 23:43:31', '2026-01-09 02:02:06'),
(239, 'App\\Models\\User', 57, 'UserApp', 'e27055eb255060116138cd942c98e89feb17dde53b805dd6a84d557a8687b273', '[\"*\"]', '2026-01-09 02:04:44', NULL, '2026-01-09 02:02:49', '2026-01-09 02:04:44'),
(240, 'App\\Models\\User', 90, 'UserApp', '75fd33887be3770305b821331c3837edec684f53b9e3cbec3615ca510ab8219d', '[\"*\"]', '2026-01-09 02:05:51', NULL, '2026-01-09 02:05:33', '2026-01-09 02:05:51'),
(241, 'App\\Models\\User', 13, 'UserApp', 'd819f4f7b689749ff809027f10a16d12b7d085336fb789317c6cc0ff84ab7aa5', '[\"*\"]', '2026-01-09 02:08:21', NULL, '2026-01-09 02:06:10', '2026-01-09 02:08:21'),
(242, 'App\\Models\\User', 1, 'UserApp', 'f1fe80b49a40f6085ca4d42641464437aa95b21cf8781b0d3e18370ddca41681', '[\"*\"]', '2026-01-09 02:10:14', NULL, '2026-01-09 02:07:31', '2026-01-09 02:10:14'),
(243, 'App\\Models\\User', 1, 'UserApp', '06c9b145f3801639bd5d02b5ba46cedb13dad971cabce0815f0a18e828330582', '[\"*\"]', '2026-01-09 08:33:39', NULL, '2026-01-09 07:15:34', '2026-01-09 08:33:39'),
(244, 'App\\Models\\User', 13, 'UserApp', '2b2b6bfeac242c65bff123b43282bc45087ecb5d9f80479b4e94e1a35df89849', '[\"*\"]', '2026-01-11 03:45:14', NULL, '2026-01-09 07:25:50', '2026-01-11 03:45:14'),
(245, 'App\\Models\\User', 13, 'UserApp', '216381968e9994fc3dbad756f28767a78ea84bb74f6a11a3f70e747c278b8585', '[\"*\"]', '2026-03-18 02:22:41', NULL, '2026-01-09 08:39:14', '2026-03-18 02:22:41'),
(246, 'App\\Models\\User', 1, 'UserApp', 'b4ba85eedfd12977c1a074052f2343983e0e34bd610e0a2d9a605144ea6acf9c', '[\"*\"]', '2026-01-12 05:09:46', NULL, '2026-01-11 03:45:43', '2026-01-12 05:09:46'),
(247, 'App\\Models\\User', 1, 'UserApp', '28138c57f533e4214d230ce9bf805cb767b297e8423f5d4ac6662379c05fdb9c', '[\"*\"]', '2026-01-12 03:38:52', NULL, '2026-01-12 03:38:04', '2026-01-12 03:38:52'),
(248, 'App\\Models\\User', 13, 'UserApp', '6fc989d06b5c2cf4c13cd76080520a2e90b2c3bb9c014a31ccd5101c48ba92a9', '[\"*\"]', '2026-01-12 05:13:38', NULL, '2026-01-12 05:12:27', '2026-01-12 05:13:38'),
(249, 'App\\Models\\User', 1, 'UserApp', '3b461c8f5115e1eb603d0d465ae087da7c83be290015ff4a021ecc03e1dfd73f', '[\"*\"]', '2026-01-16 06:50:30', NULL, '2026-01-12 06:04:54', '2026-01-16 06:50:30'),
(250, 'App\\Models\\User', 1, 'UserApp', 'e9481dc0d691503f4cf966d4dcc3dd416463998df9f2fff9aa2c98bc62a0e2fb', '[\"*\"]', '2026-01-13 07:32:21', NULL, '2026-01-13 03:46:05', '2026-01-13 07:32:21'),
(251, 'App\\Models\\User', 13, 'UserApp', '45d47568d1e25f7e2516b677e506c5cc40a4ae764a6a7431d5713fe852355d03', '[\"*\"]', '2026-01-14 09:27:37', NULL, '2026-01-14 03:50:52', '2026-01-14 09:27:37'),
(252, 'App\\Models\\User', 1, 'UserApp', 'f2777e4b4806f45c278be1e845d6dfc19669bfe26d5b73162f69c41615ebed86', '[\"*\"]', '2026-01-14 09:29:02', NULL, '2026-01-14 09:28:00', '2026-01-14 09:29:02'),
(253, 'App\\Models\\User', 13, 'UserApp', 'a55a81a3fe815d2d00d676f7f026d379fb4beea70eac9371a8b73e5c7bcf0ea9', '[\"*\"]', '2026-01-14 09:30:49', NULL, '2026-01-14 09:29:52', '2026-01-14 09:30:49'),
(254, 'App\\Models\\User', 1, 'UserApp', '5e280afa4f933364e2770a84b840d936fcd5fd2cfeeeb865795dc402c75a7f94', '[\"*\"]', '2026-01-29 03:24:51', NULL, '2026-01-14 09:31:15', '2026-01-29 03:24:51'),
(255, 'App\\Models\\User', 1, 'UserApp', '1181933b27c42443d8fe87b18201408c30ada294a549b90d1cc9f7fd1e54ca4f', '[\"*\"]', '2026-01-14 23:08:30', NULL, '2026-01-14 23:02:09', '2026-01-14 23:08:30'),
(256, 'App\\Models\\User', 1, 'UserApp', '2233fa2744438fe65f16abf720049990aa21aa11ef6391f8221aa990eb54dda2', '[\"*\"]', '2026-01-15 00:49:03', NULL, '2026-01-15 00:49:00', '2026-01-15 00:49:03'),
(257, 'App\\Models\\User', 1, 'UserApp', '2fbeb2f95eb19f903868b53df6ae4047c34266c1b6c6ed22c5c491989a03a610', '[\"*\"]', '2026-01-15 00:56:40', NULL, '2026-01-15 00:56:34', '2026-01-15 00:56:40'),
(258, 'App\\Models\\User', 13, 'UserApp', '54f75ec5e1cd4175830ab0cafc6ffce988ff97794e9c42160d665f6234853ac9', '[\"*\"]', '2026-01-15 01:08:02', NULL, '2026-01-15 01:07:53', '2026-01-15 01:08:02'),
(259, 'App\\Models\\User', 1, 'UserApp', 'eb8c83029a72f05dc4b7c6ab62475b38860aa3294590cb0ca92f6642c78a506f', '[\"*\"]', '2026-01-15 01:10:00', NULL, '2026-01-15 01:08:32', '2026-01-15 01:10:00'),
(260, 'App\\Models\\User', 109, 'UserApp', '52d55cc8c6eade371c4e2abd7d696827041641a99ecb60a87ee362301f34817f', '[\"*\"]', '2026-01-15 01:10:37', NULL, '2026-01-15 01:10:36', '2026-01-15 01:10:37'),
(261, 'App\\Models\\User', 1, 'UserApp', '241897b95dc0abcc08a870283babd814c45565026bff0c25c56b38465924711f', '[\"*\"]', '2026-01-15 01:11:51', NULL, '2026-01-15 01:11:41', '2026-01-15 01:11:51'),
(262, 'App\\Models\\User', 109, 'UserApp', '0f88accb9ec79f28c018e92376b5d32520e9b98338f725b54d4a7111d6465cea', '[\"*\"]', '2026-01-15 01:45:25', NULL, '2026-01-15 01:12:03', '2026-01-15 01:45:25'),
(263, 'App\\Models\\User', 13, 'UserApp', '1f0b7125ed7aa0ad488b19e5b0edc37df70a8e5618d5accbf5c6680d159756bb', '[\"*\"]', '2026-01-15 02:08:05', NULL, '2026-01-15 01:17:21', '2026-01-15 02:08:05'),
(264, 'App\\Models\\User', 109, 'UserApp', '937708953416c5bf155059aad4d3b9148cb059fa9cf36d34e4508f339328dd3e', '[\"*\"]', '2026-01-15 02:17:15', NULL, '2026-01-15 02:09:15', '2026-01-15 02:17:15'),
(265, 'App\\Models\\User', 13, 'UserApp', '99cb313cb1233efa2b02756ede5f83479f224bf5522c0f23bd1a4a5c8e0b2ed4', '[\"*\"]', '2026-01-15 02:20:09', NULL, '2026-01-15 02:17:27', '2026-01-15 02:20:09'),
(266, 'App\\Models\\User', 109, 'UserApp', 'c1f484d21ed9fbc16c4026522e571d9b0ada9fcfeda6c2a5441dffb005cbaebb', '[\"*\"]', '2026-01-15 05:57:50', NULL, '2026-01-15 02:22:08', '2026-01-15 05:57:50'),
(267, 'App\\Models\\User', 13, 'UserApp', '27f8e9216379eeff7182c7f38b3ce415ce403e7b3c27242ba8e807d4d0103609', '[\"*\"]', '2026-01-15 05:11:07', NULL, '2026-01-15 05:05:39', '2026-01-15 05:11:07'),
(268, 'App\\Models\\User', 13, 'UserApp', 'c5afe5c17730c80c0aea27f7070ecb7474cee131c78770c6ee878ae4c2b9102a', '[\"*\"]', '2026-01-15 05:29:07', NULL, '2026-01-15 05:29:07', '2026-01-15 05:29:07'),
(269, 'App\\Models\\User', 110, 'ClientApp', '5b3f76ced35592f156483064224a1e00773fb94dd22e848c98419140b1764149', '[\"*\"]', NULL, NULL, '2026-01-15 05:55:50', '2026-01-15 05:55:50'),
(270, 'App\\Models\\User', 109, 'UserApp', 'f1c9cb2f18a7496773678db81676e45c74b4dae22bb50ad9437941d1e9766920', '[\"*\"]', '2026-01-15 23:45:50', NULL, '2026-01-15 23:24:56', '2026-01-15 23:45:50');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(271, 'App\\Models\\User', 110, 'UserApp', '78f9aa55873ff560bbdd84718b027b508445c1dc1981b5092eb1eceaff97332f', '[\"*\"]', '2026-01-16 06:42:41', NULL, '2026-01-15 23:26:17', '2026-01-16 06:42:41'),
(272, 'App\\Models\\User', 109, 'UserApp', 'd58706993e50051a5743b9fe4ac0dc88bff60d3aae746c3d2111e67a13a6596d', '[\"*\"]', '2026-01-19 01:55:00', NULL, '2026-01-16 06:43:32', '2026-01-19 01:55:00'),
(273, 'App\\Models\\User', 13, 'UserApp', 'a45d3960f0635313af889ec11555dd9d29aa1e970cf178d63eaa06cdf70b23c1', '[\"*\"]', '2026-01-16 11:09:38', NULL, '2026-01-16 06:51:13', '2026-01-16 11:09:38'),
(274, 'App\\Models\\User', 1, 'UserApp', 'fc11ce6a240818d0aa153f3cf05cbf62ae3153734283b0a2437738dc1fce28ec', '[\"*\"]', '2026-02-10 11:28:10', NULL, '2026-01-16 11:09:46', '2026-02-10 11:28:10'),
(275, 'App\\Models\\User', 110, 'UserApp', '9d93c8ef48df8b5caf2982488ee86361233c1a911e64378d738f68ec6730e4c6', '[\"*\"]', '2026-01-19 02:09:05', NULL, '2026-01-19 01:55:12', '2026-01-19 02:09:05'),
(276, 'App\\Models\\User', 109, 'UserApp', '3d8ff5749c417e4819afc972d2fbb39a115c5db9f3933b94fcbb5bc131db4865', '[\"*\"]', '2026-01-19 04:56:36', NULL, '2026-01-19 02:09:23', '2026-01-19 04:56:36'),
(277, 'App\\Models\\User', 110, 'UserApp', 'c169d28e38158a6e7bc8adbbb5ff91571bbae534678d2b60cc5bc9d8fc3b4835', '[\"*\"]', '2026-01-19 02:30:59', NULL, '2026-01-19 02:26:22', '2026-01-19 02:30:59'),
(278, 'App\\Models\\User', 1, 'UserApp', 'd64c7f6decf5a50179ed66b7af65d628a547766e2a256a078fa7447230831fd6', '[\"*\"]', '2026-03-31 05:17:23', NULL, '2026-01-19 04:11:02', '2026-03-31 05:17:23'),
(279, 'App\\Models\\User', 109, 'UserApp', 'e0f9a7aef3abde514cb622d37e8f013695bf573afbfc2bae99b8b90d2a38d20a', '[\"*\"]', '2026-01-19 23:47:52', NULL, '2026-01-19 05:35:51', '2026-01-19 23:47:52'),
(280, 'App\\Models\\User', 110, 'UserApp', '5f19474ab62c011883742b66413dd62c5b7acfc59abf7cfcac980d30a5f21599', '[\"*\"]', '2026-01-20 04:36:57', NULL, '2026-01-19 23:47:35', '2026-01-20 04:36:57'),
(281, 'App\\Models\\User', 109, 'UserApp', '65a3450cc3a6893f64fa64af118f7d4fc37189313e44295eb619d4be083fe335', '[\"*\"]', '2026-01-20 06:50:09', NULL, '2026-01-20 04:37:12', '2026-01-20 06:50:09'),
(282, 'App\\Models\\User', 65, 'UserApp', '54326c8dc7b50569d8fae77edb2a02e7a05fd2b79cdaa8c24f2a73af0a5d5262', '[\"*\"]', '2026-01-20 06:40:53', NULL, '2026-01-20 06:33:03', '2026-01-20 06:40:53'),
(283, 'App\\Models\\User', 110, 'UserApp', 'f9dad0708e888059b044bcb2a6a00acc4d615e63ea077bc826c713f95596645a', '[\"*\"]', '2026-01-20 06:43:31', NULL, '2026-01-20 06:43:23', '2026-01-20 06:43:31'),
(284, 'App\\Models\\User', 58, 'UserApp', '741a40a343abd3e86acab3ee088b5cf3a293441b41417acb9edcbf04e3e41a4f', '[\"*\"]', '2026-01-20 06:53:27', NULL, '2026-01-20 06:50:09', '2026-01-20 06:53:27'),
(285, 'App\\Models\\User', 110, 'UserApp', 'a98086a407c9ca18414304c0a96b550d67352444bf801f8665efcc597d613fcd', '[\"*\"]', '2026-01-20 06:58:49', NULL, '2026-01-20 06:58:40', '2026-01-20 06:58:49'),
(286, 'App\\Models\\User', 1, 'UserApp', 'ba5ebc99699a29345f744f9de50b308d8240ff1643b0d263eea4b64a07989e0b', '[\"*\"]', '2026-03-19 04:58:48', NULL, '2026-01-20 07:22:10', '2026-03-19 04:58:48'),
(287, 'App\\Models\\User', 23, 'UserApp', '7595878d5146fe1cde46c809885b7107379915617e9a58f63e43bb513bb21589', '[\"*\"]', '2026-01-20 07:23:46', NULL, '2026-01-20 07:23:30', '2026-01-20 07:23:46'),
(288, 'App\\Models\\User', 1, 'UserApp', 'ea5e5de543f30c3aedb598debb8cd13bb6b1cbc58ce029fd16ed5e786332edfa', '[\"*\"]', '2026-01-28 09:35:38', NULL, '2026-01-20 07:23:57', '2026-01-28 09:35:38'),
(289, 'App\\Models\\User', 109, 'UserApp', 'd7eef4f978d462e94a11c2b147ebd9d66c6a5781489acd0c920caf255be0606e', '[\"*\"]', '2026-01-22 06:58:37', NULL, '2026-01-22 03:43:54', '2026-01-22 06:58:37'),
(290, 'App\\Models\\User', 110, 'UserApp', '3028fad284f45c205388692c9fc73292f3b09a923adbd9421b2cb27d592b275d', '[\"*\"]', '2026-01-23 03:06:23', NULL, '2026-01-22 03:56:15', '2026-01-23 03:06:23'),
(291, 'App\\Models\\User', 109, 'UserApp', 'cba48a0dcb647d10fd538b68f1ac6e1a3f545fac67e8083d9db322c52617ec1e', '[\"*\"]', '2026-01-23 03:05:40', NULL, '2026-01-22 22:39:28', '2026-01-23 03:05:40'),
(292, 'App\\Models\\User', 1, 'UserApp', '1eadab81877168f9a8a276577eff6f2301948269a6d34cc7b67f9f55640fa88a', '[\"*\"]', '2026-01-23 02:07:56', NULL, '2026-01-23 02:03:32', '2026-01-23 02:07:56'),
(293, 'App\\Models\\User', 111, 'ClientApp', '6f5de91e2501a594aab5abc14708dba16ba0157bff819b4aa441eb1175362a97', '[\"*\"]', NULL, NULL, '2026-01-23 02:11:01', '2026-01-23 02:11:01'),
(294, 'App\\Models\\User', 111, 'UserApp', '8a37b2c43e283402faa9ce4f187085388d50385f7036a5cb4c1d6b993102b10c', '[\"*\"]', '2026-02-26 05:12:12', NULL, '2026-01-23 02:13:08', '2026-02-26 05:12:12'),
(295, 'App\\Models\\User', 65, 'UserApp', '41e49285805ff52dcc612a147f9f95bfc97cb4a7822e8b275430f29cf34ead64', '[\"*\"]', '2026-01-23 02:52:44', NULL, '2026-01-23 02:42:53', '2026-01-23 02:52:44'),
(296, 'App\\Models\\User', 70, 'UserApp', '533a80413540a50d8e131b904266fc383b6e3649ee28d634fa3a553d7aa15156', '[\"*\"]', '2026-01-23 02:59:05', NULL, '2026-01-23 02:51:02', '2026-01-23 02:59:05'),
(297, 'App\\Models\\User', 65, 'UserApp', '4fdfd6356c92d2fda1ac04dca449bfb661830e14fd9e732a6a84f3e11d8865fb', '[\"*\"]', '2026-01-23 03:05:53', NULL, '2026-01-23 02:59:36', '2026-01-23 03:05:53'),
(298, 'App\\Models\\User', 109, 'UserApp', '981fa22b4fca6e28faa83dce819d2552df95ed7785d3bf4572475e1382560535', '[\"*\"]', '2026-01-23 03:07:10', NULL, '2026-01-23 03:06:40', '2026-01-23 03:07:10'),
(299, 'App\\Models\\User', 110, 'UserApp', 'c682fb5be8f1d7fc2330f3d5bbcc4195791791fbc10d6ce3ed7c5dee636a92b8', '[\"*\"]', '2026-01-23 06:24:38', NULL, '2026-01-23 03:07:17', '2026-01-23 06:24:38'),
(300, 'App\\Models\\User', 65, 'UserApp', 'e2db747f832024e138d86872393d9987f7a74ff65b3337e70aaa5c58cc2e3871', '[\"*\"]', '2026-01-23 03:08:38', NULL, '2026-01-23 03:08:31', '2026-01-23 03:08:38'),
(301, 'App\\Models\\User', 110, 'UserApp', '59812541a185500badf7425e1b9dc42486810d6af879e3f2d2442b4c45244197', '[\"*\"]', '2026-01-27 01:40:14', NULL, '2026-01-26 22:14:48', '2026-01-27 01:40:14'),
(302, 'App\\Models\\User', 109, 'UserApp', '44ef8476ac3931cef05eef285d9faee8d840752243e5bd47bd7337cfd5dd3a43', '[\"*\"]', '2026-01-27 06:21:30', NULL, '2026-01-26 22:14:56', '2026-01-27 06:21:30'),
(303, 'App\\Models\\User', 110, 'UserApp', '8368a278637ca8dad3762cc201446498927d7119b9f1d14ce25e2048cffc77cc', '[\"*\"]', '2026-01-27 04:53:25', NULL, '2026-01-27 04:19:20', '2026-01-27 04:53:25'),
(304, 'App\\Models\\User', 65, 'UserApp', '6d9404c41b7b054547b2a778b9327720e2c8dd659e7eedcb407b6efdb28e0b65', '[\"*\"]', '2026-01-27 04:21:16', NULL, '2026-01-27 04:21:11', '2026-01-27 04:21:16'),
(305, 'App\\Models\\User', 57, 'UserApp', 'b80f5c99327cfa791cd70223d665d3bbac79b99dc68fb63a629012e4c7d101f7', '[\"*\"]', '2026-01-28 09:36:00', NULL, '2026-01-28 09:35:50', '2026-01-28 09:36:00'),
(306, 'App\\Models\\User', 13, 'UserApp', 'aebc8e7a48852d2f838afc681652b107c52748b351ba52eab3030bdb191e7b21', '[\"*\"]', '2026-01-28 09:37:20', NULL, '2026-01-28 09:37:07', '2026-01-28 09:37:20'),
(307, 'App\\Models\\User', 57, 'UserApp', '375427de62c76f4cb23c2c518cae86488202771a79a7c7919ef1dc84459ccbd4', '[\"*\"]', '2026-01-30 15:01:43', NULL, '2026-01-28 09:38:04', '2026-01-30 15:01:43'),
(308, 'App\\Models\\User', 110, 'UserApp', '1e27fd4e95b23ba37a9a7e3c8c2bfebebc2fd5843668cce19f8d8a97e839d163', '[\"*\"]', '2026-01-29 01:22:45', NULL, '2026-01-28 23:12:01', '2026-01-29 01:22:45'),
(309, 'App\\Models\\User', 109, 'UserApp', '1f0441917311f6766138532d7df6c3bb5f72fe1336c6e7682f2869bfe18e0299', '[\"*\"]', '2026-01-29 01:26:16', NULL, '2026-01-29 01:26:02', '2026-01-29 01:26:16'),
(310, 'App\\Models\\User', 110, 'UserApp', '0c6ca4a2e32909d6268996cb184e76a608b49ba548a810a0501727cb45b0598b', '[\"*\"]', '2026-01-29 03:03:29', NULL, '2026-01-29 01:26:28', '2026-01-29 03:03:29'),
(311, 'App\\Models\\User', 109, 'UserApp', 'f53fac1506b0c6ff113d98b2bf2c3e24aa1c7994c80b4e8e1a045fd1aeaa15ac', '[\"*\"]', '2026-01-29 03:04:00', NULL, '2026-01-29 03:03:49', '2026-01-29 03:04:00'),
(312, 'App\\Models\\User', 109, 'UserApp', 'b24034cce24fe1ba279c88cafea4b4c0591f073260ac1b26803f20f32886908e', '[\"*\"]', '2026-01-29 03:09:58', NULL, '2026-01-29 03:09:31', '2026-01-29 03:09:58'),
(313, 'App\\Models\\User', 58, 'UserApp', 'e53bd9c2a5b8fd3a8248d6955d55c1d6ae4fa0da5b180a59988c47f9f320e7c8', '[\"*\"]', '2026-01-29 03:10:43', NULL, '2026-01-29 03:10:38', '2026-01-29 03:10:43'),
(314, 'App\\Models\\User', 109, 'UserApp', '448fe270953315a885a3454ac7a6d292c14ab8d8727079b24fda75f9252a2e87', '[\"*\"]', '2026-01-29 03:19:05', NULL, '2026-01-29 03:13:52', '2026-01-29 03:19:05'),
(315, 'App\\Models\\User', 65, 'UserApp', '9980f492de7d16b6fe6a43891b3e1364fbf435f0de19c63b5a4af37ef2fd3e67', '[\"*\"]', '2026-01-29 04:11:13', NULL, '2026-01-29 03:14:28', '2026-01-29 04:11:13'),
(316, 'App\\Models\\User', 1, 'UserApp', '4a0dee22be50e60cbd980a778cbb9016c7f4358df3852b348ab8af4178d813cb', '[\"*\"]', '2026-01-29 03:31:15', NULL, '2026-01-29 03:29:45', '2026-01-29 03:31:15'),
(317, 'App\\Models\\User', 1, 'UserApp', 'a39c1f5ff82a8b89f2e898e62d1bcd1fb1f7dbccda2367590ad65059e7aff23a', '[\"*\"]', '2026-02-13 19:13:57', NULL, '2026-01-29 03:31:07', '2026-02-13 19:13:57'),
(318, 'App\\Models\\User', 109, 'UserApp', '0a1e8093c836a1bdb9a8cd2cb8ebc7c60807a1e8d54862b4ba8da50a4efede2c', '[\"*\"]', '2026-01-29 04:11:20', NULL, '2026-01-29 04:11:19', '2026-01-29 04:11:20'),
(319, 'App\\Models\\User', 65, 'UserApp', '289958faf71dd9ecd82b9e5a73259a42593e0bfa20626ab78fb7c51d3eb20fa8', '[\"*\"]', '2026-01-29 06:19:07', NULL, '2026-01-29 04:14:06', '2026-01-29 06:19:07'),
(320, 'App\\Models\\User', 109, 'UserApp', '0ee6b38f9e584b1f845aa045d90dbb5008aafc817372d57560e2c69b61ff380c', '[\"*\"]', '2026-01-29 06:17:23', NULL, '2026-01-29 06:17:18', '2026-01-29 06:17:23'),
(321, 'App\\Models\\User', 110, 'UserApp', 'ce53096e7b4eee68f9b553c3bf6b273086b767099db4650e9a061708558842a7', '[\"*\"]', '2026-01-29 06:17:31', NULL, '2026-01-29 06:17:30', '2026-01-29 06:17:31'),
(322, 'App\\Models\\User', 65, 'UserApp', '678eef6b2f7b3f1f7bc848460a82183c839f9c5fd5d128d9959efa8b9d257916', '[\"*\"]', '2026-01-29 22:47:39', NULL, '2026-01-29 06:44:56', '2026-01-29 22:47:39'),
(323, 'App\\Models\\User', 109, 'UserApp', 'dac4989a9d7fd719d717a546f71eafd622b99767d165526ae838dd6d083db5e0', '[\"*\"]', '2026-01-29 22:53:13', NULL, '2026-01-29 22:46:21', '2026-01-29 22:53:13'),
(324, 'App\\Models\\User', 110, 'UserApp', 'd8f50042b6d9bbe5b013909e186d4e9069992c38f9ab888e4e081a18ee72534b', '[\"*\"]', '2026-02-02 01:31:03', NULL, '2026-01-29 22:46:29', '2026-02-02 01:31:03'),
(325, 'App\\Models\\User', 13, 'UserApp', '2691f478bcfeec6f1b9578cb42e2ae1996ff299b30cb3d95d108981062d05aa9', '[\"*\"]', '2026-01-30 15:02:25', NULL, '2026-01-30 15:01:55', '2026-01-30 15:02:25'),
(326, 'App\\Models\\User', 57, 'UserApp', '9e39191cf4c9d97bcd8f37bc4d30020316de7f12ab2a11c1f77b4fdb662f8e85', '[\"*\"]', '2026-01-30 15:03:26', NULL, '2026-01-30 15:03:00', '2026-01-30 15:03:26'),
(327, 'App\\Models\\User', 65, 'UserApp', '334f9662fd923d603747e3e1f9e3ed033504dd65025dac3de2a16438dc14936b', '[\"*\"]', '2026-01-30 15:03:37', NULL, '2026-01-30 15:03:36', '2026-01-30 15:03:37'),
(328, 'App\\Models\\User', 1, 'UserApp', '3115602195cf33c19a66d0e319280911582a42eb4c2cd78716f792b5f055fe99', '[\"*\"]', '2026-01-30 15:03:57', NULL, '2026-01-30 15:03:49', '2026-01-30 15:03:57'),
(329, 'App\\Models\\User', 13, 'UserApp', '8a489750ba3e733c3c7436b1fe37cf3617b5ff47c3114cb2d16ce08d6b1a9dae', '[\"*\"]', '2026-02-02 05:16:18', NULL, '2026-01-30 15:04:20', '2026-02-02 05:16:18'),
(330, 'App\\Models\\User', 65, 'UserApp', 'c7fbdae659f4a85b0247c736ae620436a40768b1958bcfb33c2a69bcd10d90a3', '[\"*\"]', '2026-02-01 23:43:56', NULL, '2026-02-01 23:43:31', '2026-02-01 23:43:56'),
(331, 'App\\Models\\User', 109, 'UserApp', '3f4bdff03379b2e25f65db4b6436822a0062a36e8d07822568198fa25211be17', '[\"*\"]', '2026-02-02 01:27:45', NULL, '2026-02-02 00:17:50', '2026-02-02 01:27:45'),
(332, 'App\\Models\\User', 60, 'UserApp', '20985073cb6223fc05e65db0bb80050f7766eb5a065ef061909cf72104be18ee', '[\"*\"]', '2026-02-02 01:24:23', NULL, '2026-02-02 01:20:56', '2026-02-02 01:24:23'),
(333, 'App\\Models\\User', 70, 'UserApp', '0407d87da9b9a691299914653289db0db4299fa461f7915803bd6fe4c7b69435', '[\"*\"]', '2026-02-02 01:25:56', NULL, '2026-02-02 01:25:31', '2026-02-02 01:25:56'),
(334, 'App\\Models\\User', 70, 'UserApp', '9bad04607ab96f799dc24977bf948e30ddbb447a946f387b78a341d0569c9062', '[\"*\"]', '2026-02-02 01:32:18', NULL, '2026-02-02 01:27:21', '2026-02-02 01:32:18'),
(335, 'App\\Models\\User', 60, 'UserApp', '94f093a38ead34debe8962aa0c1ef3d287dd8212778e45135a53a236bd7fb09e', '[\"*\"]', '2026-02-02 01:28:54', NULL, '2026-02-02 01:28:33', '2026-02-02 01:28:54'),
(336, 'App\\Models\\User', 110, 'UserApp', '626ed73d27de2f16c0ee1af379c5b7cccbd89a432fde96f9d3ed0b954641ccc9', '[\"*\"]', '2026-02-02 01:37:19', NULL, '2026-02-02 01:36:17', '2026-02-02 01:37:19'),
(337, 'App\\Models\\User', 70, 'UserApp', '72a5e045405c0c001afc2dd8f3a756a4736d03a7fe36732d391bdb06488ffbea', '[\"*\"]', '2026-02-02 01:38:51', NULL, '2026-02-02 01:37:45', '2026-02-02 01:38:51'),
(338, 'App\\Models\\User', 110, 'UserApp', '52deae9df76af6f0db2c375e8bb95d19917ead341aaa657a84e2ab0e01b8e52a', '[\"*\"]', '2026-02-02 01:46:07', NULL, '2026-02-02 01:39:07', '2026-02-02 01:46:07'),
(339, 'App\\Models\\User', 109, 'UserApp', '08400f2b09445fe24eaeb7d2753f0ed6336351249a9e6dbed861495b10fb7ea3', '[\"*\"]', '2026-02-02 01:48:23', NULL, '2026-02-02 01:47:24', '2026-02-02 01:48:23'),
(340, 'App\\Models\\User', 109, 'UserApp', 'ab50b927e429456af4a8d43dc9c86fa4bd27f6b588863bc79f557df35966d43e', '[\"*\"]', '2026-02-02 01:49:24', NULL, '2026-02-02 01:49:24', '2026-02-02 01:49:24'),
(341, 'App\\Models\\User', 110, 'UserApp', '8dd74e7354355f20b858911664ac3d1a2db28b67477daa41c6b85791b910eb97', '[\"*\"]', '2026-02-03 05:29:05', NULL, '2026-02-02 01:49:40', '2026-02-03 05:29:05'),
(342, 'App\\Models\\User', 13, 'UserApp', 'b97be2b1488c4ed4e4e7416200b6923898749977cbcddcb42a798fd4f1f89140', '[\"*\"]', '2026-02-03 05:13:09', NULL, '2026-02-02 11:00:52', '2026-02-03 05:13:09'),
(343, 'App\\Models\\User', 109, 'UserApp', 'e7d4378ca04442bf66cc80600e5a3d63fad1519bf45d4456eaf7e15ac10d79be', '[\"*\"]', '2026-02-03 05:19:40', NULL, '2026-02-03 04:53:42', '2026-02-03 05:19:40'),
(344, 'App\\Models\\User', 1, 'UserApp', '0986bfa5f172a97987573abe0dcc9238a16940f4299b624d739af1eaeb8eb478', '[\"*\"]', '2026-02-04 06:35:14', NULL, '2026-02-03 05:16:51', '2026-02-04 06:35:14'),
(345, 'App\\Models\\User', 13, 'UserApp', 'f3f05bdd1d8b9e383286c1893930f97b5239cd9ad5a7c0546ece8de891085435', '[\"*\"]', '2026-02-03 05:46:28', NULL, '2026-02-03 05:22:54', '2026-02-03 05:46:28'),
(346, 'App\\Models\\User', 109, 'UserApp', 'd91e87e41a4432ad2191f069d5cf39187707c3fe54775c8a0ab57e0224e0fe52', '[\"*\"]', '2026-02-03 05:42:12', NULL, '2026-02-03 05:29:23', '2026-02-03 05:42:12'),
(347, 'App\\Models\\User', 1, 'UserApp', '6e27adcc184d85dda7adc9a792dd09c2c13dcc46160d5d338d7ff62b40ac914f', '[\"*\"]', '2026-02-03 05:31:42', NULL, '2026-02-03 05:31:37', '2026-02-03 05:31:42'),
(348, 'App\\Models\\User', 1, 'UserApp', '79c5e29b07234b84bf68502607036603a61764fefc1436ab9eb7ab2c15d18dfe', '[\"*\"]', '2026-02-03 05:33:00', NULL, '2026-02-03 05:32:55', '2026-02-03 05:33:00'),
(349, 'App\\Models\\User', 65, 'UserApp', '4837e10300755a014ca91688dbda1299aff7004a2de957645db5fa15dc1eb0aa', '[\"*\"]', '2026-02-03 05:33:38', NULL, '2026-02-03 05:33:24', '2026-02-03 05:33:38'),
(350, 'App\\Models\\User', 1, 'UserApp', 'cd2a881e3e76dae618d90e9e48fb6fcd3a4cf33c8dbdda8af9b14d57428cac27', '[\"*\"]', '2026-02-03 10:59:09', NULL, '2026-02-03 05:34:55', '2026-02-03 10:59:09'),
(351, 'App\\Models\\User', 65, 'UserApp', '84b84062581ba20b35fc44d12b7c9dce66a49e435d9c658605e834e78c36cf06', '[\"*\"]', '2026-02-03 06:00:52', NULL, '2026-02-03 05:43:31', '2026-02-03 06:00:52'),
(352, 'App\\Models\\User', 110, 'UserApp', '1788bb22ee53b7e55eb9ec9e3f9ff6d6d640b5b297dde3c303b83e92f10818cd', '[\"*\"]', '2026-02-03 06:03:06', NULL, '2026-02-03 06:02:57', '2026-02-03 06:03:06'),
(353, 'App\\Models\\User', 109, 'UserApp', '278512613a1233d41612259953faa70e86ed762cfee2c372cd7c5c28a7ca8ecf', '[\"*\"]', '2026-02-03 06:05:23', NULL, '2026-02-03 06:03:43', '2026-02-03 06:05:23'),
(354, 'App\\Models\\User', 70, 'UserApp', 'c2f62b418683713b9fab473cd36c851034a5691bcc11b23687ec37ff98e53cce', '[\"*\"]', '2026-02-03 06:04:23', NULL, '2026-02-03 06:04:07', '2026-02-03 06:04:23'),
(355, 'App\\Models\\User', 62, 'UserApp', '9998c659660a1b1337d6ea8928eb6a26759a98637643818dbc154e50fe305aef', '[\"*\"]', '2026-02-03 06:07:00', NULL, '2026-02-03 06:05:00', '2026-02-03 06:07:00'),
(356, 'App\\Models\\User', 1, 'UserApp', 'e063a225c5de182122a9dcbbb5a96e142588f32cb6b0858fbb73f6ecab6d8f94', '[\"*\"]', '2026-02-03 06:06:11', NULL, '2026-02-03 06:06:05', '2026-02-03 06:06:11'),
(357, 'App\\Models\\User', 62, 'UserApp', '982cc92bbbdb798e3a550cba7ff8e0f1b60399a3972f8317092de9ae867c2798', '[\"*\"]', '2026-02-03 07:47:45', NULL, '2026-02-03 06:06:29', '2026-02-03 07:47:45'),
(358, 'App\\Models\\User', 109, 'UserApp', '7acfd2767cd01f51c349e1c4c8fd27b8c2f94cf317899617b0c8437add41ecc8', '[\"*\"]', '2026-02-03 06:07:32', NULL, '2026-02-03 06:07:11', '2026-02-03 06:07:32'),
(359, 'App\\Models\\User', 110, 'UserApp', 'f8853c8ecb85a58a15d37739a4017e2f3303105e66335491efb13785ad0d1239', '[\"*\"]', '2026-02-03 06:10:51', NULL, '2026-02-03 06:07:38', '2026-02-03 06:10:51'),
(360, 'App\\Models\\User', 109, 'UserApp', 'c79c29ac2a6620eaf586fce8c0b661787af1da11861ed97f1a8f02bf5b6ea525', '[\"*\"]', '2026-02-04 22:46:53', NULL, '2026-02-03 06:11:45', '2026-02-04 22:46:53'),
(361, 'App\\Models\\User', 1, 'UserApp', '8d5a7ae4ab6e982db26bf2741345c8960313aa4a56c52410c3b1843507e84002', '[\"*\"]', '2026-02-13 05:13:57', NULL, '2026-02-03 06:19:52', '2026-02-13 05:13:57'),
(362, 'App\\Models\\User', 1, 'UserApp', 'a491763e58c4efb9a3f9c4ba23be0038a850298003092a29e06438f37142c1f3', '[\"*\"]', '2026-02-03 07:52:59', NULL, '2026-02-03 07:47:52', '2026-02-03 07:52:59'),
(363, 'App\\Models\\User', 13, 'UserApp', '551757e89baf5b4116f6f8e8f5c2b29858c53acde66e1948244578a0fe0e055e', '[\"*\"]', '2026-02-03 08:40:35', NULL, '2026-02-03 07:53:16', '2026-02-03 08:40:35'),
(364, 'App\\Models\\User', 13, 'UserApp', '995ea4c81df2b0be6db37eab77d3bfec090c3f44214137b2f73e3dfee6d255e9', '[\"*\"]', '2026-02-04 06:36:52', NULL, '2026-02-03 08:40:47', '2026-02-04 06:36:52'),
(365, 'App\\Models\\User', 1, 'UserApp', 'bd418b5396b1850ad48c2653f408064408dee3ad738267ef45d1e7b949b0a80e', '[\"*\"]', '2026-02-03 10:59:09', NULL, '2026-02-03 08:41:34', '2026-02-03 10:59:09'),
(366, 'App\\Models\\User', 1, 'UserApp', '835c434bffc2b6cb8cae181783ce8366c1ef88957a8daa8a8e20853fa2b994f8', '[\"*\"]', '2026-02-04 06:33:37', NULL, '2026-02-04 06:27:15', '2026-02-04 06:33:37'),
(367, 'App\\Models\\User', 90, 'UserApp', '783c041c18691dc81814930b9844f21d0a944fd32c663505a4f47f109a5d6dc2', '[\"*\"]', '2026-02-05 00:20:31', NULL, '2026-02-04 06:35:36', '2026-02-05 00:20:31'),
(368, 'App\\Models\\User', 1, 'UserApp', 'd8d2a6ea7440052fc036008bdfbc4cfa420e84ef1549c81fe44547291311f86b', '[\"*\"]', '2026-02-04 14:06:26', NULL, '2026-02-04 06:37:00', '2026-02-04 14:06:26'),
(369, 'App\\Models\\User', 13, 'UserApp', '9294d3c37fe611de2298d223f4d20f4dc78436f1e12f4055d04dc5f46ac633be', '[\"*\"]', '2026-02-06 17:02:03', NULL, '2026-02-04 14:06:36', '2026-02-06 17:02:03'),
(370, 'App\\Models\\User', 110, 'UserApp', '003d4410011f7989180902b119e50bc1fc89badfe3f8676377afe9828e9293e5', '[\"*\"]', '2026-02-05 00:20:20', NULL, '2026-02-04 23:15:03', '2026-02-05 00:20:20'),
(371, 'App\\Models\\User', 109, 'UserApp', '92b30c6aca15edfeb811fa562323a22b984998ee77a638eb1942794449f2f0b4', '[\"*\"]', '2026-02-05 04:44:11', NULL, '2026-02-05 00:20:33', '2026-02-05 04:44:11'),
(372, 'App\\Models\\User', 1, 'UserApp', '497e50043c830fa2346f9c219831560dd6b7556b37192fb1b30756154e016bb3', '[\"*\"]', '2026-03-25 02:49:06', NULL, '2026-02-05 00:20:41', '2026-03-25 02:49:06'),
(373, 'App\\Models\\User', 66, 'UserApp', '4ed1f2658d38a60e87a279e14813a364911bdc1ade50670a069fd1e9049ecf0d', '[\"*\"]', '2026-02-05 00:24:01', NULL, '2026-02-05 00:22:40', '2026-02-05 00:24:01'),
(374, 'App\\Models\\User', 109, 'UserApp', 'c6e5be510b477866a13ed15ed1285d3b32192f055005c297f186d32a8ce675c0', '[\"*\"]', '2026-02-05 00:25:44', NULL, '2026-02-05 00:25:37', '2026-02-05 00:25:44'),
(375, 'App\\Models\\User', 110, 'UserApp', '536e99942e2e94f1ca47bf0b4f62fcd8f354b56fc3f826c069950fa6970f8413', '[\"*\"]', '2026-02-05 04:51:58', NULL, '2026-02-05 00:25:50', '2026-02-05 04:51:58'),
(376, 'App\\Models\\User', 109, 'UserApp', '662f82510171d0027274b041166094de9a0723ee5534df633442b6446339d2d4', '[\"*\"]', '2026-02-05 05:01:48', NULL, '2026-02-05 04:52:16', '2026-02-05 05:01:48'),
(377, 'App\\Models\\User', 60, 'UserApp', 'f485ca2e188fd50a70e616fd01cc5fcc1acf433fc9e939f1a7d40171e725bb9a', '[\"*\"]', '2026-02-05 04:59:16', NULL, '2026-02-05 04:57:52', '2026-02-05 04:59:16'),
(378, 'App\\Models\\User', 110, 'UserApp', 'e3bc427c612084a2a07df46bb46e45ed1a3bcf2f270057b98183d7e83f76529a', '[\"*\"]', '2026-02-05 05:00:53', NULL, '2026-02-05 05:00:33', '2026-02-05 05:00:53'),
(379, 'App\\Models\\User', 65, 'UserApp', '492694eef3f08e769f1deade9fb613237ef3a11df6ad123cd5bdaa580dfaafe9', '[\"*\"]', '2026-02-05 05:03:06', NULL, '2026-02-05 05:02:04', '2026-02-05 05:03:06'),
(380, 'App\\Models\\User', 110, 'UserApp', '83679bf361cad8d5ac56ef8f0e725b10bc58fc9d92ee0f8b01e135744c527a46', '[\"*\"]', '2026-02-05 05:05:22', NULL, '2026-02-05 05:03:22', '2026-02-05 05:05:22'),
(381, 'App\\Models\\User', 109, 'UserApp', '782def96995e944f762e07dd422c34c6877516a10fca55f68e6a6de3db6d32e7', '[\"*\"]', '2026-02-05 05:06:35', NULL, '2026-02-05 05:05:45', '2026-02-05 05:06:35'),
(382, 'App\\Models\\User', 110, 'UserApp', '14ef682192e34360b8ef0add2cb83d9c15d35cf1e0826ac8508e73e815444fe3', '[\"*\"]', '2026-02-05 05:46:51', NULL, '2026-02-05 05:06:52', '2026-02-05 05:46:51'),
(383, 'App\\Models\\User', 109, 'UserApp', '4564038233bd69a0335cceafa7c1f5ff8f1563b557f66547d831cec4fd666571', '[\"*\"]', '2026-02-08 23:20:34', NULL, '2026-02-05 05:46:06', '2026-02-08 23:20:34'),
(384, 'App\\Models\\User', 1, 'UserApp', '98a38ecf4f493237dbf5ee784601927fc96ff9db664f58ce22606caca77c8207', '[\"*\"]', '2026-02-12 05:04:17', NULL, '2026-02-06 17:02:33', '2026-02-12 05:04:17'),
(385, 'App\\Models\\User', 110, 'UserApp', 'cf26bb2d7be1694c61fd8c784a7418efecd9e5ee521b28fc4cd64e98518e3046', '[\"*\"]', '2026-02-08 23:06:03', NULL, '2026-02-08 22:33:27', '2026-02-08 23:06:03'),
(386, 'App\\Models\\User', 109, 'UserApp', '5b54ec962d1c33a3eb9694682f512c854a963cac46da8253e7c8dd47f499abc0', '[\"*\"]', '2026-02-09 06:16:51', NULL, '2026-02-09 02:57:40', '2026-02-09 06:16:51'),
(387, 'App\\Models\\User', 110, 'UserApp', '0dee72b260d7f65855858001356eba76d5fa83c14757375e718a49db5d3b742e', '[\"*\"]', '2026-02-09 23:26:25', NULL, '2026-02-09 06:17:53', '2026-02-09 23:26:25'),
(388, 'App\\Models\\User', 109, 'UserApp', 'e795963d7c6356ea8915c3389a2e3488f108c5485c4e96ac14a3a7e6071ce73f', '[\"*\"]', '2026-02-10 00:12:02', NULL, '2026-02-09 23:27:35', '2026-02-10 00:12:02'),
(389, 'App\\Models\\User', 110, 'UserApp', '15a3b6421974fc3c24685748b6f7e789510abfbf256261b0eb3da348691928b0', '[\"*\"]', '2026-02-09 23:40:47', NULL, '2026-02-09 23:29:08', '2026-02-09 23:40:47'),
(390, 'App\\Models\\User', 65, 'UserApp', '510d39e312b06322f33038a58ccf9e0e0ae9791cd5ff7cffdd36b335a0eb92d5', '[\"*\"]', '2026-02-09 23:42:05', NULL, '2026-02-09 23:41:47', '2026-02-09 23:42:05'),
(391, 'App\\Models\\User', 110, 'UserApp', '60951c451441647e2973ec89e15dd8abdca074ec414412e82b9aa120f8fb170f', '[\"*\"]', '2026-02-09 23:45:54', NULL, '2026-02-09 23:44:54', '2026-02-09 23:45:54'),
(392, 'App\\Models\\User', 65, 'UserApp', '2739477d1da946196ed2df2d6e325aab254d41ff67383a28a70e92a02324a21c', '[\"*\"]', '2026-02-09 23:48:12', NULL, '2026-02-09 23:47:02', '2026-02-09 23:48:12'),
(393, 'App\\Models\\User', 110, 'UserApp', '93ccf0193f8c4566307882fbc5b8e871e44a867df954fccf87e69ac2ef4e116d', '[\"*\"]', '2026-02-10 00:07:20', NULL, '2026-02-10 00:05:59', '2026-02-10 00:07:20'),
(394, 'App\\Models\\User', 65, 'UserApp', 'db9d2ad13c89f85de1816c41c801fa3187115fd9e23e0b3b1ad33a0a054560fd', '[\"*\"]', '2026-02-10 00:08:08', NULL, '2026-02-10 00:08:02', '2026-02-10 00:08:08'),
(395, 'App\\Models\\User', 110, 'UserApp', 'd10f5d5f31f1d5ffa34aee020df76e2a94cde7868ba7a3a295d52b349433f34b', '[\"*\"]', '2026-02-10 05:23:47', NULL, '2026-02-10 00:23:04', '2026-02-10 05:23:47'),
(396, 'App\\Models\\User', 109, 'UserApp', '5a5b1915d18998b2d1047c7d83085a586f2532118d3cf9f58b635dc87da78d9c', '[\"*\"]', '2026-02-10 05:25:12', NULL, '2026-02-10 05:23:59', '2026-02-10 05:25:12'),
(397, 'App\\Models\\User', 110, 'UserApp', 'eac166ac8bad45a13b39257ebdcde4ff4099b7b13092af46d0806e13104cd70f', '[\"*\"]', '2026-02-10 23:36:20', NULL, '2026-02-10 05:25:18', '2026-02-10 23:36:20'),
(398, 'App\\Models\\User', 1, 'UserApp', 'ec3a716490f4350d52facba6762d4d946db3ec7a335d12b9f8c95dae184e821c', '[\"*\"]', '2026-02-10 09:30:03', NULL, '2026-02-10 09:29:42', '2026-02-10 09:30:03'),
(399, 'App\\Models\\User', 13, 'UserApp', 'e59479a1c0aa35d58ebdcdc38d2e71a2ac123e37ef643869da5dffbb09d65114', '[\"*\"]', '2026-02-10 09:30:56', NULL, '2026-02-10 09:30:35', '2026-02-10 09:30:56'),
(400, 'App\\Models\\User', 13, 'UserApp', '50eef735abbc6e7426bc18208b35fe8d75cc9aa114caeb7829925da7936c452a', '[\"*\"]', '2026-02-24 02:14:14', NULL, '2026-02-10 11:30:43', '2026-02-24 02:14:14'),
(401, 'App\\Models\\User', 1, 'UserApp', '9cf425325bbfdb4bb279aa0051daa57f5b4f5095dedfbb86af5acfa3f0d6c306', '[\"*\"]', '2026-02-10 12:20:56', NULL, '2026-02-10 12:11:49', '2026-02-10 12:20:56'),
(402, 'App\\Models\\User', 57, 'UserApp', '6f52ef1c839c5181a050b793ad9b02b10b6ed1a52dbccea8fc254b617e3eed06', '[\"*\"]', '2026-02-10 12:23:04', NULL, '2026-02-10 12:22:04', '2026-02-10 12:23:04'),
(403, 'App\\Models\\User', 13, 'UserApp', '6b9cb38000721a471f0de80098a52fd126f6b76b6601b1f1b02af54a0a880065', '[\"*\"]', '2026-02-10 12:25:07', NULL, '2026-02-10 12:24:06', '2026-02-10 12:25:07'),
(404, 'App\\Models\\User', 109, 'UserApp', 'affcfa5caae5dd265a22d31a7ae9d6e4459364e86a0d57d52c4181b6be6fa666', '[\"*\"]', '2026-02-10 23:37:06', NULL, '2026-02-10 23:36:37', '2026-02-10 23:37:06'),
(405, 'App\\Models\\User', 110, 'UserApp', 'd27b3e4aa8b3837009caed72828672b409ea5be4532dad6a50707957256deeb2', '[\"*\"]', '2026-02-11 02:40:39', NULL, '2026-02-10 23:37:33', '2026-02-11 02:40:39'),
(406, 'App\\Models\\User', 109, 'UserApp', '3858eae08fac4b9a4719f574ed88d2a6df516c88df928a7e1f6362c4a6568fc4', '[\"*\"]', '2026-02-11 02:42:11', NULL, '2026-02-11 02:20:10', '2026-02-11 02:42:11'),
(407, 'App\\Models\\User', 65, 'UserApp', 'd30eb5807c3dc784a36ac8a15d540c3978b34dfcf22993f7cf1582276ef8728b', '[\"*\"]', '2026-02-11 02:35:55', NULL, '2026-02-11 02:25:57', '2026-02-11 02:35:55'),
(408, 'App\\Models\\User', 109, 'UserApp', '6e86d0a087a947c9a4cd66d5c278d07f7a010e598770adde88497375a8509118', '[\"*\"]', '2026-02-12 06:57:10', NULL, '2026-02-11 23:16:41', '2026-02-12 06:57:10'),
(409, 'App\\Models\\User', 110, 'UserApp', 'a3d3a1494e737c00425ab84a139c656beb9b3c770b06094aec52aee9a28b4ebf', '[\"*\"]', '2026-02-12 06:56:58', NULL, '2026-02-12 00:17:50', '2026-02-12 06:56:58'),
(410, 'App\\Models\\User', 65, 'UserApp', '819f612a7aecc9ded7f4046a6db72292629fbfbef26beff1ad2a9b6c851790f2', '[\"*\"]', '2026-02-12 06:57:10', NULL, '2026-02-12 00:23:16', '2026-02-12 06:57:10'),
(411, 'App\\Models\\User', 13, 'UserApp', '90bae1dacd05a0e7e210d4069b44576efe9e37178b78bdf606d71a9ee480a4e2', '[\"*\"]', '2026-02-23 06:56:04', NULL, '2026-02-12 05:04:23', '2026-02-23 06:56:04'),
(412, 'App\\Models\\User', 1, 'UserApp', '4b959c96f2a1c9a5277231c966dcabd249b3fb3188a7bc3cc254655b57ce48e5', '[\"*\"]', '2026-02-12 08:35:34', NULL, '2026-02-12 05:10:45', '2026-02-12 08:35:34'),
(413, 'App\\Models\\User', 65, 'UserApp', '4c2cacf92648d64eef0732b4fa962d20a2f77fbf96ce70b90327eb8d2a569a44', '[\"*\"]', NULL, NULL, '2026-02-12 07:01:21', '2026-02-12 07:01:21'),
(414, 'App\\Models\\User', 110, 'UserApp', '6499bdfccf0009b8c4ee4d836b695c7f8a58bbdc4a040bf38189faa3afe9c42a', '[\"*\"]', '2026-02-16 01:12:13', NULL, '2026-02-12 07:01:24', '2026-02-16 01:12:13'),
(415, 'App\\Models\\User', 1, 'UserApp', '97dbd919c10b4b7f07bbb087349b490aa5a2edd44a641b3ef0b73a00f88ec768', '[\"*\"]', '2026-03-17 03:23:57', NULL, '2026-02-13 17:32:52', '2026-03-17 03:23:57'),
(416, 'App\\Models\\User', 13, 'UserApp', '3646b8f6960898400457f2c13941754fa99a6655d23d586fd2bb4a02136a21d9', '[\"*\"]', '2026-03-01 21:56:45', NULL, '2026-02-13 19:14:24', '2026-03-01 21:56:45'),
(417, 'App\\Models\\User', 13, 'UserApp', 'b120f948bb6b557002da342cd7573319d6b5b9fd9c9d67a0631ca1a360fde5cd', '[\"*\"]', '2026-02-13 20:07:01', NULL, '2026-02-13 19:34:56', '2026-02-13 20:07:01'),
(418, 'App\\Models\\User', 109, 'UserApp', '2f4f1581949d4bbad93d12f8a01a5835a735784f6ce16781ce10c03e7924df18', '[\"*\"]', '2026-02-16 01:15:36', NULL, '2026-02-16 01:14:11', '2026-02-16 01:15:36'),
(419, 'App\\Models\\User', 13, 'UserApp', 'bc8791ee9b2edc4f4f24f712dbc28f0aee73e42a202a3df206054f652069a84b', '[\"*\"]', '2026-02-17 22:21:54', NULL, '2026-02-16 01:15:47', '2026-02-17 22:21:54'),
(420, 'App\\Models\\User', 13, 'UserApp', 'eb31884f8f8ed62712e93316a54c76069fc4e56c9d582a50ac1bc7116f193973', '[\"*\"]', '2026-02-17 22:44:38', NULL, '2026-02-17 21:29:37', '2026-02-17 22:44:38'),
(421, 'App\\Models\\User', 110, 'UserApp', '041aa4006883d64883000c0306fc324886e6a9709718c2921526ff78cc8e161d', '[\"*\"]', '2026-02-17 22:47:04', NULL, '2026-02-17 22:22:11', '2026-02-17 22:47:04'),
(422, 'App\\Models\\User', 1, 'UserApp', '800c8efa6fb7cb4647b80cb83c0d42ccbcd838b6d51aed2bd73e2b32d93ecdb3', '[\"*\"]', '2026-02-17 22:53:38', NULL, '2026-02-17 22:44:48', '2026-02-17 22:53:38'),
(423, 'App\\Models\\User', 109, 'UserApp', 'f6dc01a5984080098c8cced05d2ec96053ee6986080a74d19516cc08230a10ad', '[\"*\"]', '2026-02-17 22:53:26', NULL, '2026-02-17 22:47:10', '2026-02-17 22:53:26'),
(424, 'App\\Models\\User', 65, 'UserApp', '4f0c78000383af5eeba1849fed60d92146ab77da22a74d460d11193a3ff08ecd', '[\"*\"]', '2026-02-17 22:52:55', NULL, '2026-02-17 22:50:42', '2026-02-17 22:52:55'),
(425, 'App\\Models\\User', 13, 'UserApp', '4579b8cdc5a9356e76a9f7245391ce112b029d4a0a83e01f72b9972ac8948dc2', '[\"*\"]', '2026-02-17 22:59:22', NULL, '2026-02-17 22:54:42', '2026-02-17 22:59:22'),
(426, 'App\\Models\\User', 1, 'UserApp', '01d47558c606be1946b23e2767fe90a26e5ca0c2956b3493dee0a6d197fc47d6', '[\"*\"]', '2026-02-26 23:45:15', NULL, '2026-02-17 22:59:36', '2026-02-26 23:45:15'),
(427, 'App\\Models\\User', 109, 'UserApp', 'd5b56ec59e1d5abccb49f731ebc96ef04485ca555972eeb2f72af2270ceaa84e', '[\"*\"]', '2026-02-18 05:17:29', NULL, '2026-02-18 05:03:17', '2026-02-18 05:17:29'),
(428, 'App\\Models\\User', 110, 'UserApp', '3e9f41a71b2def8b10def7876849a4214d7fe13f11a77c039eb136f04db7ec43', '[\"*\"]', '2026-02-18 05:05:07', NULL, '2026-02-18 05:05:02', '2026-02-18 05:05:07'),
(429, 'App\\Models\\User', 65, 'UserApp', 'f8355dff9d4a20b64bc33f9d3baf8d6a0aa234e2805041747da408f51a67777f', '[\"*\"]', '2026-02-18 05:19:04', NULL, '2026-02-18 05:18:52', '2026-02-18 05:19:04'),
(430, 'App\\Models\\User', 110, 'UserApp', '49567270e7b83cacae5f1124f92a452b2f9335dd687102d2236c2fc14aebe211', '[\"*\"]', '2026-02-19 01:18:31', NULL, '2026-02-19 01:18:16', '2026-02-19 01:18:31'),
(431, 'App\\Models\\User', 109, 'UserApp', '72c085de4fbb4670e4183bb50b1b1a3baf27e8bb49840cbed24c89f309b83e0a', '[\"*\"]', '2026-02-20 00:20:10', NULL, '2026-02-19 01:18:26', '2026-02-20 00:20:10'),
(432, 'App\\Models\\User', 13, 'UserApp', '7e83e86d1f7160581be882e13db3ff980de3e0721429a365d0e7425a5ec2662f', '[\"*\"]', '2026-02-28 11:31:37', NULL, '2026-02-19 17:47:44', '2026-02-28 11:31:37'),
(433, 'App\\Models\\User', 109, 'UserApp', '37596f31c5a794daf6d8d6b2b05bbfa503b2fc9ccad35aeb9e6205d59d182c34', '[\"*\"]', '2026-02-20 00:21:37', NULL, '2026-02-20 00:20:20', '2026-02-20 00:21:37'),
(434, 'App\\Models\\User', 110, 'UserApp', '8119f497dea0c1703749ce10c211cc684f339d5fe37e2e8267f333da30c99126', '[\"*\"]', '2026-02-22 23:41:20', NULL, '2026-02-20 00:20:39', '2026-02-22 23:41:20'),
(435, 'App\\Models\\User', 109, 'UserApp', 'a4ec129568f79098d1646d06be36a28a1460dc995ff506be7ec48aeea40c3306', '[\"*\"]', '2026-02-22 23:57:40', NULL, '2026-02-22 23:41:25', '2026-02-22 23:57:40'),
(436, 'App\\Models\\User', 65, 'UserApp', 'ef5da678cb74480bb81c98baa5d7947b343b311a06d64b3266d16184a2dee51c', '[\"*\"]', '2026-02-23 00:54:20', NULL, '2026-02-22 23:57:47', '2026-02-23 00:54:20'),
(437, 'App\\Models\\User', 1, 'UserApp', 'f86e30700b15286362c05134bc70ec7802177d25ebc1fcebdb42366b30c3ad2b', '[\"*\"]', '2026-03-06 20:40:31', NULL, '2026-02-23 06:56:10', '2026-03-06 20:40:31'),
(438, 'App\\Models\\User', 110, 'UserApp', '2c78f2ae210c1132425f8e70a4832c3cf4cdb62a4850ff921ccb67436d62e053', '[\"*\"]', '2026-02-24 02:13:33', NULL, '2026-02-24 02:12:52', '2026-02-24 02:13:33'),
(439, 'App\\Models\\User', 1, 'UserApp', '18388aabe6415e25c190b7da2513cc7d7a5222fec59ac36761eee067a531961c', '[\"*\"]', '2026-02-24 02:18:55', NULL, '2026-02-24 02:14:59', '2026-02-24 02:18:55'),
(440, 'App\\Models\\User', 13, 'UserApp', 'f948610bab81ef1035d62c33fd9186b06f8c3d99142747dbdd03e7e8370f16d3', '[\"*\"]', NULL, NULL, '2026-02-24 02:18:56', '2026-02-24 02:18:56'),
(441, 'App\\Models\\User', 13, 'UserApp', '9c02a941bbf3871909ddb8c13135d448d283da796d2b4bdbf5712215363199d9', '[\"*\"]', '2026-02-26 04:51:02', NULL, '2026-02-24 02:19:01', '2026-02-26 04:51:02'),
(442, 'App\\Models\\User', 1, 'UserApp', '2e7face061a4dcb64d93aa6c440c89efc66c2cd3611e15e6302d6e654b20ef27', '[\"*\"]', '2026-02-26 05:13:17', NULL, '2026-02-26 03:41:16', '2026-02-26 05:13:17'),
(443, 'App\\Models\\User', 13, 'UserApp', '1cdeb533c3c322cbdfa0033f01c3420a24ea07d3d1b913718264832ca4925a2d', '[\"*\"]', '2026-02-28 11:20:46', NULL, '2026-02-26 05:06:47', '2026-02-28 11:20:46'),
(444, 'App\\Models\\User', 109, 'UserApp', '6a6831676b10d63100e0a94cdb2cc1b7d7270a4b0bbecdb882b6181df63cb880', '[\"*\"]', '2026-02-26 06:28:27', NULL, '2026-02-26 05:57:28', '2026-02-26 06:28:27'),
(445, 'App\\Models\\User', 110, 'UserApp', 'e92331592799456197ee09625aabaa2f5cf48c2dfbf2bd745dd0010b3c89f730', '[\"*\"]', '2026-02-26 06:28:15', NULL, '2026-02-26 06:00:47', '2026-02-26 06:28:15'),
(446, 'App\\Models\\User', 13, 'UserApp', 'd01823d58baeeedd83f979c69edd44890c5a8d83831a5ef7429eff73e75de08e', '[\"*\"]', '2026-02-26 06:01:17', NULL, '2026-02-26 06:01:17', '2026-02-26 06:01:17'),
(447, 'App\\Models\\User', 13, 'UserApp', 'a637f885fa35d2c7d6555039052d9641c35457237beff8cc75be17cecdab932b', '[\"*\"]', '2026-02-26 06:03:22', NULL, '2026-02-26 06:03:21', '2026-02-26 06:03:22'),
(448, 'App\\Models\\User', 13, 'UserApp', '55666f91782ca6876ddf3297b56ea604c84feb9bd1f7538408ba14cb8e2b020e', '[\"*\"]', '2026-04-12 23:22:12', NULL, '2026-02-26 06:25:09', '2026-04-12 23:22:12'),
(449, 'App\\Models\\User', 13, 'UserApp', '911c81431ae7f7176ed1e32d1cbe3db491f0fd9f3dd5f66d328e4ac5e9a48ab7', '[\"*\"]', '2026-02-26 23:48:33', NULL, '2026-02-26 23:45:36', '2026-02-26 23:48:33'),
(450, 'App\\Models\\User', 1, 'UserApp', '6478a6fcbdbde915a1c027a60e88e64470085bb06e2c14322eefab1031ac3bb1', '[\"*\"]', '2026-03-07 11:36:33', NULL, '2026-02-26 23:49:04', '2026-03-07 11:36:33'),
(451, 'App\\Models\\User', 1, 'UserApp', 'efe1f84b89b91e437f40818a8e3899f0986516ed0d4c4015e287c8410ced4e72', '[\"*\"]', '2026-02-28 12:41:27', NULL, '2026-02-28 12:40:12', '2026-02-28 12:41:27'),
(452, 'App\\Models\\User', 13, 'UserApp', 'ebacc6f76ed96486ba736061ecbb705b05a6500f85e0db92095494aadba5dc05', '[\"*\"]', '2026-02-28 12:43:36', NULL, '2026-02-28 12:43:27', '2026-02-28 12:43:36'),
(453, 'App\\Models\\User', 1, 'UserApp', '086e6c84313e0e0b18a6ae4e566dc6969d1f8bad0fc0f925a1464e10f58cd6dd', '[\"*\"]', '2026-03-06 02:58:59', NULL, '2026-02-28 12:59:15', '2026-03-06 02:58:59'),
(454, 'App\\Models\\User', 1, 'UserApp', 'c4ad7f37529a72bbe72480142c4fb47dfc97d8645e0f1f718c3879f040da70f1', '[\"*\"]', '2026-03-01 22:00:44', NULL, '2026-03-01 21:57:10', '2026-03-01 22:00:44'),
(455, 'App\\Models\\User', 1, 'UserApp', '6e46c18fd42da2e74816fe6efdab0235de1ec0d23ae371561c6f47476400e571', '[\"*\"]', '2026-03-17 06:53:02', NULL, '2026-03-01 22:01:48', '2026-03-17 06:53:02'),
(456, 'App\\Models\\User', 1, 'UserApp', '422c4c712165304e6d4962c00ca44386804e6475113b45c31f902acfa56f82d4', '[\"*\"]', '2026-03-06 03:01:10', NULL, '2026-03-06 03:01:09', '2026-03-06 03:01:10'),
(457, 'App\\Models\\User', 13, 'UserApp', '9e7e6590c9ffb559568bcc94b91cfb320a81afec1293ffca0cc43e58a90ad091', '[\"*\"]', '2026-03-16 03:34:02', NULL, '2026-03-06 03:01:34', '2026-03-16 03:34:02'),
(458, 'App\\Models\\User', 13, 'UserApp', '117f480bd153efea76f2600ff5aaa98c31a666f30ca7269d9a630c2435ddb86d', '[\"*\"]', '2026-03-06 20:20:21', NULL, '2026-03-06 20:09:54', '2026-03-06 20:20:21'),
(459, 'App\\Models\\User', 1, 'UserApp', 'c93a08ea2e0fa9430ce4bf0429ebe3b5a44b6689b51e4c1529c3fa7bddbc4521', '[\"*\"]', '2026-03-16 03:36:24', NULL, '2026-03-06 20:40:30', '2026-03-16 03:36:24'),
(460, 'App\\Models\\User', 13, 'UserApp', 'b85ecafe4b3f375cb7cb1eee240735ef357a1309a81ff81bb1448770039ace46', '[\"*\"]', '2026-03-07 10:05:02', NULL, '2026-03-07 07:57:11', '2026-03-07 10:05:02'),
(461, 'App\\Models\\User', 1, 'UserApp', '6a1e087f595aab2234dbb52a5c8ffc7b1d6624d1b938c0334194edd807e6f124', '[\"*\"]', '2026-03-07 10:09:40', NULL, '2026-03-07 10:05:46', '2026-03-07 10:09:40'),
(462, 'App\\Models\\User', 1, 'UserApp', 'e20f2fd48c8379ed911798fc2ab59e23145d1f77350086276668d36d3f925262', '[\"*\"]', '2026-03-07 10:11:30', NULL, '2026-03-07 10:10:38', '2026-03-07 10:11:30'),
(463, 'App\\Models\\User', 1, 'UserApp', '07fe4aaa46fc5e8191e01d13500dfcc0e5bdcfa7167b8f42631c07ff5370f01a', '[\"*\"]', '2026-03-07 10:11:38', NULL, '2026-03-07 10:11:37', '2026-03-07 10:11:38'),
(464, 'App\\Models\\User', 13, 'UserApp', 'ead8cb7b3a85917092cfbb5906f4701f5c19e633db2abd000496dfb455abf363', '[\"*\"]', '2026-03-07 10:19:38', NULL, '2026-03-07 10:11:59', '2026-03-07 10:19:38'),
(465, 'App\\Models\\User', 1, 'UserApp', '769a8791687ab4d05834a250bc6884507b7dc07810b53acd1bb085c44a849e90', '[\"*\"]', '2026-03-07 11:42:54', NULL, '2026-03-07 11:42:42', '2026-03-07 11:42:54'),
(466, 'App\\Models\\User', 13, 'UserApp', 'adaef250de934848c765101ffb50e186f22633ade38325ce020f60a878a82890', '[\"*\"]', '2026-03-07 11:48:00', NULL, '2026-03-07 11:43:31', '2026-03-07 11:48:00'),
(467, 'App\\Models\\User', 1, 'UserApp', 'fa361f528697bb572da5668d5b50d5a35b95860e994e531a885d338b55aa4b0f', '[\"*\"]', '2026-03-08 17:37:16', NULL, '2026-03-07 11:49:07', '2026-03-08 17:37:16'),
(468, 'App\\Models\\User', 57, 'UserApp', '08d2f1fcfd2941c62f9350f235ffc57794b2bac4d9b31cdd544d74ad7adfd89f', '[\"*\"]', '2026-03-07 11:49:45', NULL, '2026-03-07 11:49:41', '2026-03-07 11:49:45'),
(469, 'App\\Models\\User', 84, 'UserApp', 'a24dd02a9534da7e9e36e574e18804c2598c924364ee2579ce695a88e76dab50', '[\"*\"]', '2026-03-08 17:37:16', NULL, '2026-03-07 11:50:43', '2026-03-08 17:37:16'),
(470, 'App\\Models\\User', 1, 'UserApp', 'deac563b3cdc3203706c7abc50835283b77ddd023d7e1f79902e52404b1572dd', '[\"*\"]', '2026-03-16 03:36:10', NULL, '2026-03-16 03:34:12', '2026-03-16 03:36:10'),
(471, 'App\\Models\\User', 13, 'UserApp', '353b411f51e169965edc7633130a654e434944bc88899f42b3c31d8d2577e142', '[\"*\"]', '2026-03-16 03:36:30', NULL, '2026-03-16 03:36:30', '2026-03-16 03:36:30'),
(472, 'App\\Models\\User', 1, 'UserApp', '1155d49838793e1cbf83c58087404c926129cc970f636a1110a6c5b56000a9aa', '[\"*\"]', '2026-03-18 04:56:01', NULL, '2026-03-16 03:37:01', '2026-03-18 04:56:01'),
(473, 'App\\Models\\User', 1, 'UserApp', '0c02475f818f39ed77ecdd70b4f37e29049bf8f44723d66efda7b39dcc3d2eb6', '[\"*\"]', '2026-03-19 01:41:54', NULL, '2026-03-17 02:15:57', '2026-03-19 01:41:54'),
(474, 'App\\Models\\User', 13, 'UserApp', 'fe9615f6ed4200840c60b9dccba8f84d4a7492e5b6abbf8c191931a6c54f3dc8', '[\"*\"]', '2026-03-19 03:22:20', NULL, '2026-03-17 02:17:04', '2026-03-19 03:22:20'),
(475, 'App\\Models\\User', 81, 'UserApp', 'ede13c7f83ce4905001e663fe58042cf37397337fa83ec2b0382d05c610efc86', '[\"*\"]', '2026-03-17 03:02:46', NULL, '2026-03-17 03:02:39', '2026-03-17 03:02:46'),
(476, 'App\\Models\\User', 13, 'UserApp', '34c6cf7cb18cc40ca0586807f750c2801915edf9835ee39fd9fc75b2223240f0', '[\"*\"]', '2026-03-18 02:23:10', NULL, '2026-03-18 02:23:10', '2026-03-18 02:23:10'),
(477, 'App\\Models\\User', 1, 'UserApp', 'e088ce2659ae569f7c373a5348d2f206022d22c52936f71f50a02b146da6cedf', '[\"*\"]', '2026-03-25 09:56:17', NULL, '2026-03-18 03:06:21', '2026-03-25 09:56:17'),
(478, 'App\\Models\\User', 13, 'UserApp', 'f17cfd9ee31a4dc4a2fbcc665addb23d6c5ca7604f7c90945089bc88bee41616', '[\"*\"]', '2026-04-05 22:49:51', NULL, '2026-03-18 04:56:10', '2026-04-05 22:49:51'),
(479, 'App\\Models\\User', 1, 'UserApp', '949017e7d7d947f8cb2c543d40522f099815ff714eba91a37d0134521882f4e7', '[\"*\"]', '2026-03-24 04:24:48', NULL, '2026-03-18 06:50:38', '2026-03-24 04:24:48'),
(480, 'App\\Models\\User', 13, 'UserApp', 'de0c2cbc1eccb1c372690569cd574e27967039cd941cb73f5512ed256d9d694d', '[\"*\"]', '2026-03-19 03:12:05', NULL, '2026-03-19 03:11:48', '2026-03-19 03:12:05'),
(481, 'App\\Models\\User', 1, 'UserApp', 'eff99821602a1b63aed883a6f1577c399ad775c8e77bc19d7b1952e57db585e0', '[\"*\"]', '2026-03-23 00:45:46', NULL, '2026-03-23 00:45:17', '2026-03-23 00:45:46'),
(482, 'App\\Models\\User', 13, 'UserApp', 'f1fa15f9ea98a3702a7039afa7c9ffa23d876d5738b6e91387a7a5da157eca65', '[\"*\"]', '2026-04-06 23:36:15', NULL, '2026-03-23 00:45:38', '2026-04-06 23:36:15'),
(483, 'App\\Models\\User', 1, 'UserApp', 'a822359520c8eca47a373038ba638e4a004296d1eef21b245c2c85668657918e', '[\"*\"]', '2026-03-24 08:03:15', NULL, '2026-03-24 03:30:58', '2026-03-24 08:03:15'),
(484, 'App\\Models\\User', 1, 'UserApp', 'a4d2ff254efb251043cb293f91cb73a00995f46aadacd29dd97a31cbb56f698f', '[\"*\"]', '2026-03-31 07:34:38', NULL, '2026-03-24 04:17:23', '2026-03-31 07:34:38'),
(485, 'App\\Models\\User', 1, 'UserApp', '27f679eb65a217bff95c89f953c406689a24884c82ddeca9cc1889cf41cfe03f', '[\"*\"]', '2026-03-25 03:30:32', NULL, '2026-03-24 04:18:28', '2026-03-25 03:30:32'),
(486, 'App\\Models\\User', 13, 'UserApp', 'decf431f026d3acf6a57e879ab6922deecc39a32bf9001ed56cb07eb06ed6ccf', '[\"*\"]', '2026-03-24 10:00:04', NULL, '2026-03-24 04:19:24', '2026-03-24 10:00:04'),
(487, 'App\\Models\\User', 57, 'UserApp', '778cb674e33e16e806aa38318cd4c30fbc3b3fbb2dcbae6062361e99f8f98379', '[\"*\"]', '2026-03-24 04:27:43', NULL, '2026-03-24 04:27:13', '2026-03-24 04:27:43'),
(488, 'App\\Models\\User', 13, 'UserApp', '2bfb2a79077a7e496e2a8f8892e3816afa2fed6468b659bde4eeccbca8486ebc', '[\"*\"]', '2026-03-24 05:12:42', NULL, '2026-03-24 05:11:47', '2026-03-24 05:12:42'),
(489, 'App\\Models\\User', 13, 'UserApp', 'ff510956326f0370c719c62b96453b549ad937f0b813a238f4e911ac269a9622', '[\"*\"]', '2026-03-31 07:41:02', NULL, '2026-03-25 03:07:11', '2026-03-31 07:41:02'),
(490, 'App\\Models\\User', 1, 'UserApp', '701baa3ee99c49eac8f65e64dae14fba12e79ea249f7bf742e4f67dd34a7fa5f', '[\"*\"]', '2026-03-25 05:58:58', NULL, '2026-03-25 03:07:23', '2026-03-25 05:58:58'),
(491, 'App\\Models\\User', 13, 'UserApp', '7cbbd8dc18cf3296d42c198244ebf37bb4b490b0a2cc0283db41b4ec1cf66e2a', '[\"*\"]', '2026-03-25 03:47:43', NULL, '2026-03-25 03:31:46', '2026-03-25 03:47:43'),
(492, 'App\\Models\\User', 13, 'UserApp', 'b152c8510b1079c92c769837e02c396d6c48adb0ac92576b820ee7680cdd510a', '[\"*\"]', '2026-04-06 04:23:20', NULL, '2026-03-25 09:56:25', '2026-04-06 04:23:20'),
(493, 'App\\Models\\User', 1, 'UserApp', '524d54e3e32559122ad9b2dfd2a855aaea514bb465bce6a2add48b29e3e94444', '[\"*\"]', '2026-03-26 08:51:26', NULL, '2026-03-26 04:50:30', '2026-03-26 08:51:26'),
(494, 'App\\Models\\User', 1, 'UserApp', '44c20b50fc0079211114f1bdd6492d36baf8bcbb5eeff563d1116ec1e75eca27', '[\"*\"]', '2026-03-26 05:41:19', NULL, '2026-03-26 05:26:28', '2026-03-26 05:41:19'),
(495, 'App\\Models\\User', 13, 'UserApp', '21f50c56ca948cb66a7ae0eb7c872a21e2156540a7d6ecd2c02fc9e9beb3622f', '[\"*\"]', '2026-03-26 05:56:39', NULL, '2026-03-26 05:55:04', '2026-03-26 05:56:39'),
(496, 'App\\Models\\User', 13, 'UserApp', 'c64819c0863e44fa2f6374ca67dd6335c8e37563c7fcc0c923fdc829342b5135', '[\"*\"]', '2026-03-26 09:25:36', NULL, '2026-03-26 07:53:39', '2026-03-26 09:25:36'),
(497, 'App\\Models\\User', 13, 'UserApp', 'e74551cc6ec774ac7cee1ca44803375804e2a61bd565e659f7bae2c759a6d512', '[\"*\"]', '2026-03-26 09:41:36', NULL, '2026-03-26 09:30:28', '2026-03-26 09:41:36'),
(498, 'App\\Models\\User', 1, 'UserApp', 'c34ab2851594d87bbbf9ecc29f4cc40535375d45b5150763278c7917f23c02e2', '[\"*\"]', '2026-03-26 09:41:59', NULL, '2026-03-26 09:41:59', '2026-03-26 09:41:59'),
(499, 'App\\Models\\User', 1, 'UserApp', '11169c8c403d803b111f8276cf3de109f9b9493316708d7e063354cd441af98d', '[\"*\"]', '2026-03-26 09:42:25', NULL, '2026-03-26 09:42:25', '2026-03-26 09:42:25'),
(500, 'App\\Models\\User', 13, 'UserApp', '0dc6ebef12ad1d17c5ba20b39366e5ceae8f02118875b22211614c1a3f676dde', '[\"*\"]', '2026-03-26 09:43:29', NULL, '2026-03-26 09:43:28', '2026-03-26 09:43:29'),
(501, 'App\\Models\\User', 1, 'UserApp', '50992f7a8e4054fb96963d0ccc85cc3d3452a89b9cfd9e2154674f4ea80fa038', '[\"*\"]', '2026-03-26 09:45:03', NULL, '2026-03-26 09:45:03', '2026-03-26 09:45:03'),
(502, 'App\\Models\\User', 13, 'UserApp', 'b062ec866692d176f39b9bb515fb780bfd6164fb299ecef24b1f112c628267c5', '[\"*\"]', '2026-03-26 09:46:19', NULL, '2026-03-26 09:45:57', '2026-03-26 09:46:19'),
(503, 'App\\Models\\User', 13, 'UserApp', '5fee295ae9b77eb1276e870b3b0e16260e977f81f841dfdb31b22304637f92ce', '[\"*\"]', '2026-03-26 09:49:56', NULL, '2026-03-26 09:46:32', '2026-03-26 09:49:56'),
(504, 'App\\Models\\User', 13, 'UserApp', 'c34cb445f61d9ad2167385c9cf3793dd4d791c00d2552f7dcba7d16c29d3baad', '[\"*\"]', '2026-03-27 11:38:25', NULL, '2026-03-27 08:36:11', '2026-03-27 11:38:25'),
(505, 'App\\Models\\User', 1, 'UserApp', 'd70a47872c1f572e89cdcfc24758db6ed481c1c9858b98267bdc697e15525918', '[\"*\"]', '2026-03-30 00:44:27', NULL, '2026-03-30 00:01:20', '2026-03-30 00:44:27'),
(506, 'App\\Models\\User', 13, 'UserApp', '2dbcbd4f5d2e2b65c0001f3fd563ae3e3c68b2851add387fe5f184fcd6bb006e', '[\"*\"]', '2026-03-30 00:47:58', NULL, '2026-03-30 00:01:48', '2026-03-30 00:47:58'),
(507, 'App\\Models\\User', 1, 'UserApp', 'f4dc57df85eda3d4a6d0bed6d26b9387d3923d88d0818b76e79a6ced37dcd8f9', '[\"*\"]', '2026-04-13 05:14:54', NULL, '2026-03-31 00:53:15', '2026-04-13 05:14:54'),
(508, 'App\\Models\\User', 13, 'UserApp', '10d5fe33c1c2ddc168c3483c7bc7dd5987d997e4ff526f3545e14f8d139dedae', '[\"*\"]', '2026-04-02 08:03:10', NULL, '2026-03-31 05:17:36', '2026-04-02 08:03:10'),
(509, 'App\\Models\\User', 1, 'UserApp', '58db29f86b4c3e8285c7e7976107e8193ec89db5afd3bfd1df8e11afa78fc7a2', '[\"*\"]', '2026-04-17 03:29:07', NULL, '2026-03-31 05:24:59', '2026-04-17 03:29:07'),
(510, 'App\\Models\\User', 13, 'UserApp', '18341045486b5e8feccee950781e57b76b392af9866e9761e13aa9c54b5a8f74', '[\"*\"]', '2026-04-02 08:29:31', NULL, '2026-03-31 07:34:48', '2026-04-02 08:29:31'),
(511, 'App\\Models\\User', 1, 'UserApp', 'b7774f6c58ccc401829603039c29e91468ede5692610d624139cb6de928d4a31', '[\"*\"]', '2026-03-31 08:23:34', NULL, '2026-03-31 07:38:41', '2026-03-31 08:23:34'),
(512, 'App\\Models\\User', 1, 'UserApp', 'ebca2565be902d2e76b6938be91d38095f4151bf472ce6275211902401fe0d38', '[\"*\"]', '2026-03-31 07:43:26', NULL, '2026-03-31 07:41:08', '2026-03-31 07:43:26'),
(513, 'App\\Models\\User', 1, 'UserApp', 'cd2c02801acd92e1e130cf116e7aa3981add1da1880f0e1ef9d84a04a53bee27', '[\"*\"]', '2026-03-31 08:21:48', NULL, '2026-03-31 08:21:40', '2026-03-31 08:21:48'),
(514, 'App\\Models\\User', 13, 'UserApp', 'ee2301ac274cc59728b75d19093d760a15334e8d0be64a0ef431ad2b60fd4ea4', '[\"*\"]', '2026-04-16 09:01:19', NULL, '2026-03-31 08:21:57', '2026-04-16 09:01:19'),
(515, 'App\\Models\\User', 1, 'UserApp', 'f2f71f4946bf38e79186fe5f44272229f34e566d06796e68660125be633ded79', '[\"*\"]', '2026-04-01 04:34:13', NULL, '2026-04-01 04:05:33', '2026-04-01 04:34:13'),
(516, 'App\\Models\\User', 1, 'UserApp', '8ae65ac98ef1eac2f0bb15e92937a7ea2ae8a7d8271614dd8a3735d300bb6f9e', '[\"*\"]', '2026-04-01 07:51:01', NULL, '2026-04-01 07:48:45', '2026-04-01 07:51:01'),
(517, 'App\\Models\\User', 13, 'UserApp', '7b047a458f083b797ba11100cd89399bd2c592065effcfe68d98f7c7a63963a4', '[\"*\"]', '2026-04-02 08:00:37', NULL, '2026-04-02 07:56:13', '2026-04-02 08:00:37'),
(518, 'App\\Models\\User', 13, 'UserApp', '3ae411783c5994f76c6b2695a2074a451333de667745f105ec759b3d6b20787b', '[\"*\"]', '2026-04-03 04:52:34', NULL, '2026-04-03 04:51:50', '2026-04-03 04:52:34'),
(519, 'App\\Models\\User', 13, 'UserApp', '78388a946922475856558303bacfcb7e93de95fc6159220a63809d94a8b51049', '[\"*\"]', '2026-04-03 05:00:35', NULL, '2026-04-03 05:00:23', '2026-04-03 05:00:35'),
(520, 'App\\Models\\User', 13, 'UserApp', 'a5dd8731a571cc2aa98a5dd23143e95518c408dc01d8fb4761ed0ddffa39c1f7', '[\"*\"]', '2026-04-15 08:02:45', NULL, '2026-04-05 23:11:06', '2026-04-15 08:02:45'),
(521, 'App\\Models\\User', 1, 'UserApp', '4ca5c3bcc6fd255fe963d83c561b6543c39d6196271d979b59c88dfbadcfe5fa', '[\"*\"]', '2026-04-14 10:37:28', NULL, '2026-04-06 04:23:29', '2026-04-14 10:37:28'),
(522, 'App\\Models\\User', 13, 'UserApp', '74e60b08afd125fa3ad59fdbe66d4d5feff3a208273d50ba872b99124e629fd7', '[\"*\"]', '2026-04-06 23:50:52', NULL, '2026-04-06 23:32:47', '2026-04-06 23:50:52'),
(523, 'App\\Models\\User', 1, 'UserApp', '0e42ec3f18d499dce74e48ff14fa93064b91adf7b7a59293f133096e79b4915e', '[\"*\"]', '2026-04-14 02:36:39', NULL, '2026-04-06 23:36:33', '2026-04-14 02:36:39'),
(524, 'App\\Models\\User', 1, 'UserApp', '8f6f7a8b1d75bea6fa7c70f556d8d2680ec5a04d33a763542d37369162529f56', '[\"*\"]', '2026-04-07 07:53:32', NULL, '2026-04-07 07:52:24', '2026-04-07 07:53:32'),
(525, 'App\\Models\\User', 1, 'UserApp', '41624adbfccc77d457a1ef101a14442c82a439a2096b8c67aa45c8977cd70cb3', '[\"*\"]', '2026-04-07 10:24:24', NULL, '2026-04-07 10:19:53', '2026-04-07 10:24:24');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(526, 'App\\Models\\User', 13, 'UserApp', 'e2f9d7b0bfcbf4518dff6dff0d90d66de2593d45155268b8fec7cdf4e333f5a2', '[\"*\"]', '2026-04-07 10:26:29', NULL, '2026-04-07 10:24:48', '2026-04-07 10:26:29'),
(527, 'App\\Models\\User', 1, 'UserApp', 'cfe248d6a6cd76ef669c43d130d9c00b0a4e4985bc5e3b5f25fa5325e5c33e3c', '[\"*\"]', '2026-04-15 15:44:33', NULL, '2026-04-07 10:26:58', '2026-04-15 15:44:33'),
(528, 'App\\Models\\User', 13, 'UserApp', 'c2a69a4ad745f7dc01b7741e61d75811ca765db2057a9ba1cb89b46b6ff24ec0', '[\"*\"]', '2026-04-09 08:31:32', NULL, '2026-04-09 05:06:51', '2026-04-09 08:31:32'),
(529, 'App\\Models\\User', 1, 'UserApp', '2c5342ae4ffdf2540c0bf9b43672904feb702142108f0e6cb69cfb74a57561ab', '[\"*\"]', '2026-04-09 11:35:57', NULL, '2026-04-09 11:32:27', '2026-04-09 11:35:57'),
(530, 'App\\Models\\User', 1, 'UserApp', 'd72b4f4c6fbef9c2cb3ba00553585cbfe5428245c69004040fe58c37f3872612', '[\"*\"]', '2026-04-15 07:57:00', NULL, '2026-04-11 13:11:16', '2026-04-15 07:57:00'),
(531, 'App\\Models\\User', 13, 'UserApp', 'ce4298da39bcd770f56fa360cbe2ec38bf48aeebdab0a5d3447fc0b26ea62cdc', '[\"*\"]', '2026-04-13 05:15:37', NULL, '2026-04-12 23:22:31', '2026-04-13 05:15:37'),
(532, 'App\\Models\\User', 1, 'UserApp', 'b5a260e84d1771c88bc1b2632914c55dc18ccf196d51308a2a3d7af9c53abec5', '[\"*\"]', '2026-04-13 07:17:50', NULL, '2026-04-13 07:17:45', '2026-04-13 07:17:50'),
(533, 'App\\Models\\User', 1, 'UserApp', '5d870cd81687b7d5a11a9ecc39482894c93de9bb565ecf1f69e2c643d000d67f', '[\"*\"]', '2026-04-14 06:31:43', NULL, '2026-04-13 22:13:32', '2026-04-14 06:31:43'),
(534, 'App\\Models\\User', 13, 'UserApp', '478f139fecd82258b989aaa21d8e6edc85009b197e05782681930b2ccf4de230', '[\"*\"]', '2026-04-15 22:26:58', NULL, '2026-04-14 02:36:47', '2026-04-15 22:26:58'),
(535, 'App\\Models\\User', 1, 'UserApp', '0d64db2cc1ba6e17b79ed52d5a7b738d39d05e9c563c7138fd5022043567430e', '[\"*\"]', '2026-04-14 06:58:23', NULL, '2026-04-14 06:20:29', '2026-04-14 06:58:23'),
(536, 'App\\Models\\User', 13, 'UserApp', 'bd7f52b15b243607117e0b898909bd7896193d350e1911caf9d6828b722ed0c3', '[\"*\"]', '2026-04-14 06:32:11', NULL, '2026-04-14 06:31:51', '2026-04-14 06:32:11'),
(537, 'App\\Models\\User', 13, 'UserApp', 'c868411782394cc5d6aba4b1a6b158f58641101973617cc01c60039ac01398cd', '[\"*\"]', '2026-04-14 10:37:49', NULL, '2026-04-14 10:37:35', '2026-04-14 10:37:49'),
(538, 'App\\Models\\User', 1, 'UserApp', '557d5373def3862bdf0e67acd11994eb62ce7c2a55c8bb0d78bf7d39a38e9d37', '[\"*\"]', '2026-04-15 07:47:08', NULL, '2026-04-15 07:39:11', '2026-04-15 07:47:08'),
(539, 'App\\Models\\User', 13, 'UserApp', '15d980a2478e160fa3df55f2f50bf8b32681b709680d7f69bae5cebd5eddb7fc', '[\"*\"]', '2026-04-16 06:23:47', NULL, '2026-04-15 07:47:31', '2026-04-16 06:23:47'),
(540, 'App\\Models\\User', 13, 'UserApp', 'eca84be45c7c98c74ebd1a18a412d0956d05b9e34efa86c2f1e476ec3531d4ef', '[\"*\"]', '2026-04-16 07:22:24', NULL, '2026-04-15 07:57:16', '2026-04-16 07:22:24'),
(541, 'App\\Models\\User', 1, 'UserApp', 'b12c0537b9118fc90c087980f88e4685a7b01c678321c3bc5f724ea23de495f1', '[\"*\"]', '2026-04-15 08:06:58', NULL, '2026-04-15 08:02:50', '2026-04-15 08:06:58'),
(542, 'App\\Models\\User', 13, 'UserApp', 'c3b994ec33d9ce4d3fabeb2b2a7301a870d46a69f667c2cdf9f7c5f6c5e780e6', '[\"*\"]', '2026-04-16 06:22:00', NULL, '2026-04-15 22:18:34', '2026-04-16 06:22:00'),
(543, 'App\\Models\\User', 1, 'UserApp', '71606e9a0bf28b1290a7273bb3ad271f1b2fe947ae9221a9fe4d26ff6c9eac54', '[\"*\"]', '2026-04-16 21:23:26', NULL, '2026-04-15 22:27:11', '2026-04-16 21:23:26'),
(544, 'App\\Models\\User', 1, 'UserApp', 'edea629b8d63005e245542d57ad6d7ccacb9a793d6caa5ea25e0d3b56edbe285', '[\"*\"]', '2026-04-16 07:58:30', NULL, '2026-04-16 05:21:11', '2026-04-16 07:58:30'),
(545, 'App\\Models\\User', 13, 'UserApp', '7715b32472b531253c45d3dc24b5afed2839048324fb86f08f201963a61b4cd3', '[\"*\"]', '2026-04-16 07:58:30', NULL, '2026-04-16 06:21:28', '2026-04-16 07:58:30'),
(546, 'App\\Models\\User', 13, 'UserApp', 'f34020ed3ead80fed789d50ddb1eadeb28083461eaed728e07b43a681eea9854', '[\"*\"]', '2026-04-16 06:25:48', NULL, '2026-04-16 06:25:26', '2026-04-16 06:25:48'),
(547, 'App\\Models\\User', 1, 'UserApp', 'faba1a0d57ceead4889ab4b157154f2004907b22c29fbce6ff5a072aeda13f12', '[\"*\"]', '2026-04-17 05:45:53', NULL, '2026-04-16 09:01:59', '2026-04-17 05:45:53'),
(548, 'App\\Models\\User', 13, 'UserApp', '2312a8891ba2a6cab340821358c0aa9070d4f808c0b1b004040207bbc48f8297', '[\"*\"]', '2026-04-17 03:00:15', NULL, '2026-04-17 03:00:07', '2026-04-17 03:00:15'),
(549, 'App\\Models\\User', 1, 'UserApp', '27598f3ce96c0247a22c468a69600702724e9cf6968930df7fedc582bc177e1f', '[\"*\"]', '2026-04-17 03:00:51', NULL, '2026-04-17 03:00:48', '2026-04-17 03:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `added_by` bigint(20) UNSIGNED NOT NULL,
  `site_contact_name` varchar(255) DEFAULT NULL,
  `site_contact_phone` varchar(255) DEFAULT NULL,
  `site_instructions` text DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_lat` decimal(10,7) DEFAULT NULL,
  `delivery_long` decimal(10,7) DEFAULT NULL,
  `is_archived` tinyint(2) DEFAULT 0,
  `archived_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `added_by`, `site_contact_name`, `site_contact_phone`, `site_instructions`, `delivery_address`, `delivery_lat`, `delivery_long`, `is_archived`, `archived_by`, `created_at`, `updated_at`) VALUES
(3, 'project 2 .', 20, 'Opt', '23324', 'dafdsasd', '2880 Red Gum Pass Rd, Kendenup WA 6323, Australia', -34.4804046, 117.6506889, 0, NULL, '2025-10-11 14:53:08', '2025-11-13 08:53:23'),
(4, 'a', 20, 'a', 'a', 'a', 'Spencer St, West Melbourne VIC 3003, Australia', -37.8085418, 144.9486869, 0, NULL, '2025-10-11 15:06:53', '2025-11-13 08:53:38'),
(7, 'Project', 13, 'Project Manager', '13123123', 'Instructions', 'Sussex St, Sydney NSW 2000, Australia', -33.8709193, 151.2038552, 0, NULL, '2025-10-14 12:52:27', '2025-11-13 08:54:16'),
(8, 'NC1', 13, 'asd', 'asd', 'asd', 'Sydney Light Rail, New South Wales, Australia', -33.8768467, 151.1859653, 0, NULL, '2025-10-15 20:20:06', '2025-11-13 08:55:10'),
(9, 'Projek1', 13, 'El Gwapo', '11111111111111', 'test', 'Merivale St, South Brisbane QLD 4101, Australia', -27.4767923, 153.0176996, 0, NULL, '2025-11-03 06:11:00', '2025-11-13 08:54:44'),
(10, 'House', 13, 'jim', '0488352478', NULL, '12 Victoria Terrace, Bowen Hills QLD 4006, Australia', -27.4490284, 153.0421314, 1, 13, '2025-12-08 01:48:33', '2026-01-16 06:51:27'),
(11, 'White House', 110, 'Aiza Chan', '143 143 143', 'handle with care', 'Sydney Rd, Hornsby Heights NSW 2077, Australia', -33.6729361, 151.0934891, 0, NULL, '2026-01-15 23:27:15', '2026-01-15 23:27:15'),
(12, 'Projek', 13, NULL, NULL, NULL, '66a Erskine St, Sydney NSW 2000, Australia', -33.8666203, 151.2041626, 0, NULL, '2026-02-03 05:42:38', '2026-02-03 05:42:38'),
(13, 'Projek', 111, 'Test', '(02) 9265 6888', NULL, '500 George St, Sydney NSW 2000, Australia', -33.8724408, 151.2071504, 0, NULL, '2026-02-04 06:25:55', '2026-02-04 06:25:55'),
(14, 'contreras trading', 110, 'kevin', '143 143 143', 'handle with care', 'Brisbane St, Launceston TAS 7250, Australia', -41.4373065, 147.1388836, 0, NULL, '2026-02-05 04:47:25', '2026-02-05 04:47:25'),
(15, 'Test 1', 13, 'Bob', '0000000000;', 'UHF 29', '219 Sunrise Dr, Ocean View QLD 4521, Australia', -27.1131258, 152.8050749, 0, NULL, '2026-03-07 08:03:51', '2026-03-07 08:03:51');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_offers`
--

CREATE TABLE `supplier_offers` (
  `id` int(11) UNSIGNED NOT NULL,
  `supplier_id` int(11) UNSIGNED NOT NULL,
  `master_product_id` int(11) UNSIGNED NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `availability_status` enum('In Stock','Out of Stock','Limited') DEFAULT 'In Stock',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_offers`
--

INSERT INTO `supplier_offers` (`id`, `supplier_id`, `master_product_id`, `price`, `availability_status`, `status`, `created_at`, `updated_at`) VALUES
(1, 82, 1, 55.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(2, 82, 2, 55.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(3, 82, 3, 51.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(4, 82, 4, 26.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(5, 82, 5, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(6, 82, 6, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(7, 82, 7, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(8, 82, 8, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:07', '2026-02-18 22:21:07'),
(9, 82, 9, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(10, 82, 10, 90.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(11, 82, 11, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(12, 69, 12, 63.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(13, 69, 13, 74.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(14, 69, 14, 64.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(15, 69, 15, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(16, 69, 16, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(17, 69, 17, 160.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(18, 69, 18, 91.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(19, 69, 19, 134.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(20, 69, 20, 97.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(21, 69, 21, 74.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(22, 69, 22, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(23, 69, 23, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(24, 69, 24, 165.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(25, 69, 25, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(26, 69, 26, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(27, 69, 27, 163.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(28, 69, 28, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(29, 71, 29, 33.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(30, 71, 30, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(31, 71, 31, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(32, 71, 32, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(33, 71, 33, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(34, 71, 34, 57.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(35, 71, 35, 36.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(36, 71, 36, 38.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(37, 71, 37, 77.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(38, 71, 38, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(39, 71, 39, 77.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(40, 71, 40, 120.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(41, 71, 41, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(42, 71, 42, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(43, 71, 43, 77.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(44, 71, 44, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(45, 71, 45, 77.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(46, 71, 46, 77.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(47, 71, 47, 90.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(48, 71, 37, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(49, 71, 38, 79.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(50, 71, 39, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(51, 71, 40, 113.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(52, 71, 41, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(53, 71, 42, 79.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(54, 71, 43, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(55, 71, 44, 79.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(56, 71, 45, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(57, 71, 46, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(58, 71, 47, 85.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(59, 71, 48, 163.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(60, 71, 49, 23.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(61, 71, 50, 138.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(62, 71, 51, 113.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(63, 71, 52, 113.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(64, 71, 53, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(65, 71, 54, 188.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(66, 71, 55, 413.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(67, 71, 56, 275.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(68, 71, 57, 120.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(69, 71, 58, 120.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(70, 76, 59, 90.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(71, 76, 60, 67.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(72, 76, 61, 97.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(73, 76, 62, 57.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(74, 76, 63, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(75, 76, 64, 44.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(76, 76, 65, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(77, 76, 66, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(78, 76, 67, 87.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(79, 76, 68, 107.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(80, 76, 69, 90.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(81, 76, 70, 207.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(82, 76, 71, 214.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(83, 76, 72, 44.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(84, 76, 73, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(85, 76, 74, 110.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(86, 76, 75, 107.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(87, 76, 76, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(88, 76, 77, 63.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(89, 76, 78, 66.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(90, 76, 79, 63.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(91, 76, 80, 56.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(92, 76, 81, 56.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(93, 76, 82, 56.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(94, 76, 83, 66.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(95, 76, 84, 60.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(96, 76, 85, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(97, 76, 86, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(98, 76, 87, 55.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(99, 76, 88, 61.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(100, 76, 89, 43.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(101, 76, 90, 150.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(102, 76, 91, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(103, 76, 92, 317.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(104, 76, 93, 125.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(105, 76, 94, 317.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(106, 76, 95, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(107, 76, 96, 150.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(108, 76, 97, 267.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(109, 76, 98, 625.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(110, 76, 99, 625.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(111, 76, 100, 217.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(112, 79, 101, 59.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(113, 79, 102, 84.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(114, 79, 103, 116.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(115, 79, 104, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(116, 79, 105, 113.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(117, 79, 106, 247.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(118, 79, 107, 320.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(119, 79, 108, 114.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(120, 79, 109, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(121, 80, 110, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(122, 80, 111, 60.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(123, 80, 112, 60.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(124, 80, 113, 60.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(125, 80, 114, 35.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(126, 80, 115, 29.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(127, 80, 116, 49.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(128, 80, 117, 49.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(129, 80, 118, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:08', '2026-02-18 22:21:08'),
(130, 80, 119, 39.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(131, 80, 73, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(132, 80, 120, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(133, 80, 121, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(134, 80, 122, 67.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(135, 80, 123, 58.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(136, 80, 124, 142.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(137, 80, 125, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(138, 80, 126, 67.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(139, 80, 127, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(140, 80, 128, 142.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(141, 80, 129, 35.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(142, 80, 130, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(143, 80, 131, 46.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(144, 80, 132, 40.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(145, 80, 133, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(146, 80, 134, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(147, 80, 135, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(148, 80, 136, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(149, 80, 137, 65.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(150, 80, 138, 43.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(151, 80, 139, 225.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(152, 80, 140, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(153, 80, 141, 167.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(154, 80, 142, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(155, 80, 143, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(156, 80, 144, 334.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(157, 87, 145, 87.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(158, 87, 146, 104.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(159, 87, 147, 74.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(160, 87, 148, 104.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(161, 87, 149, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(162, 87, 150, 194.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(163, 87, 151, 14.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(164, 87, 152, 47.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(165, 87, 153, 64.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(166, 87, 115, 39.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(167, 87, 154, 107.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(168, 87, 155, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(169, 87, 156, 75.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(170, 87, 157, 91.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(171, 87, 158, 91.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(172, 87, 159, 174.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(173, 87, 160, 200.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(174, 87, 161, 220.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(175, 87, 162, 220.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(176, 87, 163, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(177, 87, 164, 134.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(178, 87, 165, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(179, 87, 166, 134.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(180, 87, 167, 127.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(181, 87, 168, 127.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(182, 87, 169, 127.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(183, 87, 170, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(184, 87, 171, 104.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(185, 87, 172, 220.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(186, 87, 173, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(187, 87, 174, 59.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(188, 87, 175, 86.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(189, 87, 176, 61.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(190, 87, 177, 275.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(191, 87, 178, 384.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(192, 87, 179, 384.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(193, 87, 180, 213.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(194, 87, 181, 213.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(195, 87, 182, 134.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(196, 87, 183, 288.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(197, 109, 184, 16.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(198, 109, 185, 16.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(199, 109, 186, 20.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(200, 109, 187, 26.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(201, 109, 188, 22.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(202, 109, 189, 20.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(203, 109, 189, 174.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(204, 109, 114, 17.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(205, 109, 114, 150.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(206, 109, 115, 12.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(207, 109, 115, 103.00, 'In Stock', 'Approved', '2026-02-18 22:21:09', '2026-02-18 22:21:09'),
(208, 109, 190, 18.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(209, 109, 191, 162.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(210, 109, 192, 27.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(211, 109, 193, 27.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(212, 109, 194, 22.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(213, 109, 195, 24.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(214, 109, 196, 19.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(215, 109, 197, 86.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(216, 109, 198, 86.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(217, 109, 199, 86.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(218, 109, 200, 86.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(219, 109, 201, 73.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(220, 109, 202, 50.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(221, 109, 203, 61.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(222, 109, 204, 80.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(223, 109, 205, 35.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(224, 109, 206, 30.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(225, 60, 207, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(226, 60, 208, 100.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(227, 58, 209, 28.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(228, 58, 210, 34.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(229, 58, 211, 34.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(230, 58, 212, 35.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(231, 58, 213, 30.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(232, 63, 214, 85.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(233, 63, 215, 107.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(234, 63, 216, 72.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(235, 63, 217, 61.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(236, 63, 218, 263.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(237, 63, 219, 188.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(238, 63, 220, 367.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(239, 63, 221, 163.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(240, 65, 222, 97.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(241, 65, 223, 94.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(242, 65, 224, 82.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(243, 65, 225, 68.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(244, 65, 226, 90.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(245, 65, 227, 73.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(246, 65, 228, 76.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(247, 65, 229, 113.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(248, 65, 230, 225.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(249, 65, 231, 252.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(250, 65, 232, 275.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(251, 65, 233, 330.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(252, 65, 234, 280.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(253, 65, 235, 310.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(254, 65, 236, 330.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(255, 65, 237, 550.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(256, 65, 238, 600.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(257, 65, 239, 415.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(258, 65, 240, 415.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(259, 74, 241, 16.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(260, 74, 242, 25.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(261, 81, 243, 37.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(262, 81, 114, 44.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(263, 81, 244, 54.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(264, 81, 245, 52.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(265, 81, 246, 36.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(266, 81, 247, 43.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(267, 81, 201, 65.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(268, 81, 248, 125.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(269, 81, 249, 257.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(270, 81, 250, 257.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(271, 75, 251, 15.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(272, 75, 252, 18.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(273, 75, 253, 22.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(274, 84, 254, 43.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(275, 84, 255, 38.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(276, 110, 256, 18.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(277, 110, 257, 25.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(278, 110, 258, 25.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(279, 110, 259, 25.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(280, 66, 260, 107.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(281, 66, 261, 83.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(282, 66, 262, 75.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(283, 66, 263, 64.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(284, 66, 264, 64.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(285, 66, 265, 71.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(286, 66, 266, 71.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(287, 66, 267, 248.00, 'In Stock', 'Approved', '2026-02-18 22:21:10', '2026-02-18 22:21:10'),
(288, 66, 268, 198.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(289, 66, 269, 248.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(290, 66, 270, 98.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(291, 66, 271, 330.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(292, 66, 272, 149.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(293, 66, 273, 248.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(294, 66, 274, 223.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(295, 92, 275, 114.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(296, 92, 276, 181.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(297, 92, 277, 42.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(298, 92, 278, 55.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(299, 59, 279, 262.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(300, 59, 280, 267.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(301, 59, 281, 274.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11'),
(302, 59, 282, 284.00, 'In Stock', 'Approved', '2026-02-18 22:21:11', '2026-02-18 22:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `surcharges`
--

CREATE TABLE `surcharges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `worked_example` text DEFAULT NULL,
  `billing_code` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `applies_to` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `integrated` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surcharges`
--

INSERT INTO `surcharges` (`id`, `name`, `description`, `conditions`, `worked_example`, `billing_code`, `amount`, `amount_type`, `applies_to`, `is_active`, `integrated`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'Sunday & Public Holiday Surcharge', 'Surcharge per m³ for all concrete deliveries on Sundays (Midnight Saturday to 4am Monday) or gazetted public holidays.', 'Applies to all deliveries scheduled between Midnight Saturday and 4am Monday, or on any gazetted public holiday.', 'Order 5m³ delivered on Christmas Day → 5 × $90.00 = $450.00 surcharge (excl. GST)', 'PH-003', 90.00, 'fixed', 'Concrete', 1, 1, 3, '2026-03-09 02:01:00', '2026-03-09 20:15:42', NULL),
(8, 'Returned Concrete Fee', 'Charged per m³ or part thereof for all returned concrete quantities of 1.0m³ and greater.', 'Applies when returned concrete is 1.0m³ or greater. Charged per m³ or part thereof.', 'Returned 1.5m³ concrete → 2 parts × $300.00 = $600.00 (excl. GST)', 'WO-008', 300.00, 'fixed', 'Concrete', 1, 0, 8, '2026-03-09 02:01:00', '2026-03-09 20:15:42', NULL),
(17, 'Environmental Levy', 'Applied per cubic metre or part thereof to all concrete delivered. Contributes to environmental compliance, water recycling, and waste management at the batching plant.', 'Applies per m³ or part thereof on all concrete delivered.', 'Order 10 m³ concrete → 10 × $2.50 = $25.00 Environmental Levy (excl. GST)', 'EL-017', 2.50, 'fixed', 'Concrete', 1, 1, 17, '2026-03-09 02:01:00', '2026-03-09 20:15:42', NULL),
(18, 'Standby / Aborted Load Surcharge', 'Applies where deliveries scheduled out of hours are cancelled, interrupted or delayed in excess of one hour from the scheduled start time. Charged per truck per hour.', 'Applies when out-of-hours deliveries are cancelled, interrupted, or delayed more than 1 hour from the scheduled start time.', 'Out-of-hours delivery delayed 2 hours → 2 × $195.00 = $390.00 surcharge (excl. GST)', 'AB-018', 195.00, 'fixed', 'Concrete', 1, 0, 18, '2026-03-09 02:01:00', '2026-03-09 20:15:42', NULL),
(19, 'Accelerator — Low Dose', 'Charged when a low dose accelerator additive is used in the concrete mix to speed up setting time.', 'Applies per m³ delivered when low dose accelerator is specified by the client or engineer.', 'Order 10 m³ with low dose accelerator → 10 × $6.00 = $60.00 (excl. GST)', 'ACCEL-LOW', 6.00, 'fixed', 'Concrete', 1, 1, 19, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(20, 'Accelerator — Medium Dose', 'Charged when a medium dose accelerator additive is used in the concrete mix to speed up setting time.', 'Applies per m³ delivered when medium dose accelerator is specified by the client or engineer.', 'Order 10 m³ with medium dose accelerator → 10 × $9.00 = $90.00 (excl. GST)', 'ACCEL-MED', 9.00, 'fixed', 'Concrete', 1, 1, 20, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(21, 'Accelerator — High Dose', 'Charged when a high dose accelerator additive is used in the concrete mix to speed up setting time.', 'Applies per m³ delivered when high dose accelerator is specified by the client or engineer.', 'Order 10 m³ with high dose accelerator → 10 × $12.00 = $120.00 (excl. GST)', 'ACCEL-HIGH', 12.00, 'fixed', 'Concrete', 1, 1, 21, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(22, 'Retarder — Low Dose', 'Charged when a low dose retarder additive is used to slow down concrete setting time. Useful for long pours or hot weather.', 'Applies per m³ delivered when low dose retarder is specified by the client or engineer.', 'Order 10 m³ with low dose retarder → 10 × $6.00 = $60.00 (excl. GST)', 'RETARD-LOW', 6.00, 'fixed', 'Concrete', 1, 1, 22, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(23, 'Retarder — Medium Dose', 'Charged when a medium dose retarder additive is used to slow down concrete setting time.', 'Applies per m³ delivered when medium dose retarder is specified by the client or engineer.', 'Order 10 m³ with medium dose retarder → 10 × $9.00 = $90.00 (excl. GST)', 'RETARD-MED', 9.00, 'fixed', 'Concrete', 1, 1, 23, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(24, 'Retarder — High Dose', 'Charged when a high dose retarder additive is used to slow down concrete setting time.', 'Applies per m³ delivered when high dose retarder is specified by the client or engineer.', 'Order 10 m³ with high dose retarder → 10 × $12.00 = $120.00 (excl. GST)', 'RETARD-HIGH', 12.00, 'fixed', 'Concrete', 1, 1, 24, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(25, 'Minimum Cartage', 'A fee applies when the delivered load size is less than 4m³. Calculated on the undelivered portion of the minimum 4m³ load.', 'Applies when a single delivery is less than 4m³. Fee is calculated on the shortfall below 4m³, i.e. the undelivered part.', 'Delivery of 2.5m³ → Shortfall = 4 − 2.5 = 1.5m³ undelivered → 1.5 × $90.00 = $135.00 (excl. GST)', 'MCART', 90.00, 'fixed', 'Concrete', 1, 1, 25, '2026-03-09 08:14:31', '2026-03-09 08:14:31', NULL),
(26, 'Saturday Delivery — 6am to 12pm', 'After hours surcharge per m³ for concrete deliveries on Saturday between 6am and 12pm.', 'Applies to all deliveries scheduled on Saturday between 06:00 and 12:00.', 'Order 5m³ delivered Saturday 9am → 5 × $10.00 = $50.00 surcharge (excl. GST)', 'SD-002A', 10.00, 'fixed', 'Concrete', 1, 1, 2, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(27, 'Saturday Delivery — 12pm to 4pm', 'After hours surcharge per m³ for concrete deliveries on Saturday between 12pm and 4pm.', 'Applies to all deliveries scheduled on Saturday between 12:00 and 16:00.', 'Order 5m³ delivered Saturday 2pm → 5 × $45.00 = $225.00 surcharge (excl. GST)', 'SD-002B', 45.00, 'fixed', 'Concrete', 1, 1, 26, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(28, 'Saturday Delivery — 4pm to Midnight', 'After hours surcharge per m³ for concrete deliveries on Saturday between 4pm and midnight.', 'Applies to all deliveries scheduled on Saturday between 16:00 and 00:00.', 'Order 5m³ delivered Saturday 7pm → 5 × $90.00 = $450.00 surcharge (excl. GST)', 'SD-002C', 90.00, 'fixed', 'Concrete', 1, 1, 27, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(29, 'After Hours Delivery — Mon–Fri 4pm to 6pm', 'After hours surcharge per m³ for weekday deliveries between 4pm and 6pm.', 'Applies to all deliveries scheduled Monday to Friday between 16:00 and 18:00.', 'Order 5m³ delivered weekday 5pm → 5 × $10.00 = $50.00 surcharge (excl. GST)', 'AH-007A', 10.00, 'fixed', 'Concrete', 1, 1, 7, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(30, 'After Hours Delivery — Mon–Fri 6pm to 4am & Sat to 6am', 'After hours surcharge per m³ for weekday deliveries between 6pm and 4am, and Saturday deliveries up to 6am.', 'Applies to all deliveries scheduled Monday to Friday between 18:00 and 04:00, and Saturday from midnight to 06:00.', 'Order 5m³ delivered weekday 11pm → 5 × $10.00 = $50.00 surcharge (excl. GST)', 'AH-007B', 10.00, 'fixed', 'Concrete', 1, 1, 28, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(31, 'Waiting Time — Normal Hours', 'Charged per minute when a delivery truck is held on site beyond the free 30-minute waiting period during normal operating hours (Mon–Fri 6am–4pm).', 'Applies after the first 30 minutes of free waiting time during normal operating hours (Monday to Friday 6am–4pm). Charged per minute.', 'Truck held 60 mins during normal hours → 30 mins free + 30 mins chargeable = 30 × $3.50 = $105.00 surcharge (excl. GST)', 'WT-009A', 3.50, 'fixed', 'Concrete', 1, 0, 9, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(32, 'Waiting Time — Outside Normal Hours', 'Charged per minute when a delivery truck is held on site beyond the free 30-minute waiting period outside normal operating hours.', 'Applies after the first 30 minutes of free waiting time outside normal operating hours (before 6am or after 4pm Mon–Fri, and weekends). Charged per minute.', 'Truck held 60 mins outside normal hours → 30 mins free + 30 mins chargeable = 30 × $7.00 = $210.00 surcharge (excl. GST)', 'WT-009B', 7.00, 'fixed', 'Concrete', 1, 0, 29, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(33, 'Cancellation Fee — Under 15m³ (Normal Hours)', 'Fee charged when a confirmed pour under 15m³ is cancelled or postponed by 3 hours or more with insufficient notice during normal hours.', 'Applies when an order under 15m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'Order 10m³ cancelled day before pour → Cancellation Fee $500.00 (excl. GST)', 'CF-011A', 500.00, 'fixed', 'Concrete', 1, 0, 11, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(34, 'Cancellation Fee — 15m³ to 75m³ (Normal Hours)', 'Fee charged when a confirmed pour between 15m³ and 75m³ is cancelled or postponed by 3 hours or more with insufficient notice during normal hours.', 'Applies when an order between 15m³ and 75m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'Order 50m³ cancelled day before pour → Cancellation Fee $1,650.00 (excl. GST)', 'CF-011B', 1650.00, 'fixed', 'Concrete', 1, 0, 30, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(35, 'Cancellation Fee — Over 75m³ (Normal Hours)', 'Fee charged when a confirmed pour over 75m³ is cancelled or postponed by 3 hours or more with insufficient notice during normal hours.', 'Applies when an order over 75m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'Order 100m³ cancelled day before pour → Cancellation Fee $3,300.00 (excl. GST)', 'CF-011C', 3300.00, 'fixed', 'Concrete', 1, 0, 31, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(36, 'Cancellation Fee — Over 200m³ (Normal Hours)', 'Fee charged when a confirmed pour over 200m³ is cancelled or postponed by 3 hours or more with insufficient notice during normal hours.', 'Applies when an order over 200m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'Order 250m³ cancelled day before pour → Cancellation Fee $7,000.00 (excl. GST)', 'CF-011D', 7000.00, 'fixed', 'Concrete', 1, 0, 32, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(37, 'Cancellation Fee — Under 15m³ (After Hours)', 'Fee charged when a pour booked for after normal operating hours under 15m³ is cancelled or postponed by 3 hours or more with insufficient notice.', 'Applies when an after-hours order under 15m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'After-hours order 10m³ cancelled day before → Cancellation Fee $1,000.00 (excl. GST)', 'CF-AH-001', 1000.00, 'fixed', 'Concrete', 1, 0, 33, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(38, 'Cancellation Fee — Under 75m³ (After Hours)', 'Fee charged when a pour booked for after normal operating hours under 75m³ is cancelled or postponed by 3 hours or more with insufficient notice.', 'Applies when an after-hours order under 75m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'After-hours order 50m³ cancelled day before → Cancellation Fee $3,300.00 (excl. GST)', 'CF-AH-002', 3300.00, 'fixed', 'Concrete', 1, 0, 34, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(39, 'Cancellation Fee — Over 75m³ (After Hours)', 'Fee charged when a pour booked for after normal operating hours over 75m³ is cancelled or postponed by 3 hours or more with insufficient notice.', 'Applies when an after-hours order over 75m³ is cancelled or postponed with less than 2 working days notice. Deadline: 12:00pm two working days prior to the pour.', 'After-hours order 100m³ cancelled day before → Cancellation Fee $6,600.00 (excl. GST)', 'CF-AH-003', 6600.00, 'fixed', 'Concrete', 1, 0, 35, '2026-03-09 20:15:42', '2026-03-09 20:15:42', NULL),
(40, 'Extra Cartage', 'Additional fee for loads delivered more than 15km from the nearest batching plant.', 'Applies when the delivery site is more than 15km from the nearest plant. Charged per km per m³.', 'Site 20km from plant, order 5m³ → 20 × $3.00 × 5 = $300.00 (excl. GST)', 'EC-002', 3.00, 'fixed', 'Concrete', 1, 0, 36, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(41, 'Plant Operational Fee — Base (First 4 Hours)', 'Fee to operate plant per period of 4 hours, or part thereof, outside normal operating hours. Minimum 48 hours notice required.', 'Applies when plant operation is required outside normal operating hours. After Normal Operating Hours charges also apply.', 'Plant required for 3hrs after hours → Plant Operational Fee $2,500.00 (excl. GST)', 'POF-003A', 2500.00, 'fixed', 'Concrete', 1, 0, 37, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(42, 'Plant Operational Fee — Hourly (After First 4 Hours)', 'Hourly charge for continued plant operation beyond the initial 4-hour period outside normal operating hours.', 'Applies after the first 4 hours of out-of-hours plant operation. Charged per hour.', 'Plant required 6hrs after hours → $2,500 base + 2 × $500.00 = $3,500.00 (excl. GST)', 'POF-003B', 500.00, 'fixed', 'Concrete', 1, 0, 38, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(43, 'Additional Balance / Plus Load Fee', 'Fee applied to unplanned deliveries required over and above the booked load. One balance or plus load is allowed per order.', 'Applies to additional balance or plus loads beyond the booked order. For orders < 50m³, 1 balance/plus load permitted. For orders > 50m³, applies after a 20% increase in order volume.', 'Order 10m³, unplanned extra load required → Additional Balance/Plus Load Fee $150.00 (excl. GST)', 'BPL-004', 150.00, 'fixed', 'Concrete', 1, 0, 39, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(44, 'Administration Fee', 'Fee for the provision of additional copies of delivery dockets, invoices, statements, or review of call recordings at customer request.', 'Applies per copy or request for duplicate documentation or call recording review.', 'Client requests duplicate invoice copy → Administration Fee $50.00 (excl. GST)', 'ADM-005', 50.00, 'fixed', 'Concrete', 1, 0, 40, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(45, 'Small Aggregate Premium — 10mm', 'Fee to cover additional costs associated with production of 10mm small aggregates and increased cement content to maintain strength.', 'Applies per m³ when a 10mm small aggregate mix is specified.', 'Order 5m³ of 10mm aggregate concrete → 5 × $10.00 = $50.00 (excl. GST)', 'SAP-006A', 10.00, 'fixed', 'Concrete', 1, 1, 41, '2026-03-12 03:34:57', '2026-03-12 04:13:49', NULL),
(46, 'Small Aggregate Premium — 7mm', 'Fee to cover additional costs associated with production of 7mm small aggregates and increased cement content to maintain strength.', 'Applies per m³ when a 7mm small aggregate mix is specified.', 'Order 5m³ of 7mm aggregate concrete → 5 × $12.00 = $60.00 (excl. GST)', 'SAP-006B', 12.00, 'fixed', 'Concrete', 1, 1, 42, '2026-03-12 03:34:57', '2026-03-12 04:13:49', NULL),
(47, 'Slump Modification', 'Applies when slump in excess of 80mm is requested on all N Class grades up to 40MPa. Charged per m³ per 20mm slump increase. S Class mixes are price on application.', 'Applies when requested slump exceeds 80mm on N Class grades up to 40MPa. Charged per 20mm increment above 80mm per m³.', 'Order 5m³, slump increased by 40mm → 2 increments × 5m³ × $5.00 = $50.00 (excl. GST)', 'SM-007', 5.00, 'fixed', 'Concrete', 1, 1, 43, '2026-03-12 03:34:57', '2026-03-12 04:13:49', NULL),
(48, 'Handling, Mixing & Washout Fee — Oxides / Fibre', 'Fee for the handling, mixing, and washout of prescribed additives including special aggregates, oxides, and fibres.', 'Applies per m³ when oxides, fibres, or special additives are included in the concrete mix.', 'Order 5m³ with oxide colouring → 5 × $15.00 = $75.00 (excl. GST)', 'HMW-008', 15.00, 'fixed', 'Concrete', 1, 1, 44, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(49, 'Temperature Control', 'Concrete supplied is not guaranteed to comply with temperature requirements of a specification. Temperature control can be supplied at an additional cost.', 'Applies when temperature-controlled concrete is requested. Price determined on application.', 'Temperature-controlled concrete requested → Price on Application', 'TC-009', 0.00, 'fixed', 'Concrete', 1, 0, 45, '2026-03-12 03:34:57', '2026-03-12 03:34:57', NULL),
(50, 'Waiting Time', 'Applies to deliveries that exceed 30 minutes from arrival on site. No waiting time is payable for the first 30 minutes of any delivery.', 'Delivery time on site exceeds 30 minutes.', 'Truck arrives at 9:00am, leaves at 10:00am. Chargeable time = 30 minutes. 30 × $2.20 = $66.00.', 'AG-WT-001', 2.20, 'fixed', 'Aggregates', 1, 0, 1, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(51, 'Waiting Time (After Hours)', 'Applies to deliveries outside Normal Operating Hours that exceed 30 minutes from arrival on site.', 'Delivery is outside normal hours and time on site exceeds 30 minutes.', 'After-hours truck on site for 45 minutes. Chargeable time = 15 minutes. 15 × $4.40 = $66.00.', 'AG-WT-002', 4.40, 'fixed', 'Aggregates', 1, 0, 2, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(52, 'Out of Hours', 'Night, weekend or holiday pick-up and deliveries outside Normal Operating Hours. Mon–Fri: 6pm–6am. Saturday: 12 noon–6pm and 6pm–midnight. Sunday: Sat midnight to 6am Monday.', 'Delivery or pick-up occurs outside normal operating hours on weekdays, Saturday afternoon/night, or Sunday.', '10 tonne delivery on Saturday at 2pm. 10 × $2.50 = $25.00 plus negotiated cartage rates.', 'AG-OOH-003', 2.50, 'fixed', 'Aggregates', 1, 1, 3, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(53, 'Minimum Cartage', 'All prices are quoted based on Minimum Load/Delivery Quantity (Rigid/Tandem: 12T, Semi/Truck & Dog: 25T). Minimum Cartage is payable when delivered quantity is below the minimum, calculated on the undelivered quantity.', 'Delivered quantity is below the minimum load/delivery quantity for the truck type.', 'Rigid truck minimum is 12T. Customer receives 8T. Shortfall of 4T charged at standard cartage rates.', 'AG-MC-004', 0.00, 'fixed', 'Aggregates', 1, 1, 4, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(54, 'Cancellation (Under 500T)', 'Cancellation of a delivery order after 4pm the previous working day for orders under 500 tonnes.', 'Order cancelled after 4pm the previous working day. Total order quantity is under 500T.', 'Order for 200T cancelled at 5pm Tuesday. Cancellation fee = $1,500 per day.', 'AG-CAN-005A', 1500.00, 'fixed', 'Aggregates', 1, 0, 5, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(55, 'Cancellation (500T or Over)', 'Cancellation of a delivery order after 4pm the previous working day for orders of 500 tonnes or more.', 'Order cancelled after 4pm the previous working day. Total order quantity is 500T or more.', 'Order for 600T cancelled at 5pm Tuesday. Cancellation fee = $4,500 per day.', 'AG-CAN-005B', 4500.00, 'fixed', 'Aggregates', 1, 0, 6, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(56, 'Environmental Levy', 'Applied to recover costs associated with environmental compliance requirements for water, dust, biodiversity, rehabilitation and quarry boundary management.', 'Applies to all aggregate deliveries.', '20 tonne delivery. 20 × $0.45 = $9.00 environmental levy.', 'AG-EL-006', 0.45, 'fixed', 'Aggregates', 1, 1, 7, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(57, 'Rough Road (Rigid)', 'Deliveries on designated dirt or rough roads where poor road conditions significantly increase cartage costs and service levels. Applies to Rigid trucks.', 'Delivery is made on a designated rough or dirt road using a Rigid truck.', 'Rigid truck travels 5km on rough road delivering 12T. 0.16 × 5 × 12 = $9.60.', 'AG-RR-007A', 0.16, 'fixed', 'Aggregates', 1, 0, 8, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(58, 'Rough Road (Semi)', 'Deliveries on designated dirt or rough roads where poor road conditions significantly increase cartage costs and service levels. Applies to Semi-Trailer / Truck & Dog.', 'Delivery is made on a designated rough or dirt road using a Semi-Trailer or Truck & Dog.', 'Semi travels 5km on rough road delivering 25T. 0.09 × 5 × 25 = $11.25.', 'AG-RR-007B', 0.09, 'fixed', 'Aggregates', 1, 0, 9, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(59, 'Delivery into Auto Grade Paver', 'Service delivery direct into customer or contractor paver or auto grade equipment.', 'Delivery is discharged directly into a paver or auto grade machine on site.', '15 tonne delivery into auto grade paver. 15 × $2.50 = $37.50.', 'AG-AGP-008', 2.50, 'fixed', 'Aggregates', 1, 1, 10, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(60, 'Conditioning (Optimum Moisture Content)', 'Additional wetting and mixing to achieve customer requested Optimum Moisture Content (OMC).', 'Customer specifies a target OMC requiring additional processing at plant.', '20 tonne delivery with OMC conditioning. 20 × $2.00 = $40.00.', 'AG-OMC-009', 2.00, 'fixed', 'Aggregates', 1, 1, 11, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(61, 'Additional Stabiliser / Cement Treatment', 'Additional percentage of Stabiliser or Cement Treatment requested by customer above the quoted product specification. Minimum 2% CTR applies.', 'Customer requests additional stabiliser or cement treatment above quoted spec. Minimum 2% CTR.', '20T delivery with 1% extra cement treatment. 20 × $4.50 = $90.00 for the additional 1%.', 'AG-SCT-010', 4.50, 'fixed', 'Aggregates', 1, 1, 12, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(62, 'Plant Opening (First 4 Hours)', 'Opening or re-opening a Quarry outside Normal Operating Hours. Minimum 24 hours notice required to cancel an opening request.', 'Quarry opening is requested outside normal operating hours.', 'Quarry opened at 5am Saturday for 3 hours. Flat fee = $4,500.00. Out of Hours fees also apply.', 'AG-PO-011A', 4500.00, 'fixed', 'Aggregates', 1, 0, 13, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(63, 'Plant Opening (Per Hour After 4 Hours)', 'Additional hourly charge for quarry openings outside Normal Operating Hours that extend beyond the initial 4-hour block. Out of Hours fees also apply.', 'Quarry opening outside normal hours extends beyond 4 hours.', 'Quarry opened for 6 hours out of hours. Base $4,500 + 2 additional hours × $1,500 = $7,500. Plus Out of Hours fees.', 'AG-PO-011B', 1500.00, 'fixed', 'Aggregates', 1, 0, 14, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(64, 'Additional Site Access Inspection', 'Additional specific inspection of delivery site or roadside stack site prior to tender or during supply.', 'An additional site access inspection is required prior to tender or during active supply.', 'One site inspection required before project commences. Fee = $350.00.', 'AG-SAI-012', 350.00, 'fixed', 'Aggregates', 1, 0, 15, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(65, 'Additional Testing', 'Additional specific customer testing requested above standard testing or agreed quoted project testing. Rate to be agreed and applied per project quote.', 'Customer requests testing beyond standard or agreed project scope.', 'Rate negotiated and applied per project quote.', 'AG-TST-013', 0.00, 'fixed', 'Aggregates', 1, 0, 16, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL),
(66, 'Administration', 'Additional manual copy of delivery docket, invoice and/or statement printed on request.', 'Customer requests a manual copy of delivery docket, invoice, or statement.', 'Customer requests 2 manual docket copies. 2 × $5.00 = $10.00.', 'AG-ADM-014', 5.00, 'fixed', 'Aggregates', 1, 0, 17, '2026-03-16 03:24:04', '2026-03-16 03:24:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `testing_fees`
--

CREATE TABLE `testing_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `worked_example` text DEFAULT NULL,
  `billing_code` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testing_fees`
--

INSERT INTO `testing_fees` (`id`, `name`, `description`, `conditions`, `worked_example`, `billing_code`, `amount`, `amount_type`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Standard Cylinder Test — Normal Hours (Set of 3)', 'Standard concrete three (3) 100mm cylinder test as per AS 1012.1, 3.1, 8.1, 9 & 12.1. Tested during normal operating hours Monday to Friday 6am–4pm.', 'Applies to cylinder sets cast and tested during normal operating hours (Mon–Fri 6am–4pm).', '1 set of 3 cylinders during normal hours → Testing Fee $215.00 per set (excl. GST)', 'GLSTD', 215.00, 'fixed', 1, 1, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(2, 'Standard Cylinder Test — After Hours 1 (Set of 3)', 'Standard concrete three (3) 100mm cylinder test as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 1: Mon–Fri 4pm to 6am the following day, and Sat 12pm to midnight.', 'Applies to cylinder sets cast during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 set of 3 cylinders after hours (Mon–Fri 4pm–6am) → Testing Fee $330.00 per set (excl. GST)', 'GLSTD1', 330.00, 'fixed', 1, 2, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(3, 'Standard Cylinder Test — After Hours 2 (Set of 3)', 'Standard concrete three (3) 100mm cylinder test as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 2: Saturday 6am–12pm.', 'Applies to cylinder sets cast on Saturday between 6am and 12pm.', '1 set of 3 cylinders Saturday 6am–12pm → Testing Fee $275.00 per set (excl. GST)', 'GLSTD2', 275.00, 'fixed', 1, 3, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(4, 'Standard Cylinder Test — After Hours 3 / Sundays & Public Holidays (Set of 3)', 'Standard concrete three (3) 100mm cylinder test as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 3: Sundays and Public Holidays.', 'Applies to cylinder sets cast on Sundays or gazetted public holidays.', '1 set of 3 cylinders on Sunday → Testing Fee $440.00 per set (excl. GST)', 'GLSTD3', 440.00, 'fixed', 1, 4, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(5, 'Extra Single Cylinder Specimen — Normal Hours', 'Extra single (1) 100mm cylinder specimen as per AS 1012.1, 3.1, 8.1, 9 & 12.1. Normal operating hours Monday to Friday 6am–4pm.', 'Applies when an additional single cylinder is required beyond the standard set during normal hours.', '1 extra cylinder specimen → Testing Fee $70.00 per cylinder (excl. GST)', 'GLCYL', 70.00, 'fixed', 1, 5, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(6, 'One-Day Site-Cured Cylinder — Normal Hours', 'One day or site cured single (1) 100mm cylinder with Next Day Results as per AS 1012.1, 3.1, 8.1, 9 & 12.1. Normal hours Mon–Fri 6am–4pm.', 'Applies when a site-cured cylinder is required with next-day results during normal operating hours.', '1 site-cured cylinder normal hours → Testing Fee $165.00 per cylinder (excl. GST)', 'GL1DCYL', 165.00, 'fixed', 1, 6, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(7, 'One-Day Site-Cured Cylinder — After Hours 1', 'One day or site cured single (1) 100mm cylinder with Next Day Results as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies when a site-cured cylinder is required during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 site-cured cylinder after hours (Mon–Fri 4pm) → Testing Fee $253.00 per cylinder (excl. GST)', 'GL1DCYL1', 253.00, 'fixed', 1, 7, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(8, 'One-Day Site-Cured Cylinder — After Hours 2', 'One day or site cured single (1) 100mm cylinder with Next Day Results as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 2: Saturday 6am–12pm.', 'Applies when a site-cured cylinder is required on Saturday between 6am and 12pm.', '1 site-cured cylinder Saturday 6am–12pm → Testing Fee $215.00 per cylinder (excl. GST)', 'GL1DCYL2', 215.00, 'fixed', 1, 8, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(9, 'One-Day Site-Cured Cylinder — After Hours 3 / Sundays & Public Holidays', 'One day or site cured single (1) 100mm cylinder with Next Day Results as per AS 1012.1, 3.1, 8.1, 9 & 12.1. After Hours 3: Sundays and Public Holidays.', 'Applies when a site-cured cylinder is required on a Sunday or gazetted public holiday.', '1 site-cured cylinder on Sunday → Testing Fee $340.00 per cylinder (excl. GST)', 'GL1DCYL3', 340.00, 'fixed', 1, 9, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(10, 'Additional Slump Test — Normal Hours', 'Additional slump test performed when testing crew is already on site, as per AS 1012.3.1. Normal hours Mon–Fri 6am–4pm.', 'Applies when an additional slump test is required while the testing team is already present on site during normal hours.', '1 additional slump test normal hours → Testing Fee $45.00 per test (excl. GST)', 'GLSLUMP', 45.00, 'fixed', 1, 10, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(11, 'Additional Slump Test — After Hours 1', 'Additional slump test performed when testing crew is already on site, as per AS 1012.3.1. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies when an additional slump test is required on site during Mon–Fri 4pm–6am or Saturday 12pm–midnight.', '1 additional slump test after hours 1 → Testing Fee $60.00 per test (excl. GST)', 'GLSLUMP1', 60.00, 'fixed', 1, 11, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(12, 'Additional Slump Test — After Hours 2', 'Additional slump test performed when testing crew is already on site, as per AS 1012.3.1. After Hours 2: Saturday 6am–12pm.', 'Applies when an additional slump test is required on site on Saturday between 6am and 12pm.', '1 additional slump test Saturday 6am–12pm → Testing Fee $50.00 per test (excl. GST)', 'GLSLUMP2', 50.00, 'fixed', 1, 12, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(13, 'Additional Slump Test — After Hours 3 / Sundays & Public Holidays', 'Additional slump test performed when testing crew is already on site, as per AS 1012.3.1. After Hours 3: Sundays and Public Holidays.', 'Applies when an additional slump test is required on site on a Sunday or gazetted public holiday.', '1 additional slump test on Sunday → Testing Fee $80.00 per test (excl. GST)', 'GLSLUMP3', 80.00, 'fixed', 1, 13, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(14, 'Flexural Strength Beam Test — Normal Hours', 'Flexural strength test using 100 × 100 × 3500mm beam specimens as per AS 1012.1, 3, 8.2, 11. Normal hours Mon–Fri 6am–4pm.', 'Applies to beam specimens cast and tested during normal operating hours.', '1 beam specimen normal hours → Testing Fee $209.00 per beam (excl. GST)', 'GL100FX', 209.00, 'fixed', 1, 14, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(15, 'Flexural Strength Beam Test — After Hours 1', 'Flexural strength test using 100 × 100 × 3500mm beam specimens as per AS 1012.1, 3, 8.2, 11. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies to beam specimens cast during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 beam specimen after hours 1 → Testing Fee $320.00 per beam (excl. GST)', 'GL100FX1', 320.00, 'fixed', 1, 15, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(16, 'Flexural Strength Beam Test — After Hours 2', 'Flexural strength test using 100 × 100 × 3500mm beam specimens as per AS 1012.1, 3, 8.2, 11. After Hours 2: Saturday 6am–12pm.', 'Applies to beam specimens cast on Saturday between 6am and 12pm.', '1 beam specimen Saturday 6am–12pm → Testing Fee $265.00 per beam (excl. GST)', 'GL100FX2', 265.00, 'fixed', 1, 16, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(17, 'Flexural Strength Beam Test — After Hours 3 / Sundays & Public Holidays', 'Flexural strength test using 100 × 100 × 3500mm beam specimens as per AS 1012.1, 3, 8.2, 11. After Hours 3: Sundays and Public Holidays.', 'Applies to beam specimens cast on a Sunday or gazetted public holiday.', '1 beam specimen on Sunday → Testing Fee $425.00 per beam (excl. GST)', 'GL100FX3', 425.00, 'fixed', 1, 17, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(18, 'Drying Shrinkage Test — Normal Hours (Set of 3 Prisms)', 'Drying shrinkage test using one set of 3 prisms as per AS 1012.13. Normal hours Mon–Fri 6am–4pm.', 'Applies to shrinkage prism sets cast during normal operating hours.', '1 set of 3 prisms normal hours → Testing Fee $605.00 per set (excl. GST)', 'GLSHRNK', 605.00, 'fixed', 1, 18, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(19, 'Drying Shrinkage Test — After Hours 1 (Set of 3 Prisms)', 'Drying shrinkage test using one set of 3 prisms as per AS 1012.13. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies to shrinkage prism sets cast during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 set of 3 prisms after hours 1 → Testing Fee $880.00 per set (excl. GST)', 'GLSHRNK1', 880.00, 'fixed', 1, 19, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(20, 'Drying Shrinkage Test — After Hours 2 (Set of 3 Prisms)', 'Drying shrinkage test using one set of 3 prisms as per AS 1012.13. After Hours 2: Saturday 6am–12pm.', 'Applies to shrinkage prism sets cast on Saturday between 6am and 12pm.', '1 set of 3 prisms Saturday 6am–12pm → Testing Fee $730.00 per set (excl. GST)', 'GLSHRNK2', 730.00, 'fixed', 1, 20, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(21, 'Drying Shrinkage Test — After Hours 3 / Sundays & Public Holidays (Set of 3 Prisms)', 'Drying shrinkage test using one set of 3 prisms as per AS 1012.13. After Hours 3: Sundays and Public Holidays.', 'Applies to shrinkage prism sets cast on a Sunday or gazetted public holiday.', '1 set of 3 prisms on Sunday → Testing Fee $1,160.00 per set (excl. GST)', 'GLSHRNK3', 1160.00, 'fixed', 1, 21, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(22, 'Spread Test — Normal Hours', 'Spread test only using Z40 CIA Method on cylinders/specimens. Normal hours Mon–Fri 6am–4pm.', 'Applies to spread tests performed during normal operating hours.', '1 spread test normal hours → Testing Fee $80.00 per test (excl. GST)', 'GLSPRED', 80.00, 'fixed', 1, 22, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(23, 'Spread Test — After Hours 1', 'Spread test only using Z40 CIA Method on cylinders/specimens. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies to spread tests performed during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 spread test after hours 1 → Testing Fee $160.00 per test (excl. GST)', 'GLSPRED1', 160.00, 'fixed', 1, 23, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(24, 'Spread Test — After Hours 2', 'Spread test only using Z40 CIA Method on cylinders/specimens. After Hours 2: Saturday 6am–12pm.', 'Applies to spread tests performed on Saturday between 6am and 12pm.', '1 spread test Saturday 6am–12pm → Testing Fee $120.00 per test (excl. GST)', 'GLSPRED2', 120.00, 'fixed', 1, 24, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(25, 'Spread Test — After Hours 3 / Sundays & Public Holidays', 'Spread test only using Z40 CIA Method on cylinders/specimens. After Hours 3: Sundays and Public Holidays.', 'Applies to spread tests performed on a Sunday or gazetted public holiday.', '1 spread test on Sunday → Testing Fee $240.00 per test (excl. GST)', 'GLSPRED3', 240.00, 'fixed', 1, 25, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(26, 'Air Content Determination — Normal Hours', 'Air content determination as per AS 1012.4. Normal hours Mon–Fri 6am–4pm.', 'Applies to air content tests performed during normal operating hours.', '1 air content test normal hours → Testing Fee $215.00 per test (excl. GST)', 'GLAIR', 215.00, 'fixed', 1, 26, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(27, 'Air Content Determination — After Hours 1', 'Air content determination as per AS 1012.4. After Hours 1: Mon–Fri 4pm–6am the following day, and Sat 12pm–midnight.', 'Applies to air content tests performed during Mon–Fri 4pm–6am (following day) or Saturday 12pm–midnight.', '1 air content test after hours 1 → Testing Fee $275.00 per test (excl. GST)', 'GLAIR1', 275.00, 'fixed', 1, 27, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(28, 'Air Content Determination — After Hours 2', 'Air content determination as per AS 1012.4. After Hours 2: Saturday 6am–12pm.', 'Applies to air content tests performed on Saturday between 6am and 12pm.', '1 air content test Saturday 6am–12pm → Testing Fee $225.00 per test (excl. GST)', 'GLAIR2', 225.00, 'fixed', 1, 28, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(29, 'Air Content Determination — After Hours 3 / Sundays & Public Holidays', 'Air content determination as per AS 1012.4. After Hours 3: Sundays and Public Holidays.', 'Applies to air content tests performed on a Sunday or gazetted public holiday.', '1 air content test on Sunday → Testing Fee $365.00 per test (excl. GST)', 'GLAIR3', 365.00, 'fixed', 1, 29, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(30, 'Test Cancellation Fee — Normal Hours', 'Fee applies to testing cancelled after 3pm the previous workday, for tests scheduled during normal hours Mon–Fri 6am–4pm.', 'Applies when testing booked during normal hours is cancelled after 3pm on the previous working day.', 'Normal hours test cancelled after 3pm previous day → Cancellation Fee $330.00 per test cancellation (excl. GST)', 'GLCANCL', 330.00, 'fixed', 1, 30, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(31, 'Test Cancellation Fee — After Hours 1', 'Fee applies to testing cancelled after 3pm the previous workday, for tests scheduled during After Hours 1 (Mon–Fri 4pm–6am following day, Sat 12pm–midnight).', 'Applies when after-hours 1 testing is cancelled after 3pm on the previous working day.', 'After hours 1 test cancelled after 3pm previous day → Cancellation Fee $550.00 per test cancellation (excl. GST)', 'GLCANCL1', 550.00, 'fixed', 1, 31, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(32, 'Test Cancellation Fee — After Hours 2', 'Fee applies to testing cancelled after 3pm the previous workday, for tests scheduled during After Hours 2 (Saturday 6am–12pm).', 'Applies when after-hours 2 testing is cancelled after 3pm on the previous working day.', 'Saturday 6am–12pm test cancelled after 3pm previous day → Cancellation Fee $440.00 per test cancellation (excl. GST)', 'GLCANCL2', 440.00, 'fixed', 1, 32, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(33, 'Test Cancellation Fee — After Hours 3 / Sundays & Public Holidays', 'Fee applies to testing cancelled after 3pm the previous workday, for tests scheduled during After Hours 3 (Sundays and Public Holidays).', 'Applies when Sunday or public holiday testing is cancelled after 3pm on the previous working day.', 'Sunday test cancelled after 3pm previous day → Cancellation Fee $550.00 per test cancellation (excl. GST)', 'GLCANCL3', 550.00, 'fixed', 1, 33, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(34, 'Operational Fee — Laboratory Outside Normal Hours', 'Fee applies where the laboratory is required to operate outside normal working hours to process or supervise testing. Applies across all after-hours tiers.', 'Applies when the laboratory must operate outside normal working hours (Mon–Fri 6am–4pm) to support any after-hours testing request.', 'After-hours testing requiring lab operation → Operational Fee $930.00 per request (excl. GST)', 'GLOPEN', 930.00, 'fixed', 1, 34, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(35, 'Re-issue of Test Report', 'Fee for the re-issue of a previously issued test report at client request.', 'Applies when a client requests a re-issued copy of any previously issued test report.', 'Client requests re-issued test report → Fee $27.00 per re-issued report (excl. GST)', 'GLREISS', 27.00, 'fixed', 1, 35, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL),
(36, 'Trial Mix Fee', 'Fee for trial mix testing. Associated concrete tests are not included in this fee. Price is on application — contact your Account Manager for a quote.', 'Applies when a trial mix is requested prior to a project pour. Associated concrete testing costs are charged separately.', 'Trial mix requested → Price on Application (POA) — contact Account Manager for pricing (excl. GST)', 'GLTRIAL', 0.00, 'fixed', 1, 36, '2026-03-09 20:22:09', '2026-03-09 20:22:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `status` enum('Active','In-Active') DEFAULT 'Active',
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `long` decimal(11,8) DEFAULT NULL,
  `delivery_radius` int(11) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `client_public_id` varchar(255) DEFAULT NULL,
  `profile_image` text DEFAULT NULL,
  `isDeleted` tinyint(1) DEFAULT 0,
  `delivery_address` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `delivery_zones` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `company_id`, `status`, `company_name`, `contact_name`, `contact_number`, `location`, `lat`, `long`, `delivery_radius`, `shipping_address`, `billing_address`, `client_public_id`, `profile_image`, `isDeleted`, `delivery_address`, `email_verified_at`, `password`, `notes`, `delivery_zones`, `remember_token`, `stripe_customer_id`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@materialconnect.com', 'admin', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '$2y$12$qx6Hobgvpz5Jb4MpmHVrmOGhOF.eiWH0.jjkgnJacWSce7srJ5CwO', NULL, NULL, NULL, NULL, '2025-10-06 13:05:46', '2026-03-01 21:57:29'),
(13, 'client', 'client1@gmail.com', 'client', 3, 'Active', NULL, 'Client', '12321312321', NULL, NULL, NULL, NULL, 'shipping address', 'billing address', 'MC-440', 'storage/profile_images/O3lZXFahNHDHFGZNuQ5q7LhVwR2rUhtHqvAzKKUh.webp', 0, NULL, NULL, '$2y$12$qx6Hobgvpz5Jb4MpmHVrmOGhOF.eiWH0.jjkgnJacWSce7srJ5CwO', NULL, NULL, NULL, NULL, '2025-10-07 14:53:59', '2026-03-01 21:57:48'),
(15, 'client 2', 'client2@gmail.com', 'client', 4, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-223', NULL, 1, NULL, NULL, '$2y$12$67lXOwUUCvgd39nzGcTqcODKfFvvSipp6r7dyhc9PUY87HXxIb4Cq', NULL, NULL, NULL, NULL, '2025-10-07 15:42:52', '2026-01-20 04:37:33'),
(23, 'Accountant', 'accountant@materialconnect.com', 'accountant', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-944', 'storage/profile_images/jRDq7Bz9t54bqbx6CC3T3K7dF2T9SRd2SLolNKDl.jpg', 0, NULL, NULL, '$2y$12$pQwdsRZ/nT.dKdKy3frfcOo2u8sCHKggmbh8HK9ToELg9O1e.g0ya', 'Accountant', NULL, NULL, NULL, '2025-11-27 06:54:04', '2026-03-01 22:02:23'),
(24, 'Support', 'support@materialconnect.com', 'support', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-509', 'storage/profile_images/OI2FC0DOUAcR2pysx9wRxnhQrOz8GF7IrPE1t3ww.jpg', 0, NULL, NULL, '$2y$12$XuDz1X6XTTiI8AEFKfZbVOJtoWUmOjKI00Yy8/VUGYuYvfTeGCoYK', 'Support Person', NULL, NULL, NULL, '2025-11-27 06:58:52', '2026-03-01 21:59:06'),
(57, 'Rindean Quarry', 'info@rindean.com.au', 'supplier', 1, 'Active', 'Rindean Quarry', 'Alice Calleija', '0476 633 388', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$4KfjmzZaoZwDyDxgfaxL1uf7eBkUaJqg4BKXs2Hg1ZJF1TVYxXzIO', NULL, '\"[{\\\"address\\\":\\\"620 Wisemans Ferry Rd Somersby, NSW 2250 AUS\\\",\\\"lat\\\":-33.3747101,\\\"long\\\":151.2922461,\\\"radius\\\":\\\"83.4\\\"}]\"', NULL, NULL, '2026-01-09 00:35:27', '2026-01-09 01:20:15'),
(58, 'Walker Quarries', 'sales@walkerquarries.com.au', 'supplier', 2, 'Active', 'Walker Quarries', 'Tim Mcloughlin', '0408 364 810', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$NikAcRk1dJ.GwQohT1gs.ezddVlGfQB5xcdVGFFni8FlH8z5bypfO', NULL, '\"[{\\\"address\\\":\\\"963 Great Western Highway, Marrangaroo NSW 2790\\\",\\\"lat\\\":-33.4357837,\\\"long\\\":150.0776442,\\\"radius\\\":\\\"150\\\"}]\"', NULL, NULL, '2026-01-09 00:35:27', '2026-01-09 01:20:15'),
(59, 'WSC Quarries', 'info@wscquarries.com.au | taynew@wsc.com.au | accounts@wsc.com.au', 'supplier', 3, 'Active', 'WSC Quarries', 'Kevin Dowling | Tayne Wilson', '0422722622| Office: 02 4761 6161', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$qx6Hobgvpz5Jb4MpmHVrmOGhOF.eiWH0.jjkgnJacWSce7srJ5CwO', NULL, '\"[{\\\"address\\\":\\\"Marulan NSW 2579, Australia\\\",\\\"lat\\\":-34.716656,\\\"long\\\":150.0005338,\\\"radius\\\":\\\"165\\\"}]\"', NULL, NULL, '2026-01-09 00:35:27', '2026-01-09 01:20:16'),
(60, 'Gow Street Recycling Centre', 'info@gsrc.com.au', 'supplier', 4, 'Active', 'Gow Street Recycling Centre', 'Zoe Muscat', '(02) 9709 2773', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$OwNxm5jALK5.rTUm2h7DKevTyFJnNqMyOPYiaGEmKKl1lXviepHOS', NULL, '\"[{\\\"address\\\":\\\"81 Gow Street, Padstow, NSW, 2211\\\",\\\"lat\\\":-33.9376771,\\\"long\\\":151.0319892,\\\"radius\\\":\\\"25\\\"}]\"', NULL, NULL, '2026-01-09 00:35:28', '2026-01-09 01:20:17'),
(61, 'BC Sands', 'sales@bcsands.com.au', 'supplier', 5, 'Active', 'BC Sands', 'Internal Sales', '(02) 8543 3401', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$IsJnW8NfqZa29xAyepVgzeTfIJNBvXRFKbM39IPQatNHH1HNw/E9W', NULL, '\"[{\\\"address\\\":\\\"26 Atkinson Rd, Taren Point\\\",\\\"lat\\\":-34.0240897,\\\"long\\\":151.1295872,\\\"radius\\\":\\\"26\\\"}]\"', NULL, NULL, '2026-01-09 00:35:28', '2026-01-09 01:20:18'),
(62, 'Turtle Nursery & Landscape Supplies', 'turtle@turtlenursery.com.au', 'supplier', 6, 'Active', 'Turtle Nursery & Landscape Supplies', 'Internal Sales', '02 9629 2299 / 02 4574 3299', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$.nysSbKjnfwOoN8PQl1Wa.q3XDHUAu./xvgAHakqfF2mXwfAPSHMS', NULL, '\"[{\\\"address\\\":\\\"Windsor &, Rouse Rd, Rouse Hill NSW 2155, Australia\\\",\\\"lat\\\":-33.6840372,\\\"long\\\":150.9168772,\\\"radius\\\":\\\"44\\\"}]\"', NULL, NULL, '2026-01-09 00:35:28', '2026-01-09 01:20:19'),
(63, 'Elite Group', 'sales@elitegroup.sydney', 'supplier', 7, 'Active', 'Elite Group', 'Internal Sales', '1300 935 483', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$TSXMZAbL5p/TC.ldk7zbNO3ZP.nq/FzAiRvXMBHfm1m4iZpI5.l5G', NULL, NULL, NULL, NULL, '2026-01-09 00:35:29', '2026-01-09 00:51:18'),
(64, 'Ace Landscapes', 'sales@acelandscapes.com.au', 'supplier', 8, 'Active', 'Ace Landscapes', 'Internal Sales', '(02) 9450 2215', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$gOF8BswiTxsvIfoNbfHLBOAj2YAnnEHtmCgUejO.t6/7Eo8bT7cqi', NULL, '\"[{\\\"address\\\":\\\"190 Forest Way, Belrose NSW 2085, Australia\\\",\\\"lat\\\":-33.7197593,\\\"long\\\":151.2160247,\\\"radius\\\":\\\"22\\\"}]\"', NULL, NULL, '2026-01-09 00:35:29', '2026-01-09 01:20:21'),
(65, 'Kincumber Sand & Soil', 'info@kincumbersand.com.au', 'supplier', 9, 'Active', 'Kincumber Sand & Soil', 'Internal Sales', '(02) 4368 1252', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$bmPulg829BqaaGOHAGw6zeXIU4U2nG0albXePJ7x2JJb0pLK8fgaK', NULL, '\"[{\\\"address\\\":\\\"4 Kerta Rd, Kincumber NSW 2251, Australia\\\",\\\"lat\\\":-33.4740916,\\\"long\\\":151.3925974,\\\"radius\\\":\\\"96\\\"}]\"', NULL, NULL, '2026-01-09 00:35:29', '2026-01-09 01:20:22'),
(66, 'Four Seasons Nursery', 'sales@fourseasonsnursery.com.au', 'supplier', 10, 'Active', 'Four Seasons Nursery', 'Internal Sales', '02 9450 1606', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$UqjjsPng4elHoO/Y6qHs8us.WV55Y7r7FspmA8MPnw/LiNBQDO4wO', NULL, '\"[{\\\"address\\\":\\\"4 Harford St, Jamisontown, NSW, 2750\\\",\\\"lat\\\":-33.7640495,\\\"long\\\":150.6861657,\\\"radius\\\":\\\"55\\\"},{\\\"address\\\":\\\"1\\\\\\/200 Forest Way, Belrose NSW 2085, Australia\\\",\\\"lat\\\":-33.7178931,\\\"long\\\":151.2155262,\\\"radius\\\":\\\"22\\\"}]\"', NULL, NULL, '2026-01-09 00:35:29', '2026-01-09 01:20:21'),
(67, 'Northshore Sand & Cement', 'admin@northshorecementandsand.com.au', 'supplier', 11, 'Active', 'Northshore Sand & Cement', 'Internal Sales', '(02) 9948 3905', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$wQzeZf55VYT2DMoBk0Z.F.67fz2a42FBDm8.GNc0ZLBfBFquS8k52', NULL, '\"[{\\\"address\\\":\\\"20 Roseberry Street, Balgowlah, NSW 2093\\\",\\\"lat\\\":-33.7883669,\\\"long\\\":151.26787,\\\"radius\\\":\\\"25\\\"}]\"', NULL, NULL, '2026-01-09 00:35:30', '2026-01-09 01:20:23'),
(68, 'Darwin Block Company (Humpty Doo)', 'darwinblockcompany@bigpond.com', 'supplier', 12, 'Active', 'Darwin Block Company (Humpty Doo)', 'Tony Furey', '0417 913 868', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$KUOJwL5bJfG/ijgtBEY7ROlHYw/elxy1k9Mbxw8ecz7.PxdUdxksa', NULL, '\"[{\\\"address\\\":\\\"15 Spencely Rd, Humpty Doo NT 0836, Australia\\\",\\\"lat\\\":-12.585925,\\\"long\\\":131.0966195,\\\"radius\\\":\\\"39\\\"}]\"', NULL, NULL, '2026-01-09 00:35:30', '2026-01-09 01:20:24'),
(69, 'Paradise Group – Landscape Supplies & Nursery', 'info@paradisegroupnt.com.au', 'supplier', 13, 'Active', 'Paradise Group – Landscape Supplies & Nursery', 'Internal Sales', '(08) 8947 2447', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$dnY/Z9ua8462A2IlMrGDdOm6FH32TUJvoajP1hGNAHiS3FcJWU5vS', NULL, '\"[{\\\"address\\\":\\\"Knuckey Lagoon, Northern Territory, Australia\\\",\\\"lat\\\":-12.4216524,\\\"long\\\":130.9419063,\\\"radius\\\":\\\"16\\\"}]\"', NULL, NULL, '2026-01-09 00:35:30', '2026-01-09 01:20:25'),
(70, 'Bunnings Warehouse', 'PalmerstonWH@bunnings.com.au', 'supplier', 14, 'Active', 'Bunnings Warehouse', 'Internal Sales', '(08) 7972 5900', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$Z/vsOf/SRsgxixA8jl26R.gwy19E.9hyHrsyuDVKByC9z32PQiazi', NULL, '\"[{\\\"address\\\":\\\"Cnr Stuart Highway &, Tulagi Rd, Palmerston City NT 0830, Australia\\\",\\\"lat\\\":-12.4709288,\\\"long\\\":130.9897934,\\\"radius\\\":\\\"20\\\"}]\"', NULL, NULL, '2026-01-09 00:35:30', '2026-01-09 01:20:25'),
(71, 'Rural Garden Supplies Humpty Doo', 'office@ruralgardensupplieshumptydoo.com', 'supplier', 15, 'Active', 'Rural Garden Supplies Humpty Doo', 'Internal Sales', '(08) 8988 5880', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$Ix3MGsJkm7fbul9fAky8nevgALRKyOcmCIcIytxwyEFYTHcea1qrO', NULL, '\"[{\\\"address\\\":\\\"Humpty Doo, Northern Territory, Australia\\\",\\\"lat\\\":-12.572862,\\\"long\\\":131.1008958,\\\"radius\\\":\\\"40\\\"}]\"', NULL, NULL, '2026-01-09 00:35:31', '2026-01-09 01:20:27'),
(72, 'Shuker Farm Mulch Hay (Darwin River)', 'info@shukerfarm.com.au', 'supplier', 16, 'Active', 'Shuker Farm Mulch Hay (Darwin River)', 'Internal Sales', '(08) 8988 6266', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$qCEPr0NdUhLXk0LYPDgoTe/SoCW/7hhjUmb8RjW0HGNhSBcMiwT8e', NULL, '\"[{\\\"address\\\":\\\"1055 Mira Road South, Darwin River, N.T. 0841\\\",\\\"lat\\\":-12.8560254,\\\"long\\\":130.9379779,\\\"radius\\\":\\\"69\\\"}]\"', NULL, NULL, '2026-01-09 00:35:31', '2026-01-09 01:20:27'),
(73, 'NT Hauliers', 'ops@ttlnt.com', 'supplier', 17, 'Active', 'NT Hauliers', 'Internal Sales', '08 8947 0300', 'Darwin River Rd, Darwin River NT 0841, Australia', NULL, NULL, 200, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$KBqyYZw0FqDirdT8ppV0LOiBlb7ZtbItcxWFOyCDxqPB037UJ5EuS', NULL, '\"[{\\\"address\\\":\\\"GPO Box 4511, Darwin NT 0801\\\",\\\"lat\\\":-12.4637333,\\\"long\\\":130.8444446,\\\"radius\\\":\\\"13\\\"}]\"', NULL, NULL, '2026-01-09 00:35:31', '2026-01-11 03:47:46'),
(74, 'Peel Resource Recovery – Pinjarra', 'admin@peelresource.com.au', 'supplier', 18, 'Active', 'Peel Resource Recovery – Pinjarra', 'Josie Brocksopp', '(08) 9531 3111', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$2fkbtLiEU5jB.jI57Ig6WuJeKLlFWj/AS/xApv3N6lT4L96prGAvi', NULL, NULL, NULL, NULL, '2026-01-09 00:35:32', '2026-01-09 00:51:22'),
(75, 'Capital Recycling', 'josh@capitalperth.com.au', 'supplier', 50, 'Active', 'Capital Recycling', 'Internal Sales', '9279 4599 / 0458 708 732', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$sW.7QVGZ1g1P4p6EoFbKd.bo2a5/UTU.BBfPbNxUK6FYM2MQ/B9hq', NULL, '\"[{\\\"address\\\":\\\"39 Briggs St, Welshpool WA 6106\\\",\\\"lat\\\":-31.9886675,\\\"long\\\":115.9228571,\\\"radius\\\":\\\"17\\\"},{\\\"address\\\":\\\"119 McLaughlan Rd, Postans WA 6167\\\",\\\"lat\\\":-32.2188731,\\\"long\\\":115.826769,\\\"radius\\\":\\\"25\\\"}]\"', NULL, NULL, '2026-01-09 00:35:32', '2026-01-09 01:23:15'),
(76, 'HALS Tasmania (Horticultural & Landscape Supplies)', 'info@hals.com.au', 'supplier', 21, 'Active', 'HALS Tasmania (Horticultural & Landscape Supplies)', 'Internal Sales', '03 6263 4688', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$S8DrTmYRks7AQVUGVqQ.eutJxR8dD0vMgppW3tVojU4GZ1RMmNHlm', NULL, '\"[{\\\"address\\\":\\\"45 Crooked Billet Drive, Brighton TAS\\\",\\\"lat\\\":-42.7172639,\\\"long\\\":147.2314251,\\\"radius\\\":\\\"24\\\"}]\"', NULL, NULL, '2026-01-09 00:35:32', '2026-01-09 01:20:29'),
(77, 'Best Mix Garden Supplies', 'hayley@bestmixgarden.com.au', 'supplier', 22, 'Active', 'Best Mix Garden Supplies', 'Internal Sales', '03 6244 6333', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$7phr5PRn41tHxMdBy.fCDuc8Gx3.8zMybG3COymJuyECadW3t1saS', NULL, '\"[{\\\"address\\\":\\\"124 Mornington Road, TAS 7018\\\",\\\"lat\\\":-42.8617347,\\\"long\\\":147.4059504,\\\"radius\\\":\\\"10\\\"}]\"', NULL, NULL, '2026-01-09 00:35:33', '2026-01-09 01:20:29'),
(78, 'Goods Landscaping & Water Deliveries', 'goodswaterdelivery@gmail.com', 'supplier', 23, 'Active', 'Goods Landscaping & Water Deliveries', 'Internal Sales', '419124813', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$XVZr91UCMKtsS2zxmtdZxeVKacdhUJ4dy7I78P83zWDmALDnvJHIG', NULL, '\"[{\\\"address\\\":\\\"26 Lewisham Scenic Dr, Lewisham TAS 7173, Australia\\\",\\\"lat\\\":-42.8220897,\\\"long\\\":147.60935,\\\"radius\\\":\\\"33\\\"}]\"', NULL, NULL, '2026-01-09 00:35:33', '2026-01-09 01:20:30'),
(79, 'Stoneman\'s Garden Centre (Glenorchy)', 'sales@stonemans.com.au', 'supplier', 24, 'Active', 'Stoneman\'s Garden Centre (Glenorchy)', 'Internal Sales', '03 6273 0611', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$xtavG/qwULItqAJxi11.B.2ncIOf7HGen9SLaqXjpfoXDBDRdEBC6', NULL, '\"[{\\\"address\\\":\\\"94 Grove Rd, Glenorchy TAS 7010, Australia\\\",\\\"lat\\\":-42.8273854,\\\"long\\\":147.273494,\\\"radius\\\":\\\"10\\\"}]\"', NULL, NULL, '2026-01-09 00:35:33', '2026-01-09 01:20:31'),
(80, 'TasMulch (Longford)', 'info@tasmulch.com.au', 'supplier', 25, 'Active', 'TasMulch (Longford)', 'Internal Sales', '(03) 6391 2382', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$NR.vFPsHHDnDN/eGWsCYEu9JFmzQ5DLCRKXloZpUwwwKEuyiTss36', NULL, '\"[{\\\"address\\\":\\\"73 Wellington St, Longford TAS 7301, Australia\\\",\\\"lat\\\":-41.5951217,\\\"long\\\":147.1227161,\\\"radius\\\":\\\"188\\\"}]\"', NULL, NULL, '2026-01-09 00:35:34', '2026-01-09 01:20:31'),
(81, 'Glebe Gardens Landscape Supplies (Launceston)', 'glebegardenslaunceston@hotmail.com', 'supplier', 26, 'Active', 'Glebe Gardens Landscape Supplies (Launceston)', 'Internal Sales', '(03) 6334 5335', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$l3e3YkGR25N5sDQjJRDZauImw2AlaCW5Eqikyagrl5X6Sci1R3ctu', NULL, '\"[{\\\"address\\\":\\\"166 Henry St, Launceston TAS 7250, Australia\\\",\\\"lat\\\":-41.425208,\\\"long\\\":147.1538521,\\\"radius\\\":\\\"204\\\"}]\"', NULL, NULL, '2026-01-09 00:35:34', '2026-01-09 01:20:32'),
(82, 'Edwards Landscaping Supplies (Wynyard)', 'admin@elstas.com.au', 'supplier', 27, 'Active', 'Edwards Landscaping Supplies (Wynyard)', 'Internal Sales', '(03) 6442 1747', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$TRTTaVummhzW1VqR8Ky/sOVE63/9dhWbCnr45Le2jy41QDHYHQK6C', NULL, '\"[{\\\"address\\\":\\\"6 Lewis St, Wynyard TAS 7325, Australia\\\",\\\"lat\\\":-40.9923435,\\\"long\\\":145.7205766,\\\"radius\\\":\\\"345\\\"}]\"', NULL, NULL, '2026-01-09 00:35:34', '2026-01-09 01:20:33'),
(83, 'Castella Quarries', 'ross.crunden@castellaquarries.com.au', 'supplier', 28, 'Active', 'Castella Quarries', 'Ross Crunden', 'T: 5962 9080\n M: 0459 777 520', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$A0KP.6PVRK2ED3qRZbgU9uaf5SUReWXeZ1Ux6ikt1GrD1IBfC/cq2', NULL, '\"[{\\\"address\\\":\\\"2900 Melba Hwy, Castella VIC 3777, Australia\\\",\\\"lat\\\":-37.5045359,\\\"long\\\":145.4251589,\\\"radius\\\":\\\"74.3\\\"}]\"', NULL, NULL, '2026-01-09 00:35:34', '2026-01-09 01:20:48'),
(84, 'Cootes Quarry', 'paul@cootesquarryproducts.com.au', 'supplier', 29, 'Active', 'Cootes Quarry', 'Leigh Cootes', '(03) 5940 8851', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$uJjMcpAuyLmr7Nnc3K5K.ecyLBNQDYzxc5J0wl5CyOuKb2egtOx7i', NULL, '\"[{\\\"address\\\":\\\"50 Mary St, Pakenham VIC 3810, Australia\\\",\\\"lat\\\":-38.0617831,\\\"long\\\":145.4609872,\\\"radius\\\":\\\"60.3\\\"}]\"', NULL, NULL, '2026-01-09 00:35:35', '2026-01-09 01:20:48'),
(85, 'Langwarrin Quarries', 'jessie@langwarrinquarries.com.au', 'supplier', 30, 'Active', 'Langwarrin Quarries', 'Jessie Ellingsen', '0487 588 390', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$27QSGIE8cBg6rrwy9jdD5OO4ZbJpELdLCjYITWwuzZNpEbr8JT.SK', NULL, '\"[{\\\"address\\\":\\\"Gate 1\\\\\\/165 Quarry Rd, Langwarrin VIC 3910, Australia\\\",\\\"lat\\\":-38.1386552,\\\"long\\\":145.1939657,\\\"radius\\\":\\\"57.2\\\"}]\"', NULL, NULL, '2026-01-09 00:35:35', '2026-01-09 01:20:49'),
(86, 'Mt Shadwell Quarry', 'darby.lee@moyne.vic.gov.au', 'supplier', 31, 'Active', 'Mt Shadwell Quarry', 'Darby Lee', '03 5568 0506', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$PIBmSxz.3rYG4kCvUjtsGuUimcEWqZqI3DdHHRZHH6HOVjC0LJ48G', NULL, '\"[{\\\"address\\\":\\\"104 Mortlake-Ararat Rd, Mortlake VIC 3272, Australia\\\",\\\"lat\\\":-38.0515935,\\\"long\\\":142.8172035,\\\"radius\\\":\\\"220\\\"}]\"', NULL, NULL, '2026-01-09 00:35:35', '2026-01-09 01:20:50'),
(87, 'Werribee Sand-Soil & Mini Mix Concrete', 'info@werribeesandsoilminimix.com.au', 'supplier', 32, 'Active', 'Werribee Sand-Soil & Mini Mix Concrete', 'Internal Sales', '03 9360 9277', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$S2y46mESloa9UmVbRgd7UOb4ZeuBAb0L2pRkiPGZ/rLnyPXhfBs2W', NULL, '\"[{\\\"address\\\":\\\"379-385 Old Geelong Rd, Hoppers Crossing VIC 3029\\\",\\\"lat\\\":-37.8703713,\\\"long\\\":144.7269593,\\\"radius\\\":\\\"27\\\"}]\"', NULL, NULL, '2026-01-09 00:35:36', '2026-01-09 01:20:50'),
(88, 'Melbourne\'s Cheapest Soils', 'mcsgroupvic@gmail.com', 'supplier', 33, 'Active', 'Melbourne\'s Cheapest Soils', 'Internal Sales', '12345678', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$smyF0jqtfKQX5F2Ms1lZaugq2fWw4dK8KOwG4.l35sgHvtBUJ5Gh6', NULL, '\"[{\\\"address\\\":\\\"Bonnie Brook, Victoria 3335, Australia\\\",\\\"lat\\\":-37.6915046,\\\"long\\\":144.6595961,\\\"radius\\\":\\\"39.8\\\"}]\"', NULL, NULL, '2026-01-09 00:35:36', '2026-01-09 01:20:51'),
(89, 'Victorian Bluestone Quarries', 'sales@vicbluestone.com.au', 'supplier', 34, 'Active', 'Victorian Bluestone Quarries', 'Tina Skliros', '03 9314 4700', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$6bGsTHk1k8PymBEzeiydm.tUct3PCK70/wGxzwFTTxoTm3qTwM8K.', NULL, '\"[{\\\"address\\\":\\\"410-422 Francis St, Brooklyn VIC 3012, Australia\\\",\\\"lat\\\":-37.8172265,\\\"long\\\":144.8572268,\\\"radius\\\":\\\"11.7\\\"}]\"', NULL, NULL, '2026-01-09 00:35:36', '2026-01-09 01:20:52'),
(90, 'Hy-Tec Concrete & Aggregates', 'info@hy-tec.com.au', 'supplier', 35, 'Active', 'Hy-Tec Concrete & Aggregates', 'Marco Keith', '0408 225 921', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$mrah5WabWsxbTA3x1a9.eeZSI2yzo4mSZ0tBM2MYVF5NG8KukidyC', NULL, '\"[{\\\"address\\\":\\\"Pier 32 South Wharf, 469-591 Lorimer St, Port Melbourne VIC 3207, Australia\\\",\\\"lat\\\":-37.8214104,\\\"long\\\":144.9143918,\\\"radius\\\":\\\"5.3\\\"},{\\\"address\\\":\\\"257-265 Dohertys Rd, Laverton North VIC 3026, Australia\\\",\\\"lat\\\":-37.8267608,\\\"long\\\":144.7677354,\\\"radius\\\":\\\"19.3\\\"},{\\\"address\\\":\\\"221 Northbourne Rd, Campbellfield VIC 3061, Australia\\\",\\\"lat\\\":-37.6486912,\\\"long\\\":144.9553221,\\\"radius\\\":\\\"30.2\\\"},{\\\"address\\\":\\\"16 Baldwin Dr, Somerton VIC 3062, Australia\\\",\\\"lat\\\":-37.6258525,\\\"long\\\":144.9461167,\\\"radius\\\":\\\"32.5\\\"},{\\\"address\\\":\\\"44 Thornton Cres, Mitcham VIC 3132, Australia\\\",\\\"lat\\\":-37.8202857,\\\"long\\\":145.1861025,\\\"radius\\\":\\\"26.6\\\"},{\\\"address\\\":\\\"199 Liverpool Rd, Kilsyth VIC 3137, Australia\\\",\\\"lat\\\":-37.819934,\\\"long\\\":145.3202245,\\\"radius\\\":\\\"39.2\\\"},{\\\"address\\\":\\\"Cnr South Gippsland Hwy and, Abbotts Rd, Dandenong VIC 3175, Australia\\\",\\\"lat\\\":-38.0344079,\\\"long\\\":145.2452498,\\\"radius\\\":\\\"42.7\\\"},{\\\"address\\\":\\\"60 Gregg St, Pinkenba QLD 4008, Australia\\\",\\\"lat\\\":-27.4263388,\\\"long\\\":153.1120715,\\\"radius\\\":\\\"11.7\\\"},{\\\"address\\\":\\\"42-48 Fishermans Rd, Kuluin QLD 4558, Australia\\\",\\\"lat\\\":-26.6527082,\\\"long\\\":153.0563578,\\\"radius\\\":\\\"103\\\"},{\\\"address\\\":\\\"27 Production Ave, Warana QLD 4575, Australia\\\",\\\"lat\\\":-26.7274636,\\\"long\\\":153.1246606,\\\"radius\\\":\\\"94.2\\\"},{\\\"address\\\":\\\"5 Beerwah Parade, Beerwah QLD 4519, Australia\\\",\\\"lat\\\":-26.8527783,\\\"long\\\":152.9588026,\\\"radius\\\":\\\"72.6\\\"},{\\\"address\\\":\\\"1865 D\'Aguilar Hwy, Wamuran QLD 4512, Australia\\\",\\\"lat\\\":-26.995336,\\\"long\\\":152.8251839,\\\"radius\\\":\\\"72.9\\\"},{\\\"address\\\":\\\"664 Old Gympie Rd, Narangba QLD 4504, Australia\\\",\\\"lat\\\":-27.1920349,\\\"long\\\":152.9851103,\\\"radius\\\":\\\"36.5\\\"},{\\\"address\\\":\\\"143 North St, Harlaxton QLD 4350, Australia\\\",\\\"lat\\\":-27.5400134,\\\"long\\\":151.9448407,\\\"radius\\\":\\\"134\\\"},{\\\"address\\\":\\\"1020 Atkinsons Dam Rd, Coominya QLD 4311, Australia\\\",\\\"lat\\\":-27.3999393,\\\"long\\\":152.3926703,\\\"radius\\\":\\\"95.9\\\"},{\\\"address\\\":\\\"51 Noblevale Way, Swanbank QLD 4306, Australia\\\",\\\"lat\\\":-27.6348688,\\\"long\\\":152.8132662,\\\"radius\\\":\\\"42.1\\\"},{\\\"address\\\":\\\"61 Radius Dr, Larapinta QLD 4110, Australia\\\",\\\"lat\\\":-27.6369058,\\\"long\\\":153.0125377,\\\"radius\\\":\\\"31.8\\\"},{\\\"address\\\":\\\"34 Computer Rd, Yatala QLD 4207, Australia\\\",\\\"lat\\\":-27.7595428,\\\"long\\\":153.2327674,\\\"radius\\\":\\\"42.3\\\"},{\\\"address\\\":\\\"13 Bee Ct, Burleigh Heads QLD 4220, Australia\\\",\\\"lat\\\":-28.1034324,\\\"long\\\":153.4175081,\\\"radius\\\":\\\"87.4\\\"}]\"', NULL, NULL, '2026-01-09 00:35:37', '2026-01-09 01:20:47'),
(91, 'Boral West Burleigh Quarry', 'Marie.low@Boral.com.au', 'supplier', 36, 'Active', 'Boral West Burleigh Quarry', 'Marie Low', '0438 161 118', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$Gy.R9Se05xzwcRR2pBqO5.s4KxM4Ld/qcVfg12A9Ml55GnoOwMkUe', NULL, '\"[{\\\"address\\\":\\\"Burleigh Rd, Burleigh Heads QLD 4220, Australia\\\",\\\"lat\\\":-28.1105641,\\\"long\\\":153.4414334,\\\"radius\\\":\\\"88\\\"}]\"', NULL, NULL, '2026-01-09 00:35:37', '2026-01-09 01:20:52'),
(92, 'NuGrow', 'KaneHauser@nugrow.com.au', 'supplier', 37, 'Active', 'NuGrow', 'Kane Hauser', '0407 582 697', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$X/OS/qUxF0UAJd/so5hunOGkIN2gOLBRtOCoe10AuTpxbEFd9qJTG', NULL, '\"[{\\\"address\\\":\\\"Lot 3 Memorial Dr, Ipswich QLD 4306\\\",\\\"lat\\\":-27.6633581,\\\"long\\\":152.8152596,\\\"radius\\\":\\\"45.7\\\"},{\\\"address\\\":\\\"100 Zipfs Road, Alberton QLD 4207\\\",\\\"lat\\\":-27.7114336,\\\"long\\\":153.2677817,\\\"radius\\\":\\\"42.8\\\"}]\"', NULL, NULL, '2026-01-09 00:35:37', '2026-01-09 01:20:54'),
(93, 'Nuway Landscape Supplies Pavers & Walls', 'info@nuway.com.au', 'supplier', 38, 'Active', 'Nuway Landscape Supplies Pavers & Walls', 'Internal Sales', '61 7 3808 8442', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$I2m4yVrUCn6bAFjNGEX3zuRXQIIfkkpqtmJ1eRgvYnBfSCsUI.pam', NULL, '\"[{\\\"address\\\":\\\"2630 Old Cleveland Rd, Chandler QLD 4155\\\",\\\"lat\\\":-27.5089781,\\\"long\\\":153.1684429,\\\"radius\\\":\\\"17.7\\\"},{\\\"address\\\":\\\"488 Loganlea Rd, Slacks Creek QLD 4127\\\",\\\"lat\\\":-27.6502677,\\\"long\\\":153.144311,\\\"radius\\\":\\\"26.8\\\"},{\\\"address\\\":\\\"650 Southport Nerang Rd, Ashmore QLD 4214\\\",\\\"lat\\\":-27.9857762,\\\"long\\\":153.3628396,\\\"radius\\\":\\\"70.9\\\"},{\\\"address\\\":\\\"249 Cleveland - Redland Bay Rd, Thornlands QLD 4164\\\",\\\"lat\\\":-27.5689302,\\\"long\\\":153.2738481,\\\"radius\\\":\\\"34.1\\\"},{\\\"address\\\":\\\"1823 Anzac Ave, Mango Hill QLD 4509\\\",\\\"lat\\\":-27.2387546,\\\"long\\\":153.0284172,\\\"radius\\\":\\\"41.3\\\"},{\\\"address\\\":\\\"12 Jennifer St, Seventeen Mile Rocks QLD 4034\\\",\\\"lat\\\":-27.5420932,\\\"long\\\":152.9568768,\\\"radius\\\":\\\"16.1\\\"},{\\\"address\\\":\\\"2\\\\\\/16 Stapylton Road(Cnr of, Johnson Rd, Forestdale QLD 4118\\\",\\\"lat\\\":-27.6492062,\\\"long\\\":152.9844067,\\\"radius\\\":\\\"30.8\\\"},{\\\"address\\\":\\\"8a\\\\\\/1 Commerce Pl, Burpengary QLD 4505, Australia\\\",\\\"lat\\\":-27.1565467,\\\"long\\\":152.9727338,\\\"radius\\\":\\\"51.3\\\"},{\\\"address\\\":\\\"93 S Pine Rd, Brendale QLD 4500\\\",\\\"lat\\\":-27.3196517,\\\"long\\\":152.9871948,\\\"radius\\\":\\\"24.8\\\"},{\\\"address\\\":\\\"7 Eggersdorf Rd, Ormeau QLD 4208\\\",\\\"lat\\\":-27.7856937,\\\"long\\\":153.2582774,\\\"radius\\\":\\\"44.3\\\"},{\\\"address\\\":\\\"168 Crosby Hill Rd, Tanawha QLD 4556\\\",\\\"lat\\\":-26.7076914,\\\"long\\\":153.0458492,\\\"radius\\\":\\\"102\\\"}]\"', NULL, NULL, '2026-01-09 00:35:37', '2026-01-09 01:21:02'),
(94, 'Western Landscape Supplies', 'Online@westernlandscape.com.au', 'supplier', 39, 'Active', 'Western Landscape Supplies', 'Internal Sales', '(07) 3372 8380', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$KCCR7f2YZ7j7ctPp1bEeV.AmsHnNtbZUk0bP9u2WU2n..f0C9lGFG', NULL, '\"[{\\\"address\\\":\\\"72 Bowhill Rd, Willawong QLD Australia 4110\\\",\\\"lat\\\":-27.5868176,\\\"long\\\":153.0074986,\\\"radius\\\":\\\"18.8\\\"}]\"', NULL, NULL, '2026-01-09 00:35:38', '2026-01-09 01:21:03'),
(95, 'Redland Soil, Sand & Gravel', 'redlandsoils@outlook.com', 'supplier', 40, 'Active', 'Redland Soil, Sand & Gravel', 'Internal Sales', '0408 195 952', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$fTEoCp8DYvnHqXa3HeXBtONH4Cea3uUT.xNw6tkQuQCAVLm1sTnK.', NULL, '\"[{\\\"address\\\":\\\"Capalaba, Queensland 4157, Australia\\\",\\\"lat\\\":-27.5354669,\\\"long\\\":153.1971252,\\\"radius\\\":\\\"22.6\\\"}]\"', NULL, NULL, '2026-01-09 00:35:38', '2026-01-09 01:21:04'),
(96, 'Logan Soils & Landscape Supplies', 'sales@logansoils.com.au', 'supplier', 41, 'Active', 'Logan Soils & Landscape Supplies', 'Internal Sales', '0408 292 951', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$fZut0t3gk4/cRLEdbcT8se3Wlp9bdx/8nup3T0PHEKfFKWFWow/8C', NULL, '\"[{\\\"address\\\":\\\"Bayside Soils, 557 Mount Cotton Rd, Sheldon QLD 4157, Australia\\\",\\\"lat\\\":-27.56645,\\\"long\\\":153.2086839,\\\"radius\\\":\\\"26.2\\\"}]\"', NULL, NULL, '2026-01-09 00:35:38', '2026-01-09 01:21:05'),
(97, 'Cobble Patch', 'info@cobblepatch.com.au', 'supplier', 42, 'Active', 'Cobble Patch', 'Internal Sales', '(07) 5547 7600', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$51Br0/nGREVEK8kMlLLAueHhLHBQhcOs5HguylpLcBBDszNfwJixe', NULL, '\"[{\\\"address\\\":\\\"Lot 26 Millstream Road, Jimboomba QLD 4280\\\",\\\"lat\\\":-27.8501726,\\\"long\\\":153.0215317,\\\"radius\\\":\\\"50.9\\\"}]\"', NULL, NULL, '2026-01-09 00:35:39', '2026-01-09 01:21:06'),
(98, 'SEQ Landscape Supplies', 'sales@hunmac.com.au', 'supplier', 43, 'Active', 'SEQ Landscape Supplies', 'Internal Sales', '0731 063 326', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$o6AiuuN./Xyj2GYr5BvfEOKJRSe./lL/oMsgxGhvxClvDfkFl8EMy', NULL, '\"[{\\\"address\\\":\\\"1 Quarry Road, Stapylton Queensland 4207, Australia\\\",\\\"lat\\\":-27.7295372,\\\"long\\\":153.2394898,\\\"radius\\\":\\\"38.7\\\"}]\"', NULL, NULL, '2026-01-09 00:35:39', '2026-01-09 01:21:06'),
(99, 'HUNMAC Landscape Supplies', 'info@hunmac.com.au', 'supplier', 44, 'Active', 'HUNMAC Landscape Supplies', 'Internal Sales', '411149905', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$HwDcqZrvQsk2eKUqT5MQyu9kdVQJD68kUowysJXPeL9tkX3GpsJK2', NULL, '\"[{\\\"address\\\":\\\"Ipswich, Brisbane\\\",\\\"lat\\\":-27.614614,\\\"long\\\":152.7608421,\\\"radius\\\":\\\"43.7\\\"}]\"', NULL, NULL, '2026-01-09 00:35:39', '2026-01-09 01:21:07'),
(100, 'Gardenscapes Landscape Centre', 'info@gardenscapeslandscapesupplies.com.au', 'supplier', 45, 'Active', 'Gardenscapes Landscape Centre', 'Internal Sales', '07 5498 5200 / 07 3888 3740', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$0NKeZX2XTCT1.LljV.vYduChrQx2RwByu0/R/Lhu2EnP.w4g4YkKy', NULL, '\"[{\\\"address\\\":\\\"286 Oakey Flat Rd, Morayfield Qld 4506\\\",\\\"lat\\\":-27.1346653,\\\"long\\\":152.938891,\\\"radius\\\":\\\"54.4\\\"},{\\\"address\\\":\\\"132 Deception Bay Rd, Deception Bay Qld 4508\\\",\\\"lat\\\":-27.1811093,\\\"long\\\":152.9971862,\\\"radius\\\":\\\"45.9\\\"}]\"', NULL, NULL, '2026-01-09 00:35:40', '2026-01-09 01:21:08'),
(101, 'Smart Stone Landscape Supplies', 'info@smartstonelandscapesupplies.com.au', 'supplier', 46, 'Active', 'Smart Stone Landscape Supplies', 'Internal Sales', '(07) 3807 5355', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$ASFi827CEvDEJROLgNFc0e9T6opO72slAw3dvevHWyHnM9KOmPCvK', NULL, '\"[{\\\"address\\\":\\\"145 Beaudesert Beenleigh Rd, Mount Warren Park QLD 4207, Australia\\\",\\\"lat\\\":-27.73402,\\\"long\\\":153.1941934,\\\"radius\\\":\\\"37.7\\\"}]\"', NULL, NULL, '2026-01-09 00:35:40', '2026-01-09 01:21:09'),
(102, 'Adelaide Hills Garden Supplies', 'info@ahgs.com.au', 'supplier', 47, 'Active', 'Adelaide Hills Garden Supplies', 'Internal Sales', '08 8388 1757', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$AKAI3XRYbPxHlg2MKIA5keaFw8I5s/wNXTqxmCORZZWBDQw3mcJca', NULL, '\"[{\\\"address\\\":\\\"820 Mount Barker Road, Verdun 5155\\\",\\\"lat\\\":-35.0178072,\\\"long\\\":138.7864756,\\\"radius\\\":\\\"23.5\\\"}]\"', NULL, NULL, '2026-01-09 00:35:40', '2026-01-09 01:21:10'),
(103, 'SA Landscape Supplies', 'info@salandscapesupplies.com.au', 'supplier', 48, 'Active', 'SA Landscape Supplies', 'Internal Sales', '08 7561 3797', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$kSj8qPsHgNu6ZyIBVB7cdumeRkr6oJ2CKCVKcvFE1rJt4YT53m82S', NULL, '\"[{\\\"address\\\":\\\"32 Gates Rd , Hackham, SA 5163\\\",\\\"lat\\\":-35.1517881,\\\"long\\\":138.526407,\\\"radius\\\":\\\"33.6\\\"},{\\\"address\\\":\\\"5 Sims St, Reynella, SA 5161\\\",\\\"lat\\\":-35.0841942,\\\"long\\\":138.5448414,\\\"radius\\\":\\\"20.3\\\"},{\\\"address\\\":\\\"72 How Rd Aldinga Beach SA, Australia 5173\\\",\\\"lat\\\":-35.2772447,\\\"long\\\":138.4707904,\\\"radius\\\":\\\"45.1\\\"},{\\\"address\\\":\\\"26 Circuit Drive, West Lakes Blvd, Hendon SA 5014\\\",\\\"lat\\\":-34.8814552,\\\"long\\\":138.5179632,\\\"radius\\\":\\\"12.4\\\"}]\"', NULL, NULL, '2026-01-09 00:35:40', '2026-01-09 01:23:13'),
(104, 'Buttrose Landscape & Garden Supplies', 'info@buttroselandscapegardensupplies.com.au', 'supplier', 49, 'Active', 'Buttrose Landscape & Garden Supplies', 'Internal Sales', '8522 6022', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$wzX9S44Y.S8ML8HL3BMxbuC52B1rURDZObMz50DSvvOH9skS2Exii', NULL, '\"[{\\\"address\\\":\\\"18 Kellys Road, WILLASTON GAWLER South Australia\\\",\\\"lat\\\":-34.5938458,\\\"long\\\":138.7345067,\\\"radius\\\":\\\"55\\\"}]\"', NULL, NULL, '2026-01-09 00:35:41', '2026-01-09 01:23:16'),
(105, 'Canberra Sand & Gravel Landscape', 'info@canberrasandgravellandscapes.com.au', 'supplier', 51, 'Active', 'Canberra Sand & Gravel Landscape', 'Internal Sales', '(02) 6260 1365', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$GxJR.mBE5.IWKhXklea2qeykjO68FQVATRkYDXlgLi.rrsmFDrEvm', NULL, '\"[{\\\"address\\\":\\\"150 Parkwood Rd, Holt ACT 2615, Australia\\\",\\\"lat\\\":-35.2163761,\\\"long\\\":148.9883515,\\\"radius\\\":\\\"19.8\\\"},{\\\"address\\\":\\\"50 Vicars St, Mitchell ACT 2911, Australia\\\",\\\"lat\\\":-35.2095841,\\\"long\\\":149.1450139,\\\"radius\\\":\\\"9.6\\\"},{\\\"address\\\":\\\"4 Johns Pl, Hume ACT 2620, Australia\\\",\\\"lat\\\":-35.3892871,\\\"long\\\":149.1710129,\\\"radius\\\":\\\"16.3\\\"}]\"', NULL, NULL, '2026-01-09 00:35:41', '2026-01-09 01:23:18'),
(106, 'Corkhill Bros', 'info@corkhillbros.com.au', 'supplier', 52, 'Active', 'Corkhill Bros', 'Internal Sales', '6239 7200', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$uSQ1KhGUD.F3XKAKORfjmewmodkolH8tI.Z5DkW72QyC2ptLM9mDO', NULL, '\"[{\\\"address\\\":\\\"Mugga Ln, Symonston ACT 2609, Australia\\\",\\\"lat\\\":-35.3499819,\\\"long\\\":149.1369302,\\\"radius\\\":\\\"12.1\\\"},{\\\"address\\\":\\\"33 Darling St, Mitchell ACT 2911, Australia\\\",\\\"lat\\\":-35.2181033,\\\"long\\\":149.1437076,\\\"radius\\\":\\\"9.1\\\"},{\\\"address\\\":\\\"67 Sawmill Cct, Hume ACT 2620, Australia\\\",\\\"lat\\\":-35.3975195,\\\"long\\\":149.1596812,\\\"radius\\\":\\\"18\\\"}]\"', NULL, NULL, '2026-01-09 00:35:42', '2026-01-09 01:23:20'),
(107, 'Stonehenge Garden & Landscape Centre (Beltana)', 'info@stonehengegardenlandscapecentre.com.au', 'supplier', 53, 'Active', 'Stonehenge Garden & Landscape Centre (Beltana)', 'Internal Sales', '61 2 6248 906', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$g5j/2ydXj.XVUQXjyKi6be/CVPEAXd9TxCji9rN1KBPaieku2qoOy', NULL, '\"[{\\\"address\\\":\\\"8 Beltana Rd, Canberra Australian Capital Territory 2609\\\",\\\"lat\\\":-35.305249,\\\"long\\\":149.178282,\\\"radius\\\":\\\"5.6\\\"}]\"', NULL, NULL, '2026-01-09 00:35:42', '2026-01-09 01:23:21'),
(108, 'Canberra Construction Recyclers', 'info@canberraconstructionrecyclers.com.au', 'supplier', 54, 'Active', 'Canberra Construction Recyclers', 'Internal Sales', '61 2 6249 742', NULL, NULL, NULL, NULL, NULL, NULL, 'MC-497', NULL, 0, NULL, NULL, '$2y$12$4iFEs6jVjgbK7bygqKKQjev8PK9ZmLQri1e/inV2TrlNKWTvoyjby', NULL, '\"[{\\\"address\\\":\\\"384 Pialligo Ave, Canberra Australian Capital Territory 2609\\\",\\\"lat\\\":-35.323145,\\\"long\\\":149.1997679,\\\"radius\\\":\\\"8.9\\\"}]\"', NULL, NULL, '2026-01-09 00:35:42', '2026-01-09 01:23:22'),
(109, 'Kevin Contreras', 'Kevinadmin@materialconnect.com', 'admin', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-357', NULL, 0, NULL, NULL, '$2y$12$dIrQvLBt1TLQKpc7pxhpsOTJR4FGfU4ZRRnkr9LFs5Ba..5D2Ij5W', NULL, NULL, NULL, NULL, '2026-01-15 01:10:00', '2026-03-01 21:59:50'),
(110, 'Aiza Chan', 'Aizachan@gmail.com', 'client', 55, 'Active', NULL, 'Aiza chan', '143 143 143', NULL, -33.67293610, 151.09348910, NULL, 'Sydney Rd, Hornsby Heights NSW 2077, Australia', 'fligno building', 'MC-580', NULL, 0, NULL, NULL, '$2y$12$1sV93yKiyx7m/qM5IP4opuAm9HgFfSWw0lckbZO5R1PFQWrTNOJTm', NULL, NULL, NULL, 'cus_TuRiB2QSFFbVUj', '2026-01-15 05:55:50', '2026-02-03 06:10:49'),
(111, 'John Doe', 'johndoe@email.com', 'client', 56, 'Active', NULL, 'John Doe', '+61 8 33475102', NULL, -33.89661770, 151.19697760, NULL, '27 Garden St, Eveleigh NSW 2015, Australia', '27 Garden Street, Eveleigh Sydney, NSW 2015, Australia', 'MC-741', NULL, 1, NULL, NULL, '$2y$12$NmIt1qqLZ7hloadEaILI2.XR5Tyrxfl0jKQDtOHZ7NjTd7U/mWytK', NULL, NULL, NULL, NULL, '2026-01-23 02:11:01', '2026-02-26 06:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `xero_tokens`
--

CREATE TABLE `xero_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text NOT NULL,
  `tenant_id` varchar(255) DEFAULT NULL,
  `tenant_name` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `xero_tokens`
--

INSERT INTO `xero_tokens` (`id`, `access_token`, `refresh_token`, `tenant_id`, `tenant_name`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjFDQUY4RTY2NzcyRDZEQzAyOEQ2NzI2RkQwMjYxNTgxNTcwRUZDMTkiLCJ0eXAiOiJKV1QiLCJ4NXQiOiJISy1PWm5jdGJjQW8xbkp2MENZVmdWY09fQmsifQ.eyJuYmYiOjE3NzIwNzQ0NjgsImV4cCI6MTc3MjA3NjI2OCwiaXNzIjoiaHR0cHM6Ly9pZGVudGl0eS54ZXJvLmNvbSIsImF1ZCI6Imh0dHBzOi8vaWRlbnRpdHkueGVyby5jb20vcmVzb3VyY2VzIiwiY2xpZW50X2lkIjoiMjdERjdBNzM5RjMwNDdFQkFGNDdEN0ZGQzk3RDQyM0IiLCJzdWIiOiI2ZmMzYTczNGIyMjk1ZGYzYTg4NzEzZGI0ODc5YzI0ZiIsImF1dGhfdGltZSI6MTc2Nzk1MDIxMywieGVyb191c2VyaWQiOiI0MzEzYTNjMi1mN2IxLTRiY2EtYTU0Zi1hMGVjODcxYWJiZjQiLCJnbG9iYWxfc2Vzc2lvbl9pZCI6IjEwYzNkOGU5ODk4NTQzODg4Njk3ZjgzZTM2ZWIxY2YzIiwic2lkIjoiMTBjM2Q4ZTk4OTg1NDM4ODg2OTdmODNlMzZlYjFjZjMiLCJhdXRoZW50aWNhdGlvbl9ldmVudF9pZCI6ImVhMjdiYWEwLWI1YTQtNGNlMC05NmJhLTFmNzc4NTQ0ZjU4NiIsImp0aSI6IkYzNUE2RDBGMjU1QzY5MDQwRjIzOTI5MUM4RTRBRkE4Iiwic2NvcGUiOlsiZW1haWwiLCJwcm9maWxlIiwib3BlbmlkIiwiYWNjb3VudGluZy5jb250YWN0cyIsImFjY291bnRpbmcuc2V0dGluZ3MiLCJhY2NvdW50aW5nLnRyYW5zYWN0aW9ucyIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJwd2QiLCJtZmEiLCJzd2siXX0.W4eBbH8s5r93CLaZsEpdVFHjTrX2h1v79c_nImukLWUuZL4Xzs-os2fZOHIeQyWKeUjMixjZFbq-suVbeTr6nsmIUT71ygeRyuJGUh6mS1nP4GhOXar8CP8ui1_H-KZKNfzGWEuh0f4dEjInoZEdB59jDevhkF8odr39uD_Z8vc8fa0VeHEzFOn8apI0gaTjqO2xuoa46yK50PdY52NhtzQ6HOs0U1LRqf2pXDeCn05QATvOZSdAzYbY9VjhwXIc0iAspt9ar4wgI82hjie4ESLQQEOpvCnO95fBurnsDMAxstKxUCidH4_0jTZCOlwFh3oyEoPmQ-n0poX2S1F3Uw', 'DXcEu2dJFS4Rp1IKQnFf54DDnc1wRULETI0wBpC5ixI', 'c9e2bde8-bd3e-43a6-b672-2ff921693c02', 'Material Connect', '2026-02-25 16:24:28', '2026-01-09 05:33:21', '2026-02-25 15:54:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_logs`
--
ALTER TABLE `action_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `failed_invoice_logs`
--
ALTER TABLE `failed_invoice_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `failed_invoice_logs_order_id_index` (`order_id`),
  ADD KEY `failed_invoice_logs_run_date_index` (`run_date`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_order_id_index` (`order_id`),
  ADD KEY `invoices_client_id_index` (`client_id`),
  ADD KEY `invoices_status_index` (`status`),
  ADD KEY `invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_index` (`invoice_id`),
  ADD KEY `invoice_items_order_item_id_index` (`order_item_id`),
  ADD KEY `invoice_items_delivery_id_index` (`order_item_delivery_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_products`
--
ALTER TABLE `master_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_client` (`client_id`),
  ADD KEY `idx_orders_project` (`project_id`),
  ADD KEY `idx_orders_date` (`delivery_date`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_order` (`order_id`),
  ADD KEY `idx_items_product` (`product_id`),
  ADD KEY `idx_items_supplier` (`supplier_id`);

--
-- Indexes for table `order_item_deliveries`
--
ALTER TABLE `order_item_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oid_idx` (`order_id`),
  ADD KEY `oiid_idx` (`order_item_id`),
  ADD KEY `sid_idx` (`supplier_id`),
  ADD KEY `order_item_deliveries_invoice_id_index` (`invoice_id`);

--
-- Indexes for table `order_item_delivery_surcharges`
--
ALTER TABLE `order_item_delivery_surcharges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oids_delivery_id` (`order_item_delivery_id`),
  ADD KEY `idx_oids_surcharge_id` (`surcharge_id`);

--
-- Indexes for table `order_item_delivery_testing_fees`
--
ALTER TABLE `order_item_delivery_testing_fees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_testing_fee_unique` (`order_item_delivery_id`,`testing_fee_id`),
  ADD KEY `fk_oidtf_testing_fee` (`testing_fee_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `supplier_offers`
--
ALTER TABLE `supplier_offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surcharges`
--
ALTER TABLE `surcharges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testing_fees`
--
ALTER TABLE `testing_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_client_public_id` (`client_public_id`);

--
-- Indexes for table `xero_tokens`
--
ALTER TABLE `xero_tokens`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_logs`
--
ALTER TABLE `action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `failed_invoice_logs`
--
ALTER TABLE `failed_invoice_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_products`
--
ALTER TABLE `master_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=283;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `order_item_deliveries`
--
ALTER TABLE `order_item_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_item_delivery_surcharges`
--
ALTER TABLE `order_item_delivery_surcharges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `order_item_delivery_testing_fees`
--
ALTER TABLE `order_item_delivery_testing_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=550;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `supplier_offers`
--
ALTER TABLE `supplier_offers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `surcharges`
--
ALTER TABLE `surcharges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `testing_fees`
--
ALTER TABLE `testing_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `xero_tokens`
--
ALTER TABLE `xero_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_delivery_id_foreign` FOREIGN KEY (`order_item_delivery_id`) REFERENCES `order_item_deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `order_item_deliveries`
--
ALTER TABLE `order_item_deliveries`
  ADD CONSTRAINT `fk_oid_order_item_deliveries_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oiid_order_item_deliveries_order_items` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sid_order_item_deliveries_users` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_item_deliveries_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_item_delivery_surcharges`
--
ALTER TABLE `order_item_delivery_surcharges`
  ADD CONSTRAINT `fk_oids_delivery` FOREIGN KEY (`order_item_delivery_id`) REFERENCES `order_item_deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oids_surcharge` FOREIGN KEY (`surcharge_id`) REFERENCES `surcharges` (`id`);

--
-- Constraints for table `order_item_delivery_testing_fees`
--
ALTER TABLE `order_item_delivery_testing_fees`
  ADD CONSTRAINT `fk_oidtf_delivery` FOREIGN KEY (`order_item_delivery_id`) REFERENCES `order_item_deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oidtf_testing_fee` FOREIGN KEY (`testing_fee_id`) REFERENCES `testing_fees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
