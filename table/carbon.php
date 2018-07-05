<?php

namespace CarbonPHP\Table;

use CarbonPHP\Database;
use CarbonPHP\Entities;
use CarbonPHP\Interfaces\iRest;

class carbon extends Entities implements iRest
{
    const PRIMARY = "entity_pk";

    const COLUMNS = [
    'entity_pk','entity_fk',
    ];

    const BINARY = [
    'entity_pk','entity_fk',
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

        $sql = 'SELECT ' .  $sql . ' FROM carbonphp.carbon';

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
        } else if (!empty(self::PRIMARY)){
            $sql .= ' WHERE ' . self::PRIMARY . '=UNHEX(' . $pdo->quote($primary) . ')';
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
        $sql = 'INSERT INTO carbonphp.carbon (entity_pk, entity_fk) VALUES ( UNHEX(:entity_pk), :entity_fk)';
        $stmt = Database::database()->prepare($sql);
            $entity_pk = $id = self::fetchColumn('SELECT (REPLACE(UUID(),"-",""))')[0];
            $stmt->bindParam(':entity_pk',$entity_pk, \PDO::PARAM_STR, 16);
            
                $entity_fk = isset($argv['entity_fk']) ? $argv['entity_fk'] : null;
                $stmt->bindParam(':entity_fk',$entity_fk, \PDO::PARAM_STR, 16);
        
        return $stmt->execute() ? $id : false;
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

        $sql = 'UPDATE carbonphp.carbon ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (isset($argv['entity_pk'])) {
            $set .= 'entity_pk=UNHEX(:entity_pk),';
        }
        if (isset($argv['entity_fk'])) {
            $set .= 'entity_fk=UNHEX(:entity_fk),';
        }

        if (empty($set)){
            return false;
        }

        $set = substr($set, 0, strlen($set)-1);

        $sql .= $set . ' WHERE ' . self::PRIMARY . "='$id'";

        $stmt = Database::database()->prepare($sql);

        if (isset($argv['entity_pk'])) {
            $entity_pk = 'UNHEX('.$argv['entity_pk'].')';
            $stmt->bindParam(':entity_pk', $entity_pk, \PDO::PARAM_STR, 16);
        }
        if (isset($argv['entity_fk'])) {
            $entity_fk = 'UNHEX('.$argv['entity_fk'].')';
            $stmt->bindParam(':entity_fk', $entity_fk, \PDO::PARAM_STR, 16);
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
        $sql = 'DELETE FROM carbonphp.carbon ';

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
        } else if (!empty(self::PRIMARY)) {
            $sql .= ' WHERE ' . self::PRIMARY . '=UNHEX(' . Database::database()->quote($primary) . ')';
        }

        $remove = null;

        return self::execute($sql);
    }
}