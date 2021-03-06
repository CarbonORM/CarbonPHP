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


class Carbons extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbons';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbons';
    public const TABLE_PREFIX = '';
    
    public const ENTITY_PK = 'carbons.entity_pk'; 
    public const ENTITY_FK = 'carbons.entity_fk'; 
    public const ENTITY_TAG = 'carbons.entity_tag'; 

    public const PRIMARY = [
        'carbons.entity_pk',
    ];

    public const COLUMNS = [
        'carbons.entity_pk' => 'entity_pk','carbons.entity_fk' => 'entity_fk','carbons.entity_tag' => 'entity_tag',
    ];

    public const PDO_VALIDATION = [
        'carbons.entity_pk' => ['binary', 'PDO::PARAM_STR', '16'],'carbons.entity_fk' => ['binary', 'PDO::PARAM_STR', '16'],'carbons.entity_tag' => ['varchar', 'PDO::PARAM_STR', '100'],
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
                [self::class => 'disallowPublicAccess', self::class]
            ]
        ],
        self::GET => [self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS]],
        self::POST => [self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS]],
        self::PUT => [self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS]],
        self::DELETE => [self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS]],
        self::FINISH => [self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS]]
    ]; 
 
    public const REGEX_VALIDATION = []; 
   

    
    public static function createTableSQL() : string {
    return /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbons` (
  `entity_pk` binary(16) NOT NULL,
  `entity_fk` binary(16) DEFAULT NULL,
  `entity_tag` varchar(100) NOT NULL,
  PRIMARY KEY (`entity_pk`),
  UNIQUE KEY `entity_entity_pk_uindex` (`entity_pk`),
  KEY `entity_entity_entity_pk_fk` (`entity_fk`),
  CONSTRAINT `entity_entity_entity_pk_fk` FOREIGN KEY (`entity_fk`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
            throw new PublicAlert('Failed to execute the query on Carbons.', 'danger');
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
        
        $sql = 'INSERT INTO carbons (entity_pk, entity_fk, entity_tag) VALUES ( UNHEX(:entity_pk), UNHEX(:entity_fk), :entity_tag)';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);
        
        $entity_pk = $id = $argv['carbons.entity_pk'] ?? false;
        if ($id === false) {
             $entity_pk = $id = self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];
        } else {
            $ref='carbons.entity_pk';
            $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $entity_pk)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbons.entity_pk\'.');
           }            
        }
        $stmt->bindParam(':entity_pk',$entity_pk, PDO::PARAM_STR, 16);

        
        
        
        
        
        $entity_fk = $argv['carbons.entity_fk'] ?? null;
        $ref='carbons.entity_fk';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $entity_fk, $entity_fk === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbons.entity_fk\'.');
        }
        $stmt->bindParam(':entity_fk',$entity_fk, PDO::PARAM_STR, 16);
        
        
        
        
        
        if (!array_key_exists('carbons.entity_tag', $argv)) {
            throw new PublicAlert('Required argument "carbons.entity_tag" is missing from the request.', 'danger');
        }
        $entity_tag = $argv['carbons.entity_tag'];
        $ref='carbons.entity_tag';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $entity_tag)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbons.entity_tag\'.');
        }
        $stmt->bindParam(':entity_tag',$entity_tag, PDO::PARAM_STR, 100);
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbons');
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
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbons.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbons SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbons.entity_pk', $argv)) {
            $set .= 'entity_pk=UNHEX(:entity_pk),';
        }
        if (array_key_exists('carbons.entity_fk', $argv)) {
            $set .= 'entity_fk=UNHEX(:entity_fk),';
        }
        if (array_key_exists('carbons.entity_tag', $argv)) {
            $set .= 'entity_tag=:entity_tag,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_pk=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbons.entity_pk', $argv)) {
            $entity_pk = $argv['carbons.entity_pk'];
            $ref = 'carbons.entity_pk';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $entity_pk)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':entity_pk',$entity_pk, PDO::PARAM_STR, 16);
        }
        if (array_key_exists('carbons.entity_fk', $argv)) {
            $entity_fk = $argv['carbons.entity_fk'];
            $ref = 'carbons.entity_fk';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $entity_fk)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':entity_fk',$entity_fk, PDO::PARAM_STR, 16);
        }
        if (array_key_exists('carbons.entity_tag', $argv)) {
            $entity_tag = $argv['carbons.entity_tag'];
            $ref = 'carbons.entity_tag';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $entity_tag)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':entity_tag',$entity_tag, PDO::PARAM_STR, 100);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbons failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbons.', '', $k); },
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
        
        /** @noinspection SqlWithoutWhere
         * @noinspection UnknownInspectionInspection - intellij is funny sometimes.
         */
        $sql = 'DELETE FROM carbons ';

        $pdo = self::database();
        
        if (null === $primary) {
           /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
            }
            
            $where = self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
            
            if (empty($where)) {
                throw new PublicAlert('The where condition provided appears invalid.', 'danger');
            }

            $sql .= ' WHERE ' . $where;
        } else {
            $sql .= ' WHERE  entity_pk=UNHEX('.self::addInjection($primary, $pdo).')';
        }


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
