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


class Carbons extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbons';
    public const TABLE_NAME = 'carbons';
    public const TABLE_NAMESPACE = 'CarbonPHP\Tables';
    
    public const ENTITY_PK = 'carbons.entity_pk'; 
    public const ENTITY_FK = 'carbons.entity_fk'; 
    public const ENTITY_TAG = 'carbons.entity_tag'; 

    public const PRIMARY = [
        'carbons.entity_pk',
    ];

    public const COLUMNS = [
        'carbons.entity_pk' => 'entity_pk','carbons.entity_fk' => 'entity_fk','carbons.entity_tag' => 'entity_tag',
    ];

    public const PDO_VALIDATION = [
        'carbons.entity_pk' => ['binary', '2', '16'],'carbons.entity_fk' => ['binary', '2', '16'],'carbons.entity_tag' => ['varchar', '2', '100'],
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
 
    public const PHP_VALIDATION = [[self::DISALLOW_PUBLIC_ACCESS]]; 
 
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
        self::$tableNamespace = self::TABLE_NAMESPACE;
   
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbons.', 'danger');
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
        self::$tableNamespace = self::TABLE_NAMESPACE;
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbons (entity_pk, entity_fk, entity_tag) VALUES ( UNHEX(:entity_pk), UNHEX(:entity_fk), :entity_tag)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
        $entity_pk = $id = $argv['carbons.entity_pk'] ?? self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];
        $stmt->bindParam(':entity_pk',$entity_pk, 2, 16);
    
    
        $entity_fk =  $argv['carbons.entity_fk'] ?? null;
        $stmt->bindParam(':entity_fk',$entity_fk, 2, 16);
    
    
        if (!array_key_exists('carbons.entity_tag', $argv)) {
            throw new PublicAlert('Required argument "carbons.entity_tag" is missing from the request.', 'danger');
        }
        $entity_tag = $argv['carbons.entity_tag'];
        $stmt->bindParam(':entity_tag',$entity_tag, 2, 100);
    


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
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
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

        $sql = 'UPDATE carbons ' . ' SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbons.entity_pk', $argv)) {
            $set .= 'entity_pk=UNHEX(:entity_pk),';
        }
        if (array_key_exists('carbons.entity_fk', $argv)) {
            $set .= 'entity_fk=UNHEX(:entity_fk),';
        }
        if (array_key_exists('carbons.entity_tag', $argv)) {
            $set .= 'entity_tag=:entity_tag,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_pk=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbons.entity_pk', $argv)) {
            $entity_pk = $argv['carbons.entity_pk'];
            $stmt->bindParam(':entity_pk',$entity_pk, 2, 16);
        }
        if (array_key_exists('carbons.entity_fk', $argv)) {
            $entity_fk = $argv['carbons.entity_fk'];
            $stmt->bindParam(':entity_fk',$entity_fk, 2, 16);
        }
        if (array_key_exists('carbons.entity_tag', $argv)) {
            $entity_tag = $argv['carbons.entity_tag'];
            $stmt->bindParam(':entity_tag',$entity_tag, 2, 100);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbons failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbons.', '', $k); },
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
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
        $sql = 'DELETE FROM carbons ';

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
            
            $where = self::buildWhere($argv, $pdo, 'carbons', self::PDO_VALIDATION);
            
            if (empty($where)) {
                throw new PublicAlert('The where condition provided appears invalid.', 'danger');
            }

            $sql .= ' WHERE ' . $where;
        } else {
            $sql .= ' WHERE  entity_pk=UNHEX('.self::addInjection($primary, $pdo).')';
        }


        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
