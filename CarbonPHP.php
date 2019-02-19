<?php

namespace CarbonPHP;

use CarbonPHP\Helpers\Serialized;
use CarbonPHP\programs\CLI;

/**
 * Class Carbon
 * @package Carbon
 *
 * This is CarbonPHP, a simple framework designed to make building robust web
 * applications extremely quick. The following class is the setup for the rest
 * of library. For reference and support visit
 *
 * @link http://www.carbonphp.com/
 */
class CarbonPHP
{
    /**
     * @var bool $safelyExit determines if start application should be executed
     * when running the invoke magic method.
     */
    private $safelyExit = false;

    public static $setupComplete = false;

    /** If safely exit is false run startApplication(), otherwise return $safelyExit
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * @param string $application The class to execute. This must extend CarbonPHP/Application
     * @return bool
     */
    public function __invoke($application)
    {
        return $this->safelyExit ?: startApplication($application);
    }

    /**
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
     *           'BOOTSTRAP' => string '',                            // This file is executed when the startApplication() function is called
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
     *           'WEBSOCKETD' => bool false,        // Todo - remove websockets
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
    public function __construct(string $PHP = null)
    {
        // TODO - make a cache of these consts

        ####################  Sockets will have already claimed this global
        \defined('TEST') OR \define('TEST', $_ENV['TEST'] ?? false);

        if (TEST) {
            $this->safelyExit = true;  // We just want the env to load, not route life :)
            $_SERVER = [
                'REMOTE_ADDR' => '::1',
                'REMOTE_PORT' => '53950',
                'SERVER_SOFTWARE' => 'PHP 7.2.3 Development Server',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => '80',
                'REQUEST_URI' => '/login/',
                'REQUEST_METHOD' => 'GET',
                'SCRIPT_NAME' => '/index.php',
                'SCRIPT_FILENAME' => "C:\Users\rmiles\Documents\GitHub\Stats.Coach\index.php",
                'PATH_INFO' => '/login/',
                'PHP_SELF' => '/index.php/login/',
                'HTTP_HOST' => 'localhost:88',
                'HTTP_CONNECTION' => 'keep-alive',
                'HTTP_CACHE_CONTROL' => 'max-age=0',
                'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'HTTP_REFERER' => 'http://localhost:88/',
                'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
                'HTTP_COOKIE' => 'PHPSESSID=gn4amaq3el5giekaboa29q27gp; _gid=GA1.1.1938536140.1530039320',
                'REQUEST_TIME_FLOAT' => 1530054388.652,
                'REQUEST_TIME' => 1530054388,
            ];
        }
        ####################  Sockets will have already claimed this global
        \defined('SOCKET') OR \define('SOCKET', false);

        ####################  Define your own server root
        \defined('APP_ROOT') OR \define('APP_ROOT', CARBON_ROOT);

        ####################  For help loading our Carbon.js
        \defined('CARBON_ROOT') OR \define('CARBON_ROOT', __DIR__ . DS);

        ####################  Did we use >> php -S localhost:8080 index.php
        \defined('APP_LOCAL') OR \define('APP_LOCAL', $this->isClientServer());

        ####################  May as well make composer a dependency
        \defined('COMPOSER_ROOT') OR \define('COMPOSER_ROOT', \dirname(CARBON_ROOT, 2) . DS);

        ####################  Template Root
        \defined('TEMPLATE_ROOT') OR \define('TEMPLATE_ROOT', CARBON_ROOT);


        ################  Helpful Global Functions ####################
        if (!file_exists(CARBON_ROOT . 'helpers' . DS . 'Application.php') || !include CARBON_ROOT . 'helpers' . DS . 'Application.php') {
            print '<h1>Your instance of CarbonPHP appears corrupt. Please see CarbonPHP.com for Documentation.</h1>';
            die(1);
        }

        ####################  Now load config file so globals above & stacktrace security
        if ($PHP !== null) {
            if (file_exists($PHP)) {
                $PHP = include $PHP;            // this file must return an array!
            } elseif ($PHP !== null) {
                print 'Invalid configuration path given! ' . $PHP;
                $this->safelyExit = true;
                return;
            }
        }

        #######################   VIEW      ######################
        \define('APP_VIEW', $PHP['VIEW']['VIEW'] ?? DS);         // Public Folder

        View::$wrapper = APP_ROOT . APP_VIEW . $PHP['VIEW']['WRAPPER'] ?? '';

        ####################  GENERAL CONF  ######################
        error_reporting($PHP['ERROR']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', $PHP['ERROR']['SHOW'] ?? true);

        date_default_timezone_set($PHP['SITE']['TIMEZONE'] ?? 'America/Chicago');

        \defined('DS') OR \define('DS', DIRECTORY_SEPARATOR);

        \defined('APP_ROOT') OR \define('APP_ROOT', CARBON_ROOT);

        \define('REPORTS', $PHP['ERROR']['LOCATION'] ?? APP_ROOT);

        #####################   AUTOLOAD    #######################
        if ($PHP['AUTOLOAD'] ?? false) {
            $PSR4 = include CARBON_ROOT . 'AutoLoad.php';
            if (\is_array($PHP['AUTOLOAD'] ?? false)) {
                foreach ($PHP['AUTOLOAD'] as $name => $path) {
                    $PSR4->addNamespace($name, $path);
                }
            }
        }

        #####################   ERRORS    #######################
        /**
         * TODO - debating on removing the start and attempting to catch our own errors. look into later
         * So I've looked into it and discovered thrown errors can return to the current execution point
         * We must now decide how and when we throw errors / exceptions..
         *
         * Questions still to test. When does the error catcher get resorted to?
         * Do Try Catch block have a higher precedence than the error catcher?
         * What if that error is thrown multiple function levels down in a block?
         **/

