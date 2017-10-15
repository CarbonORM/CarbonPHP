<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 3/1/17
 * Time: 11:29 PM
 */

namespace Carbon\Helpers;

use Carbon\Singleton;

class Skeleton implements \Traversable
{
    use Singleton;
    const Singleton = true; // turns on auto caching

    public function offsetSet($offset, $value) {
        if (is_null($offset)) $this->storage[] = $value;
        else $this->storage[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->storage[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->storage[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->storage[$offset]) ? $this->storage[$offset] : null;
    }

    public function &__get($variable)
    {
        return $this->storage[$variable];
    }

    public function __set($variable, $value)
    {
        $this->storage[$variable] = $value;
    }

    public function __isset($variable)
    {
        return array_key_exists( $variable, $this->storage );
    }

    public function __unset($variable)
    {
        unset($this->storage[$variable]);
    }
    public function __destruct()
    {
        return null;
    }
}
