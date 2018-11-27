<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 5/22/2018
 * Time: 2:39 PM
 */

namespace CarbonPHP\Helpers;



use Symfony\Component\Finder\Exception\AccessDeniedException;

trait MeagerRest
{

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv
     * @return bool
     */
    public static function Delete(array &$return, string $primary = null, array $argv): bool {
        throw new AccessDeniedException('You do not have access to this method!');
    }     // Delete all data from a table given its primary key

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv - column names desired to be in our array
     * @return bool
     */
    public static function Get(array &$return, string $primary = null, array $argv): bool {
        throw new AccessDeniedException('You do not have access to this method!');
    }   // Get table columns given in argv (usually an array) and place them into our array

    /**
     * @param array $data
     * @return mixed
     */
    public static function Post(array $data) {
        throw new AccessDeniedException('You do not have access to this method!');
    }              // Add and associative array Column => value

    /**
     * @param array $return
     * @param string $id
     * @param array $argv   - an associative array of Column => Value pairs
     * @return bool  - true on success false on failure
     */
    public static function Put(array &$return, string $id, array $argv): bool {
        throw new AccessDeniedException('You do not have access to this method!');
    }
}