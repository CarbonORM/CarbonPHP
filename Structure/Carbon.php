<?php

namespace Carbon;

use Carbon\Error\PublicAlert;

class Carbon
{
    static function Application(array $PHP): callable
    {
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        if (!defined('SERVER_ROOT')){
            if ($PHP['GENERAL']['ROOT'] ?? false)
                throw new \InvalidArgumentException('A server root must be give. Visit CarbonPHP.com for documentation.');
            define('SERVER_ROOT', $PHP['GENERAL']['ROOT']);
        }

        define('CARBON_ROOT', dirname(dirname(__FILE__)) . DS);

        ################  Filter Malicious Requests  #################
        if (!$PHP['GENERAL']['ALLOW_EXTENSION'] ?? false && pathinfo($_SERVER['REQUEST_URI'] ?? '/', PATHINFO_EXTENSION) != null) {
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

        ##############################   Autoloading   #############################
        # PSR4 Autoloader, with common case first added for namespace = currentDir #
        # Composer Autoloader                                                      #
        ############################################################################
        $thankGod = new Autoload;   // dynamically include classes via directory based namespace naming conventions (& const)

        if ($PHP['AUTOLOAD'] ?? false)
            foreach ($PHP['AUTOLOAD'] as $name => $path)
                $thankGod->addNamespace($name, $path);

        ##################    Reporting   #####################
        date_default_timezone_set($PHP['GENERAL']['TIMEZONE'] ?? 'America/Phoenix');

        error_reporting($PHP['REPORTING']['LEVEL'] ?? E_ALL | E_STRICT);

        ini_set('display_errors', 1);

        define('FULL_REPORTS', $PHP['REPORTING']['FULL'] ?? true);

        Error\ErrorCatcher::start($PHP['REPORTING']['LOCATION'] ?? CARBON_ROOT, $PHP['REPORTING']['PRINT'] ?? false);    // Catch application errors and lo


        ################    Database    ####################
        /**
         * The following constants are used by the Database
         * Which uses a MYSQL database with a PDO wrapper
         *
         * @constant DB_HOST The databases Host i.e. localhost
         * @constant DB_NAME The name of the location on the database
         * @constant DB_USER The user name if required
         * @constant DB_PASS The users password if applicable
         *
         */

        define('DB_HOST', $PHP['DATABASE']['DB_HOST'] ?? '');

        define('DB_NAME', $PHP['DATABASE']['DB_NAME'] ?? '');

        define('DB_USER', $PHP['DATABASE']['DB_USER'] ?? '');

        define('DB_PASS', $PHP['DATABASE']['DB_PASS'] ?? '');

        if ($PHP['DATABASE']['INITIAL_SETUP'] ?? false) Database::setUp(); // can comment out after first run

        ################## Basic Information  ##################
        define('SITE_TITLE', $PHP['SITE_TITLE'] ?? 'CarbonPHP');

        define('SITE_VERSION', $PHP['SITE_VERSION'] ?? phpversion());                            // printed in the footer

        define('SYSTEM_EMAIL', $PHP['SYSTEM_EMAIL'] ?? '');                                      // server email system

        define('REPLY_EMAIL', $PHP['REPLY_EMAIL'] ?? '');                                        // I give you options :P

        ################# Application.php Paths ########################
        # Dynamically Find the current url on the server
        define('URL', (isset($_SERVER['SERVER_NAME']) ?
            ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] : null), true);


        define('URI', ltrim(urldecode(parse_url($_SERVER['REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] = '/', PHP_URL_PATH)), '/'), true);

        /** Mark out for app local testing
         * if (URL !== true && $_SERVER['SERVER_NAME'] != $PHP['URL']) {
         * throw new Error('Invalid Server Name');
         * die(1);
         * }
         ***/

        define('SITE', url . DS, true);                                    // http(s)://example.com/

        define('MUSTACHE',  $PHP['DIRECTORY']['MUSTACHE'] ?? false);

        define('BOOTSTRAP', $PHP['DIRECTORY']['ROUTES'] ?? false);

        define('CONTENT',   $PHP['DIRECTORY']['CONTENT'] ?? false);

        define('VENDOR',    $PHP['DIRECTORY']['VENDOR'] ?? 'Data/vendor/');

        define('TEMPLATE',  $PHP['DIRECTORY']['TEMPLATE'] ?? false);                     // Path to the template for public use i.e. relative path for .css includes

        define('VENDOR_ROOT',   SERVER_ROOT . $PHP['DIRECTORY']['VENDOR'] ?? false);

        define('TEMPLATE_ROOT', SERVER_ROOT . $PHP['DIRECTORY']['TEMPLATE'] ?? false);

        define('CONTENT_ROOT',  SERVER_ROOT . $PHP['DIRECTORY']['CONTENT'] ?? false);

        define('CONTENT_WRAPPER', SERVER_ROOT . $PHP['DIRECTORY']['CONTENT_WRAPPER'] ?? false);

        define('WRAPPING_REQUIRES_LOGIN', $PHP['WRAPPING_REQUIRES_LOGIN'] ?? false);    // I use the same headers every where

        ######################  Up the render speed ? ####################
        define('MINIFY_CONTENTS', $PHP['MINIFY_CONTENTS']);

        #######################       Socket      ########################
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


        define('AJAX_OUT', $PHP['AJAX_OUT'] ?? false);

        // We only allow post requests through ajax/pjax
        if (!AJAX) $_POST = [];

        // This should return the template
        define('HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));

        // This will not return data
        define('HTTP', !(HTTPS || SOCKET || AJAX));

        // If we're using https our htaccess should handle url resolution before this point
        if (!$PHP['HTTP'] && HTTP) throw new PublicAlert('Failed to switch to https, please contact the server administrator.');

        ################    Session     ####################

        if ($PHP['SESSION']['SAVE_PATH'] ?? false)
        session_save_path(        $PHP['SESSION']['SAVE_PATH'] ?? '' );   // Manually Set where the Users Session Data is stored

        if ($PHP['SESSION']['STORE_REMOTE'] ?? false)
            new Session();

        $_SESSION['id'] = array_key_exists('id', $_SESSION ?? []) ? $_SESSION['id'] : false;

        define('ERROR_LOG', SERVER_ROOT . 'Data/Logs/Error/Log_' . $_SESSION['id'] . '_' . time() . '.log');

        Session::updateCallback($PHP['RESTART_CALLBACK']); // Pull From Database, manage socket ip

        forward_static_call_array(['Carbon\Helpers\Serialized', 'start'], $PHP['SERIALIZE']);    // Pull theses from session, and store on shutdown

        ################  Application Structure ###############

        require_once CARBON_ROOT . 'Helpers/Application.php';

        return function () {
            startApplication();
        }; // HTTP , AJAX, PJAX.. AKA NOT SOCKET
    }
}


#################   Development   ######################
/**
 * This will run the application in an MVC style.
 * The classes will be defined in the the first param while
 * the method inside the second param.
 *
 * The flow of the application is
 *
 *  `Controller` -> `Model` -> `View`
 *
 * The controller should work with the request class to help validate all data
 * received from the user. If all data is preceived to be valid (by type) then
 * we should return true or a value which will evaluate to true with (==) double equal,
 * else false.
 *
 * The model will only be fetched and method executed if the previous controller
 * returns true (or mixed). The mixed value will be passed to the models constructor and method.
 * This will communicate with the database (if applicable) to
 * further validate, update, or fetch information.
 *
 * If no errors have been raised then the view will be executed. We created a self
 * stored instance in the index to handle the request throughout the application. This
 * allows us to call the view->contents() method statically even though it is defined as
 * a private method.
 *
 * Singletons functionality in the view ensures constructor is called and reset
 * when needed & in conjunction to any sterilized data also present.
 *
 * We keep the contents function private and call this way to allow frontend developers
 * to include global valuables using `$this->`
 *
 * @param $class string Name of the class within the controller and model folder
 *  and a folder name in the CONTENT_ROOT
 *
 * @param $method string Name of the method in the above parameter, and file name
 *  for the template.
 *
 * @return void The View->contents() procedure will exit(1)
 */


##################   DEV Tools   #################
// This will cleanly print the var_dump function and kill the execution of the application

/**
 * This will cleanly print the var_dump function and kill the execution of the application.
 *
 * This function is for development purposes. The function accepts one value to printed on
 * the browser. If the value passes is empty or null the function will print all variables
 * in the $GLOBAL scope.
 *
 * @param mixed $mixed Will be run throught the var_dump function.
 *
 */


/**
 * This ports the javascript alert function to work in PHP. Note output is sent to the browser
 *
 * @param string $string will be placed in the javascript alert function.
 *
 * @return null
 */


