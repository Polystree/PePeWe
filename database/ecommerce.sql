-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for ecommerce_v3
CREATE DATABASE IF NOT EXISTS `ecommerce_v3` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ecommerce_v3`;

-- Dumping structure for table ecommerce_v3.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `cartId` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `image_path` varchar(255) DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `productId` int DEFAULT NULL,
  PRIMARY KEY (`cartId`),
  UNIQUE KEY `unique_user_product` (`userId`,`productId`),
  KEY `FK_cart_users` (`userId`),
  KEY `FK_cart_products` (`productId`),
  CONSTRAINT `FK_cart_products` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE,
  CONSTRAINT `FK_cart_users` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.cart: ~5 rows (approximately)
DELETE FROM `cart`;
INSERT INTO `cart` (`cartId`, `product_name`, `price`, `quantity`, `image_path`, `userId`, `productId`) VALUES
	(36, 'Google Pixel 8 Pro', 15999000, 2, '../assets/img/product/1733487418-google-pixel-8-pro.png', 2, 3),
	(38, 'Xiaomi 14 Pro', 12999000, 1, '../assets/img/product/1733487379-xiaomi-14-pro.jpg', 2, 4),
	(39, 'Samsung Galaxy S24 Ultra', 18999000, 3, '../assets/img/product/1733487293-samsung-galaxy-s24-ultra.jpg', 2, 2),
	(40, 'Google Pixel 8 Pro', 15999000, 2, '../assets/img/product/1733487418-google-pixel-8-pro.png', 6, 3),
	(41, 'iPhone 16 Pro', 21999999, 1, '../assets/img/product/7224-iphone-16-pro.webp', 2, 10);

-- Dumping structure for table ecommerce_v3.coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `discount` int DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.coupons: ~2 rows (approximately)
DELETE FROM `coupons`;
INSERT INTO `coupons` (`id`, `code`, `discount`, `expiry_date`) VALUES
	(10, 'GRATIS', 100, '2024-12-31'),
	(11, '5PERCENT', 5, '2032-12-17');

-- Dumping structure for table ecommerce_v3.discounts
CREATE TABLE IF NOT EXISTS `discounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `discount_percent` int DEFAULT '0',
  `start_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_flash_sale` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.discounts: ~3 rows (approximately)
DELETE FROM `discounts`;
INSERT INTO `discounts` (`id`, `product_id`, `discount_percent`, `start_date`, `end_date`, `is_flash_sale`) VALUES
	(2, 11, 15, '2024-12-06 08:04:00', '2024-12-26 08:04:00', 1),
	(3, 3, 20, '2024-12-06 08:04:00', '2024-12-26 08:04:00', 1),
	(4, 4, 12, '2024-12-06 08:04:00', '2024-12-26 08:04:00', 0);

