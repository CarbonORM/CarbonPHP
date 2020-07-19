<?php


namespace CarbonPHP\Programs;

use CarbonPHP\interfaces\iCommand;


/* @author Richard Tyler Miles
 *
 *      Special thanks to the following people//resources
 *
 * @link https://gist.github.com/pbojinov/8965299
 */
class TestBuilder implements iCommand
{
    use Background;

    private array $CONFIG;

    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
    }

    public function usage(): void
    {
        // TODO - improve documentation
        print 'This builds a string you can execute to create a live websocket connection with your application.';
    }

    public function cleanUp($argv): void
    {
        // nothing
    }

    public function run($argv): void
    {
        $CMD = 'php -S localhost:9999 "' . CARBON_ROOT . 'programs' . DS . 'TestAutomationServer.php" "' . APP_ROOT . '" "' . ($this->CONFIG['SITE']['CONFIG'] ?? APP_ROOT) . '" ';
        print 'pid == ' . $this->background($CMD, APP_ROOT . 'TestCaseBuilder_log.txt');
        print "\n\n\tTest Automation Server started in the background, done!\n\n";
        //`$CMD`;
    }
}