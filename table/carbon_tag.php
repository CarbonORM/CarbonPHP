<?php

namespace Table;

use CarbonPHP\Database;
use CarbonPHP\Entities;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;

class carbon_tag extends Entities implements iRest
{
    const PRIMARY = "";

    const COLUMNS = [
            'entity_id',
            'user_id',
            'tag_id',
            'creation_date',
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

        $get = $where = [];
        foreach ($argv as $column => $value) {
            if (is_array($value) || !is_int($column) && in_array($column, self::COLUMNS)) {
                if ($value !== '') {
                    $where[$column] = $value;
                } else {
                    $get[] = $column;
                }
            } elseif (in_array($value, self::COLUMNS)) {
                $get[] = $value;
            }
        }

        $get =  !empty($get) ? implode(", ", $get) : ' * ';

        $sql = 'SELECT ' .  $get . ' FROM carbonphp.carbon_tag';

        $pdo = Database::database();

        if ($primary === null) {
            if (!empty($where)) {
                $build_where = function (array $set, $join = 'AND') use (&$pdo, &$build_where) {
                    $sql = '(';
                    foreach ($set as $column => $value) {
                        if (is_array($value)) {
                            $build_where($value, $join === 'AND' ? 'OR' : 'AND');
                        } else {
                            $sql .= "($column = " . $pdo->quote($value) . ") $join ";
                        }
                    }
                    return substr($sql, 0, strlen($sql) - (strlen($join) + 1)) . ')';
                };
                $sql .= ' WHERE ' . $build_where($where);
            }
        } else if (!empty(self::PRIMARY)){
            $sql .= ' WHERE ' . self::PRIMARY . '=' . $pdo->quote($primary);
        }

        $sql .= $limit;

        $return = self::fetch($sql);

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        $sql = 'INSERT INTO carbonphp.carbon_tag (entity_id, user_id, tag_id, creation_date) VALUES (:entity_id, :user_id, :tag_id, :creation_date)';
        $stmt = Database::database()->prepare($sql);
            
                $entity_id = isset($argv['entity_id']) ? $argv['entity_id'] : 'NULL';
                $stmt->bindParam(':entity_id',$entity_id, \PDO::PARAM_STR, 225);
            
                $user_id = isset($argv['user_id']) ? $argv['user_id'] : 'NULL';
                $stmt->bindParam(':user_id',$user_id, \PDO::PARAM_STR, 225);
            
                $tag_id = isset($argv['tag_id']) ? $argv['tag_id'] : 'NULL';
                $stmt->bindParam(':tag_id',$tag_id, \PDO::PARAM_STR, 11);
            
                $creation_date = isset($argv['creation_date']) ? $argv['creation_date'] : 'NULL';
                $stmt->bindParam(':creation_date',$creation_date, \PDO::PARAM_STR, 20);
        return $stmt->execute();
    }

    /**
    * @param array $return
    * @param string $id
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $id, array $argv) : bool
    {
        foreach ($argv as $key => $value) {
            if (!in_array($key, self::COLUMNS)){
                unset($argv[$key]);
            }
        }

        $sql = 'UPDATE carbonphp.carbon_tag ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (isset($argv['entity_id'])) {
            $set .= 'entity_id=:entity_id,';
        }if (isset($argv['user_id'])) {
            $set .= 'user_id=:user_id,';
        }if (isset($argv['tag_id'])) {
            $set .= 'tag_id=:tag_id,';
        }if (isset($argv['creation_date'])) {
            $set .= 'creation_date=:creation_date,';
        }

        if (empty($set)){
            return false;
        }

        $set = substr($set, 0, strlen($set)-1);

        $sql .= $set . ' WHERE ' . self::PRIMARY . "='$id'";

        $stmt = Database::database()->prepare($sql);

        if (isset($argv['entity_id'])) {
            $stmt->bindValue(':entity_id', $argv['entity_id'], \PDO::PARAM_STR);
        }if (isset($argv['user_id'])) {
            $stmt->bindValue(':user_id', $argv['user_id'], \PDO::PARAM_STR);
        }if (isset($argv['tag_id'])) {
            $stmt->bindValue(':tag_id', $argv['tag_id'], \PDO::PARAM_STR);
        }if (isset($argv['creation_date'])) {
            $stmt->bindValue(':creation_date', $argv['creation_date'], \PDO::PARAM_STR);
        }

        if (!$stmt->execute()){
            return false;
        }

        $return = array_merge($return, $argv);

        return true;

    }

    /**
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv) : bool
    {
        $sql = 'DELETE FROM carbonphp.carbon_tag ';

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
                $sql .= " $column =" . Database::database()->quote($value) . ' AND ';
            }
            $sql = substr($sql, 0, strlen($sql)-4);
        } else if (!empty(self::PRIMARY)) {
            $sql .= ' WHERE ' . self::PRIMARY . '=' . Database::database()->quote($primary);
        }

        $remove = null;

        return self::execute($sql);
    }

}