<?php

namespace CarbonPHP\Classes\Configurations;


use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Abstracts\Rest;
use CarbonPHP\Classes\Session;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\Throwables\PrivateAlert;
use CarbonPHP\Throwables\PublicAlert;
use PDO;
use Throwable;

final class DatabaseHost
{
    private(set) int $committedTransactions = 0;
    public DatabasePool $databasePool {
        set {
            if (isset($this->databasePool)) {
                throw new PublicAlert("Overriding the connection pool after being set is not allowed");
            }
        }
    }

    private(set) ?PDO $database = null {
        get {
            if (null === $this->database) {
                $this->database = $this->newInstance();
            }
            return $this->database;
        }
    }

    /**
     * @todo - is it time to support other DBs?
     * @var string $carbonDatabaseDSN holds the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public string $carbonDatabaseDSN {
        get => 'mysql:host='
            . $this->host
            . ';dbname='
            . $this->databasePool->name
            . (!empty($this->carbonDatabasePort)
                ? ';port=' . $this->carbonDatabasePort
                : '');
    }

    /**
     * @var array - new key inserted but not verified currently
     */
    private array $carbonDatabaseEntityTransactionKeys = [];

    /**
     * ATTR_EMULATE_PREPARES - the sessions table stopped working as the php serialized
     *      uses : which cased the driver to fail
     */
    public const array DEFAULT_PDO_OPTIONS = [
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_PERSISTENT => CarbonPHP::CLI,                // only in cli (including websockets)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_FOUND_ROWS => false,                     // Return the number of found (matched) rows = true; the number of changed rows = false. Row level locking will not work if this is set to true
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL
    ];

    public function __construct(
        public string $host = '127.0.0.1',
        public int    $port = 3306,
        public bool   $readOnly = false,
        public string $namespace = Carbons::CLASS_NAMESPACE,
        public string $tablePrefix = Carbons::TABLE_PREFIX,
        public bool   $validateExternalRequests = false,
        public bool   $queryWithDatabaseName = false,
        public bool   $buildWithCarbonTables = true,
        public array  $pdo_options = self::DEFAULT_PDO_OPTIONS {
            set {
                if (null === $this->database) {
                    return;
                }

                foreach ($value as $key => $attribute) {
                    $this->database->setAttribute($key, $attribute);
                }
            }
            get => $this->pdo_options;
        },
        public array  $validatedRestRequests = [] {
            set {
                foreach ($value as $validSql) {
                    is_string($validSql) or throw new PublicAlert("Expected type string, got (" . gettype($validSql) . ')');
                }
                $this->validatedRestRequests = $value;
            }
        }
    )
    {
    }

    // @todo - this needs to be able to loop through multiple insatacces
    private function newInstance(): PDO
    {
        $attempts = 0;

        do {

            try {

                // @link https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement
                set_error_handler(static function () {
                    /* ignore errors and warnings */
                });

                // exceptions will still fall
                $this->database = $db = new PDO(
                    $this->carbonDatabaseDSN,
                    $this->databasePool->user,
                    $this->databasePool->password,
                    $this->pdo_options
                );

                restore_error_handler();

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $db->setAttribute(PDO::ATTR_PERSISTENT, CarbonPHP::CLI);

                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                return $db;

            } catch (Throwable $e) {

                ThrowableHandler::generateLog($e);  // this will exit

            } finally {

                $attempts++;

            }

        } while ($attempts < 3);

        $message = "Failed to connect to database "
            . ($this->readOnly ? 'read only' : 'reader/writer')
            . " after ($attempts) attempts.";

        ColorCode::colorCode($message, iColorCode::RED);

        die(5);

    }

    /** Clears and restarts the PDO connection
     * @return PDO
     */
    public function reset(): PDO // built to help preserve database in sockets and forks
    {

        if (null !== $this->database) {

            ColorCode::colorCode('Running PDO resource reset <close/start>', iColorCode::BACKGROUND_CYAN);

            self::close();

        } else {

            ColorCode::colorCode("Getting new database instance", iColorCode::BACKGROUND_CYAN);

        }

        return self::newInstance();

    }

