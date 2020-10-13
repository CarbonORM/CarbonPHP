<?php

use CarbonPHP\CarbonPHP;
use Config\Documentation;


// Composer autoload
if (false === (include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    print '<h1>Composer Failed. Please run <b>composer install</b>.</h1>';
    die(1);
}

(new CarbonPHP(Documentation::class, __DIR__ . DIRECTORY_SEPARATOR))();

return true;
