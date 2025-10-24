-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 02:00 PM
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
-- Database: `material_connect`
--

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
(1, 'Sample Company 1', '2025-10-07 06:34:27', '2025-10-07 06:34:27'),
(2, 'Sample Company 2', '2025-10-07 06:34:28', '2025-10-07 06:34:28'),
(3, 'company 1', '2025-10-07 14:33:49', '2025-10-07 14:33:49'),
(4, '', '2025-10-07 15:42:51', '2025-10-07 15:42:51'),
(5, 'asd', '2025-10-10 16:46:09', '2025-10-10 16:46:09'),
(6, 'asdasd', '2025-10-10 16:50:42', '2025-10-10 16:50:42'),
(7, 'abc', '2025-10-10 17:08:32', '2025-10-10 17:08:32'),
(8, 'company', '2025-10-10 20:36:00', '2025-10-10 20:36:00');

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
  `product_name` varchar(120) NOT NULL,
  `product_type` varchar(120) NOT NULL,
  `specifications` text DEFAULT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `tech_doc` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_products`
--

INSERT INTO `master_products` (`id`, `added_by`, `is_approved`, `approved_by`, `slug`, `category`, `product_name`, `product_type`, `specifications`, `unit_of_measure`, `tech_doc`, `photo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'premium-gravel-x8R02k', 1, 'Premium Gravel', 'Gravel', 'High-quality gravel, ideal for construction, landscaping, and drainage.', 'ton', NULL, 'product_photos/cM46Xwq2c03wQA2Pghbo8OpeJqoFyj8X68mZnEmw.png', '2025-10-09 13:58:07', '2025-10-15 14:39:50'),
(2, 1, 1, 1, 'river-sand-N39HgM', 1, 'River Sand', 'Sand', 'Fine river sand, ideal for plastering and construction use.', 'ton', NULL, 'product_photos/Mowuj8xypPWzOYPRvkvJTkmzhw5f4vVHLiD0mOG3.png', '2025-10-09 14:21:29', '2025-10-15 09:26:07'),
(3, 1, 1, NULL, 'washed-soil-s8R87R', 3, 'Washed Soil', 'Soil', 'Washed soil for gardening and construction projects. Clean and free from impurities.', 'Cubic Meter', NULL, 'product_photos/DmE4N7ZXP56NHYnicSyICfoPxB6HHWeMuoSybt5D.png', '2025-10-09 14:23:14', '2025-10-15 09:24:03'),
(4, 1, 1, NULL, 'm25-concrete-mi-CipCrl', 1, 'M25 Concrete Mi', 'Concrete', 'High-strength M25 concrete mix, ideal for heavy construction work.', 'Cubic Meter', NULL, 'product_photos/qIONel8BrhhQEgOnWwo94EPWkaq560Wu3NDnUQrn.png', '2025-10-09 14:23:42', '2025-10-13 09:43:27'),
(6, 14, 0, NULL, 'new-product-name-SECYR0', 1, 'New Product Name', 'asdas', 'asdas', 'kg', NULL, NULL, '2025-10-09 15:27:58', '2025-10-09 15:27:58'),
(9, 14, 0, NULL, 'asd-nypZJg', 3, 'asd', 'asd', 'asdsasadasdsa', 'asd', NULL, NULL, '2025-10-13 18:49:15', '2025-10-13 18:49:15'),
(10, 14, 0, NULL, 'sad-hfG2jS', 2, 'sad', 'asd', 'asd', 'asd', NULL, NULL, '2025-10-13 18:59:44', '2025-10-13 18:59:44'),
(11, 1, 0, NULL, 'testinggg-LxKcGb', 3, 'Testinggg', 'a', 'asd', 'sd', NULL, NULL, '2025-10-14 17:34:15', '2025-10-14 17:35:51');

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
  `delivery_date` datetime NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `delivery_window` enum('Morning','Afternoon','Evening') DEFAULT NULL,
  `delivery_method` enum('Other','Tipper','Agitator','Pump','Ute') NOT NULL,
  `load_size` varchar(50) DEFAULT NULL,
  `special_equipment` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fuel_levy` decimal(12,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gst_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `supplier_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `customer_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admin_margin` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Pending','Paid','Partially Paid','Partial Refunded','Refunded','Requested') NOT NULL DEFAULT 'Pending',
  `supplier_paid_ids` text DEFAULT NULL,
  `order_status` enum('In-Progress','Completed','Cancelled') NOT NULL DEFAULT 'In-Progress',
  `workflow` enum('Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered') DEFAULT 'Requested',
  `reason` text DEFAULT NULL,
  `repeat_order` tinyint(1) NOT NULL DEFAULT 0,
  `generate_invoice` tinyint(1) NOT NULL DEFAULT 0,
  `order_process` enum('Automated','Action Required') DEFAULT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `po_number`, `client_id`, `project_id`, `delivery_address`, `delivery_lat`, `delivery_long`, `delivery_date`, `delivery_time`, `delivery_window`, `delivery_method`, `load_size`, `special_equipment`, `subtotal`, `fuel_levy`, `other_charges`, `gst_tax`, `discount`, `total_price`, `supplier_cost`, `customer_cost`, `admin_margin`, `payment_status`, `supplier_paid_ids`, `order_status`, `workflow`, `reason`, `repeat_order`, `generate_invoice`, `order_process`, `special_notes`, `created_at`, `updated_at`) VALUES
