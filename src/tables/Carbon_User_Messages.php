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


class Carbon_User_Messages extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_user_messages';
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
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_user_messages (message_id, from_user_id, to_user_id, message, message_read) VALUES ( UNHEX(:message_id), UNHEX(:from_user_id), UNHEX(:to_user_id), :message, :message_read)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $message_id = $id = $argv['carbon_user_messages.message_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':message_id',$message_id, 2, 16);
    
    
        if (!array_key_exists('carbon_user_messages.from_user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_messages.from_user_id" is missing from the request.', 'danger');
        }
        $from_user_id = $argv['carbon_user_messages.from_user_id'];
        $stmt->bindParam(':from_user_id',$from_user_id, 2, 16);
    
    
        if (!array_key_exists('carbon_user_messages.to_user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_user_messages.to_user_id" is missing from the request.', 'danger');
        }
        $to_user_id = $argv['carbon_user_messages.to_user_id'];
        $stmt->bindParam(':to_user_id',$to_user_id, 2, 16);
    
        $stmt->bindValue(':message',$argv['carbon_user_messages.message'], 2);
    
    
        $message_read =  $argv['carbon_user_messages.message_read'] ?? '0';
        $stmt->bindParam(':message_read',$message_read, 0, 1);
    


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

        $sql = 'UPDATE carbon_user_messages ' . ' SET '; // intellij cant handle this otherwise

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

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_user_messages.message_id', $argv)) {
            $message_id = $argv['carbon_user_messages.message_id'];
            $stmt->bindParam(':message_id',$message_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.from_user_id', $argv)) {
            $from_user_id = $argv['carbon_user_messages.from_user_id'];
            $stmt->bindParam(':from_user_id',$from_user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.to_user_id', $argv)) {
            $to_user_id = $argv['carbon_user_messages.to_user_id'];
            $stmt->bindParam(':to_user_id',$to_user_id, 2, 16);
        }
        if (array_key_exists('carbon_user_messages.message', $argv)) {
            $stmt->bindValue(':message',$argv['carbon_user_messages.message'], 2);
        }
        if (array_key_exists('carbon_user_messages.message_read', $argv)) {
            $message_read = $argv['carbon_user_messages.message_read'];
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
            throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
        }
        
        /** @noinspection SqlResolve */
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_user_messages on c.entity_pk = carbon_user_messages.message_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_user_messages', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
     

    
}
