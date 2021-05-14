<?php

namespace CarbonPHP\Programs;

use Throwable;

trait Background
{
    public static function background($cmd, $outputFile = null)
    {
        try {
            if (strpos(PHP_OS, 'Windows') === 0) {
                $cmd = "start /B $cmd > $outputFile";
                print $cmd . PHP_EOL . PHP_EOL;
                return pclose(popen($cmd, 'r'));
            }
            // TODO - this doesn't work???
            $cmd = sprintf('sudo %s > %s', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!
            print $cmd . PHP_EOL . PHP_EOL;
            exec($cmd, $pid);
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