<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Rest;
use PDO;
use PDOException;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


/**
 * 
 * Class Carbon_Reports
 * @package CarbonPHP\Tables
 * 
 */
class Carbon_Reports extends Rest implements iRestfulReferences
{
    
    public const CLASS_NAME = 'Carbon_Reports';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_reports';
    public const TABLE_PREFIX = '';
    
    public const LOG_LEVEL = 'carbon_reports.log_level'; 
    public const REPORT = 'carbon_reports.report'; 
    public const DATE = 'carbon_reports.date'; 
    public const CALL_TRACE = 'carbon_reports.call_trace'; 

    public const PRIMARY = [
        
    ];

    public const COLUMNS = [
        'carbon_reports.log_level' => 'log_level','carbon_reports.report' => 'report','carbon_reports.date' => 'date','carbon_reports.call_trace' => 'call_trace',
    ];

    public const PDO_VALIDATION = [
        'carbon_reports.log_level' => ['varchar', 'PDO::PARAM_STR', '20'],'carbon_reports.report' => ['text,', 'PDO::PARAM_STR', ''],'carbon_reports.date' => ['datetime', 'PDO::PARAM_STR', ''],'carbon_reports.call_trace' => ['text', 'PDO::PARAM_STR', ''],
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
        self::PREPROCESS => [self::DISALLOW_PUBLIC_ACCESS],
        self::GET => [self::DISALLOW_PUBLIC_ACCESS],
        self::POST => [self::DISALLOW_PUBLIC_ACCESS],
        self::PUT => [self::DISALLOW_PUBLIC_ACCESS],
        self::DELETE => [self::DISALLOW_PUBLIC_ACCESS],
    ]; 
 
    public const REGEX_VALIDATION = []; 
 
    public const REFRESH_SCHEMA = [
        [self::class => 'tableExistsOrExecuteSQL', self::TABLE_NAME, self::REMOVE_MYSQL_FOREIGN_KEY_CHECKS .
                        PHP_EOL . self::CREATE_TABLE_SQL . PHP_EOL . self::REVERT_MYSQL_FOREIGN_KEY_CHECKS]
    ]; 
   
