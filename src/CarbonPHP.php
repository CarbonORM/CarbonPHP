<?php

namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Helpers\Serialized;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\CLI;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Programs\WebSocket;
use Tests\RestTest;
use Throwable;
use function define;
use function defined;
use function dirname;
use function function_exists;
use function in_array;
use function is_array;
use function is_callable;

/**
 * Class Carbon
 *
 *  The main purpose of the initial obfuscation of magic methods is for type checking and hiding configurations
 *  from the call stack. We handle values of configuration in a way which doesn't drop database user/pass
 *  information if an error was to occur during the setup process. After Carbon is done setting up we return to the
 *  lowest point in the call-stack (CS) possible so memory usage is efficient and your error CS reports are readable.
 *
 * @package Carbon
 * @link http://www.carbonphp.com/
 */
class CarbonPHP
{
    use ColorCode;

    // folder locations
    public const CARBON_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
    public static string $public_carbon_root = '/';         // uri
    public static string $app_root;
    public static string $app_view = 'view/';               // uri
    public static string $reports = DIRECTORY_SEPARATOR;
    private static string $composer_root;

    // C6 options
    public static array $configuration = [];
    public static string $not_invoked_application = '';     // string namespace
    public static string $not_invoked_configuration = '';   // string namespace
    public static ?CLI $commandLineInterface = null;
    public static ?Application $application = null;
    public static bool $setupComplete = false;

    // Application invocation method
    public static bool $app_local = false;
    public static bool $socket = false;
    public static bool $cli = false;
    public static bool $test = false;
    public static bool $pjax = false;
    public static bool $ajax = false;
    public static bool $https = false;
    public static bool $http = false;

    // Validated Server Values
    public static string $server_ip = '127.0.0.1';
    public static ?string $user_ip = null;
    public static string $uri;          // part after url
    public static string $url;
    public static string $site;         // $url . '/'
    public static string $protocol;     // http , ws , wss , https


    // Don't run the application on invocation
    public static bool $safelyExit = false;

    // basic view payload info and reporting info
    public static string $site_title;
    public static string $site_version;
    public static string $system_email;
    public static string $reply_email;


    /**
     * CarbonPHP constructor.
     * @param iConfig|string|null $config
     * @param string|null $app_root
     * @throws Error\PublicAlert
     */
    public function __construct($config = null, string $app_root = null)
    {
        self::make($config, $app_root);
    }

    /**
     * @param Application|string|null $application
     * @return bool
     */
    public function __invoke($application = null): bool
    {
        return self::run($application);
    }

    public static function setApplication(Application $application): void
    {
        self::$application = $application;
    }

    /**
     * while this could return null by type constraints, I would like that case to raise an error here
     * @return Application
     */
    public static function getApplication(): Application
    {
        return self::$application;
    }

    /**
     * @param Application|string|null $application
     * @return bool
     */
    public static function run($application = null): bool
    {
        if (!self::$safelyExit) {
            self::$socket = false;
        }

        if (empty(self::$application)) {
            if (empty($application)) {
                if (!empty(self::$not_invoked_application)) {
                    $application = self::$not_invoked_application;
                    self::setApplication(new $application);
                }
                // we're not required to pass any arguments so this is essentially a break stmt
                // this also works as the condition to move to cli
            } else if ($application instanceof Application) {
                self::setApplication($application);
            } else if (class_exists($application)) {
                self::setApplication(new $application);
            } else if (!self::$safelyExit) {
                print 'Your trying to run CarbonPHP without a valid Application configured. '
                    . 'Argument passed to the static run method or _invoke should be a reference to a child of the abstract Application class. '
                    . 'This could be a fully qualified namespace or an instantiated object.'
                    . 'If no argument is supplied the configuration passed to the constructor, which implements iConfig, must also extend the Application class.';
                return self::$safelyExit = true;
            }
        }

        if (self::$safelyExit) {
            if (self::$cli && !self::$test && self::$commandLineInterface !== null) {
                self::$safelyExit = true;
                $cli = self::$commandLineInterface;
                $cli->run($_SERVER['argv'] ?? ['index.php', null]);
                $cli->cleanUp();
            }
            return true;
        }

        return self::startApplication() !== false; // startApplication can return null which is not allowed here
    }

    public static function resetApplication(): bool
    {
        $_POST = [];
        Session::update(true);        // Check wrapper / session callback
        View::$forceWrapper = true;
        Request::changeURI('/');
        $application = self::getApplication();
        $application::$matched = true;
        $application->defaultRoute();
        return true;
    }

