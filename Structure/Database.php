<?php // Parent of Model class, can only be called indirectly

namespace Carbon;

use PDO;

class Database
{
    private static $database;
    private static $username = DB_USER;
    private static $password = DB_PASS;
    private static $dsn = DB_DSN;

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
                            self::setUp();
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

    public static function setUp()
    {
        if (file_exists(CARBON_ROOT . 'Extras/buildDatabase.php'))
            require_once CARBON_ROOT . 'Extras/buildDatabase.php';
        return self::database();
    }

} 

