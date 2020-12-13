<?php

namespace CarbonPHP;


use CarbonPHP\Database;
use CarbonPHP\Rest;
use CarbonPHP\Route;

/**
 * This is NOT THE FULL `Rest` CLASS, but an exmple showcasing the MatchRestfulRequests method.
 */
abstract class Rest extends Database
{
    public static function MatchRestfulRequests(Route $route, string $prefix = ''): Route
    {
        return $route->regexMatch(/** @lang RegExp */ '#' . $prefix . 'rest/([A-Za-z\_]{1,256})/?' . Route::MATCH_C6_ENTITY_ID_REGEX . '?#',
            static function (string $table, string $primary = null) {
                Rest::RestfulRequests($table, $primary);
                return true;
            });
    }
}