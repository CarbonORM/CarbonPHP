<?php

namespace CarbonPHP\Restful;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\MySQL;
use CarbonPHP\Tables\History_Logs;
use PDO;


abstract class RestQueryBuilder extends RestQueryValidation
{

    public static function parseAggregateWithNoOperators(&$aggregate): string
    {

        if ($aggregate === self::COUNT_ALL) {

            $aggregate = 'COUNT(*)';

        } else {

            $aggregate .= '()';
        }

        return $aggregate;

    }


    /**
     * @throws PublicAlert
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function handleSubSelectAggregate(array $stmt): string
    {

        $tableName = $stmt[1];

        $tableName = explode('_', $tableName);      // table name semantics vs class name

        $tableName = array_map('ucfirst', $tableName);

        $tableName = implode('_', $tableName);

        if (false === self::$allowSubSelectQueries) {

            throw new PublicAlert('Whoa, looks like a sub-select was encountered without `self::$allowSubSelectQueries` explicitly set to true. '
                . ' This is a security precaution and is recommend to only be set to true when explicitly needed. You should'
                . " consider doing this in the ($tableName::PHP_VALIDATION['PREPROCESS']) event cycle.");

        }

        if (false === defined("$tableName::CLASS_NAME")) {

            $tableNameFullTry = static::CLASS_NAMESPACE . $tableName;

            if (false === defined("$tableNameFullTry::CLASS_NAME")) {

                throw new PublicAlert("The restful sub-select failed as the table ($tableName) did not have member const ($tableName::CLASS_NAME) under the (" . static::CLASS_NAMESPACE . ') namespace.');

            }

            $tableName = $tableNameFullTry;

        }

        $primaryField = $tableName::PRIMARY;

        $primary = [];

        $argv = [];

        $as = '';

        $setLength = count($stmt);

        if (true === self::is_assoc($stmt)) {   // todo - php 8.1 check when we gain support

            throw new PublicAlert('The restful sub-select query has been detected to be an associative array.'
                . ' Associative array arrays can not (yet) be unpacked in PHP. Please remove the keys an allow php '
                . 'to manually assign them. The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')');

        }

        if (null === $primaryField) {

            switch ($setLength) {

                /** @noinspection PhpMissingBreakStatementInspection */
                case 4:

                    [, , , $as] = $stmt;

                case 3:

                    [, , $argv] = $stmt;

                    if (false === is_array($argv)) {

                        throw new PublicAlert("The third argument passed to the restful sub-select was a not an array. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as].  The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    if (false === is_string($as)) {

                        throw new PublicAlert("The fourth argument passed  to the restful sub-select was a not a string. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as]. The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    break;

                default:

                    throw new PublicAlert('The restful sub-select set passed was not the correct length. The '
                        . "table ($tableName) has no primary keys so only three or four values are expected."
                        . " Arguments must match the signature [ SELECT, $tableName, \$argv = [], \$as = '' ] for sub-select"
                        . ' queries on tables which have no sql queries. The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')');

            }

        } else if (is_array($primaryField) || is_string($primaryField)) {

            $error_context = static function (string $tableName) use ($primaryField, $stmt) {

                $set = ' The infringing set on table (' . static::class . ') :: (' . json_encode($stmt) . ')';

                return is_array($primaryField) ? "The signature for tables with multiple column primary keys is as follows:: [ Rest::SELECT, '$tableName',  (array) \$primary, (array) \$argv, (string|optional) \$as]. $set"
                    : "The signature for tables with a single column primary key is as follows:: [ Rest::SELECT, '$tableName',  (string|int|array) \$primary, (array) \$argv, (string|optional) \$as]. Note: if an array is given it must be a key value with the key being the fully qualified `table.column` name associated with the primary key. $set";

            };

            switch ($setLength) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case 5:

                    [, , , , $as] = $stmt;

                case 4:

                    [, , $primary, $argv] = $stmt;

                    if ('' === $primary) {

                        $primary = null;

                    }

                    /** @noinspection NotOptimalIfConditionsInspection - whatever; really */
                    if (is_string($primaryField)
                        && (true === is_int($primary)
                            || true === is_string($primary))) {

                        $primary = [static::PRIMARY => $primary];

                    }

                    if (null !== $primary && false === is_array($primary)) {

                        throw new PublicAlert("The third argument passed to the restful sub-select was a not a { null, string, int, or array }. " . $error_context($tableName));

                    }

                    if (false === is_array($argv)) {

                        throw new PublicAlert("The fourth argument passed to the restful sub-select was a not an array. " . $error_context($tableName));

                    }

                    if (false === is_string($as)) {

                        throw new PublicAlert("The fourth argument passed to the restful sub-select was a not a string. " . $error_context($tableName));

                    }

                    break;

                default:

                    throw new PublicAlert('The restful sub-select set passed was not the correct length. (' . count($stmt) . ') '
                        . $error_context($tableName));

            }

        }

