<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\interfaces\iRest;


class carbon_locations extends Database implements iRest
{
    public const PRIMARY = [
    'entity_id',
    ];

    public const COLUMNS = [
        'entity_id' => [ 'binary', '2', '16' ],'latitude' => [ 'varchar', '2', '225' ],'longitude' => [ 'varchar', '2', '225' ],'street' => [ 'text,', '2', '' ],'city' => [ 'varchar', '2', '40' ],'state' => [ 'varchar', '2', '10' ],'elevation' => [ 'varchar', '2', '40' ],
    ];

    public const VALIDATION = [];


    public static $injection = [];



    public static function buildWhere(array $set, \PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        foreach ($set as $column => $value) {
            if (\is_array($value)) {
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::COLUMNS)) {
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
        if (array_key_exists('entity_id', $argv)) {
            $entity_id = $argv['entity_id'];
            $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        }
        if (array_key_exists('latitude', $argv)) {
            $latitude = $argv['latitude'];
            $stmt->bindParam(':latitude',$latitude, 2, 225);
        }
        if (array_key_exists('longitude', $argv)) {
            $longitude = $argv['longitude'];
            $stmt->bindParam(':longitude',$longitude, 2, 225);
        }
        if (array_key_exists('street', $argv)) {
            $stmt->bindValue(':street',$argv['street'], 2);
        }
        if (array_key_exists('city', $argv)) {
            $city = $argv['city'];
            $stmt->bindParam(':city',$city, 2, 40);
        }
        if (array_key_exists('state', $argv)) {
            $state = $argv['state'];
            $stmt->bindParam(':state',$state, 2, 10);
        }
        if (array_key_exists('elevation', $argv)) {
            $elevation = $argv['elevation'];
            $stmt->bindParam(':elevation',$elevation, 2, 40);
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
                    $order .= 'entity_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY entity_id ASC LIMIT 100';
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
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |entity_id|latitude|longitude|street|city|state|elevation))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_locations';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
        $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        

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
        $sql = 'INSERT INTO carbon_locations (entity_id, latitude, longitude, street, city, state, elevation) VALUES ( UNHEX(:entity_id), :latitude, :longitude, :street, :city, :state, :elevation)';

        

        $stmt = self::database()->prepare($sql);

                $entity_id = $id = $argv['entity_id'] ?? self::beginTransaction('carbon_locations');
                $stmt->bindParam(':entity_id',$entity_id, 2, 16);
                
                    $latitude =  $argv['latitude'] ?? null;
                    $stmt->bindParam(':latitude',$latitude, 2, 225);
                        
                    $longitude =  $argv['longitude'] ?? null;
                    $stmt->bindParam(':longitude',$longitude, 2, 225);
                        $stmt->bindValue(':street',$argv['street'], 2);
                        
                    $city =  $argv['city'] ?? null;
                    $stmt->bindParam(':city',$city, 2, 40);
                        
                    $state =  $argv['state'] ?? null;
                    $stmt->bindParam(':state',$state, 2, 10);
                        
                    $elevation =  $argv['elevation'] ?? null;
                    $stmt->bindParam(':elevation',$elevation, 2, 40);
        


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

        $sql = 'UPDATE carbon_locations ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('entity_id', $argv)) {
                $set .= 'entity_id=UNHEX(:entity_id),';
            }
            if (array_key_exists('latitude', $argv)) {
                $set .= 'latitude=:latitude,';
            }
            if (array_key_exists('longitude', $argv)) {
                $set .= 'longitude=:longitude,';
            }
            if (array_key_exists('street', $argv)) {
                $set .= 'street=:street,';
            }
            if (array_key_exists('city', $argv)) {
                $set .= 'city=:city,';
            }
            if (array_key_exists('state', $argv)) {
                $set .= 'state=:state,';
            }
            if (array_key_exists('elevation', $argv)) {
                $set .= 'elevation=:elevation,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';

        

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
        return carbons::Delete($remove, $primary, $argv);
    }
}
