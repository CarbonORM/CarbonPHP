<?php

namespace CarbonPHP\Tables;

use PDO;
use PDOStatement;

use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Error\PublicAlert;


class Carbon_User_Sessions extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_user_sessions';
    public const USER_ID = 'carbon_user_sessions.user_id'; 
    public const USER_IP = 'carbon_user_sessions.user_ip'; 
    public const SESSION_ID = 'carbon_user_sessions.session_id'; 
    public const SESSION_EXPIRES = 'carbon_user_sessions.session_expires'; 
    public const SESSION_DATA = 'carbon_user_sessions.session_data'; 
    public const USER_ONLINE_STATUS = 'carbon_user_sessions.user_online_status'; 

    public const PRIMARY = [
        'carbon_user_sessions.session_id',
    ];

    public const COLUMNS = [
        'carbon_user_sessions.user_id' => 'user_id','carbon_user_sessions.user_ip' => 'user_ip','carbon_user_sessions.session_id' => 'session_id','carbon_user_sessions.session_expires' => 'session_expires','carbon_user_sessions.session_data' => 'session_data','carbon_user_sessions.user_online_status' => 'user_online_status',
    ];

    public const PDO_VALIDATION = [
        'carbon_user_sessions.user_id' => ['binary', '2', '16'],'carbon_user_sessions.user_ip' => ['binary', '2', '16'],'carbon_user_sessions.session_id' => ['varchar', '2', '255'],'carbon_user_sessions.session_expires' => ['datetime', '2', ''],'carbon_user_sessions.session_data' => ['text,', '2', ''],'carbon_user_sessions.user_online_status' => ['tinyint', '0', '1'],
    ];
 
    public const PHP_VALIDATION = []; 
 
    public const REGEX_VALIDATION = []; 
    
     
    public static function jsonSQLReporting($argv, $sql) : void {
        global $json;
        if (!is_array($json)) {
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
    * @throws PublicAlert
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, $pdo);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_User_Sessions.');
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        
        if ($primary !== null || (isset($argv[self::PAGINATION][self::LIMIT]) && $argv[self::PAGINATION][self::LIMIT] === 1 && count($return) === 1)) {
            $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

        return true;
    }

    /**
     * @param array $argv
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {
        self::$injection = []; 
         
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.");
            }
        } 
        
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_user_sessions (user_id, user_ip, session_id, session_expires, session_data, user_online_status) VALUES ( UNHEX(:user_id), UNHEX(:user_ip), :session_id, :session_expires, :session_data, :user_online_status)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $user_id = $argv['carbon_user_sessions.user_id'];
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
        $user_ip =  $argv['carbon_user_sessions.user_ip'] ?? null;
        $stmt->bindParam(':user_ip',$user_ip, 2, 16);
    
        $session_id = $argv['carbon_user_sessions.session_id'];
        $stmt->bindParam(':session_id',$session_id, 2, 255);
    
        $stmt->bindValue(':session_expires',$argv['carbon_user_sessions.session_expires'], 2);

        $stmt->bindValue(':session_data',$argv['carbon_user_sessions.session_data'], 2);

        $user_online_status =  $argv['carbon_user_sessions.user_online_status'] ?? '1';
        $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
    



    
        return $stmt->execute();
    
    }
     
   
    public static function validateSelectColumn($column) : bool {
        return (bool) preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|-|/| |carbon_user_sessions||\.user_id|\.user_ip|\.session_id|\.session_expires|\.session_data|\.user_online_status))+\)*)+ *(as [a-z]+)?#i', $column);
    }
    
    /**
     * @param string|null $primary
     * @param array $argv
     * @param PDO|null $pdo
     * @param bool $noHEX
     * @return string
     * @throws PublicAlert
     */
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

        // pagination [self::PAGINATION][self::LIMIT]
        if (array_key_exists(self::PAGINATION,$argv)) {
            if (!empty($argv[self::PAGINATION]) && is_string($argv[self::PAGINATION])) {
                $argv['pagination'] = json_decode($argv[self::PAGINATION], true);
            }
            if (array_key_exists(self::LIMIT,$argv[self::PAGINATION]) && is_numeric($argv[self::PAGINATION][self::LIMIT])) {
                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {
                    $limit = ' LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $argv[self::PAGINATION][self::LIMIT]) . ',' . $argv[self::PAGINATION][self::LIMIT];
                } else {
                    $limit = ' LIMIT ' . $argv[self::PAGINATION][self::LIMIT];
                }
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (array_key_exists(self::ORDER,$argv[self::PAGINATION]) && is_string($argv[self::PAGINATION][self::ORDER])) {
                    if (is_array($argv[self::PAGINATION][self::ORDER])) {
                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv[self::PAGINATION][self::ORDER];
                    }
                } else {
                    $order .= 'session_id ASC';
                }
            }
            $limit = "$order $limit";
        } else if (!$noHEX) {
            $limit = ' ORDER BY session_id ASC LIMIT 100';
        } else { 
            $limit = '';
        }

        // join 
        $join = ''; 
        $tableList = [];
        if (array_key_exists(self::JOIN, $argv) && !empty($argv[self::JOIN])) {
            if (!is_array($argv[self::JOIN])) { 
                throw new PublicAlert('The restful join field must be an array.');
            }
            foreach ($argv[self::JOIN] as $by => $tables) {
                $buildJoin = static function ($method) use ($tables, &$join, &$tableList) {
                    $joinColumns = [];
                    foreach ($tables as $table => $stmt) {
                        $tableList[] = $table;
                        switch (count($stmt)) {   
                            case 2: 
                                if (is_string($stmt[0]) && is_string($stmt[1])) {
                                    $joinColumns[] = $stmt[0];
                                    $joinColumns[] = $stmt[1];
                                    $join .= $method . $table . ' ON ' . $stmt[0] . '=' . $stmt[1];
                                } else {
                                    throw new PublicAlert('One or more of the array values provided in the restful JOIN condition are not strings.');
                                }
                                break;
                            case 3:
                                if (is_string($stmt[0]) && is_string($stmt[1]) && is_string($stmt[2])) {
                                    if (!((bool) preg_match('#^=|>=|<=$#', $stmt[1]))){ 
                                        throw new PublicAlert('Restful column joins may only use one (=,>=, or <=).');
                                    }
                                    $joinColumns[] = $stmt[0];
                                    $joinColumns[] = $stmt[2];
                                    $join .= $method . $table . ' ON ' . $stmt[0] . $stmt[1] . $stmt[2]; 
                                } else {
                                    throw new PublicAlert('One or more of the array values provided in the restful JOIN condition are not strings.');
                                }
                                break;
                            default:
                                throw new PublicAlert('Restful joins across two tables must be populated with two or three array values with column names, or an appropriate joining operator and column names.');
                        }
                    } 
                    foreach ($joinColumns as $columnName) { 
                        if (!parent::validateColumnName($columnName, $tableList)) {
                             throw new PublicAlert("Could not validate join column $columnName. Be sure correct restful tables are referenced.");
                        }
                    }
                    return true;
                };
                switch ($by) {
                    case self::INNER:
                        if (!$buildJoin(' INNER JOIN ')) {
                            throw new PublicAlert('The restful inner join had an unknown error.');
                        }
                        break;
                    case self::LEFT:
                        if (!$buildJoin(' LEFT JOIN ')) {
                            throw new PublicAlert('The restful left join had an unknown error.'); 
                        }
                        break;
                    case self::RIGHT:
                        if (!$buildJoin(' RIGHT JOIN ')) {
                            throw new PublicAlert('The restful right join had an unknown error.'); 
                        }
                        break;
                    default:
                        throw new PublicAlert('Restful join stmt may only use one of (' .  self::INNER . ',' . self::LEFT . ', or ' . self::RIGHT . ').');
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
                $tablesReferenced = $tableList;
                while (!empty($tablesReferenced)) {
                     $table = __NAMESPACE__ . '\\' . array_pop($tablesReferenced);
                     
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
                    throw new PublicAlert('Could not validate the column $column');
                }
                $sql .= $column;
                $aggregate = true;
            }
        }
 
        // case sensitive select 
        $sql = 'SELECT ' .  $sql . ' FROM carbon_user_sessions ' . $join;
       
        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_user_sessions', self::PDO_VALIDATION);
            }
        } else {
            $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo, 'carbon_user_sessions').'';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . implode(', ', $group). ' ';
        }

        $sql .= $limit;

        self::jsonSQLReporting(func_get_args(), $sql);

        return '(' . $sql . ')';
    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        self::$injection = []; 
        
        if (empty($primary)) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.');
        }
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.');
            }
        }

        $sql = 'UPDATE carbon_user_sessions ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_user_sessions.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('carbon_user_sessions.user_ip', $argv)) {
            $set .= 'user_ip=UNHEX(:user_ip),';
        }
        if (array_key_exists('carbon_user_sessions.session_id', $argv)) {
            $set .= 'session_id=:session_id,';
        }
        if (array_key_exists('carbon_user_sessions.session_expires', $argv)) {
            $set .= 'session_expires=:session_expires,';
        }
        if (array_key_exists('carbon_user_sessions.session_data', $argv)) {
            $set .= 'session_data=:session_data,';
        }
        if (array_key_exists('carbon_user_sessions.user_online_status', $argv)) {
            $set .= 'user_online_status=:user_online_status,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo, 'carbon_user_sessions').'';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_user_sessions.user_id', $argv)) {
            $user_id = $argv['carbon_user_sessions.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_sessions.user_ip', $argv)) {
            $user_ip = $argv['carbon_user_sessions.user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 16);
        }
        if (array_key_exists('carbon_user_sessions.session_id', $argv)) {
            $session_id = $argv['carbon_user_sessions.session_id'];
            $stmt->bindParam(':session_id',$session_id, 2, 255);
        }
        if (array_key_exists('carbon_user_sessions.session_expires', $argv)) {
            $stmt->bindValue(':session_expires',$argv['carbon_user_sessions.session_expires'], 2);
        }
        if (array_key_exists('carbon_user_sessions.session_data', $argv)) {
            $stmt->bindValue(':session_data',$argv['carbon_user_sessions.session_data'], 2);
        }
        if (array_key_exists('carbon_user_sessions.user_online_status', $argv)) {
            $user_online_status = $argv['carbon_user_sessions.user_online_status'];
            $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_User_Sessions failed to execute the update query.');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_user_sessions.', '', $k); },
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
    * @throws PublicAlert
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv) : bool
    {
        self::$injection = []; 
        
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE FROM carbon_user_sessions ';

        $pdo = self::database();
        
        if (null === $primary) {
           /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.');
            }
            
            $where = self::buildWhere($argv, $pdo, 'carbon_user_sessions', self::PDO_VALIDATION);
            
            if (empty($where)) {
                throw new PublicAlert('The where condition provided appears invalid.');
            }

            $sql .= ' WHERE ' . $where;
        } 
               


        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
}
