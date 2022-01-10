<?php

namespace CarbonPHP\Programs;

use CarbonPHP\Interfaces\iColorCode;
use Throwable;

trait Background
{
    public static function background(string $cmd, string $outputFile = '/dev/null', bool $append = false)
    {

        try {

            if (DIRECTORY_SEPARATOR === '\\') { // windows

                $cmd = "start /B $cmd > $outputFile";

                print $cmd . PHP_EOL . PHP_EOL;

                return pclose(popen($cmd, 'r'));

            }

            $cmd = sprintf('sudo sh %s ' . ($append ? '>>' : '>') . ' %s 2>&1 & echo $!;', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!

            exec($cmd, $pid);

            ColorCode::colorCode("Running Background CMD (pid::" . ($pid[0] ??='error') . ")>> " . $cmd . PHP_EOL . PHP_EOL);

            return $pid[0];

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
