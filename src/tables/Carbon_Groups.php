<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Rest;
use PDO;
use PDOException;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


class Carbon_Groups extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Groups';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_groups';
    public const TABLE_PREFIX = '';
    
    public const GROUP_NAME = 'carbon_groups.group_name'; 
    public const ENTITY_ID = 'carbon_groups.entity_id'; 
    public const CREATED_BY = 'carbon_groups.created_by'; 
    public const CREATION_DATE = 'carbon_groups.creation_date'; 

    public const PRIMARY = [
        'carbon_groups.entity_id',
    ];

    public const COLUMNS = [
        'carbon_groups.group_name' => 'group_name','carbon_groups.entity_id' => 'entity_id','carbon_groups.created_by' => 'created_by','carbon_groups.creation_date' => 'creation_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_groups.group_name' => ['varchar', '2', '20'],'carbon_groups.entity_id' => ['binary', '2', '16'],'carbon_groups.created_by' => ['binary', '2', '16'],'carbon_groups.creation_date' => ['datetime', '2', ''],
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
 
    public const PHP_VALIDATION = []; 
 
    public const REGEX_VALIDATION = []; 
   

    
    public static function createTableSQL() : string {
    return /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbon_groups` (
  `group_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `entity_id` binary(16) NOT NULL,
  `created_by` binary(16) NOT NULL,
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`),
  KEY `carbon_feature_groups_carbons_entity_pk_fk_2` (`created_by`),
  CONSTRAINT `carbon_feature_groups_carbons_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_feature_groups_carbons_entity_pk_fk_2` FOREIGN KEY (`created_by`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        self::postpreprocessRestRequest($sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Groups.', 'danger');
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
     * @throws PublicAlert|PDOException
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_groups (group_name, entity_id, created_by) VALUES ( :group_name, UNHEX(:entity_id), UNHEX(:created_by))';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);
        
        
        
        
        if (!array_key_exists('carbon_groups.group_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_groups.group_name" is missing from the request.', 'danger');
        }
        $group_name = $argv['carbon_groups.group_name'];
        $ref='carbon_groups.group_name';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $group_name)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_groups.group_name\'.');
        }
        $stmt->bindParam(':group_name',$group_name, 2, 20);
                
        $entity_id = $id = $argv['carbon_groups.entity_id'] ?? false;
        if ($id === false) {
             $entity_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_groups.entity_id';
           $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $entity_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_groups.entity_id\'.');
           }            
        }
        $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        
        
        
        
        
        
        
        if (!array_key_exists('carbon_groups.created_by', $argv)) {
            throw new PublicAlert('Required argument "carbon_groups.created_by" is missing from the request.', 'danger');
        }
        $created_by = $argv['carbon_groups.created_by'];
        $ref='carbon_groups.created_by';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $created_by)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_groups.created_by\'.');
        }
        $stmt->bindParam(':created_by',$created_by, 2, 16);
        
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_groups');
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
    * @throws PublicAlert|PDOException
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
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $key, $op, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_groups.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_groups SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_groups.group_name', $argv)) {
            $set .= 'group_name=:group_name,';
        }
        if (array_key_exists('carbon_groups.entity_id', $argv)) {
            $set .= 'entity_id=UNHEX(:entity_id),';
        }
        if (array_key_exists('carbon_groups.created_by', $argv)) {
            $set .= 'created_by=UNHEX(:created_by),';
        }
        if (array_key_exists('carbon_groups.creation_date', $argv)) {
            $set .= 'creation_date=:creation_date,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_groups.group_name', $argv)) {
            $group_name = $argv['carbon_groups.group_name'];
            $ref = 'carbon_groups.group_name';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $group_name)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':group_name',$group_name, 2, 20);
        }
        if (array_key_exists('carbon_groups.entity_id', $argv)) {
            $entity_id = $argv['carbon_groups.entity_id'];
            $ref = 'carbon_groups.entity_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $entity_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        }
        if (array_key_exists('carbon_groups.created_by', $argv)) {
            $created_by = $argv['carbon_groups.created_by'];
            $ref = 'carbon_groups.created_by';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $created_by)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':created_by',$created_by, 2, 16);
        }
        if (array_key_exists('carbon_groups.creation_date', $argv)) {
            $stmt->bindValue(':creation_date',$argv['carbon_groups.creation_date'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Groups failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_groups.', '', $k); },
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
    * @throws PublicAlert|PDOException
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
                JOIN carbon_groups on c.entity_pk = carbon_groups.entity_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

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
