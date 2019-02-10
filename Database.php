<?php // Parent of Model class, can only be called indirectly

namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Globals;
use CarbonPHP\Tables\carbon_tag;
use CarbonPHP\Tables\carbons;
use PDO;


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
    /** Represents a connection between PHP and a database server.
     * @link http://php.net/manual/en/class.pdo.php
     * @var PDO $database
     */
    public static $database;

    public static $username;

    public static $password;
    /**
     * @var string $dsn holds the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public static $dsn;

    /**
     * @var string holds the path of the users database set up file
     */
    public static $setup;
    
    /**
     * @var bool - Represents a post, aka new row inception with foreign keys, in progress.
     */
    private static $inTransaction = false;
    /**
     * @var array - new key inserted but not verified currently
     */
    private static $entityTransactionKeys;

    /** the database method will return a connection to the database.
     * Before returning a connection it must pass an active check.
     * This is mainly for persistent socket connections.
     * @return PDO
     */
    public static function database(): PDO
    {
        if (null === self::$database || !self::$database instanceof PDO) {
            return static::reset();
        }
        try {
            error_reporting(0);
            self::$database->prepare('SELECT 1');     // This has had a history of causing spotty error.. if this is the location of your error, you should keep looking...
            error_reporting(ErrorCatcher::$level);
            return static::$database;                       // Why should this work again?
        } catch (\Error | \Exception | \PDOException $e) {                       // added for socket support
            error_reporting(ErrorCatcher::$level);
            return static::reset();
        }
    }

    public static function TryCatch(callable $closure) // TODO - carbon reporting on errors.. probably in 5.*
    {
        try {
            return $closure();
        } catch (\PDOException $e) {

            switch ($e->getCode()) {        // Database has not been created

                case 1049:
                    $query = explode(';', static::$dsn);    // I programmatically put it there which is why..

                    $db_name = explode('=', $query[1])[1];  // I dont validate with count on this

                    if (empty($db_name)) {
                        print '<h1>Could not determine a database to create. See Carbonphp.com for documentation.</h1>';
                        exit(1);
                    }

                    Try {
                        $prep = function (PDO $db): PDO {
                            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $db->setAttribute(PDO::ATTR_PERSISTENT, SOCKET);

                            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

                            static::$database = $db;

                            return static::$database;
                        };

                        $db = $prep(@new PDO($query[0], static::$username, static::$password));
                    } catch (\PDOException $e) {
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
                        static::setUp(true);   // this will exit
                    }
                    break;
                case '42S02':
                    /** @noinspection ForgottenDebugOutputInspection */
                    var_dump($e->getMessage());

                    print $e->getMessage() . PHP_EOL . '<br />';

                    static::setUp(true);

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
        $attempts = 0;

        $prep = function (PDO $db): PDO {
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $db->setAttribute(PDO::ATTR_PERSISTENT, SOCKET);

            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            static::$database = $db;

            return static::$database;
        };

        do {
            try {
                return self::TryCatch(
                    function () use ($prep) {
                        return $prep(@new PDO(static::$dsn, static::$username, static::$password));
                    });
            } catch (\Error $e) {                   // The error catcher
                $attempts++;
            }
        } while ($attempts < 3);

        print 'We failed to connect to our database, please try again later.' . PHP_EOL . $e->getMessage();

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
     * @return PDO
     */
    public static function setUp(bool $refresh = true): PDO
    {
        if (file_exists(CARBON_ROOT . 'Config/buildDatabase.php')) {
            print '<h1>Connecting on </h1>'. self::$dsn . '<br>';
            include CARBON_ROOT . 'config/buildDatabase.php';
        } else {
            print '<h1>Could not find database setup. Please see Carbonphp.com for more documentation</h1>';
            die(1);
        }
        if (file_exists(static::$setup)) {
            include static::$setup;
        } else {
            print '<h3>This build is automatically generated by CarbonPHP. When you add a database be sure to re-execute the <br><br><br><b>>> php index.php buildDatabase</h3>';
        }
        if ($refresh) {
            print '<br><br><h2>Refreshing in 6 seconds</h2><script>t1 = window.setTimeout(function(){ window.location.href = \'' . SITE . '\'; },6000);</script>';
            exit(1);
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
     */
    public static function verify(string $errorMessage = null): bool
    {
        if (!static::$inTransaction) {        // We're verifying that we do not have an un finished transaction
            return true;
        }

        if (!empty(self::$entityTransactionKeys)) {
            foreach (self::$entityTransactionKeys as $key) {
                static::remove_entity($key);
            }
        }

        try {
            self::database()->rollBack();
        } catch (\PDOException $e) {
            PublicAlert::danger($e->getMessage());
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
     */
    protected static function commit(callable $lambda = null): bool
    {
        if (!self::$inTransaction) {
            return true;
        }

        if (!self::database()->commit()) {
            return static::verify();
        }

        self::$inTransaction = false;

        self::$entityTransactionKeys = [];

        if (\is_callable($lambda)) {
            return $lambda();
        }

        return true;
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
     * @param $tag_id - passed to new_entity
     * @param null $dependant - passed to new_entity
     *
     * @return bool|\PDOStatement|string
     */
    protected static function beginTransaction($tag_id, $dependant = null)
    {
        self::$inTransaction = true;
        $key = self::new_entity($tag_id, $dependant);
        self::database()->beginTransaction();
        return $key;
    }


    /** http://php.net/manual/en/language.operators.bitwise.php
     * in actual system we will have to see what bit system we are using
     * 32 bit = 28
     * 64 biit = 60
     * I assume this means php uses 4 bits to denote type (vzal) ?
     * idk
     * @param int $bitLength
     * @return string
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
     * @return bool|\PDOStatement|string
     */
    protected static function new_entity($tag_id, $dependant = null)
    {
        $id = carbons::Post([
            'entity_fk' => $dependant
        ]);
        carbon_tag::Post([
            'tag_id' => (int) $tag_id,
            'entity_id' => $id,
            'user_id' => $_SESSION['id'],
        ]);

        self::$entityTransactionKeys[] = $id;
        return $id;
    }

    /**
     *
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
     * @param $id - Remove entity_pk form carbon
     * @return bool
     */
    protected static function remove_entity($id): bool
    {
        $ref = [];
        return carbons::Delete($ref, $id, []); //Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]);
    }


    protected static function execute(string $sql, ...$execute): bool
    {
        return self::database()->prepare($sql)->execute($execute);
    }

    /**
     * Use prepaired statements with question mark values.
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
        if (\count($stmt = $stmt->fetchAll(PDO::FETCH_ASSOC)) !== 1){
            return $stmt;
        }

        while (\is_array($stmt)) {
            $stmt = array_shift($stmt);
        }

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

        if (\count($stmt = $stmt->fetchAll(PDO::FETCH_ASSOC)) !== 1){
            return $stmt;
        }

        while (\is_array($stmt)) {
            $stmt = array_shift($stmt);
        }

        return [$stmt];
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
     * @throws \Exception
     */
    protected static function fetch_object(string $sql, ...$execute): stdClass
    {
        $stmt = self::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, \stdClass::class);
        if (!$stmt->execute($execute)) {
            throw new \Exception('Failed to Execute');
        }
        $stmt = $stmt->fetchAll();  // user obj
        return (\is_array($stmt) && \count($stmt) === 1 ? $stmt[0] : new stdClass);
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
} 

