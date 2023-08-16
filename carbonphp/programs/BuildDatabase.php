<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 11:36 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\Database as DB;
use CarbonPHP\Interfaces\iCommand;

class BuildDatabase implements iCommand
{

    public function cleanUp(): void
    {
        MySQL::cleanUp();
    }

    public static function description(): string
    {
        return 'Use existing generations to (re)build the database.';
    }

    public function usage(): void
    {
        print <<<usage

        The setup program can be used to initialize and update a projects configuration files. 
        Currently it does not do this, but here is what it does do.  
        
        -s --save
        -r --rebuild                 Update the projects database configurations.
        -m --mysql_native_password      Change mysql to default to a native password.

usage;
        exit(1);
    }

    public function run($argv): void
    {
        ColorCode::colorCode('Starting '. __METHOD__.' Execution');

        $argc = count($argv);

        if ($argc === 0) {

            $this->usage();

        }

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                default:
                    print 'Invalid argument ' . $argv[$i] . PHP_EOL;
                    $this->usage();
                    exit(1);

                case '--cnf':
                    print MySQL::buildCNF() . PHP_EOL;
                    exit(0);

                case '-r':
                case '--rebuild':

                    ColorCode::colorCode('Starting Database Build');

                    DB::setUp(false, true);   // Redirect = false

                    ColorCode::colorCode('Finished Database Build');

                    // this is going to the CLI so no need to run/attach redirect scripts
                    exit(0);

                case '-m':
                case '--mysql_native_password':

                    ColorCode::colorCode('Adjusting Mysql For PHP Compatibility');

                    MySQL::mysql_native_password();

                    ColorCode::colorCode('Done with mysql password compatibility');

                    exit(0);

                case '-s':
                case'--save':

                    ColorCode::colorCode('Saving current schema');

                    break;
            }
        }

    }
}