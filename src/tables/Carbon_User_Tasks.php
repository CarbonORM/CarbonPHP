<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


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
    *              'order' => ['*column name*'=> '(ASC|DESC)'],  // i.e.  'username' => 'ASC'
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

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_User_Tasks.', 'danger');
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
     * @noinspection SqlResolve
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_user_tasks (task_id, user_id, from_id, task_name, task_description, percent_complete, start_date, end_date) VALUES ( UNHEX(:task_id), UNHEX(:user_id), UNHEX(:from_id), :task_name, :task_description, :percent_complete, :start_date, :end_date)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        if (!array_key_exists('carbon_user_tasks.task_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_tasks.task_id" is missing from the request.', 'danger');
        }
        $task_id = $argv['carbon_user_tasks.task_id'];
        $stmt->bindParam(':task_id',$task_id, 2, 16);
    
        $user_id = $id = $argv['carbon_user_tasks.user_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
    
        $from_id =  $argv['carbon_user_tasks.from_id'] ?? null;
        $stmt->bindParam(':from_id',$from_id, 2, 16);
    
    
        if (!array_key_exists('carbon_user_tasks.task_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_tasks.task_name" is missing from the request.', 'danger');
        }
        $task_name = $argv['carbon_user_tasks.task_name'];
        $stmt->bindParam(':task_name',$task_name, 2, 40);
    
    
        $task_description =  $argv['carbon_user_tasks.task_description'] ?? null;
        $stmt->bindParam(':task_description',$task_description, 2, 225);
    
        $stmt->bindValue(':percent_complete',array_key_exists('carbon_user_tasks.percent_complete',$argv) ? $argv['carbon_user_tasks.percent_complete'] : '0', 2);
    
        $stmt->bindValue(':start_date',array_key_exists('carbon_user_tasks.start_date',$argv) ? $argv['carbon_user_tasks.start_date'] : null, 2);
    
        $stmt->bindValue(':end_date',array_key_exists('carbon_user_tasks.end_date',$argv) ? $argv['carbon_user_tasks.end_date'] : null, 2);
    


        return $stmt->execute() ? $id : false;
    
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
        if (empty($primary)) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.', 'danger');
        }
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
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

        $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

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
            throw new PublicAlert('Restful table Carbon_User_Tasks failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
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
    * @noinspection SqlResolve
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
            throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
        }
        
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