    /** Start application will start a bootstrap file passed to it. It will
     * store that instance in a static variable and reuse it for the process life.
     *
     * @param string $uri - This will always be set in a socket, restarts routing
     * @return bool Returns the response from the bootstrap as a bool
     * @link
     *
     */
    public static function startApplication(string $uri = ''): ?bool
    {
        $application = self::getApplication();

        if ($uri === '') {
            Session::update(false);       // Check wrapper / session callback
            $uri = $application->uri;
        } else if ($uri === '/') {
            return self::resetApplication();
        } else {
            Session::update(true);
            Request::changeURI($uri);           // So the browser will update PJAX
            $application->changeURI($uri);      // So our routing file knows what to match
            $_POST = [];
        }

        $application::$matched = false;          // We can assume your in need of route matching again

        $return = $application->startApplication($uri) ? null : false; // this is for a recursive ending condition for Application::ControllerModelView

        // we need to invoke the destruct magic method which is inherited my Application from Route
        unset($application);
        self::$application = null;  // so we delete all the references to signal explicitly the destruct

        return $return;
    }


    private static function parseConfiguration($configuration): array
    {
        ####################  Now load config file so globals above & stacktrace security
        if ($configuration === null) {
            return self::$configuration = $config = [];
        }

        if ($configuration instanceof iConfig) {
            self::$configuration = $config = $configuration !== null ? $configuration::configuration() : [];
            if ($configuration instanceof Application) {
                self::$application = $configuration;
            }
            return self::$configuration;
        }

        if (class_exists($configuration)) {

            $imp = array_map('strtolower', array_keys(class_implements($configuration)));

            /** @noinspection ClassConstantUsageCorrectnessInspection */
            if (!in_array(strtolower(iConfig::class), $imp, true)) {
                print 'The configuration class passed to C6 must implement the interface iConfig!';
                self::$safelyExit = true;
                exit(1);
            }

            self::$not_invoked_configuration = $configuration;  // this is only ever needed for websockets

            if (is_subclass_of($configuration, Application::class)) {
                // only invoke this when setup is finished so constructors have access to constants
                self::$not_invoked_application = $configuration;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            return self::$configuration = $config = $configuration::configuration();

        }

        if (file_exists($configuration)) {
            /** @var array $config */
            /** @noinspection PhpIncludeInspection */
            $config = include $configuration;            // TODO - change the variable

            if (!is_array($config)) {
                print 'The configuration file passed to C6 must return an array!';
                exit(1);
            }

            return self::$configuration = $config;

        }

        if (is_string($configuration)) {
            print 'Invalid configuration path given! See CarbonPHP.com for instructions. ' . $configuration;
        } else {
            print 'Invalid configuration provided. See CarbonPHP.com for instructions.';
        }

        exit(1);
    }

    /**
     * @param iConfig|string|null $configuration
     * @param string|null $app_root
     * @throws Error\PublicAlert
     * @todo - php 8 add strict types
     */
    public static function make($configuration = null, string $app_root = null): void
    {
        try {
            defined('DS') OR define('DS', DIRECTORY_SEPARATOR);

            if ($app_root !== null) {
                self::$app_root = rtrim($app_root, DS) . DS;    // an extra check
            } else {
                self::$app_root = dirname(self::CARBON_ROOT) . DS;
            }

            /*
             * Caution - FFI
             * If the PHP interpreter has been built with ZTS (Zend Thread Safety) enabled, any changes to the current
             * directory made through chdir() will be invisible to the operating system. All built-in PHP functions will
             * still respect the change in current directory; but external library functions called using FFI will not.
             * You can tell whether your copy of PHP was built with ZTS enabled using php -i or the built-in constant PHP_ZTS.
             * @link https://www.php.net/manual/en/function.chdir.php
             */
            if (getcwd() !== self::$app_root && !chdir(self::$app_root)) {
                self::colorCode("\nCould not change current working directory from " . getcwd() . " to " . self::$app_root . ".\n\n", 'red', true);
            }

            // todo - we're using this as a uri and it could have directory separator in the wrong direction
            if (self::$app_root . 'src' . DS === self::CARBON_ROOT) {
                self::$public_carbon_root = '';
            } elseif (strpos(dirname(self::CARBON_ROOT), self::$app_root) === 0) {
                self::$public_carbon_root = rtrim(substr_replace(dirname(self::CARBON_ROOT), '', 0, strlen(self::$app_root)), DS);
            } else {
                self::$test or self::colorCode('The composer directory ie C6 should be in a child directory of the application root ('.self::$app_root.'). Currently set to :: ' . self::$app_root . "\n\n", 'green');
                self::$public_carbon_root = '//carbonphp.com';
            }

            ####################  CLI is not the CLI server
            self::$cli = self::$test || PHP_SAPI === 'cli';

            self::$test and RestTest::setupServerVariables();

            ####################  Define your own server root
            self::$app_root ??= self::CARBON_ROOT;

            if ($ip = filter_var($_SERVER['REMOTE_ADDR'] ??= '127.0.0.1', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                self::$server_ip = $ip;
            }

            ####################  Did we use >> php -S localhost:8080 index.php
            self::$app_local = self::$cli ?: self::isClientServer();

            ####################  todo - look into removing this and validating send resource starts with view location
            self::$composer_root = dirname(self::CARBON_ROOT, 2) . DS;

            ################  Helpful Global Functions ####################
            if (!file_exists(__DIR__ . DS . 'Functions.php')
                || !include __DIR__ . DS . 'Functions.php') {
                print PHP_EOL . 'Your instance of CarbonPHP appears corrupt. Please see CarbonPHP.com for Documentation' . PHP_EOL;
                die(1);
            }

            $config = self::parseConfiguration($configuration);

            #################  DATABASE  ########################
            if ($config['DATABASE'] ?? false) {
                Database::$dsn = 'mysql:host=' . ($config['DATABASE']['DB_HOST'] ?? '') . ';dbname=' . ($config['DATABASE']['DB_NAME'] ?? '') . (($config['DATABASE']['DB_PORT'] ?? false) && $config['DATABASE']['DB_PORT'] !== ''? ';port=' . $config['DATABASE']['DB_PORT'] : '');
                Database::$username = $config['DATABASE']['DB_USER'] ?? '';
                Database::$password = $config['DATABASE']['DB_PASS'] ?? '';
                Database::$setup = $config['DATABASE']['DB_BUILD'] ?? '';
                Database::$initialized = true;
            }

            #######################   VIEW      ######################
            self::$app_view = $config['VIEW']['VIEW'] ?? DS;         // Public Folder

            View::$wrapper = self::$app_root . self::$app_view . ($config['VIEW']['WRAPPER'] ?? '');

            ####################  GENERAL CONF  ######################
            error_reporting($config['ERROR']['LEVEL'] ?? E_ALL | E_STRICT);

            ini_set('display_errors', $config['ERROR']['SHOW'] ?? true);

            date_default_timezone_set($config['SITE']['TIMEZONE'] ?? 'America/Chicago');

            self::$reports = $config['ERROR']['LOCATION'] ?? '';

            #####################   AUTOLOAD    #######################
            if ($config['AUTOLOAD'] ?? false) {
                $PSR4 = new Autoload();
                if (is_array($config['AUTOLOAD'] ?? false)) {
                    foreach ($config['AUTOLOAD'] as $name => $path) {
                        $PSR4->addNamespace($name, $path);
                    }
                }
            }

            #####################   ERRORS + Warnings + Alerts    #######################
            if ($config['ERROR'] ??= false) {
                ErrorCatcher::$defaultLocation ??= self::$reports . 'Log_' . ($_SESSION['id'] ?? '') . '_' . time() . '.log';
                ErrorCatcher::$fullReports ??= $config['ERROR']['FULL'] ?? true;
                ErrorCatcher::$printToScreen ??= $config['ERROR']['SHOW'] ?? true;
                ErrorCatcher::$storeReport ??= $config['ERROR']['STORE'] ?? false;
                ErrorCatcher::$level ??= $config['ERROR']['LEVEL'] ?? ' E_ALL | E_STRICT';
                ErrorCatcher::start();
            }


            #################  SITE  ########################
            if ($config['SITE'] ?? false) {
                self::$site_title ??= $config['SITE']['TITLE'] ?? 'CarbonPHP [C6]';
                self::$site_version ??= $config['SITE']['VERSION'] ?? PHP_VERSION;                // printed in the footer
                self::$system_email ??= $config['SEND_EMAIL'] ?? '';                               // server email system
                self::$reply_email ??= $config['REPLY_EMAIL'] ?? '';                               // I give you options :P
            }

            if (self::$cli && !self::$test && !self::$safelyExit) {
                self::$safelyExit = true;
                self::$commandLineInterface =
                    new CLI([self::$configuration, $_SERVER['argv'] ?? ['index.php', null]]);
            }

            ##################  VALIDATE URL / URI ##################
            if (!self::$cli || !isset(self::$server_ip)) {
                self::IP_FILTER();
            }

            self::$uri ??= trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'] ?? '')), PHP_URL_PATH)), '/');

            switch ($_SERVER['SERVER_PORT'] ??= 80) {
                default:
                case 80:
                    self::$protocol = 'http://';
                    break;
                case 443:
                    self::$protocol = 'https://';
                    break;
                case WebSocket::$port:
                    self::$protocol = 'wss://';    // todo - ws vs wss
            }


            $_SERVER['SERVER_NAME'] ??= self::$server_ip;

            self::$url = self::$protocol . $_SERVER['SERVER_NAME'] . (self::$app_local ? ':' . $_SERVER['SERVER_PORT'] : '');

            self::$site = self::$url . '/';

            if (!self::$cli) {
                self::URI_FILTER($config['SITE']['URL'] ?? '', $config['SITE']['CACHE_CONTROL'] ?? []);

                #######################   Pjax Ajax Refresh   ######################
                // Must return a non empty value
                $headers = self::headers();

                self::$pjax = (isset($headers['X-PJAX']) || isset($_GET['_pjax']) || ($_SERVER['HTTP_X_PJAX'] ?? false));

                if ($_SERVER['REQUEST_METHOD'] !== 'GET' && empty($_POST)) {
                    # try to json decode. Json payloads ar sent to the input stream
                    $_POST = json_decode(file_get_contents('php://input'), true);
                    if ($_POST === null) {
                        $_POST = [];
                    }
                }

                // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
                self::$ajax = (self::$pjax ||
                    ('XMLHttpRequest' === ($headers['X-Requested-With'] ?? '')) ||
                    (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'));

                self::$https = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ??= false) === 'https' ||
                    ($_SERVER['HTTPS'] ?? 'off') !== 'off');

                self::$https or self::$ajax or self::$http = true;
            }
            // PHPUnit testing should not exit on explicit http(s) requests
            if (!self::$test && self::$http && !($config['SITE']['HTTP'] ?? true)) {
                if (headers_sent()) {
                    print '<h1>Failed to switch to https, headers already sent! Please contact the server administrator.</h1>';
                } else {
                    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                }
                die(1);
            }

            ########################  Session Management ######################
            if ($config['SESSION'] ?? true) {
                if ($config['SESSION']['PATH'] ?? false) {
                    session_save_path($config['SESSION']['PATH'] ?? '');   // Manually Set where the Users Session Data is stored
                }

                // this start a session in every possible runtime except WebSocket::$minimiseResources
                new Session(self::$user_ip, $config['SESSION']['REMOTE'] ?? false);

                $config['ERROR'] and
                ErrorCatcher::$defaultLocation = self::$reports . 'Log_' . ($_SESSION['id'] ?? 'WebSocket') . '_' . time() . '.log';

                if (is_callable($config['SESSION']['CALLBACK'] ?? null)) {
                    Session::updateCallback($config['SESSION']['CALLBACK']); // Pull From Database, manage socket ip
                }
            }

            if (!self::$cli && is_array($config['SESSION']['SERIALIZE'] ?? false)) {
                forward_static_call_array([Serialized::class, 'start'], $config['SESSION']['SERIALIZE']);    // Pull theses from session, and store on shutdown
            }

            self::$setupComplete = true;

        } catch (Throwable $e) {
            print PHP_EOL . 'Carbon Failed Initialization' . PHP_EOL;
            print "\t" . $e->getMessage() . PHP_EOL . PHP_EOL;

            if (self::$app_local && function_exists('sortDump')) {
                sortDump($e, true, false);
                print PHP_EOL . PHP_EOL;
            }
            die(1);
        }
    }


    /**
     * returns 127.0.0.1 if you are using a local development server,
     * otherwise returns false.
     * @return bool|mixed|string
     */
    private static function isClientServer(): bool
    {
        return PHP_SAPI === 'cli-server' || in_array($_SERVER['REMOTE_ADDR'] ??= '', ['127.0.0.1', 'fe80::1', '::1'], false);
    }

    /** If a url is encoded in a version control View::versionControl()
     * or ends in a file extension attempt to load it and send with
     * appropriate headers.
     * @param string $URL by the user configuration file.
     * If the url is not equal to the server url, and we are not
     * on a local development server, then Redirect to url provided.
     *
     *
     * @param array|null $cacheControl
     * @return bool
     */
    private static function URI_FILTER(string $URL = '', array $cacheControl = []): bool
    {
        if (!empty($URL = strtolower($URL)) && $_SERVER['SERVER_NAME'] !== $URL && !self::$app_local) {
            header("Refresh:0; url=$URL");
            print '<html lang="en"><head><!--suppress InjectedReferences -->
    <meta http-equiv="refresh" content="5; url=' . $URL . '"></head><body>' .
                self::$server_ip . '<h1>You appear to be lost.</h1><h2>Moving to <a href="//' . $URL . '"> ' . $URL . '</a></h2>' .
                "<script>window.location.type = $URL</script></body></html>";
            exit(1);
        }

        if (empty($cacheControl)) {
            return true;
        }

        // It does not matter if this matches, we will take care of that in the next if.

        $allowedEXT = implode('|', array_keys($cacheControl));

        // todo this can be bypassed?
        preg_match("#^(.*\.)($allowedEXT)\?*.*#", self::$uri, $matches, PREG_OFFSET_CAPTURE);

        // So if the request has an extension that's not allowed we ignore it and keep processing as a valid route
        $ext = $matches[2][0] ?? '';    // routes should be null

        if (empty($ext)) {              // We're requesting a file
            return true;
        }

        // todo validate were pulling from view dir

        // we need to ensure valid access
        $allowedAccess = false;
        foreach ($cacheControl as $extension => $headers) {
            if (strpos($extension, $ext) !== false) {   // todo - this makes sense but will false positive for woff when only woff2 is allowed
                $allowedAccess = true;
                header($headers);
                break;
            }
        }

        if (!$allowedAccess) {
            http_response_code(403);            // This is a Forbidden response
            exit(0);                                        // This is an exit with error
        }

        // Look for versioning
        View::unVersion($_SERVER['REQUEST_URI']);           // This may exit and send a file

        // add cache control
        if (file_exists(self::$composer_root . self::$uri)) {             // Composer is now always the base uri
            View::sendResource(self::$composer_root . self::$uri, $ext);
        }

        // Not versioned, so see it it exists
        if (file_exists(self::$app_root . self::$uri)) {      //  also may send and exit
            View::sendResource(self::$uri, $ext);
        }
        http_response_code(404);           // If we haven't found the request send code 404 not found
        die(1);
    }


    /** This function uses common keys for obtaining the users real IP.
     * We use this for verbose operating systems support.
     * @link http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/
     * @return mixed|string
     */
    private static function IP_FILTER()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {

                    $ip = trim($ip);

                    if (self::$app_local && $ip === '127.0.0.1') {
                        return self::$server_ip = $ip;
                    }

                    if ($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return self::$server_ip = $ip;
                    }
                }
            }
        }
        print 'Could not establish an IP address.';
        die(1);
    }


    /**
     * todo - look through this
     * @link https://stackoverflow.com/questions/2916232/call-to-undefined-function-apache-request-headers
     * @return array
     */
    private static function headers(): array
    {
        // Drop-in replacement for apache_request_headers() when it's not available
        if (function_exists('apache_request_headers')) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return apache_request_headers();
        }

        // Based on: http://www.iana.org/assignments/message-headers/message-headers.xml#perm-headers
        $arrCasedHeaders = array(
            // HTTP
            'Dasl' => 'DASL',
            'Dav' => 'DAV',
            'Etag' => 'ETag',
            'Mime-Version' => 'MIME-Version',
            'Slug' => 'SLUG',
            'Te' => 'TE',
            'Www-Authenticate' => 'WWW-Authenticate',
            // MIME
            'Content-Md5' => 'Content-MD5',
            'Content-Id' => 'Content-ID',
            'Content-Features' => 'Content-features',
        );
        $arrHttpHeaders = array();

        foreach ($_SERVER as $strKey => $mixValue) {
            if (strpos($strKey, 'HTTP_') !== 0) {
                continue;
            }

            $strHeaderKey = strtolower(substr($strKey, 5));

            if (0 < substr_count($strHeaderKey, '_')) {
                $arrHeaderKey = explode('_', $strHeaderKey);
                $arrHeaderKey = array_map('ucfirst', $arrHeaderKey);
                $strHeaderKey = implode('-', $arrHeaderKey);
            } else {
                $strHeaderKey = ucfirst($strHeaderKey);
            }

            if (array_key_exists($strHeaderKey, $arrCasedHeaders)) {
                $strHeaderKey = $arrCasedHeaders[$strHeaderKey];
            }

            $arrHttpHeaders[$strHeaderKey] = $mixValue;
        }

        return $arrHttpHeaders;

    }
}
