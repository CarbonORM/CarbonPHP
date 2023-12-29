<?php


namespace CarbonPHP\Traits;


use CarbonPHP\Classes\Types\ProgramArgumentsArray;

trait ProgramArguments {

    /**
     * @return ProgramArgumentsArray[]
     */
    abstract public static function arguments(): array;

    public function usage(): void
    {
        $arguments = self::arguments();

        foreach ($arguments as $argument => $data) {

            print "\t$argument\t" . implode(', ', $data->flags) . PHP_EOL;

            print "\t\t\t" . $data->description . PHP_EOL;

        }

    }

    public function parseArguments(array $argv): void
    {
        $argc = count($argv);

        while ($argc-- > 0) {

            $arg = $argv[$argc];

            $arguments = self::arguments();

            foreach ($arguments as $shortFlag => $data) {

                if (in_array($arg, [$shortFlag, ...$data->flags], true)) {

                    $callable = $data->callable;

                    if (is_callable($callable)) {

                        $callable($arg, $argc);

                        break;

                    }

                    break;

                }

            }

        }

    }

}