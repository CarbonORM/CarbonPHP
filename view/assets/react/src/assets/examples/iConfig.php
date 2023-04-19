<?php

namespace Config;

use CarbonPHP\Interfaces\iConfig;

class Config implements iConfig
{
    public static function configuration(): array
    {
        return [
            'DATABASE' => [

                'DB_HOST' => APP_LOCAL ? '127.0.0.1' : '33.30.229.250',      // Host and Database get put here # APP_LOCAL ? '127.0.0.1' :

                'DB_NAME' => 'StatsCoach',

                'DB_PORT' => 3306,

                'DB_USER' => 'root',                                          // User

                'DB_PASS' =>  APP_LOCAL ? 'password' : 'goldteamrules',       // Password goldteamrules # APP_LOCAL ? 'password' :

                'DB_BUILD' => APP_ROOT . '/config/buildDatabase.php',

                'REBUILD' => false                                            // Initial Setup
            ],

            'SITE' => [
                'URL' => 'stats.coach',    // Evaluated and if not the accurate redirect. Local php server okay. Remove for any domain

                'ROOT' => APP_ROOT,     // This was defined in our ../index.php

                'CACHE_CONTROL' => [
                    'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                    'jpg|jpeg|png|gif|swf|xml|txt|woff2|tff' => 'Cache-Control: max-age=604800, public',
                    'html|htm|php|hbs|css|js' => 'Cache-Control: max-age=0, private, public',
                ],

                'CONFIG' => __FILE__,      // Send to sockets

                'TIMEZONE' => 'America/Phoenix',    //  Current timezone TODO - look up php for default

                'TITLE' => 'Stats • Coach',     // Website title

                'VERSION' => '0.0.0',       // Add link to semantic versioning

                'SEND_EMAIL' => 'no-reply@carbonphp.com',     // I send emails to validate accounts

                'REPLY_EMAIL' => 'support@carbonphp.com',

                'HTTP' => 1 // todo - APP_LOCAL   // I assume that HTTP is okay by default
            ],

            'SESSION' => [

                'REMOTE' => true,                                    // Store the session in the SQL database

                'SERIALIZE' => [

                ],           // These global variables will be stored between session

                'CALLBACK' => static function () {
                    // optional variable $reset which would be true if a url is passed to startApplication()
                    if ($_SESSION['id'] ?? false) {
                        getUser($_SESSION['id']);
                    }
                },
            ],

            /** TODO - finish building php websockets
             * https://certbot.eff.org/docs/using.html#where-are-my-certificates
             */
            'SOCKET' => [
                'WEBSOCKETD' => true,  // if you'd like to use web
                'PORT' => 8888,
                'DEV' => true,
                APP_LOCAL ? 0 : 'SSL' => [                          // setting to 0 to null the field
                    'KEY' => '/var/www/stats.coach/privkey.pem',
                    'CERT' => '/var/www/stats.coach/cert.pem'
                ]
            ],

            // ERRORS on point
            'ERROR' => [
                'LOCATION' => APP_ROOT . 'data' . DS . 'logs' . DS,

                'LEVEL' => E_ALL | E_STRICT,  // php ini level

                'STORE' => false,     // Database if specified and / or File 'LOCATION' in your system

                'SHOW' => true,       // Show errors on browser

                'FULL' => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
            ],

            'VIEW' => [
                'VIEW' => 'view/',                     // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
                'WRAPPER' => 'layout/Wrapper.hbs',     // View::content() will produce this
            ],

            'MINIFY' => [
                'CSS' => [
                    'OUT' => APP_ROOT . 'view/css/style.css',
                    APP_ROOT . 'view/css/stats.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/AdminLTE.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/iCheck/all.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/bootstrap-slider/slider.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/skins/skin-green.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/select2/dist/css/select2.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/iCheck/flat/blue.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/morris.js/morris.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/pace/pace.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jvectormap/jquery-jvectormap.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/timepicker/bootstrap-timepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
                ],
                'JS' => [
                    'OUT' => APP_ROOT . 'view/js/javascript.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js',
                    APP_ROOT . 'node_modules/jquery-pjax/jquery.pjax.js',
                    APP_ROOT . 'vendor/richardtmiles/carbonphp/view/mustache/Layout/mustache.js',
                    CARBON_ROOT . 'helpers/Carbon.js',
                    CARBON_ROOT . 'helpers/asynchronous.js',
                    APP_ROOT . 'node_modules/jquery-form/src/jquery.form.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/moment/moment.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    APP_ROOT . 'node_modules/admin-lte/dist/js/adminlte.js',

                ],
            ]
        ];
    }
}

