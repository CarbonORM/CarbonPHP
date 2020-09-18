<?php

use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;
use Config\Documentation;

const DS = DIRECTORY_SEPARATOR;

CarbonPHP::$app_root = __DIR__ . DS;

// Composer autoload
if (false === (include 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die(1);
}

// I would typically put this is another file, but this is still valid and make the example flow nicely
class InstantChat extends Application implements iConfig {

    public function startApplication(string $uri): bool
    {
        return true;
    }

    public function defaultRoute()
    {
        // TODO: Implement defaultRoute() method.
    }

    public static function configuration(): array
    {
        // TODO: Implement configuration() method.
        return [];
    }
}

(new CarbonPHP(InstantChat::class))();

return true;

