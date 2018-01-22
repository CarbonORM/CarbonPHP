<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/31/17
 * Time: 6:57 PM
 */

namespace Carbon\Interfaces;

/**
 * Interface iTable
 * @package Carbon\Interfaces
 *
 * This should be implemented on all tables in
 * Application/Tables/ folder. Table files should
 * be named exactly that of the table. If a table
 * contains, or may contain, foreign keys then its
 * primary key must be generated with
 *      Carbon\Entities.beginTransaction()
 *
 *
 */
interface iTable
{
    /**
     * @param $array - values received will be placed in this array
     * @param $id    - the rows primary key
     * @return void
     */
    static function All(array &$array, string $id);         // Get all data from a table given its primary key

    /**
     * @param $array - should be set to null on success
     * @param $id    - the rows primary key
     * @return bool
     */
    static function Delete(array &$array, string $id);      // Delete all data from a table given its primary key

    /**
     * @param $array - values received will be placed in this array
     * @param $id    - the rows primary key
     * @param $argv  - column names desired to be in our array
     * @return bool
     */
    static function Get(array &$array, string $id, array $argv);   // Get table columns given in argv (usually an array) and place them into our array

    /**
     * @param $array - The array we are trying to insert
     * @return bool
     */
    static function Post(array &$array);              //


    /**
     * @param $array - on success, fields updated will be
     * @param $id    - the rows primary key
     * @param $argv  - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    static function Put(array &$array, string $id, array $argv ) : bool;
}