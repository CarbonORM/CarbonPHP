<?php


namespace CarbonPHP;


use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use PDO;
use PDOStatement;

abstract class Rest extends Database
{

    #restful identifiers
    public const SELECT = 'select';
    public const UPDATE = 'update';
    public const WHERE = 'where';
    public const JOIN = 'join';
    public const INNER = 'inner';
    public const LEFT = 'left';
    public const RIGHT = 'right';
    public const DISTINCT = 'distinct';
    public const COUNT = 'count';
    public const SUM = 'sum';
    public const MIN = 'min';
    public const MAX = 'max';
    public const GROUP_CONCAT = 'GROUP_CONCAT';

    #SQL helpful constants
    public const DESC = ' DESC'; // not case sensitive but helpful for reporting to remain uppercase
    public const ASC = ' ASC';

    #PAGINATION properties
    public const PAGINATION = 'pagination';
    public const ORDER = 'order';
    public const LIMIT = 'limit';
    public const PAGE = 'page';

    #php validation methods
    public const GET = 'GET';   // this is case sensitive dont touch
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';

    #regex validation method
    public const VALIDATE_C6_ENTITY_ID_REGEX = '#^' . Route::MATCH_C6_ENTITY_ID_REGEX . '$#';

    public const DISALLOW_PUBLIC_ACCESS = [self::class => 'disallowPublicAccess'];

    public static array $injection = [];

    /**
     * @throws PublicAlert
     */
    public function disallowPublicAccess() : void {
        throw new PublicAlert('Rest request denied by PHP_VALIDATION\'s in the tables ORM. Remove DISALLOW_PUBLIC_ACCESS to gain privileges.');
    }

    /**
     * @param $column
     * @param $tableList
     * @return string|null
     */
    public static function validateColumnName($column, $tableList): ?string
    {
        if (array_key_exists($column, static::PDO_VALIDATION)) {
            return static::class;
        }

        // I like the while loop bc it shrinks the array with ever iteration
        while (!empty($tableList)) {             // todo - this should work on all the columns with each new table reff
            $table = array_pop($tableList);

            if (array_key_exists($column, $table::PDO_VALIDATION) ||
                in_array($column, $table::COLUMNS, true)) {      // allow short tags
                return $table;
            }
        }
        return null;
    }


