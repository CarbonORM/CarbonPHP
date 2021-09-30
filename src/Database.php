<?php // Parent of Model class, can only be called indirectly

namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Programs\Composer;
use CarbonPHP\Programs\MySQL;
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
    use ColorCode, Composer;

    private static array $pdo_options;

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

    public static string $carbonDatabaseUsername;

    public static string $carbonDatabasePassword;

    public static string $carbonDatabaseName;

    public static string $carbonDatabasePort;

    public static string $carbonDatabaseHost;

    /**
     * @var string $carbonDatabaseDSN holds the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public static string $carbonDatabaseDSN;

    /**
     * @var string holds the path of the users database set up file
     */
    public static ?string $carbonDatabaseSetup = null;

    /**
     * @var array - new key inserted but not verified currently
     */
    private static array $carbonDatabaseEntityTransactionKeys;

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

    public static function database(): PDO
    {
        if (null === self::$database) { // todo - can we get the ini of mysql timeout?

            return static::reset();

        }

        $oldLevel = error_reporting(0);

        try {

            self::$database->prepare('SELECT 1')->execute();

            error_reporting($oldLevel);

            return static::$database;                       // Why should this work again?

        } catch (Throwable $e) {                            // added for socket support

            ErrorCatcher::generateLog($e, true);

            self::colorCode('Attempting to reset the database. Possible disconnect.', iColorCode::BACKGROUND_YELLOW);

            error_reporting($oldLevel);

            return static::reset();
        }
    }

    /**
     * @param PDOException $e
     * @return mixed|bool|string|object - the return of the passed callable
     */
    public static function TryCatchPDOException(PDOException $e): void
    {

        $error_array = ErrorCatcher::generateLog($e, true);

        $log_array = $error_array[ErrorCatcher::LOG_ARRAY];

        // todo - handle all pdo exceptions
        switch ($e->getCode()) {        // Database has not been created
            case 'HY000':

                ColorCode::colorCode('Caught connection reset code (HY000)', iColorCode::BACKGROUND_MAGENTA);

                ColorCode::colorCode('A recursive error has been detected. C6 has detected the MySQL'
                    . ' database in a broken pipe state. We have attempted to reset the database and rerun the'
                    . ' query in question. This process then threw the exact same error. Please make sure no long'
                    . ' running queries are being terminated by MySQL. If you have over ridden the driver settings '
                    . ' and are in a long running process make sure PDO::ATTR_PERSISTENT => true is present. Finally,'
                    . ' please make sure you are not manually terminating the connection. Attempting to parse error.', iColorCode::BACKGROUND_RED);

                self::reset();

                return;

            case 1049:

                $query = explode(';', static::$carbonDatabaseDSN);    // I programmatically put it there which is why..

                $db_name = explode('=', $query[1])[1];  // I dont validate with count on this

                if (empty($db_name)) {

                    $log_array[] = 'Could not determine a database to create. See Carbonphp.com for documentation.';

                    print ErrorCatcher::generateBrowserReport($log_array); // this

                    break;

                }

                try {
                    // https://www.php.net/manual/en/pdo.setattribute.php
                    static::$database = new PDO(
                        $query[0],
                        static::$carbonDatabaseUsername,
                        static::$carbonDatabasePassword,
                        self::getPdoOptions());

                } catch (Throwable $e) {

                    $error_array_two = ErrorCatcher::generateLog($e);

                    if ($e->getCode() === 1049) {

                        $error_array_two[] = '<h1>Auto Setup Failed!</h1><h3>Your database DSN may be slightly malformed.</h3>';

                        $error_array_two[] = '<p>CarbonPHP requires the host come before the database in your DNS.</p>';

                        $error_array_two[] = '<p>It should follow the following format "mysql:host=127.0.0.1;dbname=C6".</p>';
                    }

                    ErrorCatcher::generateBrowserReport($error_array_two);  // this terminates

                }

                $stmt = "CREATE DATABASE $db_name;";

                $db = static::$database;

                if (!$db->prepare($stmt)->execute()) {

                    $log_array[] = "<h1>Failed to execute ($stmt).</h1>";

                    print ErrorCatcher::generateBrowserReport($log_array);  // this terminates

                } else {

                    $db->exec("use $db_name");

                    static::refreshDatabase();

                }

                print ErrorCatcher::generateBrowserReport($log_array);  // this terminates

                break;

            case '42S02':

                print ErrorCatcher::generateBrowserReport($log_array);

                static::setUp(!CarbonPHP::$cli, CarbonPHP::$cli);

                break;

            default:

                if (empty(static::$carbonDatabaseUsername)) {

                    $log_array[] = '<h2>You must set a database user name. See CarbonPHP.com for documentation</h2>';
                }
                if (empty(static::$carbonDatabasePassword)) {

                    $log_array[] = '<h2>You may need to set a database password. See CarbonPHP.com for documentation</h2>';

                }

                print ErrorCatcher::generateBrowserReport($log_array);

        }

        exit(1);

    }


    /** Clears and restarts the PDO connection
     * @return PDO
     */
    public static function reset(): PDO // built to help preserve database in sockets and forks
    {
        if (null !== self::$database) {

            self::close();

        }

        $attempts = 0;

        $prep = static function (PDO $db): PDO {

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $db->setAttribute(PDO::ATTR_PERSISTENT, CarbonPHP::$cli);

            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            static::$database = $db;

            return static::$database;

        };

        do {

            try {

                // @link https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement
                return $prep(new PDO(
                    static::$carbonDatabaseDSN,
                    static::$carbonDatabaseUsername,
                    static::$carbonDatabasePassword,
                    self::getPdoOptions()));

            } catch (Throwable $e) {

                if ($e instanceof PDOException) {

                    self::TryCatchPDOException($e); // this might exit

                } else {

                    ErrorCatcher::generateLog($e);  // this will exit

                }

                $attempts++;

            }

        } while ($attempts < 3);

        $message = 'Failed to connect to database.';

        ColorCode::colorCode($message, iColorCode::RED);

        ErrorCatcher::generateLog($e);

        die(1);

    }


    public static function close(): void
    {

        try {

            if (self::$database instanceof PDO) {

                self::$database->exec('KILL CONNECTION_ID();');

            }

        } catch (Throwable $e) {

            // its common for pdo to throw an error here, we will silently ignore it
            // running KILL CONNECTION_ID() will disconnect the resource before return thus error

        } finally {

            self::$database = null;

        }

    }

    /** Overwrite the current database. If nothing is given the
     * connection to the database will be closed
     * @param PDO|null $database
     */
    public static function setDatabase(PDO $database = null): void
    {

        if (null === $database && self::$database instanceof PDO) {

            self::close();

        }

        self::$database = $database;

    }

    /**
     * This will attempt to create the required tables for CarbonPHP.
     * If a file aptly named `buildDatabase.php` exists in your configuration
     * file it will also be run. Be sure to model your build tool after ours
     * so it does not block.. setUp is synonymous = resetDatabase (which doesn't exist)
     *
     * @param bool $refresh if set to true will send Javascript
     * to refresh the browser using SITE constant
     * @param bool $cli
     * @param string|null $tableDirectory
     * @return PDO
     */
    public static function setUp(bool $refresh = true, bool $cli = false, string $tableDirectory = null): PDO
    {

        if ($cli) {

            ColorCode::colorCode('This build is automatically generated by CarbonPHP. When you add a table be sure to re-execute the RestBuilder.', iColorCode::BACKGROUND_CYAN);

            self::colorCode('Connecting on ' . self::$carbonDatabaseDSN);

        } else {

            print '<h3>This build is automatically generated by CarbonPHP. When you add a table be sure to re-execute the <br><br><br><b>>> php index.php restbuilder</b></h3>'
                . '<h1>Connecting on </h1>' . self::$carbonDatabaseDSN . '<br>';

        }

        self::refreshDatabase($tableDirectory ?? '');

        if ($refresh && !$cli) {

            print '<br><br><h2>Refreshing in 6 seconds</h2><script>let t1 = window.setTimeout(function(){ window.location.href = \'' . CarbonPHP::$site . '\'; },6000);</script>';

            exit(0);

        }

        return static::database();

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
     * @throws PublicAlert
     */
    public static function verify(): bool
    {

        $pdo = self::database();

        if (!$pdo->inTransaction()) {        // We're verifying that we do not have an un finished transaction

            return true;

        }

        try {

            $pdo->rollBack();  // this transaction was started after our keys were inserted..

            if (!empty(self::$carbonDatabaseEntityTransactionKeys)) {


                foreach (self::$carbonDatabaseEntityTransactionKeys as $key) {

                    static::remove_entity($key);

                }

            }

        } catch (PDOException $e) {

            ErrorCatcher::generateLog($e);

        }

        return false;

    }

    /** Commit the current transaction to the database.
     * @link http://php.net/manual/en/pdo.rollback.php
     * @param callable|mixed $lambda
     * @return bool
     * @throws PublicAlert
     */
    public static function commit(): bool
    {

        $db = self::database();

        if (false === $db->inTransaction()) {

            return true;

        }

        return $db->commit() ?: static::verify();

    }

    /**
     * @return array
     */
    public static function getPdoOptions(): array
    {

        return self::$pdo_options ??= [
            PDO::ATTR_PERSISTENT => CarbonPHP::$cli,                // only in cli (including websockets)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,                     // Return the number of found (matched) rows, not the number of changed rows.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL
        ];

    }

    /**
     * @param array $pdo_options
     */
    public static function setPdoOptions(array $pdo_options): void
    {

        self::$pdo_options = $pdo_options;

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
     * @return bool|PDOStatement|string
     * @throws PublicAlert
     */
    protected static function beginTransaction(string $tag_id, string $dependant = null)
    {

        $db = self::database();

        $key = self::new_entity($tag_id, $dependant);

        if (!$db->inTransaction()) {

            $db->beginTransaction();

        }

        return $key;

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
     * @param $dependant
     * @return string
     * @throws PublicAlert
     */
    public static function new_entity(string $tag_id, string $dependant = null): string
    {
        $count = 0;

        $carbons = Rest::getDynamicRestClass(Carbons::class, iRestSinglePrimaryKey::class);

        do {

            $count++;

            $post = [
                $carbons::ENTITY_TAG => $tag_id,
                $carbons::ENTITY_FK => $dependant
            ];

            /** @noinspection PhpUndefinedMethodInspection - intellij is not good at php static refs */
            $id = $carbons::post($post);

        } while ($id === false && $count < 4);  // todo - why four?

        if ($id === false) {
            throw new PublicAlert('C6 failed to create a new entity.');
        }

        self::$carbonDatabaseEntityTransactionKeys[] = $id;

        return $id;
    }

    /**
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
     * @param $id - Remove entity_pk form carbon
     * @return bool
     * @throws PublicAlert
     */
    public static function remove_entity($id): bool
    {
        $ref = [];
        $carbons = Rest::getDynamicRestClass(Carbons::class);
        /** @noinspection PhpUndefinedMethodInspection */
        return $carbons::delete($ref, $id, []); //Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]);
    }


    public static function execute(string $sql, ...$execute): bool
    {
        return self::database()->prepare($sql)->execute($execute);
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
     * @param string $sql
     * @param array ...$execute
     * @link http://php.net/manual/en/functions.arguments.php
     * @return array
     */
    protected static function fetch(string $sql, ...$execute): array
    {
        $stmt = self::database()->prepare($sql);
        if (!$stmt->execute($execute) && !$stmt->execute($execute)) { // try it twice, you never know..
            return [];
        }
        if (count($stmt = $stmt->fetchAll(PDO::FETCH_ASSOC)) !== 1) {
            return $stmt;
        }
        is_array($stmt) and $stmt = array_shift($stmt);
        return $stmt;   // promise this is needed and will still return the desired array
    }

    /** Quickly prepare and execute PDO $sql statements using
     *  variable arguments.
     *
     * Example:
     * $array['following'] = self::fetchColumn('SELECT follows_user_id FROM user_followers WHERE user_id = ?', $id);
     *
     * @param string $sql - variables should be denoted by question marks
     * @param array ...$execute -
     *  if multiple question marks exist you may use comma separated parameters to fill the statement
     * @return array
     */
    protected static function fetchColumn(string $sql, ...$execute): array
    {
        $stmt = self::database()->prepare($sql);
        if (!$stmt->execute($execute)) {
            return [];
        }
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
        $stmt = self::database()->prepare($sql);
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
        $stmt = self::database()->prepare($sql);
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
        $stmt = self::database()->prepare($sql);
        $stmt->execute($execute);
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($array as $key => $value) {
            $object->$key = $value;
        }
    }

    protected static function runRefreshSchema(array $php_validation): void
    {
        foreach ($php_validation as $key => $validation) {
            if (!is_int($key)) {
                throw new PublicAlert('All members of REFRESH_SCHEMA must be arrays with integer keys.');
            }

            if (!is_array($validation)) {
                throw new PublicAlert('Each REFRESH_SCHEMA should equal an array of arrays with [ call => method , structure followed by any additional arguments ]. Refer to Carbonphp.com for more information.');
            }

            $class = array_key_first($validation);          //  $class => $method
            $validationMethod = $validation[$class];
            unset($validation[$class]);

            if (!class_exists($class)) {
                throw new PublicAlert("A class reference in REFRESH_SCHEMA failed. Class ($class) not found.");
            }

            if (false === call_user_func([$class, $validationMethod], ...$validation)) {
                throw new PublicAlert("Any method used in REFRESH_SCHEMA must not return false. $class => $validationMethod returned with error.");
            }
        }
    }

    public static function buildCarbonPHP(): void
    {
        self::refreshDatabase(CarbonPHP::CARBON_ROOT . DS . 'tables' . DS);
    }

    public static function scanAnd(callable $callback, string $tableDirectory = null): void
    {

        $tableDirectory ??= Rest::autoTargetTableDirectory();

        $restful = glob($tableDirectory . '*.php');

        $classNamespace = Rest::getRestNamespaceFromFileList($restful);

        foreach ($restful as $file) {

            $className = ucwords(basename($file, '.php'), '_');

            if (false === class_exists($table = substr($classNamespace, 0, -1) . $className)) {
                self::colorCode("\n\nCouldn't load the class '$table' for refresh. This may indicate your file 
                    contains a syntax error or is not generated by the restful API.\n", iColorCode::YELLOW);
                continue;
            }

            if (false === is_subclass_of($table, Rest::class)) {

                $restFullyQualifiedName = Rest::class;

                self::colorCode("\n\nThe class '$table' does not implement $restFullyQualifiedName.
                        This would indicate a custom class in the table's namespaced directory. Please avoid doing this.\n", iColorCode::YELLOW);

                continue;

            }

            $imp = array_map('strtolower', array_keys(class_implements($table)));

            if (!in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
            ) {

                self::colorCode("The table ($table) did not interface the required (iRestMultiplePrimaryKeys, iRestSinglePrimaryKey, or iRestNoPrimaryKey). This is unexpected.", iColorCode::RED);

                continue;

            }

            $callback($table);
        }
    }

    public static array $tablesToValidateAfterRefresh = [];

    public static function scanAndRunRefreshDatabase(string $tableDirectory): void
    {
        static $validatedTables = [];

        ColorCode::colorCode("\n\nScanning and running refresh database using ('$tableDirectory' . '*.php')");

        self::scanAnd(static function (string $table): void {

            if (defined("$table::REFRESH_SCHEMA")) {

                self::runRefreshSchema($table::REFRESH_SCHEMA);

            } else {

                ColorCode::colorCode("The generated constant $table::REFRESH_SCHEMA does not exist. Rerun RestBuilder to repopulate.", iColorCode::YELLOW);

            }

            self::$tablesToValidateAfterRefresh[$table] = $table::CREATE_TABLE_SQL;

            $db = self::database();

            if ($db->inTransaction()) {

                self::colorCode('We are in a transaction.', iColorCode::YELLOW);

            }

            if (!self::commit()) {

                self::colorCode('Failed to commit to database!', iColorCode::RED);

                exit(1);

            }
        },
            $tableDirectory);

        if (file_exists($filename = CarbonPHP::$app_root . 'mysqldump.sql')) {

            try {

                unlink($filename); // I dont care if this works

            } catch (Throwable $e) {

                ErrorCatcher::generateLog($e, true);

            }

        }

        // Now Validate The Rest Tables Based on The MySQL Dump after update.
        $mysqldump = MySQL::mysqldump(null);

        sleep(1);   // wait for last command

        if (!file_exists($mysqldump)) {
            print 'Could not load mysql dump file!' . PHP_EOL;
            exit(1);
        }

        if (empty($mysqldump = file_get_contents($mysqldump))) {
            print 'Contents of the mysql dump file appears empty. Build Failed!';
            exit(1);
        }

        $regex = '#CREATE\s+TABLE(.|\s)+?(?=ENGINE=)ENGINE=.+;#';

        $failureEncountered = false;

        foreach (self::$tablesToValidateAfterRefresh as $fullyQualifiedClassName => $preUpdateSQL) {

            if (defined("$fullyQualifiedClassName::VALIDATE_AFTER_REBUILD") && false === $fullyQualifiedClassName::VALIDATE_AFTER_REBUILD) {

                self::colorCode("The class constant ($fullyQualifiedClassName::VALIDATE_AFTER_REBUILD) is set to false. Skipping...");

                continue;

            }

            $tableName = $fullyQualifiedClassName::TABLE_NAME;

            $matches = [];

            if (null === $preUpdateSQL || false === preg_match_all($regex, $preUpdateSQL, $matches)) {

                ColorCode::colorCode('Verifying schema failed during preg_match_all for sql ' . $preUpdateSQL, iColorCode::RED);

                return;

            }

            $preUpdateSQL = $matches[0][0] ?? false;

            if (!$preUpdateSQL) {

                ColorCode::colorCode("Regex failed to match a schema using preg_match_all('$regex', '$preUpdateSQL',...", iColorCode::RED);

                return;

            }

            self::addTablePrefix($tableName, $fullyQualifiedClassName::TABLE_PREFIX, $preUpdateSQL);

            if (in_array($tableName, $validatedTables, true)) {

                self::colorCode("The table [C6] ($tableName) has already been validated. Skipping...");

                continue;

            }

            $validatedTables[] = $tableName;

            $table_regex = "#CREATE\s+TABLE\s`$tableName`(.|\s)+?(?=ENGINE=)ENGINE=.+;#";

            if (null === $preUpdateSQL || false === preg_match_all($table_regex, $mysqldump, $matches)) {

                ColorCode::colorCode('Verifying schema failed during preg_match_all on the ./mysqlDump.sql', iColorCode::RED);

                return;

            }

            $postUpdateSQL = $matches[0][0] ?? false;

            if (!$postUpdateSQL) {

                ColorCode::colorCode("Regex failed to match a schema using preg_match_all('$table_regex', '$mysqldump',...", iColorCode::RED);

                exit(1);

            }

            $preUpdateSQL = trim($preUpdateSQL);

            $postUpdateSQL = trim(str_replace("\\n", "\n", Rest::parseSchemaSQL($postUpdateSQL)));

            // the table definition maybe reordered and we just want to know whats dif
            $preUpdateSQLArray = array_map('trim', explode(PHP_EOL, $preUpdateSQL));

            $postUpdateSQLArray = array_map('trim', explode(PHP_EOL, $postUpdateSQL));

            $changesOne = array_diff($preUpdateSQLArray, $postUpdateSQLArray);
            $changesTwo = array_diff($postUpdateSQLArray, $preUpdateSQLArray);

            $replace = [
                /** @lang PhpRegExp */ '#bigint\(\d+\)#' => 'bigint',
                /** @lang PhpRegExp */ '#int\(\d+\)#' => 'int',
                /** @lang PhpRegExp */ '#CHARACTER\sSET\s\w+#' => '',
                /** @lang PhpRegExp */ '#COLLATE\s\w+#' => '',
                /** @lang PhpRegExp */ '#datetime\sDEFAULT\sNULL#' => 'datetime',
                /** @lang PhpRegExp */ '#\sON\sDELETE\sNO\sACTION#' => '',
                /** @lang PhpRegExp */ '#AUTO_INCREMENT=\d+#' => '',
                /** @lang PhpRegExp */ '#COLLATE=\w+#' => '',
                /** @lang PhpRegExp */ '#DEFAULT CHARSET=\w+#' => '',   // todo - I feel like this makes sense to flag but Actions
                /** @lang PhpRegExp */ '#\s{2,}#' => ' ',
                /** @lang PhpRegExp */ '#\s?,$#' => '',
                /** @lang PhpRegExp */ '#\s?;$#' => '',
            ];

            $pattern = array_keys($replace);
            $replacement = array_values($replace);

            $looseSQLOne = preg_replace($pattern,$replacement, $preUpdateSQLArray);
            $looseSQLTwo = preg_replace($pattern,$replacement, $postUpdateSQLArray);

            $looseChangesOne = array_diff($looseSQLOne, $looseSQLTwo);
            $looseChangesTwo = array_diff($looseSQLTwo, $looseSQLOne);

            // safe compare multibyte strings
            if ([] !== $changesOne || $changesTwo !== []) {

                if ([] !== $looseChangesOne || [] !== $looseChangesTwo) {

                    ColorCode::colorCode('Oh No! After running the database updated it looks like the sql found in'
                        . " the mysql dump file did not match the expected. Any updates done to the database should be automated in the $fullyQualifiedClassName::REFRESH_SCHEMA[] definition. "
                        . "If this is not a table you manage, but rather 3rd-party generated, you should change "
                        . "($fullyQualifiedClassName::VALIDATE_AFTER_REBUILD = false;) and re-try; this can also be set to "
                        . ' false if you would like to manage table definition(s) using other means.'
                        . ' To update your table using REFRESH_SCHEMA, please refer to the documentation that is been provided'
                        . " above this constant in the php class for $tableName.", iColorCode::RED);

                    self::colorCode("If the new SQL appears correct you probably"
                        . " just need to re-run the RestBuilder program (not the database rebuild program currently raising error).", iColorCode::BACKGROUND_YELLOW);

                    ColorCode::colorCode("Due to version differences in how MySQLDump will print your schema, the following are used with preg_replace to `loosen` the condition PHP array_diff must meet ::\n" . json_encode($replace, JSON_PRETTY_PRINT) . "\n\n", iColorCode::BACKGROUND_CYAN);

                    ColorCode::colorCode("Expected (pre-updated sql) :: $preUpdateSQL\n\n", iColorCode::YELLOW);

                    ColorCode::colorCode("GOT (post-updated sql) :: $postUpdateSQL\n\n", iColorCode::BLUE);    // I want to bring your attention back to the red ^^ then down to blue

                    ColorCode::colorCode("\tChanges\n", iColorCode::ITALIC);
                    ColorCode::colorCode("\tNew->Old", iColorCode::CYAN);
                    ColorCode::colorCode("Needs to be added or modified :: ", iColorCode::YELLOW);
                    ColorCode::colorCode('preg_replace\'d :: ' . json_encode($looseChangesOne, JSON_PRETTY_PRINT) . "\n\n", iColorCode::CYAN);
                    ColorCode::colorCode('exact :: ' . json_encode($changesOne, JSON_PRETTY_PRINT) . "\n\n", iColorCode::CYAN);
                    ColorCode::colorCode("\tOld->New", iColorCode::RED);
                    ColorCode::colorCode("needs to be removed or modified :: ", iColorCode::YELLOW);
                    ColorCode::colorCode('preg_replace\'d :: ' . json_encode($looseChangesTwo, JSON_PRETTY_PRINT) . "\n\n", iColorCode::RED);
                    ColorCode::colorCode('exact :: ' . json_encode($changesTwo, JSON_PRETTY_PRINT) . "\n\n", iColorCode::RED);

                    self::colorCode('Only the `preg_replace` differences need be changed to complete with success.');

                    $failureEncountered = true;

                } elseif (defined("$fullyQualifiedClassName::VERBOSE_LOGGING") && true === $fullyQualifiedClassName::VERBOSE_LOGGING){

                    ColorCode::colorCode("Due to version differences in how MySQLDump will print your schema, the following are used with preg_replace to `loosen` the condition PHP array_diff must meet ::\n" . json_encode($replace, JSON_PRETTY_PRINT) . "\n\n", iColorCode::MAGENTA);
                    self::colorCode("Due to the loosened conditions the table ($tableName) has passed.");
                    ColorCode::colorCode("\tNew->Old", iColorCode::CYAN);
                    ColorCode::colorCode(json_encode($changesOne, JSON_PRETTY_PRINT) . "\n\n", iColorCode::CYAN);
                    ColorCode::colorCode("\tOld->New", iColorCode::CYAN);
                    ColorCode::colorCode(json_encode($changesTwo, JSON_PRETTY_PRINT) . "\n\n", iColorCode::CYAN);

                }

            }

            ColorCode::colorCode("Table `$tableName` was verified.");

        }

        if ($failureEncountered) {

            exit(1);

        }

    }


    /**
     * @param string $tableDirectory
     * @param bool|null $cli
     */
    public static function refreshDatabase(string $tableDirectory = '', bool $cli = null): void
    {

        if (null === $cli) {

            $cli = CarbonPHP::$cli;

        }

        try {

            if (CarbonPHP::$carbon_is_root) {

                $tableDirectory = CarbonPHP::CARBON_ROOT . 'tables/'; // todo - use config array to set this

            } elseif ($tableDirectory === '') {

                $tableDirectory = Rest::autoTargetTableDirectory();

            }

            if ($cli) {

                self::colorCode('(Setup || Rebuild) Database');

            } else {

                print '<html lang="en"><head><title>(Setup || Rebuild) Database</title></head><body><h1>REFRESHING SYSTEM</h1>' . PHP_EOL;

            }

            self::colorCode('Building CarbonPHP [C6] Tables', iColorCode::CYAN);

            if ($tableDirectory !== Carbons::DIRECTORY) {

                self::scanAndRunRefreshDatabase(Carbons::DIRECTORY);

            }

            self::scanAndRunRefreshDatabase($tableDirectory);

            self::colorCode('Success!');

            self::colorCode('After Refreshing the database one should rerun the RestBuilder program to capture any changes made to tables with (public const VALIDATE_AFTER_REBUILD = false;)!', iColorCode::CYAN);

        } catch (Throwable $e) {

            if ($cli) {

                ColorCode::colorCode('The refreshDatabase method failed.', iColorCode::BACKGROUND_RED);

            } else {

                print '<h2>The refreshDatabase method failed.</h2>';

            }

            ErrorCatcher::generateLog($e);

            exit(1);        // exit 1 is phpunit // composer scripts safe to === error

        }

    }

    public static function columnExistsOrExecuteSQL(string $column, string $table_name, string $sql): void
    {
        // Check if exist the column named image
        $existed = self::fetch("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$table_name' 
              AND column_name = '$column'");

        // If not exists
        if ([] === $existed) {

            self::colorCode("Column ($column) did not appear to exist. Attempting to run ($sql).");

            if (self::execute($sql)) {

                self::colorCode("success");

            } else {

                self::colorCode("failure", iColorCode::RED);
            }

        } else {

            self::colorCode("The ($column) already exists.");

        }

    }


    public static function addTablePrefix(string &$table_name, string $table_prefix, string &$sql): void
    {

        $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

        if ($prefix === '' || $prefix === $table_prefix) {

            return;

        }

        $sql = preg_replace(["#([^a-z_])({$table_name}[^a-z_])#i", "#([^a-z_])(carbon_carbons[^a-z_])#i"],
            '$1' . $prefix . '$2', $sql);

        if (false !== strpos($sql, "`$table_name`")
            || false !== strpos($sql, "`carbon_carbons`")) {

            self::colorCode('Preg_replace failed to add prefix to table.', iColorCode::RED);

            exit(1);

        }

        $table_name = $prefix . $table_name;

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

            self::colorCode('Table `' . $table_name . '` already exists');

            return true;

        }

        self::colorCode("Attempting to create table ($table_name).");

        if (false === self::execute($sql)) {

            self::colorCode('Failed to update table :: ' . $table_name, iColorCode::RED);

            exit(1);

        }

        $result = self::fetch("SELECT * 
                        FROM information_schema.tables
                        WHERE table_schema = '" . self::$carbonDatabaseName . "' 
                            AND table_name = '$table_name'
                        LIMIT 1;");

        if ([] === $result) {

            self::colorCode("The table ($table_name) does not exist and was attempted to be created. This operation failed without error. (error unknown) Please manually try to run the create table sql.\n\n($sql)\n\n", iColorCode::RED);

            exit(1);

        }

        self::colorCode('Table `' . $table_name . '` Created');


        return null;

    }

    protected static function addPrefixAndExecute($sql, $tableName, $tablePrefix): array
    {

        self::addTablePrefix($tableName, $tablePrefix, $sql);

        return self::fetch($sql);

    }

}

