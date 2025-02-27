<?php

declare(strict_types=1);

namespace CarbonPHP;

use CarbonPHP\Abstracts\Application;
use CarbonPHP\Classes\Programs\CLI;
use CarbonPHP\Classes\Programs\WebSocket;
use CarbonPHP\Abstracts\Classes\Request;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\Serialized;
use CarbonPHP\Classes\Session;
use CarbonPHP\Enums\Environment;
use CarbonPHP\Interfaces\iConfiguration;
use CarbonPHP\Throwables\PrivateAlert;
use CarbonPHP\Throwables\PublicAlert;
use CarbonPHP\Traits\Configurations;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;
use function define;

use Tests\Feature\CarbonRestTest;
use function function_exists;
use function is_array;

/**
 * Class Carbon
 *
 *  The main purpose of the initial obfuscation of magic methods is for type checking and hiding configurations
 *  from the call stack. We handle values of configuration in a way which doesn't drop database user/pass
 *  information if an error was to occur during the setup process. After Carbon is done setting up we return to the
 *  lowest point in the call-stack (CS) possible so memory usage is efficient and your error CS reports are readable.
 *
 * @see http://www.carbonphp.com/
 */
class CarbonPHP
{

    public const bool CLI = PHP_SAPI === 'cli';
    public const string CARBON_ROOT = __DIR__ . DIRECTORY_SEPARATOR;

    public static bool $carbon_is_root = false;
    public static string $public_carbon_root = '/';
    public static string $app_root;
    private static string $composer_root;
    public static string $not_invoked_application = '';
    public static ?CLI $commandLineInterface = null;
    public static ?Application $application = null;
    public static bool $setupComplete = false;

    // Application invocation method
    public iConfiguration $configuration;
    public static Environment $env = Environment::LOCAL;
    public static bool $app_local = false;
    public static bool $socket = false;
    public static bool $test = false;
    public static bool $verbose = false;
    public static string $server_ip = '127.0.0.1';
    public static ?string $user_ip = null;
    public static string $site;
    public static string $uri;
    public static string $url;
    public static bool $http;
    public static bool $https;
    public static bool $ajax;
    public static string $protocol;     // http , ws , wss , https
    public static bool $safelyExit = false;

    public function __construct(?iConfiguration $config = null, ?string $app_root = null)
    {
        $this->make($config, $app_root);
    }

    public function __invoke(?Application $application = null): bool
    {
        return self::run($application);
    }

    public static function setApplication(Application $application): void
    {
        self::$application = $application;
    }

    public static function getApplication(): Application
    {
        return self::$application;
    }

    public static function isCarbonPHPDocumentation(): bool
    {
        static $cache;
        return $cache ??= self::$app_root . 'carbonphp' . DS === self::CARBON_ROOT;
    }

