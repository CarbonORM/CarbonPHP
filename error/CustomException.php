<?php
/**
 * Quickly make custom exceptions. As seen on
 * http://php.net/manual/en/language.exceptions.php
 */

namespace CarbonPHP\Error;

use CarbonPHP\interfaces\iException;

abstract class CustomException extends \Exception implements iException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code

    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown


    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. \get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return \get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . $this->getTraceAsString();
    }
}
