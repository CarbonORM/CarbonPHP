<?php



return [
    'DATABASE' => [

        'DB_DSN' =>  APP_LOCAL ? 'mysql:host=127.0.0.1;dbname=CarbonPHP;' : 'mysql:host=35.231.27.152;dbname=CarbonPHP;',      // Host and Database get put here

        'DB_USER' => 'root',                 // User

        'DB_PASS' => APP_LOCAL ? 'Huskies!99' : 'goldteamrules',          // Password

        'DB_BUILD' => '',                       // This framework sets up its-self implicitly

        'REBUILD' => false                       // Initial Setup todo - remove this check
    ],

    'SITE' => [
        'URL' => 'example.com',    // Evaluated and if not the accurate redirect. Local php server okay. Remove for any domain

        'ROOT' => APP_ROOT,     // This was defined in our ../index.php

        'ALLOWED_EXTENSIONS' => 'png|jpg|gif|jpeg|bmp|icon|js|css|woff|woff2|map|hbs|eotv',     // File ending in these extensions will be served

        'CONFIG' => __FILE__,      // Send to sockets

        'TIMEZONE' => 'America/Phoenix',    //  Current timezone

        'TITLE' => 'Root • Prerogative',      // Website title

        'VERSION' => '0.0.0',       // Add link to semantic versioning

        'SEND_EMAIL' => 'no-reply@carbonphp.com',     // I send emails to validate accounts

        'REPLY_EMAIL' => 'support@carbonphp.com',

        'HTTP' => true   // I assume that HTTP is okay by default
    ],

    'SESSION' => [
        'REMOTE' => true,             // Store the session in the SQL database

        'SERIALIZE' => [ 'user' ],           // These global variables will be stored between session

        'CALLBACK' => function () {         // optional variable $reset which would be true if a url is passed to startApplication()

        },
    ],

    /*          TODO - finish building php websockets
    'SOCKET' => [
        'WEBSOCKETD' => false,  // if you'd like to use web
        'PORT' => 8888,
        'DEV' => true,
        'SSL' => [
            'KEY' => '',
            'CERT' => ''
        ]
    ],  */


    // ERRORS on point
    'ERROR' => [
        'LEVEL' =>  E_ALL | E_STRICT,  // php ini level

        'STORE' =>  true,      // Database if specified and / or File 'LOCATION' in your system

        'SHOW' =>  true,       // Show errors on browser

        'FULL' => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
    ],

    'VIEW' => [
        'VIEW' => 'view/',  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc

        'WRAPPER' => 'Documentation/Wrapper.php',     // View::content() will produce this
    ],

];