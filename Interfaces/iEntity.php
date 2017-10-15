<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/31/17
 * Time: 6:57 PM
 */

namespace Carbon\Interfaces;


interface iEntity
{
    static function get(&$array, $id);

    static function add(&$object, $id, $argv);

    static function remove(&$object, $id);

    static function all(&$object, $id);

    static function range(&$object, $id, $argv);
}