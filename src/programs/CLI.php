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

    private array $CONFIG;
    private array $C6Programs = [];
    private array $UserPrograms = [];
    private string $userProgramsDirectory = '';

    public function __construct(array $CONFIG)
    {
        $this->CONFIG = $CONFIG;
        $this->programList();
    }

    public function programList(): void
    {
        $clean = static function (&$program) {
            $program = basename($program, '.php');
        };

        if (!file_exists(APP_ROOT . 'composer.json')) {
            print "\tCouldn't find composer.json under the APP_ROOT.\n\tLearn how to add cli programs at CarbonPHP.com";
        } else {
            $json = file_get_contents(APP_ROOT . 'composer.json');
            $json = json_decode($json, true);
            if ($json === null) {
                print "\n\tThe decoding of composer.json failed. Please make sure the file contains a valid json.\n\n";
                return;
            }
            $this->userProgramsDirectory = $programDirectory = $json['autoload']['psr-4']["Programs\\"] ??= false;

            if (is_string(APP_ROOT . $programDirectory)) {

                $programDirectory = APP_ROOT . $programDirectory;

                if (!is_dir($programDirectory)) {
                    print "The directory defined for the Programs Namespace ($programDirectory) in composer.json does not exist.";
                    exit(1);
                }
                $userDefinedPrograms = scandir($programDirectory, null);

                $userDefinedPrograms = array_diff(
                    $userDefinedPrograms,
                    ['.', '..']);

                if (!array_walk($userDefinedPrograms, $clean)) {
                    exit('array_walk failed in Cli::run()');
                }

                $this->UserPrograms = $userDefinedPrograms;
            }
        }

        // the following removes helper classes invalid responses and unfinished tools
        $program = array_diff(
            scandir(CARBON_ROOT . 'programs', null),
            array(
                '.',
                '..',
                'CLI.php',
                'Background.php',
                'MySQL.php',
                'TestAutomationServer.php',
                'testBuilder.php',
                'Websocketd.php',
                'WebSocketPHP.php'
            ));


        if (!array_walk($program, $clean)) {
            exit('array_walk failed in Cli::run()');
        }

        $this->C6Programs = $program;
    }

    /** Command Line Interface. Use
     *  I pass the php array to this function
     *  for reporting purposes.
     * php index.php [command]
     * @param $argv
     * @return mixed
     */
    public function run(array $argv): void
    {
        $PHP = $this->CONFIG;

        // I do this so the I can pass the argvs correctly to the php executables
        print "\nCalling Command >> " . implode(' ', $argv) . "\n\n";

        array_shift($argv);

        $program = array_shift($argv);

        $searchAndExecute = static function ($path, $array) use ($program, $PHP, $argv) {
            foreach ($array as $name) {
                // I prefer this loop so I catch
                if (strtolower($program) !== strtolower($name)) {
                    continue;
                }

                /** @noinspection PhpIncludeInspection */
                if (false === include($path . $name . '.php')) {
                    die('Failed loading file "' . $name . '.php". Please no syntax errors exist in this file.');
                }

                $classes = get_declared_classes();

                $class = end($classes);

                if (!class_exists($class)) {
                    die('Failed to load the class ("' . $class . '")');
                }

                $imp = class_implements($class);

                if (!array_key_exists(iCommand::class, $imp)) {
                    die('The program class "' . $class . '" should also implement iCommand. ' . print_r($imp, true));
                }

                // We're only using this closure to reinforce our iCommand // type hint
                $run = static function (iCommand $cmd) use ($argv) {
                    $cmd->run($argv);
                    $cmd->cleanUp($argv);
                    return 0;               // successful exit code is 0
                };

                exit($run(new $class($PHP)));
            }
        };

        // while way more likely to run a C6 program and not user defined, precedence says a user program should
        // overwrite a C6
        $searchAndExecute(APP_ROOT . $this->userProgramsDirectory, $this->UserPrograms);

        // If a user makes a program with a name C6 will later take, for example, backwards compatibility
        $searchAndExecute(CARBON_ROOT . 'programs/', $this->C6Programs);

        // executables switch TODO - make these programs
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
                // I used to use `phpunit --testdox  tests --bootstrap vendor/autoload.php`
                // but better defined in test constructor this may not be the case for browser test / tbd
                print shell_exec('composer test');
                print PHP_EOL;
                break;
            case 'websocketphp':
                print 'Starting PHP Websocket' . PHP_EOL;
                $CMD = 'php ' . CARBON_ROOT . 'Server.php ' . APP_ROOT . ' ' . $PHP['SITE']['CONFIG'];
                print `$CMD`;
                break;
            case 'help':
            default:
                $this->usage();
        }
    }

    public function usage(): void
    {
        // common knowledge tabs do not work well across os
        $c6 = implode("\n                        ", $this->C6Programs);

        if (APP_ROOT . 'src/' !== CARBON_ROOT) {
            if (!empty($this->UserPrograms)) {
                $UserPrograms = implode("\n                        ", $this->UserPrograms);

                print <<<END
          Available CarbonPHP CLI Commands  
            
          User Defined Commands :: 

                        $UserPrograms

END;
            } else {

                print <<<END
          You can create custom commands by adding the "Programs//" namespace to 
          
                  APP_ROOT . composers.json
                  
                .
                .
                .  
                "autoload": {
                    "psr-4": {
                        "App\\":"",
                        "Programs\\": "programs/"
                    }
                },
                .
                .
                .
                      
                   Then implementing program classes with the iCommand interface.  
                
                <?php

                namespace Programs;

                use CarbonPHP\interfaces\iCommand;
                
                class YourNewProgram implements iCommand 
                { 
                 
                       ...
                
                }

END;
            }
        }


        // $c6

        print <<<END
          
          CarbonPHP Built-in commands ::
        
                        help                          - This list of options
                        [command] -help               - display a list of options for each sub command
                        Test                          - Run PHPUnit tests
                        Rest                          - auto generate rest api from mysqldump
                        WebSocketPHP                  - start a HTTP 5 web socket server written in PHP
                        WebSocketGO                   - start a HTTP 5 web socket server written in Google Go
                        Database                      - cache current database schema or rebuild cached schema
                        Minify                        - minify css and js files defined in configuration 
                        Setup                         - wip
                        TestBuilder                   - wip (work in progress) 


          While CarbonPHP displays this in the cli, it does not exit here. Custom functions may 
          be written after the CarbonPHP invocation. The CLI execution will however, stop the 
          routing of HTTP(S) request normally invoked through the (index.php). <-- Which could really 
          be any file run in CLI with CarbonPHP invoked.\n\n
END;
    }

    public function cleanUp($PHP): void
    {
    }
}