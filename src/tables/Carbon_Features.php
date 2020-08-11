<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace CarbonPHP\Tables;

use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

class Carbon_Features extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_features';
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
        'carbon_features.feature_entity_id' => ['binary', '2', '16'],'carbon_features.feature_code' => ['varchar', '2', '30'],'carbon_features.feature_creation_date' => ['datetime', '2', ''],
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

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Features.');
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
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.");
            }
        } 
        
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_features (feature_entity_id, feature_code) VALUES ( UNHEX(:feature_entity_id), :feature_code)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $feature_entity_id = $id = $argv['carbon_features.feature_entity_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':feature_entity_id',$feature_entity_id, 2, 16);
    
    
        if (!array_key_exists('carbon_features.feature_code', $argv)) {
            throw new PublicAlert('Required argument "carbon_features.feature_code" is missing from the request.');
        }
        $feature_code = $argv['carbon_features.feature_code'];
        $stmt->bindParam(':feature_code',$feature_code, 2, 30);
    


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

        $sql = 'UPDATE carbon_features ' . ' SET '; // intellij cant handle this otherwise

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

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_features.feature_entity_id', $argv)) {
            $feature_entity_id = $argv['carbon_features.feature_entity_id'];
            $stmt->bindParam(':feature_entity_id',$feature_entity_id, 2, 16);
        }
        if (array_key_exists('carbon_features.feature_code', $argv)) {
            $feature_code = $argv['carbon_features.feature_code'];
            $stmt->bindParam(':feature_code',$feature_code, 2, 30);
        }
        if (array_key_exists('carbon_features.feature_creation_date', $argv)) {
            $stmt->bindValue(':feature_creation_date',$argv['carbon_features.feature_creation_date'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Features failed to execute the update query.');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_features.', '', $k); },
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
        
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_features on c.entity_pk = carbon_features.feature_entity_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_features', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
}
