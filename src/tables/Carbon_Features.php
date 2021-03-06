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


class Carbon_Features extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Features';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_features';
    public const TABLE_PREFIX = '';
    
    public const FEATURE_ENTITY_ID = 'carbon_features.feature_entity_id'; 
    public const FEATURE_CODE = 'carbon_features.feature_code'; 
    public const FEATURE_CREATION_DATE = 'carbon_features.feature_creation_date'; 

    public const PRIMARY = [
        'carbon_features.feature_entity_id',
    ];

    public const COLUMNS = [
        'carbon_features.feature_entity_id' => 'feature_entity_id','carbon_features.feature_code' => 'feature_code','carbon_features.feature_creation_date' => 'feature_creation_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_features.feature_entity_id' => ['binary', 'PDO::PARAM_STR', '16'],'carbon_features.feature_code' => ['varchar', 'PDO::PARAM_STR', '30'],'carbon_features.feature_creation_date' => ['datetime', 'PDO::PARAM_STR', ''],
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
    CREATE TABLE `carbon_features` (
  `feature_entity_id` binary(16) NOT NULL,
  `feature_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `feature_creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feature_entity_id`),
  UNIQUE KEY `carbon_features_feature_code_uindex` (`feature_code`),
  UNIQUE KEY `carbon_features_feature_entity_id_uindex` (`feature_entity_id`),
  CONSTRAINT `carbon_features_carbons_entity_pk_fk` FOREIGN KEY (`feature_entity_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
            throw new PublicAlert('Failed to execute the query on Carbon_Features.', 'danger');
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
     * @return bool|string|mixed
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
        
        $sql = 'INSERT INTO carbon_features (feature_entity_id, feature_code) VALUES ( UNHEX(:feature_entity_id), :feature_code)';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);        
        $feature_entity_id = $id = $argv['carbon_features.feature_entity_id'] ?? false;
        if ($id === false) {
             $feature_entity_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_features.feature_entity_id';
           $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $feature_entity_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_features.feature_entity_id\'.');
           }            
        }
        $stmt->bindParam(':feature_entity_id',$feature_entity_id, PDO::PARAM_STR, 16);
        
        
        
        
        
        
        
        if (!array_key_exists('carbon_features.feature_code', $argv)) {
            throw new PublicAlert('Required argument "carbon_features.feature_code" is missing from the request.', 'danger');
        }
        $feature_code = $argv['carbon_features.feature_code'];
        $ref='carbon_features.feature_code';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $feature_code)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_features.feature_code\'.');
        }
        $stmt->bindParam(':feature_code',$feature_code, PDO::PARAM_STR, 30);
        
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_features');
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
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_features.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_features SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_features.feature_entity_id', $argv)) {
            $set .= 'feature_entity_id=UNHEX(:feature_entity_id),';
        }
        if (array_key_exists('carbon_features.feature_code', $argv)) {
            $set .= 'feature_code=:feature_code,';
        }
        if (array_key_exists('carbon_features.feature_creation_date', $argv)) {
            $set .= 'feature_creation_date=:feature_creation_date,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  feature_entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_features.feature_entity_id', $argv)) {
            $feature_entity_id = $argv['carbon_features.feature_entity_id'];
            $ref = 'carbon_features.feature_entity_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $feature_entity_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':feature_entity_id',$feature_entity_id, PDO::PARAM_STR, 16);
        }
        if (array_key_exists('carbon_features.feature_code', $argv)) {
            $feature_code = $argv['carbon_features.feature_code'];
            $ref = 'carbon_features.feature_code';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $feature_code)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':feature_code',$feature_code, PDO::PARAM_STR, 30);
        }
        if (array_key_exists('carbon_features.feature_creation_date', $argv)) {
            $stmt->bindValue(':feature_creation_date',$argv['carbon_features.feature_creation_date'], PDO::PARAM_STR);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Features failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_features.', '', $k); },
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
                JOIN carbon_features on c.entity_pk = carbon_features.feature_entity_id';

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
        
        self::prepostprocessRestRequest($remove);
        
        self::postprocessRestRequest($remove);
        
        self::completeRest();
        
        return $r;
    }
     

    
}
