<?php

namespace CarbonPHP\Helpers;

use CarbonPHP\CarbonPHP;

abstract class Composer
{
    public static function getComposerConfig(): array
    {
        if (!file_exists(CarbonPHP::$app_root . 'composer.json')) {
            ColorCode::colorCode("\tCouldn't find composer.json under the CarbonPHP::\$app_root ( " . CarbonPHP::$app_root . " ).\n\tLearn how to add cli programs at CarbonPHP.com\n\n", 'red');
            exit(1);
        }
        $json = file_get_contents(CarbonPHP::$app_root . 'composer.json');
        $json = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if ($json === null) {
            ColorCode::colorCode("\n\tThe decoding of " . CarbonPHP::$app_root . "composer.json failed. Please make sure the file contains a valid json.\n\n", 'red');
            exit(1);
        }
        return $json;
    }
}
