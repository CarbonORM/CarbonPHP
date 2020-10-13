<?php


namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
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

    public function cleanUp(): void
    {
        // nothing
    }

    public function run($argv): void
    {
        $CMD = 'php -S localhost:9999 "' . CarbonPHP::CARBON_ROOT . 'programs' . DS . 'TestAutomationServer.php" "' . CarbonPHP::$app_root . '" "' . ($this->CONFIG['SITE']['CONFIG'] ?? CarbonPHP::$app_root) . '" ';
        print 'pid == ' . $this->background($CMD, CarbonPHP::$app_root . 'TestCaseBuilder_log.txt');
        print "\n\n\tTest Automation Server started in the background, done!\n\n";
        //`$CMD`;
    }
}