    public function run(Application|string|null $application = null): bool
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
                        self::setApplication(new $application());
                    }
                    // we're not required to pass any arguments so this is essentially a break stmt
                    // this also works as the condition to move to cli
                } elseif ($application instanceof Application) {
                    self::setApplication($application);
                } elseif (class_exists($application)) {
                    self::setApplication(new $application());
                } elseif (!self::$safelyExit) {
                    echo 'Your trying to run CarbonPHP without a valid Application configured. '
                        . 'Argument passed to the static run method or _invoke should be a reference to a child of the abstract Application class. '
                        . 'This could be a fully qualified namespace or an instantiated object.'
                        . 'If no argument is supplied the configuration passed to the constructor, which implements iConfig, must also extend the Application class.';

                    return self::$safelyExit = true;
                }
            }

            if (self::$safelyExit) {
                if (self::CLI && !self::$test && self::$commandLineInterface !== null) {
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

            // todo - if not apache it's possible to be handled???
            if (PHP_SAPI !== 'cli'
                && function_exists('apache_connection_stream')
                && str_contains($_SERVER['HTTP_CONNECTION'] ?? '', 'Upgrade')
                && str_contains($_SERVER['HTTP_UPGRADE'] ?? '', 'websocket')) {
                // Here you can handle the WebSocket upgrade logic
                WebSocket::handleSingleUserConnections();
            }

            return self::startApplication() !== false; // startApplication can return null which is not allowed here
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);  // this terminates

            exit(1);
        }
    }

    public function resetApplication(): bool
    {
        $_POST = [];

        $this->configuration->sessionConfiguration->update(true);

        Request::changeURI('/');

        $application = self::getApplication();

        $application::$matched = true;

        $application->defaultRoute();

        return true;
    }

    /** Start application will start a bootstrap file passed to it. It will
     * store that instance in a static variable and reuse it for the process life.
     * @param string $uri - This will always be set in a socket, restarts routing
     * @return bool|null Returns the response from the bootstrap as a bool
     * @see http://carbonphp.com
     */
    public function startApplication(string $uri = ''): ?bool
    {
        $application = self::getApplication();

        if ($uri === '') {
            $this->configuration->sessionConfiguration->update();
            $uri = $application::$uri;
        } elseif ($uri === '/') {
            return self::resetApplication();
        } else {
            $this->configuration->sessionConfiguration->update(true);
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

    private function parseConfiguration(?iConfiguration $configuration): iConfiguration
    {
        return $configuration === null
            ? $this->defaultConfigurations()
            : $configuration;
    }

    /**
     * @see https://www.php.net/manual/en/wrappers.php.php
     *
     * @return void
     */
    public static function protocolWrapper(): void
    {
        if (!\defined('STDOUT')) {
            \define('STDOUT', fopen('php://stdout', 'wb'));
        }

        if (!\defined('STDIN')) {
            \define('STDIN', fopen('php://stdin', 'rb'));
        }

        if (!\defined('STDERR')) {
            \define('STDERR', fopen('php://stderr', 'wb'));
        }

        if (!\defined('OUTPUT')) {
            \define('OUTPUT', fopen('php://output', 'wb'));
        }

        if (!\defined('INPUT')) {
            \define('INPUT', fopen('php://input', 'rb'));
        }
    }

    public function make(?iConfiguration $configuration = null, ?string $app_root = null): void
    {
        try {
            if (false === putenv('PATH=/bin:/usr/bin/:/usr/sbin/:/usr/local/bin:$PATH')) {
                ColorCode::colorCode('putenv: failed to set the PATH environment variable. (file://' . __FILE__ . ':' . __LINE__ . ')', iColorCode::YELLOW);
            }

            self::$test = '1' === ($_ENV['TESTING'] ?? ''); // set with phpunit.xml

            if (false === \defined('DS')) {
                \define('DS', DIRECTORY_SEPARATOR);
            }

            self::protocolWrapper();

            if ($app_root !== null) {
                self::$app_root = rtrim($app_root, DS) . DS;    // an extra check
            } else {
                self::$app_root = \dirname(self::CARBON_ROOT) . DS;
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
                $message = "\nCould not change current working directory from " . getcwd() . ' to ' . self::$app_root . ".\n\n";

                ColorCode::colorCode($message, iColorCode::RED);

                exit($message);
            }

            // todo - we're using this as a uri and it could have directory separator in the wrong direction
            if (self::$app_root . 'carbonphp' . DS === self::CARBON_ROOT) {
                self::$carbon_is_root = true;

                self::$public_carbon_root = '';
            } elseif (str_starts_with(\dirname(self::CARBON_ROOT), self::$app_root)) {
                self::$public_carbon_root = rtrim(substr_replace(\dirname(self::CARBON_ROOT), '', 0, strlen(self::$app_root)), DS);
            } else {
                if (!self::$test) {
                    ColorCode::colorCode('The composer directory ie C6 should be in a child directory of the application root (' . self::$app_root . '). Currently set to :: ' . self::$app_root . "\n
                        Continuing gracefully, but some features may not work as expected.\n", iColorCode::RED);
                }

                self::$public_carbon_root = '//carbonphp.com';
            }

            if (self::$test) {
                CarbonRestTest::setupServerVariables();
            }

            // ###################  Define your own server root
            self::$app_root ??= self::CARBON_ROOT;

            if ($ip = filter_var($_SERVER['REMOTE_ADDR'] ??= '127.0.0.1', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                self::$server_ip = $ip;
            }

            // ###################  Did we use >> php -S localhost:8080 index.php
            self::$app_local = self::CLI ?: self::isClientServer();

            // ###############  Helpful Global Functions ####################
            /** @noinspection UsingInclusionOnceReturnValueInspection */
            if (false === file_exists(__DIR__ . DS . 'Functions.php')
                || false === include_once __DIR__ . DS . 'Functions.php') {
                $message = PHP_EOL . 'Your instance of CarbonPHP appears corrupt. Please reinstall.' . PHP_EOL;

                exit($message);
            }

            $this->configuration = $configuration ?? Configurations::defaultConfigurations();

            // ###################  GENERAL CONF  ######################
            ThrowableHandler::start($configuration->errorConfiguration);

            // ################  SITE  ########################
            if (self::CLI && !self::$test && !self::$safelyExit) {
                self::$safelyExit = true;

                self::$commandLineInterface = new CLI($configuration, $_SERVER['argv'] ?? ['index.php', null]);

                if (null !== self::$commandLineInterface::$program) {
                    ColorCode::colorCode('CarbonPHP CLI has loaded a program into memory, CarbonPHP::make(...) will need to be executed to be invoked.');
                }
            }

            // #################  VALIDATE URL / URI ##################
            if (!self::CLI && $configuration->applicationConfiguration->ipTest) {
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
                    self::$protocol = 'wss://';    // todo - ws vs wss and upgrade connections to wss
            }

            $_SERVER['SERVER_NAME'] ??= self::$server_ip;

            self::$url = self::$protocol . $_SERVER['SERVER_NAME'] . (self::$app_local ? ':' . $_SERVER['SERVER_PORT'] : '');

            self::$site = self::$url . '/';

            if (!self::CLI) {
                // ######################   Pjax Ajax Refresh   ######################
                // Must return a non empty value
                $headers = self::headers();

                if ($_SERVER['REQUEST_METHOD'] !== 'GET' && empty($_POST)) {
                    $getAnyJsonPayloads = file_get_contents('php://input');

                    if (json_validate($getAnyJsonPayloads)) {
                        // try to json decode. Json payloads ar sent to the input stream
                        $_POST = json_decode($getAnyJsonPayloads, true, 512, JSON_THROW_ON_ERROR);
                    } elseif (is_string($getAnyJsonPayloads) && !empty($getAnyJsonPayloads)) {
                        $_POST = ['stdin' => $getAnyJsonPayloads];
                    }

                    if ($_POST === null) {
                        $_POST = [];
                    }
                }

                // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
                self::$ajax = 'XMLHttpRequest' === ($headers['X-Requested-With'] ?? '')
                    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

                self::$https = array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER)
                    ? 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']
                    : ($_SERVER['HTTPS'] ?? 'off') !== 'off';

                self::$https or self::$ajax or self::$http = true;
            }

            // PHPUnit testing should not exit on explicit http(s) requests
            if (!self::$test
                && self::$http
                && !$configuration->applicationConfiguration->httpsOnly) {
                if (headers_sent()) {
                    echo '<h1>Failed to switch to https, headers already sent! Please contact the server administrator.</h1>';
                } else {
                    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 302);
                }

                exit(1);
            }

            // #######################  Session Management ######################
            if (!self::CLI && !empty($this->configuration->sessionConfiguration->serializeClasses)) {
                Serialized::start($this->configuration->sessionConfiguration->serializeClasses);
            }

            self::$setupComplete = true;
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);   // this will exit if executed
        }
    }

    /**
     * returns 127.0.0.1 if you are using a local development server,
     * otherwise returns false.
     *
     * @return bool|mixed|string
     */
    private static function isClientServer(): bool
    {
        return PHP_SAPI === 'cli-server' || \in_array($_SERVER['REMOTE_ADDR'] ??= '', ['127.0.0.1', 'fe80::1', '::1'], false);
    }

    /** This function uses common keys for obtaining the users real IP.
     * We use this for verbose operating systems support.
     *
     * @see http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/
     *
     * @return mixed|string
     */
    private static function IP_FILTER()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

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

        exit(1);
    }

    /**
     * todo - look through this
     *
     * @see https://stackoverflow.com/questions/2916232/call-to-undefined-function-apache-request-headers
     *
     * @return array
     */
    private static function headers(): array
    {
        // Drop-in replacement for apache_request_headers() when it's not available
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        // Based on: http://www.iana.org/assignments/message-headers/message-headers.xml#perm-headers
        $arrCasedHeaders = [
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
        ];
        $arrHttpHeaders = [];

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
