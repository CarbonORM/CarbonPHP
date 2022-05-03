<?php


namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\MySQL;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\Tables\History_Logs;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

abstract class Rest extends Database
{

    # mysql restful identifiers
    public const ADDDATE = 'ADDDATE';
    public const ADDTIME = 'ADDTIME';
    public const AS = 'AS';
    public const ASC = 'ASC';
    public const BETWEEN = 'BETWEEN';
    public const CONVERT_TZ = 'CONVERT_TZ';
    public const COUNT = 'COUNT';
    public const COUNT_ALL = 'COUNT_ALL';
    public const CURRENT_DATE = 'CURRENT_DATE';
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    public const DAY = 'DAY';
    public const DAY_HOUR = 'DAY_HOUR';
    public const DAY_MICROSECOND = 'DAY_MICROSECOND';
    public const DAY_MINUTE = 'DAY_MINUTE';
    public const DAY_SECOND = 'DAY_SECOND';
    public const DAYNAME = 'DAYNAME';
    public const DAYOFMONTH = 'DAYOFMONTH';
    public const DAYOFWEEK = 'DAYOFWEEK';
    public const DAYOFYEAR = 'DAYOFYEAR';
    public const DATE = 'DATE';
    public const DATE_ADD = 'DATE_ADD';
    public const DATEDIFF = 'DATEDIFF';
    public const DATE_SUB = 'DATE_SUB';
    public const DATE_FORMAT = 'DATE_FORMAT';
    public const DESC = 'DESC'; // not case sensitive but helpful for reporting to remain uppercase
    public const DISTINCT = 'DISTINCT';
    public const EXTRACT = 'EXTRACT';
    public const EQUAL = '=';
    public const EQUAL_NULL_SAFE = '<=>';
    public const FULL_OUTER = 'FULL_OUTER';
    public const FROM_DAYS = 'FROM_DAYS';
    public const FROM_UNIXTIME = 'FROM_UNIXTIME';
    public const GET_FORMAT = 'GET_FORMAT';
    public const GREATER_THAN = '>';
    public const GROUP_BY = 'GROUP_BY';             // js // http get will convert the space so _ explicitly
    public const GROUP_CONCAT = 'GROUP_CONCAT';
    public const GREATER_THAN_OR_EQUAL_TO = '>=';
    public const HAVING = 'HAVING';
    public const HEX = 'HEX';
    public const HOUR = 'HOUR';
    public const HOUR_MICROSECOND = 'HOUR_MICROSECOND';
    public const HOUR_SECOND = 'HOUR_SECOND';
    public const HOUR_MINUTE = 'HOUR_MINUTE';
    public const MICROSECOND = 'MICROSECOND';
    public const MINUTE = 'MINUTE';
    public const MINUTE_MICROSECOND = 'MINUTE_MICROSECOND';
    public const MINUTE_SECOND = 'MINUTE_SECOND';
    public const MONTH = 'MONTH';
    public const QUARTER = 'QUARTER';
    public const SECOND = 'SECOND';
    public const SECOND_MICROSECOND = 'SECOND_MICROSECOND';
    public const WEEK = 'WEEK';
    public const YEAR = 'YEAR';
    public const YEAR_MONTH = 'YEAR_MONTH';
    public const IN = 'IN';
    public const INNER = 'INNER';
    public const INTERVAL = 'INTERVAL';
    public const INSERT = 'INSERT';
    public const JOIN = 'JOIN';
    public const LEFT = 'LEFT';
    public const LOCALTIME = 'LOCALTIME';
    public const LOCALTIMESTAMP = 'LOCALTIMESTAMP';
    public const MAKEDATE = 'MAKEDATE';
    public const MAKETIME = 'MAKETIME';
    public const MONTHNAME = 'MONTHNAME';
    public const LEFT_OUTER = 'LEFT_OUTER';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL_TO = '<=';
    public const LIKE = 'LIKE';
    public const NOT_LIKE = 'NOT_LIKE';
    public const LIMIT = 'LIMIT';
    public const MIN = 'MIN';
    public const MAX = 'MAX';
    public const NOW = 'NOW';
    public const NOT_EQUAL = '<>';
    public const NOT_IN = 'NOT_IN';
    public const ORDER = 'ORDER';
    public const PAGE = 'PAGE';
    public const PAGINATION = 'PAGINATION';
    public const PERIOD_DIFF = 'PERIOD_DIFF';
    public const REPLACE = 'REPLACE INTO';
    public const RIGHT = 'RIGHT';
    public const RIGHT_OUTER = 'RIGHT_OUTER';
    public const SELECT = 'SELECT';
    public const STR_TO_DATE = 'STR_TO_DATE';
    public const SUBDATE = 'SUBDATE';
    public const SUBTIME = 'SUBTIME';
    public const SYSDATE = 'SYSDATE';
    public const TIME = 'TIME';
    public const TIME_FORMAT = 'TIME_FORMAT';
    public const TIME_TO_SEC = 'TIME_TO_SEC';
    public const TIMEDIFF = 'TIMEDIFF';
    public const TIMESTAMP = 'TIMESTAMP';
    public const TIMESTAMPADD = 'TIMESTAMPADD';
    public const TIMESTAMPDIFF = 'TIMESTAMPDIFF';
    public const TO_DAYS = 'TO_DAYS';
    public const TO_SECONDS = 'TO_SECONDS';
    public const UNIX_TIMESTAMP = 'UNIX_TIMESTAMP';
    public const UTC_DATE = 'UTC_DATE';
    public const UTC_TIME = 'UTC_TIME';
    public const UTC_TIMESTAMP = 'UTC_TIMESTAMP';
    public const WEEKDAY = 'WEEKDAY';
    public const WEEKOFYEAR = 'WEEKOFYEAR';
    public const YEARWEEK = 'YEARWEEK';
    public const SUM = 'SUM';
    public const TRANSACTION_TIMESTAMP = 'TRANSACTION_TIMESTAMP';
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

    public const  SQL_VERSION_PREG_REPLACE = [
        /** @lang PhpRegExp */
        '#bigint\(\d+\)#' => 'bigint',
        /** @lang PhpRegExp */
        '#int\(\d+\)#' => 'int',
        /** @lang PhpRegExp */
        '#CHARACTER\sSET\s[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#COLLATE\s[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#datetime\sDEFAULT\sNULL#' => 'datetime',
        /** @lang PhpRegExp */
        '#\sON\sDELETE\sNO\sACTION#' => '',
        /** @lang PhpRegExp */
        '#AUTO_INCREMENT=\d+#' => '',
        /** @lang PhpRegExp */
        '#COLLATE=[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#CREATE\sTABLE\s`#' => 'CREATE TABLE IF NOT EXISTS `',
        /** @lang PhpRegExp */
        '#DEFAULT CHARSET=[A-Za-z0-9_]+#' => '',   // todo - I feel like this makes sense to flag but Actions
        /** @lang PhpRegExp */
        '#\s{2,}#' => ' ',
        /** @lang PhpRegExp */
        '#\s?,$#' => '',
        /** @lang PhpRegExp */
        '#\s?;$#' => '',
    ];

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
    public static bool $jsonReport = true;


    public static bool $aggregateSelectEncountered = false;
    public static bool $columnSelectEncountered = false;


    // False for external and internal requests by default. If a primary key exists you should always attempt to use it.
    public static bool $allowFullTableUpdates = false;
    public static bool $allowFullTableDeletes = false;

    // many other requirements must be met for this to apply, see how method signalError is defined
    public static bool $suppressErrorsAndReturnFalse = false;

    public const AGGREGATES = [
        self::ADDDATE,
        self::ADDTIME,
        self::DATE_ADD,
        self::DAYNAME,
        self::CONVERT_TZ,
        self::DAYOFMONTH,
        self::MAX,
        self::MIN,
        self::SUM,
        self::HEX,
        self::UNHEX,
        self::DISTINCT,
        self::NOW,
        self::GROUP_CONCAT,
        self::COUNT,
        self::AS,                // just in case were using  $column => [ self::AS, '' ]  syntax
        self::IN,
        self::INTERVAL,
        self::NOT_IN,            // Sub-select was handled earlier
    ];

    public const AGGREGATES_WITH_NO_PARAMETERS = [
        'COUNT(*)',
        self::COUNT_ALL,
        self::NOW,
    ];

    /**
     * @warning Rest constructor. Avoid this if possible.
     * If the value is modified in the original array or this `static` object it will be modified in the instantiated
     *  object by reference. The call time is slightly slower but your server will thank you when you start editing these
     *  values // recompiling to send. Really try to avoid looping through an array and using new. Refer to php manual
     *  as to why arrays will help your dev.
     * @link https://www.php.net/manual/en/features.gc.php
     * @link https://gist.github.com/nikic/5015323 -> https://www.npopov.com/2014/12/22/PHPs-new-hashtable-implementation.html
     * @param array $return
     */
    public function __construct(array &$return = [])
    {

        if ([] === $return) {

            return;

        }

        foreach ($return as $key => &$value) {

            $this->$key = &$value;

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

            throw new PublicAlert('Rest request denied by the PHP_VALIDATION\'s in the tables ORM. Remove DISALLOW_PUBLIC_ACCESS ' . (null !== $calledFrom ? "from ($calledFrom) " : '') . 'to gain privileges.');

        }

    }

