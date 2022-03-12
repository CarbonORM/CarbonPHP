<?php
/*
Plugin Name: CarbonPHP
Plugin URI: https://www.carbonphp.com/
Description: CarbonPHP
Author: Richard Tyler Miles
*/


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\Deployment;
use CarbonPHP\Programs\Migrate;
use CarbonPHP\Rest;

if (false === defined('ABSPATH')) {

    http_response_code(400);

    print '<h1>CarbonPHP is an opensource library. It looks like you have accessed the wordpress bootstrap file, or in 
            n00b terminology what allows us to be compatible with WordPress as a plugin. You should not try to access
            any file directly as classes are PSR-4 compliant. This means all file include operations will be dynamic using
            composer. To lean more about how to use CarbonPHP please refer to 
            <a href="https://www.carbonphp.com/">https://CarbonPHP.com/</a></h1>';

    exit(1);

}


// Composer autoload
/** @noinspection UsingInclusionOnceReturnValueInspection */
if (false === (include_once ABSPATH . 'vendor' . DS . 'autoload.php')) {

    print '<h1>Composer Failed</h1>';

    exit(2);

}

function addCarbonPHPWordpressMenuItem(bool $advanced): void
{
    add_action('admin_menu', static fn() => add_menu_page(
        'CarbonPHP',
        'CarbonPHP',
        'edit_posts',
        'CarbonPHP',
        static function () use ($advanced) {

            $notice = $advanced ? "Advanced Setup Detected" : "Basic Configuration";

            print "<h1>CarbonPHP</h1><h2>$notice</h2>";

        },
        'dashicons-editor-customchar',
        '4.5'
    ));
}

$loaded = static function () : void {

    try {

        $callable = CarbonPHP::$carbon_php_loaded_callback;

        if (null === $callable) {

            return;

        }

        if (false === is_callable($callable)) {

            throw new PublicAlert('Failed to load carbonphp. User defined (CarbonPHP::$carbon_php_loaded_callback) must be of type callable.');

        }

        $callable();

    } catch (Throwable $e) {

        ErrorCatcher::generateLog($e);

    }

};


if (true === CarbonPHP::$setupComplete) {

    addCarbonPHPWordpressMenuItem(true);

    $loaded();

    return true;

}

$loaded();

(new CarbonPHP(new class implements iConfig {

    public static function configuration(): array
    {
        return [
            CarbonPHP::SOCKET => [
                CarbonPHP::PORT => defined('SOCKET_PORT') ? SOCKET_PORT : 8888,    // the ladder would case when boot-strapping server setup on aws invocation stating at dig.php
            ],
            // ERRORS on point
            CarbonPHP::ERROR => [
                CarbonPHP::LOCATION => CarbonPHP::$app_root . 'logs' . DS,
                CarbonPHP::LEVEL => E_ALL | E_USER_DEPRECATED | E_DEPRECATED | E_RECOVERABLE_ERROR | E_STRICT
                    | E_USER_NOTICE | E_USER_WARNING | E_USER_ERROR | E_COMPILE_WARNING | E_COMPILE_ERROR
                    | E_CORE_WARNING | E_CORE_ERROR | E_NOTICE | E_PARSE | E_WARNING | E_ERROR,  // php ini level
                CarbonPHP::STORE => false,      // Database if specified and / or File 'LOCATION' in your system
                CarbonPHP::SHOW => true,       // Show errors on browser
                CarbonPHP::FULL => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
            ],
            CarbonPHP::SESSION => [
                CarbonPHP::REMOTE => true,  // Store the session in the SQL database
                CarbonPHP::CALLBACK => static fn() => true,
            ],
            CarbonPHP::DATABASE => [
                CarbonPHP::DB_HOST => DB_HOST,
                CarbonPHP::DB_PORT => '', //3306
                CarbonPHP::DB_NAME => DB_NAME,
                CarbonPHP::DB_USER => DB_USER,
                CarbonPHP::DB_PASS => DB_PASSWORD,
            ],
            CarbonPHP::SITE => [
                CarbonPHP::URL => '', // todo - this should be changed back :: CarbonPHP::$app_local ? '127.0.0.1:8080' : basename(CarbonPHP::$app_root),    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
                CarbonPHP::ROOT => ABSPATH,
                CarbonPHP::CACHE_CONTROL => [
                    'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                    'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
                    'html|htm|hbs|js|json|map' => 'Cache-Control: max-age=0, private, public',
                ],
                CarbonPHP::CONFIG => __FILE__,
                CarbonPHP::IP_TEST => false,
                CarbonPHP::HTTP => true,
            ],
        ];

    }

}))(new class extends Application {

        public function startApplication(string $uri): bool
        {

            addCarbonPHPWordpressMenuItem(false);

            if (Deployment::github($this)()
                || Rest::MatchRestfulRequests($this)()
                || Migrate::enablePull($this, [
                    ABSPATH
                ])()) {

                exit(0);

            }

            return true;

        }

        public function defaultRoute(): void
        {
            // If nothing routes in this wordpress plugin just move on
        }

    }
);

return true;