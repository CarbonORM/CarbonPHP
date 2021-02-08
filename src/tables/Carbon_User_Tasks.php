<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Rest;
use PDO;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports
use CarbonPHP\CarbonPHP;
use Tests\RestTest;

class Carbon_User_Tasks extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_User_Tasks';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_user_tasks';
    public const TABLE_PREFIX = '';
    
    public const TASK_ID = 'carbon_user_tasks.task_id'; 
    public const USER_ID = 'carbon_user_tasks.user_id'; 
    public const FROM_ID = 'carbon_user_tasks.from_id'; 
    public const TASK_NAME = 'carbon_user_tasks.task_name'; 
    public const TASK_DESCRIPTION = 'carbon_user_tasks.task_description'; 
    public const PERCENT_COMPLETE = 'carbon_user_tasks.percent_complete'; 
    public const START_DATE = 'carbon_user_tasks.start_date'; 
    public const END_DATE = 'carbon_user_tasks.end_date'; 

    public const PRIMARY = [
        'carbon_user_tasks.task_id',
    ];

    public const COLUMNS = [
        'carbon_user_tasks.task_id' => 'task_id','carbon_user_tasks.user_id' => 'user_id','carbon_user_tasks.from_id' => 'from_id','carbon_user_tasks.task_name' => 'task_name','carbon_user_tasks.task_description' => 'task_description','carbon_user_tasks.percent_complete' => 'percent_complete','carbon_user_tasks.start_date' => 'start_date','carbon_user_tasks.end_date' => 'end_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_user_tasks.task_id' => ['binary', '2', '16'],'carbon_user_tasks.user_id' => ['binary', '2', '16'],'carbon_user_tasks.from_id' => ['binary', '2', '16'],'carbon_user_tasks.task_name' => ['varchar', '2', '40'],'carbon_user_tasks.task_description' => ['varchar', '2', '225'],'carbon_user_tasks.percent_complete' => ['int', '2', ''],'carbon_user_tasks.start_date' => ['datetime', '2', ''],'carbon_user_tasks.end_date' => ['datetime', '2', ''],
    ];
     
    /**
     * PHP validations works as follows:
     *  The first index '0' of PHP_VALIDATIONS will run after REGEX_VALIDATION's but
     *  before every other validation method described here below.
     *  The other index positions are respective to the request method calling the ORM
     *  or column which maybe present in the request.
     *  Column names using the 1 to 1 constants in the class maybe used for global
     *  specific methods when under PHP_VALIDATION, or method specific operations when under
     *  its respective request method, which only run when the column is requested or acted on.
     *  Global functions and method specific functions will receive the full request which
     *  maybe acted on by reference. All column specific validation methods will only receive
     *  the associated value given in the request which may also be received by reference.
     *  All methods MUST be declared as static.
     */
 
    public const PHP_VALIDATION = [
        self::PREPROCESS => [
            self::PREPROCESS => [
                [self::class => 'disallowPublicAccess', self::class],
                [self::class => 'restTesting', self::PREPROCESS, self::class],
            ]
        ],
        self::GET => [
            self::PREPROCESS => [
                self::DISALLOW_PUBLIC_ACCESS
            ],
            self::TASK_ID => [
                [self::class => 'restTesting', self::GET, self::PREPROCESS],
            ]
        ],
        self::POST => [
            self::PREPROCESS => [
                self::DISALLOW_PUBLIC_ACCESS,
                [self::class => 'restTesting', self::POST, self::PREPROCESS],
            ],
            self::PERCENT_COMPLETE => [
                [self::class => 'restTesting', self::PERCENT_COMPLETE, 'CustomArgument', self::POST],
            ],
            self::TASK_DESCRIPTION => [
                [self::class => 'restTesting']
            ]
        ],
        self::PUT => [
            self::PREPROCESS => [
                self::DISALLOW_PUBLIC_ACCESS,
                [self::class => 'restTesting']
            ],
            self::TASK_NAME => [
                [self::class => 'restTesting', self::PUT]
            ]
        ],
        self::DELETE => [
            self::PREPROCESS => [
                self::DISALLOW_PUBLIC_ACCESS,
                [self::class => 'restTesting', self::DELETE]
            ]
        ],
        self::FINISH => [
            self::PREPROCESS => [
                self::DISALLOW_PUBLIC_ACCESS,
                [self::class => 'restTesting', self::FINISH, self::PREPROCESS]
            ],
            self::END_DATE => [
                [self::class => 'restTesting', self::FINISH, 'Post Process When EndDate Requested.']
            ],
            self::FINISH => [
                [self::class => 'restTesting', self::FINISH]
            ]
        ]
    ]; 
 
    public const REGEX_VALIDATION = []; 
   
    public static function restTesting(...$argv)
    {
        if (CarbonPHP::$test) {
            /** @noinspection PhpUndefinedClassInspection - todo - remove example php files in react */
            RestTest::$restChallenge[] = $argv;
        }
    }
    
    public static function createTableSQL() : string {
    return /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbon_user_tasks` (
  `task_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL COMMENT 'This is the user the task is being assigned to',
  `from_id` binary(16) DEFAULT NULL COMMENT 'Keeping this colum so forgen key will remove task if user deleted',
  `task_name` varchar(40) NOT NULL,
  `task_description` varchar(225) DEFAULT NULL,
  `percent_complete` int DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `user_tasks_entity_entity_pk_fk` (`from_id`),
  KEY `user_tasks_entity_task_pk_fk` (`task_id`),
  KEY `carbon_user_tasks_carbons_entity_pk_fk_2` (`user_id`),
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk` FOREIGN KEY (`task_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk_2` FOREIGN KEY (`user_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_user_tasks_carbons_entity_pk_fk_3` FOREIGN KEY (`from_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
MYSQL;
    }
    
    
    /**
    *
    *   $argv = [
    *       Rest::SELECT => [
    *              ]'*column name array*', 'etc..'
    *        ],
    *
    *       Rest::WHERE => [
    *              'Column Name' => 'Value To Constrain',
    *              'Defaults to AND' => 'Nesting array switches to OR',
    *              [
    *                  'Column Name' => 'Value To Constrain',
    *                  'This array is OR'ed together' => 'Another sud array would `AND`'
    *                  [ etc... ]
    *              ]
    *        ],
    *        Rest::JOIN => [
    *            Rest::INNER => [
    *                Carbon_Users::CLASS_NAME => [
    *                    'Column Name' => 'Value To Constrain',
    *                    'Defaults to AND' => 'Nesting array switches to OR',
    *                    [
    *                       'Column Name' => 'Value To Constrain',
    *                       'This array is OR'ed together' => 'value'
    *                       [ 'Another sud array would `AND`ed... ]
    *                    ],
    *                    [ 'Column Name', Rest::LESS_THAN, 'Another Column Name']
    *                ]
    *            ],
    *            Rest::LEFT_OUTER => [
    *                Example_Table::CLASS_NAME => [
    *                    Location::USER_ID => Users::ID,
    *                    Location_References::ENTITY_KEY => $custom_var,
    *                   
    *                ],
    *                Example_Table_Two::CLASS_NAME => [
    *                    ect... 
    *                ]
    *            ]
    *        ],
    *        Rest::PAGINATION => [
    *              Rest::LIMIT => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *               Rest::ORDER => ['*column name*' => Rest::ASC ],  // i.e.  'username' => Rest::DESC
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
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

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

        self::postprocessRestRequest($return);
        self::completeRest();
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
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_user_tasks (task_id, user_id, from_id, task_name, task_description, percent_complete, start_date, end_date) VALUES ( UNHEX(:task_id), UNHEX(:user_id), UNHEX(:from_id), :task_name, :task_description, :percent_complete, :start_date, :end_date)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

                
        $task_id = $id = $argv['carbon_user_tasks.task_id'] ?? false;
        if ($id === false) {
             $task_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_user_tasks.task_id';
           if (!self::validateInternalColumn(self::POST, $ref, $task_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.task_id\'.');
           }            
        }
        $stmt->bindParam(':task_id',$task_id, 2, 16);
        
        
        
        
        
        if (!array_key_exists('carbon_user_tasks.user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_tasks.user_id" is missing from the request.', 'danger');
        }
        $user_id = $argv['carbon_user_tasks.user_id'];
        $ref='carbon_user_tasks.user_id';
        if (!self::validateInternalColumn(self::POST, $ref, $user_id)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.user_id\'.');
        }        
        $stmt->bindParam(':user_id',$user_id, 2, 16);
        
        
        
        $from_id = $argv['carbon_user_tasks.from_id'] ?? null;
        $ref='carbon_user_tasks.from_id';
        if (!self::validateInternalColumn(self::POST, $ref, $from_id, $from_id === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.from_id\'.');
        }        
        $stmt->bindParam(':from_id',$from_id, 2, 16);
        
        
        
        
        if (!array_key_exists('carbon_user_tasks.task_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_tasks.task_name" is missing from the request.', 'danger');
        }
        $task_name = $argv['carbon_user_tasks.task_name'];
        $ref='carbon_user_tasks.task_name';
        if (!self::validateInternalColumn(self::POST, $ref, $task_name)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.task_name\'.');
        }        
        $stmt->bindParam(':task_name',$task_name, 2, 40);
        
        
        
        $task_description = $argv['carbon_user_tasks.task_description'] ?? null;
        $ref='carbon_user_tasks.task_description';
        if (!self::validateInternalColumn(self::POST, $ref, $task_description, $task_description === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.task_description\'.');
        }        
        $stmt->bindParam(':task_description',$task_description, 2, 225);
        
                        
        
        $percent_complete = $argv['carbon_user_tasks.percent_complete'] ?? '0';
        $ref='carbon_user_tasks.percent_complete';
        if (!self::validateInternalColumn(self::POST, $ref, $percent_complete, $percent_complete === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.percent_complete\'.');
        }
        $stmt->bindValue(':percent_complete', $percent_complete, 2);
        
        
                        
        
        $start_date = $argv['carbon_user_tasks.start_date'] ?? null;
        $ref='carbon_user_tasks.start_date';
        if (!self::validateInternalColumn(self::POST, $ref, $start_date, $start_date === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.start_date\'.');
        }
        $stmt->bindValue(':start_date', $start_date, 2);
        
        
                        
        
        $end_date = $argv['carbon_user_tasks.end_date'] ?? null;
        $ref='carbon_user_tasks.end_date';
        if (!self::validateInternalColumn(self::POST, $ref, $end_date, $end_date === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
        }
        $stmt->bindValue(':end_date', $end_date, 2);
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_user_tasks');
            } 
             
            self::postprocessRestRequest($id); 
             
            self::completeRest(); 
            
            return $id; 
        } 
       
        self::completeRest();
        return false;
        
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
        self::startRest(self::PUT, $argv);
        
        if (empty($primary)) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.', 'danger');
        }
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => &$value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
            if (!self::validateInternalColumn(self::PUT, $key, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_user_tasks SET '; // intellij cant handle this otherwise

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

        $sql .= ' WHERE  task_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_user_tasks.task_id', $argv)) {
            $task_id = $argv['carbon_user_tasks.task_id'];
            $ref = 'carbon_user_tasks.task_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $task_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':task_id',$task_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.user_id', $argv)) {
            $user_id = $argv['carbon_user_tasks.user_id'];
            $ref = 'carbon_user_tasks.user_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $user_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.from_id', $argv)) {
            $from_id = $argv['carbon_user_tasks.from_id'];
            $ref = 'carbon_user_tasks.from_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $from_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':from_id',$from_id, 2, 16);
        }
        if (array_key_exists('carbon_user_tasks.task_name', $argv)) {
            $task_name = $argv['carbon_user_tasks.task_name'];
            $ref = 'carbon_user_tasks.task_name';
            if (!self::validateInternalColumn(self::PUT, $ref, $task_name)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':task_name',$task_name, 2, 40);
        }
        if (array_key_exists('carbon_user_tasks.task_description', $argv)) {
            $task_description = $argv['carbon_user_tasks.task_description'];
            $ref = 'carbon_user_tasks.task_description';
            if (!self::validateInternalColumn(self::PUT, $ref, $task_description)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
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

        self::prepostprocessRestRequest($return);
        
        self::postprocessRestRequest($return);
        
        self::completeRest();
        
        return true;
    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
    {
        self::startRest(self::DELETE, $argv);
        
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
        
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_user_tasks on c.entity_pk = carbon_user_tasks.task_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        if ($r) {
            $remove = [];
        }
        
        self::prepostprocessRestRequest($return);
        
        self::postprocessRestRequest($return);
        
        self::completeRest();
        
        return $r;
    }
     

    
}
