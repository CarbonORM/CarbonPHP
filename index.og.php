<?php

use CarbonPHP\Abstracts\Composer;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Documentation;

// Composer autoload
if (false === ($loader = include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {

    print '<h1>Composer Failed. Please run <b>composer install</b>.</h1>';

    die(1);

}

Composer::$loader = $loader;

(new CarbonPHP(Documentation::class, __DIR__ . DIRECTORY_SEPARATOR))();

return true;





