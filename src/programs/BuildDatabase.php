<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 11:36 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Database as DB;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;

class BuildDatabase implements iCommand
{
    use ColorCode, MySQL {
        cleanUp as removeFiles;
    }


    public function cleanUp(): void
    {
        $this->removeFiles();
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
                case '-targetDirectory':
                    $tableDirectory = $argv[$i++];
                    break;
                case '-r':
                case '--rebuild':
                    self::colorCode('Starting Database Build');

                    DB::setUp(false, true);   // Redirect = false

                self::colorCode('Finished Database Build');
                    // this is going to the CLI so no need to run/attach redirect scripts
                    exit(0);
                case '-m':
                case '--mysql_native_password':
                    self::colorCode('Adjusting Mysql For PHP Compatibility');
                    self::mysql_native_password();
                    self::colorCode('Done with mysql password compatibility');
                    exit(0);
                case '-s':
                case'--save':
                    self::colorCode('Saving current schema');
                    break;
            }
        }

    }
}