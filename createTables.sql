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

    CREATE TABLE IF NOT EXISTS `carbon_carbons` (
  `entity_pk` binary(16) NOT NULL,
  `entity_fk` binary(16) DEFAULT NULL,
  `entity_tag` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'manually',
  PRIMARY KEY (`entity_pk`),
  UNIQUE KEY `entity_entity_pk_uindex` (`entity_pk`),
  KEY `entity_entity_entity_pk_fk` (`entity_fk`),
  CONSTRAINT `entity_entity_entity_pk_fk` FOREIGN KEY (`entity_fk`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_comments` (
  `parent_id` binary(16) NOT NULL,
  `comment_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `comment` blob NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `entity_comments_entity_parent_pk_fk` (`parent_id`),
  KEY `entity_comments_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `entity_comments_entity_entity_pk_fk` FOREIGN KEY (`comment_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_comments_entity_parent_pk_fk` FOREIGN KEY (`parent_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entity_comments_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_feature_group_references` (
  `feature_entity_id` binary(16) DEFAULT NULL,
  `group_entity_id` binary(16) DEFAULT NULL,
  KEY `carbon_feature_references_carbons_entity_pk_fk_2` (`feature_entity_id`),
  KEY `carbon_feature_group_references_carbons_entity_pk_fk` (`group_entity_id`),
  CONSTRAINT `carbon_feature_group_references_carbons_entity_pk_fk` FOREIGN KEY (`group_entity_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_feature_references_carbons_entity_pk_fk` FOREIGN KEY (`feature_entity_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_features` (
  `feature_entity_id` binary(16) NOT NULL,
  `feature_code` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `feature_creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feature_entity_id`),
  UNIQUE KEY `carbon_features_feature_code_uindex` (`feature_code`),
  UNIQUE KEY `carbon_features_feature_entity_id_uindex` (`feature_entity_id`),
  CONSTRAINT `carbon_features_carbons_entity_pk_fk` FOREIGN KEY (`feature_entity_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_group_references` (
  `group_id` binary(16) DEFAULT NULL,
  `allowed_to_grant_group_id` binary(16) DEFAULT NULL,
  KEY `carbon_group_references_carbons_entity_pk_fk` (`group_id`),
  KEY `carbon_group_references_carbons_entity_pk_fk_2` (`allowed_to_grant_group_id`),
  CONSTRAINT `carbon_group_references_carbons_entity_pk_fk` FOREIGN KEY (`group_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_group_references_carbons_entity_pk_fk_2` FOREIGN KEY (`allowed_to_grant_group_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_groups` (
  `group_name` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `entity_id` binary(16) NOT NULL,
  `created_by` binary(16) NOT NULL,
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`),
  KEY `carbon_feature_groups_carbons_entity_pk_fk_2` (`created_by`),
  CONSTRAINT `carbon_feature_groups_carbons_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_feature_groups_carbons_entity_pk_fk_2` FOREIGN KEY (`created_by`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_history_logs` (
  `history_uuid` binary(16) NOT NULL,
  `history_table` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `history_type` varchar(20) CHARACTER SET utf8mb4 DEFAULT NULL,
  `history_data` json DEFAULT NULL,
  `history_original_query` varchar(1024) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `history_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_location_references` (
  `entity_reference` binary(16) NOT NULL,
  `location_reference` binary(16) NOT NULL,
  `location_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `carbon_location_references_carbons_entity_pk_fk` (`entity_reference`),
  KEY `carbon_location_references_carbons_entity_pk_fk_2` (`location_reference`),
  CONSTRAINT `carbon_location_references_carbons_entity_pk_fk` FOREIGN KEY (`entity_reference`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_location_references_carbons_entity_pk_fk_2` FOREIGN KEY (`location_reference`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_locations` (
  `entity_id` binary(16) NOT NULL,
  `latitude` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `longitude` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `street` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `city` varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `state` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `elevation` varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `zip` int(11) DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `entity_location_entity_id_uindex` (`entity_id`),
  CONSTRAINT `entity_location_entity_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_photos` (
  `parent_id` binary(16) NOT NULL,
  `photo_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `photo_path` varchar(225) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `photo_description` text COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`parent_id`),
  UNIQUE KEY `entity_photos_photo_id_uindex` (`photo_id`),
  KEY `photos_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `entity_photos_entity_entity_pk_fk` FOREIGN KEY (`photo_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_entity_pk_fk` FOREIGN KEY (`parent_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_reports` (
  `log_level` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `report` text COLLATE utf8mb4_unicode_520_ci,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `call_trace` text COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_sessions` (
  `user_id` binary(16) NOT NULL,
  `user_ip` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `session_expires` datetime NOT NULL,
  `session_data` text COLLATE utf8mb4_unicode_520_ci,
  `user_online_status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_user_followers` (
  `follower_table_id` binary(16) NOT NULL,
  `follows_user_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  PRIMARY KEY (`follower_table_id`),
  KEY `followers_entity_entity_pk_fk` (`follows_user_id`),
  KEY `followers_entity_entity_followers_pk_fk` (`user_id`),
  CONSTRAINT `carbon_user_followers_carbons_entity_pk_fk` FOREIGN KEY (`follower_table_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `followers_entity_entity_follows_pk_fk` FOREIGN KEY (`follows_user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `followers_entity_followers_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_user_groups` (
  `group_id` binary(16) DEFAULT NULL,
  `user_id` binary(16) DEFAULT NULL,
  KEY `carbon_user_groups_carbons_entity_pk_fk` (`group_id`),
  KEY `carbon_user_groups_carbons_entity_pk_fk_2` (`user_id`),
  CONSTRAINT `carbon_user_groups_carbons_entity_pk_fk` FOREIGN KEY (`group_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_user_groups_carbons_entity_pk_fk_2` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_user_messages` (
  `message_id` binary(16) NOT NULL,
  `from_user_id` binary(16) NOT NULL,
  `to_user_id` binary(16) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `message_read` tinyint(1) DEFAULT '0',
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `messages_entity_entity_pk_fk` (`message_id`),
  KEY `messages_entity_user_from_pk_fk` (`to_user_id`),
  KEY `carbon_user_messages_carbon_entity_pk_fk` (`from_user_id`),
  CONSTRAINT `carbon_user_messages_carbon_entity_pk_fk` FOREIGN KEY (`from_user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_entity_entity_pk_fk` FOREIGN KEY (`message_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_entity_user_from_pk_fk` FOREIGN KEY (`to_user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_user_sessions` (
  `user_id` binary(16) NOT NULL,
  `user_ip` binary(16) DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `session_expires` datetime NOT NULL,
  `session_data` text COLLATE utf8mb4_unicode_520_ci,
  `user_online_status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_user_tasks` (
  `task_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL COMMENT 'This is the user the task is being assigned to',
  `from_id` binary(16) DEFAULT NULL COMMENT 'Keeping this colum so forgen key will remove task if user deleted',
  `task_name` varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `task_description` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `percent_complete` int(11) DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `user_tasks_entity_entity_pk_fk` (`from_id`),
  KEY `user_tasks_entity_task_pk_fk` (`task_id`),
  KEY `carbon_user_tasks_carbons_entity_pk_fk_2` (`user_id`),
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk` FOREIGN KEY (`task_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk_2` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk_3` FOREIGN KEY (`from_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_users` (
  `user_username` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_password` varchar(225) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_id` binary(16) NOT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Athlete',
  `user_sport` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT 'GOLF',
  `user_session_id` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_facebook_id` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_first_name` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_last_name` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_profile_pic` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_profile_uri` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_cover_photo` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_birthday` varchar(9) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_gender` varchar(25) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_about_me` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_rank` int(11) DEFAULT '0',
  `user_email` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_email_code` varchar(225) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_email_confirmed` tinyint(1) DEFAULT '0' COMMENT 'need to change to enums, but no support in rest yet',
  `user_generated_string` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_membership` int(11) DEFAULT '0',
  `user_deactivated` tinyint(1) DEFAULT '0',
  `user_last_login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_ip` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_education_history` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_location` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `carbon_users_user_username_uindex` (`user_username`),
  UNIQUE KEY `user_user_profile_uri_uindex` (`user_profile_uri`),
  UNIQUE KEY `carbon_users_user_facebook_id_uindex` (`user_facebook_id`),
  CONSTRAINT `user_entity_entity_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbon_carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT '1',
  `link_rating` int(11) NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

    CREATE TABLE IF NOT EXISTS `carbon_wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;