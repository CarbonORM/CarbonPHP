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


class Carbon_Photos extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Photos';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_photos';
    public const TABLE_PREFIX = '';
    
    public const PARENT_ID = 'carbon_photos.parent_id'; 
    public const PHOTO_ID = 'carbon_photos.photo_id'; 
    public const USER_ID = 'carbon_photos.user_id'; 
    public const PHOTO_PATH = 'carbon_photos.photo_path'; 
    public const PHOTO_DESCRIPTION = 'carbon_photos.photo_description'; 

    public const PRIMARY = [
        'carbon_photos.parent_id',
    ];

    public const COLUMNS = [
        'carbon_photos.parent_id' => 'parent_id','carbon_photos.photo_id' => 'photo_id','carbon_photos.user_id' => 'user_id','carbon_photos.photo_path' => 'photo_path','carbon_photos.photo_description' => 'photo_description',
    ];

    public const PDO_VALIDATION = [
        'carbon_photos.parent_id' => ['binary', '2', '16'],'carbon_photos.photo_id' => ['binary', '2', '16'],'carbon_photos.user_id' => ['binary', '2', '16'],'carbon_photos.photo_path' => ['varchar', '2', '225'],'carbon_photos.photo_description' => ['text,', '2', ''],
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
    CREATE TABLE `carbon_photos` (
  `parent_id` binary(16) NOT NULL,
  `photo_id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `photo_path` varchar(225) NOT NULL,
  `photo_description` text,
  PRIMARY KEY (`parent_id`),
  UNIQUE KEY `entity_photos_photo_id_uindex` (`photo_id`),
  KEY `photos_entity_user_pk_fk` (`user_id`),
  CONSTRAINT `entity_photos_entity_entity_pk_fk` FOREIGN KEY (`photo_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_entity_pk_fk` FOREIGN KEY (`parent_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `photos_entity_user_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
    * @throws PublicAlert
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Photos.', 'danger');
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
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_photos (parent_id, photo_id, user_id, photo_path, photo_description) VALUES ( UNHEX(:parent_id), UNHEX(:photo_id), UNHEX(:user_id), :photo_path, :photo_description)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

                
        $parent_id = $id = $argv['carbon_photos.parent_id'] ?? false;
        if ($id === false) {
             $parent_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_photos.parent_id';
           if (!self::validateInternalColumn(self::POST, $ref, $parent_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.parent_id\'.');
           }            
        }
        $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        
        
        
        
        
        if (!array_key_exists('carbon_photos.photo_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.photo_id" is missing from the request.', 'danger');
        }
        $photo_id = $argv['carbon_photos.photo_id'];
        $ref='carbon_photos.photo_id';
        if (!self::validateInternalColumn(self::POST, $ref, $photo_id)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.photo_id\'.');
        }        
        $stmt->bindParam(':photo_id',$photo_id, 2, 16);
        
        
        
        
        if (!array_key_exists('carbon_photos.user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.user_id" is missing from the request.', 'danger');
        }
        $user_id = $argv['carbon_photos.user_id'];
        $ref='carbon_photos.user_id';
        if (!self::validateInternalColumn(self::POST, $ref, $user_id)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.user_id\'.');
        }        
        $stmt->bindParam(':user_id',$user_id, 2, 16);
        
        
        
        
        if (!array_key_exists('carbon_photos.photo_path', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.photo_path" is missing from the request.', 'danger');
        }
        $photo_path = $argv['carbon_photos.photo_path'];
        $ref='carbon_photos.photo_path';
        if (!self::validateInternalColumn(self::POST, $ref, $photo_path)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.photo_path\'.');
        }        
        $stmt->bindParam(':photo_path',$photo_path, 2, 225);
        
                
        
        if (!array_key_exists('carbon_photos.photo_description', $argv)) {
            throw new PublicAlert('The column \'carbon_photos.photo_description\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        } 
        $ref='carbon_photos.photo_description';
        if (!self::validateInternalColumn(self::POST, $ref, $photo_description)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.photo_description\'.');
        }
        $stmt->bindValue(':photo_description', $argv['carbon_photos.photo_description'], 2);

        

        if ($stmt->execute()) {
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
            if (!self::validateInternalColumn(self::PUT, $key, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_photos.\'.');
            }
        }

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_photos SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_photos.parent_id', $argv)) {
            $set .= 'parent_id=UNHEX(:parent_id),';
        }
        if (array_key_exists('carbon_photos.photo_id', $argv)) {
            $set .= 'photo_id=UNHEX(:photo_id),';
        }
        if (array_key_exists('carbon_photos.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('carbon_photos.photo_path', $argv)) {
            $set .= 'photo_path=:photo_path,';
        }
        if (array_key_exists('carbon_photos.photo_description', $argv)) {
            $set .= 'photo_description=:photo_description,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  parent_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_photos.parent_id', $argv)) {
            $parent_id = $argv['carbon_photos.parent_id'];
            $ref = 'carbon_photos.parent_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $parent_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.photo_id', $argv)) {
            $photo_id = $argv['carbon_photos.photo_id'];
            $ref = 'carbon_photos.photo_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $photo_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':photo_id',$photo_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.user_id', $argv)) {
            $user_id = $argv['carbon_photos.user_id'];
            $ref = 'carbon_photos.user_id';
            if (!self::validateInternalColumn(self::PUT, $ref, $user_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.photo_path', $argv)) {
            $photo_path = $argv['carbon_photos.photo_path'];
            $ref = 'carbon_photos.photo_path';
            if (!self::validateInternalColumn(self::PUT, $ref, $photo_path)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':photo_path',$photo_path, 2, 225);
        }
        if (array_key_exists('carbon_photos.photo_description', $argv)) {
            $stmt->bindValue(':photo_description',$argv['carbon_photos.photo_description'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Photos failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_photos.', '', $k); },
                array_keys($argv)
            ),
            array_values($argv)
        );

        $return = array_merge($return, $argv);

        self::postprocessRestRequest($return);
        self::completeRest();
        return true;

    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert
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
                JOIN carbon_photos on c.entity_pk = carbon_photos.parent_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
     

    
}