        if ($PHP['ERROR'] ?? false) {
            Error\ErrorCatcher::$defaultLocation = REPORTS . 'Log_' . ($_SESSION['id'] ?? '') . '_' . time() . '.log';
            Error\ErrorCatcher::$fullReports = $PHP['ERROR']['STORE'] ?? false;
            Error\ErrorCatcher::$printToScreen = $PHP['ERROR']['SHOW'] ?? true;
            Error\ErrorCatcher::$storeReport = $PHP['ERROR']['FULL'] ?? true;
            Error\ErrorCatcher::$level = $PHP['ERROR']['LEVEL'] ?? ' E_ALL | E_STRICT';
            Error\ErrorCatcher::start();
        } // Catch application errors and alerts


        #################  DATABASE  ########################
        if ($PHP['DATABASE'] ?? false) {
            Database::$dsn = 'mysql:host='.($PHP['DATABASE']['DB_HOST'] ?? '').';dbname='. ($PHP['DATABASE']['DB_NAME'] ?? '') .';';
            Database::$username = $PHP['DATABASE']['DB_USER'] ?? '';
            Database::$password = $PHP['DATABASE']['DB_PASS'] ?? '';
            Database::$setup = $PHP['DATABASE']['DB_BUILD'] ?? '';
        }

        #################  SITE  ########################
        if ($PHP['SITE'] ?? false) {
            \define('SITE_TITLE', $PHP['SITE']['TITLE'] ?? 'CarbonPHP');                     // Carbon doesnt use
            \define('SITE_VERSION', $PHP['SITE']['VERSION'] ?? PHP_VERSION);                // printed in the footer
            \define('SYSTEM_EMAIL', $PHP['SEND_EMAIL'] ?? '');                               // server email system
            \define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                               // I give you options :P
        }

        // TODO - move to app invocation
        // PHPUnit Runs in a cli to ini the 'CarbonPHP' env.
        // We're not testing out extra resources
        if (PHP_SAPI === 'cli' && !TEST && !SOCKET) {
            $this->safelyExit = true;
            $cli = new CLI($PHP);
            $cli->run($_SERVER['argv'] ?? ['index.php', null]);
            $cli->cleanUp($PHP);
            return;
        }

        ##################  VALIDATE URL / URI ##################
        // This is the first step that could kick users out of our application.
        // Even if a request is bad, we need to store the log
        if (!\defined('IP')) {
            $this->IP_FILTER();
        }

        // This is the first event that could resolve the request (to a file), respond, and exit safely
        $this->URI_FILTER($PHP['SITE']['URL'] ?? '', $PHP['SITE']['CACHE_CONTROL'] ?? []);


        // TODO - I'm probably going to move this to the cli
        if ($PHP['DATABASE']['REBUILD'] ?? false) {
            Database::setUp(false);   // Redirect = false
            $this->safelyExit = true;
            return;
        }

        #######################   Pjax Ajax Refresh  ######################
        // Must return a non empty value
        \define('PJAX', SOCKET ? false : isset($_GET['_pjax']) || (isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX']));

        if (PJAX && empty($_POST)) {
            # try to json decode. Json payloads ar sent to the input stream
            $_POST = json_decode(file_get_contents('php://input'), true);
        }

        // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
        \define('AJAX', SOCKET ? false : PJAX || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));

        \define('HTTPS', SOCKET ? false : ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? false) === 'https' || ($_SERVER['HTTPS'] ?? false) !== 'off');

        \define('HTTP', !(HTTPS || SOCKET || AJAX));