(1, 'PO-2025-10425', 13, 7, '12 Quarry Rd, Dandenong VIC 3175', -34.4805240, 117.6457214, '2025-10-18 09:30:00', '09:30:00', NULL, 'Agitator', '6m³', 'Chute extension', 165.00, 660.00, 0.00, 16.50, 0.00, 841.50, 710.00, 841.50, 82.50, 'Pending', NULL, 'In-Progress', 'Payment Requested', NULL, 1, 0, 'Automated', NULL, '2025-10-17 17:27:09', '2025-10-20 16:14:13'),
(4, 'PO-2525-2525', 13, 8, '2880 Red Gum Pass Rd, Kendenup WA 6323, Australia', -34.4804046, 117.6506889, '2025-10-30 00:00:00', '02:15:00', NULL, 'Other', 's', 'sad', 142.50, 440.00, 0.00, 14.25, 0.00, 596.75, 495.00, 596.75, 71.25, 'Pending', NULL, 'In-Progress', 'Payment Requested', NULL, 0, 0, 'Automated', 'asd', '2025-10-21 16:15:35', '2025-10-21 16:17:00'),
(6, 'Po-2025-25', 13, 7, '2880 Red Gum Pass Rd, Kendenup WA 6323, Australia', -34.4804046, 117.6506889, '2025-10-31 00:00:00', '02:53:00', NULL, 'Tipper', '6', 'sad', 142.50, 22.00, 0.00, 14.25, 0.00, 178.75, 115.00, 178.75, 71.25, 'Pending', NULL, 'In-Progress', 'Payment Requested', NULL, 0, 0, 'Automated', 'asd', '2025-10-21 16:53:16', '2025-10-21 16:53:55'),
(7, 'PO-213213', 13, 7, '2880 Red Gum Pass Rd, Kendenup WA 6323, Australia', -34.4804046, 117.6506889, '2025-10-31 00:00:00', '04:03:00', NULL, 'Other', '5', 'asd', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'In-Progress', 'Supplier Missing', NULL, 0, 0, 'Action Required', 'asd', '2025-10-21 18:03:51', '2025-10-21 18:03:51'),
(8, 'PO-2989890', 13, 8, '2880 Red Gum Pass Rd, Kendenup WA 6323, Australia', -34.4804046, 117.6506889, '2025-10-31 00:00:00', '04:05:00', NULL, 'Agitator', '34', 'sad', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Pending', NULL, 'In-Progress', 'Supplier Assigned', NULL, 1, 0, 'Automated', 'asd', '2025-10-21 18:05:17', '2025-10-21 21:41:19');

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
  `supplier_unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `supplier_delivery_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `supplier_discount` decimal(12,2) DEFAULT 0.00,
  `supplier_delivery_date` datetime DEFAULT NULL,
  `supplier_confirms` tinyint(1) NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `supplier_id`, `choosen_offer_id`, `custom_blend_mix`, `supplier_unit_cost`, `supplier_delivery_cost`, `supplier_discount`, `supplier_delivery_date`, `supplier_confirms`, `is_paid`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 8.00, 14, 10, '32MPa, 20mm agg, slump 80', 10.00, 300.00, 10.00, '2025-10-18 00:00:00', 1, 0, '2025-10-17 17:27:09', '2025-10-17 19:32:33'),
