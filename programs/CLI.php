<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 7:03 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\Error\PublicAlert;
use CarbonPHP\interfaces\iCommand;

class CLI implements iCommand
{

    private $CONFIG;
    private $programs;

    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
        $this->programList();
    }


    public function programList() {

        // the following removes helper classes invalid responses and unfinished tools
        $program = array_diff(
            scandir(CARBON_ROOT . 'programs', null),
            array('.', '..',
                'CLI.php',
                'Server.php',
                'Background.php',
                'MySQL.php',
                'TestAutomationServer.php',
                'Setup.php',
                'testBuilder.php'));

        $clean = function (&$program) {
            $program = basename($program, '.php');
        };

        if (!array_walk($program, $clean)) {
            exit('array_walk failed in Cli::run()');
        }


        $this->programs = $program;
    }

    /** Command Line Interface. Use
     *  I pass the php array to this function
     *  for reporting purposes.
     * php index.php [command]
     * @param $argv
     * @return mixed
     */
    public function run($argv)
    {
        $PHP = $this->CONFIG;

        // I do this so the I can pass the argvs correctly to the php executables
        print "\nIt's a powerful \"" . array_shift($argv) . "\", huh?\n\n";

        $program = array_shift($argv);

        foreach ($this->programs as $name) {
            // I prefer this loop so I catch
            if (strtolower($program) !== strtolower($name)) {
                continue;
            }

            /** @noinspection PhpIncludeInspection */
            if (false === include $name . '.php') {
                die('Failed loading file "' . $name . '.php". Please no syntax errors exist in this file.');
            }

            $classes = get_declared_classes();

            $class = end($classes);

            if (!class_exists($class)) {
                die('Failed to load the class ("' . $class . '")');
            }

            $imp = class_implements($class);

            if (!array_key_exists(iCommand::class, $imp)) {
                die('The program class should also implement iCommand. '. print_r($imp, true));
            }

            // We're only using this closure to reinforce our iCommand // type hint
            $run = function (iCommand $cmd) use ($argv) {
                return $cmd->run($argv) and $cmd->cleanUp($argv);
            };

            exit($run(new $class($PHP)));
        }

        switch ($program) {
            case 'minify':
                    print "\n\nminify\n\n";
                break;
            case 'check':
                $cmd = 'netstat -a -n -o | find "' . ($PHP['SOCKET']['PORT'] ?? 8888) . '\"';
                print PHP_EOL . $cmd . PHP_EOL;
                print shell_exec($cmd);
                break;
            case 'test':
                print PHP_EOL;
                print shell_exec('phpunit --bootstrap vendor/autoload.php --testdox  tests');
                print PHP_EOL;
                break;
            case 'php':
                print 'Starting PHP Websocket' . PHP_EOL;
                $CMD = 'php ' . CARBON_ROOT . 'Server.php ' . APP_ROOT . ' ' . $PHP['SITE']['CONFIG'];
                print `$CMD`;
                break;
            case 'help':
            default:
                $this->usage();
        }
        return 0;
    }

    public function usage()
    {
        // common knowledge tabs do not work well across os
        $this->programs = implode("\n                        ", $this->programs);

        print <<<END
          Available CarbonPHP CLI Commands  
            
          User Defined Commands :: 

                        $this->programs

          CarbonPHP Built-in commands ::
        
                        help                          - This list of options
                        [command] -help               - display a list of options for each sub command
                        test                          - Run PHPUnit tests
                        Rest                          - auto generate rest api from mysqldump
                        php                           - start a HTTP 5 web socket server written in PHP
                        GO                            - start a HTTP 5 web socket server written in Google Go


          While CarbonPHP displays this in the cli, it does not exit here. Custom functions may 
          be written after the CarbonPHP invocation. The CLI execution will however, stop the 
          routing of HTTP(S) request normally invoked through the (index.php). <-- Which could really 
          be any file run in CLI with CarbonPHP invoked.\n\n
END;
    }

    public function cleanUp($PHP) : int
    {
        return 0;
    }
}