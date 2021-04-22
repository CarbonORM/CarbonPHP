<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Helpers\RestfulValidations;
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
 * Class Wp_Postmeta
 * @package CarbonPHP\Tables
 * @note Note for convenience a flag '-prefix' maybe passed to remove table prefixes.
 *  Use '-help' for a full list of options.
 * @link https://carbonphp.com/ 
 *
 * This class contains autogenerated code.
 * This class is 1=1 relation named after a table in the database provided.
 * Edits are carried over during updates given they follow::
 *      METHODS SHOULD ONLY BE STATIC and may be reordered during generation.
 *      FUNCTIONS MUST NOT exist outside the class.
 *      IMPORTED CLASSED and FUNCTIONS ARE ALLOWED though maybe reordered.
 *      ADDITIONAL CONSTANTS of any kind are NOT ALLOWED.
 *      ADDITIONAL CLASS MEMBER VARIABLES are NOT ALLOWED.
 *
 * When creating member functions which require persistent variables, consider making them static members of that method.
 */
class Wp_Postmeta extends Rest implements iRestSinglePrimaryKey
{
    use RestfulValidations;
    
    public const CLASS_NAME = 'Wp_Postmeta';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'wp_postmeta';
    public const TABLE_PREFIX = '';
    
    /**
     * COLUMNS
     * The columns below are a 1=1 mapping to your columns found in wp_postmeta. Remember to regenerate when as all changes  
     * SHOULD be made first in the database. The RestBuilder program will capture any changes made and update this file 
     * auto-magically. 
    **/
    public const META_ID = 'wp_postmeta.meta_id'; 
    public const POST_ID = 'wp_postmeta.post_id'; 
    public const META_KEY = 'wp_postmeta.meta_key'; 
    public const META_VALUE = 'wp_postmeta.meta_value'; 

    /**
     * This could be null for tables without primary key(s), a string for tables with a single primary key, or an array 
     * given composite primary keys. The existence and amount of primary keys of the will also determine the interface 
     * aka method signatures used.
    **/
    public const PRIMARY = 'wp_postmeta.meta_id';

    public const COLUMNS = [
        'wp_postmeta.meta_id' => 'meta_id','wp_postmeta.post_id' => 'post_id','wp_postmeta.meta_key' => 'meta_key','wp_postmeta.meta_value' => 'meta_value',
    ];

    public const PDO_VALIDATION = [
        'wp_postmeta.meta_id' => ['bigint', 'PDO::PARAM_STR', ''],'wp_postmeta.post_id' => ['bigint', 'PDO::PARAM_STR', ''],'wp_postmeta.meta_key' => ['varchar', 'PDO::PARAM_STR', '255'],'wp_postmeta.meta_value' => ['longtext', 'PDO::PARAM_STR', ''],
    ];
     
    /**
     * REGEX_VALIDATION
     * Regular Expression validations are run before and recommended over PHP_VALIDATION.
     * It is a 1 to 1 column regex relation with fully regex for preg_match_all(). This regex must satisfy the condition 
     *        1 > preg_match_all(self::$compiled_regex_validations[$column], $value, ...
     * 
     * Table generated column constants must be used. 
     *       self::EXAMPLE_COLUMN_NAME => '#^[A-F0-9]{20,35}$#i'
     *
     * @link https://regexr.com
     * @link https://php.net/manual/en/function.preg-match-all.php
     */
 
