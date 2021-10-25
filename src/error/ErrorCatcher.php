<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace CarbonPHP\Error;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\Background;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Reports;
use ReflectionException;
use ReflectionMethod;
use Throwable;

/**
 * This is really an Error and Exception handler
 *
 * Class ErrorCatcher
 * @package CarbonPHP\Error
 *
 * Provide a global error and exception handler.
 *
 */
class ErrorCatcher
{
    use Background, ColorCode;

    public const LOG_ARRAY = 'LOG_ARRAY';

    public const HTML_ERROR_PAGE = 'HTML_ERROR_PAGE';

    public const STORED_HTML_LOG_FILE_PATH = 'STORED_HTML_LOG_FILE_PATH';

    // todo - defaultLocation this does nothing.
    public static ?string $defaultLocation = null;
    /**
     * @var bool $printToScreen determine if a generated error log should be shown on the browser.
     * This value can be set using the ["ERROR"]["SHOW"] configuration option
     */
    public static bool $printToScreen = true;

    public static bool $bypass_standard_PHP_error_handler = true;

    /**
     * @var bool
     */
    public static bool $storeReport = false;

    public static string $fileName = '';

    public static string $className = '';

    public static string $methodName = '';

    // The following two should be of type   ?Closure|array
    // @link https://www.php.net/manual/en/function.set-error-handler.php
    public static $old_error_handler = null;

    public static $old_exception_handler = null;

    public static ?int $old_error_level = null;

    public static bool $attemptRestartAfterError = false;

    public static string $errorTemplate = <<<DEVOPS
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
              background: url("{{carbon_public_root}}/view/assets/img/Carbon-teal.png") no-repeat center center fixed; 
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
            
            pre {
              background-color:rgba(18,18,18,0.9);
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
              background-color: rgba(0,255,252,0.5);
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
            <script src="{{carbon_public_root}}/node_modules/jquery/dist/jquery.slim.min.js"></script>
            <script src="{{carbon_public_root}}/node_modules/jquery-backstretch/jquery.backstretch.min.js"></script>
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
            <h1>{{{code}}}</h1>
            <div>
            <p>> <span>HTTP RESPONSE</span>: "<i>HTTP {{{code}}} {{{statusText}}}</i>"</p>
            <p>> <span>{{{actual_message}}}}</span>: "<i>{{{actual_message_body}}}</i>"</p>
            {{{cleanErrorReport}}}
            </div>
            </div>
            <a class="avatar" href="/" title="Go Home"><img src="{{carbon_public_root}}/view/assets/img/Carbon-white.png"/></a>
            </body>
            </html>
            DEVOPS;

    /**
     * @var int to be used with error_reporting()
     * @link http://php.net/manual/en/function.error-reporting.php
     */
    public static int $level = E_ALL | E_STRICT;

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

                Database::verify();     // Check that all database commit chains have finished successfully, otherwise attempt to remove

