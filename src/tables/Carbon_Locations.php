<?php

namespace CarbonPHP\Tables;

use PDO;
use PDOStatement;

use function array_key_exists;
use function count;
use function is_array;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Error\PublicAlert;


class Carbon_Locations extends Rest implements iRest
{
    
    public const TABLE_NAME = 'carbon_locations';
    public const ENTITY_ID = 'carbon_locations.entity_id'; 
    public const LATITUDE = 'carbon_locations.latitude'; 
    public const LONGITUDE = 'carbon_locations.longitude'; 
    public const STREET = 'carbon_locations.street'; 
    public const CITY = 'carbon_locations.city'; 
    public const STATE = 'carbon_locations.state'; 
    public const ELEVATION = 'carbon_locations.elevation'; 
    public const ZIP = 'carbon_locations.zip'; 

    public const PRIMARY = [
        'carbon_locations.entity_id',
    ];

    public const COLUMNS = [
        'carbon_locations.entity_id' => 'entity_id','carbon_locations.latitude' => 'latitude','carbon_locations.longitude' => 'longitude','carbon_locations.street' => 'street','carbon_locations.city' => 'city','carbon_locations.state' => 'state','carbon_locations.elevation' => 'elevation','carbon_locations.zip' => 'zip',
    ];

    public const PDO_VALIDATION = [
        'carbon_locations.entity_id' => ['binary', '2', '16'],'carbon_locations.latitude' => ['varchar', '2', '225'],'carbon_locations.longitude' => ['varchar', '2', '225'],'carbon_locations.street' => ['varchar', '2', '225'],'carbon_locations.city' => ['varchar', '2', '40'],'carbon_locations.state' => ['varchar', '2', '10'],'carbon_locations.elevation' => ['varchar', '2', '40'],'carbon_locations.zip' => ['int', '2', '11'],
    ];
    
    public const VALIDATION = [];

    public static array $injection = [];

    
    
