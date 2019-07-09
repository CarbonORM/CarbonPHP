<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class carbon_comments extends Database implements iRest
{

    public const PARENT_ID = 'parent_id';
    public const COMMENT_ID = 'comment_id';
    public const USER_ID = 'user_id';
    public const COMMENT = 'comment';

    public const PRIMARY = [
    'comment_id',
    ];

    public const COLUMNS = [
        'parent_id' => [ 'binary', '2', '16' ],'comment_id' => [ 'binary', '2', '16' ],'user_id' => [ 'binary', '2', '16' ],'comment' => [ 'blob', '2', '' ],
    ];

    public const VALIDATION = [];


    public static $injection = [];


    public static function jsonSQLReporting($argv, $sql) : void {
        global $json;
        if (!\is_array($json)) {
            $json = [];
        }
        if (!isset($json['sql'])) {
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
        $bump = false;
        foreach ($set as $column => $value) {
            if (\is_array($value)) {
                if ($bump) {
                    $sql .= " $join ";
                }
                $bump = true;
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::COLUMNS)) {
                $bump = false;
                if (self::COLUMNS[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo)  . ")) $join ";
                } else {
                    $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
                }
            } else {
                $bump = false;
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
   
   /*
    $bind = function (array $argv) use (&$bind, &$stmt) {
            foreach ($argv as $key => $value) {
                
                if (is_numeric($key) && is_array($value)) {
                    $bind($value);
                    continue;
                }
                
                   if (array_key_exists('parent_id', $argv)) {
            $parent_id = $argv['parent_id'];
            $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        }
                   if (array_key_exists('comment_id', $argv)) {
            $comment_id = $argv['comment_id'];
            $stmt->bindParam(':comment_id',$comment_id, 2, 16);
        }
                   if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
                   if (array_key_exists('comment', $argv)) {
            $stmt->bindValue(':comment',$argv['comment'], 2);
        }
           
          }
        };
        
        $bind($argv); */

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
    */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        self::$injection = [];
        $aggregate = false;
        $group = $sql = '';
        $pdo = self::database();

        $get = $argv['select'] ?? array_keys(self::COLUMNS);
        $where = $argv['where'] ?? [];

        if (array_key_exists('pagination',$argv)) {
            if (!empty($argv['pagination']) && !\is_array($argv['pagination'])) {
                $argv['pagination'] = json_decode($argv['pagination'], true);
            }
            if (array_key_exists('limit',$argv['pagination']) && $argv['pagination']['limit'] !== null) {
                $limit = ' LIMIT ' . $argv['pagination']['limit'];
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (array_key_exists('order',$argv['pagination']) && $argv['pagination']['order'] !== null) {
                    if (\is_array($argv['pagination']['order'])) {
                        foreach ($argv['pagination']['order'] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv['pagination']['order'];
                    }
                } else {
                    $order .= 'comment_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY comment_id ASC LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                if (!empty($group)) {
                    $group .= ', ';
                }
            }
            $columnExists = array_key_exists($column, self::COLUMNS);
            if ($columnExists && self::COLUMNS[$column][0] === 'binary') {
                $sql .= "HEX($column) as $column";
                $group .= $column;
            } elseif ($columnExists) {
                $sql .= $column;
                $group .= $column;
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |parent_id|comment_id|user_id|comment))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_comments';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
        $sql .= ' WHERE  comment_id=UNHEX('.self::addInjection($primary, $pdo).')';
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

        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        
        if ($primary !== null || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1 && \count($return) === 1)) {
            $return = isset($return[0]) && \is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_comments (parent_id, comment_id, user_id, comment) VALUES ( UNHEX(:parent_id), UNHEX(:comment_id), UNHEX(:user_id), :comment)';

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

                
                    $parent_id = $argv['parent_id'];
                    $stmt->bindParam(':parent_id',$parent_id, 2, 16);
                        $comment_id = $id = $argv['comment_id'] ?? self::beginTransaction('carbon_comments');
                $stmt->bindParam(':comment_id',$comment_id, 2, 16);
                
                    $user_id = $argv['user_id'];
                    $stmt->bindParam(':user_id',$user_id, 2, 16);
                        $stmt->bindValue(':comment',$argv['comment'], 2);
        


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
        self::$injection = [];
        if (empty($primary)) {
            return false;
        }

        foreach ($argv as $key => $value) {
            if (!\array_key_exists($key, self::COLUMNS)){
                return false;
            }
        }

        $sql = 'UPDATE carbon_comments ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('parent_id', $argv)) {
                $set .= 'parent_id=UNHEX(:parent_id),';
            }
            if (array_key_exists('comment_id', $argv)) {
                $set .= 'comment_id=UNHEX(:comment_id),';
            }
            if (array_key_exists('user_id', $argv)) {
                $set .= 'user_id=UNHEX(:user_id),';
            }
            if (array_key_exists('comment', $argv)) {
                $set .= 'comment=:comment,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  comment_id=UNHEX('.self::addInjection($primary, $pdo).')';

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

                   if (array_key_exists('parent_id', $argv)) {
            $parent_id = $argv['parent_id'];
            $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        }
                   if (array_key_exists('comment_id', $argv)) {
            $comment_id = $argv['comment_id'];
            $stmt->bindParam(':comment_id',$comment_id, 2, 16);
        }
                   if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
                   if (array_key_exists('comment', $argv)) {
            $stmt->bindValue(':comment',$argv['comment'], 2);
        }

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
        if (null !== $primary) {
            return carbons::Delete($remove, $primary, $argv);
        }

        /**
         *   While useful, we've decided to disallow full
         *   table deletions through the rest api. For the
         *   n00bs and future self, "I got chu."
         */
        if (empty($argv)) {
            return false;
        }

        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_comments on c.entity_pk = follower_table_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo);

        self::jsonSQLReporting(\func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        $r = self::bind($stmt, $argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = null;

        return $r;
    }
}
