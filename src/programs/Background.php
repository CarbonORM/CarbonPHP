<?php

namespace CarbonPHP\Programs;

use CarbonPHP\Helpers\Files;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;

trait Background
{
    /**
     * Attempt to run shell command in the background on any os.
     * This is convent as appending an ampersand will not work universally, and may not work where expected
     * @param string $cmd
     * @param string $outputFile
     * @param bool $append
     * @return string|int
     */
    public static function background(string $cmd, string $outputFile = '/dev/null', bool $append = false)
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

            exec($cmd, $pid);

            ColorCode::colorCode("Running Background CMD <disassociated> (parent<current> pid:: " . getmypid() . "; child pid::" . ($pid[0] ??='error') . ")>> " . $cmd . PHP_EOL . PHP_EOL);

        } catch (Throwable $e) {
        }

        return $pid[0] ?? 'Failed to execute cmd!';

    }

    public static function executeAndCheckStatus(string $command, bool $exitOnFailure = true): void
    {

        $output = [];

        $return_var = null;

        ColorCode::colorCode('Running CMD >> ' . $command . PHP_EOL . PHP_EOL . ' ');

        exec($command, $output, $return_var);

        if ($return_var !== 0 && $return_var !== '0') {

            ColorCode::colorCode("The command >>  $command \n\t returned with a status code (" . $return_var . '). Expecting 0 for success.', iColorCode::RED);

            $output = implode(PHP_EOL, $output);

            ColorCode::colorCode("Command output::\t $output ", iColorCode::RED);

            if ($exitOnFailure) {

                exit($return_var);

            }

        }

    }

}
