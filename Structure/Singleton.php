<?php

namespace Carbon;

use Carbon\Error\ErrorCatcher;
use Carbon\Helpers\Serialized;

/**
 * Trait Singleton
 * @package Carbon
 *
 * Singeltons are considered an anti-pattern if being utilized during runtime.
 * This class is designed to give you a lot of features for the quick and dirty.
 * PHP has magic methods that allow you to do almost anything to the syntax, even
 * make it more Javascript like. Adding
 *
 * class Example {
 *      use Singleton;
 *      const Singleton = true;  // for auto class serialization
 *
 *      ...
 * }
 *
 * Will cause serializable variables defined in the class to be stored between sessions.
 * The Caveat:
 *      Instead of using the new statement you must use getInstance() in a static context.
 *      $obj = Example::getInstance();
 *
 * Javascript fans: If singleton is used, the following syntax becomes available
 *
 *      $obj->newClosure = function () { print "Closure Binding"; };
 *
 *      $obj->newClosure();
 *
 * This will store the new closure in the methods variable and attempt to run it using
 * call_user_func_array();
 *
 */

trait Singleton
{
    /**
     * @var
     */
    public $storage;                // A Temporary variable for 'quick data'
    /**
     * @var array
     */
    protected $methods = array();   // Anonymous Function Declarations

    /**
     * @param $methodName
     * @param array $arguments
     * @return Singleton|mixed
     */
    public static function __callStatic($methodName, $arguments = array())
    {
        return self::getInstance()->Skeleton($methodName, $arguments);
    }

    /**
     * @param array ...$args
     * @return self
     */
    public static function newInstance(...$args): self
    {   // Start a new instance of the class and pass any arguments
        self::clearInstance();
        $reflect = new \ReflectionClass($class = static::class);
        $GLOBALS['Singleton'][$class] = $reflect->newInstanceArgs($args);

        return $GLOBALS['Singleton'][$class];
    }

    /**
     * @param array ...$args
     * @return self
     */
    public static function getInstance(...$args): self
    {   // see if the class has already been called this run
        if (!empty($GLOBALS['Singleton'][$calledClass = static::class])) {
            return $GLOBALS['Singleton'][$calledClass];
        }
        // check if the object has been sterilized in the session
        // This will invoke the __wake up operator   TODO - base64_decode
        if (isset($_SESSION[$calledClass]) && Serialized::is_serialized($_SESSION[$calledClass], $GLOBALS['Singleton'][$calledClass]))
            return $GLOBALS['Singleton'][$calledClass];

        // Start a new instance of the class and pass any arguments
        $class = new \ReflectionClass($calledClass);
        $GLOBALS['Singleton'][$calledClass] = $class->newInstanceArgs($args);

        return $GLOBALS['Singleton'][$calledClass];
    }


    /**
     * @param self $object
     * @return self
     */
    public static function setInstance(self $object): self
    {
        return $GLOBALS['Singleton'][static::class] = $object;
    }


