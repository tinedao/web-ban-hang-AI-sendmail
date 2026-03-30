-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: sql204.infinityfree.com
-- Thời gian đã tạo: Th3 29, 2026 lúc 09:18 PM
-- Phiên bản máy phục vụ: 11.4.10-MariaDB
-- Phiên bản PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `if0_41419290_crowne`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(2, 'Áo'),
(6, 'Đồ lưu niệm'),
(5, 'Nón'),
(7, 'Phụ kiện'),
(1, 'Quần'),
(4, 'Vòng cổ'),
(3, 'Vòng tay');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `slug` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `events`
--

INSERT INTO `events` (`id`, `slug`, `name`, `start_date`, `end_date`, `is_enabled`, `priority`, `created_at`) VALUES
(1, 'tet', 'Tết Âm lịch 2026', '2026-02-01', '2026-02-15', 1, 100, '2026-01-01 00:00:00'),
(2, 'gpmnam', 'Kỷ niệm 30/4 năm 2026', '2026-04-25', '2026-05-02', 1, 90, '2026-01-01 00:00:00'),
(3, 'quockhanh', 'Quốc khánh 2/9 năm 2026', '2026-08-28', '2026-09-05', 1, 80, '2026-01-01 00:00:00'),
(4, 'noel', 'Noel 2026', '2026-12-15', '2026-12-31', 1, 70, '2026-01-01 00:00:00'),
(5, 'default', 'Sản phẩm thường ngày', '2026-01-01', '2026-12-31', 1, 10, '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `is_admin`, `is_read`, `created_at`) VALUES
(1, 2, 'xin chào', 0, 0, '2026-03-23 11:06:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `phone`, `address`, `payment_method`, `total`, `status`, `created_at`) VALUES
(1, 2, 'TIến', '0979499802', 'pt', 'cod', '800000.00', 'pending', '2026-03-23 11:08:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 8, 'Áo tết family GUYS', 1, '800000.00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `event_slug` varchar(20) NOT NULL DEFAULT 'default',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_id`, `event_slug`, `created_at`) VALUES
(1, 'Cây lưu niệm tết', 'Cây hoa cây trái đâm lộc hehe', '200000.00', 10, '1773126744_dlnt4.jpg', 6, 'tet', '2026-03-10 14:12:24'),
(2, 'Móc treo Stitch Bính Ngọ', 'Móc treo stitch hài hước cutê', '300000.00', 20, '1773126963_dlnmt3.jpg', 6, 'tet', '2026-03-10 14:16:03'),
(3, 'Móc treo ông dẹer', 'Móc treo ông địa trông đẹp như pịaaa', '50000.00', 13, '1773127103_dlnt2.jpg', 6, 'tet', '2026-03-10 14:18:23'),
(4, 'Móc treo ông dẹer version tu (2)', 'Móc treo ông địa nhưng ccutê hơnm', '30000.00', 43, '1773127207_dlnt1.jpg', 6, 'tet', '2026-03-10 14:20:07'),
(5, 'Áo tết bé gái đỏ chót', 'Áo tết tóc bết là biu ti phùn', '600000.00', 52, '1773127277_aotet1.jpg', 2, 'tet', '2026-03-10 14:20:59'),
(6, 'Áo tết gia đình rập rình đón xuân tóc đỏ', 'Xập rình tưng bừng đón xuân CA', '700000.00', 24, '1773127335_aotet4.jpg', 2, 'tet', '2026-03-10 14:22:15'),
(7, 'Áo tết cặp so beautiFÙN', 'cặp đôi nà cắp bồ', '500000.00', 10, '1773127422_aotet3.jpg', 2, 'tet', '2026-03-10 14:23:42'),
(8, 'Áo tết family GUYS', 'ÁO TẾT ĐÓN SPRIT', '800000.00', 10, '1773127466_aotet2.jpg', 2, 'tet', '2026-03-10 14:24:26'),
(9, 'Áo Việt Nam nón lá', 'Việt lam lón ná 1 tá iu thưn', '200000.00', 10, '1773128209_vietnameseshirt.jpg', 2, 'quockhanh', '2026-03-10 14:36:49'),
(10, 'Áo Việt Nam cờ đỏ', 'Việt Nam cờ đỏ chứng tỏ người việt', '300000.00', 10, '1773128262_ao29-3.jpg', 2, 'quockhanh', '2026-03-10 14:37:42'),
(11, 'Áo Việt Nam đẹp 2/9', 'Áo quốc khánh 2/9', '700000.00', 10, '1773128600_ao29-2.jpg', 2, 'quockhanh', '2026-03-10 14:43:20'),
(12, 'Áo việt nam basic', 'Vietnamese shirt basic cotton', '200000.00', 10, '1773128623_ao29.jpg', 2, 'quockhanh', '2026-03-10 14:43:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_length` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `password_length`, `created_at`) VALUES
(1, 'Mai Anh', 'mai.anh@example.com', '0976795872', '$2y$10$/DUtpqlWH.e6MbJXLBjqT.4F71enUz4s7c/HvDvGYJboo1qiHcxKK', 7, '2026-02-02 07:13:48'),
(2, 'TIến', NULL, '0979499802', '$2y$10$rbMA2oq3eOO7aVYJbwpwWOsWngFRGPAbCwILaO.r6EtdotrkmnRcu', 8, '2026-03-23 04:03:57');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_categories_name` (`name`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_events_slug` (`slug`),
  ADD KEY `idx_events_date` (`is_enabled`,`start_date`,`end_date`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_user` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_order` (`order_id`),
  ADD KEY `fk_items_product` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_category` (`category_id`),
  ADD KEY `idx_products_event_slug` (`event_slug`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_phone` (`phone`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
