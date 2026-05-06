-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 05:33 AM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 2, 1, 'hey', '2026-05-06 02:42:40'),
(2, 2, 1, 'i have a problem', '2026-05-06 02:42:47'),
(3, 1, 2, 'what is it', '2026-05-06 02:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','delivered') DEFAULT 'pending',
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `address`, `created_at`) VALUES
(1, 9, 219.93, 'delivered', '88 Mohamed Ali Road, Cairo, Egypt', '2026-05-02 03:32:23'),
(2, 8, 309.46, 'pending', '12 Tahrir Square, Cairo, Egypt', '2026-05-02 03:32:23'),
(3, 6, 299.93, 'processing', '7 El Nasr Street, Alexandria, Egypt', '2026-05-02 03:32:23'),
(4, 6, 179.98, 'processing', '88 Mohamed Ali Road, Cairo, Egypt', '2026-05-02 03:32:23'),
(5, 8, 199.99, 'pending', '3 El Horreya Ave, Port Said, Egypt', '2026-05-02 03:32:23'),
(6, 2, 159.96, 'delivered', '88 Mohamed Ali Road, Cairo, Egypt', '2026-05-02 03:32:23'),
(7, 11, 194.97, 'pending', '88 Mohamed Ali Road, Cairo, Egypt', '2026-05-02 03:32:23'),
(8, 2, 229.95, 'processing', '45 Corniche El Nil, Giza, Egypt', '2026-05-02 03:32:23'),
(9, 5, 799.96, 'delivered', '7 El Nasr Street, Alexandria, Egypt', '2026-05-02 03:32:23'),
(10, 6, 294.91, 'delivered', '88 Mohamed Ali Road, Cairo, Egypt', '2026-05-02 03:32:23'),
(11, 10, 408.97, 'pending', '12 Tahrir Square, Cairo, Egypt', '2026-05-02 03:32:23'),
(12, 7, 279.95, 'processing', '12 Tahrir Square, Cairo, Egypt', '2026-05-02 03:32:23'),
(13, 2, 194.95, 'pending', '12 Tahrir Square, Cairo, Egypt', '2026-05-02 03:32:23'),
(14, 10, 209.94, 'pending', '45 Corniche El Nil, Giza, Egypt', '2026-05-02 03:32:23'),
(15, 5, 758.43, 'processing', '12 Tahrir Square, Cairo, Egypt', '2026-05-02 03:32:23'),
(16, 1, 100.59, 'pending', '', '2026-05-05 20:17:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 2, 49.99),
(2, 1, 6, 1, 79.99),
(3, 1, 15, 4, 9.99),
(4, 2, 2, 1, 129.50),
(5, 2, 14, 4, 44.99),
(6, 3, 6, 1, 79.99),
(7, 3, 10, 2, 89.99),
(8, 3, 15, 4, 9.99),
(9, 4, 10, 2, 89.99),
(10, 5, 1, 1, 199.99),
(11, 6, 7, 4, 39.99),
(12, 7, 6, 2, 79.99),
(13, 7, 13, 1, 34.99),
(14, 8, 8, 3, 59.99),
(15, 8, 11, 2, 24.99),
(16, 9, 1, 4, 199.99),
(17, 10, 5, 4, 29.99),
(18, 10, 12, 2, 19.99),
(19, 10, 14, 3, 44.99),
(20, 11, 2, 2, 129.50),
(21, 11, 4, 3, 49.99),
(22, 12, 6, 2, 79.99),
(23, 12, 7, 3, 39.99),
(24, 13, 5, 2, 29.99),
(25, 13, 14, 3, 44.99),
(26, 14, 11, 3, 24.99),
(27, 14, 14, 3, 44.99),
(28, 15, 2, 3, 129.50),
(29, 15, 10, 3, 89.99),
(30, 15, 11, 4, 24.99),
(31, 16, 6, 1, 79.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category`, `created_at`) VALUES
(1, 'Elite Wireless Headphones', 'High-fidelity audio with active noise cancellation and 30-hour battery life.', 199.99, 15, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', 'Electronics', '2026-05-02 03:20:52'),
(2, 'Ergonomic Mechanical Keyboard', 'Ultra-responsive switches with dynamic RGB backlighting and premium wrist rest.', 129.50, 22, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600', 'Peripherals', '2026-05-02 03:20:52'),
(3, 'Smart Fitness Watch', 'Water-resistant with heart rate tracking, built-in GPS, and sleep monitoring.', 249.00, 8, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600', 'Wearables', '2026-05-02 03:20:52'),
(4, 'Wireless Earbuds Pro', 'True wireless with 24hr battery and IPX5 water resistance.', 49.99, 30, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600', 'Electronics', '2026-05-02 03:32:23'),
(5, 'USB-C Fast Charger 65W', 'Charges laptops, phones and tablets. GaN technology, compact size.', 29.99, 50, 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600', 'Electronics', '2026-05-02 03:32:23'),
(6, 'Portable Bluetooth Speaker', '360° sound, waterproof, 12hr playtime. Ideal for outdoor use.', 79.99, 18, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600', 'Electronics', '2026-05-02 03:32:23'),
(7, 'Laptop Stand Aluminum', 'Adjustable height, foldable, compatible with 10–17 inch laptops.', 39.99, 25, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600', 'Accessories', '2026-05-02 03:32:23'),
(8, 'Mechanical Gaming Mouse', 'RGB, 16000 DPI, 7 programmable buttons, braided cable.', 59.99, 20, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=600', 'Peripherals', '2026-05-02 03:32:23'),
(9, '27\" 4K Monitor', 'IPS panel, 144Hz, HDR400, USB-C and HDMI inputs.', 329.00, 7, 'https://images.unsplash.com/photo-1527443224154-c4a573d5f5e4?w=600', 'Electronics', '2026-05-02 03:32:23'),
(10, 'Running Sneakers - Black', 'Lightweight mesh upper, cushioned sole, sizes 38–46.', 89.99, 35, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600', 'Footwear', '2026-05-02 03:32:23'),
(11, 'Minimalist Leather Wallet', 'Slim bifold, genuine leather, holds 6 cards + cash.', 24.99, 60, 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=600', 'Accessories', '2026-05-02 03:32:23'),
(12, 'Stainless Steel Water Bottle', '1L, double-wall insulated, keeps cold 24hr / hot 12hr.', 19.99, 45, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600', 'Lifestyle', '2026-05-02 03:32:23'),
(13, 'Yoga Mat Non-Slip', 'Extra thick 6mm, eco-friendly TPE, carrying strap included.', 34.99, 28, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600', 'Sports', '2026-05-02 03:32:23'),
(14, 'Desk Lamp LED Dimmable', '3 color modes, touch control, USB charging port on base.', 44.99, 22, 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600', 'Home', '2026-05-02 03:32:23'),
(15, 'Coffee Mug Ceramic 350ml', 'Microwave safe, dishwasher safe, printed motivational quote.', 9.99, 100, 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=600', 'Home', '2026-05-02 03:32:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `profile_pic`, `phone`, `address_line1`, `city`, `state`, `zip_code`, `country`, `remember_token`, `created_at`) VALUES
(1, 'Administrator', 'admin@admin.com', '$2y$10$/tB8vlXM21wdchS8nvhWFuer4nUKMnsOZgyaK86K8rLQxivRaGubO', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:20:52'),
(2, 'Sara Ahmed', 'sara@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', 'uploads/1777899508_425efbf4c9f5c4108ac29fd14db011e573e3205a.png', '', '', '', '', '', 'United States', NULL, '2026-05-02 03:32:23'),
(3, 'Omar Hassan', 'omar@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(4, 'Layla Mohamed', 'layla@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(5, 'Karim Nasser', 'karim@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(6, 'Nour Ibrahim', 'nour@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(7, 'Youssef Ali', 'youssef@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(8, 'Mona Salem', 'mona@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(9, 'Tarek Mahmoud', 'tarek@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(10, 'Hana Khaled', 'hana@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(11, 'Adel Fathy', 'adel@example.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(12, 'Super Admin', 'superadmin@admin.com', '$2y$10$NWnAdv9MWcCt4GIsnhO9WuzdbtAhzGHMyBm0mdZ7Rb58U4VTOn9Oi', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 03:32:23'),
(13, 'abdelaliem Mohamed', 'abdelaliieem@gmail.com', '$2y$10$vZCr5GoQ.MNc550l8Sm9z.t5ASOaP1qZ0V6Q5S10VvAQaNczUrHE2', 'user', 'uploads/1777893088_unnamed.jpg', '01551157155', 'Matria', 'Cairo', 'matria', '12345', 'United Kingdom', NULL, '2026-05-04 11:10:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
