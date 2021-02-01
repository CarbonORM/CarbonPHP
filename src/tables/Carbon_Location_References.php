<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


class Carbon_Location_References extends Rest implements iRestfulReferences
{
    
    public const CLASS_NAME = 'Carbon_Location_References';
    public const TABLE_NAME = 'carbon_location_references';
    public const TABLE_NAMESPACE = 'CarbonPHP\Tables';
    
    public const ENTITY_REFERENCE = 'carbon_location_references.entity_reference'; 
    public const LOCATION_REFERENCE = 'carbon_location_references.location_reference'; 
    public const LOCATION_TIME = 'carbon_location_references.location_time'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'carbon_location_references.entity_reference' => 'entity_reference','carbon_location_references.location_reference' => 'location_reference','carbon_location_references.location_time' => 'location_time',
    ];

    public const PDO_VALIDATION = [
        'carbon_location_references.entity_reference' => ['binary', '2', '16'],'carbon_location_references.location_reference' => ['binary', '2', '16'],'carbon_location_references.location_time' => ['datetime', '2', ''],
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
        [self::DISALLOW_PUBLIC_ACCESS],
        self::GET => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::POST => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::PUT => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::DELETE => [ self::DISALLOW_PUBLIC_ACCESS ],    
    ]; 
 
    public const REGEX_VALIDATION = []; 
    
    public static function createTableSQL() : string {
    return <<<MYSQL
    CREATE TABLE `carbon_location_references` (
  `entity_reference` binary(16) NOT NULL,
  `location_reference` binary(16) NOT NULL,
  `location_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `carbon_location_references_carbons_entity_pk_fk` (`entity_reference`),
  KEY `carbon_location_references_carbons_entity_pk_fk_2` (`location_reference`),
  CONSTRAINT `carbon_location_references_carbons_entity_pk_fk` FOREIGN KEY (`entity_reference`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_location_references_carbons_entity_pk_fk_2` FOREIGN KEY (`location_reference`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='tuple';
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
    public static function Get(array &$return, array $argv = []): bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
   
        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Location_References.', 'danger');
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
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     */
    public static function Post(array $argv, string $dependantEntityId = null): bool
    {   
        self::$tableNamespace = self::TABLE_NAMESPACE;
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_location_references (entity_reference, location_reference) VALUES ( UNHEX(:entity_reference), UNHEX(:location_reference))';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        if (!array_key_exists('carbon_location_references.entity_reference', $argv)) {
            throw new PublicAlert('Required argument "carbon_location_references.entity_reference" is missing from the request.', 'danger');
        }
        $entity_reference = $argv['carbon_location_references.entity_reference'];
        $stmt->bindParam(':entity_reference',$entity_reference, 2, 16);
    
    
        if (!array_key_exists('carbon_location_references.location_reference', $argv)) {
            throw new PublicAlert('Required argument "carbon_location_references.location_reference" is missing from the request.', 'danger');
        }
        $location_reference = $argv['carbon_location_references.location_reference'];
        $stmt->bindParam(':location_reference',$location_reference, 2, 16);
    



    
        return $stmt->execute();
    
    }
    
    /**
    * @param array $return
    
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &$return,  array $argv) : bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
        $where = $argv[self::WHERE];

        $argv = $argv[self::UPDATE];

        if (empty($where) || empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated specific where conditions.', 'danger');
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
        }

        $sql = 'UPDATE carbon_location_references ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_location_references.entity_reference', $argv)) {
            $set .= 'entity_reference=UNHEX(:entity_reference),';
        }
        if (array_key_exists('carbon_location_references.location_reference', $argv)) {
            $set .= 'location_reference=UNHEX(:location_reference),';
        }
        if (array_key_exists('carbon_location_references.location_time', $argv)) {
            $set .= 'location_time=:location_time,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        
        $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_location_references', [self::class]);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_location_references.entity_reference', $argv)) {
            $entity_reference = $argv['carbon_location_references.entity_reference'];
            $stmt->bindParam(':entity_reference',$entity_reference, 2, 16);
        }
        if (array_key_exists('carbon_location_references.location_reference', $argv)) {
            $location_reference = $argv['carbon_location_references.location_reference'];
            $stmt->bindParam(':location_reference',$location_reference, 2, 16);
        }
        if (array_key_exists('carbon_location_references.location_time', $argv)) {
            $stmt->bindValue(':location_time',$argv['carbon_location_references.location_time'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Location_References failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_location_references.', '', $k); },
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
    public static function Delete(array &$remove, array $argv = []) : bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
        $sql = 'DELETE FROM carbon_location_references ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_location_references', [self::class]);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
