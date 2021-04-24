<?php
/**
 * Rest assured this is not a php version of mysql.
 *
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 11:27 PM
 */

namespace CarbonPHP\Programs;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;

trait MySQL
{

    private string $mysql = '';
    private string $mysqldump = '';


    private function mysql_native_password(): void
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

            $out = $this->MySQLSource(true, 'query.txt');

            if (!unlink('query.txt')) {
                print 'Failed to remove query.txt file' . PHP_EOL;
            }
            print $out . PHP_EOL;
            exit(0);
        } catch (Throwable $e) {
            print 'Failed to change mysql auth method' . PHP_EOL;
            ErrorCatcher::generateLog($e);
            exit(1);
        }
    }


    private function buildCNF($cnfFile = null): string
    {
        $c = CarbonPHP::$configuration;

        if ($cnfFile !== null) {
            $this->mysql = $cnfFile;
            return $cnfFile;
        }

        if (!empty($this->mysql)) {
            return $this->mysql;
        }

        if (empty($c['SITE']['CONFIG'])) {
            print 'The [\'SITE\'][\'CONFIG\'] option is missing. It should have the value __FILE__. This helps with debugging.' . PHP_EOL;
            exit(1);
        }

        if (empty($c['DATABASE']['DB_USER'])) {
            print 'You must set [\'DATABASE\'][\'DB_USER\'] in the "' . $c['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        if (empty($c['DATABASE']['DB_HOST'])) {
            print 'You must set [\'DATABASE\'][\'DB_HOST\'] in the "' . $c['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        $cnf = [
            '[client]',
            "user = {$c['DATABASE']['DB_USER']}",
            "password = {$c['DATABASE']['DB_PASS']}",
            "host = {$c['DATABASE']['DB_HOST']}"
        ];

        if (($c['DATABASE']['DB_PORT'] ?? false) && $c['DATABASE']['DB_PORT'] !== '') {
            ColorCode::colorCode('No [\'DATABASE\'][\'DB_PORT\'] configuration active. Using default port 3306. ' . PHP_EOL . 'Set to an empty string "" for mysql to auto-resolve.', 'yellow');
            $c['DATABASE']['DB_PORT'] = 3306;
            $cnf[] = "port = {$c['DATABASE']['DB_PORT']}";
        }

        // We're going to use this function to execute mysql from the command line
        // Mysql needs this to access the server
        if (false === file_put_contents(CarbonPHP::$app_root . 'mysql.cnf', implode(PHP_EOL, $cnf))) {
            print 'Failed to store file contents of mysql.cnf in ' . CarbonPHP::$app_root;
            exit('Failed to store file contents mysql.cnf in ' . CarbonPHP::$app_root);
        }

        return $this->mysql = CarbonPHP::$app_root . 'mysql.cnf';
    }

    /**
     * @param String|null $mysqldump
     * @param bool $data
     * @return string
     */
    private function MySQLDump(string $mysqldump = null, bool $data = false): string
    {
        $c = CarbonPHP::$configuration;
        $cmd = ($mysqldump ?? 'mysqldump') . ' --defaults-extra-file="' . $this->buildCNF() . '" '
            . ($data ? '' : '--no-data ') . $c['DATABASE']['DB_NAME'] . ' > ' . CarbonPHP::$app_root . 'mysqldump.sql';
        ColorCode::colorCode("\n\n>> $cmd");
        shell_exec($cmd);
        return $this->mysqldump = CarbonPHP::$app_root . 'mysqldump.sql';
    }


    /**
     * @param bool $verbose
     * @param String $query
     * @param bool $mysql
     * @return string|null
     */
    private function MySQLSource(bool $verbose, string $query, $mysql = false): ?string
    {
        $c = CarbonPHP::$configuration;
        $cmd = ($mysql ?: 'mysql') . ' --defaults-extra-file="' . $this->buildCNF() . '" ' . $c['DATABASE']['DB_NAME'] . ' < "' . $query . '"';
        ColorCode::colorCode("\n\nRunning Command >> $cmd\n\n");
        return shell_exec($cmd);
    }

    public function cleanUp() : void
    {
        if (file_exists(CarbonPHP::$app_root . 'mysql.cnf') && !unlink('./mysql.cnf')) {
            ColorCode::colorCode('Failed to unlink mysql.cnf', iColorCode::BACKGROUND_RED);
        }
        if (file_exists(CarbonPHP::$app_root . 'mysqldump.sql') && !unlink('./mysqldump.sql')) {
            ColorCode::colorCode('Failed to unlink mysqldump.sql', iColorCode::BACKGROUND_RED);
        }
    }
}