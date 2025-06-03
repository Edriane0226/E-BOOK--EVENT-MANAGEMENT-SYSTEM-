-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: ebook_planner
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (2,'EdrianeBangonon','cpxusaptayo1109@gmail.com','$2y$10$tPWXaVJR50LgK98GdOYlcOrIoVD3oKRPie0jYGO.qnGHy24ukViTK','2025-03-26 13:07:45'),(3,'Carl','carl@gmail.com','$2y$10$lrX7jsBgY5vckHv4tpTDcu8zJa5o2G5o/Ey.WkJuPySESvKKXLtNq','2025-03-28 05:28:53'),(4,'Ed','ed@gmail.com','$2y$10$5EN8IcoWnEPWeweq85JPDuSTDZioz7opzb5FGEmR6Roo2QvaiHrQK','2025-04-22 06:44:52'),(5,'kian','kian@gmail.com','$2y$10$n8vzKrnyqmnvo5v6ycuCk.SyYGTHjzr/xnVFg5A5NfBSYPWRGRtvy','2025-04-25 12:39:15'),(6,'Edriane','edriane.bangonon26@gmail.com','$2y$10$cK47fQbkWcf9g5l2Cj51VeQqR9Ktt6Tm6KaLUB8M2bhT4cvTPQS2q','2025-04-28 13:34:04');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `guests` int NOT NULL,
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `package_id` int DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `refundable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `fk_package` (`package_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (86,20,'Wedding','2025-05-05',1109,'Edit test','2025-05-19 15:20:36','Pending',1,NULL,500.00,1),(87,20,'Birthday','2055-02-26',1111,'Refund Tesh','2025-05-21 06:01:44','Pending',1,NULL,500.00,1),(88,20,'Conference','2005-02-26',1111,'Test 2 Refund req','2025-05-21 14:43:28','Pending',1,NULL,500.00,1),(89,20,'Conference','2005-02-26',1109,'Try','2025-05-24 14:24:29','Pending',2,NULL,1000.00,1),(91,20,'Birthday','2005-02-26',1109,'Trial Again','2025-05-25 17:04:17','Pending',1,NULL,500.00,1),(92,20,'Conference','2025-05-22',1109,'tryu','2025-05-27 05:21:44','Approved',1,NULL,500.00,1),(93,20,'Conference','2025-05-14',100,'1','2025-05-27 10:58:51','Pending',2,NULL,1000.00,1),(94,23,'Birthday','2026-04-04',20,'kana lng budget','2025-05-27 11:51:43','Approved',3,NULL,1500.00,1),(95,25,'Birthday','2026-02-02',20,'gwapoha','2025-05-27 12:01:47','Approved',3,NULL,1500.00,1),(96,25,'Conference','2025-05-28',20,'nice na venue\r\n','2025-05-27 12:24:11','Approved',3,NULL,1500.00,1),(97,25,'Conference','2025-06-08',8,'makar 1','2025-05-27 12:34:09','Pending',3,NULL,1500.00,1),(98,25,'Birthday','2025-05-31',100,'Try Edit','2025-05-27 16:04:04','Approved',1,NULL,500.00,1),(99,20,'Birthday','2025-05-31',100,'Try record payment\r\n','2025-05-27 17:05:12','Pending',1,NULL,27944.00,1),(101,25,'Birthday','2025-12-25',100,'0','2025-06-03 16:47:00','Pending',2,NULL,44744.00,1),(102,25,'Birthday','2025-09-25',100,'update test try ulit','2025-06-03 16:49:04','Pending',2,NULL,44744.00,1),(103,25,'Wedding','2025-10-26',100,'0','2025-06-03 16:51:47','Pending',2,NULL,44744.00,1),(104,25,'Wedding','2025-10-27',100,'0','2025-06-03 16:54:33','Pending',2,NULL,44744.00,1);
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edit_requests`
--

DROP TABLE IF EXISTS `edit_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edit_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `guests` int DEFAULT NULL,
  `message` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `original_event_type` varchar(100) DEFAULT NULL,
  `original_event_date` date DEFAULT NULL,
  `original_guests` int DEFAULT NULL,
  `original_message` text,
  `package_id` int DEFAULT NULL,
  `original_package_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_status` (`status`),
  KEY `fk_edit_requests_package` (`package_id`),
  KEY `fk_edit_requests_original_package` (`original_package_id`),
  CONSTRAINT `fk_edit_requests_original_package` FOREIGN KEY (`original_package_id`) REFERENCES `packages` (`id`),
  CONSTRAINT `fk_edit_requests_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edit_requests`
