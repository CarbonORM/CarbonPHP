<?php
/**
 * as seen on
 * http://php.net/manual/en/language.exceptions.php
 *
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/16/17
 * Time: 5:11 PM
 */

namespace Carbon\Error;


use Carbon\Interfaces\iException;

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
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
        . "{$this->getTraceAsString()}";
    }
}
