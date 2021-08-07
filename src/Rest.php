<?php


namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Tables\Carbons;
use PDO;
use PDOStatement;
use Throwable;

abstract class Rest extends Database
{

    # mysql restful identifiers
    public const AS = 'AS';
    public const ASC = ' ASC';
    public const COUNT = 'COUNT';
    public const CURRENT_TIMESTAMP = ' CURRENT_TIMESTAMP ';
    public const DESC = ' DESC'; // not case sensitive but helpful for reporting to remain uppercase
    public const DISTINCT = 'DISTINCT';
    public const EQUAL = '=';
    public const EQUAL_NULL_SAFE = '<=>';
    public const FULL_OUTER = 'FULL OUTER';
    public const GREATER_THAN = '>';
    public const GROUP_CONCAT = 'GROUP_CONCAT';
    public const GREATER_THAN_OR_EQUAL_TO = '>=';
    public const HEX = 'HEX';
    public const INNER = 'INNER';
    public const INSERT = 'INSERT';
    public const JOIN = 'JOIN';
    public const LEFT = 'LEFT';
    public const LEFT_OUTER = 'LEFT OUTER';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL_TO = '<=';
    public const LIKE = ' LIKE ';
    public const LIMIT = 'LIMIT';
    public const MIN = 'MIN';
    public const MAX = 'MAX';
    public const NOW = ' NOW() ';
    public const NOT_EQUAL = '<>';
    public const ORDER = 'ORDER';
    public const PAGE = 'PAGE';
    public const PAGINATION = 'PAGINATION';
    public const REPLACE = 'REPLACE INTO';
    public const RIGHT = 'RIGHT';
    public const RIGHT_OUTER = 'RIGHT OUTER';
    public const SELECT = 'SELECT';
    public const SUM = 'SUM';
    public const TRANSACTION_TIMESTAMP = ' TRANSACTION_TIMESTAMP ';
    public const UPDATE = 'UPDATE';
    public const UNHEX = 'UNHEX';
    public const WHERE = 'WHERE';

    # carbon identifiers
    public const DEPENDANT_ON_ENTITY = 'DEPENDANT_ON_ENTITY';

    # HTTP Methods (case sensitive dont touch)
    public const OPTIONS = 'OPTIONS';
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';           // can also preform REPLACE INTO operations
    public const DELETE = 'DELETE';

    // Only for php
    public const MYSQL_TYPE = 'MYSQL_TYPE';
    public const PDO_TYPE = 'PDO_TYPE';
    public const MAX_LENGTH = 'MAX_LENGTH';
    public const AUTO_INCREMENT = 'AUTO_INCREMENT';
    public const SKIP_COLUMN_IN_POST = 'SKIP_COLUMN_IN_POST';
    public const DEFAULT_POST_VALUE = 'DEFAULT_POST_VALUE';
    public const REST_REQUEST_PREPROCESS_CALLBACKS = 'PREPROCESS';  // had to change from 0 so we could array merge recursively.
    public const PREPROCESS = self::REST_REQUEST_PREPROCESS_CALLBACKS;
    public const REST_REQUEST_FINNISH_CALLBACKS = 'FINISH';
    public const FINISH = self::REST_REQUEST_FINNISH_CALLBACKS;
    public const VALIDATE_C6_ENTITY_ID_REGEX = '#^' . Route::MATCH_C6_ENTITY_ID_REGEX . '$#';
    public const DISALLOW_PUBLIC_ACCESS = [self::class => 'disallowPublicAccess'];
    public static array $activeQueryStates = [];
    public static ?string $REST_REQUEST_METHOD = null;

    /**
     * @var mixed
     */
    public static $REST_REQUEST_PRIMARY_KEY = null;       // this is set with the request payload

    public const REST_REQUEST_PRIMARY_KEY = 'REST_REQUEST_PRIMARY_KEY';       // this is set with the request payload

    /**
     * @var mixed
     */
    public static $REST_REQUEST_PARAMETERS = [];            // this is set with the request payload

    /**
     * @var mixed
     */
    public static $REST_REQUEST_RETURN_DATA = [];           // this is set with the request payload
    public static array $VALIDATED_REST_COLUMNS = [];
    public static array $compiled_valid_columns = [];
    public static array $compiled_PDO_validations = [];
    public static array $compiled_PHP_validations = [];
    public static array $compiled_regex_validations = [];
    public static array $join_tables = [];
    public static array $injection = [];                    // this increments well regardless of state, amount of rest calls ect.

    // validating across joins for rest is hard enough. I'm not going to allow user/FED provided sub queries
    public static bool $commit = true;
    public static bool $allowInternalMysqlFunctions = true;
    public static bool $allowSubSelectQueries = false;
    public static bool $externalRestfulRequestsAPI = false;

    // False for external and internal requests by default. If a primary key exists you should always attempt to use it.
    public static bool $allowFullTableUpdates = false;
    public static bool $allowFullTableDeletes = false;

    /**
     * Rest constructor. Avoid this if possible.
     * This is complex, but if the value is modified in the original array it will be modified in the instantiated
     *  object by reference. The call time is slightly slower but your server will thank you when you start editing these
     *  values // recompiling to send. Really try to avoid looping through an array and using new. Refer to php manual
     *  as to why arrays will help your dev.
     * @link https://www.php.net/manual/en/features.gc.php
     * @link https://gist.github.com/nikic/5015323 -> https://www.npopov.com/2014/12/22/PHPs-new-hashtable-implementation.html
     * @param array $return
     */
    public function __construct(array &$return) {

        if ([] === $return) {

            return;

        }

        foreach ($return  as $key => &$value) {

            $this->$key = $value;

        }

    }

    /**
     * static::class ends up not working either because of how it is called.
     * @param $request
     * @param string|null $calledFrom
     * @throws PublicAlert
     * @noinspection PhpUnusedParameterInspection
     */
    public static function disallowPublicAccess($request, $calledFrom = null): void
    {
        if (self::$externalRestfulRequestsAPI && !CarbonPHP::$test) {
            throw new PublicAlert('Rest request denied by the PHP_VALIDATION\'s in the tables ORM. Remove DISALLOW_PUBLIC_ACCESS ' . (null !== $calledFrom ? ' from \'' . $calledFrom . '\'' : '') . ' to gain privileges.');
        }
    }

    public static function getDynamicRestClass(string $fullyQualifiedRestClassName, string $mustInterface = null): string
    {
        static $cache = [];

        if (array_key_exists($fullyQualifiedRestClassName, $cache)) {
            return $cache[$fullyQualifiedRestClassName];
        }

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        if ($fullyQualifiedRestClassName::TABLE_PREFIX === $prefix) {
            return $fullyQualifiedRestClassName;
        }

        $namespace = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::NAMESPACE] ?? '';

        $custom_prefix_carbon_table = $namespace . $fullyQualifiedRestClassName::TABLE_NAME;        //  we're using table name and not class name as any different prefix, even a subset of the original, will be appended

        if (!class_exists($custom_prefix_carbon_table)) {
            throw new PublicAlert("Could not find the required class ($custom_prefix_carbon_table) in the user defined namespace ($namespace). This is required because a custom table prefix ($prefix) has been detected.");
        }

