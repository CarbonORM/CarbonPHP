<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace CarbonPHP\Error;

use CarbonPHP\Database;

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

    /**
     * ErrorCatcher constructor.
     */
    public static function start(): void     // TODO - not this.
    {
        ini_set('display_errors', 1);
        ini_set('track_errors', 1);
        error_reporting(self::$level);
        /**
         * if you return true from here it will continue script execution from the point of the error..
         * this is not wanted. Die and Exit are equivalent in execution. I ran across a post once that
         * explained how die should signify a complete error with no resolution, while exit has resolution
         * returning 1 rather than 0 in both cases will signify an error occurred
         * @param array $argv
         * @internal param TYPE_NAME $closure
         */
        $closure = function (...$argv) {
            static $count;

            if (empty($count)) {
                $count = 0;
            }
            $count++;

            self::generateLog($argv);

            if (!SOCKET && !APP_LOCAL && \function_exists('startApplication')) {     // TODO - do we really want to reset?
                if ($count > 1) {
                    print 'A recursive error has occurred in (or at least affecting) your $app->defaultRoute();';
                    die(1);
                }
                startApplication(true);
            } else {
                print 'CarbonPHP Failed to initialize.';
                die(1);
            }
            exit(1);
        };
        set_error_handler($closure);
        set_exception_handler($closure);
    }

    /** Generate a full error log consisting of a
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

        if ($e instanceof \Throwable) {
            $trace = self::generateCallTrace($e);
            if (!$e instanceof PublicAlert) {
                print '(set_error_handler || set_exception_handler) caught this error. #Bubbled up#' . PHP_EOL;
            } else {
                print 'Public Alert Thrown!' . PHP_EOL;
            }
            print PHP_EOL . $e->getMessage() . PHP_EOL;

        } else {
            $trace = self::generateCallTrace();

            if (\is_array($e) && \count($e) >= 4) {
                print PHP_EOL . 'Carbon caught this Message: ' . $e[1] . PHP_EOL . 'line: ' . $e[2] . '(' . $e[3] . ')' . PHP_EOL;
            }
        }

        print PHP_EOL . $trace . PHP_EOL;

        $output = ob_get_contents();

        ob_end_clean();

        /** @noinspection ForgottenDebugOutputInspection */
        error_log($output);

        if (self::$printToScreen) {
            print "<h1>You have the print to screen Error Catching option turned on!</h1><h2> Turn it off to suppress this reporting.</h2><pre>$output</pre>";
        }

        if (self::$storeReport === true || self::$storeReport === 'file') {       // TODO - store to file?
            $file = fopen(REPORTS . '/Log_' . time() . '.log', 'ab');
            if (!fwrite($file, $output)) {
                PublicAlert::danger('Failed Storing Log');
            }
            fclose($file);
        }

        if (self::$storeReport === true || self::$storeReport === 'database') {       // TODO - store to file?
            $sql = 'INSERT INTO carbon_reports (log_level, report, call_trace) VALUES (?, ?, ?)';
            $sql = Database::database()->prepare($sql);
            if (!$sql->execute([$level, $output, $trace])) {
                print 'Failed to store error log, nothing works... Why does nothing work?' and die(1);
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
        ob_start();
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
        $result = array();
        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ') ' . implode(' (', explode('(', substr($trace[$i], strpos($trace[$i], ' ')))) . PHP_EOL . "\t\t\t\t" . json_encode($args[$i]['args']) . PHP_EOL;
        }
        print "\n\t" . implode("\n\t", $result);

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}



