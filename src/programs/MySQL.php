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
use Throwable;

trait MySQL
{

    private array $config;
    private string $mysql = '';
    private string $mysqldump = '';


    public function __construct($CONFIG)
    {
        [$this->config] = $CONFIG;
    }

    private function mysql_native_password() : void
    {
        $query = <<<IDENTIFIED
                    ALTER USER '{$this->config['DATABASE']['DB_USER']}'@'localhost' IDENTIFIED WITH mysql_native_password BY '{$this->config['DATABASE']['DB_PASS']}';
                    ALTER USER '{$this->config['DATABASE']['DB_USER']}'@'%' IDENTIFIED WITH mysql_native_password BY '{$this->config['DATABASE']['DB_PASS']}';
IDENTIFIED;
        print PHP_EOL . $query . PHP_EOL;

        try {
            if (!file_put_contents('query.txt', $query)){
                print 'Failed to create query file!';
                exit(2);
            }

            $out = $this->MySQLSource(true, 'query.txt');

            $this->removeFiles();

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


    private function buildCNF($cnfFile = null) : string
    {
        if ($cnfFile !== null) {
            $this->mysql = $cnfFile;
            return $cnfFile;
        }

        if (!empty($this->mysql)){
            return $this->mysql;
        }

        if (empty($this->config['SITE']['CONFIG'])) {
            print 'The [\'SITE\'][\'CONFIG\'] option is missing. It should have the value __FILE__. This helps with debugging.' . PHP_EOL;
            exit(1);
        }

        if (empty($this->config['DATABASE']['DB_USER'])){
            print 'You must set [\'DATABASE\'][\'DB_USER\'] in the "' . $this->config['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        if (empty($this->config['DATABASE']['DB_HOST'])) {
            print 'You must set [\'DATABASE\'][\'DB_HOST\'] in the "' . $this->config['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        if (empty($this->config['DATABASE']['DB_PORT'])) {
            print 'No [\'DATABASE\'][\'DB_PORT\'] configuration active. Using default port 3306. ' . PHP_EOL;
            $this->config['DATABASE']['DB_PORT'] = 3306;
        }

        // We're going to use this function to execute mysql from the command line
        // Mysql needs this to access the server
        $cnf = ['[client]', "user = {$this->config['DATABASE']['DB_USER']}", "password = {$this->config['DATABASE']['DB_PASS']}", "host = {$this->config['DATABASE']['DB_HOST']}", "port = {$this->config['DATABASE']['DB_PORT']}"];
        if (false === file_put_contents(CarbonPHP::$app_root . 'mysql.cnf', implode(PHP_EOL, $cnf))) {
            print 'Failed to store file contents of mysql.cnf in ' . CarbonPHP::$app_root;
            exit('Failed to store file contents mysql.cnf in ' . CarbonPHP::$app_root);
        }
        return $this->mysql = CarbonPHP::$app_root . 'mysql.cnf';
    }

    /**
     * @param String|null $mysqldump
     * @return string
     * @throws PublicAlert
     */
    private function MySQLDump(String $mysqldump = null) : string
    {
        $cmd = ($mysqldump ?? 'mysqldump') . ' --defaults-extra-file="' . $this->buildCNF() . '" --no-data ' . $this->config['DATABASE']['DB_NAME'] . ' > '. CarbonPHP::$app_local .'mysqldump.sql';
        ColorCode::colorCode("\n\nRunning Command >> $cmd\n\n");
        shell_exec($cmd);
        return $this->mysqldump = CarbonPHP::$app_root . 'mysqldump.sql';
    }

    /**
     * @param bool $verbose
     * @param String $query
     * @param bool $mysql
     * @return string|null
     * @throws PublicAlert
     */
    private function MySQLSource(bool $verbose, String $query, $mysql = false) : ?string
    {
        $cmd = ($mysql ?: 'mysql') . ' --defaults-extra-file="' . $this->buildCNF() . '" ' . $this->config['DATABASE']['DB_NAME'] . ' < "' . $query . '"';
        ColorCode::colorCode("\n\nRunning Command >> $cmd\n\n");
        return shell_exec($cmd);
    }

    public function cleanUp() : void
    {
        #unlink('./mysql.cnf');
        #unlink('./mysqldump.sql');  todo - argument, uncommenting will break git actions
    }

}