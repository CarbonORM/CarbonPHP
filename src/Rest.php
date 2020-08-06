<?php


namespace CarbonPHP;


use CarbonPHP\Error\PublicAlert;
use CarbonPHP\interfaces\iRest;
use CarbonPHP\interfaces\iRestfulReferences;
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

    #SQL helpful constants
    public const DESC = ' DESC'; // not case sensitive but helpful for reporting to remain upper
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

    public static array $injection = [];

    abstract public static function validateSelectColumn($column) : bool;

    public static function validateColumnName($column, $tableList): bool
    {
        if (array_key_exists($column, static::PDO_VALIDATION) || static::validateSelectColumn($column)) {
            return true;
        }
        while (!empty($tableList)) {
            $table = __NAMESPACE__ . '\\' . array_pop($tableList);

            if (!class_exists($table)) {
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
                return true;
            }
        }
        return false;
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
     * @param string $table
     * @param string|null $primary
     * @param string $namespace
     * @return bool
     * @throws PublicAlert
     */
    public static function RestfulRequests(string $table, string $primary = null, string $namespace = 'Tables\\'): bool
    {
        global $json;

        if (APP_ROOT . 'src' . DS === CARBON_ROOT) {
            $namespace = 'CarbonPHP\\Tables\\';
        }

        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'GET', 'DELETE'])) {
            throw new PublicAlert('The table does not implement the correct interfaces. (iRest::class or iRestfulReferences::class).');
        }

        if (!class_exists($table = $namespace . $table)) {
            throw new PublicAlert('Failed to find the table requested');
        }

        if (!is_subclass_of($table, self::class)) {
            throw new PublicAlert('The table must extent :: ' . self::class);
        }

        $imp = array_map('strtolower', array_keys(class_implements($table)));

        if (!($hasPrimary = in_array(strtolower(iRest::class), $imp, true)) &&
            !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
            throw new PublicAlert('The table does not implement the correct interfaces. (' . iRest::class . ' or ' . iRestfulReferences::class . ').');
        }

        if (defined("$table::REGEX_VALIDATION")) {
            $regex = constant("$table::REGEX_VALIDATION");
        } else {
            throw new PublicAlert('The table does not implement REGEX_VALIDATION. This can be an empty static array.');
        }

        if (defined("$table::PHP_VALIDATION")) {
            $php_validation = constant("$table::PHP_VALIDATION");
        } else {
            throw new PublicAlert('The table does not implement PHP_VALIDATION. This can be an empty static array.');
        }

        $validate = static function ($method, $args) use ($regex, $php_validation) {
            if (array_key_exists(0, $php_validation)) {
                foreach ($php_validation[0] as $class => $php_validation_method) {
                    if (!is_array($php_validation)) {
                        if (!call_user_func([$class, $php_validation_method], $args)) {
                            throw new PublicAlert('The request failed, please make sure arguments are correct.');
                        }
                    } else {
                        throw new PublicAlert('The first numeric key 0 in PHP_VALIDATION must be an array with call => method structure. Refer to Carbonphp.com for more info.');
                    }
                }
            }

            if (array_key_exists($method, $php_validation) && array_key_exists(0, $php_validation[$method])) {
                foreach ($php_validation[$method][0] as $class => $php_validation_method) {
                    if (!is_array($php_validation)) {
                        if (!call_user_func([$class, $php_validation_method], $args)) {
                            throw new PublicAlert('The request failed, please make sure arguments are correct.');
                        }
                    } else {
                        throw new PublicAlert('The first numeric key 0 in PHP_VALIDATION must be an array with call => method structure. Refer to Carbonphp.com for more info.');
                    }
                }
            }

            foreach ($args as $column => $value) {
                if (array_key_exists($column, $regex) &&
                    preg_match_all($regex[$column], $value, $matches, PREG_SET_ORDER) < 1) {  // can return 0 or false
                    throw new PublicAlert('The request failed, please make sure arguments are correct.');
                }
                if (array_key_exists($column, $php_validation)) {
                    foreach ($php_validation[$column] as $class => $validationMethod) {
                        if (!call_user_func([$class, $validationMethod], $value)) {
                            throw new PublicAlert('The request failed, please make sure arguments are correct.');
                        }
                    }
                }
                if (array_key_exists($column, $php_validation[$method] ?? [])) {
                    foreach ($php_validation[$method][$column] as $class => $validationMethod) {
                        if (!call_user_func([$class, $validationMethod], $value)) {
                            throw new PublicAlert('The request failed, please make sure arguments are correct.');
                        }
                    }
                }
            }
            return true;
        };

        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $methodCase = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));  // this is to match actual method spelling

        switch ($method) {
            case self::PUT:
                if ($primary === null) {
                    throw new PublicAlert('Updating records requires a primary key.');
                }
            case self::DELETE:
            case self::GET:
                $args = $method === REST::GET ? $_GET : $_POST;

                empty($args[self::WHERE] ?? null) or $validate($method, $args[self::WHERE]);

                $return = [];
                if (!call_user_func_array([$table, $methodCase], $hasPrimary ? [&$return, $primary, $args] : [&$return, $args])) {
                    throw new PublicAlert('The request failed, please make sure arguments are correct.');
                }

                $json['rest'] = $return;
                break;
            case self::POST:
                empty($_POST) or $validate($method, $_POST);
                if (!$id = call_user_func([$table, $methodCase], $_POST, $primary)) {
                    throw new PublicAlert('The request failed, please make sure arguments are correct.');
                }
                $json['rest'] = ['created' => $id];
                break;
        }
        headers_sent() or header('Content-Type: application/json');
        print PHP_EOL . json_encode($json) . PHP_EOL;
        return true; // stmt unreachable
    }


    public static function buildWhere(array $set, PDO $pdo, string $tableName, array $validation, $join = 'AND'): string
    {
        $sql = '(';
        $bump = false;
        foreach ($set as $column => $value) {
            if (is_array($value)) {
                if ($bump) {
                    $sql .= " $join ";
                }
                $bump = true;
                $sql .= self::buildWhere($value, $pdo, $tableName, $validation, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, $validation)) {
                $bump = false;
                /** @noinspection SubStrUsedAsStrPosInspection */
                if (substr($value, 0, '8') === 'C6SUB522') {
                    $subQuery = substr($value, '8');
                    $sql .= "($column = $subQuery ) $join ";
                } else if ($validation[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(" . self::addInjection($value, $pdo, $tableName) . ")) $join ";
                } else {
                    $sql .= "($column = " . self::addInjection($value, $pdo, $tableName) . ") $join ";
                }
            } else {
                $bump = false;
                $sql .= "($column = " . self::addInjection($value, $pdo, $tableName) . ") $join ";
            }
        }
        return rtrim($sql, " $join") . ')';
    }

    public static function addInjection($value, PDO $pdo, string $tableName, $quote = false): string
    {
        $inject = ':injection' . count(self::$injection) . $tableName;
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(PDOStatement $stmt): void
    {
        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
}