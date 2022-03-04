<?php
/*
Plugin Name: CarbonPHP
Plugin URI: https://www.carbonphp.com/
Description: CarbonPHP
Author: Richard Tyler Miles
*/


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;

if (false === defined('ABSPATH')) {

    http_response_code(400);

    print '<h1>CarbonPHP is an opensource library. It looks like you have accessed the wordpress bootstrap file, or in 
            n00b terminology what allows us to be compatable with wordpress as a plugin. You should not try to access
            any file directly as classes are PSR-4 compliant. This means all file include operations will be dynamic using
            composer. To lean more about how to use CarbonPHP please refer to 
            <a href="https://www.carbonphp.com/">https://CarbonPHP.com/</a></h1>';

    exit(1);

}

// Composer autoload
if (false === (include ABSPATH . 'vendor' . DS . 'autoload.php')) {

    print '<h1>Composer Failed</h1>';

    exit(2);

}

(new CarbonPHP(new class extends Application implements iConfig {


    public function startApplication(string $uri): bool
    {
        // TODO: Implement startApplication() method.
    }

    public function defaultRoute(): void
    {
        // If nothing routes in this wordpress plugin just move on
    }

    public static function configuration(): array
    {
        // TODO: Implement configuration() method.

        return [

        ];

    }

}))();

return true;