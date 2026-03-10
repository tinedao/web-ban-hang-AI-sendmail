-- --------------------------------------------------------
-- CSDL cho dự án web bán đồ lưu niệm theo sự kiện
-- Hỗ trợ tiếng Việt chuẩn UTF-8 (utf8mb4 + utf8mb4_vietnamese_ci)
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci */;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Bảng danh mục
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Quần'),
(2, 'Áo'),
(3, 'Vòng tay'),
(4, 'Vòng cổ'),
(5, 'Nón'),
(6, 'Đồ lưu niệm'),
(7, 'Phụ kiện');

-- --------------------------------------------------------
-- Bảng sự kiện theo năm
-- --------------------------------------------------------
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_events_slug` (`slug`),
  KEY `idx_events_date` (`is_enabled`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `events` (`id`, `slug`, `name`, `start_date`, `end_date`, `is_enabled`, `priority`, `created_at`) VALUES
(1, 'tet', 'Tết Âm lịch 2026', '2026-01-01', '2026-03-31', 1, 100, '2026-01-01 00:00:00'),
(2, 'gpmnam', 'Kỷ niệm 30/4 năm 2026', '2026-04-01', '2026-06-30', 1, 90, '2026-01-01 00:00:00'),
(3, 'quockhanh', 'Quốc khánh 2/9 năm 2026', '2026-07-01', '2026-09-30', 1, 80, '2026-01-01 00:00:00'),
(4, 'noel', 'Noel 2026', '2026-10-01', '2026-12-31', 1, 70, '2026-01-01 00:00:00'),
(5, 'default', 'Sản phẩm thường ngày', '2026-01-01', '2026-12-31', 1, 10, '2026-01-01 00:00:00');

-- --------------------------------------------------------
-- Bảng người dùng
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_length` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `password_length`, `created_at`) VALUES
(1, 'Mai Anh', 'mai.anh@example.com', '0976795872', '$2y$10$/DUtpqlWH.e6MbJXLBjqT.4F71enUz4s7c/HvDvGYJboo1qiHcxKK', 7, '2026-02-02 14:13:48');

-- --------------------------------------------------------
-- Bảng sản phẩm
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `event_slug` varchar(20) NOT NULL DEFAULT 'default',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_products_category` (`category_id`),
  KEY `idx_products_event_slug` (`event_slug`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_id`, `event_slug`, `created_at`) VALUES
(1, 'Áo thun Tết Phúc Lộc', 'Áo thun form rộng, họa tiết Tết đỏ vàng, phù hợp đi chơi xuân.', 350000.00, 40, 'placeholder.jpg', 2, 'tet', '2026-01-20 09:00:00'),
(2, 'Nón lưỡi trai Tết Đỏ Vàng', 'Nón lưỡi trai phối màu lễ hội, chất liệu thoáng mát.', 220000.00, 55, 'placeholder.jpg', 5, 'tet', '2026-01-22 10:30:00'),
(3, 'Vòng tay may mắn ngày Tết', 'Vòng tay phong cách lễ hội, thích hợp làm quà đầu năm.', 180000.00, 70, 'placeholder.jpg', 3, 'tet', '2026-01-23 14:00:00'),
(4, 'Quần jogger chủ đề 30/4', 'Quần jogger năng động, dễ phối áo sự kiện.', 420000.00, 35, 'placeholder.jpg', 1, 'gpmnam', '2026-04-10 11:00:00'),
(5, 'Áo polo kỷ niệm 30/4', 'Áo polo in biểu tượng kỷ niệm, mặc thoải mái cả ngày.', 390000.00, 32, 'placeholder.jpg', 2, 'gpmnam', '2026-04-11 15:20:00'),
(6, 'Phụ kiện cờ mini 30/4', 'Set phụ kiện cầm tay dùng cho hoạt động sự kiện.', 95000.00, 120, 'placeholder.jpg', 7, 'gpmnam', '2026-04-12 16:00:00'),
(7, 'Áo sơ mi Quốc khánh 2/9', 'Áo sơ mi tông đỏ trang trọng, phù hợp ngày lễ.', 480000.00, 28, 'placeholder.jpg', 2, 'quockhanh', '2026-08-20 10:00:00'),
(8, 'Nón bucket Quốc khánh', 'Nón bucket trẻ trung, nổi bật theo chủ đề 2/9.', 210000.00, 45, 'placeholder.jpg', 5, 'quockhanh', '2026-08-21 12:00:00'),
(9, 'Vòng cổ biểu tượng Việt Nam', 'Vòng cổ thời trang, thiết kế biểu tượng tinh gọn.', 260000.00, 50, 'placeholder.jpg', 4, 'quockhanh', '2026-08-21 12:30:00'),
(10, 'Áo len Noel Snow', 'Áo len mềm, giữ ấm tốt, phù hợp mùa lễ cuối năm.', 520000.00, 24, 'placeholder.jpg', 2, 'noel', '2026-12-05 09:30:00'),
(11, 'Nón len Noel', 'Nón len phối màu đỏ xanh, dùng cho tiệc Giáng sinh.', 190000.00, 60, 'placeholder.jpg', 5, 'noel', '2026-12-06 11:30:00'),
(12, 'Đồ trang trí Noel để bàn', 'Set đồ lưu niệm mini trang trí bàn làm việc và góc học tập.', 150000.00, 80, 'placeholder.jpg', 6, 'noel', '2026-12-07 13:00:00'),
(13, 'Quần kaki cơ bản sự kiện', 'Quần kaki trung tính, phối tốt với áo sự kiện.', 460000.00, 30, 'placeholder.jpg', 1, 'default', '2026-03-01 09:00:00'),
(14, 'Túi đeo chéo lưu niệm', 'Túi đeo chéo đa năng, đựng vật dụng cá nhân gọn nhẹ.', 275000.00, 65, 'placeholder.jpg', 7, 'default', '2026-03-02 09:45:00');

-- --------------------------------------------------------
-- Bảng liên hệ
-- --------------------------------------------------------
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'Nguyễn Thanh', 'nguyenthanh@example.com', '0909123456', 'Tư vấn combo Tết', 'Mình cần tư vấn combo quà tặng cho công ty khoảng 50 phần.', '2026-02-03 08:00:00');

-- --------------------------------------------------------
-- Bảng đơn hàng
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_orders_user` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `orders` (`id`, `user_id`, `name`, `phone`, `address`, `payment_method`, `total`, `status`, `created_at`) VALUES
(1, 1, 'Mai Anh', '0976795872', 'Việt Trì, Phú Thọ', 'cod', 700000.00, 'pending', '2026-02-02 09:16:53');

-- --------------------------------------------------------
-- Bảng chi tiết đơn hàng
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_items_order` (`order_id`),
  KEY `fk_items_product` (`product_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 1, 'Áo thun Tết Phúc Lộc', 2, 350000.00);

-- --------------------------------------------------------
-- Bảng tin nhắn hỗ trợ
-- --------------------------------------------------------
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_messages_user` (`user_id`),
  CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

INSERT INTO `messages` (`id`, `user_id`, `message`, `is_admin`, `is_read`, `created_at`) VALUES
(1, 1, 'Shop ơi, còn áo thun Tết size L không?', 0, 1, '2026-02-01 19:00:00'),
(2, 1, 'Dạ còn ạ, bên em hỗ trợ giữ hàng trong ngày.', 1, 1, '2026-02-01 19:02:00');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