        // thus ( $tableName::CLASS_NAME === $tableName )
        // the following if from the original sub-select query, in this context xss could be dirty so this
        // should be set during validations explicitly by end user
        # self::$allowSubSelectQueries = true;

        $tableName::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $primary, $argv, true);

        $sql = $tableName::buildSelectQuery($primary, $argv, true);

        $sql = "($sql)";

        if (!empty($as)) {
            $sql = "$sql AS " . self::addInjection($as, [self::PDO_TYPE => null]);
        }

        $tableName::completeRest(true);

        return $sql;

    }

    /**
     * @throws PublicAlert
     */
    public static function isAggregate(string $column, $name): string
    {

        if (true === is_string($name)) {

            $allowedIsAggregations = [self::NULL, self::FALSE, self::TRUE, self::UNKNOWN];

            if (in_array($name, $allowedIsAggregations, true)) {

                return "$column IS $name";

            }

        }

        throw new PublicAlert("The aggregate IS used on column ($column) must be one of (" . implode(', ', $allowedIsAggregations) . ") exclusively; the value ($name) in incorrect.");

    }


    /**
     * @throws PublicAlert
     */
    private static function validateInternalOrAddInjection(string &$columnOrInjection, string $aggregate, &...$rest): string
    {

        $value_is_custom = false === self::validateInternalColumn($columnOrInjection, $aggregate, $rest);

        if ($value_is_custom) {

            // this is just a technicality as name could be injected but also referenced in the query
            $columnOrInjection = self::addInjection($columnOrInjection, [self::PDO_TYPE => null]);

        }

        return $columnOrInjection;

    }

    /**
     * @param array $stmt
     * @param bool $isSubSelect
     * @return string
     * @throws PublicAlert
     */
    private static function buildAggregate(array $stmt, bool $isSubSelect = false): string
    {
        $name = '';

        if (self::isSubSelectAggregation($stmt)) {

            // todo - it is unclear if this is more an aggregate or column with respect to the group by
            return self::handleSubSelectAggregate($stmt);

        }


        self::$aggregateSelectEncountered = true; # todo - is this correct?

        $stmtCount = count($stmt);

        if (4 === $stmtCount) {

            [$aggregate, $time, $zoneOne, $zoneTwo] = $stmt;

            if ($aggregate !== self::CONVERT_TZ) {

                throw new PublicAlert('Expected the CONVERT_TZ aggregate function as four arguments were encountered.');

            }

            return $aggregate .
                "(" . self::validateInternalOrAddInjection($time, $aggregate, $zoneOne, $zoneTwo) . ","
                . self::validateInternalOrAddInjection($zoneOne, $aggregate, $time, $zoneTwo) . ','
                . self::validateInternalOrAddInjection($zoneTwo, $aggregate, $time, $zoneOne) . ')';

        }

        if (3 === $stmtCount) {

            [$column, $aggregate, $name] = $stmt;

            switch ($aggregate) {
                case self::IS:
                case self::AS:
                case self::INTERVAL:
                case self::IN:
                case self::NOT_IN:
                    break;
                default:
                    // this just flips order for aggregates like SUM(), the variable $name is preserved
                    [$aggregate, $column] = $stmt;
            }

        } elseif (2 === $stmtCount) {

            [$aggregate, $column] = $stmt;    // todo - nested aggregates :: [$aggregate, string | array ]

        } else {

            if (count($stmt) !== 1) {

                throw new PublicAlert('A Restful array value in the aggregation must be at least two values: array( $aggregate, $column [, optional: $as] ) ');

            }

            [$aggregate] = $stmt;

            if (self::NOW !== $aggregate) {

                throw new PublicAlert("Restful request encountered an ($aggregate) aggregate function as the only member of the array.");

            }

            return $aggregate . '()';

        }

        $allowedValues = [...self::AGGREGATES, ...self::OPERATORS];

        if (false === in_array($aggregate, $allowedValues, true)) {

            throw new PublicAlert('The attempted aggregate (' . $aggregate . ') in query ( ' . json_encode($stmt) . ') in the GET request must be one of the following: '
                . implode(', ', $allowedValues));

        }

        if ($aggregate === self::CONCAT) {

            if (false === is_array($column) || empty($column)) {

                throw new PublicAlert("Aggregation failed for $aggregate. Arguments must be a sub-array. ($column)");

            }

            $returnIsAggregate = [];

            foreach ($column as $item) {

                $returnIsAggregate [] = self::validateInternalOrAddInjection($item, $aggregate, $column);

            }

            return self::CONCAT . '(' . implode(',', $returnIsAggregate) . ')';

        }


        if (false === self::validateInternalColumn($column, $aggregate)) {

            /** @noinspection JsonEncodingApiUsageInspection */
            throw new PublicAlert("The column value of stmt (" . json_encode($stmt) . ") caused validateInternalColumn to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

        }

        switch ($aggregate) {
            case self::NOT_IN:
            case self::IN:

                return self::inAggergaation($column, $aggregate, $name);

            case self::IS:

                return self::isAggregate($column, $name);

        }

        if ('' !== $name) {

            // this is just a technicality as name could be injected but also referenced in the query
            $name = self::addInjection($name, [self::PDO_TYPE => null]);

        }

        switch ($aggregate) {

            case self::AS:

                if (empty($name)) {

                    throw new PublicAlert("The third argument provided to the ($aggregate) select aggregate must not be empty and be a string.");

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "HEX($column) $aggregate $name";

                }

                return "$column $aggregate $name";

            case self::GROUP_CONCAT:

                if (empty($name)) {

                    $name = self::$compiled_valid_columns[$column];

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "GROUP_CONCAT(DISTINCT HEX($column) ORDER BY $column ASC SEPARATOR ',') AS $name";

                }

                return "GROUP_CONCAT(DISTINCT($column) ORDER BY $column ASC SEPARATOR ',') AS $name";

            case self::DISTINCT:

                if (empty($name)) {

                    $name = self::$compiled_valid_columns[$column];

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "$aggregate HEX($column) AS $name";

                }

                return "$aggregate($column) AS $name";

            default:

                if ($name === '') {

                    return "$aggregate($column)";

                }

                return "$aggregate($column) AS $name";

        }

    }

    public static function inAggergaation($column, $aggregate, array $values)
    {
        if (false === is_array($values)) {

            throw new PublicAlert('Rest IN aggregate error (' . $column . ') required an array!');

        }

        $returnIsAggregate = [];

        foreach ($values as $item) {

            $returnIsAggregate [] = self::validateInternalOrAddInjection($item, $aggregate, $column);

        }

        return "$column $aggregate ( " . implode(',', $returnIsAggregate) . ' )';
    }

    public static function buildMysqlHistoryTrigger(string $table): void
    {

        $queryNew = '';

        $queryOld = '';

        $prefix = $table::TABLE_PREFIX !== History_Logs::TABLE_PREFIX ? $table::TABLE_PREFIX : '';

        foreach ($table::PDO_VALIDATION as $name => $columnInfo) {

            $column = $table::COLUMNS[$name];  // get name without table prefixed

            $is_bin = $columnInfo[self::PDO_TYPE] === 'binary';

            $queryNew .= $is_bin
                ? <<<END
                                '$column', HEX(NEW.$column),
                            END
                : <<<END
                                '$column', JSON_QUOTE(COALESCE(NEW.$column,'')),
                            END;

            $queryOld .= $is_bin
                ? <<<END
                                '$column', HEX(OLD.$column),
                            END
                : <<<END
                                '$column', JSON_QUOTE(COALESCE(OLD.$column,'')),
                            END;
        }

        $queryOld = rtrim($queryOld, ',');

        $queryNew = rtrim($queryNew, ',');

        $dependencies = $table::EXTERNAL_TABLE_CONSTRAINTS;

        $delete_children = '';

        if (!empty($dependencies)) {
            foreach ($dependencies as $external => $internal) {

                [$externalTableName, $externalColumn] = explode('.', $external);

                [, $internalColumn] = explode('.', $internal);

                $delete_children .= "# noinspection SqlResolve
DELETE FROM $externalTableName WHERE $externalColumn = OLD.$internalColumn;" . PHP_EOL;


            }

        }

        $table_name = $table::TABLE_NAME;

        $trigger = <<<TRIGGER
DROP TRIGGER IF EXISTS `trigger_{$table_name}_b_d`;;
CREATE TRIGGER `trigger_{$table_name}_b_d` BEFORE DELETE ON `$table_name` FOR EACH ROW
BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'DELETE'
                        , history_data = JSON_OBJECT($queryOld
                        ), original_query);

      -- Delete Children
$delete_children

END;;

DROP TRIGGER IF EXISTS `trigger_{$table_name}_a_u`;;
CREATE TRIGGER `trigger_{$table_name}_a_u` AFTER UPDATE ON `$table_name` FOR EACH ROW
                                                                   BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'PUT'
                        , history_data = JSON_OBJECT($queryNew
                        ), original_query);

