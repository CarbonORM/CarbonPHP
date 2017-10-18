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
if (false == (include 'Structure/Carbon.php')) {       // Load the autoload() for composer dependencies located in the Services folder
    echo "Internal Server Error";                                             // Composer autoload
    exit(3);
}

$PHP = [
    'GENERAL' => [
        'URL' => 'stats.coach',

        'ROOT' => SERVER_ROOT,

        'TIMEZONE' => 'America/Phoenix',

        'SITE_TITLE' => 'Stats Coach',

        'SITE_VERSION' => '1.0.8',

        'SYSTEM_EMAIL' => 'Support@Stats.Coach',

        'REPLY_EMAIL' => 'RichardMiles2@my.unt.edu',

        'ALLOW_EXTENSION' => (bool)false,

        'USER_SUPPORT' => (bool)true                           // currently unsupported
    ],

    'ROUTES' => 'Application/Route.php',

    'SERIALIZE' => [],

    'SESSION' => [],

    'REPORTING' => [

        'LEVEL' => (int)E_ALL | E_STRICT,

        'LOCATION' => (string)"/",

        'STORE' => (bool)true,

        'PRINT' => (bool)true,

        'FULL' => (bool)true
    ],

    'CALLABLE' => [
        'WRAPPING_REQUIRES_LOGIN' => false,

        'RESTART_CALLBACK' => null

    ],

    'DIRECTORY' => [
        'MUSTACHE' => '',

        'CONTENT' => 'Public/',

        'CONTENT_WRAPPER' => SERVER_ROOT . 'Public/AdminLTE.php',

        'VENDOR' => 'Data/vendor/',

    ],

    'VIEW' => [
        'MINIFY_CONTENTS' => (bool)false,

        'SOCKET' => (bool)false,      // [ 'port' => 8080, ]

        'HTTP' => (bool)true,

        'HTTPS' => (bool)true,
    ],

    'DATABASE' => false,                                // Mixed (bool|array)

    /* [
        'DB_HOST' => '',

        'DB_NAME' => '',

        'DB_USER' => '',

        'DB_PASS' => '',

        'INITIAL_SETUP' => false,                       // no tables
    ],
    */

    'AUTOLOAD' => [                                     // 'Carbon' => '',
        'View' => '/Application/View',

        'Tables' => '/Application/Services',

        'Controller' => '/Application/Controller',

        'Model' => '/Application/Model',

        'App' => '/Application'
    ]

];



Carbon\Carbon::Application($PHP);




