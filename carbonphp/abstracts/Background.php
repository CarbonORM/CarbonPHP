<?php

namespace CarbonPHP\Abstracts;

use CarbonPHP\Interfaces\iColorCode;
use Throwable;

abstract class Background
{

    /**
     * Attempt to run shell command in the background on any os.
     * This is convent as appending an ampersand will not work universally, and may not work where expected
     * @param string $cmd
     * @param string $outputFile
     * @param bool $append
     * @return string|int
     */
    public static function background(string $cmd, string $outputFile = '/dev/null', bool $append = false): int|string
    {

        try {

            if (DIRECTORY_SEPARATOR === '\\') { // windows

                $cmd = "start /B $cmd > $outputFile";

                print $cmd . PHP_EOL . PHP_EOL;

                return pclose(popen($cmd, 'r'));

            }

            Files::mkdir($outputFile);

            touch($outputFile);

            $cmd = sprintf('nohup %s ' . ($append ? '>>' : '>') . ' %s 2>&1 & echo $! ; disown', $cmd, $outputFile);

            exec($cmd, $pid, $return);

            ColorCode::colorCode("Running Background CMD <disassociated> (parent<current> pid:: " . getmypid() . "; child pid::" . ($pid[0] ??='error') . ")>> " . $cmd,
                iColorCode::BACKGROUND_BLUE);

        } catch (Throwable $e) {
        }

        $backgroundProcessesStatusCodes[$pid[0] ??= 'error'] = &$return;

        return $pid[0] ?? 'Failed to execute cmd!';

    }

    public static function executeAndCheckStatus(string $command, bool $exitOnFailure = true, array &$output = null): int
    {

        $output = [];

        $return_var = null;

        ColorCode::colorCode('Running CMD >> ' . $command,
            iColorCode::BACKGROUND_BLUE);

        exec($command, $output, $return_var);

        if ($return_var !== 0 && $return_var !== '0') {

            $color = $exitOnFailure ? iColorCode::RED : iColorCode::YELLOW;

            ColorCode::colorCode("The command >>  $command \n\t returned with a status code (" . $return_var . '). Expecting 0 for success.', $color);

            $output = implode(PHP_EOL, $output);

            ColorCode::colorCode("Command output::\t $output ", $color);

            if ($exitOnFailure) {

                exit($return_var);

            }

        }

        return (int) $return_var;

    }

}
