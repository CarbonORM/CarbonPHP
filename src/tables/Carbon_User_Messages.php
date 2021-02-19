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


class Carbon_User_Messages extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_User_Messages';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_user_messages';
    public const TABLE_PREFIX = '';
    
    public const MESSAGE_ID = 'carbon_user_messages.message_id'; 
    public const FROM_USER_ID = 'carbon_user_messages.from_user_id'; 
    public const TO_USER_ID = 'carbon_user_messages.to_user_id'; 
    public const MESSAGE = 'carbon_user_messages.message'; 
    public const MESSAGE_READ = 'carbon_user_messages.message_read'; 
    public const CREATION_DATE = 'carbon_user_messages.creation_date'; 

    public const PRIMARY = [
        'carbon_user_messages.message_id',
    ];

    public const COLUMNS = [
        'carbon_user_messages.message_id' => 'message_id','carbon_user_messages.from_user_id' => 'from_user_id','carbon_user_messages.to_user_id' => 'to_user_id','carbon_user_messages.message' => 'message','carbon_user_messages.message_read' => 'message_read','carbon_user_messages.creation_date' => 'creation_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_user_messages.message_id' => ['binary', '2', '16'],'carbon_user_messages.from_user_id' => ['binary', '2', '16'],'carbon_user_messages.to_user_id' => ['binary', '2', '16'],'carbon_user_messages.message' => ['text', '2', ''],'carbon_user_messages.message_read' => ['tinyint', '0', '1'],'carbon_user_messages.creation_date' => ['datetime', '2', ''],
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
    CREATE TABLE `carbon_user_messages` (
  `message_id` binary(16) NOT NULL,
  `from_user_id` binary(16) NOT NULL,
  `to_user_id` binary(16) NOT NULL,
  `message` text NOT NULL,
  `message_read` tinyint(1) DEFAULT '0',
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `messages_entity_entity_pk_fk` (`message_id`),
  KEY `messages_entity_user_from_pk_fk` (`to_user_id`),
  KEY `carbon_user_messages_carbon_entity_pk_fk` (`from_user_id`),
  CONSTRAINT `carbon_user_messages_carbon_entity_pk_fk` FOREIGN KEY (`from_user_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_entity_entity_pk_fk` FOREIGN KEY (`message_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_entity_user_from_pk_fk` FOREIGN KEY (`to_user_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
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
            throw new PublicAlert('Failed to execute the query on Carbon_User_Messages.', 'danger');
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
        
        $sql = 'INSERT INTO carbon_user_messages (message_id, from_user_id, to_user_id, message, message_read) VALUES ( UNHEX(:message_id), UNHEX(:from_user_id), UNHEX(:to_user_id), :message, :message_read)';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);        
        $message_id = $id = $argv['carbon_user_messages.message_id'] ?? false;
        if ($id === false) {
             $message_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_user_messages.message_id';
           $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $message_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.message_id\'.');
           }            
        }
        $stmt->bindParam(':message_id',$message_id, 2, 16);
        
        
        
        
        
        
        
        if (!array_key_exists('carbon_user_messages.from_user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_messages.from_user_id" is missing from the request.', 'danger');
        }
        $from_user_id = $argv['carbon_user_messages.from_user_id'];
        $ref='carbon_user_messages.from_user_id';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $from_user_id)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.from_user_id\'.');
        }
        $stmt->bindParam(':from_user_id',$from_user_id, 2, 16);
        
        
        
        
        
        if (!array_key_exists('carbon_user_messages.to_user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_messages.to_user_id" is missing from the request.', 'danger');
        }
        $to_user_id = $argv['carbon_user_messages.to_user_id'];
        $ref='carbon_user_messages.to_user_id';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $to_user_id)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.to_user_id\'.');
        }
        $stmt->bindParam(':to_user_id',$to_user_id, 2, 16);
        
        
                
        
        if (!array_key_exists('carbon_user_messages.message', $argv)) {
            throw new PublicAlert('The column \'carbon_user_messages.message\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        } 
        $ref='carbon_user_messages.message';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $argv['message'])) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.message\'.');
        }
        $stmt->bindValue(':message', $argv['carbon_user_messages.message'], 2);
        

        
        
        
        
        $message_read = $argv['carbon_user_messages.message_read'] ?? '0';
        $ref='carbon_user_messages.message_read';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $message_read, $message_read === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.message_read\'.');
        }
        $stmt->bindParam(':message_read',$message_read, 0, 1);
        
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_user_messages');
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
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_messages.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_user_messages SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_user_messages.message_id', $argv)) {
            $set .= 'message_id=UNHEX(:message_id),';
        }
        if (array_key_exists('carbon_user_messages.from_user_id', $argv)) {
            $set .= 'from_user_id=UNHEX(:from_user_id),';
        }
        if (array_key_exists('carbon_user_messages.to_user_id', $argv)) {
            $set .= 'to_user_id=UNHEX(:to_user_id),';
        }
        if (array_key_exists('carbon_user_messages.message', $argv)) {
            $set .= 'message=:message,';
        }
        if (array_key_exists('carbon_user_messages.message_read', $argv)) {
            $set .= 'message_read=:message_read,';
        }
        if (array_key_exists('carbon_user_messages.creation_date', $argv)) {
            $set .= 'creation_date=:creation_date,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  message_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_user_messages.message_id', $argv)) {
            $message_id = $argv['carbon_user_messages.message_id'];
            $ref = 'carbon_user_messages.message_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $message_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':message_id',$message_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.from_user_id', $argv)) {
            $from_user_id = $argv['carbon_user_messages.from_user_id'];
            $ref = 'carbon_user_messages.from_user_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $from_user_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':from_user_id',$from_user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.to_user_id', $argv)) {
            $to_user_id = $argv['carbon_user_messages.to_user_id'];
            $ref = 'carbon_user_messages.to_user_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $to_user_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':to_user_id',$to_user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.message', $argv)) {
            $stmt->bindValue(':message',$argv['carbon_user_messages.message'], 2);
        }
        if (array_key_exists('carbon_user_messages.message_read', $argv)) {
            $message_read = $argv['carbon_user_messages.message_read'];
            $ref = 'carbon_user_messages.message_read';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $message_read)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':message_read',$message_read, 0, 1);
        }
        if (array_key_exists('carbon_user_messages.creation_date', $argv)) {
            $stmt->bindValue(':creation_date',$argv['carbon_user_messages.creation_date'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_User_Messages failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_user_messages.', '', $k); },
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
                JOIN carbon_user_messages on c.entity_pk = carbon_user_messages.message_id';

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
