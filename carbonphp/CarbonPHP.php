<?php

namespace CarbonPHP;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\Serialized;
use CarbonPHP\Enums\ThrowableReportDisplay;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\CLI;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Restful\RestfulValidations;
use Tests\Feature\CarbonRestTest;
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

    // folder locations
    public const CARBON_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
    public static bool $carbon_is_root = false;
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

    // CarbonPHP Configuration Keys
    public const DATABASE = 'DATABASE';
    public const DB_HOST = 'DB_HOST';
    public const DB_READER = 'DB_READER';
    public const DB_PORT = 'DB_PORT';
    public const DB_NAME = 'DB_NAME';
    public const DB_USER = 'DB_USER';
    public const DB_PASS = 'DB_PASS';
    public const REBUILD = 'REBUILD';
    public const REBUILD_WITH_CARBON_TABLES = 'REBUILD_WITH_CARBON_TABLES';

    // todo - transfer all cli options to the config
    public const REST = 'REST';
    public const NAMESPACE = 'NAMESPACE';
    public const TABLE_PREFIX = 'TABLE_PREFIX';
    public const VALIDATE_EXTERNAL_REQUESTS_GENERATED_SQL = 'VALIDATE_EXTERNAL_REQUESTS_GENERATED_SQL';

    // Site Config
    public const SITE = 'SITE';
    public const QUERY_WITH_DATABASE_NAME = 'QUERY_WITH_DATABASE_NAME';
    public const URL = 'URL';
    public const ROOT = 'ROOT';
    public const CACHE_CONTROL = 'CACHE_CONTROL';
    public const CONFIG = 'CONFIG';
    public const TIMEZONE = 'TIMEZONE';
    public const TITLE = 'TITLE';
    public const VERSION = 'VERSION';
    public const SEND_EMAIL = 'SEND_EMAIL';
    public const REPLY_EMAIL = 'REPLY_EMAIL';
    public const HTTP = 'HTTP';
    public const IP_TEST = 'IP_TEST';
    public const PROGRAMS = 'PROGRAMS';
    public const PROGRAM_DIRECTORIES = 'PROGRAM_DIRECTORIES';

    // Session Mgmt
    public const SESSION = 'SESSION';
    public const REMOTE = 'REMOTE';
    public const SERIALIZE = 'SERIALIZE';   // accepts an array of values todo - doc
    public const CALLBACK = 'CALLBACK';
    public const PATH = 'PATH';

    // Web Socket (Secure* see carbonphp.com)
    public const SOCKET = 'SOCKET';
    public const WEBSOCKETD = 'WEBSOCKETD';
    public const PORT = 'PORT';
    public const DEV = 'DEV';
    public const SSL = 'SSL';
    public const KEY = 'KEY';
    public const CERT = 'CERT';

    // Error Catcher
    public const ERROR = 'ERROR';
    public const DISPLAY = 'DISPLAY';
    public const LOCATION = 'LOCATION';
    public const LEVEL = 'LEVEL';
    public const STORE = 'STORE';
    public const SHOW = 'SHOW';
    public const FULL = 'FULL';

    // Default View info for Mvc
    public const VIEW = 'VIEW';
    public const WRAPPER = 'WRAPPER';
    public const MINIFY = 'MINIFY';
    public const CSS = 'CSS';
    public const JS = 'JS';
    public const OUT = 'OUT';

    // Auto load is deprecated
    public const AUTOLOAD = 'AUTOLOAD';

    // Application invocation method
    public static bool $is_running_production = false;
    public static bool $app_local = false;
    public static bool $socket = false;
    public static bool $cli = true;
    public static bool $test = false;
    public static bool $pjax = false;
    public static bool $ajax = false;
    public static bool $https = false;
    public static bool $http = false;
    public static bool $verbose = false;

    // Wordpress Support
    public static bool $wordpressPluginEnabled = false;

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


    public static function isCarbonPHPDocumentation(): bool
    {

        static $cache;

        return $cache ??= self::$app_root . 'carbonphp' . DS === self::CARBON_ROOT;

    }

    /**
     * @param Application|string|null $application
     * @return bool
     */
    public static function run($application = null): bool
    {
        try {

            if (!self::$safelyExit) {

                self::$socket = false;

            }

            if (false === self::$setupComplete) {

                throw new PrivateAlert('Failed to verify CarbonPHP was created successfully.');

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

                    ColorCode::colorCode('CarbonPHP is finished initializing and is running the command line interface.');

                    $cli = self::$commandLineInterface;

                    $cli->run($_SERVER['argv'] ?? ['index.php', null]);

                    $cli->cleanUp();

                    ColorCode::colorCode('CarbonPHP is returning (true) from (' . __METHOD__ . '). The cli command has finished.');

                    return true;

                }

                ColorCode::colorCode('CarbonPHP is returning (true) from (' . __METHOD__ . ').');

                return true;

            }

            return self::startApplication() !== false; // startApplication can return null which is not allowed here

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);  // this terminates

            exit(1);

        }

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
     * @link http://carbonphp.com
     */
    public static function startApplication(string $uri = ''): ?bool
    {
        $application = self::getApplication();

        if ($uri === '') {

            Session::update();       // Check wrapper / session callback

            $uri = $application::$uri;

        } else if ($uri === '/') {

            return self::resetApplication();

        } else {

            Session::update(true);

            Request::changeURI($uri);           // So the browser will update PJAX

            $application::changeURI($uri);      // So our routing file knows what to match

            $_POST = [];

        }

        $application::$matched = false;          // We can assume your in need of route matching again

        $return = $application->startApplication($uri) ? null : false; // this is for a recursive ending condition for Application::ControllerModelView

        // we need to invoke the destruct magic method which is inherited my Application from Route
        unset($application);

        self::$application = null;  // so we delete all the references to signal explicitly the destruct

        return $return;
    }


    private static function &parseConfiguration(iConfig|array|string $configuration): array
    {

        ####################  Now load config file so globals above & stacktrace security
        if ($configuration === null) {

            self::$configuration = [];

            return self::$configuration;

        }

        if (is_array($configuration)) {

            self::$configuration = $configuration;

            return self::$configuration;

        }

        if ($configuration instanceof iConfig) {

            self::$configuration = $configuration::configuration();

            if ($configuration instanceof Application) {

                self::$application = $configuration;

            }

            return self::$configuration;

        }

        if (class_exists($configuration)) {

            $imp = array_map('strtolower', array_keys(class_implements($configuration)));

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

            self::$configuration = $configuration::configuration();

            return self::$configuration;

        }

        if (file_exists($configuration)) {

            /** @var array $config */
            $config = include $configuration;            // TODO - change the variable

            if (!is_array($config)) {
                print 'The configuration file passed to C6 must return an array!';
                exit(1);
            }

            self::$configuration = $config;

            return self::$configuration;

        }

        if (is_string($configuration)) {
            print 'Invalid configuration path given! See CarbonPHP.com for instructions. (' . $configuration . ')';
        } else {
            print 'Invalid configuration provided. See CarbonPHP.com for instructions.';
        }

        exit(1);
    }

    /**
     * @param iConfig|array|string|null $configuration
     * @param string|null $app_root
     * @todo - php 8 add strict types
     */
    public static function make(iConfig|array|string $configuration = null, string $app_root = null): void
    {
        try {

            self::$test = '1' === ($_ENV['TESTING'] ?? ''); // set with phpunit.xml

            if (false === defined('DS')) {

                define('DS', DIRECTORY_SEPARATOR);

            }

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

                $message = "\nCould not change current working directory from " . getcwd() . " to " . self::$app_root . ".\n\n";

                ColorCode::colorCode($message, iColorCode::RED);

                exit($message);

            }


            // @link https://stackoverflow.com/questions/20124327/php-shell-exec-command-is-not-working
            if (false === putenv('PATH=/bin:/usr/bin/:/usr/sbin/:/usr/local/bin:$PATH')) {

                ColorCode::colorCode('putenv: failed to set the PATH environment variable. (file://' . __FILE__ . ':' . __LINE__ . ')', iColorCode::YELLOW);

            }

            // todo - we're using this as a uri and it could have directory separator in the wrong direction
            if (self::$app_root . 'carbonphp' . DS === self::CARBON_ROOT) {

                self::$carbon_is_root = true;

                self::$public_carbon_root = '';

            } elseif (str_starts_with(dirname(self::CARBON_ROOT), self::$app_root)) {

                self::$public_carbon_root = rtrim(substr_replace(dirname(self::CARBON_ROOT), '', 0, strlen(self::$app_root)), DS);

            } else {

                if (!self::$test) {

                    ColorCode::colorCode('The composer directory ie C6 should be in a child directory of the application root (' . self::$app_root . '). Currently set to :: ' . self::$app_root . "\n
                        Continuing gracefully, but some features may not work as expected.\n", iColorCode::RED);

                }

                self::$public_carbon_root = '//carbonphp.com';

            }

            ####################  CLI is not the CLI server
            self::$cli = (self::$test || PHP_SAPI === 'cli');

            if (self::$test) {

                CarbonRestTest::setupServerVariables();

            }

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
            /** @noinspection UsingInclusionOnceReturnValueInspection */
            if (false === file_exists(__DIR__ . DS . 'Functions.php')
                || false === include_once __DIR__ . DS . 'Functions.php') {

                $message = PHP_EOL . 'Your instance of CarbonPHP appears corrupt. Please reinstall.' . PHP_EOL;

                die($message);

            }

            $config = &self::parseConfiguration($configuration);

            #################  DATABASE  ########################
            if ($config[self::DATABASE] ?? false) {

                Database::$rebuildWithCarbonTables = $config[self::DATABASE][self::REBUILD_WITH_CARBON_TABLES] ??= false;

                Database::$carbonDatabaseUsername = $config[self::DATABASE][self::DB_USER] ??= '';

                Database::$carbonDatabasePassword = $config[self::DATABASE][self::DB_PASS] ??= '';

                Database::$carbonDatabaseName = $config[self::DATABASE][self::DB_NAME] ??= '';

                Database::$carbonDatabasePort = $config[self::DATABASE][self::DB_PORT] ??= '';

                Database::$carbonDatabaseHost = $config[self::DATABASE][self::DB_HOST] ??= '';

                Database::$carbonDatabaseReader = $config[self::DATABASE][self::DB_READER] ??= '';

                Database::$carbonDatabaseDSN = 'mysql:host='
                    . Database::$carbonDatabaseHost
                    . ';dbname='
                    . Database::$carbonDatabaseName
                    . (!empty(Database::$carbonDatabasePort)
                        ? ';port=' . Database::$carbonDatabasePort
                        : '');

                if ('' !== Database::$carbonDatabaseReader) {

                    Database::$carbonDatabaseReaderDSN = 'mysql:host='
                        . Database::$carbonDatabaseReader
                        . ';dbname='
                        . Database::$carbonDatabaseName
                        . (!empty(Database::$carbonDatabasePort)
                            ? ';port=' . Database::$carbonDatabasePort
                            : '');

                }

            }

            #######################   VIEW      ######################
            self::$app_view = $config[self::VIEW][self::VIEW] ??= '';         // Public Folder

            View::$wrapper = self::$app_root . self::$app_view . ($config[self::VIEW][self::WRAPPER] ??= '');

            ####################  GENERAL CONF  ######################
            error_reporting($config[self::ERROR][self::LEVEL] ??= E_ALL | E_STRICT);

            ini_set('display_errors', $config[self::ERROR][self::SHOW] ??= true);

            date_default_timezone_set($config[self::SITE][self::TIMEZONE] ??= 'America/Chicago');

            self::$reports = $config[self::ERROR][self::LOCATION] ??= '';

            #####################   ERRORS + Warnings + Alerts    #######################
            if ($config[self::ERROR] ??= false) {

                ThrowableHandler::$defaultLocation ??= self::$reports . 'default_log.txt';

                ThrowableHandler::$throwableReportDisplay = $config[self::ERROR][self::DISPLAY] ??= (self::$cli ? ThrowableReportDisplay::CLI_MINIMAL : ThrowableReportDisplay::FULL_DEFAULT);

                ThrowableHandler::$printToScreen = $config[self::ERROR][self::SHOW] ??= ThrowableHandler::$printToScreen;

                ThrowableHandler::$storeReport = $config[self::ERROR][self::STORE] ??= ThrowableHandler::$storeReport;

                ThrowableHandler::$level = $config[self::ERROR][self::LEVEL] ??= ThrowableHandler::$level;

                ThrowableHandler::start();

            }

            #################  SITE  ########################
            if ($config['SITE'] ?? false) {

                self::$site_title ??= $config[self::SITE][self::TITLE] ??= 'CarbonPHP [C6]';

                self::$site_version ??= $config[self::SITE][self::VERSION] ??= PHP_VERSION;                // printed in the footer

                self::$system_email ??= $config[self::SEND_EMAIL] ??= '';                               // server email system

                self::$reply_email ??= $config[self::REPLY_EMAIL] ??= '';                               // I give you options :P

            }

            if (self::$cli && !self::$test && !self::$safelyExit) {

                self::$safelyExit = true;

                CLI::$customProgramDirectories = $config[self::SITE][self::PROGRAM_DIRECTORIES] ??= [];

                self::$commandLineInterface = new CLI([self::$configuration, $_SERVER['argv'] ?? ['index.php', null]]);

                if (null !== self::$commandLineInterface::$program) {

                    ColorCode::colorCode('CarbonPHP CLI has loaded a program into memory, CarbonPHP::make(...) will need to be executed to be invoked.');

                }

            }

            ##################  VALIDATE URL / URI ##################
            if (!self::$cli && (false !== ($config[self::SITE][self::IP_TEST] ?? true))) {

                self::IP_FILTER();

            }

            self::$uri ??= trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'] ??= '')), PHP_URL_PATH)), '/');

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
            if (!self::$test && self::$http && !($config[self::SITE][self::HTTP] ?? true)) {

                if (headers_sent()) {

                    print '<h1>Failed to switch to https, headers already sent! Please contact the server administrator.</h1>';

                } else {

                    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 302);

                }

                die(1);

            }

            ########################  Session Management ######################
            RestfulValidations::$validateExternalRequestsGeneratedSql = $config[self::REST][self::VALIDATE_EXTERNAL_REQUESTS_GENERATED_SQL] ??= !self::$app_local;


            ########################  Session Management ######################
            if ($config[self::SESSION] ?? false) {


                $sessionSavePath = $config[self::SESSION][self::PATH] ??= self::$app_root . 'tmp' . DS . 'sessions' . DS;

                Files::createDirectoryIfNotExist($sessionSavePath);

                if (headers_sent($file, $line)) {

                    throw new PrivateAlert("Failed to start session, headers already sent from file ($file) on line ($line) ! Please contact the server administrator.");

                }

                session_save_path($sessionSavePath);   // Manually Set where the Users Session Data is stored

                new Session($config[self::SESSION][self::REMOTE] ?? false);

                if (is_callable($config[self::SESSION][self::CALLBACK] ?? null)) {

                    Session::updateCallback($config[self::SESSION][self::CALLBACK]); // Pull From Database, manage socket ip

                }

            }


            if (!self::$cli && is_array($config[self::SESSION][self::SERIALIZE] ?? false)) {
                forward_static_call_array([Serialized::class, 'start'], $config[self::SESSION][self::SERIALIZE]);    // Pull theses from session, and store on shutdown
            }

            self::$setupComplete = true;

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);   // this will exit if executed

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
     * @param array|null $cacheControl
     * @return bool
     * @todo recursion checking
     *
     */
    private static function URI_FILTER(string $URL = '', array $cacheControl = []): bool
    {
        if (!empty($URL = strtolower($URL)) && $_SERVER['SERVER_NAME'] !== $URL && !self::$app_local) {

            header("Refresh:0; url=$URL");

            print '<html lang="en"><head><meta http-equiv="refresh" content="5; url=//' . $URL . '"></head><body>' .
                self::$server_ip . '<h1>Whoa, this server you reached does not appear to have permission to access the website hosted on it.' .
                ' The server name (' . $_SERVER["SERVER_NAME"] . ') must match that passed via ["SITE"]["URL"] = ('.$URL.') to CarbonPHP.</h1>' .
                '<h3>This message can be bypassed by leaving the configuration field "URL".</h3>' .
                '<h2>Redirecting to <a href="//' . $URL . '"> ' . $URL . '</a></h2>';
            // . "<script>window.location.type = $URL</script></body></html>";

            exit(1);

        }

        if (empty($cacheControl)) {

            return true;

        }

        // It does not matter if this matches, we will take care of that in the next if.

        $allowedEXT = implode('|', array_keys($cacheControl));

        preg_match("#^(.*\.)($allowedEXT)\?*.*#", self::$uri, $matches, PREG_OFFSET_CAPTURE);

        // So if the request has an extension that's not allowed we ignore it and keep processing as a valid route
        $ext = $matches[2][0] ?? '';    // routes should be null

        if (empty($ext)) {              // We're requesting a file

            return true;

        }

        // we need to ensure valid access
        $allowedAccess = false;

        foreach ($cacheControl as $extension => $headers) {

            if (str_contains($extension, $ext)) {   // todo - this makes sense but will false positive for woff when only woff2 is allowed

                $allowedAccess = true;

                header($headers);

                break;

            }

        }

        if (!$allowedAccess) {


            ColorCode::colorCode("Sending 403 Forbidden for uri :: {$_SERVER['REQUEST_URI']}",
                iColorCode::BACKGROUND_YELLOW);

            http_response_code(403);            // This is a Forbidden response


            exit(0);                                        // This is an exit with error

        }

        // Look for versioning
        View::unVersion($_SERVER['REQUEST_URI']);           // This may exit and send a file

        // Not versioned, so see it it exists
        if (self::$app_root !== self::$composer_root
            && file_exists(self::$app_root . self::$uri)) {      //  also may send and exit

            View::sendResource(self::$uri, $ext);

        } else {

            ColorCode::colorCode("File was not found ::\nfile://" . self::$app_root . self::$uri,
                iColorCode::BACKGROUND_CYAN);

        }

        if (file_exists(self::$composer_root . self::$uri)) {             // Composer is now always the base uri

            View::sendResource(self::$composer_root . self::$uri, $ext);

        } else {

            ColorCode::colorCode("
            File was not found ::\nfile://" . self::$composer_root . self::$uri,
                iColorCode::BACKGROUND_CYAN);

        }

        ColorCode::colorCode("Sending 404 for uri :: {$_SERVER['REQUEST_URI']}", iColorCode::BACKGROUND_RED);

        http_response_code(404);           // If we haven't found the request send code 404 not found

        exit(0);

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

                    if (self::$app_local && ($ip === '127.0.0.1' || $ip === '::1')) {

                        return self::$server_ip = $ip;

                    }

                    if ($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

                        return self::$server_ip = $ip;

                    }

                }

            }

        }

        try {

            throw new PublicAlert('Could not establish an IP address.');

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);  // this should terminate

        }

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
            if (!str_starts_with($strKey, 'HTTP_')) {
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
