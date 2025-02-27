<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Programs;


use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\Interfaces\iCommand;

class SendToUserPipe implements iCommand
{
    public function usage(): void
    {
        // TODO - improve documentation
        echo <<<END
	           Question Marks Denote Optional Parameters
	           Order does not matter.
	           Flags do not stack ie. not -edf, this -e -f -d
	 Usage::
	  php index.php SendToUserPipe [sessionId] [value]
	  
	       Arguments can be provided with flags described below or usage above.  

	       -help                        - this dialogue    
	       -s                           - hex session id 
	       -v                           - value to send to the socket


END;
        exit(1);
    }

    public static function description(): string
    {
        return 'Send a value to a user pipe.';
    }

    public function cleanUp(): void
    {
        // do nothing
    }

    public function run($argv): void
    {
        $argc       = count($argv);
        $session_id = $value = '';
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $argc; ++$i) {
            switch ($argv[$i]) {
                case '-s':
                    $session_id = $argv[$i++];
                    break;
                case '-v':
                    $value = $argv[$i++];
                    break;
                default:
                    if (empty($session_id) || empty($value)) {
                        if (count($argv) === 2) {
                            [$session_id, $value] = $argv;
                        } else {
                            echo 'Your not doing it right... smh... Here\'s some help';
                            $this->usage();
                            exit(1);
                        }
                    }
                    // no break
                case '-help':
                    $this->usage();
                    break;
            }
        }

        if (false === Pipe::sendToFifoChannel($session_id, $value)) {
            echo 'Failed to send to pipe!'.PHP_EOL;
        } else {
            echo 'Success!'.PHP_EOL;
        }
    }
}