    /**
     * @param $argv
     * @param $sql
     */
    public static function jsonSQLReporting($argv, $sql): void
    {
        global $json;
        if (!is_array($json)) {
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


    /**
     * @param Route $route
     * @param string $prefix
     * @return Route
     * @throws PublicAlert
     */

    public static function MatchRestfulRequests(Route $route, string $prefix = ''): Route
    {
        return $route->regexMatch(/** @lang RegExp */ '#' . $prefix . 'rest/([A-Za-z\_]{1,256})/?' . Route::MATCH_C6_ENTITY_ID_REGEX . '?#',
            static function (string $table, string $primary = null) {
                Rest::RestfulRequests($table, $primary);
                return true;
            });
    }

    /**
     * @param string $method
     * @param array $args
     * @param array $where
     * @param array $table_php_validation
     * @throws PublicAlert
     */
    public static function validateRestfulRequestWithCustomMethods(string $method, array &$args, array $where, array $table_php_validation): void
    {
        foreach ($table_php_validation as $php_validation) {
            if (empty($php_validation)) {
                continue;
            }

            // these function maps are designed to run on every request
            if (array_key_exists(0, $php_validation)) {
                if (is_array($php_validation[0])) {
                    foreach ($php_validation[0] as $validation) {
                        if (is_array($validation)) {
                            $class = array_key_first($validation);
                            $validationMethod = $validation[$class];
                            unset($validation[$class]);
                            if (!class_exists($class)) {
                                throw new PublicAlert("A class reference in PHP_VALIDATION failed. Class ($class) not found.");
                            }
                            if (false === call_user_func_array([$class, $validationMethod], [&$args, ...$validation])) {
                                throw new PublicAlert('The global request validation failed, please make sure arguments are correct.');
                            }
                        } else {
                            throw new PublicAlert('PHP_VALIDATION[0][] should equal = arrays with [ call => method , structure followed by any additional arguments ]. Refer to Carbonphp.com for more info.');
                        }
                    }
                } else {
                    throw new PublicAlert('The first numeric key 0 in PHP_VALIDATION must be an array. Refer to Carbonphp.com for more info.');
                }
            }

            // these function maps are designed to run on specific $method request
            if (array_key_exists($method, $php_validation) && is_array($php_validation[$method]) && array_key_exists(0, $php_validation[$method])) {
                foreach ($php_validation[$method][0] as $validation) {
                    if (is_array($validation)) {

                        $class = array_key_first($validation);
                        $validationMethod = $validation[$class];
                        unset($validation[$class]);

                        if (!class_exists($class)) {
                            throw new PublicAlert('A class reference in PHP_VALIDATION failed. Make sure arrow \'=>\' is use and not a comma \',\'.');
                        }
                        if (false === call_user_func_array([$class, $validationMethod], [&$args, ...$validation])) {
                            throw new PublicAlert('The request failed, please make sure arguments are correct for this method.');
                        }
                    } else {
                        throw new PublicAlert('The first numeric key for PHP_VALIDATION["'.$method.'"][0][] should equal = arrays with [ call => method , structure followed by any additional arguments ]. Refer to Carbonphp.com for more info.');
                    }
                }
            }

            // these function maps are designed column values
            if (is_array($where) && !empty($where)) {
                foreach ($where as $column => &$value) {

                    // this maybe for every method (GET,PUT,POST,DELETE)
                    if (array_key_exists($column, $php_validation)) {
                        foreach ($php_validation[$column] as $validation) {
                            $class = array_key_first($validation);
                            $validationMethod = $validation[$class];
                            unset($validation[$class]);
                            if (!class_exists($class)) {
                                throw new PublicAlert('A class reference in PHP_VALIDATION[$column] failed. Make sure an arrow \'=>\' is use and not a comma \',\' for class method pair.');
                            }
                            if (false === call_user_func_array([$class, $validationMethod], [&$value, ...$validation])) {
                                throw new PublicAlert('Restful PHP validation returned false for column (' . $column . '). The request failed, please make sure arguments are correct.');
                            }
                        }
                    }

                    // Or specific methods
                    if (array_key_exists($column, $php_validation[$method] ?? [])) {
                        foreach ($php_validation[$method][$column] as $validation) {
                            $class = array_key_first($validation);
                            $validationMethod = $validation[$class];
                            unset($validation[$class]);
                            if (!class_exists($class)) {
                                throw new PublicAlert('A class reference in PHP_VALIDATION[$method][$column] failed. Make sure arrow \'=>\' is use and not a comma \',\' for class method  pair..');
                            }
                            if (false === call_user_func([$class, $validationMethod], [&$value, ...$validation])) {
                                throw new PublicAlert('The request failed, please make sure arguments are correct.');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $where
     * @param array $regex
     * @throws PublicAlert
     */
    public static function validateRestfulRequestWithCustomRegexps(array $where, array $regex): void
    {
        if (is_array($where) && !empty($where)) {
            foreach ($where as $column => &$value) {
                if (array_key_exists($column, $regex) &&
                    preg_match_all($regex[$column], $value, $matches, PREG_SET_ORDER) < 1) {  // can return 0 or false
                    throw new PublicAlert('The request failed the column (' . $column . ') regex (' . $regex[$column] . ') test, please make sure arguments are correct.');
                }
            }
        }
    }


    /**
     * @param string $method
     * @param array $args
     * @param array $regex_validation
     * @param array $php_validation
     * @return void
     * @throws PublicAlert
     */
    public static function validateRestfulArguments(string $method, array &$args, array $regex_validation, array $php_validation): void
    {
        if ($method === self::POST) {
            $where = &$args;
        } else {
            $args[self::WHERE] ??= [];
            $where = &$args[self::WHERE];
        }

        empty($where) or self::validateRestfulRequestWithCustomRegexps($where, $regex_validation);

        self::validateRestfulRequestWithCustomMethods($method, $args, $where, $php_validation);

    }


    /**
     * @param string $mainTable
     * @param string $namespace
     * @param array $requestBody
     * @return array
     * @throws PublicAlert
     */
    public static function gatherValidationsForRequest(string &$mainTable, string $namespace, array $requestBody): array
    {
        $tables = [&$mainTable];

        if (array_key_exists(self::JOIN, $requestBody)) {
            foreach ($requestBody[self::JOIN] as $key => $value) {
                if (!empty($requestBody[self::JOIN][$key])) {
                    $tables = [...$tables, ...array_keys($requestBody[self::JOIN][$key])];
                }
            }
        }

        $php_validations = $regex_validations = [];

        foreach ($tables as &$table) {

            $table = explode('_', $table);      // table name semantics vs class name

            $table = array_map('ucfirst', $table);

            $table = implode('_', $table);

            if (!class_exists($table = $namespace . $table)) {
                throw new PublicAlert("Failed to find the table ($table) requested");
            }

            if (!is_subclass_of($table, self::class)) {
                throw new PublicAlert('The table must extent :: ' . self::class);
            }

            $imp = array_map('strtolower', array_keys(class_implements($table)));

            if (!in_array(strtolower(iRest::class), $imp, true) &&
                !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                throw new PublicAlert('The table does not implement the correct interface. Requires (' . iRest::class . ' or ' . iRestfulReferences::class . ').');
            }

            if (defined("$table::REGEX_VALIDATION")) {
                $regex_validations[] = constant("$table::REGEX_VALIDATION");
            } else {
                throw new PublicAlert('The table does not implement REGEX_VALIDATION. This can be an empty static array.');
            }

            if (defined("$table::PHP_VALIDATION")) {
                $php_validations[] = constant("$table::PHP_VALIDATION");
            } else {
                throw new PublicAlert('The table does not implement PHP_VALIDATION. This can be an empty static array.');
            }
        }

        unset($table);

        return [
            array_merge([], ...$regex_validations),
            $php_validations
        ];
    }


    /**
     * @param string $mainTable
     * @param string|null $primary
     * @param string $namespace
     * @return bool
     */
    public static function RestfulRequests(string $mainTable, string $primary = null, string $namespace = 'Tables\\'): bool
    {
        global $json;

        $json = [];

        try {
            if (CarbonPHP::$app_root . 'src' . DS === CarbonPHP::CARBON_ROOT) {
                $namespace = 'CarbonPHP\\Tables\\';
            }

            $mainTable = explode('_', $mainTable);      // table name semantics vs class name

            $mainTable = array_map('ucfirst', $mainTable);

            $mainTable = implode('_', $mainTable);

            $requestTableHasPrimary = in_array(strtolower(iRest::class),
                array_map('strtolower', array_keys(class_implements($namespace . $mainTable))), true);

            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            switch ($method) {
                case self::GET:
                    if (array_key_exists(0, $_GET)) {
                        $_GET = json_decode($_GET[0], true);    // which is why this is here
                        if (null === $_GET) {
                            $_GET = [];
                        }
                    } else {
                        array_key_exists(self::SELECT, $_GET) and $_GET[self::SELECT] = json_decode($_GET[self::SELECT], true);
                        array_key_exists(self::JOIN, $_GET) and $_GET[self::JOIN] = json_decode($_GET[self::JOIN], true);
                        array_key_exists(self::WHERE, $_GET) and $_GET[self::WHERE] = json_decode($_GET[self::WHERE], true);
                        array_key_exists(self::PAGINATION, $_GET) and $_GET[self::PAGINATION] = json_decode($_GET[self::PAGINATION], true);
                    }
                    $args = $_GET;
                    break;
                case self::PUT:
                    if ($primary === null) {
                        throw new PublicAlert('Updating restful records requires a primary key.');
                    }
                case self::POST:
                case self::DELETE:
                    $args = $_POST;
                    break;
                default:
                    throw new PublicAlert('The REQUEST_METHOD is not RESTFUL. Method must be either \'POST\', \'PUT\', \'GET\', or \'DELETE\'.');
            }

            [$regex_validations, $php_validations] = self::gatherValidationsForRequest($mainTable, $namespace, $args);


            switch ($method) {
                case self::PUT:
                case self::DELETE:
                case self::GET:
                    empty($args) or self::validateRestfulArguments($method, $args, $regex_validations, $php_validations);

                    $methodCase = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));  // this is to match actual method spelling

                    $return = [];
                    if (!call_user_func_array([$mainTable, $methodCase], $requestTableHasPrimary ? [&$return, $primary, $args] : [&$return, $args])) {
                        throw new PublicAlert('The request failed, please make sure arguments are correct.');
                    }

                    $json['rest'] = $return;

                    if ($method === self::PUT) {
                        $json['rest']['updated'] = $primary ?? $args;
                    } elseif ($method === self::DELETE) {
                        $json['rest']['deleted'] = $primary ?? $args;
                    }

                    break;

                case self::POST:

                    empty($_POST) or self::validateRestfulArguments($method, $_POST, $regex_validations, $php_validations);

                    $methodCase = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));  // this is to match actual method spelling

                    if (!$id = call_user_func([$mainTable, $methodCase], $_POST, $primary)) {
                        throw new PublicAlert('The request failed, please make sure arguments are correct.');
                    }

                    if (!self::commit()) {
                        throw new PublicAlert('Failed to commit the transaction. Please, try again.');
                    }

                    $json['rest'] = ['created' => $id];

                    break;
            }
        } catch (PublicAlert $e) {
            http_response_code(400);
        } finally {
            headers_sent() or header('Content-Type: application/json');
            print PHP_EOL . json_encode($json) . PHP_EOL;
        }
        return true;
    }

    // validating across joins for rest is hard enough. I'm not going to allow user/FED provided sub queries
    public static bool $allowSubSelectQueries = false; // todo - maybe C6v8.0.0 maybe?


    /**
     * @param string|null $primary
     * @param array $argv
     * @param string $database
     * @param PDO|null $pdo
     * @param bool $noHEX
     * @return string
     * @throws PublicAlert
     * @noinspection PhpUndefinedFieldInspection
     */
    public static function buildSelectQuery(string $primary = null, array $argv, string $database = '', PDO $pdo = null, bool $noHEX = false): string
    {
        if ($pdo === null) {
            $pdo = self::database();
        }

        $aggregate = false;
        $tableClassList = [];
        $joinColumns = [];
        $group = [];
        $join = '';
        $sql = '';

        $get = $argv[self::SELECT] ?? array_keys(static::PDO_VALIDATION);

        $where = $argv[self::WHERE] ?? [];


        // CARBON_ROOT === CarbonPHP::$app_root is also $database = '' in this context, but cheers to clarity!
        $tablePrefix = CarbonPHP::CARBON_ROOT === CarbonPHP::$app_root . 'src' . DS ? 'CarbonPHP\\Tables\\' : 'Tables\\';

        // build join
        if (array_key_exists(self::JOIN, $argv) && !empty($argv[self::JOIN])) {
            if (!is_array($argv[self::JOIN])) {
                throw new PublicAlert('The restful join field must be an array.');
            }
            foreach ($argv[self::JOIN] as $by => $tables) {
                $buildJoin = static function ($method) use ($tablePrefix, $tables, &$join, &$tableClassList, &$joinColumns) {
                    foreach ($tables as $table => $stmt) {
                        $tableClassList[] = $JoiningClass = $tablePrefix . ucwords($table, '_');

                        if (!class_exists($JoiningClass)) {
                            throw new PublicAlert('A table provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
                        }

                        $imp = array_map('strtolower', array_keys(class_implements($JoiningClass)));

                        /** @noinspection ClassConstantUsageCorrectnessInspection */
                        if (!in_array(strtolower(iRest::class), $imp, true) &&
                            !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                            throw new PublicAlert('Rest error, class/table exists in the restful generation folder which does not implement the correct interfaces. Please re-run rest generation.');
                        }

                        switch (count($stmt)) {
                            case 2:
                                if (is_string($stmt[0]) && is_string($stmt[1])) {
                                    $joinColumns[] = $stmt[0];
                                    $joinColumns[] = $stmt[1];
                                    $join .= $method . $table . ' ON ' . $stmt[0] . '=' . $stmt[1];
                                } else {
                                    throw new PublicAlert('One or more of the array values provided in the restful JOIN condition are not strings.');
                                }
                                break;
                            case 3:
                                if (is_string($stmt[0]) && is_string($stmt[1]) && is_string($stmt[2])) {
                                    if (!((bool)preg_match('#^=|>=|<=$#', $stmt[1]))) {
                                        throw new PublicAlert('Restful column joins may only use one (=,>=, or <=).');
                                    }
                                    $joinColumns[] = $stmt[0];
                                    $joinColumns[] = $stmt[2];
                                    $join .= $method . $table . ' ON ' . $stmt[0] . $stmt[1] . $stmt[2];
                                } else {
                                    throw new PublicAlert('One or more of the array values provided in the restful JOIN condition are not strings.');
                                }
                                break;
                            default:
                                throw new PublicAlert('Restful joins across two tables must be populated with two or three array values with column names, or an appropriate joining operator and column names.');
                        }
                    }
                    foreach ($joinColumns as $columnName) {
                        if (null === self::validateColumnName($columnName, $tableClassList)) {
                            throw new PublicAlert("Could not validate join column $columnName. Be sure correct restful tables are referenced.");
                        }
                    }
                    return true;
                };
                switch ($by) {
                    case self::INNER:
                        if (!$buildJoin(' INNER JOIN ')) {
                            throw new PublicAlert('The restful inner join had an unknown error.');
                        }
                        break;
                    case self::LEFT:
                        if (!$buildJoin(' LEFT JOIN ')) {
                            throw new PublicAlert('The restful left join had an unknown error.');
                        }
                        break;
                    case self::RIGHT:
                        if (!$buildJoin(' RIGHT JOIN ')) {
                            throw new PublicAlert('The restful right join had an unknown error.');
                        }
                        break;
                    default:
                        throw new PublicAlert('Restful join stmt may only use one of (' . self::INNER . ',' . self::LEFT . ', or ' . self::RIGHT . ').');
                }
            }
        }

        // pagination [self::PAGINATION][self::LIMIT]
        if (array_key_exists(self::PAGINATION, $argv) && !empty($argv[self::PAGINATION])) {    // !empty should not be in this block - I look all the time
            if (array_key_exists(self::LIMIT, $argv[self::PAGINATION]) && is_numeric($argv[self::PAGINATION][self::LIMIT])) {
                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {
                    $limit = ' LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $argv[self::PAGINATION][self::LIMIT]) . ',' . $argv[self::PAGINATION][self::LIMIT];
                } else {
                    $limit = ' LIMIT ' . $argv[self::PAGINATION][self::LIMIT];
                }
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (array_key_exists(self::ORDER, $argv[self::PAGINATION]) && is_string($argv[self::PAGINATION][self::ORDER])) {
                    if (is_array($argv[self::PAGINATION][self::ORDER])) {
                        $orderArray = [];
                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {
                            if (!in_array($sort, [self::ASC, self::DESC], true)) {
                                throw new PublicAlert('Restful order by failed to validate sorting method.');
                            }
                            if (null === $inTable = self::validateColumnName($item, $tableClassList)) {
                                throw new PublicAlert('Failed to validate order by column.');
                            }
                            $orderArray[] = "$item $sort";                                            // todo - validation
                        }
                        $order = implode(', ', $orderArray);
                        unset($orderArray);
                    } else {
                        $order .= $argv[self::PAGINATION][self::ORDER];
                    }
                } else if (array_key_exists(0, static::PRIMARY)) {
                    if ('binary' === (static::PDO_VALIDATION[static::PRIMARY[0]][0] ?? '')) {
                        $order .= static::COLUMNS[static::PRIMARY[0]] . self::DESC;
                    } else {
                        $order .= static::PRIMARY[0] . self::DESC;
                    }
                }
            }
            $limit = "$order $limit";
        } else if (!$noHEX && array_key_exists(0, static::PRIMARY)) {
            $limit = ' ORDER BY ' . static::PRIMARY[0] . ' ASC LIMIT 100';
        } else {
            $limit = '';
        }

        //is_string($argv[0] ?? false) and sortDump($argv);

        foreach ($get as $key => $column) {

            if (!empty($sql) && ',' !== $sql[-2]) {
                $sql .= ', ';
            }

            if (is_array($column)) {
                if (count($column) !== 2) {
                    throw new PublicAlert('An array in the GET Restful Request must be two values: [aggregate, column]');
                }
                [$aggregate, $column] = $column;    // todo - nested aggregates :: [$aggregate, string | array ]

                if (!in_array($aggregate, [
                    self::MAX,
                    self::MIN,
                    self::SUM,
                    self::DISTINCT,
                    self::GROUP_CONCAT
                ], true)) {
                    throw new PublicAlert('The aggregate method in the GET request must be one of the following: ' . implode(', ', [self::MAX, self::MIN, self::SUM, self::DISTINCT]));
                }

                if (null === $table = self::validateColumnName($column, $tableClassList)) {
                    throw new PublicAlert('Could not validate a column ' . $column . ' in the request.'); // todo html entities
                }

                switch ($aggregate) {
                    case self::GROUP_CONCAT:
                        if (!$noHEX && $table::PDO_VALIDATION[$column][0] === 'binary') {
                            $sql = "GROUP_CONCAT(DISTINCT HEX($column) ORDER BY $column ASC SEPARATOR ',') as " . $table::COLUMNS[$column] . ', ' . $sql;
                        } else {
                            $sql = "GROUP_CONCAT(DISTINCT($column) ORDER BY $column ASC SEPARATOR ',') as " . $table::COLUMNS[$column] . ', ' . $sql;
                        }
                        $sql = rtrim($sql, ', ');
                        break;
                    case self::DISTINCT:
                        if (!$noHEX && $table::PDO_VALIDATION[$column][0] === 'binary') {
                            $sql = "$aggregate HEX($column) as " . $table::COLUMNS[$column] . ', ' . $sql;
                            $group[] = $table::COLUMNS[$column];
                        } else {
                            $sql = "$aggregate($column), $sql";
                            $group[] = $column;
                        }
                        $sql = rtrim($sql, ', ');
                        break;
                    default:
                        $sql .= "$aggregate($column)";
                        $group[] = $column;
                }
                continue;               // next foreach iteration
            }


            if (!is_string($column)) {         // is this even possible at this point?
                throw new PublicAlert('C6 Rest client could not validate a column in the GET:[] request.');
            }

            if (array_key_exists($column, $joinColumns)) {  // todo - we need to cache everywhere / for every / validateColumnName
                continue;
            }

            if ($inTable = self::validateColumnName($column, $tableClassList)) {
                if (!$noHEX && $inTable::PDO_VALIDATION[$column][0] === 'binary') {
                    $sql .= "HEX($column) as " . $inTable::COLUMNS[$column];        // get short tag
                    $group[] = $column;
                } else {
                    $sql .= $column;
                    $group[] = $column;
                }
                continue;
            }

            throw new PublicAlert('Could not validate a column ' . $column . ' in the request.');
        }

        // case sensitive select
        $sql = 'SELECT ' . $sql . ' FROM ' . ($database === '' ? '' : $database . '.') . static::TABLE_NAME . ' ' . $join;

        if (null === $primary) {
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo, 'carbon_users', static::PDO_VALIDATION);
            }
        } else if (empty(static::PRIMARY)) {
            throw new PublicAlert('Primary keys given in GET request to a table without a primary key.');
        } else {
            $primaryEquals = [];
            foreach (static::PRIMARY as $column) {
                if ('binary' === static::PDO_VALIDATION[$column][0] ?? false) {
                    $primaryEquals[] = " $column=UNHEX(" . self::addInjection($primary, $pdo) . ') ';
                } else {
                    $primaryEquals[] = " $column='" . self::addInjection($primary, $pdo) . '\' ';
                }
            }
            $sql .= ' WHERE ' . implode(' OR ', $primaryEquals);
        }

        if ($aggregate && !empty($group)) {
            $sql .= ' GROUP BY ' . implode(', ', $group) . ' ';
        }

        $sql .= $limit;

        return '(' . $sql . ')';
    }


    /**
     * @param string|null $primary
     * @param array $argv
     * @param PDO|null $pdo
     * @param string $database
     * @return string
     * @throws PublicAlert
     */
    public static function subSelect(string $primary = null, array $argv, PDO $pdo = null, string $database = ''): string
    {
        self::$allowSubSelectQueries = true;
        return self::buildSelectQuery($primary, $argv, $database, $pdo, true);
    }

    public static function buildWhere(array $set, PDO $pdo, string $tableName, array $validation, $join = 'AND'): string
    {
        $sql = '(';
        $addJoinNext = false;

        foreach ($set as $column => $value) {
            if (is_array($value)) {
                if ($addJoinNext) {
                    $sql .= " $join ";
                }
                $addJoinNext = true;
                // recurse and change join method
                $sql .= self::buildWhere($value, $pdo, $tableName, $validation, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, $validation)) {
                $addJoinNext = false;
                /** @noinspection SubStrUsedAsStrPosInspection */
                if (self::$allowSubSelectQueries && substr($value, 0, '8') === '(SELECT ') {
                    $sql .= "($column = $value ) $join ";
                } else if ($validation[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo) . ")) $join ";
                } else {
                    $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
                }
            } else {                    // todo - were not validating a column here
                $addJoinNext = false;
                $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
            }
        }

        return rtrim($sql, " $join") . ')';
    }

    public static function addInjection($value, PDO $pdo, $quote = false): string
    {
        $inject = ':injection' . count(self::$injection);
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(PDOStatement $stmt): void
    {
        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        self::$injection = [];
    }
}