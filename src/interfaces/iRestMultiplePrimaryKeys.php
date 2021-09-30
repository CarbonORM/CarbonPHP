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
 * Interface iRestMultiplePrimaryKeys
 * @package CarbonPHP\Interfaces
 */
interface iRestMultiplePrimaryKeys
{
    /**
     * @param array $remove
     * @param array|null $primary
     * @param array $argv
     * @return bool
     */
    public static function delete(array &$remove, array $primary = null, array $argv = []): bool;      // Delete all data from a tables given its primary key

    /**
     * @param array $return
     * @param array|null $primary
     * @param array $argv - column names desired to be in our array
     * @return bool
     */
    public static function get(array &$return, array $primary = null, array $argv = []): bool;   // Get tables columns given in argv (usually an array) and place them into our array

    /**
     * @param array $post
     * @param string|null \$dependantEntityId - a C6 Hex entity key
     * @return bool|string
     * @throws PublicAlert
     */
    public static function post(array &$post = []);              // Add and associative array Column => value

    /**
     * @param array $returnUpdated
     * @param array|null $primary
     * @param array $argv   - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    public static function put(array &$returnUpdated, array $primary = null, array $argv = []): bool;
}