    public static function autoTargetTableDirectory(): string
    {

        $composerJson = self::getComposerConfig();

        $tableNamespace = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::NAMESPACE] ??= "Tables\\";

        $tableDirectory = $composerJson['autoload']['psr-4'][$tableNamespace] ?? false;

        if (false === $tableDirectory) {

            throw new PublicAlert('Failed to parse composer json for ["autoload"]["psr-4"]["' . $tableNamespace . '"].');

        }

        return CarbonPHP::$app_root . $tableDirectory;

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

        $custom_prefix_carbon_table = $namespace . ucwords($fullyQualifiedRestClassName::TABLE_NAME, '_');        //  we're using table name and not class name as any different prefix, even a subset of the original, will be appended

        if (!class_exists($custom_prefix_carbon_table)) {

            throw new PublicAlert("Could not find the required class ($custom_prefix_carbon_table) in the user defined namespace ($namespace). This is required because a custom table prefix ($prefix) has been detected.");

        }

        if (($mustInterface === iRestSinglePrimaryKey::class || $mustInterface === null)
            && in_array(iRestSinglePrimaryKey::class, class_implements($fullyQualifiedRestClassName), true)) {

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


    public static function getRestNamespaceFromFileList(array $filePaths): string
    {

        foreach ($filePaths as $filename) {

            $fileAsString = file_get_contents($filename);

            $matches = [];

            if (!preg_match('#public const CLASS_NAMESPACE\s?=\s?\'(.*)\';#i', $fileAsString, $matches)) {
                continue;
            }

            if (array_key_exists(1, $matches)) {

                $classNamespace = $matches[1];

                break;

            }

        }

        if (empty($classNamespace)) {

            // filePaths should be from glob

            $tableDirectory = dirname($filePaths[0]);

            throw new PublicAlert("Failed to parse class namespace from files in ($tableDirectory). ");

        }

        return $classNamespace;

    }


    /**
     * @param string $message
     * @return bool
     * @throws PublicAlert
     */
    public static function signalError(string $message): bool
    {
        if (true === self::$suppressErrorsAndReturnFalse
            && false === self::$externalRestfulRequestsAPI
            && CarbonPHP::$is_running_production
            && false === CarbonPHP::$carbon_is_root
            && false === CarbonPHP::$test) {

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

    public static function runCustomCallables(&$column, string &$operator = null, &$value = null, bool $default = false): void
    {

        $method = self::$REST_REQUEST_METHOD;

        self::$VALIDATED_REST_COLUMNS[] = $column;  // to prevent recursion.

        if (null !== $value && $default === false) {

            // this is just a test for the bool
            $equalsValidColumn = self::validateInternalColumn($value, $column);

            if (false === $equalsValidColumn
                && array_key_exists($column, self::$compiled_regex_validations)
                && 1 > preg_match_all(self::$compiled_regex_validations[$column], $value, $matches, PREG_SET_ORDER)) {  // can return 0 or false

                throw new PublicAlert("The column ($column) was set to be compared with a value who did not pass the regex test. Please check this value and try again.");

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

    }

    public static function parseAggregateWithNoOperators(&$aggregate) : string {

        if ($aggregate === self::COUNT_ALL) {

            $aggregate = 'COUNT(*)';

        } else {

            $aggregate .= '()';
        }

        return $aggregate;

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
    public static function validateInternalColumn(&$column, string &$operator = null, &$value = null, bool $default = false): bool
    {

        if (!is_string($column) && !is_int($column)) {

            return false; // this may indicate a json column

        }

        if (in_array($column, self::AGGREGATES_WITH_NO_PARAMETERS, true)) {

            self::parseAggregateWithNoOperators($column);

            return true;

        }

        if (in_array(substr($column, 0, -2), self::AGGREGATES_WITH_NO_PARAMETERS, true)
            || in_array(substr($column, 0, -3), self::AGGREGATES_WITH_NO_PARAMETERS, true)) {

            $aggregateCharCheck = $column[-2];

            if ('*' !== $aggregateCharCheck
                && '(' !== $aggregateCharCheck) {

                throw new PublicAlert("An unexpected aggregate ($column) was encountered.");

            }

            return true;
        }

        if (array_key_exists($column, self::$compiled_PDO_validations)) {      // allow short tags

            self::runCustomCallables($column, $operator, $value, $default);

            return true;

        }

        if ($key = array_search($column, self::$compiled_valid_columns, true)) {

            $column = $key; // adds table name.

            self::runCustomCallables($column, $operator, $value, $default);

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

            if (!in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
            ) {

                $possibleImpl = implode('|', [
                    iRestMultiplePrimaryKeys::class, iRestSinglePrimaryKey::class, iRestNoPrimaryKey::class]);

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

                $singleTableValidations = $table::PHP_VALIDATION;

                if (property_exists($table, 'PHP_VALIDATION')) {

                    $tableInstantiated = new $table;

                    $singleTableValidations += $tableInstantiated->PHP_VALIDATION;

                }

                $php_validations[] = $singleTableValidations;

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

                throw new PublicAlert("The table ($namespace$mainTable) was not found in our generated api. Please try rerunning the rest builder and contact us if problems persist.");

            }

            $implementations = array_map('strtolower', array_keys(class_implements($namespace . $mainTable)));

            $requestTableHasPrimary = in_array(strtolower(iRestSinglePrimaryKey::class), $implementations, true)
                || in_array(strtolower(iRestMultiplePrimaryKeys::class), $implementations, true);

            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            switch ($method) {

                case self::GET:

                    if (array_key_exists(0, $_GET)) {

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

                        if (array_key_exists(self::HAVING, $_GET) && is_string($_GET[self::HAVING])) {

                            $_GET[self::HAVING] = json_decode($_GET[self::HAVING], true);

                        }

                        if (array_key_exists(self::GROUP_BY, $_GET) && is_string($_GET[self::GROUP_BY])) {

                            $_GET[self::GROUP_BY] = json_decode($_GET[self::GROUP_BY], true);

                        }

                    }

                    $args = $_GET;

                    break;

                case self::OPTIONS:

                    $_SERVER['REQUEST_METHOD'] = self::GET;

                case self::POST:
                case self::PUT:
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

                        throw new PublicAlert('The request failed (returned false), please make sure arguments are correct.');

                    }

                    $json['rest'] = $return;

                    if ($method === self::PUT) {

                        $json['rest']['updated'] = $primary ?? $args;

                    } elseif ($method === self::DELETE) {

                        $json['rest']['deleted'] = $primary ?? $args;

                    }

                    break;

                case self::POST:

                    if (!$id = call_user_func_array([$namespace . $mainTable, $methodCase], [&$_POST, $primary])) {

                        throw new PublicAlert('The request failed, please make sure arguments are correct.');

                    }

                    if (!self::commit()) {

                        throw new PublicAlert('Failed to commit the transaction. Please, try again.');

                    }

                    $json['rest'] = ['created' => $id];

                    break;
            }
        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

        } finally {

            if (false === headers_sent($filename, $line)) {

                header('Content-Type: application/json', true, 200);

            } else {

                $json['headers_sent:filename'] = $filename;

                $json['headers_sent:line'] = $line;

            }

            print PHP_EOL . json_encode($json) . PHP_EOL;

        }

        return true;
    }

    private static function isAggregateArray(array $array): bool
    {

        if (self::isSubSelectAggregation($array)) {

            return true;

        }


        if (false === array_key_exists(0, $array)
            || false === array_key_exists(1, $array)
            || count($array) > 3) {

            return false;

        }

        if (in_array($array[1], [self::IN, self::NOT_IN, self::AS], true)) {

            return true;

        }

        if (in_array($array[0], self::AGGREGATES, true)) {

            return true;

        }

        return false;

    }

    public static function is_assoc(array $array): bool
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;

    }


    public static function handleSubSelectAggregate(array $stmt): string
    {

        $tableName = $stmt[1];

        if (false === self::$allowSubSelectQueries) {

            throw new PublicAlert('Whoa, looks like a sub-select was encountered without `self::$allowSubSelectQueries` explicitly set to true. '
                . ' This is a security precaution and is recommend to only be set to true when explicitly needed. You should'
                . " consider doing this in the ($tableName::PHP_VALIDATION['PREPROCESS']) event cycle.");

        }

        if (false === defined("$tableName::CLASS_NAME")) {

            $tableNameFullTry = static::CLASS_NAMESPACE . $tableName;

            if (false === defined("$tableNameFullTry::CLASS_NAME")) {

                throw new PublicAlert("The restful sub-select failed as the table ($tableName) did not have member const ($tableName::CLASS_NAME) under the (" . static::CLASS_NAMESPACE . ') namespace.');

            }

            $tableName = $tableNameFullTry;

        }

        $primaryField = $tableName::PRIMARY;

        $primary = [];

        $argv = [];

        $as = '';

        $setLength = count($stmt);

        if (true === self::is_assoc($stmt)) {   // todo - php 8.1 check when we gain support

            throw new PublicAlert('The restful sub-select query has been detected to be an associative array.'
                . ' Associative array arrays can not (yet) be unpacked in PHP. Please remove the keys an allow php '
                . 'to manually assign them. The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')');

        }

        if (null === $primaryField) {

            switch ($setLength) {

                case 4:

                    [, , , $as] = $stmt;

                case 3:

                    [, , $argv] = $stmt;

                    if (false === is_array($argv)) {

                        throw new PublicAlert("The third argument passed to the restful sub-select was a not an array. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as].  The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    if (false === is_string($as)) {

                        throw new PublicAlert("The fourth argument passed  to the restful sub-select was a not a string. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as]. The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    break;

                default:

                    throw new PublicAlert('The restful sub-select set passed was not the correct length. The '
                        . "table ($tableName) has no primary keys so only three or four values are expected."
                        . " Arguments must match the signature [ SELECT, $tableName, \$argv = [], \$as = '' ] for sub-select"
                        . ' queries on tables which have no sql queries. The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')');

            }

        } else if (is_array($primaryField) || is_string($primaryField)) {

            $error_context = static function (string $tableName) use ($primaryField, $stmt) {

                $set = ' The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')';

                return is_array($primaryField) ? "The signature for tables with multiple column primary keys is as follows:: [ Rest::SELECT, '$tableName',  (array) \$primary, (array) \$argv, (string|optional) \$as]. $set"
                    : "The signature for tables with a single column primary key is as follows:: [ Rest::SELECT, '$tableName',  (string|int|array) \$primary, (array) \$argv, (string|optional) \$as]. Note: if an array is given it must be a key value with the key being the fully qualified `table.column` name associated with the primary key. $set";

            };

            switch ($setLength) {
                case 5:

                    [, , , , $as] = $stmt;

                case 4:

                    [, , $primary, $argv] = $stmt;

                    if ('' === $primary) {

                        $primary = null;

                    }

                    /** @noinspection NotOptimalIfConditionsInspection - whatever; really */
                    if (is_string($primaryField)
                        && (true === is_int($primary)
                            || true === is_string($primary))) {

                        $primary = [static::PRIMARY => $primary];

                    }

                    if (null !== $primary && false === is_array($primary)) {

                        throw new PublicAlert("The third argument passed to the restful sub-select was a not a { null, string, int, or array }. " . $error_context($tableName));

                    }

                    if (false === is_array($argv)) {

                        throw new PublicAlert("The fourth argument passed to the restful sub-select was a not an array. " . $error_context($tableName));

                    }

                    if (false === is_string($as)) {

                        throw new PublicAlert("The fourth argument passed to the restful sub-select was a not a string. " . $error_context($tableName));

                    }

                    break;

                default:

                    throw new PublicAlert('The restful sub-select set passed was not the correct length. (' . count($stmt) . ') '
                        . $error_context($tableName));

            }

        }

        // thus ( $tableName::CLASS_NAME === $tableName )
        // the following if from the original sub-select query, in this context xss could be dirty so this
        // should be set during validations explicitly by end user
        # self::$allowSubSelectQueries = true;

        $tableName::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $primary, $argv, true);

        $sql = $tableName::buildSelectQuery($primary, $argv, true);

        $sql = "($sql)";

        if (!empty($as)) {
            $sql = "$sql AS $as";
        }

        $tableName::completeRest(true);

        return $sql;

    }

    public static function isSubSelectAggregation(array $stmt): bool
    {
        return array_key_exists(0, $stmt)
            && array_key_exists(1, $stmt)
            && self::SELECT === $stmt[0];
    }

    public static function verifyAsValue($value): void
    {

        $validNameRegex = '#^[a-zA-Z_][a-zA-Z0-9_]*$#';

        if (false === preg_match($validNameRegex, $value)) {

            throw new PublicAlert("When using a RESTFUL (AS) aggregation the name ($value) value must match the regex ($validNameRegex)");

        }

    }

    /**
     * @param array $stmt
     * @param bool $isSubSelect
     * @return string
     * @throws PublicAlert
     */
    private static function buildAggregate(array $stmt, bool $isSubSelect = false): string
    {
        $name = '';

        if (self::isSubSelectAggregation($stmt)) {

            // todo - it is unclear if this is more an aggregate or column with respect to the group by
            return self::handleSubSelectAggregate($stmt);

        }

        $validateInternalOrAddInjection = static function (string $columnOrInjection, string $aggregate, &...$rest) {

            $value_is_custom = false === self::validateInternalColumn($columnOrInjection, $operator, $rest);

            if ($value_is_custom) {

                // this is just a technicality as name could be injected but also referenced in the query
                return self::addInjection($columnOrInjection, [self::PDO_TYPE => null]);

            }

            return $columnOrInjection;

        };

        self::$aggregateSelectEncountered = true;

        $stmtCount = count($stmt);

        if (4 === $stmtCount) {

            [$aggregate, $time, $zoneOne, $zoneTwo] = $stmt;

            if ($aggregate !== self::CONVERT_TZ) {

                throw new PublicAlert('Expected the CONVERT_TZ aggregate function as four arguments were encountered.');

            }

            return $aggregate .
                "({$validateInternalOrAddInjection($time, $aggregate, $zoneOne, $zoneTwo)},
                {$validateInternalOrAddInjection($zoneOne, $aggregate, $time, $zoneTwo)},
                {$validateInternalOrAddInjection($zoneTwo, $aggregate, $time, $zoneOne)})";

        }

        if (3 === $stmtCount) {

            [$column, $aggregate, $name] = $stmt;

            switch ($aggregate) {
                case self::AS:
                case self::IN:
                case self::INTERVAL:
                case self::NOT_IN:
                    break;
                default:
                    [$aggregate, $column] = $stmt;
            }

        } elseif (2 === $stmtCount) {

            [$aggregate, $column] = $stmt;    // todo - nested aggregates :: [$aggregate, string | array ]

        } else {

            if (count($stmt) !== 1) {

                throw new PublicAlert('A Restful array value in the aggregation must be at least two values: array( $aggregate, $column [, optional: $as] ) ');

            }

            [$aggregate] = $stmt;

            if (self::NOW !== $aggregate) {

                throw new PublicAlert("Restful request encountered an ($aggregate) aggregate function as the only member of the array.");

            }

            return $aggregate . '()';

        }

        if (false === in_array($aggregate, self::AGGREGATES, true)) {

            throw new PublicAlert('The aggregate ( ' . json_encode($stmt) . ') method in the GET request must be one of the following: '
                . implode(', ', self::AGGREGATES));

        }

        if (false === self::validateInternalColumn($column, $aggregate)) {

            throw new PublicAlert("The column value of ($column) caused validateInternalColumn to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

        }

        if ('' !== $name) {

            // this is just a technicality as name could be injected but also referenced in the query
            $name = self::addInjection($name, [self::PDO_TYPE => null]);

        }

        switch ($aggregate) {

            case self::AS:

                if (empty($name)) {

                    throw new PublicAlert("The third argument provided to the ($aggregate) select aggregate must not be empty and be a string.");

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "HEX($column) $aggregate $name";

                }

                return "$column $aggregate $name";

            case self::GROUP_CONCAT:

                if (empty($name)) {

                    $name = self::$compiled_valid_columns[$column];

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "GROUP_CONCAT(DISTINCT HEX($column) ORDER BY $column ASC SEPARATOR ',') AS $name";

                }

                return "GROUP_CONCAT(DISTINCT($column) ORDER BY $column ASC SEPARATOR ',') AS $name";

            case self::DISTINCT:

                if (empty($name)) {

                    $name = self::$compiled_valid_columns[$column];

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "$aggregate HEX($column) AS $name";

                }

                return "$aggregate($column) AS $name";

            default:

                if ($name === '') {

                    return "$aggregate($column)";

                }

                return "$aggregate($column) AS $name";

        }

    }

    protected static function remove(array &$remove, array $argv, array $primary = null): bool
    {

        do {

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

                    $sql = self::DELETE . ' c FROM ' . $query_database_name . $table_prefix . 'carbon_carbons c JOIN ' . $table_name . ' on c.entity_pk = ' . static::PRIMARY;

                    if (false === self::$allowFullTableDeletes
                        || !empty($argv)) {

                        $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($argv);
                    }

                } else {

                    $sql = self::DELETE . ' FROM ' . $table_name . ' ';

                    if (false === self::$allowFullTableDeletes
                        && $emptyPrimary
                        && empty($argv)) {

                        return self::signalError('When deleting from restful tables a primary key or where query must be provided. This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                    }

                    // todo - loosen logic
                    if (is_array(static::PRIMARY)) {

                        $primaryIntersect = count(array_intersect(array_keys($primary), static::PRIMARY));

                        $primaryCount = count($primary);

                        $actualPrimaryCount = count(static::PRIMARY);

                        if ($primaryCount !== $primaryIntersect) {

                            return self::signalError('The keys provided to table ' . $table_name . ' was not a subset of (' . implode(', ', static::PRIMARY) . '). Only primary keys associated with the root table requested, thus not joined tables, are allowed.');

                        }

                        // todo - complex join logic
                        if (false === self::$allowFullTableDeletes
                            && $actualPrimaryCount !== $primaryIntersect
                            && $actualPrimaryCount !== count(array_intersect(array_keys($argv[self::WHERE] ?? $argv), static::PRIMARY))) {

                            return self::signalError('You must provide all primary keys ('
                                . implode(', ', static::PRIMARY)
                                . '). This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                        }

                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $argv = array_merge($argv, $primary ?? []);
                        // todo - this is a good point. were looping and running and array merge..

                    } elseif (is_string(static::PRIMARY) && !$emptyPrimary) {

                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $argv = array_merge($argv, $primary);

                    }

                    $where = self::buildBooleanJoinedConditions($argv);

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

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    /**
     * This will terminate 99% of the time, but the 1% it wasn't you need to rerun what you try caught
     * as the error was just the database going away.
     * @param Throwable $e
     */
    private static function handleRestException(Throwable $e): void
    {

        if ($e instanceof PDOException) {

            // this most likely terminates (only on db resource drop will it continue < 1%)
            Database::TryCatchPDOException($e);

        } else {

            ErrorCatcher::generateLog($e);  // this terminates

        }

    }


    protected static function select(array &$return, array $argv, array $primary = null): bool
    {

        do {

            try {

                self::startRest(self::GET, $return, $argv, $primary);

                $pdo = self::database();

                if (null !== $primary && false === is_array($primary)) {

                    throw new PublicAlert('Looks like your restful validations changed the primary value to an invalid state.'
                        . ' The $primary field should be null or an array with the following syntax :: [ Table::EXAMPLE_COLUMN => "primary_key_string" ] '
                        . ' The value (' . json_encode($primary) . ') was instead received. ');

                }

                if (false === is_array($argv)) {

                    throw new PublicAlert('Looks like your restful validations changed the $argv value to an invalid state.'
                        . ' The $argv was not an array. Received :: (' . json_encode($argv) . ')');

                }

                $sql = self::buildSelectQuery($primary, $argv);

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

                    if ((null !== $primary && '' !== $primary)
                        || (isset($argv[self::PAGINATION][self::LIMIT])
                            && $argv[self::PAGINATION][self::LIMIT] === 1
                            && count($return) === 1)) {

                        $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;

                    }

                } else if (isset($argv[self::PAGINATION][self::LIMIT])
                    && $argv[self::PAGINATION][self::LIMIT] === 1
                    && count($return) === 1) {

                    $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;

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

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    protected static function updateReplace(array &$returnUpdated, array $argv = [], array $primary = null): bool
    {

        do {

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

                    if (false === self::$allowFullTableUpdates && [] === $where) {

                        return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No WHERE attribute given. To bypass this set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events, or just directly before this request.');

                    }

                    // todo - more validations on payload not empty
                    if (empty($argv)) {

                        return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No UPDATE attribute given.');

                    }

                } else {

                    $emptyPrimary = null === $primary || [] === $primary;

                    if (false === $replace
                        && false === self::$allowFullTableUpdates
                        && $emptyPrimary) {

                        return self::signalError('Restful tables which have a primary key must be updated by its primary key. To bypass this set you may set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events.');

                    }

                    if (is_array(static::PRIMARY)) {

                        if (false === self::$allowFullTableUpdates
                            || false === $emptyPrimary) {

                            $primary ??= [];

                            $primaryCount = count(static::PRIMARY);

                            $explicitPrimaryCount = count(array_intersect(array_keys($primary), static::PRIMARY));

                            if ($explicitPrimaryCount !== $primaryCount) {

                                $implicitPrimaryCount = count(array_intersect(array_keys($primary), static::PRIMARY));

                                if (($explicitPrimaryCount + $implicitPrimaryCount) !== $primaryCount) {

                                    return self::signalError('You must provide all primary keys (' . implode(', ', static::PRIMARY) . ').');

                                }
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

                    if (false === self::validateInternalColumn($key, $op, $value)) {

                        return self::signalError("Your custom restful api validations caused the request to fail on column ($key).");

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

                    $set .= " $fullName = " .
                        ('binary' === self::$compiled_PDO_validations[$fullName][self::MYSQL_TYPE]
                            ? "UNHEX(:$shortName) ,"
                            : ":$shortName ,");

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

                    $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($where);

                }

                self::jsonSQLReporting(func_get_args(), $sql);

                self::postpreprocessRestRequest($sql);

                $stmt = $pdo->prepare($sql);

                if (false === $stmt) {

                    return self::signalError("PDO failed to prepare the sql generated! ($sql)");

                }

                foreach (static::COLUMNS as $fullName => $shortName) {

                    if (array_key_exists($fullName, $argv)) {

                        $op = self::EQUAL;

                        if (false === self::validateInternalColumn($fullName, $op, $value)) {

                            return self::signalError("Your custom restful api validations caused the request to fail on column ($fullName).");

                        }

                        if ('' === static::PDO_VALIDATION[$fullName][self::MAX_LENGTH]) { // does length exist

                            $value = static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] === 'json'
                                ? json_encode($argv[$fullName])
                                : $argv[$fullName];


                            if (false === $stmt->bindValue(":$shortName", $value, static::PDO_VALIDATION[$fullName][self::PDO_TYPE])) {

                                return self::signalError("Failed to bind (:$shortName) with value ($value)");

                            }

                        } else if (false === $stmt->bindParam(":$shortName", $argv[$fullName],
                                static::PDO_VALIDATION[$fullName][self::PDO_TYPE],
                                (int)static::PDO_VALIDATION[$fullName][self::MAX_LENGTH])) {

                            return self::signalError("Failed to bind (:$shortName) with value ({$argv[$fullName]})");

                        }


                    }

                }

                self::bind($stmt);

                if (false === $stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

                }

                if (0 === $stmt->rowCount()) {

                    return self::signalError("MySQL failed to find the target row during ($update_or_replace) on "
                        . 'table (' . static::TABLE_NAME . ") while executing query ($sql). By default CarbonPHP passes "
                        . 'PDO::MYSQL_ATTR_FOUND_ROWS => true, to the PDO driver; aka return the number of found (matched) rows, '
                        . 'not the number of changed rows. Thus; if you have not manually updated these options, your issue is '
                        . 'the target row not existing.');

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

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    public static function has_string_keys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * @param array $post
     * @return mixed|string|int
     */
    protected static function insert(array &$post = [])
    {
        do {
            try {

                self::startRest(self::POST, [], $post);

                if ([] !== $post && true === self::has_string_keys($post)) {

                    $post = [

                        $post

                    ];

                }

                foreach ($post as $iValue) {

                    foreach ($iValue as $columnName => $postValue) {

                        if (false === array_key_exists($columnName, static::COLUMNS)) {

                            return self::signalError("Restful table (" . static::class . ") would not post column ($columnName), because the column does not appear to exist.");

                        }

                    }

                }

                $keys = '';

                $pdo_values = $bound_values = [];

                $rowsToInsert = count($post);

                $i = 0;

                do {

                    $pdo_values[$i] = '';

                    foreach (static::COLUMNS as $fullName => $shortName) {

                        if (static::PDO_VALIDATION[$fullName][self::SKIP_COLUMN_IN_POST] ?? false) {

                            continue;

                        }

                        if ($i === 0) {

                            $keys .= "$shortName, ";

                        }

                        $shortName .= $i;

                        $bound_values[] = $shortName;

                        $pdo_values[$i] .= 'binary' === static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] ? "UNHEX(:$shortName), " : ":$shortName, ";

                    }

                    $pdo_values[$i] = rtrim($pdo_values[$i], ', ');

                    ++$i;

                } while ($i < $rowsToInsert);

                $sql = self::INSERT . ' INTO '
                    . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                    . static::TABLE_NAME . ' ('
                    . rtrim($keys, ', ')
                    . ') VALUES ('
                    . implode('), (', $pdo_values) . ')';

                $primaryBinary = is_string(static::PRIMARY) && 'binary' === static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE] ?? false;

                if ($primaryBinary) {

                    $pdo = self::database();

                    if (false === $pdo->inTransaction()) {

                        $pdo->beginTransaction();

                    }

                }

                self::jsonSQLReporting(func_get_args(), $sql);

                self::postpreprocessRestRequest($sql);

                $pdo ??= self::database();

                $stmt = $pdo->prepare($sql);

                $op = self::EQUAL;

                $i = 0;

                do {

                    $post[$i] ??= [];

                    $iValue = &$post[$i];   // this allows you to get your binary keys if they were C6 enabled.

                    foreach (static::PDO_VALIDATION as $fullName => $info) {

                        if ($info[self::SKIP_COLUMN_IN_POST] ?? false) {

                            if (array_key_exists($fullName, $iValue)
                                && self::CURRENT_TIMESTAMP === ($info[self::DEFAULT_POST_VALUE] ?? '')) {

                                return self::signalError("The column ($fullName) is set to default to CURRENT_TIMESTAMP. The Rest API does not allow POST requests with columns explicitly set whose default is CURRENT_TIMESTAMP. You can remove to the default in MySQL or the column ($fullName) from the request.");

                            }

                            continue;

                        }

                        $shortName = static::COLUMNS[$fullName] . $i;

                        if (false === $key = array_search($shortName, $bound_values, true)) {

                            return self::signalError("An internal rest error has occurred where rest attempted binding ($shortName) which was in in the prepared sql ($sql)");

                        }

                        unset($bound_values[$key]);

                        if ($fullName === static::PRIMARY
                            || (is_array(static::PRIMARY)
                                && array_key_exists($fullName, static::PRIMARY))) {

                            $iValue[$fullName] ??= false;

                            if ($iValue[$fullName] === false) {

                                $iValue[$fullName] = static::CARBON_CARBONS_PRIMARY_KEY
                                    ? self::beginTransaction(self::class, $iValue[self::DEPENDANT_ON_ENTITY] ?? null)     // clusters should really use this
                                    : self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];

                            } else if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PublicAlert("The column value of ($fullName) caused custom restful api validations for (" . static::class . ") primary key to fail (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            /**
                             * I'm fairly confident the length attribute does nothing.
                             * @todo - hex / unhex length conversion on any binary data
                             * @link https://stackoverflow.com/questions/28251144/inserting-and-selecting-uuids-as-binary16
                             * @link https://www.php.net/ChangeLog-8.php
                             * @notice PDO type validation has a bug until 8
                             **/
                            $maxLength = $info[self::MAX_LENGTH] === '' ? null : (int)$info[self::MAX_LENGTH];

                            $stmt->bindParam(":$shortName",
                                $iValue[$fullName],
                                $info[self::PDO_TYPE],
                                $maxLength);

                        } elseif ('json' === $info[self::MYSQL_TYPE]) {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Table ('" . static::class . "') column ($fullName) is set to not null and has no default value. It must exist in the request and was not found in the one sent.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PublicAlert("Your tables ('" . static::class . "'), or joining tables, custom restful api validations caused the request to fail on json column ($fullName). Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            if (false === is_string($iValue[$fullName])) {

                                $json = json_encode($iValue[$fullName]);  // todo - is this over-validating?

                                if (false === $json && $iValue[$fullName] !== false) {

                                    return self::signalError("The column ($fullName) failed to be json encoded.");

                                }

                                $iValue[$fullName] = $json;

                                unset($json);

                            }

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } elseif (array_key_exists(self::DEFAULT_POST_VALUE, $info)) {

                            $iValue[$fullName] ??= $info[self::DEFAULT_POST_VALUE];

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE])) {

                                return self::signalError("Your custom restful table ('" . static::class . "') api validations caused the request to fail on column ($fullName)");

                            }

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } else {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Required argument ($fullName) is missing from the request to ('" . static::class . "') and has no default value.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], array_key_exists(self::DEFAULT_POST_VALUE, $info) ? $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE] : false)) {

                                return self::signalError("Your custom restful api validations for ('" . static::class . "') caused the request to fail on required column ($fullName).");

                            }

                            $stmt->bindParam(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE], $info[self::MAX_LENGTH] === '' ? null : (int)$info[self::MAX_LENGTH]);

                        }
                        // end foreach bind
                    }

                    ++$i;

                } while ($i < $rowsToInsert);

                if ([] !== $bound_values) {

                    // todo - link support forums
                    return self::signalError("The insert query ($sql) did not receive values for (" . implode(', ', $bound_values) . '). This is not expected, please open a ticket so we can fix this at (' . Documentation::GIT_SUPPORT . ').');

                }


                if (false === $stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute for (' . static::class . '), with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR));

                }

                # https://dev.mysql.com/doc/refman/5.6/en/information-functions.html#function_last-insert-id
                if (static::AUTO_INCREMENT_PRIMARY_KEY || $primaryBinary) {

                    /** @noinspection NotOptimalIfConditionsInspection */
                    if (static::AUTO_INCREMENT_PRIMARY_KEY) {

                        $post[0][static::PRIMARY] = $id = $pdo->lastInsertId();

                        if (1 < $rowsToInsert) {

                            PublicAlert::warning(<<<WARNING
                                                Auto increment keys used indiscriminately are a waste of the primary key access which is always the fastest way to get to a row in a table.
                                                Auto increment locks can and do impact concurrency and scalability of your database.
                                                In an HA replication environment using standard Async Replication, auto increment risks orphan rows during a master failure event.
                                                If you are lucky enough to need to scale writes beyond a single server and end up having to shard auto increment no longer produces unique keys.
                                                @quote @author John Schulz
                                                @source @reference @link https://blog.pythian.com/case-auto-increment-mysql/
                                                WARNING
                            );

                            PublicAlert::warning('CarbonPHP offers a scalable primary key solution using UUIDs. Please refer to the documentation.');

                            PublicAlert::success("The first key ($id) is the primary key id of the first row inserted in the request. Please understand implications of sharded environments. Refer to link :: https://dev.mysql.com/doc/refman/5.6/en/information-functions.html#function_last-insert-id");

                        }

                    } else {

                        $id = $post[0][static::PRIMARY];

                    }

                    if (null === $id) {

                        return self::signalError("Failed to parse the id of the first inserted element after running ($sql); (" . json_encode($post) . ')');

                    }

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

                    return self::signalError('Failed to commit transaction on table ' . static::class);

                }

                self::postprocessRestRequest();

                self::completeRest();

                return true;

            } catch (Throwable $e) {

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    /**
     * @throws PublicAlert
     */
    protected static function checkPrefix($table_prefix): void
    {

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        if ($prefix !== $table_prefix) {

            throw new PublicAlert("The tables prefix ($table_prefix) does not match the one ($prefix) found in your configuration. Please make sure you've initiated CarbonPHP before trying to run restful operations. Otherwise you make need to rebuild rest.");

        }

    }

    /**
     * only get, put, and delete
     * @param array $where
     * @param array|null $primary
     * @return string
     * @throws PublicAlert
     */
    private static function buildQueryWhereValues(array $where, array $primary = null): string
    {

        if (null === $primary) {

            if ([] === $where) {

                switch (self::$REST_REQUEST_METHOD) {

                    default:

                        throw new PublicAlert('An unexpected request (' . self::$REST_REQUEST_METHOD . ') method was given to (' . static::class . ').');

                    case self::GET:

                        return '';

                    case self::PUT:

                        if (false === self::$allowFullTableUpdates) {

                            return '';

                        }

                        throw new PublicAlert('Rest expected a primary key or valid where statement but none was provided during a (PUT) request on (' . static::class . ')');

                    case self::DELETE:

                        if (false === self::$allowFullTableDeletes) {

                            return '';

                        }

                        throw new PublicAlert('Rest expected a primary key or valid where statement but none was provided during a (DELETE) request on (' . static::class . ')');


                    case self::POST:

                        throw new PublicAlert('The post method was executed while running buildQueryWhereValues against table (' . static::class . ')');


                }

            }

            return ' WHERE ' . self::buildBooleanJoinedConditions($where);

        }

        if (empty(static::PRIMARY)) {

            throw new PublicAlert('Primary keys given during a request to (' . static::class . ') which does not have a primary key. Please regenerate this class using RestBuilder.');

        }

        if (is_string(static::PRIMARY)) {

            if ([] !== $where && '' !== ($primary[static::PRIMARY] ?? '')) {

                throw new PublicAlert('Restful tables with a single primary key must not have WHERE values passed when the primary key is given. Table (' . static::class . ') was passed a non empty key `WHERE` (' . json_encode($where, JSON_PRETTY_PRINT) . ') to the arguments of GET.');

            }

            if ('binary' === (static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE])) {

                return ' WHERE ' . static::PRIMARY . "=UNHEX(" . self::addInjection($primary[static::PRIMARY]) . ') ';

            }

            return ' WHERE ' . static::PRIMARY . "=" . self::addInjection($primary[static::PRIMARY], static::PDO_VALIDATION[static::PRIMARY]) . ' ';

        }

        if (is_array(static::PRIMARY)) {

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

            return ' WHERE ' . self::buildBooleanJoinedConditions($primary);

        }

        throw new PublicAlert('An unexpected error occurred while paring your primary key in ' . static::class . '. static::PRIMARY may only be a string, array, or null. You may need to regenerate with RestBuilder.');

    }

    private static function buildQueryGroupByValues(array $group, string $sql): string
    {

        // concatenation and aggregation
        if (true === self::$columnSelectEncountered && true === self::$aggregateSelectEncountered && [] === $group) {

            throw new PublicAlert("Restful Error! A simple column select and aggregate function were used in the same query without the ['GROUP_BY'] clause explicitly set. This has been deprecated. Failed after compiling only :: ($sql)");

        }

        if ([] === $group) {

            return '';

        }

        // GROUP BY clause can only be used with aggregate functions like SUM, AVG, COUNT, MAX, and MIN.
        return ' GROUP BY ' . self::buildQuerySelectValues($group, true) . ' ';

    }

    private static function buildQueryHavingValues(array $having): string
    {
        if ([] === $having) {

            return '';

        }

        return ' ' . self::HAVING . ' ' . self::buildBooleanJoinedConditions($having);
    }

    private static function buildQuerySelectValues(array $get, bool $isSubSelect): string
    {
        $sql = '';

        foreach ($get as $key => $column) {

            if (false === is_numeric($key)) {

                throw new PublicAlert('Select values must only use numeric keys. The key (' . $key . ') was encountered.');

            }

            // this is for buildAggregate which prepends
            if ('' !== $sql && ',' !== $sql[-2]) {

                $sql .= ', ';

            }

            $wasCallable = false;

            while (is_callable($column)) {

                $wasCallable = true;    // this works as if rest generated the query or not and can be trusted as

                $column = $column();

            }

            // todo - more validation on sub-select?
            if ($wasCallable && strpos($column, '(SELECT ') === 0) {

                self::$columnSelectEncountered = true;

                $sql .= $column;

                continue;

            }


            if (is_array($column)) {

                if (is_string($column[0] ?? false) && in_array($column[0], [
                        self::GROUP_CONCAT,
                        self::DISTINCT      // todo - all I know is that count need be after distinct
                    ], true)) {

                    $sql = self::buildAggregate($column, $isSubSelect) . ", $sql";

                    continue;

                }

                $sql .= self::buildAggregate($column, $isSubSelect);

                continue;               // next foreach iteration

            }

            if (!is_string($column)) {

                // is this even possible at this point?
                throw new PublicAlert('C6 Rest client could not validate a column in the GET:[] request.');

            }

            // todo - update this syntax allow for remote sub select using [ buildAggregate ]
            if (self::$allowSubSelectQueries && strpos($column, '(SELECT ') === 0) {

                self::$columnSelectEncountered = true;

                $sql .= $column;

                continue;

            }

            if (self::validateInternalColumn($column)) {

                self::$columnSelectEncountered = true;

                if (false === $isSubSelect
                    && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    $sql .= "HEX($column) as " . self::$compiled_valid_columns[$column];        // get short tag

                    continue;

                }

                $sql .= $column;

                continue;

            }

            throw new PublicAlert("CarbonPHP could not validate a column ($column) passed in the (SELECT) request which caused "
                . "the request to fail. It does not appear to be allowed based on the tables joined. Possible values include ("
                . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

        }

        // this is for buildAggregate self::GROUP_CONCAT & self::DISTINCT
        return rtrim($sql, ', ');
    }

    private static function buildQueryPaginationValues(array $argv, bool $isSubSelect, array $primary = null): string
    {
        // pagination [self::PAGINATION][self::LIMIT]
        if (array_key_exists(self::PAGINATION, $argv) && !empty($argv[self::PAGINATION])) {    // !empty should not be in this block - I look all the time

            $limit = '';

            // setting the limit to null will cause no limit
            // I get tempted to allow 0 to symbolically mean the same thing, but 0 Limit is allowed in mysql
            // @link https://stackoverflow.com/questions/30269084/why-is-limit-0-even-allowed-in-mysql-select-statements
            if (array_key_exists(self::LIMIT, $argv[self::PAGINATION]) && null !== $argv[self::PAGINATION][self::LIMIT]) {

                $limit = $argv[self::PAGINATION][self::LIMIT];

                if (false === is_numeric($limit)) {

                    throw new PublicAlert("A non numeric LIMIT ($limit) was provided to REST while querying against (" . static::class . ').');

                }

                if (0 > (int)$limit) {

                    throw new PublicAlert('A negative limit was encountered in the rest Builder while querying against (' . static::class . ').');

                }

                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {

                    if ($argv[self::PAGINATION][self::PAGE] < 1) {

                        return self::signalError('The body of PAGINATION requires that PAGE attribute be no less than 1.');

                    }

                    $limit = 'LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $limit) . ',' . $limit;

                } else {

                    $limit = 'LIMIT ' . $limit;

                }

            }

            if ('' === $limit || array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                $order = ' ORDER BY ';

                /** @noinspection NotOptimalIfConditionsInspection */
                if (array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                    if (is_array($argv[self::PAGINATION][self::ORDER])) {

                        /** @noinspection NotOptimalIfConditionsInspection */
                        if (2 === count($argv[self::PAGINATION][self::ORDER])
                            && array_key_exists(0, $argv[self::PAGINATION][self::ORDER])
                            && array_key_exists(1, $argv[self::PAGINATION][self::ORDER])
                            && is_string($argv[self::PAGINATION][self::ORDER][0])
                            && is_string($argv[self::PAGINATION][self::ORDER][1])
                        ) {

                            throw new PublicAlert('The syntax used in the order by must be array( $key => $value ) pairs; not a comma `,` separated list. The (' . json_encode($argv[self::PAGINATION][self::ORDER]) . ') argument passed is not correct.');

                        }

                        $orderArray = [];

                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {

                            if (false === in_array($sort, [self::ASC, self::DESC], true)) {

                                throw new PublicAlert('Restful order by failed to validate sorting method. The value should be one of (' . json_encode([self::ASC, self::DESC]) . ')');

                            }

                            if (false === self::validateInternalColumn($item, $sort)) {

                                throw new PublicAlert("The column value ($item) caused your custom validations to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            $orderArray[] = "$item $sort";

                        }

                        $order .= implode(', ', $orderArray);

                        unset($orderArray);

                    } else {

                        throw new PublicAlert('Rest query builder failed during the order pagination.');

                    }

                } else if (null !== static::PRIMARY
                    && (is_string(static::PRIMARY) || is_array(static::PRIMARY))) {

                    $primaryColumn = is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0];

                    if ('binary' === static::PDO_VALIDATION[$primaryColumn][self::MYSQL_TYPE]) {

                        $order .= static::COLUMNS[$primaryColumn] . ' ' . self::DESC;

                    } else {

                        $order .= $primaryColumn . ' ' . self::DESC;

                    }

                } elseif (!empty($limit)) {

                    throw new PublicAlert('An error was detected in your REST syntax regarding the limit. No order by clause was provided and no primary keys were detected in the main joining table to automagically set.');

                } else {

                    throw new PublicAlert('A unknown Restful error was encountered while compiling the order by statement.');

                }

                return $order . ($limit === '' ? '' : " $limit");

            }

            return $limit;

        }

        if (false === $isSubSelect && static::PRIMARY !== null) {

            return ' ORDER BY ' . (is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0])
                . ' ASC LIMIT ' . (null === $primary ? '100' : '1');

        }

        return '';

    }

    private static function buildQueryJoinValues(array $join): string
    {

        $sql = '';

        foreach ($join as $by => $tables) {

            $validJoins = [
                self::INNER,
                self::LEFT,
                self::RIGHT,
                self::FULL_OUTER,
                self::LEFT_OUTER,
                self::RIGHT_OUTER
            ];

            if (false === in_array($by, $validJoins, true)) {

                throw new PublicAlert('The restful inner join had an unknown error.'); // todo - message

            }

            $by = str_replace("_", " ", $by);

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PublicAlert('A table ' . $JoiningClass . ' provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');

                }

                $imp = array_map('strtolower', array_keys(class_implements($JoiningClass)));

                /** @noinspection ClassConstantUsageCorrectnessInspection */
                if (false === in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                    && false === in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                    && false === in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
                ) {

                    throw new PublicAlert('Rest error, class/table exists in the restful generation folder which does not implement the correct interfaces. Please re-run rest generation.');

                }

                if (false === is_array($stmt)) {

                    throw new PublicAlert("Rest error in the join stmt, the value of $JoiningClass is not an array.");

                }

            }

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE
                        . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PublicAlert('A table (' . $JoiningClass . ') provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
                }


                $table = $JoiningClass::TABLE_NAME;

                /**
                 * Prefix is normally included in the table name variable.
                 * The table prefix is expected to be an empty string for all tables except carbon_ internals.
                 * The following check is so CarbonPHP internal tables can be compatible with table prefixing.
                 * It also ensures the table you are joining has been generated correctly for your current build.
                 */
                self::checkPrefix($JoiningClass::TABLE_PREFIX);

                $prefix = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';   // its super important to do this period on this line

                if ('' !== $sql && ' ' !== $sql[-1]) {

                    $sql .= ' ';

                }

                $sql .= strtoupper($by) . " JOIN $prefix$table ON " . self::buildBooleanJoinedConditions($stmt);

            }

        }

        return $sql;

    }


    /**
     * @param array|null $primary
     * @param array $argv
     * @param bool $isSubSelect
     * @return string
     * @throws PublicAlert
     */
    protected static function buildSelectQuery(array $primary = null, array $argv = [], bool $isSubSelect = false): string
    {

        $sql = 'SELECT '
            . self::buildQuerySelectValues($argv[self::SELECT] ?? array_keys(static::PDO_VALIDATION), $isSubSelect)
            . ' FROM '
            . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
            . static::TABLE_NAME . ' ';

        $ifArrayKeyExistsThenValueMustBeArray = static function (array $argv, string $key) {

            if (true === array_key_exists($key, $argv)) {

                if (false === is_array($argv[$key])) {

                    throw new PublicAlert("The restful join field ($argv) passed to (" . static::class . ") must be an array.");
                }

                return true;

            }

            return false;

        };

        if (true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::JOIN)) {

            $sql .= self::buildQueryJoinValues($argv[self::JOIN]);

        }

        // Boolean Conditions and aggregation
        $sql .= self::buildQueryWhereValues($argv[self::WHERE] ?? [], $primary);

        $sql .= self::buildQueryGroupByValues(
            true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::GROUP_BY)
                ? $argv[self::GROUP_BY]
                : [], $sql);

        if (true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::HAVING)) {

            // Boolean Conditions and aggregation
            $sql .= self::buildQueryHavingValues($argv[self::HAVING]);

        }

        $sql .= self::buildQueryPaginationValues($argv, $isSubSelect, $primary);

        return $sql;
    }


    /**
     * @param array|null $primary
     * @param array $argv
     * @param string $as
     * @return callable
     * @todo - external subselect
     * Rest::SELECT => [
     *
     *      [ Rest::SELECT,  [  [primary:(array|null), optional argv:(array)]  ],  optional as:(string)) ]
     *
     * ]
     *
     * @deprecated private use
     *
     */
    public static function subSelect(array $primary = null, array $argv = [], string $as = ''): callable
    {

        return static function () use ($primary, $argv, $as): string {

            self::$allowSubSelectQueries = true;

            self::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $primary, $argv, true);

            $sql = self::buildSelectQuery($primary, $argv, true);

            $sql = "($sql)";

            if (!empty($as)) {
                $sql = "$sql AS $as";
            }

            self::completeRest(true);

            return $sql;

        };

    }

    /**
     * @param string|array $valueOne
     * @param string $operator
     * @param string|array $valueTwo
     * @return string
     * @throws PublicAlert
     */
    protected static function addSingleConditionToWhereOrJoin($valueOne, string $operator, $valueTwo): string
    {
        // js ajax get will automatically add _ where spaces exist
        $operator = str_replace('_', ' ', $operator);

        if (is_array($valueOne)) {

            if (self::isAggregateArray($valueOne)) {

                $valueOne = self::buildAggregate($valueOne);

            } else {

                throw new PublicAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate. ($valueOne)");

            }

            $key_is_custom = false;

        } else {

            $key_is_custom = false === self::validateInternalColumn($valueOne, $operator, $valueTwo);

        }

        if (is_array($valueTwo)) {

            if (self::isAggregateArray($valueTwo)) {

                $valueTwo = self::buildAggregate($valueTwo);

            } else {

                throw new PublicAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate. ($valueTwo)");

            }

            $value_is_custom = false;

        } else {

            $value_is_custom = false === self::validateInternalColumn($valueTwo, $operator, $valueOne);

        }

        if ($key_is_custom && $value_is_custom) {

            throw new PublicAlert("Rest failed in as you have custom columns ($valueOne) &| ($valueTwo). This may mean you need to regenerate your rest tables or have misspellings in your request. Please uses dedicated constants.");

        }

        if (false === $key_is_custom && false === $value_is_custom) {

            return "$valueOne $operator $valueTwo";

        }

        if ($value_is_custom) {

            if (self::$allowSubSelectQueries && strpos($valueTwo, '(SELECT ') === 0) {

                return "$valueOne $operator $valueTwo";

            }

            if (self::$compiled_PDO_validations[$valueOne][self::MYSQL_TYPE] === 'binary') {

                return "$valueOne $operator UNHEX(" . self::addInjection($valueTwo) . ")";

            }

            return "$valueOne $operator " . self::addInjection($valueTwo);

        }

        if (self::$compiled_PDO_validations[$valueTwo][self::MYSQL_TYPE] === 'binary') {

            return "$valueTwo $operator UNHEX(" . self::addInjection($valueOne) . ")";

        }

        return self::addInjection($valueOne) . " $operator $valueTwo";

    }

    private static function array_of_numeric_keys_and_string_int_or_aggregate_values(array &$a): bool
    {

        $return = true;

        if (empty($a)) {

            return $return;

        }

        foreach ($a as $key => &$value) {

            while (is_callable($value)) {

                $value = $value();

            }

            if (false === is_numeric($key)
                || (false === is_string($value)
                    && false === is_int($value)
                    && (is_array($value)
                        && false === self::isAggregateArray($value)))) {

                // not an Aggregate
                $return = false;    // we want to remove callables

            }

        }

        return $return;

    }


    /**
     * It was easier for me to think of this in a recursive manner.
     * In reality we should limit our use of recursion php <= 8.^
     * @param array $set
     * @return string
     * @throws PublicAlert
     */
    protected static function buildBooleanJoinedConditions(array $set): string
    {
        static $booleanOperatorIsAnd = true;

        $booleanOperator = $booleanOperatorIsAnd ? 'AND' : 'OR';

        $sql = '';

        // this is designed to be different than buildAggregate
        $supportedOperators = [
            self::GREATER_THAN_OR_EQUAL_TO,
            self::GREATER_THAN,
            self::LESS_THAN_OR_EQUAL_TO,
            self::LESS_THAN,
            self::EQUAL,
            self::EQUAL_NULL_SAFE,
            self::NOT_EQUAL,
            self::IN,
            self::NOT_IN,
            self::LIKE,
            self::NOT_LIKE,
        ];


        // we have to determine when an aggregate might occur early
        if (true === self::array_of_numeric_keys_and_string_int_or_aggregate_values($set)) {

            switch (count($set)) {

                case 0:

                    throw new PublicAlert('An empty array was passed as a leaf member of a condition. Nested arrays recursively flip the AND vs OR joining conditional. Multiple conditions are not required as only one condition is needed. Two may be asserted equal implicitly by placing them in positions one and two of the array. An explicit definition may see the second $position[1] equal one of  (' . implode('|', $supportedOperators) . ')');

                case 1:

                    $key = array_key_first($set);

                    if (is_array($set[$key])) {

                        $set[$key] = json_encode($set[$key]);

                    }

                    throw new PublicAlert("The single value ({$set[$key]}) has the numeric array key [$key] which could imply a conditional equality; however, numeric keys (implicit or explicit) to imply equality are not allowed in C6. Please reorder the condition to have a string key and numeric value.");

                case 2:

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], self::EQUAL, $set[1]);

                    break;

                case 3:

                    if (false === in_array($set[1], $supportedOperators, true)) { // ie #^=|>=|<=$#

                        throw new PublicAlert("Restful column joins may only use one of the following (" . implode('|', $supportedOperators) . ") the value ({$set[1]}) is not supported.");

                    }

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], $set[1], $set[2]);

                    break;

                default:

                    throw new PublicAlert('An array containing all numeric keys was found with (' . count($set) . ') values. Only exactly two or three values maybe given.');

            } // end switch

        } else {

            $addJoinNext = false;

            // the value is not callable (we dealt with it in the condition above)
            foreach ($set as $column => $value) {

                if ($addJoinNext) {

                    $sql .= " $booleanOperator ";

                } else {

                    $addJoinNext = true;

                }

                if (is_int($column)) {

                    if (false === is_array($value)) {

                        throw new PublicAlert("Rest boolean condition builder expected ($value) to be an array as it has a numeric key in the set :: " . json_encode($set));

                    }

                    $booleanOperatorIsAnd = !$booleanOperatorIsAnd; // saves memory

                    $sql .= self::buildBooleanJoinedConditions($value);

                    $booleanOperatorIsAnd = !$booleanOperatorIsAnd;

                    continue;

                }

                // else $column is equal a (string)
                if (false === is_array($value) || self::isAggregateArray($value)) {

                    $sql .= self::addSingleConditionToWhereOrJoin($column, self::EQUAL, $value);

                    continue;

                }


                $count = count($value);

                switch ($count) {

                    case 1:

                        // this would be odd syntax but I will allow it
                        if (true === array_key_exists(0, $value)
                            && true === is_array($value[0])
                            && true === self::isAggregateArray($value[0])) {

                            $sql .= self::addSingleConditionToWhereOrJoin($column, self::EQUAL, $value[0]);

                            break;

                        }

                    // let this fall to throw alert

                    default:

                        throw new PublicAlert("A rest key column ($column) value (" . json_encode($value) . ") was set to an array with ($count) "
                            . "values but requires only one or two. Boolean comparisons can use one of the following operators "
                            . "(" . implode('|', $supportedOperators) . "). The same comparison can be made with an empty (aka default numeric) key "
                            . "and three array entries :: [  Column,  Operator, (Operand|Column2) ]. Both ways are made equally "
                            . "for conditions which might require multiple of the same key in the same array; as this would be invalid syntax in php.");

                    case 2:

                        if (false === in_array($value[0], $supportedOperators)) { // ie #^=|>=|<=$#

                            throw new PublicAlert("Table (" . static::class . ") restful column joins may only use one of the following supported operators ($supportedOperators).");

                        }

                        $sql .= self::addSingleConditionToWhereOrJoin($column, $value[0], $value[1]);

                }


            } // end foreach

        }

