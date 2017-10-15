<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 10/12/17
 * Time: 5:38 PM
 */


try {
    $db = \Carbon\Database::getConnection();


    echo "STARTING MAJOR CARBON SYSTEMS" . PHP_EOL;


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

    $sql = <<<END
CREATE TABLE user
(
	user_id VARCHAR(225) NOT NULL
		PRIMARY KEY,
	user_type VARCHAR(20) NOT NULL,
	user_sport VARCHAR(20) DEFAULT 'Golf' NOT NULL COMMENT 'This is used to start the model
	',
	user_session_id VARCHAR(225) NULL,
	user_facebook_id INT NULL,
	user_username VARCHAR(25) NOT NULL,
	user_first_name VARCHAR(25) NOT NULL,
	user_last_name VARCHAR(25) NOT NULL,
	user_profile_pic VARCHAR(225) NULL,
	user_profile_uri VARCHAR(225) NULL,
	user_cover_photo VARCHAR(225) DEFAULT 'Data/Uploads/Pictures/default_cover.png' NULL,
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

    $sql = <<<END
create table carbon_session
(
	user_id varchar(225) not null
		primary key,
	user_ip varchar(255) null,
	session_id varchar(255) not null,
	session_expires datetime not null,
	session_data text null,
	user_online_status tinyint(1) default '1' null,
	constraint user_session_user_user_id_fk
		foreign key (user_id) references user (user_id)
			on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

END;

    $db->exec($sql);

    echo 'USER ENTITY SYSTEM COMPLETE' . PHP_EOL;
    echo 'INI COMMENTS' . PHP_EOL;

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

    echo 'DONE....... INI LOCATION' . PHP_EOL;

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

    echo 'DONE....... INI PHOTOS' . PHP_EOL;

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

    echo "\n DONE....... INI TAG SYSTEMS" . PHP_EOL;

    $sql = <<<END
create table carbon_tags
(
	tag_id int auto_increment
		primary key,
	tag_description text not null,
	tag_name text null,
	constraint tag_tag_id_uindex
		unique (tag_id)
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;


END;

    $db->exec($sql);

    $sql = <<<END
create table carbon_tag
(
	entity_id varchar(225) not null,
	user_id varchar(225) null,
	tag_id int not null,
	creation_date int(20) not null,
	constraint entity_tag_entity_entity_pk_fk
		foreign key (entity_id) references carbon (entity_pk)
			on update cascade on delete cascade,
	constraint entity_tag_entity_user_pk_fk
		foreign key (user_id) references carbon (entity_pk)
			on update cascade on delete set null,
	constraint entity_tag_tag_tag_id_fk
		foreign key (tag_id) references carbon_tags (tag_id)
			on update cascade on delete cascade
)  ENGINE=InnoDB DEFAULT CHARSET=latin1
;

create index entity_tag_entity_entity_pk_fk
	on carbon_tag (entity_id)
;

create index entity_tag_entity_user_pk_fk
	on carbon_tag (user_id)
;

create index entity_tag_tag_tag_id_fk
	on carbon_tag (tag_id)
;


END;

    $db->exec($sql);

    echo "W00T!" . PHP_EOL;

    echo "Rocking!" . PHP_EOL;

} catch (PDOException $e) {

    echo "Oh no!! Goto CarbonPHP.com for support! ( code this code )" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;

}

if (file_exists($file = SERVER_ROOT . 'Application/Configs/buildDatabase.php')) include_once $file;

exit(1);
