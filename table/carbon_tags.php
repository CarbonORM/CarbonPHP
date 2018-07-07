<?php

namespace CarbonPHP\Table;

use CarbonPHP\Database;
use CarbonPHP\Entities;
use CarbonPHP\Interfaces\iRest;

class carbon_tags extends Entities implements iRest
{
    const PRIMARY = [
    'tag_id',
    ];

    const COLUMNS = [
    'tag_id','tag_description','tag_name',
    ];

    const BINARY = [
    
    ];

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv
     * @return bool
     */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        if (isset($argv['limit'])){
            if ($argv['limit'] !== '') {
                $pos = strrpos($argv['limit'], "><");
                if ($pos !== false) { // note: three equal signs
                    substr_replace($argv['limit'],',',$pos, 2);
                }
                $limit = ' LIMIT ' . $argv['limit'];
            } else {
                $limit = '';
            }
        } else {
            $limit = ' LIMIT 100';
        }

        $get = isset($argv['select']) ? $argv['select'] : self::COLUMNS;
        $where = isset($argv['where']) ? $argv['where'] : [];

        $sql = '';
        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
            }
            if (in_array($column, self::BINARY)) {
                $sql .= "HEX($column) as $column";
            } else {
                $sql .= $column;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbonphp.carbon_tags';

        $pdo = Database::database();

        if ($primary === null) {
            if (!empty($where)) {
                $build_where = function (array $set, $join = 'AND') use (&$pdo, &$build_where) {
                    $sql = '(';
                    foreach ($set as $column => $value) {
                        if (is_array($value)) {
                            $build_where($value, $join === 'AND' ? 'OR' : 'AND');
                        } else {
                            if (in_array($column, self::BINARY)) {
                                $sql .= "($column = UNHEX(" . $pdo->quote($value) . ")) $join ";
                            } else {
                                $sql .= "($column = " . $pdo->quote($value) . ") $join ";
                            }
                        }
                    }
                    return substr($sql, 0, strlen($sql) - (strlen($join) + 1)) . ')';
                };
                $sql .= ' WHERE ' . $build_where($where);
            }
        } else {
            $primary = $pdo->quote($primary);
            $sql .= ' WHERE  tag_id=' . $primary .'';
        }

        $sql .= $limit;

        $return = self::fetch($sql);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        if ($primary === null && count($return) && in_array(array_keys($return)[0], self::COLUMNS, true)) {  // You must set tr
            $return = [$return];
        }        if ($primary === null && count($return) && in_array(array_keys($return)[0], self::COLUMNS, true)) {  // You must set tr
            $return = [$return];
        }

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        $sql = 'INSERT INTO carbonphp.carbon_tags (tag_description, tag_name) VALUES ( :tag_description, :tag_name)';
        $stmt = Database::database()->prepare($sql);
                    $stmt->bindValue(':tag_description',$argv['tag_description'], \PDO::PARAM_STR);
                    $stmt->bindValue(':tag_name',$argv['tag_name'], \PDO::PARAM_STR);
        

        return $stmt->execute();
    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        foreach ($argv as $key => $value) {
            if (!in_array($key, self::COLUMNS)){
                unset($argv[$key]);
            }
        }

        $sql = 'UPDATE carbonphp.carbon_tags ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (isset($argv['tag_id'])) {
            $set .= 'tag_id=:tag_id,';
        }
        if (isset($argv['tag_description'])) {
            $set .= 'tag_description=:tag_description,';
        }
        if (isset($argv['tag_name'])) {
            $set .= 'tag_name=:tag_name,';
        }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, strlen($set)-1);

        $db = Database::database();

        
        $primary = $db->quote($primary);
        $sql .= ' WHERE  tag_id=' . $primary .'';

        $stmt = $db->prepare($sql);

        if (isset($argv['tag_id'])) {
            $tag_id = $argv['tag_id'];
            $stmt->bindParam(':tag_id',$tag_id, \PDO::PARAM_STR, 11);
        }
        if (isset($argv['tag_description'])) {
            $stmt->bindValue(':tag_description',$argv['tag_description'], \PDO::PARAM_STR);
        }
        if (isset($argv['tag_name'])) {
            $stmt->bindValue(':tag_name',$argv['tag_name'], \PDO::PARAM_STR);
        }

        if (!$stmt->execute()){
            return false;
        }

        $return = array_merge($return, $argv);

        return true;

    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv) : bool
    {
        $sql = 'DELETE FROM carbonphp.carbon_tags ';

        foreach($argv as $column => $constraint){
            if (!in_array($column, self::COLUMNS)){
                unset($argv[$column]);
            }
        }

        if ($primary === null) {
            /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                return false;
            }
            $sql .= ' WHERE ';
            foreach ($argv as $column => $value) {
                if (in_array($column, self::BINARY)) {
                    $sql .= " $column =UNHEX(" . Database::database()->quote($value) . ') AND ';
                } else {
                    $sql .= " $column =" . Database::database()->quote($value) . ' AND ';
                }
            }
            $sql = substr($sql, 0, strlen($sql)-4);
        } else {
            $primary = Database::database()->quote($primary);
            $sql .= ' WHERE  tag_id=' . $primary .'';
        }

        $remove = null;

        return self::execute($sql);
    }
}