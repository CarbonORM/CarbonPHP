<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/17/17
 * Time: 11:48 AM
 */

namespace Carbon;


use Carbon\Error\PublicAlert;

abstract class Route
{
    use Singleton;                // We use the add method function to bind the closure to the class

    /**
     * @var array $uri will hold an exploded array with the
     * back slash '\' as our delimiter
     */
    public $uri;
    /**
     * @var int $uriLength will hold the current number of fields
     * separated by backslashes
     */
    public $uriLength;
    /**
     * @var bool|string $matched will equal "weMatched" if the
     * current state has not executed a lambda function in response.
     * If a function has been executed the value of matched will be
     * true;
     */
    public $matched = false;             // a bool
    /**
     * @var callable $closure will hold the function to execute if
     * the match function should accept a given path-to-match. Arguments
     * can be specified in the variable length parameter field $argv.
     * See Route.Match(...)
     */
    protected $closure;           // The MVC pattern is currently passes

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
     * executed. If matched is set to "weMatched", indicating that
     * no callable has been run, no errors or warnings will be printed.
     * This is by design.
     */
    public function __destruct()
    {
        if ($this->matched || SOCKET) {
            return;
        }
        $this->matched = true;
        $this->defaultRoute();
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
        $this->uri = explode('/', $this->uriLength = ltrim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)), ' /'));
        $this->uriLength = \substr_count($this->uriLength, '/') + 1; // I need the exploded string
        if (empty($this->uri[0])) {
            if (SOCKET) {
                throw new PublicAlert('$_SERVER["REQUEST_URI"] MUST BE SET IN SOCKET REQUESTS');
            }
            $this->matched = true;
            $this->defaultRoute();
        }
    }

    /** This will return the current value of $this->matched.
     * Added to allow us to quickly check the status using the
     * (string) type cast
     * @return string
     */
    public function __toString()
    {
        return (string)(bool)$this->matched;
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


    /** // TODO - comment this
     * @param string $pathToMatch
     * @param array ...$argv
     * @return Route
     * @throws PublicAlert
     */
    public function match(string $pathToMatch, ...$argv): self
    {
        $this->storage = $argv;  // This is for home route function (singleton)

        $uri = $this->uri;

        $arrayToMatch = explode('/', $pathToMatch);

        $pathLength = \count($arrayToMatch);

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
                    $this->matched = 'weMatched';
                    $referenceVariables = [];

                    foreach ($variables as $key => $value) {
                        $GLOBALS[$key] = $value;                    // this must be done in two lines
                        $referenceVariables[] = &$GLOBALS[$key];
                    }

                    // Variables captured in the path to match will passed to the closure
                    if (\is_callable($argv[0])) {
                        $this->addMethod('routeMatched', $argv[0]);
                        if (\call_user_func_array($this->methods['routeMatched'], $referenceVariables) === false) {
                            throw new PublicAlert('Bad Closure Passed to Route::match()');
                        }
                        return $this;
                    }

                    // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
                    if (\is_callable($this->closure)) {
                        $argv[] = &$referenceVariables;
                        $this->addMethod('routeMatched', $this->closure);
                        if (\call_user_func_array($this->methods['routeMatched'], $argv) === false) {
                            throw new PublicAlert('Bad Arguments Passed to Route::match()');
                        }
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




