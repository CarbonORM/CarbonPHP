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


class Carbon_Locations extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_locations';
    public const ENTITY_ID = 'carbon_locations.entity_id'; 
    public const LATITUDE = 'carbon_locations.latitude'; 
    public const LONGITUDE = 'carbon_locations.longitude'; 
    public const STREET = 'carbon_locations.street'; 
    public const CITY = 'carbon_locations.city'; 
    public const STATE = 'carbon_locations.state'; 
    public const ELEVATION = 'carbon_locations.elevation'; 
    public const ZIP = 'carbon_locations.zip'; 

    public const PRIMARY = [
        'carbon_locations.entity_id',
    ];

    public const COLUMNS = [
        'carbon_locations.entity_id' => 'entity_id','carbon_locations.latitude' => 'latitude','carbon_locations.longitude' => 'longitude','carbon_locations.street' => 'street','carbon_locations.city' => 'city','carbon_locations.state' => 'state','carbon_locations.elevation' => 'elevation','carbon_locations.zip' => 'zip',
    ];

    public const PDO_VALIDATION = [
        'carbon_locations.entity_id' => ['binary', '2', '16'],'carbon_locations.latitude' => ['varchar', '2', '225'],'carbon_locations.longitude' => ['varchar', '2', '225'],'carbon_locations.street' => ['varchar', '2', '225'],'carbon_locations.city' => ['varchar', '2', '40'],'carbon_locations.state' => ['varchar', '2', '10'],'carbon_locations.elevation' => ['varchar', '2', '40'],'carbon_locations.zip' => ['int', '2', ''],
    ];
 
    public const PHP_VALIDATION = [ self::DISALLOW_PUBLIC_ACCESS ]; 
 
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
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Locations.', 'danger');
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
        $sql = 'INSERT INTO carbon_locations (entity_id, latitude, longitude, street, city, state, elevation, zip) VALUES ( UNHEX(:entity_id), :latitude, :longitude, :street, :city, :state, :elevation, :zip)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $entity_id = $id = $argv['carbon_locations.entity_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':entity_id',$entity_id, 2, 16);
    
    
        $latitude =  $argv['carbon_locations.latitude'] ?? null;
        $stmt->bindParam(':latitude',$latitude, 2, 225);
    
    
        $longitude =  $argv['carbon_locations.longitude'] ?? null;
        $stmt->bindParam(':longitude',$longitude, 2, 225);
    
    
        $street =  $argv['carbon_locations.street'] ?? null;
        $stmt->bindParam(':street',$street, 2, 225);
    
    
        $city =  $argv['carbon_locations.city'] ?? null;
        $stmt->bindParam(':city',$city, 2, 40);
    
    
        $state =  $argv['carbon_locations.state'] ?? null;
        $stmt->bindParam(':state',$state, 2, 10);
    
    
        $elevation =  $argv['carbon_locations.elevation'] ?? null;
        $stmt->bindParam(':elevation',$elevation, 2, 40);
    
        $stmt->bindValue(':zip',array_key_exists('carbon_locations.zip',$argv) ? $argv['carbon_locations.zip'] : null, 2);
    


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

        $sql = 'UPDATE carbon_locations ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_locations.entity_id', $argv)) {
            $set .= 'entity_id=UNHEX(:entity_id),';
        }
        if (array_key_exists('carbon_locations.latitude', $argv)) {
            $set .= 'latitude=:latitude,';
        }
        if (array_key_exists('carbon_locations.longitude', $argv)) {
            $set .= 'longitude=:longitude,';
        }
        if (array_key_exists('carbon_locations.street', $argv)) {
            $set .= 'street=:street,';
        }
        if (array_key_exists('carbon_locations.city', $argv)) {
            $set .= 'city=:city,';
        }
        if (array_key_exists('carbon_locations.state', $argv)) {
            $set .= 'state=:state,';
        }
        if (array_key_exists('carbon_locations.elevation', $argv)) {
            $set .= 'elevation=:elevation,';
        }
        if (array_key_exists('carbon_locations.zip', $argv)) {
            $set .= 'zip=:zip,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_locations.entity_id', $argv)) {
            $entity_id = $argv['carbon_locations.entity_id'];
            $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        }
        if (array_key_exists('carbon_locations.latitude', $argv)) {
            $latitude = $argv['carbon_locations.latitude'];
            $stmt->bindParam(':latitude',$latitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.longitude', $argv)) {
            $longitude = $argv['carbon_locations.longitude'];
            $stmt->bindParam(':longitude',$longitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.street', $argv)) {
            $street = $argv['carbon_locations.street'];
            $stmt->bindParam(':street',$street, 2, 225);
        }
        if (array_key_exists('carbon_locations.city', $argv)) {
            $city = $argv['carbon_locations.city'];
            $stmt->bindParam(':city',$city, 2, 40);
        }
        if (array_key_exists('carbon_locations.state', $argv)) {
            $state = $argv['carbon_locations.state'];
            $stmt->bindParam(':state',$state, 2, 10);
        }
        if (array_key_exists('carbon_locations.elevation', $argv)) {
            $elevation = $argv['carbon_locations.elevation'];
            $stmt->bindParam(':elevation',$elevation, 2, 40);
        }
        if (array_key_exists('carbon_locations.zip', $argv)) {
            $stmt->bindValue(':zip',$argv['carbon_locations.zip'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Locations failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_locations.', '', $k); },
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
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
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
                JOIN carbon_locations on c.entity_pk = carbon_locations.entity_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_locations', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
     

    
}
