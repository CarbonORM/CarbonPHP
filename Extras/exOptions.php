<?php

return [
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

        'LOCATION' => (string) SERVER_ROOT . 'Data/Logs/',

        'STORE' => (bool)true,

        'SHOW' => (bool)true,

        'FULL' => (bool)true
    ],

    'VIEW' => [
        'WRAPPER' => 'Public/Wrapper.php',

        'MUSTACHE' => 'Public/Mustache/',
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

