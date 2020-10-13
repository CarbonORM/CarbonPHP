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

        public function startApplication(string $uri): bool
        {
            if ($_SESSION['id'] === null) {
                $this->structure($this->MVC());

                return $this->regexMatch('#Login.*#i', 'User', 'login')() ||
                    $this->regexMatch('#oAuth/([a-zA-z]{0,10})/([a-zA-z]{0,10})#i', 'User', 'oAuth')() ||
                    $this->regexMatch('#Register#i', 'User', 'Register')() ||           // Register
                    $this->regexMatch('#Recover/([a-zA-Z@\.]){8,40}/([A-Za-z0-9]){4,40})/?#i', 'User', 'recover')();     // Recover $userId
            }

            $this->structure($this->wrap());

            return $this->regexMatch('#drills/putting#i', 'golf/putting.hbs')() ||
                $this->regexMatch('#drills/approach#i', 'golf/approach.hbs')() ||
                $this->regexMatch('#drills/accuracy#i', 'golf/accuracy.hbs')() ||
                $this->regexMatch('#drills/distance#i', 'golf/distance.hbs')();
        }

        public function defaultRoute()
        {
            http_response_code(404);
            print '<h1>The server could not complete the request.</h1>';
        }

        public static function configuration(): array
        {
            return [];
        }
    }
))();

return true;

