CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') DEFAULT 'user',
    `profile_pic` VARCHAR(255) NULL,
    `remember_token` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(255) NULL,
    `category` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'delivered') DEFAULT 'pending',
    `address` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Admin User
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Administrator', 'admin@admin.com', '$2y$10$/tB8vlXM21wdchS8nvhWFuer4nUKMnsOZgyaK86K8rLQxivRaGubO', 'admin');

-- Sample Products
INSERT INTO `products` (`name`, `description`, `price`, `stock`, `image`, `category`) VALUES
('Elite Wireless Headphones', 'High-fidelity audio with active noise cancellation and 30-hour battery life.', 199.99, 15, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', 'Electronics'),
('Ergonomic Mechanical Keyboard', 'Ultra-responsive switches with dynamic RGB backlighting and premium wrist rest.', 129.50, 22, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600', 'Peripherals'),
('Smart Fitness Watch', 'Water-resistant with heart rate tracking, built-in GPS, and sleep monitoring.', 249.00, 8, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600', 'Wearables');
