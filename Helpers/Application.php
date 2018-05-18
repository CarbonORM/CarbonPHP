<?php

namespace {                                     // This runs the following code in the global scope

    use /** @noinspection PhpUndefinedNamespaceInspection */
        App\Bootstrap;
    use CarbonPHP\Application;
    use CarbonPHP\Error\ErrorCatcher;              //  Catches development errors
    use CarbonPHP\Error\PublicAlert;               //  Displays alerts nicely
    use CarbonPHP\Entities;                        //  Manages table relations
    use CarbonPHP\Helpers\Files;
    use CarbonPHP\Session;                         //  Automatically stores session
    use CarbonPHP\Request;                         //  Sterilizes input
    use CarbonPHP\Route;                           //  Easily route app
    use CarbonPHP\View;                            //  Seamlessly include the DOM


    /** Start application will start the registered bootstrap from line one.
     *  If a string is given
     *
     * @param $reset
     *  If a string is passed to reset then the uri of the website will be changed
     *  to the value of reset.
     *
     *  If ($reset == true) then set our uri to '/' and all variables cached using
     *  the serialized class will be reset. The the outer html will be sent and
     *  our session callback will be executed.
     *
     *  The session callback is be set in carbon's configuration
     * @link
     *
     * @return bool Returns the response from the bootstrap as a bool
     */
    function startApplication($reset = false): bool
    {
        static $application;

        if (null === $application) {
            $application = new (BOOTSTRAP);

            if (!$application instanceof Application) {
                print 'Your application must extend the CarbonPHP/Application::class' . PHP_EOL;
                return false;
            }
        }

        if ($reset):                                    // This will always be se in a socket
            if ($reset === true):
                View::$forceWrapper = true;
                Request::changeURI($uri = '/');         // Dynamically using pjax + headers
            else:
                Request::changeURI($uri = $reset);
            endif;
            $reset = true;
            $_POST = [];                      // Only PJAX + AJAX can post
        endif;

        Session::update($reset);              // Check wrapper / session callback

        return $application->startApplication($uri ?? null);  // Routing file
    }

