<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 7:03 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;

class CLI implements iCommand
{

    private array $CONFIG;
    private array $ARGV;

    public static array $customProgramDirectories = [];

    private array $C6Programs = [];
    public static array $UserPrograms = [];

    public static bool $showEmptyCliWarning = true;

    public static ?iCommand $program = null;

    public function __construct(array $configuration)
    {

        [$this->CONFIG, $this->ARGV] = $configuration;

        $this->programList();

        $PHP = $this->CONFIG;

        $argv = &$this->ARGV;

        $fullCommand = 'php ' . implode(' ', $argv);

        ColorCode::colorCode("CLI Command Parsed >>", iColorCode::BLUE);

        ColorCode::colorCode($fullCommand, iColorCode::BACKGROUND_GREEN);

        array_shift($argv);

        $program = array_shift($argv);

        // todo - document this // done for git actions // order matters
        if ($program === '--stdOut') {

            ColorCode::$colorCodeBool = false;

            $program = array_shift($argv);

        }

        if (empty($program)) {

            if (self::$showEmptyCliWarning) {

                ColorCode::colorCode('No command provided. Printing help.');

                $this->usage();
            }

            return;

        }

        $searchAndConstruct = static function (array $array, bool $C6Internal = true) use ($program, $PHP, $argv) {

            if (empty($array)) {

                return false;

            }

            // Validation with this loop
            foreach ($array as $fullyQualifiedProgramName) {

                // I prefer this loop so I catch
                if (false === str_ends_with(strtolower($fullyQualifiedProgramName), strtolower($program))) {

                    continue;

                }

                ColorCode::colorCode("Constructing Program >> $fullyQualifiedProgramName", iColorCode::CYAN);

                $cmd = new $fullyQualifiedProgramName([$PHP, $argv]);

                ColorCode::colorCode("Program Constructed >> $fullyQualifiedProgramName", iColorCode::CYAN);

                if ($cmd instanceof iCommand) { // only because my editor is dumb

                    self::$program = $cmd;

                } else {

                    ColorCode::colorCode("\nA very unexpected error occurred. Your command doesn't implement iCommand?", iColorCode::RED);

                    exit(1);

                }

                return true;

            }

            return false;

        };

        // while way more likely to run a C6 program and not user defined, precedence says a user program should
        // overwrite a C6
        // If a user makes a program with a name C6 will later take, for example, backwards compatibility
        if ($searchAndConstruct(self::$UserPrograms, false) ||
            $searchAndConstruct($this->C6Programs)) {

            return;

        }

        ColorCode::colorCode("Program not found in CarbonPHP. Use help to list all CarbonPHP programs. Safely moving on.", iColorCode::YELLOW);

    }

    public static function description(): string
    {
        return 'CLI main entry point. This is the default program, this description should not be seen :O';
    }

