<?php // Parent of Model class, can only be called indirectly

namespace CarbonPHP;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Tables\Carbons;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use stdClass;
use Throwable;
use function array_shift;
use function count;
use function is_array;

/**
 * Class Database
 * @link https://en.wikipedia.org/wiki/Entity–component–system
 * @package Carbon
 *
 * Popular in game development, web apps are perfect
 * candidates for Entity Systems.
 * Databases become complicated when you need
 * a hierarchical system of inheritance.
 * A singular tuple tables containing only primary keys solves
 * this issue. If a tables needs or may need a primary key, you
 * must use the Entities.beginTransaction() &
 *  Entities.commit() methods to generate them.
 */
class Database
{

    private static ?array $pdo_options = null;

    /**
     * @var bool - error catcher needs to initialize quickly,
     * and can relies on a data connection which may not be set here at the moment of its own initialization
     * This bool will determine this use case.
     */
    public static bool $carbonDatabaseInitialized = false;

    /** Represents a connection between PHP and a database server.
     * @link http://php.net/manual/en/class.pdo.php
     * @var PDO|null $database // todo php 8.0
     */
    private static ?PDO $database = null;

    private static ?PDO $databaseReader = null;

    public static bool $rebuildWithCarbonTables = true;

    public static ?string $carbonDatabaseUsername = null;

    public static ?string $carbonDatabasePassword = null;

    public static ?string $carbonDatabaseName = null;

    public static string $carbonDatabasePort = '3306';

    public static ?string $carbonDatabaseHost = null;

    public static ?string $carbonDatabaseReader = null;

    /**
     * @var string|null $carbonDatabaseDSN holds the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public static ?string $carbonDatabaseDSN = null;

    public static ?string $carbonDatabaseReaderDSN = null;

    /**
     * @var string holds the path of the users database set up file
     */
    public static ?string $carbonDatabaseSetup = null;

    /**
     * @var array - new key inserted but not verified currently
     */
    private static array $carbonDatabaseEntityTransactionKeys = [];

    /** the database method will return a connection to the database.
     * Before returning a connection it must pass an active check.
     * This is mainly for persistent socket connections.
     * @return PDO
     */

    public const REMOVE_MYSQL_FOREIGN_KEY_CHECKS = <<<HEAD
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
HEAD;

    /*
     * Must be used in conjunction with REMOVE_MYSQL_FOREIGN_KEY_CHECKS
     */
    public const REVERT_MYSQL_FOREIGN_KEY_CHECKS = <<<FOOT
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
FOOT;

