<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Rest;
use PDO;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


class History_Logs extends Rest implements iRestfulReferences
{
    
    public const CLASS_NAME = 'History_Logs';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'history_logs';
    public const TABLE_PREFIX = '';
    
    public const UUID = 'history_logs.uuid'; 
    public const RESOURCE_TYPE = 'history_logs.resource_type'; 
    public const RESOURCE_UUID = 'history_logs.resource_uuid'; 
    public const OPERATION_TYPE = 'history_logs.operation_type'; 
    public const DATA = 'history_logs.data'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'history_logs.uuid' => 'uuid','history_logs.resource_type' => 'resource_type','history_logs.resource_uuid' => 'resource_uuid','history_logs.operation_type' => 'operation_type','history_logs.data' => 'data',
    ];

    public const PDO_VALIDATION = [
        'history_logs.uuid' => ['binary', '2', '16'],'history_logs.resource_type' => ['varchar', '2', '40'],'history_logs.resource_uuid' => ['binary', '2', '16'],'history_logs.operation_type' => ['varchar', '2', '20'],'history_logs.data' => ['json', '2', ''],
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
        self::GET => [self::DISALLOW_PUBLIC_ACCESS],
        self::POST => [self::DISALLOW_PUBLIC_ACCESS],
        self::PUT => [self::DISALLOW_PUBLIC_ACCESS],
        self::DELETE => [self::DISALLOW_PUBLIC_ACCESS],
    ]; 
 
    public const REGEX_VALIDATION = []; 
   

    
    public static function createTableSQL() : string {
    return /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `history_logs` (
  `uuid` binary(16) NOT NULL,
  `resource_type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `resource_uuid` binary(16) DEFAULT NULL,
  `operation_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
    public static function Get(array &$return, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on History_Logs.', 'danger');
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        

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
    public static function Post(array $argv, string $dependantEntityId = null): bool
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO history_logs (uuid, resource_type, resource_uuid, operation_type, data) VALUES ( UNHEX(:uuid), :resource_type, UNHEX(:resource_uuid), :operation_type, :data)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

        
        
        
        
        if (!array_key_exists('history_logs.uuid', $argv)) {
            throw new PublicAlert('Required argument "history_logs.uuid" is missing from the request.', 'danger');
        }
        $uuid = $argv['history_logs.uuid'];
        $ref='history_logs.uuid';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $uuid)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.uuid\'.');
        }
        $stmt->bindParam(':uuid',$uuid, 2, 16);
        
        
        
        $resource_type = $argv['history_logs.resource_type'] ?? null;
        $ref='history_logs.resource_type';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $resource_type, $resource_type === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.resource_type\'.');
        }
        $stmt->bindParam(':resource_type',$resource_type, 2, 40);
        
        
        
        $resource_uuid = $argv['history_logs.resource_uuid'] ?? null;
        $ref='history_logs.resource_uuid';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $resource_uuid, $resource_uuid === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.resource_uuid\'.');
        }
        $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        
        
        
        $operation_type = $argv['history_logs.operation_type'] ?? null;
        $ref='history_logs.operation_type';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $operation_type, $operation_type === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.operation_type\'.');
        }
        $stmt->bindParam(':operation_type',$operation_type, 2, 20);
        
        
        
        if (!array_key_exists('history_logs.data', $argv)) {
            throw new PublicAlert('The column \'history_logs.data\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        }
        $ref='history_logs.data';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $data)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.data\'.');
        }
        if (!is_string($data = $argv['history_logs.data']) && false === $data = json_encode($data)) {
            throw new PublicAlert('The column \'history_logs.data\' failed to be json encoded.');
        }
        $stmt->bindValue(':data', $data, 2);
        
        


        
        if ($stmt->execute()) {
            self::prepostprocessRestRequest();
            
            self::postprocessRestRequest();
            
            self::completeRest();
            
            return true;  
        }
        
        self::completeRest();
         
        return false;
    
    }
    
    /**
    * @param array $return
    
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &$return,  array $argv) : bool
    {
        self::startRest(self::PUT, $argv);
        
        $where = $argv[self::WHERE];

        $argv = $argv[self::UPDATE];

        if (empty($where) || empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated with specific where and update attributes.', 'danger');
        }
        
        foreach ($argv as $key => &$value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $key, $op, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'history_logs.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE history_logs SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('history_logs.uuid', $argv)) {
            $set .= 'uuid=UNHEX(:uuid),';
        }
        if (array_key_exists('history_logs.resource_type', $argv)) {
            $set .= 'resource_type=:resource_type,';
        }
        if (array_key_exists('history_logs.resource_uuid', $argv)) {
            $set .= 'resource_uuid=UNHEX(:resource_uuid),';
        }
        if (array_key_exists('history_logs.operation_type', $argv)) {
            $set .= 'operation_type=:operation_type,';
        }
        if (array_key_exists('history_logs.data', $argv)) {
            $set .= 'data=:data,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        
        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::PUT, $where, $pdo);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('history_logs.uuid', $argv)) {
            $uuid = $argv['history_logs.uuid'];
            $ref = 'history_logs.uuid';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $uuid)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':uuid',$uuid, 2, 16);
        }
        if (array_key_exists('history_logs.resource_type', $argv)) {
            $resource_type = $argv['history_logs.resource_type'];
            $ref = 'history_logs.resource_type';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $resource_type)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':resource_type',$resource_type, 2, 40);
        }
        if (array_key_exists('history_logs.resource_uuid', $argv)) {
            $resource_uuid = $argv['history_logs.resource_uuid'];
            $ref = 'history_logs.resource_uuid';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $resource_uuid)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':resource_uuid',$resource_uuid, 2, 16);
        }
        if (array_key_exists('history_logs.operation_type', $argv)) {
            $operation_type = $argv['history_logs.operation_type'];
            $ref = 'history_logs.operation_type';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $operation_type)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':operation_type',$operation_type, 2, 20);
        }
        if (array_key_exists('history_logs.data', $argv)) {
            $stmt->bindValue(':data',json_encode($argv['history_logs.data']), 2);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table History_Logs failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('history_logs.', '', $k); },
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
    * @return bool
    */
    public static function Delete(array &$remove, array $argv = []) : bool
    {
        self::startRest(self::DELETE, $argv);
        
        /** @noinspection SqlWithoutWhere
         * @noinspection UnknownInspectionInspection - intellij is funny sometimes.
         */
        $sql = 'DELETE FROM history_logs ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);

        self::jsonSQLReporting(func_get_args(), $sql);

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
