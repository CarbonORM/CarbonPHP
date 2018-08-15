<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/28/17
 * Time: 5:48 AM
 */

namespace CarbonPHP;

use CarbonPHP\Table\carbon_tag;
use PDO;
use stdClass;
use CarbonPHP\helpers\Globals;
use CarbonPHP\interfaces\iRest;
use CarbonPHP\error\PublicAlert;
use CarbonPHP\Table\carbon;

/**
 * Class Entities
 * @link https://en.wikipedia.org/wiki/Entity–component–system
 * @package Carbon
 *
 * Popular in game development, web apps are perfect
 * candidates for Entity Systems.
 * Databases become complicated when you need
 * a hierarchical system of inheritance.
 * A singular tuple table containing only primary keys solves
 * this issue. If a table needs or may need a primary key, you
 * must use the Entities.beginTransaction() &
 *  Entities.commit() methods to generate them.
 */
abstract class Entities
{
    /**
     * @var PDO - Represents a connection between PHP and a database server.
     * @link http://php.net/manual/en/class.pdo.php
     */
    protected $db;
    /**
     * @var bool - Represents a post, aka new row inception with foreign keys, in progress.
     */
    private static $inTransaction = false;
    /**
     * @var array - new key inserted but not verified currently
     */
    private static $entityTransactionKeys;


    public static function database() : PDO {
        return Database::database();
    }

    /**
     * Entities constructor.
     * @param array|null $array
     *
     * Define our database and possibly create a new row in the database
     */
    public function __construct(array &$array = null)
    {
        $this->db = self::database();
        if ($this instanceof iRest && $array !== null) {
            $this::Post($array);
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
            Database::database()->rollBack();
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
     * @param callable|null $lambda
     * @return bool
     */
    protected static function commit(callable $lambda = null): bool
    {
        if (!Database::database()->commit()) {
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
     * This <b>must be static</b> so multiple table files can insert on the same
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
        Database::database()->beginTransaction();
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
    public static function genRandomHex($bitLength = 40)
    {
        $sudoRandom = 1;
        for ($i = 0; $i <= $bitLength; $i++) $sudoRandom = ($sudoRandom << 1) | rand(0, 1);
        return dechex($sudoRandom);
    }

    /**
     * @param $tag_id
     * This will be inserted in out tags table, it is just a reference
     *  I define constants named after the tables in the configuration file
     *  which I use for this field. ( USERS, MESSAGES, ect...)
     *
     * @param $dependant
     * @return bool|\PDOStatement|string
     */
    protected static function new_entity($tag_id, $dependant = null)
    {
        do {        // TODO - log the tag
            try {
                $stmt = carbon::Post([
                    'entity_fk'=>$dependant
                ]);
                $stmt = carbon_tag::Post([
                    'tag_id'=>$stmt,
                    'entity_id'=>$tag_id,
                    'user_id'=>$_SESSION['id'],
                ]);
            } catch (\PDOException $e) {
                $stmt = false;
            }
        } while (!$stmt);
        self::$entityTransactionKeys[] = $stmt;
        return $stmt;
    }

    /**
     * If other entities reference the my be deleted baised on how they are set up on your database
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
     * @param $id - Remove entity_pk form carbon
     * @return bool
     */
    protected static function remove_entity($id) : bool
    {
        $ref = [];
        return carbon::Delete($ref,$id,[]); //Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]);
    }


    protected static function execute(string $sql, ...$execute) : bool
    {
        return Database::database()->prepare($sql)->execute($execute);
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
        $stmt = Database::database()->prepare($sql);
        if (!$stmt->execute($execute) && !$stmt->execute($execute)) { // try it twice, you never know..
            return [];
        }
        return (\count($stmt = $stmt->fetchAll()) === 1 ?
            (\is_array($stmt['0']) ? $stmt['0'] : $stmt) : $stmt);   // promise this is needed and will still return the desired array
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
        $stmt = Database::database()->prepare($sql);
        if (!$stmt->execute($execute)) {
            return [];
        }
        return (\count($stmt = $stmt->fetchAll(PDO::FETCH_COLUMN)) === 1 ?
            (\is_array($stmt['0']) ? $stmt['0'] : $stmt) : $stmt);
    }

    /** This returns all values from the requested query as an Object to type stdClass.
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
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, stdClass::class);
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
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, stdClass::class);
        if (!$stmt->execute($execute)) {
            return [];
        }
        return $stmt->fetchAll();  // user obj
    }

    /** Fetch a sql query and return results directly into the global scope
     * @param string $sql
     * @param $execute
     */
    protected static function fetch_to_global(string $sql, $execute)
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Globals::class);
        $stmt->execute($execute);
        $stmt->fetchAll();  // user obj
    }

    /** Run an sql statement and return results as attributes of a stdClass
     * @param $object
     * @param $sql
     * @param array ...$execute
     */
    protected static function fetch_into_class(&$object, $sql, ...$execute)
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->execute($execute);
        $array = $stmt->fetchAll();
        foreach ($array as $key => $value) {
            $object->$key = $value;
        }
    }

}