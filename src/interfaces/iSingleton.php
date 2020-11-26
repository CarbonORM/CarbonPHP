<?php

namespace CarbonPHP\Interfaces;

interface iSingleton
{

    public static function __callStatic($methodName, array $arguments = array());

    public static function newInstance(...$args);

    public static function getInstance(...$args);

    public static function clearInstance() : void;

    public function __call($methodName, array $arguments = array());

    public function __wakeup();

    // for auto class serialization add: const Singleton = true; to calling class
    public function __sleep();

    public function __destruct();

    public function &__get(string $variable);

    public function __set(string $variable, $value);

    public function __isset(string $variable): bool;

    public function __unset(string $variable);

    public function __invoke();

    public function set(...$argv);

    public function get(string $variable = null);

    public function has(string $variable): bool;
}
