<?php

namespace Config;


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Request;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Users;
use CarbonPHP\View;

class Config extends Application implements iConfig
{

    // these are all relative to the /view/ directory
    private const REACT = '60/index.html';

    private array $version2Dot0 = [
        'Home' => '20/Home.hbs',
        'CarbonPHP' => '20/Introduction.hbs',
        'Installation' => '20/Installation.hbs',
        'Implementations' => '20/Implementations.hbs',
        'Dependencies' => '20/Dependencies.hbs',
        'FileStructure' => '20/QuickStart/FileStructure.hbs',
        'Environment' => '20/QuickStart/Environment.hbs',
        'Options' => '20/QuickStart/Options.hbs',
        'Bootstrap' => '20/QuickStart/Bootstrap.hbs',
        'Wrapper' => '20/QuickStart/Wrapper.hbs',
        'Parallel' => '20/QuickStart/ParallelProcessing.hbs',
        'Overview' => '20/PHP/Overview.hbs',
        'Entities' => '20/PHP/Entities.hbs',
        'Request' => '20/PHP/Request.hbs',
        'Route' => '20/PHP/Route.hbs',
        'Server' => '20/PHP/Server.hbs',
        'Session' => '20/PHP/Session.hbs',
        'Singleton' => '20/PHP/Singleton.hbs',
        'View' => '20/PHP/View.hbs',
        'OSSupport' => '20/PlatformSupport.hbs',
        'UpgradeGuide' => '20/PlatformSupport.hbs',
        'Support' => '20/Support.hbs',
        'License' => '20/License.hbs',
        'AdminLTE' => '20/AdminLTE.hbs',
        'N00B' => '20/N00B.hbs'
    ];


    /**
     * @return mixed|void
     * @throws PublicAlert
     */
    public function defaultRoute()  // Sockets will not execute this
    {
        self::getUser();

        View::$forceWrapper = true; // this will hard refresh the wrapper

        if (APP_LOCAL) {
            // todo - whoami check
            throw new PublicAlert('You need to be on port 3000 :)');
        }

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
            View::$wrapper = APP_ROOT . APP_VIEW . 'assets/AdminLTE/wrapper.hbs';
            return View::content(APP_VIEW . 'color'. DS .'color.hbs', APP_ROOT);
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

                if (file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                    file_exists(APP_ROOT . APP_VIEW . $path = 'assets' . DS . 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')) {

                    // this needs to be in this if block
                    View::$wrapper = APP_ROOT . APP_VIEW . 'assets/AdminLTE/wrapper.hbs';

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

                    if ($page && array_key_exists($page, $this->version2Dot0)) {
                        $this->wrap()($this->version2Dot0[$page]);
                    } else {
                        $this->wrap()($this->version2Dot0['Home']);
                    }
                    return true;
                case '6.0':
                    $this->fullPage()('60/index.html');
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
        global $users, $json;

        if (!is_array($users)) {
            $users = [];
        }

        if ($id === '') {
            $id = session_id();
        }

        $me = [];
        if (!Users::Get($me, null, [
            REST::SELECT => [
                Users::USER_ID,
                Users::USER_USERNAME,
                Users::USER_PASSWORD,
                Users::USER_FIRST_NAME,
                Users::USER_LAST_NAME
            ],
            REST::WHERE => [
                [
                    Users::USER_USERNAME => $id,
                    Users::USER_PASSWORD => $id
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
            $_SESSION['id'] = $json['id'] = $me[Users::COLUMNS[Users::USER_ID]];
        } else if (is_string($id = Users::Post([         // required fields :P
                Users::USER_USERNAME => $id,
                Users::USER_PASSWORD => $id,
                Users::USER_FIRST_NAME => 'Guest',
                Users::USER_LAST_NAME => 'Account',
                Users::USER_GENDER => 'N/A',
                Users::USER_EMAIL => 'N/A',
                Users::USER_IP => IP
            ])) && Database::commit()) {
            PublicAlert::info('A new session has been established with the id ' . $id);
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
                'URL' => APP_LOCAL ? 'local.carbonphp.com' : 'carbonphp.com',    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
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
                    /*if ($_SESSION['id'] ??= false) {
                        self::getUser(session_id());       // todo - opted for the run in self::defaultRoute &| self::startApplication
                    }*/
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
                'WRAPPER' => '20/Wrapper.hbs',     // View::content() will produce this
            ],
            'MINIFY' => [
                'CSS' => [
                    'OUT' => APP_ROOT . 'view/assets/css/style.css',
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
                    'OUT' => APP_ROOT . 'view/assets/js/javascript.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.js',  // do not use slim version
                    APP_ROOT . 'node_modules/jquery-pjax/jquery.pjax.js',
                    APP_ROOT . 'node_modules/mustache/mustache.js',
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