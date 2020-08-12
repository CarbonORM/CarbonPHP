<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace CarbonPHP\Tables;

use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

class Carbon_Location_References extends Rest implements iRestfulReferences
{
    
    public const TABLE_NAME = 'carbon_location_references';
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
 
    public const PHP_VALIDATION = [

    ]; 
 
    public const REGEX_VALIDATION = [

    ]; 
    
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
    public static function Get(array &$return, array $argv): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Location_References.');
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
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.");
            }
        } 
        
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_location_references (entity_reference, location_reference) VALUES ( UNHEX(:entity_reference), UNHEX(:location_reference))';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        if (!array_key_exists('carbon_location_references.entity_reference', $argv)) {
            throw new PublicAlert('Required argument "carbon_location_references.entity_reference" is missing from the request.');
        }
        $entity_reference = $argv['carbon_location_references.entity_reference'];
        $stmt->bindParam(':entity_reference',$entity_reference, 2, 16);
    
    
        if (!array_key_exists('carbon_location_references.location_reference', $argv)) {
            throw new PublicAlert('Required argument "carbon_location_references.location_reference" is missing from the request.');
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
        $where = $argv[self::WHERE];

        $argv = $argv[self::UPDATE];

        if (empty($where) || empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated specific where conditions.');
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.');
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

        
        $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_location_references', self::PDO_VALIDATION);

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
            throw new PublicAlert('Restful table Carbon_Location_References failed to execute the update query.');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.');
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
    public static function Delete(array &$remove, array $argv) : bool
    {
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE FROM carbon_location_references ';

        $pdo = self::database();
        
               
        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_location_references', self::PDO_VALIDATION);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
}
