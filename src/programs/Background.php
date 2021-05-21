<?php

namespace CarbonPHP\Programs;

use Throwable;

trait Background
{
    public static function background($cmd, $outputFile = '/dev/null')
    {
        try {
            if (DIRECTORY_SEPARATOR === '\\') { // windows
                $cmd = "start /B $cmd > $outputFile";
                print $cmd . PHP_EOL . PHP_EOL;
                return pclose(popen($cmd, 'r'));
            }
            $cmd = sprintf('sudo sh %s > %s 2>&1 & echo $!;', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!
            exec($cmd, $pid);
            ColorCode::colorCode("Running CMD (pid::$pid)>> " . $$cmd . PHP_EOL . PHP_EOL . ' ');
            return $pid;
        } catch (Throwable $e) {
        }
        return $pid[0] ?? 'Failed to execute cmd!';
    }

    public static function executeAndCheckStatus($command): void
    {
        $output = [];
        $return_var = null;
        ColorCode::colorCode('Running CMD >> ' . $command . PHP_EOL . PHP_EOL . ' ');
        exec($command, $output, $return_var);
        if ($return_var !== 0 && $return_var !== '0') {
            ColorCode::colorCode("The command >>  $command \n\t returned with a status code (" . $return_var . '). Expecting 0 for success.', 'red');
            $output = implode(PHP_EOL, $output);
            ColorCode::colorCode("\n\n\tCommand output::\n\n $output \n\n", 'red');
            exit($return_var);
        }
    }
}