    public function programList(): void
    {

        try {


            $clean = static function (&$program) {

                $program = basename($program, '.php');

            };

            if (!file_exists(CarbonPHP::$app_root . 'composer.json')) {

                ColorCode::colorCode("\tCouldn't find composer.json under the CarbonPHP::\$app_root ( " . CarbonPHP::$app_root . " ).\n\tLearn how to add cli programs at CarbonPHP.com", 'red');

            } else if (false === CarbonPHP::$carbon_is_root) {

                $json = file_get_contents(CarbonPHP::$app_root . 'composer.json');

                /** @noinspection JsonEncodingApiUsageInspection */
                $json = json_decode($json, true);

                if ($json === null) {

                    print "\n\tThe decoding of composer.json failed. Please make sure the file contains a valid json.\n\n";

                    return;

                }

                foreach ($this->CONFIG[CarbonPHP::SITE][CarbonPHP::PROGRAMS] as $namespace) {

                    if (class_exists($namespace) === false) {

                        ColorCode::colorCode("The namespace ($namespace) provided in the composer.json file does not exist. Please check your composer.json file and configuration passe to CarbonPHP.", iColorCode::RED);

                        continue;

                    }

                    if (!in_array(iCommand::class, class_implements($namespace, true), true)) {

                        ColorCode::colorCode("The namespace ($namespace) provided in the composer.json file does not implement iCommand. Please check your composer.json file and configuration passe to CarbonPHP.", iColorCode::RED);

                        continue;

                    }

                    self::$UserPrograms[] = $namespace;

                }

                $customProgramDirectories = NewProgram::getProgramsNamespacesAndDirectories();

                foreach ($customProgramDirectories as $namespace => $relativeDirectory) {

                    self::$customProgramDirectories[$namespace] = CarbonPHP::$app_root . $relativeDirectory;

                }

                foreach (self::$customProgramDirectories as $namespace => $programDirectory) {

                    if (is_numeric($namespace)) {

                        if (false === class_exists($programDirectory)) {

                            throw new PrivateAlert('The program directories provided (CarbonPHP::SITE => [ CarbonPHP::PROGRAM_DIRECTORIES => [...]]) must be comma seperated fully qualified namespaces. Please check your composer.json file and configuration passe to CarbonPHP.');

                        }

                        continue;

                    }

                    if (!is_string($programDirectory)) {


                        throw new PrivateAlert('The program directories provided must be a string. Please check your composer.json file and configuration passe to CarbonPHP.');

                    }

                    if (!is_dir($programDirectory)) {

                        $message = "The directory defined for the Programs Namespace ($programDirectory) in composer.json does not exist.";

                        ColorCode::colorCode($message, iColorCode::RED);

                        exit(77);

                    }

                    $userDefinedPrograms = scandir($programDirectory, SCANDIR_SORT_ASCENDING);

                    $userDefinedPrograms = array_diff(
                        $userDefinedPrograms,
                        ['.', '..']);

                    // todo - check if class exists (autoload now) and if it implements iCommand in $clean()
                    if (!array_walk($userDefinedPrograms, $clean)) {

                        throw new PrivateAlert('array_walk failed in Cli::run()');

                    }

                    foreach ($userDefinedPrograms as $name) {

                        if (false === class_exists($namespace . $name)) {

                            ColorCode::colorCode("The file ($name) found under directory ($programDirectory) namespace ($namespace) was could not be auto-loaded.\n Please check your composer.json file and configuration passe to CarbonPHP.", iColorCode::RED);

                            continue;

                        }

                        self::$UserPrograms[] = $namespace . $name;

                    }

                    unset(self::$customProgramDirectories[$namespace]);

                }

            }

            // the following removes helper classes invalid responses and unfinished tools
            $program = array_diff(
                scandir($programDirectory = CarbonPHP::CARBON_ROOT . 'programs'),
                array(
                    '.',
                    '..',
                    'CLI.php',
                ));


            if (!array_walk($program, $clean)) {
                exit('array_walk failed in Cli::run()');
            }

            $namespace = 'CarbonPHP\\Programs\\';

            foreach ($program as $name) {

                if (false === class_exists($namespace . $name)) {

                    ColorCode::colorCode("The file ($name) found under directory ($programDirectory) namespace ($namespace) was could not be auto-loaded. Please check your composer.json file and configuration passe to CarbonPHP.", iColorCode::RED);

                    continue;

                }

                $this->C6Programs[] = $namespace . $name;

            }

        } catch (PublicAlert $e) {

            ThrowableHandler::generateLogAndExit($e);

        }
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
        ColorCode::colorCode("Running Command", iColorCode::BLUE);

        ColorCode::colorCode(implode(' ', $argv), iColorCode::BACKGROUND_MAGENTA);

        array_shift($argv);

        $program = array_shift($argv);

        if (isset(self::$program)) {

            $cmd = self::$program;

            $cmd->run($argv);

            $cmd->cleanUp();

            self::$program = null;

            return;

        }

        // executables switch
        switch (strtolower($program ?? 'help')) {
            case 'check':
                $cmd = 'netstat -a -n -o | find "' . ($PHP['SOCKET']['PORT'] ?? 8888) . '\"';
                print PHP_EOL . $cmd . PHP_EOL;
                print shell_exec($cmd);
                break;

            case 'test':
                // I used to use `phpunit --testdox  tests --bootstrap vendor/autoload.php`
                // but better defined in test constructor this may not be the case for browser test / tbd
                print shell_exec('composer test');
                break;

            case 'help':
                $this->usage();
                break;
            default:
                ColorCode::colorCode("Command not found. Use `help` to list all CarbonPHP programs. Safely moving on.", iColorCode::YELLOW);
        }
    }

    public function usage(): void
    {

        $compileProgramDescriptions = static function (array $fullyQualifiedPrograms): string {
            $c6Programs = '';

            foreach ($fullyQualifiedPrograms as $fullyQualified) {

                $namespaceExploded = explode("\\", $fullyQualified);

                $arrayKeyLast = array_key_last($namespaceExploded);

                $c6Programs .= "\t\t" . $namespaceExploded[$arrayKeyLast] . "  -  " . $fullyQualified::description() . "\n";

            }

            return $c6Programs;

        };


        # $c6 = implode("\n                        ", $this->C6Programs);

        if (CarbonPHP::$app_root . 'carbonphp/' !== CarbonPHP::CARBON_ROOT) {

            if (!empty(self::$UserPrograms)) {

                $UserPrograms = $compileProgramDescriptions(self::$UserPrograms);

                ColorCode::colorCode(<<<END
          Available CarbonPHP CLI Commands  
            
          User Defined Commands :: 

                        $UserPrograms

END
                );

            } else {

                ColorCode::colorCode(<<<END
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

        $c6InternalPrograms = $compileProgramDescriptions($this->C6Programs);

        ColorCode::colorCode("
          
        CarbonPHP Built-in commands (case insensitive)::
        
                help                          - This list of options
                [command] -help               - display a list of options for each sub command

        Commands::  

$c6InternalPrograms

          While CarbonPHP displays this in the cli, it does not exit here. Custom functions may 
          be written after the CarbonPHP invocation. The CLI execution will however, stop the 
          routing of HTTP(S) request normally invoked through the (index.php). <-- Which could really 
          be any file run in CLI with CarbonPHP invoked.\n\n", iColorCode::CYAN);
    }

    public function cleanUp(): void
    {
    }
}