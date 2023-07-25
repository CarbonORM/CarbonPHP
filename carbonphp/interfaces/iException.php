<?php
/**
 * Taken from
 * http://php.net/manual/en/language.exceptions.php
 * for quickly implementing custom exceptions
 */

namespace CarbonPHP\Interfaces;

interface iException
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formatted string of trace

    /* Overridable methods inherited from Exception class */
    public function __toString();                 // formatted string for display
    public function __construct(string|null $message = null, int $code = 0);
}