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

abstract class Route
{
    /**
     * @var array $uriExplode will hold an exploded array with the
     * back slash '\' as our delimiter
     */
    public array $uriExplode;
    /**
     * @var string $uri full uri from server request $_SERVER['REQUEST_URI']
     * back slash '\' as our delimiter
     */
    public string $uri;
    /**
     * @var int $uriLength will hold the current number of fields
     * separated by backslashes
     */
    public int $uriLength;
    /**
     * @var bool|string $matched will equal "true" if the
     * current state has not executed a lambda function in response.
     * If a function has been executed the value of matched will be
     * true;
     */
    public bool $matched = false;             // a bool
    /**
     * @var callable $closure will hold the function to execute if
     * the match function should accept a given path-to-match. Arguments
     * can be specified in the variable length parameter field $argv.
     * See Route.Match(...)
     */
    protected $closure;           // The MVC pattern is currently passes


    public const MATCH_C6_ENTITY_ID_REGEX = '([a-fA-F0-9]{20,35})';

    /**
     * This function must be implemented by the user to use the Route Class.
     * If no url is provided in $_SERVER['REQUEST_URI'], or $matched is
     * false when this class destructs the default route will be executed.
     * @return mixed
     */
    abstract public function defaultRoute();

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
            if (CarbonPHP::$safelyExit || $this->matched || CLI || TEST) {
                return;
            }
            if (SOCKET) {
                print 'Socket Left Route Class Un-Matched' . PHP_EOL;
                exit(1);
            }
            $this->matched = true;
            $this->defaultRoute();
        } catch (Throwable $e) {
            ErrorCatcher::generateBrowserReportFromError($e);
        }
    }


    /**
     * Route constructor. If the url is / or null then our
     * default route will be invoked.
     * @param callable|null $structure
     * @throws PublicAlert
     */
    public function __construct(callable $structure = null)
    {
        $this->closure = $structure;
        // This check allows Route to be independent of Carbon/Application, but benefit if we've already initiated
        if (\defined('URI') && !SOCKET) {
            $this->uri = URI;
            $this->uriExplode = explode('/', trim(URI, '/'));
            $this->uriLength = \count($this->uriExplode);
        } else {
            $this->uri = trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)));
            $this->uriExplode = explode('/', $this->uri, ' /');
            $this->uriLength = \substr_count($this->uri, '/') + 1; // I need the exploded string
        }
    }

    /**
     * @param string $uri
     */
    public function changeURI(string $uri): void
    {
        $this->uri = $uri = trim($uri, '/');
        $this->uriExplode = explode('/', $uri);
        $this->uriLength = substr_count($uri, '/') + 1;
        $this->matched = false;
    }

    /** This will return the current value of $this->matched.
     * Added to allow us to quickly check the status using the
     * (string) type cast
     * @return bool
     */
    public function __invoke()
    {
        return (bool)$this->matched;
    }

    /** This sets a default route to execute immediately when
     * our match function is successful. Useful when multiple routes
     * rely on the same algorithm. Parameters used in the closure
     * may be specified using the match function.
     * @param callable|null $struct
     * @return Route
     */
    public function structure(callable $struct = null): Route
    {
        $this->closure = $struct;
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
            if (1 > @preg_match_all($regexToMatch, $this->uri, $matches, PREG_SET_ORDER)) {  // can return 0 or false
                return $this;
            }
        } catch (Throwable $exception) {
            //ErrorCatcher::generateLog($exception);
            throw new PublicAlert('The following regex failed :: ' . $regexToMatch);
        }
        $this->matched = true;
        $matches = array_shift($matches);
        array_shift($matches);  // could care less about the full match
        // Variables captured in the path to match will passed to the closure
        if (is_callable($argv[0])) {
            if (call_user_func_array($argv[0], $matches) === false) {
                throw new PublicAlert('Bad Closure Passed to Route::match(). The return value must not be false.');
            }
            return $this;
        }
        // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
        if (is_callable($this->closure)) {
            $argv[] = &$matches;        // todo - review this logic
            call_user_func_array($this->closure, $argv);
            return $this;
        }
        return $this;
    }

    /** This is our main uri routing mechanism.
     *
     *  syntactically similar to Laravel's routing, what
     *  should probably be done with regex was implemented
     *  by a younger novice me.
     *
     * @param string $pathToMatch
     * @param array ...$argv
     * @return Route
     * @throws PublicAlert
     * @deprecated use matchRegex instead
     */
    public function match(string $pathToMatch, ...$argv): self
    {
        $uri = $this->uriExplode;
        $arrayToMatch = explode('/', trim($pathToMatch, '/'));
        $pathLength = \count($arrayToMatch);
        $pathLength === 0 and $pathToMatch = '*';   // shorthand if stmt
        // The order of the following
        if ($pathLength < $this->uriLength && substr($pathToMatch, -1) !== '*') {
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
                    $this->matched = true;
                    $referenceVariables = [];
                    foreach ($variables as $key => $value) {
                        $GLOBALS[$key] = $value;                    // this must be done in two lines
                        $referenceVariables[] = &$GLOBALS[$key];
                    }
                    // Variables captured in the path to match will passed to the closure
                    if (is_callable($argv[0])) {
                        if (call_user_func_array($argv[0], $referenceVariables) === false) {
                            throw new PublicAlert('Bad Closure Passed to Route::match()');
                        }
                        return $this;
                    }
                    // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
                    if (is_callable($this->closure)) {
                        $argv[] = &$referenceVariables;
                        call_user_func_array($this->closure, $argv);
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
                    $value = null;
                    if (array_key_exists($i, $uri)) {
                        $value = $uri[$i];
                    }
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