(2, 1, 2, 5.00, 14, 11, NULL, 10.00, 300.00, 10.00, '2025-10-18 00:00:00', 1, 0, '2025-10-17 17:27:09', '2025-10-20 16:14:13'),
(7, 4, 1, 1.00, 14, 10, NULL, 51.00, 200.00, 10.00, '2025-10-30 00:00:00', 1, 0, '2025-10-21 16:15:35', '2025-10-21 16:16:50'),
(8, 4, 2, 1.00, 14, 11, NULL, 54.00, 200.00, 0.00, '2025-10-30 00:00:00', 1, 0, '2025-10-21 16:15:35', '2025-10-21 16:17:00'),
(11, 6, 1, 1.00, 14, 10, NULL, 51.00, 10.00, 5.00, '2025-10-31 00:00:00', 1, 0, '2025-10-21 16:53:16', '2025-10-21 16:53:38'),
(12, 6, 2, 1.00, 14, 11, NULL, 54.00, 10.00, 5.00, '2025-10-31 00:00:00', 1, 0, '2025-10-21 16:53:16', '2025-10-21 16:53:55'),
(13, 7, 1, 1.00, 14, 10, NULL, 51.00, 0.00, 0.00, '2025-10-31 00:00:00', 0, 0, '2025-10-21 18:03:51', '2025-10-21 18:03:51'),
(14, 7, 3, 1.00, NULL, NULL, NULL, 0.00, 0.00, 0.00, '2025-10-31 00:00:00', 0, 0, '2025-10-21 18:03:51', '2025-10-21 18:03:51'),
(15, 8, 1, 1.00, 14, 10, NULL, 51.00, 0.00, 0.00, '2025-10-31 00:00:00', 0, 0, '2025-10-21 18:05:17', '2025-10-21 18:05:17'),
(16, 8, 2, 1.00, 14, 11, NULL, 54.00, 0.00, 0.00, '2025-10-31 00:00:00', 0, 0, '2025-10-21 18:05:17', '2025-10-21 18:05:17');

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
(2, 'App\\Models\\User', 1, 'UserApp', '2fecf6313c137b4ccf331145b105e19eb70b93f3828080784cc2adcd81f1681f', '[\"*\"]', '2025-10-24 13:18:27', NULL, '2025-10-07 13:50:54', '2025-10-24 13:18:27'),
(3, 'App\\Models\\User', 8, 'ClientApp', '971894f9376c230d332f9d2593af3f93926f62b71c63ee6e8bbdc7dd8a8b056c', '[\"*\"]', NULL, NULL, '2025-10-07 14:33:49', '2025-10-07 14:33:49'),
(4, 'App\\Models\\User', 9, 'ClientApp', '2a371020988476bfcc1bd4bb5732aa3f8ab4d32a8ca0c2216236abf66f01b388', '[\"*\"]', NULL, NULL, '2025-10-07 14:40:47', '2025-10-07 14:40:47'),
(5, 'App\\Models\\User', 10, 'ClientApp', '3af6b947c4db47723f051aaf976bf51567894e5eb9daa3404b312723602bea83', '[\"*\"]', NULL, NULL, '2025-10-07 14:42:53', '2025-10-07 14:42:53'),
(6, 'App\\Models\\User', 11, 'ClientApp', '0c6fcb6dc289c33d2e86f82a3aeaafb6140f484cc06f922ec6adf9d6221e8315', '[\"*\"]', NULL, NULL, '2025-10-07 14:44:54', '2025-10-07 14:44:54'),
(7, 'App\\Models\\User', 12, 'ClientApp', '2aae275b9d5b47a5f0bf0564d67dd03e163388c550ce101e5f0d39055ed14961', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:13', '2025-10-07 14:53:13'),
(8, 'App\\Models\\User', 13, 'ClientApp', '8710e54e5cf0d6e232ee48633cbf7345ab8e173f025c4d3aa5e02cab302805b7', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:59', '2025-10-07 14:53:59'),
(9, 'App\\Models\\User', 14, 'SupplierApp', '74473f4f05db56a419255005b10ed5a788cb10d48f3238118428175adc87d50a', '[\"*\"]', NULL, NULL, '2025-10-07 14:59:19', '2025-10-07 14:59:19'),
(10, 'App\\Models\\User', 1, 'UserApp', '7b40f53b56ffd02bdcb7284b09143f693a79a9c0eed7a9af72dc1af5d270930a', '[\"*\"]', NULL, NULL, '2025-10-07 15:18:37', '2025-10-07 15:18:37'),
(11, 'App\\Models\\User', 13, 'UserApp', 'f2c0a0fec3972cff58e4adac4f31f085d1f94f7eac104a258423e581d027f853', '[\"*\"]', '2025-10-20 16:14:32', NULL, '2025-10-07 15:27:33', '2025-10-20 16:14:32'),
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
(60, 'App\\Models\\User', 13, 'UserApp', '89ca15dc1baa7a24893be8a0516aedfa87d1e8c98c4eaa0e66c602d0ad8b665d', '[\"*\"]', '2025-10-15 18:28:19', NULL, '2025-10-15 14:45:06', '2025-10-15 18:28:19'),
(61, 'App\\Models\\User', 13, 'UserApp', 'aced521ed8f05ef1db991dc23fdd55a684b6f5964997733393f4d7039d4ec98a', '[\"*\"]', '2025-10-17 17:27:09', NULL, '2025-10-15 14:45:52', '2025-10-17 17:27:09'),
(62, 'App\\Models\\User', 13, 'UserApp', '4f39fe371744a2c35bcdda6786ff53510f25d43be55761d751defff1104c6b57', '[\"*\"]', '2025-10-15 20:23:07', NULL, '2025-10-15 18:25:20', '2025-10-15 20:23:07'),
(63, 'App\\Models\\User', 13, 'UserApp', 'f30ba5a83da7116d7854e8e491a59804cb75a613982f478a51f03a29081731b5', '[\"*\"]', '2025-10-17 16:03:18', NULL, '2025-10-17 11:29:15', '2025-10-17 16:03:18'),
(64, 'App\\Models\\User', 1, 'UserApp', 'ab850cd95fe51548fdb036b619461b8fdcd1331186ce24a21dcc56f16d4b69a7', '[\"*\"]', '2025-10-17 16:09:09', NULL, '2025-10-17 11:55:12', '2025-10-17 16:09:09'),
(65, 'App\\Models\\User', 14, 'UserApp', 'ca8cfaa7571c16c7cc9f1a2dc057ee3f8842c510e9fb0fb7530f0cbde4cb8946', '[\"*\"]', NULL, NULL, '2025-10-17 18:56:17', '2025-10-17 18:56:17'),
(66, 'App\\Models\\User', 14, 'UserApp', 'af4e15cfcc2543ca6f4d6640a62f3342f7f4ec2ab12f9abb5f1b042fbde16176', '[\"*\"]', '2025-10-17 19:46:29', NULL, '2025-10-17 18:57:12', '2025-10-17 19:46:29'),
(67, 'App\\Models\\User', 13, 'UserApp', 'a779527b556e644375c816579a3b951105b1d2c8fe0e08fb93841171e1d1ff48', '[\"*\"]', '2025-10-20 16:40:42', NULL, '2025-10-20 14:40:17', '2025-10-20 16:40:42'),
(68, 'App\\Models\\User', 14, 'UserApp', '6c826c91457ae7407d324dcf49753faf26e4bb3e6dbe435fd83a67fe1fee1238', '[\"*\"]', '2025-10-20 16:40:40', NULL, '2025-10-20 16:13:56', '2025-10-20 16:40:40'),
(69, 'App\\Models\\User', 14, 'UserApp', '5dc2d806fd9dbd8cf9a2cd3a2668fefa0cdd7efd8ce55a56d685a03187b0f8fe', '[\"*\"]', '2025-10-21 22:08:38', NULL, '2025-10-21 14:56:32', '2025-10-21 22:08:38'),
(70, 'App\\Models\\User', 13, 'UserApp', '78635f495d41809918e75113871e593d787007a70958586b27ba1f3f0fcbd926', '[\"*\"]', '2025-10-21 22:08:38', NULL, '2025-10-21 15:12:35', '2025-10-21 22:08:38'),
(71, 'App\\Models\\User', 1, 'UserApp', 'bc6025d74e9042e2754a5c989a1ca28361949ee80ad02c7ff4348389d9d25dca', '[\"*\"]', '2025-10-21 22:08:57', NULL, '2025-10-21 21:36:12', '2025-10-21 22:08:57'),
(72, 'App\\Models\\User', 1, 'UserApp', '47e81548aa30d85ee604e97e1258b2da1428be73e153d6c341822c20bc7e02e5', '[\"*\"]', '2025-10-23 17:55:10', NULL, '2025-10-23 10:46:31', '2025-10-23 17:55:10'),
(73, 'App\\Models\\User', 14, 'UserApp', '36013a0f2b37b274a14931c4100ec482b8e02e59d672fb5706db45cb34465b06', '[\"*\"]', '2025-10-23 10:56:32', NULL, '2025-10-23 10:51:38', '2025-10-23 10:56:32');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `added_by`, `site_contact_name`, `site_contact_phone`, `site_instructions`, `created_at`, `updated_at`) VALUES
(3, 'project 2 .', 20, 'Opt', '23324', 'dafdsasd', '2025-10-11 14:53:08', '2025-10-11 14:53:08'),
(4, 'a', 20, 'a', 'a', 'a', '2025-10-11 15:06:53', '2025-10-11 15:06:53'),
(7, 'Project', 13, 'Project Manager', '13123123', 'Instructions', '2025-10-14 12:52:27', '2025-10-14 15:25:19'),
(8, 'NC1', 13, 'asd', 'asd', 'asd', '2025-10-15 20:20:06', '2025-10-15 20:20:06');

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
(10, 14, 1, 51.00, 'In Stock', 'Approved', '2025-10-15 15:12:36', '2025-10-15 15:13:07'),
(11, 14, 2, 54.00, 'In Stock', 'Approved', '2025-10-15 15:12:41', '2025-10-15 15:13:14'),
(12, 16, 2, 55.00, 'In Stock', 'Approved', '2025-10-15 15:12:53', '2025-10-15 15:13:17'),
(13, 16, 3, 56.00, 'In Stock', 'Approved', '2025-10-15 15:12:57', '2025-10-15 16:18:25');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `company_id`, `status`, `company_name`, `contact_name`, `contact_number`, `location`, `lat`, `long`, `delivery_radius`, `shipping_address`, `billing_address`, `client_public_id`, `profile_image`, `isDeleted`, `delivery_address`, `email_verified_at`, `password`, `notes`, `delivery_zones`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@materialconnect.com', 'admin', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '$2y$12$vTcdyZMjDDzBMqvTUV7vVOVQYaibsRyiN787yN3bYomkb0hE/gY.G', NULL, NULL, NULL, '2025-10-06 13:05:46', '2025-10-10 21:53:06'),
(13, 'client', 'client1@gmail.com', 'client', 3, 'Active', NULL, 'Client', '12321312321', NULL, NULL, NULL, NULL, 'shipping address', 'billing address', 'MC-440', 'storage/profile_images/8RojGkaSnqAzZADEUjjxrVFGea0li6B3QiPw5BGc.jpg', 0, NULL, NULL, '$2y$12$nF9leXWi.wcZuPPCYln7Leb/eOPKACMECQuEQSLOfMSWcX9aWm2Wy', NULL, NULL, NULL, '2025-10-07 14:53:59', '2025-10-10 22:22:10'),
(14, 'Supplier 1', 'supplier1@gmail.com', 'supplier', 3, 'Active', NULL, 'Supplier 1', '3213213', 'asdasdas', 21.00000000, 22.00000000, 322, NULL, NULL, NULL, 'storage/profile_images/8afMJ5DGIPi21MSGJkMos3FRx0mwHFFqjFQeXAUk.webp', 0, NULL, NULL, '$2y$12$sL926r4dL/cdLnVFHeFA4uTXQCzpF1mTJepzXcxFimKs2r8PzvNGS', NULL, '[{\"address\":\"62 Mildura Rd, Kendenup WA 6323, Australia\",\"lat\":-34.4797916,\"long\":117.6099508,\"radius\":45},{\"address\":\"44 Kintyre Rd, Hamlyn Terrace NSW 2259, Australia\",\"lat\":-33.2490342,\"long\":151.4800541,\"radius\":65}]', NULL, '2025-10-07 14:59:19', '2025-10-11 18:14:52'),
(15, 'client 2', 'client2@gmail.com', 'client', 4, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-223', NULL, 0, NULL, NULL, '$2y$12$67lXOwUUCvgd39nzGcTqcODKfFvvSipp6r7dyhc9PUY87HXxIb4Cq', NULL, NULL, NULL, '2025-10-07 15:42:52', '2025-10-07 15:42:52'),
(16, 'supplier 2', 'supplier2@gmail.com', 'supplier', 4, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-295', NULL, 0, NULL, NULL, '$2y$12$lryRqzI5yX8tADBJhPCb7O/9euMjc7xpvWAfDDk4Q23wpSmkRPU9q', NULL, '[{\"address\":\"7 Larissa Ct, Croydon VIC 3136, Australia\",\"lat\":-37.8040487,\"long\":145.2947942,\"radius\":30}]', NULL, '2025-10-07 15:43:13', '2025-10-14 11:54:39'),
(17, 'supplier 3', 'supplier3@gmail.com', 'supplier', 4, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-563', NULL, 0, NULL, NULL, '$2y$12$lw64iHLDuw/ze6GovU.zueOj/s2eteMJEDFaEracwZOeU4BnJrCdO', NULL, NULL, NULL, '2025-10-07 15:57:34', '2025-10-07 15:57:34'),
(18, 'Roshaan Faisa', 'sup1@gmail.com', 'supplier', 8, 'Active', NULL, 'Roshaan Faisal', '+924444444444', '54 South St, Rydalmere NSW 2116, Australia', -33.81312910, 151.03055910, 50, NULL, NULL, NULL, 'storage/profile_images/XAASePQiWniJhv7F1E4DX1vjRYQJsyjNzqxqKX3r.jpg', 0, NULL, NULL, '$2y$12$jdsjt4OmyREXqWrJip.7Iufiyd3MwVJM/qtqCHMfJd3yVTQLOUEKy', NULL, NULL, NULL, '2025-10-10 16:19:20', '2025-10-10 20:36:00'),
(19, 'Roshaan Faisal', 'sup2@gmail.com', 'supplier', 5, 'Active', NULL, 'Roshaan Faisal', '+924444444444', '54 South St, Rydalmere NSW 2116, Australia', NULL, NULL, NULL, NULL, NULL, NULL, 'storage/profile_images/Lt5xF4GMtJCvUWSpQ7sEovWBv8GRJBSbADjBwjXX.webp', 0, NULL, NULL, '$2y$12$crrojiNcA.WqzHBOw662A.ybGCqxuv1pg8ybOB2PCiNpseBkHdldS', NULL, NULL, NULL, '2025-10-10 16:46:10', '2025-10-10 16:46:10'),
(20, 'Material Connect', 'cli11@gmail.com', 'client', 6, 'Active', NULL, 'Roshaan Faisa', '+924444444444', NULL, -42.90242220, 147.49230520, NULL, '71 Carrick Rd, Lauderdale TAS 7021, Australia', '54 South Street', 'MC-630', 'storage/profile_images/MsZUccqRZVYz3fzYgnOFBJ6lUOm24U1W6Y9Aeqgz.webp', 0, NULL, NULL, '$2y$12$kiQKJS.lv8Mm9QhzmTfJ4uD/yOi3eFPXKc/SGe8ro37mRlYX3B5Py', NULL, NULL, NULL, '2025-10-10 16:50:43', '2025-10-10 20:39:52'),
(21, 'Sup3', 'sup3@gmail.com', 'supplier', 7, 'Active', NULL, 'jane withj', '+61 43434 43434 34', '54 South St, Rydalmere NSW 2116, Australia', NULL, NULL, 50, NULL, NULL, NULL, 'storage/profile_images/DO3u9yW7ngkoKO0c0zKOuEwSXFZ3mWef0kwDrq1c.jpg', 0, NULL, NULL, '$2y$12$YrxRgsVCJj56uQ7RUtAvEeGFu5TOQ3CGMTYsiqvaeuh5ZYFvM1iRu', NULL, NULL, NULL, '2025-10-10 17:08:33', '2025-10-10 22:24:08');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_client_public_id` (`client_public_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_products`
--
ALTER TABLE `master_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `supplier_offers`
--
ALTER TABLE `supplier_offers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
