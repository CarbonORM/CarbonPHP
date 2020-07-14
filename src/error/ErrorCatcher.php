<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace CarbonPHP\Error;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Tables\Carbon_Reports;

/**
 * Class ErrorCatcher
 * @package CarbonPHP\Error
 *
 * Provide a global error and exception handler.
 *
 */
class ErrorCatcher
{
    /**
     * @var string TODO - re-setup logs saving to files
     */
    public static $defaultLocation;
    /**
     * @var bool $printToScreen determine if a generated error log should be shown on the browser.
     * This value can be set using the ["ERROR"]["SHOW"] configuration option
     */
    public static $printToScreen;
    /**
     * @var bool
     */
    public static $fullReports;
    /**
     * @var bool
     */
    public static $storeReport;

    /**
     * @var int to be used with error_reporting()
     * @link http://php.net/manual/en/function.error-reporting.php
     */
    public static $level;

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
            } catch (\Throwable $e) {
                if (!$e instanceof PublicAlert) {
                    $message = 'Developers make mistakes, and you found a big one! We\'ve logged this event and will be investigating soon.';
                    if (APP_LOCAL) {
                        PublicAlert::info($message);
                        PublicAlert::danger(\get_class($e) . ' ' . $e->getMessage());
                    } else {
                        PublicAlert::danger($message);
                    }

                    try {
                        ErrorCatcher::generateLog($e);
                    } catch (\Throwable $e) {
                        PublicAlert::danger('Error handling failed.');
                        print $e->getMessage();
                        PublicAlert::info(json_encode($e));
                    }
                }
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $argv = null;
            } finally {
                if (ob_get_status() && ob_get_length()) {
                    $out = ob_get_contents();
                    ob_end_clean();
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


    /**
     * ErrorCatcher constructor.
     */
    public static function start(): void     // TODO - not this.
    {
        ini_set('display_errors', 1);
        ini_set('track_errors', 1);
        error_reporting(self::$level);

        if (TEST) {
            return;
        }

        /**
         * if you return true from here it will continue script execution from the point of the error..
         * this is not wanted. Die and Exit are equivalent in execution. I ran across a post once that
         * explained how die should signify a complete error with no resolution, while exit has resolution
         * returning 1 rather than 0 in both cases will signify an error occurred
         * @param array $argv
         * @internal param TYPE_NAME $closure
         */

        $closure = static function (...$argv) {
            static $count = 0;
            $count++;

            // set_error_handler vs set_exception_handler signatures
            is_array($argv) ? self::generateLog($argv) : self::generateLog(...$argv);

            // try resetting to the default page if conditions correct
            if (!TEST && !CLI && !SOCKET && !APP_LOCAL && CarbonPHP::$setupComplete) {
                if ($count > 1) {
                    print 'A recursive error has occurred in (or at least affecting) your $app->defaultRoute();';
                    die(1);
                }
                CarbonPHP::resetApplication();
                exit(1);
            }
            die(1);
        };

        set_error_handler($closure);
        set_exception_handler($closure);   // takes one argument
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
     * @param \Throwable|array|null $e
     * @param string $level
     * @return string
     * @internal param $argv
     */
    public static function generateLog($e = null, string $level = 'log'): string
    {
        if (ob_get_status()) {
            // Attempt to remove any previous in-progress output buffers
            ob_end_clean();
        }

        ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);     // start a new buffer for saving errors

        $throwable = '';

        if ($e instanceof \Throwable) {
            $class = \get_class($e);
            $trace = self::generateCallTrace($e);
            if (!$e instanceof PublicAlert) {
                print '(set_error_handler || set_exception_handler) caught this ( ' . $class . ' ) throwable. #Bubbled up#' . PHP_EOL;
            } else {
                print 'Public Alert Thrown!' . PHP_EOL;
            }

            print PHP_EOL . $e->getMessage() . PHP_EOL; // todo why are we printing twice?

            $throwable = /** @lang HTML */
                <<<ERRORMESSAGE
                    <div class="alert alert-danger"><h4><i class="icon fa fa-ban"></i>$class :: {$e->getMessage()}</h4></div>
ERRORMESSAGE;

        } else if (($e[0] ?? null) instanceof \Throwable) {

            print PHP_EOL . 'Carbon caught this Message: ' . PHP_EOL . $e[0]->getMessage() . PHP_EOL;

            $trace = self::generateCallTrace($e[0]); // todo - see use cases


        } else {
            $trace = self::generateCallTrace(null);

            if (\is_array($e) && \count($e) >= 4) {
                print PHP_EOL . 'Carbon caught this Message: ' . PHP_EOL . $e[1] . PHP_EOL . 'line: ' . $e[2] . '(' . $e[3] . ')' . PHP_EOL;
            }
        }

        print PHP_EOL . $trace . PHP_EOL;

        $output = ob_get_clean();

        /** @noinspection ForgottenDebugOutputInspection */
        error_log($output);

        if (self::$printToScreen) {
            print /** @lang HTML */
                <<<ERRORMESSAGE
<h3>CarbonPHP [C6] is generating a log for you. <small>ErrorCatcher::generateLog</small></h3>
<h4>Turn it off to suppress stacktrace output.   <small>\$config['ERROR']['SHOW'] = true;</small></h4>
$throwable
<pre>$output</pre>
ERRORMESSAGE;
        }

        if (self::$storeReport === true || self::$storeReport === 'file') {
            if (!is_dir(REPORTS) && !mkdir($concurrentDirectory = REPORTS) && !is_dir($concurrentDirectory)) {
                PublicAlert::danger('Failed Storing Log');
            } else {
                $file = fopen(REPORTS . 'Log_' . time() . '.log', 'ab');

                if (!\is_resource($file) || !fwrite($file, $output)) {
                    PublicAlert::danger('Failed Storing Log');
                }
                fclose($file);
            }
        }

        if (self::$storeReport === true || self::$storeReport === 'database') {
            if (!Carbon_Reports::Post([
                Carbon_Reports::LOG_LEVEL => $level,
                Carbon_Reports::REPORT => $output,
                Carbon_Reports::CALL_TRACE => $trace
            ])) {
                print 'Failed to store error log, nothing works... Why does nothing work?';
                die(1);
            }
        }

        return $output;
    }

    /** A simplified back trace for quickly identifying route.
     * reverse array to make steps line up chronologically
     * @link http://php.net/manual/en/function.debug-backtrace.php
     * @param \Throwable $e
     * @return string
     */
    public static function generateCallTrace(\Throwable $e = null): string
    {
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
            array_shift($trace); // remove {main}
        }


        $length = \count($trace);
        if ($length === 0) {
            print "Found in :: \n\n\t" . $e->getTraceAsString();
        } else {
            $result = array();
            for ($i = 0; $i < $length; $i++) {
                $result[] = ($i + 1) . ') ' . implode(' (', explode('(', substr($trace[$i], strpos($trace[$i], ' ')))) . PHP_EOL . "\t\t\t\t" . (array_key_exists('args', $args[$i]) ? json_encode($args[$i]['args']) : '[]') . PHP_EOL;
            }
            print "\n\t" . implode("\n\t", $result);
        }

        return ob_get_clean();
    }

}



