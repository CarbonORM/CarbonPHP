<?php


// http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/


namespace Carbon;

use Carbon\Error\PublicAlert;

class Carbon
{
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
        $file = fopen(SERVER_ROOT . 'Data/Logs/Request/url_' . time() . '.log', "a");
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

    static function Application(array $PHP): callable
    {
        error_reporting($PHP['REPORTING']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', 1);

        date_default_timezone_set($PHP['GENERAL']['TIMEZONE'] ?? 'America/Phoenix');

        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        if (!defined('SERVER_ROOT')) {
            if ($PHP['GENERAL']['ROOT'] ?? false)
                throw new \InvalidArgumentException('A server root must be give. Visit CarbonPHP.com for documentation.');
            define('SERVER_ROOT', $PHP['GENERAL']['ROOT']);
        }

        define('CARBON_ROOT', dirname(dirname(__FILE__)) . DS);

        define('REQUEST_LOG', $PHP['REPORTING']['LOCATION']['REQUEST'] ?? '/' );    // invalid url was sent to server

        if (!$PHP['GENERAL']['ALLOW_EXTENSION'] ?? false) self::URI_FILTER();

        //Mark out for app local testing
        if (($PHP['URL'] ?? false) && URL !== true && $_SERVER['SERVER_NAME'] != $PHP['URL'])
            throw new \Error('Invalid Server Name');

        $PSR4 = new Autoload;

        if ($PHP['AUTOLOAD'] ?? false)
            foreach ($PHP['AUTOLOAD'] as $name => $path)
                $PSR4->addNamespace($name, $path);

        define('FULL_REPORTS', $PHP['REPORTING']['FULL'] ?? true);

        Error\ErrorCatcher::start($PHP['REPORTING']['LOCATION']['ERROR'] ?? CARBON_ROOT, $PHP['REPORTING']['PRINT'] ?? false);    // Catch application errors and lo

        ################# Application.php Paths ########################
        # Dynamically Find the current url on the server

        define('DB_HOST', $PHP['DATABASE']['DB_HOST'] ?? '');

        define('DB_NAME', $PHP['DATABASE']['DB_NAME'] ?? '');

        define('DB_USER', $PHP['DATABASE']['DB_USER'] ?? '');

        define('DB_PASS', $PHP['DATABASE']['DB_PASS'] ?? '');

        if ($PHP['DATABASE']['INITIAL_SETUP'] ?? false) Database::setUp(); // can comment out after first run

        define('BOOTSTRAP', $PHP['ROUTES'] ?? false);

        define('ERROR_LOG', $PHP['REPORTING']['LOCATION']['ERROR'] ?? '/' );

        define('SORTED_LOG', $PHP['REPORTING']['LOCATION']['SORTED'] ?? '/' );      // we ran the funtion sort or sortdump

        define('SITE_TITLE', $PHP['SITE_TITLE'] ?? 'CarbonPHP');

        define('SITE_VERSION', $PHP['SITE_VERSION'] ?? phpversion());                            // printed in the footer

        define('SYSTEM_EMAIL', $PHP['SYSTEM_EMAIL'] ?? '');                                      // server email system

        define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                                        // I give you options :P

        define('MUSTACHE', $PHP['DIRECTORY']['MUSTACHE'] ?? false);

        define('CONTENT', $PHP['DIRECTORY']['CONTENT'] ?? false);

        define('VENDOR', $PHP['DIRECTORY']['VENDOR'] ?? 'Data/vendor/');

        define('TEMPLATE', $PHP['DIRECTORY']['TEMPLATE'] ?? false);                     // Path to the template for public use i.e. relative path for .css includes

        define('VENDOR_ROOT', SERVER_ROOT . $PHP['DIRECTORY']['VENDOR'] ?? false);

        define('TEMPLATE_ROOT', SERVER_ROOT . $PHP['DIRECTORY']['TEMPLATE'] ?? false);

        define('CONTENT_ROOT', SERVER_ROOT . $PHP['DIRECTORY']['CONTENT'] ?? false);

        define('CONTENT_WRAPPER', SERVER_ROOT . $PHP['DIRECTORY']['CONTENT_WRAPPER'] ?? false);

        define('WRAPPING_REQUIRES_LOGIN', $PHP['WRAPPING_REQUIRES_LOGIN'] ?? false);    // I use the same headers every where

        define('MINIFY_CONTENTS', $PHP['MINIFY_CONTENTS'] ?? false);

        if (!defined('SOCKET')) {
            define('SOCKET', false);
            if (($PHP['SOCKET'] ?? false) && 'webcache' !== getservbyport(($PHP['SOCKET']['PORT'] ?? 8080), 'tcp'))
                Helpers\Fork::safe(function () {
                    shell_exec('server.php');           // when threading is supported ill do more, until then I wait
                    exit(1);
                });
        }
        #######################   Pjax Ajax Refresh  ######################

        // Must return a non empty value
        define('PJAX', (isset($_GET['_pjax']) || (isset($_SERVER["HTTP_X_PJAX"]) && $_SERVER["HTTP_X_PJAX"])));

        // (PJAX == true) return required, else (!PJAX && AJAX) return optional (socket valid)
        define('AJAX', (PJAX || ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))));

        if (!AJAX) $_POST = [];  // We only allow post requests through ajax/pjax

        define('HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));

        define('HTTP', !(HTTPS || SOCKET || AJAX));

        if (!($PHP['HTTP'] ?? true) && HTTP) throw new PublicAlert('Failed to switch to https, please contact the server administrator.');

        if ($PHP['SESSION']['SAVE_PATH'] ?? false)
            session_save_path($PHP['SESSION']['SAVE_PATH'] ?? '');   // Manually Set where the Users Session Data is stored

        new Session(self::IP_LOOKUP(), ($PHP['SESSION']['STORE_REMOTE'] ?? false));

        $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

        define('ERROR_LOG', SERVER_ROOT . 'Data/Logs/Error/Log_' . $_SESSION['id'] . '_' . time() . '.log');

        Session::updateCallback($PHP['RESTART_CALLBACK'] ?? null); // Pull From Database, manage socket ip

        if (is_array($PHP['SERIALIZE'] ?? false))
            forward_static_call_array(['Carbon\Helpers\Serialized', 'start'], $PHP['SERIALIZE']);    // Pull theses from session, and store on shutdown

        ################  Application Structure ###############

        require_once CARBON_ROOT . 'Helpers/Application.php';

        return function () {
            startApplication();
        }; // HTTP , AJAX, PJAX.. AKA NOT SOCKET
    }
}





