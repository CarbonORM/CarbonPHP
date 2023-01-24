<?php

namespace CarbonPHP\Restful;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;

abstract class RestQueryValidation extends RestAutoTargeting
{

    public const DISALLOW_PUBLIC_ACCESS = [self::class => 'disallowPublicAccess'];

    /**
     * returns true if it is a column name that exists and all user validations pass.
     * return is false otherwise.
     * @param mixed $column
     * @param string|null $operator
     * @param string|null|mixed $value
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

            static::parseAggregateWithNoOperators($column);

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
     * static::class ends up not working either because of how it is called.
     * @param $request
     * @param string|null $calledFrom
     * @throws PublicAlert
     */
    public static function disallowPublicAccess($request, string $calledFrom = null): void
    {

        if (self::$externalRestfulRequestsAPI && !CarbonPHP::$test) {

            /** @noinspection JsonEncodingApiUsageInspection */
            throw new PublicAlert('Rest request denied by the PHP_VALIDATION\'s in the tables ORM. Remove DISALLOW_PUBLIC_ACCESS '
                . (null !== $calledFrom ? "from ($calledFrom) " : '')
                . 'to gain privileges. Method: (' . $_SERVER['REQUEST_METHOD'] . ') Uri: (' . $_SERVER['REQUEST_URI'] . ') Request: (' . json_encode($request, JSON_PRETTY_PRINT) . ')');

        }

    }

    /**
     * @throws PublicAlert
     */
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

    /**
     * @throws PublicAlert
     */
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

