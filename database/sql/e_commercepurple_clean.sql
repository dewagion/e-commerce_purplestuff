-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Jun 2026 pada 14.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_commercepurple`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_push_subscriptions`
--

CREATE TABLE `admin_push_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fcm_token` text NOT NULL,
  `user_agent` text DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admin_push_subscriptions`
--


-- --------------------------------------------------------

--
-- Struktur dari tabel `artists`
--

CREATE TABLE `artists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `artists`
--

INSERT INTO `artists` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'THE ROSE', 'the-rose', '2026-05-31 19:55:16', '2026-05-31 19:55:16'),
(2, 'BTS', 'bts', '2026-06-04 18:09:46', '2026-06-04 18:09:46'),
(3, 'TXT', 'txt', '2026-06-04 18:09:52', '2026-06-04 18:09:52'),
(4, 'ILLIT', 'illit', '2026-06-04 18:09:58', '2026-06-04 18:09:58'),
(5, 'TREASURE', 'treasure', '2026-06-04 18:11:34', '2026-06-04 18:11:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `dashboard_section` varchar(40) NOT NULL DEFAULT 'hero',
  `cta_text` varchar(50) DEFAULT NULL,
  `cta_link` varchar(255) DEFAULT NULL,
  `target_type` varchar(30) NOT NULL DEFAULT 'manual',
  `target_category_slug` varchar(255) DEFAULT NULL,
  `target_artist_slug` varchar(255) DEFAULT NULL,
  `target_product_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_product_ids`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `badge_text`, `image`, `dashboard_section`, `cta_text`, `cta_link`, `target_type`, `target_category_slug`, `target_artist_slug`, `target_product_ids`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'BTS COLLECTIONS', 'new collections', 'new products', '/images/banners/8a57c1fa-b4de-4395-b189-7f23b3e215ed.jpg', 'hero', 'Shop Now', 'http://127.0.0.1:8000/artist/bts', 'artist', NULL, 'bts', '[\"prod-official-light-stick-the-rose-zxniu\"]', 1, 1, '2026-06-04 03:52:28', '2026-06-07 02:30:26'),
(2, 'New Collection', 'menu baru yang ada pada purple Stuff', 'new drop', '/images/banners/34de16b9-ff06-4e0b-a7ba-9988f2af35b2.jpeg', 'single_feature', 'Shop Now', 'http://127.0.0.1:8000/artist/bts', 'artist', NULL, 'bts', '[\"prod-offical-bts-merch-11-esqlj\",\"prod-offical-bts-merch-8-kwnyx\",\"prod-offical-bts-shirt-denim-butter-e2mrj\"]', 1, 1, '2026-06-04 19:31:05', '2026-06-07 02:29:41'),
(7, 'LIghtstick', NULL, NULL, '/images/banners/72358867-25f9-470c-98a2-77358caa6613.jpeg', 'category_grid', 'Shop Now', 'http://127.0.0.1:8000/products?category=lightstick', 'category', 'lightstick', NULL, '[]', 0, 1, '2026-06-04 23:14:53', '2026-06-05 03:07:36'),
(8, 'Merch', NULL, NULL, '/images/banners/ea7931a4-eb73-4ab8-a5a5-c8101c99bfe8.jpeg', 'category_grid', 'Shop Now', 'http://127.0.0.1:8000/products?category=merch', 'category', 'merch', NULL, '[]', 1, 1, '2026-06-04 23:14:53', '2026-06-04 23:14:53'),
(9, 'Shirt', NULL, NULL, '/images/banners/4a90eafe-c262-46a9-83e8-c3379a917d37.jpeg', 'category_grid', 'Shop Now', 'http://127.0.0.1:8000/products?category=shirt', 'category', 'shirt', NULL, '[]', 2, 1, '2026-06-04 23:14:53', '2026-06-04 23:14:53'),
(10, 'THE ROSE IS HERE', 'Hey Black Rose! Look what’s here', 'New Drop', '/images/banners/9f50c3a3-e436-4e08-8ac7-13781d8e23ec.jpg', 'hero', 'Shop Now', 'http://127.0.0.1:8000/artist/the-rose', 'artist', NULL, 'the-rose', '[\"prod-official-light-stick-the-rose-zxniu\"]', 2, 1, '2026-06-05 03:00:08', '2026-06-07 02:30:02'),
(11, 'New Collection', 'produk baru', 'New Drop', '/images/banners/063f249b-ab88-4cd9-a87a-f8a8be1acf08.jpeg', 'hero', 'Shop Now', 'http://127.0.0.1:8000/products?ids=prod-offical-bts-merch-11-esqlj%2Cprod-offical-bts-shirt-denim-butter-e2mrj', 'products', NULL, NULL, '[\"prod-offical-bts-merch-11-esqlj\",\"prod-offical-bts-shirt-denim-butter-e2mrj\"]', 2, 1, '2026-06-07 22:02:12', '2026-06-07 22:02:12'),
(12, 'New Collection Illit', 'produk terbaru dari illit', 'New Drop', '/images/banners/e60a267a-d77d-4d1f-985e-3e969de03db6.jpg', 'single_feature', 'Shop Now', 'http://127.0.0.1:8000/products?ids=prod-official-lightstick-illit-gypir', 'products', NULL, NULL, '[\"prod-official-lightstick-illit-gypir\"]', 2, 1, '2026-06-21 03:44:23', '2026-06-21 03:44:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `key` varchar(191) NOT NULL,
  `size` varchar(255) DEFAULT NULL,
  `variant` varchar(255) DEFAULT NULL,
  `qty` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `bg` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `bg`, `created_at`, `updated_at`) VALUES
