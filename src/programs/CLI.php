<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 7:03 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;

class CLI implements iCommand
{
    use Background, ColorCode;

    private array $CONFIG;
    private array $ARGV;

    private array $C6Programs = [];
    private array $UserPrograms = [];
    private string $userProgramsDirectory = '';

    public static $showEmptyCliWarning = true;

    private static ?iCommand $program;

    public function __construct(array $configuration)
    {

        [$this->CONFIG, $this->ARGV] = $configuration;

        $this->programList();

        $PHP = $this->CONFIG;

        $argv = &$this->ARGV;

        $fullCommand = 'php ' . implode(' ', $argv);

        self::colorCode("CLI Command Parsed >>", iColorCode::BLUE);

        self::colorCode($fullCommand, iColorCode::BACKGROUND_GREEN);

        array_shift($argv);

        $program = array_shift($argv);

        // todo - document this // done for git actions // order matters
        if ($program === '--stdOut') {

            ColorCode::$colorCodeBool = false;

            $program = array_shift($argv);

        }

        if (empty($program)) {

            if (self::$showEmptyCliWarning) {

                self::colorCode('No command provided. Printing help.');

                $this->usage();
            }

            return;

        }

        $searchAndConstruct = static function ($array, bool $C6Internal = true) use ($program, $PHP, $argv) {

            // Validation with this loop
            foreach ($array as $name) {

                // I prefer this loop so I catch
                if (strtolower($program) !== strtolower($name)) {

                    continue;

                }

                // todo - custom namespaces?
                $namespace = ($C6Internal ? "CarbonPHP\\" : '') . "Programs\\$name";

                if (!class_exists($namespace)) {

                    self::colorCode("Failed to load the class ($namespace). Your namespace is probably incorrect.\n");

                    die("Failed to load the class ($namespace). Your namespace is probably incorrect.\n");

                }

                $imp = class_implements($namespace);

                if (!array_key_exists(iCommand::class, $imp)) {

                    die('The program class "' . $namespace . '" should also implement iCommand ("'.iCommand::class.'"). ' . print_r($imp, true));

                }

                self::colorCode("\nConstructing Program >> $namespace", 'blue');

                $cmd = new $namespace([$PHP, $argv]);

                if ($cmd instanceof iCommand) { // only because my editor is dumb

                    self::$program = $cmd;

                } else {

                    self::colorCode("\nA very unexpected error occurred. Your command doesn't implement iCommand?", iColorCode::RED);

                    exit(1);

                }

                return true;

            }

            return false;

        };

        // while way more likely to run a C6 program and not user defined, precedence says a user program should
        // overwrite a C6
        // If a user makes a program with a name C6 will later take, for example, backwards compatibility
        if ($searchAndConstruct($this->UserPrograms, false) ||

            $searchAndConstruct($this->C6Programs)) {

            return;

        }

        self::colorCode("Program not found in CarbonPHP. Use help to list all CarbonPHP programs. Safely moving on.", iColorCode::YELLOW);

    }

    public function programList(): void
    {

        $clean = static function (&$program) {

            $program = basename($program, '.php');

        };

        if (!file_exists(CarbonPHP::$app_root . 'composer.json')) {

            self::colorCode("\tCouldn't find composer.json under the CarbonPHP::\$app_root ( " . CarbonPHP::$app_root . " ).\n\tLearn how to add cli programs at CarbonPHP.com", 'red');

        } else {

            $json = file_get_contents(CarbonPHP::$app_root . 'composer.json');

            /** @noinspection JsonEncodingApiUsageInspection */
            $json = json_decode($json, true);

            if ($json === null) {

                print "\n\tThe decoding of composer.json failed. Please make sure the file contains a valid json.\n\n";

                return;

            }

            $this->userProgramsDirectory = $programDirectory = $json['autoload']['psr-4']["Programs\\"] ??= false;

            if (is_string(CarbonPHP::$app_root . $programDirectory)) {

                $programDirectory = CarbonPHP::$app_root . $programDirectory;

                if (!is_dir($programDirectory)) {

                    $message = "The directory defined for the Programs Namespace ($programDirectory) in composer.json does not exist.";

                    self::colorCode( $message, iColorCode::RED);

                    exit($message);

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
            scandir(CarbonPHP::CARBON_ROOT . 'programs'),
            array(
                '.',
                '..',
                'CLI.php',
                'Background.php',
                'ColorCode.php',
                'MySQL.php',
                'TestAutomationServer.php',
                'testBuilder.php',
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
        self::colorCode("\nRunning Command", 'blue');

        array_shift($argv);

        $program = array_shift($argv);

        if (isset(self::$program)) {
            $cmd = self::$program;
            $cmd->run($argv);
            $cmd->cleanUp();
            return;
        }

        // executables switch TODO - make these programs
        switch (strtolower($program)) {
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

            case 'help':
            default:
                $this->usage();
        }
    }

    public function usage(): void
    {
        # $c6 = implode("\n                        ", $this->C6Programs);

        if (CarbonPHP::$app_root . 'src/' !== CarbonPHP::CARBON_ROOT) {
            if (!empty($this->UserPrograms)) {
                $UserPrograms = implode("\n                        ", $this->UserPrograms);

                self::colorCode( <<<END
          Available CarbonPHP CLI Commands  
            
          User Defined Commands :: 

                        $UserPrograms

END);

            } else {

                self::colorCode(<<<END
          You can create custom commands by adding the "Programs//" namespace to 
          
                  CarbonPHP::\$app_root . composers.json
                  
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

                use CarbonPHP\Interfaces\iCommand;
                
                class YourNewProgram implements iCommand 
                { 
                 
                       ...
                
                }

END, iColorCode::YELLOW);
            }
        }

        self::colorCode("
          
          CarbonPHP Built-in commands (case insensitive)::
        
                        help                          - This list of options
                        [command] -help               - display a list of options for each sub command
                        Test                          - Run PHPUnit tests
                        Rest                          - auto generate rest api from mysqldump
                        WebSocket                     - start a HTTP 5 web socket server written in PHP or Google Go
                        Database                      - cache current database schema or rebuild cached schema
                        Minify                        - minify css and js files defined in configuration 
                        Setup                         - wip
                        TestBuilder                   - creates program boilerplate code and stores to file
                        Deployment                    - Designed around deploying to a GCP server
                        SendToUserPipe                - send text over named pipe with session id


          While CarbonPHP displays this in the cli, it does not exit here. Custom functions may 
          be written after the CarbonPHP invocation. The CLI execution will however, stop the 
          routing of HTTP(S) request normally invoked through the (index.php). <-- Which could really 
          be any file run in CLI with CarbonPHP invoked.\n\n", iColorCode::BLUE);
    }

    public function cleanUp(): void
    {
    }
}