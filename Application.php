<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/25/18
 * Time: 3:20 AM
 */

namespace CarbonPHP;


use CarbonPHP\Error\PublicAlert;

abstract class Application extends Route
{
    abstract public function startApplication($uri = null) : bool;
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
        return catchErrors(function (string $file) {
            return include APP_VIEW . $file;
        });
    }

    public function wrap() : callable
    {
        /**
         * @throws \Mustache_Exception_InvalidArgumentException
         * @param string $file
         * @return bool
         */
        return function (string $file): bool {
            return View::content(APP_VIEW . $file);
        };
    }

    public function MVC() : callable
    {
        return function (string $class, string $method, array &$argv = []) {
            return MVC($class, $method, $argv);         // So I can throw in ->structure($route->MVC())-> anywhere
        };
    }

    public function JSON($selector = '#pjax-content') : callable
    {
        return function ($class, $method, $argv) use ($selector) {
            global $alert, $json;

            if (false === $argv = catchErrors(CM($class, $method, $argv))()) {
                return false;
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

            $json = array_merge($json, [
                'Errors' => $alert,
                'Event' => 'Controller->Model',   // This doesn't do anything.. Its just a mental note when I look at the json's in console (controller->model only)
                'Model' => $argv,
                'Mustache' => DS . $file,
                'Widget' => $selector
            ]);

            header('Content-Type: application/json'); // Send as JSON

            if (false === $json = json_encode($json)) {
                PublicAlert::danger('Json Failed to encode, this may occur when trying to encode binary content.');
                $json = json_encode($json);
            }

            print PHP_EOL . $json . PHP_EOL; // new line ensures it sends through the socket

            return true;
        };
    }
}
