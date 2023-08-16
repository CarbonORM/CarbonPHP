<?php
/*
 * Plugin Name: YOUR PLUGIN NAME
 */

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Documentation;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\Deployment;
use CarbonPHP\Programs\Migrate;
use CarbonPHP\Tables\Carbons;

// Composer autoload
/** @noinspection UsingInclusionOnceReturnValueInspection */
if (false === class_exists('CarbonPHP\Application')
    && false === (include_once ABSPATH . 'vendor' . DS . 'autoload.php')) {

    print '<h1>Composer Failed</h1>';

    exit(2);

}

ColorCode::colorCode("Detected composer autoload in (" . __FILE__ . ')');

function addCarbonPHPWordpressMenuItem(bool $advanced): void
{
    $notice = $advanced ? "<Advanced>" : "<Basic>";

    add_action('admin_menu', static fn() => add_menu_page(
        "CarbonPHP $notice" ,
        "CarbonPHP $notice",
        'edit_posts',
        'CarbonPHP',
        static function () {

            print Documentation::inlineReact();

        },
        'dashicons-editor-customchar',
        '4.5'
    ));
}

CarbonPHP::$wordpressPluginEnabled = true;

if (true === CarbonPHP::$setupComplete) {

    addCarbonPHPWordpressMenuItem(true);

    return true;

}

ColorCode::colorCode("Starting Full Wordpress CarbonPHP Configuration!",
    iColorCode::BACKGROUND_CYAN);

(new CarbonPHP(new class implements iConfig {

    public static function configuration(): array
    {

        addCarbonPHPWordpressMenuItem(false);

        Documentation::$pureWordpressPluginConfigured = true;

        $prefix = ABSPATH;

        $str = dirname(CarbonPHP::CARBON_ROOT);

        if (str_starts_with($str, $prefix)) {

            $str = substr($str, strlen($prefix));

        }

        return [
            CarbonPHP::SOCKET => [
                CarbonPHP::PORT => defined('SOCKET_PORT') ? SOCKET_PORT : 8888,    // the ladder would case when boot-strapping server setup on aws invocation stating at dig.php
            ],
            CarbonPHP::VIEW => [
                // TODO - THIS IS USED AS A URL AND DIRECTORY PATH. THIS IS BAD. WE NEED DS
                CarbonPHP::VIEW => DS,  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
                CarbonPHP::WRAPPER => '2.0.0/Wrapper.hbs',     // View::content() will produce this
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
                CarbonPHP::REMOTE => false,  // Store the session in the SQL database
                CarbonPHP::CALLBACK => static fn() => true,
            ],
            CarbonPHP::DATABASE => [
                CarbonPHP::DB_HOST => DB_HOST,
                CarbonPHP::DB_PORT => '', //3306
                CarbonPHP::DB_NAME => DB_NAME,
                CarbonPHP::DB_USER => DB_USER,
                CarbonPHP::DB_PASS => DB_PASSWORD,
            ],
            CarbonPHP::REST => [
                // This section has a recursion property, as the generated data is input for its program
                CarbonPHP::NAMESPACE => Carbons::CLASS_NAMESPACE,
                CarbonPHP::TABLE_PREFIX => Carbons::TABLE_PREFIX
            ],
            CarbonPHP::SITE => [
                CarbonPHP::URL => '', // todo - this should be changed back :: CarbonPHP::$app_local ? '127.0.0.1:8080' : basename(CarbonPHP::$app_root),    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
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

}, ABSPATH))(new class extends Application {

    public function startApplication(string $uri): bool
    {

        if (Deployment::github()
            || Migrate::enablePull([CarbonPHP::$app_root])) {

            ColorCode::colorCode("CarbonPHP matched matched a route with the Wordpress Plugin Feature!");

        }

        return true;

    }

    public function defaultRoute(): void
    {
        // TODO: Implement defaultRoute() method.
    }
});

ColorCode::colorCode("FINISHED Full Wordpress CarbonPHP Configuration!");

return true;