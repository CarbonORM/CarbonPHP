<?php // Parent of Model class, can only be called indirectly

namespace Carbon;

use PDO;

/**
 * Class Database
 * @package Carbon
 */
class Database
{
    /**
     * @var PDO
     */
    private static $database;
    /**
     * @var string
     */
    private static $username = DB_USER;
    /**
     * @var string
     */
    private static $password = DB_PASS;
    /**
     * @var string
     */
    private static $dsn = DB_DSN;

    /**
     * @return PDO
     */
    public static function database(): PDO
    {

        if (empty(self::$database) || !self::$database instanceof PDO)
            return static::reset();

        try {
            error_reporting(0);
            self::$database->query("SELECT 1");     // This has had a history of causing spotty error.. if this is the location of your error, you should keep looking...
            return self::$database;
        } catch (\Error $e) {                       // added for socket support
            error_reporting(REPORTING);
            return self::reset();
        }
    }

    /** Clears and restarts the PDO connection
     *
     * @param bool $clear - set this to true if you do not want to re-initialise the connection
     * @return bool|PDO
     */
    public static function reset($clear = false){       // mainly to help preserve database in sockets and forks
        self::$database = null;

        if ($clear) return true;

        $attempts = 0;


        $prep = function (PDO $db): PDO {
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $db->setAttribute(PDO::ATTR_PERSISTENT, SOCKET);

            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            self::$database = $db;

            return self::$database;
        };

        do {
            try {
                return $prep(@new PDO(static::$dsn, static::$username, static::$password));

            } catch (\PDOException $e) {

                switch ($e->getCode()) {        // Database has not been created

                    case 1049:
                        $query = explode(';', self::$dsn);

                        $db_name = explode('=', $query[1])[1];

                        if (empty($db_name))
                            print '<h1>Could not determine a database to create. See Carbonphp.com for documentation.</h1>' and die;

                        $db = $prep(@new PDO($query[0], static::$username, static::$password));

                        $stmt = "CREATE DATABASE $db_name;";

                        if (!$db->prepare($stmt)->execute())
                            print '<h1>Failed to insert database. See Carbonphp.com for documentation.</h1>' and die;
                        else
                            $db->query("use $db_name") and self::setUp();
                        break;
                    case '42S02':
                            self::setUp(true);
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

    /** Overwrite the current database
     * @param PDO $database
     */
    public static function setDatabase(PDO $database)
    {
        self::$database = $database;
    }

    /**
     * This will attempt to create the required tables for CarbonPHP
     * If a file aptly named `buildDatabase.php` exists in your configuration
     * file it will also be run. Be sure to model your build tool after ours
     * so it does not block.. setUp is synonymous = resetDatabase (which doesn't exist)
     *
     * @param bool $refresh - if true sends Javascript refresh to browser using SITE constant
     * @return PDO
     */
    public static function setUp(bool $refresh)
    {
        if (file_exists(CARBON_ROOT . 'Extras/buildDatabase.php'))
            require_once CARBON_ROOT . 'Extras/buildDatabase.php';

        if ($refresh)
            print '<br><br><h2>Refreshing in 6 seconds</h2><script>t1 = window.setTimeout(function(){ window.location.href = "'.SITE.'"; },6000);</script>'
            and exit(1);

        return self::database();
    }

} 

