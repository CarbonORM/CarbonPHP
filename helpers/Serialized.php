<?php

/**
 * This has been edited to include an auto serialized method.
 * Variables given to the start function will be cached between requests.
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace CarbonPHP\Helpers;

abstract class Serialized {

    /**
     * @var array $sessionVar is an array who's values equal variables
     * names in the global scope.
     */
    private static $sessionVar;

    /** Variables given will be cached between requests.
     * Variables should be provided as string names referencing
     * the global scope.
     * @param array ...$argv
     */
    public static function start(...$argv) : void
	{
        self::$sessionVar = $argv;
		foreach (self::$sessionVar as $value){
			if (isset($_SESSION[__CLASS__][$value])) {
                $GLOBALS[$value] = $_SESSION[__CLASS__][$value];
                //self::is_serialized( base64_decode(  $_SESSION[__CLASS__][$value] ), $GLOBALS[$value] );
            }
		}

		// You CAN register multiple shutdown functions
		register_shutdown_function( function () use ($argv) {
            $last_error = error_get_last();
            if ($last_error['type'] === E_ERROR) {
                sortDump($last_error);
            } else {
                foreach ($argv as $value) {
                    if (isset($GLOBALS[$value])) {
                        $_SESSION[__CLASS__][$value] = $GLOBALS[$value];
                        /// $_SESSION[__CLASS__][$value] = base64_encode( serialize( $GLOBALS[$value] ) );
                    }
                }
            }
		} );
	}

    /**
     * Clear any variables given to the start method.
     * This does not stop caching, just removes all data
     * from current variables and cache.
     */
    public static function clear() : void
    {
        if (!empty(self::$sessionVar)) {
            foreach (self::$sessionVar as $value) {
                $GLOBALS[$value] = $_SESSION[$value] = null;
            }
        }
    }


    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation
     * to throw out obviously incorrect strings. Unserialize is then run
     * on the string to perform the final verification.
     *
     * Valid serialized forms are the following:
     * <ul>
     * <li>boolean: <code>b:1;</code></li>
     * <li>integer: <code>i:1;</code></li>
     * <li>double: <code>d:0.2;</code></li>
     * <li>string: <code>s:4:"test";</code></li>
     * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
     * <li>object: <code>O:8:"stdClass":0:{}</code></li>
     * <li>null: <code>N;</code></li>
     * </ul>
     *
     * @author		Chris Smith <code+php@chris.cs278.org>
     * @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license		http://sam.zoy.org/wtfpl/ WTFPL
     * @param		string	$value	Value to test for serialized form
     * @param		mixed	$result	Result of unserialize() of the $value
     * @return		boolean			True if $value is serialized data, otherwise false
     */
	public static function is_serialized($value, &$result = null) : bool
	{
		// Bit of a give away this one
		if (!\is_string( $value ))
			return false;

		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;') {
			$result = false;
			return true;
		}

		$length = \strlen( $value );
		$end = '';

		switch ($value[0]) {
			case 's':
				if ($value[$length - 2] !== '"') {
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';

				if ($value[1] !== ':') {
					return false;
				}

				switch ($value[2]) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						break;

					default:
						return false;
				}
			case 'N':
				$end .= ';';

				if ($value[$length - 1] !== $end[0]) {
					return false;
				}
				break;

			default:
				return false;
		}

		if (($result = @unserialize( $value, true )) === false) {
			$result = null;
			return false;
		}
		return true;
	}
}