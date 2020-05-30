<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class Carbon_User_Sessions extends Database implements iRest
{

    public const USER_ID = 'user_id';
    public const USER_IP = 'user_ip';
    public const SESSION_ID = 'session_id';
    public const SESSION_EXPIRES = 'session_expires';
    public const SESSION_DATA = 'session_data';
    public const USER_ONLINE_STATUS = 'user_online_status';

    public const PRIMARY = [
    'session_id',
    ];

    public const COLUMNS = [
        'user_id' => [ 'binary', '2', '16' ],'user_ip' => [ 'binary', '2', '16' ],'session_id' => [ 'varchar', '2', '255' ],'session_expires' => [ 'datetime', '2', '' ],'session_data' => [ 'text,', '2', '' ],'user_online_status' => [ 'tinyint', '0', '1' ],
    ];

    public const VALIDATION = [];


    public static $injection = [];


    public static function jsonSQLReporting($argv, $sql) : void {
        global $json;
        if (!\is_array($json)) {
            $json = [];
        }
        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = [
            $argv,
            $sql
        ];
    }

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
                
                   if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
                   if (array_key_exists('user_ip', $argv)) {
            $user_ip = $argv['user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 16);
        }
                   if (array_key_exists('session_id', $argv)) {
            $session_id = $argv['session_id'];
            $stmt->bindParam(':session_id',$session_id, 2, 255);
        }
                   if (array_key_exists('session_expires', $argv)) {
            $stmt->bindValue(':session_expires',$argv['session_expires'], 2);
        }
                   if (array_key_exists('session_data', $argv)) {
            $stmt->bindValue(':session_data',$argv['session_data'], 2);
        }
                   if (array_key_exists('user_online_status', $argv)) {
            $user_online_status = $argv['user_online_status'];
            $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
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
        self::$injection = [];
        $aggregate = false;
        $group = $sql = '';
        $pdo = self::database();

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
                    $order .= 'session_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY session_id ASC LIMIT 100';
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
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |user_id|user_ip|session_id|session_expires|session_data|user_online_status))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_user_sessions';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        self::jsonSQLReporting(\func_get_args(), $sql);

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

        
        if ($primary !== null || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1 && \count($return) === 1)) {
            $return = isset($return[0]) && \is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

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
        $sql = 'INSERT INTO carbon_user_sessions (user_id, user_ip, session_id, session_expires, session_data, user_online_status) VALUES ( UNHEX(:user_id), UNHEX(:user_ip), :session_id, :session_expires, :session_data, :user_online_status)';

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

                
                    $user_id = $argv['user_id'];
                    $stmt->bindParam(':user_id',$user_id, 2, 16);
                        
                    $user_ip =  $argv['user_ip'] ?? null;
                    $stmt->bindParam(':user_ip',$user_ip, 2, 16);
                        
                    $session_id = $argv['session_id'];
                    $stmt->bindParam(':session_id',$session_id, 2, 255);
                        $stmt->bindValue(':session_expires',$argv['session_expires'], 2);
                        $stmt->bindValue(':session_data',$argv['session_data'], 2);
                        
                    $user_online_status =  $argv['user_online_status'] ?? '1';
                    $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
        



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
        self::$injection = [];
        if (empty($primary)) {
            return false;
        }

        foreach ($argv as $key => $value) {
            if (!\array_key_exists($key, self::COLUMNS)){
                return false;
            }
        }

        $sql = 'UPDATE carbon_user_sessions ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('user_id', $argv)) {
                $set .= 'user_id=UNHEX(:user_id),';
            }
            if (array_key_exists('user_ip', $argv)) {
                $set .= 'user_ip=UNHEX(:user_ip),';
            }
            if (array_key_exists('session_id', $argv)) {
                $set .= 'session_id=:session_id,';
            }
            if (array_key_exists('session_expires', $argv)) {
                $set .= 'session_expires=:session_expires,';
            }
            if (array_key_exists('session_data', $argv)) {
                $set .= 'session_data=:session_data,';
            }
            if (array_key_exists('user_online_status', $argv)) {
                $set .= 'user_online_status=:user_online_status,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

                   if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
                   if (array_key_exists('user_ip', $argv)) {
            $user_ip = $argv['user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 16);
        }
                   if (array_key_exists('session_id', $argv)) {
            $session_id = $argv['session_id'];
            $stmt->bindParam(':session_id',$session_id, 2, 255);
        }
                   if (array_key_exists('session_expires', $argv)) {
            $stmt->bindValue(':session_expires',$argv['session_expires'], 2);
        }
                   if (array_key_exists('session_data', $argv)) {
            $stmt->bindValue(':session_data',$argv['session_data'], 2);
        }
                   if (array_key_exists('user_online_status', $argv)) {
            $user_online_status = $argv['user_online_status'];
            $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
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
        $sql = 'DELETE FROM carbon_user_sessions ';

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
        } else {
        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        }

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        $r = self::bind($stmt, $argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = null;

        return $r;
    }
}
