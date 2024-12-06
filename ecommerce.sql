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


-- Dumping database structure for ecommerce
DROP DATABASE IF EXISTS `ecommerce_v3`;
CREATE DATABASE IF NOT EXISTS `ecommerce_v3` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */;
USE `ecommerce_v3`;

-- Drop tables in correct order (dependent tables first)
DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

-- Create tables in correct order (independent tables first)
-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '/assets/img/Generic avatar.svg',
  `contact_details` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'fill contact here',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'fill address here',
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,  -- Changed from varchar(50) to varchar(255)
  `oauth_provider` varchar(50) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create products table with all needed columns from the start
CREATE TABLE IF NOT EXISTS `products` (
  `productId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `description` TEXT,
  `category` VARCHAR(50),
  `sold_count` INT DEFAULT 0,
  `view_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_featured` BOOLEAN DEFAULT FALSE,
  `status` VARCHAR(20) DEFAULT 'active',
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create ratings table with correct syntax
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `productId` int NOT NULL,
  `userId` int NOT NULL,
  `rating` int NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`productId`, `userId`),
  FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE,
  FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create other dependent tables
CREATE TABLE IF NOT EXISTS `cart` (
  `cartId` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `image_path` varchar(255) DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `productId` int DEFAULT NULL,
  PRIMARY KEY (`cartId`),
  KEY `FK_cart_users` (`userId`),
  KEY `FK_cart_products` (`productId`),
  CONSTRAINT `FK_cart_products` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE,
  CONSTRAINT `FK_cart_users` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) UNIQUE,
  user_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  amount DECIMAL(10,2),
  status VARCHAR(20),
  payment_type VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `FK_orders_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`productId`) ON DELETE SET NULL,
  CONSTRAINT `FK_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create order_items table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR(50),
    `product_id` int,
    `quantity` int DEFAULT 1,
    `price` DECIMAL(10,2),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`productId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create discounts table
CREATE TABLE IF NOT EXISTS `discounts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT,
    `discount_percent` INT DEFAULT 0,
    `start_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `end_date` TIMESTAMP,
    `is_flash_sale` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`productId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert default users (after all table creations, before product inserts)
INSERT INTO `users` (`username`, `email`, `password`, `profile_image`, `contact_details`, `address`, `oauth_provider`) VALUES
('admin', 'admin@admin.com', '$$2y$10$4ZAUiTgX0Tw0eeIad0jAw.PgqoxOzBtrE.H1jURp1PHF7vS6Surju', '/assets/img/Generic avatar.svg', 'Admin Contact', 'Admin Address', NULL),
('user', 'user@user.com', '$2y$10$4ZAUiTgX0Tw0eeIad0jAw.PgqoxOzBtrE.H1jURp1PHF7vS6Surju', '/assets/img/Generic avatar.svg', 'User Contact', 'User Address', NULL);

-- Clear and reinsert sample data with explicit timestamps
TRUNCATE TABLE `discounts`;
TRUNCATE TABLE `products`;

-- Insert products first
INSERT INTO `products` (`name`, `price`, `image_path`, `description`, `category`, `quantity`, `is_featured`, `status`) VALUES
('iPhone 15 Pro Max', 19999000, '/assets/img/products/iphone15promax.jpg', 'Latest iPhone with A17 Pro chip, 48MP camera system, and titanium design', 'Phones', 50, TRUE, 'active'),
('Samsung Galaxy S24 Ultra', 18999000, '/assets/img/products/s24ultra.jpg', 'Features Snapdragon 8 Gen 3, 200MP camera, and S Pen functionality', 'Phones', 45, TRUE, 'active'),
('Google Pixel 8 Pro', 15999000, '/assets/img/products/pixel8pro.jpg', 'Advanced AI features, exceptional camera capabilities, and pure Android experience', 'Phones', 30, TRUE, 'active'),
('Xiaomi 14 Pro', 12999000, '/assets/img/products/xiaomi14pro.jpg', 'Leica optics, Snapdragon 8 Gen 3, and 120W fast charging', 'Phones', 40, FALSE, 'active'),
('OnePlus 12', 11999000, '/assets/img/products/oneplus12.jpg', 'Hasselblad camera system, 100W SUPERVOOC charging, and 120Hz AMOLED display', 'Phones', 35, FALSE, 'active');

-- Insert flash sales with explicit timestamps and longer duration
INSERT INTO `discounts` (`product_id`, `discount_percent`, `start_date`, `end_date`, `is_flash_sale`) VALUES
(1, 10, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1),
(2, 15, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1),
(3, 20, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1);

-- Insert regular discounts
INSERT INTO `discounts` (`product_id`, `discount_percent`, `start_date`, `end_date`, `is_flash_sale`) VALUES
(4, 12, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY), FALSE),
(5, 25, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY), FALSE);

-- Update some products with sample analytics data
UPDATE `products` SET 
    `sold_count` = FLOOR(RAND() * 50),
    `view_count` = FLOOR(RAND() * 1000)
WHERE `productId` IN (1,2,3,4,5);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
