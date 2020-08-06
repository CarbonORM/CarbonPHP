<?php

namespace CarbonPHP\Programs;

trait Composer
{
    public static function getComposerConfig(): array
    {
        if (!file_exists(APP_ROOT . 'composer.json')) {
            print "\tCouldn't find composer.json under the APP_ROOT.\n\tLearn how to add cli programs at CarbonPHP.com\n\n";
            exit(1);
        }

        $json = file_get_contents(APP_ROOT . 'composer.json');
        $json = json_decode($json, true);
        if ($json === null) {
            print "\n\tThe decoding of composer.json failed. Please make sure the file contains a valid json.\n\n";
            exit(1);
        }

        return $json;
    }
}
