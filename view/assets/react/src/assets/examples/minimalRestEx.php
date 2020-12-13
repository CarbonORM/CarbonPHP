<?php

use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Rest;

const DS = DIRECTORY_SEPARATOR;

const APP_ROOT = __DIR__ . DS;

// Composer autoload
if (false === (include __DIR__ . DS  . 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die('Composer Failed');
}

(new CarbonPHP(new class extends Application implements iConfig {

    /** This method is actully copied here for refrence and is not used,
     * its identical twin in CarbonPHP\Rest::MatchRestfulRequests is what is called in defaultRoute();
     * @package CarbonPHP\Rest
     * @param Route $route
     * @param string $prefix
     * @return Route
     */
    public static function MatchRestfulRequests(Route $route, string $prefix = ''): Route
    {
        return $route->regexMatch(/** @lang RegExp */ '#' . $prefix . 'rest/([A-Za-z\_]{1,256})/?' . Route::MATCH_C6_ENTITY_ID_REGEX . '?#',
            static function (string $table, string $primary = null) {
                Rest::RestfulRequests($table, $primary);
                return true;
            });
    }

    public function startApplication(string $uri): bool
    {
        return true;    // silence is golden
    }

    public function defaultRoute()
    {
        if (Rest::MatchRestfulRequests($this)()) {
            return true;
        }
    }

    public static function configuration(): array
    {
        return [
            'DATABASE' => [
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => '3306',
                'DB_NAME' => 'carbonphp',
                'DB_USER' => 'root',
                'DB_PASS' => 'password',
                'DB_BUILD' => '',
            ],
            'SITE' => [
                'CONFIG' => __FILE__
            ]
        ];
    }
}, __DIR__ . DS))();

return true;