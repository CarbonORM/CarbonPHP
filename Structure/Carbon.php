<?php


// http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/


namespace Carbon;

class Carbon
{
    static function Application(array $PHP = []): callable
    {
        error_reporting($PHP['REPORTING']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', $PHP['REPORTING']['PRINT'] ?? 1);

        date_default_timezone_set($PHP['GENERAL']['TIMEZONE'] ?? 'America/Phoenix');

        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        define('CARBON_ROOT', dirname(dirname(__FILE__)) . DS);

        if (!defined('SERVER_ROOT')) define('SERVER_ROOT', CARBON_ROOT);

        define('REPORTS', $PHP['REPORTING']['SAVE_PATH'] ?? CARBON_ROOT);

        if (!($PHP['GENERAL']['ALLOW_EXTENSION'] ?? false))
            self::URI_FILTER();

        if (($PHP['URL'] ?? false) && URL !== true && $_SERVER['SERVER_NAME'] != $PHP['URL'])
            throw new \Error('Invalid Server Name');


        if (!array_key_exists('AUTOLOAD', $PHP) || $PHP['AUTOLOAD']) {
            $PSR4 = include_once 'AutoLoad.php';   // in case of
            if (is_array($PHP['AUTOLOAD'] ?? false)) {
                foreach ($PHP['AUTOLOAD'] as $name => $path)
                    $PSR4->addNamespace($name, $path);
            }
            $PSR4->addNamespace("Carbon", CARBON_ROOT);
            $PSR4->addNamespace("Carbon", dirname(__FILE__));
        }

        Error\ErrorCatcher::getInstance(
            REPORTS,
            $PHP['REPORTING']['STORE'] ?? false,
            $PHP['REPORTING']['PRINT'] ?? false,  // Print to screen
            $PHP['REPORTING']['FULL'] ?? true);     // Catch application errors and lo

        // More cache control is given in the .htaccess File
        Request::setHeader('Cache-Control: must-revalidate');

        #################   SOCKET AND SYNC    #######################
        if (!defined('SOCKET')) {
            define('SOCKET', false);
            if (($PHP['SOCKET'] ?? false) && getservbyport(($PHP['SOCKET']['PORT'] ?? 8080), 'tcp')) {
                $path = ($PHP['SOCKET']['WEBSOCKETD'] ?? false ? CARBON_ROOT . 'Extras' . DS . 'Websocketd.php' : CARBON_ROOT . 'Structure' . DS . 'Server.php');
                $JSON = json_encode($PHP);
                Helpers\Fork::safe(function () use ($path, $JSON) {
                    shell_exec("$path $JSON");
                    exit(1);
                });
            }
        }

        ################# Application.php Paths ########################
        # Dynamically Find the current url on the server

        if ($PHP['DATABASE'] ?? false) {
            define('DB_HOST', $PHP['DATABASE']['DB_HOST'] ?? '');

            define('DB_NAME', $PHP['DATABASE']['DB_NAME'] ?? '');

            define('DB_USER', $PHP['DATABASE']['DB_USER'] ?? '');

            define('DB_PASS', $PHP['DATABASE']['DB_PASS'] ?? '');

            if ($PHP['DATABASE']['INITIAL_SETUP'] ?? false) Database::setUp(); // can comment out after first run
        }

        define('BOOTSTRAP', $PHP['ROUTES'] ?? false);

        define('SITE_TITLE', $PHP['SITE_TITLE'] ?? 'CarbonPHP');

        define('SITE_VERSION', $PHP['SITE_VERSION'] ?? phpversion());                            // printed in the footer

        define('SYSTEM_EMAIL', $PHP['SYSTEM_EMAIL'] ?? '');                                      // server email system

        define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                                        // I give you options :P

        define('MUSTACHE', $PHP['DIRECTORY']['MUSTACHE'] ?? 'Public/Mustache/');

        define('CONTENT', $PHP['DIRECTORY']['CONTENT'] ?? 'Public/');

        define('VENDOR', $PHP['DIRECTORY']['VENDOR'] ?? 'Data/vendor/');

        define('CONTENT_WRAPPER', $PHP['DIRECTORY']['CONTENT_WRAPPER'] ?? CARBON_ROOT . 'Extras' . DS . 'AdminLTE.php');

        define('MINIFY_CONTENTS', $PHP['MINIFY_CONTENTS'] ?? false);

        #######################   Pjax Ajax Refresh  ######################

        // Must return a non empty value
        define('PJAX', (isset($_GET['_pjax']) || (isset($_SERVER["HTTP_X_PJAX"]) && $_SERVER["HTTP_X_PJAX"])));

        // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
        define('AJAX', (PJAX || ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))));

        define('HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));

        define('HTTP', !(HTTPS || SOCKET || AJAX));

        if (!($PHP['HTTP'] ?? true) && HTTP)
            throw new Error\PublicAlert('Failed to switch to https, please contact the server administrator.');

        if (!AJAX) $_POST = [];  // We only allow post requests through ajax/pjax

        ########################  Session Management ######################
        if ($PHP['SESSION']['SAVE_PATH'] ?? false)
            session_save_path($PHP['SESSION']['SAVE_PATH'] ?? '');   // Manually Set where the Users Session Data is stored

        if ($PHP['SESSION'] ?? false)
            new Session(self::IP_LOOKUP(), ($PHP['SESSION']['STORE_REMOTE'] ?? false)); // session start

        $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

        if (is_callable($PHP['RESTART_CALLBACK'] ?? null))
            Session::updateCallback($PHP['RESTART_CALLBACK']); // Pull From Database, manage socket ip

        if (is_array($PHP['SERIALIZE'] ?? false))
            forward_static_call_array(['Carbon\Helpers\Serialized', 'start'], $PHP['SERIALIZE']);    // Pull theses from session, and store on shutdown


        ################  Helpful Global Functions ####################
        if (file_exists(CARBON_ROOT . 'Helpers/Application.php') && !@include CARBON_ROOT . 'Helpers/Application.php')
            print "Your instance of CarbonPHP appears corrupt. Please see CarbonPHP.com for Documentation." and die;

        return function () {
            startApplication();
        }; // HTTP , AJAX, PJAX.. AKA NOT SOCKET
    }

    static function URI_FILTER()
    {
        if (pathinfo($_SERVER['REQUEST_URI'] ?? '/', PATHINFO_EXTENSION) == null) {
            define('URL', (isset($_SERVER['SERVER_NAME']) ?
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] : null), true);

            define('URI', ltrim(urldecode(parse_url($_SERVER['REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] = '/', PHP_URL_PATH)), '/'), true);

            define('SITE', url . DS, true);                                    // http(s)://example.com/

            return null;
        }
        if ($_SERVER['REQUEST_URI'] == '/robots.txt') {
            echo include CARBON_ROOT . 'Extras/robots.txt';
            exit(1);
        }
        ob_start();
        echo inet_pton($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Go away.' . PHP_EOL;
        echo "\n\n\t\n" . $_SERVER['REQUEST_URI'];
        $report = ob_get_clean();
        $file = fopen(REPORTS . 'url_' . time() . '.log', "a");
        fwrite($file, $report);
        fclose($file);
        exit(0);    // A request has been made to an invalid file
    }

    static function IP_LOOKUP()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

}





