<?php
return [
    'SITE' => [
        'URL' => '',                           // Server Url name you do not need to chane in remote development
        'ROOT' => SERVER_ROOT,                    // This was defined in our ../index.php
        'ALLOWED_EXTENSIONS' => 'jpg|png',     // File ending in these extensions will be served, may be override from .htaccess
        'CONFIG' => __FILE__,                  // Send to sockets
        'TIMEZONE' => 'America/Chicago',       // Current timezone TODO - look up php
        'TITLE' => 'Carbon 6',                 // Website title
        'VERSION' => phpversion(),             // Add link to semantic versioning
        'SEND_EMAIL' => '',                    // I send emails to validate accounts
        'REPLY_EMAIL' => '',
        'BOOTSTRAP' => '',                     // This file is executed when the startApplication() function is called
        'HTTP' => true
    ],
    'AUTOLOAD' => [                                   // Provide PSR-4 namespace roots

        // Composer auto-loads the following for us, but for reference...

        'View' => SERVER_ROOT . 'Application/View',

        'Table' => SERVER_ROOT . 'Application/Table',

        'Controller' => SERVER_ROOT . 'Application/Controller',

        'Model' => SERVER_ROOT . 'Application/Model',

        'App' => SERVER_ROOT . 'Application'

        // So comment out the above if moving to production

    ],
    'DATABASE' => [
        'DB_DSN' => '',                        // Host and Database get put here
        'DB_USER' => '',
        'DB_PASS' => '',
        'DB_BUILD' => '',                      // Absolute file path to your database set up file
        'REBUILD' => false
    ],
    'SESSION' => [
        'REMOTE' => true,                // Store the session in the SQL database
        'SERIALIZE' => [],               // These global variables will be stored between session
        'CALLBACK' => function () {      // a callable that is executed when StartAppliction() runs
        }
    ],
    'SOCKET' => [
        'WEBSOCKETD' => false,           // Todo - remove websocketd
        'PORT' => 8888,
        'DEV' => false,
        'SSL' => [
            'KEY' => '',
            'CERT' => ''
        ],
    ],

    'ERROR' => [
        'LEVEL' => (int)E_ALL | E_STRICT,
        'STORE' => (bool)true,                // Database if specified and / or File 'LOCATION' in your system
        'SHOW' => (bool)true,                 // Show errors on browser
        'FULL' => (bool)true                  // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
    ],
    'VIEW' => [
        'VIEW' => '/',           // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
        'WRAPPER' => '',         // View::content() will produce this
    ]

];
