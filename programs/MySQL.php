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
    private /** @noinspection SpellCheckingInspection */
        $mysqldump;


    public function __construct($CONFIG)
    {
        $this->CONFIG = $CONFIG;
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

        if (empty($this->CONFIG['DATABASE']['DB_PORT'])) {
            print 'No [\'DATABASE\'][\'DB_HOST\'] configuration active. Using default port 3306. ';
            $this->CONFIG['DATABASE']['DB_PORT'] = 3306;
            exit(1);
        }

        // We're going to use this function to execute mysql from the command line
        // Mysql needs this to access the server
        $cnf = ['[client]', "user = {$this->CONFIG['DATABASE']['DB_USER']}", "password = {$this->CONFIG['DATABASE']['DB_PASS']}", "host = {$this->CONFIG['DATABASE']['DB_HOST']}", "port = {$this->CONFIG['DATABASE']['DB_PORT']}"];
        file_put_contents('mysql.cnf', implode(PHP_EOL, $cnf));
        return $this->mysql = './mysql.cnf';
    }

    private function MySQLDump(String $mysqldump = null) : string
    {
        shell_exec(($mysqldump ?: 'mysqldump') . ' --defaults-extra-file="' . $this->buildCNF() . '" --no-data ' . $this->CONFIG['DATABASE']['DB_NAME'] . ' > ./mysqldump.sql');
        return $this->mysqldump = './mysqldump.sql';
    }

    private function MySQLSource(bool $verbose, String $query, $mysql = false)
    {
        $cmd = ($mysql ?: 'mysql') . ' --defaults-extra-file="' . $this->buildCNF() . '" ' . $this->CONFIG['DATABASE']['DB_NAME'] . ' < "' . $query . '"';

        $verbose and print "\n\nRunning Command >> $cmd\n\n";

        shell_exec($cmd);
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function cleanUp() : void
    {
        $this->mysql and unlink('./mysql.cnf');
        $this->mysqldump and unlink('./mysqldump.sql');
    }

}