(1, 'Lightstick', 'lightstick', 'bolt', 'from-fuchsia-500 to-violet-500', '2026-05-31 19:54:17', '2026-05-31 19:54:17'),
(2, 'Shirt', 'shirt', 'shirt', 'bg-purple-100', '2026-06-04 18:06:40', '2026-06-04 18:06:53'),
(3, 'Hoodie', 'hoodie', 'hoodie', 'from-amber-400 to-yellow-500', '2026-06-04 18:10:17', '2026-06-04 18:10:17'),
(4, 'Merch', 'merch', 'card', 'bg-green-100', '2026-06-04 18:19:30', '2026-06-07 02:30:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
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
-- Struktur dari tabel `jobs`
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
-- Struktur dari tabel `job_batches`
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
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_02_123223_add_phone_address_to_users_table', 1),
(5, '2026_03_03_094636_create_artists_table', 1),
(6, '2026_03_03_094832_create_categories_table', 1),
(7, '2026_03_03_094919_create_products_table', 1),
(8, '2026_03_03_095038_create_product_images_table', 1),
(9, '2026_03_03_095107_create_product_variants_table', 1),
(10, '2026_03_03_095221_create_product_sizes_table', 1),
(11, '2026_03_03_153121_add_phone_address_to_users_table', 1),
(12, '2026_03_11_000001_create_orders_table', 1),
(13, '2026_03_11_000002_create_order_items_table', 1),
(14, '2026_03_11_110254_add_bank_transfer_fields_to_orders_table', 1),
(15, '2026_03_11_111415_add_payment_proof_to_orders_table', 1),
(16, '2026_03_12_000001_add_biteship_postal_code_to_users_table', 1),
(17, '2026_03_12_000002_add_weight_to_products_table', 1),
(18, '2026_03_12_000003_add_biteship_shipping_fields_to_orders_table', 1),
(19, '2026_03_12_160500_backfill_product_weights', 1),
(20, '2026_03_12_170000_add_is_admin_to_users_table', 1),
(21, '2026_03_13_000001_create_banners_table', 1),
(22, '2026_03_14_000001_add_targeting_fields_to_banners_table', 1),
(23, '2026_03_15_000001_create_wishlists_table', 1),
(24, '2026_03_23_131058_create_notifications_table', 1),
(25, '2026_04_18_100000_create_admin_push_subscriptions_table', 1),
(26, '2026_05_12_200000_add_tracking_fields_to_orders_table', 1),
(27, '2026_05_12_200100_create_order_tracking_histories_table', 1),
(28, '2026_05_23_000001_create_newsletter_subscribers_table', 1),
(29, '2026_05_23_000002_create_cart_items_table', 1),
(30, '2026_05_23_000004_seed_default_dashboard_banners', 1),
(31, '2026_05_23_000005_add_stock_to_products_table', 1),
(32, '2026_05_23_000007_create_product_option_stocks_table', 1),
(33, '2026_05_25_000001_add_shipping_receipt_to_orders_table', 1),
(34, '2026_05_25_000003_add_payment_deadline_to_orders_table', 1),
(35, '2026_05_25_000004_fix_payment_timeout_and_stock_release_columns', 1),
(36, '2026_05_27_000001_add_discount_and_display_status_to_products_table', 1),
(37, '2026_05_27_000002_cleanup_default_template_catalog_data', 1),
(38, '2026_06_01_000001_add_option_pricing_media_to_products', 2),
(39, '2026_06_05_000001_add_dashboard_section_to_banners_table', 3),
(40, '2026_06_21_000001_add_customer_cancelled_email_sent_at_to_orders_table', 4),
(41, '2026_06_22_000001_standardize_order_payment_defaults', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`, `created_at`, `updated_at`) VALUES
(1, 'customer@example.com', '2026-06-06 00:14:02', '2026-06-06 00:14:02', '2026-06-06 00:14:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--


-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_cost` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `grand_total` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_items` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_courier` varchar(255) DEFAULT NULL,
  `shipping_courier_name` varchar(255) DEFAULT NULL,
  `shipping_service_code` varchar(255) DEFAULT NULL,
  `shipping_service_name` varchar(255) DEFAULT NULL,
  `shipping_etd` varchar(255) DEFAULT NULL,
  `shipping_weight` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_origin_postal_code` varchar(10) DEFAULT NULL,
  `shipping_destination_postal_code` varchar(10) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `tracking_notes` text DEFAULT NULL,
  `shipping_receipt_path` varchar(255) DEFAULT NULL,
  `shipping_receipt_uploaded_at` timestamp NULL DEFAULT NULL,
  `tracking_updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'bank_transfer',
  `payment_label` varchar(255) NOT NULL DEFAULT 'Transfer Bank',
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_code` varchar(255) DEFAULT NULL,
  `payment_deadline_at` timestamp NULL DEFAULT NULL,
  `payment_expired_at` timestamp NULL DEFAULT NULL,
  `payment_proof_path` varchar(255) DEFAULT NULL,
  `payment_proof_uploaded_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Menunggu Pembayaran',
  `paid_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `stock_reserved_at` timestamp NULL DEFAULT NULL,
  `stock_released_at` timestamp NULL DEFAULT NULL,
  `stock_release_reason` varchar(255) DEFAULT NULL,
  `customer_order_cancelled_email_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `notes`, `subtotal`, `shipping_cost`, `grand_total`, `total_items`, `shipping_courier`, `shipping_courier_name`, `shipping_service_code`, `shipping_service_name`, `shipping_etd`, `shipping_weight`, `shipping_origin_postal_code`, `shipping_destination_postal_code`, `tracking_number`, `tracking_notes`, `shipping_receipt_path`, `shipping_receipt_uploaded_at`, `tracking_updated_at`, `payment_method`, `payment_label`, `payment_status`, `payment_code`, `payment_deadline_at`, `payment_expired_at`, `payment_proof_path`, `payment_proof_uploaded_at`, `status`, `paid_at`, `confirmed_at`, `shipped_at`, `completed_at`, `stock_reserved_at`, `stock_released_at`, `stock_release_reason`, `customer_order_cancelled_email_sent_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'PSM-20260602151908-N8OZ', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260602-531424', '2026-06-02 07:29:08', NULL, NULL, '2026-06-02 07:19:25', 'Diproses', '2026-06-02 07:26:48', '2026-06-02 07:26:40', NULL, NULL, '2026-06-02 07:19:08', NULL, NULL, NULL, '2026-06-02 07:19:08', '2026-06-02 07:30:25'),
(2, 3, 'PSM-20260602153312-JET2', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260602-500015', '2026-06-02 07:43:12', NULL, NULL, '2026-06-02 07:33:37', 'Diproses', '2026-06-02 07:37:34', '2026-06-02 07:37:34', NULL, NULL, '2026-06-02 07:33:12', NULL, NULL, NULL, '2026-06-02 07:33:12', '2026-06-02 07:37:34'),
(3, 3, 'PSM-20260602154130-WMOG', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 160000, 1260000, 1, 'jne', 'JNE', 'jtr', 'JNE Trucking', '5 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'waiting_verification', 'TRF-20260602-217822', '2026-06-02 07:51:30', NULL, NULL, '2026-06-02 07:41:49', 'Menunggu Verifikasi Pembayaran', NULL, NULL, NULL, NULL, '2026-06-02 07:41:30', NULL, NULL, NULL, '2026-06-02 07:41:30', '2026-06-02 07:41:49'),
(4, 3, 'PSM-20260604111106-DM6Q', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260604-923399', '2026-06-04 03:21:06', NULL, NULL, '2026-06-04 03:11:25', 'Diproses', '2026-06-04 03:14:36', '2026-06-04 03:14:36', NULL, NULL, '2026-06-04 03:11:06', NULL, NULL, NULL, '2026-06-04 03:11:06', '2026-06-04 03:14:36'),
(5, 3, 'PSM-20260606033702-LSN3', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 880000, 42000, 922000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 1000, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'expired', 'TRF-20260606-350998', '2026-06-05 19:47:02', '2026-06-05 19:47:04', NULL, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, '2026-06-05 19:37:02', '2026-06-05 19:47:04', 'payment_timeout', NULL, '2026-06-05 19:37:02', '2026-06-05 19:47:04'),
(6, 3, 'PSM-20260608040522-SLZ8', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1200000, 126000, 1326000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 3000, '80237', '83663', 'JNE080980', 'sudah kirim', NULL, '2026-06-07 20:24:22', '2026-06-07 20:24:22', 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260608-771549', '2026-06-07 20:15:22', NULL, NULL, '2026-06-07 20:06:07', 'Dikirim', '2026-06-07 20:12:10', '2026-06-07 20:12:10', '2026-06-07 20:24:22', NULL, '2026-06-07 20:05:22', NULL, NULL, NULL, '2026-06-07 20:05:22', '2026-06-07 20:24:22'),
(7, 3, 'PSM-20260608053802-H62J', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 780000, 42000, 822000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', 'JNE976987', 'sudah dikirim', NULL, '2026-06-07 21:57:22', '2026-06-07 21:57:22', 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260608-351523', '2026-06-07 21:48:02', NULL, NULL, '2026-06-07 21:39:50', 'Dikirim', '2026-06-07 21:55:14', '2026-06-07 21:55:14', '2026-06-07 21:57:22', NULL, '2026-06-07 21:38:02', NULL, NULL, NULL, '2026-06-07 21:38:02', '2026-06-07 21:57:22'),
(8, 3, 'PSM-20260620130646-AJTT', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'expired', 'TRF-20260620-580519', '2026-06-20 05:16:46', '2026-06-20 05:36:21', NULL, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, '2026-06-20 05:06:46', '2026-06-20 05:36:21', 'payment_timeout', NULL, '2026-06-20 05:06:46', '2026-06-20 05:36:21'),
(9, 3, 'PSM-20260620130711-M7HN', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260620-648207', '2026-06-20 05:17:11', NULL, NULL, '2026-06-20 05:07:24', 'Diproses', '2026-06-20 05:37:02', '2026-06-20 05:37:02', NULL, NULL, '2026-06-20 05:07:11', NULL, NULL, NULL, '2026-06-20 05:07:11', '2026-06-20 05:37:02'),
(10, 3, 'PSM-20260621082231-IV7X', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1200000, 84000, 1284000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 2000, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'expired', 'TRF-20260621-359155', '2026-06-21 00:32:31', '2026-06-21 00:32:33', NULL, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, '2026-06-21 00:22:31', '2026-06-21 00:32:33', 'payment_timeout', '2026-06-21 01:16:22', '2026-06-21 00:22:31', '2026-06-21 01:16:22'),
(11, 3, 'PSM-20260621092522-N7JA', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 1100000, 42000, 1142000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 500, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'expired', 'TRF-20260621-286759', '2026-06-21 01:35:22', '2026-06-21 02:07:22', NULL, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, '2026-06-21 01:25:22', '2026-06-21 02:07:22', 'payment_timeout', NULL, '2026-06-21 01:25:22', '2026-06-21 02:07:22'),
(12, 3, 'PSM-20260621101052-AIJZ', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 880000, 42000, 922000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 1000, '80237', '83663', NULL, NULL, NULL, NULL, NULL, 'bank_transfer', 'Transfer Bank', 'expired', 'TRF-20260621-924165', '2026-06-21 02:20:52', '2026-06-21 02:20:54', NULL, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, '2026-06-21 02:10:52', '2026-06-21 02:20:54', 'payment_timeout', '2026-06-21 02:21:00', '2026-06-21 02:10:52', '2026-06-21 02:21:00'),
(13, 3, 'PSM-20260621102341-CMIE', 'Customer Demo', 'customer@example.com', '081234567891', 'Alamat customer demo\nKode Pos: 83663', NULL, 880000, 42000, 922000, 1, 'jne', 'JNE', 'reg', 'Reguler', '4 - 6 days', 1000, '80237', '83663', 'JNE2394', 'dalam perjalanan', NULL, '2026-06-21 02:25:55', '2026-06-21 02:25:55', 'bank_transfer', 'Transfer Bank', 'paid', 'TRF-20260621-790432', '2026-06-21 02:33:41', NULL, NULL, '2026-06-21 02:23:51', 'Dikirim', '2026-06-21 02:24:53', '2026-06-21 02:24:53', '2026-06-21 02:25:55', NULL, '2026-06-21 02:23:41', NULL, NULL, NULL, '2026-06-21 02:23:41', '2026-06-21 02:25:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `variant` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `price` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `line_total` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `sku`, `variant`, `size`, `type`, `price`, `qty`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-02 07:19:08', '2026-06-02 07:19:08'),
(2, 2, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-02 07:33:12', '2026-06-02 07:33:12'),
(3, 3, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-02 07:41:30', '2026-06-02 07:41:30'),
(4, 4, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-04 03:11:06', '2026-06-04 03:11:06'),
(5, 5, 'prod-official-lightstick-illit-gypir', 'OFFICIAL LIGHTSTICK ILLIT', '/images/products/8794c6c5-11d0-47ba-9ad4-fc4e2720cf94.jpeg', 'I-001', NULL, NULL, 'accessory', 880000, 1, 880000, '2026-06-05 19:37:02', '2026-06-05 19:37:02'),
(6, 6, 'prod-offical-bts-merch-11-esqlj', 'OFFICAL BTS MERCH 11', '/images/products/643d0c11-ac52-4940-a901-2e4dd7d7f504.jpeg', 'B-003', NULL, NULL, 'accessory', 1200000, 1, 1200000, '2026-06-07 20:05:22', '2026-06-07 20:05:22'),
(7, 7, 'prod-offical-bts-shirt-denim-butter-e2mrj', 'OFFICAL BTS SHIRT DENIM BUTTER', '/images/products/cc697812-8da8-40d4-b9fe-0ab88b49c1a6.jpeg', NULL, NULL, 'XL', 'apparel', 780000, 1, 780000, '2026-06-07 21:38:02', '2026-06-07 21:38:02'),
(8, 8, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-20 05:06:46', '2026-06-20 05:06:46'),
(9, 9, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Strap', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-20 05:07:11', '2026-06-20 05:07:11'),
(10, 10, 'prod-offical-bts-merch-8-kwnyx', 'OFFICAL BTS MERCH 8', '/images/products/946b5d6d-2804-4eb1-88be-fbc446269be1.jpeg', 'B-002', NULL, NULL, 'accessory', 1200000, 1, 1200000, '2026-06-21 00:22:31', '2026-06-21 00:22:31'),
(11, 11, 'prod-official-light-stick-the-rose-zxniu', 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 'TR-001', 'Lightstick', NULL, 'accessory', 1100000, 1, 1100000, '2026-06-21 01:25:22', '2026-06-21 01:25:22'),
(12, 12, 'prod-official-lightstick-illit-gypir', 'OFFICIAL LIGHTSTICK ILLIT', '/images/products/8794c6c5-11d0-47ba-9ad4-fc4e2720cf94.jpeg', 'I-001', NULL, NULL, 'accessory', 880000, 1, 880000, '2026-06-21 02:10:52', '2026-06-21 02:10:52'),
(13, 13, 'prod-official-lightstick-illit-gypir', 'OFFICIAL LIGHTSTICK ILLIT', '/images/products/8794c6c5-11d0-47ba-9ad4-fc4e2720cf94.jpeg', 'I-001', NULL, NULL, 'accessory', 880000, 1, 880000, '2026-06-21 02:23:41', '2026-06-21 02:23:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_tracking_histories`
--

CREATE TABLE `order_tracking_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `tracking_notes` text DEFAULT NULL,
  `status_snapshot` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL DEFAULT 'tracking_updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_tracking_histories`
--

INSERT INTO `order_tracking_histories` (`id`, `order_id`, `admin_user_id`, `tracking_number`, `tracking_notes`, `status_snapshot`, `event_type`, `created_at`, `updated_at`) VALUES
(1, 1, 4, NULL, NULL, 'Diproses', 'status_updated', '2026-06-02 07:26:40', '2026-06-02 07:26:40'),
(2, 6, 4, 'JNE080980', 'sudah kirim', 'Dikirim', 'tracking_updated', '2026-06-07 20:24:23', '2026-06-07 20:24:23'),
(3, 7, 4, 'JNE976987', 'sudah dikirim', 'Dikirim', 'tracking_updated', '2026-06-07 21:57:22', '2026-06-07 21:57:22'),
(4, 13, 4, 'JNE2394', 'dalam perjalanan', 'Dikirim', 'tracking_updated', '2026-06-21 02:25:55', '2026-06-21 02:25:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` varchar(255) NOT NULL,
  `artist_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `type` enum('apparel','accessory') NOT NULL DEFAULT 'accessory',
  `price` bigint(20) UNSIGNED NOT NULL,
  `discount_price` int(10) UNSIGNED DEFAULT NULL,
  `discount_active` tinyint(1) NOT NULL DEFAULT 0,
  `product_status` varchar(30) NOT NULL DEFAULT 'ready',
  `option_pricing_active` tinyint(1) NOT NULL DEFAULT 0,
  `option_media_active` tinyint(1) NOT NULL DEFAULT 0,
  `weight` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `desc` text DEFAULT NULL,
  `shipping` varchar(255) DEFAULT NULL,
  `in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `primary_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `artist_id`, `category_id`, `name`, `slug`, `sku`, `type`, `price`, `discount_price`, `discount_active`, `product_status`, `option_pricing_active`, `option_media_active`, `weight`, `desc`, `shipping`, `in_stock`, `stock`, `primary_image`, `created_at`, `updated_at`) VALUES
('prod-offical-bts-merch-11-esqlj', 2, 4, 'OFFICAL BTS MERCH 11', 'offical-bts-merch-11', 'B-003', 'accessory', 1200000, NULL, 0, 'ready', 0, 0, 3000, 'Hi Purples\r\nREADY STOCK SIAP KIRIM\r\nOFFICIAL BTS MERCH 11\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\n\r\nKONDISI BOX TIDAK 100% MULUS YA, terdapat sedikit damage karena pengiriman dari Korea ke Indonesia. \r\n\r\nDetail Produk :\r\n▪︎ Outbox\r\n▪︎ Orgel\r\n▪︎ Sticker\r\n▪︎ Orgel Frame Photo\r\n\r\nInclude :\r\n- Packing (dus, bubble wrap) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', 'Estimasi 1-7 hari pengiriman', 0, 0, '/images/products/643d0c11-ac52-4940-a901-2e4dd7d7f504.jpeg', '2026-06-04 18:23:19', '2026-06-07 20:05:22'),
('prod-offical-bts-merch-8-kwnyx', 2, 4, 'OFFICAL BTS MERCH 8', 'offical-bts-merch-8', 'B-002', 'accessory', 1200000, NULL, 0, 'ready', 0, 0, 2000, 'OFFICAL BTS MERCH 8\r\n\r\nHi Purples\r\nREADY STOCK SIAP KIRIM\r\nOFFICIAL BTS MERCH 8\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\n\r\nSEGEL PADA BOX SUDAH DIBUKA KARENA MELIHAT KONDISI PRODUK DALAM BOX, OVERALL UNTUK MASING - MASING PRODUK MASIH SEALED SEMUA YA. PLASTIK MASIH SEALED HANYA MENGECEK DARI LUAR SAJA TANPA MEMBUKA PLASTIK.\r\n\r\nDetail Produk :\r\n▪︎ Outbox\r\n▪︎ Weverse Magazine (Interview Photobook)\r\n▪︎ Premium Photo\r\n▪︎ Premium Photo Holder & Stand\r\n▪︎ Sticker\r\n\r\nInclude :\r\n- Packing (dus, bubble wrap) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', 'Estimasi 1-7 hari pengiriman', 1, 1, '/images/products/946b5d6d-2804-4eb1-88be-fbc446269be1.jpeg', '2026-06-04 18:18:15', '2026-06-21 00:32:33'),
('prod-offical-bts-shirt-denim-butter-e2mrj', 2, 2, 'OFFICAL BTS SHIRT DENIM BUTTER', 'offical-bts-shirt-denim-butter', NULL, 'apparel', 780000, NULL, 0, 'ready', 0, 0, 500, 'Hi Purples\r\nREADY STOCK SIAP KIRIM\r\nOFFICIAL BTS SHIRT DENIM BUTTER\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\nUkuran Shirt : XL\r\n\r\nInclude :\r\n- Packing (bubble wrap, plastik polymailer) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', NULL, 0, 0, '/images/products/cc697812-8da8-40d4-b9fe-0ab88b49c1a6.jpeg', '2026-06-04 18:09:34', '2026-06-07 21:38:02'),
('prod-official-armyst-zip-up-hoodie-wnxvt', 2, 3, 'OFFICIAL ARMYST ZIP-UP HOODIE BLACK BY JUNG KOOK BTS', 'official-armyst-zip-up-hoodie-black-by-jung-kook-bts', 'B-004', 'apparel', 3300000, NULL, 0, 'ready', 0, 0, 1000, 'OFFICIAL ARMYST ZIP-UP HOODIE BLACK BY JUNG KOOK BTS\r\n\r\nHi Purples\r\nBARANG READY STOCK SIAP KIRIM\r\n \r\nProduk 100% Official Weverse Artist-Made Collection by BTS Jung Kook Zip Up Hoodie\r\n\r\nProduk : [Jung Kook] ARMYST ZIP-UP Hoodie \r\nKondisi produk : NEW / good condi\r\nUkuran : L\r\nWarna  : BLACK\r\n\r\nPRICE : \r\nIDR 3.300.000 \r\n(Berat 1kg)\r\n*STOK 1*\r\n\r\nNote :\r\n- Inc Packing\r\n- Inc Freebies\r\n- Tidak menerima komplain tanpa video unboxing dengan jelas tanpa di jeda\r\n- Produk 100% new, good condi, sealed \r\n- Kerusakan yang terjadi akibat pengiriman diluar tanggung jawab seller\r\n- Harap bijak dalam memberikan penilaian produk dan bintang\r\n\r\nHappy shopping dear', 'Estimasi 1-7 hari pengiriman', 0, 0, '/images/products/0e6bd464-fc76-4db3-958a-55bf63891b9b.jpeg', '2026-06-06 02:31:41', '2026-06-11 17:17:08'),
('prod-official-keyring-lightstick-tx-8uq45', 3, 1, 'OFFICIAL KEYRING LIGHTSTICK TXT VER. 2', 'official-keyring-lightstick-txt-ver-2', 'T-001', 'accessory', 420000, NULL, 0, 'upcoming', 0, 0, 300, 'OFFICIAL KEYRING LIGHTSTICK TXT VER. 2\r\n\r\nHi Purples\r\nOFFICIAL KEYRING LIGHTSTICK TXT VER.2\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\n\r\nKONDISI BOX TIDAK 100% MULUS YA, terdapat sedikit damage karena pengiriman dari Korea ke Indonesia. \r\n\r\nDetail Produk :\r\n▪︎ Package \r\n▪︎ Keyring Lightstick\r\n\r\nInclude :\r\n- Packing (dus, bubble wrap) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', NULL, 1, 3, '/images/products/98d412a6-4088-4aa4-b89b-d1ece5d2f660.jpeg', '2026-06-04 18:28:58', '2026-06-07 21:50:03'),
('prod-official-light-stick-the-rose-zxniu', 1, 1, 'OFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP', 'official-light-stick-the-rose-v2-rostar-dan-rostar-strap', 'TR-001', 'accessory', 1200000, 1100000, 1, 'ready', 0, 0, 500, 'Hi Purples\r\n-READY SIAP KIRIM-\r\n\r\nOFFICIAL LIGHT STICK THE ROSE V2 : ROSTAR DAN ROSTAR STRAP \r\n100% OFFICIAL THE ROSE (pembelian dari web resmi The Rose)\r\nBUKTI CO SLIDE 3\r\nREAL PICT!!\r\nPRODUK NEW SEALED\r\n\r\n>BOX PRODUK TIDAK 100% MULUS, TERDAPAT SEDIKIT DAMAGE PADA BOX AKIBAT PENGIRIMAN DARI KOREA KE INDONESIA (CO = MENERIMA KONDISI BOX PRODUK YANG DAMAGE)<\r\n\r\nDetail Produk :\r\n- Package \r\n- Light Stick \r\n- Strap\r\n- User Manual\r\n\r\nInclude :\r\n- Packing (sesuai standar packing kami dus, bubble wrap) jika mau lebih aman lagi silahkan co tambahan bubble wrap\r\n\r\nNOTE :\r\n- Barang yang dijual 100% OFFICIAL THE ROSE (tidak menjual unofficial)\r\n- Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n- Jika ada item yang kurang atau cacat didalam box berarti kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n- Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n- Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n- Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n- Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', 'Estimasi 1-7 hari pengiriman', 1, 2, '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', '2026-06-01 00:10:21', '2026-06-21 02:07:22'),
('prod-official-lightstick-illit-gypir', 4, 1, 'OFFICIAL LIGHTSTICK ILLIT', 'official-lightstick-illit', 'I-001', 'accessory', 880000, NULL, 0, 'ready', 0, 0, 1000, 'OFFICIAL LIGHTSTICK ILLIT\r\n\r\nHi Purples\r\nOFFICIAL LIGHTSTICK ILLIT\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\n\r\nKONDISI BOX TIDAK 100% MULUS YA, terdapat sedikit damage karena pengiriman dari Korea ke Indonesia. \r\n\r\nDetail Produk :\r\n▪︎ Package \r\n▪︎ Light Stick \r\n▪︎ Petal Part 2ea\r\n▪︎ Dust Bag \r\n▪︎ Strap\r\n▪︎ Instruction Manual\r\n▪︎ Photocard 5ea\r\n\r\nInclude :\r\n- Packing (dus, bubble wrap) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', 'Estimasi 1-7 hari pengiriman', 0, 0, '/images/products/8794c6c5-11d0-47ba-9ad4-fc4e2720cf94.jpeg', '2026-06-04 18:26:14', '2026-06-21 02:23:41'),
('prod-official-treasure-lightstick-k-hmtqh', 5, 1, 'OFFICIAL TREASURE LIGHTSTICK KIT', 'official-treasure-lightstick-kit', 'T-001', 'accessory', 480000, NULL, 0, 'ready', 0, 0, 1000, 'Hi Purples\r\nOFFICIAL LIGHTSTICK KIT TREASURE\r\n\r\n100% Official WEVERSE\r\nReal pict\r\nPRODUK NEW SEALED\r\n\r\nKONDISI BOX TIDAK 100% MULUS YA, terdapat sedikit damage karena pengiriman dari Korea ke Indonesia. \r\n\r\nDetail Produk :\r\n▪︎ Package \r\n▪︎ Photocard \r\n▪︎ Pom poms/Crystals\r\n▪︎ Hologram Sticker\r\n▪︎ Screwdriver + Screw\r\n\r\nInclude :\r\n- Packing (dus, bubble wrap) \r\n- Freebies \r\n\r\nNOTE :\r\n▪︎ Barang yang dijual OFFICIAL WEVERSE (tidak menjual unofficial)\r\n▪︎ Produk dikirim dalam keadaan baru (SEALED) / masih segel\r\n▪︎ Jika ada item yang kurang atau cacat didalam box berarti itu kesalahan dari produksi bukan kesalahan seller karena seller tidak cek isi di dalam box, hanya cek bagian luar box saja karena produk dalam keadaan sealed\r\n▪︎ Jika ada kerusakan yang terjadi akibat pengiriman di luar tanggung jawab seller karena kami sudah packing seaman mungkin menggunakan kardus dan bubble wrap\r\n▪︎ Harap sertakan video unboxing dengan jelas tanpa di jeda dan di edit jika terdapat komplain (silahkan chat admin)\r\n▪︎ Tidak terima komplain atau pengembalian barang tanpa menyertakan video unboxing dengan jelas \r\n▪︎ Harap dibaca terlebih dahulu sebelum melakukan check out\r\n\r\nSo, Happy Shopping Dear', 'Estimasi 1-7 hari pengiriman', 1, 2, '/images/products/e486a128-f255-4ff2-94b7-9038555d62ba.jpeg', '2026-06-04 18:14:50', '2026-06-04 18:14:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `path`, `sort_order`, `created_at`, `updated_at`) VALUES
(64, 'prod-offical-bts-shirt-denim-butter-e2mrj', '/images/products/cc697812-8da8-40d4-b9fe-0ab88b49c1a6.jpeg', 0, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(65, 'prod-offical-bts-shirt-denim-butter-e2mrj', '/images/products/76ddd882-8a30-4e8b-8dfe-65a799c5e4d4.jpeg', 1, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(66, 'prod-offical-bts-shirt-denim-butter-e2mrj', '/images/products/c5ea5d46-5b25-4f2f-92c5-4ce6f7d72d73.jpeg', 2, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(67, 'prod-offical-bts-shirt-denim-butter-e2mrj', '/images/products/68d58399-005a-4d16-ade4-ade2585fc01d.jpeg', 3, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(68, 'prod-offical-bts-shirt-denim-butter-e2mrj', '/images/products/2397d7b2-6335-47c5-8e7c-0e346f64547b.jpeg', 4, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(69, 'prod-official-treasure-lightstick-k-hmtqh', '/images/products/e486a128-f255-4ff2-94b7-9038555d62ba.jpeg', 0, '2026-06-04 18:14:50', '2026-06-04 18:14:50'),
(70, 'prod-official-treasure-lightstick-k-hmtqh', '/images/products/5c08b846-2a7a-4778-baef-4d85d56e5aa8.jpeg', 1, '2026-06-04 18:14:50', '2026-06-04 18:14:50'),
(71, 'prod-official-treasure-lightstick-k-hmtqh', '/images/products/8cde9245-1618-470e-a628-63ecb526e74d.jpeg', 2, '2026-06-04 18:14:50', '2026-06-04 18:14:50'),
(72, 'prod-official-treasure-lightstick-k-hmtqh', '/images/products/fb7d2ec3-f8e2-44a9-8430-2a33fdc3e4d1.jpeg', 3, '2026-06-04 18:14:50', '2026-06-04 18:14:50'),
(79, 'prod-offical-bts-merch-8-kwnyx', '/images/products/946b5d6d-2804-4eb1-88be-fbc446269be1.jpeg', 0, '2026-06-04 18:20:29', '2026-06-04 18:20:29'),
(80, 'prod-offical-bts-merch-8-kwnyx', '/images/products/f2a368b3-d738-44e0-a4ec-bc8ff7be558c.jpeg', 1, '2026-06-04 18:20:29', '2026-06-04 18:20:29'),
(81, 'prod-offical-bts-merch-8-kwnyx', '/images/products/6049a04e-12aa-40a6-81c2-5e0cbe51e022.jpeg', 2, '2026-06-04 18:20:29', '2026-06-04 18:20:29'),
(82, 'prod-offical-bts-merch-11-esqlj', '/images/products/643d0c11-ac52-4940-a901-2e4dd7d7f504.jpeg', 0, '2026-06-04 18:23:19', '2026-06-04 18:23:19'),
(83, 'prod-offical-bts-merch-11-esqlj', '/images/products/60726cac-80e7-4592-af9c-a28663d98e6b.jpeg', 1, '2026-06-04 18:23:19', '2026-06-04 18:23:19'),
(84, 'prod-offical-bts-merch-11-esqlj', '/images/products/0b9d8fed-286a-4ca6-ad62-94d271217434.jpeg', 2, '2026-06-04 18:23:19', '2026-06-04 18:23:19'),
(85, 'prod-official-lightstick-illit-gypir', '/images/products/8794c6c5-11d0-47ba-9ad4-fc4e2720cf94.jpeg', 0, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(86, 'prod-official-lightstick-illit-gypir', '/images/products/efde577e-3995-4403-8343-be80f308e0fb.jpeg', 1, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(87, 'prod-official-lightstick-illit-gypir', '/images/products/e0dc2116-17b2-4e49-8ac2-0ae5562758df.jpeg', 2, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(88, 'prod-official-lightstick-illit-gypir', '/images/products/8d27df68-f43a-4af2-8ed2-5382be0883f2.jpeg', 3, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(89, 'prod-official-lightstick-illit-gypir', '/images/products/ba6191fc-2435-4b95-8069-728b312b868f.jpeg', 4, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(90, 'prod-official-lightstick-illit-gypir', '/images/products/32920e51-60c5-41f6-a9b4-64a3f5fe51f5.jpeg', 5, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(91, 'prod-official-lightstick-illit-gypir', '/images/products/ea4cf6a1-e00d-430e-824a-2edb6702856e.jpeg', 6, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(92, 'prod-official-lightstick-illit-gypir', '/images/products/7ff7aac9-dcbd-4e07-84ef-b4675f2d6f89.jpeg', 7, '2026-06-04 18:26:14', '2026-06-04 18:26:14'),
(114, 'prod-official-light-stick-the-rose-zxniu', '/images/products/b14dc913-e7eb-4601-b1f9-f7510fa73678.jpeg', 0, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(115, 'prod-official-light-stick-the-rose-zxniu', '/images/products/8fc6ab74-3873-493c-968e-a0f8fbf4ab70.jpeg', 1, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(116, 'prod-official-light-stick-the-rose-zxniu', '/images/products/1ed9bc0a-9000-4d6a-8f78-071587066bd1.jpeg', 2, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(117, 'prod-official-light-stick-the-rose-zxniu', '/images/products/992974a0-98e4-4b9b-a279-674f102f1e02.jpeg', 3, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(118, 'prod-official-light-stick-the-rose-zxniu', '/images/products/3ba23379-3c35-4c58-8b1b-49062fd3cc35.jpeg', 4, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(119, 'prod-official-light-stick-the-rose-zxniu', '/images/products/d7dfcbb7-90a3-42bd-8dd8-73b75b5aac71.jpeg', 5, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(120, 'prod-official-light-stick-the-rose-zxniu', '/images/products/60529d51-ecc8-467a-b786-985ee3c91487.jpeg', 6, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(121, 'prod-official-light-stick-the-rose-zxniu', '/images/products/bca6705c-27bb-41c6-a27c-8a076ba19887.jpeg', 7, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(135, 'prod-official-keyring-lightstick-tx-8uq45', '/images/products/98d412a6-4088-4aa4-b89b-d1ece5d2f660.jpeg', 0, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(136, 'prod-official-keyring-lightstick-tx-8uq45', '/images/products/372f73ba-da73-4f68-9097-871308d7c89c.jpeg', 1, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(137, 'prod-official-keyring-lightstick-tx-8uq45', '/images/products/e1dec84b-89c5-4795-9156-1195090cd186.jpeg', 2, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(138, 'prod-official-keyring-lightstick-tx-8uq45', '/images/products/7dec5e22-c1ed-4c77-8014-e034cae3a759.jpeg', 3, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(139, 'prod-official-keyring-lightstick-tx-8uq45', '/images/products/b76a00ca-5a2c-458d-89cf-9170ec921ebc.jpeg', 4, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(144, 'prod-official-armyst-zip-up-hoodie-wnxvt', '/images/products/0e6bd464-fc76-4db3-958a-55bf63891b9b.jpeg', 0, '2026-06-11 17:17:08', '2026-06-11 17:17:08'),
(145, 'prod-official-armyst-zip-up-hoodie-wnxvt', '/images/products/348d669b-13ab-4d11-b655-64ec5337a631.jpeg', 1, '2026-06-11 17:17:08', '2026-06-11 17:17:08'),
(146, 'prod-official-armyst-zip-up-hoodie-wnxvt', '/images/products/014d1e20-1773-4b67-8f6f-f73a173c34a6.jpeg', 2, '2026-06-11 17:17:08', '2026-06-11 17:17:08'),
(147, 'prod-official-armyst-zip-up-hoodie-wnxvt', '/images/products/33e64ae6-3eca-4a0b-9e37-c8a86ba0f431.jpeg', 3, '2026-06-11 17:17:08', '2026-06-11 17:17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_option_stocks`
--

CREATE TABLE `product_option_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `variant` varchar(255) NOT NULL DEFAULT '',
  `size` varchar(255) NOT NULL DEFAULT '',
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `price` int(10) UNSIGNED DEFAULT NULL,
  `discount_price` int(10) UNSIGNED DEFAULT NULL,
  `discount_active` tinyint(1) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_option_stocks`
--

INSERT INTO `product_option_stocks` (`id`, `product_id`, `variant`, `size`, `stock`, `price`, `discount_price`, `discount_active`, `image_path`, `created_at`, `updated_at`) VALUES
(14, 'prod-offical-bts-shirt-denim-butter-e2mrj', '', 'XL', 0, NULL, NULL, 0, NULL, '2026-06-04 18:10:41', '2026-06-07 21:38:02'),
(15, 'prod-official-treasure-lightstick-k-hmtqh', '', '', 2, NULL, NULL, 0, NULL, '2026-06-04 18:14:50', '2026-06-04 18:14:50'),
(18, 'prod-offical-bts-merch-8-kwnyx', '', '', 1, NULL, NULL, 0, NULL, '2026-06-04 18:20:29', '2026-06-21 00:32:33'),
(19, 'prod-offical-bts-merch-11-esqlj', '', '', 0, NULL, NULL, 0, NULL, '2026-06-04 18:23:19', '2026-06-07 20:05:22'),
(20, 'prod-official-lightstick-illit-gypir', '', '', 0, NULL, NULL, 0, NULL, '2026-06-04 18:26:14', '2026-06-21 02:23:41'),
(26, 'prod-official-light-stick-the-rose-zxniu', 'Lightstick', '', 1, NULL, NULL, 0, NULL, '2026-06-07 02:29:12', '2026-06-21 02:07:22'),
(27, 'prod-official-light-stick-the-rose-zxniu', 'Strap', '', 1, NULL, NULL, 0, NULL, '2026-06-07 02:29:12', '2026-06-20 05:07:11'),
(31, 'prod-official-keyring-lightstick-tx-8uq45', '', '', 3, NULL, NULL, 0, NULL, '2026-06-07 21:50:03', '2026-06-07 21:50:03'),
(33, 'prod-official-armyst-zip-up-hoodie-wnxvt', 'Black', 'L', 0, NULL, NULL, 0, NULL, '2026-06-11 17:17:08', '2026-06-11 17:17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 'prod-offical-bts-shirt-denim-butter-e2mrj', 'XL', 0, '2026-06-04 18:10:41', '2026-06-04 18:10:41'),
(8, 'prod-official-armyst-zip-up-hoodie-wnxvt', 'L', 0, '2026-06-11 17:17:08', '2026-06-11 17:17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `name`, `sort_order`, `created_at`, `updated_at`) VALUES
(17, 'prod-official-light-stick-the-rose-zxniu', 'Lightstick', 0, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(18, 'prod-official-light-stick-the-rose-zxniu', 'Strap', 1, '2026-06-07 02:29:12', '2026-06-07 02:29:12'),
(22, 'prod-official-armyst-zip-up-hoodie-wnxvt', 'Black', 0, '2026-06-11 17:17:08', '2026-06-11 17:17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--


-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `shipping_postal_code` varchar(10) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `phone`, `address`, `shipping_postal_code`, `is_admin`) VALUES
(1, 'Admin Demo', 'admin@example.com', NULL, '$2y$12$L8FOWPUXtlT3V8SEJ8jmEOhmA9Fm8vSOqI5XRoPxqG2lBFZIej2Ku', NULL, '2026-05-31 02:49:43', '2026-05-31 03:30:54', '081234567890', 'Alamat admin demo', '80582', 1),
(3, 'Customer Demo', 'customer@example.com', NULL, '$2y$12$L8FOWPUXtlT3V8SEJ8jmEOhmA9Fm8vSOqI5XRoPxqG2lBFZIej2Ku', NULL, '2026-05-31 02:57:40', '2026-06-07 21:29:43', '081234567891', 'Alamat customer demo', '83663', 0),
(4, 'Admin Toko', 'admin.purplestuff@example.com', NULL, '$2y$12$L8FOWPUXtlT3V8SEJ8jmEOhmA9Fm8vSOqI5XRoPxqG2lBFZIej2Ku', NULL, '2026-05-31 03:27:00', '2026-06-07 22:06:22', '081234567892', 'Alamat toko demo', '80582', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(4, 3, 'prod-official-light-stick-the-rose-zxniu', '2026-06-06 02:48:23', '2026-06-06 02:48:23'),
(5, 3, 'prod-official-treasure-lightstick-k-hmtqh', '2026-06-07 21:35:08', '2026-06-07 21:35:08');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin_push_subscriptions`
--
ALTER TABLE `admin_push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_push_subscriptions_fcm_token_unique` (`fcm_token`) USING HASH,
  ADD KEY `admin_push_subscriptions_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `artists_slug_unique` (`slug`);

--
-- Indeks untuk tabel `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_user_id_key_unique` (`user_id`,`key`),
  ADD KEY `cart_items_user_id_product_id_index` (`user_id`,`product_id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newsletter_subscribers_email_unique` (`email`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_status_created_at_index` (`status`,`created_at`),
  ADD KEY `orders_payment_status_created_at_index` (`payment_status`,`created_at`),
  ADD KEY `orders_payment_deadline_at_index` (`payment_deadline_at`),
  ADD KEY `orders_payment_expired_at_index` (`payment_expired_at`),
  ADD KEY `orders_stock_reserved_at_index` (`stock_reserved_at`),
  ADD KEY `orders_stock_released_at_index` (`stock_released_at`),
  ADD KEY `orders_cancelled_email_sent_at_idx` (`customer_order_cancelled_email_sent_at`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indeks untuk tabel `order_tracking_histories`
--
ALTER TABLE `order_tracking_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_tracking_histories_order_id_foreign` (`order_id`),
  ADD KEY `order_tracking_histories_admin_user_id_foreign` (`admin_user_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_artist_id_foreign` (`artist_id`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indeks untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indeks untuk tabel `product_option_stocks`
--
ALTER TABLE `product_option_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_option_stocks_unique_option` (`product_id`,`variant`,`size`),
  ADD KEY `product_option_stocks_product_stock_idx` (`product_id`,`stock`);

--
-- Indeks untuk tabel `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_sizes_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indeks untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `wishlists_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin_push_subscriptions`
--
ALTER TABLE `admin_push_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `artists`
--
ALTER TABLE `artists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `order_tracking_histories`
--
ALTER TABLE `order_tracking_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT untuk tabel `product_option_stocks`
--
ALTER TABLE `product_option_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `admin_push_subscriptions`
--
ALTER TABLE `admin_push_subscriptions`
  ADD CONSTRAINT `admin_push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `order_tracking_histories`
--
ALTER TABLE `order_tracking_histories`
  ADD CONSTRAINT `order_tracking_histories_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_tracking_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_artist_id_foreign` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_option_stocks`
--
ALTER TABLE `product_option_stocks`
  ADD CONSTRAINT `product_option_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
