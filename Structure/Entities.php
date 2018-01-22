<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/28/17
 * Time: 5:48 AM
 */

namespace Carbon;

use Carbon\Helpers\Bcrypt;
use PDO;
use stdClass;
use Carbon\Helpers\Globals;
use Carbon\Helpers\Skeleton;
use Carbon\Interfaces\iTable;
use Carbon\Error\PublicAlert;

/**
 * Class Entities
 * @link https://en.wikipedia.org/wiki/Entity–component–system
 * @package Carbon
 *
 * Popular in game development, web apps are perfect
 * candidates for Entity Systems. Slighly
 * Databases become complicated when you need
 * you have rows who can reference any and a
 *
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


    /**
     * Entities constructor.
     * @param array|null $array
     *
     * Define our database and possibly create a new row in the database
     */
    public function __construct(array &$array = null)
    {
        $this->db = Database::Database();
        return ($this instanceof iTable && $array != null ?
            $this::Post($array) : null);
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
    static function verify(string $errorMessage = null): bool
    {
        if (!static::$inTransaction)        // We're verifying that we do not have an un finished transaction
            return true;

        if (!empty(self::$entityTransactionKeys))
            foreach (self::$entityTransactionKeys as $key)
                static::remove_entity($key);

        try {
            Database::Database()->rollBack();
        } catch (\PDOException $e) {
            PublicAlert::danger($e->getMessage());
        } finally {
            if (!empty($errorMessage))
                PublicAlert::danger($errorMessage);
        }
        return false;
    }

    /** Commit the current transaction to the database.
     * @link http://php.net/manual/en/pdo.rollback.php
     * @param callable|null $lambda
     * @return bool
     */
    static function commit(callable $lambda = null): bool
    {
        if (!Database::database()->commit())
            return static::verify();

        self::$inTransaction = false;

        self::$entityTransactionKeys = [];

        if (is_callable($lambda))
            return $lambda();

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
     * @param $tag_id         - passed to new_entity
     * @param null $dependant - passed to new_entity
     *
     * @return bool|\PDOStatement|string
     */
    static function beginTransaction($tag_id, $dependant = null)
    {
        self::$inTransaction = true;
        $key = self::new_entity($tag_id, $dependant);
        Database::Database()->beginTransaction();
        return $key;
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
    static function new_entity($tag_id, $dependant)
    {
        if (defined($tag_id))
            $tag_id = constant($tag_id);

        $db = Database::database();
        do {
            try {
                $stmt = $db->prepare('INSERT INTO carbon (entity_pk, entity_fk) VALUE (?,?)');
                $stmt->execute([$stmt = Bcrypt::genRandomHex(), $dependant]);
            } catch (\PDOException $e) {
                $stmt = false;
            }
        } while (!$stmt);
        $db->prepare('INSERT INTO carbon_tag (entity_id, user_id, tag_id, creation_date) VALUES (?,?,?,?)')->execute([$stmt, (!empty($_SESSION['id']) ? $_SESSION['id'] : $stmt), $tag_id, time()]);
        self::$entityTransactionKeys[] = $stmt;
        return $stmt;
    }

    /**
     * If other entities reference the my be deleted baised on how they are set up on your database
     * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-index-types.html
     * @param $id - Remove entity_pk form carbon
     * @throws \Exception
     */
    static protected function remove_entity($id)
    {
        if (!Database::database()->prepare('DELETE FROM carbon WHERE entity_pk = ?')->execute([$id]))
            throw new \Exception("Failed to delete $id");
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
    static function fetch(string $sql, ...$execute): array
    {
        $stmt = Database::database()->prepare($sql);
        if (!$stmt->execute($execute))
            if (!$stmt->execute($execute))
                return [];
        return (count($stmt = $stmt->fetchAll()) === 1 ?
            (is_array($stmt['0']) ? $stmt['0'] : $stmt) : $stmt);  //
    }

    /** Quickly prepare and execute PDO $sql statements using
     *  variable arguments.
     *
     * Example:
     * $array['following'] = self::fetchColumn('SELECT follows_user_id FROM user_followers WHERE user_id = ?', $id);
     *
     * @param string $sql           - variables should be denoted by question marks
     * @param array ...$execute     -
     *  if multiple question marks exist you may use comma separated parameters to fill the statement
     * @return array
     */
    static function fetchColumn(string $sql, ...$execute): array
    {
        $stmt = Database::database()->prepare($sql);
        if (!$stmt->execute($execute)) return [];
        return (count($stmt = $stmt->fetchAll(PDO::FETCH_COLUMN)) === 1 ?
            (is_array($stmt['0']) ? $stmt['0'] : $stmt) : $stmt);
    }

    /** This returns all vaules from the requested query as an Object to type stdClass.
     *  Its important to note that PHP arrays are hash tables. This means that
     *  while semantically pleasing, fetch_
     *
     * @param string $sql
     * @param array ...$execute
     * @return stdClass
     * @throws \Exception
     */
    static function fetch_object(string $sql, ...$execute): stdClass
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, stdClass::class);
        if (!$stmt->execute($execute))
            throw new \Exception('Failed to Execute');
        $stmt = $stmt->fetchAll();  // user obj
        return (is_array($stmt) && count($stmt) == 1 ? $stmt[0] : new stdClass);
    }

    /**
     * @param string $sql
     * @param array ...$execute
     * @return array
     */
    static function fetch_classes(string $sql, ...$execute): array
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, stdClass::class);
        if (!$stmt->execute($execute)) return [];
        return $stmt->fetchAll();  // user obj
    }

    /**
     * @param string $sql
     * @param array ...$execute
     * @return array
     */
    static function fetch_as_array_object(string $sql, ...$execute): array
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Skeleton::class);
        if (!$stmt->execute($execute)) return [];
        return $stmt->fetchAll();  // user obj
    }

    /**
     * @param string $sql
     * @param $execute
     */
    static function fetch_to_global(string $sql, $execute)
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Globals::class);
        $stmt->execute($execute);
        $stmt->fetchAll();  // user obj
    }

    /**
     * @param $object
     * @param $sql
     * @param array ...$execute
     */
    static function fetch_into_class($object, $sql, ...$execute)
    {
        $stmt = Database::database()->prepare($sql);
        $stmt->execute($execute);
        $array = $stmt->fetchAll();
        foreach ($array as $key => $value) $object->$key = $value;
    }

}