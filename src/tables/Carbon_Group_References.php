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


class Carbon_Group_References extends Rest implements iRestfulReferences
{
    
    public const CLASS_NAME = 'Carbon_Group_References';
    public const TABLE_NAME = 'carbon_group_references';
    public const TABLE_NAMESPACE = 'CarbonPHP\Tables';
    
    public const GROUP_ID = 'carbon_group_references.group_id'; 
    public const ALLOWED_TO_GRANT_GROUP_ID = 'carbon_group_references.allowed_to_grant_group_id'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'carbon_group_references.group_id' => 'group_id','carbon_group_references.allowed_to_grant_group_id' => 'allowed_to_grant_group_id',
    ];

    public const PDO_VALIDATION = [
        'carbon_group_references.group_id' => ['binary', '2', '16'],'carbon_group_references.allowed_to_grant_group_id' => ['binary', '2', '16'],
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
     *  All methods MUST be declaired as static.
     */
 
    public const PHP_VALIDATION = []; 
 
    public const REGEX_VALIDATION = []; 
    
    public static function createTableSQL() : string {
    return <<<MYSQL
    CREATE TABLE `carbon_group_references` (
  `group_id` binary(16) DEFAULT NULL,
  `allowed_to_grant_group_id` binary(16) DEFAULT NULL,
  KEY `carbon_group_references_carbons_entity_pk_fk` (`group_id`),
  KEY `carbon_group_references_carbons_entity_pk_fk_2` (`allowed_to_grant_group_id`),
  CONSTRAINT `carbon_group_references_carbons_entity_pk_fk` FOREIGN KEY (`group_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbon_group_references_carbons_entity_pk_fk_2` FOREIGN KEY (`allowed_to_grant_group_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
MYSQL;
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
    public static function Get(array &$return, array $argv = []): bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
   
        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Group_References.', 'danger');
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
        
        $sql = 'INSERT INTO carbon_group_references (group_id, allowed_to_grant_group_id) VALUES ( UNHEX(:group_id), UNHEX(:allowed_to_grant_group_id))';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        $group_id =  $argv['carbon_group_references.group_id'] ?? null;
        $stmt->bindParam(':group_id',$group_id, 2, 16);
    
    
        $allowed_to_grant_group_id =  $argv['carbon_group_references.allowed_to_grant_group_id'] ?? null;
        $stmt->bindParam(':allowed_to_grant_group_id',$allowed_to_grant_group_id, 2, 16);
    



    
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

        $sql = 'UPDATE carbon_group_references ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_group_references.group_id', $argv)) {
            $set .= 'group_id=UNHEX(:group_id),';
        }
        if (array_key_exists('carbon_group_references.allowed_to_grant_group_id', $argv)) {
            $set .= 'allowed_to_grant_group_id=UNHEX(:allowed_to_grant_group_id),';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        
        $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_group_references', [self::class]);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_group_references.group_id', $argv)) {
            $group_id = $argv['carbon_group_references.group_id'];
            $stmt->bindParam(':group_id',$group_id, 2, 16);
        }
        if (array_key_exists('carbon_group_references.allowed_to_grant_group_id', $argv)) {
            $allowed_to_grant_group_id = $argv['carbon_group_references.allowed_to_grant_group_id'];
            $stmt->bindParam(':allowed_to_grant_group_id',$allowed_to_grant_group_id, 2, 16);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Group_References failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_group_references.', '', $k); },
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
        
        $sql = 'DELETE FROM carbon_group_references ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_group_references', [self::class]);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
