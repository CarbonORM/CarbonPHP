<?php


namespace CarbonPHP\Programs;


class Wordpress
{
    /**
     * @param string $db_name
     * @param string $db_user
     * @param string $db_password
     * @param string $db_host
     * @param string $table_prefix
     * @return string
     */
    public static function configuration($db_name = 'wordpress', $db_user = 'root', $db_password = 'password', $db_host = 'localhost', $table_prefix = 'wp_'): string
    {
        return /** @lang InjectablePHP */ <<<CONFIGURATION
<?php
/**
 * wp-config.php is about the 6~7 op from the index. 
 */
 
/** Adding composer to our base setup */
const DS = DIRECTORY_SEPARATOR;

define('DIG_WP_START_TIME', microtime(true));

if (false === include __DIR__ . DS . 'vendor' . DS . 'autoload.php') {
    // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Try running <code>>> composer run rei</code></h1>';
    die(1);
}

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
const DB_NAME = '$db_name';

/** MySQL database username */
const DB_USER = '$db_user';

/** MySQL database password */
const DB_PASSWORD = '$db_password';

/** MySQL hostname */
const DB_HOST = '$db_host';

/** Database Charset to use in creating database tables. */
const DB_CHARSET = 'utf8';

/** The Database Collate type. Don't change this if in doubt. */
const DB_COLLATE = '';

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         't)6G:QFhuKq9:8(n/cs:A4ksBdJoa%Y}9Fv-Wx;r>i/@Zaf.N.tZi2f+$&?w*Qa9');
define('SECURE_AUTH_KEY',  '+PYzzq,g{KwK7|WD3P4R,M|Ty`OC38w|u_|$%c!H?;8YyF*;+HP!gio~~kn=:=+j');
define('LOGGED_IN_KEY',    'wnM8|,89nRQ>H&w4UXe5V9|Ik7(TC3-BpB/5Rn(:jBWT3[w&WE+Ci5C2WGbpRlP&');
define('NONCE_KEY',        'SQ4u`sG~ZUhe-lh0}+(cyj;c{Y0*qq|r`,?E#qS-Ma;`Bw @k!8.;1+YY9i_nz,>');
define('AUTH_SALT',        '%gQ+wn+Re|-C;2THG1V(e5]_8zMczF:YEt>=!O-g/8Ep<5&e,{1`70g@oyUby:qw');
define('SECURE_AUTH_SALT', '8|-~i!#C|&>ujeo9#Jl-pH],akD--DqQ=(+kM1c7I(kMO|urL*gBVm)>L24a*a9%');
define('LOGGED_IN_SALT',   'WT)N3GUV+@-B Pi|DUDMqLG$^>,$:Pr,O/w[IDks-t6W.vjDUgyP0N-O+xJpz/*1');
define('NONCE_SALT',       'af,^OD2!./Ml{V*:<?+{!6Ac/};um4Q@0fmjDb;WqWLVQeKgT<b&#,&~SP*[^|-{');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
\$table_prefix = '$table_prefix';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';



CONFIGURATION;

    }


    /** @noinspection PhpIncludeInspection */
    public static function addComposerToWordpress(string $original = '') : string {
        $return = /** @lang InjectablePHP */ <<<PHP
<?php

const DS = DIRECTORY_SEPARATOR;

if (false === include __DIR__ . DS . 'vendor' . DS . 'autoload.php') {
    // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Try running <code>>> composer run rei</code></h1>' and die;
    // Composer autoload
}

PHP;

        if ($original !== '') {
            $original = preg_replace('/^.+\n/', '', $original);
        }

        return $return . $original;

    }

}