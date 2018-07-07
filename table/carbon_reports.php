<?php

namespace CarbonPHP\Table;

use CarbonPHP\Database;
use CarbonPHP\Entities;
use CarbonPHP\Interfaces\iRest;

class carbon_reports extends Entities implements iRest
{
    const PRIMARY = [
    
    ];

    const COLUMNS = [
    'log_level','report','date','call_trace',
    ];

    const BINARY = [
    
    ];

    /**
     * @param array $return
     * @param string|null $primary
     * @param array $argv
     * @return bool
     */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        if (isset($argv['limit'])){
            if ($argv['limit'] !== '') {
                $pos = strrpos($argv['limit'], "><");
                if ($pos !== false) { // note: three equal signs
                    substr_replace($argv['limit'],',',$pos, 2);
                }
                $limit = ' LIMIT ' . $argv['limit'];
            } else {
                $limit = '';
            }
        } else {
            $limit = ' LIMIT 100';
        }

        $get = isset($argv['select']) ? $argv['select'] : self::COLUMNS;
        $where = isset($argv['where']) ? $argv['where'] : [];

        $sql = '';
        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
            }
            if (in_array($column, self::BINARY)) {
                $sql .= "HEX($column) as $column";
            } else {
                $sql .= $column;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbonphp.carbon_reports';

        $pdo = Database::database();

        if ($primary === null) {
            if (!empty($where)) {
                $build_where = function (array $set, $join = 'AND') use (&$pdo, &$build_where) {
                    $sql = '(';
                    foreach ($set as $column => $value) {
                        if (is_array($value)) {
                            $build_where($value, $join === 'AND' ? 'OR' : 'AND');
                        } else {
                            if (in_array($column, self::BINARY)) {
                                $sql .= "($column = UNHEX(" . $pdo->quote($value) . ")) $join ";
                            } else {
                                $sql .= "($column = " . $pdo->quote($value) . ") $join ";
                            }
                        }
                    }
                    return substr($sql, 0, strlen($sql) - (strlen($join) + 1)) . ')';
                };
                $sql .= ' WHERE ' . $build_where($where);
            }
        } 

        $sql .= $limit;

        $return = self::fetch($sql);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */



        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        $sql = 'INSERT INTO carbonphp.carbon_reports (log_level, report, date, call_trace) VALUES ( :log_level, :report, :date, :call_trace)';
        $stmt = Database::database()->prepare($sql);
            
                $log_level = isset($argv['log_level']) ? $argv['log_level'] : null;
                $stmt->bindParam(':log_level',$log_level, \PDO::PARAM_STR, 20);
                    $stmt->bindValue(':report',$argv['report'], \PDO::PARAM_STR);
                    
                $date = $argv['date'];
                $stmt->bindParam(':date',$date, \PDO::PARAM_STR, 22);
                    $stmt->bindValue(':call_trace',$argv['call_trace'], \PDO::PARAM_STR);
        

        return $stmt->execute();
    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        foreach ($argv as $key => $value) {
            if (!in_array($key, self::COLUMNS)){
                unset($argv[$key]);
            }
        }

        $sql = 'UPDATE carbonphp.carbon_reports ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (isset($argv['log_level'])) {
            $set .= 'log_level=:log_level,';
        }
        if (isset($argv['report'])) {
            $set .= 'report=:report,';
        }
        if (isset($argv['date'])) {
            $set .= 'date=:date,';
        }
        if (isset($argv['call_trace'])) {
            $set .= 'call_trace=:call_trace,';
        }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, strlen($set)-1);

        $db = Database::database();

        

        $stmt = $db->prepare($sql);

        if (isset($argv['log_level'])) {
            $log_level = $argv['log_level'];
            $stmt->bindParam(':log_level',$log_level, \PDO::PARAM_STR, 20);
        }
        if (isset($argv['report'])) {
            $stmt->bindValue(':report',$argv['report'], \PDO::PARAM_STR);
        }
        if (isset($argv['date'])) {
            $date = $argv['date'];
            $stmt->bindParam(':date',$date, \PDO::PARAM_STR, 22);
        }
        if (isset($argv['call_trace'])) {
            $stmt->bindValue(':call_trace',$argv['call_trace'], \PDO::PARAM_STR);
        }

        if (!$stmt->execute()){
            return false;
        }

        $return = array_merge($return, $argv);

        return true;

    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv) : bool
    {
        $sql = 'DELETE FROM carbonphp.carbon_reports ';

        foreach($argv as $column => $constraint){
            if (!in_array($column, self::COLUMNS)){
                unset($argv[$column]);
            }
        }

        if ($primary === null) {
            /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty($argv)) {
                return false;
            }
            $sql .= ' WHERE ';
            foreach ($argv as $column => $value) {
                if (in_array($column, self::BINARY)) {
                    $sql .= " $column =UNHEX(" . Database::database()->quote($value) . ') AND ';
                } else {
                    $sql .= " $column =" . Database::database()->quote($value) . ' AND ';
                }
            }
            $sql = substr($sql, 0, strlen($sql)-4);
        } 

        $remove = null;

        return self::execute($sql);
    }
}