-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2026 at 11:27 AM
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
-- Database: `inventory_stock_management_api_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(2, 'kontruksi', 'bahan pertambangan', '2026-07-09 20:25:47', '2026-07-10 01:00:55'),
(4, 'peledak', 'Peralatan peledak', '2026-07-10 01:02:58', '2026-07-10 01:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
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
  `attempts` smallint(5) UNSIGNED NOT NULL,
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
(4, '2026_07_10_015143_create_personal_access_tokens_table', 2),
(5, '2026_07_10_015642_add_role_to_users_table', 3),
(6, '2026_07_10_023521_create_categories_table', 4),
(7, '2026_07_10_032617_create_suppliers_table', 5),
(8, '2026_07_10_035803_create_warehouses_table', 6),
(9, '2026_07_10_040839_create_products_table', 7),
(10, '2026_07_10_061749_create_stock_histories_table', 8);

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
(1, 'App\\Models\\User', 1, 'inventory-api', '3953d3d91d834075aba581f284295a11502fa472ecf5bd87f351af6049f8be29', '[\"*\"]', NULL, NULL, '2026-07-09 19:17:09', '2026-07-09 19:17:09'),
(3, 'App\\Models\\User', 2, 'inventory-api', '24be6c0695a4f8e104afef611512e4b3bbc8a7cc3479fb6bfeb8013c7972c225', '[\"*\"]', '2026-07-09 19:56:29', NULL, '2026-07-09 19:56:05', '2026-07-09 19:56:29'),
(4, 'App\\Models\\User', 3, 'inventory-api', '8fa3c849c2c6aeb0fd4ffd44809935977c8fe5e1560b2ac2572025b659e77034', '[\"*\"]', '2026-07-10 00:58:34', NULL, '2026-07-09 20:02:51', '2026-07-10 00:58:34'),
(5, 'App\\Models\\User', 3, 'inventory-api', '0f1a12cc76017e2c0731c4e6f42fd0ba552b536c8c4c21cf341e499d79289029', '[\"*\"]', NULL, NULL, '2026-07-09 23:24:57', '2026-07-09 23:24:57'),
(6, 'App\\Models\\User', 4, 'inventory-api', '620b72022047b13a9fcd8905dfa7527919c0d5e1257dac28c0ae650b044825ce', '[\"*\"]', '2026-07-10 00:09:13', NULL, '2026-07-09 23:26:22', '2026-07-10 00:09:13'),
(7, 'App\\Models\\User', 5, 'inventory-api', '24f38c002f2f88df1de0f749c46972e93fe53c95eafcdf9a3b50f4bbde630e9a', '[\"*\"]', NULL, NULL, '2026-07-10 00:03:37', '2026-07-10 00:03:37'),
(8, 'App\\Models\\User', 7, 'inventory-api', '990739b19602daa2333b8576ed7272c68960ede352802c9ff48d8d0051ecd74a', '[\"*\"]', NULL, NULL, '2026-07-10 00:17:37', '2026-07-10 00:17:37'),
(9, 'App\\Models\\User', 7, 'inventory-api', '4feef0b79cda1d7975c56a8442f0c7734d10c2deccd843e618e2e2bc157c8a97', '[\"*\"]', NULL, NULL, '2026-07-10 00:17:43', '2026-07-10 00:17:43'),
(10, 'App\\Models\\User', 7, 'inventory-api', '9aa5b0c47b5b2b8318a876a312b6959b8cb84c6502154b0e1c9fa2da60aa6d2a', '[\"*\"]', '2026-07-10 00:20:32', NULL, '2026-07-10 00:19:18', '2026-07-10 00:20:32'),
(11, 'App\\Models\\User', 6, 'inventory-api', 'e3935f16a5a95793d46afd4348bcadc4927434e7fc9298e8ea2da71d6172b963', '[\"*\"]', '2026-07-10 00:28:10', NULL, '2026-07-10 00:24:42', '2026-07-10 00:28:10'),
(12, 'App\\Models\\User', 8, 'inventory-api', '801380090fe40f3bf58016da30f21dae6bf970496f25ba909105f57436d503ed', '[\"*\"]', '2026-07-10 00:31:00', NULL, '2026-07-10 00:29:25', '2026-07-10 00:31:00'),
(13, 'App\\Models\\User', 9, 'inventory-api', '301b87209ac74121db0ab9a2c08bc041b8f68ba02eaae8a6f34a3b0f60c74ed0', '[\"*\"]', NULL, NULL, '2026-07-10 00:52:05', '2026-07-10 00:52:05'),
(15, 'App\\Models\\User', 8, 'inventory-api', 'f1a22f4c5bb51fec4b41f4dd747fc78f944dac5425be826e667437ea7bd956a4', '[\"*\"]', '2026-07-10 01:02:58', NULL, '2026-07-10 00:54:39', '2026-07-10 01:02:58'),
(16, 'App\\Models\\User', 6, 'inventory-api', '4421cbb6de9a84ddcaabb4c024a503d628e7c6e4fa183fa6e346cfe5796618c0', '[\"*\"]', '2026-07-10 01:05:08', NULL, '2026-07-10 01:04:20', '2026-07-10 01:05:08'),
(17, 'App\\Models\\User', 6, 'inventory-api', 'acc63bb882ffcb380432cb3a2076c13a8a3c0bfba43c3f09ede05b68bfb9df19', '[\"*\"]', '2026-07-10 01:14:10', NULL, '2026-07-10 01:13:49', '2026-07-10 01:14:10'),
(18, 'App\\Models\\User', 6, 'inventory-api', 'ece2815c5ae2d224c2ec3a4a50556bebb0f44b28ff9d546e217ab2e4569b3b82', '[\"*\"]', '2026-07-10 01:17:47', NULL, '2026-07-10 01:17:19', '2026-07-10 01:17:47'),
(19, 'App\\Models\\User', 8, 'inventory-api', '41c6666ad01f4589035b2a4d78207d5af4a39ce186808588d96715c830079229', '[\"*\"]', '2026-07-10 01:30:49', NULL, '2026-07-10 01:18:21', '2026-07-10 01:30:49'),
(20, 'App\\Models\\User', 8, 'inventory-api', '13600f7fa67915c6bb6f83b3d2c1abff0f6dab3421bff693626cf24de02d1bed', '[\"*\"]', NULL, NULL, '2026-07-10 01:28:17', '2026-07-10 01:28:17'),
(21, 'App\\Models\\User', 8, 'inventory-api', '360b2eb96fa98f8e6fad0ae4f88e1269c3e117961788d8335e52a36cc055adf3', '[\"*\"]', '2026-07-10 02:12:59', NULL, '2026-07-10 01:56:31', '2026-07-10 02:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `warehouse_id`, `name`, `sku`, `price`, `stock`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 'Laptop Dell Latitude', 'LAP-DELL-001', 8500000.00, 20, 'Laptop untuk kebutuhan kantor', '2026-07-09 23:13:04', '2026-07-10 01:28:29');

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
-- Table structure for table `stock_histories`
--

CREATE TABLE `stock_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `stock_before` int(11) NOT NULL,
  `stock_after` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_histories`
--

INSERT INTO `stock_histories` (`id`, `product_id`, `type`, `quantity`, `stock_before`, `stock_after`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 'IN', 10, 0, 10, 'Barang datang dari supplier', '2026-07-09 23:26:47', '2026-07-09 23:26:47'),
(2, 1, 'IN', 20, 10, 30, 'Barang datang dari supplier', '2026-07-10 00:20:32', '2026-07-10 00:20:32'),
(3, 1, 'OUT', 2, 30, 28, 'Barang dijual', '2026-07-10 01:19:11', '2026-07-10 01:19:11'),
(4, 1, 'ADJUSTMENT', 8, 28, 20, 'Stock opname', '2026-07-10 01:28:29', '2026-07-10 01:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'PT Dahana', '08123456789', 'Subang', '2026-07-09 20:33:51', '2026-07-10 01:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Albar', 'albar@gmail.com', NULL, '$2y$12$6cmkeQa1xn6GsQo3NUijveWCqdt2YHHBxTrJIrjpaSN04KHTC/ZsS', 'user', NULL, '2026-07-09 19:15:33', '2026-07-09 19:15:33'),
(2, 'iyes', 'iyes@gmail.com', NULL, '$2y$12$jnllXVC1HLzFA.Xv34Ctaus09yDxRGjJWlPdX9aJbPAkjHUkk3Vim', 'user', NULL, '2026-07-09 19:20:17', '2026-07-09 19:20:17'),
(3, 'ino saepul', 'ino@gmail.com', NULL, '$2y$12$GBuQIdbCtznb22dBCWp5deEQL/0c/OStaiZNm.HuDl9QDUoAmvdk6', 'user', NULL, '2026-07-09 20:02:40', '2026-07-09 20:02:40'),
(4, 'idam akbar', 'idam@gmail.com', NULL, '$2y$12$GdU1BE8xQN8wQWgHnn4KF./QhOs7LtPW3uPVN27/DUWWtuMaXozN.', 'user', NULL, '2026-07-09 23:26:14', '2026-07-09 23:26:14'),
(5, 'Administrator', 'admin@gmail.com', NULL, '$2y$12$bHOY6fpv2o1C7cbaIvpUTOrZoWKI6uMV7C6p0ugEjWNaDC6nqr5g.', 'admin', NULL, '2026-07-10 00:02:34', '2026-07-10 00:02:34'),
(6, 'User', 'user@gmail.com', NULL, '$2y$12$KgojJNQc8a4N3n0MpxFfiO8a63kRl0qGRQBpXV87PsZ3zPVetdrju', 'user', NULL, '2026-07-10 00:02:34', '2026-07-10 00:02:34'),
(7, 'rizal suyaman', 'rizal@gmail.com', NULL, '$2y$12$R4TvUJgqoNpPII9uBDLMcuCtzkmvTdQJ5VlMqRIG75.oS3sVFEy1m', 'user', NULL, '2026-07-10 00:17:19', '2026-07-10 00:17:19'),
(8, 'cikra', 'cikra@gmail.com', NULL, '$2y$12$XaP1BmuIbKmOccmxj2g1NuME3T1z7ly5vSMzxmBILL.nmkmmWVSmu', 'admin', NULL, '2026-07-10 00:29:15', '2026-07-10 00:29:15'),
(9, 'kamil', 'kamil@gmail.com', NULL, '$2y$12$KjH/douA3xbR1KS.2b.3JeTGYZPji4f0RP.0lyMHDX0K6HIOA5n8m', 'user', NULL, '2026-07-10 00:51:54', '2026-07-10 00:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `location`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Gudang Pusat', 'Subang', 'Ring 1', '2026-07-09 21:07:33', '2026-07-10 02:04:00'),
(3, 'Gedung D', 'PT Dahana Subang', 'Direksi', '2026-07-10 02:06:22', '2026-07-10 02:06:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_supplier_id_foreign` (`supplier_id`),
  ADD KEY `products_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_histories`
--
ALTER TABLE `stock_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_histories_product_id_foreign` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_histories`
--
ALTER TABLE `stock_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_histories`
--
ALTER TABLE `stock_histories`
  ADD CONSTRAINT `stock_histories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