    public const CREATE_TABLE_SQL = /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbon_reports` (
  `log_level` varchar(20) DEFAULT NULL,
  `report` text,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `call_trace` text NOT NULL
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
MYSQL;
   
   

    
    /**
     * @deprecated Use the class constant CREATE_TABLE_SQL directly
     * @return string
     */
    public static function createTableSQL() : string {
        return self::CREATE_TABLE_SQL;
    }
    
    /**
    * Currently nested aggregation is not supported. It is recommended to avoid using 'AS' where possible. Sub-selects are 
    * allowed and do support 'as' aggregation. Refer to the static subSelect method parameters in the parent `Rest` class.
    * All supported aggregation is listed in the example below. Note while the WHERE and JOIN members are syntactically 
    * similar, and are moreover compiled through the same method, our aggregation is not. Please refer to this example 
    * when building your queries. By design, queries using subSelect are only allowed internally. Public Sub-Selects may 
    * be given an optional argument with future releases but will never default to on. Thus, you external API validation
    * need only validate for possible table joins. In many cases sub-selects can be replaces using simple joins, this is
    * highly recommended.
    *
    *   $argv = [
    *       Rest::SELECT => [
    *              'table_name.column_name',
    *              self::EXAMPLE_COLUMN_ONE,
    *              [self::EXAMPLE_COLUMN_TWO, self::AS, 'customName'],
    *              [self::GROUP_CONCAT, self::EXAMPLE_COLUMN_THREE], 
    *              [self::MAX, self::EXAMPLE_COLUMN_FOUR], 
    *              [self::MIN, self::EXAMPLE_COLUMN_FIVE], 
    *              [self::SUM, self::EXAMPLE_COLUMN_SIX], 
    *              [self::DISTINCT, self::EXAMPLE_COLUMN_SEVEN], 
    *              ANOTHER_EXAMPLE_TABLE::subSelect($primary, $argv, $as, $pdo, $database)
    *       ],
    *       Rest::WHERE => [
    *              
    *              self::EXAMPLE_COLUMN_NINE => 'Value To Constrain',                       // self::EXAMPLE_COLUMN_NINE AND           
    *              'Defaults to boolean AND grouping' => 'Nesting array switches to OR',    // ''='' AND 
    *              [
    *                  'Column Name' => 'Value To Constrain',                                  // ''='' OR
    *                  'This array is OR'ed together' => 'Another sud array would `AND`'       // ''=''
    *                  [ etc... ]
    *              ],
    *              'last' => 'whereExample'                                                  // AND '' = ''
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
    *                    [ 'Column Name', Rest::LESS_THAN, 'Another Column Name']           // NOTE the Rest::LESS_THAN
    *                ]
    *            ],
    *            Rest::LEFT_OUTER => [
    *                Example_Table::CLASS_NAME => [
    *                    Location::USER_ID => Users::ID,
    *                    Location_References::ENTITY_KEY => $custom_var,
    *                   
    *                ],
    *                Example_Table_Two::CLASS_NAME => [
    *                    Example_Table_Two::ID => Example_Table_Two::subSelect($primary, $argv, $as, $pdo, $database)
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
    *               Rest::ORDER => [self::EXAMPLE_COLUMN_TEN => Rest::ASC ],  // i.e.  'username' => Rest::DESC
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
    public static function Get(array &$return, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        self::postpreprocessRestRequest($sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Reports.', 'danger');
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        
        if (isset($argv[self::PAGINATION][self::LIMIT]) && $argv[self::PAGINATION][self::LIMIT] === 1 && count($return) === 1) {
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
     * @throws PublicAlert|PDOException
     */
    public static function Post(array $argv, string $dependantEntityId = null): bool
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_reports (log_level, report, call_trace) VALUES ( :log_level, :report, :call_trace)';

        $pdo = self::database();
        
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);
        
        
        
        $log_level = $argv['carbon_reports.log_level'] ?? null;
        $ref='carbon_reports.log_level';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $log_level, $log_level === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_reports.log_level\'.');
        }
        $stmt->bindParam(':log_level',$log_level, PDO::PARAM_STR, 20);
        
        
                
        
        if (!array_key_exists('carbon_reports.report', $argv)) {
            throw new PublicAlert('The column \'carbon_reports.report\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        } 
        $ref='carbon_reports.report';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $argv['report'])) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_reports.report\'.');
        }
        $stmt->bindValue(':report', $argv['carbon_reports.report'], PDO::PARAM_STR);
        

        
        
        
        
                
        
        if (!array_key_exists('carbon_reports.call_trace', $argv)) {
            throw new PublicAlert('The column \'carbon_reports.call_trace\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        } 
        $ref='carbon_reports.call_trace';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $argv['call_trace'])) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_reports.call_trace\'.');
        }
        $stmt->bindValue(':call_trace', $argv['carbon_reports.call_trace'], PDO::PARAM_STR);
        

        


        if ($stmt->execute()) {
            self::prepostprocessRestRequest();
            
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_reports');
            }
            
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
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Put(array &$return,  array $argv) : bool
    {
        self::startRest(self::PUT, $argv);
        
        $where = $argv[self::WHERE] ?? [];
        
        if (empty($where)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated using conditions given to $argv[self::WHERE] and values given to $argv[self::UPDATE]. No WHERE attribute given.', 'danger');
        }

        $argv = $argv[self::UPDATE] ?? [];
        
        if (empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated using conditions given to $argv[self::WHERE] and values given to $argv[self::UPDATE]. No UPDATE attribute given.', 'danger');
        }

        if (empty($where) || empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated with specific where and update attributes.', 'danger');
        }
        
        foreach ($argv as $key => &$value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $key, $op, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_reports.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_reports SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_reports.log_level', $argv)) {
            $set .= 'log_level=:log_level,';
        }
        if (array_key_exists('carbon_reports.report', $argv)) {
            $set .= 'report=:report,';
        }
        if (array_key_exists('carbon_reports.date', $argv)) {
            $set .= 'date=:date,';
        }
        if (array_key_exists('carbon_reports.call_trace', $argv)) {
            $set .= 'call_trace=:call_trace,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        
        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::PUT, $where, $pdo);

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_reports.log_level', $argv)) {
            $log_level = $argv['carbon_reports.log_level'];
            $ref = 'carbon_reports.log_level';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $log_level)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':log_level',$log_level, PDO::PARAM_STR, 20);
        }
        if (array_key_exists('carbon_reports.report', $argv)) {
            $stmt->bindValue(':report',$argv['carbon_reports.report'], PDO::PARAM_STR);
        }
        if (array_key_exists('carbon_reports.date', $argv)) {
            $stmt->bindValue(':date',$argv['carbon_reports.date'], PDO::PARAM_STR);
        }
        if (array_key_exists('carbon_reports.call_trace', $argv)) {
            $stmt->bindValue(':call_trace',$argv['carbon_reports.call_trace'], PDO::PARAM_STR);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Reports failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_reports.', '', $k); },
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
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Delete(array &$remove, array $argv = []) : bool
    {
        self::startRest(self::DELETE, $argv);
        
        /** @noinspection SqlWithoutWhere
         * @noinspection UnknownInspectionInspection - intellij is funny sometimes.
         */
        $sql = 'DELETE FROM carbon_reports ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        if ($r) {
            $remove = [];
        }
        
        self::prepostprocessRestRequest($remove);
        
        self::postprocessRestRequest($remove);
        
        self::completeRest();
        
        return $r;
    }
     

    
}