END;;

DROP TRIGGER IF EXISTS `trigger_{$table_name}_a_i`;;
CREATE TRIGGER `trigger_{$table_name}_a_i` AFTER INSERT ON `$table_name` FOR EACH ROW
                                                                   BEGIN

        DECLARE original_query text;

SELECT argument INTO original_query 
  FROM mysql.general_log 
  where thread_id = connection_id() 
  order by event_time desc 
  limit 1;

      -- Insert record into audit tables
INSERT INTO {$prefix}carbon_history_logs (history_uuid, history_table, history_type, history_data, history_original_query)
                VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-',''))
                        , '$table_name'
                        , 'POST'
                        , history_data = JSON_OBJECT($queryNew
                        ), original_query);

END;;
TRIGGER;


        if (false === file_put_contents(CarbonPHP::$app_root . 'trigger.sql', 'DELIMITER ;;' . PHP_EOL . $trigger . PHP_EOL . 'DELIMITER ;')) {

            self::colorCode('PHP file_put_contents failed to store (' . CarbonPHP::$app_root . 'trigger.sql)', iColorCode::RED);

            return;

        }

        MySQL::MySQLSource(CarbonPHP::$app_root . 'trigger.sql');

        unlink(CarbonPHP::$app_root . 'trigger.sql');

    }

    public static function addInjection($value, array $pdo_column_validation = null): string
    {
        switch ($pdo_column_validation[self::PDO_TYPE] ?? null) {

            default:

                // todo - cache db better (startRest?)
                $value = (Database::database())->quote($value);        // boolean, string

            case null:
            case PDO::PARAM_INT:
            case PDO::PARAM_STR: // bindValue will quote strings

                $key = array_search($value, self::$injection, true);

                if (false !== $key) {

                    return $key;   // this is a cache

                }

                $inject = ':injection' . count(self::$injection); // self::$injection needs to be a class const not a

                self::$injection[$inject] = $value;

                break;

        }

        return " $inject ";

    }

    /**
     * only get, put, and delete
     * @param array $where
     * @param array|null $primary
     * @return string
     * @throws PublicAlert
     */
    private static function buildQueryWhereValues(array $where, array $primary = null): string
    {

        if (null === $primary) {

            if ([] === $where) {

                switch (self::$REST_REQUEST_METHOD) {

                    default:

                        throw new PublicAlert('An unexpected request (' . self::$REST_REQUEST_METHOD . ') method was given to (' . static::class . ').');

                    case self::GET:

                        return '';

                    case self::PUT:

                        if (false === self::$allowFullTableUpdates) {

                            return '';

                        }

                        throw new PublicAlert('Rest expected a primary key or valid where statement but none was provided during a (PUT) request on (' . static::class . ')');

                    case self::DELETE:

                        if (false === self::$allowFullTableDeletes) {

                            return '';

                        }

                        throw new PublicAlert('Rest expected a primary key or valid where statement but none was provided during a (DELETE) request on (' . static::class . ')');


                    case self::POST:

                        throw new PublicAlert('The post method was detected while running buildQueryWhereValues against table (' . static::class . ')');


                }

            }

            return ' WHERE ' . self::buildBooleanJoinedConditions($where);

        }

        if (empty(static::PRIMARY)) {

            throw new PublicAlert('Primary keys given during a request to (' . static::class . ') which does not have a primary key. Please regenerate this class using RestBuilder.');

        }

        if (is_string(static::PRIMARY)) {

            if ([] !== $where && '' !== ($primary[static::PRIMARY] ?? '')) {

                throw new PublicAlert('Restful tables with a single primary key must not have WHERE values passed when the primary key is given. Table (' . static::class . ') was passed a non empty key `WHERE` (' . json_encode($where, JSON_PRETTY_PRINT) . ') to the arguments of GET.');

            }

            if ('binary' === (static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE])) {

                return ' WHERE ' . static::PRIMARY . "=UNHEX(" . self::addInjection($primary[static::PRIMARY]) . ') ';

            }

            return ' WHERE ' . static::PRIMARY . "=" . self::addInjection($primary[static::PRIMARY], static::PDO_VALIDATION[static::PRIMARY]) . ' ';

        }

        if (is_array(static::PRIMARY)) {

            if (count($primary) !== count(static::PRIMARY)) {

                throw new PublicAlert('The table ' . static::class . ' was passed a subset of the required primary keys. When passing primary keys all must be present.');

            }

            $primaryKeys = array_keys($primary);

            if (!empty(array_intersect_key($primaryKeys, static::PRIMARY))) {

                throw new PublicAlert('The rest table ' . static::class . ' was passed the correct number of primary keys; however, the associated keys did not match the static::PRIMARY attribute.');

            }

            if (!empty($where)) {

                throw new PublicAlert('Restful tables selecting with primary keys must not have WHERE values passed when the primary keys are given. Table ' . static::class . ' was passed a non empty key `WHERE` to the arguments of GET.');

            }

            return ' WHERE ' . self::buildBooleanJoinedConditions($primary);

        }

        throw new PublicAlert('An unexpected error occurred while paring your primary key in ' . static::class . '. static::PRIMARY may only be a string, array, or null. You may need to regenerate with RestBuilder.');

    }

    private static function buildQueryGroupByValues(array $group, string $sql): string
    {

        // concatenation and aggregation
        if (true === self::$columnSelectEncountered && true === self::$aggregateSelectEncountered && [] === $group) {

            throw new PublicAlert("Restful Error! A simple column select and aggregate function were used in the same query without the ['GROUP_BY'] clause explicitly set. This has been deprecated. Failed after compiling only :: ($sql)");

        }

        if ([] === $group) {

            return '';

        }

        // GROUP BY clause can only be used with aggregate functions like SUM, AVG, COUNT, MAX, and MIN.
        return ' GROUP BY ' . self::buildQuerySelectValues($group, true) . ' ';

    }

    private static function buildQueryHavingValues(array $having): string
    {
        if ([] === $having) {

            return '';

        }

        return ' ' . self::HAVING . ' ' . self::buildBooleanJoinedConditions($having);
    }

    private static function buildQuerySelectValues(array $get, bool $isSubSelect): string
    {
        $sql = '';

        foreach ($get as $key => $column) {

            if (false === is_numeric($key)) {

                throw new PublicAlert('Select values must only use numeric keys. The key (' . $key . ') was encountered.');

            }

            // this is for buildAggregate which prepends
            if ('' !== $sql && ',' !== $sql[-2]) {

                $sql .= ', ';

            }

            $wasCallable = false;

            while (is_callable($column)) {

                $wasCallable = true;    // this works as if rest generated the query or not and can be trusted as

                $column = $column();

            }

            // todo - more validation on sub-select?
            if ($wasCallable && strpos($column, '(SELECT ') === 0) {

                self::$columnSelectEncountered = true;

                $sql .= $column;

                continue;

            }


            if (is_array($column)) {

                if (is_string($column[0] ?? false) && in_array($column[0], [
                        self::GROUP_CONCAT,
                        self::DISTINCT      // todo - all I know is that count need be after distinct
                    ], true)) {

                    $sql = self::buildAggregate($column, $isSubSelect) . ", $sql";

                    continue;

                }

                $sql .= self::buildAggregate($column, $isSubSelect);

                continue;               // next foreach iteration

            }

            if (!is_string($column)) {

                // is this even possible at this point?
                throw new PublicAlert('C6 Rest client could not validate a column in the GET:[] request.');

            }

            // todo - update this syntax allow for remote sub select using [ buildAggregate ]
            if (self::$allowSubSelectQueries && strpos($column, '(SELECT ') === 0) {

                self::$columnSelectEncountered = true;

                $sql .= $column;

                continue;

            }

            if (self::validateInternalColumn($column)) {

                self::$columnSelectEncountered = true;

                if (false === $isSubSelect
                    && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    $sql .= "HEX($column) as " . self::$compiled_valid_columns[$column];        // get short tag

                    continue;

                }

                $sql .= $column;

                continue;

            }

            throw new PublicAlert("CarbonPHP could not validate a column ($column) passed in the (SELECT) request which caused "
                . "the request to fail. It does not appear to be allowed based on the tables joined. Possible values include ("
                . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

        }

        // this is for buildAggregate self::GROUP_CONCAT & self::DISTINCT
        return rtrim($sql, ', ');
    }

    private static function buildQueryPaginationValues(array $argv, bool $isSubSelect, array $primary = null): string
    {
        // pagination [self::PAGINATION][self::LIMIT]
        if (array_key_exists(self::PAGINATION, $argv) && !empty($argv[self::PAGINATION])) {    // !empty should not be in this block - I look all the time

            $limit = '';

            // setting the limit to null will cause no limit
            // I get tempted to allow 0 to symbolically mean the same thing, but 0 Limit is allowed in mysql
            // @link https://stackoverflow.com/questions/30269084/why-is-limit-0-even-allowed-in-mysql-select-statements
            if (array_key_exists(self::LIMIT, $argv[self::PAGINATION]) && null !== $argv[self::PAGINATION][self::LIMIT]) {

                $limit = $argv[self::PAGINATION][self::LIMIT];

                if (false === is_numeric($limit)) {

                    throw new PublicAlert("A non numeric LIMIT ($limit) was provided to REST while querying against (" . static::class . ').');

                }

                if (0 > (int)$limit) {

                    throw new PublicAlert('A negative limit was encountered in the rest Builder while querying against (' . static::class . ').');

                }

                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {

                    if ($argv[self::PAGINATION][self::PAGE] < 1) {

                        return static::signalError('The body of PAGINATION requires that PAGE attribute be no less than 1.');

                    }

                    $limit = 'LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $limit) . ',' . $limit;

                } else {

                    $limit = 'LIMIT ' . $limit;

                }

            }

            if ('' === $limit || array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                $order = ' ORDER BY ';

                /** @noinspection NotOptimalIfConditionsInspection */
                if (array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

                    if (is_array($argv[self::PAGINATION][self::ORDER])) {

                        /** @noinspection NotOptimalIfConditionsInspection */
                        if (2 === count($argv[self::PAGINATION][self::ORDER])
                            && array_key_exists(0, $argv[self::PAGINATION][self::ORDER])
                            && array_key_exists(1, $argv[self::PAGINATION][self::ORDER])
                            && is_string($argv[self::PAGINATION][self::ORDER][0])
                            && is_string($argv[self::PAGINATION][self::ORDER][1])
                        ) {

                            throw new PublicAlert('The syntax used in the order by must be array( $key => $value ) pairs; not a comma `,` separated list. The (' . json_encode($argv[self::PAGINATION][self::ORDER]) . ') argument passed is not correct.');

                        }

                        $orderArray = [];

                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {

                            if (false === in_array($sort, [self::ASC, self::DESC], true)) {

                                throw new PublicAlert('Restful order by failed to validate sorting method. The value should be one of (' . json_encode([self::ASC, self::DESC]) . ')');

                            }

                            if (false === self::validateInternalColumn($item, $sort)) {

                                throw new PublicAlert("The column value ($item) caused your custom validations to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            $orderArray[] = "$item $sort";

                        }

                        $order .= implode(', ', $orderArray);

                        unset($orderArray);

                    } else {

                        throw new PublicAlert('Rest query builder failed during the order pagination.');

                    }

                } else if (null !== static::PRIMARY
                    && (is_string(static::PRIMARY) || is_array(static::PRIMARY))) {

                    $primaryColumn = is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0];

                    if ('binary' === static::PDO_VALIDATION[$primaryColumn][self::MYSQL_TYPE]) {

                        $order .= static::COLUMNS[$primaryColumn] . ' ' . self::DESC;

                    } else {

                        $order .= $primaryColumn . ' ' . self::DESC;

                    }

                } elseif (!empty($limit)) {

                    throw new PublicAlert('An error was detected in your REST syntax regarding the limit. No order by clause was provided and no primary keys were detected in the main joining table to automagically set.');

                } else {

                    throw new PublicAlert('A unknown Restful error was encountered while compiling the order by statement.');

                }

                return $order . ($limit === '' ? '' : " $limit");

            }

            return $limit;

        }

        if (false === $isSubSelect && static::PRIMARY !== null) {

            return ' ORDER BY ' . (is_string(static::PRIMARY) ? static::PRIMARY : static::PRIMARY[0])
                . ' ASC LIMIT ' . (null === $primary ? '100' : '1');

        }

        return '';

    }


    /**
     * @param array|null $primary
     * @param array $argv
     * @param bool $isSubSelect
     * @return string
     * @throws PublicAlert
     */
    protected static function buildSelectQuery(array $primary = null, array $argv = [], bool $isSubSelect = false): string
    {

        $sql = 'SELECT '
            . self::buildQuerySelectValues($argv[self::SELECT] ?? array_keys(static::PDO_VALIDATION), $isSubSelect)
            . ' FROM '
            . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
            . static::TABLE_NAME . ' ';

        $ifArrayKeyExistsThenValueMustBeArray = static function (array $argv, string $key) {

            if (true === array_key_exists($key, $argv)) {

                if (false === is_array($argv[$key])) {

                    throw new PublicAlert("The restful join field ($argv) passed to (" . static::class . ") must be an array.");
                }

                return true;

            }

            return false;

        };

        if (true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::JOIN)) {

            $sql .= self::buildQueryJoinValues($argv[self::JOIN]);

        }

        // Boolean Conditions and aggregation
        $sql .= self::buildQueryWhereValues($argv[self::WHERE] ?? [], $primary);

        $sql .= self::buildQueryGroupByValues(
            true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::GROUP_BY)
                ? $argv[self::GROUP_BY]
                : [], $sql);

        if (true === $ifArrayKeyExistsThenValueMustBeArray($argv, self::HAVING)) {

            // Boolean Conditions and aggregation
            $sql .= self::buildQueryHavingValues($argv[self::HAVING]);

        }

        $sql .= self::buildQueryPaginationValues($argv, $isSubSelect, $primary);

        return $sql;
    }


    /**
     * @throws PublicAlert
     */
    private static function buildQueryJoinValues(array $join): string
    {

        $sql = '';

        foreach ($join as $by => $tables) {

            $validJoins = [
                self::INNER,
                self::LEFT,
                self::RIGHT,
                self::FULL_OUTER,
                self::LEFT_OUTER,
                self::RIGHT_OUTER
            ];

            if (false === in_array($by, $validJoins, true)) {

                throw new PublicAlert('The restful inner join had an unknown error.'); // todo - message

            }

            $by = str_replace("_", " ", $by);

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PublicAlert('A table ' . $JoiningClass . ' provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');

                }

                $imp = array_map('strtolower', array_keys(class_implements($JoiningClass)));

                if (false === in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                    && false === in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                    && false === in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
                ) {

                    throw new PublicAlert('Rest error, class/table exists in the restful generation folder which does not implement the correct interfaces. Please re-run rest generation.');

                }

                if (false === is_array($stmt)) {

                    throw new PublicAlert("Rest error in the join stmt, the value of $JoiningClass is not an array.");

                }

            }

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE
                        . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PublicAlert('A table (' . $JoiningClass . ') provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
                }


                $table = $JoiningClass::TABLE_NAME;

                /**
                 * Prefix is normally included in the table name variable.
                 * The table prefix is expected to be an empty string for all tables except carbon_ internals.
                 * The following check is so CarbonPHP internal tables can be compatible with table prefixing.
                 * It also ensures the table you are joining has been generated correctly for your current build.
                 */
                self::checkPrefix($JoiningClass::TABLE_PREFIX);

                $prefix = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';   // its super important to do this period on this line

                if ('' !== $sql && ' ' !== $sql[-1]) {

                    $sql .= ' ';

                }

                $sql .= strtoupper($by) . " JOIN $prefix$table ON " . self::buildBooleanJoinedConditions($stmt);

            }

        }

        return $sql;

    }


    /**
     * It was easier for me to think of this in a recursive manner.
     * In reality we should limit our use of recursion php <= 8.^
     * @param array $set
     * @return string
     * @throws PublicAlert
     */
    protected static function buildBooleanJoinedConditions(array $set): string
    {
        static $booleanOperatorIsAnd = true;

        $booleanOperator = $booleanOperatorIsAnd ? 'AND' : 'OR';

        $sql = '';

        // this is designed to be different than buildAggregate
        $supportedOperators = iRest::OPERATORS;

        // we have to determine when an aggregate might occur early
        if (true === self::array_of_numeric_keys_and_string_int_or_aggregate_values($set)) {

            switch (count($set)) {

                case 0:

                    throw new PublicAlert('An empty array was passed as a leaf member of a condition. Nested arrays recursively flip the AND vs OR joining conditional. Multiple conditions are not required as only one condition is needed. Two may be asserted equal implicitly by placing them in positions one and two of the array. An explicit definition may see the second $position[1] equal one of  (' . implode('|', $supportedOperators) . ')');

                case 1:

                    $key = array_key_first($set);

                    if (is_array($set[$key])) {

                        $set[$key] = json_encode($set[$key]);

                    }

                    throw new PublicAlert("The single value ({$set[$key]}) has the numeric array key [$key] which could imply a conditional equality; however, numeric keys (implicit or explicit) to imply equality are not allowed in C6. Please reorder the condition to have a string key and numeric value.");

                case 2:

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], self::EQUAL, $set[1]);

                    break;

                case 3:

                    if (false === in_array($set[1], $supportedOperators, true)) { // ie #^=|>=|<=$#

                        throw new PublicAlert("Restful column joins may only use one of the following (" . implode('|', $supportedOperators) . ") the value ({$set[1]}) is not supported.");

                    }

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], $set[1], $set[2]);

                    break;

                default:

                    throw new PublicAlert('An array containing all numeric keys was found with (' . count($set) . ') values. Only exactly two or three values maybe given.');

            } // end switch

        } else {

            $addJoinNext = false;

            // the value is not callable (we dealt with it in the condition above)
            foreach ($set as $column => $value) {

                if ($addJoinNext) {

                    $sql .= " $booleanOperator ";

                } else {

                    $addJoinNext = true;

                }

                if (is_int($column)) {

                    if (false === is_array($value)) {

                        throw new PublicAlert("Rest boolean condition builder expected ($value) to be an array as it has a numeric key in the set :: " . json_encode($set));

                    }

                    $booleanOperatorIsAnd = !$booleanOperatorIsAnd; // saves memory

                    $sql .= self::buildBooleanJoinedConditions($value);

                    $booleanOperatorIsAnd = !$booleanOperatorIsAnd;

                    continue;

                }

                // else $column is equal a (string)
                if (false === is_array($value) || self::isAggregateArray($value)) {

                    $sql .= self::addSingleConditionToWhereOrJoin($column, self::EQUAL, $value);

                    continue;

                }


                $count = count($value);

                switch ($count) {

                    case 1:

                        // this would be odd syntax but I will allow it
                        if (true === array_key_exists(0, $value)
                            && true === is_array($value[0])
                            && true === self::isAggregateArray($value[0])) {

                            $sql .= self::addSingleConditionToWhereOrJoin($column, self::EQUAL, $value[0]);

                            break;

                        }

                    // let this fall to throw alert

                    default:

                        throw new PublicAlert("A rest key column ($column) value (" . json_encode($value) . ") was set to an array with ($count) "
                            . "values but requires only one or two. Boolean comparisons can use one of the following operators "
                            . "(" . implode('|', $supportedOperators) . "). The same comparison can be made with an empty (aka default numeric) key "
                            . "and three array entries :: [  Column,  Operator, (Operand|Column2) ]. Both ways are made equally "
                            . "for conditions which might require multiple of the same key in the same array; as this would be invalid syntax in php.");

                    case 2:

                        if (false === in_array($value[0], $supportedOperators)) { // ie #^=|>=|<=$#

                            throw new PublicAlert("Table (" . static::class . ") restful column joins may only use one of the following supported operators ($supportedOperators).");

                        }

                        $sql .= self::addSingleConditionToWhereOrJoin($column, $value[0], $value[1]);

                }


            } // end foreach

        }

        $sql = preg_replace("/\s$booleanOperator\s?$/", '', $sql);

        return "($sql)";  // do not try to remove the parenthesis from this return

    }

    private static function array_of_numeric_keys_and_string_int_or_aggregate_values(array &$a): bool
    {

        $return = true;

        if (empty($a)) {

            return $return;

        }

        foreach ($a as $key => &$value) {

            while (is_callable($value)) {

                $value = $value();

            }

            if (false === is_numeric($key)
                || (false === is_string($value)
                    && false === is_int($value)
                    && (is_array($value)
                        && false === self::isAggregateArray($value)))) {

                // not an Aggregate
                $return = false;    // we want to remove callables

            }

        }

        return $return;

    }


    /**
     * @param array|null $primary
     * @param array $argv
     * @param string $as
     * @return callable
     * @todo - external subselect
     * Rest::SELECT => [
     *
     *      [ Rest::SELECT,  [  [primary:(array|null), optional argv:(array)]  ],  optional as:(string)) ]
     *
     * ]
     *
     * @deprecated private use
     *
     */
    public static function subSelect(array $primary = null, array $argv = [], string $as = ''): callable
    {

        return static function () use ($primary, $argv, $as): string {

            self::$allowSubSelectQueries = true;

            static::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $primary, $argv, true);

            $sql = self::buildSelectQuery($primary, $argv, true);

            $sql = "($sql)";

            if (!empty($as)) {
                $sql = "$sql AS $as";
            }

            static::completeRest(true);

            return $sql;

        };

    }

    /**
     * @param string|array $valueOne
     * @param string $operator
     * @param string|array $valueTwo
     * @return string
     * @throws PublicAlert
     */
    protected static function addSingleConditionToWhereOrJoin($valueOne, string $operator, $valueTwo): string
    {
        // js ajax get will automatically add _ where spaces exist
        $operator = str_replace('_', ' ', $operator);

        if (is_array($valueOne)) {

            if (self::isAggregateArray($valueOne)) {

                $valueOne = self::buildAggregate($valueOne);

            } else {

                throw new PublicAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate ($operator). (" . implode(',', $valueOne) . ")");

            }

            $key_is_custom = false;

        } else {

            $key_is_custom = false === self::validateInternalColumn($valueOne, $operator, $valueTwo);

        }

        switch ($operator) {

            case iRest::IN:
            case str_replace('_', ' ', iRest::NOT_IN):


            if ($key_is_custom) {

                    throw new PublicAlert("A non-internal column key was used in conjunction with the IN or NOT IN aggregate. addSingleConditionToWhereOrJoin was given (" . implode(',', func_get_args()) . ").");

                }

                return self::inAggergaation($valueOne, $operator, $valueTwo);

            case iRest::IS:

                if ($key_is_custom) {

                    throw new PublicAlert("A non-internal column key was used in conjunction with the IS aggregate. addSingleConditionToWhereOrJoin was given (" . implode(',', func_get_args()) . ").");

                }

                return self::isAggregate($valueOne, $valueTwo);

        }


        if (is_array($valueTwo)) {

            if (self::isAggregateArray($valueTwo)) {

                $valueTwo = self::buildAggregate($valueTwo);

            } else {

                throw new PublicAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate (" . implode(',', func_get_args()) . ")");

            }

            $value_is_custom = false;

        } else {

            $value_is_custom = false === self::validateInternalColumn($valueTwo, $operator, $valueOne);

        }

        if ($key_is_custom && $value_is_custom) {

            throw new PublicAlert("Rest failed in as you have custom columns ($valueOne) &| ($valueTwo). This may mean you need to regenerate your rest tables, have misspellings in your request, have incorrect aggregation, or join conditions. Please uses dedicated constants; modify your request and try again.");

        }

        if (false === $key_is_custom && false === $value_is_custom) {

            return "$valueOne $operator $valueTwo";

        }

        if ($value_is_custom) {

            if (self::$allowSubSelectQueries && strpos($valueTwo, '(SELECT ') === 0) {

                return "$valueOne $operator $valueTwo";

            }

            if (self::$compiled_PDO_validations[$valueOne][self::MYSQL_TYPE] === 'binary') {

                return "$valueOne $operator UNHEX(" . self::addInjection($valueTwo) . ")";

            }

            return "$valueOne $operator " . self::addInjection($valueTwo);

        }

        if (self::$compiled_PDO_validations[$valueTwo][self::MYSQL_TYPE] === 'binary') {

            return "$valueTwo $operator UNHEX(" . self::addInjection($valueOne) . ")";

        }

        return self::addInjection($valueOne) . " $operator $valueTwo";

    }


}

