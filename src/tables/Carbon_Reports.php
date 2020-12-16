<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports


class Carbon_Reports extends Rest implements iRestfulReferences
{
    
    public const TABLE_NAME = 'carbon_reports';
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
        'carbon_reports.log_level' => ['varchar', '2', '20'],'carbon_reports.report' => ['text,', '2', ''],'carbon_reports.date' => ['datetime', '2', ''],'carbon_reports.call_trace' => ['text', '2', ''],
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
    public static function Get(array &$return, array $argv = []): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery(null, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
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
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_reports (log_level, report, call_trace) VALUES ( :log_level, :report, :call_trace)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        $log_level =  $argv['carbon_reports.log_level'] ?? null;
        $stmt->bindParam(':log_level',$log_level, 2, 20);
    
        $stmt->bindValue(':report',$argv['carbon_reports.report'], 2);
    
        $stmt->bindValue(':call_trace',$argv['carbon_reports.call_trace'], 2);
    



    
        return $stmt->execute();
    
    }
    
    /**
    * @param array $return
    
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &$return,  array $argv) : bool
    {
        $where = $argv[self::WHERE];

        $argv = $argv[self::UPDATE];

        if (empty($where) || empty($argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated specific where conditions.', 'danger');
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
        }

        $sql = 'UPDATE carbon_reports ' . ' SET '; // intellij cant handle this otherwise

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

        
        $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_reports', self::PDO_VALIDATION);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_reports.log_level', $argv)) {
            $log_level = $argv['carbon_reports.log_level'];
            $stmt->bindParam(':log_level',$log_level, 2, 20);
        }
        if (array_key_exists('carbon_reports.report', $argv)) {
            $stmt->bindValue(':report',$argv['carbon_reports.report'], 2);
        }
        if (array_key_exists('carbon_reports.date', $argv)) {
            $stmt->bindValue(':date',$argv['carbon_reports.date'], 2);
        }
        if (array_key_exists('carbon_reports.call_trace', $argv)) {
            $stmt->bindValue(':call_trace',$argv['carbon_reports.call_trace'], 2);
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
        $sql = 'DELETE FROM carbon_reports ';

        $pdo = self::database();
        
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_reports', self::PDO_VALIDATION);

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        $r and $remove = [];

        return $r;
    }
     

    
}