    public static function buildWhere(array $set, PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        $bump = false;
        foreach ($set as $column => $value) {
            if (is_array($value)) {
                if ($bump) {
                    $sql .= " $join ";
                }
                $bump = true;
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::PDO_VALIDATION)) {
                $bump = false;
                /** @noinspection SubStrUsedAsStrPosInspection */
                if (substr($value, 0, '7') === 'C6SUB19') {
                    $subQuery = substr($value, '7');
                    $sql .= "($column = $subQuery ) $join ";
                } else if (self::PDO_VALIDATION[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo) . ")) $join ";
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

    public static function addInjection($value, PDO $pdo, $quote = false): string
    {
        $inject = ':injection' . count(self::$injection) . 'carbon_locations';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(PDOStatement $stmt): void 
    {
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
    public static function Get(array &$return, string $primary = null, array $argv): bool
    {
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, $pdo);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            return false;
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        
        if ($primary !== null || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1 && count($return) === 1)) {
            $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

        return true;
    }

    /**
     * @param array $argv
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_locations (entity_id, latitude, longitude, street, city, state, elevation, zip) VALUES ( UNHEX(:entity_id), :latitude, :longitude, :street, :city, :state, :elevation, :zip)';

        

        $stmt = self::database()->prepare($sql);

    
        $entity_id = $id = $argv['carbon_locations.entity_id'] ?? self::beginTransaction('carbon_locations', $dependant);
        $stmt->bindParam(':entity_id',$entity_id, 2, 16);
    
        $latitude =  $argv['carbon_locations.latitude'] ?? null;
        $stmt->bindParam(':latitude',$latitude, 2, 225);
    
        $longitude =  $argv['carbon_locations.longitude'] ?? null;
        $stmt->bindParam(':longitude',$longitude, 2, 225);
    
        $street =  $argv['carbon_locations.street'] ?? null;
        $stmt->bindParam(':street',$street, 2, 225);
    
        $city =  $argv['carbon_locations.city'] ?? null;
        $stmt->bindParam(':city',$city, 2, 40);
    
        $state =  $argv['carbon_locations.state'] ?? null;
        $stmt->bindParam(':state',$state, 2, 10);
    
        $elevation =  $argv['carbon_locations.elevation'] ?? null;
        $stmt->bindParam(':elevation',$elevation, 2, 40);
    
        $zip =  $argv['carbon_locations.zip'] ?? null;
        $stmt->bindParam(':zip',$zip, 2, 11);
    


        return $stmt->execute() ? $id : false;
    
    }
     
    public static function subSelect(string $primary = null, array $argv, PDO $pdo = null): string
    {
        return 'C6SUB19' . self::buildSelectQuery($primary, $argv, $pdo, true);
    }
    
    public static function validateSelectColumn($column) : bool {
        return (bool) preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|-|/| |carbon_locations\.entity_id|carbon_locations\.latitude|carbon_locations\.longitude|carbon_locations\.street|carbon_locations\.city|carbon_locations\.state|carbon_locations\.elevation|carbon_locations\.zip))+\)*)+ *(as [a-z]+)?#i', $column);
    }
    
    public static function buildSelectQuery(string $primary = null, array $argv, PDO $pdo = null, bool $noHEX = false) : string 
    {
        if ($pdo === null) {
            $pdo = self::database();
        }
        self::$injection = [];
        $aggregate = false;
        $group = [];
        $sql = '';
        $get = $argv['select'] ?? array_keys(self::PDO_VALIDATION);
        $where = $argv['where'] ?? [];

        // pagination
        if (array_key_exists('pagination',$argv)) {
            if (!empty($argv['pagination']) && !is_array($argv['pagination'])) {
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
                    if (is_array($argv['pagination']['order'])) {
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

        // join 
        $join = ''; 
        $tableList = [];
        if (array_key_exists('join', $argv)) {
            foreach ($argv['join'] as $by => $tables) {
                $buildJoin = static function ($method) use ($tables, &$join, &$tableList) {
                    foreach ($tables as $table => $stmt) {
                        $tableList[] = $table;
                        switch (count($stmt)) {
                            case 2: 
                                if (is_string($stmt[0]) && is_string($stmt[1])) {
                                    $join .= $method . $table . ' ON ' . $stmt[0] . '=' . $stmt[1];
                                } else {
                                    return false; // todo debugging
                                }
                                break;
                            case 3:
                                if (is_string($stmt[0]) && is_string($stmt[1]) && is_string($stmt[2])) {
                                    $join .= $method . $table . ' ON ' . $stmt[0] . $stmt[1] . $stmt[2]; 
                                } else {
                                    return false; // todo debugging
                                }
                                break;
                            default:
                                return false; // todo debug check, common when joins are not a list of values
                        }
                    }
                    return true;
                };
                switch ($by) {
                    case 'inner':
                        if (!$buildJoin(' INNER JOIN ')) {
                            return false; 
                        }
                        break;
                    case 'left':
                        if (!$buildJoin(' LEFT JOIN ')) {
                            return false; 
                        }
                        break;
                    case 'right':
                        if (!$buildJoin(' RIGHT JOIN ')) {
                            return false; 
                        }
                        break;
                    default:
                        return false; // todo - debugging stmts
                }
            }
        }

        // Select
        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
            }
            $columnExists = array_key_exists($column, self::PDO_VALIDATION);
            if ($columnExists) {
                if (!$noHEX && self::PDO_VALIDATION[$column][0] === 'binary') {
                    $asShort = trim($column, self::TABLE_NAME . '.');
                    $prefix = self::TABLE_NAME . '.';
                    if (strpos($column, $prefix) === 0) {
                        $asShort = substr($column, strlen($prefix));
                    }
                    $sql .= "HEX($column) as $asShort";
                    $group[] = $column;
                } elseif ($columnExists) {
                    $sql .= $column;
                    $group[] = $column;  
                }  
            } else if (self::validateSelectColumn($column)) {
                $sql .= $column;
                $group[] = $column;
                $aggregate = true;
            } else {  
                $valid = false;
                $tablesReffrenced = $tableList;
                while (!empty($tablesReffrenced)) {
                     $table = __NAMESPACE__ . '\\' . array_pop($tablesReffrenced);
                     
                     if (!class_exists($table)){
                         continue;
                     }
                     $imp = array_map('strtolower', array_keys(class_implements($table)));
                    
                   
                     /** @noinspection ClassConstantUsageCorrectnessInspection */
                     if (!in_array(strtolower(iRest::class), $imp, true) && 
                         !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                         continue;
                     }
                     /** @noinspection PhpUndefinedMethodInspection */
                     if ($table::validateSelectColumn($column)) { 
                        $group[] = $column;
                        $valid = true;
                        break; 
                     }
                }
                if (!$valid) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_locations ' . $join;
       
        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
            $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . implode(', ', $group). ' ';
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
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                return false;
            }
        }

        $sql = 'UPDATE carbon_locations ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

        if (array_key_exists('carbon_locations.entity_id', $argv)) {
            $set .= 'entity_id=UNHEX(:entity_id),';
        }
        if (array_key_exists('carbon_locations.latitude', $argv)) {
            $set .= 'latitude=:latitude,';
        }
        if (array_key_exists('carbon_locations.longitude', $argv)) {
            $set .= 'longitude=:longitude,';
        }
        if (array_key_exists('carbon_locations.street', $argv)) {
            $set .= 'street=:street,';
        }
        if (array_key_exists('carbon_locations.city', $argv)) {
            $set .= 'city=:city,';
        }
        if (array_key_exists('carbon_locations.state', $argv)) {
            $set .= 'state=:state,';
        }
        if (array_key_exists('carbon_locations.elevation', $argv)) {
            $set .= 'elevation=:elevation,';
        }
        if (array_key_exists('carbon_locations.zip', $argv)) {
            $set .= 'zip=:zip,';
        }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  entity_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_locations.entity_id', $argv)) {
            $entity_id = $argv['carbon_locations.entity_id'];
            $stmt->bindParam(':entity_id',$entity_id, 2, 16);
        }
        if (array_key_exists('carbon_locations.latitude', $argv)) {
            $latitude = $argv['carbon_locations.latitude'];
            $stmt->bindParam(':latitude',$latitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.longitude', $argv)) {
            $longitude = $argv['carbon_locations.longitude'];
            $stmt->bindParam(':longitude',$longitude, 2, 225);
        }
        if (array_key_exists('carbon_locations.street', $argv)) {
            $street = $argv['carbon_locations.street'];
            $stmt->bindParam(':street',$street, 2, 225);
        }
        if (array_key_exists('carbon_locations.city', $argv)) {
            $city = $argv['carbon_locations.city'];
            $stmt->bindParam(':city',$city, 2, 40);
        }
        if (array_key_exists('carbon_locations.state', $argv)) {
            $state = $argv['carbon_locations.state'];
            $stmt->bindParam(':state',$state, 2, 10);
        }
        if (array_key_exists('carbon_locations.elevation', $argv)) {
            $elevation = $argv['carbon_locations.elevation'];
            $stmt->bindParam(':elevation',$elevation, 2, 40);
        }
        if (array_key_exists('carbon_locations.zip', $argv)) {
            $zip = $argv['carbon_locations.zip'];
            $stmt->bindParam(':zip',$zip, 2, 11);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            return false;
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_locations.', '', $k); },
                array_keys($argv)
            ),
            array_values($argv)
        );

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
            return Carbons::Delete($remove, $primary, $argv);
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
        /** @noinspection SqlWithoutWhere */
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_locations on c.entity_pk = carbon_locations.entity_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
}