    public const REGEX_VALIDATION = []; 
     
     
    /**
     * PHP validations works as follows:
     * @note regex validation is always step #1 and should be favored over php validations.
     *  Syntax ::
     *      [Example_Class::class => 'disallowPublicAccess', (optional) ...$rest]
     *      self::EXAMPLE_COLUMN => [Example_Class::class => 'exampleOtherMethod', (optional) ...$rest]
     *
     *  Callables defined above MUST NOT RETURN FALSE. Moreover; return values are ignored so `): void {` may be used. 
     *  array_key_first() must return a classes fully qualified class with namespace. In the example above Example_Class would be a 
     *  class defined in our system. PHP's `::class` appended to the end will return the fully qualified namespace. Note
     *  this will require the custom import added to the top of the file. You can allow your editor to add these for you
     *  as the RestBuilder program will capture, preserve, and possibly reorder the imports. The value of the first key 
     *  MUST BE the exact name of a member-method of that class. Typically validations are defined in the same class 
     *  they are used ('self::class') though it is useful to export more dynamic functions. The $rest variable can be 
     *  used to add additional arguments to the request. RESTFUL INTERNAL ARGUMENTS will be passed before any use defined
     *  variables after the first key value pair. Only array values will be passed to the method. Thus, additional keys 
     *  listed in the array will be ignored. Take for example::
     *
     *      [ self::class => 'validateUnique', self::class, self::EXAMPLE_COLUMN]
     *  The above is defined in RestfulValidations::class. 
     *      RestfulValidations::validateUnique(string $columnValue, string $className, string $columnName)
     *  Its definition is with a trait this classes inherits using `use` just after the `class` keyword. 
     * 
     *   What is the RESTFUL lifecycle?
     *      Regex validations are done first on any main query; sub-queries are treated like callbacks which get run 
     *      during the main queries invocation. The main query is 'paused' while the sub-query will compile and validate.
     *      Validations across tables are concatenated on joins and sub-queries. All callbacks will be run across any 
     *       table joins.
     *      
     *   What are the RESTFUL INTERNAL ARGUMENTS? (The single $arg string or array passed before my own...)
     *      REST_REQUEST_PREPROCESS_CALLBACKS ::   
     *           PREPROCESS::
     *              Methods defined here will be called at the beginning of every request. 
     *              Each method will be passed ( & self::$REST_REQUEST_PARAMETERS ) by reference so changes can be made pre-request.
     *              Method validations under the main 'PREPROCESS' key will be run first, while validations specific to 
     *              ( GET | POST | PUT | DELETE )::PREPROCESS will be run directly after.
     *
     *           FINAL:: 
     *              Each method will be passed the final ( & $SQL ), which may be a sub-query, by reference.
     *              Modifying the SQL string will effect the parent function. This can have disastrous effects.
     *
     *           COLUMN::
     *              Preformed while a column is being parsed in a query. The first column validations to run.
     *              Each column specific method under PREPROCESS will be passed nothing from rest. 
     *              Each method will ONLY be RUN ONCE regardless of how many times the column has been seen. 
     *
     *      COLUMN::
     *           Column validations are only run when they have been parsed in the query. Global column validations maybe
     *            RUN MULTIPLE TIMES if the column is used multiple times in a single restful query. 
     *           If you have a column that is used multiple times the validations will run for each occurrence.
     *           Column validation can mean many thing. There are three possible scenarios in which your method 
     *            signature would change. For this reason it is more common to use method ( GET | POST ... ) wise column validations.
     *              *The signature required are as follows:
     *                  Should the column be...
     *                      SELECTED:  
     *                          In a select stmt no additional parameters will be passed.
     *                      
     *                      ORDERED BY: (self::ASC | self::DESC)
     *                          The $operator will be passed to the method.
     *  
     *                      JOIN STMT:
     *                          The $operator followed by the $value will be passed. 
     *                          The operator could be :: >,<,<=,<,=,<>,=,<=>
     *
     *      REST_REQUEST_FINNISH_CALLBACKS::
     *          PREPROCESS::
     *              These callbacks are called after a successful PDOStatement->execute() but before Database::commit().
     *              Each method will be passed ( GET => &$return, DELETE => &$remove, PUT => &$returnUpdated ) by reference. 
     *              POST will BE PASSED NULL.          
     *
     *          FINAL::
     *              Run directly after method specific [FINAL] callbacks.
     *              The final, 'final' callback set. After these run rest will return. 
     *              Each method will be passed ( GET => &$return, DELETE => &$remove, PUT => &$returnUpdated ) by reference. 
     *              POST will BE PASSED NULL. 
     *
     *          COLUMN::
     *              These callables will be run after the [( GET | POST | PUT | DELETE )][FINAL] methods.
     *              Directly after, the [REST_REQUEST_FINNISH_CALLBACKS][FINAL] will run. 
     *              
     *
     *      (POST|GET|PUT|DELETE)::
     *          PREPROCESS::
     *              Methods run after any root 'REST_REQUEST_PREPROCESS_CALLBACKS'
     *              Each method will not be passed any argument from system. User arguments will be directly reflected.
     *
     *          COLUMN::
     *              Methods run after any root column validations, the last of the PREPROCESS column validations to run.
     *              Based on the existences and number of primary key(s), the signature will change. 
     *               See the notes on the base column validations as signature of parameters may change. 
     *              It is not possible to directly define a method->column specific post processes. This can be done by
     *               dynamically pairing multiple method processes starting with one here which signals a code routine 
     *               in a `finial`-ly defined method. The FINAL block specific to the method would suffice. 
     *
     *          FINAL::
     *              Passed the ( & $return )  
     *              Run before any other column validation 
     *
     *  Be aware the const: self::DISALLOW_PUBLIC_ACCESS = [self::class => 'disallowPublicAccess'];
     *  could be used to replace each occurrence of 
     *          [self::class => 'disallowPublicAccess', self::class]
     *  though would loose information as self::class is a dynamic variable which must be used in this class given 
     *  static and constant context. 
     *  @version ^9
     */
 
