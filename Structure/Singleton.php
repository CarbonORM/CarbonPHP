<?php

namespace Carbon;

use Carbon\Helpers\Serialized;

trait Singleton
{
    public $storage;                // A Temporary variable for 'quick data'
    protected $methods = array();   // Anonymous Function Declarations

    public static function __callStatic($methodName, $arguments = array())
    {
        return self::getInstance()->Skeleton($methodName, $arguments);
    }

    public static function newInstance(...$args): self
    {   // Start a new instance of the class and pass any arguments
        self::clearInstance();

        $reflect = new \ReflectionClass($class = get_called_class());

        $GLOBALS['Singleton'][$class] = $reflect->newInstanceArgs($args);

        return $GLOBALS['Singleton'][$class];
    }

    public static function getInstance(...$args): self
    {   // see if the class has already been called this run
        if (!empty($GLOBALS['Singleton'][$calledClass = get_called_class()]))
            return $GLOBALS['Singleton'][$calledClass];

        // check if the object has been sterilized in the session
        // This will invoke the __wake up operator   TODO - base64_decode
        if (isset($_SESSION[$calledClass]) && Serialized::is_serialized($_SESSION[$calledClass], $GLOBALS['Singleton'][$calledClass]))
            return $GLOBALS['Singleton'][$calledClass];

        // Start a new instance of the class and pass any arguments
        $class = new \ReflectionClass($calledClass);
        $GLOBALS['Singleton'][$calledClass] = $class->newInstanceArgs($args);

        return $GLOBALS['Singleton'][$calledClass];
    }

    public static function setInstance(self $object): self
    {
        return $GLOBALS['Singleton'][get_called_class()] = $object;
    }

    public static function clearInstance() : void
    {
        unset($_SESSION[$calledClass = get_called_class()], $GLOBALS['Singleton'][$calledClass]);
    }
    
    public function __call($methodName, $arguments = array())
    {
        return $this->Skeleton($methodName, $arguments);
    }

    private function Skeleton($methodName, $arguments = array())
    {
        // Have we used addMethod() to override an existing method
        if (key_exists($methodName, $this->methods))
            return (null === ($result = call_user_func_array($this->methods[$methodName], $arguments)) ? $this : $result);
        // Is the method in the current scope ( public, protected, private ).
        // Note declaring the method as private is the only way to ensure single instancing
        if (method_exists($this, $methodName))
            return (null === ($result = call_user_func_array(array($this, $methodName), $arguments)) ? $this : $result);

        if (is_callable($this->{$methodName})) {
            self::addMethod($methodName, $this->{$methodName});
            return call_user_func_array($this->{$methodName}, $arguments);
        } // closure binding

        if (key_exists('closures', $GLOBALS) && key_exists($methodName, $GLOBALS['closures'])) {
            $function = $GLOBALS['closures'][$methodName];
            $this->addMethod($methodName, $function);
            return (null === ($result = call_user_func_array($this->methods[$methodName], $arguments)) ? $this : $result);
        }

        throw new \Exception("There is valid method or closure with the given name '$methodName' to call");
    }

    private function addMethod($name, $closure): void
    {
        if (is_callable($closure)):
            $this->methods[$name] = \Closure::bind($closure, $this, get_called_class());
        else: // Nested to ensure Singleton returns the correct value of self
            throw new \Exception("New Method Must Be A Valid Closure");
        endif;
    }

    public function __wakeup()
    {
        $object = get_object_vars( $this );
        foreach ($object as $item => $value) Serialized::is_serialized($value, $this->$item);
        if (method_exists( $this, '__construct' )) self::__construct();
    }

    // for auto class serialization add: const Singleton = true; to calling class
    public function __sleep()
    {
        if (!defined( 'self::Singleton' ) || !self::Singleton) return null;
        foreach (get_object_vars( $this ) as $key => &$value) {
            if (empty($value)) continue;    // The object could be null from serialization?
            if (is_object( $value )) {
                try { $this->$key = (@serialize( $value ));
                } catch (\Exception $e){ continue; }                // Database object we need to catch the error thrown.
            } $onlyKeys[] = $key;
        } return (isset($onlyKeys) ? $onlyKeys : []);
    }

    public function __destruct()
    {   // We require a sleep function to be set manually for singleton to manage utilization
        if (!defined( 'self::Singleton' ) || !self::Singleton) return null;
        try { $_SESSION[__CLASS__] =  @serialize( $this );     // TODO - base64_encode(
        } catch (\Exception $e){ unset($_SESSION[__CLASS__]); return null; };
    }

    public function &__get($variable)   // TODO - MAJOR rework, store in $storage
    {
        return $GLOBALS[$variable];
    }

    public function __set($variable, $value)
    {
        if (is_callable($value)) $this->{$variable} = $value->bindTo($this, $this);
        else $GLOBALS[$variable] = $value;
    }

    public function __isset($variable): bool
    {
        return array_key_exists($variable, $GLOBALS);
    }

    public function __unset($variable)
    {
        unset($GLOBALS[$variable]);
    }

    public function __invoke()
    {
        return $this->storage;
    }

    public function set(...$argv): self
    {
        $this->storage = $argv;
        return $this;
    }

    public function get($variable = null)
    {
        return ($variable == null ?
            $this->storage :
            $this->{$variable});
    }

    public function has($variable): bool
    {
        return isset($this->$variable);
    }
}