        // PHPUnit testing should not exit on explicit http(s) requests
        if (!TEST && HTTP && !($PHP['SITE']['HTTP'] ?? true)) {
            if (headers_sent()) {
                print '<h1>Failed to switch to https, headers already sent! Please contact the server administrator.</h1>';
            } else {
                header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
            die(1);
        }

        // TODO - I think we should make this optional
        #AJAX OR $_POST = []; // We only allow post requests through ajax/pjax

        ########################  Session Management ######################
        if ($PHP['SESSION'] ?? true) {
            if ($PHP['SESSION']['PATH'] ?? false) {
                session_save_path($PHP['SESSION']['PATH'] ?? '');   // Manually Set where the Users Session Data is stored
            }

            new Session(IP, $PHP['SESSION']['REMOTE'] ?? false); // session start

            $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

            if (\is_callable($PHP['SESSION']['CALLBACK'] ?? null)) {
                Session::updateCallback($PHP['SESSION']['CALLBACK']); // Pull From Database, manage socket ip
            }
        }

        if (\is_array($PHP['SESSION']['SERIALIZE'] ?? false)) {
            forward_static_call_array([Serialized::class, 'start'], $PHP['SESSION']['SERIALIZE']);    // Pull theses from session, and store on shutdown
        }

        self::$setupComplete = true;
    }


    /**
     * returns 127.0.0.1 if you are using a local development server,
     * otherwise returns false.
     * @return bool|mixed|string
     */
    private function isClientServer()
    {
        if (PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli' || \in_array($_SERVER['REMOTE_ADDR'] ?? [], ['127.0.0.1', 'fe80::1', '::1'], false)) {
            if (SOCKET && $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                \define('IP', $ip);
                return false;
            }
            \define('IP', '127.0.0.1');
            return IP;
        }
        return false;
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
    private
    function URI_FILTER(string $URL = 'CarbonPHP.com', array $cacheControl = null): bool
    {
        if (!empty($URL = strtolower($URL)) && $_SERVER['SERVER_NAME'] !== $URL && !APP_LOCAL) {
            header("Refresh:0; url=$URL");
            print '<html><head><!--suppress InjectedReferences -->
    <meta http-equiv="refresh" content="5; url=' . $URL . '"></head><body>' .
                IP . '<h1>You appear to be lost.</h1><h2>Moving to <a href="' . $URL . '"> ' . $URL . '</a></h2>' .
                "<script>window.location.type = $URL</script></body></html>";
            $this->safelyExit = true;
        }

        \define('URI', trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)), '/'), true);

        \define('URL',
            (isset($_SERVER['SERVER_NAME']) ?
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ? 'https://' : 'http://') .
                $_SERVER['SERVER_NAME'] . (APP_LOCAL ? ':' . $_SERVER['SERVER_PORT'] : '') : null), true);

        \define('SITE', url . '/', true);   // http(s)://example.com/  - URL's require a forward slash so DS may not work on os (windows)

        // It does not matter if this matches, we will take care of that in the next if.

        $allowedEXT = implode('|', array_keys($cacheControl));

        preg_match("#^(.*\.)($allowedEXT)\?*.*#", URI, $matches, PREG_OFFSET_CAPTURE);

        // So if the request has an extension that's not allowed we ignore it and keep processing as a valid route
        $ext = $matches[2][0] ?? '';    // routes should be null

        if (empty($ext)) {              // We're requesting a file
            return true;
        }


        // we need to ensure valid access
        $allowedAccess = false;
        foreach ($cacheControl as $extension => $headers) {
            if (strpos($extension, $ext) !== false) {
                $allowedAccess = true;
                header($headers);
                break;
            }
        }

        if (!$allowedAccess) {
            http_response_code(403);            // This is a Forbidden response
            exit(1);                                        // This is an exit with error
        }

        // Look for versioning
        View::unVersion($_SERVER['REQUEST_URI']);           // This may exit and send a file


        // add cache control

        if (file_exists(COMPOSER_ROOT . URI)) {             // Composer is now always the base uri
            View::sendResource(COMPOSER_ROOT . URI, $ext);
        }

        // Not versioned, so see it it exists
        if (file_exists(APP_ROOT . URI)) {      //  also may send and exit
            View::sendResource(URI, $ext);
        }
        http_response_code(404);           // If we haven't found the request send code 404 not found
        die(1);
    }


    /** This function uses common keys for obtaining the users real IP.
     * We use this for verbo se operating systems support.
     * @link http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/
     * @return mixed|string
     */
    private function IP_FILTER()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    if ($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        \define('IP', $ip);
                        return IP;
                    }
                }
            }
        }   // TODO - log invalid ip addresses
        print 'Could not establish an IP address.';
        die(1);
    }


}





