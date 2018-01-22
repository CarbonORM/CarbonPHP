<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 1/13/17
 * Time: 4:28 AM
 */

namespace Carbon;

use Carbon\Helpers\Files;

class Request   // requires carbon::application;
{
    use Singleton; // provides us with the variable $storage which we will call the `set` or group of key => value pairs

    ########################## Manual Input ################################

    /** Adds comma separated parameters to the set
     * @param array ...$argv
     * @return Request
     */
    public function set(...$argv): self
    {
        $this->storage = $argv;
        return $this;
    }

    ########################## Session Storage #############################
    /** Headers can only be sent and set if output has not already started.
     *  If we attempt to set a header after output has started, store it and
     *  send on the next request.
     * @return null
     */
    public static function sendHeaders()
    {
        if (SOCKET || headers_sent()) return null;

        if (isset( $_SESSION['Cookies'] ) && is_array( $_SESSION['Cookies'] ))
            foreach ($_SESSION['Cookies'] as $key => $array)
                static::setCookie( $key, $array[0] ?? null , $array[1] ?? null );

        if (isset( $_SESSION['Headers'] ) && is_array( $_SESSION['Headers'] ))
            foreach ($_SESSION['Headers'] as $value) static::setHeader( $value );

        unset( $_SESSION['Cookies'], $_SESSION['Headers'] );
    }