--

LOCK TABLES `edit_requests` WRITE;
/*!40000 ALTER TABLE `edit_requests` DISABLE KEYS */;
INSERT INTO `edit_requests` VALUES (18,63,'Debut','2025-03-31',500,'UPDATE TEST','pending','2025-04-28 14:13:30',21,NULL,NULL,NULL,NULL,NULL,NULL),(24,52,'Wedding','2005-02-26',100,'hehe','pending','2025-04-28 14:35:50',14,NULL,NULL,NULL,NULL,NULL,NULL),(25,29,'Wedding','2025-12-14',100,'Hmmmm','pending','2025-04-28 14:36:36',14,NULL,NULL,NULL,NULL,NULL,NULL),(37,66,'Conference','2021-01-20',100,'gana na please','approved','2025-05-16 07:14:35',14,NULL,'2021-01-20',100,'gana na please',NULL,NULL),(38,24,'Debut','2025-03-31',50,'d nako\r\n','approved','2025-05-16 07:19:07',20,NULL,'2025-03-31',50,'Help im under the water',NULL,NULL),(39,51,'Debut','2025-03-31',500,'haaaaaaaaaaaaaaaa','approved','2025-05-16 07:24:01',20,NULL,'2025-03-31',500,'what the heeeeell',NULL,NULL),(40,24,'Debut','2025-03-31',50,'yooo\r\n','approved','2025-05-17 06:11:46',20,NULL,'2025-03-31',50,'d nako\r\n',NULL,NULL),(41,51,'Debut','2025-03-31',500,'oh no','approved','2025-05-17 09:33:53',20,NULL,'2025-03-31',500,'haaaaaaaaaaaaaaaa',NULL,NULL),(42,24,'Debut','2025-03-31',50,'nig','approved','2025-05-17 09:39:09',20,NULL,'2025-03-31',50,'yooo\r\n',NULL,NULL),(43,51,'Debut','2025-03-31',500,'yawa','approved','2025-05-17 09:40:18',20,NULL,'2025-03-31',500,'oh no',NULL,NULL),(44,51,'Debut','2025-03-31',500,'tralelero tralala\r\n','approved','2025-05-17 09:42:38',20,NULL,'2025-03-31',500,'yawa',NULL,NULL),(45,51,'Debut','2025-03-31',500,':)\r\n','rejected','2025-05-17 09:46:18',20,NULL,NULL,NULL,NULL,NULL,NULL),(46,51,'Debut','2025-03-31',500,'yawa\r\n','approved','2025-05-17 10:01:57',20,NULL,'2025-03-31',500,'tralelero tralala\r\n',NULL,NULL),(47,51,'Debut','2025-03-31',500,'no way\r\n','rejected','2025-05-17 10:06:09',20,NULL,NULL,NULL,NULL,NULL,NULL),(48,51,'Debut','2025-03-31',500,'Tik lng admin','approved','2025-05-17 10:06:43',20,NULL,'2025-03-31',500,'yawa\r\n',NULL,NULL),(49,70,'Birthday','2005-02-26',1000,'wow','pending','2025-05-17 10:09:07',20,NULL,NULL,NULL,NULL,NULL,NULL),(50,71,'Wedding','2222-02-23',1111,'awdaw','pending','2025-05-17 11:46:40',20,NULL,NULL,NULL,NULL,1,NULL),(51,71,'Wedding','2222-02-23',1111,'awdaw','pending','2025-05-17 13:40:43',20,NULL,NULL,NULL,NULL,1,NULL),(52,51,'Debut','2025-03-31',500,'Tik lng admin','pending','2025-05-17 14:31:09',20,'Debut','2025-03-31',500,'0',1,2),(53,77,'Conference','2025-05-13',500,'Trial 1','approved','2025-05-18 06:34:23',20,'Conference','2025-05-13',50,'0',1,1),(54,77,'Conference','2025-05-13',500,'TRRY','approved','2025-05-18 10:21:04',20,'Conference','2025-05-13',500,'0',1,1),(55,77,'Conference','2025-05-13',500,'Test fire','approved','2025-05-18 10:35:59',20,'Conference','2025-05-13',500,'0',1,1),(56,77,'Conference','2025-05-13',500,'Yawa','approved','2025-05-18 10:42:11',20,'Conference','2025-05-13',500,'0',1,1),(57,77,'Conference','2025-05-13',500,'Gana na please','approved','2025-05-18 10:47:33',20,'Conference','2025-05-13',500,'0',1,1),(58,77,'Conference','2025-05-13',500,'Traleleo Alberca','approved','2025-05-18 10:49:22',20,'Conference','2025-05-13',500,'0',1,1),(59,77,'Conference','2025-05-13',500,'Nigga\r\n','approved','2025-05-18 10:58:00',20,'Conference','2025-05-13',500,'0',1,1),(60,77,'Conference','2025-05-13',500,'niggana','rejected','2025-05-18 11:00:33',20,'Conference','2025-05-13',500,'0',1,1),(61,77,'Conference','2025-05-13',500,'Nigga\r\n','approved','2025-05-18 14:07:53',20,'Conference','2025-05-13',500,'Nigga\r\n',1,1),(62,77,'Conference','2025-05-13',500,'Try\r\n','approved','2025-05-18 14:08:23',20,'Conference','2025-05-13',500,'Nigga\r\n',1,1),(63,80,'Debut','2025-02-25',100,'USER UPDATE TEST.','approved','2025-05-18 16:19:51',20,'Debut','2025-02-25',100,'USER UPDATE TEST.',3,3),(64,80,'Debut','2025-02-25',100,'USER UPDATE TEST.','pending','2025-05-18 16:22:26',20,'Birthday','2025-02-25',100,'API TEST ADMIN UPDATE',3,3),(65,77,'Conference','2025-05-13',500,'Try\r\n','approved','2025-05-18 16:23:53',20,'Conference','2025-05-13',500,'Try\r\n',2,1),(66,86,'Wedding','2025-05-05',1109,'Edit test','approved','2025-05-21 14:54:52',20,'Wedding','2025-05-05',1109,'1010',1,1),(67,98,'Birthday','2025-05-31',100,'Try Edit','approved','2025-05-27 16:04:34',25,'Birthday','2025-05-31',100,'Try',1,1),(68,102,'Birthday','2025-09-25',100,'update test','pending','2025-06-03 16:51:13',25,'Birthday','2025-10-25',100,'0',2,2),(69,102,'Birthday','2025-09-25',100,'update test try ulit','approved','2025-06-03 16:58:16',25,'Birthday','2025-10-25',100,'0',2,2);
/*!40000 ALTER TABLE `edit_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,'Basic','Basic event package with limited services',27944.00),(2,'Standard','Standard event package with moderate services',44744.00),(3,'Premium','Premium event package with full services',72644.00);
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `is_refunded` tinyint(1) DEFAULT '0',
  `paypal_payment_id` varchar(255) DEFAULT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (33,86,20,'GCash',150.00,'2025-05-19 23:20:43','completed',0,NULL,NULL,NULL),(34,86,20,'Bank',150.00,'2025-05-19 23:26:19','completed',0,NULL,NULL,NULL),(35,86,20,'GCash',200.00,'2025-05-19 23:26:46','completed',0,NULL,NULL,NULL),(36,87,20,'GCash',200.00,'2025-05-21 14:01:54','completed',0,NULL,NULL,NULL),(37,87,20,'GCash',300.00,'2025-05-21 21:20:27','completed',1,NULL,NULL,NULL),(38,88,20,'GCash',150.00,'2025-05-21 22:44:01','pending',1,NULL,NULL,NULL),(39,89,20,'PayPal',300.00,'2025-05-24 22:59:33','pending',1,NULL,NULL,NULL),(44,91,20,'paypal',150.00,'2025-05-26 01:04:34','completed',1,'PAYID-NAZU3IY58X34780GV8869424','1Y499609WK166315N','2025-05-25 17:07:11'),(45,92,20,'paypal',500.00,'2025-05-27 13:21:54','completed',0,'PAYID-NA2UX4Y3DK292176M403233P','1A9247778V419873P','2025-05-27 05:22:58'),(46,93,20,'paypal',300.00,'2025-05-27 18:59:09','completed',0,'PAYID-NA2ZV7I41470402AC0216812','0WE71124YP739332Y','2025-05-27 10:59:28'),(47,93,20,'paypal',300.00,'2025-05-27 19:08:13','pending',0,'PAYID-NA2Z2HA48K98958BR271641W',NULL,NULL),(48,93,20,'paypal',300.00,'2025-05-27 19:11:29','pending',0,'PAYID-NA2Z3YA1XG5837996618662P',NULL,NULL),(49,93,20,'paypal',300.00,'2025-05-27 19:25:40','completed',0,'PAYID-NA22CMY7GF71555K8750744U','3KV78062WP957404U','2025-05-27 11:25:50'),(50,94,23,'paypal',450.00,'2025-05-27 19:51:55','completed',0,'PAYID-NA22OWQ78N467012K921905B','3YY56356PY824341L','2025-05-27 11:52:12'),(51,94,23,'paypal',1500.00,'2025-05-27 19:55:23','completed',0,'PAYID-NA22QKQ34800036LL550032F','0XG94242B1945841T','2025-05-27 11:55:33'),(52,95,25,'paypal',450.00,'2025-05-27 20:02:55','completed',0,'PAYID-NA22T3Q8VW342766A907951H','8U43107660238163P','2025-05-27 12:03:12'),(53,95,25,'paypal',1050.00,'2025-05-27 20:04:04','completed',0,'PAYID-NA22UMY4KB46699ES274011H','8UH9540746464323G','2025-05-27 12:04:14'),(54,96,25,'paypal',450.00,'2025-05-27 20:24:27','completed',0,'PAYID-NA2256Q3UL42453G7655201B','00411634JR615561J','2025-05-27 12:24:39'),(55,96,25,'paypal',1500.00,'2025-05-27 20:25:28','completed',0,'PAYID-NA226NY25V13837X4156030D','11384369Y6794712V','2025-05-27 12:25:38'),(56,97,25,'paypal',1500.00,'2025-05-27 20:34:42','pending',1,'PAYID-NA23CYI2VP69630P4024541M',NULL,NULL),(57,98,25,'paypal',150.00,'2025-05-28 00:09:19','completed',0,'PAYID-NA26HMA8WG620274E043474N','6KR57518F76126424','2025-05-27 16:11:04'),(58,98,25,'paypal',500.00,'2025-05-28 00:11:24','pending',0,'PAYID-NA26ILI50598463J8650432G',NULL,NULL),(59,98,25,'paypal',350.00,'2025-05-28 00:20:28','completed',0,'PAYID-NA26MTI6T065245EE7579312','8ED47598KB0572620','2025-05-27 16:20:36'),(60,96,25,'paypal',70694.00,'2025-05-28 00:46:59','pending',0,'PAYID-NA26ZBA06610962RN2995337',NULL,NULL),(61,96,25,'paypal',70694.00,'2025-05-28 00:49:28','pending',0,'PAYID-NA262GI86L74706LY5989900',NULL,NULL),(62,98,25,'paypal',27444.00,'2025-05-28 00:50:10','pending',0,'PAYID-NA262QY02F48058DJ863143G',NULL,NULL),(63,98,25,'paypal',27444.00,'2025-05-28 00:50:20','pending',0,'PAYID-NA262TI0VF61270WC4917522',NULL,NULL),(64,99,20,'paypal',8383.20,'2025-05-28 01:06:04','completed',0,'PAYID-NA27B7A7N038989YK051284C','8YS47208BF927605R','2025-05-27 17:07:23');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refunds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `reason` text,
  `refund_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
INSERT INTO `refunds` VALUES (11,37,20,300.00,'d nalang ko kol','approved','2025-05-21 21:22:41','2025-05-21 21:23:38'),(12,38,20,150.00,'Testt refund 2','approved','2025-05-21 22:44:16','2025-05-21 22:44:37'),(13,39,20,300.00,'a','approved','2025-05-25 23:48:08','2025-05-25 23:50:55'),(14,44,20,150.00,'woaw','approved','2025-05-26 19:34:17','2025-05-26 19:34:23'),(15,56,25,1500.00,'sge na please','approved','2025-05-28 00:06:44','2025-05-28 00:07:04');
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_code` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic` varchar(255) DEFAULT NULL,
  `pending_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'a','h4nduma@gmail.com',0,NULL,'123','$2y$10$HnELD/MM2XSEKY7PwNooz.P60XRiaMTauCk81tT7QXF8tUhnfyiCm','2025-02-27 17:40:23',NULL,NULL),(5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Test User','test@example.com',0,NULL,NULL,'dummyhashedpass','2025-04-26 07:50:54',NULL,NULL),(6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'as','u@gmail.com',0,NULL,'123','$2y$10$HU.wTyDVsSLJRA4TyMwIx.K7NSnghZ3SKWZ7BJQv4Udsx6lrHPfea','2025-02-27 17:43:58',NULL,NULL),(7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'sxc','admin@example.com',0,NULL,'xs','$2y$10$0a.nMu7ETh.TIf2JI1ljj.CXt0KP6U3Dj4BVCap8fuiDdV7ehjD.C','2025-02-27 19:21:07',NULL,NULL),(8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'z','z@gmail.com',0,NULL,'12','$2y$10$6LUsMUgc2x7Y.TNm9bJ5z.0lNLsRsTywNOicH3kfIcOBrAS3CEYEO','2025-02-27 19:21:40',NULL,NULL),(9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'elias tv','elias@gmail.com',0,NULL,'123','$2y$10$.7ywWJqShn0.gaagFOXmNOJxe6b6gfd/3LZ4dpSNqrXWzrnMFgG1W','2025-03-01 19:10:22',NULL,NULL),(10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aira dela torre','aira@gmail.com',0,NULL,'09754725322','$2y$10$LcjzBXzdXCHnH77kPszh3O3.lg.faDpvWKEyz3v25p9O3k6K1T8Ku','2025-03-01 21:23:17',NULL,NULL),(11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Christian B Danoso','yang@gmail.com',0,NULL,'','$2y$10$DKozRvGkPqP69UZYtythAeoCWzRbr/Q9QcYXwn3c8DIyMwcMo6UuO','2025-03-01 22:23:52',NULL,NULL),(12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'yaya rang bugan','yaya@gmail.com',0,NULL,'','$2y$10$riAn3gQEYEoDACt.3yjvSuE437keGqVq5J65g6sdG4t6e7yqrKT0a','2025-03-02 00:45:20',NULL,NULL),(13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Joshua I. Pecayo','joshuapecayo25@gmail.com',0,NULL,'','$2y$10$RfiFd2SHm4c4D.Ajj0bqAucId9mpwSrWuLHwPjOZzrK4U/eoK5fwy','2025-03-02 23:17:45',NULL,NULL),(14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Christian B Danosoo','yu@gmail.com',0,NULL,'','$2y$10$/VSgcKPOyN.PdxxQA7GyauuFaiQkgZMbJJLA5hu/cugbwU4Lg20gm','2025-03-23 01:56:01','profile_14_1742975415.jpg',NULL),(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Christian B Danoso','yaa@gmail.com',0,NULL,'','$2y$10$2X2fFhVFGsZNF92J2HlPi.Bi3piVhO7dFTq8yVwssgq2/Ci5QG8BK','2025-03-24 18:49:00','profile_15_1742870999.jpg',NULL),(16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aba kabogera','aba@gmail.com',0,NULL,'','$2y$10$RHn.WUXJKh8tKRAKabBjNOaMoh8N9FFUnRL/IFlAtSnCLeZiq5mra','2025-03-24 19:19:24','profile_16_1742873446.jpg',NULL),(20,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Edriane Ortiz Bangonon ','edrianebangonon8@gmail.com',0,'448384',NULL,'$2y$10$Bf4LNbD79LQLhGuqbJC4WeL2YIJO.HDFgRCxi2UxSzt1WB4nNfZQC','2025-03-27 13:32:45','profile_20_1747669312.jpg',NULL),(21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Edriane Bangonon','edriane@gmail.com',0,NULL,NULL,'$2y$10$r2xYqEhkcGd5LpIrlW3s5uFR1tzQuor7HG8bnlkXiLkcvUnmEkVJm','2025-04-28 13:35:54',NULL,NULL),(22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'CarlPorque','carl1109@gmail.com',0,NULL,NULL,'$2y$10$JtN6zPngZrVRVTS64quRvurmuB61XsV6LHWQnBT5ryMf30Tj3UJ1G','2025-05-27 06:00:55',NULL,NULL),(23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'james pecayo','jamespecayo25@gmail.com',0,NULL,NULL,'$2y$10$5M7WW6vPHnV0TgMUybuzMOdX17QM/KqBIg2m7j25ba1.iMpJ29B/2','2025-05-27 11:50:32',NULL,NULL),(24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'edriane bangonon','irishmakilan@gmail.com',0,NULL,NULL,'$2y$10$q86yRNb5.iQIJL9boAlbKufmJJMBM7ECt74k62/NLxRYrNanExtnK','2025-05-27 11:59:27',NULL,NULL),(25,'edriane','bangonon',20,'2004-02-26','city heighs general santos city','09776779217','','edriane bangonon','edriane.bangonon26@gmail.com',1,NULL,NULL,'$2y$10$drd.7wIlGHFHrOylHJhiEe/5Z9rG1UoeqdFMCXOwyyJR6Fp5yVTEC','2025-05-27 11:59:50','6835e75cbd96d_alcayde.jpg',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-04  1:40:32
