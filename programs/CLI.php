<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 7:03 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\Interfaces\iCommand;

class CLI implements iCommand
{

    private $CONFIG;


    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
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
        print "\nIt's a powerful " . array_shift($argv) . ", hu?\n\n";

        $program = strtolower(array_shift($argv));

        $files = array_diff(scandir(CARBON_ROOT . 'programs', null), array('.', '..'));

        foreach ($files as $file) {
            // I prefer this loop so I catch
            if ($program !== strtolower(basename($file, '.php'))) {
                continue;
            }

            if (false === include($file)) {
                die('Failed loading file ' . $file . '. Please no syntax errors exist in this file.');
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
        print <<<END
          Available CarbonPHP CLI Commands  

                           help                          - This list of options
                         [command] -help                 - display a list of options for each sub command
                           test                          - Run PHPUnit tests
                           rest                          - auto generate rest api from mysqldump
                           php                           - start a HTTP 5 web socket server written in PHP
                           go                            - start a HTTP 5 web socket server written in Google Go


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