    public const PHP_VALIDATION = [ 
        self::REST_REQUEST_PREPROCESS_CALLBACKS => [ 
            self::PREPROCESS => [ 
                [self::class => 'disallowPublicAccess', self::class],
            ]
        ],
        self::GET => [ 
            self::PREPROCESS => [ 
                [self::class => 'disallowPublicAccess', self::class],
            ]
        ],    
        self::POST => [ self::PREPROCESS => [[ self::class => 'disallowPublicAccess', self::class ]]],    
        self::PUT => [ self::PREPROCESS => [[ self::class => 'disallowPublicAccess', self::class ]]],    
        self::DELETE => [ self::PREPROCESS => [[ self::class => 'disallowPublicAccess', self::class ]]],
        self::REST_REQUEST_FINNISH_CALLBACKS => [ self::PREPROCESS => [[ self::class => 'disallowPublicAccess', self::class ]]]    
    ]; 
    
    /**
     * REFRESH_SCHEMA
     * These directives should be designed to maintain and update your team's schema &| database &| table over time. 
     * The changes you made in your local env should be coded out in callables such as the 'tableExistsOrExecuteSQL' 
     * method call below. If a PDO exception is thrown with `$e->getCode()` equal to 42S02 or 1049 CarbonPHP will attempt
     * to REFRESH the full database with with all directives in all tables. If possible keep table specific procedures in 
     * it's respective restful-class table file. Check out the 'tableExistsOrExecuteSQL' method in the parent class to see
     * an example using self::REMOVE_MYSQL_FOREIGN_KEY_CHECKS. Note each directive should be designed to run multiple times.
     */
 
    public const REFRESH_SCHEMA = [
        [self::class => 'tableExistsOrExecuteSQL', self::TABLE_NAME, self::REMOVE_MYSQL_FOREIGN_KEY_CHECKS .
                        PHP_EOL . self::CREATE_TABLE_SQL . PHP_EOL . self::REVERT_MYSQL_FOREIGN_KEY_CHECKS]
    ]; 
   
