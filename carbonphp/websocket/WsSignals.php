<?php

namespace CarbonPHP\WebSocket;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iColorCode;
use Closure;

abstract class WsSignals
{

    public static function signalHandler(callable $garbage_collection): void
    {

        $signal = self::catchSignals($garbage_collection);

        # https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
        pcntl_signal(SIGTERM, $signal); // Termination ('kill' was called')

        pcntl_signal(SIGHUP, $signal);  // Terminal log-out

        pcntl_signal(SIGUSR1, $signal);  // custom signal

        pcntl_signal(SIGCHLD, SIG_IGN); // @link https://stackoverflow.com/questions/9976441/terminating-zombie-child-processes-forked-from-socket-server/10114945#10114945

    }

    /**
     * @param callable $garbage_collection
     * @return Closure
     */
    public static function catchSignals(callable $garbage_collection): callable
    {
        return static function (int $signal) use ($garbage_collection) {

            // @link https://unix.stackexchange.com/questions/317492/list-of-kill-signals
            ColorCode::colorCode("Signal Caught :: $signal", iColorCode::BACKGROUND_YELLOW);

            switch ($signal) {
                default:
                    ColorCode::colorCode('Received signal ' . $signal . ' ( unknown )', iColorCode::RED);

                    $garbage_collection();

                    break;

                case SIGKILL:

                    ColorCode::colorCode('Received signal SIGKILL.', iColorCode::RED);

                    $garbage_collection();

                    exit(SIGKILL);

                case SIGHUP: // handle restart tasks

                    ColorCode::colorCode('Received signal SIGUP ( controlling terminal closed ).', iColorCode::RED);

                    $garbage_collection();

                    exit(SIGHUP);

                case SIGINT:

                    ColorCode::colorCode('Received signal SIGINT. Cleaning up and exiting with SIGINT.', iColorCode::RED);

                    $garbage_collection();

                    exit(SIGINT);

                case SIGTERM:

                    ColorCode::colorCode('Received signal SIGTERM. Shutting down.', iColorCode::BLUE);

                    $garbage_collection(null, true);

                    exit(SIGTERM);

                case SIGUSR1:

                    // @link https://www.gnu.org/software/libc/manual/html_node/Miscellaneous-Signals.html

                    ColorCode::colorCode('Received signal SIGUSR1. Custom signal not implemented.', iColorCode::BLUE);

                    // custom signal

                    $garbage_collection(null, false);

                    break;

            }

        };

    }


}