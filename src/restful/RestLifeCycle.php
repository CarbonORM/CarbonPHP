<?php

namespace CarbonPHP\Restful;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Route;
use PDOException;
use PDOStatement;
use Throwable;

abstract class RestLifeCycle extends RestQueryBuilder
{


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

    /**
     * This will terminate 99% of the time, but the 1% it wasn't you need to rerun what you try caught
     * as the error was just the database going away.
     * @param Throwable $e
     */
    public static function handleRestException(Throwable $e): void
    {

        if ($e instanceof PDOException) {

            // this most likely terminates (only on db resource drop will it continue < 1%)
            Database::TryCatchPDOException($e);

        } else {

            ErrorCatcher::generateLog($e);  // this terminates

        }

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

        static::checkPrefix(static::TABLE_PREFIX);

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

        self::$REST_REQUEST_METHOD = $method;
        self::$REST_REQUEST_PRIMARY_KEY = &$primary;
        self::$REST_REQUEST_PARAMETERS = &$args;
        self::$REST_REQUEST_RETURN_DATA = &$return;

        if (!$subQuery) {
            self::$VALIDATED_REST_COLUMNS = [];
            self::$compiled_valid_columns = [];
            self::$compiled_PDO_validations = [];
            self::$compiled_PHP_validations = [];
            self::$compiled_regex_validations = [];
            self::$join_tables = [];
            self::$injection = [];
            # self::$allowSubSelectQueries = false;
        }

        // this must use late static binding
        static::gatherValidationsForRequest();

        // this must use late static binding
        static::preprocessRestRequest();

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


    public static function bind(PDOStatement $stmt): void
    {

        foreach (self::$injection as $key => $value) {

            $stmt->bindValue($key, $value);

        }

        self::$injection = [];

    }

    public static function jsonSQLReporting($argv, $sql): ?callable
    {
        global $json;

        if (false === self::$jsonReport) {

            return null;

        }

        if (false === is_array($json)) {

            $json = [];

        }

        if (!isset($json['sql'])) {

            $json['sql'] = [];

        }

        if (self::$REST_REQUEST_METHOD === self::GET) {

            $json['sql'][] = [
                'argv' => $argv,
                'stmt' => [
                    $sql,
                    self::$injection
                ]
            ];

            return null;

        }

        $committed = false;

        $affected_rows = -1;

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $json['sql'][] = [
            'argv' => $argv,
            'affected_rows' => &$affected_rows,
            'committed' => &$committed,
            'stmt' => [
                $sql,
                self::$injection
            ]
        ];

        return static function (PDOStatement $stmt) use (&$affected_rows, &$committed) {

            $affected_rows = $stmt->rowCount();

            return static function () use (&$committed) {

                $committed = true;

            };

        };

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

        }

        if (false === headers_sent($filename, $line)) {

            header('Content-Type: application/json', true, 200);

        } else {

            $json['headers_sent:filename'] = $filename;

            $json['headers_sent:line'] = $line;

        }

        print PHP_EOL . json_encode($json) . PHP_EOL;


        return true;
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

                    // this must use late static binding
                    static::ExternalRestfulRequestsAPI($table, $primary);

                    return;

                }

                // this must use late static binding
                static::ExternalRestfulRequestsAPI($table, $primary, $namespace);

            });

    }
}
