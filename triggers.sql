DELIMITER ;;
DROP TRIGGER IF EXISTS `trigger_carbon_carbons_b_d`;;
CREATE TRIGGER `trigger_carbon_carbons_b_d` BEFORE DELETE ON `carbon_carbons` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_pk":"', HEX(OLD.entity_pk), '"');SET history_data = CONCAT(history_data,'"entity_fk":"', HEX(OLD.entity_fk), '"');SET history_data = CONCAT(history_data,'"entity_tag":"', COALESCE(OLD.entity_tag,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_pk":"', HEX(OLD.entity_pk), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', history_primary_data , 'DELETE', history_data, history_original_query);
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

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_pk":"', HEX(NEW.entity_pk), '"');SET history_data = CONCAT(history_data,'"entity_fk":"', HEX(NEW.entity_fk), '"');SET history_data = CONCAT(history_data,'"entity_tag":"', COALESCE(NEW.entity_tag,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_pk":"', HEX(NEW.entity_pk), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_carbons_a_i`;;
CREATE TRIGGER `trigger_carbon_carbons_a_i` AFTER INSERT ON `carbon_carbons` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_pk":"', HEX(NEW.entity_pk), '"');SET history_data = CONCAT(history_data,'"entity_fk":"', HEX(NEW.entity_fk), '"');SET history_data = CONCAT(history_data,'"entity_tag":"', COALESCE(NEW.entity_tag,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_pk":"', HEX(NEW.entity_pk), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_carbons', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_comments_b_d`;;
CREATE TRIGGER `trigger_carbon_comments_b_d` BEFORE DELETE ON `carbon_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(OLD.parent_id), '"');SET history_data = CONCAT(history_data,'"comment_id":"', HEX(OLD.comment_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"comment":"', COALESCE(OLD.comment,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_id":"', HEX(OLD.comment_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_comments_a_u`;;
CREATE TRIGGER `trigger_carbon_comments_a_u` AFTER UPDATE ON `carbon_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(NEW.parent_id), '"');SET history_data = CONCAT(history_data,'"comment_id":"', HEX(NEW.comment_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"comment":"', COALESCE(NEW.comment,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_id":"', HEX(NEW.comment_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_comments_a_i`;;
CREATE TRIGGER `trigger_carbon_comments_a_i` AFTER INSERT ON `carbon_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(NEW.parent_id), '"');SET history_data = CONCAT(history_data,'"comment_id":"', HEX(NEW.comment_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"comment":"', COALESCE(NEW.comment,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_id":"', HEX(NEW.comment_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_comments', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_feature_group_references_b_d`;;
CREATE TRIGGER `trigger_carbon_feature_group_references_b_d` BEFORE DELETE ON `carbon_feature_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(OLD.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"group_entity_id":"', HEX(OLD.group_entity_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_feature_group_references', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_feature_group_references_a_u`;;
CREATE TRIGGER `trigger_carbon_feature_group_references_a_u` AFTER UPDATE ON `carbon_feature_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"group_entity_id":"', HEX(NEW.group_entity_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_feature_group_references', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_feature_group_references_a_i`;;
CREATE TRIGGER `trigger_carbon_feature_group_references_a_i` AFTER INSERT ON `carbon_feature_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"group_entity_id":"', HEX(NEW.group_entity_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_feature_group_references', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_features_b_d`;;
CREATE TRIGGER `trigger_carbon_features_b_d` BEFORE DELETE ON `carbon_features` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(OLD.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"feature_code":"', COALESCE(OLD.feature_code,''), '"');SET history_data = CONCAT(history_data,'"feature_creation_date":"', COALESCE(OLD.feature_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"feature_entity_id":"', HEX(OLD.feature_entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_features_a_u`;;
CREATE TRIGGER `trigger_carbon_features_a_u` AFTER UPDATE ON `carbon_features` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"feature_code":"', COALESCE(NEW.feature_code,''), '"');SET history_data = CONCAT(history_data,'"feature_creation_date":"', COALESCE(NEW.feature_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_features_a_i`;;
CREATE TRIGGER `trigger_carbon_features_a_i` AFTER INSERT ON `carbon_features` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '"');SET history_data = CONCAT(history_data,'"feature_code":"', COALESCE(NEW.feature_code,''), '"');SET history_data = CONCAT(history_data,'"feature_creation_date":"', COALESCE(NEW.feature_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"feature_entity_id":"', HEX(NEW.feature_entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_features', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_group_references_b_d`;;
CREATE TRIGGER `trigger_carbon_group_references_b_d` BEFORE DELETE ON `carbon_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(OLD.group_id), '"');SET history_data = CONCAT(history_data,'"allowed_to_grant_group_id":"', HEX(OLD.allowed_to_grant_group_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_group_references', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_group_references_a_u`;;
CREATE TRIGGER `trigger_carbon_group_references_a_u` AFTER UPDATE ON `carbon_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(NEW.group_id), '"');SET history_data = CONCAT(history_data,'"allowed_to_grant_group_id":"', HEX(NEW.allowed_to_grant_group_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_group_references', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_group_references_a_i`;;
CREATE TRIGGER `trigger_carbon_group_references_a_i` AFTER INSERT ON `carbon_group_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(NEW.group_id), '"');SET history_data = CONCAT(history_data,'"allowed_to_grant_group_id":"', HEX(NEW.allowed_to_grant_group_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_group_references', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_groups_b_d`;;
CREATE TRIGGER `trigger_carbon_groups_b_d` BEFORE DELETE ON `carbon_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_name":"', COALESCE(OLD.group_name,''), '"');SET history_data = CONCAT(history_data,'"entity_id":"', HEX(OLD.entity_id), '"');SET history_data = CONCAT(history_data,'"created_by":"', HEX(OLD.created_by), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(OLD.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(OLD.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_groups_a_u`;;
CREATE TRIGGER `trigger_carbon_groups_a_u` AFTER UPDATE ON `carbon_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_name":"', COALESCE(NEW.group_name,''), '"');SET history_data = CONCAT(history_data,'"entity_id":"', HEX(NEW.entity_id), '"');SET history_data = CONCAT(history_data,'"created_by":"', HEX(NEW.created_by), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(NEW.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_groups_a_i`;;
CREATE TRIGGER `trigger_carbon_groups_a_i` AFTER INSERT ON `carbon_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_name":"', COALESCE(NEW.group_name,''), '"');SET history_data = CONCAT(history_data,'"entity_id":"', HEX(NEW.entity_id), '"');SET history_data = CONCAT(history_data,'"created_by":"', HEX(NEW.created_by), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(NEW.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_groups', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_location_references_b_d`;;
CREATE TRIGGER `trigger_carbon_location_references_b_d` BEFORE DELETE ON `carbon_location_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_reference":"', HEX(OLD.entity_reference), '"');SET history_data = CONCAT(history_data,'"location_reference":"', HEX(OLD.location_reference), '"');SET history_data = CONCAT(history_data,'"location_time":"', COALESCE(OLD.location_time,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_location_references', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_location_references_a_u`;;
CREATE TRIGGER `trigger_carbon_location_references_a_u` AFTER UPDATE ON `carbon_location_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_reference":"', HEX(NEW.entity_reference), '"');SET history_data = CONCAT(history_data,'"location_reference":"', HEX(NEW.location_reference), '"');SET history_data = CONCAT(history_data,'"location_time":"', COALESCE(NEW.location_time,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_location_references', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_location_references_a_i`;;
CREATE TRIGGER `trigger_carbon_location_references_a_i` AFTER INSERT ON `carbon_location_references` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_reference":"', HEX(NEW.entity_reference), '"');SET history_data = CONCAT(history_data,'"location_reference":"', HEX(NEW.location_reference), '"');SET history_data = CONCAT(history_data,'"location_time":"', COALESCE(NEW.location_time,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_location_references', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_locations_b_d`;;
CREATE TRIGGER `trigger_carbon_locations_b_d` BEFORE DELETE ON `carbon_locations` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_id":"', HEX(OLD.entity_id), '"');SET history_data = CONCAT(history_data,'"latitude":"', COALESCE(OLD.latitude,''), '"');SET history_data = CONCAT(history_data,'"longitude":"', COALESCE(OLD.longitude,''), '"');SET history_data = CONCAT(history_data,'"street":"', COALESCE(OLD.street,''), '"');SET history_data = CONCAT(history_data,'"city":"', COALESCE(OLD.city,''), '"');SET history_data = CONCAT(history_data,'"state":"', COALESCE(OLD.state,''), '"');SET history_data = CONCAT(history_data,'"elevation":"', COALESCE(OLD.elevation,''), '"');SET history_data = CONCAT(history_data,'"zip":"', COALESCE(OLD.zip,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(OLD.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_locations_a_u`;;
CREATE TRIGGER `trigger_carbon_locations_a_u` AFTER UPDATE ON `carbon_locations` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_id":"', HEX(NEW.entity_id), '"');SET history_data = CONCAT(history_data,'"latitude":"', COALESCE(NEW.latitude,''), '"');SET history_data = CONCAT(history_data,'"longitude":"', COALESCE(NEW.longitude,''), '"');SET history_data = CONCAT(history_data,'"street":"', COALESCE(NEW.street,''), '"');SET history_data = CONCAT(history_data,'"city":"', COALESCE(NEW.city,''), '"');SET history_data = CONCAT(history_data,'"state":"', COALESCE(NEW.state,''), '"');SET history_data = CONCAT(history_data,'"elevation":"', COALESCE(NEW.elevation,''), '"');SET history_data = CONCAT(history_data,'"zip":"', COALESCE(NEW.zip,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(NEW.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_locations_a_i`;;
CREATE TRIGGER `trigger_carbon_locations_a_i` AFTER INSERT ON `carbon_locations` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"entity_id":"', HEX(NEW.entity_id), '"');SET history_data = CONCAT(history_data,'"latitude":"', COALESCE(NEW.latitude,''), '"');SET history_data = CONCAT(history_data,'"longitude":"', COALESCE(NEW.longitude,''), '"');SET history_data = CONCAT(history_data,'"street":"', COALESCE(NEW.street,''), '"');SET history_data = CONCAT(history_data,'"city":"', COALESCE(NEW.city,''), '"');SET history_data = CONCAT(history_data,'"state":"', COALESCE(NEW.state,''), '"');SET history_data = CONCAT(history_data,'"elevation":"', COALESCE(NEW.elevation,''), '"');SET history_data = CONCAT(history_data,'"zip":"', COALESCE(NEW.zip,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"entity_id":"', HEX(NEW.entity_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_locations', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_photos_b_d`;;
CREATE TRIGGER `trigger_carbon_photos_b_d` BEFORE DELETE ON `carbon_photos` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(OLD.parent_id), '"');SET history_data = CONCAT(history_data,'"photo_id":"', HEX(OLD.photo_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"photo_path":"', COALESCE(OLD.photo_path,''), '"');SET history_data = CONCAT(history_data,'"photo_description":"', COALESCE(OLD.photo_description,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"parent_id":"', HEX(OLD.parent_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_photos_a_u`;;
CREATE TRIGGER `trigger_carbon_photos_a_u` AFTER UPDATE ON `carbon_photos` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(NEW.parent_id), '"');SET history_data = CONCAT(history_data,'"photo_id":"', HEX(NEW.photo_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"photo_path":"', COALESCE(NEW.photo_path,''), '"');SET history_data = CONCAT(history_data,'"photo_description":"', COALESCE(NEW.photo_description,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"parent_id":"', HEX(NEW.parent_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_photos_a_i`;;
CREATE TRIGGER `trigger_carbon_photos_a_i` AFTER INSERT ON `carbon_photos` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"parent_id":"', HEX(NEW.parent_id), '"');SET history_data = CONCAT(history_data,'"photo_id":"', HEX(NEW.photo_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"photo_path":"', COALESCE(NEW.photo_path,''), '"');SET history_data = CONCAT(history_data,'"photo_description":"', COALESCE(NEW.photo_description,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"parent_id":"', HEX(NEW.parent_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_photos', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_reports_b_d`;;
CREATE TRIGGER `trigger_carbon_reports_b_d` BEFORE DELETE ON `carbon_reports` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"log_level":"', COALESCE(OLD.log_level,''), '"');SET history_data = CONCAT(history_data,'"report":"', COALESCE(OLD.report,''), '"');SET history_data = CONCAT(history_data,'"date":"', COALESCE(OLD.date,''), '"');SET history_data = CONCAT(history_data,'"call_trace":"', COALESCE(OLD.call_trace,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_reports', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_reports_a_u`;;
CREATE TRIGGER `trigger_carbon_reports_a_u` AFTER UPDATE ON `carbon_reports` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"log_level":"', COALESCE(NEW.log_level,''), '"');SET history_data = CONCAT(history_data,'"report":"', COALESCE(NEW.report,''), '"');SET history_data = CONCAT(history_data,'"date":"', COALESCE(NEW.date,''), '"');SET history_data = CONCAT(history_data,'"call_trace":"', COALESCE(NEW.call_trace,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_reports', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_reports_a_i`;;
CREATE TRIGGER `trigger_carbon_reports_a_i` AFTER INSERT ON `carbon_reports` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"log_level":"', COALESCE(NEW.log_level,''), '"');SET history_data = CONCAT(history_data,'"report":"', COALESCE(NEW.report,''), '"');SET history_data = CONCAT(history_data,'"date":"', COALESCE(NEW.date,''), '"');SET history_data = CONCAT(history_data,'"call_trace":"', COALESCE(NEW.call_trace,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_reports', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_sessions_b_d`;;
CREATE TRIGGER `trigger_carbon_sessions_b_d` BEFORE DELETE ON `carbon_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(OLD.user_ip,''), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(OLD.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(OLD.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(OLD.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(OLD.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(OLD.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_sessions', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_sessions_a_u`;;
CREATE TRIGGER `trigger_carbon_sessions_a_u` AFTER UPDATE ON `carbon_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(NEW.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(NEW.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(NEW.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(NEW.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(NEW.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_sessions', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_sessions_a_i`;;
CREATE TRIGGER `trigger_carbon_sessions_a_i` AFTER INSERT ON `carbon_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(NEW.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(NEW.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(NEW.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(NEW.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(NEW.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_sessions', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_b_d`;;
CREATE TRIGGER `trigger_carbon_user_followers_b_d` BEFORE DELETE ON `carbon_user_followers` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"follower_table_id":"', HEX(OLD.follower_table_id), '"');SET history_data = CONCAT(history_data,'"follows_user_id":"', HEX(OLD.follows_user_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"follower_table_id":"', HEX(OLD.follower_table_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_a_u`;;
CREATE TRIGGER `trigger_carbon_user_followers_a_u` AFTER UPDATE ON `carbon_user_followers` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"follower_table_id":"', HEX(NEW.follower_table_id), '"');SET history_data = CONCAT(history_data,'"follows_user_id":"', HEX(NEW.follows_user_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"follower_table_id":"', HEX(NEW.follower_table_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_followers_a_i`;;
CREATE TRIGGER `trigger_carbon_user_followers_a_i` AFTER INSERT ON `carbon_user_followers` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"follower_table_id":"', HEX(NEW.follower_table_id), '"');SET history_data = CONCAT(history_data,'"follows_user_id":"', HEX(NEW.follows_user_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"follower_table_id":"', HEX(NEW.follower_table_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_followers', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_groups_b_d`;;
CREATE TRIGGER `trigger_carbon_user_groups_b_d` BEFORE DELETE ON `carbon_user_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(OLD.group_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_groups', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_groups_a_u`;;
CREATE TRIGGER `trigger_carbon_user_groups_a_u` AFTER UPDATE ON `carbon_user_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(NEW.group_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_groups', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_groups_a_i`;;
CREATE TRIGGER `trigger_carbon_user_groups_a_i` AFTER INSERT ON `carbon_user_groups` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"group_id":"', HEX(NEW.group_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_groups', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_b_d`;;
CREATE TRIGGER `trigger_carbon_user_messages_b_d` BEFORE DELETE ON `carbon_user_messages` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"message_id":"', HEX(OLD.message_id), '"');SET history_data = CONCAT(history_data,'"from_user_id":"', HEX(OLD.from_user_id), '"');SET history_data = CONCAT(history_data,'"to_user_id":"', HEX(OLD.to_user_id), '"');SET history_data = CONCAT(history_data,'"message":"', COALESCE(OLD.message,''), '"');SET history_data = CONCAT(history_data,'"message_read":"', COALESCE(OLD.message_read,''), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(OLD.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"message_id":"', HEX(OLD.message_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_a_u`;;
CREATE TRIGGER `trigger_carbon_user_messages_a_u` AFTER UPDATE ON `carbon_user_messages` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"message_id":"', HEX(NEW.message_id), '"');SET history_data = CONCAT(history_data,'"from_user_id":"', HEX(NEW.from_user_id), '"');SET history_data = CONCAT(history_data,'"to_user_id":"', HEX(NEW.to_user_id), '"');SET history_data = CONCAT(history_data,'"message":"', COALESCE(NEW.message,''), '"');SET history_data = CONCAT(history_data,'"message_read":"', COALESCE(NEW.message_read,''), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"message_id":"', HEX(NEW.message_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_messages_a_i`;;
CREATE TRIGGER `trigger_carbon_user_messages_a_i` AFTER INSERT ON `carbon_user_messages` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"message_id":"', HEX(NEW.message_id), '"');SET history_data = CONCAT(history_data,'"from_user_id":"', HEX(NEW.from_user_id), '"');SET history_data = CONCAT(history_data,'"to_user_id":"', HEX(NEW.to_user_id), '"');SET history_data = CONCAT(history_data,'"message":"', COALESCE(NEW.message,''), '"');SET history_data = CONCAT(history_data,'"message_read":"', COALESCE(NEW.message_read,''), '"');SET history_data = CONCAT(history_data,'"creation_date":"', COALESCE(NEW.creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"message_id":"', HEX(NEW.message_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_messages', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_sessions_b_d`;;
CREATE TRIGGER `trigger_carbon_user_sessions_b_d` BEFORE DELETE ON `carbon_user_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', HEX(OLD.user_ip), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(OLD.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(OLD.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(OLD.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(OLD.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(OLD.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_sessions', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_sessions_a_u`;;
CREATE TRIGGER `trigger_carbon_user_sessions_a_u` AFTER UPDATE ON `carbon_user_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', HEX(NEW.user_ip), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(NEW.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(NEW.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(NEW.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(NEW.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(NEW.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_sessions', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_sessions_a_i`;;
CREATE TRIGGER `trigger_carbon_user_sessions_a_i` AFTER INSERT ON `carbon_user_sessions` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_ip":"', HEX(NEW.user_ip), '"');SET history_data = CONCAT(history_data,'"session_id":"', COALESCE(NEW.session_id,''), '"');SET history_data = CONCAT(history_data,'"session_expires":"', COALESCE(NEW.session_expires,''), '"');SET history_data = CONCAT(history_data,'"session_data":"', COALESCE(NEW.session_data,''), '"');SET history_data = CONCAT(history_data,'"user_online_status":"', COALESCE(NEW.user_online_status,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"session_id":"', COALESCE(NEW.session_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_sessions', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_b_d`;;
CREATE TRIGGER `trigger_carbon_user_tasks_b_d` BEFORE DELETE ON `carbon_user_tasks` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"task_id":"', HEX(OLD.task_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"from_id":"', HEX(OLD.from_id), '"');SET history_data = CONCAT(history_data,'"task_name":"', COALESCE(OLD.task_name,''), '"');SET history_data = CONCAT(history_data,'"task_description":"', COALESCE(OLD.task_description,''), '"');SET history_data = CONCAT(history_data,'"percent_complete":"', COALESCE(OLD.percent_complete,''), '"');SET history_data = CONCAT(history_data,'"start_date":"', COALESCE(OLD.start_date,''), '"');SET history_data = CONCAT(history_data,'"end_date":"', COALESCE(OLD.end_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"task_id":"', HEX(OLD.task_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_a_u`;;
CREATE TRIGGER `trigger_carbon_user_tasks_a_u` AFTER UPDATE ON `carbon_user_tasks` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"task_id":"', HEX(NEW.task_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"from_id":"', HEX(NEW.from_id), '"');SET history_data = CONCAT(history_data,'"task_name":"', COALESCE(NEW.task_name,''), '"');SET history_data = CONCAT(history_data,'"task_description":"', COALESCE(NEW.task_description,''), '"');SET history_data = CONCAT(history_data,'"percent_complete":"', COALESCE(NEW.percent_complete,''), '"');SET history_data = CONCAT(history_data,'"start_date":"', COALESCE(NEW.start_date,''), '"');SET history_data = CONCAT(history_data,'"end_date":"', COALESCE(NEW.end_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"task_id":"', HEX(NEW.task_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_user_tasks_a_i`;;
CREATE TRIGGER `trigger_carbon_user_tasks_a_i` AFTER INSERT ON `carbon_user_tasks` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"task_id":"', HEX(NEW.task_id), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"from_id":"', HEX(NEW.from_id), '"');SET history_data = CONCAT(history_data,'"task_name":"', COALESCE(NEW.task_name,''), '"');SET history_data = CONCAT(history_data,'"task_description":"', COALESCE(NEW.task_description,''), '"');SET history_data = CONCAT(history_data,'"percent_complete":"', COALESCE(NEW.percent_complete,''), '"');SET history_data = CONCAT(history_data,'"start_date":"', COALESCE(NEW.start_date,''), '"');SET history_data = CONCAT(history_data,'"end_date":"', COALESCE(NEW.end_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"task_id":"', HEX(NEW.task_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_user_tasks', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_users_b_d`;;
CREATE TRIGGER `trigger_carbon_users_b_d` BEFORE DELETE ON `carbon_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_username":"', COALESCE(OLD.user_username,''), '"');SET history_data = CONCAT(history_data,'"user_password":"', COALESCE(OLD.user_password,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(OLD.user_id), '"');SET history_data = CONCAT(history_data,'"user_type":"', COALESCE(OLD.user_type,''), '"');SET history_data = CONCAT(history_data,'"user_sport":"', COALESCE(OLD.user_sport,''), '"');SET history_data = CONCAT(history_data,'"user_session_id":"', COALESCE(OLD.user_session_id,''), '"');SET history_data = CONCAT(history_data,'"user_facebook_id":"', COALESCE(OLD.user_facebook_id,''), '"');SET history_data = CONCAT(history_data,'"user_first_name":"', COALESCE(OLD.user_first_name,''), '"');SET history_data = CONCAT(history_data,'"user_last_name":"', COALESCE(OLD.user_last_name,''), '"');SET history_data = CONCAT(history_data,'"user_profile_pic":"', COALESCE(OLD.user_profile_pic,''), '"');SET history_data = CONCAT(history_data,'"user_profile_uri":"', COALESCE(OLD.user_profile_uri,''), '"');SET history_data = CONCAT(history_data,'"user_cover_photo":"', COALESCE(OLD.user_cover_photo,''), '"');SET history_data = CONCAT(history_data,'"user_birthday":"', COALESCE(OLD.user_birthday,''), '"');SET history_data = CONCAT(history_data,'"user_gender":"', COALESCE(OLD.user_gender,''), '"');SET history_data = CONCAT(history_data,'"user_about_me":"', COALESCE(OLD.user_about_me,''), '"');SET history_data = CONCAT(history_data,'"user_rank":"', COALESCE(OLD.user_rank,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(OLD.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_email_code":"', COALESCE(OLD.user_email_code,''), '"');SET history_data = CONCAT(history_data,'"user_email_confirmed":"', COALESCE(OLD.user_email_confirmed,''), '"');SET history_data = CONCAT(history_data,'"user_generated_string":"', COALESCE(OLD.user_generated_string,''), '"');SET history_data = CONCAT(history_data,'"user_membership":"', COALESCE(OLD.user_membership,''), '"');SET history_data = CONCAT(history_data,'"user_deactivated":"', COALESCE(OLD.user_deactivated,''), '"');SET history_data = CONCAT(history_data,'"user_last_login":"', COALESCE(OLD.user_last_login,''), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(OLD.user_ip,''), '"');SET history_data = CONCAT(history_data,'"user_education_history":"', COALESCE(OLD.user_education_history,''), '"');SET history_data = CONCAT(history_data,'"user_location":"', COALESCE(OLD.user_location,''), '"');SET history_data = CONCAT(history_data,'"user_creation_date":"', COALESCE(OLD.user_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"user_id":"', HEX(OLD.user_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_users_a_u`;;
CREATE TRIGGER `trigger_carbon_users_a_u` AFTER UPDATE ON `carbon_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_username":"', COALESCE(NEW.user_username,''), '"');SET history_data = CONCAT(history_data,'"user_password":"', COALESCE(NEW.user_password,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_type":"', COALESCE(NEW.user_type,''), '"');SET history_data = CONCAT(history_data,'"user_sport":"', COALESCE(NEW.user_sport,''), '"');SET history_data = CONCAT(history_data,'"user_session_id":"', COALESCE(NEW.user_session_id,''), '"');SET history_data = CONCAT(history_data,'"user_facebook_id":"', COALESCE(NEW.user_facebook_id,''), '"');SET history_data = CONCAT(history_data,'"user_first_name":"', COALESCE(NEW.user_first_name,''), '"');SET history_data = CONCAT(history_data,'"user_last_name":"', COALESCE(NEW.user_last_name,''), '"');SET history_data = CONCAT(history_data,'"user_profile_pic":"', COALESCE(NEW.user_profile_pic,''), '"');SET history_data = CONCAT(history_data,'"user_profile_uri":"', COALESCE(NEW.user_profile_uri,''), '"');SET history_data = CONCAT(history_data,'"user_cover_photo":"', COALESCE(NEW.user_cover_photo,''), '"');SET history_data = CONCAT(history_data,'"user_birthday":"', COALESCE(NEW.user_birthday,''), '"');SET history_data = CONCAT(history_data,'"user_gender":"', COALESCE(NEW.user_gender,''), '"');SET history_data = CONCAT(history_data,'"user_about_me":"', COALESCE(NEW.user_about_me,''), '"');SET history_data = CONCAT(history_data,'"user_rank":"', COALESCE(NEW.user_rank,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(NEW.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_email_code":"', COALESCE(NEW.user_email_code,''), '"');SET history_data = CONCAT(history_data,'"user_email_confirmed":"', COALESCE(NEW.user_email_confirmed,''), '"');SET history_data = CONCAT(history_data,'"user_generated_string":"', COALESCE(NEW.user_generated_string,''), '"');SET history_data = CONCAT(history_data,'"user_membership":"', COALESCE(NEW.user_membership,''), '"');SET history_data = CONCAT(history_data,'"user_deactivated":"', COALESCE(NEW.user_deactivated,''), '"');SET history_data = CONCAT(history_data,'"user_last_login":"', COALESCE(NEW.user_last_login,''), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');SET history_data = CONCAT(history_data,'"user_education_history":"', COALESCE(NEW.user_education_history,''), '"');SET history_data = CONCAT(history_data,'"user_location":"', COALESCE(NEW.user_location,''), '"');SET history_data = CONCAT(history_data,'"user_creation_date":"', COALESCE(NEW.user_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"user_id":"', HEX(NEW.user_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_users_a_i`;;
CREATE TRIGGER `trigger_carbon_users_a_i` AFTER INSERT ON `carbon_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"user_username":"', COALESCE(NEW.user_username,''), '"');SET history_data = CONCAT(history_data,'"user_password":"', COALESCE(NEW.user_password,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', HEX(NEW.user_id), '"');SET history_data = CONCAT(history_data,'"user_type":"', COALESCE(NEW.user_type,''), '"');SET history_data = CONCAT(history_data,'"user_sport":"', COALESCE(NEW.user_sport,''), '"');SET history_data = CONCAT(history_data,'"user_session_id":"', COALESCE(NEW.user_session_id,''), '"');SET history_data = CONCAT(history_data,'"user_facebook_id":"', COALESCE(NEW.user_facebook_id,''), '"');SET history_data = CONCAT(history_data,'"user_first_name":"', COALESCE(NEW.user_first_name,''), '"');SET history_data = CONCAT(history_data,'"user_last_name":"', COALESCE(NEW.user_last_name,''), '"');SET history_data = CONCAT(history_data,'"user_profile_pic":"', COALESCE(NEW.user_profile_pic,''), '"');SET history_data = CONCAT(history_data,'"user_profile_uri":"', COALESCE(NEW.user_profile_uri,''), '"');SET history_data = CONCAT(history_data,'"user_cover_photo":"', COALESCE(NEW.user_cover_photo,''), '"');SET history_data = CONCAT(history_data,'"user_birthday":"', COALESCE(NEW.user_birthday,''), '"');SET history_data = CONCAT(history_data,'"user_gender":"', COALESCE(NEW.user_gender,''), '"');SET history_data = CONCAT(history_data,'"user_about_me":"', COALESCE(NEW.user_about_me,''), '"');SET history_data = CONCAT(history_data,'"user_rank":"', COALESCE(NEW.user_rank,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(NEW.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_email_code":"', COALESCE(NEW.user_email_code,''), '"');SET history_data = CONCAT(history_data,'"user_email_confirmed":"', COALESCE(NEW.user_email_confirmed,''), '"');SET history_data = CONCAT(history_data,'"user_generated_string":"', COALESCE(NEW.user_generated_string,''), '"');SET history_data = CONCAT(history_data,'"user_membership":"', COALESCE(NEW.user_membership,''), '"');SET history_data = CONCAT(history_data,'"user_deactivated":"', COALESCE(NEW.user_deactivated,''), '"');SET history_data = CONCAT(history_data,'"user_last_login":"', COALESCE(NEW.user_last_login,''), '"');SET history_data = CONCAT(history_data,'"user_ip":"', COALESCE(NEW.user_ip,''), '"');SET history_data = CONCAT(history_data,'"user_education_history":"', COALESCE(NEW.user_education_history,''), '"');SET history_data = CONCAT(history_data,'"user_location":"', COALESCE(NEW.user_location,''), '"');SET history_data = CONCAT(history_data,'"user_creation_date":"', COALESCE(NEW.user_creation_date,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"user_id":"', HEX(NEW.user_id), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_users', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_commentmeta_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_commentmeta_b_d` BEFORE DELETE ON `carbon_wp_commentmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '"');SET history_data = CONCAT(history_data,'"comment_id":"', COALESCE(OLD.comment_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(OLD.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(OLD.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_commentmeta', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_commentmeta_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_commentmeta_a_u` AFTER UPDATE ON `carbon_wp_commentmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"comment_id":"', COALESCE(NEW.comment_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_commentmeta', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_commentmeta_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_commentmeta_a_i` AFTER INSERT ON `carbon_wp_commentmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"comment_id":"', COALESCE(NEW.comment_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_commentmeta', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_comments_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_comments_b_d` BEFORE DELETE ON `carbon_wp_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"comment_ID":"', COALESCE(OLD.comment_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_post_ID":"', COALESCE(OLD.comment_post_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_author":"', COALESCE(OLD.comment_author,''), '"');SET history_data = CONCAT(history_data,'"comment_author_email":"', COALESCE(OLD.comment_author_email,''), '"');SET history_data = CONCAT(history_data,'"comment_author_url":"', COALESCE(OLD.comment_author_url,''), '"');SET history_data = CONCAT(history_data,'"comment_author_IP":"', COALESCE(OLD.comment_author_IP,''), '"');SET history_data = CONCAT(history_data,'"comment_date":"', COALESCE(OLD.comment_date,''), '"');SET history_data = CONCAT(history_data,'"comment_date_gmt":"', COALESCE(OLD.comment_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"comment_content":"', COALESCE(OLD.comment_content,''), '"');SET history_data = CONCAT(history_data,'"comment_karma":"', COALESCE(OLD.comment_karma,''), '"');SET history_data = CONCAT(history_data,'"comment_approved":"', COALESCE(OLD.comment_approved,''), '"');SET history_data = CONCAT(history_data,'"comment_agent":"', COALESCE(OLD.comment_agent,''), '"');SET history_data = CONCAT(history_data,'"comment_type":"', COALESCE(OLD.comment_type,''), '"');SET history_data = CONCAT(history_data,'"comment_parent":"', COALESCE(OLD.comment_parent,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(OLD.user_id,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_ID":"', COALESCE(OLD.comment_ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_comments', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_comments_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_comments_a_u` AFTER UPDATE ON `carbon_wp_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"comment_ID":"', COALESCE(NEW.comment_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_post_ID":"', COALESCE(NEW.comment_post_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_author":"', COALESCE(NEW.comment_author,''), '"');SET history_data = CONCAT(history_data,'"comment_author_email":"', COALESCE(NEW.comment_author_email,''), '"');SET history_data = CONCAT(history_data,'"comment_author_url":"', COALESCE(NEW.comment_author_url,''), '"');SET history_data = CONCAT(history_data,'"comment_author_IP":"', COALESCE(NEW.comment_author_IP,''), '"');SET history_data = CONCAT(history_data,'"comment_date":"', COALESCE(NEW.comment_date,''), '"');SET history_data = CONCAT(history_data,'"comment_date_gmt":"', COALESCE(NEW.comment_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"comment_content":"', COALESCE(NEW.comment_content,''), '"');SET history_data = CONCAT(history_data,'"comment_karma":"', COALESCE(NEW.comment_karma,''), '"');SET history_data = CONCAT(history_data,'"comment_approved":"', COALESCE(NEW.comment_approved,''), '"');SET history_data = CONCAT(history_data,'"comment_agent":"', COALESCE(NEW.comment_agent,''), '"');SET history_data = CONCAT(history_data,'"comment_type":"', COALESCE(NEW.comment_type,''), '"');SET history_data = CONCAT(history_data,'"comment_parent":"', COALESCE(NEW.comment_parent,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(NEW.user_id,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_ID":"', COALESCE(NEW.comment_ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_comments', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_comments_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_comments_a_i` AFTER INSERT ON `carbon_wp_comments` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"comment_ID":"', COALESCE(NEW.comment_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_post_ID":"', COALESCE(NEW.comment_post_ID,''), '"');SET history_data = CONCAT(history_data,'"comment_author":"', COALESCE(NEW.comment_author,''), '"');SET history_data = CONCAT(history_data,'"comment_author_email":"', COALESCE(NEW.comment_author_email,''), '"');SET history_data = CONCAT(history_data,'"comment_author_url":"', COALESCE(NEW.comment_author_url,''), '"');SET history_data = CONCAT(history_data,'"comment_author_IP":"', COALESCE(NEW.comment_author_IP,''), '"');SET history_data = CONCAT(history_data,'"comment_date":"', COALESCE(NEW.comment_date,''), '"');SET history_data = CONCAT(history_data,'"comment_date_gmt":"', COALESCE(NEW.comment_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"comment_content":"', COALESCE(NEW.comment_content,''), '"');SET history_data = CONCAT(history_data,'"comment_karma":"', COALESCE(NEW.comment_karma,''), '"');SET history_data = CONCAT(history_data,'"comment_approved":"', COALESCE(NEW.comment_approved,''), '"');SET history_data = CONCAT(history_data,'"comment_agent":"', COALESCE(NEW.comment_agent,''), '"');SET history_data = CONCAT(history_data,'"comment_type":"', COALESCE(NEW.comment_type,''), '"');SET history_data = CONCAT(history_data,'"comment_parent":"', COALESCE(NEW.comment_parent,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(NEW.user_id,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"comment_ID":"', COALESCE(NEW.comment_ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_comments', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_links_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_links_b_d` BEFORE DELETE ON `carbon_wp_links` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"link_id":"', COALESCE(OLD.link_id,''), '"');SET history_data = CONCAT(history_data,'"link_url":"', COALESCE(OLD.link_url,''), '"');SET history_data = CONCAT(history_data,'"link_name":"', COALESCE(OLD.link_name,''), '"');SET history_data = CONCAT(history_data,'"link_image":"', COALESCE(OLD.link_image,''), '"');SET history_data = CONCAT(history_data,'"link_target":"', COALESCE(OLD.link_target,''), '"');SET history_data = CONCAT(history_data,'"link_description":"', COALESCE(OLD.link_description,''), '"');SET history_data = CONCAT(history_data,'"link_visible":"', COALESCE(OLD.link_visible,''), '"');SET history_data = CONCAT(history_data,'"link_owner":"', COALESCE(OLD.link_owner,''), '"');SET history_data = CONCAT(history_data,'"link_rating":"', COALESCE(OLD.link_rating,''), '"');SET history_data = CONCAT(history_data,'"link_updated":"', COALESCE(OLD.link_updated,''), '"');SET history_data = CONCAT(history_data,'"link_rel":"', COALESCE(OLD.link_rel,''), '"');SET history_data = CONCAT(history_data,'"link_notes":"', COALESCE(OLD.link_notes,''), '"');SET history_data = CONCAT(history_data,'"link_rss":"', COALESCE(OLD.link_rss,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"link_id":"', COALESCE(OLD.link_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_links', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_links_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_links_a_u` AFTER UPDATE ON `carbon_wp_links` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"link_id":"', COALESCE(NEW.link_id,''), '"');SET history_data = CONCAT(history_data,'"link_url":"', COALESCE(NEW.link_url,''), '"');SET history_data = CONCAT(history_data,'"link_name":"', COALESCE(NEW.link_name,''), '"');SET history_data = CONCAT(history_data,'"link_image":"', COALESCE(NEW.link_image,''), '"');SET history_data = CONCAT(history_data,'"link_target":"', COALESCE(NEW.link_target,''), '"');SET history_data = CONCAT(history_data,'"link_description":"', COALESCE(NEW.link_description,''), '"');SET history_data = CONCAT(history_data,'"link_visible":"', COALESCE(NEW.link_visible,''), '"');SET history_data = CONCAT(history_data,'"link_owner":"', COALESCE(NEW.link_owner,''), '"');SET history_data = CONCAT(history_data,'"link_rating":"', COALESCE(NEW.link_rating,''), '"');SET history_data = CONCAT(history_data,'"link_updated":"', COALESCE(NEW.link_updated,''), '"');SET history_data = CONCAT(history_data,'"link_rel":"', COALESCE(NEW.link_rel,''), '"');SET history_data = CONCAT(history_data,'"link_notes":"', COALESCE(NEW.link_notes,''), '"');SET history_data = CONCAT(history_data,'"link_rss":"', COALESCE(NEW.link_rss,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"link_id":"', COALESCE(NEW.link_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_links', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_links_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_links_a_i` AFTER INSERT ON `carbon_wp_links` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"link_id":"', COALESCE(NEW.link_id,''), '"');SET history_data = CONCAT(history_data,'"link_url":"', COALESCE(NEW.link_url,''), '"');SET history_data = CONCAT(history_data,'"link_name":"', COALESCE(NEW.link_name,''), '"');SET history_data = CONCAT(history_data,'"link_image":"', COALESCE(NEW.link_image,''), '"');SET history_data = CONCAT(history_data,'"link_target":"', COALESCE(NEW.link_target,''), '"');SET history_data = CONCAT(history_data,'"link_description":"', COALESCE(NEW.link_description,''), '"');SET history_data = CONCAT(history_data,'"link_visible":"', COALESCE(NEW.link_visible,''), '"');SET history_data = CONCAT(history_data,'"link_owner":"', COALESCE(NEW.link_owner,''), '"');SET history_data = CONCAT(history_data,'"link_rating":"', COALESCE(NEW.link_rating,''), '"');SET history_data = CONCAT(history_data,'"link_updated":"', COALESCE(NEW.link_updated,''), '"');SET history_data = CONCAT(history_data,'"link_rel":"', COALESCE(NEW.link_rel,''), '"');SET history_data = CONCAT(history_data,'"link_notes":"', COALESCE(NEW.link_notes,''), '"');SET history_data = CONCAT(history_data,'"link_rss":"', COALESCE(NEW.link_rss,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"link_id":"', COALESCE(NEW.link_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_links', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_options_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_options_b_d` BEFORE DELETE ON `carbon_wp_options` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"option_id":"', COALESCE(OLD.option_id,''), '"');SET history_data = CONCAT(history_data,'"option_name":"', COALESCE(OLD.option_name,''), '"');SET history_data = CONCAT(history_data,'"option_value":"', COALESCE(OLD.option_value,''), '"');SET history_data = CONCAT(history_data,'"autoload":"', COALESCE(OLD.autoload,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"option_id":"', COALESCE(OLD.option_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_options', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_options_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_options_a_u` AFTER UPDATE ON `carbon_wp_options` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"option_id":"', COALESCE(NEW.option_id,''), '"');SET history_data = CONCAT(history_data,'"option_name":"', COALESCE(NEW.option_name,''), '"');SET history_data = CONCAT(history_data,'"option_value":"', COALESCE(NEW.option_value,''), '"');SET history_data = CONCAT(history_data,'"autoload":"', COALESCE(NEW.autoload,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"option_id":"', COALESCE(NEW.option_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_options', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_options_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_options_a_i` AFTER INSERT ON `carbon_wp_options` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"option_id":"', COALESCE(NEW.option_id,''), '"');SET history_data = CONCAT(history_data,'"option_name":"', COALESCE(NEW.option_name,''), '"');SET history_data = CONCAT(history_data,'"option_value":"', COALESCE(NEW.option_value,''), '"');SET history_data = CONCAT(history_data,'"autoload":"', COALESCE(NEW.autoload,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"option_id":"', COALESCE(NEW.option_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_options', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_postmeta_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_postmeta_b_d` BEFORE DELETE ON `carbon_wp_postmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '"');SET history_data = CONCAT(history_data,'"post_id":"', COALESCE(OLD.post_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(OLD.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(OLD.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_postmeta', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_postmeta_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_postmeta_a_u` AFTER UPDATE ON `carbon_wp_postmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"post_id":"', COALESCE(NEW.post_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_postmeta', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_postmeta_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_postmeta_a_i` AFTER INSERT ON `carbon_wp_postmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"post_id":"', COALESCE(NEW.post_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_postmeta', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_posts_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_posts_b_d` BEFORE DELETE ON `carbon_wp_posts` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(OLD.ID,''), '"');SET history_data = CONCAT(history_data,'"post_author":"', COALESCE(OLD.post_author,''), '"');SET history_data = CONCAT(history_data,'"post_date":"', COALESCE(OLD.post_date,''), '"');SET history_data = CONCAT(history_data,'"post_date_gmt":"', COALESCE(OLD.post_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content":"', COALESCE(OLD.post_content,''), '"');SET history_data = CONCAT(history_data,'"post_title":"', COALESCE(OLD.post_title,''), '"');SET history_data = CONCAT(history_data,'"post_excerpt":"', COALESCE(OLD.post_excerpt,''), '"');SET history_data = CONCAT(history_data,'"post_status":"', COALESCE(OLD.post_status,''), '"');SET history_data = CONCAT(history_data,'"comment_status":"', COALESCE(OLD.comment_status,''), '"');SET history_data = CONCAT(history_data,'"ping_status":"', COALESCE(OLD.ping_status,''), '"');SET history_data = CONCAT(history_data,'"post_password":"', COALESCE(OLD.post_password,''), '"');SET history_data = CONCAT(history_data,'"post_name":"', COALESCE(OLD.post_name,''), '"');SET history_data = CONCAT(history_data,'"to_ping":"', COALESCE(OLD.to_ping,''), '"');SET history_data = CONCAT(history_data,'"pinged":"', COALESCE(OLD.pinged,''), '"');SET history_data = CONCAT(history_data,'"post_modified":"', COALESCE(OLD.post_modified,''), '"');SET history_data = CONCAT(history_data,'"post_modified_gmt":"', COALESCE(OLD.post_modified_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content_filtered":"', COALESCE(OLD.post_content_filtered,''), '"');SET history_data = CONCAT(history_data,'"post_parent":"', COALESCE(OLD.post_parent,''), '"');SET history_data = CONCAT(history_data,'"guid":"', COALESCE(OLD.guid,''), '"');SET history_data = CONCAT(history_data,'"menu_order":"', COALESCE(OLD.menu_order,''), '"');SET history_data = CONCAT(history_data,'"post_type":"', COALESCE(OLD.post_type,''), '"');SET history_data = CONCAT(history_data,'"post_mime_type":"', COALESCE(OLD.post_mime_type,''), '"');SET history_data = CONCAT(history_data,'"comment_count":"', COALESCE(OLD.comment_count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(OLD.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_posts', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_posts_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_posts_a_u` AFTER UPDATE ON `carbon_wp_posts` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(NEW.ID,''), '"');SET history_data = CONCAT(history_data,'"post_author":"', COALESCE(NEW.post_author,''), '"');SET history_data = CONCAT(history_data,'"post_date":"', COALESCE(NEW.post_date,''), '"');SET history_data = CONCAT(history_data,'"post_date_gmt":"', COALESCE(NEW.post_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content":"', COALESCE(NEW.post_content,''), '"');SET history_data = CONCAT(history_data,'"post_title":"', COALESCE(NEW.post_title,''), '"');SET history_data = CONCAT(history_data,'"post_excerpt":"', COALESCE(NEW.post_excerpt,''), '"');SET history_data = CONCAT(history_data,'"post_status":"', COALESCE(NEW.post_status,''), '"');SET history_data = CONCAT(history_data,'"comment_status":"', COALESCE(NEW.comment_status,''), '"');SET history_data = CONCAT(history_data,'"ping_status":"', COALESCE(NEW.ping_status,''), '"');SET history_data = CONCAT(history_data,'"post_password":"', COALESCE(NEW.post_password,''), '"');SET history_data = CONCAT(history_data,'"post_name":"', COALESCE(NEW.post_name,''), '"');SET history_data = CONCAT(history_data,'"to_ping":"', COALESCE(NEW.to_ping,''), '"');SET history_data = CONCAT(history_data,'"pinged":"', COALESCE(NEW.pinged,''), '"');SET history_data = CONCAT(history_data,'"post_modified":"', COALESCE(NEW.post_modified,''), '"');SET history_data = CONCAT(history_data,'"post_modified_gmt":"', COALESCE(NEW.post_modified_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content_filtered":"', COALESCE(NEW.post_content_filtered,''), '"');SET history_data = CONCAT(history_data,'"post_parent":"', COALESCE(NEW.post_parent,''), '"');SET history_data = CONCAT(history_data,'"guid":"', COALESCE(NEW.guid,''), '"');SET history_data = CONCAT(history_data,'"menu_order":"', COALESCE(NEW.menu_order,''), '"');SET history_data = CONCAT(history_data,'"post_type":"', COALESCE(NEW.post_type,''), '"');SET history_data = CONCAT(history_data,'"post_mime_type":"', COALESCE(NEW.post_mime_type,''), '"');SET history_data = CONCAT(history_data,'"comment_count":"', COALESCE(NEW.comment_count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(NEW.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_posts', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_posts_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_posts_a_i` AFTER INSERT ON `carbon_wp_posts` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(NEW.ID,''), '"');SET history_data = CONCAT(history_data,'"post_author":"', COALESCE(NEW.post_author,''), '"');SET history_data = CONCAT(history_data,'"post_date":"', COALESCE(NEW.post_date,''), '"');SET history_data = CONCAT(history_data,'"post_date_gmt":"', COALESCE(NEW.post_date_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content":"', COALESCE(NEW.post_content,''), '"');SET history_data = CONCAT(history_data,'"post_title":"', COALESCE(NEW.post_title,''), '"');SET history_data = CONCAT(history_data,'"post_excerpt":"', COALESCE(NEW.post_excerpt,''), '"');SET history_data = CONCAT(history_data,'"post_status":"', COALESCE(NEW.post_status,''), '"');SET history_data = CONCAT(history_data,'"comment_status":"', COALESCE(NEW.comment_status,''), '"');SET history_data = CONCAT(history_data,'"ping_status":"', COALESCE(NEW.ping_status,''), '"');SET history_data = CONCAT(history_data,'"post_password":"', COALESCE(NEW.post_password,''), '"');SET history_data = CONCAT(history_data,'"post_name":"', COALESCE(NEW.post_name,''), '"');SET history_data = CONCAT(history_data,'"to_ping":"', COALESCE(NEW.to_ping,''), '"');SET history_data = CONCAT(history_data,'"pinged":"', COALESCE(NEW.pinged,''), '"');SET history_data = CONCAT(history_data,'"post_modified":"', COALESCE(NEW.post_modified,''), '"');SET history_data = CONCAT(history_data,'"post_modified_gmt":"', COALESCE(NEW.post_modified_gmt,''), '"');SET history_data = CONCAT(history_data,'"post_content_filtered":"', COALESCE(NEW.post_content_filtered,''), '"');SET history_data = CONCAT(history_data,'"post_parent":"', COALESCE(NEW.post_parent,''), '"');SET history_data = CONCAT(history_data,'"guid":"', COALESCE(NEW.guid,''), '"');SET history_data = CONCAT(history_data,'"menu_order":"', COALESCE(NEW.menu_order,''), '"');SET history_data = CONCAT(history_data,'"post_type":"', COALESCE(NEW.post_type,''), '"');SET history_data = CONCAT(history_data,'"post_mime_type":"', COALESCE(NEW.post_mime_type,''), '"');SET history_data = CONCAT(history_data,'"comment_count":"', COALESCE(NEW.comment_count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(NEW.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_posts', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_relationships_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_term_relationships_b_d` BEFORE DELETE ON `carbon_wp_term_relationships` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"object_id":"', COALESCE(OLD.object_id,''), '"');SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(OLD.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_order":"', COALESCE(OLD.term_order,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"object_id":"', COALESCE(OLD.object_id,''), '",');SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(OLD.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_relationships', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_relationships_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_term_relationships_a_u` AFTER UPDATE ON `carbon_wp_term_relationships` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"object_id":"', COALESCE(NEW.object_id,''), '"');SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_order":"', COALESCE(NEW.term_order,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"object_id":"', COALESCE(NEW.object_id,''), '",');SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_relationships', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_relationships_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_term_relationships_a_i` AFTER INSERT ON `carbon_wp_term_relationships` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"object_id":"', COALESCE(NEW.object_id,''), '"');SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_order":"', COALESCE(NEW.term_order,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"object_id":"', COALESCE(NEW.object_id,''), '",');SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_relationships', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_taxonomy_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_term_taxonomy_b_d` BEFORE DELETE ON `carbon_wp_term_taxonomy` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(OLD.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(OLD.term_id,''), '"');SET history_data = CONCAT(history_data,'"taxonomy":"', COALESCE(OLD.taxonomy,''), '"');SET history_data = CONCAT(history_data,'"description":"', COALESCE(OLD.description,''), '"');SET history_data = CONCAT(history_data,'"parent":"', COALESCE(OLD.parent,''), '"');SET history_data = CONCAT(history_data,'"count":"', COALESCE(OLD.count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(OLD.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_taxonomy', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_taxonomy_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_term_taxonomy_a_u` AFTER UPDATE ON `carbon_wp_term_taxonomy` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"taxonomy":"', COALESCE(NEW.taxonomy,''), '"');SET history_data = CONCAT(history_data,'"description":"', COALESCE(NEW.description,''), '"');SET history_data = CONCAT(history_data,'"parent":"', COALESCE(NEW.parent,''), '"');SET history_data = CONCAT(history_data,'"count":"', COALESCE(NEW.count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_taxonomy', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_term_taxonomy_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_term_taxonomy_a_i` AFTER INSERT ON `carbon_wp_term_taxonomy` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"taxonomy":"', COALESCE(NEW.taxonomy,''), '"');SET history_data = CONCAT(history_data,'"description":"', COALESCE(NEW.description,''), '"');SET history_data = CONCAT(history_data,'"parent":"', COALESCE(NEW.parent,''), '"');SET history_data = CONCAT(history_data,'"count":"', COALESCE(NEW.count,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_taxonomy_id":"', COALESCE(NEW.term_taxonomy_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_term_taxonomy', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_termmeta_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_termmeta_b_d` BEFORE DELETE ON `carbon_wp_termmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(OLD.term_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(OLD.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(OLD.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(OLD.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_termmeta', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_termmeta_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_termmeta_a_u` AFTER UPDATE ON `carbon_wp_termmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_termmeta', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_termmeta_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_termmeta_a_i` AFTER INSERT ON `carbon_wp_termmeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '"');SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"meta_id":"', COALESCE(NEW.meta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_termmeta', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_terms_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_terms_b_d` BEFORE DELETE ON `carbon_wp_terms` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(OLD.term_id,''), '"');SET history_data = CONCAT(history_data,'"name":"', COALESCE(OLD.name,''), '"');SET history_data = CONCAT(history_data,'"slug":"', COALESCE(OLD.slug,''), '"');SET history_data = CONCAT(history_data,'"term_group":"', COALESCE(OLD.term_group,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_id":"', COALESCE(OLD.term_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_terms', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_terms_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_terms_a_u` AFTER UPDATE ON `carbon_wp_terms` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"name":"', COALESCE(NEW.name,''), '"');SET history_data = CONCAT(history_data,'"slug":"', COALESCE(NEW.slug,''), '"');SET history_data = CONCAT(history_data,'"term_group":"', COALESCE(NEW.term_group,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_id":"', COALESCE(NEW.term_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_terms', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_terms_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_terms_a_i` AFTER INSERT ON `carbon_wp_terms` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"term_id":"', COALESCE(NEW.term_id,''), '"');SET history_data = CONCAT(history_data,'"name":"', COALESCE(NEW.name,''), '"');SET history_data = CONCAT(history_data,'"slug":"', COALESCE(NEW.slug,''), '"');SET history_data = CONCAT(history_data,'"term_group":"', COALESCE(NEW.term_group,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"term_id":"', COALESCE(NEW.term_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_terms', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_usermeta_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_usermeta_b_d` BEFORE DELETE ON `carbon_wp_usermeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"umeta_id":"', COALESCE(OLD.umeta_id,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(OLD.user_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(OLD.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(OLD.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"umeta_id":"', COALESCE(OLD.umeta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_usermeta', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_usermeta_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_usermeta_a_u` AFTER UPDATE ON `carbon_wp_usermeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"umeta_id":"', COALESCE(NEW.umeta_id,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(NEW.user_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"umeta_id":"', COALESCE(NEW.umeta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_usermeta', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_usermeta_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_usermeta_a_i` AFTER INSERT ON `carbon_wp_usermeta` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"umeta_id":"', COALESCE(NEW.umeta_id,''), '"');SET history_data = CONCAT(history_data,'"user_id":"', COALESCE(NEW.user_id,''), '"');SET history_data = CONCAT(history_data,'"meta_key":"', COALESCE(NEW.meta_key,''), '"');SET history_data = CONCAT(history_data,'"meta_value":"', COALESCE(NEW.meta_value,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"umeta_id":"', COALESCE(NEW.umeta_id,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_usermeta', history_primary_data , 'POST', history_data, history_original_query);

END;;DROP TRIGGER IF EXISTS `trigger_carbon_wp_users_b_d`;;
CREATE TRIGGER `trigger_carbon_wp_users_b_d` BEFORE DELETE ON `carbon_wp_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;


SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(OLD.ID,''), '"');SET history_data = CONCAT(history_data,'"user_login":"', COALESCE(OLD.user_login,''), '"');SET history_data = CONCAT(history_data,'"user_pass":"', COALESCE(OLD.user_pass,''), '"');SET history_data = CONCAT(history_data,'"user_nicename":"', COALESCE(OLD.user_nicename,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(OLD.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_url":"', COALESCE(OLD.user_url,''), '"');SET history_data = CONCAT(history_data,'"user_registered":"', COALESCE(OLD.user_registered,''), '"');SET history_data = CONCAT(history_data,'"user_activation_key":"', COALESCE(OLD.user_activation_key,''), '"');SET history_data = CONCAT(history_data,'"user_status":"', COALESCE(OLD.user_status,''), '"');SET history_data = CONCAT(history_data,'"display_name":"', COALESCE(OLD.display_name,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(OLD.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_users', history_primary_data , 'DELETE', history_data, history_original_query);
      -- Delete Children


END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_users_a_u`;;
CREATE TRIGGER `trigger_carbon_wp_users_a_u` AFTER UPDATE ON `carbon_wp_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(NEW.ID,''), '"');SET history_data = CONCAT(history_data,'"user_login":"', COALESCE(NEW.user_login,''), '"');SET history_data = CONCAT(history_data,'"user_pass":"', COALESCE(NEW.user_pass,''), '"');SET history_data = CONCAT(history_data,'"user_nicename":"', COALESCE(NEW.user_nicename,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(NEW.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_url":"', COALESCE(NEW.user_url,''), '"');SET history_data = CONCAT(history_data,'"user_registered":"', COALESCE(NEW.user_registered,''), '"');SET history_data = CONCAT(history_data,'"user_activation_key":"', COALESCE(NEW.user_activation_key,''), '"');SET history_data = CONCAT(history_data,'"user_status":"', COALESCE(NEW.user_status,''), '"');SET history_data = CONCAT(history_data,'"display_name":"', COALESCE(NEW.display_name,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(NEW.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_users', history_primary_data , 'PUT', history_data, history_original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_carbon_wp_users_a_i`;;
CREATE TRIGGER `trigger_carbon_wp_users_a_i` AFTER INSERT ON `carbon_wp_users` FOR EACH ROW
BEGIN

DECLARE history_data text;
DECLARE history_primary_data text;
DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

SET history_data = '{';
SET history_data = CONCAT(history_data,'"ID":"', COALESCE(NEW.ID,''), '"');SET history_data = CONCAT(history_data,'"user_login":"', COALESCE(NEW.user_login,''), '"');SET history_data = CONCAT(history_data,'"user_pass":"', COALESCE(NEW.user_pass,''), '"');SET history_data = CONCAT(history_data,'"user_nicename":"', COALESCE(NEW.user_nicename,''), '"');SET history_data = CONCAT(history_data,'"user_email":"', COALESCE(NEW.user_email,''), '"');SET history_data = CONCAT(history_data,'"user_url":"', COALESCE(NEW.user_url,''), '"');SET history_data = CONCAT(history_data,'"user_registered":"', COALESCE(NEW.user_registered,''), '"');SET history_data = CONCAT(history_data,'"user_activation_key":"', COALESCE(NEW.user_activation_key,''), '"');SET history_data = CONCAT(history_data,'"user_status":"', COALESCE(NEW.user_status,''), '"');SET history_data = CONCAT(history_data,'"display_name":"', COALESCE(NEW.display_name,''), '"');SET history_data = TRIM(TRAILING ',' FROM history_data);
SET history_data = CONCAT(history_data, '}');
      -- Insert record into audit tables
SET history_primary_data = '{';
SET history_primary_data = CONCAT(history_primary_data,'"ID":"', COALESCE(NEW.ID,''), '",');SET history_primary_data = TRIM(TRAILING ',' FROM history_primary_data);
SET history_primary_data = CONCAT(history_primary_data, '}');INSERT INTO carbon_history_logs (history_uuid, history_table, history_primary, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), 'carbon_wp_users', history_primary_data , 'POST', history_data, history_original_query);

END;;
DELIMITER ;