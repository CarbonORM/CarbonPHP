<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/17/17
 * Time: 12:18 PM
 */

namespace Carbon\Interfaces;

interface iSingleton
{
    public static function __callStatic($methodName, $arguments = array());

    public static function newInstance(...$args) : self;

    public static function getInstance(...$args) : self;

    public static function clearInstance() : void;

    public static function setInstance(self $object) : self;

    public function __call($methodName, $arguments = array());

    public function __wakeup();

    public function __sleep();

    public function __destruct();

    public function &__get($variable);

    public function __set($variable, $value);

    public function __isset($variable);

    public function __unset($variable);

    public function __invoke();

    public function set(...$argv);

    public function get($variable = null);

    public function has($variable);

}