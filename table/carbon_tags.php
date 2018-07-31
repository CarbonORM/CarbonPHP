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

    const VALIDATION = [];

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
        $get = isset($argv['select']) ? $argv['select'] : self::COLUMNS;
        $where = isset($argv['where']) ? $argv['where'] : [];

        $group = $sql = '';

        if (isset($argv['pagination'])) {
            if (!empty($argv['pagination']) && !is_array($argv['pagination'])) {
                $argv['pagination'] = json_decode($argv['pagination'], true);
            }
            if (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] != null) {
                $pos = strrpos($argv['pagination']['limit'], "><");
                if ($pos !== false) { // note: three equal signs
                    substr_replace($argv['pagination']['limit'],',',$pos, 2);
                }
                $limit = ' LIMIT ' . $argv['pagination']['limit'];
            } else {
                $limit = '';
            }
        } else {
            $limit = ' LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                $group .= ', ';
            }
            if (in_array($column, self::BINARY)) {
                $sql .= "HEX($column) as $column";
                $group .= "$column";
            } else {
                $sql .= $column;
                $group .= $column;
            }
        }

        if (isset($argv['aggregate']) && (is_array($argv['aggregate']) || $argv['aggregate'] = json_decode($argv['aggregate'], true))) {
            foreach($argv['aggregate'] as $key => $value){
                switch ($key){
                    case 'count':
                        if (!empty($sql)) {
                            $sql .= ', ';
                        }
                        $sql .= "COUNT($value) AS count ";
                        break;
                    case 'AVG':
                        if (!empty($sql)) {
                            $sql .= ', ';
                        }
                        $sql .= "AVG($value) AS avg ";
                        break;
                    case 'MIN':
                        if (!empty($sql)) {
                            $sql .= ', ';
                        }
                        $sql .= "MIN($value) AS min ";
                        break;
                    case 'MAX':
                        if (!empty($sql)) {
                            $sql .= ', ';
                        }
                        $sql .= "MAX($value) AS max ";
                        break;
                }
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM CarbonPHP.carbon_tags';

        $pdo = Database::database();

        if (empty($primary)) {
            if (!empty($where)) {
                $build_where = function (array $set, $join = 'AND') use (&$pdo, &$build_where) {
                    $sql = '(';
                    foreach ($set as $column => $value) {
                        if (is_array($value)) {
                            $sql .= $build_where($value, $join === 'AND' ? 'OR' : 'AND');
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

        if (isset($argv['aggregate'])) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        $return = self::fetch($sql);

        global $json;

        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = $sql;

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        
        if (empty($primary) && count($return) && in_array(array_keys($return)[0], self::COLUMNS, true)) {  // You must set tr
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
        $sql = 'INSERT INTO CarbonPHP.carbon_tags (tag_description, tag_name) VALUES ( :tag_description, :tag_name)';
        $stmt = sDatabaseelf::database()->prepare($sql);

        global $json;

        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = $sql;

                    $stmt->bindValue(':tag_description',$argv['tag_description'], \2);
                    $stmt->bindValue(':tag_name',$argv['tag_name'], \2);
        

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

        $sql = 'UPDATE CarbonPHP.carbon_tags ';

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

        global $json;

        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = $sql;


        if (isset($argv['tag_id'])) {
            $tag_id = $argv['tag_id'];
            $stmt->bindParam(':tag_id',$tag_id, 2, 11);
        }
        if (isset($argv['tag_description'])) {
            $stmt->bindValue(':tag_description',$argv['tag_description'], 2);
        }
        if (isset($argv['tag_name'])) {
            $stmt->bindValue(':tag_name',$argv['tag_name'], 2);
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
        $sql = 'DELETE FROM CarbonPHP.carbon_tags ';

        foreach($argv as $column => $constraint){
            if (!in_array($column, self::COLUMNS)){
                unset($argv[$column]);
            }
        }

        if (empty($primary)) {
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

        global $json;

        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = $sql;

        return self::execute($sql);
    }
}