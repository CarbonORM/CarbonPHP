<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 9/3/17
 * Time: 11:16 PM
 *
 * Let it be known the basic commands of IntelliJ
 *
 * Jump to function definition:     (Command + click)
 *
 */


const DS = DIRECTORY_SEPARATOR;

define('SERVER_ROOT', dirname(__FILE__) . DS);  // Set our root folder for the application

// These files are required for the app to run. You
if (false == (include 'vendor/autoload.php')) {       // Load the autoload() for composer dependencies located in the Services folder
    echo "Internal Server Error";                                             // Composer autoload
    exit(3);
}

use Carbon\CarbonPHP;

CarbonPHP::Application([

    'GENERAL' => [
        'URL' => 'stats.coach',

        'ROOT' => SERVER_ROOT,

        'TIMEZONE' => 'America/Phoenix',

        'SITE_TITLE' => 'Stats Coach',

        'SITE_VERSION' => 'Beta 1',

        'SYSTEM_EMAIL' => 'Support@Stats.Coach',

        'REPLY_EMAIL' => 'RichardMiles2@my.unt.edu',

        'ALLOW_EXTENSION' => false,

        'USER_SUPPORT' => true
    ],

    'ROUTES' => 'Application/Routes.php',

    'SERIALIZE' => [],

    'SESSION' => [
        'SAVE_PATH' => SERVER_ROOT . 'Data/Sessions',
        'STORE_REMOTE' => true
    ],

    'REPORTING' => [
        'LEVEL' => E_ALL | E_STRICT,

        'LOCATION' => 'Data/Logs/Error.txt',

        'STORE' => true,

        'PRINT' => true,

        'FULL' => true
    ],

    'CALLABLE' => [
        'WRAPPING_REQUIRES_LOGIN' => false,
    ],

    'DIRECTORY' => [
        'MUSTACHE' => '',

        'CONTENT' => 'Public/',

        'CONTENT_WRAPPER' => 'Public/StatsCoach.php',

        'TEMPLATE' => 'Data/vendor/almasaeed2010/adminlte/',

        'VENDOR' => 'Data/vendor/',

    ],

    'VIEW' => [
        'MINIFY_CONTENTS' => false,

        'SOCKET' => false,      // [ 'port' => 8080, ]

        'HTTP' => true,

        'HTTPS' => true,
    ],

    'DATABASE' => [
        'DB_HOST' => '127.0.0.1',

        'DB_NAME' => 'StatsCoach',

        'DB_USER' => 'root',

        'DB_PASS' => 'Huskies!99',

        'INITIAL_SETUP' => false,                       // no tables
    ],

    'AUTOLOAD' => [
        'View' => '/Application/View',

        'Tables' => '/Application/Services',

        'Carbon' => '/Application/CarbonPHP',

        'Controller' => '/Application/Controller',

        'Model' => '/Application/Model',

        'App' => '/Application'
    ]

])();




