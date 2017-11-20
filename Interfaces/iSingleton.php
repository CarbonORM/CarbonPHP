<?php

namespace Carbon\Interfaces;

interface iSingleton
{

    public static function __callStatic($methodName, $arguments = array());

    public static function newInstance(...$args);

    public static function getInstance(...$args);

    public static function clearInstance() : void;

    public function __call($methodName, $arguments = array());

    public function __wakeup();

    // for auto class serialization add: const Singleton = true; to calling class
    public function __sleep();

    public function __destruct();

    public function &__get($variable);

    public function __set($variable, $value);

    public function __isset($variable): bool;

    public function __unset($variable);

    public function __invoke();

    public function set(...$argv);

    public function get($variable = null);

    public function has($variable): bool;
}
