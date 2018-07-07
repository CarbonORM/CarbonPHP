-- MySQL dump 10.13  Distrib 5.7.18, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: carbonphp
-- ------------------------------------------------------
-- Server version	5.7.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `carbon`
--

DROP TABLE IF EXISTS `carbon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon` (
  `entity_pk` binary(16) NOT NULL,
  `entity_fk` binary(16) DEFAULT '0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  PRIMARY KEY (`entity_pk`),
  UNIQUE KEY `entity_entity_pk_uindex` (`entity_pk`),
  KEY `entity_entity_entity_pk_fk` (`entity_fk`),
  CONSTRAINT `entity_entity_entity_pk_fk` FOREIGN KEY (`entity_fk`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_reports`
--

DROP TABLE IF EXISTS `carbon_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_reports` (
  `log_level` varchar(20) DEFAULT NULL,
  `report` text,
  `date` varchar(22) NOT NULL,
  `call_trace` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_tag`
--

DROP TABLE IF EXISTS `carbon_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_tag` (
  `entity_id` binary(16) NOT NULL,
  `user_id` binary(16) DEFAULT NULL,
  `tag_id` int(11) NOT NULL,
  `creation_date` int(20) NOT NULL,
  KEY `entity_tag_entity_entity_pk_fk` (`entity_id`),
  KEY `entity_tag_entity_user_pk_fk` (`user_id`),
  KEY `entity_tag_tag_tag_id_fk` (`tag_id`),
  CONSTRAINT `entity_tag_entity_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_tag_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `entity_tag_tag_tag_id_fk` FOREIGN KEY (`tag_id`) REFERENCES `carbon_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_tags`
--

DROP TABLE IF EXISTS `carbon_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_description` text NOT NULL,
  `tag_name` text,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_tag_id_uindex` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `user_id` binary(16) NOT NULL,
  `user_ip` binary(16) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `session_expires` datetime NOT NULL,
  `session_data` text,
  `user_online_status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  0:36:27
