-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2025 at 01:28 PM
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
(1, 1, 1, 1, 'premium-gravel-x8R02k', 1, 'Premium Gravel', 'Gravel', 'High-quality gravel, ideal for construction, landscaping, and drainage.', 'ton', NULL, 'product_photos/cM46Xwq2c03wQA2Pghbo8OpeJqoFyj8X68mZnEmw.png', '2025-10-09 13:58:07', '2025-10-09 13:58:07'),
(2, 1, 0, NULL, 'river-sand-N39HgM', 1, 'River Sand', 'Sand', 'Fine river sand, ideal for plastering and construction use.', 'ton', NULL, 'product_photos/Mowuj8xypPWzOYPRvkvJTkmzhw5f4vVHLiD0mOG3.png', '2025-10-09 14:21:29', '2025-10-09 14:21:29'),
(3, 1, 0, NULL, 'washed-soil-s8R87R', 1, 'Washed Soil', 'Soil', 'Washed soil for gardening and construction projects. Clean and free from impurities.', 'Cubic Meter', NULL, 'product_photos/DmE4N7ZXP56NHYnicSyICfoPxB6HHWeMuoSybt5D.png', '2025-10-09 14:23:14', '2025-10-09 14:23:14'),
(4, 1, 0, NULL, 'm25-concrete-mi-CipCrl', 1, 'M25 Concrete Mi', 'Concrete', 'High-strength M25 concrete mix, ideal for heavy construction work.', 'Cubic Meter', NULL, 'product_photos/qIONel8BrhhQEgOnWwo94EPWkaq560Wu3NDnUQrn.png', '2025-10-09 14:23:42', '2025-10-09 15:19:02'),
(6, 14, 0, NULL, 'new-product-name-SECYR0', 1, 'New Product Name', 'asdas', 'asdas', 'kg', NULL, NULL, '2025-10-09 15:27:58', '2025-10-09 15:27:58');

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
  `id` int(11) UNSIGNED NOT NULL,
  `client_id` int(11) UNSIGNED NOT NULL,
  `project_id` int(11) UNSIGNED NOT NULL,
  `delivery_method` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax` decimal(12,2) NOT NULL,
  `fuel_levy` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `margin` decimal(5,2) NOT NULL DEFAULT 50.00,
  `supplier_cost` decimal(12,2) NOT NULL,
  `customer_cost` decimal(12,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `repeat_order` tinyint(1) DEFAULT 0,
  `custom_mix_blend` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `client_id`, `project_id`, `delivery_method`, `delivery_address`, `delivery_date`, `subtotal`, `tax`, `fuel_levy`, `total_price`, `margin`, `supplier_cost`, `customer_cost`, `discount`, `repeat_order`, `custom_mix_blend`, `created_at`, `updated_at`) VALUES
(3, 13, 1, 'Tipper', '123 Site Address, Melbourne', '2025-10-15', 0.00, 0.00, 0.00, 0.00, 50.00, 0.00, 0.00, 10.00, 1, NULL, '2025-10-10 11:51:05', '2025-10-10 11:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `supplier_id` int(11) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `supplier_id`, `price`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 10.00, NULL, NULL, '2025-10-10 11:51:05', '2025-10-10 11:51:05'),
(2, 3, 2, 5.00, NULL, NULL, '2025-10-10 11:51:05', '2025-10-10 11:51:05');

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
(2, 'App\\Models\\User', 1, 'UserApp', '2fecf6313c137b4ccf331145b105e19eb70b93f3828080784cc2adcd81f1681f', '[\"*\"]', '2025-10-11 17:57:51', NULL, '2025-10-07 13:50:54', '2025-10-11 17:57:51'),
(3, 'App\\Models\\User', 8, 'ClientApp', '971894f9376c230d332f9d2593af3f93926f62b71c63ee6e8bbdc7dd8a8b056c', '[\"*\"]', NULL, NULL, '2025-10-07 14:33:49', '2025-10-07 14:33:49'),
(4, 'App\\Models\\User', 9, 'ClientApp', '2a371020988476bfcc1bd4bb5732aa3f8ab4d32a8ca0c2216236abf66f01b388', '[\"*\"]', NULL, NULL, '2025-10-07 14:40:47', '2025-10-07 14:40:47'),
(5, 'App\\Models\\User', 10, 'ClientApp', '3af6b947c4db47723f051aaf976bf51567894e5eb9daa3404b312723602bea83', '[\"*\"]', NULL, NULL, '2025-10-07 14:42:53', '2025-10-07 14:42:53'),
(6, 'App\\Models\\User', 11, 'ClientApp', '0c6fcb6dc289c33d2e86f82a3aeaafb6140f484cc06f922ec6adf9d6221e8315', '[\"*\"]', NULL, NULL, '2025-10-07 14:44:54', '2025-10-07 14:44:54'),
(7, 'App\\Models\\User', 12, 'ClientApp', '2aae275b9d5b47a5f0bf0564d67dd03e163388c550ce101e5f0d39055ed14961', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:13', '2025-10-07 14:53:13'),
(8, 'App\\Models\\User', 13, 'ClientApp', '8710e54e5cf0d6e232ee48633cbf7345ab8e173f025c4d3aa5e02cab302805b7', '[\"*\"]', NULL, NULL, '2025-10-07 14:53:59', '2025-10-07 14:53:59'),
(9, 'App\\Models\\User', 14, 'SupplierApp', '74473f4f05db56a419255005b10ed5a788cb10d48f3238118428175adc87d50a', '[\"*\"]', NULL, NULL, '2025-10-07 14:59:19', '2025-10-07 14:59:19'),
(10, 'App\\Models\\User', 1, 'UserApp', '7b40f53b56ffd02bdcb7284b09143f693a79a9c0eed7a9af72dc1af5d270930a', '[\"*\"]', NULL, NULL, '2025-10-07 15:18:37', '2025-10-07 15:18:37'),
(11, 'App\\Models\\User', 13, 'UserApp', 'f2c0a0fec3972cff58e4adac4f31f085d1f94f7eac104a258423e581d027f853', '[\"*\"]', '2025-10-11 15:21:07', NULL, '2025-10-07 15:27:33', '2025-10-11 15:21:07'),
(12, 'App\\Models\\User', 14, 'UserApp', '25abc8a9729ffd8e79c47581cda2e6efde059a60e272574a237cece314c31c7a', '[\"*\"]', '2025-10-11 15:53:57', NULL, '2025-10-09 15:06:35', '2025-10-11 15:53:57'),
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
(38, 'App\\Models\\User', 20, 'UserApp', 'f4d4d570ce4f14108b23a3d5647964d2c261292ab3000c9bf88dd98b062e718c', '[\"*\"]', '2025-10-11 15:21:56', NULL, '2025-10-11 15:21:46', '2025-10-11 15:21:56'),
(39, 'App\\Models\\User', 18, 'UserApp', 'c3cd03d2069bfcffbff2a8ebccf17f9cdae5fa0ce973ad18b109ca061105e573', '[\"*\"]', NULL, NULL, '2025-10-11 16:10:14', '2025-10-11 16:10:14'),
(40, 'App\\Models\\User', 18, 'UserApp', '97ed3b13f7ce4350cbca1fad9958f727283812f2a92acc03ef4160256f5d72f2', '[\"*\"]', NULL, NULL, '2025-10-11 16:10:21', '2025-10-11 16:10:21'),
(41, 'App\\Models\\User', 21, 'UserApp', 'f7c2e3963ece7d4bbd9cc9e380cab3f40e56411c0eb6ae3223a6d89cac1f23fe', '[\"*\"]', NULL, NULL, '2025-10-11 16:14:17', '2025-10-11 16:14:17'),
(42, 'App\\Models\\User', 18, 'UserApp', '6807c8157bc1703251b882ff2b9dde8148f526d8a19c8d5d8f2a0f8e244f909a', '[\"*\"]', NULL, NULL, '2025-10-11 16:20:26', '2025-10-11 16:20:26'),
(43, 'App\\Models\\User', 19, 'UserApp', '32e6903fbaad9bc364ba5b9e5c72da2eb3617601def5b7b399ed92dcd6723cf2', '[\"*\"]', NULL, NULL, '2025-10-11 16:20:42', '2025-10-11 16:20:42'),
(44, 'App\\Models\\User', 14, 'UserApp', '459dc6ff682d7ac95a995e0a38d7bb0d98027c5d45b726e7dd3ea6fa4ed2cd30', '[\"*\"]', '2025-10-11 17:08:44', NULL, '2025-10-11 16:21:02', '2025-10-11 17:08:44'),
(45, 'App\\Models\\User', 1, 'UserApp', 'af6d535213ff57ba64fa571b7cf1ea0ca01c8dcba2d26ec501dbaafc94859b04', '[\"*\"]', '2025-10-11 18:27:17', NULL, '2025-10-11 17:09:23', '2025-10-11 18:27:17');

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
(1, 'Project 1', 13, NULL, NULL, NULL, '2025-10-07 18:51:43', '2025-10-07 18:57:27'),
(3, 'project 2 .', 20, 'Opt', '23324', 'dafdsasd', '2025-10-11 14:53:08', '2025-10-11 14:53:08'),
(4, 'a', 20, 'a', 'a', 'a', '2025-10-11 15:06:53', '2025-10-11 15:06:53');

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
  `delivery_zones` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_offers`
--

INSERT INTO `supplier_offers` (`id`, `supplier_id`, `master_product_id`, `price`, `availability_status`, `delivery_zones`, `status`, `created_at`, `updated_at`) VALUES
(1, 14, 1, 60.99, 'In Stock', '[{\"lat\":40.7128,\"long\":-74.006,\"radius\":10},{\"lat\":41.8781,\"long\":-87.6298,\"radius\":15}]', 'Pending', '2025-10-09 15:07:39', '2025-10-09 15:43:00');

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
(16, 'supplier 2', 'supplier2@gmail.com', 'supplier', 4, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MC-295', NULL, 0, NULL, NULL, '$2y$12$lryRqzI5yX8tADBJhPCb7O/9euMjc7xpvWAfDDk4Q23wpSmkRPU9q', NULL, NULL, NULL, '2025-10-07 15:43:13', '2025-10-07 15:43:13'),
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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supplier_offers`
--
ALTER TABLE `supplier_offers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
