<?php

namespace Config;


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Request;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Carbon_Users;
use CarbonPHP\View;

class Config extends Application implements iConfig
{
    // these are all relative to the /view/ directory
    private const REACT = 'react/material-dashboard-react-c6/build/index.html';

    private array $adminLTE = [
        'Home' => 'mustache/Documentation/Home.hbs',
        'CarbonPHP' => 'mustache/Documentation/Introduction.hbs',
        'Installation' => 'mustache/Documentation/Installation.hbs',
        'Implementations' => 'mustache/Documentation/Implementations.hbs',
        'Dependencies' => 'mustache/Documentation/Dependencies.hbs',
        'FileStructure' => 'mustache/Documentation/QuickStart/FileStructure.hbs',
        'Environment' => 'mustache/Documentation/QuickStart/Environment.hbs',
        'Options' => 'mustache/Documentation/QuickStart/Options.hbs',
        'Bootstrap' => 'mustache/Documentation/QuickStart/Bootstrap.hbs',
        'Wrapper' => 'mustache/Documentation/QuickStart/Wrapper.hbs',
        'Parallel' => 'mustache/Documentation/QuickStart/ParallelProcessing.hbs',
        'Overview' => 'mustache/Documentation/PHP/Overview.hbs',
        'Entities' => 'mustache/Documentation/PHP/Entities.hbs',
        'Request' => 'mustache/Documentation/PHP/Request.hbs',
        'Route' => 'mustache/Documentation/PHP/Route.hbs',
        'Server' => 'mustache/Documentation/PHP/Server.hbs',
        'Session' => 'mustache/Documentation/PHP/Session.hbs',
        'Singleton' => 'mustache/Documentation/PHP/Singleton.hbs',
        'View' => 'mustache/Documentation/PHP/View.hbs',
        'OSSupport' => 'mustache/Documentation/PlatformSupport.hbs',
        'UpgradeGuide' => 'mustache/Documentation/PlatformSupport.hbs',
        'Support' => 'mustache/Documentation/Support.hbs',
        'License' => 'mustache/Documentation/License.hbs',
        'AdminLTE' => 'mustache/Documentation/AdminLTE.hbs',
        'N00B' => 'mustache/Documentation/N00B.hbs'
    ];


    /**
     * @return mixed|void
     * @throws PublicAlert
     */
    public function defaultRoute()  // Sockets will not execute this
    {
        self::getUser();

        View::$forceWrapper = true; // this will hard refresh the wrapper

        $this->fullPage()(self::REACT);
    }


    /**
     * todo - document well what can go into constructor and when.
     * @param null $structure
     * way this will throw an error is if you do
     * not define a url using sockets.
     * @throws PublicAlert
     */
    public function __construct($structure = null)
    {
        if (CarbonPHP::$safelyExit) {
            return;
        }

        global $json;

        if (!is_array($json)) {
            $json = array();
        }
        $json['SITE'] = SITE;
        $json['POST'] = $_POST;
        $json['HTTP'] = HTTP ? 'True' : 'False';
        $json['HTTPS'] = HTTPS ? 'True' : 'False';
        $json['SOCKET'] = SOCKET ? 'True' : 'False';
        $json['AJAX'] = AJAX ? 'True' : 'False';
        $json['PJAX'] = PJAX ? 'True' : 'False';
        $json['SITE_TITLE'] = SITE_TITLE;
        $json['APP_VIEW'] = APP_VIEW;
        $json['TEMPLATE'] = TEMPLATE_ROOT;
        $json['COMPOSER'] = COMPOSER_ROOT;
        $json['X_PJAX_Version'] = &$_SESSION['X_PJAX_Version'];
        $json['FACEBOOK_APP_ID'] = '';

        parent::__construct($structure);
    }


