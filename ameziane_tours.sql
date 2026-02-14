-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 06, 2026 at 02:40 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ameziane_tours`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` varchar(200) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `details`, `contact`, `created_at`) VALUES
(1, 'MY MOROCCO', 'ICE : 000216663000072', NULL, '2025-06-18 13:08:24'),
(2, 'ACCESS MOROCCO', 'ICE : 00203624000015', NULL, '2025-06-18 13:08:24'),
(3, 'CAP TANJA', 'RC : 110411/IF : 427GG765/TP : 504086G5', NULL, '2025-06-18 13:08:24'),
(4, 'STE UPPER', 'ICE : 00001891425000085', NULL, '2025-06-18 13:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
CREATE TABLE IF NOT EXISTS `comptes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `comptes`
--

INSERT INTO `comptes` (`id`, `email`, `password`) VALUES
(1, 'imorafid@gmail.com', '12345678');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `client_id`, `invoice_date`, `total`, `created_at`) VALUES
(20, 2, '2025-06-17', 1400.00, '2025-06-22 16:56:31'),
(21, 4, '2025-07-02', 2100.00, '2025-07-06 16:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `service_id` int NOT NULL,
  `custom_desc` varchar(255) DEFAULT NULL,
  `service_date` date DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `service_id`, `custom_desc`, `service_date`, `quantity`, `unit_price`, `from_location`, `to_location`, `city`, `created_at`) VALUES
(85, 21, 1, NULL, '2025-06-29', 1, 300.00, 'Tangier airport', 'Fairmont Tazi', NULL, '2025-07-06 16:19:17'),
(84, 21, 1, NULL, '2025-06-13', 1, 1200.00, 'Dar Nour', 'Hotel Fiermmontina', NULL, '2025-07-06 16:19:17'),
(77, 20, 1, NULL, '2025-06-13', 1, 700.00, 'Tangier Train Station', 'Club Yassmina Cabo Negro', NULL, '2025-06-22 16:57:31'),
(78, 20, 1, NULL, '2025-06-15', 1, 700.00, 'Club Yassmina Cabo Negro', 'Tangier Train Station', NULL, '2025-06-22 16:57:31'),
(83, 21, 1, NULL, '2025-06-28', 1, 300.00, 'Tangier Train Station', 'Fairmont Tazi', NULL, '2025-07-06 16:19:17'),
(86, 21, 1, NULL, '2025-06-29', 1, 300.00, 'Tangier airport', 'Fairmont Tazi', NULL, '2025-07-06 16:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `created_at`) VALUES
(1, 'Private Transport', 'Custom intercity chauffeur service.', '2025-06-18 13:08:24'),
(2, 'Car Hold', 'Car at Client Disposition.', '2025-06-18 13:08:24'),
(3, 'Excursion', 'Half day to full day trip', '2025-06-18 13:08:24');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
