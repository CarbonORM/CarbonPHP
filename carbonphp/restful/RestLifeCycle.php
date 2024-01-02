<?php

namespace CarbonPHP\Restful;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Rest;
use CarbonPHP\Route;
use CarbonPHP\Session;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

abstract class RestLifeCycle extends RestQueryBuilder
{


    /**
     * It's far better to cast your arrays to objects
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

            if (property_exists($this, $key)) {

                $this->$key = &$value;

            }

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

        throw new PrivateAlert($message);

    }

    /**
     * This will terminate 99% of the time, but the 1% it wasn't you need to rerun what you try caught
     * as the error was just the database going away.
     * @param Throwable $e
     */
    public static function handleRestException(Throwable $e): void
    {

        ThrowableHandler::generateLog($e);  // this terminates

    }

    /**
     * It is most common for a user validation to use a rest request
     * @param string $method
     * @param array $return
     * @param array|null $args
     * @param string|int|array|null $primary
     * @param bool $subQuery
     * @throws PublicAlert
     */
    protected static function startRest(
        string                $method,
        array|null            $return,
        array                 &$args = null,
        string|int|float|array|null &$primary = null,
        bool                  $subQuery = false): void
    {

        static::checkPrefix(static::TABLE_PREFIX);

        if (self::$REST_REQUEST_METHOD !== null) {
            self::$activeQueryStates[] = [
                self::$REST_REQUEST_METHOD,
                &self::$REST_REQUEST_PRIMARY_KEY,
                &self::$REST_REQUEST_PARAMETERS,
                &self::$REST_REQUEST_RETURN_DATA,
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

        } else {

            $reff = array_pop(self::$activeQueryStates);

            self::$REST_REQUEST_METHOD = &$reff[0];
            self::$REST_REQUEST_PRIMARY_KEY = &$reff[1];
            self::$REST_REQUEST_PARAMETERS = &$reff[2];
            self::$REST_REQUEST_RETURN_DATA = &$reff[3];
            self::$VALIDATED_REST_COLUMNS = &$reff[4];
            self::$compiled_valid_columns = &$reff[5];
            self::$compiled_PDO_validations = &$reff[6];
            self::$compiled_PHP_validations = &$reff[7];
            self::$compiled_regex_validations = &$reff[8];
            self::$externalRestfulRequestsAPI = &$reff[9];
            self::$join_tables = &$reff[10];
            self::$aggregateSelectEncountered = &$reff[11];
            self::$columnSelectEncountered = &$reff[12];

            if (!$subQuery) {

                self::$injection = &$reff[13];

            }

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

        $committed = false;

        $affected_rows = -1;

        $debugDumpParams = null;

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $json['sql'][] = [
            'method' => self::$REST_REQUEST_METHOD,
            'table' => static::class,
            RestSettings::class . '::$externalRestfulRequestsAPI' => self::$externalRestfulRequestsAPI,
            'argv' => $argv,
            'affected_rows' => &$affected_rows,
            'committed' => &$committed,
            'stmt' => [
                'sql' => $sql,
                'debugDumpParams' => &$debugDumpParams,
            ],
            ...(self::$externalRestfulRequestsAPI ? [
                'uri' => $_SERVER['REQUEST_URI']
            ] : [])
        ];

        return static function (PDOStatement $stmt) use (&$affected_rows, &$debugDumpParams, &$committed) {

            $affected_rows = $stmt->rowCount();

            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

            $stmt->debugDumpParams();

            $debugDumpParams = explode("\n", ob_get_clean() ?? 'FAILED TO GET DEBUG DUMP');

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
    public static function ExternalRestfulRequestsAPI(string $mainTable, string $primary = null, string $namespace = 'Tables\\', callable $postProcess = null): bool
    {
        global $json;

        if (false === is_array($json)) {

            $json = [];

        }

        self::$externalRestfulRequestsAPI = true;   // This is to help you determine the request type in

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        try {

            $mainTable = ucwords($mainTable, '_');

            if (!class_exists($namespace . $mainTable)
                && !class_exists($namespace . $mainTable = preg_replace('/^' . preg_quote($prefix, '/') . '/i', '', $mainTable))) {

                throw new PrivateAlert("The table ($namespace$mainTable) was not found in our generated api. Please try rerunning the rest builder and contact us if problems persist. (E_PRE_$prefix)");

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

                            throw new PrivateAlert('Json decoding of $_GET[0] returned null. Please attempt serializing another way.');

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

                    throw new PrivateAlert('The REQUEST_METHOD is not RESTFUL. Method must be either \'POST\', \'PUT\', \'GET\', or \'DELETE\'. The \'OPTIONS\' method may be used in substation for the \'GET\' request which better enables and speeds up advanced queries.');

            }

            $methodCase = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));  // this is to match actual method spelling


            $fullyQualified = $namespace . $mainTable;


            $tableHasNumericPdoPrimaryKey = $requestTableHasPrimary
                && true === is_string($fullyQualified::PRIMARY)
                && ($fullyQualified::PDO_VALIDATION[$fullyQualified::PRIMARY][self::PDO_TYPE] ?? false) === PDO::PARAM_INT;

            switch ($method) {
                case self::OPTIONS:
                case self::PUT:
                case self::DELETE:
                case self::GET:

                    $return = [];

                    // TODO - update argv to be passed by reference
                    if (!call_user_func_array([$fullyQualified, $methodCase], $requestTableHasPrimary ? [&$return, $primary, $args] : [&$return, $args])) {

                        throw new PrivateAlert('The request failed (returned false), please make sure arguments are correct.');

                    }

                    // we're in success operation; TODO - should we double check the commit status?

                    $json['rest'] = $return;

                    if ($method === self::PUT) {

                        $updated = $primary ?? $args;

                        // this could fail?
                        if ($tableHasNumericPdoPrimaryKey) {

                            $updated = (int)$updated;

                        }

                        $json['updated'] = $updated;

                    } elseif ($method === self::DELETE) {

                        $deleted = $primary ?? $args;

                        if ($tableHasNumericPdoPrimaryKey) {

                            $deleted = (int)$deleted;

                        }

                        $json['deleted'] = $deleted;

                    }

                    break;

                case self::POST:

                    if (!$id = call_user_func_array([$namespace . $mainTable, $methodCase], [&$_POST, $primary])) {

                        throw new PrivateAlert('The request failed, please make sure arguments are correct.');

                    }

                    if (!self::commit()) {

                        throw new PrivateAlert('Failed to commit the transaction. Please, try again.');

                    }

                    $created = $id;

                    if ($tableHasNumericPdoPrimaryKey) {

                        $created = (int)$created;

                    }

                    $json['created'] = $created;

                    break;
            }
        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

        if (false === headers_sent($filename, $line)) {

            header('Content-Type: application/json', true, 200);

        } else {

            $json['headers_sent:filename'] = $filename;

            $json['headers_sent:line'] = $line;

        }

        $database = Database::database(false);

        $inTransaction = $database->inTransaction();

        if ($inTransaction && self::$commit === false) {

            $json ['WARNING_NO_COMMIT'] = $json[Session::class][self::class] = 'self::$commit was set to false at the end of (' . __METHOD__ . ') while a transaction was in progress. Adverse effects include rolling back anything currently uncommitted, as-well-as running session_abort() which will discard any changes in the session.';

            if (false === $database->rollBack()) {

                throw new PrivateAlert('Failed to rollBack the transaction. Please, try again.');
            }

            // session abort will rollback the transaction
            if (session_abort()) {

                throw new PrivateAlert('Failed to abort the session; a side effect of `self::$commit === false` at the end of (' . __METHOD__ . '). Please contact support.');

            }

        } else if (session_status() === PHP_SESSION_ACTIVE) {

            $json[Session::class]['write_close'] = session_write_close();

            if (false === $json[Session::class]['write_close']) {

                $json[Session::class][self::class] = 'Failed to close the session';

            } else {

                $json[self::class] = $json[Session::class][self::class] = 'Automatically closed the session & open transaction @ (' . __METHOD__ . ').';

            }

        } else if ($inTransaction) {

            if (false === $database->commit()) {

                throw new PrivateAlert('Failed to auto commit!');

            }

            $json[self::class] = 'Automatically committed the transaction @ (' . __METHOD__ . ')';

        }

        if (is_callable($postProcess)) {

            $json = $postProcess($json);

        }

        try {

            print PHP_EOL . json_encode($json, JSON_THROW_ON_ERROR) . PHP_EOL;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

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

    /**
     * This is used in external libraries to hijack the request and return a json response
     * @TODO - test this in bootstrap to remove the noinspection
     * @noinspection PhpUnused
     */
    public static function hijackRestfulRequest(array $return, bool $commitPendingTransactions = false): never
    {

        try {

            global $json;

            if ($commitPendingTransactions) {

                self::commit();

            }
            
            if (Session::$storeSessionToDatabase === true
                && ($db = Database::database(false))->inTransaction() === true) {

                $json[Session::class][Session::DATABASE_CLOSED_AND_COMMITTED] = 'Database::database(false)->inTransaction() was set to true at the end of (' . __METHOD__ . '). Adverse effects include rolling back anything currently uncommitted, as-well-as running session_abort() which will discard any changes in the session.';

                if (false === empty(Session::$session_id)
                    && session_status() === PHP_SESSION_ACTIVE) {

                    session_abort();

                    $json[Session::class]['session_abort()'] ??= [];

                    $json[Session::class]['session_abort()'][] =[
                        'session_id' => Session::$session_id,
                        'debug_backtrace()' => debug_backtrace()
                    ];

                } else {

                    $db->rollBack();

                    $json[Session::class]['rollBack()'][] =[
                        'session_id' => Session::$session_id,
                        'debug_backtrace()' => debug_backtrace()
                    ];

                }

            }

            if (false === headers_sent($filename, $line)) {

                header('Content-Type: application/json', true, 200);

            } else {

                $json['headers_sent'] = [
                    'filename' => $filename,
                    'line' => $line
                ];

            }

            print json_encode(    $return + $json, JSON_THROW_ON_ERROR);

            exit(0);

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

}

