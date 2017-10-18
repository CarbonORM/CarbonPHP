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

// These files are required for the app to run. You
if (false == (include 'Structure/Carbon.php')) {       // Load the autoload() for composer dependencies located in the Services folder
    echo "Internal Server Error";                                             // Composer autoload
    exit(3);
}

$PHP = [
    'SITE' => [
        'URL' => 'stats.coach',

        'ROOT' => dirname(__FILE__) . DS,

        'TIMEZONE' => 'America/Phoenix',

        'TITLE' => 'Example',

        'VERSION' => '1.0.8',

        'SEND_EMAIL' => 'Support@Stats.Coach',

        'REPLY_EMAIL' => 'RichardMiles2@my.unt.edu',

        'BOOTSTRAP' => 'Application/Route.php',

        'HTTP' => (bool)true,
    ],

    'SERIALIZE' => [],

    'SESSION' => [
        'PATH' => (string) '',

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


    'SOCKET' => [
        'TYPE' => 'WEBSOCKETD',
        'port' => 8080,
        'KEY' => '',
        'CERT' => ''
    ],


    'VIEW' => [
        'WRAPPER' => 'Public/AdminLTE.php',

        'MINIFY' => (bool)false,

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

Carbon\Carbon::Application($PHP);




