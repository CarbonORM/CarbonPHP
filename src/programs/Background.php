<?php

namespace CarbonPHP\Programs;


// TODO - probably should be named better but were preserving backwards compatibility (BC)
trait Background
{
    use ColorCode;

    protected static bool $colorCodeBool = true;

    public function background($cmd, $outputFile = null)
    {
        try {
            if (strpos(PHP_OS, 'Windows') === 0) {
                $cmd = "start /B $cmd > $outputFile";
                print $cmd . PHP_EOL . PHP_EOL;
                pclose(popen($cmd, 'r'));
            } else {
                // TODO - this doesn't work???
                $cmd = sprintf('sudo %s > %s', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!
                print $cmd . PHP_EOL . PHP_EOL;
                exec($cmd, $pid);
            }
        } catch (\Throwable $e) {
        }
        return $pid[0] ?? 'Failed to execute cmd!';
    }

    public static function executeAndCheckStatus($command): void
    {
        $output = [];
        $return_var = null;
        self::colorCode('Running CMD >> ' . $command . PHP_EOL . PHP_EOL . ' ');
        exec($command, $output, $return_var);
        if ($return_var > 0) {
            self::colorCode("The command >>  $command \n\t returned with a status code (" . $return_var . '). ', 'red');
            $output = implode(PHP_EOL, $output);
            self::colorCode("\n\n\tCommand output::\n\n $output \n\n", 'cyan');
            exit(1);
        }
    }
}