    public static function database(bool $reader): PDO
    {

        self::readerCheck($reader);

        $database = $reader ? self::$databaseReader : self::$database;

        if (null === $database) { // todo - can we get the ini of mysql timeout?

            return static::reset($reader);

        }

        $oldLevel = error_reporting(0);

        $database = $reader ? self::$databaseReader : self::$database;

        try {

            // this has a lot of waist at this point
            $database->prepare('SELECT 1')->execute();

            error_reporting($oldLevel);

            return $database;

        } catch (PDOException $e) {

            ColorCode::colorCode('PDOException ' . get_class($e) . ' ' . $e->getMessage(), iColorCode::BACKGROUND_YELLOW);

            ColorCode::colorCode( $e->getTraceAsString(), iColorCode::YELLOW);

            return static::reset($reader);

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e, true);

            ColorCode::colorCode('Attempting to reset the database. Possible disconnect.', iColorCode::BACKGROUND_YELLOW);

            error_reporting($oldLevel);

            return static::reset($reader);

        }

    }

    public static function readerCheck(bool &$reader): void
    {

        if (true === $reader &&
            (null === self::$carbonDatabaseReader ||
                '' === self::$carbonDatabaseReader)) {

            $reader = false;

        }

    }

    protected static function newInstance(bool $reader = false): PDO
    {

        $attempts = 0;

        self::readerCheck($reader);

        do {

            try {

                // @link https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement
                $user_options = self::getPdoOptions();

                set_error_handler(static function () { /* ignore errors // warnings */
                });

                // exceptions will still fall
                $db = new PDO(
                    $reader ? static::$carbonDatabaseReaderDSN : static::$carbonDatabaseDSN,
                    static::$carbonDatabaseUsername,
                    static::$carbonDatabasePassword,
                    $user_options);

                self::$carbonDatabaseInitialized = true;

                restore_error_handler();

                if ($reader) {

                    self::$databaseReader = $db;

                } else {

                    self::$database = $db;

                }

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $db->setAttribute(PDO::ATTR_PERSISTENT, CarbonPHP::$cli);

                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                return $db;

            } catch (Throwable $e) {

                self::$carbonDatabaseInitialized = false;

                ThrowableHandler::generateLogAndExit($e);  // this will exit

            } finally {

                $attempts++;

            }

        } while ($attempts < 3);

        $databaseType = $reader ? 'reader ' : '';

        $message = "Failed to connect to database {$databaseType}after ($attempts) attempts.";

        ColorCode::colorCode($message, iColorCode::RED);

        die(5);

    }

    /** Clears and restarts the PDO connection
     * @param bool $reader
     * @return PDO
     */
    public static function reset(bool $reader = false): PDO // built to help preserve database in sockets and forks
    {

        self::readerCheck($reader); // check if the connection information is set

        $database = $reader ? self::$databaseReader : self::$database;

        if (null !== $database) {

            ColorCode::colorCode('Running PDO resource reset <close/start>', iColorCode::BACKGROUND_CYAN);

            self::close($reader);

        } else {

            ColorCode::colorCode("Getting new database instance", iColorCode::BACKGROUND_CYAN);

        }

        return self::newInstance($reader);

    }

    public static function close($reader = false): void
    {

        try {

            if ($reader) {

                $db =  &self::$databaseReader;

            } else {

                $db =  &self::$database;

            }


            if ($db instanceof PDO) {

                // @link https://stackoverflow.com/questions/21595402/php-pdo-how-to-get-the-current-connection-status/21595939
                $server_status = $db->getAttribute(PDO::ATTR_SERVER_INFO);

                $connection_status = $db->getAttribute(PDO::ATTR_CONNECTION_STATUS);

                ColorCode::colorCode("Closing MySQL, Connection Status ::\n$connection_status\nCurrent Server Status ::\n$server_status", iColorCode::BLUE);

                $db->exec('KILL CONNECTION_ID();');

            }

        } catch (Throwable) {

            // its common for pdo to throw an error here, we will silently ignore it
            // running KILL CONNECTION_ID() will disconnect the resource before return thus error

        } finally {

            $db = null;

        }

    }

    /** Overwrite the current database. If nothing is given the
     * connection to the database will be closed
     * @param PDO|null $database
     */
    public static function setDatabase(PDO $database = null): void
    {

        if (null === $database
            && self::$database instanceof PDO) {

            self::close();

        }

        self::$database = $database;

    }

    /**
     * @param bool $refresh if set to true will send Javascript
     * to refresh the browser using SITE constant
     * @param bool $cli
     * @param string|null $tableDirectory
     * @return PDO
     */
    public static function setUp(bool $refresh = true, bool $cli = false): PDO
    {

        if ($cli) {

            ColorCode::colorCode('This build is automatically generated by CarbonPHP. When you add a table be sure to re-execute the RestBuilder.', iColorCode::BACKGROUND_CYAN);

            ColorCode::colorCode('Connecting on ' . self::$carbonDatabaseDSN);

        } else {

            print '<h3>This build is automatically generated by CarbonPHP. When you add a table be sure to re-execute the <br><br><br><b>>> php index.php restbuilder</b></h3>'
                . '<h1>Connecting on </h1>' . self::$carbonDatabaseDSN . '<br>';

        }

        self::refreshDatabase();

        if ($refresh && !$cli) {

            print '<br><br><h2>Refreshing in 6 seconds</h2><script>let t1 = window.setTimeout(function(){ window.location.href = \'' . CarbonPHP::$site . '\'; },6000);</script>';

            exit(0);

        }

        return static::database(false);

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
    public static function rollBack(): void
    {

        $pdo = self::database(false);

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

            if (!empty(self::$carbonDatabaseEntityTransactionKeys)) {

                $logging['self::$carbonDatabaseEntityTransactionKeys'] = self::$carbonDatabaseEntityTransactionKeys;

                foreach (self::$carbonDatabaseEntityTransactionKeys as $key) {

                    static::remove_entity($key);

                }

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }


    public static int $committedTransactions = 0;

    /** Commit the current transaction to the database.
     * @link http://php.net/manual/en/pdo.rollback.php
     * @return bool
     */
    public static function commit(bool $strict = false): bool
    {

        try {

            $db = self::database(false);

            $transactionActive = $db->inTransaction();

            if (false === $transactionActive
                && false === $strict) {

                return true;

            }

            $transactionNumber = $transactionActive ? self::$committedTransactions++ : self::$committedTransactions;

            $moreLogging = [
                'Database::$carbonDatabaseEntityTransactionKeys' => self::$carbonDatabaseEntityTransactionKeys,
                ($transactionActive ? '' : 'WARNING no transaction started. ') . __METHOD__ . '($strict = ' . ($strict ? 'true' : 'false') . ')' => $transactionNumber,
                'debug_backtrace()' => CarbonPHP::$verbose || false === CarbonPHP::$cli ? $backtrace = debug_backtrace() : '(CarbonPHP::$verbose || false === CarbonPHP::$cli) = false;'
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

            if (false === empty(Session::$session_id)
                && session_status() === PHP_SESSION_ACTIVE) {

                $session = $_SESSION;

                $success = session_write_close();

                // this should be added to the logs directly after session_write_close()
                $GLOBALS['json']['sql'][] = $GLOBALS['json'][Session::class]['TRANSACTIONS'][] = &$moreLogging;

                if (false === $success) {

                    throw new PrivateAlert('Session failed to write close in (' . __METHOD__ . ') at line (' . __LINE__ . ').');

                }

                $moreLogging += [
                    'Session ID' => Session::$session_id,
                    'Session Status' => [
                        'session_status()' => session_status(),
                        'PHP_SESSION_DISABLED' => PHP_SESSION_DISABLED,
                        'PHP_SESSION_ACTIVE' => PHP_SESSION_ACTIVE,
                        'PHP_SESSION_NONE' => PHP_SESSION_NONE,
                    ],
                    '$_SESSION' => $session,
                    'Session Write Close' => '(this commit closed the session)'
                ];

                self::$carbonDatabaseEntityTransactionKeys = [];

                return true;

            }

            if ($db->commit()) {

                self::$carbonDatabaseEntityTransactionKeys = [];

                return true;

            }

            // rollback
            return static::rollBack();

        } catch (Throwable $e) {

            static::rollBack();

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    /**
     *
     * ATTR_EMULATE_PREPARES - the sessions table stopped working as the php serialized
     *      uses : which cased the driver to fail
     * @return array
     */
    public static function getPdoOptions(): array
    {
        return self::$pdo_options ??= self::getDefaultPdoOptions();
    }

    public static function setPdoOptions(array $pdo_options, PDO|bool $reader): void
    {

        self::$pdo_options = $pdo_options;

        if ($reader instanceof PDO) {

            $database = $reader;

        } else {

            $database = $reader ? self::$databaseReader : self::$database;

        }

        if (null === $database) {

            return;

        }

        foreach ($pdo_options as $key => $value) {

            $database->setAttribute($key, $value);

        }

    }

    public static function getDefaultPdoOptions(): array
    {
        return [
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_PERSISTENT => CarbonPHP::$cli,                // only in cli (including websockets)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_FOUND_ROWS => false,                     // Return the number of found (matched) rows = true; the number of changed rows = false. Row level locking will not work if this is set to true
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL
        ];
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
     * @param string $tag_id - passed to new_entity
     * @param ?string $dependant - passed to new_entity
     *
     * @return string
     * @throws PublicAlert
     */
    public static function beginTransaction(bool $strict = false): void
    {

        $moreInfo = self::$committedTransactions;

        $baseInfo = [
            (__METHOD__ . '($strict = ' . ($strict ? 'true' : 'false') . ')') => &$moreInfo,
            'debug_backtrace()' => CarbonPHP::$verbose || false === CarbonPHP::$cli ? debug_backtrace() : '(CarbonPHP::$verbose || false === CarbonPHP::$cli) = false;'
        ];


        try {

            $db = self::database(false);

            $inTransaction = $db->inTransaction();

            if ($inTransaction) {

                if (false === $strict) {

                    if (CarbonPHP::$verbose) {

                        $moreInfo = 'Transaction (' . self::$committedTransactions . ') already started.';

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

    /** http://php.net/manual/en/language.operators.bitwise.php
     * in actual system we will have to see what bit system we are using
     * 32 bit = 28
     * 64 bit = 60
     * I assume this means php uses 4 bits to denote type (z_val) ?
     * idk
     * @param int $bitLength
     * @return string
     * @throws Exception
     */
    public static function genRandomHex($bitLength = 40): string
    {
        $r = 1;
        for ($i = 0; $i <= $bitLength; $i++) {
            $r = ($r << 1) | random_int(0, 1);
        }
        return dechex($r);
    }

    /**
     * @param $tag_id
     * This will be inserted in out tags tables, it is just a reference
     *  I define constants named after the tables in the configuration file
     *  which I use for this field. ( USERS, MESSAGES, ect...)
     *
     * @param $dependant_carbon_id
     * @return array
     * @throws PublicAlert
     */
    public static function newEntity(string $tag_id, string $dependant_carbon_id = null): string
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

        self::commit(true);

        self::beginTransaction();

        self::$carbonDatabaseEntityTransactionKeys[] = $id;

        return $id;

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


    public static function execute(string $sql, ...$execute): bool
    {

        $reader = false === self::isWriteQuery($sql);

        return self::database($reader)->prepare($sql)->execute($execute);
    }

    /**
     * Use prepared statements with question mark values.
     * @link https://www.w3schools.com/php/php_mysql_prepared_statements.asp
     *
     * Pass parameters separated by commas in order denoted by the sql stmt
     *
     * Example:
     *  $array = static::fetch('SELECT * FROM user WHERE user_id = ?', $id);
     *
     * @deprecated
     * @param string $sql
     * @param mixed ...$execute
     * @link http://php.net/manual/en/functions.arguments.php
     * @return array
     */
    protected static function fetch(string $sql, ...$execute): array
    {
        try {

            $reader = false === self::isWriteQuery($sql);

            $stmt = self::database($reader)->prepare($sql);

            if (!$stmt->execute($execute) && !$stmt->execute($execute)) { // try it twice, you never know..
                return [];
            }

            // @deprecated
            if (count($stmt = $stmt->fetchAll(PDO::FETCH_ASSOC)) !== 1) {

                return $stmt;

            }

            if (is_array($stmt)) {

                $stmt = array_shift($stmt);

            }

            return $stmt;   // promise this is needed and will still return the desired array

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);  // this terminates

            exit(1);

        }

    }

    /**
     * @param string $sql
     * @param ...$execute
     * @return array|bool
     */
    public static function fetchAll(string $sql, ...$execute): false|array
    {

        try {

            $reader = false === self::isWriteQuery($sql);

            $stmt = self::database($reader)->prepare($sql);

            if (false === $stmt->execute($execute)) {

                throw new PrivateAlert("Failed to execute query ($sql).");

            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);   // promise this is needed and will still return the desired array

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);  // this terminates

        }

    }


    /**
     * Same as fetchAll but allows for custom PDO fetch modes to apply to the result
     * @param string $sql
     * @param int $fetchMode
     * @param ...$execute
     * @return array|false
     */
    public static function fetchAllCustom(string $sql, int $fetchMode = PDO::FETCH_ASSOC, ...$execute): array|false
    {

        try {

            $reader = false === self::isWriteQuery($sql);

            $stmt = self::database($reader)->prepare($sql);

            if (false === $stmt->execute($execute)) {

                throw new PrivateAlert("Failed to execute query ($sql).");

            }

            return $stmt->fetchAll($fetchMode);   // promise this is needed and will still return the desired array

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);  // this terminates

            exit(112);

        }

    }


    /** Quickly prepare and execute PDO $sql statements using
     * @NOTE PDO's version of fetchColumn() will fail on statements like SHOW TABLES;
     * @TODO can we optimise based on when we know it will fail?
     *  variable arguments.
     *
     * Example:
     * $array['following'] = self::fetchColumn('SELECT follows_user_id FROM user_followers WHERE user_id = ?', $id);
     *
     * @param string $sql - variables should be denoted by question marks
     * @param ...$execute -
     *  if multiple question marks exist you may use comma separated parameters to fill the statement
     * @return array
     */
    public static function fetchColumn(string $sql, ...$execute): array
    {
        $reader = false === self::isWriteQuery($sql);

        $stmt = self::database($reader)->prepare($sql);

        if (!$stmt->execute($execute)) {

            return [];

        }

        // pdo's version of fetchColumn is flawed see note
        $count = count($stmt = $stmt->fetchAll(PDO::FETCH_ASSOC));

        if ($count === 0) {
            return $stmt;
        }

        if ($count === 1) {
            while (is_array($stmt)) {
                $stmt = array_shift($stmt);
            }
            return [$stmt];
        }

        foreach ($stmt as &$value) {

            while (is_array($value)) {

                $value = array_shift($value);

            }

        }

        return $stmt;

    }

    /** TODO - see if this even still works
     *
     * This returns all values from the requested query as an Object to type stdClass.
     *  Its important to note that PHP arrays are hash tables. This means that
     *  while semantically pleasing, fetching into an object should be avoided
     *
     * @param string $sql
     * @param array ...$execute
     * @return stdClass
     * @throws Exception
     */
    protected static function fetch_object(string $sql, ...$execute): stdClass
    {

        $reader = false === self::isWriteQuery($sql);

        $stmt = self::database($reader)->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_CLASS, \stdClass::class);

        if (!$stmt->execute($execute)) {

            throw new RuntimeException('Failed to Execute');

        }

        $stmt = $stmt->fetchAll();  // user obj

        return (is_array($stmt) && count($stmt) === 1 ? $stmt[0] : new stdClass);

    }

    /** Each row received will be converted into its own object
     * @param string $sql
     * @param array ...$execute
     * @return array of stdClass::class objects
     */
    protected static function fetch_classes(string $sql, ...$execute): array
    {

        $reader = false === self::isWriteQuery($sql);

        $stmt = self::database($reader)->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_CLASS, \stdClass::class);

        if (!$stmt->execute($execute)) {

            return [];

        }

        return $stmt->fetchAll();  // user obj

    }

    /** Run an sql statement and return results as attributes of a stdClass
     * @param $object
     * @param $sql
     * @param array ...$execute
     */
    protected static function fetch_into_class(&$object, $sql, ...$execute): void
    {
        $reader = false === self::isWriteQuery($sql);

        $stmt = self::database($reader)->prepare($sql);

        $stmt->execute($execute);

        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($array as $key => $value) {

            $object->$key = $value;

        }

    }

    protected static function runRefreshSchema(array $REFRESH_SCHEMA): void
    {
        try {

            foreach ($REFRESH_SCHEMA as $key => $validation) {

                if (!is_int($key)) {

                    throw new PrivateAlert('All members of REFRESH_SCHEMA must be callables or arrays with integer keys. Note: callables are not allowed in constants.');

                }

                if (!is_array($validation)) {

                    if (!is_callable($validation)) {

                        throw new PrivateAlert('Each REFRESH_SCHEMA should equal an array of arrays with [ call => method , structure followed by any additional arguments ]. Optionally a public member array $REFRESH_SCHEMA maybe used to explicitly reference using callables. Refer to Carbonphp.com for more information.');

                    }

                    if (false === $validation()) {

                        throw new PrivateAlert("Any method used in REFRESH_SCHEMA must not return false. A failure was caught in a callable. This typically can be tough debugging. ");

                    }

                    continue;

                }

                $class = array_key_first($validation);          //  $class => $method

                $validationMethod = $validation[$class];

                unset($validation[$class]);

                if (!class_exists($class)) {

                    throw new PrivateAlert("A class reference in REFRESH_SCHEMA failed. Class ($class) not found.");

                }

                if (false === call_user_func([$class, $validationMethod], ...$validation)) {

                    throw new PrivateAlert("Any method used in REFRESH_SCHEMA must not return false. $class => $validationMethod returned with error.");

                }

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }
    }


    public static function scanAnd(callable $callback, string $tableDirectory = null): void
    {

        $tableDirectory ??= Rest::autoTargetTableDirectory();

        $restful = glob($tableDirectory . '*.php');

        if (empty($restful)) {

            ColorCode::colorCode("\n\n\nWARNING: No tables found in the directory ($tableDirectory).\n\n\n", iColorCode::BACKGROUND_YELLOW);

            return;

        }

        $classNamespace = Rest::getRestNamespaceFromFileList($restful);

        foreach ($restful as $file) {

            $className = ucwords(basename($file, '.php'), '_');

            if (false === class_exists($table = substr($classNamespace, 0, -1) . $className)) {
                ColorCode::colorCode("\n\nCouldn't load the class '$table' for refresh. This may indicate your file 
                    contains a syntax error or is not generated by the restful API.\n", iColorCode::YELLOW);
                continue;
            }

            if (false === is_subclass_of($table, Rest::class)) {

                $restFullyQualifiedName = Rest::class;

                ColorCode::colorCode("\n\nThe class '$table' does not implement $restFullyQualifiedName.
                        This would indicate a custom class in the table's namespaced directory. Please avoid doing this.\n", iColorCode::YELLOW);

                continue;

            }

            $imp = array_map('strtolower', array_keys(class_implements($table)));

            if (!in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
            ) {

                ColorCode::colorCode("The table ($table) did not interface the required (iRestMultiplePrimaryKeys, iRestSinglePrimaryKey, or iRestNoPrimaryKey). This is unexpected.", iColorCode::RED);

                continue;

            }

            $callback($table);
        }
    }

    public static function foreignKeyConstraintExistsThenDrop($externalTableName, $externalColumnName, $internalTableName, $internalColumnName): void
    {

        try {

            $info = self::selectForeignKeyConstraintInfo($externalTableName, $externalColumnName, $internalTableName, $internalColumnName);

            if (0 === count($info)) {

                return;

            }

            $constraintName = $info['CONSTRAINT_NAME'];

            if (null === $constraintName) {

                throw new PrivateAlert('Parsed fk constraint but not its name. (' . print_r($info, true) . ')');

            }

            if (false === self::execute($sql = "alter table $internalTableName drop foreign key $constraintName;")) {

                throw new PrivateAlert("Failed to execute ($sql)");

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

    }

    public static function selectForeignKeyConstraintInfo($externalTableName, $externalColumnName, $internalTableName, $internalColumnName): array
    {
        // @link https://stackoverflow.com/questions/4004205/show-constraints-on-tables-command
        $verifySqlConstraint = /** @lang MySQL */
            'SELECT cols.TABLE_NAME, cols.COLUMN_NAME, cols.ORDINAL_POSITION,
       cols.COLUMN_DEFAULT, cols.IS_NULLABLE, cols.DATA_TYPE, links.CONSTRAINT_NAME,
       cols.CHARACTER_MAXIMUM_LENGTH, cols.CHARACTER_OCTET_LENGTH,
       cols.NUMERIC_PRECISION, cols.NUMERIC_SCALE,
       cols.COLUMN_TYPE, cols.COLUMN_KEY, cols.EXTRA,
       cols.COLUMN_COMMENT, refs.REFERENCED_TABLE_NAME, refs.REFERENCED_COLUMN_NAME,
       cRefs.UPDATE_RULE, cRefs.DELETE_RULE,
       links.TABLE_NAME, links.COLUMN_NAME,
       cLinks.UPDATE_RULE, cLinks.DELETE_RULE
FROM INFORMATION_SCHEMA.`COLUMNS` as cols
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS refs
                   ON refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.TABLE_NAME=cols.TABLE_NAME
                       AND refs.COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cRefs
                   ON cRefs.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cRefs.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS links
                   ON links.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_NAME=cols.TABLE_NAME
                       AND links.REFERENCED_COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cLinks
                   ON cLinks.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cLinks.CONSTRAINT_NAME=links.CONSTRAINT_NAME
WHERE cols.TABLE_SCHEMA=?
    AND links.REFERENCED_TABLE_NAME = ?
    AND links.REFERENCED_COLUMN_NAME = ?
    AND links.TABLE_NAME = ?
    AND links.COLUMN_NAME = ?
';

        $return = self::fetch($verifySqlConstraint, self::$carbonDatabaseName, $externalTableName,
            $externalColumnName, $internalTableName, $internalColumnName);

        if (false === $return) {

            throw new PrivateAlert('Failed to capture constraint info.');

        }

        return $return;

    }

    public static function verifyAndCreateForeignKeyRelations(string $fullyQualifiedClassName, callable $cb): bool
    {

        $constraintsAdded = $failureEncountered = false;

        foreach ($fullyQualifiedClassName::INTERNAL_TABLE_CONSTRAINTS as $internalTableColumn => $externalTableColumn) {

            $ignoreRef = '';

            [$externalTableName, $externalColumnName] = explode('.', $externalTableColumn);

            self::addTablePrefix($externalTableName, $fullyQualifiedClassName::TABLE_PREFIX, $ignoreRef);

            [$internalTableName, $internalColumnName] = explode('.', $internalTableColumn);

            self::addTablePrefix($internalTableName, $fullyQualifiedClassName::TABLE_PREFIX, $ignoreRef);

            $constraintName = $fullyQualifiedClassName::PDO_VALIDATION[$internalTableColumn][iRest::COLUMN_CONSTRAINTS][$externalTableColumn][iRest::CONSTRAINT_NAME];

            $onDelete = $fullyQualifiedClassName::PDO_VALIDATION[$internalTableColumn][iRest::COLUMN_CONSTRAINTS][$externalTableColumn][iRest::DELETE_RULE];

            $onUpdate = $fullyQualifiedClassName::PDO_VALIDATION[$internalTableColumn][iRest::COLUMN_CONSTRAINTS][$externalTableColumn][iRest::UPDATE_RULE];

            // @link https://stackoverflow.com/questions/4004205/show-constraints-on-tables-command
            $verifySqlConstraint = /** @lang MySQL */
                'SELECT cols.TABLE_NAME, cols.COLUMN_NAME, cols.ORDINAL_POSITION,
       cols.COLUMN_DEFAULT, cols.IS_NULLABLE, cols.DATA_TYPE,
       cols.CHARACTER_MAXIMUM_LENGTH, cols.CHARACTER_OCTET_LENGTH,
       cols.NUMERIC_PRECISION, cols.NUMERIC_SCALE,
       cols.COLUMN_TYPE, cols.COLUMN_KEY, cols.EXTRA,
       cols.COLUMN_COMMENT, refs.REFERENCED_TABLE_NAME, refs.REFERENCED_COLUMN_NAME,
       cRefs.UPDATE_RULE, cRefs.DELETE_RULE,
       links.TABLE_NAME, links.COLUMN_NAME,
       cLinks.UPDATE_RULE, cLinks.DELETE_RULE
FROM INFORMATION_SCHEMA.`COLUMNS` as cols
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS refs
                   ON refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.TABLE_NAME=cols.TABLE_NAME
                       AND refs.COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cRefs
                   ON cRefs.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cRefs.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS links
                   ON links.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_NAME=cols.TABLE_NAME
                       AND links.REFERENCED_COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cLinks
                   ON cLinks.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cLinks.CONSTRAINT_NAME=links.CONSTRAINT_NAME
WHERE cols.TABLE_SCHEMA=?
    AND links.REFERENCED_TABLE_NAME = ?
    AND links.REFERENCED_COLUMN_NAME = ?
    AND links.TABLE_NAME = ?
    AND links.COLUMN_NAME = ?
    AND links.CONSTRAINT_NAME = ?
    AND cLinks.DELETE_RULE = ?
    AND cLinks.UPDATE_RULE = ?
';

            $values = self::fetchAll($verifySqlConstraint, self::$carbonDatabaseName, $externalTableName,
                $externalColumnName, $internalTableName, $internalColumnName, $constraintName, $onDelete, $onUpdate);

            if ([] === $values) {

                ColorCode::colorCode("Failed to verify that the table ($internalTableName) contains FOREIGN KEY NAME ($constraintName) CONSTRAINT ($externalTableName.$externalColumnName) => ($internalTableName.$internalColumnName) using sql ($verifySqlConstraint)", iColorCode::BACKGROUND_YELLOW);

                ColorCode::colorCode(" key values (" . self::$carbonDatabaseName . ", $externalTableName, $externalColumnName, $internalTableName, $internalColumnName, $constraintName, $onDelete, $onUpdate) respectively.", iColorCode::BACKGROUND_CYAN);

                // todo - allow composite primary keys to automated, it should be a another loop through the $fullyQualifiedClassName::INTERNAL_TABLE_CONSTRAINTS
                // A composite foreign key is a foreign key that consists of two or more columns. It is important to note that all the columns in a single foreign key must point to the same table. (one row in one table)
                self::recreateColumnForeignKeyConstraint(
                    $constraintName, $internalTableName,
                    $internalColumnName, $externalTableName,
                    $externalColumnName, $onDelete, $onUpdate);

                $constraintsAdded = true;

            }

            ColorCode::colorCode("Verified relation $internalTableName.$internalColumnName => $externalTableName.$externalColumnName; onDelete $onDelete; onUpdate $onUpdate", iColorCode::BACKGROUND_MAGENTA);

        }


        if ($constraintsAdded) {

            $cb();

        }

        return false === $failureEncountered;

    }


    /**
     * @throws Throwable
     */
    public static function createDatabaseIfNotExist(): void
    {

        $query = explode(';', static::$carbonDatabaseDSN);    // I programmatically put it there which is why..

        $db_name = explode('=', $query[1])[1];  // I dont validate with count on this

        if (empty($db_name)) {

            throw new PrivateAlert('Failed to parse the database name. Please look at the mysql connection information.');

        }

        try {

            // https://www.php.net/manual/en/pdo.setattribute.php
            static::$database = new PDO(
                $query[0],
                static::$carbonDatabaseUsername,
                static::$carbonDatabasePassword,
                self::getPdoOptions());

        } catch (PDOException $e) {

            ColorCode::colorCode("\n\nFailed to connect to the database using the following DSN (" . static::$carbonDatabaseDSN . ") => ($query[0])" . (CarbonPHP::$app_root ? "\nUsing username: (" . static::$carbonDatabaseUsername .
                    ")\npassword: (" . static::$carbonDatabasePassword . ")\n\n" : '') . "\n\n", iColorCode::BACKGROUND_RED);

            throw $e;

        }


        $stmt = "CREATE DATABASE IF NOT EXISTS $db_name;";

        $db = static::$database;

        if (!$db->prepare($stmt)->execute()) {

            throw new PrivateAlert("The following mysql command failed 'CREATE DATABASE IF NOT EXISTS ($db_name)'");

        }

        $db->exec("use $db_name");

    }


    public static array $tablesToValidateAfterRefresh = [];

    /**
     * @throws PublicAlert
     */
    public static function scanAndRunRefreshDatabase(string $tableDirectory): bool
    {

        static $validatedTables = [];

        ColorCode::colorCode("\n\nScanning and running refresh database using ('$tableDirectory' . '*.php')");

        self::scanAnd(static function (string $table): void {

            if (defined("$table::TABLE_NAME")
                && defined("$table::CREATE_TABLE_SQL")) {

                $tableName = $table::TABLE_NAME;

                $sql = $table::CREATE_TABLE_SQL;

                self::addTablePrefix($tableName, $table::TABLE_PREFIX, $sql);

                self::compileMySqlStatementsAndExecuteWithoutForeignKeyChecks($sql);

            } else {

                ColorCode::colorCode("The generated constant $table::TABLE_NAME or $table::CREATE_TABLE_SQL does not exist in the class. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

            }

        }, $tableDirectory);

        ColorCode::colorCode('Running Compiled Table Creates.');

        self::compileMySqlStatementsAndExecuteWithoutForeignKeyChecks();

        $getCurrentSchema = self::$carbonDatabaseName;

        ColorCode::colorCode("\n\n\nDone Creating New Tables. <$getCurrentSchema>\n\n", iColorCode::BACKGROUND_GREEN);

        self::scanAnd(static function (string $table): void {

            if (!defined("$table::COLUMNS")) {

                ColorCode::colorCode("The generated constant $table::COLUMNS does not exist. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

                exit(24);

            }

            if (!defined("$table::TABLE_NAME")) {

                ColorCode::colorCode("The generated constant $table::TABLE_NAME does not exist. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

                exit(25);

            }

            if (!defined("$table::PDO_VALIDATION")) {

                ColorCode::colorCode("The generated constant $table::PDO_VALIDATION does not exist. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

                exit(26);

            }

            $pdoValidations = $table::PDO_VALIDATION;

            $tableName = $table::TABLE_NAME;

            $compiledColumns = $table::COLUMNS;

            foreach ($compiledColumns as $fullyQualified => $shortName) {

                if (false === array_key_exists(iRest::NOT_NULL, $pdoValidations[$fullyQualified])) {

                    ColorCode::colorCode("The generated constant $table::PDO_VALIDATION does not contain the key (iRest::NOT_NULL). Rerun RestBuilder to repopulate." . print_r($pdoValidations, true), iColorCode::YELLOW);

                    exit(24);

                }

                $notNull = $pdoValidations[$fullyQualified][iRest::NOT_NULL] ? ' NOT NULL ' : '';

                $autoIncrement = $pdoValidations[$fullyQualified][iRest::AUTO_INCREMENT] ? ' AUTO_INCREMENT ' : '';

                $maxLength = $pdoValidations[$fullyQualified][iRest::MAX_LENGTH] ?? '';

                $type = $pdoValidations[$fullyQualified][iRest::MYSQL_TYPE];

                $maxLength = '' === $maxLength ? '' : "($maxLength) ";

                $sql = 'ALTER TABLE ' . $tableName . ' ADD ' . $shortName
                    . ' ' . $type . $maxLength
                    . $notNull . $autoIncrement
                    . (array_key_exists('default', $pdoValidations[$fullyQualified])
                        ? ' DEFAULT ' . ($pdoValidations[$fullyQualified][iRest::DEFAULT_POST_VALUE] ?? 'NULL')
                        : '')
                    . (array_key_exists(iRest::COMMENT, $pdoValidations[$fullyQualified])
                        ? ' ' . iRest::COMMENT . ' \'' . $pdoValidations[$fullyQualified][iRest::COMMENT] . '\''
                        : '')
                    . ';';


                self::columnExistsOrExecuteSQL($shortName, $table, $sql);

                ColorCode::colorCode("Verified column ($fullyQualified) exists <$table>.", iColorCode::BACKGROUND_MAGENTA);

                $maxLength = $pdoValidations[$fullyQualified][iRest::MAX_LENGTH] ?? '';

                $maxLength = '' === $maxLength || ($maxLength === '1' && $type === 'tinyint')
                    ? '' : "($maxLength)";


                self::columnIsTypeOrChange($shortName, $table,
                    $pdoValidations[$fullyQualified][iRest::MYSQL_TYPE] . $maxLength);

            }

            if (!defined("$table::REFRESH_SCHEMA")) {

                ColorCode::colorCode("The generated constant $table::REFRESH_SCHEMA does not exist. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

                exit(24);

            }

            $refreshFunctions = $table::REFRESH_SCHEMA;

            if (property_exists($table, 'REFRESH_SCHEMA')) {

                $tableInstantiated = new $table;

                $refreshFunctions += $tableInstantiated->REFRESH_SCHEMA;

            }

            if (0 < count($refreshFunctions)) {

                ColorCode::colorCode("Running refresh schema for ($table) in database <" . self::$carbonDatabaseName . ">", iColorCode::BACKGROUND_CYAN);

                self::runRefreshSchema($refreshFunctions);

            } else {

                ColorCode::colorCode("No refresh schema for ($table) in database <" . self::$carbonDatabaseName . ">", iColorCode::CYAN);

            }

            self::$tablesToValidateAfterRefresh[$table] = $table::CREATE_TABLE_SQL;

            $db = self::database(false);

            if ($db->inTransaction()) {

                ColorCode::colorCode('We are in a transaction.', iColorCode::YELLOW);

            }

            if (false === self::commit()) {

                ColorCode::colorCode('Failed to commit to database!', iColorCode::RED);

                exit(1);

            }

        }, $tableDirectory);

        if (file_exists($filename = CarbonPHP::$app_root . 'mysqldump.sql')) {

            try {

                unlink($filename); // I dont care if this works

            } catch (Throwable $e) {

                ThrowableHandler::generateLog($e, true);

            }

        }

        ColorCode::colorCode("\n\n\nDone with REFRESH_SCHEMA! <" . self::$carbonDatabaseDSN . ">\n\n", iColorCode::BACKGROUND_CYAN);

        $mysqldump = '';

        $getCurrentSchema = static function () use (&$mysqldump) {

            // Now Validate The Rest Tables Based on The MySQL Dump after update.
            $mysqldump = MySQL::mysqldump();

            sleep(1);   // wait for last command

            if (!file_exists($mysqldump)) {

                ColorCode::colorCode("Could not load mysql dump file created at <$mysqldump>" . PHP_EOL);

                exit(1);

            }

            $mysqldump = file_get_contents($mysqldump);

            if (empty($mysqldump)) {

                ColorCode::colorCode("Contents of the mysql dump file <$mysqldump> appears empty. Build Failed!");

                exit(1);

            }

        };

        $getCurrentSchema();

        $regex = '#CREATE\s+TABLE(.|\s)+?(?=ENGINE=)ENGINE=.+;#';

        $failureEncountered = false;

        $databaseName = self::$carbonDatabaseName;

        foreach (self::$tablesToValidateAfterRefresh as $fullyQualifiedClassName => $autoGeneratedSQL) {

            if ($failureEncountered) {

                ColorCode::colorCode("DB <$databaseName> refresh FAILED ABOVE; please keep scrolling above ($fullyQualifiedClassName) for #1st error.", iColorCode::BLUE);

            }

            if (defined("$fullyQualifiedClassName::VALIDATE_AFTER_REBUILD") && false === $fullyQualifiedClassName::VALIDATE_AFTER_REBUILD) {

                ColorCode::colorCode("The class constant ($fullyQualifiedClassName::VALIDATE_AFTER_REBUILD) is set to false. Skipping...");

                continue;

            }

            $tableName = $fullyQualifiedClassName::TABLE_NAME;

            $matches = [];

            if (null === $autoGeneratedSQL
                || false === preg_match_all($regex, $autoGeneratedSQL, $matches)) {

                ColorCode::colorCode('Verifying schema failed during preg_match_all for sql ' . $autoGeneratedSQL, iColorCode::RED);

                exit(70);

            }

            $autoGeneratedSQL = $matches[0][0] ?? false;

            if (!$autoGeneratedSQL) {

                ColorCode::colorCode("Regex failed to match a schema using preg_match_all('$regex', '$autoGeneratedSQL',...", iColorCode::RED);

                exit(71);

            }

            self::addTablePrefix($tableName, $fullyQualifiedClassName::TABLE_PREFIX, $autoGeneratedSQL);

            if (null === $autoGeneratedSQL) {

                throw new PrivateAlert("The \$preUpdateSQL variable is null; this is very unexpected. \n\n" . print_r(self::$tablesToValidateAfterRefresh, true));

            }

            if (in_array($tableName, $validatedTables, true)) {

                ColorCode::colorCode("The table [C6] ($tableName) has already been validated. Skipping...");

                continue;

            }

            $validatedTables[] = $tableName;

            $postUpdateSQL = '';

            $pregMatchSchema = static function () use (&$postUpdateSQL, $tableName, $autoGeneratedSQL, &$mysqldump, $getCurrentSchema, &$failureEncountered): bool {

                static $hasRun = [];

                if (false === in_array($tableName, $hasRun)) {

                    $hasRun[] = $tableName;

                } else {

                    $getCurrentSchema();

                }

                $table_regex = "#CREATE\s+TABLE\s`$tableName`(.|\s)+?(?=ENGINE=)ENGINE=.+;#";

                if (false === preg_match_all($table_regex, $mysqldump, $matches)) {

                    ColorCode::colorCode("Verifying schema using regex ($table_regex) failed during preg_match_all on the ./mysqlDump.sql", iColorCode::RED);

                    exit(72);

                }

                $postUpdateSQL = $matches[0][0] ?? false;

                if (false === $postUpdateSQL) {

                    ColorCode::colorCode("Regex failed to match a schema using preg_match_all('$table_regex', '$mysqldump',...", iColorCode::RED);

                    exit(75);

                }

                return true;

            };

            if (false === $pregMatchSchema()) {

                continue;

            }

            if (false === self::verifyAndCreateForeignKeyRelations($fullyQualifiedClassName, $pregMatchSchema)) {

                ColorCode::colorCode("Failed during verifyAndCreateForeignKeyRelations:", iColorCode::RED);

                exit(76);

            }

            if ($failureEncountered) {

                exit(77);

            }

            // Rest::parseSchemaSQL() is only done on $preUpdateSQL for legacy builds
            // we add 'CONSTRAINT\s`.*' => '' only to the post updated query as AWS will not include
            // FK constraints in mysql dump files // post update
            $awsLoose = Interfaces\iRest::SQL_IRRELEVANT_REPLACEMENTS + Interfaces\iRest::SQL_VERSION_PREG_REPLACE;

            $preUpdateSQLArray = Rest::parseSchemaSQL($autoGeneratedSQL, $awsLoose);

            $postUpdateSQLArray = Rest::parseSchemaSQL($postUpdateSQL, $awsLoose);

            // the table definition maybe reordered and we just want to know whats dif
            $preUpdateSQLArray = array_map('trim', $preUpdateSQLArray);

            $postUpdateSQLArray = array_map('trim', $postUpdateSQLArray);

            $changesOne = array_diff($preUpdateSQLArray, $postUpdateSQLArray);

            $changesTwo = array_diff($postUpdateSQLArray, $preUpdateSQLArray);

            // safe compare multibyte strings
            if ([] !== $changesOne || $changesTwo !== []) {

                $autoGeneratedSQL = trim(Rest::reformatLoosenedSQL($preUpdateSQLArray));

                // parseSchemaSQL is needed as dif versions of mysql will dump diff things.
                $postUpdateSQL = trim(str_replace("\\n", "\n", Rest::reformatLoosenedSQL($postUpdateSQLArray)));

                ColorCode::colorCode('Oh No! After running the database updated it looks like the sql found in'
                    . " the mysql dump file did not match the expected. Any updates done to the database should be automated in the $fullyQualifiedClassName::REFRESH_SCHEMA[] definition. "
                    . "If this is not a table you manage, but rather 3rd-party generated, you should change "
                    . "($fullyQualifiedClassName::VALIDATE_AFTER_REBUILD = false;) and re-try; this can also be set to "
                    . ' false if you would like to manage table definition(s) using other means.'
                    . ' To update your table using REFRESH_SCHEMA, please refer to the documentation that is been provided'
                    . " above this constant in the php class for ($tableName).", iColorCode::RED);

                ColorCode::colorCode("If the new SQL appears correct you probably"
                    . " just need to re-run the RestBuilder program (not the database rebuild program currently raising error).", iColorCode::BACKGROUND_YELLOW);

                ColorCode::colorCode("Due to version differences in how MySQLDump will print your schema, the following are used with preg_replace to `loosen` the condition PHP array_diff must meet ::\n" . json_encode($awsLoose, JSON_PRETTY_PRINT) . "\n\n", iColorCode::BACKGROUND_CYAN);

                ColorCode::colorCode("Expected (From auto generated php class <$tableName>):: $autoGeneratedSQL\n\n", iColorCode::YELLOW);

                ColorCode::colorCode("GOT (post-updated sql) :: $postUpdateSQL\n\n", iColorCode::BLUE);    // I want to bring your attention back to the red ^^ then down to blue

                ColorCode::colorCode("\tChanges <$databaseName>\n", iColorCode::ITALIC);
                ColorCode::colorCode("\tWhat's currently in your database:", iColorCode::CYAN);
                ColorCode::colorCode("Needs to be added or modified to code :: ", iColorCode::YELLOW);
                ColorCode::colorCode("Note: Should the changes below in (cyan) be newer than what is previously currently generated in code (red), you need only rebuild the autogenerated rest classes.", iColorCode::MAGENTA);
                ColorCode::colorCode('preg_replace\'d :: ' . json_encode($changesTwo, JSON_PRETTY_PRINT) . "\n\n", iColorCode::CYAN);
                ColorCode::colorCode("\tWhat was missing from your database based on whats in your php rest code:", iColorCode::RED);
                ColorCode::colorCode("Needs to be removed or modified from code:: ", iColorCode::YELLOW);
                ColorCode::colorCode('preg_replace\'d :: ' . json_encode($changesOne, JSON_PRETTY_PRINT) . "\n\n", iColorCode::RED);

                ColorCode::colorCode('Only the `preg_replace` differences need be changed to complete with success.');

                $failureEncountered = true;

            }

            ColorCode::colorCode("Table `$tableName` was verified.");

        }

        if ($failureEncountered) {

            exit(79);

        }

        return true;

    }


    /**
     * @param bool|null $cli
     */
    public static function refreshDatabase(bool $cli = null): void
    {

        if (null === $cli) {

            $cli = CarbonPHP::$cli;

        }

        try {

            $tableDirectory = Rest::autoTargetTableDirectory();

            $isC6 = $tableDirectory === Carbons::DIRECTORY;

            if ($cli) {

                ColorCode::colorCode('(Setup || Rebuild) Database');

            } else {

                print '<html lang="en"><head><title>(Setup || Rebuild) Database</title></head><body><h1>REFRESHING SYSTEM</h1>' . PHP_EOL;

            }

            ColorCode::colorCode('Building CarbonPHP [C6] Tables', iColorCode::CYAN);


            self::createDatabaseIfNotExist();


            if (false === $isC6 && true === self::$rebuildWithCarbonTables) {

                $status = self::scanAndRunRefreshDatabase(Carbons::DIRECTORY);

                ColorCode::colorCode('CarbonPHP [C6] Tables Built ' . ($status ? '<success>' : '<failure>'),
                    $status ? iColorCode::CYAN : iColorCode::RED);

            }

            $status = self::scanAndRunRefreshDatabase($tableDirectory) && ($status ?? true);

            if (true === $status) {

                ColorCode::colorCode('Success!');

            } else {

                ColorCode::colorCode('Failed refreshing schema; view output above for more information!', iColorCode::RED);

                exit(1);

            }

            ColorCode::colorCode('After refreshing the database one should rerun the RestBuilder program to capture any changes made to tables with (public const VALIDATE_AFTER_REBUILD = false;)!', iColorCode::CYAN);

        } catch (Throwable $e) {

            if ($cli) {

                ColorCode::colorCode('The refreshDatabase method failed.', iColorCode::BACKGROUND_RED);

            } else {

                print '<h2>The refreshDatabase method failed.</h2>';

            }

            ThrowableHandler::generateLog($e);

            exit(1);        // exit 1 is phpunit // composer scripts safe to === error

        }

    }

    public static function recreateColumnForeignKeyConstraint(
        string $constraintName,
        string $tableName,
        string $columnName,
        string $referenceTable,
        string $referenceColumn,
        string $onDelete, string $onUpdate): void
    {

        $getCurrentConstraintName = /** @lang MySQL */
            "SELECT 
    links.CONSTRAINT_NAME,
    cols.TABLE_NAME, cols.COLUMN_NAME, cols.ORDINAL_POSITION,
    cols.COLUMN_DEFAULT, cols.IS_NULLABLE, cols.DATA_TYPE,
    cols.CHARACTER_MAXIMUM_LENGTH, cols.CHARACTER_OCTET_LENGTH,
    cols.NUMERIC_PRECISION, cols.NUMERIC_SCALE,
    cols.COLUMN_TYPE, cols.COLUMN_KEY, cols.EXTRA,
    cols.COLUMN_COMMENT, refs.REFERENCED_TABLE_NAME, refs.REFERENCED_COLUMN_NAME,
    cRefs.UPDATE_RULE, cRefs.DELETE_RULE,
    links.TABLE_NAME, links.COLUMN_NAME,
    cLinks.UPDATE_RULE, cLinks.DELETE_RULE
FROM INFORMATION_SCHEMA.`COLUMNS` as cols
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS refs
                   ON refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.TABLE_NAME=cols.TABLE_NAME
                       AND refs.COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cRefs
                   ON cRefs.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cRefs.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS links
                   ON links.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_NAME=cols.TABLE_NAME
                       AND links.REFERENCED_COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cLinks
                   ON cLinks.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cLinks.CONSTRAINT_NAME=links.CONSTRAINT_NAME
WHERE cols.TABLE_SCHEMA=?
AND links.TABLE_NAME = ?
AND links.COLUMN_NAME = ?
AND links.REFERENCED_COLUMN_NAME = ?
AND links.REFERENCED_TABLE_NAME = ?";

        $constraintOld = self::fetchAll($getCurrentConstraintName,
            self::$carbonDatabaseName, $tableName, $columnName, $referenceColumn, $referenceTable)[0] ?? null;

        $oldConstraintName = $constraintOld['CONSTRAINT_NAME'] ?? null;

        if (null !== $oldConstraintName) {  // [0] ?? null;

            ColorCode::colorCode("Dropping old constraint ($oldConstraintName) to replace with new name ($constraintName) from table ($tableName). Old values :: \n" . print_r($constraintOld, true), iColorCode::YELLOW);

            $dropConstraint = /** @lang MySQL */
                "ALTER TABLE " . self::$carbonDatabaseName . "." . $tableName . " DROP FOREIGN KEY $oldConstraintName;";

            $result = self::execute($dropConstraint);

            if (false === $result) {

                ColorCode::colorCode("Failed to drop old foreign key ($oldConstraintName) on table ($tableName) using sql ($dropConstraint); Values: ( " . self::$carbonDatabaseName . ".$tableName , $oldConstraintName )", iColorCode::RED);

                exit(60);

            }

            ColorCode::colorCode("Successfully dropped old foreign key ($oldConstraintName) on table ($tableName). Preparing to update ($constraintName).", iColorCode::CYAN);

        }

        if ($oldConstraintName !== $constraintName) {

            ColorCode::colorCode("Updating foreign key constraint ($constraintName) on table ($tableName)."
                . (null === $oldConstraintName ? '' : " The old constraint name ($oldConstraintName) was removed.")
                . " Checking if new name ($constraintName) already exists.", iColorCode::CYAN);

            // this only checks if a name collision may happen.
            $doesCurrentConstraintNameExist = self::fetchColumn(/** @lang MySQL */ "SELECT 
    links.CONSTRAINT_NAME,
    cols.TABLE_NAME, cols.COLUMN_NAME, cols.ORDINAL_POSITION,
    cols.COLUMN_DEFAULT, cols.IS_NULLABLE, cols.DATA_TYPE,
    cols.CHARACTER_MAXIMUM_LENGTH, cols.CHARACTER_OCTET_LENGTH,
    cols.NUMERIC_PRECISION, cols.NUMERIC_SCALE,
    cols.COLUMN_TYPE, cols.COLUMN_KEY, cols.EXTRA,
    cols.COLUMN_COMMENT, refs.REFERENCED_TABLE_NAME, refs.REFERENCED_COLUMN_NAME,
    cRefs.UPDATE_RULE, cRefs.DELETE_RULE,
    links.TABLE_NAME, links.COLUMN_NAME,
    cLinks.UPDATE_RULE, cLinks.DELETE_RULE
FROM INFORMATION_SCHEMA.`COLUMNS` as cols
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS refs
                   ON refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND refs.TABLE_NAME=cols.TABLE_NAME
                       AND refs.COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cRefs
                   ON cRefs.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cRefs.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
         LEFT JOIN INFORMATION_SCHEMA.`KEY_COLUMN_USAGE` AS links
                   ON links.TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
                       AND links.REFERENCED_TABLE_NAME=cols.TABLE_NAME
                       AND links.REFERENCED_COLUMN_NAME=cols.COLUMN_NAME
         LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cLinks
                   ON cLinks.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
                       AND cLinks.CONSTRAINT_NAME=links.CONSTRAINT_NAME
WHERE cols.TABLE_SCHEMA = '" . self::$carbonDatabaseName . "'
AND links.TABLE_NAME = '$tableName'
AND links.CONSTRAINT_NAME = '$constraintName'");

            if ([] !== $doesCurrentConstraintNameExist) {

                ColorCode::colorCode("The constraint name ($constraintName) already exists on table ($tableName). We will remove the old relation. Please make sure this is intended. Old constraint::", iColorCode::YELLOW);

                ColorCode::colorCode(print_r($doesCurrentConstraintNameExist, true), iColorCode::BACKGROUND_YELLOW);

                $dropConstraint = /** @lang MySQL */
                    "ALTER TABLE $tableName DROP FOREIGN KEY $constraintName";

                $result = self::execute($dropConstraint);

                if (false === $result) {

                    ColorCode::colorCode("Failed to drop foreign key ($constraintName) on table ($tableName) using sql: ($dropConstraint)", iColorCode::RED);

                    exit(62);

                }

                ColorCode::colorCode("Dropped foreign key `$constraintName` on table `$tableName`. Preparing to update.", iColorCode::CYAN);

                sleep(1);

            } else {

                ColorCode::colorCode("The foreign key `$constraintName` on table `$tableName` does not exist. Preparing to update.", iColorCode::CYAN);

            }

        }

        // reverse the above condition
        $selectInvalidColumnCount = "SELECT COUNT($columnName) FROM $tableName WHERE $columnName NOT IN (SELECT $referenceColumn FROM $referenceTable)";

        $invalidColumnCount = self::fetchColumn($selectInvalidColumnCount)[0] ?? 0;

        if ($invalidColumnCount > 0) {

            ColorCode::colorCode("There are $invalidColumnCount invalid references in the column ($columnName) on table ($tableName).", iColorCode::RED);

            $deleteInvalidReferences = "DELETE FROM $tableName WHERE $columnName NOT IN (SELECT $referenceColumn FROM $referenceTable)";

            $result = self::execute($deleteInvalidReferences);

            if (false === $result) {

                ColorCode::colorCode("Failed to delete invalid references from table ($referenceTable) using sql: ($deleteInvalidReferences)", iColorCode::BACKGROUND_RED);

                exit(64);

            }

            ColorCode::colorCode("Deleted $invalidColumnCount invalid references from table ($referenceTable).", iColorCode::BACKGROUND_CYAN);

        }

        // todo - check if the external constraint table exists. (could be non-rest in an timing issue)
        /** @noinspection SqlResolve */
        $addConstraint = /** @lang MySQL */
            "ALTER TABLE `$tableName` ADD CONSTRAINT `$constraintName` FOREIGN KEY (`$columnName`) REFERENCES `$referenceTable` (`$referenceColumn`) ON DELETE $onDelete ON UPDATE $onUpdate";

        $result = self::execute($addConstraint);

        if (false === $result) {

            ColorCode::colorCode("Failed to add foreign key ($constraintName) on table ($tableName) with SQL ($addConstraint)", iColorCode::RED);

            exit(61);

        }

        ColorCode::colorCode("Successfully added foreign key ($constraintName) on table ($tableName)");

    }

    /**
     * @param string $column
     * @param string $table_name
     * @param string $sql
     * @return void
     * @throws PublicAlert
     */
    public static function columnExistsOrExecuteSQL(string $column, string $fullyQualifiedClassName, string $sql): void
    {
        $tableName = $fullyQualifiedClassName::TABLE_NAME;

        self::addTablePrefix($tableName, $fullyQualifiedClassName::TABLE_PREFIX, $sql);

        $currentSchema = self::$carbonDatabaseName;

        // Check if exist the column named image
        $existed = self::fetchColumn("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?", $currentSchema, $tableName, $column);

        // If not exists
        if ([] === $existed) {

            ColorCode::colorCode("Column ($column) did not appear to exist. Attempting to run ($sql).", iColorCode::YELLOW);

            if (self::execute($sql)) {

                ColorCode::colorCode("success");

            } else {

                ColorCode::colorCode("failure", iColorCode::RED);

                exit(94);

            }

        } else {

            ColorCode::colorCode("The column ($column) was validated to already exists on table ($tableName).");

        }

    }


    /**
     * @throws PublicAlert
     */
    public static function columnIsTypeOrChange(string $column, string $fullyQualifiedClassName, string $type): void
    {

        $currentSchema = self::$carbonDatabaseName;

        $tableName = $fullyQualifiedClassName::TABLE_NAME;

        $pdoValidations = $fullyQualifiedClassName::PDO_VALIDATION;

        $generatedInformation = $pdoValidations[$tableName . '.' . $column];

        $defaultAutoIncrement = $generatedInformation[iRest::AUTO_INCREMENT];

        $defaultValue = $generatedInformation[iRest::DEFAULT_POST_VALUE] ?? null;

        $commentSet = array_key_exists(iRest::COMMENT, $generatedInformation);

        $comment = $commentSet
            ? ' ' . iRest::COMMENT . ' \'' . $generatedInformation[iRest::COMMENT] . '\''
            : '';

        $nullable = false === $generatedInformation[iRest::NOT_NULL]
            ? 'YES'
            : 'NO';

        $nullableSQL = $generatedInformation[iRest::NOT_NULL]
            ? 'NOT NULL'
            : '';

        $sql = "SELECT COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_COMMENT, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";

        self::addTablePrefix($tableName, $fullyQualifiedClassName::TABLE_PREFIX, $sql);

        // Check if exist the column named image
        $columnInformation = self::fetchAll($sql, $currentSchema, $tableName, $column)[0] ?? '';

        $currentType = $columnInformation['COLUMN_TYPE'];

        $currentDefault = $columnInformation['COLUMN_DEFAULT'];

        $currentNullable = $columnInformation['IS_NULLABLE'];

        $currentComment = $columnInformation['COLUMN_COMMENT'];

        $currentAutoIncrement = $columnInformation['EXTRA'] === 'auto_increment';

        $typesMatch = $currentType === $type
            || (($currentType === 'tinyint(1)' || $type === 'tinyint(1)')
                && ($currentType === 'tinyint' || $type === 'tinyint'));

        $defaultsMatch = $currentDefault === $defaultValue
            || '"' . $currentDefault . '"' === $defaultValue;

        $currentNullablesMatch = $currentNullable === $nullable;

        $commentsMatch = ('' === $currentComment && false === $commentSet)
            || $currentComment === ($generatedInformation[iRest::COMMENT] ?? null);

        $autoIncrementMatches = $currentAutoIncrement === $defaultAutoIncrement;

        // If not exists
        if (false === $typesMatch
            || false === $currentNullablesMatch
            || false === $autoIncrementMatches
            || false === $commentsMatch
            || false === $defaultsMatch) {


            ColorCode::colorCode("The column ($tableName.$column) was validated to not match the expected type information.", iColorCode::YELLOW);

            ColorCode::colorCode(" The values set in code are :: " . print_r([
                    'type' => $type,
                    'default' => $defaultValue,
                    'nullable' => $nullable,
                    'auto_increment' => $defaultAutoIncrement,
                    'comment' => $generatedInformation[iRest::COMMENT] ?? '',
                ], true), iColorCode::CYAN);

            ColorCode::colorCode(" The values set on the database are :: " . print_r([
                    'type' => $currentType,
                    'typesMatch' => $typesMatch,
                    'default' => $currentDefault,
                    'defaultsMatch' => $defaultsMatch,
                    'nullable' => $currentNullable,
                    'currentNullablesMatch' => $currentNullablesMatch,
                    'auto_increment' => $currentAutoIncrement,
                    'autoIncrementMatches' => $autoIncrementMatches,
                    'comment' => $currentComment,
                    'commentsMatch' => $commentsMatch
                ], true), iColorCode::YELLOW);

            $optionalDefault = false === $defaultsMatch || null !== $defaultValue ? "DEFAULT " . ($defaultValue ?? 'NULL') : '';

            $autoIncrementSQL = $defaultAutoIncrement ? 'AUTO_INCREMENT' : '';

            $sql = "ALTER TABLE $tableName MODIFY $column $type $nullableSQL $autoIncrementSQL $optionalDefault $comment;";

            ColorCode::colorCode("Column ($tableName.$column) needs to be modified. Attempting to run ($sql).");

            if (self::execute($sql)) {

                ColorCode::colorCode("success");

            } else {

                ColorCode::colorCode("failure", iColorCode::RED);

                exit(95);

            }

        } else {

            ColorCode::colorCode("Verified column ($tableName.$column) already exists as type ($type) on table ($tableName) with comment ($currentComment) default ($currentDefault).", iColorCode::CYAN);

        }

    }

    public static function addTablePrefix(string &$table_name, string $table_prefix, string &$sql): void
    {

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        if ($prefix === '' || $prefix === $table_prefix) {

            return;

        }

        $sqlReplaced = preg_replace(["#([^a-z_])({$table_name}[^a-z_])#i", "#([^a-z_])(carbon_carbons[^a-z_])#i"],
            '$1' . $prefix . '$2', $sql);

        if (false === is_string($sqlReplaced)
            || str_contains($sqlReplaced, "`$table_name`")
            || str_contains($sqlReplaced, "`carbon_carbons`")
        ) {

            ColorCode::colorCode("Preg_replace failed to add prefix to table; (" . print_r($sqlReplaced, true) . ")", iColorCode::RED);

            exit(1);

        }

        if (false === is_string($sqlReplaced)) {

            throw new PrivateAlert('Failed to replace schema.');

        }

        $sql = (string)$sqlReplaced;

        $table_name = $prefix . $table_name;

    }


    private static function compileMySqlStatementsAndExecuteWithoutForeignKeyChecks($tableCreateStmt = ''): void
    {

        static $stmts = '';

        if ('' === $tableCreateStmt) {

            if ('' === $stmts) {

                throw new PrivateAlert('Nothing was passed to ' . __FUNCTION__ . ' and no query was compiled yet!');

            }

            $sql = self::REMOVE_MYSQL_FOREIGN_KEY_CHECKS . PHP_EOL
                . $stmts . PHP_EOL . self::REVERT_MYSQL_FOREIGN_KEY_CHECKS;

            ColorCode::colorCode("Will execute the sql now.");

            ColorCode::colorCode($sql);

            $success = self::execute($sql);

            ColorCode::colorCode($success ? 'success' : 'failed', $success ? iColorCode::GREEN : iColorCode::RED);

            if (false === $success) {

                ColorCode::colorCode('Failed to execute sql', iColorCode::RED);

                exit(1);

            }

            if (false === file_put_contents(CarbonPHP::$app_root . 'createTables.sql', $sql)) {

                ColorCode::colorCode('Failed to store sql to ' . CarbonPHP::$app_root . 'createTables.sql', iColorCode::RED);

            }

        }

        $stmts .= PHP_EOL . $tableCreateStmt . PHP_EOL;

    }

    /**
     * @param string $table_name
     * @param string $table_prefix
     * @param string $sql
     * @return bool|null
     * @throws PublicAlert
     */
    public static function tableExistsOrExecuteSQL(string $table_name, string $table_prefix, string $sql): ?bool
    {

        self::addTablePrefix($table_name, $table_prefix, $sql);

        // Check if exist the column named image
        $result = self::fetch("SELECT * 
                        FROM information_schema.tables
                        WHERE table_schema = '" . self::$carbonDatabaseName . "' 
                            AND table_name = '$table_name'
                        LIMIT 1;");

        if ([] !== $result) {

            ColorCode::colorCode('Table `' . $table_name . '` already exists');

            return true;

        }

        ColorCode::colorCode("Attempting to create table ($table_name).");

        if (false === self::execute($sql)) {

            ColorCode::colorCode('Failed to update table :: ' . $table_name, iColorCode::RED);

            exit(1);

        }

        $result = self::fetch("SELECT * 
                        FROM information_schema.tables
                        WHERE table_schema = '" . self::$carbonDatabaseName . "' 
                            AND table_name = '$table_name'
                        LIMIT 1;");

        if ([] === $result) {

            ColorCode::colorCode("The table ($table_name) does not exist and was attempted to be created. This operation failed without error. (error unknown) Please manually try to run the create table sql.\n\n($sql)\n\n", iColorCode::RED);

            exit(1);

        }

        ColorCode::colorCode('Table `' . $table_name . '` Created');


        return null;

    }

    protected static function addPrefixAndExecute($sql, $tableName, $tablePrefix): array
    {

        self::addTablePrefix($tableName, $tablePrefix, $sql);

        return self::fetch($sql);

    }

    public static function fetchConstraint(string $tableName, string $constraintName): array
    {
        return self::fetchAll(" 
                SELECT * 
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
                ", $tableName, $constraintName)[0] ?? [];
    }

    public static function fetchIndex(string $tableName, string $indexName): array
    {
        return self::fetchAll(" 
                SELECT * 
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
                ", $tableName, $indexName)[0] ?? [];
    }

    public static function indexExistsAndDropIndexCallable(string $tableName, string $constraintName): null|callable
    {

        $exists = self::fetchIndex($tableName, $constraintName);

        ColorCode::colorCode('Checking if (' . $constraintName . ') constraint exists in the database. ' . (empty($exists) ? '(NO)' : '(' . json_encode($exists) . ')'), iColorCode::BACKGROUND_MAGENTA);

        if (empty($exists)) {

            return null;

        }

        return static fn() => self::execute("alter table $tableName drop index $constraintName;");

    }

    public static function indexExistsOrCreateCallback(string $tableName, string $constraintName, array $columns, bool $unique = true): null|callable
    {

        $exists = self::fetchIndex($tableName, $constraintName);

        ColorCode::colorCode('Checking if (' . $constraintName . ') constraint exists in the database. (' . (empty($exists) ? '(NO)' : json_encode($exists)) . ')', iColorCode::BACKGROUND_MAGENTA);

        if (!empty($exists)) {

            if ($unique === false || 'UNIQUE' === ($exists[0]['CONSTRAINT_TYPE'] ?? '')) {

                // this will drop the incorrect index
                self::indexExistsAndDropIndexCallable($tableName, $constraintName)();

                return self::indexExistsOrCreateCallback($tableName, $constraintName, $columns, $unique);

            }

            ColorCode::colorCode('The (' . $constraintName . ') constraint already exists in the database. Skipping...', iColorCode::BACKGROUND_MAGENTA);

            return null;

        }


        return static function () use ($unique, $tableName, $constraintName, $columns) {

            $columnsInline = implode(', ', $columns);

            // remove invalid column entries that would cause a unique index to fail
            if ($unique) {

                // @link https://stackoverflow.com/questions/12188027/mysql-select-distinct-multiple-columns
                // DELETE FROM wp_zesv6j_um_friends
                // WHERE (user_id1, user_id2) in (SELECT user_id1, user_id2
                //                               FROM wp_zesv6j_um_friends
                //                               GROUP BY user_id1, user_id2
                //                               HAVING COUNT(*) > 1)

                $sql = "SELECT $columnsInline FROM $tableName GROUP BY $columnsInline HAVING COUNT(*) > 1";

                ColorCode::colorCode($sql, iColorCode::BACKGROUND_RED);

                // @link https://stackoverflow.com/questions/10517737/delete-duplicates-from-a-composite-primary-key-table
                // Composite Key
                $columnsToDelete = self::fetchAll($sql);

                if (count($columnsToDelete) > 0) {

                    ColorCode::colorCode('The (' . $constraintName . ') constraint has violations that exist in the database. The following sql was used to gather the violations (' . $sql . ') We will delete the following records (' . json_encode($columnsToDelete, JSON_PRETTY_PRINT) . ')', iColorCode::BACKGROUND_RED);

                    // @link https://www.codeproject.com/Tips/831164/MySQL-can-t-specify-target-table-for-update-in-FRO
                    if (false === self::execute($sql = "DELETE FROM $tableName WHERE ($columnsInline) in (SELECT * FROM (SELECT $columnsInline FROM $tableName GROUP BY $columnsInline HAVING COUNT(*) > 1) tblTmp)")) {

                        throw new PrivateAlert('Failed to delete records that violate the unique constraint. (' . $constraintName . ') using sql (' . $sql . ')');

                    }

                }

            }

            self::execute("create " . ($unique ? 'unique' : '') . " index $constraintName on $tableName ($columnsInline);");

        };

    }


    /**
     * Determine the likelihood that this query could alter anything
     *
     * Statements are considered read-only when:
     * 1. not including UPDATE nor other "may-be-write" strings
     * 2. begin with SELECT etc.
     *
     * @param string $q Query.
     *
     * @return bool
     * @since 1.0.0
     * @source /ludicrousdb/includes/class-ludicrousdb.php
     */
    public static function isWriteQuery(string $q = ''): bool
    {

        // Trim potential whitespace or subquery chars
        $q = ltrim($q, "\r\n\t (");

        // Possible writes
        if (preg_match('/(?:^|\s)(?:ALTER|CREATE|ANALYZE|CHECK|OPTIMIZE|REPAIR|CALL|DELETE|DROP|INSERT|LOAD|REPLACE|UPDATE|SHARE|SET|RENAME\s+TABLE)(?:\s|$)/i', $q)) {
            return true;
        }

        // Not possible non-writes (phew!)
        return !preg_match('/^(?:SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)(?:\s|$)/i', $q);
    }


}