    /**  NOTE:
     *  This is actually overriding the CM function in Carbon/Application.
     *  I do this because the namespace changes for other applications not
     *  in C6 context.
     *
     *  Stands for Controller -> Model
     *
     * This will run the controller/$class.$method().
     * If the method returns !empty() the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model.
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @TODO - I remember once using the return by reference to allow the changing of model from the controller. We now just use the return value of startApplication ie. false
     *       - while I think this is a good use case, I think the obfuscation that this logic holds isn't true to the MVC arch thus shouldn't be done. I think with this in mind
     *       - removing the & would same time in every route.... and I do not see a valid use case for it now (logically could it be used). I think we can remove with a minor version bump
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed the returned value from model/$class.$method() or false | void
     * @noinspection DuplicatedCode                    - intellij needing help (avoiding unnecessary abstraction)
     * @noinspection UnknownInspectionInspection       - intellij not helping intellij
     */
    public static function CM(string $class, string &$method, array &$argv = []): callable
    {
        $class = ucfirst(strtolower($class));   // Prevent malformed class names
        $controller = "CarbonPHP\\Controller\\$class";     // add namespace for autoloader
        $model = "CarbonPHP\\Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists
        if (!class_exists($controller)) {
            print "Invalid Controller ($controller) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        if (!class_exists($model)) {
            print "Invalid Model ($model) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        // the array $argv will be passed as arguments to the method requested, see link above
        $exec = static function &(string $class, array &$argv) use ($method) {
            $argv = call_user_func_array([new $class, $method], $argv);
            return $argv;
        };

        return static function () use ($exec, $controller, $model, &$argv) {
            // execute controller
            $argv = $exec($controller, $argv);

            if (!empty($argv)) {                            // continue to the model?
                if (is_array($argv)) {
                    return $exec($model, $argv);        // array passed
                }
                $controller = [&$argv];                     // single return, allow return by reference TODO - is this still a thing? (not imperative
                return $exec($model, $controller);
            }

            return $argv;
        };
    }


    /** TODO - we dont use this return value for anything
     * @param string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        global $json;

        $json['APP_LOCAL'] = APP_LOCAL;

        defined('TEMPLATE') or define('TEMPLATE', 'node_modules/admin-lte/');

        self::getUser();

        if (Rest::MatchRestfulRequests($this)()) {
            return true;
        }

        if (APP_LOCAL && $this->regexMatch('#color#', static function () {
            global $json;

            if (array_key_exists('code', $_POST)) {
                if (!is_string($_POST['code'])) {
                    throw new PublicAlert('You must submit a string. This could be code or a file path.');
                }
                $json['colorCode'] = highlight($_POST['code'], false);
            } else {
                $json['colorCode'] = '';
            }
            View::$wrapper = APP_ROOT . APP_VIEW . 'mustache/AdminLTE/wrapper.hbs';
            return View::content(APP_VIEW . 'color'. DS .'color.php', APP_ROOT);
        })()) {
            return true;
        }

        $this->structure($this->wrap());

        ###################################### AdminLTE DOC
        if ($this->regexMatch('#2.0/UIElements/([A-Za-z]+)#',
            function ($AdminLTE) {
                $AdminLTE = (new Request())->set($AdminLTE)->word();  // must be validated

                if (!$AdminLTE) {
                    View::$forceWrapper = true;
                    $AdminLTE = 'widgets';
                }

                if (file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')) {

                    // this needs to be in this if block
                    View::$wrapper = APP_ROOT . APP_VIEW . 'mustache/AdminLTE/wrapper.hbs';

                    $this->wrap()(APP_VIEW . $path);    // still relative to APP_ROOT
                }
            })()) {
            return true;
        }

        if ($version = ($this->uriExplode[0] ?? false)) {
            switch ($version) {
                case '2.0':
                    $this->matched = true;
                    $json['VersionTWO'] = true;
                    $page = $this->uriExplode[1] ?? false;

                    $page or View::$forceWrapper = true;

                    if ($page && array_key_exists($page, $this->adminLTE)) {
                        $this->wrap()($this->adminLTE[$page]);
                    } else {
                        $this->wrap()($this->adminLTE['Home']);
                    }
                    return true;
                case '5.0':
                    $this->fullPage()('react/material-dashboard-react-c6/build/index.html');
                    return true;
                default:
                    break;
            }
        }

        if ($this->regexMatch('#carbon/authenticated#', static function () {
            global $json;

            header('Content-Type: application/json', true, 200); // Send as JSON

            #PublicAlert::JsonAlert('Test', 'Danger', 'success', 'success');

            $json['success'] = !true;

            print json_encode($json, JSON_THROW_ON_ERROR, 512);

            return true;
        })()) {
            return true;
        }

        $this->structure($this->wrap());

        return false;
    }

    /**
     * @param string $id
     * @throws PublicAlert
     */
    public static function getUser(string $id = ''): void
    {
        global $users;

        if (!is_array($users)) {
            $users = [];
        }

        if ($id === '') {
            $id = session_id();
        }

        $me = [];
        if (!Carbon_Users::Get($me, null, [
            REST::WHERE => [
                [
                    Carbon_Users::USER_USERNAME => $id,
                    Carbon_Users::USER_ID => $id
                ]
            ],
            REST::PAGINATION => [
                REST::LIMIT => 1
            ]
        ])) {
            PublicAlert::warning('Failed to find the user. Some demos may not function as expected.');
            return;
        }

        if (!empty($me)) {
            PublicAlert::success('Welcome back. ' . $id);
        } else if (is_string($id = Carbon_Users::Post([         // required fields :P
                Carbon_Users::USER_USERNAME => $id,
                Carbon_Users::USER_PASSWORD => $id,
                Carbon_Users::USER_FIRST_NAME => 'Guest',
                Carbon_Users::USER_LAST_NAME => 'Account',
                Carbon_Users::USER_GENDER => 'N/A',
                Carbon_Users::USER_EMAIL => 'N/A',
                Carbon_Users::USER_IP => IP
            ])) && Database::commit()) {
            PublicAlert::info('You session has been established. ' . $id);
        } else {
            PublicAlert::warning('Failed to create you a new user. Some demos may not function as expected.');
        }
    }

    public static function configuration(): array
    {
        return [
            'DATABASE' => [
                'DB_HOST' => APP_LOCAL ? '127.0.0.1' : '35.224.229.250',                        // IP
                'DB_PORT' => '3306',
                'DB_NAME' => 'CarbonPHP',                        // Schema
                'DB_USER' => 'root',                            // User
                'DB_PASS' => APP_LOCAL ? 'password' : 'goldteamrules',                        // Password
                'DB_BUILD' => '',                               // SERVER_ROOT . '/config/buildDatabase.php' TODO - auto set this when cli program is run
                'REBUILD' => false
            ],
            'SITE' => [
                'URL' => APP_LOCAL ? 'dev.carbonphp.com' : 'carbonphp.com',    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
                'ROOT' => APP_ROOT,          /* This was defined in our ../index.php */
                'CACHE_CONTROL' => [
                    'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                    'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
                    'html|htm|php|hbs|js' => 'Cache-Control: max-age=0, private, public',
                ],
                'CONFIG' => __FILE__,               // Send to sockets
                'TIMEZONE' => 'America/Phoenix',    //  Current timezone
                'TITLE' => 'CarbonPHP • C6',        // Website title
                'VERSION' => '4.9.0',               // Add link to semantic versioning
                'SEND_EMAIL' => 'richard@miles.systems',
                'REPLY_EMAIL' => 'richard@miles.systems',
                'HTTP' => APP_LOCAL
            ],
            'SESSION' => [
                'REMOTE' => true,             // Store the session in the SQL database
                # 'SERIALIZE' => [ ],           // These global variables will be stored between session
                # 'PATH' => ''
                'CALLBACK' => static function () {         // optional variable $reset which would be true if a url is passed to startApplication()
                    // optional variable $reset which would be true if a url is passed to startApplication()
                    // This is a special case used for documentation and should not be used in prod. See repo stats.coach for example
                    if ($_SESSION['id'] ??= false) {
                        self::getUser(session_id());
                    }
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
                'STORE' => false,      // Database if specified and / or File 'LOCATION' in your system
                'SHOW' => true,       // Show errors on browser
                'FULL' => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
            ],
            'VIEW' => [
                // TODO - THIS IS USED AS A URL AND DIRECTORY PATH. THIS IS BAD. WE NEED DS
                'VIEW' => 'view/',  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
                'WRAPPER' => 'mustache/Documentation/Wrapper.hbs',     // View::content() will produce this
            ],
            'MINIFY' => [
                'CSS' => [
                    'OUT' => APP_ROOT . 'view/mustache/css/style.css',
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
                    'OUT' => APP_ROOT . 'view/mustache/js/javascript.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.js',  // do not use slim version
                    APP_ROOT . 'node_modules/jquery-pjax/jquery.pjax.js',
                    APP_ROOT . 'view/mustache/Layout/mustache.js',
                    CARBON_ROOT . 'helpers/Carbon.js',
                    CARBON_ROOT . 'helpers/asynchronous.js',
                    APP_ROOT . 'node_modules/jquery-form/src/jquery.form.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    APP_ROOT . 'node_modules/admin-lte/dist/js/adminlte.js',
                ],
            ]
        ];
    }
}