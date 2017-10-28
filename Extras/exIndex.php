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

define( 'DS', DIRECTORY_SEPARATOR);

define('SERVER_ROOT', dirname(__FILE__) . DS);  // Set our root folder for the application

// These files are required for the app to run. You
if (false == (include SERVER_ROOT . 'Data/vendor/autoload.php')) {       // Load the autoload() for composer dependencies located in the Services folder
    echo "Internal Server Error.";                                             // Composer autoload
    exit(3);
}

$PHP = [
    'SITE' => [
        'URL' => '',

        'ROOT' => SERVER_ROOT,

        'TIMEZONE' => 'America/Phoenix',

        'TITLE' => 'CarbonPHP',

        'VERSION' => '1.0.0',

        'SEND_EMAIL' => 'notice@example.com',

        'REPLY_EMAIL' => 'support@example.com',

        'BOOTSTRAP' => 'Application/Route.php',

        'HTTP' => (bool) true,
    ],

    'SESSION' => [
        'PATH' => (string) SERVER_ROOT . 'Data/Session/',

        'REMOTE' => (bool) false,

        'CALLBACK' => null,
    ],

    'ERROR' => [
        'LEVEL' => (int)E_ALL | E_STRICT,

        'LOCATION' => (string)'',

        'STORE' => (bool)true,

        'SHOW' => (bool)true,

        'FULL' => (bool)true
    ],

    'VIEW' => [
        'WRAPPER' => 'Public/Wrapper.php',

        'MUSTACHE' => '',
    ],

    'DATABASE' => [
        'DB_HOST' => '',

        'DB_NAME' => '',

        'DB_USER' => '',

        'DB_PASS' => '',

        'INITIAL_SETUP' => false,                       // no tables
    ],

    'AUTOLOAD' => [                                     // 'Carbon' => '',

        'View' => '/Application/View',

        'Tables' => '/Application/Services',

        'Controller' => '/Application/Controller',

        'Model' => '/Application/Model',

        'App' => '/Application'
    ]
];


Carbon\Carbon::Application($PHP)();




