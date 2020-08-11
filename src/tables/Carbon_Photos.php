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

class Carbon_Photos extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_photos';
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
            throw new PublicAlert('Failed to execute the query on Carbon_Photos.');
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
        $sql = 'INSERT INTO carbon_photos (parent_id, photo_id, user_id, photo_path, photo_description) VALUES ( UNHEX(:parent_id), UNHEX(:photo_id), UNHEX(:user_id), :photo_path, :photo_description)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $parent_id = $id = $argv['carbon_photos.parent_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':parent_id',$parent_id, 2, 16);
    
    
        if (!array_key_exists('carbon_photos.photo_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.photo_id" is missing from the request.');
        }
        $photo_id = $argv['carbon_photos.photo_id'];
        $stmt->bindParam(':photo_id',$photo_id, 2, 16);
    
    
        if (!array_key_exists('carbon_photos.user_id', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.user_id" is missing from the request.');
        }
        $user_id = $argv['carbon_photos.user_id'];
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
    
        if (!array_key_exists('carbon_photos.photo_path', $argv)) {
            throw new PublicAlert('Required argument "carbon_photos.photo_path" is missing from the request.');
        }
        $photo_path = $argv['carbon_photos.photo_path'];
        $stmt->bindParam(':photo_path',$photo_path, 2, 225);
    
        $stmt->bindValue(':photo_description',$argv['carbon_photos.photo_description'], 2);
    


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

        $sql = 'UPDATE carbon_photos ' . ' SET '; // intellij cant handle this otherwise

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
            $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.photo_id', $argv)) {
            $photo_id = $argv['carbon_photos.photo_id'];
            $stmt->bindParam(':photo_id',$photo_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.user_id', $argv)) {
            $user_id = $argv['carbon_photos.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_photos.photo_path', $argv)) {
            $photo_path = $argv['carbon_photos.photo_path'];
            $stmt->bindParam(':photo_path',$photo_path, 2, 225);
        }
        if (array_key_exists('carbon_photos.photo_description', $argv)) {
            $stmt->bindValue(':photo_description',$argv['carbon_photos.photo_description'], 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Photos failed to execute the update query.');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_photos.', '', $k); },
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
                JOIN carbon_photos on c.entity_pk = carbon_photos.parent_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_photos', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
}