    /** This extends the PHP's built-in highlight function to highlight
     *  other file types. Currently java and html are custom colored.
     *  All languages should, to some degree, benefit from this.
     * @link http://php.net/manual/en/function.highlight-string.php
     * @param $argv - if a filepath is given load it from memory,
     *  otherwise highlight the string provided as code
     *
     * @param bool $fileExt
     * @return string -- the text highlighted and converted to html
     */
    function highlight($argv, $fileExt = false): string
    {
        if ($fileExt === 'java') {
            ini_set('highlight.comment', '#008000');
            ini_set('highlight.default', '#000000');
            ini_set('highlight.html', '#808080');
            ini_set('highlight.keyword', '#0000BB; font-weight: bold');
            ini_set('highlight.string', '#DD0000');
        } else if ($fileExt === 'html') {
            ini_set('highlight.comment', 'orange');
            ini_set('highlight.default', 'green');
            ini_set('highlight.html', 'blue');
            ini_set('highlight.keyword', 'black');
            ini_set('highlight.string', '#0000FF');
        }

        if (file_exists($argv)) {
            $text = file_get_contents($argv);
            $fileExt = $fileExt ?: pathinfo($argv, PATHINFO_EXTENSION);

            if ($fileExt !== 'php') {
                $text = ' <?php ' . $text;
            }
        } else {
            $text = ' <?php ' . $argv;
        }

        $text = highlight_string($text, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text

        $text = substr_replace($text, '', 0, 6);    // this removes the <code>

        $text = preg_replace('#^<span style="[\w\s\#">;:]*#', '', $text, 1);  // remove prefix

        $text = (($pos = strpos($text, $needle = '&lt;?php')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</span>')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</code>')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = '<span style="overflow-x: scroll">' . $text . '</span>';

        return $text;
    }

    /** Attempt to safely catch errors and public alerts in a closure
     * @param callable $lambda
     * @return callable
     */
    function catchErrors(callable $lambda): callable
    {
        return function (...$argv) use ($lambda) {
            try {
                $argv = \call_user_func_array($lambda, $argv);
            } catch (Exception | Error $e) {
                if (!$e instanceof PublicAlert) {
                    ErrorCatcher::generateLog($e);
                    PublicAlert::danger('Developers make mistakes, and you found a big one. We\'ve logged this event and will be investigating soon.'); // TODO - Change what is logged
                }
                $argv = null;
            } finally {
                if (ob_get_status()) {
                    ob_end_clean(); // Attempt to remove any previous in-progress output buffers
                }
                Entities::verify();     // Check that all database commit chains have finished successfully, otherwise attempt to remove
                return $argv;
            }
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
     * @return mixed          the returned value from model/$class.$method() or false | void
     * @throws Exception
     */
    function CM(string $class, string &$method, array &$argv = [])
    {
        $class = ucfirst(strtolower($class));   // Prevent malformed class names
        $controller = "Controller\\$class";     // add namespace for autoloader
        $model = "Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists

        if (!class_exists($controller) || !class_exists($model)) {
            throw new InvalidArgumentException("Invalid Class ({$class}) Passed to MVC");
        }
        // the array $argv will be passed as arguments to the method requested, see link above
        $exec = function &(string $class, array &$argv) use ($method) {
            $argv = \call_user_func_array([new $class, "$method"], $argv);
            return $argv;
        };

        return (function () use ($exec, $controller, $model, &$argv) {          // TODO - this is where catch Errors is / goes
            if (!empty($argv = $exec($controller, $argv))) {
                if (\is_array($argv)) {
                    return $exec($model, $argv);        // array passed
                }
                $controller = [&$argv];                 // allow return by reference
                return $exec($model, $controller);
            }
            return $argv;
        })();
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
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed          the returned value from model/$class.$method() or false | void
     * @throws Exception
     */
    function MVC(string $class, string $method, array &$argv = [])
    {
        if (false === CM($class, $method, $argv)) {  // Controller -> Model
            return false;
        }

        // This could cache or send
        $file = APP_VIEW . "$class/$method";

        if (!file_exists(APP_ROOT . $file . ($ext = '.php')) && !file_exists(APP_ROOT . $file . ($ext = '.hbs'))) {
            $ext = '';
        }
        return View::content($file . $ext);  // View
    }

    /** Ports the javascript alert function in php.
     * @param string $string
     */
    function alert($string = 'Stay woke')
    {
        static $count = 0;
        ++$count;
        print "<script>alert('(#$count)  $string')</script>\n";
    }

    /** Prots the javascript console.log() function
     * @link http://php.net/manual/en/debugger.php
     * @param string $data data to be sent to the browsers console
     */
    function console_log($data)
    {
        ob_start();
        echo $data;
        $report = ob_get_clean();
        $file = fopen(REPORTS . '/Log_' . time() . '.log', 'ab');
        fwrite($file, $report);
        fclose($file);
        print '<script>console.log(\'' . json_encode($data) . '\')</script>' . PHP_EOL;
    }

    /** Output all parameters given neatly to the screen and continue execution.
     * @param array ...$argv variable length parameters stored as array
     */
    function dump(...$argv)
    {
        echo '<pre>';
        var_dump(\count($argv) === 1 ? array_shift($argv) : $argv);
        echo '</pre>';
    }

    /** This is dump()'s big brother, a better dump per say.
     * By default, this outputs the value passed in and exits our execution.
     * This is convent when dealing with requests that would otherwise refresh the session.
     *
     * @param mixed $mixed the variable to output.
     * You can output multiple variables by wrapping them in an array
     *       [$var, $var2, $anotherVar]
     *
     * @param bool $fullReport this outputs a backtrace and zvalues
     * @link http://php.net/manual/en/function.debug-backtrace.php
     *
     * From personal experience you should not worry about Z-values, as it is almost
     * never ever the issue. I'm 99.9% sure of this, but if you don't trust me you
     * should read this full manual page
     *
     * @link http://php.net/manual/en/internals2.php                 -- the hackers guide
     * @link http://php.net/manual/en/function.debug-zval-dump.php
     *
     * @param bool $die -
     */
    function sortDump($mixed, $fullReport = false, $die = true)
    {
        // Notify that sort dump was executed
        alert(__FUNCTION__);

        // Generate Report
        ob_start();
        print '####################### VAR DUMP ########################<br><pre>';
        var_dump($mixed);
        print '</pre><br><br><br>';
        if ($fullReport) {
            echo '####################### MIXED DUMP ########################<br><pre>';
            $mixed = (\is_array($mixed) && \count($mixed) === 1 ? array_pop($mixed) : $mixed);
            echo '<pre>';
            debug_zval_dump($mixed ?: $GLOBALS);
            echo '</pre><br><br>';
            echo '####################### BACK TRACE ########################<br><pre>';
            var_dump(debug_backtrace());
            echo '</pre>';
        }

        $report = ob_get_clean();

        // TODO - re-create a store to file configuration option
        #$file = REPORTS . 'Dumped/Sort_' . time() . '.log';
        #Files::storeContent($file, $report);

        print $report . PHP_EOL;

        // Output to browser
        if (AJAX) {
            print $report;
        } else {
            View::$bufferedContent = base64_encode($report);
        }
        if ($die) {
            exit(1);
        }
    }


    /** Command Line Interface
     * @param array $argv
     * @param array $PHP
     */
    function CLI(array $PHP)
    {
        $argv = $_SERVER['argv'] ?? ['index.php', null];

        switch ($argv[1] ?? null) {
            case 'go':
                $CMD = '/usr/bin/websocketd --port=' . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
                    (($PHP['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
                    (($PHP['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey={$PHP['SOCKET']['SSL']['KEY']} --sslcert={$PHP['SOCKET']['SSL']['CERT']} " : ' ') .
                    'php ' . CARBON_ROOT . 'Extras' . DS . 'Websocketd.php ' . APP_ROOT . ' ' . ($PHP['SITE']['CONFIG'] ?? APP_ROOT) . ' 2>&1';
                `$CMD`;
                break;
            case 'php':
                $CMD = 'php ' . CARBON_ROOT . 'Server.php ' . ($PHP['SITE']['CONFIG'] ?? APP_ROOT);
                `$CMD`;
                break;
            case 'help':
            default:
                print <<<END
\n\n
\t.$argv[0] [ :path? ] [ :command [ :args? ] ]\n
\thelp\t - display a list of options
\tphp\t  - start a HTTP 5 web socket server written in PHP
\tgo\t   - start a HTTP 5 web socket server written in Google Go
\n\n
END;

        }
    }
}