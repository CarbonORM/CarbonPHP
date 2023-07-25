<?php

namespace CarbonPHP\Helpers;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use Throwable;

abstract class Composer
{
    public static function getComposerConfig(): array
    {
        try {

            if (!file_exists(CarbonPHP::$app_root . 'composer.json')) {

                throw new PublicAlert("\tCouldn't find composer.json under the CarbonPHP::\$app_root ( " . CarbonPHP::$app_root . " ).\n\tLearn how to add cli programs at CarbonPHP.com\n\n");

            }

            $json = file_get_contents(CarbonPHP::$app_root . 'composer.json');

            $json = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if ($json === null) {

                throw new PublicAlert("\n\tThe decoding of (" . CarbonPHP::$app_root . "composer.json) failed. Please make sure the file contains a valid json.\n\n");

            }

            return $json;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

}
