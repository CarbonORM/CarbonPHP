<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Helpers\ColorCode;
use CarbonPHP\Helpers\MySQL;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Interfaces\iColorCode;

class CNF implements iCommand
{
    private array $CONFIG;

    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
    }

    public function usage(): void
    {
        // TODO - improve documentation
            print <<<END
            
            Generate a new mysql cnf file for your project. This will be located in the root of your project.
	 Usage::
	  php index.php CNF

	       -help                        - this dialogue                


END;
        exit(1);
    }

    public function cleanUp(): void
    {
        // do something or nothing.. up to you
    }

    public function run($argv): void
    {
        $C6 = CarbonPHP::CARBON_ROOT === CarbonPHP::$app_root . 'src' . DS;
        $argc = count($argv);
        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                default:
                    MySQL::buildCNF();
                    exit(0);
                case '-help':
                    if ($C6) {
                        ColorCode::colorCode("\tYou da bomb :)\n", iColorCode::CYAN);
                        break;
                    }
                    $this->usage();
                    break;
                
            }
        }
       // todo - add program code
    }

}
