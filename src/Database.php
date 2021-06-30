<?php // Parent of Model class, can only be called indirectly

namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Globals;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Programs\Composer;
use CarbonPHP\Tables\Carbons;
use Error;
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

    /**
     * @var bool - error catcher needs to initialize quickly,
     * and can relies on a data connection which may not be set here at the moment of its own initialization
     * This bool will determine this use case.
     */
    public static bool $initialized = false;
    /** Represents a connection between PHP and a database server.
     * @link http://php.net/manual/en/class.pdo.php
     * @var PDO|null $database // todo php 8.0
     */
    private static ?PDO $database = null;

    public static string $username;
    public static string $password;
    public static string $name;
    public static string $port;
    public static string $host;
    /**
     * @var string $dsn holds the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public static string $dsn;

    /**
     * @var string holds the path of the users database set up file
     */
    public static ?string $setup = null;

    /**
     * @var array - new key inserted but not verified currently
     */
    private static array $entityTransactionKeys;

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
        if (null === self::$database) {
            return static::reset();
        }
        $oldLevel = error_reporting(0);
        try {
            self::$database->prepare('SELECT 1')->execute();     // This has had a history of causing spotty error.. if this is the location of your error, you should keep looking...
            error_reporting($oldLevel);
            return static::$database;                       // Why should this work again?
        } catch (Error | Exception | PDOException $e) {// added for socket support
            self::colorCode('Attempting to reset the database. Possible disconnect.', iColorCode::RED);
            error_reporting($oldLevel);
            return static::reset();
        }
    }

    public static function TryCatch(callable $closure) // TODO - carbon reporting on errors.. probably in 5.*
    {
        try {
            return $closure();
        } catch (PDOException $e) {

            switch ($e->getCode()) {        // Database has not been created
                case 1049:
                    $query = explode(';', static::$dsn);    // I programmatically put it there which is why..

                    $db_name = explode('=', $query[1])[1];  // I dont validate with count on this

                    if (empty($db_name)) {
                        print '<h1>Could not determine a database to create. See Carbonphp.com for documentation.</h1>';
                        exit(1);
                    }

                    try {
                        // https://www.php.net/manual/en/pdo.setattribute.php
                        static::$database = new PDO($query[0], static::$username, static::$password,
                            [
                                PDO::ATTR_PERSISTENT => CarbonPHP::$cli,                // only in cli (including websockets)
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::MYSQL_ATTR_FOUND_ROWS => true,
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL
                            ]);

                    } catch (PDOException $e) {
                        if ($e->getCode() === 1049) {
                            print '<h1>Auto Setup Failed!</h1><h3>Your database DSN may be slightly malformed.</h3>';
                            print '<p>CarbonPHP requires the host come before the database in your DNS.</p>';
                            print '<p>It should follow the following format "mysql:host=127.0.0.1;dbname=C6".</p>';
                        }
                        /** @noinspection ForgottenDebugOutputInspection */
                        var_dump($e->getMessage());
                        exit($e->getMessage());
                    }

                    $stmt = "CREATE DATABASE $db_name;";

                    if (!$db->prepare($stmt)->execute()) {
                        print '<h1>Failed to insert database. See CarbonPHP.com for documentation.</h1>' and die;
                    } else {
                        $db->exec("use $db_name");
                        static::setUp(!CarbonPHP::$cli, CarbonPHP::$cli);
                    }
                    break;
                case '42S02':
                    /** @noinspection ForgottenDebugOutputInspection */
                    var_dump($e->getMessage());

                    print $e->getMessage() . PHP_EOL . '<br />';

                    static::setUp(!CarbonPHP::$cli, CarbonPHP::$cli);

                    break;
                default:
                    if (empty(static::$username)) {
                        print '<h2>You must set a database user name. See CarbonPHP.com for documentation</h2>';
                    }
                    if (empty(static::$password)) {
                        print '<h2>You may need to set a database password. See CarbonPHP.com for documentation</h2>';
                    }
                    print $e->getMessage() . '<br>';    // This may print twice if a try catch block is active.

                    exit($e->getMessage());                            // but don't fear, die does work.
            }
        }
    }


    /** Clears and restarts the PDO connection
     * @return PDO
     */
    public static function reset(): PDO // built to help preserve database in sockets and forks
    {
        self::$database = null;

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
                return self::TryCatch(
                    static function () use ($prep) {
                        // @link https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement
                        return $prep(@new PDO(static::$dsn, static::$username, static::$password, array(PDO::MYSQL_ATTR_FOUND_ROWS => true)));
                    });
            } catch (Throwable $e) {
                $attempts++;
            }
        } while ($attempts < 3);

        ColorCode::colorCode('Failed to connect to database.', 'red');

        ErrorCatcher::generateLog($e);

        die(1);
    }

    /** Overwrite the current database. If nothing is given the
     * connection to the database will be closed
     * @param PDO|null $database
     */
    public static function setDatabase(PDO $database = null): void
    {
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
            self::colorCode('Connecting on ' . self::$dsn);
        } else {
            print '<h3>This build is automatically generated by CarbonPHP. When you add a table be sure to re-execute the <br><br><br><b>>> php index.php restbuilder</b></h3>' . PHP_EOL . PHP_EOL;
            print '<h1>Connecting on </h1>' . self::$dsn . '<br>';
        }

        if (null !== $tableDirectory) {
            self::refreshDatabase($tableDirectory);
        } else if (!empty(static::$setup)) {
            if (file_exists(static::$setup)) {
                include static::$setup;
            } else {
                self::colorCode('Failed to locate setup file at (' . static::$setup . '). This feature is deprecated and will 
                    be removed in the future. Please use the Rest Generation to manage database rebuilds. See CarbonPHP.com for details.', iColorCode::RED);
            }
        } else {
            self::refreshDatabase();
        }

        if ($refresh && !$cli) {
            print '<br><br><h2>Refreshing in 6 seconds</h2><script>let t1 = window.setTimeout(function(){ window.location.href = \'' . CarbonPHP::$site . '\'; },6000);</script>';
            exit(0);
        }

        if (CarbonPHP::$cli) {
            print PHP_EOL . PHP_EOL . PHP_EOL;
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
     * @param string|null $errorMessage
     * @return bool
     * @throws PublicAlert
     */
    public static function verify(string $errorMessage = null): bool
    {
        $pdo = self::database();

        if (!$pdo->inTransaction()) {        // We're verifying that we do not have an un finished transaction
            return true;
        }
        try {
            $pdo->rollBack();  // this transaction was started after our keys were inserted..
            if (!empty(self::$entityTransactionKeys)) {
                foreach (self::$entityTransactionKeys as $key) {
                    static::remove_entity($key);
                }
            }
        } catch (PDOException $e) {                     // todo - think about this more
            ErrorCatcher::generateBrowserReportFromError($e);
        } finally {
            if (null !== $errorMessage) {
                PublicAlert::danger($errorMessage);
            }
        }
        return false;
    }

    /** Commit the current transaction to the database.
     * @link http://php.net/manual/en/pdo.rollback.php
     * @param callable|mixed $lambda
     * @return bool
     * @throws PublicAlert
     */
    public static function commit(callable $lambda = null): bool
    {
        $db = self::database();

        if (!$db->inTransaction()) {
            return true;
        }

        if (!$db->commit()) {
            return static::verify();
        }

        self::$entityTransactionKeys = [];

        if ($lambda === null) {
            return true;
        }

        $return = $lambda();

        if (!is_bool($return)) {
            throw new Error('The return type of the lambda supplied should be a boolean');
        }

        return $return;
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
        do {
            $count++;
            $id = Carbons::Post([
                Carbons::ENTITY_TAG => $tag_id,
                Carbons::ENTITY_FK => $dependant
            ]);
        } while ($id === false && $count < 4);  // todo - why four?

        if ($id === false) {
            throw new PublicAlert('C6 failed to create a new entity.');
        }

        self::$entityTransactionKeys[] = $id;

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
        return Carbons::Delete($ref, $id, []); //Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]);
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

    /** Fetch a sql query and return results directly into the global scope
     * @param string $sql
     * @param $execute
     */
    protected static function fetch_to_global(string $sql, $execute): void
    {
        $stmt = self::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Globals::class);
        $stmt->execute($execute);
        $stmt->fetchAll();  // user obj
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


    public static function buildCarbonPHP() : void {
        self::refreshDatabase(CarbonPHP::CARBON_ROOT . DS . 'tables' . DS);
    }


    /**
     * This is not implemeted in the wild rn
     *
     *
     * to be proficient it needs to use dynamically the configuration passed to carbonphp
     *
     * @param string $tableDirectory
     * @param bool|null $cli
     */
    public static function refreshDatabase(string $tableDirectory = '', bool $cli = null): void
    {
        if (null === $cli) {
            $cli = CarbonPHP::$cli;
        }

        $autoTarget = static function () use (&$tableDirectory) {
            $composerJson = self::getComposerConfig();
            $tableNamespace = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::NAMESPACE] ??= "Tables\\";
            $tableDirectory = $composerJson['autoload']['psr-4'][$tableNamespace] ?? false;
            if (false === $tableDirectory) {
                throw new PublicAlert('Failed to parse composer json for ["autoload"]["psr-4"]["' . $tableNamespace .'"].');
            }
            $tableDirectory = CarbonPHP::$app_root . $tableDirectory;
        };

        try {
            if (CarbonPHP::$carbon_is_root) {
                $tableDirectory = CarbonPHP::CARBON_ROOT . 'tables/'; // todo - use config array to set this
            } elseif ($tableDirectory === '') {
                $autoTarget();
            }

            if ($cli) {
                self::colorCode('(Setup || Rebuild) Database');
            } else {
                print '<html><head><title>(Setup || Rebuild) Database</title></head><body><h1>REFRESHING SYSTEM</h1>' . PHP_EOL;
            }

            // ADVANCED REST REBUILD

            $restful = glob($tableDirectory . '*.php');

            $classNamespace = '';

            foreach ($restful as $filename) {
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
                throw new PublicAlert("Failed to parse class namespace from files in ($tableDirectory). 
                This could mean no files in the directory provided are Restfully generated.");
            }

            foreach ($restful as $file) {

                $className = ucwords(basename($file, '.php'), '_');

                if (!class_exists($table = substr($classNamespace, 0, -1) . $className)) {
                    self::colorCode("\n\nCouldn't load the class '$table' for refresh. This may indicate your file 
                    contains a syntax error or is not generated by the restful API.\n", iColorCode::YELLOW);
                    continue;
                }

                if (!is_subclass_of($table, Rest::class)) {
                    $restFullyQualifiedName = Rest::class;
                    self::colorCode("\n\nThe class '$table' does not implement $restFullyQualifiedName.
                        This would indicate a custom class in the table's namespaced directory. Please avoid doing this.\n", iColorCode::YELLOW);
                    continue;
                }

                $imp = array_map('strtolower', array_keys(class_implements($table)));

                if (!in_array(strtolower(iRest::class), $imp, true)
                    && !in_array(strtolower(iRestfulReferences::class), $imp, true)
                    && !in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                    && !in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                    && !in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
                ) {
                    continue;
                }

                if (defined("$table::REFRESH_SCHEMA")) {
                    self::runRefreshSchema($table::REFRESH_SCHEMA);
                }

                $db = self::database();

                if ($db->inTransaction()) {
                    self::colorCode('We are in a translation.', iColorCode::YELLOW);
                }
                
                if (!self::commit()) {
                    self::colorCode('Failed to commit to database!', iColorCode::RED);
                    exit(1);
                }
                
            }
        } catch (Throwable $e) {
            if ($cli) {
                ColorCode::colorCode('The refreshDatabase method failed.', iColorCode::BACKGROUND_RED);
            } else {
                print '<h2>The refreshDatabase method failed.</h2>';
            }
            ErrorCatcher::generateLog($e);
            exit(1);
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
            $status = self::execute($sql);
            // a switch doesn't work here because it doesn't do ===, only == and null == false
            if ($status === null) {
                if (CarbonPHP::$cli) {
                    self::colorCode('Table `' . $table_name . '` already exists');
                } else {
                    print '<br>Table `' . $table_name . '` already exists';
                }
            } else if ($status === false) {
                throw new PublicAlert('Failed to update table :: ' . $table_name);
            } else if (CarbonPHP::$cli) {
                self::colorCode('Table `' . $table_name . '` Created');
            } else {
                print '<br><p style="color: green">Table `' . $table_name . '` Created</p>';
            }
        }
    }

    /**
     * @param string $table_name
     * @param string $sql
     * @param bool $forceEngineAndCharset - this will force the table to generate with InnoDB and utf8mb4 for the charset
     * @return bool|null
     */
    public static function tableExistsOrExecuteSQL(string $table_name, string $sql, bool $forceEngineAndCharset = true): ?bool
    {
        // Check if exist the column named image
        $result = self::fetch("SELECT * 
                        FROM information_schema.tables
                        WHERE table_schema = '" . self::$name . "' 
                            AND table_name = '$table_name'
                        LIMIT 1;");

        if ([] === $result) {
            self::colorCode("Attempting to update table ($table_name).");
            if (!self::execute($sql)) {
                throw new PublicAlert('Failed to update table :: ' . $table_name);
            }
            if (CarbonPHP::$cli) {
                self::colorCode('Table `' . $table_name . '` Created');
            } else {
                print '<br><p style="color: green">Table `' . $table_name . '` Created</p>';
            }
        } else if (CarbonPHP::$cli) {
            self::colorCode('Table `' . $table_name . '` already exists');
        } else {
            print '<br>Table `' . $table_name . '` already exists';
        }
        return null;
    }
} 

