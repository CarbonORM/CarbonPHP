<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/31/17
 * Time: 6:57 PM
 */

namespace CarbonPHP\Interfaces;

/**
 * Interface iTable
 * @package Carbon\Interfaces
 *
 * This should be implemented on all tables in
 * Application/Tables/ folder. Table files should
 * be named exactly that of the database table. If
 * a table contains, or may contain, foreign keys
 * then its primary key must be generated with
 *      Carbon\Entities.beginTransaction() : string
 *
 */
interface iRest
{
    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv
     * @return bool
     */
    public static function Delete(array &$return, string $primary = null, array $argv): bool;      // Delete all data from a table given its primary key

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv - column names desired to be in our array
     * @return bool
     */
    public static function Get(array &$return, string $primary = null, array $argv): bool;   // Get table columns given in argv (usually an array) and place them into our array

    /**
     * @param array $data
     * @return mixed
     */
    public static function Post(array $data);              // Add and associative array Column => value

    /**
     * @param array $return
     * @param string $id
     * @param array $argv   - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    public static function Put(array &$return, string $id, array $argv): bool;
}