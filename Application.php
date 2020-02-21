<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/25/18
 * Time: 3:20 AM
 */

namespace CarbonPHP;


use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;

abstract class Application extends Route
{
    abstract public function startApplication(string $uri) : bool;
    /**
     * App constructor. If no uri is set than
     * the Route constructor will execute the
     * defaultRoute method defined below.
     * @return callable
     * @throws \Mustache_Exception_InvalidArgumentException
     * @throws \CarbonPHP\Error\PublicAlert
     */

    public function fullPage() : callable
    {
        return function (string $file) {
            $this->matched = true;
            return include APP_VIEW . $file;
        };
    }

    public function wrap() : callable
    {
        /**
         * @throws \Mustache_Exception_InvalidArgumentException
         * @param string $file
         * @return bool
         */
        return function (string $file): bool {
            $this->matched = true;
            return View::content(APP_VIEW . $file);
        };
    }


    /**Stands for Controller -> Model .
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
        $controller = "Controller\\$class";     // add namespace for autoloader
        $model = "Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists
        if (!class_exists($controller)) {
            print "Invalid Controller ({$controller}) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        if (!class_exists($model)) {
            print "Invalid Model ({$model}) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        return static function () use ($controller, $model, $method, $argv) {

            $argv = \call_user_func_array([new $controller, $method], $argv);

            if ($argv !== null && $argv !== false) {
                return \call_user_func_array([new $model, $method], is_array($argv) ? $argv : [$argv]);
            }

            return $argv;   // false will indicate (in mvc) that the view should not run
        };
    }

    /** Stands for Controller -> Model -> View
     *
     * This will run the controller/$class.$method().
     * If the method returns true the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model. Finally the View will
     * be executed. The file should be in the APP_VIEW directory (set in config)
     * with the following naming convention
     *
     *  APP_VIEW / $class / $method . (php | hbs)  - We accept handlebar templates.
     *
     * The view will be processed server-side and returned
     *
     * @link http://php.net/manual/en/function.call-user-func-array.php
     * @link http://php.net/manual/en/language.oop5.late-static-bindings.php
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return bool - false if the view, controller, or model returned false.
     */
    private static $APPLICATION;
    private static $CLASS;
    private static $METHOD;

    public static function ControllerModelView(string $class, string $method, array &$argv = []): bool
    {
        self::$CLASS = $class;
        self::$METHOD = $method;

        if (self::$APPLICATION === null) {
            self::$APPLICATION = $recurse = 0;
        } else {
            $recurse = self::$APPLICATION;
        }

        // keep track of which recursive iteration this is.
        self::$APPLICATION++;

        /* I use a different CM function in carbonphp because the namespace needed is CarbonPHP/Controller
         * using the keyword static rather than self allows us to call the child implementation
         */
        if (false === ErrorCatcher::catchErrors(static::CM($class, $method, $argv))()) {  // Controller -> Model
            return false;
        }

        // This is so we can clear our stack quickly if recursively called.
        // This helps with error reporting
        if ($recurse !== 0) {
            return true;
        }

        // This could cache or send
        $file = APP_VIEW . strtolower(self::$CLASS) . '/' . strtolower(self::$METHOD);

        if (!file_exists(APP_ROOT . $file . ($ext = '.php')) && !file_exists(APP_ROOT . $file . ($ext = '.hbs'))) {
            $ext = '';
        }

        return View::content($file . $ext);  // View
    }

    public function MVC() : callable
    {
        return function (string $class, string $method, array &$argv = []) {
            $this->matched = true;
            // So I can throw in ->structure($route->MVC())-> anywhere
            // and still have static variables in the mvc function,
            // because it needs to run as a callable..
            // so if ControllerModelView's code was in this closure
            // the code would not work bc static // runtime memory
            return self::ControllerModelView($class, $method, $argv);
        };
    }

    public function JSON($selector = '#pjax-content') : callable
    {
        return function ($class, $method, $argv) use ($selector) {
            global $alert, $json;

            $this->matched = true;

            if (false === $return = ErrorCatcher::catchErrors(static::CM($class, $method, $argv))()) {
                return null;
            }

            if (!file_exists(APP_ROOT . $file = (APP_VIEW . $class . DS . $method . '.hbs'))) {
                $alert = 'Mustache Template Not Found ' . $file;
            }

            if (!\is_array($alert)) {
                $alert = [];
            }

            if (!\is_array($json)){
                $json = [];
            }

            $json = array_merge_recursive($json, [
                'errors' => $alert,
                'controller->model' => $class,
                'method' => $method,
                'argv' => $argv,
                'return' => $return,
                'widget' => $selector
            ]);

            header('Content-Type: application/json', true, 200); // Send as JSON

            if (false === $json = json_encode($json)) {
                PublicAlert::danger('Json Failed to encode, this may occur when trying to encode binary content.');
                $json = json_encode($json);
            }

            SOCKET and $json = PHP_EOL . $json . PHP_EOL;

            print $json; // new line ensures it sends through the socket

            return true;
        };
    }
}
