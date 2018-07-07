-- MySQL dump 10.13  Distrib 5.7.18, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: statscoach
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
  `entity_fk` binary(16) DEFAULT NULL,
  PRIMARY KEY (`entity_pk`),
  UNIQUE KEY `entity_entity_pk_uindex` (`entity_pk`),
  KEY `entity_entity_entity_pk_fk` (`entity_fk`),
  CONSTRAINT `entity_entity_entity_pk_fk` FOREIGN KEY (`entity_fk`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_comments`
--

DROP TABLE IF EXISTS `carbon_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_comments` (
  `parent_id` binary(16) NOT NULL,
  `comment_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `comment` blob NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `entity_comments_entity_parent_pk_fk` (`parent_id`),
  KEY `entity_comments_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `entity_comments_entity_entity_pk_fk` FOREIGN KEY (`comment_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_comments_entity_parent_pk_fk` FOREIGN KEY (`parent_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_comments_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_locations`
--

DROP TABLE IF EXISTS `carbon_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_locations` (
  `entity_id` binary(16) NOT NULL,
  `latitude` varchar(225) DEFAULT NULL,
  `longitude` varchar(225) DEFAULT NULL,
  `street` text,
  `city` varchar(40) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `elevation` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `entity_location_entity_id_uindex` (`entity_id`),
  CONSTRAINT `entity_location_entity_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_photos`
--

DROP TABLE IF EXISTS `carbon_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_photos` (
  `parent_id` binary(16) NOT NULL,
  `photo_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `photo_path` varchar(225) NOT NULL,
  `photo_description` text,
  PRIMARY KEY (`parent_id`),
  UNIQUE KEY `entity_photos_photo_id_uindex` (`photo_id`),
  KEY `photos_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `entity_photos_entity_entity_pk_fk` FOREIGN KEY (`photo_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_entity_pk_fk` FOREIGN KEY (`parent_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
  `table_name` varchar(50) NOT NULL,
  `creation_date` int(20) NOT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `entity_tag_entity_entity_pk_fk` (`entity_id`),
  KEY `entity_tag_entity_user_pk_fk` (`user_id`),
  KEY `entity_tag_tag_tag_id_fk` (`table_name`),
  CONSTRAINT `entity_tag_entity_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_tag_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE SET NULL ON UPDATE CASCADE
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_teams`
--

DROP TABLE IF EXISTS `carbon_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_teams` (
  `team_id` binary(16) NOT NULL,
  `team_coach` binary(16) NOT NULL COMMENT 'user_id',
  `parent_team` binary(16) DEFAULT NULL,
  `team_code` varchar(225) NOT NULL,
  `team_name` varchar(225) NOT NULL,
  `team_rank` int(11) DEFAULT '0',
  `team_sport` varchar(225) NOT NULL DEFAULT 'Golf',
  `team_division` varchar(225) DEFAULT NULL,
  `team_school` varchar(225) DEFAULT NULL,
  `team_district` varchar(225) DEFAULT NULL,
  `team_membership` varchar(225) DEFAULT NULL,
  `team_photo` binary(16) DEFAULT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `teams_team_id_uindex` (`team_id`),
  KEY `teams_entity_coach_pk_fk` (`team_coach`),
  KEY `teams_entity_photos_photo_id_fk` (`team_photo`),
  KEY `teams_teams_team_id_fk` (`parent_team`),
  CONSTRAINT `teams_entity_coach_pk_fk` FOREIGN KEY (`team_coach`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `teams_entity_entity_pk_fk` FOREIGN KEY (`team_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `teams_entity_photos_photo_id_fk` FOREIGN KEY (`team_photo`) REFERENCES `carbon_photos` (`photo_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `teams_teams_team_id_fk` FOREIGN KEY (`parent_team`) REFERENCES `carbon_teams` (`team_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_users`
--

DROP TABLE IF EXISTS `carbon_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carbon_users` (
  `user_id` binary(16) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'Athlete',
  `user_sport` varchar(20) DEFAULT 'GOLF',
  `user_session_id` varchar(225) DEFAULT NULL,
  `user_facebook_id` varchar(225) DEFAULT NULL,
  `user_username` varchar(25) NOT NULL,
  `user_first_name` varchar(25) NOT NULL,
  `user_last_name` varchar(25) NOT NULL,
  `user_profile_pic` varchar(225) DEFAULT NULL,
  `user_profile_uri` varchar(225) DEFAULT NULL,
  `user_cover_photo` varchar(225) DEFAULT NULL,
  `user_birthday` datetime DEFAULT NULL,
  `user_gender` varchar(25) DEFAULT NULL,
  `user_about_me` varchar(236) DEFAULT NULL,
  `user_rank` int(8) DEFAULT '0',
  `user_password` varchar(225) DEFAULT NULL,
  `user_email` varchar(50) DEFAULT NULL,
  `user_email_code` varchar(225) DEFAULT NULL,
  `user_email_confirmed` varchar(20) NOT NULL DEFAULT '0',
  `user_generated_string` varchar(200) DEFAULT NULL,
  `user_membership` int(10) DEFAULT '0',
  `user_deactivated` tinyint(1) DEFAULT '0',
  `user_ip` varchar(20) NOT NULL,
  `user_education_history` text,
  `user_location` text,
  `user_creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `carbon_users_user_id_uindex` (`user_id`),
  UNIQUE KEY `carbon_users_user_username_uindex` (`user_username`),
  UNIQUE KEY `user_user_profile_uri_uindex` (`user_profile_uri`),
  CONSTRAINT `user_entity_entity_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_course`
--

DROP TABLE IF EXISTS `golf_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_course` (
  `course_id` binary(16) NOT NULL,
  `course_name` binary(16) NOT NULL,
  `course_holes` int(2) NOT NULL DEFAULT '18',
  `course_phone` text NOT NULL,
  `course_difficulty` int(10) DEFAULT NULL,
  `course_rank` int(5) DEFAULT NULL,
  `box_color_1` varchar(10) DEFAULT NULL,
  `box_color_2` varchar(10) DEFAULT NULL,
  `box_color_3` varchar(10) DEFAULT NULL,
  `box_color_4` varchar(10) DEFAULT NULL,
  `box_color_5` varchar(10) DEFAULT NULL,
  `course_par` blob NOT NULL,
  `course_par_out` int(2) NOT NULL,
  `course_par_in` int(2) NOT NULL,
  `par_tot` int(2) NOT NULL,
  `course_par_hcp` int(4) DEFAULT NULL,
  `course_type` char(30) DEFAULT NULL,
  `course_access` varchar(120) DEFAULT NULL,
  `course_handicap` blob,
  `pga_professional` text,
  `website` text,
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `golf_course_course_id_uindex` (`course_id`),
  UNIQUE KEY `golf_courses_course_id_uindex` (`course_id`),
  CONSTRAINT `golf_course_entity_entity_pk_fk` FOREIGN KEY (`course_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_rounds`
--

DROP TABLE IF EXISTS `golf_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_rounds` (
  `user_id` binary(16) NOT NULL,
  `round_id` binary(16) NOT NULL,
  `course_id` binary(16) NOT NULL COMMENT 'golf_courses(course_id)',
  `round_public` int(1) NOT NULL DEFAULT '1' COMMENT 'true "1" or false "2"',
  `score` text NOT NULL,
  `score_gnr` text NOT NULL,
  `score_ffs` text NOT NULL,
  `score_putts` text NOT NULL,
  `score_out` int(2) NOT NULL,
  `score_in` int(3) NOT NULL,
  `score_total` int(3) NOT NULL,
  `score_total_gnr` int(11) DEFAULT '0',
  `score_total_ffs` int(3) DEFAULT '0',
  `score_total_putts` int(11) DEFAULT NULL,
  `score_date` text,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `golf_rounds_entity_entity_pk_fk` (`round_id`),
  KEY `golf_rounds_entity_course_pk_fk` (`course_id`),
  KEY `golf_rounds_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `golf_rounds_entity_course_pk_fk` FOREIGN KEY (`course_id`) REFERENCES `carbon` (`entity_pk`) ON UPDATE CASCADE,
  CONSTRAINT `golf_rounds_entity_entity_pk_fk` FOREIGN KEY (`round_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `golf_rounds_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_stats`
--

DROP TABLE IF EXISTS `golf_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_stats` (
  `stats_id` binary(16) NOT NULL,
  `stats_tournaments` int(11) DEFAULT '0',
  `stats_rounds` int(11) DEFAULT '0',
  `stats_handicap` int(11) DEFAULT '0',
  `stats_strokes` int(11) DEFAULT '0',
  `stats_ffs` int(11) DEFAULT '0',
  `stats_gnr` int(11) DEFAULT '0',
  `stats_putts` int(11) DEFAULT '0',
  PRIMARY KEY (`stats_id`),
  CONSTRAINT `golf_stats_entity_entity_pk_fk` FOREIGN KEY (`stats_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_tee_box`
--

DROP TABLE IF EXISTS `golf_tee_box`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_tee_box` (
  `course_id` binary(16) NOT NULL COMMENT 'Reference from golf_courses',
  `tee_box` int(1) NOT NULL COMMENT 'options ( 1 - 5 )',
  `distance` blob NOT NULL,
  `distance_color` varchar(10) NOT NULL,
  `distance_general_slope` int(4) DEFAULT NULL,
  `distance_general_difficulty` float DEFAULT NULL,
  `distance_womens_slope` int(4) DEFAULT NULL,
  `distance_womens_difficulty` float DEFAULT NULL,
  `distance_out` int(7) DEFAULT NULL,
  `distance_in` int(7) DEFAULT NULL,
  `distance_tot` int(10) DEFAULT NULL,
  PRIMARY KEY (`course_id`),
  KEY `golf_distance_entity_entity_pk_fk` (`course_id`),
  CONSTRAINT `golf_distance_entity_entity_pk_fk` FOREIGN KEY (`course_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_tournament_teams`
--

DROP TABLE IF EXISTS `golf_tournament_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_tournament_teams` (
  `team_id` binary(16) NOT NULL COMMENT 'teams(team_id)',
  `tournament_id` binary(16) NOT NULL COMMENT 'tournaments(tournament_id)',
  `tournament_paid` int(1) DEFAULT '0',
  `tournament_accepted` int(1) DEFAULT '0',
  KEY `golf_tournament_teams_entity_team_pk_fk` (`team_id`),
  KEY `golf_tournament_teams_entity_tournament_pk_fk` (`tournament_id`),
  CONSTRAINT `golf_tournament_teams_entity_team_pk_fk` FOREIGN KEY (`team_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `golf_tournament_teams_entity_tournament_pk_fk` FOREIGN KEY (`tournament_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `golf_tournaments`
--

DROP TABLE IF EXISTS `golf_tournaments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `golf_tournaments` (
  `tournament_id` binary(16) NOT NULL,
  `tournament_name` binary(16) NOT NULL,
  `course_id` binary(16) DEFAULT NULL COMMENT 'course_id, in case double',
  `host_name` varchar(225) NOT NULL COMMENT 'This could be a school or org',
  `tournament_style` int(11) NOT NULL,
  `tournament_team_price` int(11) DEFAULT NULL,
  `tournament_paid` int(1) DEFAULT '1' COMMENT 'True False',
  `tournament_date` date DEFAULT NULL,
  PRIMARY KEY (`tournament_id`),
  KEY `golf_tournaments_entity_course_pk_fk` (`course_id`),
  KEY `golf_tournaments_entity_entity_pk_fk` (`tournament_id`),
  CONSTRAINT `golf_tournaments_entity_course_pk_fk` FOREIGN KEY (`course_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `golf_tournaments_entity_entity_pk_fk` FOREIGN KEY (`tournament_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
  `user_ip` varchar(16) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `session_expires` datetime NOT NULL,
  `session_data` text,
  `user_online_status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members` (
  `member_id` binary(16) DEFAULT NULL,
  `team_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `accepted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`team_id`),
  KEY `team_members_entity_entity_pk_fk` (`member_id`),
  KEY `team_member_entity_entity_pk_fk` (`user_id`),
  KEY `team_member_entity_team_pk_fk` (`team_id`),
  CONSTRAINT `team_member_entity_entity_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `team_member_entity_team_pk_fk` FOREIGN KEY (`team_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `team_members_entity_entity_pk_fk` FOREIGN KEY (`member_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_followers`
--

DROP TABLE IF EXISTS `user_followers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_followers` (
  `follows_user_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  PRIMARY KEY (`follows_user_id`),
  KEY `followers_entity_entity_pk_fk` (`user_id`),
  CONSTRAINT `followers_entity_entity_follows_pk_fk` FOREIGN KEY (`follows_user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `followers_entity_entity_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_messages`
--

DROP TABLE IF EXISTS `user_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_messages` (
  `message_id` binary(16) NOT NULL,
  `to_user_id` binary(16) DEFAULT NULL,
  `message` text NOT NULL,
  `message_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`message_id`),
  KEY `messages_entity_entity_pk_fk` (`message_id`),
  KEY `messages_entity_user_from_pk_fk` (`to_user_id`),
  CONSTRAINT `messages_entity_entity_pk_fk` FOREIGN KEY (`message_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_entity_user_from_pk_fk` FOREIGN KEY (`to_user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_tasks`
--

DROP TABLE IF EXISTS `user_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tasks` (
  `task_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL COMMENT 'This is the user the task is being assigned to',
  `from_id` binary(16) DEFAULT NULL COMMENT 'Keeping this colum so forgen key will remove task if user deleted',
  `task_name` varchar(40) NOT NULL,
  `task_description` varchar(225) DEFAULT NULL,
  `percent_complete` int(11) DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `user_tasks_entity_entity_pk_fk` (`from_id`),
  KEY `user_tasks_entity_task_pk_fk` (`task_id`),
  CONSTRAINT `tasks_entity_entity_pk_fk` FOREIGN KEY (`task_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_tasks_entity_entity_pk_fk` FOREIGN KEY (`from_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_tasks_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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

-- Dump completed on 2018-07-07  1:59:00