    /**
     * @throws PublicAlert
     */
    public static function prepostprocessRestRequest(&$return = null): void
    {
        if ((self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PRECOMMIT] ?? false)
            && is_array(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PRECOMMIT])) {

            self::runValidations(self::$compiled_PHP_validations[self::$REST_REQUEST_METHOD][self::PRECOMMIT], $return);

        }

        if ((self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS] ?? false)
            && is_array(self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS])) {

            self::runValidations(self::$compiled_PHP_validations[self::FINISH][self::PREPROCESS], $return);

        }

        if ((self::$compiled_PHP_validations[self::FINISH][self::PRECOMMIT] ?? false)
            && is_array(self::$compiled_PHP_validations[self::FINISH][self::PRECOMMIT])) {

            self::runValidations(self::$compiled_PHP_validations[self::FINISH][self::PRECOMMIT], $return);

        }

    }

    /**
     * @throws PublicAlert
     */
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
     * @throws PublicAlert
     */
    public static function runCustomCallables(&$column, string &$operator = null, &$value = null, bool $default = false): void
    {

        $method = self::$REST_REQUEST_METHOD;

        self::$VALIDATED_REST_COLUMNS[] = $column;  // to prevent recursion.

        if (null !== $value && $default === false) {

            // this is just a test for the bool
            $equalsValidColumn = self::validateInternalColumn($value, $column);

            if (false === $equalsValidColumn
                && array_key_exists($column, self::$compiled_regex_validations)) {

                if (true === is_string(self::$compiled_regex_validations[$column])) {

                    self::$compiled_regex_validations[$column] = [self::$compiled_regex_validations[$column] => null];

                }

                foreach (self::$compiled_regex_validations[$column] as $pattern => $errorMessage) {

                    if (1 > preg_match_all($pattern, $value, $matches, PREG_SET_ORDER)) {  // can return 0 or false

                        throw new PublicAlert(($errorMessage ?? "The column ($column) was set to be compared with a value who did not pass the regex (" . $pattern . ") test. Please check this ($value) value and try again. preg_match_all: (" . var_export($matches, true) . ') preg_last_error_msg: (' . preg_last_error_msg() . ')')
                            . " CODE: ($pattern) <> ($value) preg_last_error_msg: (" . preg_last_error_msg() . ')');

                    }

                }

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

    /**
     * @return void
     * @throws PublicAlert
     */
    public static function gatherValidationsForRequest(): void
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

        $compiled_columns = $pdo_validations = $regex_validations = [];

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
            if (false === defined("$table::REGEX_VALIDATION")) {
                throw new PublicAlert('The table does not implement REGEX_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.');
            }

            $table_regular_expressions = constant("$table::REGEX_VALIDATION");

            if (false === is_array($table_regular_expressions)) {

                throw new PublicAlert("The class constant $table::REGEX_VALIDATION must equal an array.");

            }

            if (false === empty($table_regular_expressions)) {

                // todo - run table validation on cli command to save time??
                foreach ($table_regular_expressions as $columnName => $regex) {

                    if (is_array($regex)) {

                        foreach ($regex as $regexTest => $errorMessage) {

                            if (false === is_string($errorMessage) && false === is_null($errorMessage)) {

                                throw new PublicAlert("The column ($columnName) regex ($regexTest) should have a string error message or null for development, but (" . print_r($errorMessage, true) . ') was given.');

                            }

                        }

                    } else if (false === is_string($regex)) {

                        throw new PublicAlert("A key => value pair ($columnName => " . print_r($regex, true) . ") encountered in $table::REGEX_VALIDATION is invalid.");

                    }

                }

                $regex_validations[] = $table_regular_expressions;

            }

            if (defined("$table::COLUMNS")) {

                $table_columns_constant = constant("$table::COLUMNS");

                foreach ($table_columns_constant as $key => $value) {

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
            if (false === defined("$table::PDO_VALIDATION")) {

                throw new PublicAlert("The ($table) does not implement PHP_VALIDATION. This should be an empty static array. Try re-running the RestBuilder.");

            }

            if (false === property_exists($table, 'PHP_VALIDATION')) {

                throw new PublicAlert("The ($table) does not implement \${$table}->PHP_VALIDATION. This could be an empty public array. Try re-running the RestBuilder.");

            }

            $table_pdo_validation = constant("$table::PDO_VALIDATION");

            $pdo_validations[] = $table_pdo_validation;

        }
        unset($table);

        self::$compiled_valid_columns = array_merge(self::$compiled_valid_columns, ... $compiled_columns);

        // We compiled all the request columns before gathering all callables that should run.
        foreach ($tables as $table) {

            $table_php_validations_static = constant("$table::PHP_VALIDATION");

            if (!is_array($table_php_validations_static)) {

                throw new PublicAlert("The class constant $table::PDO_VALIDATION must equal an array.");

            }

            self::validateAndConcatenate($table, $table_php_validations_static);

            $table_php_validations_public = (new $table)->PHP_VALIDATION;

            if (!is_array($table_php_validations_public)) {

                throw new PublicAlert("The class constant {$table}->PDO_VALIDATION must equal an array.");

            }

            self::validateAndConcatenate($table, $table_php_validations_public);

        }

        self::$join_tables = $tables;

        // we're merging for sub-selects
        self::$compiled_PDO_validations = array_merge(self::$compiled_PDO_validations, ... $pdo_validations);

        // we're merging for sub-selects
        self::$compiled_regex_validations = array_merge(self::$compiled_regex_validations, ...$regex_validations); // a nice way to avoid running a merge in a loop.

    }

    /**
     * @throws PublicAlert
     */
    public static function gatherValidation(string $firstKey, string $secondKey, string $table, array $table_php_validations): void
    {

        $table_php_validations[$firstKey][$secondKey] ??= [];

        $table_php_validation = $table_php_validations[$firstKey][$secondKey];

        if (empty($table_php_validation)) {

            return;

        }

        self::$compiled_PHP_validations[$firstKey][$secondKey] ??= [];

        if (false === is_array($table_php_validation)) {

            throw new PublicAlert("The class constant $table::PDO_VALIDATION[$firstKey][$secondKey] must be an array. This is unexpected, please send a stack trace to CarbonPHP.com");

        }

        self::pushCallables(self::$compiled_PHP_validations[$firstKey][$secondKey], $table_php_validation, "$table::PDO_VALIDATION[$firstKey][$secondKey]");

    }

    /**
     * @throws PublicAlert
     */
    public static function gatherValidations(string $firstKey, string $table, array $table_php_validation): void
    {

        if (empty($table_php_validation)) {

            return;

        }

        if (!is_array($table_php_validation[$firstKey])) {

            throw new PublicAlert("The class constant ( $table::PDO_VALIDATION[$firstKey] || {$table}->PDO_VALIDATION[$firstKey] )   must be an array.");

        }

        self::gatherValidation($firstKey, self::PREPROCESS, $table, $table_php_validation);

        if ($firstKey !== self::PREPROCESS) {

            // TODO - is this run for GET requests?? if so remove?
            self::gatherValidation($firstKey, self::PRECOMMIT, $table, $table_php_validation);

        }

        self::gatherValidation($firstKey, self::FINISH, $table, $table_php_validation);

    }


    /**
     * @param array $a
     * @param array $b
     * @param string $errorMessageContext
     * @return void
     * @throws PublicAlert
     */
    public static function pushCallables(array &$a, array $b, string $errorMessageContext): void
    {

        if (empty($b)) {

            throw new PublicAlert('An unexpected error has occurred in which an empty array was passed, but not expected. Please report this to CarbonPHP.');

        }

        // error reporting gets messed up when you use array_pop/while directly on the function arguments, use foreach
        foreach ($b as $callable) {

            $isCallable = is_callable($callable);

            if (false === is_array($callable) &&
                false === $isCallable) {

                /** @noinspection JsonEncodingApiUsageInspection */
                throw new PublicAlert("The Restful validations ($errorMessageContext) failed to compile. Value (" . json_encode($b) . ') must be a callable, or an array with structure [ self::class => \'method\', ...$extraArguments ].');

            }

            if (false === $isCallable) {

                $class = array_key_first($callable);

                if (is_numeric($class)) {

                    /** @noinspection JsonEncodingApiUsageInspection */
                    throw new PublicAlert("The restful validation ($errorMessageContext) failed as an 'callable' array argument failed validation. The first key must be a fully qualified class name. Numeric value ($class) provided is incorrect.");

                }

                $method = $callable[$class];

                if (false === class_exists($class)) {

                    throw new PublicAlert("The class name provided in ($errorMessageContext) could not be resolved: ($class).");

                }

                if (false === method_exists($class, $method)) {


                    throw new PublicAlert("Failed to verify that method ($method) exists in the class ($class), found in ($errorMessageContext).");

                }

            }

            $a[] = $callable;

        }

    }


    /**
     * @param string $table - the table to gather php validations for
     * @param array $table_php_validation - the public and static member
     * @param array $table_columns_full - all columns from any joining table
     * @return void
     * @throws PublicAlert
     */
    public static function validateAndConcatenate(string $table, array $table_php_validation): void
    {

        if ($table_php_validation[self::PREPROCESS] ?? false) {

            self::gatherValidations(self::PREPROCESS, $table, $table_php_validation);

        }

        if ($table_php_validation[self::FINISH] ?? false) {

            self::gatherValidations(self::FINISH, $table, $table_php_validation);

        }

        if ($table_php_validation[self::$REST_REQUEST_METHOD] ?? false) {

            self::gatherValidations(self::$REST_REQUEST_METHOD, $table, $table_php_validation);

        }

        $fullyQualifiedColumnNames = array_keys(self::$compiled_valid_columns);

        // doing the foreach like this allows us to avoid multiple loops later,.... wonder what the best case is though... this is more readable for sure
        foreach ($fullyQualifiedColumnNames as $column) {

            self::gatherValidation(self::PREPROCESS, $column, $table, $table_php_validation);

            self::gatherValidation(self::FINISH, $column, $table, $table_php_validation);

            self::gatherValidation(self::$REST_REQUEST_METHOD, $column, $table, $table_php_validation);

        }

    }

    public static function isAggregateArray(array $array): bool
    {

        if (self::isSubSelectAggregation($array)) {

            return true;

        }


        if (false === array_key_exists(0, $array)
            || false === array_key_exists(1, $array)
            || count($array) > 3) {

            return false;

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

    public static function runValidations(array $php_validation, &...$rest): void
    {

        foreach ($php_validation as $key => $validation) {

            if (!is_int($key)) {

                // This would indicated a column value explicitly on pre or post method.
                continue;

            }

            if (empty($validation)) {

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

                    continue;

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

    public static function has_string_keys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
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


}


