<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/17/17
 * Time: 11:48 AM
 */

namespace CarbonPHP;


use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use Throwable;
use function count;
use function defined;
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

    /**
     * @var callable $closure will hold the function to execute if
     * the match function should accept a given path-to-match. Arguments
     * can be specified in the variable length parameter field $argv.
     * See Route.Match(...)
     */
    protected static $closure;           // The MVC pattern is currently passes


    public const MATCH_C6_ENTITY_ID_REGEX = '([a-fA-F0-9]{20,35})';

    /**
     * This function must be implemented by the user to use the Route Class.
     * If no url is provided in $_SERVER['REQUEST_URI'], or $matched is
     * false when this class destructs the default route will be executed.
     * @return mixed
     */
    abstract public function defaultRoute() : void;

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
                print 'Socket Left Route Class Un-Matched' . PHP_EOL;
                exit(1);
            }

            self::$matched = true;

            $this->defaultRoute();

        } catch (Throwable $e) {
            ErrorCatcher::generateBrowserReportFromThrowable($e);
        }
    }


    /**
     * Route constructor. If the url is / or null then our
     * default route will be invoked.
     * @param callable|null $structure
     */
    public function __construct(callable $structure = null)
    {
        self::$closure = $structure;

        // This check allows Route to be independent of Carbon/Application, but benefit if we've already initiated
        if (isset(CarbonPHP::$uri) && !CarbonPHP::$socket) {
            self::$uri = CarbonPHP::$uri;

            self::$uriExplode = explode('/', trim(CarbonPHP::$uri, '/'));

            self::$uriLength = count(self::$uriExplode);
        } else {
            self::$uri = trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'] ?? '')), PHP_URL_PATH)));

            self::$uriExplode = explode('/', self::$uri,);

            self::$uriLength = substr_count(self::$uri, '/') + 1; // I need the exploded string
        }
    }

    /**
     * @param string $uri
     */
    public function changeURI(string $uri): void
    {
        self::$uri = $uri = trim($uri, '/');

        self::$uriExplode = explode('/', $uri);

        self::$uriLength = substr_count($uri, '/') + 1;

        self::$matched = false;
    }

    /** This will return the current value of  self::$matched.
     * Added to allow us to quickly check the status using the
     * (string) type cast
     * @return bool
     */
    public function __invoke() : bool
    {
        return (bool) self::$matched;
    }

    /** This sets a default route to execute immediately when
     * our match function is successful. Useful when multiple routes
     * rely on the same algorithm. Parameters used in the closure
     * may be specified using the match function.
     * @param callable|null $struct
     * @return Route
     */
    public function structure(callable $struct = null) : self
    {
        self::$closure = $struct;

        return $this;
    }


    /**
     * @param string $regexToMatch
     * @param mixed ...$argv
     * @return $this
     * @throws PublicAlert
     */
    public function regexMatch(string $regexToMatch, ...$argv): self
    {
        $matches = [];

        try {
            if (1 > @preg_match_all($regexToMatch, self::$uri, $matches, PREG_SET_ORDER)) {  // can return 0 or false
                return $this;
            }
        } catch (Throwable $exception) {
            throw new PublicAlert('The following regex failed :: ' . $regexToMatch);
        }

        self::$matched = true;

        $matches = array_shift($matches);

        array_shift($matches);  // could care less about the full match

        // Variables captured in the path to match will passed to the closure
        if (is_callable($argv[0])) {

            $callable = array_shift($argv);

            $argv = array_merge($argv, $matches);

            call_user_func_array($callable, $argv); // I'm ignoring this return now,

            return $this;
        }

        // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
        if (is_callable(self::$closure)) {

            $argv = array_merge($argv, $matches);

            call_user_func_array(self::$closure, $argv);

            return $this;
        }

        return $this;
    }

    /** This is our main uri routing mechanism.
     *
     *  syntactically similar to Laravel routing, what
     *  should probably be done with regex was implemented
     *  by a younger novice me.
     *
     * @param string $pathToMatch
     * @param array ...$argv
     * @return Route
     * @throws PublicAlert
     * @deprecated use matchRegex instead - no version
     */
    public function match(string $pathToMatch, ...$argv): self
    {
        $uri = self::$uriExplode;

        $arrayToMatch = explode('/', trim($pathToMatch, '/'));

        $pathLength = count($arrayToMatch);

        if ($pathLength === 0) {

            $pathToMatch = '*';   // shorthand if stmt

        }

        // The order of the following
        if ($pathLength < self::$uriLength && substr($pathToMatch, -1) !== '*') {

            return $this;

        }

        $required = true;       // variables can be made optional by `?`

        $variables = array();

        for ($i = 0; $i <= $pathLength; $i++) {

            // set up our ending condition
            if ($pathLength === $i || $arrayToMatch[$i] === null) {

                $arrayToMatch[$i] = '*';

            }

            switch ($arrayToMatch[$i][0]) {
                case  '*':

                     self::$matched = true;

                    $referenceVariables = [];

                    foreach ($variables as $key => $value) {

                        $GLOBALS[$key] = $value;                    // this must be done in two lines

                        $referenceVariables[] = &$GLOBALS[$key];
                    }

                    // Variables captured in the path to match will passed to the closure
                    if (is_callable($argv[0])) {

                        $callable = array_shift($argv);

                        $argv = array_merge($argv, $referenceVariables);

                        call_user_func_array($callable, $argv); // I'm ignoring this return now,

                        return $this;
                    }

                    // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
                    if (is_callable(self::$closure)) {

                        $argv = array_merge($argv, $referenceVariables);

                        call_user_func_array(self::$closure, $argv);

                        return $this;
                    }

                    return $this;

                case '{': // this is going to indicate the start of a variable name

                    if (substr($arrayToMatch[$i], -1) !== '}') {

                        throw new PublicAlert('Variable declaration must be rapped in brackets. ie `/{var}/`');

                    }

                    $variable = rtrim(ltrim($arrayToMatch[$i], '{'), '}');

                    if (substr($variable, -1) === '?' && $variable = rtrim($variable, '?')) {

                        $required = false;

                    }

                    if (empty($variable)) {

                        throw new PublicAlert('Variable must have a name association. ie  `/{var}/`');

                    }

                    $value = $uri[$i] ?? null;

                    if ($required === true && $value === null) {

                        return $this;

                    }

                    $variables[$variable] = $value;

                    break;

                default:

                    if (!array_key_exists($i, $uri)) {

                        return $this;

                    }

                    if (strtolower($arrayToMatch[$i]) !== strtolower($uri[$i])) {

                        return $this;

                    }
            }
        }
        return $this;
    }
}




