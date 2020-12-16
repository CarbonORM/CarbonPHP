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


class Carbon_Feature_Group_References extends Rest implements iRestfulReferences
{
    
    public const TABLE_NAME = 'carbon_feature_group_references';
    public const FEATURE_ENTITY_ID = 'carbon_feature_group_references.feature_entity_id'; 
    public const GROUP_ENTITY_ID = 'carbon_feature_group_references.group_entity_id'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'carbon_feature_group_references.feature_entity_id' => 'feature_entity_id','carbon_feature_group_references.group_entity_id' => 'group_entity_id',
    ];

    public const PDO_VALIDATION = [
        'carbon_feature_group_references.feature_entity_id' => ['binary', '2', '16'],'carbon_feature_group_references.group_entity_id' => ['binary', '2', '16'],
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
        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Feature_Group_References.', 'danger');
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
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_feature_group_references (feature_entity_id, group_entity_id) VALUES ( UNHEX(:feature_entity_id), UNHEX(:group_entity_id))';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        $feature_entity_id =  $argv['carbon_feature_group_references.feature_entity_id'] ?? null;
        $stmt->bindParam(':feature_entity_id',$feature_entity_id, 2, 16);
    
    
        $group_entity_id =  $argv['carbon_feature_group_references.group_entity_id'] ?? null;
        $stmt->bindParam(':group_entity_id',$group_entity_id, 2, 16);
    



    
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

        $sql = 'UPDATE carbon_feature_group_references ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_feature_group_references.feature_entity_id', $argv)) {
            $set .= 'feature_entity_id=UNHEX(:feature_entity_id),';
        }
        if (array_key_exists('carbon_feature_group_references.group_entity_id', $argv)) {
            $set .= 'group_entity_id=UNHEX(:group_entity_id),';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        
        $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_feature_group_references', self::PDO_VALIDATION);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_feature_group_references.feature_entity_id', $argv)) {
            $feature_entity_id = $argv['carbon_feature_group_references.feature_entity_id'];
            $stmt->bindParam(':feature_entity_id',$feature_entity_id, 2, 16);
        }
        if (array_key_exists('carbon_feature_group_references.group_entity_id', $argv)) {
            $group_entity_id = $argv['carbon_feature_group_references.group_entity_id'];
            $stmt->bindParam(':group_entity_id',$group_entity_id, 2, 16);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Feature_Group_References failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_feature_group_references.', '', $k); },
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
        $sql = 'DELETE FROM carbon_feature_group_references ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_feature_group_references', self::PDO_VALIDATION);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