    public function close(): void
    {

        try {

            if ($this->database instanceof PDO) {
                // @link https://stackoverflow.com/questions/21595402/php-pdo-how-to-get-the-current-connection-status/21595939
                $server_status = $this->database->getAttribute(PDO::ATTR_SERVER_INFO);
                $connection_status = $this->database->getAttribute(PDO::ATTR_CONNECTION_STATUS);
                ColorCode::colorCode("Closing MySQL, Connection Status ::\n$connection_status\nCurrent Server Status ::\n$server_status", iColorCode::BLUE);
                $this->database->exec('KILL CONNECTION_ID();');
            }
        } catch (Throwable) {
            // its common for pdo to throw an error here, we will silently ignore it
            // running KILL CONNECTION_ID() will disconnect the resource before return thus error
        } finally {
            $this->database = null;
        }

    }

    /** Check our database to verify that a transaction
     *  didn't fail after adding an a new primary key.
     *  If verify is run before commit, the transaction
     *  and newly created primary keys will be removed.
     *  Foreign keys are created in the beginTransaction()
     *  method found in this class.
     *
     * @link https://www.w3schools.com/sql/sql_primarykey.asp
     *
     * @return bool
     */
    public function rollBack(): void
    {

        $pdo = $this->database;

        if (!$pdo->inTransaction()) {        // We're verifying that we do not have an un finished transaction

            return;

        }

        $logging = [

        ];

        $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$logging;

        try {

            $status = $pdo->rollBack();

            $logging['ROLLBACK'] = $status;

            if (false === $status) {

                throw new PrivateAlert('Failed to rollback transaction!');

            }  // this transaction was started after our keys were inserted..

            if (!empty($this->carbonDatabaseEntityTransactionKeys)) {

                $logging['$this->carbonDatabaseEntityTransactionKeys'] = $this->carbonDatabaseEntityTransactionKeys;

                foreach ($this->carbonDatabaseEntityTransactionKeys as $key) {

                    static::remove_entity($key);

                }

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    /**
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
     * @param $id - Remove entity_pk form carbon
     * @return bool
     */
    public static function remove_entity($id): bool
    {

        $ref = [];

        $carbons = Rest::getDynamicRestClass(Carbons::class);

        /** @noinspection PhpUndefinedMethodInspection */
        return $carbons::delete($ref, $id, []); // Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]);

    }

    /**
     * @param string $tag_id
     * This will be inserted in out tags tables, it is just a reference
     *  I define constants named after the tables in the configuration file
     *  which I use for this field. ( USERS, MESSAGES, ect...)
     *
     * @param string|null $dependant_carbon_id
     * @return string
     * @throws PrivateAlert
     */
    public function newEntity(string $tag_id, string|null $dependant_carbon_id = null): string
    {

        $carbons = Rest::getDynamicRestClass(Carbons::class, iRestSinglePrimaryKey::class);

        self::beginTransaction();

        $post = [
            $carbons::ENTITY_TAG => $tag_id,
            $carbons::ENTITY_FK => $dependant_carbon_id
        ];

        /** @noinspection PhpUndefinedMethodInspection - intellij is not good at php static refs */
        $id = $carbons::post($post);

        if (false === $id) {

            throw new PrivateAlert('C6 failed to create a new entity.');

        }

        $this->commit(true);

        self::beginTransaction();

        $this->carbonDatabaseEntityTransactionKeys[] = $id;

        return $id;

    }

    /** Based off the pdo.beginTransaction() method
     * @link http://php.net/manual/en/pdo.begintransaction.php
     *
     * Primary keys that are also foreign keys require the references
     * be present before they may be inserted. PDO has built transactions
     * but if you try creating a new row which has a reference to a primary
     * key created in the transaction, it will fail.
     *
     * This <b>must be static</b> so multiple tables files can insert on the same
     * transaction without running beginTransaction again
     *
     * @param bool $strict
     * @return void
     */
    public function beginTransaction(bool $strict = false): void
    {

        $moreInfo = $this->committedTransactions;

        $baseInfo = [
            (__METHOD__ . '($strict = ' . ($strict ? 'true' : 'false') . ')') => &$moreInfo,
            'debug_backtrace()' => CarbonPHP::$verbose
            || false === CarbonPHP::CLI
                ? debug_backtrace()
                : '(CarbonPHP::$verbose || false === CarbonPHP::CLI) = false;'
        ];

        try {

            $db = $this->database;

            $inTransaction = $db->inTransaction();

            if ($inTransaction) {

                if (false === $strict) {

                    if (CarbonPHP::$verbose) {

                        $moreInfo = 'Transaction (' . $this->committedTransactions . ') already started.';

                        $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$baseInfo;
                    }

                    return;

                }

                throw new PrivateAlert('Transaction already started.');

            }

            $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$baseInfo;

            if (false === $db->beginTransaction()) {

                throw new PrivateAlert('Failed to start transaction.');

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }


    /** Commit the current transaction to the database.
     * @link http://php.net/manual/en/pdo.rollback.php
     * @param bool $strict
     * @return bool
     */
    public function commit(bool $strict = false): bool
    {

        try {

            $db = $this->database;

            $transactionActive = $db->inTransaction();

            if (false === $transactionActive
                && false === $strict) {
                return true;
            }

            $transactionNumber = $transactionActive ? $this->committedTransactions++ : $this->committedTransactions;

            $moreLogging = [
                self::class . '->carbonDatabaseEntityTransactionKeys' => $this->carbonDatabaseEntityTransactionKeys,
                ($transactionActive ? '' : 'WARNING no transaction started. ') . __METHOD__ . '($strict = ' . ($strict ? 'true' : 'false') . ')' => $transactionNumber,
                'debug_backtrace()' => CarbonPHP::$verbose || false === CarbonPHP::CLI ? $backtrace = debug_backtrace() : '(CarbonPHP::$verbose || false === CarbonPHP::$cli) = false;'
            ];

            $GLOBALS['json'][Session::class]['sql'] ??= [];

            if (false === $db->inTransaction()) {

                $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$moreLogging;

                $moreLogging['WARNING $db->inTransaction()'] = [
                    'WARNING' => 'Transaction not started',
                    'debug_backtrace()' => $backtrace ?? debug_backtrace()
                ];

                return true;

            }

            if (false === empty($this->session_id)
                && session_status() === PHP_SESSION_ACTIVE) {

                $session = $_SESSION;

                $success = session_write_close();

                // this should be added to the logs directly after session_write_close()
                $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$moreLogging;

                if (false === $success) {

                    throw new PrivateAlert('Session failed to write close in (' . __METHOD__ . ') at line (' . __LINE__ . ').');

                }

                $moreLogging += [
                    'Session ID' => $this->session_id,
                    'Session Status' => [
                        'session_status()' => session_status(),
                        'PHP_SESSION_DISABLED' => PHP_SESSION_DISABLED,
                        'PHP_SESSION_ACTIVE' => PHP_SESSION_ACTIVE,
                        'PHP_SESSION_NONE' => PHP_SESSION_NONE,
                    ],
                    '$_SESSION' => $session,
                    'Session Write Close' => '(this commit closed the session)'
                ];

                $this->carbonDatabaseEntityTransactionKeys = [];

                return true;

            }

            if ($db->commit()) {

                $this->carbonDatabaseEntityTransactionKeys = [];

                return true;

            }

            // rollback
            return static::rollBack();

        } catch (Throwable $e) {

            static::rollBack();

            ThrowableHandler::generateLogAndExit($e);

        }

    }

}