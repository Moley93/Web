-- MySQL dump 10.13  Distrib 8.0.33, for Linux (x86_64)
--
-- Host: localhost    Database: vylo_ecommerce
-- ------------------------------------------------------
-- Server version	8.0.33

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_verified` tinyint(1) DEFAULT '0',
  `newsletter_subscribed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `county` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'United Kingdom',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_default` (`user_id`,`is_default`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_addresses`
--

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specifications` json DEFAULT NULL,
  `in_stock` tinyint(1) DEFAULT '1',
  `stock_quantity` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_in_stock` (`in_stock`),
  KEY `idx_price` (`price`),
  KEY `idx_products_category_stock` (`category`,`in_stock`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Arduino Uno R3','The Arduino Uno R3 is a microcontroller board perfect for beginners and professionals alike.',25.99,'development-board','🔧','[\"ATmega328P Microcontroller\", \"14 Digital I/O Pins\", \"6 Analog Inputs\", \"USB Connection\"]',1,50,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(2,'Raspberry Pi 4 Model B','Latest Raspberry Pi with quad-core ARM processor and 8GB RAM for advanced projects.',89.99,'development-board','🥧','[\"Quad-core ARM Cortex-A72\", \"8GB LPDDR4 RAM\", \"Dual-band Wi-Fi\", \"Bluetooth 5.0\"]',1,25,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(3,'ESP32 DevKit V1','Powerful Wi-Fi and Bluetooth microcontroller for IoT applications.',15.99,'microcontroller','📡','[\"Dual-core Xtensa processor\", \"Wi-Fi 802.11b/g/n\", \"Bluetooth 4.2\", \"36 GPIO pins\"]',1,100,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(4,'DHT22 Temperature Sensor','High-precision digital temperature and humidity sensor with calibrated output.',12.50,'sensor','🌡️','[\"±0.5°C Temperature Accuracy\", \"±2% Humidity Accuracy\", \"3.3V-5V Operating Voltage\", \"Digital Output\"]',1,75,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(5,'HC-SR04 Ultrasonic Sensor','Ultrasonic distance sensor for obstacle detection and ranging applications.',8.99,'sensor','📏','[\"2cm-400cm Range\", \"15° Measuring Angle\", \"5V Operating Voltage\", \"Trigger/Echo Interface\"]',1,60,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(6,'ESP8266 WiFi Module','Compact Wi-Fi module for adding wireless connectivity to your projects.',9.99,'module','📶','[\"802.11 b/g/n Wi-Fi\", \"3.3V Operating Voltage\", \"AT Command Interface\", \"Low Power Consumption\"]',0,0,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(7,'STM32F103C8T6 Blue Pill','ARM Cortex-M3 development board with excellent performance-to-price ratio.',18.75,'development-board','💊','[\"72MHz ARM Cortex-M3\", \"64KB Flash Memory\", \"20KB SRAM\", \"37 GPIO Pins\"]',1,30,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(8,'OLED Display 0.96\"','High-contrast OLED display module perfect for showing sensor data and system status.',14.99,'module','📺','[\"128x64 Resolution\", \"I2C Interface\", \"White/Blue Display\", \"3.3V-5V Compatible\"]',1,45,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(9,'L298N Motor Driver','Dual H-bridge motor driver for controlling DC motors and stepper motors.',11.99,'module','⚙️','[\"Dual H-Bridge\", \"2A Output Current\", \"5V-35V Input\", \"Logic Level Compatible\"]',1,35,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(10,'MPU6050 Gyroscope','6-axis motion tracking device combining accelerometer and gyroscope.',16.50,'sensor','🎯','[\"3-axis Gyroscope\", \"3-axis Accelerometer\", \"I2C Interface\", \"Digital Motion Processor\"]',1,40,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(11,'NRF24L01 Wireless Module','2.4GHz wireless transceiver for short-range communication projects.',13.25,'module','📻','[\"2.4GHz ISM Band\", \"125 Channels\", \"2Mbps Data Rate\", \"Low Power Consumption\"]',1,55,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(12,'Servo Motor SG90','Micro servo motor for precise position control in robotics projects.',7.99,'component','🔄','[\"180° Rotation\", \"Plastic Gear Set\", \"PWM Control\", \"4.8V-6V Operating\"]',1,80,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(101,'Universal IoT Firmware Suite','Comprehensive firmware package for ESP32 and Arduino platforms. Includes wireless connectivity, sensor integration, and remote management capabilities.',89.99,'firmware','🔧','[\"Wi-Fi and Bluetooth connectivity modules\", \"Over-the-air (OTA) update system\", \"Modular sensor support\", \"MQTT protocol implementation\", \"Web-based configuration interface\", \"Power management optimization\"]',1,999,'2025-01-04 20:10:39','2025-01-04 20:10:39'),(102,'Advanced Motor Control Firmware','Precision motor control firmware for robotics and automation projects. Supports stepper motors, servo motors, and DC motors with advanced algorithms.',149.99,'firmware','⚙️','[\"Multi-axis motor coordination\", \"PID control algorithms with auto-tuning\", \"Real-time trajectory planning\", \"Encoder feedback integration\", \"Safety and limit switch monitoring\", \"CAN bus communication support\"]',1,999,'2025-01-04 20:10:39','2025-01-04 20:10:39');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `discount_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moonpay_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_moonpay_transaction` (`moonpay_transaction_id`),
  KEY `idx_orders_user_status` (`user_id`,`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopping_cart`
--

DROP TABLE IF EXISTS `shopping_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopping_cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_added_at` (`added_at`),
  KEY `idx_cart_user_added` (`user_id`,`added_at`),
  CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopping_cart`
--

LOCK TABLES `shopping_cart` WRITE;
/*!40000 ALTER TABLE `shopping_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `shopping_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-04 20:10:39