                return $argv;
            }
        };
    }

    public static function checkCreateLogFile(string &$message) : void {

        $directory = dirname(self::$defaultLocation);

        if (false === is_dir($directory) && (false === mkdir($directory, 0755, true) || false === is_dir($directory))) {

            throw new PublicAlert('The directory ('.$directory.') for ErrorCatcher::$defaultLocation (' . self::$defaultLocation . ') does not exist and could not be created');

        }

        if (false === touch(ErrorCatcher::$defaultLocation)) {

            $message .= "\n\nCould not create file (" . ErrorCatcher::$defaultLocation . ') as it does not exist on the system. All folders appear correct. Please create the directories required to store logs correctly!' . PHP_EOL;

        }

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

        if (false === function_exists('highlight')) {

            include_once CarbonPHP::CARBON_ROOT . 'Functions.php';

        }

        return highlight($comment . PHP_EOL . implode(PHP_EOL, array_slice($source, $start_line, $length)), true);
    }


    public static function exitAndSendBasedOnRequested(array $json, string $html) {

        $_SERVER["CONTENT_TYPE"] ??= '';

        if (false === headers_sent()) {

            $code = $json['CODE'] ?? false;

            if (false === $code || false === is_numeric($code)) {

                $code = 500;

            }

            $_SERVER["CONTENT_TYPE"] =
                false === strpos($_SERVER["CONTENT_TYPE"], 'application/json')
                    ? 'text/html'
                    : 'application/json';

            $contentType = 'Content-Type: ' . $_SERVER["CONTENT_TYPE"];

            header($contentType, true, $code);

        }

        if ($_SERVER["CONTENT_TYPE"] === 'application/json') {

            $json = json_encode($json, JSON_PRETTY_PRINT);

            if (false === $json) {

                sortDump(['FAILED TO JSON ENCODE THE FOLLOWING (Retrying with sortDump) ::', $json]);

            }

            print $json;

        } else {

            print $html;

        }

        self::closeStdoutStderrAndExit(1);

    }



    /**
     * This terminates!
     * @param array $errorForTemplate
     */
    public static function generateBrowserReport(array $errorForTemplate, bool $return = false): string
    {
        static $count = 0, $error_page = '';

        if (1 < $count++) {

            $errorForTemplate['DANGER'] = 'A possible recursive error has occurred in (or at least affecting) your $app->defaultRoute();';

        }

        $errorForTemplate['CODE'] ??= '0';

        $code = ($errorForTemplate['CODE'] === '0') ? 500 : $errorForTemplate['CODE'];

        if (is_string($code)) {

            if (is_numeric($code)) {

                $code = (int)$code;

            } else {

                $code = 500;

            }

        }

        $error_page = self::errorTemplate($errorForTemplate, $code);

        if ($return) {

            return $error_page;

        }

        // try resetting to the default page if conditions correct, we've already generated a log and optionally printed
        if (!CarbonPHP::$setupComplete) {

            self::exitAndSendBasedOnRequested($errorForTemplate, $error_page);

        }

        // this causes this ::  View::$forceWrapper = true;
        // which breaks the recursive check ?? or does it,
        // we would still need to make it to the view
        // so it only break when we reach the view? todo - test? -- found error in wp finally, we need a default route check here.. or at least a .... startapplication check application === null? __destruct check

        if (CarbonPHP::$application !== null && self::$attemptRestartAfterError && $count === 1) {

            CarbonPHP::resetApplication();  // we're in prod and we want to recover gracefully...

            self::closeStdoutStderrAndExit(1);

        }

        self::exitAndSendBasedOnRequested($errorForTemplate, $error_page);

    }

    /**
     * ErrorCatcher constructor.
     * @return int
     */
    public static function start(): int
    {

        ini_set('display_errors', 1);

        self::$old_error_level = error_reporting(self::$level);

        if (CarbonPHP::$test) {

            return self::$old_error_level;

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
         * php.net/manual/en/function.set-error-handler.php
         *      return true from here it will continue script execution
         *      return false and PHP will use it's built in error handler
         *
         * @param int $errorLevel
         * @param string $errorString
         * @param string $errorFile
         * @param int $errorLine
         * @return bool
         * @link https://www.php.net/manual/en/function.set-error-handler.php
         * @link https://www.php.net/manual/en/function.set-exception-handler.phpd
         */
        $error_handler = static function (int $errorLevel, string $errorString, string $errorFile, int $errorLine) {

            static $errorsHandled = 0;

            static $fatalError = false;

            $errorsHandled++;

            $browserOutput = [];


            ColorCode::colorCode('The Global Error (set_error_handler) Handler has been invoked.', iColorCode::CYAN);

            // refer to link on this one
            /*if (!(error_reporting() & $errorLevel)) {
                // This error code is not included in error_reporting, so let it fall
                // through to the standard PHP error handler
                return false;
            }*/

            $browserOutput['PHP'] = PHP_VERSION . ' (' . PHP_OS . ')';

            switch ($errorLevel) {
                case E_ALL:
                    $browserOutput['FATAL ERROR'] ??= 'E_ALL';
                case E_ERROR:
                    $browserOutput['FATAL ERROR'] ??= 'E_ERROR';
                case E_RECOVERABLE_ERROR:
                    $browserOutput['FATAL ERROR'] ??= 'E_RECOVERABLE_ERROR';
                case E_PARSE:
                    $browserOutput['FATAL ERROR'] ??= 'E_PARSE';
                case E_STRICT:
                    $browserOutput['FATAL ERROR'] ??= 'E_STRICT';
                case E_DEPRECATED:
                    $browserOutput['FATAL ERROR'] ??= 'E_DEPRECATED';
                case E_COMPILE_ERROR:
                    $browserOutput['FATAL ERROR'] ??= 'E_COMPILE_ERROR';
                case E_USER_ERROR:
                    $browserOutput['FATAL ERROR'] ??= 'E_USER_ERROR';
                default:
                    $browserOutput['FATAL ERROR'] ??= "Unknown error type ($errorLevel) with message ($errorString)";

                    $fatalError = true;

                    $browserOutput["ERROR MESSAGE"] = $errorString;

                    break;

                case E_WARNING:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_WARNING';
                case E_NOTICE:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_NOTICE';
                case E_CORE_WARNING:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_CORE_WARNING';
                case E_COMPILE_WARNING:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_COMPILE_WARNING';
                case E_USER_WARNING:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_USER_WARNING';
                case E_USER_NOTICE:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_USER_NOTICE';
                case E_USER_DEPRECATED:
                    $browserOutput['RECOVERABLE WARNING'] ??= 'E_USER_DEPRECATED';

                    $browserOutput["WARNING MESSAGE"] = $errorString;

                    break;
            }

            $browserOutput['FILE'] = $errorFile;

            $browserOutput['LINE'] = (string)$errorLine;

            self::generateLog(null, false === $fatalError, (string)$errorLevel, $browserOutput);

            return self::$bypass_standard_PHP_error_handler;  // todo this will continue execution for set_error_handler

        };

        /**
         * if you return true from here it will continue script execution
         *
         * @param Throwable $exception
         * @return bool
         */
        $exception_handler = static function (Throwable $exception) {

            ColorCode::colorCode('The Global Exception (set_error_handler) Handler has been invoked.', iColorCode::CYAN);

            $browserOutput = ['Exception Handler' => 'CarbonPHP Generated This Report.'];

            self::generateLog($exception, false, null, $browserOutput, iColorCode::YELLOW);

            return false;   // php will exit after this

        };

        // the return hint on set_error_handler is incorrect as array may also be returned.
        // @link https://www.php.net/manual/en/function.set-error-handler.php
        self::$old_error_handler = set_error_handler($error_handler);

        self::$old_exception_handler = set_exception_handler($exception_handler);   // takes one argument

        return self::$old_error_level;

    }

    public static function stop(bool $ignoreRedundantStops = false): void
    {

        if (false === $ignoreRedundantStops && self::$old_error_level === null) {
            /** trigger_error is just a warning here
             * @link https://www.php.net/manual/en/language.exceptions.php
             * @link https://stackoverflow.com/questions/1095860/is-there-any-way-to-show-or-throw-a-php-warning
             **/
            trigger_error('Looks like you are trying to run ErrorCatcher::stop() before running start. This is not supported.', E_USER_WARNING);

            return;
        }

        error_reporting(self::$old_error_level);

        $error_handler = self::$old_error_handler;

        $exception_handler = self::$old_exception_handler;

        set_error_handler($error_handler);

        set_exception_handler($exception_handler);   // takes one argument

    }


    /** Generate a full error log consisting of a stack trace and arguments to each function call
     *
     *  The following functions point to this method by passing the arguments on via redirection.
     *  The required method signatures are different, so it is important that we do not type hint this function.
     *
     *  The options to make this signatures unique are compelling, but df essential.
     *
     *  set_error_handler
     *  set_exception_handler
     *
     * @param Throwable|array|null $e
     * @param string $level
     * @param array $log_array
     * @param string $color
     * @return array
     * @internal param $argv
     * @noinspection ForgottenDebugOutputInspection
     */
    public static function generateLog(Throwable $e = null, bool $return = false, string $level = null, array &$log_array = [], string $color = iColorCode::RED): array
    {

        if (null === $level) {

            $level = 'log';

        }

        if (!CarbonPHP::$test && ob_get_status()) {

            // Attempt to remove any previous in-progress output buffers
            ob_end_clean();

        }

        $cliOutput = '';

        if ($e instanceof Throwable) {

            $class = get_class($e);

            [$traceCLI, $traceHTML] = self::generateCallTrace($e);

            $log_array = [
                    $class => $e->getMessage()
                ] + $log_array;

            if (!$e instanceof PublicAlert) {

                $cliOutput .= '(set_error_handler || set_exception_handler) caught this ( ' . $class . ' ) throwable. #Bubbled up#' . PHP_EOL;

            } else {

                $cliOutput .= 'Public Alert Thrown!' . PHP_EOL;

                $log_array['ERROR TYPE'] = 'A Public Alert Was Thrown!';

            }

            $cliOutput .= $e->getMessage() . PHP_EOL;

            $log_array['FILE'] = $e->getFile();

            $log_array['LINE'] = (string)$e->getLine();

            $log_array['CODE'] = (string)$e->getCode();

        } else {

            [$traceCLI, $traceHTML] = self::generateCallTrace(null);

        }

        $log_array['[C6] CARBONPHP'] = 'ErrorCatcher::generateLog';

        $pre = static fn ($code) => static function ($serialize = true) use ($code) {

            if (false === $serialize) {

                return $code;

            }

            if (CarbonPHP::$cli || $_SERVER["CONTENT_TYPE"] === 'application/json') {

                return $code;

            }

            if (false === is_string($code)) {

                try {

                    $code = json_encode($code, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: serialize($code);

                } catch (Throwable $e) {

                    ColorCode::colorCode('The trace failed to be json_encoded or serialized.', iColorCode::RED);

                    ColorCode::colorCode($e->getMessage(), iColorCode::RED);

                    ColorCode::colorCode('Attempting with print_r and sortDump...', iColorCode::RED);

                    $code = print_r($code, true);

                    ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

                    sortDump($trace, true, false);

                    $code .= ob_get_clean();

                }

            }

            return "<pre>$code</pre>";

        };

        $log_array['TRACE'] = [];

        $json = $GLOBALS['json'] ??= [];

        $log_array['$GLOBALS[\'json\']'] = $pre($json);

        $debugBacktrace = debug_backtrace();

        $log_array['debug_backtrace()'] = $pre($debugBacktrace);

        if (self::$storeReport === true || self::$storeReport === 'database') {

            if (Database::$carbonDatabaseInitialized) {

                try {

                    $reports = Rest::getDynamicRestClass(Reports::class);

                    if (false === $reports::post([
                            $reports::LOG_LEVEL => $level,
                            $reports::REPORT => $traceHTML,
                            $reports::CALL_TRACE => $html_error_log
                        ])) {

                        error_log($message = 'Failed storing log in database. The restful Reports table returned false.');

                        $log_array['[C6] ISSUE'] = $message;

                        die(1);

                    }

                } catch (PublicAlert $e) {

                    error_log($message = 'Failed storing log in database. The restful Carbon_Reports table through and error :: ' . $e->getMessage());

                    $log_array['[C6] ISSUE'] = $message;

                }

            } else {

                error_log($message = 'An error occurred before the database use initialized. This likely means you have no database configurations, or a general configuration issue issues occurred :: ' . $e->getMessage());

                $log_array['[C6] ISSUE'] = $message;

                $log_array['[C6] STORAGE ISSUE'] = 'The database was not initialized when the error occurred. Storage to the database was not possible.';

            }

        }

        $messageRepeat = $log_array["WARNING MESSAGE"] ?? $log_array['ERROR MESSAGE'] ?? false;

        if (false !== $messageRepeat) {

            $log_array['MESSAGE AGAIN'] = $messageRepeat;

        }

        $cliOutputArray = $log_array;

        $cliOutputArray['TRACE'] = $traceCLI;

        $log_array['TRACE'] = '<pre>' . $traceHTML . '</pre>' ;

        foreach ($log_array as $key => $val) {

            if (is_callable($val)) {

                $cliOutputArray[$key] = $val(false);

                $log_array[$key] = $val();

            }

        }

        $message = json_encode($cliOutputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        self::colorCode($message, $color);

        $html_error_log = self::generateBrowserReport($log_array, true);

        if (self::$storeReport === true || self::$storeReport === 'file') {

            $log_file = 'logs/ErrorCatcherReport.' . ($_SESSION['id'] ?? 'guest'). '.' . session_id() . '.' . microtime(true) . '.' . getmypid() . '.html';

            $log_saved = true;

            if (false === file_put_contents(CarbonPHP::$app_root . $log_file, $html_error_log)) {

                ColorCode::colorCode("Failed to store html log using file_put_contents. Arguments :: ($log_file) ($html_error_log);", iColorCode::RED);

            }

        }

        if (false === $return) {

            if (false === CarbonPHP::$cli) {    // we have alreay logged

                if (headers_sent()) {

                    sortDump($log_file, false, false);

                    print <<<REDIRECT
                        <meta http-equiv="refresh" content="0; URL=/$log_file" />
                        <script>window.location.replace("/$log_file");</script>
                        REDIRECT;

                } else {

                    self::exitAndSendBasedOnRequested($log_array, $html_error_log);

                }

            }

            self::closeStdoutStderrAndExit(1);

        }

        return [
            self::LOG_ARRAY => $log_array,
            self::HTML_ERROR_PAGE => $html_error_log,
            self::STORED_HTML_LOG_FILE_PATH => $log_file ?? 'N/A'
        ];

    }

    /** A simplified back trace for quickly identifying route.
     * reverse array to make steps line up chronologically
     * @link http://php.net/manual/en/function.debug-backtrace.php
     * @param Throwable $e
     * @return string|array
     */
    protected static function generateCallTrace(Throwable $e = null) : array
    {

        $_SERVER["CONTENT_TYPE"] ??= '';

        self::$methodName = self::$className = '';

        if (false === CarbonPHP::$cli) {
            ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE
                | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);     // start a new buffer for saving errors
        }

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

            $trace = "Found in :: \n\n\t" . $e->getTraceAsString();

            $result[] = $trace;

        } else {

            $resultHTML = array();

            $resultCLI = array();

            for ($i = 0; $i < $length; $i++) {

                if ('{closure}' !== substr($args[$i]['function'], -9)) {

                    self::$className = $args[$i]['class'] ?? '';

                    self::$methodName = $args[$i]['function'] ?? '';

                }

                $line_one = implode(' (', explode('(', substr($trace[$i], strpos($trace[$i], ' '))));

                $line_one = explode(' ', $line_one);

                // get line number
                if (array_key_exists(1, $line_one) && is_string($line_one[1])) {

                    $line_number = $line_one[1];

                    if ('(' === $line_number[0] && ')' === $line_number[-1]) {

                        $line_one[0] .= $line_one[1];

                        $line_one[1] = '';

                    }

                }

                $call_number = $i + 1;

                $line_one = $call_number . ') ' . implode(' ', $line_one);

                $line_two = '[]';

                if (array_key_exists('args', $args[$i])) {

                    $line_two_html = json_encode($args[$i]['args'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                    $line_two_cli = CarbonPHP::$cli || $_SERVER["CONTENT_TYPE"] === 'application/json'
                        ? $args[$i]['args'] : $line_two_html;

                }

                $resultCLI[] = [
                        "TRACE $call_number" => $line_one,
                        'ARGUMENTS' => $line_two_cli
                    ];

                $resultHTML[] = $line_one . "\n\t\t\t\t" . $line_two_html . PHP_EOL;

            }

        }

        return [ $resultCLI,   PHP_EOL . implode(PHP_EOL, $resultHTML ) . PHP_EOL ];
    }

    /**
     * @param array $message
     * @param int $code
     * @return string
     */
    protected static function errorTemplate(array $message, int $code = 200): string
    {

        $cleanErrorReport = '';

        $actual_message = array_key_first($message);

        if (CarbonPHP::$app_local || self::$printToScreen) {
            $codePreview = self::grabCodeSnippet();

            // todo - log invalid files?
            if ($codePreview === '' && ($message['FILE'] ?? false) && file_exists($message['FILE'])
                && ($message['LINE'] ?? false) && is_numeric($message['LINE'])) {

                $start_line = $message['LINE'] - 10;

                $source = file_get_contents($message['FILE']);

                $source = preg_split('/' . PHP_EOL . '/', $source);

                if (false === function_exists('highlight')) {

                    include_once CarbonPHP::CARBON_ROOT . 'Functions.php';

                }

                $codePreview = highlight(implode(PHP_EOL, array_slice($source, $start_line, 20)), true);

            }

            foreach ($message as $left => $right) {

                if (!is_string($left)) {

                    $left = json_encode($left, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: serialize($left);                      //  todo - we can do better

                }

                if (!is_string($right)) {

                    $right = json_encode($right, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: serialize($left);                      //  todo - we can do better

                }

                $blocks = $left === 'TRACE'
                    || $left === '$GLOBALS[\'json\']'
                    || $left === 'debug_backtrace()';

                if ($blocks && is_callable($right)) {

                    $right();

                }

                $cleanErrorReport .= $blocks ?
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

        $statusText = self::statusText($code);

        $public_root = trim(CarbonPHP::$public_carbon_root, '/');

        if ('' !== $public_root) {

            $public_root = '/' . $public_root;

        }

        return (new \Mustache_Engine())->render(self::$errorTemplate, [
            'carbon_public_root' => $public_root,
            'public_root' => trim(CarbonPHP::$public_root ?? '', '/'),
            'code' => $code,
            'statusText' => $statusText,
            'actual_message' => $actual_message,
            'actual_message_body' => $message[$actual_message],
            'cleanErrorReport' => $cleanErrorReport,
            'json' => $GLOBALS['json'] ?? 'null',
            'json_string' => json_encode($GLOBALS['json'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ]);
    }

    public static function statusText(int $code = 0): ?string
    {
        // List of HTTP status codes.
        return [
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
            ][$code] ?? null;
    }

    /**
     * @param int|string|mixed $exitCode
     */
    public static function closeStdoutStderrAndExit($exitCode = 0): void
    {
        if (defined('STDOUT')) {

            fclose(STDOUT);

        }

        if (defined('STDERR')) {

            fclose(STDERR);

        }

        ob_start();

        ErrorCatcher::stop();

        error_reporting(0);

        exit($exitCode);
    }

}
