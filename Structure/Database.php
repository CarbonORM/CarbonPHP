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
            self::$database->query("SELECT 1");     // This has had a history of causing spotty error.. if this is the location of your error, you should keep looking...
            return self::$database;
        } catch (\Error $e) {
            error_reporting(REPORTING);
            return self::reset();
        }
    }

    public static function reset($clear = false){       // mainly to help preserve database in sockets and forks
        self::$database = null;

        if ($clear) return true;

        $attempts = 0;

        do {
            try {
                $db = @new PDO( "mysql:host=".static::$host.";dbname=".static::$dbName, static::$username, static::$password );

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $db->setAttribute(PDO::ATTR_PERSISTENT, SOCKET);

                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

                self::$database = $db;

                return self::$database;
            } catch (\PDOException $e){
                if (empty(static::$host))
                    print '<h2>You must set a host. See CarbonPHP.com for documentation</h2>';

                if (empty(static::$dbName))
                    print '<h2>You must set a database name. See CarbonPHP.com for documentation</h2>';

                if (empty(static::$username))
                    print '<h2>You must set a database user name. See CarbonPHP.com for documentation</h2>';

                if (empty(static::$password))
                    print '<h2>You may need to set a database password. See CarbonPHP.com for documentation</h2>';


                print $e->getMessage() . '<br>';    // This may print twice if the error catcher is trying to
                die(0);                            // but don't fear, die works...
            } catch (\Error $e) {                   // The error catcher
                $attempts++;
            }
        } while ($attempts < 3);

        print 'We failed to connect to our database, please try again later.' . PHP_EOL;

        print $e->getMessage();

        exit(1);
    }

    public static function setUp()
    {
        if (file_exists(CARBON_ROOT . 'Extras/buildDatabase.php'))
            require_once CARBON_ROOT . 'Extras/buildDatabase.php';
    }

} 

