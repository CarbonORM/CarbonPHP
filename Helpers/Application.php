<?php

namespace {                                     // Carbon

    use Carbon\Error\ErrorCatcher;              //  Catches development errors
    use Carbon\Error\PublicAlert;               //  Displays alerts nicely
    use Carbon\Entities;                        //  Manages table relations
    use Carbon\Helpers\Files;
    use Carbon\Session;                         //  Automatically stores session
    use Carbon\Request;                         //  Sterilizes input
    use Carbon\Route;                           //  Easily route app
    use Carbon\View;                            //  Seamlessly include the DOM

    function startApplication($reset = false): void
    {
        if ($reset):                                          // This will always be se in a socket
            Request::changeURI($reset === true ? '/' : $reset);         // Dynamically using pjax + headers
            $_POST = [];                                           // Only PJAX + AJAX can post
        endif;

        Session::update($reset = ($reset === true));                             // Check wrapper / session callback

        View::getInstance();                                // Send wrapper

        if (!defined('BOOTSTRAP') || !file_exists(BOOTSTRAP))
            print 'You must define a route in your configuration. Visit CarbonPHP.com for Documentation.' and die;

        include BOOTSTRAP;                            // Router
    }

    function uri(): Route
    {
        call_user_func_array(array($route = Route::getInstance(), "match"), func_get_args());
        return $route;
    }

    // Wrap a closure in try {} catch ()
    function catchErrors(callable $lambda): callable
    {
        return function (...$argv) use ($lambda) {
            try {
                call_user_func_array($lambda, $argv);
            } catch (Exception | Error $e) {
                if (!$e instanceof PublicAlert){
                    ErrorCatcher::generateErrorLog($e);
                    PublicAlert::danger('Developers make mistakes, and you found a big one. We\'ve logged this event and will be investigating soon.'); // TODO - Change what is logged
                }
            } finally {
                return Entities::verify();     // Check that all database commit chains have finished successfully, otherwise attempt to remove
            }
        };
    }

    // Controller -(true?)> Model -(final)> View();
    function MVC(string $class, string $method, array &$argv = [])
    {
        $class = ucfirst(strtolower($class));
        $controller = "Controller\\$class";
        $model = "Model\\$class";
        $method = strtolower($method);

        if (!class_exists($controller, true) || !class_exists($model, true))
            throw new Exception("Invalid Class {$class} Passed to MVC");

        $run = function ($class, $argv) use ($method) {
            return call_user_func_array([new $class, "$method"], (is_array($argv) ? $argv : [$argv]));
        };

        catchErrors(function () use ($run, $controller, $model, $argv) {
            if ($argv = $run($controller, $argv))
                $run($model, $argv);
        })();

        // This could cache or send
        $file = SERVER_ROOT . "Public/$class/$method";
        $file .= (file_exists($file . '.php') ? '.php' :
            (file_exists($file . '.hbs') ? '.hbs'
                : ''));

        View::contents($file);  // but will exit(1);
    }

    // Sends Json array to browser
    function Mustache(string $path, array $options = [])
    {
        catchErrors(function ($path, $options = array()) {
            global $json;   // It's best to leave the array empty before this function call, but the option is left open..

            $file = MUSTACHE . "$path.php";
            if (file_exists($file) && is_array($file = include $file))
                $json = array_merge(
                    is_array($json) ? $json : [], $file);

            $json = array_merge(
                is_array($json) ? $json : [],            // Easy Error Catching
                array('UID' => $_SESSION['id'],
                    'Mustache' => SITE . "Application/View/Mustache/$path.hbs"));

            $json = array_merge(
                (is_array($json) ? $json : []),               // Easy Error Catching - dont mess up
                (is_array($options) ? $options : []));       // Options Trumps all

            print json_encode($json) . PHP_EOL;
        })($path, $options);
    }

    function alert($string = "Stay woke.")
    {
        static $count = 0;
        print "<script>alert('( #" . ++$count . " )  $string')</script>";
    }

    // http://php.net/manual/en/debugger.php
    function console_log($data)
    {
        ob_start();
        echo $data;
        $report = ob_get_clean();
        $file = fopen(REPORTS . '/Log_' . time() . '.log', "a");
        fwrite($file, $report);
        fclose($file);
        echo '<script>console.log(\'' . json_encode($data) . '\')</script>';
    }

    function dump(...$argv)
    {
        echo '<pre>';
        var_dump(count($argv) == 1 ? array_shift($argv) : $argv);
        echo '</pre>';
    }

    function sortDump($mixed, $fullReport = false, $die = true)
    {
        // Notify or error
        alert(__FUNCTION__);

        // Generate Report
        ob_start();
        echo '####################### VAR DUMP ########################<br><pre>';
        var_dump($mixed);
        echo '</pre><br><br><br>';
        if ($fullReport) {
            echo '####################### MIXED DUMP ########################<br><pre>';
            $mixed = (is_array($mixed) && count($mixed) == 1 ? array_pop($mixed) : $mixed);
            echo '<pre>';
            debug_zval_dump($mixed ?: $GLOBALS);
            echo '</pre><br><br>';
            echo '####################### BACK TRACE ########################<br><pre>';
            var_dump(debug_backtrace());
            echo '</pre>';
        };

        $report = ob_get_clean();
        $file = REPORTS . 'Dumped/Sort_' . time() . '.log';
        Files::storeContent($file, $report);

        print $report . PHP_EOL;

        // Output to browser
        if (AJAX) echo $report;
        else View::getInstance()->bufferedContent = base64_encode( $report );
        if ($die) exit(1);
    }
}