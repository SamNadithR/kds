-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 10:02 AM
-- Server version: 5.6.25
-- PHP Version: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kds_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Laptops', 'High-performance laptops for work and gaming'),
(2, 'Desktop PCs', 'Custom-built desktop computers'),
(3, 'Components', 'Computer parts and components'),
(4, 'Accessories', 'Computer peripherals and accessories');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 4, '205000.00', 'delivered', '2025-03-28 08:37:07'),
(2, 4, '12000.00', 'pending', '2025-03-28 08:55:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 2, 1, '205000.00'),
(2, 2, 3, 1, '12000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pc_builds`
--

CREATE TABLE IF NOT EXISTS `pc_builds` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `purpose` enum('gaming','office','content_creation','programming','student') NOT NULL,
  `budget_range` enum('budget','mid_range','high_end') NOT NULL,
  `processor` varchar(100) NOT NULL,
  `motherboard` varchar(100) NOT NULL,
  `ram` varchar(100) NOT NULL,
  `storage` varchar(100) NOT NULL,
  `gpu` varchar(100) NOT NULL,
  `psu` varchar(100) NOT NULL,
  `case_type` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pc_builds`
--

INSERT INTO `pc_builds` (`id`, `name`, `purpose`, `budget_range`, `processor`, `motherboard`, `ram`, `storage`, `gpu`, `psu`, `case_type`, `total_price`, `description`, `created_at`) VALUES
(1, 'Budget Gaming Build', 'gaming', 'budget', 'AMD Ryzen 5 5600G', 'MSI B550M PRO', '16GB DDR4 3200MHz', '500GB NVMe SSD', 'GTX 1660 Super', '550W 80+ Bronze', 'Micro ATX Tower', '85000.00', 'Perfect for 1080p gaming on a budget', '2025-03-28 08:04:23'),
(2, 'Mid-Range Gaming PC', 'gaming', 'mid_range', 'AMD Ryzen 7 5800X', 'ASUS ROG B550-F', '32GB DDR4 3600MHz', '1TB NVMe SSD + 2TB HDD', 'RTX 3070', '750W 80+ Gold', 'ATX Mid Tower', '175000.00', 'Excellent 1440p gaming performance', '2025-03-28 08:04:23'),
(3, 'High-End Gaming Rig', 'gaming', 'high_end', 'Intel i9-12900K', 'ASUS ROG Z690', '64GB DDR5 5200MHz', '2TB NVMe SSD + 4TB HDD', 'RTX 3080 Ti', '1000W 80+ Platinum', 'Full Tower', '350000.00', 'Ultimate 4K gaming experience', '2025-03-28 08:04:23'),
(4, 'Student Budget PC', 'student', 'budget', 'Intel i3-12100', 'ASRock H610M', '8GB DDR4 3200MHz', '256GB SSD', 'Intel UHD Graphics', '450W 80+ Bronze', 'Mini Tower', '45000.00', 'Perfect for students and basic computing', '2025-03-28 08:04:23'),
(5, 'Content Creator Build', 'content_creation', 'high_end', 'AMD Ryzen 9 5950X', 'ASUS X570 Pro', '64GB DDR4 3600MHz', '2TB NVMe SSD + 8TB HDD', 'RTX 3090', '1200W 80+ Platinum', 'Full Tower', '450000.00', 'Professional content creation and rendering', '2025-03-28 08:04:23'),
(6, 'Office Workstation', 'office', 'mid_range', 'Intel i5-12400', 'ASUS B660M', '16GB DDR4 3200MHz', '512GB NVMe SSD', 'Intel UHD Graphics', '550W 80+ Bronze', 'Micro ATX Tower', '65000.00', 'Reliable office workstation for productivity', '2025-03-28 08:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `created_at`) VALUES
(2, 1, 'Asus Vivobook F1504VA-NJ822 | i5 1335U | 16GB DDR4 | 512GB NVMe | 15.6â€ FHD | DOS | FingerPrint 5955', '01 YEAR HARDWARE WARRANTY + 02 YEARS SERVICE WARRANTY\r\nTOTAL 03 YEARS WARRANTY\r\n\r\nPRODUCT SPECIFICATIONS\r\n*MODEL : Asus Vivobook F1504VA\r\n\r\n*PROCESSOR : IntelÂ® Coreâ„¢ i5-1335U Processor 1.3 GHz (12MB Cache, up to 4.6 GHz, 10 cores, 12 Threads)', '205000.00', 3, 'uploads/products/67e64ba3a6f49.png', '2025-03-28 07:11:31'),
(3, 4, '1155 Stock Cooler (Used)', 'used fan\r\n', '12000.00', 4, 'uploads/products/67e663e1e1371.png', '2025-03-28 08:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(2, 'saminda', '$2y$10$ui.EmyeDoCjGob2vjSPbKuEmxqVx4M.tF28zmdd1NSnmFAFjcesby', 'snrathnasena333@gmail.com', 'admin', '2025-03-28 05:30:53'),
(4, 'nadith', '$2y$10$gFslhV5PWpFdKLGFlqpVf.Muocysy2E1P6Q0oDN2WNyGuKzqqNg76', 'samnadith3@gmail.com', 'user', '2025-03-28 08:36:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `pc_builds`
--
ALTER TABLE `pc_builds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `pc_builds`
--
ALTER TABLE `pc_builds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
