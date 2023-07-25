<?php
/**
 * Quickly make custom exceptions. As seen on
 * http://php.net/manual/en/language.exceptions.php
 */

namespace CarbonPHP\Error;

use CarbonPHP\Interfaces\iException;
use Exception;
use Throwable;

abstract class CustomException extends Exception implements iException
{

    protected $message = 'CustomException';     // Exception message

    protected $code = 0;                       // User-defined exception code

    public function __construct(string|null $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
