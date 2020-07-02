<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class History_Logs extends Database implements iRest
{

    public const UUID = 'history_logs.uuid';
    public const RESOURCE_TYPE = 'history_logs.resource_type';
    public const RESOURCE_UUID = 'history_logs.resource_uuid';
    public const OPERATION_TYPE = 'history_logs.operation_type';
    public const DATA = 'history_logs.data';

    public const PRIMARY = [
    
    ];

    public const COLUMNS = [
        'uuid' => [ 'binary', '2', '16' ],'resource_type' => [ 'varchar', '2', '40' ],'resource_uuid' => [ 'binary', '2', '16' ],'operation_type' => [ 'varchar', '2', '20' ],'data' => [ 'json', '2', '' ],
    ];

    public const VALIDATION = [];


    public static array $injection = [];



    public static function buildWhere(array $set, \PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        $bump = false;
        foreach ($set as $column => $value) {
            if (\is_array($value)) {
                if ($bump) {
                    $sql .= " $join ";
                }
                $bump = true;
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::COLUMNS)) {
                $bump = false;
                if (self::COLUMNS[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo)  . ")) $join ";
                } else {
                    $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
                }
            } else {
                $bump = false;
                $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
            }
        }
        return rtrim($sql, " $join") . ')';
    }

    public static function addInjection($value, \PDO $pdo, $quote = false) : string
    {
        $inject = ':injection' . \count(self::$injection) . 'buildWhere';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(\PDOStatement $stmt, array $argv) {
   
   /*
    $bind = function (array $argv) use (&$bind, &$stmt) {
            foreach ($argv as $key => $value) {
                
                if (is_numeric($key) && is_array($value)) {
                    $bind($value);
                    continue;
                }
                
                   if (array_key_exists('uuid', $argv)) {
            $uuid = $argv['uuid'];
            $stmt->bindParam(':uuid',$uuid, 2, 16);
        }
                   if (array_key_exists('resource_type', $argv)) {
            $resource_type = $argv['resource_type'];
            $stmt->bindParam(':resource_type',$resource_type, 2, 40);
        }
                   if (array_key_exists('resource_uuid', $argv)) {
            $resource_uuid = $argv['resource_uuid'];
            $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        }
                   if (array_key_exists('operation_type', $argv)) {
            $operation_type = $argv['operation_type'];
            $stmt->bindParam(':operation_type',$operation_type, 2, 20);
        }
                   if (array_key_exists('data', $argv)) {
            $stmt->bindValue(':data',json_encode($argv['data']), 2);
        }
           
          }
        };
        
        $bind($argv); */

        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key,$value);
        }

        return $stmt->execute();
    }


    /**
    *
    *   $argv = [
    *       'select' => [
    *                          '*column name array*', 'etc..'
    *        ],
    *
    *       'where' => [
    *              'Column Name' => 'Value To Constrain',
    *              'Defaults to AND' => 'Nesting array switches to OR',
    *              [
    *                  'Column Name' => 'Value To Constrain',
    *                  'This array is OR'ed togeather' => 'Another sud array would `AND`'
    *                  [ etc... ]
    *              ]
    *        ],
    *
    *        'pagination' => [
    *              'limit' => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *              'order' => '*column name* [ASC|DESC]',  // i.e.  'username ASC' or 'username, email DESC'
    *
    *
    *         ],
    *
    *   ];
    *
    *
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        $pdo = self::database();

        $sql = self::buildSelect($primary, $argv, $pdo);
        
        $stmt = $pdo->prepare($sql);

        if (!self::bind($stmt, $argv['where'] ?? [])) {
            return false;
        }

        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO history_logs (uuid, resource_type, resource_uuid, operation_type, data) VALUES ( UNHEX(:uuid), :resource_type, UNHEX(:resource_uuid), :operation_type, :data)';

        

        $stmt = self::database()->prepare($sql);

                
                    $uuid = $argv['uuid'];
                    $stmt->bindParam(':uuid',$uuid, 2, 16);
                        
                    $resource_type =  $argv['resource_type'] ?? null;
                    $stmt->bindParam(':resource_type',$resource_type, 2, 40);
                        
                    $resource_uuid =  $argv['resource_uuid'] ?? null;
                    $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
                        
                    $operation_type =  $argv['operation_type'] ?? null;
                    $stmt->bindParam(':operation_type',$operation_type, 2, 20);
                        $stmt->bindValue(':data',json_encode($argv['data']), 2);
        



            return $stmt->execute();
    }
    
    public static function buildSelect(string $primary = null, array $argv, \PDO $pdo) : string {
        self::$injection = [];
        $aggregate = false;
        $group = $sql = '';
        $get = $argv['select'] ?? array_keys(self::COLUMNS);
        $where = $argv['where'] ?? [];

        if (array_key_exists('pagination',$argv)) {
            if (!empty($argv['pagination']) && !\is_array($argv['pagination'])) {
                $argv['pagination'] = json_decode($argv['pagination'], true);
            }
            if (array_key_exists('limit',$argv['pagination']) && $argv['pagination']['limit'] !== null) {
                $limit = ' LIMIT ' . $argv['pagination']['limit'];
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (array_key_exists('order',$argv['pagination']) && $argv['pagination']['order'] !== null) {
                    if (\is_array($argv['pagination']['order'])) {
                        foreach ($argv['pagination']['order'] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv['pagination']['order'];
                    }
                } else {
                    $order .= ' ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY  ASC LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                if (!empty($group)) {
                    $group .= ', ';
                }
            }
            $columnExists = array_key_exists($column, self::COLUMNS);
            if ($columnExists && self::COLUMNS[$column][0] === 'binary') {
                $sql .= "HEX($column) as $column";
                $group .= $column;
            } elseif ($columnExists) {
                $sql .= $column;
                $group .= $column;
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |uuid|resource_type|resource_uuid|operation_type|data))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM history_logs';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } 

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        

        return '(' . $sql . ')';
    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        self::$injection = [];
        if (empty($primary)) {
            return false;
        }

        foreach ($argv as $key => $value) {
            if (!\array_key_exists($key, self::COLUMNS)){
                return false;
            }
        }

        $sql = 'UPDATE history_logs ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('uuid', $argv)) {
                $set .= 'uuid=UNHEX(:uuid),';
            }
            if (array_key_exists('resource_type', $argv)) {
                $set .= 'resource_type=:resource_type,';
            }
            if (array_key_exists('resource_uuid', $argv)) {
                $set .= 'resource_uuid=UNHEX(:resource_uuid),';
            }
            if (array_key_exists('operation_type', $argv)) {
                $set .= 'operation_type=:operation_type,';
            }
            if (array_key_exists('data', $argv)) {
                $set .= 'data=:data,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        

        

        $stmt = $pdo->prepare($sql);

                   if (array_key_exists('uuid', $argv)) {
            $uuid = $argv['uuid'];
            $stmt->bindParam(':uuid',$uuid, 2, 16);
        }
                   if (array_key_exists('resource_type', $argv)) {
            $resource_type = $argv['resource_type'];
            $stmt->bindParam(':resource_type',$resource_type, 2, 40);
        }
                   if (array_key_exists('resource_uuid', $argv)) {
            $resource_uuid = $argv['resource_uuid'];
            $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        }
                   if (array_key_exists('operation_type', $argv)) {
            $operation_type = $argv['operation_type'];
            $stmt->bindParam(':operation_type',$operation_type, 2, 20);
        }
                   if (array_key_exists('data', $argv)) {
            $stmt->bindValue(':data',json_encode($argv['data']), 2);
        }

        if (!self::bind($stmt, $argv)){
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
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'DELETE FROM history_logs ';

        $pdo = self::database();

        if (null === $primary) {
        /**
        *   While useful, we've decided to disallow full
        *   table deletions through the rest api. For the
        *   n00bs and future self, "I got chu."
        */
        if (empty($argv)) {
            return false;
        }


        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo);
        } 

        

        $stmt = $pdo->prepare($sql);

        $r = self::bind($stmt, $argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = null;

        return $r;
    }
}
