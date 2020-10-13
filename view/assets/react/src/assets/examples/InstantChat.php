<?php

use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;
use Config\Config;

const DS = DIRECTORY_SEPARATOR;

const APP_ROOT = __DIR__ . DS;

// Composer autoload
if (false === (include 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die(1);
}

(new CarbonPHP(new class extends Application implements iConfig {

        public function startApplication(string $uri): bool
        {
            // TODO: Implement startApplication() method.
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
))();

return true;

