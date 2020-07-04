<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class Carbon_Photos extends Database implements iRest
{

    public const PARENT_ID = 'carbon_photos.parent_id';
    public const PHOTO_ID = 'carbon_photos.photo_id';
    public const USER_ID = 'carbon_photos.user_id';
    public const PHOTO_PATH = 'carbon_photos.photo_path';
    public const PHOTO_DESCRIPTION = 'carbon_photos.photo_description';

    public const PRIMARY = [
    'parent_id',
    ];

    public const COLUMNS = [
        'carbon_photos.parent_id' => [ 'binary', '2', '16' ],'carbon_photos.photo_id' => [ 'binary', '2', '16' ],'carbon_photos.user_id' => [ 'binary', '2', '16' ],'carbon_photos.photo_path' => [ 'varchar', '2', '225' ],'carbon_photos.photo_description' => [ 'text,', '2', '' ],
    ];

    public const VALIDATION = [];


    public static array $injection = [];



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
                if ($column !== $subQuery = trim('C6SUB488', $column)) {
                    $sql .= "($column = $subQuery ) $join ";
                } else if (self::COLUMNS[$column][0] === 'binary') {
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
        $inject = ':injection' . \count(self::$injection) . 'carbon_photos';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(\PDOStatement $stmt, array $argv) : void {
        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key,$value);
        }
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
        $pdo = self::database();

        $sql = self::buildSelect($primary, $argv, $pdo);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt, $argv['where'] ?? []);

        if (!$stmt->execute()) {
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
        $sql = 'INSERT INTO carbon_photos (parent_id, photo_id, user_id, photo_path, photo_description) VALUES ( UNHEX(:parent_id), UNHEX(:photo_id), UNHEX(:user_id), :photo_path, :photo_description)';

        

        $stmt = self::database()->prepare($sql);

                $parent_id = $id = $argv['parent_id'] ?? self::beginTransaction('carbon_photos');
                $stmt->bindParam(':parent_id',$parent_id, 2, 16);
                
                    $photo_id = $argv['photo_id'];
                    $stmt->bindParam(':photo_id',$photo_id, 2, 16);
                        
                    $user_id = $argv['user_id'];
                    $stmt->bindParam(':user_id',$user_id, 2, 16);
                        
                    $photo_path = $argv['photo_path'];
                    $stmt->bindParam(':photo_path',$photo_path, 2, 225);
                        $stmt->bindValue(':photo_description',$argv['photo_description'], 2);
        


        return $stmt->execute() ? $id : false;

    }
     
    public static function subSelect(string $primary = null, array $argv, \PDO $pdo = null): string
    {
        return 'C6SUB488' . self::buildSelect($primary, $argv, $pdo, true);
    }
    
    public static function buildSelect(string $primary = null, array $argv, \PDO $pdo = null, bool $noHEX = false) : string 
    {
        if ($pdo === null) {
            $pdo = self::database();
        }
        self::$injection = [];
        $aggregate = false;
        $group = $sql = '';
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
                    $order .= 'parent_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY parent_id ASC LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                if (!empty($group)) {
                    $group .= ', ';
                }
            }
            $columnExists = array_key_exists($column, self::COLUMNS);
            if ($columnExists) {
                if (!$noHEX && self::COLUMNS[$column][0] === 'binary') {
                    $sql .= "HEX($column) as $column";
                    $group .= $column;
                } elseif ($columnExists) {
                    $sql .= $column;
                    $group .= $column;  
                }  
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |parent_id|photo_id|user_id|photo_path|photo_description))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_photos';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
            $sql .= ' WHERE  parent_id=UNHEX('.self::addInjection($primary, $pdo).')';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        

        return '(' . $sql . ')';
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

        $sql = 'UPDATE carbon_photos ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('parent_id', $argv)) {
                $set .= 'parent_id=UNHEX(:parent_id),';
            }
            if (array_key_exists('photo_id', $argv)) {
                $set .= 'photo_id=UNHEX(:photo_id),';
            }
            if (array_key_exists('user_id', $argv)) {
                $set .= 'user_id=UNHEX(:user_id),';
            }
            if (array_key_exists('photo_path', $argv)) {
                $set .= 'photo_path=:photo_path,';
            }
            if (array_key_exists('photo_description', $argv)) {
                $set .= 'photo_description=:photo_description,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  parent_id=UNHEX('.self::addInjection($primary, $pdo).')';

        

        $stmt = $pdo->prepare($sql);

                   if (array_key_exists('parent_id', $argv)) {
            $parent_id = $argv['parent_id'];
            $stmt->bindParam(':parent_id',$parent_id, 2, 16);
        }
                   if (array_key_exists('photo_id', $argv)) {
            $photo_id = $argv['photo_id'];
            $stmt->bindParam(':photo_id',$photo_id, 2, 16);
        }
                   if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
                   if (array_key_exists('photo_path', $argv)) {
            $photo_path = $argv['photo_path'];
            $stmt->bindParam(':photo_path',$photo_path, 2, 225);
        }
                   if (array_key_exists('photo_description', $argv)) {
            $stmt->bindValue(':photo_description',$argv['photo_description'], 2);
        }

        self::bind($stmt, $argv);

        if (!$stmt->execute()) {
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
                JOIN carbon_photos on c.entity_pk = follower_table_id';

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
