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


class Carbon_User_Tasks extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_user_tasks';
    public const TASK_ID = 'carbon_user_tasks.task_id'; 
    public const USER_ID = 'carbon_user_tasks.user_id'; 
    public const FROM_ID = 'carbon_user_tasks.from_id'; 
    public const TASK_NAME = 'carbon_user_tasks.task_name'; 
    public const TASK_DESCRIPTION = 'carbon_user_tasks.task_description'; 
    public const PERCENT_COMPLETE = 'carbon_user_tasks.percent_complete'; 
    public const START_DATE = 'carbon_user_tasks.start_date'; 
    public const END_DATE = 'carbon_user_tasks.end_date'; 

    public const PRIMARY = [
        'carbon_user_tasks.user_id',
    ];

    public const COLUMNS = [
        'carbon_user_tasks.task_id' => 'task_id','carbon_user_tasks.user_id' => 'user_id','carbon_user_tasks.from_id' => 'from_id','carbon_user_tasks.task_name' => 'task_name','carbon_user_tasks.task_description' => 'task_description','carbon_user_tasks.percent_complete' => 'percent_complete','carbon_user_tasks.start_date' => 'start_date','carbon_user_tasks.end_date' => 'end_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_user_tasks.task_id' => ['binary', '2', '16'],'carbon_user_tasks.user_id' => ['binary', '2', '16'],'carbon_user_tasks.from_id' => ['binary', '2', '16'],'carbon_user_tasks.task_name' => ['varchar', '2', '40'],'carbon_user_tasks.task_description' => ['varchar', '2', '225'],'carbon_user_tasks.percent_complete' => ['int', '2', ''],'carbon_user_tasks.start_date' => ['datetime', '2', ''],'carbon_user_tasks.end_date' => ['datetime', '2', ''],
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
            throw new PublicAlert('Failed to execute the query on Carbon_User_Tasks.');
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
        $sql = 'INSERT INTO carbon_user_tasks (task_id, user_id, from_id, task_name, task_description, percent_complete, start_date, end_date) VALUES ( UNHEX(:task_id), UNHEX(:user_id), UNHEX(:from_id), :task_name, :task_description, :percent_complete, :start_date, :end_date)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $task_id = $argv['carbon_user_tasks.task_id'];
        $stmt->bindParam(':task_id',$task_id, 2, 16);
    
        $user_id = $id = $argv['carbon_user_tasks.user_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
        $from_id =  $argv['carbon_user_tasks.from_id'] ?? null;
        $stmt->bindParam(':from_id',$from_id, 2, 16);
    
        $task_name = $argv['carbon_user_tasks.task_name'];
        $stmt->bindParam(':task_name',$task_name, 2, 40);
    
        $task_description =  $argv['carbon_user_tasks.task_description'] ?? null;
        $stmt->bindParam(':task_description',$task_description, 2, 225);
    
        $stmt->bindValue(':percent_complete',array_key_exists('carbon_user_tasks.percent_complete',$argv) ? $argv['carbon_user_tasks.percent_complete'] : '0', 2);

        $stmt->bindValue(':start_date',array_key_exists('carbon_user_tasks.start_date',$argv) ? $argv['carbon_user_tasks.start_date'] : null, 2);

        $stmt->bindValue(':end_date',array_key_exists('carbon_user_tasks.end_date',$argv) ? $argv['carbon_user_tasks.end_date'] : null, 2);



        return $stmt->execute() ? $id : false;
    
    }
     
   
    public static function validateSelectColumn($column) : bool {
        return (bool) preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|-|/| |carbon_user_tasks||\.task_id|\.user_id|\.from_id|\.task_name|\.task_description|\.percent_complete|\.start_date|\.end_date))+\)*)+ *(as [a-z]+)?#i', $column);
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
                    $order .= 'user_id ASC';
                }
            }
            $limit = "$order $limit";
        } else if (!$noHEX) {
            $limit = ' ORDER BY user_id ASC LIMIT 100';
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
        $sql = 'SELECT ' .  $sql . ' FROM carbon_user_tasks ' . $join;
       
        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_user_tasks', self::PDO_VALIDATION);
            }
        } else {
            $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo, 'carbon_user_tasks').')';
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

        $sql = 'UPDATE carbon_user_tasks ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_user_tasks.task_id', $argv)) {
            $set .= 'task_id=UNHEX(:task_id),';
        }
        if (array_key_exists('carbon_user_tasks.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('carbon_user_tasks.from_id', $argv)) {
            $set .= 'from_id=UNHEX(:from_id),';
        }
        if (array_key_exists('carbon_user_tasks.task_name', $argv)) {
            $set .= 'task_name=:task_name,';
        }
        if (array_key_exists('carbon_user_tasks.task_description', $argv)) {
            $set .= 'task_description=:task_description,';
        }
        if (array_key_exists('carbon_user_tasks.percent_complete', $argv)) {
            $set .= 'percent_complete=:percent_complete,';
        }
        if (array_key_exists('carbon_user_tasks.start_date', $argv)) {
            $set .= 'start_date=:start_date,';
        }
        if (array_key_exists('carbon_user_tasks.end_date', $argv)) {
            $set .= 'end_date=:end_date,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo, 'carbon_user_tasks').')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_user_tasks.task_id', $argv)) {
            $task_id = $argv['carbon_user_tasks.task_id'];
            $stmt->bindParam(':task_id',$task_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.user_id', $argv)) {
            $user_id = $argv['carbon_user_tasks.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.from_id', $argv)) {
            $from_id = $argv['carbon_user_tasks.from_id'];
            $stmt->bindParam(':from_id',$from_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.task_name', $argv)) {
            $task_name = $argv['carbon_user_tasks.task_name'];
            $stmt->bindParam(':task_name',$task_name, 2, 40);
        }
        if (array_key_exists('carbon_user_tasks.task_description', $argv)) {
            $task_description = $argv['carbon_user_tasks.task_description'];
            $stmt->bindParam(':task_description',$task_description, 2, 225);
        }
        if (array_key_exists('carbon_user_tasks.percent_complete', $argv)) {
            $stmt->bindValue(':percent_complete',$argv['carbon_user_tasks.percent_complete'], 2);
        }
        if (array_key_exists('carbon_user_tasks.start_date', $argv)) {
            $stmt->bindValue(':start_date',$argv['carbon_user_tasks.start_date'], 2);
        }
        if (array_key_exists('carbon_user_tasks.end_date', $argv)) {
            $stmt->bindValue(':end_date',$argv['carbon_user_tasks.end_date'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_User_Tasks failed to execute the update query.');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_user_tasks.', '', $k); },
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
        if (null !== $primary) {
            return Carbons::Delete($remove, $primary, $argv);
        }

        /**
         *   While useful, we've decided to disallow full
         *   table deletions through the rest api. For the
         *   n00bs and future self, "I got chu."
         */
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.');
        }

        self::$injection = []; 
        
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_user_tasks on c.entity_pk = carbon_user_tasks.user_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_user_tasks', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
}
