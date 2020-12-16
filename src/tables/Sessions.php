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


class Sessions extends Rest implements iRest
{
    
    public const TABLE_NAME = 'sessions';
    public const USER_ID = 'sessions.user_id'; 
    public const USER_IP = 'sessions.user_ip'; 
    public const SESSION_ID = 'sessions.session_id'; 
    public const SESSION_EXPIRES = 'sessions.session_expires'; 
    public const SESSION_DATA = 'sessions.session_data'; 
    public const USER_ONLINE_STATUS = 'sessions.user_online_status'; 

    public const PRIMARY = [
        'sessions.session_id',
    ];

    public const COLUMNS = [
        'sessions.user_id' => 'user_id','sessions.user_ip' => 'user_ip','sessions.session_id' => 'session_id','sessions.session_expires' => 'session_expires','sessions.session_data' => 'session_data','sessions.user_online_status' => 'user_online_status',
    ];

    public const PDO_VALIDATION = [
        'sessions.user_id' => ['binary', '2', '16'],'sessions.user_ip' => ['varchar', '2', '20'],'sessions.session_id' => ['varchar', '2', '255'],'sessions.session_expires' => ['datetime', '2', ''],'sessions.session_data' => ['text,', '2', ''],'sessions.user_online_status' => ['tinyint', '0', '1'],
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
 
    public const PHP_VALIDATION = [
        [self::DISALLOW_PUBLIC_ACCESS],
        self::GET => [ self::DISALLOW_PUBLIC_ACCESS ],
        self::POST => [ self::DISALLOW_PUBLIC_ACCESS ],
        self::PUT => [ self::DISALLOW_PUBLIC_ACCESS ],
        self::DELETE => [ self::DISALLOW_PUBLIC_ACCESS ],
    ]; 
 
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
            throw new PublicAlert('Failed to execute the query on Sessions.', 'danger');
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
        
        $sql = 'INSERT INTO sessions (user_id, user_ip, session_id, session_expires, session_data, user_online_status) VALUES ( UNHEX(:user_id), :user_ip, :session_id, :session_expires, :session_data, :user_online_status)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        if (!array_key_exists('sessions.user_id', $argv)) {
            throw new PublicAlert('Required argument "sessions.user_id" is missing from the request.', 'danger');
        }
        $user_id = $argv['sessions.user_id'];
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
    
        $user_ip =  $argv['sessions.user_ip'] ?? null;
        $stmt->bindParam(':user_ip',$user_ip, 2, 20);
    
    
        if (!array_key_exists('sessions.session_id', $argv)) {
            throw new PublicAlert('Required argument "sessions.session_id" is missing from the request.', 'danger');
        }
        $session_id = $argv['sessions.session_id'];
        $stmt->bindParam(':session_id',$session_id, 2, 255);
    
        $stmt->bindValue(':session_expires',$argv['sessions.session_expires'], 2);
    
        $stmt->bindValue(':session_data',$argv['sessions.session_data'], 2);
    
    
        $user_online_status =  $argv['sessions.user_online_status'] ?? '1';
        $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
    



    
        return $stmt->execute();
    
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

        $sql = 'UPDATE sessions ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('sessions.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('sessions.user_ip', $argv)) {
            $set .= 'user_ip=:user_ip,';
        }
        if (array_key_exists('sessions.session_id', $argv)) {
            $set .= 'session_id=:session_id,';
        }
        if (array_key_exists('sessions.session_expires', $argv)) {
            $set .= 'session_expires=:session_expires,';
        }
        if (array_key_exists('sessions.session_data', $argv)) {
            $set .= 'session_data=:session_data,';
        }
        if (array_key_exists('sessions.user_online_status', $argv)) {
            $set .= 'user_online_status=:user_online_status,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('sessions.user_id', $argv)) {
            $user_id = $argv['sessions.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('sessions.user_ip', $argv)) {
            $user_ip = $argv['sessions.user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 20);
        }
        if (array_key_exists('sessions.session_id', $argv)) {
            $session_id = $argv['sessions.session_id'];
            $stmt->bindParam(':session_id',$session_id, 2, 255);
        }
        if (array_key_exists('sessions.session_expires', $argv)) {
            $stmt->bindValue(':session_expires',$argv['sessions.session_expires'], 2);
        }
        if (array_key_exists('sessions.session_data', $argv)) {
            $stmt->bindValue(':session_data',$argv['sessions.session_data'], 2);
        }
        if (array_key_exists('sessions.user_online_status', $argv)) {
            $user_online_status = $argv['sessions.user_online_status'];
            $stmt->bindParam(':user_online_status',$user_online_status, 0, 1);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Sessions failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('sessions.', '', $k); },
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
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
    {
        $sql = 'DELETE FROM sessions ';

        $pdo = self::database();
        
        if (null === $primary) {
           /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
            }
            
            $where = self::buildWhere($argv, $pdo, 'sessions', self::PDO_VALIDATION);
            
            if (empty($where)) {
                throw new PublicAlert('The where condition provided appears invalid.', 'danger');
            }

            $sql .= ' WHERE ' . $where;
        } else {
            $sql .= ' WHERE  session_id='.self::addInjection($primary, $pdo).'';
        }


        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
