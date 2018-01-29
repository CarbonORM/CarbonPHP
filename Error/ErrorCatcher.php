<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace Carbon\Error;

use Carbon\Database;

/**
 * Class ErrorCatcher
 * @package Carbon\Error
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
        $closure = function (...$argv) {
            self::generateLog($argv);
            if (\function_exists('startApplication')) {     // TODO - do we really want to reset?
                startApplication(true);
            }
            exit(1);
        };
        set_error_handler($closure);
        set_exception_handler($closure);
    }

    /** Generate a full error log consisting of a
     * @param array $argv
     * @param string
     * @return string
     */
    public static function generateLog(array $argv = [], string $level = 'log'): string
    {
        if (ob_get_status()) {
            ob_end_clean(); // Attempt to remove any previous in-progress output buffers
        }
        ob_start();     // start a new buffer for saving errors
        $trace = self::generateCallTrace();
        print $trace . PHP_EOL;
        if (count($argv) >= 4)
            print 'Message: ' . $argv[1] . PHP_EOL . 'line: ' . $argv[2] . '(' . $argv[3] . ')';
        else var_dump($argv);
        $output = ob_get_contents();
        ob_end_clean();

        if (self::$printToScreen) {
            print '<pre>';
            print_r($argv);
            print '</pre>';
        }

        if (self::$storeReport) {       // TODO - store to file?
            $sql = 'INSERT INTO carbon_reports (date, log_level, report, call_trace) VALUES (?, ?, ?, ?)';
            $sql = Database::database()->prepare($sql);
            if (!$sql->execute([date('Y-m-d H:i:s'), $level, $output, $trace]))
                print 'Failed to store error log, nothing works... Why does nothing work?' and die(1);
        }
        return $output;
    }

    /** A simplified back trace for quickly identifying route.
     * @link http://php.net/manual/en/function.debug-backtrace.php
     * @return string
     */
    public static function generateCallTrace(): string
    {
        $e = new \Exception();
        ob_start();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        array_pop($trace); // remove call to this method
        $length = \count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ') ' . substr(substr($trace[$i], strpos($trace[$i], ' ')), 35) . PHP_EOL;
        }
        print "\t" . implode("\n\t", $result);

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}



