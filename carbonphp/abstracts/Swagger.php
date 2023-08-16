<?php


namespace CarbonPHP\Abstracts;

use CarbonPHP\Rest;
use CarbonPHP\Route;

abstract class Swagger
{

    public static array $restTablesArray = [];

    /**
     * I think the idea of the swagger api with c6 is clean.. 1 night build?
     * @param Route $route
     * @param string $prefix
     * @return Route
     */
    public static function swaggerUI(Route $route, string $prefix = ''): Route
    {
        return $route->regexMatch("#^{$prefix}swagger/?#i", static function () {

            Rest::scanAnd(static function (string $fullyQualifiedTableName) {

                self::$restTablesArray[] = $fullyQualifiedTableName;

            });

            $rest_info = [];

            foreach (self::$restTablesArray as $className)
            {
                $rest_info[$className] = [
                    'COLUMNS' => $className::COLUMNS,
                    'REGEX_VALIDATION' => $className::REGEX_VALIDATION
                ];
            }


            print self::buildWebpage($rest_info);

        });

    }

    public static function buildWebpage(array $rest_info) : string {



        return (new \Mustache_Engine())->render(
            /** @lang Handlebars */ <<<HTML
                  

// swagger uses react to im not sure what I will do atm 

HTML,$rest_info);

    }


}