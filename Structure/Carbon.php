<?php

namespace Carbon;

class Carbon
{
    private static $safelyExit;

    static function Application(array $PHP = []): callable
    {

        ####################  GENERAL CONF  ######################
        error_reporting($PHP['ERROR']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', $PHP['ERROR']['SHOW'] ?? 1);

        date_default_timezone_set($PHP['SITE']['TIMEZONE'] ?? 'America/Chicago');

        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR, false);

        define('CARBON_ROOT', dirname(dirname(__FILE__)) . DS);

        if (!defined('SERVER_ROOT')) define('SERVER_ROOT', CARBON_ROOT);

        define('REPORTS', $PHP['ERROR']['LOCATION'] ?? SERVER_ROOT);

        self::$safelyExit = $safelyExit = function () use (&$safelyExit) {
          return $safelyExit;
        };

        #####################   AUTOLOAD    #######################
        if (!array_key_exists('AUTOLOAD', $PHP) || $PHP['AUTOLOAD']) {

            $PSR4 = include_once CARBON_ROOT . 'Structure/AutoLoad.php';

            if (is_array($PHP['AUTOLOAD'] ?? false))
                foreach ($PHP['AUTOLOAD'] as $name => $path)
                    $PSR4->addNamespace($name, $path);
        }

        #####################   ERRORS    #######################
        Error\ErrorCatcher::getInstance(
            REPORTS,
            $PHP['ERROR']['STORE'] ?? false,    // Store on server
            $PHP['ERROR']['SHOW'] ?? false,     // Print to screen
            $PHP['ERROR']['FULL'] ?? true,
            $PHP['ERROR']['LEVEL']);            // Catch application errors and alerts

        // More cache control is given in the .htaccess File
        Request::setHeader('Cache-Control:  must-revalidate');

        #################   SOCKET AND SYNC    #######################
        if (!defined('SOCKET')) {

            define('SOCKET', false);

            if (($PHP['SOCKET'] ?? false) && getservbyport('8888', 'tcp')) {
                $path = CARBON_ROOT . ($PHP['SOCKET']['WEBSOCKETD'] ?? false ?
                            'Extras' . DS . 'Websocketd.php' :
                            'Structure' . DS . 'Server.php');

                $config = $PHP['SITE']['CONFIG'] ?? SERVER_ROOT;

                $CMD = "/usr/local/bin/websocketd --port=" . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
                    (($PHP['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
                    (($PHP['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey={$PHP['SOCKET']['SSL']['KEY']} --sslcert={$PHP['SOCKET']['SSL']['CERT']} " : ' ') .
                    "php $path " . SERVER_ROOT . ' ' . $config . ' 2>&1';

                Helpers\Fork::become_daemon(function () use ($CMD) {
                    `$CMD`;
                });
            }
        }

        #################  DATABASE  ########################
        if ($PHP['DATABASE'] ?? false) {

            define('DB_DSN', $PHP['DATABASE']['DB_DSN'] ?? '');

            define('DB_USER', $PHP['DATABASE']['DB_USER'] ?? '');

            define('DB_PASS', $PHP['DATABASE']['DB_PASS'] ?? '');

        }

        ##################  VALIDATE URL / URI ##################
        // Even if a request is bad, we need to store the log
        define('LOCAL_SERVER', self::isClientServer());

        if (!LOCAL_SERVER) self::IP_FILTER();

        self::URI_FILTER($PHP['SITE']['URL'] ?? '', $PHP['SITE']['ALLOWED_EXTENSIONS'] ?? '');

        if ($PHP['DATABASE']['INITIAL_SETUP'] ?? false)
            Database::setUp(false);   // redirect = false

        #################  SITE  ########################
        if ($PHP['SITE'] ?? false) {
            define('BOOTSTRAP', SERVER_ROOT . $PHP['SITE']['BOOTSTRAP'] ?? false);          // Routing file

            define('SITE_TITLE', $PHP['SITE']['TITLE'] ?? 'CarbonPHP');                     // Carbon doesnt use

            define('SITE_VERSION', $PHP['SITE']['VERSION'] ?? phpversion());                // printed in the footer

            define('SYSTEM_EMAIL', $PHP['SEND_EMAIL'] ?? '');                               // server email system

            define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                               // I give you options :P
        }

        #######################   Pjax Ajax Refresh  ######################
        // Must return a non empty value
        define('PJAX', (isset($_GET['_pjax']) || (isset($_SERVER["HTTP_X_PJAX"]) && $_SERVER["HTTP_X_PJAX"])));

        // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
        define('AJAX', (PJAX || ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))));

        define('HTTPS', ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? false) == 'https' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));

        define('HTTP', !(HTTPS || SOCKET || AJAX));

        if (HTTP && !($PHP['SITE']['HTTP'] ?? true))
            print '<h1>Failed to switch to https, please contact the server administrator.</h1>' and die;

        if (!AJAX) $_POST = [];  // We only allow post requests through ajax/pjax

        #######################   VIEW             #####################
        define('APP_VIEW', $PHP['VIEW']['VIEW'] ?? '');         // Public Folder
        define('WRAPPER', SERVER_ROOT . APP_VIEW . $PHP['VIEW']['WRAPPER'] ?? '');          // Wrapper

        ########################  Session Management ######################

        if ($PHP['SESSION'] ?? true) {
            if ($PHP['SESSION']['PATH'] ?? false)
                session_save_path($PHP['SESSION']['PATH'] ?? '');   // Manually Set where the Users Session Data is stored

            new Session(IP, ($PHP['SESSION']['REMOTE'] ?? false)); // session start
            $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

            if (is_callable($PHP['SESSION']['CALLBACK'] ?? null))
                Session::updateCallback($PHP['SESSION']['CALLBACK']); // Pull From Database, manage socket ip
        }

        if (is_array($PHP['SERIALIZE'] ?? false))
            forward_static_call_array(['Carbon\Helpers\Serialized', 'start'], $PHP['SERIALIZE']);    // Pull theses from session, and store on shutdown

        ################  Helpful Global Functions ####################
        if (file_exists(CARBON_ROOT . 'Helpers/Application.php') && !@include CARBON_ROOT . 'Helpers/Application.php')
            print "<h1>Your instance of CarbonPHP appears corrupt. Please see CarbonPHP.com for Documentation.</h1>" and die;


        return function () {
            return startApplication();
        };
    }

    /* TODO - Google uses a callback system that uses a .me ending. PATHINFO_EXT.. catches that
     *  This brings up a good point that we shouldnt assume the user has apache installed
     *  Which brings us to...
     *      We must add all the error validation from the htaccess file to the framework
     */

    static function isClientServer()
    {
        if (in_array($_SERVER['REMOTE_ADDR'] ?? [], ['127.0.0.1', 'fe80::1', '::1']) || php_sapi_name() === 'cli-server') {
            define('IP', '127.0.0.1');
            return IP;
        }
        return false;
    }


    static function URI_FILTER($URL, $allowedEXT = ['jpg'])
    {
        if (!empty($URL) && $_SERVER['SERVER_NAME'] != $URL && !LOCAL_SERVER) {
            print IP . '<h1>You appear to be redirected.</h1><h2>Moving to ' . $URL .'</h2>';
            print "<script>window.location.type = $URL";
            return self::$safelyExit;
        }


        View::unVersion($_SERVER['REQUEST_URI']);  // This may exit and send a file

        // It does not matter if this matches, we will take care of that in the next if.
        preg_match("#^(.*\.)($allowedEXT)\?*.*#", $_SERVER['REQUEST_URI'], $matches, PREG_OFFSET_CAPTURE);

        $ext = $matches[2][0] ?? '';  // routes should be null

        define('URI', ltrim(urldecode(parse_url(($matches[1][0] ?? '/') . $ext, PHP_URL_PATH)), '/'), true);

        define('URL',
            (isset($_SERVER['SERVER_NAME']) ?
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') .
                $_SERVER['SERVER_NAME'] . (LOCAL_SERVER ? ':' . $_SERVER['SERVER_PORT'] : '') : null), true);


        define('SITE', url . DS, true);   // http(s)://example.com/

        if ($ext == null) return true;

        if (file_exists(SERVER_ROOT . URI))
            View::sendResource(URI, $ext);

        http_response_code(404);
        die(1);
    }

    // http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/
    static function IP_FILTER()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    if ($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        define('IP', $ip);
                        return IP;
                    }
                }
            }
        }   // TODO - log invalid ip addresses
        print 'Could not establish an IP address.';
        die(1);
    }

}





