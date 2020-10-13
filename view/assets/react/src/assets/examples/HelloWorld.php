<?php

use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;

const DS = DIRECTORY_SEPARATOR;

const APP_ROOT = __DIR__ . DS;

// Composer autoload
if (false === (include 'vendor' . DS . 'autoload.php')) {
    print '<h1>Composer Failed</h1>';
    die(1);
}

(new CarbonPHP(new class extends Application implements iConfig {
        /**
         * @inheritDoc
         */
        public function startApplication(string $uri): bool
        {
            return $this->regexMatch('#home#i', static function () {
                print '<h1>Hello World! My name is ' . ucfirst(trim(`whoami`) ). '.</h1>';
            })();
        }

        public function defaultRoute()
        {
            print <<<HTML
<html>
    <head><title>[C6] Hello World - No Route Matched</title></head>
    <body><h1>Country roads, take me <a href="/home">home</a></h1></body>
</html>
HTML;
        }

        public static function configuration(): array
        {
            return [];
        }
    }
))();

return true;

