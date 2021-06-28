DELIMITER ;;
DROP TRIGGER IF EXISTS `trigger_carbon_carbons_b_d`;;
CREATE TRIGGER `trigger_carbon_carbons_b_d` BEFORE DELETE ON `carbon_carbons` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_pk":"', HEX(OLD.entity_pk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_fk":"', HEX(OLD.entity_fk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_tag":"', COALESCE(OLD.entity_tag,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', OLD.entity_pk , 'DELETE', json);
      -- Delete Children
DELETE FROM carbon_carbons WHERE entity_fk = OLD.entity_pk;
DELETE FROM carbon_comments WHERE comment_id = OLD.entity_pk;
DELETE FROM carbon_comments WHERE parent_id = OLD.entity_pk;
DELETE FROM carbon_comments WHERE user_id = OLD.entity_pk;
DELETE FROM carbon_feature_group_references WHERE group_entity_id = OLD.entity_pk;
DELETE FROM carbon_feature_group_references WHERE feature_entity_id = OLD.entity_pk;
DELETE FROM carbon_features WHERE feature_entity_id = OLD.entity_pk;
DELETE FROM carbon_group_references WHERE group_id = OLD.entity_pk;
DELETE FROM carbon_group_references WHERE allowed_to_grant_group_id = OLD.entity_pk;
DELETE FROM carbon_groups WHERE entity_id = OLD.entity_pk;
DELETE FROM carbon_groups WHERE created_by = OLD.entity_pk;
DELETE FROM carbon_location_references WHERE entity_reference = OLD.entity_pk;
DELETE FROM carbon_location_references WHERE location_reference = OLD.entity_pk;
DELETE FROM carbon_locations WHERE entity_id = OLD.entity_pk;
DELETE FROM carbon_photos WHERE photo_id = OLD.entity_pk;
DELETE FROM carbon_photos WHERE parent_id = OLD.entity_pk;
DELETE FROM carbon_photos WHERE user_id = OLD.entity_pk;
DELETE FROM carbon_user_followers WHERE follower_table_id = OLD.entity_pk;
DELETE FROM carbon_user_followers WHERE follows_user_id = OLD.entity_pk;
DELETE FROM carbon_user_followers WHERE user_id = OLD.entity_pk;
DELETE FROM carbon_user_groups WHERE group_id = OLD.entity_pk;
DELETE FROM carbon_user_groups WHERE user_id = OLD.entity_pk;
DELETE FROM carbon_user_messages WHERE from_user_id = OLD.entity_pk;
DELETE FROM carbon_user_messages WHERE message_id = OLD.entity_pk;
DELETE FROM carbon_user_messages WHERE to_user_id = OLD.entity_pk;
DELETE FROM carbon_user_tasks WHERE task_id = OLD.entity_pk;
DELETE FROM carbon_user_tasks WHERE user_id = OLD.entity_pk;
DELETE FROM carbon_user_tasks WHERE from_id = OLD.entity_pk;
DELETE FROM carbon_users WHERE user_id = OLD.entity_pk;


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_carbons_a_u`;;
CREATE TRIGGER `trigger_carbon_carbons_a_u` AFTER UPDATE ON `carbon_carbons` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_pk":"', HEX(NEW.entity_pk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_fk":"', HEX(NEW.entity_fk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_tag":"', COALESCE(NEW.entity_tag,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', NEW.entity_pk , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_carbons_a_i`;;
CREATE TRIGGER `trigger_carbon_carbons_a_i` AFTER INSERT ON `carbon_carbons` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_pk":"', HEX(NEW.entity_pk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_fk":"', HEX(NEW.entity_fk), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_tag":"', COALESCE(NEW.entity_tag,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', NEW.entity_pk);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', NEW.entity_pk , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_comments_b_d`;;
CREATE TRIGGER `trigger_carbon_comments_b_d` BEFORE DELETE ON `carbon_comments` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(OLD.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment_id":"', HEX(OLD.comment_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(OLD.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment":"', COALESCE(OLD.comment,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', OLD.comment_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_comments_a_u`;;
CREATE TRIGGER `trigger_carbon_comments_a_u` AFTER UPDATE ON `carbon_comments` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(NEW.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment_id":"', HEX(NEW.comment_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment":"', COALESCE(NEW.comment,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', NEW.comment_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_comments_a_i`;;
CREATE TRIGGER `trigger_carbon_comments_a_i` AFTER INSERT ON `carbon_comments` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(NEW.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment_id":"', HEX(NEW.comment_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"comment":"', COALESCE(NEW.comment,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', NEW.comment_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', NEW.comment_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_features_b_d`;;
CREATE TRIGGER `trigger_carbon_features_b_d` BEFORE DELETE ON `carbon_features` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"feature_entity_id":"', HEX(OLD.feature_entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_code":"', COALESCE(OLD.feature_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_creation_date":"', COALESCE(OLD.feature_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', OLD.feature_entity_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_features_a_u`;;
CREATE TRIGGER `trigger_carbon_features_a_u` AFTER UPDATE ON `carbon_features` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_code":"', COALESCE(NEW.feature_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_creation_date":"', COALESCE(NEW.feature_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', NEW.feature_entity_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_features_a_i`;;
CREATE TRIGGER `trigger_carbon_features_a_i` AFTER INSERT ON `carbon_features` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_code":"', COALESCE(NEW.feature_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"feature_creation_date":"', COALESCE(NEW.feature_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', NEW.feature_entity_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', NEW.feature_entity_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_groups_b_d`;;
CREATE TRIGGER `trigger_carbon_groups_b_d` BEFORE DELETE ON `carbon_groups` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"group_name":"', COALESCE(OLD.group_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_id":"', HEX(OLD.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"created_by":"', HEX(OLD.created_by), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(OLD.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', OLD.entity_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_groups_a_u`;;
CREATE TRIGGER `trigger_carbon_groups_a_u` AFTER UPDATE ON `carbon_groups` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"group_name":"', COALESCE(NEW.group_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_id":"', HEX(NEW.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"created_by":"', HEX(NEW.created_by), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', NEW.entity_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_groups_a_i`;;
CREATE TRIGGER `trigger_carbon_groups_a_i` AFTER INSERT ON `carbon_groups` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"group_name":"', COALESCE(NEW.group_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"entity_id":"', HEX(NEW.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"created_by":"', HEX(NEW.created_by), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', NEW.entity_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', NEW.entity_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_locations_b_d`;;
CREATE TRIGGER `trigger_carbon_locations_b_d` BEFORE DELETE ON `carbon_locations` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_id":"', HEX(OLD.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"latitude":"', COALESCE(OLD.latitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"longitude":"', COALESCE(OLD.longitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"street":"', COALESCE(OLD.street,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"city":"', COALESCE(OLD.city,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"state":"', COALESCE(OLD.state,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"elevation":"', COALESCE(OLD.elevation,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"zip":"', COALESCE(OLD.zip,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', OLD.entity_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_locations_a_u`;;
CREATE TRIGGER `trigger_carbon_locations_a_u` AFTER UPDATE ON `carbon_locations` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_id":"', HEX(NEW.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"latitude":"', COALESCE(NEW.latitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"longitude":"', COALESCE(NEW.longitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"street":"', COALESCE(NEW.street,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"city":"', COALESCE(NEW.city,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"state":"', COALESCE(NEW.state,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"elevation":"', COALESCE(NEW.elevation,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"zip":"', COALESCE(NEW.zip,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', NEW.entity_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_locations_a_i`;;
CREATE TRIGGER `trigger_carbon_locations_a_i` AFTER INSERT ON `carbon_locations` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"entity_id":"', HEX(NEW.entity_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"latitude":"', COALESCE(NEW.latitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"longitude":"', COALESCE(NEW.longitude,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"street":"', COALESCE(NEW.street,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"city":"', COALESCE(NEW.city,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"state":"', COALESCE(NEW.state,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"elevation":"', COALESCE(NEW.elevation,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"zip":"', COALESCE(NEW.zip,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', NEW.entity_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', NEW.entity_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_photos_b_d`;;
CREATE TRIGGER `trigger_carbon_photos_b_d` BEFORE DELETE ON `carbon_photos` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(OLD.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_id":"', HEX(OLD.photo_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(OLD.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_path":"', COALESCE(OLD.photo_path,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_description":"', COALESCE(OLD.photo_description,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', OLD.parent_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_photos_a_u`;;
CREATE TRIGGER `trigger_carbon_photos_a_u` AFTER UPDATE ON `carbon_photos` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(NEW.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_id":"', HEX(NEW.photo_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_path":"', COALESCE(NEW.photo_path,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_description":"', COALESCE(NEW.photo_description,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', NEW.parent_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_photos_a_i`;;
CREATE TRIGGER `trigger_carbon_photos_a_i` AFTER INSERT ON `carbon_photos` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"parent_id":"', HEX(NEW.parent_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_id":"', HEX(NEW.photo_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_path":"', COALESCE(NEW.photo_path,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"photo_description":"', COALESCE(NEW.photo_description,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', NEW.parent_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', NEW.parent_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_b_d`;;
CREATE TRIGGER `trigger_carbon_user_followers_b_d` BEFORE DELETE ON `carbon_user_followers` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"follower_table_id":"', HEX(OLD.follower_table_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"follows_user_id":"', HEX(OLD.follows_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(OLD.user_id), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', OLD.follower_table_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_a_u`;;
CREATE TRIGGER `trigger_carbon_user_followers_a_u` AFTER UPDATE ON `carbon_user_followers` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"follower_table_id":"', HEX(NEW.follower_table_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"follows_user_id":"', HEX(NEW.follows_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', NEW.follower_table_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_a_i`;;
CREATE TRIGGER `trigger_carbon_user_followers_a_i` AFTER INSERT ON `carbon_user_followers` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"follower_table_id":"', HEX(NEW.follower_table_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"follows_user_id":"', HEX(NEW.follows_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', NEW.follower_table_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', NEW.follower_table_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_b_d`;;
CREATE TRIGGER `trigger_carbon_user_messages_b_d` BEFORE DELETE ON `carbon_user_messages` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"message_id":"', HEX(OLD.message_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_user_id":"', HEX(OLD.from_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"to_user_id":"', HEX(OLD.to_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message":"', COALESCE(OLD.message,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message_read":"', COALESCE(OLD.message_read,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(OLD.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', OLD.message_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_a_u`;;
CREATE TRIGGER `trigger_carbon_user_messages_a_u` AFTER UPDATE ON `carbon_user_messages` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"message_id":"', HEX(NEW.message_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_user_id":"', HEX(NEW.from_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"to_user_id":"', HEX(NEW.to_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message":"', COALESCE(NEW.message,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message_read":"', COALESCE(NEW.message_read,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', NEW.message_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_a_i`;;
CREATE TRIGGER `trigger_carbon_user_messages_a_i` AFTER INSERT ON `carbon_user_messages` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"message_id":"', HEX(NEW.message_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_user_id":"', HEX(NEW.from_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"to_user_id":"', HEX(NEW.to_user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message":"', COALESCE(NEW.message,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"message_read":"', COALESCE(NEW.message_read,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', NEW.message_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', NEW.message_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_b_d`;;
CREATE TRIGGER `trigger_carbon_user_tasks_b_d` BEFORE DELETE ON `carbon_user_tasks` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"task_id":"', HEX(OLD.task_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(OLD.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_id":"', HEX(OLD.from_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_name":"', COALESCE(OLD.task_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_description":"', COALESCE(OLD.task_description,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"percent_complete":"', COALESCE(OLD.percent_complete,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"start_date":"', COALESCE(OLD.start_date,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"end_date":"', COALESCE(OLD.end_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', OLD.task_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_a_u`;;
CREATE TRIGGER `trigger_carbon_user_tasks_a_u` AFTER UPDATE ON `carbon_user_tasks` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"task_id":"', HEX(NEW.task_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_id":"', HEX(NEW.from_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_name":"', COALESCE(NEW.task_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_description":"', COALESCE(NEW.task_description,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"percent_complete":"', COALESCE(NEW.percent_complete,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"start_date":"', COALESCE(NEW.start_date,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"end_date":"', COALESCE(NEW.end_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', NEW.task_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_a_i`;;
CREATE TRIGGER `trigger_carbon_user_tasks_a_i` AFTER INSERT ON `carbon_user_tasks` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"task_id":"', HEX(NEW.task_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"from_id":"', HEX(NEW.from_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_name":"', COALESCE(NEW.task_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"task_description":"', COALESCE(NEW.task_description,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"percent_complete":"', COALESCE(NEW.percent_complete,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"start_date":"', COALESCE(NEW.start_date,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"end_date":"', COALESCE(NEW.end_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', NEW.task_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', NEW.task_id , 'POST', json);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_users_b_d`;;
CREATE TRIGGER `trigger_carbon_users_b_d` BEFORE DELETE ON `carbon_users` FOR EACH ROW
BEGIN
DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"user_username":"', COALESCE(OLD.user_username,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_password":"', COALESCE(OLD.user_password,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(OLD.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_type":"', COALESCE(OLD.user_type,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_sport":"', COALESCE(OLD.user_sport,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_session_id":"', COALESCE(OLD.user_session_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_facebook_id":"', COALESCE(OLD.user_facebook_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_first_name":"', COALESCE(OLD.user_first_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_name":"', COALESCE(OLD.user_last_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_pic":"', COALESCE(OLD.user_profile_pic,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_uri":"', COALESCE(OLD.user_profile_uri,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_cover_photo":"', COALESCE(OLD.user_cover_photo,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_birthday":"', COALESCE(OLD.user_birthday,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_gender":"', COALESCE(OLD.user_gender,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_about_me":"', COALESCE(OLD.user_about_me,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_rank":"', COALESCE(OLD.user_rank,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email":"', COALESCE(OLD.user_email,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_code":"', COALESCE(OLD.user_email_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_confirmed":"', COALESCE(OLD.user_email_confirmed,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_generated_string":"', COALESCE(OLD.user_generated_string,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_membership":"', COALESCE(OLD.user_membership,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_deactivated":"', COALESCE(OLD.user_deactivated,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_login":"', COALESCE(OLD.user_last_login,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_ip":"', COALESCE(OLD.user_ip,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_education_history":"', COALESCE(OLD.user_education_history,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_location":"', COALESCE(OLD.user_location,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_creation_date":"', COALESCE(OLD.user_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', OLD.user_id , 'DELETE', json);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_users_a_u`;;
CREATE TRIGGER `trigger_carbon_users_a_u` AFTER UPDATE ON `carbon_users` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"user_username":"', COALESCE(NEW.user_username,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_password":"', COALESCE(NEW.user_password,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_type":"', COALESCE(NEW.user_type,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_sport":"', COALESCE(NEW.user_sport,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_session_id":"', COALESCE(NEW.user_session_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_facebook_id":"', COALESCE(NEW.user_facebook_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_first_name":"', COALESCE(NEW.user_first_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_name":"', COALESCE(NEW.user_last_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_pic":"', COALESCE(NEW.user_profile_pic,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_uri":"', COALESCE(NEW.user_profile_uri,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_cover_photo":"', COALESCE(NEW.user_cover_photo,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_birthday":"', COALESCE(NEW.user_birthday,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_gender":"', COALESCE(NEW.user_gender,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_about_me":"', COALESCE(NEW.user_about_me,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_rank":"', COALESCE(NEW.user_rank,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email":"', COALESCE(NEW.user_email,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_code":"', COALESCE(NEW.user_email_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_confirmed":"', COALESCE(NEW.user_email_confirmed,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_generated_string":"', COALESCE(NEW.user_generated_string,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_membership":"', COALESCE(NEW.user_membership,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_deactivated":"', COALESCE(NEW.user_deactivated,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_login":"', COALESCE(NEW.user_last_login,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_education_history":"', COALESCE(NEW.user_education_history,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_location":"', COALESCE(NEW.user_location,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_creation_date":"', COALESCE(NEW.user_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', NEW.user_id , 'PUT', json);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_users_a_i`;;
CREATE TRIGGER `trigger_carbon_users_a_i` AFTER INSERT ON `carbon_users` FOR EACH ROW
BEGIN

DECLARE json text;
 SET json = '{';
SET json = CONCAT(json,'"user_username":"', COALESCE(NEW.user_username,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_password":"', COALESCE(NEW.user_password,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_id":"', HEX(NEW.user_id), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_type":"', COALESCE(NEW.user_type,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_sport":"', COALESCE(NEW.user_sport,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_session_id":"', COALESCE(NEW.user_session_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_facebook_id":"', COALESCE(NEW.user_facebook_id,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_first_name":"', COALESCE(NEW.user_first_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_name":"', COALESCE(NEW.user_last_name,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_pic":"', COALESCE(NEW.user_profile_pic,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_profile_uri":"', COALESCE(NEW.user_profile_uri,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_cover_photo":"', COALESCE(NEW.user_cover_photo,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_birthday":"', COALESCE(NEW.user_birthday,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_gender":"', COALESCE(NEW.user_gender,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_about_me":"', COALESCE(NEW.user_about_me,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_rank":"', COALESCE(NEW.user_rank,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email":"', COALESCE(NEW.user_email,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_code":"', COALESCE(NEW.user_email_code,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_email_confirmed":"', COALESCE(NEW.user_email_confirmed,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_generated_string":"', COALESCE(NEW.user_generated_string,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_membership":"', COALESCE(NEW.user_membership,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_deactivated":"', COALESCE(NEW.user_deactivated,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_last_login":"', COALESCE(NEW.user_last_login,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_education_history":"', COALESCE(NEW.user_education_history,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_location":"', COALESCE(NEW.user_location,''), '"');
SET json = CONCAT(json, ',');
SET json = CONCAT(json,'"user_creation_date":"', COALESCE(NEW.user_creation_date,''), '"');SET json = CONCAT(json, '}');
      -- Insert record into audit tables
INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', NEW.user_id);
INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', NEW.user_id , 'POST', json);

END;;
DELIMITER ;