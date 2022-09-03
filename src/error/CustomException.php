<?php
/**
 * Quickly make custom exceptions. As seen on
 * http://php.net/manual/en/language.exceptions.php
 */

namespace CarbonPHP\Error;

use CarbonPHP\Interfaces\iException;
use Exception;

abstract class CustomException extends Exception implements iException
{

    protected $message = 'CustomException';     // Exception message

    protected $code    = 0;                       // User-defined exception code

    public function __construct($message = null, $code = 0)
    {

        if (!$message) {

            throw new $this('Unknown '. \get_class($this));

        }

        parent::__construct($message, $code);

    }

}

