<?php

namespace CarbonPHP;

use CarbonPHP\Error\PublicAlert;

use function is_array;
use function defined;
use function call_user_func_array;


class Documentation extends Application
{

    // these are all relative to the /view/ directory
    private static array $adminLTE = [
        'Home' => 'mustache/Documentation/Home.hbs',
        'CarbonPHP' => 'mustache/Documentation/Introduction.hbs',
        'Installation' => 'mustache/Documentation/Installation.hbs',
        'Implementations' => 'mustache/Documentation/Implementations.hbs',
        'Dependencies' => 'mustache/Documentation/Dependencies.hbs',
        'FileStructure' => 'mustache/Documentation/QuickStart/FileStructure.hbs',
        'Environment' => 'mustache/Documentation/QuickStart/Environment.hbs',
        'Options' => 'mustache/Documentation/QuickStart/Options.hbs',
        'Bootstrap' => 'mustache/Documentation/QuickStart/Bootstrap.hbs',
        'Wrapper' => 'mustache/Documentation/QuickStart/Wrapper.hbs',
        'Parallel' => 'mustache/Documentation/QuickStart/ParallelProcessing.hbs',
        'Overview' => 'mustache/Documentation/PHP/Overview.hbs',
        'Entities' => 'mustache/Documentation/PHP/Entities.hbs',
        'Request' => 'mustache/Documentation/PHP/Request.hbs',
        'Route' => 'mustache/Documentation/PHP/Route.hbs',
        'Server' => 'mustache/Documentation/PHP/Server.hbs',
        'Session' => 'mustache/Documentation/PHP/Session.hbs',
        'Singleton' => 'mustache/Documentation/PHP/Singleton.hbs',
        'View' => 'mustache/Documentation/PHP/View.hbs',
        'OSSupport' => 'mustache/Documentation/PlatformSupport.hbs',
        'UpgradeGuide' => 'mustache/Documentation/PlatformSupport.hbs',
        'Support' => 'mustache/Documentation/Support.hbs',
        'License' => 'mustache/Documentation/License.hbs',
        'AdminLTE' => 'mustache/Documentation/AdminLTE.hbs',
        'N00B' => 'mustache/Documentation/N00B.hbs'
    ];


    /**
     *  constructor.
     * @param null $structure
     * @throws PublicAlert
     * way this will throw an error is if you do
     * not define a url using sockets.
     */
    public function __construct($structure = null)
    {
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

    public function defaultRoute()  // Sockets will not execute this
    {
        View::$forceWrapper = true; // this will hard refresh the wrapper

        // v2.0

        return $this->wrap()('mustache/Documentation/Home.hbs');  // don't change how wrap works, I know it looks funny

        // v5.0

        // $this->fullPage()('react/material-dashboard-react-c6/build/index.html');

        // todo - context switch user session

        /*
        if (!$_SESSION['id']):
        else:
            return MVC('user', 'profile');
        endif;
        */
    }


    /** we dont use this return value for anything
     * @param string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        global $json;

        $json['APP_LOCAL'] = APP_LOCAL;

        defined('TEMPLATE') or define('TEMPLATE', 'node_modules/admin-lte/');

        #color
        $this->structure($this->MVC());

        if ($this->match('color', 'color', 'color')()) {
            return true;
        }

        $this->structure($this->wrap());

        ###################################### AdminLTE DOC
        if ($this->match('2.0/UIElements/{AdminLTE?}',
            function ($AdminLTE) {

                $AdminLTE = (new Request())->set($AdminLTE)->word();  // must be validated

                if (!$AdminLTE) {
                    View::$forceWrapper = true;
                    $AdminLTE = 'widgets';
                }

                if (file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                    file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')) {

                    View::$wrapper = SERVER_ROOT . APP_VIEW . 'mustache/AdminLTE/wrapper.hbs';

                    $this->wrap()($path);
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

                    if ($page && array_key_exists($page, self::$adminLTE)) {
                        $this->wrap()(self::$adminLTE[$page]);
                    } else {
                        $this->wrap()(self::$adminLTE['Home']);
                    }
                    return true;
                case '5.0':
                    $this->fullPage()('react/material-dashboard-react-c6/build/index.html');
                    return true;
                default:
                    break;
            }
        }

        if ($this->match('carbon/authenticated', static function () {
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
}