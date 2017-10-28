<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/17/17
 * Time: 11:48 AM
 */

namespace Carbon;

use Psr\Log\InvalidArgumentException;

abstract class Route
{
    use Singleton;                // We use the add method function to bind the closure to the class

    protected $uri;
    protected $uriLength;
    protected $matched;             // a bool
    protected $structure;           // The MVC pattern is currently passes

    public abstract function defaultRoute($force = false);

    public function __construct(callable $structure = null)
    {
        $this->structure = $structure;
        $this->uri = explode( '/', ltrim( urldecode( parse_url( trim( preg_replace( '/\s+/', ' ', $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) ), ' /' ) );
        $this->uriLength =  $uriLength = sizeof( $this->uri );
        if (empty($this->uri[0])) {
            if (SOCKET) throw new InvalidArgumentException('URL MUST BE SET IN SOCKETS');
            $this->matched = true;
            $this->defaultRoute(true) and exit(1);
        } else
            $this->matched = false;
    }

    public function structure(callable $struct): Route
    {
        $this->structure = $struct;
        return $this;
    }


    public function __destruct()
    {
        if ($this->matched || SOCKET) return null;
        $this->matched = true;
        $this->defaultRoute(true) and exit(1);
    }

    public function closure(callable $closure = null) : self
    {
        if($this->matched !== 'weMatched')
            return $this;
        if ($closure == null)
            if (!is_callable($closure = $this->structure))
                throw new \InvalidArgumentException('We matched but had nothing to call. Make sure you set a structure!');
        call_user_func_array($closure, $this->storage );
        exit(1);
    }

    public function lambda(callable $lambda = null) : self
    {
        if ($this->matched !== 'weMatched') return $this;
        if ($lambda == null)
            if (!is_callable($lambda = $this->structure))
                throw new \InvalidArgumentException('We matched but had nothing to call. Make sure you set a structure!');
        call_user_func_array($lambda, $this->storage );
        $this->matched = true;
        return $this;
    }

    public function match(string $pathToMatch, ...$argv): self     // TODO - rewrite this in REGEX
    {
        $this->storage = $argv;  // This is for home route function (singleton)

        $uri = $this->uri;

        if (($pathLength = sizeof( $arrayToMatch = explode( '/', $pathToMatch ))) <
            $uriLength = $this->uriLength && substr( $pathToMatch, -1 ) != '*')
            return $this;

        $required = true;       // if a variable is found in the code you must
        $variables = array();

        for ($i = 0; $i <= $pathLength; $i++) {

            // set up our ending condition
            if ($pathLength == $i || $arrayToMatch[$i] == null) $arrayToMatch[$i] = '*';

            switch ($arrayToMatch[$i][0]) {
                case  '*':
                    $this->matched = 'weMatched';
                    $referenceVariables = [];

                    foreach ($variables as $key => $value) {
                        $GLOBALS[$key] = $value;                    // this must be done in two lines
                        $referenceVariables[] = &$GLOBALS[$key];
                    }

                    if (is_callable( $argv[0] )) {
                        $this->addMethod('routeMatched', $argv[0]);
                        if (call_user_func_array($this->methods['routeMatched'], $referenceVariables) === false)
                            throw new \Error('Bad Closure Passed to Route::match()');
                        return $this;
                    }

                    if (is_callable($this->structure)) {
                        $this->addMethod('routeMatched', $this->structure);
                        if (call_user_func_array($this->methods['routeMatched'], $argv) === false)
                            throw new \Error('Bad Arguments Passed to Route::match()');
                        return $this;
                    }

                    return $this;

                case '{': // this is going to indicate the start of a variable name

                    if (substr( $arrayToMatch[$i], -1 ) != '}')
                        throw new \InvalidArgumentException( 'Variable declaration must be rapped in brackets. ie `/{var}/`' );

                    $variable = null;
                    $variable = rtrim( ltrim( $arrayToMatch[$i], '{' ), '}' );

                    if (substr( $variable, -1 ) == '?' && $variable = rtrim( $variable, '?' ))
                        $required = false;

                    if (empty($variable)) throw new \Exception('Variable must have a name association. ie  `/{var}/`');

                    $value = null;
                    if (array_key_exists( $i, $uri ))
                        $value = $uri[$i];

                    if ($required == true && $value == null)
                        return $this;

                    $variables[$variable] = $value;
                    break;

                default:
                    if (!array_key_exists( $i, $uri ))
                        return $this;

                    if (strtolower( $arrayToMatch[$i] ) != strtolower( $uri[$i] ))
                        return $this;
            }
        }
        return $this;
    }
}




