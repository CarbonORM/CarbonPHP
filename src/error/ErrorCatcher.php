<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace CarbonPHP\Error;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Programs\Background;
use CarbonPHP\Tables\Carbon_Reports;
use CarbonPHP\View;
use ReflectionException;
use ReflectionMethod;
use Throwable;

/**
 * TODO - we cant SEND public alerts in this class ever
 *
 *
 * Class ErrorCatcher
 * @package CarbonPHP\Error
 *
 * Provide a global error and exception handler.
 *
 */
class ErrorCatcher
{
    use Background;

    /**
     * @var string TODO - re-setup logs saving to files
     */
    public static string $defaultLocation;
    /**
     * @var bool $printToScreen determine if a generated error log should be shown on the browser.
     * This value can be set using the ["ERROR"]["SHOW"] configuration option
     */
    public static bool $printToScreen;
    /**
     * @var bool
     */
    public static bool $fullReports;
    /**
     * @var bool
     */
    public static bool $storeReport;


    public static string $fileName = '';
    public static string $className = '';
    public static string $methodName = '';

    /**
     * @var int to be used with error_reporting()
     * @link http://php.net/manual/en/function.error-reporting.php
     */
    public static int $level;

    /** Attempt to safely catch errors, output, and public alerts in a closure.
     *
     * @param callable $lambda
     * @return callable
     */
    public static function catchErrors(callable $lambda): callable
    {
        return static function (...$argv) use ($lambda) {
            try {
                ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);
                $argv = \call_user_func_array($lambda, $argv);
            } catch (Throwable $e) {
                if (!$e instanceof PublicAlert) {
                    $message = 'Developers make mistakes, and you found a big one! We\'ve logged this event and will be investigating soon.';
                    if (CarbonPHP::$app_local) {
                        PublicAlert::info($message);
                        PublicAlert::danger(\get_class($e) . ' ' . $e->getMessage());
                    } else {
                        PublicAlert::danger($message);
                    }

                    try {
                        ErrorCatcher::generateLog($e);
                    } catch (Throwable $e) {
                        PublicAlert::danger('Error handling failed.');
                        print $e->getMessage();
                        PublicAlert::info(json_encode($e));
                    }
                }
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $argv = null;
            } finally {
                if (ob_get_status() && ob_get_length()) {
                    $out = ob_get_clean();
                    print <<<END
                                <div class="container">
                                <div class="callout callout-info" style="margin-top: 40px">
                                <h4>You have printed to the screen while within the catchErrors() function!</h4>
                                Don't slip up in your production code!
                                <a href="http://carbonphp.com/">Note: All MVC routes are wrapped in this function. Output to the browser should be done within the view! Use this as a reporting tool only.</a>
                                </div><div class="box"><div class="box-body">$out</div></div></div>
END;

                }
                Database::verify('Check that all database commit chains have finished successfully. You may need to self::commit().');     // Check that all database commit chains have finished successfully, otherwise attempt to remove
                return $argv;
            }
        };
    }

    public static function grabCodeSnippet(): string
    {
        if (self::$className === '' || self::$methodName === '') {
            return '';
        }

        try {
            $func = new ReflectionMethod(self::$className, self::$methodName);
            $comment = $func->getDocComment();

        } catch (ReflectionException $e) {
            return '<div>Failed to load code preview in ErrorCatcher class using ReflectionMethod.<div>';
        }

        $f = $func->getFileName(); // stub says string but may also produce false

        if (empty($f)) {
            return '';
        }

        $start_line = $func->getStartLine() - 1;

        $end_line = $func->getEndLine();

        $length = $end_line - $start_line;

        $source = file_get_contents($f);

        $source = preg_split('/' . PHP_EOL . '/', $source);

        return highlight($comment . PHP_EOL . implode(PHP_EOL, array_slice($source, $start_line, $length)), true);
    }

    /**
     * @param Throwable $throwable
     */
    public static function generateBrowserReportFromError(Throwable $throwable): void
    {
        self::generateBrowserReport(self::generateLog($throwable));
    }

    /**
     * @param array $errorForTemplate
     */
    public static function generateBrowserReport(array $errorForTemplate): void
    {
        static $count = 0;
        $count++;

        if (CarbonPHP::$app_local) {
            self::errorTemplate($errorForTemplate, 500);
        }

        // try resetting to the default page if conditions correct, we've already generated a log and optionally printed
        if (CarbonPHP::$cli || CarbonPHP::$socket || !CarbonPHP::$setupComplete) {
            die(1);
        }

        if ($count > 1) {
            $errorForTemplate['DANGER'] = 'A possible recursive error has occurred in (or at least affecting) your $app->defaultRoute();';
            self::errorTemplate($errorForTemplate, 500);
        }

        // this causes this ::  View::$forceWrapper = true;
        // which breaks the recursive check ?? or does it,
        // we would still need to make it to the view
        // so it only break when we reach the view? todo - test?

        CarbonPHP::resetApplication();  // we're in prod and we want to recover gracefully...

        exit(1);

    }

    /**
     * ErrorCatcher constructor.
     */
    public static function start(): void     // TODO - not this.
    {
        ini_set('display_errors', 1);

        error_reporting(self::$level);

        if (CarbonPHP::$test) {
            return;
        }

        /**
         * if you return true from here it will continue script execution from the point of the error..
         * this is not wanted. Die and Exit are equivalent in execution. I ran across a post once that
         * explained how die should signify a complete error with no resolution, while exit has resolution
         * returning 1 rather than 0 in both cases will signify an error occurred
         * @param array $errorForTemplate
         * @internal param TYPE_NAME $closure
         */


        /**
         * if you return true from here it will continue script execution
         * @param int $errorLevel
         * @param string $errorString
         * @param string $errorFile
         * @param int $errorLine
         * @return bool
         * @link https://www.php.net/manual/en/function.set-error-handler.php
         */
        $error_handler = static function (int $errorLevel, string $errorString, string $errorFile, int $errorLine) {

            $browserOutput = [];

            // refer to link on this one
            /*if (!(error_reporting() & $errorLevel)) {
                // This error code is not included in error_reporting, so let it fall
                // through to the standard PHP error handler
                return false;
            }*/

            switch ($errorLevel) {
                case E_USER_ERROR:
                    $browserOutput["USER ERROR [$errorLevel]"] = $errorString;
                    $browserOutput['FATAL ERROR'] = "Fatal error on line $errorLine in file $errorFile";
                    $browserOutput['PHP'] = PHP_VERSION . ' (' . PHP_OS . ')';
                    break;
                case E_USER_WARNING:
                    // TODO - not report in app local?
                    $browserOutput["WARNING [$errorLevel]"] = $errorString;
                    break;
                case E_USER_NOTICE:
                    $browserOutput["NOTICE [$errorLevel]"] = $errorString;
                    break;
                default:
                    $browserOutput["Unknown error type: [$errorLevel]"] = $errorString;
                    break;
            }

            $browserOutput['FILE'] = $errorFile;
            $browserOutput['LINE'] = (string) $errorLine;

            self::generateLog(null, 'log', $browserOutput);

            !CarbonPHP::$cli && self::generateBrowserReport($browserOutput);

            /* Don't execute PHP internal error handler */
            exit(1);
            # return false;  // todo this will continue execution
        };

        /**
         * if you return true from here it will continue script execution
         *
         * @param Throwable $exception
         * @return bool
         */
        $exception_handler = static function (Throwable $exception){

            $browserOutput['Exception Handler'] = 'CarbonPHP Generated This Report.';

            $browserOutput = self::generateLog($exception);

            !CarbonPHP::$cli && self::generateBrowserReport($browserOutput);    // this will die

            return false;
        };

        set_error_handler($error_handler);
        set_exception_handler($exception_handler);   // takes one argument
    }


    /** Generate a full error log consisting of a stack trace and arguments to each function call
     *
     *  The following functions point to this method by passing the arguments on via redirection.
     *  The required method signatures are different, so it is important that we do not type hint this function.
     *
     *  The options to make this signatures unique are compelling, but not essential.
     *
     *  set_error_handler
     *  set_exception_handler
     *
     * @param Throwable|array|null $e
     * @param string $level
     * @param array $browserOutput
     * @return array
     * @internal param $argv
     * @noinspection ForgottenDebugOutputInspection
     */
    public static function generateLog(Throwable $e = null, string $level = 'log', array &$browserOutput = []): array
    {
        if (ob_get_status()) {
            // Attempt to remove any previous in-progress output buffers
            ob_end_clean();
        }

        $cliOutput = '';

        if ($e instanceof Throwable) {
            $class = \get_class($e);
            $trace = self::generateCallTrace($e);

            if (!$e instanceof PublicAlert) {
                $cliOutput .= '(set_error_handler || set_exception_handler) caught this ( ' . $class . ' ) throwable. #Bubbled up#' . PHP_EOL;
            } else {
                $cliOutput .= 'Public Alert Thrown!' . PHP_EOL;
                $browserOutput['ERROR TYPE'] = 'A Public Alert Was Thrown!';
            }

            $cliOutput .= $e->getMessage() . PHP_EOL;

            $browserOutput[$class] = $e->getMessage();
            $browserOutput['FILE'] = $e->getFile();
            $browserOutput['LINE'] = (string)$e->getLine();
        } else {
            $trace = self::generateCallTrace(null);
        }

        $cliOutput .= $trace;


        if (CarbonPHP::$app_local || self::$printToScreen) { // todo - what the fuck is this supposed to do?
            if (self::$printToScreen) {
                $browserOutput['ERROR LIKE THIS COULD SHOW IN PRODUCTION'] = 'Please be sure $config["ERROR"]["SHOW"] = (bool) is set to CarbonPHP::$app_local';
            } else {
                $browserOutput['THIS WILL NOT BE REPORTED IN PRODUCTION'] = 'To debug an error in a production environment set the following configuration option to true :: $config["ERROR"]["SHOW"] = (bool)';
            }
            $browserOutput['[C6] CARBONPHP'] = 'ErrorCatcher::generateLog';
            $browserOutput['TRACE'] = "<pre>$trace</pre>";
        }

        if (self::$storeReport === true || self::$storeReport === 'file') {
            if (!is_dir(CarbonPHP::$reports) && !mkdir($concurrentDirectory = CarbonPHP::$reports) && !is_dir($concurrentDirectory)) {
                error_log($message = 'Failed storing to custom log. The directory could not be found or created :: ' . CarbonPHP::$reports);
                $browserOutput['[C6] ISSUE'] = $message;
            } else {
                $file = fopen($fileName = CarbonPHP::$reports . 'Log_' . time() . '.log', 'ab');

                if (!\is_resource($file)) {
                    error_log($message = 'Failed writing to log to file. The file could not opened :: ' . $fileName);
                    $browserOutput['[C6] ISSUE'] = $message;
                } else if (!fwrite($file, $cliOutput)) {
                    error_log($message = 'Failed writing to log to file. The file could not be written to :: ' . $fileName);
                    $browserOutput['[C6] ISSUE'] = $message;
                }
                fclose($file);
            }
        }

        if (self::$storeReport === true || self::$storeReport === 'database') {
            if (Database::$initialized) {
                try {
                    if (!Carbon_Reports::Post([
                        Carbon_Reports::LOG_LEVEL => $level,
                        Carbon_Reports::REPORT => $cliOutput,
                        Carbon_Reports::CALL_TRACE => $trace
                    ])) {
                        error_log($message = 'Failed storing log in database. The restful Carbon_Reports table returned false.');
                        $browserOutput['[C6] ISSUE'] = $message;
                        die(1);
                    }
                } catch (PublicAlert $e) {
                    error_log($message = 'Failed storing log in database. The restful Carbon_Reports table through and error :: ' . $e->getMessage());
                    $browserOutput['[C6] ISSUE'] = $message;
                }
            } else {
                error_log($message = 'An error occurred before the database use initialized. This likely means you have no database configurations, or a general configuration issue issues occurred :: ' . $e->getMessage());
                $browserOutput['[C6] ISSUE'] = $message;
                $browserOutput['[C6] STORAGE ISSUE'] = 'The database was not initialized when the error occurred. Storage to the database was not possible.';
            }
        }

        self::colorCode(implode(PHP_EOL, $browserOutput), 'red');

        return $browserOutput; // as array
    }

    /** A simplified back trace for quickly identifying route.
     * reverse array to make steps line up chronologically
     * @link http://php.net/manual/en/function.debug-backtrace.php
     * @param Throwable $e
     * @return string
     */
    public static function generateCallTrace(Throwable $e = null): string
    {
        self::$methodName = self::$className = '';

        ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);     // start a new buffer for saving errors
        if (null === $e) {
            $e = new \Exception();
            $trace = explode("\n", $e->getTraceAsString());
            $args = array_reverse($e->getTrace());
            $trace = array_reverse($trace);
            array_shift($trace); // remove {main}
            array_pop($args); // remove call to this method
            array_pop($args); // remove call to this method
            array_pop($trace); // remove call to this method
            array_pop($trace); // remove call to this method
        } else {
            $trace = explode("\n", $e->getTraceAsString());
            $args = array_reverse($e->getTrace());
            $trace = array_reverse($trace);
            array_shift($trace);       // remove {main} now but will add back iff originated from main
        }

        $length = \count($trace);
        if ($length === 0) {
            print "Found in :: \n\n\t" . $e->getTraceAsString();
        } else {
            $result = array();
            for ($i = 0; $i < $length; $i++) {

                if ('{closure}' !== substr( $args[$i]['function'], -9 )) {
                    self::$className = $args[$i]['class'] ?? '';
                    self::$methodName = $args[$i]['function'] ?? '';
                }

                $result[] = ($i + 1) . ') ' . implode(' (', explode('(', substr($trace[$i], strpos($trace[$i], ' '))))
                    . PHP_EOL . "\t\t\t\t" . (array_key_exists('args', $args[$i]) ? json_encode($args[$i]['args']) : '[]') . PHP_EOL;
            }
            print "\n" . implode("\n", $result);
        }

        return ob_get_clean();
    }


    /**
     * @param array $message
     * @param int $code
     * @return string
     */
    private static function errorTemplate(array $message, int $code = 200): string
    {
        CarbonPHP::$safelyExit = true;  // we're steeling the output entirely

        $cleanErrorReport = '';

        if (CarbonPHP::$app_local) {
            $codePreview = self::grabCodeSnippet();

            foreach ($message as $left => $right) {
                if (!(is_string($left) && is_string($right))) {
                    sortDump($message);                      //  todo - we can do better
                }

                $cleanErrorReport .= $left === 'TRACE' ?
                    <<<DESCRIPTION
<p>> <span>$left</span>: <i>$right</i></p>

DESCRIPTION
                    :
                    <<<DESCRIPTION
<p>> <span>$left</span>: <b style="color: white">"</b><i>$right</i><b style="color: white">"</b></p>
DESCRIPTION;

            }

            if ($codePreview !== '') {
                $cleanErrorReport = "<p>> <span>THROWN NEAR</span>: <pre><code>$codePreview</code></pre></p>$cleanErrorReport";
            }
        } else {
            $cleanErrorReport = <<<PRODUCTION

<p>> <span>ERROR DESCRIPTION</span>: "<i>Something went wrong on our end. We will be investigating soon.</i>"</p>
<p>> <span>ERROR POSSIBLY CAUSED BY</span>: [<b>execute access forbidden, read access forbidden, write access forbidden, ssl required, ssl 128 required, ip address rejected, client certificate required, site access denied, too many users, invalid configuration, password change, mapper denied access, client certificate revoked, directory listing denied, client access licenses exceeded, client certificate is untrusted or invalid, client certificate has expired or is not yet valid, passport logon failed, source access denied, infinite depth is denied, too many requests from the same client ip</b>...]</p>
<p>> <span>SOME PAGES ON THIS SERVER THAT YOU DO HAVE PERMISSION TO ACCESS</span>: [<a href="/">Home Page</a>, <a href="/about">About Us</a>, <a href="/contact">Contact Us</a>, <a href="/Blog">Blog</a>...]</p>


PRODUCTION;

        }

        $message = self::statusText($code);

        $public_root = trim(CarbonPHP::$public_carbon_root, '/');

        print /** @lang HTML */
            <<<DEVOPS
<html lang="en">
<head>
<style>
@import url("https://fonts.googleapis.com/css?family=Share+Tech+Mono|Montserrat:700");

* {
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    vertical-align: baseline;
    box-sizing: border-box;
    color: inherit;
}

html { 
  background: url("$public_root/view/assets/img/Carbon-red.png") no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}

body {
    height: 100vh;
}

h1 {
    font-size: 45vw;
    text-align: center;
    position: fixed;
    width: 100vw;
    z-index: 1;
    color: #ffffff26;
    text-shadow: 0 0 50px rgba(0, 0, 0, 0.07);
    top: 50%;
    transform: translateY(-50%);
    font-family: "Montserrat", monospace;
}

div {
    background-color: rgba(1,1,1,0.9);
    width: 70vw;
    overflow: scroll;
    max-height: 80%;
    position: relative;
    top: 50%;
    transform: translateY(-50%);
    margin: 0 auto;
    padding: 30px 30px 10px;
    box-shadow: 0 0 150px -20px rgba(0, 0, 0, 0.5);
    z-index: 3;
}

P {
    font-family: "Share Tech Mono", monospace;
    color: #f5f5f5;
    margin: 0 0 20px;
    font-size: 17px;
    line-height: 1.2;
}

span {
    color: #f0c674;
}

i {
    color: #8abeb7;
}

div a {
    text-decoration: none;
}

b {
    color: #81a2be;
}

a.avatar {
    position: fixed;
    bottom: 15px;
    right: -100px;
    animation: slide 0.5s 4.5s forwards;
    display: block;
    z-index: 4
}

a.avatar img {
    border-radius: 100%;
    width: 44px;
    border: 2px solid white;
}

@keyframes slide {
    from {
        right: -100px;
        transform: rotate(360deg);
        opacity: 0;
    }
    to {
        right: 15px;
        transform: rotate(0deg);
        opacity: 1;
    }
}

<!-- code opts -->

table {
  background-color:#ffffff;
}

pre {
  background-color:rgba(255,255,255,0.9);
  max-height: 30%;
  overflow:scroll;
  margin:0 0 1em;
  padding:.5em 1em;
}

::-webkit-scrollbar {
  -webkit-appearance: none;
  width: 10px;
}

::-webkit-scrollbar-thumb {
  border-radius: 5px;
  background-color: rgba(230,32,45,0.5);
  -webkit-box-shadow: 0 0 1px rgba(0,255,252,0.5);
}

pre code,
pre .line-number {
  /* Ukuran line-height antara teks di dalam tag <code> dan <span class="line-number"> harus sama! */
  font:normal normal 12px/14px "Courier New",Courier,Monospace;
  color:black;
  display:block;
}

pre .line-number {
  float:left;
  margin:0 1em 0 -1em;
  border-right:1px solid;
  text-align:right;
}

pre .line-number span {
  display:block;
  padding:0 .5em 0 1em;
}

pre .cl {
  display:block;
  clear:both;
}


</style>
<script src="$public_root/node_modules/jquery/dist/jquery.slim.min.js"></script>
<script src="$public_root/node_modules/jquery-backstretch/jquery.backstretch.min.js"></script>
<script>
(function() {
    var pre = document.getElementsByTagName('pre'),
        pl = pre.length;
    for (var i = 0; i < pl; i++) {
        pre[i].innerHTML = '<span class="line-number"></span>' + pre[i].innerHTML + '<span class="cl"></span>';
       
        let regExp = /\\n /;
        
        var num = pre[i].innerHTML.split(regExp).length;
        for (var j = 0; j < num; j++) {
            var line_num = pre[i].getElementsByTagName('span')[0];
            line_num.innerHTML += '<span>' + (j + 1) + '</span>';
        }
    }
})();
</script>
</head>
<body>
<h1>$code</h1>

<div>
<p>> <span>HTTP RESPONSE</span>: "<i>HTTP $code $message</i>"</p>

$cleanErrorReport
</div>

</div>

<a class="avatar" href="/" title="Go Home"><img src="$public_root/view/assets/img/Carbon-white.png"/></a>
</body>
</html>
DEVOPS;

        exit(1);
    }

    private static function statusText(int $code = 0): ?string
    {
        // List of HTTP status codes.
        $statusList = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];

        if (array_key_exists($code, $statusList)) {
            return $statusList[$code];
        }
        return null;
    }


}