        if (($mustInterface === iRestSinglePrimaryKey::class || $mustInterface === null) && in_array(iRestSinglePrimaryKey::class, class_implements($fullyQualifiedRestClassName), true)) {
            if (!in_array(iRestSinglePrimaryKey::class, class_implements($custom_prefix_carbon_table), true)) {
                throw new PublicAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestSinglePrimaryKey::class . '. You should rerun RestBuilder.');
            }
        } else if (($mustInterface === iRestNoPrimaryKey::class || $mustInterface === null) && in_array(iRestNoPrimaryKey::class, class_implements($fullyQualifiedRestClassName), true)) {
            if (!in_array(iRestNoPrimaryKey::class, class_implements($custom_prefix_carbon_table), true)) {
                throw new PublicAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestNoPrimaryKey::class . '. You should rerun RestBuilder.');
            }
        } else if (($mustInterface === iRestMultiplePrimaryKeys::class || $mustInterface === null) && in_array(iRestMultiplePrimaryKeys::class, class_implements($fullyQualifiedRestClassName), true)
            && !in_array(iRestMultiplePrimaryKeys::class, class_implements($custom_prefix_carbon_table), true)) {
            throw new PublicAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestMultiplePrimaryKeys::class . '. You should rerun RestBuilder.');
        } else {
            if ($mustInterface === null) {
                throw new PublicAlert("The table '$custom_prefix_carbon_table' we determined to be your implementation of '$fullyQualifiedRestClassName' failed to implement any of the correct interfaces.");
            }
            throw new PublicAlert("The table '$custom_prefix_carbon_table' we determined to be your implementation of '$fullyQualifiedRestClassName' failed to implement the required '$mustInterface'.");
        }

        return $cache[$fullyQualifiedRestClassName] = $custom_prefix_carbon_table;
    }


    /**
     * @param string $message
     * @return bool
     * @throws PublicAlert
     */
    public static function signalError(string $message): bool
    {
        if (!self::$externalRestfulRequestsAPI && CarbonPHP::$is_running_production && !CarbonPHP::$test) {
            return false;
        }
        throw new PublicAlert($message);
    }

    public static function preprocessRestRequest(): void
    {
        if ((self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::PREPROCESS] ?? false)
            && is_array(self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::PREPROCESS])) {

            self::runValidations(self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::PREPROCESS]);

        }

        if ((self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PREPROCESS] ?? false)
            && is_array(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PREPROCESS])) {

            self::runValidations(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PREPROCESS]);

        }

    }

    public static function postpreprocessRestRequest(string &$sql): void
    {
        foreach (self::$VALIDATED_REST_COLUMNS as $column) {

            if ((self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::FINISH][$column] ?? false)
                && is_array(self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::FINISH][$column])) {

                self::runValidations(self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][self::FINISH][$column], $sql);

            }

        }

        if ((self::$compiled_PHP_validations[self::PREPROCESS][self::FINISH] ?? false)
            && is_array(self::$compiled_PHP_validations[self::PREPROCESS][self::FINISH])) {

            self::runValidations(self::$compiled_PHP_validations[self::PREPROCESS][self::FINISH], $sql);

        }

    }

    public static function prepostprocessRestRequest(&$return = null): void
    {
        if ((self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS] ?? false)
            && is_array(self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS])) {

            self::runValidations(self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS], $return);

        }

    }

    public static function postprocessRestRequest(&$return = null): void
    {
        if ((self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::FINISH] ?? false)
            && is_array(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::FINISH])) {

            self::runValidations(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::FINISH], $return);

        }

        foreach (self::$VALIDATED_REST_COLUMNS as $column) {

            if ((self::$compiled_PHP_validations[self::FINISH][$column] ?? false)
                && is_array(self::$compiled_PHP_validations[self::FINISH][$column])) {

                self::runValidations(self::$compiled_PHP_validations[self::FINISH][$column], $return);

            }

        }

        if ((self::$compiled_PHP_validations[self::FINISH][self::FINISH] ?? false)
            && is_array(self::$compiled_PHP_validations[self::FINISH][self::FINISH])) {

            self::runValidations(self::$compiled_PHP_validations[self::FINISH][self::FINISH], $return);

        }

    }

    /**
     * returns true if it is a column name that exists and all user validations pass.
     * return is false otherwise.
     * @param string $method
     * @param mixed $column
     * @param string|null $operator
     * @param string|null $value
     * @param bool $default
     * @return bool
     * @throws PublicAlert
     */
    public static function validateInternalColumn(string $method, &$column, string &$operator = null, &$value = null, bool $default = false): bool
    {
        $runCustomCallables = static function () use (&$column, &$operator, &$value, $method, $default): void {

            self::$VALIDATED_REST_COLUMNS[] = $column;  // to prevent recursion.

            if (null !== $value && $default === false) {

                $equalsValidColumn = self::validateInternalColumn($method, $value, $column);

                if (false === $equalsValidColumn
                    && array_key_exists($column, self::$compiled_regex_validations)
                    && 1 > preg_match_all(self::$compiled_regex_validations[$column], $value, $matches, PREG_SET_ORDER)) {  // can return 0 or false

                    throw new PublicAlert("The column $column was set to be compared with a value who did not pass the regex test. Please check this value and try again.");

                }
                // todo - add injection logic here // double down on aggregate placement (low priority as it's done elsewhere)
            }

            // run validation on the whole request give column now exists
            /** @noinspection NotOptimalIfConditionsInspection */
            if (!in_array($column, self::$VALIDATED_REST_COLUMNS, true)
                && (self::$compiled_PHP_validations[self::REST_REQUEST_PREPROCESS_CALLBACKS][$column] ?? false)) {

                if (!is_array(self::$compiled_PHP_validations[self::PREPROCESS][$column])) {

                    throw new PublicAlert('The value of [' . self::PREPROCESS . '][' . $column . '] should equal an array. See Carbonphp.com for more info.');

                }

                self::runValidations(self::$compiled_PHP_validations[self::PREPROCESS][$column]);
            }

            // run validation on each condition
            if ((self::$compiled_PHP_validations[$column] ?? false) && is_array(self::$compiled_PHP_validations[$column])) {
                if ($operator === null) {
                    self::runValidations(self::$compiled_PHP_validations[$column]);
                } elseif ($operator === self::ASC || $operator === self::DESC) {
                    self::runValidations(self::$compiled_PHP_validations[$column], $operator);
                } else {
                    self::runValidations(self::$compiled_PHP_validations[$column], $operator, $value);
                }
            }

            // run validation on each condition
            if ((self::$compiled_PHP_validations[$method][$column] ?? false) && is_array(self::$compiled_PHP_validations[$method][$column])) {

                if ($operator === null) {

                    self::runValidations(self::$compiled_PHP_validations[$method][$column]);

                } elseif ($operator === self::ASC || $operator === self::DESC) {

                    self::runValidations(self::$compiled_PHP_validations[$method][$column], $operator);

                } else {

                    self::runValidations(self::$compiled_PHP_validations[$method][$column], $operator, $value);

                }

            }

        };

        if (!is_string($column) && !is_int($column)) {

            return false; // this may indicate a json column

        }

        if (array_key_exists($column, self::$compiled_PDO_validations)) {      // allow short tags

            $runCustomCallables();

            return true;

        }

        if ($key = array_search($column, self::$compiled_valid_columns, true)) {

            $column = $key; // adds table name.

            $runCustomCallables();

            return true;

        }

        return false;
    }

    /**
     * @return void
     * @throws PublicAlert
     */
    protected static function gatherValidationsForRequest(): void
    {
        $tables = [static::CLASS_NAME];

        if (array_key_exists(self::JOIN, self::$REST_REQUEST_PARAMETERS ?? [])) {

            foreach (self::$REST_REQUEST_PARAMETERS[self::JOIN] as $key => $value) {

                if (!in_array($key, [self::INNER, self::LEFT_OUTER, self::RIGHT_OUTER, self::RIGHT, self::LEFT], true)) {

                    throw new PublicAlert('Invalid join condition ' . $key . ' passed. Supported options are (inner, left outter, right outter, right, and left).');

                }

                if (!empty(self::$REST_REQUEST_PARAMETERS[self::JOIN][$key])) {

                    $tables = [...$tables, ...array_keys(self::$REST_REQUEST_PARAMETERS[self::JOIN][$key])];

                }

            }

        }

        $compiled_columns = $pdo_validations = $php_validations = $regex_validations = [];

        foreach ($tables as &$table) {

            $table = explode('_', $table);      // table name semantics vs class name

            $table = array_map('ucfirst', $table);

            $table = implode('_', $table);

            if (!defined(static::class . '::CLASS_NAMESPACE')) {
                throw new PublicAlert('The rest table did not appear to have constant CLASS_NAMESPACE. Please try regenerating rest tables.');
            }

            $class_name = preg_replace('/^' . preg_quote(constant(static::class . '::TABLE_PREFIX'), '/') . '/i', '', $table);

            if (!class_exists($table = static::CLASS_NAMESPACE . $table)
                && !class_exists($table = static::CLASS_NAMESPACE . $class_name)) {

                throw new PublicAlert("Failed to find the table ($table) requested.");

            }

            if (!is_subclass_of($table, self::class)) {

                throw new PublicAlert('The table must extent :: ' . self::class);

            }

            $imp = array_map('strtolower', array_keys(class_implements($table)));

            if (!in_array(strtolower(iRest::class), $imp, true)
                && !in_array(strtolower(iRestfulReferences::class), $imp, true)
                && !in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
            ) {

                $possibleImpl = implode('|', [iRest::class, iRestfulReferences::class, iRestMultiplePrimaryKeys::class, iRestSinglePrimaryKey::class, iRestNoPrimaryKey::class]);

                throw new PublicAlert("The table does not implement the correct interface. Requires ($possibleImpl). Try re-running the RestBuilder.");

            }

            // It is possible to have a column validation assigned to another table,
            // which would cause rest to only run it when joined
            if (defined("$table::REGEX_VALIDATION")) {

                $table_regular_expressions = constant("$table::REGEX_VALIDATION");

                if (!is_array($table_regular_expressions)) {

                    throw new PublicAlert("The class constant $table::REGEX_VALIDATION must equal an array.");

                }

                if (!empty($table_regular_expressions)) {

                    if (!is_array($table_regular_expressions)) {

                        throw new PublicAlert("The class constant $table::REGEX_VALIDATION should equal an array. Please see CarbonPHP.com for more information.");

                    }

                    // todo - run table validation on cli command to save time??
                    foreach ($table_regular_expressions as $columnName => $regex) {
                        # [$table_name, $columnName] = ... explode $columnName

                        if (!is_string($regex)) {

                            throw new PublicAlert("A key => value pair encountered in $table::REGEX_VALIDATION is invalid. All values must equal a string.");

                        }

                    }

                    $regex_validations[] = $table_regular_expressions;

                }

            } else {

                throw new PublicAlert('The table does not implement REGEX_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.');

            }

            $table_columns_full = [];

            if (defined("$table::COLUMNS")) {

                $table_columns_constant = constant("$table::COLUMNS");

                foreach ($table_columns_constant as $key => $value) {

                    if (!is_string($key)) {

                        throw new PublicAlert("A key in the constant $table::COLUMNS was found not to be a string. Please try regenerating the restbuilder.");

                    }

                    $table_columns_full[] = $key;

                    if (!is_string($value)) {

                        throw new PublicAlert("A value in the constant $table::COLUMNS was found not to be a string. Please try regenerating the restbuilder.");

                    }

                }

                $compiled_columns[] = $table_columns_constant;

            } else {

                throw new PublicAlert('The table does not implement PHP_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.');

            }

            // their is no way to know exactly were the validations come from after this moment in the rest lifecycle
            // this makes this validation segment valuable
            if (defined("$table::PDO_VALIDATION")) {

                $table_php_validation = constant("$table::PDO_VALIDATION");

                if (!is_array($table_php_validation)) {

                    throw new PublicAlert("The class constant $table::PDO_VALIDATION must equal an array.");

                }

                if (!empty($table_php_validation)) {

                    // doing the foreach like this allows us to avoid multiple loops later,.... wonder what the best case is though... this is more readable for sure
                    foreach ($table_columns_full as $column) {

                        if (($table_php_validation[self::PREPROCESS][$column] ?? false) && !is_array($table_php_validation[self::PREPROCESS][$column])) {

                            throw new PublicAlert("The class constant $table_php_validation\[self::PREPROCESS][$column] should be an array of numeric indexed arrays. See CarbonPHP.com for examples.");

                        }

                        if (($table_php_validation[self::FINISH][$column] ?? false) && !is_array($table_php_validation[self::PREPROCESS][$column])) {

                            throw new PublicAlert("The class constant $table_php_validation\[self::FINISH][$column] should be an array of numeric indexed arrays. See CarbonPHP.com for examples.");

                        }

                        // Only validate the columns the request is coming on to save time.
                        if (($table_php_validation[self::$REST_REQUEST_METHOD][$column] ?? false) && !is_array($table_php_validation[self::$REST_REQUEST_METHOD][$column])) {

                            throw new PublicAlert("The class constant $table_php_validation\[self::" . self::$REST_REQUEST_METHOD . "][$column] should be an array of numeric indexed arrays. See CarbonPHP.com for examples.");

                        }

                    }

                    if ($table_php_validation[self::PREPROCESS] ?? false) {

                        if (!is_array($table_php_validation[self::PREPROCESS])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::PREPROCESS] must be an array.");

                        }

                        if (($table_php_validation[self::PREPROCESS][self::PREPROCESS] ?? false) && !is_array($table_php_validation[self::PREPROCESS][self::PREPROCESS])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::PREPROCESS][self::PREPROCESS] must be an array.");

                        }

                        if (($table_php_validation[self::PREPROCESS][self::FINISH] ?? false) && !is_array($table_php_validation[self::PREPROCESS][self::FINISH])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::PREPROCESS][self::FINISH] must be an array.");

                        }

                    }

                    if ($table_php_validation[self::FINISH] ?? false) {

                        if (!is_array($table_php_validation[self::FINISH])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::FINISH] must be an array.");

                        }

                        if (($table_php_validation[self::FINISH][self::PREPROCESS] ?? false) && !is_array($table_php_validation[self::FINISH][self::PREPROCESS])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::FINISH][self::PREPROCESS] must be an array.");

                        }

                        if (($table_php_validation[self::FINISH][self::FINISH] ?? false) && !is_array($table_php_validation[self::FINISH][self::FINISH])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::FINISH][self::FINISH] must be an array.");

                        }

                    }

                    if ($table_php_validation[self::$REST_REQUEST_METHOD] ?? false) {

                        if (!is_array($table_php_validation[self::$REST_REQUEST_METHOD])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::" . self::$REST_REQUEST_METHOD . "] must be an array.");

                        }

                        if (($table_php_validation[self::$REST_REQUEST_METHOD][self::PREPROCESS] ?? false) && !is_array($table_php_validation[self::$REST_REQUEST_METHOD][self::PREPROCESS])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::" . self::$REST_REQUEST_METHOD . "][self::PREPROCESS] must be an array.");

                        }

                        if (($table_php_validation[self::$REST_REQUEST_METHOD][self::FINISH] ?? false) && !is_array($table_php_validation[self::$REST_REQUEST_METHOD][self::FINISH])) {

                            throw new PublicAlert("The class constant $table::PDO_VALIDATION[self::" . self::$REST_REQUEST_METHOD . "][self::FINISH] must be an array.");

                        }

                    }

                }

                $pdo_validations[] = $table_php_validation;

            } else {

                throw new PublicAlert('The table  does not implement PHP_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.');

            }

            if (defined("$table::PHP_VALIDATION")) {

                $php_validations[] = constant("$table::PHP_VALIDATION");

            } else {

                throw new PublicAlert('The table does not implement PHP_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.');

            }

        }

        unset($table);

        self::$join_tables = $tables;

        self::$compiled_valid_columns = array_merge(self::$compiled_valid_columns, ... $compiled_columns);

        self::$compiled_PDO_validations = array_merge(self::$compiled_PDO_validations, ... $pdo_validations);

        self::$compiled_PHP_validations = array_merge_recursive(self::$compiled_PHP_validations, ... $php_validations);

        self::$compiled_regex_validations = array_merge(self::$compiled_regex_validations, ...$regex_validations); // a nice way to avoid running a merge in a loop.

    }

    /**
     * This should only be used for api requests.
     * @param string $mainTable
     * @param string|null $primary
     * @param string $namespace
     * @return bool
     */
    public static function ExternalRestfulRequestsAPI(string $mainTable, string $primary = null, string $namespace = 'Tables\\'): bool
    {
        global $json;

        $json = [];

        self::$externalRestfulRequestsAPI = true;   // This is to help you determine the request type in

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        try {
            $mainTable = ucwords($mainTable, '_');

            if (!class_exists($namespace . $mainTable)
                && !class_exists($namespace . $mainTable = preg_replace('/^' . preg_quote($prefix, '/') . '/i', '', $mainTable))) {
                throw new PublicAlert("The table $mainTable was not found in our generated api. Please try rerunning the rest builder and contact us if problems persist.");
            }

            $implementations = array_map('strtolower', array_keys(class_implements($namespace . $mainTable)));

            $requestTableHasPrimary = in_array(strtolower(iRestSinglePrimaryKey::class), $implementations, true)
                || in_array(strtolower(iRestMultiplePrimaryKeys::class), $implementations, true);

            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            switch ($method) {

                case self::GET:

                    if (array_key_exists(0, $_GET)) {

                        if (ctype_xdigit($_GET[0])) {

                            $_GET[0] = pack('H*', $_GET[0]);

                        }

                        $_GET = json_decode(stripcslashes($_GET[0]), true, JSON_THROW_ON_ERROR);

                        if (null === $_GET) {

                            throw new PublicAlert('Json decoding of $_GET[0] returned null. Please attempt serializing another way.');

                        }

                    } else {

                        if (array_key_exists(self::SELECT, $_GET) && is_string($_GET[self::SELECT])) {

                            $_GET[self::SELECT] = json_decode($_GET[self::SELECT], true);

                        }

                        if (array_key_exists(self::JOIN, $_GET) && is_string($_GET[self::JOIN])) {

                            $_GET[self::JOIN] = json_decode($_GET[self::JOIN], true);

                        }

                        if (array_key_exists(self::WHERE, $_GET) && is_string($_GET[self::WHERE])) {

                            $_GET[self::WHERE] = json_decode($_GET[self::WHERE], true);

                        }

                        if (array_key_exists(self::PAGINATION, $_GET) && is_string($_GET[self::PAGINATION])) {

                            $_GET[self::PAGINATION] = json_decode($_GET[self::PAGINATION], true);

                        }

                    }

                    $args = $_GET;

                    break;

                case self::PUT:

                    if ($primary === null) {

                        throw new PublicAlert('Updating restful records requires a primary key.'); // todo - add updates optional primary bool

                    }

                case self::OPTIONS:

                    $_SERVER['REQUEST_METHOD'] = self::GET;

                case self::POST:
                case self::DELETE:

                    $args = $_POST;

                    break;

                default:

                    throw new PublicAlert('The REQUEST_METHOD is not RESTFUL. Method must be either \'POST\', \'PUT\', \'GET\', or \'DELETE\'. The \'OPTIONS\' method may be used in substation for the \'GET\' request which better enables and speeds up advanced queries.');

            }

            $methodCase = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));  // this is to match actual method spelling

            switch ($method) {
                case self::OPTIONS:
                case self::PUT:
                case self::DELETE:
                case self::GET:

                    $return = [];

                    if (!call_user_func_array([$namespace . $mainTable, $methodCase], $requestTableHasPrimary ? [&$return, $primary, $args] : [&$return, $args])) {

                        throw new PublicAlert('The request failed, please make sure arguments are correct.');

                    }

                    $json['rest'] = $return;

                    if ($method === self::PUT) {

                        $json['rest']['updated'] = $primary ?? $args;

                    } elseif ($method === self::DELETE) {

                        $json['rest']['deleted'] = $primary ?? $args;

                    }

                    break;

                case self::POST:

                    if (!$id = call_user_func([$namespace . $mainTable, $methodCase], $_POST, $primary)) {

                        throw new PublicAlert('The request failed, please make sure arguments are correct.');

                    }

                    if (!self::commit()) {

                        throw new PublicAlert('Failed to commit the transaction. Please, try again.');

                    }

                    $json['rest'] = ['created' => $id];

                    break;
            }
        } catch (Throwable $e) {

            ErrorCatcher::safelyHandleThrowableAndExit($e);

        } finally {

            if (false === headers_sent()) {

                header('Content-Type: application/json');

            }

            print PHP_EOL . json_encode($json) . PHP_EOL;

        }

        return true;
    }

    /**
     * @param $stmt
     * @param $sql
     * @param array $group
     * @param bool $noHEX
     * @throws PublicAlert
     */
    private static function buildAggregate(array $stmt, string &$sql, array &$group = [], bool $noHEX = false): void
    {
        $name = '';

        if (count($stmt) === 3) {

            [$column, $aggregate, $name] = $stmt;

            if ($aggregate !== self::AS) {

                [$aggregate, $column, $name] = $stmt;

            }

        } else {

            if (count($stmt) !== 2) {

                throw new PublicAlert('An array in the GET Restful Request must be two values: [aggregate, column]');

            }

            [$aggregate, $column] = $stmt;    // todo - nested aggregates :: [$aggregate, string | array ]

        }

        if (!in_array($aggregate, $aggregateArray = [
            self::AS,
            self::MAX,
            self::MIN,
            self::SUM,
            self::HEX,
            self::UNHEX,
            self::DISTINCT,
            self::GROUP_CONCAT,
            self::COUNT
        ], true)) {

            throw new PublicAlert('The aggregate method in the GET request must be one of the following: '
                . implode(', ', $aggregateArray));

        }

        if (!self::validateInternalColumn(self::GET, $column, $aggregate)) {

            throw new PublicAlert('Could not validate the column "' . $column . '" in the request.'); // todo html entities

        }

        switch ($aggregate) {

            case self::AS:

                if (empty($name) && is_string($name)) {

                    throw new PublicAlert('The third argument provided to the AS select aggregate must not be empty and equal a string.');

                }

                if (!$noHEX && self::$compiled_PDO_validations[$column][0] === 'binary') {

                    $sql = "HEX($column) AS $name," . $sql;

                } else {

                    $sql = "$column AS $name, " . $sql;

                }

                $sql = rtrim($sql, ', ');

                break;

            case self::GROUP_CONCAT:

                if (!$noHEX && self::$compiled_PDO_validations[$column][0] === 'binary') {

                    $sql = "GROUP_CONCAT(DISTINCT HEX($column) ORDER BY $column ASC SEPARATOR ',') AS " . self::$compiled_valid_columns[$column] . ', ' . $sql;

                } else {

                    $sql = "GROUP_CONCAT(DISTINCT($column) ORDER BY $column ASC SEPARATOR ',') AS " . self::$compiled_valid_columns[$column] . ', ' . $sql;

                }

                $sql = rtrim($sql, ', ');

                break;

            case self::DISTINCT:

                if (!$noHEX && self::$compiled_PDO_validations[$column][0] === 'binary') {

                    $sql = "$aggregate HEX($column) as " . self::$compiled_valid_columns[$column] . ', ' . $sql;

                    $group[] = self::$compiled_valid_columns[$column];

                } else {

                    $sql = "$aggregate($column), $sql";

                    $group[] = $column;

                }

                $sql = rtrim($sql, ', ');

                break;

            default:

                if ($name === '') {

                    $sql .= "$aggregate($column)";

                } else {

                    $sql .= "$aggregate($column) as $name";

                }

                $group[] = $column;

        }

    }

    protected static function remove(array &$remove, array $argv, array $primary = null): bool
    {
        try {

            self::startRest(self::DELETE, $remove, $argv, $primary);

            $pdo = self::database();

            $emptyPrimary = null === $primary || [] === $primary;

            if (static::CARBON_CARBONS_PRIMARY_KEY && false === $emptyPrimary) {

                $primary = is_string(static::PRIMARY)
                    ? $primary[static::PRIMARY]
                    : $primary;

                return Carbons::Delete($remove, $primary, $argv);
            }

            if (false === self::$allowFullTableDeletes && true === $emptyPrimary && array() === $argv) {

                return self::signalError('When deleting from restful tables a primary key or where query must be provided.');

            }

            $query_database_name = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';

            $table_name = $query_database_name . static::TABLE_NAME;

            if (static::CARBON_CARBONS_PRIMARY_KEY) {

                if (is_array(static::PRIMARY)) {
                    throw new PublicAlert('Tables which use carbon for indexes should not have composite primary keys.');
                }

                $table_prefix = static::TABLE_PREFIX === Carbons::TABLE_PREFIX ? '' : static::TABLE_PREFIX;

                $sql = self::DELETE . ' c FROM ' . $query_database_name . $table_prefix . 'carbon_carbons c 
                JOIN ' . $table_name . ' on c.entity_pk = ' . static::PRIMARY;


                if (false === self::$allowFullTableDeletes || !empty($argv)) {
                    $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
                }

            } else {

                $sql = self::DELETE . ' FROM ' . $table_name;

                if (false === self::$allowFullTableDeletes && $emptyPrimary && empty($argv)) {
                    return self::signalError('When deleting from restful tables a primary key or where query must be provided. This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');
                }

                if (is_array(static::PRIMARY)) {

                    $primaryIntersect = count(array_intersect_key($primary, static::PRIMARY));

                    $primaryCount = count($primary);

                    if ($primaryCount !== $primaryIntersect) {
                        return self::signalError('The keys provided to table ' . $table_name . ' was not a subset of (' . implode(', ', static::PRIMARY) . '). Only primary keys associated with the root table requested, thus not joined tables, are allowed.');
                    }

                    if (false === self::$allowFullTableDeletes && $primaryIntersect !== count(static::PRIMARY)) {

                        return self::signalError('You must provide all primary keys ('
                            . implode(', ', static::PRIMARY)
                            . '). This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                    }

                    $argv = array_merge($argv, $primary);

                } elseif (is_string(static::PRIMARY) && !$emptyPrimary) {

                    $argv = array_merge($argv, $primary);

                }

                $where = self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);

                $emptyWhere = empty($where);

                if ($emptyWhere && false === self::$allowFullTableDeletes) {
                    return self::signalError('The where condition provided appears invalid.');
                }

                if (false === $emptyWhere) {
                    $sql .= ' WHERE ' . $where;
                }
            }

            if (!$pdo->inTransaction()) {

                $pdo->beginTransaction();

            }

            self::jsonSQLReporting(func_get_args(), $sql);

            self::postpreprocessRestRequest($sql);

            $stmt = $pdo->prepare($sql);

            self::bind($stmt);

            if (!$stmt->execute()) {

                self::completeRest();

                return self::signalError('The REST generated PDOStatement failed to execute with error :: '
                    . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            }

            $remove = [];

            self::prepostprocessRestRequest($remove);

            if (self::$commit && !Database::commit()) {

                return self::signalError('Failed to store commit transaction on table {{TableName}}');

            }

            self::postprocessRestRequest($remove);

            self::completeRest();

            return true;
        } catch (Throwable $e) {

            ErrorCatcher::safelyHandleThrowableAndExit($e);

        }

    }

    protected static function select(array &$return, array $argv, array $primary = null): bool
    {

        try {

            self::startRest(self::GET, $return, $argv, $primary);

            $pdo = self::database();

            $sql = self::buildSelectQuery($primary, $argv, static::QUERY_WITH_DATABASE ? static::DATABASE : '', $pdo);

            self::jsonSQLReporting(func_get_args(), $sql);

            self::postpreprocessRestRequest($sql);

            $stmt = $pdo->prepare($sql);

            self::bind($stmt);

            if (!$stmt->execute()) {
                self::completeRest();
                return self::signalError('The REST generated PDOStatement failed to execute with error :: '
                    . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            }

            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (is_array(static::PRIMARY)) {
                if ((null !== $primary && [] !== $primary)
                    || (isset($argv[self::PAGINATION][self::LIMIT])
                        && $argv[self::PAGINATION][self::LIMIT] === 1
                        && count($return) === 1)) {
                    $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
                }
            } elseif (is_string(static::PRIMARY)) {
                if ((null !== $primary && '' !== $primary) || (isset($argv[self::PAGINATION][self::LIMIT]) && $argv[self::PAGINATION][self::LIMIT] === 1 && count($return) === 1)) {
                    $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
                }
            } else {
                if (isset($argv[self::PAGINATION][self::LIMIT]) && $argv[self::PAGINATION][self::LIMIT] === 1 && count($return) === 1) {
                    $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
                }
            }

            foreach (static::JSON_COLUMNS as $key) {
                if (array_key_exists($key, $return)) {
                    $return[$key] = json_decode($return[$key], true);
                }
            }

            self::postprocessRestRequest($return);

            self::completeRest();

            return true;

        } catch (Throwable $e) {

            ErrorCatcher::safelyHandleThrowableAndExit($e);

        }

    }

    protected static function updateReplace(array &$returnUpdated, array $argv = [], array $primary = null): bool
    {
        try {

            self::startRest(self::PUT, $returnUpdated, $argv, $primary);

            $replace = false;

            $where = [];

            if (array_key_exists(self::WHERE, $argv)) {

                $where = $argv[self::WHERE];

                unset($argv[self::WHERE]);
            }

            if (array_key_exists(self::REPLACE, $argv)) {

                $replace = true;

                $argv = $argv[self::REPLACE];

            } else if (array_key_exists(self::UPDATE, $argv)) {

                $argv = $argv[self::UPDATE];

            }

            if (null === static::PRIMARY) {

                if (false === self::$allowFullTableUpdates && empty($where)) {

                    return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No WHERE attribute given. To bypass this set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events, or just directly before this request.');

                }

                if (empty($argv)) {

                    return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No UPDATE attribute given.');

                }

            } else {

                $emptyPrimary = null === $primary || [] === $primary;

                if (false === $replace && false === self::$allowFullTableUpdates && $emptyPrimary) {

                    return self::signalError('Restful tables which have a primary key must be updated by its primary key. To bypass this set you may set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events.');

                }

                if (is_array(static::PRIMARY)) {

                    if (false === self::$allowFullTableUpdates || !$emptyPrimary) {

                        if (count(array_intersect_key($primary, static::PRIMARY)) !== count(static::PRIMARY)) {

                            return self::signalError('You must provide all primary keys (' . implode(', ', static::PRIMARY) . ').');

                        }

                        $where = array_merge($argv, $primary);

                    }

                } elseif (!$emptyPrimary) {

                    $where = $primary;

                }
            }

            foreach ($argv as $key => &$value) {

                if (false === array_key_exists($key, self::$compiled_PDO_validations)) {

                    return self::signalError("Restful table could not update column $key, because it does not appear to exist. Please re-run RestBuilder if you believe this is incorrect.");

                }

                $op = self::EQUAL;

                if (false === self::validateInternalColumn(self::PUT, $key, $op, $value)) {

                    return self::signalError('Your custom restful api validations caused the request to fail on column \'{{TableName}}.{{name}}\'.');

                }
            }
            unset($value);

            $update_or_replace = $replace ? self::REPLACE : self::UPDATE;

            $sql = $update_or_replace . ' '
                . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                . static::TABLE_NAME . ' SET ';

            $set = '';

            foreach ($argv as $fullName => $value) {
                $shortName = static::COLUMNS[$fullName];
                $set .= "$fullName=" .
                    ('binary' === self::$compiled_PDO_validations[$fullName][self::MYSQL_TYPE]
                        ? "UNHEX(:$shortName),"
                        : ":$shortName,");

            }

            $sql .= substr($set, 0, -1);

            $pdo = self::database();

            if (!$pdo->inTransaction()) {

                $pdo->beginTransaction();

            }

            if (true === $replace) {

                if (!empty($where)) {

                    return self::signalError('Replace queries may not be given a where clause. Use Put instead.');

                }

            } else if (false === self::$allowFullTableUpdates || !empty($where)) {

                if (empty($where)) {
                    throw new PublicAlert('The where clause is required but has been detected as empty. Arguments were :: ' . json_encode(func_get_args(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
                }

                $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::PUT, $where, $pdo);

            }

            self::jsonSQLReporting(func_get_args(), $sql);

            self::postpreprocessRestRequest($sql);

            $stmt = $pdo->prepare($sql);

            foreach (static::COLUMNS as $fullName => $shortName) {

                if (array_key_exists($fullName, $argv)) {

                    $op = self::EQUAL;

                    if (!self::validateInternalColumn(self::PUT, $fullName, $op, $value)) {

                        return self::signalError("Your custom restful api validations caused the request to fail on column '$fullName'.");

                    }

                    if ('' === static::PDO_VALIDATION[$fullName][self::MAX_LENGTH]) { // does length exist

                        $value = static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] === 'json'
                            ? json_encode($argv[$fullName])
                            : $argv[$fullName];

                        $stmt->bindValue(":$shortName", $value, static::PDO_VALIDATION[$fullName][self::PDO_TYPE]);

                    } else {

                        $stmt->bindParam(":$shortName", $argv[$fullName],
                            static::PDO_VALIDATION[$fullName][self::PDO_TYPE],
                            (int) static::PDO_VALIDATION[$fullName][self::MAX_LENGTH]);
                    }
                }
            }

            self::bind($stmt);

            if (!$stmt->execute()) {

                self::completeRest();

                return self::signalError('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            }

            if (!$stmt->rowCount()) {

                return self::signalError('CarbonPHP failed to find the target row during ('. $update_or_replace . ') on table (' . static::TABLE_NAME . ').');

            }

            $argv = array_combine(
                array_map(
                    static fn($k) => str_replace(static::TABLE_NAME . '.', '', $k),
                    array_keys($argv)
                ),
                array_values($argv)
            );

            $returnUpdated = array_merge($returnUpdated, $argv);

            self::prepostprocessRestRequest($returnUpdated);

            if (self::$commit && !Database::commit()) {

                return self::signalError('Failed to store commit transaction on table {{TableName}}');

            }

            self::postprocessRestRequest($returnUpdated);

            self::completeRest();

            return true;

        } catch (Throwable $e) {

            ErrorCatcher::safelyHandleThrowableAndExit($e);

        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed|string|int
     */
    protected static function insert(array $data = [])
    {
        try {

            self::startRest(self::POST, [], $data);

            foreach ($data as $columnName => $postValue) {
                if (!array_key_exists($columnName, static::COLUMNS)) {
                    return self::signalError("Restful table could not post column $columnName, because it does not appear to exist.");
                }
            }

            $keys = $pdo_values = '';

            foreach (static::COLUMNS as $fullName => $shortName) {

                if (static::PDO_VALIDATION[$fullName][self::SKIP_COLUMN_IN_POST] ?? false) {

                    continue;

                }

                $keys .= "$shortName, ";

                $pdo_values .= 'binary' === static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] ? "UNHEX(:$shortName), " : ":$shortName, ";

            }

            $keys = rtrim($keys, ', ');

            $pdo_values = rtrim( $pdo_values, ', ');

            $sql = self::INSERT . ' INTO '
                . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                . static::TABLE_NAME . " ($keys) VALUES ($pdo_values)";

            $primaryBinary = is_string(static::PRIMARY) && 'binary' === static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE] ?? false;

            if ($primaryBinary) {

                $pdo = self::database();

                if (!$pdo->inTransaction()) {

                    $pdo->beginTransaction();

                }

            }

            self::jsonSQLReporting(func_get_args(), $sql);

            self::postpreprocessRestRequest($sql);

            $pdo ??= self::database();

            $stmt = $pdo->prepare($sql);

            $op = self::EQUAL;

            foreach (static::PDO_VALIDATION as $fullName => $info) {

                if ($info[self::SKIP_COLUMN_IN_POST] ?? false) {

                    if (array_key_exists($fullName, $data)
                        && self::CURRENT_TIMESTAMP === ($info[self::DEFAULT_POST_VALUE] ?? '')) {

                        return self::signalError("The column $fullName is set to default to CURRENT_TIMESTAMP. The Rest API does not allow POST requests with columns explicitly set whose default is CURRENT_TIMESTAMP. You can remove to the default in MySQL or the column $fullName from the request.");

                    }

                    continue;

                }

                $shortName = static::COLUMNS[$fullName];

                if ($fullName === static::PRIMARY
                    || (is_array(static::PRIMARY)
                        && array_key_exists($fullName, static::PRIMARY))) {

                    $data[$fullName] ??= false;

                    if ($data[$fullName] === false) {

                        $data[$fullName] = static::CARBON_CARBONS_PRIMARY_KEY
                            ? self::beginTransaction(self::class, $data[self::DEPENDANT_ON_ENTITY] ?? null)
                            : self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];

                    } else if (false === self::validateInternalColumn(self::POST, $fullName, $op, $data[$fullName])) {

                        return self::signalError("Your custom restful api validations for your primary key caused the request to fail on column '$fullName'.");

                    }

                    /**
                     * @todo - hex / unhex length conversion on any binary data
                     * @link https://stackoverflow.com/questions/28251144/inserting-and-selecting-uuids-as-binary16
                     * @link https://www.php.net/ChangeLog-8.php
                     * @notice PDO type validation has a bug until 8
                    **/
                    if (static::CARBON_CARBONS_PRIMARY_KEY) {

                        $maxLength = 16;

                    } else {

                        $maxLength = $info[self::MAX_LENGTH] === '' ? null : (int) $info[self::MAX_LENGTH];

                    }

                    $stmt->bindParam(":$shortName", $data[$fullName], $info[self::PDO_TYPE],
                        $maxLength);

                } elseif ('json' === $info[self::MYSQL_TYPE]) {

                    if (false === array_key_exists($fullName, $data)) {

                        return self::signalError("The column $fullName is set to not null and has no default value. It must exist in the request and was not found in the one sent.");

                    }

                    if (!self::validateInternalColumn(self::POST, $fullName, $op, $data[$fullName])) {

                        return self::signalError("Your custom restful api validations caused the request to fail on json column '$fullName'.");

                    }

                    if (false === is_string($data[$fullName])) {

                        $data[$fullName] = json_encode($data[$fullName]);

                        if (false === $data[$fullName]) {

                            return self::signalError("The column $fullName failed to be json encoded.");

                        }

                    }

                    $stmt->bindValue(":$shortName", $data[$fullName], $info[self::PDO_TYPE]);

                } elseif (array_key_exists(self::DEFAULT_POST_VALUE, $info)) {

                    $data[$fullName] ??= $info[self::DEFAULT_POST_VALUE];

                    if (false === self::validateInternalColumn(self::POST, $fullName, $op, $data[$fullName], $data[$fullName] === $info[self::DEFAULT_POST_VALUE])) {

                        return self::signalError("Your custom restful api validations caused the request to fail on column '$fullName'. (has default)");

                    }

                    $stmt->bindValue(":$shortName", $data[$fullName], $info[self::PDO_TYPE]);

                } else {

                    if (false === array_key_exists($fullName, $data)) {

                        return self::signalError("Required argument '$fullName' is missing from the request and has no default value.");

                    }

                    if (false === self::validateInternalColumn(self::POST, $fullName, $op, $data[$fullName], array_key_exists(self::DEFAULT_POST_VALUE, $info) ? $data[$fullName] === $info[self::DEFAULT_POST_VALUE] : false)) {

                        return self::signalError("Your custom restful api validations caused the request to fail on required column '$fullName'.");

                    }

                    $stmt->bindParam(":$shortName", $data[$fullName], $info[self::PDO_TYPE], $info[self::MAX_LENGTH] === '' ? null : (int) $info[self::MAX_LENGTH]);

                }
                // end foreach bind
            }

            if (false === $stmt->execute()) {

                self::completeRest();

                return self::signalError('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            }

            if (static::AUTO_INCREMENT_PRIMARY_KEY || $primaryBinary) {

                /** @noinspection NotOptimalIfConditionsInspection */
                if (static::AUTO_INCREMENT_PRIMARY_KEY) {

                    $id = $pdo->lastInsertId();

                }

                $id ??= $data[static::PRIMARY];

                self::prepostprocessRestRequest($id);

                if (self::$commit && !Database::commit()) {

                    return self::signalError('Failed to store commit transaction on table ' . static::TABLE_NAME);

                }

                self::postprocessRestRequest($id);

                self::completeRest();

                return $id;
            }

            self::prepostprocessRestRequest();

            if (self::$commit && false === Database::commit()) {

                return self::signalError('Failed to commit transaction on table ' . static::TABLE_NAME);

            }

            self::postprocessRestRequest();

            self::completeRest();

            return true;
        } catch (Throwable $e) {

            ErrorCatcher::safelyHandleThrowableAndExit($e);

        }

        return false; // this will never be reached.

    }

    protected static function getCheckPrefix($table_prefix): void
    {

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        if ($prefix !== $table_prefix) {
            throw new PublicAlert("The tables prefix ($prefix) does not match the on found in your configuration.");
        }
    }

    /**
     * @param string|null $primary
     * @param array $argv
     * @param string $database
     * @param PDO|null $pdo
     * @param bool $noHEX
     * @return string
     * @throws PublicAlert
     */
    protected static function buildSelectQuery(array $primary = null, array $argv = [], string $database = '', PDO $pdo = null, bool $noHEX = false): string
    {
        if ($pdo === null) {

            $pdo = self::database();

        }

        $aggregate = false;

        $joinColumns = [];          // this speeds up the query, by skipping duplicate validations

        $group = [];

        $join = '';

        $sql = '';

        $validColumns = array_keys(static::PDO_VALIDATION);

        $get = $argv[self::SELECT] ?? $validColumns;

        $where = $argv[self::WHERE] ?? [];

        // build join
        if (array_key_exists(self::JOIN, $argv) && !empty($argv[self::JOIN])) {
            if (!is_array($argv[self::JOIN])) {
                throw new PublicAlert('The restful join field must be an array.');
            }

            foreach ($argv[self::JOIN] as $by => $tables) {

                $validJoins = [
                    self::INNER,
                    self::LEFT,
                    self::RIGHT,
                    self::FULL_OUTER,
                    self::LEFT_OUTER,
                    self::RIGHT_OUTER
                ];

                if (!in_array($by, $validJoins, true)) {
                    throw new PublicAlert('The restful inner join had an unknown error.');
                }

                foreach ($tables as $class => $stmt) {

                    $class = ucwords($class, '_');

                    if (
                        !class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                        && !class_exists($JoiningClass = static::CLASS_NAMESPACE . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                    ) {
                        throw new PublicAlert('A table ' . $JoiningClass . ' provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
                    }

                    $imp = array_map('strtolower', array_keys(class_implements($JoiningClass)));

                    /** @noinspection ClassConstantUsageCorrectnessInspection */
                    if (!in_array(strtolower(iRest::class), $imp, true)
                        && !in_array(strtolower(iRestfulReferences::class), $imp, true)
                        && !in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                        && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                        && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
                    ) {
                        throw new PublicAlert('Rest error, class/table exists in the restful generation folder which does not implement the correct interfaces. Please re-run rest generation.');
                    }
                    if (!is_array($stmt)) {
                        throw new PublicAlert("Rest error in the join stmt, the value of $JoiningClass is not an array.");
                    }
                }

                foreach ($tables as $class => $stmt) {

                    $class = ucwords($class, '_');

                    if (
                        !class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                        && !class_exists($JoiningClass = static::CLASS_NAMESPACE . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                    ) {
                        throw new PublicAlert('A table ' . $JoiningClass . ' provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
                    }

                    $table = $JoiningClass::TABLE_NAME;

                    /**
                     * Prefix is normally included in the table name variable.
                     * The table prefix is expected to be an empty string for all tables except carbon_ internals.
                     * The following check is so CarbonPHP internal tables can be compatible with table prefixing.
                     * It also ensures the table you are joining has been generated correctly for your current build.
                     */
                    self::getCheckPrefix($JoiningClass::TABLE_PREFIX);

                    $prefix = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';

                    $join .= ' ' . strtoupper($by) . ' JOIN ' . $prefix . $table . ' ON ' . self::buildBooleanJoinConditions(self::GET, $stmt, $pdo);

                }
            }
        }

        // pagination [self::PAGINATION][self::LIMIT]
        if (array_key_exists(self::PAGINATION, $argv) && !empty($argv[self::PAGINATION])) {    // !empty should not be in this block - I look all the time

            // setting the limit to null will cause no limit
            // I get tempted to allow 0 to symbolically mean the same thing, but 0 Limit is allowed in mysql
            // @link https://stackoverflow.com/questions/30269084/why-is-limit-0-even-allowed-in-mysql-select-statements
            if (array_key_exists(self::LIMIT, $argv[self::PAGINATION])
                && is_numeric($argv[self::PAGINATION][self::LIMIT])) {

                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {

                    if ($argv[self::PAGINATION][self::PAGE] < 1) {

                        return self::signalError('The body of PAGINATION requires that PAGE attribute be no less than 1.');

                    }

                    $limit = ' LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $argv[self::PAGINATION][self::LIMIT]) . ',' . $argv[self::PAGINATION][self::LIMIT];

                } else {

                    $limit = ' LIMIT ' . $argv[self::PAGINATION][self::LIMIT];

                }

            } else {

                $limit = '';

            }

            $order = '';

            if (!empty($limit)
                || array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                $order = ' ORDER BY ';

                /** @noinspection NotOptimalIfConditionsInspection */
                if (array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                    if (is_array($argv[self::PAGINATION][self::ORDER])) {

                        $orderArray = [];

                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {

                            if (!in_array($sort, [self::ASC, self::DESC], true)) {

                                throw new PublicAlert('Restful order by failed to validate sorting method.');

                            }

                            if (!self::validateInternalColumn(self::GET, $item, $sort)) {

                                throw new PublicAlert('Failed to validate order by column.');

                            }

                            $orderArray[] = "$item $sort";

                        }

                        $order .= implode(', ', $orderArray);

                        unset($orderArray);

                    } else {

                        throw new PublicAlert('Rest query builder failed during the order pagination.');

                    }

                } else if (null !== static::PRIMARY && (is_string(static::PRIMARY) || is_array(static::PRIMARY))) {

                    $primaryColumn = is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0];

                    if ('binary' === static::PDO_VALIDATION[$primaryColumn][self::MYSQL_TYPE]) {

                        $order .= static::COLUMNS[$primaryColumn] . self::DESC;

                    } else {

                        $order .= $primaryColumn . ' ' . self::DESC;

                    }

                } elseif (!empty($limit)) {

                    throw new PublicAlert('An error was detected in your REST syntax regarding the limit. No order by clause was provided and no primary keys were detected in the main joining table to automagically set.');

                } else {

                    throw new PublicAlert('A unknown Restful error was encountered while compiling the order by statement.');

                }

            }

            $limit = "$order $limit";

        } else if (!$noHEX && static::PRIMARY !== null) {

            $limit = ' ORDER BY ' . (is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0]) . ' ASC LIMIT ' . (null === $primary ? '100' : '1');

        } else {

            $limit = '';

        }


        foreach ($get as $key => $column) {

            if (!empty($sql) && ',' !== $sql[-2]) {

                $sql .= ', ';

            }

            if (is_callable($column)) {

                $column = $column();

                if (strpos($column, '(SELECT ') === 0) {

                    $sql .= $column;

                    continue;

                }

            }

            if (is_array($column)) {

                self::buildAggregate($column, $sql, $group, $noHEX);

                continue;               // next foreach iteration

            }

            if (!is_string($column)) {
                // is this even possible at this point?
                throw new PublicAlert('C6 Rest client could not validate a column in the GET:[] request.');

            }

            if (self::$allowSubSelectQueries && strpos($column, '(SELECT ') === 0) {
                $sql .= $column;
                continue;
            }

            if (array_key_exists($column, $joinColumns)) {  // todo - we need to cache everywhere / for every / validateColumnName -- orrr should we // what about validations

                $sql .= $column;

                continue;

            }

            if (self::validateInternalColumn(self::GET,$column)) {

                $group[] = $column;

                if (!$noHEX && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    $sql .= "HEX($column) as " . self::$compiled_valid_columns[$column];        // get short tag

                    continue;

                }

                $sql .= $column;

                continue;

            }

            throw new PublicAlert('Could not validate a column (' . $column . ') in the (SELECT) request.');
        }

        /**
         * The table prefix is normally included int the TABLE_NAME constant.
         * The only time prefix is not an empty string is when a CarbonPHP table is
         **/
        self::getCheckPrefix(static::TABLE_PREFIX);

        // case sensitive select
        $sql = 'SELECT ' . $sql . ' FROM ' . ($database === '' ? '' : $database . '.') . static::TABLE_NAME . ' ' . $join;

        if (null === $primary) {

            if (!empty($where)) {

                $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::GET, $where, $pdo);

            }

        } elseif (empty(static::PRIMARY)) {

            throw new PublicAlert('Primary keys given in GET request to a table without a primary key. Please regenerate this class using RestBuilder.');

        } elseif (is_string(static::PRIMARY)) {

            if ('binary' === (static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE])) {

                $sql .= ' WHERE ' . static::PRIMARY . "=UNHEX(" . self::addInjection($primary[static::PRIMARY], $pdo) . ') ';

            } else {

                $sql .= ' WHERE ' . static::PRIMARY . "=" . self::addInjection($primary[static::PRIMARY], $pdo, static::PDO_VALIDATION[static::PRIMARY]) . ' ';

            }

            if (!empty($where)) {

                throw new PublicAlert('Restful tables with a single primary key must not have WHERE values passed when the primary key is given. Table ' . static::class . ' was passed a non empty key `WHERE` to the arguments of GET.');

            }

        } elseif (is_array(static::PRIMARY)) {

            if (count($primary) !== count(static::PRIMARY)) {

                throw new PublicAlert('The table ' . static::class . ' was passed a subset of the required primary keys. When passing primary keys all must be present.');

            }

            $primaryKeys = array_keys($primary);

            if (!empty(array_intersect_key($primaryKeys, static::PRIMARY))) {

                throw new PublicAlert('The rest table ' . static::class . ' was passed the correct number of primary keys; however, the associated keys did not match the static::PRIMARY attribute.');

            }

            if (!empty($where)) {

                throw new PublicAlert('Restful tables selecting with primary keys must not have WHERE values passed when the primary keys are given. Table ' . static::class . ' was passed a non empty key `WHERE` to the arguments of GET.');

            }

            $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::GET, $primary, $pdo);

        } else {

            throw new PublicAlert('An unexpected error occurred while paring your primary key in ' . static::class . '. static::PRIMARY may only be a string, array, or null. You may need to regenerate with RestBuilder.');

        }

        if ($aggregate && !empty($group)) {

            $sql .= ' GROUP BY ' . implode(', ', $group) . ' ';

        }

        $sql .= $limit;

        return '(' . $sql . ')';
    }


    /**
     * @param string|null $primary
     * @param array $argv
     * @param string $as
     * @param PDO|null $pdo
     * @param string $database
     * @return callable
     */
    public static function subSelect($primary = null, array $argv = [], string $as = '', PDO $pdo = null, string $database = ''): callable
    {
        return static function () use ($primary, $argv, $as, $database, $pdo): string {

            self::$allowSubSelectQueries = true;

            self::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $primary, $argv, true);

            $sql = self::buildSelectQuery($primary, $argv, $database, $pdo, true);

            if (!empty($as)) {
                $sql = "$sql AS $as";
            }

            self::completeRest(true);

            return $sql;

        };
    }

    /**
     * It was easier for me to think of this in a recursive manner.
     * In reality we should limit our use of recursion php <= 8.^
     * @param string $method
     * @param array $set
     * @param PDO $pdo
     * @param string $booleanOperator
     * @return string
     * @throws PublicAlert
     */
    protected static function buildBooleanJoinConditions(string $method, array $set, PDO $pdo, $booleanOperator = 'AND'): string
    {
        $sql = '(';

        $addJoinNext = false;

        $addSingleConditionToJoin = static function (string $valueOne, string $operator, string $valueTwo) use ($method, $pdo, &$booleanOperator, &$sql): void {

            $key_is_custom = false === self::validateInternalColumn($method, $valueOne, $valueTwo);

            $value_is_custom = false === self::validateInternalColumn($method, $valueTwo, $valueOne);

            if ($key_is_custom && $value_is_custom) {

                throw new PublicAlert("Rest failed in as you have two custom columns ($valueOne && $valueTwo). This may mean you need to regenerate your rest tables or have misspellings in your request. Please uses dedicated constants.");

            }

            if (!$key_is_custom && !$value_is_custom) {

                $joinColumns[] = $valueOne; // todo - prefix by key?

                $joinColumns[] = $valueTwo;

                $sql .= '(' . $valueOne . $operator . $valueTwo . ") $booleanOperator ";

                return;

            }

            if ($value_is_custom) {
                $joinColumns[] = $valueOne;

                if (self::$allowSubSelectQueries && strpos($valueTwo, '(SELECT ') === 0) {

                    $sql .= "($valueOne $operator $valueTwo ) $booleanOperator ";

                    return;

                }

                if (self::$compiled_PDO_validations[$valueOne][self::MYSQL_TYPE] === 'binary') {

                    $sql .= "($valueOne $operator UNHEX(" . self::addInjection($valueTwo, $pdo) . ")) $booleanOperator ";

                    return;

                }

                $sql .= '(' . $valueOne . $operator . self::addInjection($valueTwo, $pdo) . ") $booleanOperator ";

                return;

            }

            // column is custom
            $joinColumns[] = $valueTwo;

            if (self::$compiled_PDO_validations[$valueTwo][self::MYSQL_TYPE] === 'binary') {

                $sql .= "($valueTwo $operator UNHEX(" . self::addInjection($valueOne, $pdo) . ")) $booleanOperator ";

                return;

            }

            $sql .= '(' . self::addInjection($valueOne, $pdo) . $operator . $valueTwo . ") $booleanOperator ";

        };

        foreach ($set as $column => $value) {

            if (is_callable($value)) {

                $value = $value();              // todo - validation and injection logic here.

            }

            if ($addJoinNext) {

                $sql .= " $booleanOperator ";

            }

            if (is_array($value)) {                         /// do we intemperate as a boolean switch or custom operation (w/ optional operation)

                $supportedOperators = implode('|', [
                    self::GREATER_THAN_OR_EQUAL_TO,
                    self::GREATER_THAN,
                    self::LESS_THAN_OR_EQUAL_TO,
                    self::LESS_THAN,
                    self::EQUAL,
                    self::EQUAL_NULL_SAFE,
                    self::NOT_EQUAL
                ]);

                switch (count($value)) {
                    case 2:
                        if (!array_key_exists(0, $value) || !is_string($value[0])
                            || !array_key_exists(1, $value) || !is_string($value[1])) {

                            $addJoinNext = true;

                            $sql .= self::buildBooleanJoinConditions($method, $value, $pdo, $booleanOperator === 'AND' ? 'OR' : 'AND');

                            break;

                        }

                        if (is_string($column) && !is_numeric($column)) {

                            if (!((bool)preg_match('#^' . $supportedOperators . '$#', $value[0]))) { // ie #^=|>=|<=$#

                                throw new PublicAlert('Restful column joins may only use one (=,>=, or <=).');

                            }

                            if (!is_string($value[1])) {

                                throw new PublicAlert('A value parsed during a boolean join condition was not correct. String expected as second value while creating aggregation.');

                            }

                            $addSingleConditionToJoin($column, $value[0], $value[1]);

                        } else {

                            $addSingleConditionToJoin($value[0], self::EQUAL, $value[1]);

                        }

                        break;

                    case 3:
                        if (!array_key_exists(0, $value) || !is_string($value[0])
                            || !array_key_exists(1, $value) || !is_string($value[1])
                            || !array_key_exists(2, $value) || !is_string($value[2])) {
                            $addJoinNext = true;
                            $sql .= self::buildBooleanJoinConditions($method, $value, $pdo, $booleanOperator === 'AND' ? 'OR' : 'AND');
                            break;
                        }

                        if (!is_string($value[0]) || !is_string($value[1]) || !is_string($value[2])) {

                            throw new PublicAlert('One or more of the array values provided in the restful JOIN condition are not strings.');

                        }

                        if (!((bool)preg_match('#^' . $supportedOperators . '$#', $value[1]))) { // ie #^=|>=|<=$#

                            throw new PublicAlert('Restful column joins may only use one (=,>=, or <=).');

                        }

                        $addSingleConditionToJoin($value[0], $value[1], $value[2]);

                        break;

                    default:
                        if (is_int($column)) {

                            $addJoinNext = true;

                            $sql .= self::buildBooleanJoinConditions($method, $value, $pdo, $booleanOperator === 'AND' ? 'OR' : 'AND');

                            break;
                        }

                        throw new PublicAlert('Restful joins across two tables must be populated with two or three array values with column names, or an appropriate joining operator and column names. An invalid value was seen :: "' . json_encode($value, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . '"');

                }

                // end switch
                continue;

            } // end is_array

            $addJoinNext = false;

            $addSingleConditionToJoin($column, self::EQUAL, $value);

        } // end foreach

        return preg_replace("/\s$booleanOperator\s?$/", '', $sql) . ')';
    }


    /**
     * @param $argv
     * @param $sql
     */
    public static function jsonSQLReporting($argv, $sql): void
    {
        global $json;
        if (!is_array($json)) {
            $json = [];
        }
        if (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = [
            $argv,
            $sql
        ];
    }

    /**
     * @param Route $route
     * @param string $prefix
     * @param string|null $namespace
     * @return Route
     * @throws PublicAlert
     */
    public static function MatchRestfulRequests(Route $route, string $prefix = '', string $namespace = null): Route
    {
        return $route->regexMatch(/** @lang RegExp */ '#' . $prefix . 'rest/([A-Za-z\_]{1,256})/?([^/]+)?#',
            static function (string $table, string $primary = null) use ($namespace): void {
                if ($namespace === null) {
                    Rest::ExternalRestfulRequestsAPI($table, $primary);
                    return;
                }
                Rest::ExternalRestfulRequestsAPI($table, $primary, $namespace);
            });
    }

    /**
     * It is most common for a user validation to use a rest request
     * @param string $method
     * @param array $return
     * @param string|null $primary
     * @param array $args
     * @param bool $subQuery
     * @throws PublicAlert
     */
    protected static function startRest(
        string $method,
        array $return,
        array &$args = null,
        &$primary = null,
        bool $subQuery = false): void
    {
        self::getCheckPrefix(static::TABLE_PREFIX);

        if (self::$REST_REQUEST_METHOD !== null) {
            self::$activeQueryStates[] = [
                self::$REST_REQUEST_METHOD,
                self::$REST_REQUEST_PRIMARY_KEY,
                self::$REST_REQUEST_PARAMETERS,
                self::$REST_REQUEST_RETURN_DATA,
                self::$VALIDATED_REST_COLUMNS,
                self::$compiled_valid_columns,
                self::$compiled_PDO_validations,
                self::$compiled_PHP_validations,
                self::$compiled_regex_validations,
                self::$externalRestfulRequestsAPI,
                self::$join_tables,
                self::$allowSubSelectQueries,
                self::$injection
            ];
            self::$allowSubSelectQueries = true;
            self::$externalRestfulRequestsAPI = false;
        }

        if ($subQuery) {
            self::$REST_REQUEST_METHOD = $method;
            self::$REST_REQUEST_PRIMARY_KEY = &$primary;
            self::$REST_REQUEST_PARAMETERS = &$args;
            self::$REST_REQUEST_RETURN_DATA = &$return;
            self::$allowSubSelectQueries = true;
        } else {
            self::$REST_REQUEST_METHOD = $method;
            self::$REST_REQUEST_PRIMARY_KEY = &$primary;
            self::$REST_REQUEST_PARAMETERS = &$args;
            self::$REST_REQUEST_RETURN_DATA = &$return;
            self::$VALIDATED_REST_COLUMNS = [];
            self::$compiled_valid_columns = [];
            self::$compiled_PDO_validations = [];
            self::$compiled_PHP_validations = [];
            self::$compiled_regex_validations = [];
            self::$join_tables = [];
            self::$allowSubSelectQueries = false;
        }

        self::gatherValidationsForRequest();

        self::preprocessRestRequest();

    }

    /**
     * This must be done even on failure.
     * @param bool $subQuery
     */
    protected static function completeRest(bool $subQuery = false): void
    {
        if (empty(self::$activeQueryStates)) {

            self::$REST_REQUEST_METHOD = null;
            self::$REST_REQUEST_PARAMETERS = [];
            self::$REST_REQUEST_PRIMARY_KEY = null;
            self::$VALIDATED_REST_COLUMNS = [];
            self::$compiled_valid_columns = [];
            self::$compiled_PDO_validations = [];
            self::$compiled_PHP_validations = [];
            self::$compiled_regex_validations = [];
            self::$externalRestfulRequestsAPI = false;     // this should only be done on completion
            self::$join_tables = [];
            self::$allowSubSelectQueries = false;          // this should only be done on completion
            self::$injection = [];

        } elseif ($subQuery) {
            [
                self::$REST_REQUEST_METHOD,
                self::$REST_REQUEST_PRIMARY_KEY,
                self::$REST_REQUEST_PARAMETERS,
                self::$REST_REQUEST_RETURN_DATA,
                self::$VALIDATED_REST_COLUMNS,
                self::$compiled_valid_columns,
                self::$compiled_PDO_validations,
                self::$compiled_PHP_validations,
                self::$compiled_regex_validations,
                self::$externalRestfulRequestsAPI,
                self::$join_tables,
            ] = array_pop(self::$activeQueryStates);
        } else {
            [
                self::$REST_REQUEST_METHOD,
                self::$REST_REQUEST_PRIMARY_KEY,
                self::$REST_REQUEST_PARAMETERS,
                self::$REST_REQUEST_RETURN_DATA,
                self::$VALIDATED_REST_COLUMNS,
                self::$compiled_valid_columns,
                self::$compiled_PDO_validations,
                self::$compiled_PHP_validations,
                self::$compiled_regex_validations,
                self::$externalRestfulRequestsAPI,
                self::$join_tables,
                self::$allowSubSelectQueries,
                self::$injection
            ] = array_pop(self::$activeQueryStates);
        }
    }

    public static function addInjection($value, PDO $pdo, array $pdo_column_validation = null): string
    {

        $inject = ':injection' . count(self::$injection);

        switch ($pdo_column_validation[1] ?? null) {

            case null:
            case PDO::PARAM_INT:
            case PDO::PARAM_STR: // bindValue will quote strings

                self::$injection[$inject] = $value;

                break;

            default:

                self::$injection[$inject] = $pdo->quote($value);        // boolean, string

        }

        return $inject;

    }

    public static function bind(PDOStatement $stmt): void
    {

        foreach (self::$injection as $key => $value) {

            $stmt->bindValue($key, $value);

        }

        self::$injection = [];

    }

    /**
     * @param mixed $args
     */
    public static function sortDump($args): void
    {
        sortDump($args);
    }

    public static function parseSchemaSQL(string $sql = null, string $engineAndDefaultCharset = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'): ?string
    {
        if (null === preg_replace('#AUTO_INCREMENT=\d+#i', 'AUTO_INCREMENT=0', $sql)) {

            ColorCode::colorCode('parseSchemaSQL preg_replace failed for sql ' . $sql, iColorCode::RED);

            return null;

        }

        if (null === $sql || false === preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=)#', $sql, $matches)) {

            ColorCode::colorCode('parseSchemaSQL preg_match_all failed for sql ' . $sql, iColorCode::RED);

            return null;

        }
        if (!($matches[0][0] ?? false)) {

            ColorCode::colorCode('Regex failed to match a schema.', iColorCode::RED);

            return null;

        }

        return $matches[0][0] . $engineAndDefaultCharset;

    }

    protected static function runValidations(array $php_validation, &...$rest): void
    {
        foreach ($php_validation as $key => $validation) {

            if (!is_int($key)) {
                // This would indicated a column value explicitly on pre or post method.

                continue;

            }

            if (!is_array($validation)) {

                throw new PublicAlert('Each PHP_VALIDATION should equal an array of arrays with [ call => method , structure followed by any additional arguments ]. Refer to Carbonphp.com for more information.');

            }

            $class = array_key_first($validation);          //  $class => $method

            $validationMethod = $validation[$class];

            unset($validation[$class]);

            if (!class_exists($class)) {

                throw new PublicAlert("A class reference in PHP_VALIDATION failed. Class ($class) not found.");

            }

            if (empty($rest)) {

                if (!empty(static::PRIMARY)) {

                    if (false === call_user_func_array([$class, $validationMethod],
                            [&self::$REST_REQUEST_PARAMETERS, ...$validation]
                        )) {

                        throw new PublicAlert('The global request validation failed, please make sure the arguments are correct.');

                    }

                } else {

                    $primaryKey = self::REST_REQUEST_PRIMARY_KEY;

                    // add primary key to custom validations request as it maybe passed outside the $argv
                    self::$REST_REQUEST_PARAMETERS[self::REST_REQUEST_PRIMARY_KEY] = &$primaryKey;

                    if (false === call_user_func_array([$class, $validationMethod],
                            [&self::$REST_REQUEST_PARAMETERS, ...$validation]
                        )) {

                        throw new PublicAlert('The global request validation failed, please make sure the arguments are correct.');

                    }

                    self::$REST_REQUEST_PRIMARY_KEY = $primaryKey;

                    unset(self::$REST_REQUEST_PARAMETERS[self::REST_REQUEST_PRIMARY_KEY]);

                }

            } else if (false === call_user_func_array([$class, $validationMethod], [&$rest, ...$validation])) {

                throw new PublicAlert('A column request validation failed, please make sure arguments are correct.');

            }

        }

    }

}