<?php

try {
    $db = \Carbon\Database::Database();


    echo "<html><head><body><h1>STARTING MAJOR CARBON SYSTEMS</h1>" . PHP_EOL;

    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon
(
	entity_pk VARCHAR(225) NOT NULL
		PRIMARY KEY,
	entity_fk VARCHAR(225) NULL,
	CONSTRAINT entity_entity_pk_uindex
		UNIQUE (entity_pk),
	CONSTRAINT entity_entity_entity_pk_fk
		FOREIGN KEY (entity_fk) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX entity_entity_entity_pk_fk
	ON carbon (entity_fk)
;
END;
        $db->exec($sql);
        print "<br>Table `carbon` Created";
    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_reports LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_reports` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
create table carbon_reports
(
	log_level varchar(20) null,
	report text null,
	date varchar(22) not null,
	call_trace text not null
)
;
END;
        $db->exec($sql);
        print "<br>Table `carbon_reports` Created";
    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM `user` LIMIT 1;");
        $stmt->execute();
        print "<br>Table `user` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE user
(
	user_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	user_type VARCHAR(20) NOT NULL,
	user_sport VARCHAR(20) DEFAULT 'Golf' NOT NULL COMMENT 'This is used to start the model
	',
	user_session_id VARCHAR(225) NULL,
	user_facebook_id VARCHAR(225) NULL,
	user_username VARCHAR(25) NOT NULL,
	user_first_name VARCHAR(25) NOT NULL,
	user_last_name VARCHAR(25) NOT NULL,
	user_profile_pic VARCHAR(225) NULL,
	user_profile_uri VARCHAR(225) NULL,
	user_cover_photo VARCHAR(225) NULL,
	user_birthday TEXT NULL,
	user_gender VARCHAR(25) NULL,
	user_about_me TEXT NULL,
	user_rank INT(8) DEFAULT '0' NULL,
	user_password VARCHAR(225) NULL,
	user_email VARCHAR(50) NULL,
	user_email_code VARCHAR(225) NULL,
	user_email_confirmed VARCHAR(20) DEFAULT '0' NOT NULL,
	user_generated_string VARCHAR(200) NULL,
	user_membership INT(10) DEFAULT '0' NULL,
	user_deactivated TINYINT(1) DEFAULT '0' NULL,
	user_last_login VARCHAR(14) NOT NULL,
	user_ip VARCHAR(20) NOT NULL,
	user_education_history TEXT NULL,
	user_location TEXT NULL,
	user_creation_date VARCHAR(14) NULL,
	CONSTRAINT user_user_profile_uri_uindex
		UNIQUE (user_profile_uri),
	CONSTRAINT user_entity_entity_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;  
END;
        $db->exec($sql);
        print "<br>Table `user` Created";
    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_session LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_session` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
        
create table carbon_session
(
	user_id varchar(225) not null,
	user_ip varchar(255) null,
	session_id varchar(255) not null
		primary key,
	session_expires datetime not null,
	session_data text null,
	user_online_status tinyint(1) default '1' null
)
;

END;
        $db->exec($sql);
        print "<br>Table `carbon_session` Created";
    }


    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_comments LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_comments` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon_comments
(
	parent_id VARCHAR(225) NOT NULL,
	comment_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	user_id VARCHAR(225) NOT NULL,
	comment BLOB NOT NULL,
	CONSTRAINT entity_comments_entity_parent_pk_fk
		FOREIGN KEY (parent_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT entity_comments_entity_entity_pk_fk
		FOREIGN KEY (comment_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT entity_comments_entity_user_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX entity_comments_entity_parent_pk_fk
	ON carbon_comments (parent_id)
;

CREATE INDEX entity_comments_entity_user_pk_fk
	ON carbon_comments (user_id)
;
END;
        $db->exec($sql);
        print "<br>Table `carbon_comments` Created";
    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_location LIMIT 1;");
        $stmt->execute();
        print '<br>Table `carbon_location` already exists';
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon_location
(
	entity_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	latitude VARCHAR(225) NULL,
	longitude VARCHAR(225) NULL,
	street TEXT NULL,
	city VARCHAR(40) NULL,
	state VARCHAR(10) NULL,
	elevation VARCHAR(40) NULL,
	CONSTRAINT entity_location_entity_id_uindex
		UNIQUE (entity_id),
	CONSTRAINT entity_location_entity_entity_pk_fk
		FOREIGN KEY (entity_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

END;

        $db->exec($sql);
        print "<br>Table `carbon_location` Created";
    }


    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_photos LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_photos` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon_photos
(
	parent_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	photo_id VARCHAR(225) NOT NULL,
	user_id VARCHAR(225) NOT NULL,
	photo_path VARCHAR(225) NOT NULL,
	photo_description TEXT NULL,
	CONSTRAINT entity_photos_photo_id_uindex
		UNIQUE (photo_id),
	CONSTRAINT photos_entity_entity_pk_fk
		FOREIGN KEY (parent_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT entity_photos_entity_entity_pk_fk
		FOREIGN KEY (photo_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT photos_entity_user_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX photos_entity_user_pk_fk
	ON carbon_photos (user_id)
;

END;

        $db->exec($sql);
        print "<br>Table `carbon_photos` Created";
    }



    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_tags LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_tags` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon_tags
(
	tag_id INT AUTO_INCREMENT
		PRIMARY KEY,
	tag_description TEXT NOT NULL,
	tag_name TEXT NULL,
	CONSTRAINT tag_tag_id_uindex
		UNIQUE (tag_id)
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;


END;

        $db->exec($sql);
        print "<br>Table `carbon_tags` Created";

    }


    try {
        $stmt = $db->prepare("SELECT 1 FROM carbon_tag LIMIT 1;");
        $stmt->execute();
        print "<br>Table `carbon_tag` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE carbon_tag
(
	entity_id VARCHAR(225) NOT NULL,
	user_id VARCHAR(225) NULL,
	tag_id INT NOT NULL,
	creation_date INT(20) NOT NULL,
	CONSTRAINT entity_tag_entity_entity_pk_fk
		FOREIGN KEY (entity_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT entity_tag_entity_user_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT entity_tag_tag_tag_id_fk
		FOREIGN KEY (tag_id) REFERENCES carbon_tags (tag_id)
			ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX entity_tag_entity_entity_pk_fk
	ON carbon_tag (entity_id)
;

CREATE INDEX entity_tag_entity_user_pk_fk
	ON carbon_tag (user_id)
;

CREATE INDEX entity_tag_tag_tag_id_fk
	ON carbon_tag (tag_id)
;


END;

        $db->exec($sql);

        print "<br>Table `carbon_tag` Created";
    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM user_followers LIMIT 1;");
        $stmt->execute();
        print "<br>Table `user_followers` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE user_followers
(
	follows_user_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	user_id VARCHAR(225) NOT NULL,
	CONSTRAINT followers_entity_entity_follows_pk_fk
		FOREIGN KEY (follows_user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT followers_entity_entity_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX followers_entity_entity_pk_fk
	ON user_followers (user_id)
;



END;

        $db->exec($sql);
        print "<br>Table `user_followers` Created";

    }


    try {
        $stmt = $db->prepare("SELECT 1 FROM user_messages LIMIT 1;");
        $stmt->execute();
        print "<br>Table `user_messages` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE user_messages
(
	message_id VARCHAR(225) NULL,
	to_user_id VARCHAR(225) NULL,
	message TEXT NOT NULL,
	message_read TINYINT(1) DEFAULT '0' NULL,
	CONSTRAINT messages_entity_entity_pk_fk
		FOREIGN KEY (message_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT messages_entity_user_from_pk_fk
		FOREIGN KEY (to_user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX messages_entity_entity_pk_fk
	ON user_messages (message_id)
;

CREATE INDEX messages_entity_user_from_pk_fk
	ON user_messages (to_user_id)
;


END;

        $db->exec($sql);
        print "<br>Table `user_messages` Created";

    }

    try {
        $stmt = $db->prepare("SELECT 1 FROM user_tasks LIMIT 1;");
        $stmt->execute();
        print "<br>Table `user_tasks` already exists";
    } catch (PDOException $e) {
        $sql = <<<END
CREATE TABLE user_tasks
(
	task_id VARCHAR(225) NOT NULL,
	user_id VARCHAR(225) NOT NULL COMMENT 'This is the user the task is being assigned to'
		PRIMARY KEY,
	from_id VARCHAR(225) NULL COMMENT 'Keeping this colum so forgen key will remove task if user deleted',
	task_name VARCHAR(40) NOT NULL,
	task_description VARCHAR(225) NULL,
	percent_complete INT DEFAULT '0' NULL,
	start_date DATETIME NULL,
	end_date DATETIME NULL,
	CONSTRAINT tasks_entity_entity_pk_fk
		FOREIGN KEY (task_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT user_tasks_entity_user_pk_fk
		FOREIGN KEY (user_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT user_tasks_entity_entity_pk_fk
		FOREIGN KEY (from_id) REFERENCES carbon (entity_pk)
			ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE INDEX user_tasks_entity_entity_pk_fk
	ON user_tasks (from_id)
;

CREATE INDEX user_tasks_entity_task_pk_fk
	ON user_tasks (task_id)
;

END;

        $db->exec($sql);

        print "<br>Table `user_tasks` Created";
    }

    echo "<br><br><h3>Rocking! Setup and rebuild complete.</h3>";

} catch (PDOException $e) {

    echo "Oh no, we failed to insert our databases!! Goto CarbonPHP.com for support and show the following code!<b>" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;

}

print '<h4>Creating Tags</h4>';

Try {
    $sql = <<<END
REPLACE INTO carbon_tags (tag_id, tag_description, tag_name) VALUES (?,?,?);
END;

    $tag = [
        [USER,'','USER'],
        [USER_FOLLOWERS, '', 'USER_FOLLOWERS'],
        [USER_NOTIFICATIONS, '', 'USER_NOTIFICATIONS'],
        [USER_MESSAGES, '', 'USER_MESSAGES'],
        [USER_TASKS, '', 'USER_TASKS'],
        [ENTITY_COMMENTS, '', 'ENTITY_COMMENTS'],
        [ENTITY_PHOTOS, '', 'ENTITY_PHOTOS']
    ];

    foreach ($tag as $key => $value)
        $db->prepare($sql)->execute($value);

    print '<br>Tags inserted';

} catch (PDOException $e) {
    print '<br>' . $e->getMessage();
}

print '<br><h4>Done!</h4>';

if (file_exists($file = SERVER_ROOT . 'Application/Configs/buildDatabase.php'))
    include_once $file;
else print '<h3>When you add a database be sure to add it to the file \'Application/Configs/buildDatabase.php\'</h3><h4>Use '. __FILE__ .' a as refrence.</h4>';

print '<br><br><h2>Refreshing in 6 seconds</h2><script>t1 = window.setTimeout(function(){ window.location.href = "'.SITE.'"; },6000);</script>';

return true;
