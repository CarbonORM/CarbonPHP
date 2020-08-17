<?php

use CarbonPHP\CarbonPHP;
use Config\Config;

const DS = DIRECTORY_SEPARATOR;

// I would like to change to only using app_root soon
const APP_ROOT = __DIR__ . DS;

// Composer autoload
if (false === (include 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die(1);
}

// todo - regex new functions and namespaces
(new CarbonPHP(Config::class))();

return true;

