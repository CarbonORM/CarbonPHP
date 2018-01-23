<?php // Parent of Model class, can only be called indirectly

namespace Carbon;

use Carbon\Error\ErrorCatcher;
use PDO;

/**
 * Class Database
 * @package Carbon
 *
 *  TODO - create setters? lambda binding makes me not care about permissions so I think no
 */
class Database
{
    /** Represents a connection between PHP and a database server.
     * @link http://php.net/manual/en/class.pdo.php
     */
    private static $database;

    public static $username;

    public static $password;
    /**
     * @var string this hold the connection protocol
     * @link http://php.net/manual/en/pdo.construct.php
     */
    public static $dsn;

    /**
     * @var string holds the path of the users database set up file
     */
    public static $setup;

    /** the database method will return a connection to the database
     * Before returning a connection it must pass an active check.
     * This is mainly for persistent socket connections.
     * @return PDO
     */
    public static function database(): PDO
    {

        if (empty(self::$database) || !self::$database instanceof PDO)
            return static::reset();

        try {
            error_reporting(0);
            PDO.exec("SELECT 1");     // This has had a history of causing spotty error.. if this is the location of your error, you should keep looking...
            error_reporting(ErrorCatcher::$level);
            return static::$database;
        } catch (\Error | \Exception $e) {                       // added for socket support
            error_reporting(ErrorCatcher::$level);
            return static::reset();
        }
    }

    /** Clears and restarts the PDO connection
     * @param bool $clear set this to true if you do not want to re-initialise the connection
     * @return bool|PDO
     */
    public static function reset() // mainly to help preserve database in sockets and forks
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
                return $prep(@new PDO(static::$dsn, static::$username, static::$password));

            } catch (\PDOException $e) {

                switch ($e->getCode()) {        // Database has not been created

                    case 1049:
                        $query = explode(';', static::$dsn);

                        $db_name = explode('=', $query[1])[1];

                        if (empty($db_name))
                            print '<h1>Could not determine a database to create. See Carbonphp.com for documentation.</h1>' and die;

                        $db = $prep(@new PDO($query[0], static::$username, static::$password));

                        $stmt = "CREATE DATABASE $db_name;";

                        if (!$db->prepare($stmt)->execute())
                            print '<h1>Failed to insert database. See Carbonphp.com for documentation.</h1>' and die;
                        else
                            $db->query("use $db_name")
                            and static::setUp(true);   // this will exit
                        break;
                    case '42S02':
                        static::setUp(true);
                        break;

                    default:
                        if (empty(static::$username))
                            print '<h2>You must set a database user name. See CarbonPHP.com for documentation</h2>';

                        if (empty(static::$password))
                            print '<h2>You may need to set a database password. See CarbonPHP.com for documentation</h2>';

                        print $e->getMessage() . '<br>';    // This may print twice if the error catcher is trying to

                        die(0);                            // but don't fear, die works...

                }

            } catch (\Error $e) {                   // The error catcher
                $attempts++;
            }
        } while ($attempts < 3);

        print 'We failed to connect to our database, please try again later.' . PHP_EOL;

        print $e->getMessage();

        exit(1);
    }

    /** Overwrite the current database. If nothing is given the
     * connection to the database will be closed
     * @param PDO|null $database
     */
    public static function setDatabase(PDO $database = null)
    {
        self::$database = $database;
    }

    /**
     * This will attempt to create the required tables for CarbonPHP
     * If a file aptly named `buildDatabase.php` exists in your configuration
     * file it will also be run. Be sure to model your build tool after ours
     * so it does not block.. setUp is synonymous = resetDatabase (which doesn't exist)
     *
     * @param bool $refresh if set to true will send Javascript
     * to refresh the browser using SITE constant
     * @return PDO
     */
    public static function setUp(bool $refresh)
    {
        if (file_exists(CARBON_ROOT . 'Extras/buildDatabase.php'))
            require_once CARBON_ROOT . 'Extras/buildDatabase.php';

        if (file_exists(static::$setup))
            include_once static::$setup;
        else print '<h3>When you add a database be sure to add it to the file ["DATABASE"][]</h3><h5>Use '. __FILE__ .' a as refrence.</h5>';

        if ($refresh)
            print '<br><br><h2>Refreshing in 6 seconds</h2><script>t1 = window.setTimeout(function(){ window.location.href = "'.SITE.'"; },6000);</script>'
            and exit(1);

        return static::database();
    }

} 

