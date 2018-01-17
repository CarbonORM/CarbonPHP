<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace Carbon\Error;

use Carbon\Database;
use Carbon\Singleton;

class ErrorCatcher
{
    use Singleton;                              // todo - put notes also why singleton is used in each class

    private $defaultLocation;
    private $printToScreen;
    private $fullReports;
    private $storeReport;

    public function __construct(string $logLocation, bool $storeReport, bool $printToScreen, bool $fullReports, int $level)
    {
        ini_set('display_errors', 1);
        ini_set('track_errors', 1);
        define('REPORTING', $level);
        error_reporting(REPORTING);
        $logLocation .= 'Error/';
        $this->defaultLocation = $logLocation . 'Log_' . ($_SESSION['id'] ?? '') . '_' . time() . '.log';
        $this->printToScreen = $printToScreen;
        $this->fullReports = $fullReports;
        $this->storeReport = $storeReport;
        $closure = function (...$argv) {
            $this->generateLog($argv);
            if (function_exists('startApplication'))
                startApplication(true);
            exit(1);
        };
        set_error_handler($closure);
        set_exception_handler($closure);
    }

    public static function generateErrorLog($argv = array())
    {
        $self = static::getInstance();
        return $self->generateLog($argv);
    }

    public function generateLog($argv = array())
    {
        ob_start();
        print_r($argv);
        $trace = $this->generateCallTrace();
        print $trace . PHP_EOL;
        if (count($argv) >= 4)
            print 'Message: ' . $argv[1] . PHP_EOL . 'line: ' . $argv[2] . '(' . $argv[3] . ')';
        else var_dump($argv);
        $output = ob_get_contents();
        ob_end_clean();


        if ($this->storeReport) {       // TODO - store to file?

            $sql = "INSERT INTO carbon_reports (date, log_level, report, call_trace) VALUES (?, ?, ?, ?)";
            $sql = Database::database()->prepare($sql);


            if (!$sql->execute([date("Y-m-d H:i:s"), 'LOG', $output, $trace]))
                print 'Failed to store error log, nothing works... Why does nothing work?' and die(1);
        }

        return $output;
    }

    public function generateCallTrace()
    {
        $e = new \Exception();
        ob_start();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ') ' . substr(substr($trace[$i], strpos($trace[$i], ' ')), 35) . PHP_EOL;
            print PHP_EOL; // replace '#someNum' with '$i)', set the right ordering
        }

        print "\t" . implode("\n\t", $result);

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}



