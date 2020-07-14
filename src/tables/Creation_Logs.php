<?php

namespace CarbonPHP\Tables;

use PDO;
use PDOStatement;
use function array_key_exists;
use function count;
use function is_array;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;


class Creation_Logs extends Rest implements iRest
{
    
    public const TABLE_NAME = 'creation_logs';
    public const UUID = 'creation_logs.uuid'; 
    public const RESOURCE_TYPE = 'creation_logs.resource_type'; 
    public const RESOURCE_UUID = 'creation_logs.resource_uuid'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'creation_logs.uuid' => 'uuid','creation_logs.resource_type' => 'resource_type','creation_logs.resource_uuid' => 'resource_uuid',
    ];

    public const PDO_VALIDATION = [
        'creation_logs.uuid' => ['binary', '2', '16'],'creation_logs.resource_type' => ['varchar', '2', '40'],'creation_logs.resource_uuid' => ['binary', '2', '16'],
    ];
    public const VALIDATION = [];

    public static array $injection = [];

    
    
    public static function buildWhere(array $set, PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        $bump = false;
        foreach ($set as $column => $value) {
            if (is_array($value)) {
                if ($bump) {
                    $sql .= " $join ";
                }
                $bump = true;
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::PDO_VALIDATION)) {
                $bump = false;
                /** @noinspection SubStrUsedAsStrPosInspection */
                if (substr($value, 0, '7') === 'C6SUB91') {
                    $subQuery = substr($value, '7');
                    $sql .= "($column = $subQuery ) $join ";
                } else if (self::PDO_VALIDATION[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo) . ")) $join ";
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

    public static function addInjection($value, PDO $pdo, $quote = false): string
    {
        $inject = ':injection' . \count(self::$injection) . 'creation_logs';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(PDOStatement $stmt): void 
    {
        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key,$value);
        }
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
    public static function Get(array &$return, string $primary = null, array $argv): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, $pdo);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            return false;
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
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
        $sql = 'INSERT INTO creation_logs (uuid, resource_type, resource_uuid) VALUES ( UNHEX(:uuid), :resource_type, UNHEX(:resource_uuid))';

        

        $stmt = self::database()->prepare($sql);

                
                    $uuid =  $argv['creation_logs.uuid'] ?? null;
                    $stmt->bindParam(':uuid',$uuid, 2, 16);
                        
                    $resource_type =  $argv['creation_logs.resource_type'] ?? null;
                    $stmt->bindParam(':resource_type',$resource_type, 2, 40);
                        
                    $resource_uuid =  $argv['creation_logs.resource_uuid'] ?? null;
                    $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        



            return $stmt->execute();
    }
     
    public static function subSelect(string $primary = null, array $argv, \PDO $pdo = null): string
    {
        return 'C6SUB91' . self::buildSelectQuery($primary, $argv, $pdo, true);
    }
    
    public static function validateSelectColumn($column) : bool {
        return (bool) preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |creation_logs\.uuid|creation_logs\.resource_type|creation_logs\.resource_uuid))+\)*)+ *(as [a-z]+)?#i', $column);
    }
    
    public static function buildSelectQuery(string $primary = null, array $argv, \PDO $pdo = null, bool $noHEX = false) : string 
    {
        if ($pdo === null) {
            $pdo = self::database();
        }
        self::$injection = [];
        $aggregate = false;
        $group = [];
        $sql = '';
        $get = $argv['select'] ?? array_keys(self::PDO_VALIDATION);
        $where = $argv['where'] ?? [];

        // pagination
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

        // join 
        $join = ''; 
        $tableList = [];
        if (array_key_exists('join', $argv)) {
            foreach ($argv['join'] as $by => $tables) {
                $buildJoin = static function ($method) use ($tables, &$join, &$tableList) {
                    foreach ($tables as $table => $stmt) {
                        $tableList[] = $table;
                        switch (count($stmt)) {
                            case 2: 
                                if (is_string($stmt[0]) && is_string($stmt[1])) {
                                    $join .= $method . $table . ' ON ' . $stmt[0] . '=' . $stmt[1];
                                } else {
                                    return false; // todo debugging
                                }
                                break;
                            case 3:
                                if (is_string($stmt[0]) && is_string($stmt[1]) && is_string($stmt[2])) {
                                    $join .= $method . $table . ' ON ' . $stmt[0] . $stmt[1] . $stmt[2]; 
                                } else {
                                    return false; // todo debugging
                                }
                                break;
                            default:
                                return false; // todo debug check
                        }
                    }
                };
                switch ($by) {
                    case 'inner':
                        $buildJoin(' INNER JOIN ');
                        break;
                    case 'left':
                        $buildJoin(' LEFT JOIN ');
                        break;
                    case 'right':
                        $buildJoin(' RIGHT JOIN ');
                        break;
                    default:
                        return false; // todo - debugging stmts
                }
            }
        }

        // Select
        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
            }
            $columnExists = array_key_exists($column, self::PDO_VALIDATION);
            if ($columnExists) {
                if (!$noHEX && self::PDO_VALIDATION[$column][0] === 'binary') {
                    $asShort = trim($column, self::TABLE_NAME . '.');
                    $prefix = self::TABLE_NAME . '.';
                    if (strpos($column, $prefix) === 0) {
                        $asShort = substr($column, strlen($prefix));
                    }
                    $sql .= "HEX($column) as $asShort";
                    $group[] = $column;
                } elseif ($columnExists) {
                    $sql .= $column;
                    $group[] = $column;  
                }  
            } else if (self::validateSelectColumn($column)) {
                $sql .= $column;
                $aggregate = true;
            } else {  
                $valid = false;
                $tablesReffrenced = $tableList;
                while (!empty($tablesReffrenced)) {
                     $table = __NAMESPACE__ . '\\' . array_pop($tablesReffrenced);
                     
                     if (!class_exists($table)){
                         continue;
                     }
                     $imp = class_implements($table);
                    
                     /** @noinspection ClassConstantUsageCorrectnessInspection */
                     if (!in_array(strtolower(iRest::class), array_map('strtolower', array_keys($imp)))) {
                         continue;
                     }
                     if ($table::validateSelectColumn($column)) { 
                        $valid = true;
                         break; 
                     }
                }
                if (!$valid) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM creation_logs ' . $join;
       
        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } 

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . implode(', ', $group). ' ';
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
            if (!\array_key_exists($key, self::PDO_VALIDATION)){
                return false;
            }
        }

        $sql = 'UPDATE creation_logs ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('creation_logs.uuid', $argv)) {
                $set .= 'uuid=UNHEX(:uuid),';
            }
            if (array_key_exists('creation_logs.resource_type', $argv)) {
                $set .= 'resource_type=:resource_type,';
            }
            if (array_key_exists('creation_logs.resource_uuid', $argv)) {
                $set .= 'resource_uuid=UNHEX(:resource_uuid),';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        

        

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('creation_logs.uuid', $argv)) {
            $uuid = $argv['creation_logs.uuid'];
            $stmt->bindParam(':uuid',$uuid, 2, 16);
        }
        if (array_key_exists('creation_logs.resource_type', $argv)) {
            $resource_type = $argv['creation_logs.resource_type'];
            $stmt->bindParam(':resource_type',$resource_type, 2, 40);
        }
        if (array_key_exists('creation_logs.resource_uuid', $argv)) {
            $resource_uuid = $argv['creation_logs.resource_uuid'];
            $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            return false;
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('creation_logs.', '', $k); },
                array_keys($argv)
            ),
            array_values($argv)
        );

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
        $sql = 'DELETE FROM creation_logs ';

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
            
            $where = self::buildWhere($argv, $pdo);
            
            if (empty($where)) {
                return false;
            }

            $sql .= ' WHERE ' . $where;
        } 

        

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
}