-- Dumping structure for table ecommerce_v3.products
CREATE TABLE IF NOT EXISTS `products` (
  `productId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `description` text,
  `category` varchar(50) DEFAULT NULL,
  `sold_count` int DEFAULT '0',
  `view_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_featured` tinyint(1) DEFAULT '0',
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.products: ~9 rows (approximately)
DELETE FROM `products`;
INSERT INTO `products` (`productId`, `name`, `price`, `image_path`, `url`, `quantity`, `description`, `category`, `sold_count`, `view_count`, `created_at`, `updated_at`, `is_featured`, `status`) VALUES
	(2, 'Samsung Galaxy S24 Ultra', 18999000, '../assets/img/product/1733487293-samsung-galaxy-s24-ultra.jpg', '0', 45, 'Features Snapdragon 8 Gen 3, 200MP camera, and S Pen functionality', 'Smartphone', 10, 27, '2024-12-06 08:04:04', '2024-12-16 21:47:36', 1, 'active'),
	(3, 'Google Pixel 8 Pro', 15999000, '../assets/img/product/1733487418-google-pixel-8-pro.png', '0', 30, 'Advanced AI features, exceptional camera capabilities, and pure Android experience', 'Smartphone', 22, 210, '2024-12-06 08:04:04', '2024-12-16 19:38:56', 0, 'active'),
	(4, 'Xiaomi 14 Pro', 12999000, '../assets/img/product/1733487379-xiaomi-14-pro.jpg', '0', 40, 'Leica optics, Snapdragon 8 Gen 3, and 120W fast charging', 'Smartphone', 34, 885, '2024-12-06 08:04:04', '2024-12-16 21:47:46', 1, 'active'),
	(9, 'Xiaomi 14T', 6499999, '../assets/img/product/6175-xiaomi-14t.jpg', '0', 32, 'Leica lens, Dimensity 8300 Ultra, and 67W fast charging', 'Smartphone', 0, 22, '2024-12-16 18:34:26', '2024-12-17 06:31:11', 0, 'active'),
	(10, 'iPhone 16 Pro', 21999999, '../assets/img/product/7224-iphone-16-pro.webp', '0', 99, 'Apple A18 Pro, 120Hz LTPO Super Retina XDR OLED, iOS 18, upgradable to iOS 18.2', 'Smartphone', 0, 18, '2024-12-16 18:59:08', '2024-12-17 04:43:41', 1, 'active'),
	(11, 'ASUS ROG Zephyrus G14 (2024)', 30999000, '../assets/img/product/3191-asus-rog-zephyrus-g14-2024-.png', '0', 16, 'Windows 11 Home, AMD Ryzen™ 7 8845HS, NVIDIA® GeForce RTX™ 4050, 16GB LPDDR5X 6400 on board, 1TB PCIe® 4.0 NVMe™ M.2 SSD, 120Hz ROG Nebula Display 14-inch 3K (2880 x 1800) OLED 16:10 aspect ratio', 'Laptop', 0, 7, '2024-12-16 19:07:51', '2024-12-16 20:06:04', 1, 'active'),
	(13, 'ROG Ally X', 13999000, '../assets/img/product/4017-rog-ally-x.jpg', '0', 18, 'Windows 11 Home (64-bit), AMD Ryzen™ Z1 Extreme, 7” FHD (1920 x 1080) 16:9 IPS Display, 12GB + 12GB LPDDR5, 1TB M.2 NVMe™ PCIe® 4.0 SSD', 'Tablet', 0, 13, '2024-12-16 19:35:20', '2024-12-16 21:47:40', 0, 'active'),
	(14, 'Samsung Galaxy Buds3 Pro', 3299000, '../assets/img/product/5982-galaxy-buds3-pro.avif', '0', 41, 'Ultimate Hi-Fi, Iconic design with Blade Lights, Adaptive Noise Control', 'Accessories', 0, 1, '2024-12-16 21:35:25', '2024-12-16 21:39:31', 0, 'active'),
	(15, 'tesset2', 45325, '../assets/img/product/3784-tesset.jpg', '0', 24, 'esrser', 'Smartphone', 0, 0, '2024-12-16 21:40:07', '2024-12-16 21:43:02', 0, 'inactive');

-- Dumping structure for table ecommerce_v3.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `review` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `productId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Index 4` (`userId`,`productId`),
  KEY `FK_review_products` (`productId`),
  CONSTRAINT `FK_review_products` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE,
  CONSTRAINT `FK_review_users` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.reviews: ~5 rows (approximately)
DELETE FROM `reviews`;
INSERT INTO `reviews` (`id`, `review`, `userId`, `productId`) VALUES
	(28, 'bagus', 2, 4),
	(29, 'bagussss', 2, 3),
	(31, 'good', 6, 4),
	(32, 'oke', 4, 4),
	(33, 'mastin? good', 4, 3);

-- Dumping structure for table ecommerce_v3.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '/assets/img/Generic avatar.svg',
  `contact_details` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Contact details',
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.users: ~4 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_image`, `contact_details`, `security_question`, `security_answer`, `oauth_provider`, `password_reset_token`, `password_reset_expires`, `last_password_change`) VALUES
	(1, 'admin', 'admin@admin.com', '$2y$10$O6yd5iZLSHurI/xAv5JTj.CHgbXL52Gv74nv8l05nt/d4jzb5kGJ2', '/assets/img/profile/9041-admin.png', 'Admin Contact', 'In which city were you born?', '$2y$10$RQ7JGDg0jQzyg6seWU1TB.AsYQxeJkZcincRnPVnMcYahycFiULGu', NULL, NULL, NULL, NULL),
	(2, 'user', 'user@user.com', '$2y$10$4ZAUiTgX0Tw0eeIad0jAw.PgqoxOzBtrE.H1jURp1PHF7vS6Surju', '/assets/img/Generic avatar.svg', 'User Contact', 'In which city were you born?', '$2y$10$Vw6SjQlGDWNCavs5LVFqAuR6iM96BOlLQeBFAwVS5zidBHMdGqfGC', NULL, NULL, NULL, NULL),
	(4, 'Miku', 'tes@tes.com', '$2y$10$D4xfMe4LFvKHBESBwOPHlOQZ34rIoAmBAnpN5.2KKcCnLYwcf6O66', '/assets/img/profile/6307-Miku Naka.jpg', 'fill contact here', 'What was the name of your first pet?', '$2y$10$G6F03Xkeij8oqnqsk8vATeIoJF1aJktVch78Dh7uBotilMc5MRfnC', NULL, NULL, NULL, NULL),
	(6, 'pengguna', 'pengguna@pengguna', '$2y$10$BhcQOLDxaZD0cketb5edoOxGVJywBKw1r3Nrn5uy6L.w8hJE93zee', '/assets/img/Generic avatar.svg', 'fill contact here', NULL, NULL, NULL, NULL, NULL, NULL);

-- Dumping structure for table ecommerce_v3.user_addresses
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address_label` varchar(50) DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ecommerce_v3.user_addresses: ~4 rows (approximately)
DELETE FROM `user_addresses`;
INSERT INTO `user_addresses` (`id`, `user_id`, `address_label`, `recipient_name`, `phone`, `address`, `city`, `postal_code`, `is_default`, `created_at`) VALUES
	(1, 1, 'Home', 'John Doe', '+6281234567890', 'Jl. Sudirman No. 123', 'Jakarta', '12345', 1, '2024-12-17 05:28:29'),
	(2, 1, 'Office', 'John Doe', '+6281234567891', 'Jl. Thamrin No. 456', 'Jakarta', '12346', 0, '2024-12-17 05:28:29'),
	(3, 2, 'Home', 'Regular User', '+6281234567892', 'Jl. Asia Afrika No. 789', 'Bandung', '40111', 1, '2024-12-17 05:28:29'),
	(4, 2, 'Parents', 'User Parents', '+6281234567893', 'Jl. Malioboro No. 101', 'Yogyakarta', '55111', 0, '2024-12-17 05:28:29');

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT '0.00',
  `shipping_method` varchar(100) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `status` varchar(20) DEFAULT 'pending',
  `payment_status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table ecommerce_v3.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
