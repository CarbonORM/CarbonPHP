<?php

namespace Carbon;
use Carbon\Helpers\Serialized;

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
class Carbon
{
    /**
     * @var bool $safelyExit determines if start application should be executed
     * when running the invoke magic method.
     */
    private $safelyExit = false;


    /** If safely exit is false run startApplication(), otherwise return $safelyExit
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * @return bool
     */
    public function __invoke()
    {
        return $this->safelyExit ?: startApplication();
    }

    /**
     * @type $PHP = [
     *       'AUTOLOAD' => []                       // Provide PSR-4 namespace roots
     *       'SITE' => [
     *           'URL' => string '',                                  // Server Url name you do not need to chane in remote development
     *           'ROOT' => string '__FILE__',                         // This was defined in our ../index.php
     *           'ALLOWED_EXTENSIONS' => string 'jpg|png',            // File ending in these extensions will be served
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
     */
    public function __construct(array $PHP = [])
    {
        ####################  GENERAL CONF  ######################
        error_reporting($PHP['ERROR']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', $PHP['ERROR']['SHOW'] ?? true);

        date_default_timezone_set($PHP['SITE']['TIMEZONE'] ?? 'America/Chicago');

        if (!\defined('DS')) {
            \define('DS', DIRECTORY_SEPARATOR);
        }

        \define('CARBON_ROOT', \dirname(__FILE__, 2) . DS);

        if (!\defined('SERVER_ROOT')) {
            \define('SERVER_ROOT', CARBON_ROOT);
        }

        \define('REPORTS', $PHP['ERROR']['LOCATION'] ?? SERVER_ROOT);

        #####################   AUTOLOAD    #######################
        if (!array_key_exists('AUTOLOAD', $PHP) || $PHP['AUTOLOAD']) {

            $PSR4 = include CARBON_ROOT . 'Structure/AutoLoad.php';

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
        Error\ErrorCatcher::$defaultLocation = REPORTS . 'Log_' . ($_SESSION['id'] ?? '') . '_' . time() . '.log';
        Error\ErrorCatcher::$fullReports = $PHP['ERROR']['STORE'] ?? false;
        Error\ErrorCatcher::$printToScreen = $PHP['ERROR']['SHOW'] ?? true;
        Error\ErrorCatcher::$storeReport = $PHP['ERROR']['FULL'] ?? true;
        Error\ErrorCatcher::$level = $PHP['ERROR']['LEVEL'] ?? ' E_ALL | E_STRICT';
        Error\ErrorCatcher::start();            // Catch application errors and alerts

        // More cache control is given in the .htaccess File
        Request::setHeader('Cache-Control:  must-revalidate');

        #################   SOCKET AND SYNC    #######################
        if (!\defined('SOCKET')) {

            \define('SOCKET', false);

            if (($PHP['SOCKET'] ?? false) && getservbyport($PHP['SOCKET']['PORT'] ?? 8888, 'tcp')) {
                $path = CARBON_ROOT . ($PHP['SOCKET']['WEBSOCKETD'] ?? false ?
                            'Extras' . DS . 'Websocketd.php' :
                            'Structure' . DS . 'Server.php');

                $config = $PHP['SITE']['CONFIG'] ?? SERVER_ROOT;

                $CMD = '/usr/local/bin/websocketd --port=' . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
                    (($PHP['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
                    (($PHP['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey={$PHP['SOCKET']['SSL']['KEY']} --sslcert={$PHP['SOCKET']['SSL']['CERT']} " : ' ') .
                    "php $path " . SERVER_ROOT . ' ' . $config . ' 2>&1';

                Helpers\Fork::become_daemon(function () use ($CMD) {`$CMD`;});
            }
        }

        #################  DATABASE  ########################
        if ($PHP['DATABASE'] ?? false) {
            Database::$dsn = $PHP['DATABASE']['DB_DSN'] ?? '';
            Database::$username = $PHP['DATABASE']['DB_USER'] ?? '';
            Database::$password = $PHP['DATABASE']['DB_PASS'] ?? '';
            Database::$setup = $PHP['DATABASE']['DB_BUILD'] ?? '';
        }

        ##################  VALIDATE URL / URI ##################
        // Even if a request is bad, we need to store the log
        \define('LOCAL_SERVER', $this->isClientServer());

        if (!LOCAL_SERVER) {
            $this->IP_FILTER();
        }

        $this->URI_FILTER($PHP['SITE']['URL'] ?? '', $PHP['SITE']['ALLOWED_EXTENSIONS'] ?? '');

        if ($PHP['DATABASE']['REBUILD'] ?? false) {
            Database::setUp(false);   // redirect = false
        }
        #################  SITE  ########################
        if ($PHP['SITE'] ?? false) {
            \define('BOOTSTRAP', SERVER_ROOT . $PHP['SITE']['BOOTSTRAP'] ?? '');          // Routing file

            \define('SITE_TITLE', $PHP['SITE']['TITLE'] ?? 'CarbonPHP');                     // Carbon doesnt use

            \define('SITE_VERSION', $PHP['SITE']['VERSION'] ?? PHP_VERSION);                // printed in the footer

            \define('SYSTEM_EMAIL', $PHP['SEND_EMAIL'] ?? '');                               // server email system

            \define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                               // I give you options :P
        }

        #######################   Pjax Ajax Refresh  ######################
        // Must return a non empty value
        \define('PJAX', isset($_GET['_pjax']) || (isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX']));

        // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
        \define('AJAX', PJAX || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));

        \define('HTTPS', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? false) === 'https' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        \define('HTTP', !(HTTPS || SOCKET || AJAX));

        if (HTTP && !($PHP['SITE']['HTTP'] ?? true)) {
            print '<h1>Failed to switch to https, please contact the server administrator.</h1>' and die;
        }

        if (!AJAX) {
            $_POST = []; // We only allow post requests through ajax/pjax
        }

        #######################   VIEW             #####################
        \define('APP_VIEW', $PHP['VIEW']['VIEW'] ?? '/');         // Public Folder

        View::$wrapper = SERVER_ROOT . APP_VIEW . $PHP['VIEW']['WRAPPER'] ?? '';

        ########################  Session Management ######################

        if ($PHP['SESSION'] ?? true) {
            if ($PHP['SESSION']['PATH'] ?? false) {
                session_save_path($PHP['SESSION']['PATH'] ?? '');   // Manually Set where the Users Session Data is stored
            }

            new Session(IP, ($PHP['SESSION']['REMOTE'] ?? false)); // session start

            $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

            if (\is_callable($PHP['SESSION']['CALLBACK'] ?? null)) {
                Session::updateCallback($PHP['SESSION']['CALLBACK']); // Pull From Database, manage socket ip
            }
        }

        if (\is_array($PHP['SERIALIZE'] ?? false)) {
            forward_static_call_array([Serialized::class, 'start'], $PHP['SERIALIZE']);    // Pull theses from session, and store on shutdown
        }
        ################  Helpful Global Functions ####################
        if (file_exists(CARBON_ROOT . 'Helpers/Application.php') && !@include CARBON_ROOT . 'Helpers/Application.php') {
            print '<h1>Your instance of CarbonPHP appears corrupt. Please see CarbonPHP.com for Documentation.</h1>';
            die(1);
        }
    }


    /**
     * returns 127.0.0.1 if you are using a local development server,
     * otherwise returns false.
     * @return bool|mixed|string
     */
    private function isClientServer()
    {
        if (PHP_SAPI === 'cli-server' ||  PHP_SAPI === 'cli' || \in_array($_SERVER['REMOTE_ADDR'] ?? [], ['127.0.0.1', 'fe80::1', '::1'], false)) {
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
     * on a local development server, then redirect to url provided.
     * @param string $allowedEXT is a list separated by the logical | or
     * operator denoting allowed file extensions.
     * @return bool
     */
    private function URI_FILTER(string $URL, string $allowedEXT = 'jpg|png') : bool
    {
        if (!empty($URL=strtolower($URL)) && $_SERVER['SERVER_NAME'] !== $URL && !LOCAL_SERVER) {
            print IP . '<h1>You appear to be lost.</h1><h2>Moving to ' . $URL . '</h2>';
            print "<script>window.location.type = $URL</script>";
            $this->safelyExit = true;
        }

        \define('URI', trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)), '/'), true);

        \define('URL',
            (isset($_SERVER['SERVER_NAME']) ?
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ? 'https://' : 'http://') .
                $_SERVER['SERVER_NAME'] . (LOCAL_SERVER ? ':' . $_SERVER['SERVER_PORT'] : '') : null), true);

        \define('SITE', url . DS, true);   // http(s)://example.com/


        // It does not matter if this matches, we will take care of that in the next if.
        preg_match("#^(.*\.)($allowedEXT)\?*.*#",URI, $matches, PREG_OFFSET_CAPTURE);
        // So if the request has an extension that's not allowed we ignore it and keep processing as a valid route

        $ext = $matches[2][0] ?? '';    // routes should be null

        if (empty($ext)) {              // We're requesting a file
            return true;
        }
        // Look for versioning
        View::unVersion($_SERVER['REQUEST_URI']);           // This may exit and send a file

        // Not versioned, so see it it exists
        if (file_exists(SERVER_ROOT . URI)) {      //  also may send and exit
            View::sendResource(URI, $ext);
        }
        http_response_code(404);              // If we haven't found the request send code 404
        die(1);
    }


    /** This function uses common keys for obtaining the users real IP.
     * We use this for verbose operating systems support.
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





