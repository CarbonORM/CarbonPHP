<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/13/17
 * Time: 4:28 AM
 */

namespace Carbon;

use Carbon\Helpers\Files;

class Request
{
    use Singleton;

    ########################## Manual Input ################################
    public function set(...$argv): self
    {
        $this->storage = $argv;
        return $this;
    }

    ########################## Session Storage #############################
    public static function sendHeaders()
    {
        if (SOCKET || headers_sent()) return null;

        if (isset( $_SESSION['Cookies'] ) && is_array( $_SESSION['Cookies'] ))
            foreach ($_SESSION['Cookies'] as $key => $array) static::setCookie( $key, $array[0], $array[1] );

        if (isset( $_SESSION['Headers'] ) && is_array( $_SESSION['Headers'] ))
            foreach ($_SESSION['Headers'] as $value) static::setHeader( $value );

        unset( $_SESSION['Cookies'], $_SESSION['Headers'] );
    }

    public static function setCookie($key, $value = null, $time = 604800) // Week?
    {
        if (headers_sent()) $_SESSION['Cookies'][] = [$key => [$value, $time]];
        else setcookie( $key, $value, time() + $time, '/', $_SERVER['SERVER_NAME'], (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off'), true );
    }

    public function clearCookies()
    {
        $all = array_keys( is_array( $_COOKIE ) ? $_COOKIE : [] );
        foreach ($all as $key => $value)
            static::setCookie( $value );
    }       // Supporting function to setCookie

    public static function setHeader($string)
    {
        if (headers_sent()) $_SESSION['Headers'][] = $string;
        else header( $string, true );
    }

    public static function changeURI($string)
    {
        $_SERVER['REQUEST_URI'] = $string;
        static::setHeader( "X-PJAX-URL: " . SITE . $string );
    }


    ########################### Request Data ###############################
    private function request($argv, &$array, $removeHTML = false): self
    {
        $this->storage = null;
        $closure = function ($key) use ($removeHTML, &$array) {
            if (array_key_exists( $key, $array )) {
                $this->storage[] = $removeHTML ? htmlspecialchars( $array[$key] ) : $array[$key];
                $array[$key] = null;    // by reference, if we validate it then we should ensure no one uses it
            } else $this->storage[] = false;
        };
        if (count( $argv ) == 0 || !array_walk( $argv, $closure )) $this->storage = [];
        return $this;
    }

    public function get(...$argv): self
    {
        return $this->request( $argv, $_GET );
    }

    public function post(...$argv): self
    {
        return $this->request( $argv, $_POST );
    }

    public function cookie(...$argv): self
    {
        return $this->request( $argv, $_COOKIE, true );
    }

    public function files(...$argv): self
    {
        return $this->request( $argv, $_FILES );
    }

    ########################### Store Files   #############################
    public function storeFiles($location = 'Data/Uploads/Temp/')
    {
        $storagePath = array();
        array_walk( $this->storage, function ($file) use ($location, &$storagePath) {
            $storagePath[] = Files::storeFile( $file, $location );
        } );
        return count( $storagePath ) == 1 ? array_shift( $storagePath ) : $storagePath;
    }

    ##########################  Storage Shifting  #########################
    public function base64_decode(): self
    {
        $array = [];
        $lambda = function ($key) use (&$array) {
            $array[] = base64_decode( $key, true );
        };

        if (is_array( $this->storage )) array_walk( $this->storage, $lambda );
        elseif ($this->storage != null) $lambda( $this->storage );
        $this->storage = $array;

        return $this;
    }

    public function has($key): bool
    {
        return array_key_exists( $key, $this->storage );
    }

    public function except(...$argv): self
    {
        array_walk( $argv, function ($key) {
            if (array_key_exists( $key, $this->storage )) unset( $this->storage[$key] );
        } );
        return $this;
    }

    ########################## Validating    ##############################
    public function is($type): bool
    {
        $type = 'is_' . strtolower( $type );
        if (function_exists( $type )) return $type( $this->storage );
        throw new \InvalidArgumentException( 'no valid function is_$type' );
    }

    public function regex($condition)
    {
        if (empty( $this->storage )) return false;

        $array = [];
        $regex = function ($key) use ($condition, &$array) {
            return $array[] = (preg_match( $condition, $key ) ? $key : false);
        };

        return (array_walk( $this->storage, $regex ) ?
            count( $array ) == 1 ? array_shift( $array ) : $array :
            $regex( $this->storage ));
    }   // Match a pcal regex expression

    public function noHTML($complete = false)
    {   // Disallow: $, ", ', <, >
        if ($this->storage == null) return false;
        $array = [];
        $lambda = function ($key) use (&$array) {
            return $array[] = htmlspecialchars( $key );
        };

        $this->storage = (array_walk( $this->storage, $lambda ) ? $array : false);

        return ($complete && $this->storage ?
            (count( $array ) == 1 ? array_shift( $array )
                : $array)
            : $this);
    }

    public function int(int $min = null, int $max = null)   // inclusive max and min
    {
        if ($this->storage == null) return false;

        $array = [];
        $integer = function ($key) use (&$array, $min, $max) {
            if (($key = intval( $key )) === false) return $array[] = false;
            if ($max !== null) $key = ($key <= $max ? $key : false);
            if ($min !== null) $key = ($key >= $min ? $key : false);
            return $array[] = $key;
        };

        return (array_walk( $this->storage, $integer ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            false);
    }

    public function date()
    {
        return $this->regex( '#^\d{1,2}\/\d{1,2}\/\d{4}$#' );
    }

    public function float()
    {
        if ($this->storage == null) return false;

        $array = [];
        $lambda = function ($key) use (&$array) {
            return $array[] = floatval( $key );
        };

        return (array_walk( $this->storage, $lambda ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            false);
    }

    public function alnum()
    {
        if ($this->storage == null) return false;

        $array = [];
        $alphaNumeric = function ($key) use (&$array) {
            return $array[] = (ctype_alnum( $key ) ? $key : false);
        };

        return (array_walk( $this->storage, $alphaNumeric ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            $alphaNumeric( $this->storage ));
    }           // One word alpha numeric

    public function text()
    {
        return $this->regex( '/([\w\s])+/' );   // Multiple word alpha numeric
    }

    public function word()
    {   // One word alpha
        return $this->regex( '/^[a-zA-Z]+$/' );
    }

    public function phone()
    {
        return (preg_match( '#((\(\d{3}\) ?)|(\d{3}-))?\d{3}-\d{4}#', $this->storage[0] ) ? $this->storage[0] : false);
    }

    public function email()
    {
        if (empty( $this->storage )) return false;
        return filter_var( array_shift( $this->storage ), FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE );
    }

    public function website()
    {
        $array = [];
        $lambda = function ($key) use (&$array) {
            $array[] = filter_var( $key, FILTER_VALIDATE_URL );
        };
        return (array_walk( $this->storage, $lambda ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            $lambda( $this->storage ));
    }

    public function value()
    {
        return count( $this->storage ) == 1 ? array_shift( $this->storage ) : $this->storage;
    }


}