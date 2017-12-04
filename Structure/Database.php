<?php // Parent of Model class, can only be called indirectly

namespace Carbon;

use PDO;

class Database
{
    private static $database;
    private static $username = DB_USER;
    private static $password = DB_PASS;
    private static $dbName = DB_NAME;
    private static $host = DB_HOST;

    public static function database(string $dbName = null): PDO
    {

        if (empty(self::$database) || !self::$database instanceof PDO)
            return static::reset();

        try {
            error_reporting(0);
            self::$database->query("SELECT 1");
            return self::$database;
        } catch (\Error $e) {
            error_reporting(REPORTING);
            return self::reset();
        }
    }

    public static function reset($clear = false){
        self::$database = null;
        if ($clear) return true;
        $attempts = 0;
        do { try {
                $db = @new PDO( "mysql:host=".static::$host.";dbname=".static::$dbName, static::$username, static::$password );
                $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $db->setAttribute( PDO::ATTR_PERSISTENT, SOCKET );
                $db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
                self::$database = $db;
                return self::$database;
            } catch (\Error $e) {
                $attempts++;
            }
        } while($attempts < 3);

        print 'We failed to connect to our database, please try again later.' . PHP_EOL;

        print $e->getMessage();

        exit(1);
    }

    public static function setUp()
    {
        if (file_exists(  CARBON_ROOT . 'Extras/buildDatabase.php' ))
            require_once CARBON_ROOT . 'Extras/buildDatabase.php';
    }

} 

