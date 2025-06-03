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
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
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
  `package` varchar(255) NOT NULL,
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (2,7,'Debut','2025-03-04',50,'Standard','dapat bonnga','2025-02-27 19:54:09','Pending'),(3,9,'Wedding','2025-03-13',30,'Full Setup','need high end sound system at goods band equiptment','2025-03-01 19:11:28','Pending'),(5,11,'Conference','2025-03-11',30,'Full Setup','seminar sa mga palahubog','2025-03-01 22:30:02','Pending'),(6,12,'Birthday','2025-03-14',20,'Full Setup','tagay hanggang mamatay','2025-03-02 00:48:37','Pending'),(7,12,'Debut','2025-04-02',1,'Standard','dfd','2025-03-02 02:35:18','Pending'),(8,13,'Wedding','2025-04-04',100,'Full Setup','none','2025-03-02 23:19:21','Pending'),(14,15,'Wedding','2025-04-04',5678,'Full Setup','boom sabog dapat','2025-03-24 18:50:37','Pending'),(15,16,'Debut','2025-03-29',567,'Full Setup','Account\r\nAdvertise\r\nSpeedtest Awards™\r\nSpeedtest Servers™\r\nSpeedtest Performance Directory™','2025-03-24 19:20:14','Pending'),(24,20,'Debut','2025-03-31',50,'Standard','Help im under the water','2025-03-27 14:11:30','Pending'),(48,1,'Wedding','2025-02-25',100,'Premium','Please arrange for a photographer.','2025-04-26 07:59:34','Pending'),(51,20,'Debut','2025-03-31',500,'Standard','what the heeeeell','2025-04-26 08:06:18','Pending'),(54,21,'Birthday','2005-02-26',50,'Standard','Birthday sakong kabitbahay','2025-04-28 13:38:02','Pending'),(55,21,'Conference','2005-02-26',50,'Full Setup','Conference ni mother','2025-04-28 13:39:43','Pending'),(56,6,'Debut','2025-02-25',50,'Premium','EDI WOW.','2025-04-28 13:41:29','Pending'),(57,6,'Debut','2025-02-25',50,'Premium','EDI WOW.','2025-04-28 13:47:07','Pending'),(59,21,'Debut','2025-02-25',100,'Premium','CREATE BOOKING TEST.','2025-04-28 13:57:31','Pending'),(62,21,'Debut','2025-03-31',500,'Standard','PAGOD NA AKO FOREMAN BUKAS NANAMAN','2025-04-28 14:01:58','Pending'),(64,21,'Birthday','2025-02-25',100,'Premium','TEST FIRE No.2','2025-04-28 14:22:05','Pending'),(65,21,'Debut','2025-02-25',100,'Basic','TRALELEO ALBERCA','2025-04-28 14:23:32','Pending'),(66,14,'Conference','2021-01-20',100,'Basic','try1','2025-04-28 14:57:09','Pending'),(67,14,'Conference','2030-02-25',500,'Basic','trial 2','2025-04-28 14:59:55','Pending');
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
  `package` varchar(100) DEFAULT NULL,
  `message` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edit_requests`
--

LOCK TABLES `edit_requests` WRITE;
/*!40000 ALTER TABLE `edit_requests` DISABLE KEYS */;
INSERT INTO `edit_requests` VALUES (18,63,'Debut','2025-03-31',500,'Standard','UPDATE TEST','pending','2025-04-28 14:13:30',21),(21,54,'Birthday','2005-02-26',50,'Standard','CDO ULAM BURGER','pending','2025-04-28 14:28:35',21),(23,59,'Debut','2025-02-25',100,'Basic','WOWOWO','pending','2025-04-28 14:29:26',21),(24,52,'Wedding','2005-02-26',100,'Basic','hehe','pending','2025-04-28 14:35:50',14),(25,29,'Wedding','2025-12-14',100,'Full Setup','Hmmmm','pending','2025-04-28 14:36:36',14);
/*!40000 ALTER TABLE `edit_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'a','h4nduma@gmail.com','123','$2y$10$HnELD/MM2XSEKY7PwNooz.P60XRiaMTauCk81tT7QXF8tUhnfyiCm','2025-02-27 17:40:23',NULL),(5,'Test User','test@example.com',NULL,'dummyhashedpass','2025-04-26 07:50:54',NULL),(6,'as','u@gmail.com','123','$2y$10$HU.wTyDVsSLJRA4TyMwIx.K7NSnghZ3SKWZ7BJQv4Udsx6lrHPfea','2025-02-27 17:43:58',NULL),(7,'sxc','admin@example.com','xs','$2y$10$0a.nMu7ETh.TIf2JI1ljj.CXt0KP6U3Dj4BVCap8fuiDdV7ehjD.C','2025-02-27 19:21:07',NULL),(8,'z','z@gmail.com','12','$2y$10$6LUsMUgc2x7Y.TNm9bJ5z.0lNLsRsTywNOicH3kfIcOBrAS3CEYEO','2025-02-27 19:21:40',NULL),(9,'elias tv','elias@gmail.com','123','$2y$10$.7ywWJqShn0.gaagFOXmNOJxe6b6gfd/3LZ4dpSNqrXWzrnMFgG1W','2025-03-01 19:10:22',NULL),(10,'aira dela torre','aira@gmail.com','09754725322','$2y$10$LcjzBXzdXCHnH77kPszh3O3.lg.faDpvWKEyz3v25p9O3k6K1T8Ku','2025-03-01 21:23:17',NULL),(11,'Christian B Danoso','yang@gmail.com','','$2y$10$DKozRvGkPqP69UZYtythAeoCWzRbr/Q9QcYXwn3c8DIyMwcMo6UuO','2025-03-01 22:23:52',NULL),(12,'yaya rang bugan','yaya@gmail.com','','$2y$10$riAn3gQEYEoDACt.3yjvSuE437keGqVq5J65g6sdG4t6e7yqrKT0a','2025-03-02 00:45:20',NULL),(13,'Joshua I. Pecayo','joshuapecayo25@gmail.com','','$2y$10$RfiFd2SHm4c4D.Ajj0bqAucId9mpwSrWuLHwPjOZzrK4U/eoK5fwy','2025-03-02 23:17:45',NULL),(14,'Christian B Danosoo','yu@gmail.com','','$2y$10$/VSgcKPOyN.PdxxQA7GyauuFaiQkgZMbJJLA5hu/cugbwU4Lg20gm','2025-03-23 01:56:01','profile_14_1742975415.jpg'),(15,'Christian B Danoso','yaa@gmail.com','','$2y$10$2X2fFhVFGsZNF92J2HlPi.Bi3piVhO7dFTq8yVwssgq2/Ci5QG8BK','2025-03-24 18:49:00','profile_15_1742870999.jpg'),(16,'aba kabogera','aba@gmail.com','','$2y$10$RHn.WUXJKh8tKRAKabBjNOaMoh8N9FFUnRL/IFlAtSnCLeZiq5mra','2025-03-24 19:19:24','profile_16_1742873446.jpg'),(20,'Edriane Ortiz Bangonon ','ed@gmail.com',NULL,'$2y$10$Bf4LNbD79LQLhGuqbJC4WeL2YIJO.HDFgRCxi2UxSzt1WB4nNfZQC','2025-03-27 13:32:45','profile_20_1743133362.jpg'),(21,'Edriane Bangonon','edriane@gmail.com',NULL,'$2y$10$r2xYqEhkcGd5LpIrlW3s5uFR1tzQuor7HG8bnlkXiLkcvUnmEkVJm','2025-04-28 13:35:54',NULL);
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

-- Dump completed on 2025-04-28 23:11:37