    /** Cookies are a pain to set up as they also rely on headers not being sent.
     *  This method makes setting cookies easy with three params.
     * @param string $key the name of the cookie
     * @param mixed $value
     * @param int $time the expiration time of our cookie
     */
    public static function setCookie(string $key, $value = null, int $time = 604800) // Week?
    {
        if (headers_sent()) $_SESSION['Cookies'][] = [$key => [$value, $time]];
        else setcookie( $key, $value, time() + $time, '/', $_SERVER['SERVER_NAME'], (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off'), true );
    }


    /**
     * Clear all active cookies in the current session
     */
    public function clearCookies()
    {
        $all = array_keys( is_array( $_COOKIE ) ? $_COOKIE : [] );
        foreach ($all as $key => $value)
            static::setCookie( $value );
    }       // Supporting function to setCookie


    /**
     * @param string $string is passed to the php header function
     * @link http://php.net/manual/en/function.header.php
     * @return bool
     */
    public static function setHeader(string $string)
    {
        if (defined('SOCKET') && SOCKET) return false;
        if (headers_sent()) $_SESSION['Headers'][] = $string;
        else header( $string, true );
    }

    /** This method requires that PJAX be a loaded JS include in
     * our html page. This does not hard refresh the url, or execute
     * a new instance on the uri's route... It just changes the uri
     * in the browser.
     * @param string $string the new uri location.
     */
    public static function changeURI(string $string)
    {
        $_SERVER['REQUEST_URI'] = $string;
        static::setHeader( "X-PJAX-URL: " . SITE . $string );
    }


    ########################### Request Data ###############################

    /** Request is a blanket method for other data validation procedures.
     * @param array $argv values should match the keys in the given set
     * @param array $array usually a transfer protocol ($_POST, $_GET, $_COOKIE)
     * @param bool $removeHTML if true htmlspecialchars() will be run on each member of the set
     * @link http://php.net/manual/en/function.htmlspecialchars.php
     * @return Request
     */
    private function request(array $argv, array &$array, bool $removeHTML = false): self
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

    /** Filter the $_GET array with the arguments passed in
     * @param array ...$argv
     * @return Request
     */
    public function get(...$argv): self
    {
        return $this->request( $argv, $_GET );
    }

    /** Filter the $_POST array with the arguments passed in
     * @param array ...$argv
     * @return Request
     */
    public function post(...$argv): self
    {
        return $this->request( $argv, $_POST );
    }

    /** Filter the $_COOKIE array with the arguments passed in
     * @param array ...$argv
     * @return Request
     */
    public function cookie(...$argv): self
    {
        return $this->request( $argv, $_COOKIE, true );
    }

    /** Filter the $_FILES array with the arguments passed in
     * @param array ...$argv
     * @return Request
     */
    public function files(...$argv): self
    {
        return $this->request( $argv, $_FILES );
    }

    ########################### Store Files   #############################

    /** storeFiles attempts to store files currently in the storage set
     * @param string $location the location to store the files
     * @return array|mixed
     */
    public function storeFiles(string $location = 'Data/Uploads/Temp/')
    {
        $storagePath = array();
        array_walk( $this->storage, function ($file) use ($location, &$storagePath) {
            $storagePath[] = Files::uploadFile( $file, $location );
        } );
        return count( $storagePath ) == 1 ? array_shift( $storagePath ) : $storagePath;
    }

    ##########################  Storage Shifting  #########################

    /** Attempts to base64 decode all members of our set
     * @return Request
     */
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


    /** Checks our set to see if our set contains the key
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists( $key, $this->storage );
    }

    /** Removes passed arguments from the set
     * @param array ...$argv
     * @return Request
     */
    public function except(...$argv): self
    {
        array_walk( $argv, function ($key) {
            if (array_key_exists( $key, $this->storage )) unset( $this->storage[$key] );
        } );
        return $this;
    }

    ########################## Validating    ##############################

    /** Use a built-in PHP "is_" method on a given set.
     *  If a member of the set is not valid its value will be set to false
     *  Possible options include:
     *      a
     *      array
     *      bool
     *      callable
     *      double
     *      dir
     *      executable
     *      file
     *      finite
     *      float
     *      infinite
     *      int
     *      integer
     *      iterable
     *      link
     *      long
     *      nan
     *      null
     *      numeric
     *      object
     *      readable
     *      real
     *      scalar
     *      soap_fault
     *      string
     *      resource
     *      uploaded_file
     *      is_writeable
     *      is_writable
     *
     * @param string $type
     * @return array|bool|mixed
     */
    public function is(string $type)
    {
        if (!function_exists( $type = 'is_' . strtolower( $type )))
            throw new \InvalidArgumentException( 'no valid function is_$type' );

        $array = [];
        $is = function ($key) use ($type, &$array) {
            $array[] = $type($key) ? $key : false;
        };

        if (is_array( $this->storage )) array_walk( $this->storage, $is );
        elseif ($this->storage != null) $is( $this->storage );
        $this->storage = $array;

        return (array_walk( $this->storage, $is ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            false);
    }

    /** Preforms a regex expression using preg_match on each member of the set
     * @link http://php.net/manual/en/function.preg-match.php
     * @param string $condition
     * @return array|bool|mixed
     */
    public function regex(string $condition)
    {
        if (empty( $this->storage )) return false;

        $array = [];
        $regex = function ($key) use ($condition, &$array) {
            return $array[] = (preg_match( $condition, $key ) ? $key : false);
        };

        return (array_walk( $this->storage, $regex ) ?
            count( $array ) == 1 ? array_shift( $array ) : $array :
            $regex( $this->storage ));
    }

    /** Removes HTML chars from a given set
     * @param bool $complete if set to true than $this
     * will be returned
     * @return array|bool|Request|mixed
     */
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


    /** Returns found integers between the given min and max if set. If no max
     * or min is set, just validate integers.
     * @param int|null $min
     * @param int|null $max
     * @return array|bool|mixed
     */
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

    /** Runs a regex expression to find dates matching the pattern
     *      11/12/1995
     * @return array|bool|mixed
     */
    public function date()
    {
        return $this->regex( '#^\d{1,2}\/\d{1,2}\/\d{4}$#' );
    }


    /** Check for alphanumeric character(s)
     * @link http://php.net/manual/en/function.ctype-alnum.php
     * @return array|bool|mixed
     */
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

    /**
     * Filter text only with regex
     * @return array|bool|mixed
     */
    public function text()
    {
        return $this->regex( '/([\w\s])+/' );   // Multiple word alpha numeric
    }

    /** Filter single words containing only letters
     * @return array|bool|mixed
     */
    public function word()
    {
        return $this->regex( '/^[\w]+$/' );
    }

    /** Filters phone numbers matching the pattern
     *        #((\(\d{3}\) ?)|(\d{3}-))?\d{3}-\d{4}#
     * @return bool
     */
    public function phone()
    {
        return (preg_match( '#((\(\d{3}\) ?)|(\d{3}-))?\d{3}-\d{4}#', $this->storage[0] ) ? $this->storage[0] : false);
    }

    /**Checks to see if emails names are given in the set
     * @return bool|mixed
     */
    public function email()
    {
        $array = [];
        $lambda = function ($key) use (&$array) {
            $array[] = filter_var( $key, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE );
        };
        return (array_walk( $this->storage, $lambda ) ?
            (count( $array ) == 1 ? array_shift( $array ) : $array) :
            $lambda( $this->storage ));
    }

    /** Checks to see if domain names are given in the set
     * @link http://php.net/manual/en/function.filter-var.php
     * @return array|mixed
     */
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

    /** returns our full current set
     * @return mixed
     */
    public function value()
    {
        return count( $this->storage ) == 1 ? array_shift( $this->storage ) : $this->storage;
    }


}