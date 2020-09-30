<?php

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Rest;
use Config\Documentation;
use CarbonPHP\Tables\Carbon_Location_References;
use CarbonPHP\Tables\Carbon_Locations;
use CarbonPHP\Tables\Carbon_Users as Users;

// Composer autoload
if (false === (include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    print '<h1>Composer Failed. Please run <b>composer install</b>.</h1>';
    die(1);
}


CarbonPHP::$safelyExit = true;

(new CarbonPHP(Documentation::class, __DIR__ . DIRECTORY_SEPARATOR))();

print 'testing playground' . PHP_EOL;

$_POST = [Users::SELECT => [
        Users::USER_USERNAME,
        Carbon_Locations::STATE
    ],
    Users::JOIN => [
        Users::INNER => [
            Carbon_Location_References::TABLE_NAME => [
                Users::USER_ID,
                Carbon_Location_References::ENTITY_REFERENCE
            ],
            Carbon_Locations::TABLE_NAME => [
                Carbon_Locations::ENTITY_ID,
                Carbon_Location_References::LOCATION_REFERENCE
            ]
        ]
    ],
    Users::PAGINATION => [
        Users::LIMIT => 10,
        Users::ORDER => Users::USER_USERNAME . Users::ASC
    ]];


$_SERVER['REQUEST_METHOD'] = 'GET';

Rest::RestfulRequests(Users::TABLE_NAME, null);

sortDump($GLOBALS['json']['rest']);





return true;