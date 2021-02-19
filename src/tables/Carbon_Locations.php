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


class Carbon_Locations extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Locations';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_locations';
    public const TABLE_PREFIX = '';
    
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
    return /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbon_locations` (
  `entity_id` binary(16) NOT NULL,
  `latitude` varchar(225) DEFAULT NULL,
  `longitude` varchar(225) DEFAULT NULL,
  `street` varchar(225) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `elevation` varchar(40) DEFAULT NULL,
  `zip` int DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `entity_location_entity_id_uindex` (`entity_id`),
  CONSTRAINT `entity_location_entity_entity_pk_fk` FOREIGN KEY (`entity_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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

        self::postprocessRestRequest($return);
        self::completeRest();
        return true;
    }

    /**
     * @param array $argv
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     * @throws PDOException
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_locations (entity_id, latitude, longitude, street, city, state, elevation, zip) VALUES ( UNHEX(:entity_id), :latitude, :longitude, :street, :city, :state, :elevation, :zip)';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);        
        $entity_id = $id = $argv['carbon_locations.entity_id'] ?? false;
        if ($id === false) {
             $entity_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_locations.entity_id';
           $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $entity_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.entity_id\'.');
           }            
        }
        $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        
        
        
        
        
        
        $latitude = $argv['carbon_locations.latitude'] ?? null;
        $ref='carbon_locations.latitude';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $latitude, $latitude === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.latitude\'.');
        }
        $stmt->bindParam(':latitude',$latitude, 2, 225);
        
        
        
        
        $longitude = $argv['carbon_locations.longitude'] ?? null;
        $ref='carbon_locations.longitude';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $longitude, $longitude === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.longitude\'.');
        }
        $stmt->bindParam(':longitude',$longitude, 2, 225);
        
        
        
        
        $street = $argv['carbon_locations.street'] ?? null;
        $ref='carbon_locations.street';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $street, $street === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.street\'.');
        }
        $stmt->bindParam(':street',$street, 2, 225);
        
        
        
        
        $city = $argv['carbon_locations.city'] ?? null;
        $ref='carbon_locations.city';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $city, $city === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.city\'.');
        }
        $stmt->bindParam(':city',$city, 2, 40);
        
        
        
        
        $state = $argv['carbon_locations.state'] ?? null;
        $ref='carbon_locations.state';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $state, $state === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.state\'.');
        }
        $stmt->bindParam(':state',$state, 2, 10);
        
        
        
        
        $elevation = $argv['carbon_locations.elevation'] ?? null;
        $ref='carbon_locations.elevation';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $elevation, $elevation === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.elevation\'.');
        }
        $stmt->bindParam(':elevation',$elevation, 2, 40);
        
        
                        
        $zip = $argv['carbon_locations.zip'] ?? null;
        $ref='carbon_locations.zip';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $zip, $zip === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.zip\'.');
        }
        $stmt->bindValue(':zip', $zip, 2);
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_locations');
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
    * @throws PDOException
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
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_locations.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_locations SET '; // intellij cant handle this otherwise

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

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_locations.entity_id', $argv)) {
            $entity_id = $argv['carbon_locations.entity_id'];
            $ref = 'carbon_locations.entity_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $entity_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        }
        if (array_key_exists('carbon_locations.latitude', $argv)) {
            $latitude = $argv['carbon_locations.latitude'];
            $ref = 'carbon_locations.latitude';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $latitude)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':latitude',$latitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.longitude', $argv)) {
            $longitude = $argv['carbon_locations.longitude'];
            $ref = 'carbon_locations.longitude';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $longitude)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':longitude',$longitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.street', $argv)) {
            $street = $argv['carbon_locations.street'];
            $ref = 'carbon_locations.street';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $street)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':street',$street, 2, 225);
        }
        if (array_key_exists('carbon_locations.city', $argv)) {
            $city = $argv['carbon_locations.city'];
            $ref = 'carbon_locations.city';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $city)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':city',$city, 2, 40);
        }
        if (array_key_exists('carbon_locations.state', $argv)) {
            $state = $argv['carbon_locations.state'];
            $ref = 'carbon_locations.state';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $state)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':state',$state, 2, 10);
        }
        if (array_key_exists('carbon_locations.elevation', $argv)) {
            $elevation = $argv['carbon_locations.elevation'];
            $ref = 'carbon_locations.elevation';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $elevation)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
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
    * @throws PDOException
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
                JOIN carbon_locations on c.entity_pk = carbon_locations.entity_id';

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
