<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/31/17
 * Time: 6:57 PM
 */

namespace CarbonPHP\Interfaces;

use CarbonPHP\Error\PublicAlert;

/**
 * Interface iRestNoPrimaryKeys
 * @package CarbonPHP\Interfaces
 */
interface iRestNoPrimaryKey
{
    /**
     * @param array $remove
     * @param array $argv
     * @return bool
     */
    public static function delete(array &$remove, array $argv): bool;      // Delete all data from a tables given its primary key

    /**
     * @param array $return
     * @param array $argv - column names desired to be in our array
     * @return bool
     */
    public static function get(array &$return, array $argv = []): bool;   // Get tables columns given in argv (usually an array) and place them into our array

    /**
     * @param array $post
     * @param string|null \$dependantEntityId
     * @return bool|string
     * @throws PublicAlert
     */
    public static function post(array &$post = []) : bool;              // Add and associative array Column => value

    /**
     * @param array $returnUpdated
     * @param array $argv - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    public static function put(array &$returnUpdated, array $argv = []): bool;
}