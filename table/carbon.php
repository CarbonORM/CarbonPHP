<?php
namespace Table;


use CarbonPHP\Entities;
use CarbonPHP\Interfaces\iRest;
use Psr\Log\InvalidArgumentException;


class carbon extends Entities implements iRest
{
    public const PRIMARY = [
    'entity_pk',
    ];

    public const COLUMNS = [
        'entity_pk' => [ 'binary', '2', '16' ],'entity_fk' => [ 'binary', '2', '16' ],
    ];

    public const VALIDATION = [];


    public static $injection = [];


    public static function jsonSQLReporting($argv, $sql) : void {
        global $json;
        if (!\is_array($json)) {
            $json = [];
        } elseif (!isset($json['sql'])) {
            $json['sql'] = [];
        }
        $json['sql'][] = [
            $argv,
            $sql
        ];
    }

    public static function buildWhere(array $set, \PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        foreach ($set as $column => $value) {
            if (\is_array($value)) {
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (isset(self::COLUMNS[$column])) {
                if (self::COLUMNS[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(:" . $column . ")) $join ";
                } else {
                    $sql .= "($column = :" . $column . ") $join ";
                }
            } else {
                $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
            }

        }
        return rtrim($sql, " $join") . ')';
    }

    public static function addInjection($value, \PDO $pdo, $quote = false) : string
    {
        $inject = ':injection' . \count(self::$injection) . 'buildWhere';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(\PDOStatement $stmt, array $argv) {
        if (!empty($argv['entity_pk'])) {
            $entity_pk = $argv['entity_pk'];
            $stmt->bindParam(':entity_pk',$entity_pk, 2, 16);
        }
        if (!empty($argv['entity_fk'])) {
            $entity_fk = $argv['entity_fk'];
            $stmt->bindParam(':entity_fk',$entity_fk, 2, 16);
        }

        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key,$value);
        }

        return $stmt->execute();
    }


    /**
    *
    *   $argv = [
    *       'select' => [
    *                          '*column name array*', 'etc..'
    *        ],
    *
    *       'where' => [
    *              'Column Name' => 'Value To Constrain',
    *              'Defaults to AND' => 'Nesting array switches to OR',
    *              [
    *                  'Column Name' => 'Value To Constrain',
    *                  'This array is OR'ed togeather' => 'Another sud array would `AND`'
    *                  [ etc... ]
    *              ]
    *        ],
    *
    *        'pagination' => [
    *              'limit' => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *              'order' => '*column name* [ASC|DESC]',  // i.e.  'username ASC' or 'username, email DESC'
    *
    *
    *         ],
    *
    *   ];
    *
    *
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @return bool
    * @throws \Exception
    */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        $aggregate = false;
        $group = $sql = '';
        $pdo = self::database();

        $get = $argv['select'] ?? array_keys(self::COLUMNS);
        $where = $argv['where'] ?? [];

        if (isset($argv['pagination'])) {
            if (!empty($argv['pagination']) && !\is_array($argv['pagination'])) {
                $argv['pagination'] = json_decode($argv['pagination'], true);
            }
            if (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] !== null) {
                $limit = ' LIMIT ' . $argv['pagination']['limit'];
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (isset($argv['pagination']['order']) && $argv['pagination']['order'] !== null) {
                    if (\is_array($argv['pagination']['order'])) {
                        foreach ($argv['pagination']['order'] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv['pagination']['order'];
                    }
                } else {
                    $order .= 'entity_pk ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY entity_pk ASC LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                if (!empty($group)) {
                    $group .= ', ';
                }
            }
            $columnExists = isset(self::COLUMNS[$column]);
            if ($columnExists && self::COLUMNS[$column][0] === 'binary') {
                $sql .= "HEX($column) as $column";
                $group .= $column;
            } elseif ($columnExists) {
                $sql .= $column;
                $group .= $column;
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |entity_pk|entity_fk))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    /** @noinspection PhpUndefinedClassInspection */
                    throw new InvalidArgumentException('Arguments passed in SELECT failed the REGEX test!');
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM CarbonPHP.carbon';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
        $sql .= ' WHERE  entity_pk=UNHEX(".self::addInjection($primary, $pdo).")';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (!self::bind($stmt, $argv['where'] ?? [])) {
            return false;
        }

        $return = $stmt->fetchAll();

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        
            if (!empty($primary) || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1)) {
            $return = (\count($return) === 1 ?
            (\is_array($return['0']) ? $return['0'] : $return) : $return);   // promise this is needed and will still return the desired array except for a single record will not be an array
            }

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
    /** @noinspection SqlResolve */
    $sql = 'INSERT INTO CarbonPHP.carbon (entity_pk, entity_fk) VALUES ( UNHEX(:entity_pk), UNHEX(:entity_fk))';

    self::jsonSQLReporting(\func_get_args(), $sql);

    $stmt = self::database()->prepare($sql);

                $entity_pk = $id = $argv['entity_pk'] ?? self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];
                $stmt->bindParam(':entity_pk',$entity_pk, 2, 16);
                
                    $entity_fk =  $argv['entity_fk'] ?? null;
                    $stmt->bindParam(':entity_fk',$entity_fk, 2, 16);
        


        return $stmt->execute() ? $id : false;

    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        if (empty($primary)) {
            return false;
        }

        foreach ($argv as $key => $value) {
            if (!\in_array($key, self::COLUMNS, true)){
                unset($argv[$key]);
            }
        }

        $sql = 'UPDATE CarbonPHP.carbon ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (!empty($argv['entity_pk'])) {
                $set .= 'entity_pk=UNHEX(:entity_pk),';
            }
            if (!empty($argv['entity_fk'])) {
                $set .= 'entity_fk=UNHEX(:entity_fk),';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_pk=UNHEX(".self::addInjection($primary, $pdo).")';

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        if (!self::bind($stmt, $argv)){
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
        /** @noinspection SqlResolve */
        $sql = 'DELETE FROM CarbonPHP.carbon ';

        $pdo = self::database();

        if (null === $primary) {
        /**
        *   While useful, we've decided to disallow full
        *   table deletions through the rest api. For the
        *   n00bs and future self, "I got chu."
        */
        if (empty($argv)) {
            return false;
        }


        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo);
        } else {
        $sql .= ' WHERE  entity_pk=UNHEX(".self::addInjection($primary, $pdo).")';
        }

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        $r = self::bind($stmt, $argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = null;

        return $r;
    }
}