<?php

namespace {                                     // Carbon

    use Carbon\Error\ErrorCatcher;              //  Catches development errors
    use Carbon\Error\PublicAlert;               //  Displays alerts nicely
    use Carbon\Entities;                        //  Manages table relations
    use Carbon\Helpers\Files;
    use Carbon\Session;                         //  Automatically stores session
    use Carbon\Request;                         //  Sterilizes input
    use Carbon\Route;                           //  Easily route app
    use Carbon\View;                                     //  Seamlessly include the DOM


    function startApplication($reset = false): void
    {
        if ($reset):                                    // This will always be se in a socket
            if ($reset === true):
                View::getInstance()->forceWrapper = true;
                Request::changeURI('/');         // Dynamically using pjax + headers
            else:
                Request::changeURI($reset);
            endif;
            $reset = true;
            $_POST = [];                      // Only PJAX + AJAX can post
        endif;

        Session::update($reset);              // Check wrapper / session callback

        if (!defined('BOOTSTRAP') || !file_exists(BOOTSTRAP))
            print 'You must define a route in your configuration. Visit CarbonPHP.com for Documentation.' and die;

        include BOOTSTRAP;  // Router
    }

    function highlight($argv, $fileExt = false)
    {
        if ($fileExt == "java") {
            ini_set("highlight.comment", "#008000");
            ini_set("highlight.default", "#000000");
            ini_set("highlight.html", "#808080");
            ini_set("highlight.keyword", "#0000BB; font-weight: bold");
            ini_set("highlight.string", "#DD0000");
        } else if ($fileExt == "html") {
            ini_set("highlight.comment", "green");
            ini_set("highlight.default", "black");
            ini_set("highlight.html", "blue");
            ini_set("highlight.keyword", "black");
            ini_set("highlight.string",  "#0000FF");
        }


        if (file_exists($argv)) {
            $text = file_get_contents($argv);
            $fileExt = pathinfo($argv, PATHINFO_EXTENSION);

            if ($fileExt !== 'php') $text = " <?php " . $text;
        } else $text = " <?php " . $argv;

        $text = highlight_string($text, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text

        $text = substr_replace($text, '', 0, 6);    // this removes the <code>

        $text = preg_replace('#^<span style="[\w\d\s\#">;:]*#', "", $text, 1);  // remove prefix

        $text = (($pos = strpos($text, $needle = '&lt;?php')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</span>')) ?
            substr_replace($text, '', $pos, strlen($needle) ) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</code>')) ?
            substr_replace($text, '', $pos, strlen($needle) ) :
            $text);


        $text = '<span style="overflow-x: scroll">' . $text . '</span>';

        return $text;
    }

    // Wrap a closure in try {} catch ()
    function catchErrors(callable $lambda): callable
    {
        return function (...$argv) use ($lambda) {
            try {
                $argv = call_user_func_array($lambda, $argv);
            } catch (Exception | Error $e) {
                if (!$e instanceof PublicAlert) {
                    ErrorCatcher::generateErrorLog($e);
                    PublicAlert::danger('Developers make mistakes, and you found a big one. We\'ve logged this event and will be investigating soon.'); // TODO - Change what is logged
                }
            } finally {
                Entities::verify();     // Check that all database commit chains have finished successfully, otherwise attempt to remove
                return $argv;
            }
        };
    }

    function CM(string $class, string $method, array &$argv = [])
    {
        $class = ucfirst(strtolower($class));
        $controller = "Controller\\$class";
        $model = "Model\\$class";
        $method = strtolower($method);

        if (!class_exists($controller, true) || !class_exists($model, true))
            throw new Exception("Invalid Class {$class} Passed to MVC");

        $exec = function ($class,$argv) use ($method) {
            return call_user_func_array([new $class, "$method"], (is_array($argv) ? $argv : [$argv]));
        };

        return catchErrors(function () use ($exec, $controller, $model, $argv) {
            if ($argv = $exec($controller, $argv))
                return $exec($model, $argv);
            return $argv;
        })();
    }

    // Controller -(true?)> Model -(final)> View();
    function MVC(string $class, string $method, array &$argv = [])
    {
        CM($class, $method, $argv); // Controller -> Model

        // This could cache or send
        $file = APP_VIEW . "$class/$method";
        $found = $file . (file_exists(SERVER_ROOT . $file . '.php') ? '.php' :
                (file_exists(SERVER_ROOT . $file . '.hbs') ? '.hbs' : ''));

        return View::contents($found);  // View
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
        //Files::storeContent($file, $report);

        print $report . PHP_EOL;

        // Output to browser
        if (AJAX) echo $report;
        else View::getInstance()->bufferedContent = base64_encode($report);
        if ($die) exit(1);
    }
}