    /**
     * Remove the current instance. Also removes any serialized data.
     */
    public static function clearInstance(): void
    {
        unset($_SESSION[$calledClass = static::class], $GLOBALS['Singleton'][$calledClass]);
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @return Singleton|mixed
     */
    public function __call($methodName, array $arguments = [])
    {
        return $this->Skeleton($methodName, $arguments);
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @return Singleton|mixed
     * @throws \InvalidArgumentException
     */
    private function Skeleton(string $methodName, array $arguments = [])
    {
        // Have we used addMethod() to override an existing method
        if (array_key_exists($methodName, $this->methods)) {
            return (null === ($result = \call_user_func_array($this->methods[$methodName], $arguments)) ? $this : $result);
        }
        // Is the method in the current scope ( public, protected, private ).
        // Note declaring the method as private is the only way to ensure single instancing
        if (method_exists($this, $methodName)) {
            return (null === ($result = \call_user_func_array(array($this, $methodName), $arguments)) ? $this : $result);
        }
        // Check to see if we've bound it to the class using an undefined attribute (variable)
        if (\is_callable($this->{$methodName})) {
            $this->addMethod($methodName, $this->{$methodName});
            return \call_user_func_array($this->{$methodName}, $arguments);
        }
        // closure binding
        if (array_key_exists('closures', $GLOBALS) && array_key_exists($methodName, $GLOBALS['closures'])) {
            $function = $GLOBALS['closures'][$methodName];
            $this->addMethod($methodName, $function);
            return (null === ($result = \call_user_func_array($this->methods[$methodName], $arguments)) ? $this : $result);
        }
        throw new \InvalidArgumentException("There is valid method or closure with the given name '$methodName' to call");
    }

    /** Attempt to bind a closure to the working scope. This would mean the calling class
     * not the class which uses the Singelton trait
     * @param $name
     * @param $closure
     */
    private function addMethod(string $name, callable $closure): void
    {
        $this->methods[$name] = \Closure::bind($closure, $this, static::class);
    }

    /**
     *  If a previous class object was stored in our session then getInstance()
     *  will return the un-serialized object.
     */
    public function __wakeup()
    {
        $object = get_object_vars($this);
        foreach ($object as $item => $value) {
            Serialized::is_serialized($value, $this->$item);
        }
        if (method_exists($this, '__construct')) {
            $this->__construct();
        }
    }

    /** Attempt to serialize the current class.
     * @return array|null
     */
    public function __sleep()
    {
        if (!\defined('self::Singleton') || !self::Singleton) {
            return [];
        }

        foreach (get_object_vars($this) as $key => &$value) {
            if (empty($value)) {
                continue;
            }   // The object could be null from serialization?
            if (\is_object($value)) {
                try {
                    $this->$key = (@serialize($value));
                } catch (\Exception $e) {
                    continue;
                }                // Database object we need to catch the error thrown.
            }
            $onlyKeys[] = $key;
        }
        return $onlyKeys ?? [];
    }

    /**
     *   Attempt to serialize the current class.
     */
    public function __destruct()
    {   // We require a sleep function to be set manually for singleton to manage utilization
        if (!\defined('self::Singleton') || !self::Singleton) {
            return;
        }
        try {
            $_SESSION[__CLASS__] = @serialize($this);     // TODO - base64_encode(
        } catch (\Exception $e) {
            unset($_SESSION[__CLASS__]);
            return;
        }
    }

    /**
     * @param string $variable a global variable
     * @return mixed
     */
    public function &__get(string $variable)   // TODO - MAJOR rework, store in $storage
    {
        return $GLOBALS[$variable];
    }

    /**
     * @param string $variable variable name to set to the global scope
     * @param mixed $value
     */
    public function __set(string $variable, $value)
    {
        if (\is_callable($value)) {
            $this->{$variable} = $value->bindTo($this, $this);  // This preserves the 'normal' new attribute closure binding
        } else {
            $GLOBALS[$variable] = $value;
        }
    }

    /**
     * @param string $variable check the global scope for the given variable name
     * @return bool
     */
    public function __isset(string $variable): bool
    {
        return array_key_exists($variable, $GLOBALS);
    }

    /**
     * @param string $variable unset the variable from the global scope
     */
    public function __unset(string $variable)
    {
        unset($GLOBALS[$variable]);
    }

    /**
     * @return mixed the value(s) contained in our $storage attribute
     */
    public function __invoke()
    {
        return $this->storage;
    }

    /**
     * @param array ...$argv comma separated parameters to be set to the storage attribute
     * @return self
     */
    public function set(...$argv): self
    {
        $this->storage = $argv;
        return $this;
    }

    /**
     * @param string $variable
     * @return mixed
     */
    public function get(string $variable = null)
    {
        return ($variable === null ?
            $this->storage :
            $this->{$variable});
    }

    /**
     * @param string $variable the variable name to
     * lookup. Will return true if a variable matching the
     * given param exists and is accessible in the global or
     * current scope. This does not work for variable methods
     * added to the current scope via closures.
     * @return bool
     */
    public function has(string $variable): bool
    {
        return isset($this->$variable);
    }
}