        $sql = preg_replace("/\s$booleanOperator\s?$/", '', $sql);

        return "($sql)";  // do not try to remove the parenthesis from this return

    }


    /**
     * @param $argv
     * @param $sql
     */
    public static function jsonSQLReporting($argv, $sql): void
    {
        global $json;

        if (false === self::$jsonReport) {

            return;

        }

        if (false === is_array($json)) {

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
     * @param string $prefix
     * @param string|null $namespace
     * @return bool
     */
    public static function MatchRestfulRequests(string $prefix = '', string $namespace = null): bool
    {

        return Route::regexMatch(/** @lang RegExp */ '#' . $prefix . 'rest/([A-Za-z\_]{1,256})/?([^/]+)?#',

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
     * @param array|null $args
     * @param string|array|null $primary
     * @param bool $subQuery
     * @throws PublicAlert
     */
    protected static function startRest(
        string $method,
        array  $return,
        array  &$args = null,
               &$primary = null,
        bool   $subQuery = false): void
    {
        self::checkPrefix(static::TABLE_PREFIX);

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
                self::$aggregateSelectEncountered,
                self::$columnSelectEncountered,
                self::$injection
            ];
            self::$externalRestfulRequestsAPI = false;
            self::$aggregateSelectEncountered = false;
            self::$columnSelectEncountered = false;
        }

        if ($subQuery) {
            self::$REST_REQUEST_METHOD = $method;
            self::$REST_REQUEST_PRIMARY_KEY = &$primary;
            self::$REST_REQUEST_PARAMETERS = &$args;
            self::$REST_REQUEST_RETURN_DATA = &$return;
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
            self::$injection = [];
            # self::$allowSubSelectQueries = false;
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

        // named to remind myself not to use this for all empty array reset occurrences in this file
        $empty_request_parameters_array = [];

        // tested, this is the only way to reset static member references
        self::$REST_REQUEST_PARAMETERS = &$empty_request_parameters_array;

        if (empty(self::$activeQueryStates)) {
            self::$REST_REQUEST_METHOD = null;
            self::$REST_REQUEST_PRIMARY_KEY = null;
            self::$compiled_valid_columns = [];
            self::$compiled_PDO_validations = [];
            self::$compiled_PHP_validations = [];
            self::$compiled_regex_validations = [];
            self::$externalRestfulRequestsAPI = false;     // this should only be done on completion
            self::$join_tables = [];
            self::$injection = [];
            self::$aggregateSelectEncountered = false;
            self::$columnSelectEncountered = false;

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
                self::$aggregateSelectEncountered,
                self::$columnSelectEncountered,
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
                self::$aggregateSelectEncountered,
                self::$columnSelectEncountered,
                self::$injection
            ] = array_pop(self::$activeQueryStates);
        }
    }

    public static function addInjection($value, array $pdo_column_validation = null): string
    {
        switch ($pdo_column_validation[self::PDO_TYPE] ?? null) {

            default:

                // todo - cache db better (startRest?)
                $value = (Database::database())->quote($value);        // boolean, string

            case null:
            case PDO::PARAM_INT:
            case PDO::PARAM_STR: // bindValue will quote strings

                $key = array_search($value, self::$injection, true);

                if (false !== $key) {

                    return $key;   // this is a cache

                }

                $inject = ':injection' . count(self::$injection); // self::$injection needs to be a class const not a

                self::$injection[$inject] = $value;

                break;

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

    public static function allowSubSelect(): void
    {
        self::$allowSubSelectQueries = true;
    }

    public static function buildMysqlHistoryTrigger(string $table): void
    {

        $queryNew = '';

        $queryOld = '';

        $prefix = $table::TABLE_PREFIX !== History_Logs::TABLE_PREFIX ? $table::TABLE_PREFIX : '';

        foreach ($table::PDO_VALIDATION as $name => $columnInfo) {

            $column = $table::COLUMNS[$name];  // get name without table prefixed

            $is_bin = $columnInfo[self::PDO_TYPE] === 'binary';

            $queryNew .= $is_bin
                ? <<<END
                                '$column', HEX(NEW.$column),
                            END
                : <<<END
                                '$column', JSON_QUOTE(COALESCE(NEW.$column,'')),
                            END;

            $queryOld .= $is_bin
                ? <<<END
                                '$column', HEX(OLD.$column),
                            END
                : <<<END
                                '$column', JSON_QUOTE(COALESCE(OLD.$column,'')),
                            END;
        }

        $queryOld = rtrim($queryOld, ',');

        $queryNew = rtrim($queryNew, ',');

        $dependencies = $table::EXTERNAL_TABLE_CONSTRAINTS;

        $delete_children = '';

        if (!empty($dependencies)) {
            foreach ($dependencies as $external => $internal) {

                [$externalTableName, $externalColumn] = explode('.', $external);

                [, $internalColumn] = explode('.', $internal);

                $delete_children .= "# noinspection SqlResolve
DELETE FROM $externalTableName WHERE $externalColumn = OLD.$internalColumn;" . PHP_EOL;


            }

        }

        $table_name = $table::TABLE_NAME;

        $trigger = <<<TRIGGER
DROP TRIGGER IF EXISTS `trigger_{$table_name}_b_d`;;
CREATE TRIGGER `trigger_{$table_name}_b_d` BEFORE DELETE ON `$table_name` FOR EACH ROW
BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'DELETE'
                        , history_data = JSON_OBJECT($queryOld
                        ), original_query);

      -- Delete Children
$delete_children

END;;

DROP TRIGGER IF EXISTS `trigger_{$table_name}_a_u`;;
CREATE TRIGGER `trigger_{$table_name}_a_u` AFTER UPDATE ON `$table_name` FOR EACH ROW
                                                                   BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'PUT'
                        , history_data = JSON_OBJECT($queryNew
                        ), original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_{$table_name}_a_i`;;
CREATE TRIGGER `trigger_{$table_name}_a_i` AFTER INSERT ON `$table_name` FOR EACH ROW
                                                                   BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'POST'
                        , history_data = JSON_OBJECT($queryNew
                        ), original_query);

END;;
TRIGGER;


        if (false === file_put_contents(CarbonPHP::$app_root . 'trigger.sql', 'DELIMITER ;;' . PHP_EOL . $trigger . PHP_EOL . 'DELIMITER ;')) {

            self::colorCode('PHP file_put_contents failed to store (' . CarbonPHP::$app_root . 'trigger.sql)', iColorCode::RED);

            return;

        }

        MySQL::MySQLSource(CarbonPHP::$app_root . 'trigger.sql');

        unlink(CarbonPHP::$app_root . 'trigger.sql');

    }




    public static function parseSchemaSQL(string $sql = null, array $replace = null): ?string
    {

        $sql = trim($sql);

        $sql = str_replace("\\n", "\n", $sql);

        $sql = trim($sql);

        $replace ??= self::SQL_VERSION_PREG_REPLACE;

        $pattern = array_keys($replace);

        $replacement = array_values($replace);

        $SQLArray = array_map('trim', explode(PHP_EOL, $sql));

        $looseSQL = preg_replace($pattern, $replacement, $SQLArray);

        $last = array_pop($looseSQL);

        $first = array_shift($looseSQL);

        return $first . PHP_EOL . implode( ',' . PHP_EOL, $looseSQL) . PHP_EOL . $last . ';';

    }

    protected static function runValidations(array $php_validation, &...$rest): void
    {

        foreach ($php_validation as $key => $validation) {

            if (!is_int($key)) {

                // This would indicated a column value explicitly on pre or post method.
                continue;

            }

            if (!is_array($validation)) {

                if (is_callable($validation)) {

                    if (empty($rest)) {

                        if (false === call_user_func_array($validation, [&self::$REST_REQUEST_PARAMETERS])) {

                            throw new PublicAlert('A global request callable validation failed, please make sure the arguments are correct.');

                        }

                    } else if (false === call_user_func_array($validation, [&$rest])) {

                        throw new PublicAlert('A column request validation callable validation failed, please make sure arguments are correct.');

                    }

                    return;

                }

                throw new PublicAlert('Each PHP_VALIDATION should equal an array of arrays or callables with [ call => method , structure followed by any additional arguments ]. Refer to Carbonphp.com for more information.');

            }

            $class = array_key_first($validation);          //  $class => $method

            $validationMethod = $validation[$class];

            unset($validation[$class]);

            if (!class_exists($class)) {

                throw new PublicAlert("A class reference in PHP_VALIDATION failed. Class ($class) not found.");

            }

            if (empty($rest)) {

                if (false === call_user_func_array([$class, $validationMethod],
                        [&self::$REST_REQUEST_PARAMETERS, ...$validation]
                    )) {

                    throw new PublicAlert('The global request validation failed, please make sure the arguments are correct.');

                }

            } else if (false === call_user_func_array([$class, $validationMethod], [&$rest, ...$validation])) {

                throw new PublicAlert('A column request validation failed, please make sure arguments are correct.');

            }

        }

    }

}
