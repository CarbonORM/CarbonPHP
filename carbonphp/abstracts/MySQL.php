<?php
/**
 * Rest assured this is not a php version of mysql.
 *
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 11:27 PM
 */

namespace CarbonPHP\Abstracts;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;

abstract class MySQL
{

    public static string $mysql = '';
    public static string $mysqldump = '';


    public static function mysql_native_password(): void
    {
        $c = CarbonPHP::$configuration;

        $query = <<<IDENTIFIED
                    ALTER USER '{$c['DATABASE']['DB_USER']}'@'localhost' IDENTIFIED WITH mysql_native_password BY '{$c['DATABASE']['DB_PASS']}';
                    ALTER USER '{$c['DATABASE']['DB_USER']}'@'%' IDENTIFIED WITH mysql_native_password BY '{$c['DATABASE']['DB_PASS']}';
IDENTIFIED;

        print PHP_EOL . $query . PHP_EOL;

        try {

            if (!file_put_contents('query.txt', $query)) {

                print 'Failed to create query file!';

                exit(2);

            }

            self::mysqlSource('query.txt');

            if (!unlink('query.txt')) {
                print 'Failed to remove query.txt file' . PHP_EOL;
            }

            exit(0);

        } catch (Throwable $e) {

            print 'Failed to change mysql auth method' . PHP_EOL;

            ThrowableHandler::generateLog($e);

            exit(1);

        }

    }


    public static function buildCNF($cnfFile = null): string
    {

        if ('' !== self::$mysql) {

            return self::$mysql;

        }

        $c = CarbonPHP::$configuration;

        if (empty($c[CarbonPHP::SITE][CarbonPHP::CONFIG])) {

            print 'The [\'SITE\'][\'CONFIG\'] option is missing. It should have the value __FILE__. This helps with debugging.' . PHP_EOL;

            exit(1);

        }

        if (empty($c[CarbonPHP::DATABASE][CarbonPHP::DB_USER])) {

            print 'You must set [\'DATABASE\'][\'DB_USER\'] in the "' . $c['SITE']['CONFIG'] . '" file.' . PHP_EOL;

            exit(1);

        }

        if (empty($c[CarbonPHP::DATABASE][CarbonPHP::DB_HOST])) {
            print 'You must set [\'DATABASE\'][\'DB_HOST\'] in the "' . $c['SITE']['CONFIG'] . '" file.' . PHP_EOL;
            exit(1);
        }

        $cnf = [
            '[client]',
            "user = {$c[CarbonPHP::DATABASE][CarbonPHP::DB_USER]}",
            "password = {$c[CarbonPHP::DATABASE][CarbonPHP::DB_PASS]}",
            "host = {$c[CarbonPHP::DATABASE][CarbonPHP::DB_HOST]}",
            PHP_EOL
        ];

        if (($c[CarbonPHP::DATABASE][CarbonPHP::DB_PORT] ?? false) && $c[CarbonPHP::DATABASE][CarbonPHP::DB_PORT] !== '') {

            ColorCode::colorCode('No [\'DATABASE\'][\'DB_PORT\'] configuration active. Using default port 3306. ' . PHP_EOL . 'Set to an empty string "" for mysql to auto-resolve.', 'yellow');

        }

        $c[CarbonPHP::DATABASE][CarbonPHP::DB_PORT] ??= 3306;

        // explicit empty indicates to mysql to auto-resolve, this is important for aws rds
        if (!empty($c[CarbonPHP::DATABASE][CarbonPHP::DB_PORT])) {

            $cnf[] = "port = {$c[CarbonPHP::DATABASE][CarbonPHP::DB_PORT]}";

        }

        // We're going to use this function to execute mysql from the command line
        // Mysql needs this to access the server
        if (false === file_put_contents($cnfFile ??= CarbonPHP::$app_root . 'mysql.cnf', implode(PHP_EOL, $cnf))) {
            ColorCode::colorCode('Failed to store file contents of mysql.cnf in ' . CarbonPHP::$app_root, iColorCode::RED);
            exit(1);
        }

        // @link https://www.php.net/manual/en/function.chmod.php
        if (false === chmod(CarbonPHP::$app_root . 'mysql.cnf', 0750)) {
            ColorCode::colorCode('The chmod(\'' . CarbonPHP::$app_root . 'mysql.cnf\', 0750); has failed. This isn\'t always an issue. Moving on. Cross your fingers.', iColorCode::YELLOW);
        }

        ColorCode::colorCode('Successfully created mysql.cnf file in (' . $cnfFile . ')');

        return self::$mysql = $cnfFile;
    }

    /**
     * @link https://dba.stackexchange.com/questions/5033/mysqldump-with-insert-on-duplicate
     * @link https://dev.mysql.com/doc/refman/5.7/en/mysqldump-definition-data-dumps.html#:~:text=The%20%2D%2Dno%2Ddata%20option,file%20contains%20only%20table%20data.
     * @link skip-add-locks - https://stackoverflow.com/questions/104612/run-mysqldump-without-locking-tables
     * @param String|null $mysqldump
     * @param bool $data
     * @param bool $schemas
     * @param string|null $outputFile
     * @param string $otherOption -   --insert-ignore     Insert rows with INSERT IGNORE.
     *                                --replace           Use REPLACE INTO instead of INSERT INTO.
     * @param string|null $specificTable - will limit the dump to a single table! Can be left empty string or null for the full sh
     * @return string
     * @throws PublicAlert
     */
    public static function MySQLDump(string $mysqldump = null, bool $data = false, bool $schemas = true, string $outputFile = null, string $otherOption = '', string $specificTable = null): string
    {

        $specificTable ??= '';

        if (null === $outputFile) {

            $outputFile = CarbonPHP::$app_root . 'mysqldump.sql';

        }

        if (false === $data && false === $schemas) {

            ColorCode::colorCode("MysqlDump is running with --no-create-info and --no-data. Why?",
                iColorCode::BACKGROUND_YELLOW);

        }

        // defaults extra file must be the first argument
        $cmd = ($mysqldump ?? 'mysqldump') . ' '
            . ' --defaults-extra-file="' . self::buildCNF() . '" '
            . $otherOption . ' --skip-add-locks --single-transaction --quick '
            . ($schemas ? '' : ' --no-create-info ')
            . ($data ? '--hex-blob ' : '--no-data ') . CarbonPHP::$configuration['DATABASE']['DB_NAME'] . " $specificTable > '$outputFile'";

        Background::executeAndCheckStatus($cmd);

        return self::$mysqldump = $outputFile;

    }


    /**
     * @param String $filename
     * @param bool $mysql
     * @return void
     */
    public static function MySQLSource(string $filename, $mysql = false): void
    {
        $c = CarbonPHP::$configuration;
        $cmd = ($mysql ?: 'mysql') . ' --defaults-extra-file="' . self::buildCNF() . '" ' . ($c['DATABASE']['DB_NAME'] ?? '') . ' < "' . $filename . '"';
        Background::executeAndCheckStatus($cmd);
    }

    public static function cleanUp(): void
    {
        if (file_exists(CarbonPHP::$app_root . 'mysql.cnf') && !unlink('./mysql.cnf')) {
            ColorCode::colorCode('Failed to unlink mysql.cnf', iColorCode::BACKGROUND_RED);
        }
        if (file_exists(CarbonPHP::$app_root . 'mysqldump.sql') && !unlink('./mysqldump.sql')) {
            ColorCode::colorCode('Failed to unlink mysqldump.sql', iColorCode::BACKGROUND_RED);
        }
    }
}


