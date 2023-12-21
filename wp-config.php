<?php /** @noinspection DuplicatedCode */
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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Documentation;

// Composer autoload
if (false === (include __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {

    print '<h1>Composer Failed. Please run <b>composer install</b>.</h1>';

    die(1);

}

ColorCode::colorCode("Loading wordpress configuration for CarbonPHP documentation.");

const DS = DIRECTORY_SEPARATOR;


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {


    define( 'ABSPATH', __DIR__ . DS );

}

CarbonPHP::$app_root = ABSPATH;

$configuration = Documentation::configuration();

CarbonPHP::make($configuration);

$dbInfo = $configuration[CarbonPHP::DATABASE];

define( 'DB_NAME', $dbInfo[CarbonPHP::DB_NAME] );

/** MySQL database username */
define( 'DB_USER', $dbInfo[CarbonPHP::DB_USER] );

/** MySQL database password */
define( 'DB_PASSWORD', $dbInfo[CarbonPHP::DB_PASS] );

/** MySQL hostname */
define( 'DB_HOST', $dbInfo[CarbonPHP::DB_HOST] );

/** Database Charset to use in creating database tables. */
const DB_CHARSET = 'utf8mb4';

/** The Database Collate type. Don't change this if in doubt. */
const DB_COLLATE = '';

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
const AUTH_KEY = 'put your unique phrase here';
const SECURE_AUTH_KEY = 'put your unique phrase here';
const LOGGED_IN_KEY = 'put your unique phrase here';
const NONCE_KEY = 'put your unique phrase here';
const AUTH_SALT = 'put your unique phrase here';
const SECURE_AUTH_SALT = 'put your unique phrase here';
const LOGGED_IN_SALT = 'put your unique phrase here';
const NONCE_SALT = 'put your unique phrase here';

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'carbon_wp_';

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
const WP_DEBUG = false;

/* That's all, stop editing! Happy publishing. */



/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';