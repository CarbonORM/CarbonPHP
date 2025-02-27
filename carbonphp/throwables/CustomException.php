<?php

declare(strict_types=1);

/**
 * Quickly make custom exceptions. As seen on
 * http://php.net/manual/en/language.exceptions.php
 */

namespace CarbonPHP\Throwables;

use CarbonPHP\Interfaces\iException;
use Exception;

abstract class CustomException extends Exception implements iException
{
    protected $message = 'CustomException';     // Exception message

    protected $code = 0;                       // User-defined exception code

    public function __construct(?string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
