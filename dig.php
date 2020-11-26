<?php

/**
 * Designed to be the cli for DIG
 *
 */


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;

const DS = DIRECTORY_SEPARATOR;

const APP_ROOT = __DIR__ . DS;

// Composer autoload
if (false === (include __DIR__ . DS  . 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die('Composer Failed');
}

(new CarbonPHP(new class extends Application implements iConfig {

        public function startApplication(string $uri): bool
        {
            return true;    // silence is golden
        }

        public function defaultRoute()
        {
            // silence is golden
        }

        public static function configuration(): array
        {
            return [];
        }
    }
))();

return true;