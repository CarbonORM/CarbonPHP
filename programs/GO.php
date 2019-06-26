<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/4/19
 * Time: 8:22 PM
 */


namespace CarbonPHP\Programs;


use CarbonPHP\interfaces\iCommand;


class GO implements iCommand
{
    private $CONFIG;

    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
    }

    public function usage(): void
    {
        // TODO - improve documentation
        print 'This builds a string you can execute to create a live websocket connection with your application.';
    }

    public function cleanUp($argv) : int
    {
        return 0;
    }

    public function run($argv): void
    {
        $background = function ($cmd, $outputFile) {
            try {
                if (strpos(PHP_OS, 'Windows') === 0) {
                    $cmd = "start /B $cmd > $outputFile";
                    print $cmd . PHP_EOL . PHP_EOL;
                    pclose(popen($cmd, 'r'));
                } else {
                    $cmd = sprintf('sudo %s ', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!
                    print $cmd . PHP_EOL . PHP_EOL;
                    exec($cmd, $pid);
                }
            } catch (\Throwable $e) {
            }
            return $pid[0] ?? 'Failed to execute cmd!';
        };


        $CMD = 'websocketd --port=' . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
            (($this->CONFIG['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
            (($this->CONFIG['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey='{$this->CONFIG['SOCKET']['SSL']['KEY']}' --sslcert='{$this->CONFIG['SOCKET']['SSL']['CERT']}' " : ' ') .
            'php "' . CARBON_ROOT . 'programs' . DS . 'Websocketd.php" "' . APP_ROOT . '" "' . ($this->CONFIG['SITE']['CONFIG'] ?? APP_ROOT) . '" ';

        print 'pid == ' . $background($CMD, APP_ROOT . 'websocketd_log.txt');
        print "\n\n\tWebsocket started in the background, done!\n\n";
        //`$CMD`;
    }

}