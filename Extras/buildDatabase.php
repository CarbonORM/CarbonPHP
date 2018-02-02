<?php
/* This script will safely build or rebuild you database
 * tables. You should never execute this script manually as
 * CarbonPHP will automatically rebuild itself if needed.
 *
 * If you add tables to you c
 */

print '<h1>Setting up CarbonPHP</h1>';

$db = \Carbon\Database::database();

try {
    print '<html><head><title>Setup or Rebuild Database</title></head><body><h1>STARTING MAJOR CARBON SYSTEMS</h1>' . PHP_EOL;

    try {
        $db->prepare('SELECT 1 FROM carbon LIMIT 1;')->execute();
        print '<br>Table `carbon` already exists';
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
        print '<br>Table `carbon` Created';
    }

    try {
        $db->prepare('SELECT 1 FROM carbon_reports LIMIT 1;')->execute();
        print '<br>Table `carbon_reports` already exists';
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
        print '<br>Table `carbon_reports` Created';
    }


    try {
        $db->prepare('SELECT 1 FROM sessions LIMIT 1;')->execute();
        print '<br>Table `sessions` already exists';
    } catch (PDOException $e) {
        try {
            $sql = <<<END
        
CREATE TABLE sessions
(
	user_id VARCHAR(225) NOT NULL,
	user_ip VARCHAR(255) NULL,
	session_id VARCHAR(255) NOT NULL
		PRIMARY KEY,
	session_expires DATETIME NOT NULL,
	session_data TEXT NULL,
	user_online_status TINYINT(1) DEFAULT '1' NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

END;
            $db->exec($sql);
            print '<br>Table `sessions` Created';
        } catch (\Error | Exception $e) {
            print '<pre>';
            var_dump($e);
            print '</pre>';
            exit;
        }
    }


    try {
        $db->prepare('SELECT 1 FROM carbon_tags LIMIT 1;')->execute();
        print '<br>Table `carbon_tags` already exists';
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
        print '<br>Table `carbon_tags` Created';

    }


    try {
        $stmt = $db->prepare('SELECT 1 FROM carbon_tag LIMIT 1;')->execute();
        print '<br>Table `carbon_tag` already exists';
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

        print '<br>Table `carbon_tag` Created';
    }

    print '<br><br><h3>Rocking! CarbonPHP setup and/or rebuild is complete.</h3>';

} catch (PDOException $e) {

    print 'Oh no, we failed to insert our databases!! Goto CarbonPHP.com for support and show the following code!<b>' . PHP_EOL;
    print $e->getMessage() . PHP_EOL;

}

