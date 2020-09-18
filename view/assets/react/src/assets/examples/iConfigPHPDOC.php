@return array = [
'AUTOLOAD' => string array []                            // Provide PSR-4 namespace roots
'SITE' => [
    'URL' => string '',                                  // Server Url name you do not need to chane in remote development
    'ROOT' => string '__FILE__',                         // This was defined in our ../index.php

    'CACHE_CONTROL' => [                                 // Key value map of $extension => $headers
         'png|jpg|gif|jpeg|bmp|icon|js|css' => 'Cache-Control: max-age=<seconds>',
         'woff|woff2|map|hbs|eotv' => 'Cache-Control: no-cache ',              // if the extension is found the headers provided will be sent
    ],

    'CONFIG' => string __FILE__,                         // Send to sockets
    'TIMEZONE' => string 'America/Chicago',              // Current timezone TODO - look up php
    'TITLE' => string 'Carbon 6',                        // Website title
    'VERSION' => string /phpversion(),                   // Add link to semantic versioning
    'SEND_EMAIL' => string '',                           // I send emails to validate accounts
    'REPLY_EMAIL' => string '',
    'BOOTSTRAP' => string '',                            // This file is executed when the startApplication() function is called
    'HTTP' => bool true
],
'DATABASE' => [
    'DB_DSN'  => string '',                        // Host and Database get put here
    'DB_USER' => string '',
    'DB_PASS' => string '',
    'DB_BUILD'=> string '',                        // Absolute file path to your database set up file
    'REBUILD' => bool false
],
'SESSION' => [
    'REMOTE' => bool true,             // Store the session in the SQL database
    'SERIALIZE' => [],                 // These global variables will be stored between session
    'CALLBACK' => callable,
],
'SOCKET' => [
    'HOST' => string '',               // The IP or DNS server to connect ws or wss with
    'WEBSOCKETD' => bool false,        // Todo - remove websockets
    'PORT' => int 8888,
    'DEV' => bool false,
    'SSL' => [
        'KEY' => string '',
        'CERT' => string ''
    ]
],
'ERROR' => [
    'LEVEL' => (int) E_ALL | E_STRICT,
    'STORE' => (bool) true,                // Database if specified and / or File 'LOCATION' in your system
    'SHOW' => (bool) true,                 // Show errors on browser
    'FULL' => (bool) true                  // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
],
'VIEW' => [
    'VIEW' => string '/',          // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
    'WRAPPER' => string '',         // View::content() will produce this
],
];
