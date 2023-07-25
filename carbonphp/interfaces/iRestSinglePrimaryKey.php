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
 * Interface iRestSinglePrimaryKey
 * @package CarbonPHP\Interfaces
 */
interface iRestSinglePrimaryKey
{
    /**
     * @param array $remove
     * @param string|null $primary
     * @param array $argv
     * @return bool
     */
    public static function delete(array &$remove, string $primary = null, array $argv = []): bool;      // Delete all data from a tables given its primary key

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv - column names desired to be in our array
     * @return bool
     */
    public static function get(array &$return, string $primary = null, array $argv = []): bool;   // Get tables columns given in argv (usually an array) and place them into our array

    /**
     * @param array $post
     * @param string|null \$dependantEntityId - a C6 Hex entity key
     * @return bool|string
     * @throws PublicAlert
     */
    public static function post(array &$post = []);              // Add and associative array Column => value

    /**
     * @param array $returnUpdated
     * @param string|null $primary
     * @param array $argv - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    public static function put(array &$returnUpdated, string $primary = null, array $argv = []): bool;
}