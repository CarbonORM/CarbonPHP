<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/17/17
 * Time: 11:48 AM
 */

namespace CarbonPHP;


use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;
use function count;
use function explode;
use function substr_count;

abstract class Route
{

    /**
     * @var string $uri full uri from server request $_SERVER['REQUEST_URI']
     * back slash '\' as our delimiter
     */
    public static string $uri;

    /**
     * @var array $uriExplode will hold an exploded array with the
     * back slash '\' as our delimiter
     */
    public static array $uriExplode;

    /**
     * @var int $uriLength will hold the current number of fields
     * separated by backslashes
     */
    public static int $uriLength;

    /**
     * @var bool|string $matched will equal "true" if the
     * current state has not executed a lambda function in response.
     * If a function has been executed the value of matched will be
     * true;
     */
    public static bool $matched = false;             // a bool



    public const MATCH_C6_ENTITY_ID_REGEX = '([a-fA-F0-9]{20,35})';

    /**
     * This function must be implemented by the user to use the Route Class.
     * If no url is provided in $_SERVER['REQUEST_URI'], or $matched is
     * false when this class destructs the default route will be executed.
     * @return mixed
     */
    abstract public function defaultRoute(): void;

    /**
     * Will be run when the active object is destroyed. If the
     * $matched variable is false then our default route will be
     * executed. If matched is set to "true", indicating that
     * no callable has been run, no errors or warnings will be printed.
     * This is by design.
     */
    public function __destruct()
    {

        try {

            // in direct invocation this class may be needlessly initialized
            if (CarbonPHP::$safelyExit || self::$matched || CarbonPHP::$cli || CarbonPHP::$test) {

                return;

            }

            if (CarbonPHP::$socket) {

                ColorCode::colorCode('Socket Left Route Class Un-Matched', iColorCode::BACKGROUND_RED);

                exit(1);

            }

            self::$matched = true;

            $this->defaultRoute();

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

    }


    /**
     * Route constructor. If the url is / or null then our
     * default route will be invoked.
     */
    public function __construct()
    {
        // This check allows Route to be independent of Carbon/Application, but benefit if we've already initiated
        if (isset(CarbonPHP::$uri) && !CarbonPHP::$socket) {

            self::$uri = CarbonPHP::$uri;

            self::$uriExplode = explode('/', trim(CarbonPHP::$uri, '/'));

            self::$uriLength = count(self::$uriExplode);

        } else {

            self::$uri = trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'] ?? '')), PHP_URL_PATH)));

            self::$uriExplode = explode('/', self::$uri);

            self::$uriLength = substr_count(self::$uri, '/') + 1; // I need the exploded string

        }

    }

    /**
     * @param string $uri
     */
    public static function changeURI(string $uri): void
    {
        self::$uri = $uri = trim($uri, '/');

        self::$uriExplode = explode('/', $uri);

        self::$uriLength = substr_count($uri, '/') + 1;

        self::$matched = false;
    }


    /**
     * @param string $regexToMatch
     * @param callable $closure
     * @return bool
     */
    public static function regexMatch(string $regexToMatch, callable $closure): bool
    {
        try {

            $matches = [];

            if (1 > preg_match_all($regexToMatch, self::$uri, $matches, PREG_SET_ORDER)) {  // can return 0 or false

                return self::$matched;

            }

            self::$matched = true;

            $matches = array_shift($matches);

            array_shift($matches);  // could care less about the full match

            // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
            call_user_func_array($closure, $matches);

            return self::$matched;

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(0);

        }

    }

}

