<?php

namespace CarbonPHP\Programs;


trait Background
{
    public function background($cmd, $outputFile = null)
    {
        try {
            if (strpos(PHP_OS, 'Windows') === 0) {
                $cmd = "start /B $cmd > $outputFile";
                print $cmd . PHP_EOL . PHP_EOL;
                pclose(popen($cmd, 'r'));
            } else {
                $cmd = sprintf('sudo %s', $cmd, $outputFile); // sudo %s > %s 2>$1 & echo $!
                print $cmd . PHP_EOL . PHP_EOL;
                exec($cmd, $pid);
            }
        } catch (\Throwable $e) {
        }
        return $pid[0] ?? 'Failed to execute cmd!';
    }

}