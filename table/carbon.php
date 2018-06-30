<?php

namespace Table;

use CarbonPHP\Database;
use CarbonPHP\Entities;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;

class carbon extends Entities implements iRest
{
    const PRIMARY = "entity_pk";

    const COLUMNS = [
            'entity_pk',
            'entity_fk',
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

        $sql = 'SELECT ' .  $get . ' FROM carbonphp.carbon';

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
        $sql = 'INSERT INTO carbonphp.carbon (entity_pk, entity_fk) VALUES (:entity_pk, :entity_fk)';
        $stmt = Database::database()->prepare($sql);
            
                $entity_pk = $argv['entity_pk'];
                $stmt->bindParam(':entity_pk',$entity_pk, \PDO::PARAM_STR, 225);
            
                $entity_fk = isset($argv['entity_fk']) ? $argv['entity_fk'] : NULL;
                $stmt->bindParam(':entity_fk',$entity_fk, \PDO::PARAM_STR, 225);


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
            $set .= 'entity_pk=:entity_pk,';
        }if (isset($argv['entity_fk'])) {
            $set .= 'entity_fk=:entity_fk,';
        }

        if (empty($set)){
            return false;
        }

        $set = substr($set, 0, strlen($set)-1);

        $sql .= $set . ' WHERE ' . self::PRIMARY . "='$id'";

        $stmt = Database::database()->prepare($sql);

        if (isset($argv['entity_pk'])) {
            $stmt->bindValue(':entity_pk', $argv['entity_pk'], \PDO::PARAM_STR);
        }if (isset($argv['entity_fk'])) {
            $stmt->bindValue(':entity_fk', $argv['entity_fk'], \PDO::PARAM_STR);
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