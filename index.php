<?php

declare(strict_types=1);

use CarbonPHP\Abstracts\Composer;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Documentation;

// Composer autoload
if (false === ($loader = include 'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
    echo '<h1>Composer Failed. Please run <b>composer install</b>.</h1>';

    exit(1);
}

Composer::$loader = $loader;

new CarbonPHP(Documentation::class, __DIR__.DIRECTORY_SEPARATOR)();

return true;