    public const CREATE_TABLE_SQL = /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `wp_postmeta` (
  `meta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
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
    *              'table_name.column_name',                            // bad, dont pass strings manually. Use Table Constants instead.
    *              self::EXAMPLE_COLUMN_ONE,                            // good, 
    *              [self::EXAMPLE_COLUMN_TWO, Rest::AS, 'customName'],
    *              [Rest::COUNT, self::EXAMPLE_COLUMN_TWO, 'custom_return_name_using_as'],
    *              [Rest::GROUP_CONCAT, self::EXAMPLE_COLUMN_THREE], 
    *              [Rest::MAX, self::EXAMPLE_COLUMN_FOUR], 
    *              [Rest::MIN, self::EXAMPLE_COLUMN_FIVE], 
    *              [Rest::SUM, self::EXAMPLE_COLUMN_SIX], 
    *              [Rest::DISTINCT, self::EXAMPLE_COLUMN_SEVEN], 
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
    *              Rest::PAGE => (int) 0, // used for pagination which equates to 
    *                  // ... LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $argv[self::PAGINATION][self::LIMIT]) 
    *                  //       . ',' . $argv[self::PAGINATION][self::LIMIT];
    *              
    *              Rest::LIMIT => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *               Rest::ORDER => [self::EXAMPLE_COLUMN_TEN => Rest::ASC ],  // i.e.  'username' => Rest::DESC
    *         ],
    *   ];
    *
    *
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @noinspection DuplicatedCode - possible as this is generated
    * @generated
    * @throws PublicAlert|PDOException|JsonException
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::startRest(self::GET, $return, $argv ,$primary);

        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        self::postpreprocessRestRequest($sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            self::completeRest();
            throw new PublicAlert('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
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
        }

        

        self::postprocessRestRequest($return);
        
        self::completeRest();
        
        return true;
    }

    /**
     * @param array $data 
     * @return bool|string|mixed
     * @generated
     * @throws PublicAlert|PDOException|JsonException
     */
    public static function Post(array $data)
    {   
        self::startRest(self::POST, [], $data);
    
        foreach ($data as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ( :post_id, :meta_key, :meta_value)';

        $pdo = self::database();
        
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);         
        $post_id = $data['wp_postmeta.post_id'] ?? '0';
        $ref='wp_postmeta.post_id';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $post_id, $post_id === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'wp_postmeta.post_id\'.');
        }
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_STR);
        
        $meta_key = $data['wp_postmeta.meta_key'] ?? null;
        $ref='wp_postmeta.meta_key';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $meta_key, $meta_key === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'wp_postmeta.meta_key\'.');
        }
        $stmt->bindParam(':meta_key',$meta_key, PDO::PARAM_STR, 255);
        
        if (!array_key_exists('wp_postmeta.meta_value', $data)) {
            throw new PublicAlert('The column \'wp_postmeta.meta_value\' is set to not null and has no default value. It must exist in the request and was not found in the one sent.');
        } 
        $ref='wp_postmeta.meta_value';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $data['meta_value'])) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'wp_postmeta.meta_value\'.');
        }
        $stmt->bindValue(':meta_value', $data['wp_postmeta.meta_value'], PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            self::completeRest();
            throw new PublicAlert('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }
        
        $id = $pdo->lastInsertId();
        
        self::prepostprocessRestRequest($id);
        
        if (self::$commit && !Database::commit()) {
           throw new PublicAlert('Failed to store commit transaction on table wp_postmeta');
        }
        
        self::postprocessRestRequest($id);
        
        self::completeRest();
        
        return $id;  
    }
    
    /**
    * 
    * 
    * Tables where primary keys exist must be updated by its primary key. 
    * Column should be in a key value pair passed to $argv or optionally using syntax:
    * $argv => [
    *       Rest::UPDATE => [
    *              ...
    *       ]
    * ]
    * 
    * @param array $returnUpdated - will be merged with with array_merge, with a successful update. 
    * @param string $primary
    * @param array $argv 
    * @generated
    * @throws PublicAlert|PDOException|JsonException
    * @return bool - if execute fails, false will be returned and $returnUpdated = $stmt->errorInfo(); 
    */
    public static function Put(array &$returnUpdated, string $primary, array $argv) : bool
    {
        self::startRest(self::PUT, $returnUpdated, $argv, $primary);
        
        if ('' === $primary) {
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
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'wp_postmeta.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE wp_postmeta SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('wp_postmeta.meta_id', $argv)) {
            $set .= 'meta_id=:meta_id,';
        }        if (array_key_exists('wp_postmeta.post_id', $argv)) {
            $set .= 'post_id=:post_id,';
        }        if (array_key_exists('wp_postmeta.meta_key', $argv)) {
            $set .= 'meta_key=:meta_key,';
        }        if (array_key_exists('wp_postmeta.meta_value', $argv)) {
            $set .= 'meta_value=:meta_value,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();
        
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('wp_postmeta.meta_id', $argv)) {
            $stmt->bindValue(':meta_id',$argv['wp_postmeta.meta_id'], PDO::PARAM_STR);
}if (array_key_exists('wp_postmeta.post_id', $argv)) {
            $stmt->bindValue(':post_id',$argv['wp_postmeta.post_id'], PDO::PARAM_STR);
}if (array_key_exists('wp_postmeta.meta_key', $argv)) {
            $meta_key = $argv['wp_postmeta.meta_key'];
            $ref = 'wp_postmeta.meta_key';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $meta_key)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':meta_key',$meta_key, PDO::PARAM_STR, 255);
        }if (array_key_exists('wp_postmeta.meta_value', $argv)) {
            $stmt->bindValue(':meta_value',$argv['wp_postmeta.meta_value'], PDO::PARAM_STR);
}

        self::bind($stmt);

        if (!$stmt->execute()) {
            self::completeRest();
            throw new PublicAlert('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to find the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('wp_postmeta.', '', $k); },
                array_keys($argv)
            ),
            array_values($argv)
        );

        $returnUpdated = array_merge($returnUpdated, $argv);
        
        self::prepostprocessRestRequest($returnUpdated);
        
        if (self::$commit && !Database::commit()) {
           throw new PublicAlert('Failed to store commit transaction on table wp_postmeta');
        }
        
        self::postprocessRestRequest($returnUpdated);
        
        self::completeRest();
        
        return true;
    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @generated
    * @noinspection DuplicatedCode
    * @throws PublicAlert|PDOException|JsonException
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
    {
        self::startRest(self::DELETE, $remove, $argv, $primary);
        /** @noinspection SqlWithoutWhere
         * @noinspection UnknownInspectionInspection - intellij is funny sometimes.
         */
        $sql = 'DELETE FROM wp_postmeta ';

        $pdo = self::database();
        
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }
        
        
        if (null === $primary) {
           /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
            }
            
            $where = self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
            
            if (empty($where)) {
                throw new PublicAlert('The where condition provided appears invalid.', 'danger');
            }

            $sql .= ' WHERE ' . $where;
        } else {
            $sql .= ' WHERE  meta_id='.self::addInjection($primary, $pdo).'';
        }


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            self::completeRest();
            throw new PublicAlert('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }

        $remove = [];
        
        self::prepostprocessRestRequest($remove);
        
        if (self::$commit && !Database::commit()) {
           throw new PublicAlert('Failed to store commit transaction on table wp_postmeta');
        }
        
        self::postprocessRestRequest($remove);
        
        self::completeRest();
        
        return true;
    }
    
}
