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


trait MySQL
{

    private $CONFIG;
    private $mysql;
    private $mysqldump;


    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
    }

    private function buildCNF() : string
    {
        if (empty($this->CONFIG['SITE']['CONFIG'])) {
            print 'The [\'SITE\'][\'CONFIG\'] option is missing. It does not look like CarbonPHP is setup correctly. Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        if (empty($this->CONFIG['DATABASE']['DB_USER'])){
            print 'You must set [\'DATABASE\'][\'DB_USER\'] in the "' . $this->CONFIG['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        if (empty($this->CONFIG['DATABASE']['DB_HOST'])) {
            print 'You must set [\'DATABASE\'][\'DB_HOST\'] in the "' . $this->CONFIG['SITE']['CONFIG'] . '".  Run `>> php index.php setup` to fix this.' . PHP_EOL;
            exit(1);
        }

        // We're going to use this function to execute mysql from the command line
        // Mysql needs this to access the server
        $cnf = ['[client]', "user = {$this->CONFIG['DATABASE']['DB_USER']}", "password = {$this->CONFIG['DATABASE']['DB_PASS']}", "host = {$this->CONFIG['DATABASE']['DB_HOST']}"];
        file_put_contents('mysql.cnf', implode(PHP_EOL, $cnf));
        return $this->mysql = './mysql.cnf';
    }

    private function MySQLDump(String $mysqldump = null) : string
    {
        shell_exec(($mysqldump ?: 'mysqldump') . ' --defaults-extra-file="' . $this->buildCNF() . '" --no-data ' . $this->CONFIG['DATABASE']['DB_NAME'] . ' > ./mysqldump.sql');
        return $this->mysqldump = './mysqldump.sql';
    }

    private function MySQLSource(String $query, $mysql = false) : string
    {
        return shell_exec(($mysql ?: 'mysql') . ' --defaults-extra-file="' . $this->buildCNF() . '" ' . $this->CONFIG['DATABASE']['DB_HOST'] . ' < "' . $query . '"');
    }

    public function cleanUp($PHP) : void
    {
        $this->mysql and unlink('./mysql.cnf');
        $this->mysqldump and unlink('./mysqldump.sql');
    }

}