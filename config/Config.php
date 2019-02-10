<?php
/**
 * The file is automatically generated by the code generator (CG).
 *
 *  Invalid options and code outside the optional ['SESSION']['CALLBACK']
 *  Will be removed if the file is regenerated. If you think code should live
 *  here, I suggest you add it before the start of `new CarbonPHP('pathToThisFile')`.
 *
 *  To Update/Regenerate
 *
 *     >> php index.php setup
 *
 *
 *  This will open a menu for further options.
 *
 *  Edits made to values already present in this file will be saved.
 *
 *  Available Options:
 *
 * @type $PHP = [
 *       'AUTOLOAD' => string array []                       // Provide PSR-4 namespace roots
 *       'SITE' => [
 *           'URL' => string '',                                  // Server Url name you do not need to chane in remote development
 *           'ROOT' => string '__FILE__',                         // This was defined in our ../index.php
 *           'CACHE_CONTROL' => [                                 // Key value map of $extension => $headers
 * 'png|jpg|gif|jpeg|bmp|icon|js|css' => 'Cache-Control: max-age=<seconds>',
 * 'woff|woff2|map|hbs|eotv' => 'Cache-Control: no-cache ',              // if the extension is found the headers provided will be sent
 * ],
 *           'CONFIG' => string __FILE__,                         // Send to sockets
 *           'TIMEZONE' => string 'America/Chicago',              // Current timezone TODO - look up php
 *           'TITLE' => string 'Carbon 6',                        // Website title
 *           'VERSION' => string /phpversion(),                   // Add link to semantic versioning
 *           'SEND_EMAIL' => string '',                           // I send emails to validate accounts
 *           'REPLY_EMAIL' => string '',
 *           'HTTP' => bool true
 *       ],
 *       'DATABASE' => [
 *           'DB_DSN'  => string '',                        // Host and Database get put here
 *           'DB_USER' => string '',
 *           'DB_PASS' => string '',
 *           'DB_BUILD'=> string '',                        // Absolute file path to your database set up file
 *           'REBUILD' => bool false
 *       ],
 *       'SESSION' => [
 *           'REMOTE' => bool true,             // Store the session in the SQL database
 *           'SERIALIZE' => [],                 // These global variables will be stored between session
 *           'CALLBACK' => callable,
 *       'SOCKET' => [
 *           'HOST' => string '',               // The IP or DNS server to connect ws or wss with
 *           'WEBSOCKETD' => bool false,
 *           'PORT' => int 8888,
 *           'DEV' => bool false,
 *           'SSL' => [
 *               'KEY' => string '',
 *               'CERT' => string ''
 *           ]
 *       ],
 *       'ERROR' => [
 *           'LEVEL' => (int) E_ALL | E_STRICT,
 *           'STORE' => (bool) true,                // Database if specified and / or File 'LOCATION' in your system
 *           'SHOW' => (bool) true,                 // Show errors on browser
 *           'FULL' => (bool) true                  // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
 *       ],
 *       'VIEW' => [
 *           'VIEW' => string '/',          // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
 *          'WRAPPER' => string '',         // View::content() will produce this
 *      ],
 * ]
 * @throws \Exception
 */

return [
    'DATABASE' => [

        'DB_HOST' => '127.0.0.1',                        // IP

        'DB_NAME' => 'CarbonPHP',                        // Schema

        'DB_USER' => 'root',                        // User

        'DB_PASS' => 'Huskies!99',                        // Password

        'DB_BUILD' => '',                       // This framework sets up its-self implicitly

        'REBUILD' => false                      // Initial Setup todo - remove this check
    ],

    'SITE' => [
        'URL' => 'carbonphp.com',    // Evaluated and if not the accurate redirect. Local php server okay. Remove for any domain

        'ROOT' => APP_ROOT,          // This was defined in our ../index.php

        'CACHE_CONTROL' => [
            'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',

            'jpg|jpeg|png|gif|swf|xml|txt|css|js|woff2|tff|svg' => 'Cache-Control: max-age=604800, public',

            'html|htm|php|hbs' => 'Cache-Control: max-age=0, private, public',
        ],

        'CONFIG' => __FILE__,               // Send to sockets

        'TIMEZONE' => 'America/Phoenix',    //  Current timezone

        'TITLE' => 'Root • Prerogative',    // Website title

        'VERSION' => '4.9.0',               // Add link to semantic versioning

        'SEND_EMAIL' => 'richard@miles.systems',     // I send emails to validate accounts

        'REPLY_EMAIL' => 'richard@miles.systems',

        'HTTP' => true   // I assume that HTTP is okay by default
    ],


    'SESSION' => [
        'REMOTE' => false,             // Store the session in the SQL database

        # 'SERIALIZE' => [ ],           // These global variables will be stored between session

        # 'PATH' => ''

        'CALLBACK' => function () {         // optional variable $reset which would be true if a url is passed to startApplication()

        },
    ],

    'SOCKET' => [
        'WEBSOCKETD' => false,
        'PORT' => 8888,
        'DEV' => true,
        'SSL' => [
            'KEY' => '',
            'CERT' => ''
        ]
    ],


    // ERRORS on point
    'ERROR' => [
        'LOCATION' => APP_ROOT . 'logs' . DS,

        'LEVEL' => E_ALL | E_STRICT,  // php ini level

        'STORE' => true,      // Database if specified and / or File 'LOCATION' in your system

        'SHOW' => true,       // Show errors on browser

        'FULL' => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
    ],

    'VIEW' => [
        'VIEW' => 'view/',  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc

        'WRAPPER' => 'Documentation/Wrapper.php',     // View::content() will produce this
    ],

    'MINIFY' => [
        'CSS' => [
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/dist/css/AdminLTE.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/iCheck/all.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/bootstrap-slider/slider.css',
            CARBON_ROOT .'/node_modules/admin-lte/dist/css/skins/skin-green.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/select2/dist/css/select2.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/iCheck/flat/blue.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/morris.js/morris.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/pace/pace.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/jvectormap/jquery-jvectormap.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/timepicker/bootstrap-timepicker.css',
            CARBON_ROOT .'/node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
        ],
        'JS' => [
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js',
            CARBON_ROOT .'/node_modules/jquery-pjax/jquery.pjax.js',
            CARBON_ROOT .'/view/Layout/mustache.js',
            CARBON_ROOT .'/helpers/Carbon.js',
            CARBON_ROOT .'/helpers/asynchronous.js',
            CARBON_ROOT .'/node_modules/jquery-form/src/jquery.form.js',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
            CARBON_ROOT .'/node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
            CARBON_ROOT .'/node_modules/admin-lte/dist/js/adminlte.js',
        ],
    ]
];