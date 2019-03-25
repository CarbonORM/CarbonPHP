<?php

namespace CarbonPHP;

class C6 extends Application
{
    private static $adminLTE = [
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
     * @throws \CarbonPHP\Error\PublicAlert the only
     * way this will throw an error is if you do
     * not define a url using sockets.
     */
    public function __construct($structure = null)
    {

        global $json;

        if (!\is_array($json)) {
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

        #if ($_SESSION['id']) {
        ##View::$wrapper = SERVER_ROOT . APP_VIEW . 'Layout/logged-in-layout.php';
        #}

    }


    /**  NOTE:
     *  This is actually overriding the CM function in Carbon/Application.
     *  I do this because the namespace changes for other applications not
     *  in C6 context.
     *
     * Stands for Controller -> Model .
     *
     * This will run the controller/$class.$method().
     * If the method returns !empty() the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model.
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed the returned value from model/$class.$method() or false | void
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
        $exec = function &(string $class, array &$argv) use ($method) {
            $argv = \call_user_func_array([new $class, $method], $argv);
            return $argv;
        };

        return function () use ($exec, $controller, $model, &$argv) {
            if (!empty($argv = $exec($controller, $argv))) {
                if (\is_array($argv)) {
                    return $exec($model, $argv);        // array passed
                }
                $controller = [&$argv];                 // allow return by reference
                return $exec($model, $controller);
            }
            return $argv;
        };
    }

    public function defaultRoute()  // Sockets will not execute this
    {
        View::$forceWrapper = true; // this will hard refresh the wrapper

        // v1

        #return $this->wrap()('mustache/Documentation/Home.php');  // don't change how wrap works, I know it looks funny

        // v2

        $this->fullPage()('react/material-dashboard-react-c6/build/index.html');

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
     * @throws \CarbonPHP\Error\PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        global $json;

        \defined('TEMPLATE') or \define('TEMPLATE', 'node_modules/admin-lte/');

        #color
        $this->structure($this->MVC());

        if ($this->match('color', 'color', 'color')()){
            return true;
        }

        ###################################### AdminLTE DOC
        if ($this->match('2.0/UIElements/{AdminLTE?}', function ($AdminLTE) {

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


        if ($version = ($this->uri[0] ?? false)) {

            switch ($version) {
                case '2.0':
                    $this->matched = true;
                    $json['VersionTWO'] = true;
                    $page = $this->uri[1] ?? false;
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

        # $this->structure($this->JSON());

        if ($this->match('carbon/authenticated', function () {
            global $json;

            header('Content-Type: application/json', true, 200); // Send as JSON

            #PublicAlert::JsonAlert('Test', 'Danger', 'success', 'success');

            $json['success'] = !true;

            print json_encode($json);

            return true;
        })()) {
            return true;
        }


        $this->structure($this->wrap());



        return false;
    }
}
