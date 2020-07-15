<?php

namespace CarbonPHP\Tables;

use PDO;
use PDOStatement;
use function array_key_exists;
use function count;
use function is_array;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;


class Sessions extends Rest implements iRest
{
    
    public const TABLE_NAME = 'sessions';
    public const USER_ID = 'sessions.user_id'; 
    public const USER_IP = 'sessions.user_ip'; 
    public const SESSION_ID = 'sessions.session_id'; 
    public const SESSION_EXPIRES = 'sessions.session_expires'; 
    public const SESSION_DATA = 'sessions.session_data'; 
    public const USER_ONLINE_STATUS = 'sessions.user_online_status'; 

    public const PRIMARY = [
        'sessions.session_id',
    ];

    public const COLUMNS = [
        'sessions.user_id' => 'user_id','sessions.user_ip' => 'user_ip','sessions.session_id' => 'session_id','sessions.session_expires' => 'session_expires','sessions.session_data' => 'session_data','sessions.user_online_status' => 'user_online_status',
    ];

    public const PDO_VALIDATION = [
        'sessions.user_id' => ['binary', '2', '16'],'sessions.user_ip' => ['varchar', '2', '20'],'sessions.session_id' => ['varchar', '2', '255'],'sessions.session_expires' => ['datetime', '2', ''],'sessions.session_data' => ['text,', '2', ''],'sessions.user_online_status' => ['tinyint', '0', '1'],
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
                if (substr($value, 0, '8') === 'C6SUB957') {
                    $subQuery = substr($value, '8');
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
        $inject = ':injection' . count(self::$injection) . 'sessions';
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

        
        if ($primary !== null || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1 && count($return) === 1)) {
            $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

        return true;
    }

    /**
    * @param array $argv
    * @return bool|string
    */
    public static function Post(array $argv)
    {
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO sessions (user_id, user_ip, session_id, session_expires, session_data, user_online_status) VALUES ( UNHEX(:user_id), :user_ip, :session_id, :session_expires, :session_data, :user_online_status)';

        

        $stmt = self::database()->prepare($sql);

                
                    $user_id = $argv['sessions.user_id'];
                    $stmt->bindParam(':user_id',$user_id, 2, 16);
                        
                    $user_ip =  $argv['sessions.user_ip'] ?? null;
                    $stmt->bindParam(':user_ip',$user_ip, 2, 20);
                        
                    $session_id = $argv['sessions.session_id'];
                    $stmt->bindParam(':session_id',$session_id, 2, 255);
                        $stmt->bindValue(':session_expires',$argv['sessions.session_expires'], 2);
                        $stmt->bindValue(':session_data',$argv['sessions.session_data'], 2);
                        
                    $user_online_status =  $argv['sessions.user_online_status'] ?? '1';
                    $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
        



            return $stmt->execute();
    }
     
    public static function subSelect(string $primary = null, array $argv, PDO $pdo = null): string
    {
        return 'C6SUB957' . self::buildSelectQuery($primary, $argv, $pdo, true);
    }
    
    public static function validateSelectColumn($column) : bool {
        return (bool) preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|-|/| |sessions\.user_id|sessions\.user_ip|sessions\.session_id|sessions\.session_expires|sessions\.session_data|sessions\.user_online_status))+\)*)+ *(as [a-z]+)?#i', $column);
    }
    
    public static function buildSelectQuery(string $primary = null, array $argv, PDO $pdo = null, bool $noHEX = false) : string 
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
            if (!empty($argv['pagination']) && !is_array($argv['pagination'])) {
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
                    if (is_array($argv['pagination']['order'])) {
                        foreach ($argv['pagination']['order'] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv['pagination']['order'];
                    }
                } else {
                    $order .= 'session_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY session_id ASC LIMIT 100';
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
                                return false; // todo debug check, common when joins are not a list of values
                        }
                    }
                    return true;
                };
                switch ($by) {
                    case 'inner':
                        if (!$buildJoin(' INNER JOIN ')) {
                            return false; 
                        }
                        break;
                    case 'left':
                        if (!$buildJoin(' LEFT JOIN ')) {
                            return false; 
                        }
                        break;
                    case 'right':
                        if (!$buildJoin(' RIGHT JOIN ')) {
                            return false; 
                        }
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
                $group[] = $column;
                $aggregate = true;
            } else {  
                $valid = false;
                $tablesReffrenced = $tableList;
                while (!empty($tablesReffrenced)) {
                     $table = __NAMESPACE__ . '\\' . array_pop($tablesReffrenced);
                     
                     if (!class_exists($table)){
                         continue;
                     }
                     $imp = array_map('strtolower', array_keys(class_implements($table)));
                    
                   
                     /** @noinspection ClassConstantUsageCorrectnessInspection */
                     if (!in_array(strtolower(iRest::class), $imp, true) && 
                         !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                         continue;
                     }
                     /** @noinspection PhpUndefinedMethodInspection */
                     if ($table::validateSelectColumn($column)) { 
                        $group[] = $column;
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

        $sql = 'SELECT ' .  $sql . ' FROM sessions ' . $join;
       
        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
            $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
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
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                return false;
            }
        }

        $sql = 'UPDATE sessions ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (array_key_exists('sessions.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('sessions.user_ip', $argv)) {
            $set .= 'user_ip=:user_ip,';
        }
        if (array_key_exists('sessions.session_id', $argv)) {
            $set .= 'session_id=:session_id,';
        }
        if (array_key_exists('sessions.session_expires', $argv)) {
            $set .= 'session_expires=:session_expires,';
        }
        if (array_key_exists('sessions.session_data', $argv)) {
            $set .= 'session_data=:session_data,';
        }
        if (array_key_exists('sessions.user_online_status', $argv)) {
            $set .= 'user_online_status=:user_online_status,';
        }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        

        

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('sessions.user_id', $argv)) {
            $user_id = $argv['sessions.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('sessions.user_ip', $argv)) {
            $user_ip = $argv['sessions.user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 20);
        }
        if (array_key_exists('sessions.session_id', $argv)) {
            $session_id = $argv['sessions.session_id'];
            $stmt->bindParam(':session_id',$session_id, 2, 255);
        }
        if (array_key_exists('sessions.session_expires', $argv)) {
            $stmt->bindValue(':session_expires',$argv['sessions.session_expires'], 2);
        }
        if (array_key_exists('sessions.session_data', $argv)) {
            $stmt->bindValue(':session_data',$argv['sessions.session_data'], 2);
        }
        if (array_key_exists('sessions.user_online_status', $argv)) {
            $user_online_status = $argv['sessions.user_online_status'];
            $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            return false;
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('sessions.', '', $k); },
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
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE FROM sessions ';

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
        }         if (null === $primary) {
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
        } else {
        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        }
               


        

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
}
