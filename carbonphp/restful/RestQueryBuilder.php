<?php

namespace CarbonPHP\Restful;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
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
     * @throws PrivateAlert
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function handleSubSelectAggregate(array $stmt): string
    {

        $tableName = $stmt[1];

        $tableName = explode('_', $tableName);      // table name semantics vs class name

        $tableName = array_map('ucfirst', $tableName);

        $tableName = implode('_', $tableName);

        if (false === self::$allowSubSelectQueries) {

            throw new PrivateAlert("Whoa, looks like a sub-select was encountered without `$tableName::\$allowSubSelectQueries` explicitly set to true. "
                . ' This is a security precaution and is required to only be set to true when explicitly needed. You should'
                . " consider doing this in the ($tableName::PHP_VALIDATION['PREPROCESS']) event cycle.");

        }

        if (false === defined("$tableName::CLASS_NAME")) {

            $tableNameFullTry = static::CLASS_NAMESPACE . $tableName;

            if (false === defined("$tableNameFullTry::CLASS_NAME")) {

                throw new PrivateAlert("The restful sub-select failed as the table ($tableName) did not have member const ($tableName::CLASS_NAME) under the (" . static::CLASS_NAMESPACE . ') namespace.');

            }

            $tableName = $tableNameFullTry;

        }

        $primaryField = $tableName::PRIMARY;

        $primary = [];

        $argv = [];

        $as = '';

        $setLength = count($stmt);

        if (true === self::is_assoc($stmt)) {   // todo - php 8.1 check when we gain support

            throw new PrivateAlert('The restful sub-select query has been detected to be an associative array.'
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

                        throw new PrivateAlert("The third argument passed to the restful sub-select was a not an array. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as].  The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    if (false === is_string($as)) {

                        throw new PrivateAlert("The fourth argument passed  to the restful sub-select was a not a string. The signature for tables without primary keys is as follows:: [ Rest::SELECT, '$tableName' , (array) \$argv, (string|optional) \$as]. The infringing set on table (" . static::class . ") :: (" . json_encode($stmt) . ")'");

                    }

                    break;

                default:

                    throw new PrivateAlert('The restful sub-select set passed was not the correct length. The '
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

                        throw new PrivateAlert("The third argument passed to the restful sub-select was a not a { null, string, int, or array }. " . $error_context($tableName));

                    }

                    if (false === is_array($argv)) {

                        throw new PrivateAlert("The fourth argument passed to the restful sub-select was a not an array. " . $error_context($tableName));

                    }

                    if (false === is_string($as)) {

                        throw new PrivateAlert("The fourth argument passed to the restful sub-select was a not a string. " . $error_context($tableName));

                    }

                    break;

                default:

                    throw new PrivateAlert('The restful sub-select set passed was not the correct length. (' . count($stmt) . ') '
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

            $injection = self::addInjection($as, [self::PDO_TYPE => null]);

            $sql = "$sql AS " . $injection;
        }

        $tableName::completeRest(true);

        return $sql;

    }

    /**
     * @throws PrivateAlert
     */
    public static function isAggregate(string $column, string $aggregate, $name): string
    {

        $allowedIsAggregations = [self::NULL, self::FALSE, self::TRUE, self::UNKNOWN];

        if (null === $name) {

            $name = self::NULL;

        }

        if ((true === is_string($name)) && in_array($name, $allowedIsAggregations, true)) {

            return "$column $aggregate $name";

        }

        throw new PrivateAlert("The aggregate IS used on column ($column) must be one of (" . implode(', ', $allowedIsAggregations) . ") exclusively; the value ($name) in incorrect.");

    }


    /**
     * @throws PrivateAlert
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
     * @throws PrivateAlert
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

                throw new PrivateAlert('Expected the CONVERT_TZ aggregate function as four arguments were encountered.');

            }

            return $aggregate .
                "(" . self::validateInternalOrAddInjection($time, $aggregate, $zoneOne, $zoneTwo) . ","
                . self::validateInternalOrAddInjection($zoneOne, $aggregate, $time, $zoneTwo) . ','
                . self::validateInternalOrAddInjection($zoneTwo, $aggregate, $time, $zoneOne) . ')';

        }

        if (5 === $stmtCount) {
            // should be specific to GROUP_CONCAT (for now)
            [$aggregate, $column, $name, $sortColumn, $sortType] = $stmt;

            if ($aggregate !== self::GROUP_CONCAT) {
                throw new PrivateAlert('Expected the GROUP_CONCAT aggregate function with custom ordering as five arguments were encountered.');
            }
        } elseif (3 === $stmtCount) {

            [$column, $aggregate, $name] = $stmt;

            switch ($aggregate) {
                case self::IS_NOT:
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

                throw new PrivateAlert('A Restful array value in the aggregation must be at least two values: array( $aggregate, $column [, optional: $as] ) ');

            }

            [$aggregate] = $stmt;

            if (self::NOW !== $aggregate) {

                throw new PrivateAlert("Restful request encountered an ($aggregate) aggregate function as the only member of the array.");

            }

            return $aggregate . '()';

        }

        $allowedValues = [...self::AGGREGATES, ...self::OPERATORS];

        if (false === in_array($aggregate, $allowedValues, true)) {

            throw new PrivateAlert('The attempted aggregate (' . $aggregate . ') in query ( ' . json_encode($stmt) . ') in the GET request must be one of the following: '
                . implode(', ', $allowedValues));

        }

        if ($aggregate === self::CONCAT) {

            if (false === is_array($column) || empty($column)) {

                throw new PrivateAlert("Aggregation failed for $aggregate. Arguments must be a sub-array. ($column)");

            }

            $returnIsAggregate = [];

            foreach ($column as $item) {

                $returnIsAggregate [] = self::validateInternalOrAddInjection($item, $aggregate, $column);

            }

            return self::CONCAT . '(' . implode(',', $returnIsAggregate) . ')';

        }


        if (false === self::validateInternalColumn($column, $aggregate)) {

            /** @noinspection JsonEncodingApiUsageInspection */
            throw new PrivateAlert("The column value of stmt (" . json_encode($stmt) . ") caused validateInternalColumn to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

        }

        switch ($aggregate) {
            case self::NOT_IN:
            case self::IN:

                return self::inAggergaation($column, $aggregate, $name);

            case self::IS_NOT:
            case self::IS:

                return self::isAggregate($column, $aggregate, $name);

        }

        if ('' !== $name) {

            // this is just a technicality as name could be injected but also referenced in the query
            $name = self::addInjection($name, [self::PDO_TYPE => null]);

        }

        switch ($aggregate) {

            case self::AS:

                if (empty($name)) {

                    throw new PrivateAlert("The third argument provided to the ($aggregate) select aggregate must not be empty and be a string.");

                }

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "HEX($column) $aggregate $name";

                }

                return "$column $aggregate $name";

            case self::GROUP_CONCAT:

                if (empty($name)) {

                    $name = self::$compiled_valid_columns[$column];

                }

                if (empty($name)) {

                    throw new PrivateAlert('The `name` argument provided to (' . self::GROUP_CONCAT . ') was empty!');

                }

                $orderByCol = $sortColumn ?? $column;

                $orderByType = $sortType ?? self::ASC;

                if (!$isSubSelect && self::$compiled_PDO_validations[$column][self::MYSQL_TYPE] === 'binary') {

                    return "GROUP_CONCAT(DISTINCT HEX($column) ORDER BY $orderByCol $orderByType SEPARATOR ',') AS $name";

                }

                return "GROUP_CONCAT(DISTINCT($column) ORDER BY $orderByCol $orderByType SEPARATOR ',') AS $name";

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

            throw new PrivateAlert('Rest IN aggregate error (' . $column . ') required an array!');

        }

        $returnIsAggregate = [];

        foreach ($values as $item) {

            if (is_array($item)) {

                if (self::isSubSelectAggregation($item)) {

                    // todo - it is unclear if this is more an aggregate or column with respect to the group by
                    $returnIsAggregate [] = self::handleSubSelectAggregate($item);

                } else {

                    throw new PrivateAlert('Failed to parse IN aggregate. An array was passed with did not pass the isSubSelectAggregation test! (' . print_r($item, true) . ')');

                }

            } else {

                $returnIsAggregate [] = self::validateInternalOrAddInjection($item, $aggregate, $column);
            }

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

                // todo - wha even is this?
                // cascaded delete but in a way that history triggers log; I want to remove this feature
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

            ColorCode::colorCode('PHP file_put_contents failed to store (' . CarbonPHP::$app_root . 'trigger.sql)', iColorCode::RED);

            return;

        }

        MySQL::MySQLSource(CarbonPHP::$app_root . 'trigger.sql');

        unlink(CarbonPHP::$app_root . 'trigger.sql');

    }

    public static function addInjection($value, array $pdo_column_validation = null): string
    {
        switch ($pdo_column_validation[self::PDO_TYPE] ?? null) {

            /** @noinspection PhpMissingBreakStatementInspection */
            default:

                // todo - cache db better (startRest?)
                $value = (Database::database(true))->quote($value);        // boolean, string

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

        return $inject;

    }

    /**
     * only get, put, and delete
     * @param array $where
     * @param array|null $primary
     * @return string
     * @throws PrivateAlert
     */
    private static function buildQueryWhereValues(array $where, array $primary = null): string
    {

        if (null === $primary) {

            if ([] === $where) {

                switch (self::$REST_REQUEST_METHOD) {

                    default:

                        throw new PrivateAlert('An unexpected request (' . self::$REST_REQUEST_METHOD . ') method was given to (' . static::class . ').');

                    case self::GET:

                        return '';

                    case self::PUT:

                        if (false === self::$allowFullTableUpdates) {

                            return '';

                        }

                        throw new PrivateAlert('Rest expected a primary key or valid where statement but none was provided during a (PUT) request on (' . static::class . ')');

                    case self::DELETE:

                        if (false === self::$allowFullTableDeletes) {

                            return '';

                        }

                        throw new PrivateAlert('Rest expected a primary key or valid where statement but none was provided during a (DELETE) request on (' . static::class . ')');


                    case self::POST:

                        throw new PrivateAlert('The post method was detected while running buildQueryWhereValues against table (' . static::class . ')');


                }

            }

            return ' WHERE ' . self::buildBooleanJoinedConditions($where);

        }

        if (empty(static::PRIMARY)) {

            throw new PrivateAlert('Primary keys given during a request to (' . static::class . ') which does not have a primary key. Please regenerate this class using RestBuilder.');

        }

        if (is_string(static::PRIMARY)) {

            if ([] !== $where && '' !== ($primary[static::PRIMARY] ?? '')) {

                throw new PrivateAlert('Restful tables with a single primary key must not have WHERE values passed when the primary key is given. Table (' . static::class . ') was passed a non empty key `WHERE` (' . json_encode($where, JSON_PRETTY_PRINT) . ') to the arguments of GET.');

            }

            if ('binary' === (static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE])) {

                return ' WHERE ' . static::PRIMARY . "=UNHEX(" . self::addInjection($primary[static::PRIMARY]) . ') ';

            }

            return ' WHERE ' . static::PRIMARY . "=" . self::addInjection($primary[static::PRIMARY], static::PDO_VALIDATION[static::PRIMARY]) . ' ';

        }

        if (is_array(static::PRIMARY)) {

            if (count($primary) !== count(static::PRIMARY)) {

                throw new PrivateAlert('The table ' . static::class . ' was passed a subset of the required primary keys. When passing primary keys all must be present.');

            }

            $primaryKeys = array_keys($primary);

            if (!empty(array_intersect_key($primaryKeys, static::PRIMARY))) {

                throw new PrivateAlert('The rest table ' . static::class . ' was passed the correct number of primary keys; however, the associated keys did not match the static::PRIMARY attribute.');

            }

            if (!empty($where)) {

                throw new PrivateAlert('Restful tables selecting with primary keys must not have WHERE values passed when the primary keys are given. Table ' . static::class . ' was passed a non empty key `WHERE` to the arguments of GET.');

            }

            return ' WHERE ' . self::buildBooleanJoinedConditions($primary);

        }

        throw new PrivateAlert('An unexpected error occurred while paring your primary key in ' . static::class . '. static::PRIMARY may only be a string, array, or null. You may need to regenerate with RestBuilder.');

    }

    private static function buildQueryGroupByValues(array $group, string $sql): string
    {

        // concatenation and aggregation
        if (true === self::$columnSelectEncountered && true === self::$aggregateSelectEncountered && [] === $group) {

            throw new PrivateAlert("Restful Error! A simple column select and aggregate function were used in the same query without the ['GROUP_BY'] clause explicitly set. This has been deprecated. Failed after compiling only :: ($sql)");

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

                throw new PrivateAlert('Select values must only use numeric keys. The key (' . $key . ') was encountered.');

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
            if ($wasCallable && str_starts_with($column, '(SELECT ')) {

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
                throw new PrivateAlert('C6 Rest client could not validate a column in the GET:[] request.');

            }

            // todo - update this syntax allow for remote sub select using [ buildAggregate ]
            if (self::$allowSubSelectQueries && str_starts_with($column, '(SELECT ')) {

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

            throw new PrivateAlert("CarbonPHP could not validate a column ($column) passed in the (SELECT) request which caused "
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

                    throw new PrivateAlert("A non numeric LIMIT ($limit) was provided to REST while querying against (" . static::class . ').');

                }

                // $limit = self::addInjection($limit, [self::PDO_TYPE => null]);

                if (0 > (int)$limit) {

                    throw new PrivateAlert('A negative limit was encountered in the rest Builder while querying against (' . static::class . ').');

                }

                if (array_key_exists(self::PAGE, $argv[self::PAGINATION])) {

                    if ($argv[self::PAGINATION][self::PAGE] < 1) {

                        return static::signalError('The body of PAGINATION requires that PAGE attribute be no less than 1.');

                    }

                    $limit = ' LIMIT ' . (($argv[self::PAGINATION][self::PAGE] - 1) * $limit) . ',' . $limit;

                } else {

                    $limit = ' LIMIT ' . $limit;

                }

            }

            if ('' === $limit ||
                array_key_exists(self::ORDER, $argv[self::PAGINATION])) {

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

                            throw new PrivateAlert('The syntax used in the order by must be array( $key => $value ) pairs; not a comma `,` separated list. The (' . json_encode($argv[self::PAGINATION][self::ORDER]) . ') argument passed is not correct.');

                        }

                        $orderArray = [];

                        foreach ($argv[self::PAGINATION][self::ORDER] as $item => $sort) {

                            if (false === in_array($sort, [self::ASC, self::DESC], true)) {

                                throw new PrivateAlert('Restful order by failed to validate sorting method. The value should be one of (' . json_encode([self::ASC, self::DESC]) . ')');

                            }

                            if (false === self::validateInternalColumn($item, $sort)) {

                                throw new PrivateAlert("The column value ($item) caused your custom validations to fail. Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            $orderArray[] = "$item $sort";

                        }

                        $order .= implode(', ', $orderArray);

                        unset($orderArray);

                    } else {

                        throw new PrivateAlert('Rest query builder failed during the order pagination.');

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

                    throw new PrivateAlert('An error was detected in your REST syntax regarding the limit. No order by clause was provided and no primary keys were detected in the main joining table to automagically set.');

                } else {

                    throw new PrivateAlert('A unknown Restful error was encountered while compiling the order by statement.');

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
     * @link https://dev.mysql.com/doc/refman/8.0/en/select.html
     * @param array|null $primary
     * @param array $argv
     * @param bool $isSubSelect
     * @return string
     * @throws PrivateAlert
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

                    /** @noinspection JsonEncodingApiUsageInspection */
                    throw new PrivateAlert("The restful join field (" . json_encode($argv) . ") passed to (" . static::class . ") must be an array.");
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

        //
        // https://dev.mysql.com/worklog/task/?id=3597
        // SELECT * FROM tab1 WHERE col1 = 1 FOR UPDATE NOWAIT;
        // SELECT * FROM tab1 WHERE col1 = 1 FOR UPDATE NOWAIT SKIP LOCKED;
        //public const LOCK = 'LOCK';
        //public const FOR_SHARE = 'FOR_SHARE';
        //public const FOR_UPDATE = 'FOR_UPDATE';
        //public const NOWAIT = 'NOWAIT';
        //public const SKIP_LOCKED = 'SKIP_LOCKED';


        // https://dev.mysql.com/blog-archive/mysql-8-0-1-using-skip-locked-and-nowait-to-handle-hot-rows/
        // SELECT seat_no
        // FROM seats JOIN seat_rows USING ( row_no )
        // WHERE seat_no IN (3,4) AND seat_rows.row_no IN (12)
        // AND booked = 'NO'
        // FOR UPDATE OF seats SKIP LOCKED
        // FOR SHARE OF seat_rows NOWAIT;

        $sql .= self::buildSelectLockStatement($argv);

        return $sql;
    }


    /**
     * @throws PrivateAlert
     */
    public static function addSimpleLock(string $lockAggregateValue): string
    {
        // todo - verify // remove NOWAIT & SKIP_LOCKED as positionally not allowed?
        // [LIMIT {[offset,] row_count | row_count OFFSET offset}]
        //    [into_option]
        //    [FOR {UPDATE | SHARE}
        //        [OF tbl_name [, tbl_name] ...]
        //        [NOWAIT | SKIP LOCKED]
        //      | LOCK IN SHARE MODE]
        //    [into_option]

        return match ($lockAggregateValue) {
            self::NOWAIT, self::FOR_SHARE, self::FOR_UPDATE, self::SKIP_LOCKED => ' ' . str_replace('_', ' ', $lockAggregateValue),
            default => throw new PrivateAlert('A SELECT LOCK which was not one of (NOWAIT, FOR_SHARE, FOR_UPDATE, or SKIP_LOCKED) was encounter. The value (' . $lockAggregateValue . ') is incorrect.'),
        };
    }

    /**
     * @throws PrivateAlert
     */
    public static function buildSelectLockStatement(array $argv): string
    {

        if (false === array_key_exists(self::LOCK, $argv)) {

            return '';

        }

        if (self::$externalRestfulRequestsAPI) {

            throw new PrivateAlert('A SELECT LOCK was supplied for an external request!');

        }

        if (false === is_array($argv[self::LOCK])) {

            if (false === is_string($argv[self::LOCK])) {

                throw new PrivateAlert('Failed to parse LOCK argument passed to REST. An array or string is required! The value (' . json_encode($argv) . ') was given.');

            }

            // this should be like 99% of the time
            return self::addSimpleLock($argv[self::LOCK]);

        }

        $lockArgc = count($argv[self::LOCK]);

        if (0 === $lockArgc) {

            throw new PrivateAlert('An empty array was passed to rest in the LOCK parameter!');

        }


        // SELECT seat_no
        // FROM seats JOIN seat_rows USING ( row_no )
        // WHERE seat_no IN (3,4) AND seat_rows.row_no IN (12)
        // AND booked = 'NO'

        $lockStatement = '';
        // FOR UPDATE OF seats SKIP LOCKED
        // FOR SHARE OF seat_rows NOWAIT;


        do {

            $specificTableLockOnJoin = array_shift($argv[self::LOCK]);

            if (false === is_array($specificTableLockOnJoin)) {

                throw new PrivateAlert('Either a single string or array of arrays must be passed to the LOCK.');

            }


        } while (false === empty($argv[self::LOCK]));

        return $lockStatement;

    }

    public static function isArrayOfStrings(array $argv): bool
    {

        foreach ($argv as $value) {
            if (false === is_string($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws PrivateAlert
     */
    public static function addSingleConditionToLock(array $specificTableLockOnJoin): string
    {

        if (false === self::isArrayOfStrings($specificTableLockOnJoin)) {

            /** @noinspection JsonEncodingApiUsageInspection */
            throw new PrivateAlert('The LOCK array (' . json_encode($specificTableLockOnJoin) . ') must be comprised of only strings at this point!');

        }

        $lockArgc = count($specificTableLockOnJoin);

        switch ($lockArgc) {
            default:
                throw new PrivateAlert('The LOCK parameter passed contained (' . $lockArgc . ') elements, between 1 and 3 must be provided.');

            case 0:
                throw new PrivateAlert('The LOCK parameter passed was an empty array!');

            case 1:

                $singleTableLockOnJoin = array_shift($specificTableLockOnJoin);

                return self::addSimpleLock($singleTableLockOnJoin);

            case 2:

                [$ShareOrUpdate, $NoWaitOrSkip] = $specificTableLockOnJoin;

                switch ($ShareOrUpdate) {
                    case self::FOR_SHARE:
                    case self::FOR_UPDATE:
                        break;
                    default:
                        throw new PrivateAlert('A SELECT LOCK which was not one of (FOR_SHARE or FOR_UPDATE) was encounter. The value (' . $ShareOrUpdate . ') is incorrect.');
                }

                return match ($NoWaitOrSkip) {
                    self::NOWAIT, self::SKIP_LOCKED => $ShareOrUpdate . ' ' . $NoWaitOrSkip,
                    default => throw new PrivateAlert('A SELECT LOCK value which was not one of (NOWAIT or SKIP_LOCKED) was encounter. The value (' . $NoWaitOrSkip . ') is incorrect.'),
                };

            case 3:

                /** @noinspection SuspiciousAssignmentsInspection */
                [$ShareOrUpdate, $tableToLock, $NoWaitOrSkip] = $specificTableLockOnJoin;

                switch ($ShareOrUpdate) {
                    case self::FOR_SHARE:
                    case self::FOR_UPDATE:
                        break;
                    default:
                        throw new PrivateAlert('A SELECT LOCK which was not one of (FOR_SHARE or FOR_UPDATE) was encounter. The value (' . $ShareOrUpdate . ') is incorrect.');
                }

                $allTables = [...self::$join_tables, static::TABLE_NAME];

                if (false === in_array($tableToLock, $allTables, true)) {

                    /** @noinspection JsonEncodingApiUsageInspection */
                    throw new PrivateAlert("A LOCK argument was incorrect, the value ($tableToLock) was expected to be one of (" . json_encode($allTables) . ")");

                }

                return match ($NoWaitOrSkip) {
                    self::NOWAIT, self::SKIP_LOCKED => $ShareOrUpdate . ' OF ' . $tableToLock . ' ' . $NoWaitOrSkip,
                    default => throw new PrivateAlert('A SELECT LOCK value which was not one of (NOWAIT or SKIP_LOCKED) was encounter. The value (' . $NoWaitOrSkip . ') is incorrect.'),
                };

        }

    }


    /**
     * @throws PrivateAlert
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

                throw new PrivateAlert('The restful inner join had an unknown error.'); // todo - message

            }

            $by = str_replace("_", " ", $by);

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PrivateAlert('A table ' . $JoiningClass . ' provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');

                }

                $imp = array_map('strtolower', array_keys(class_implements($JoiningClass)));

                if (false === in_array(strtolower(iRestMultiplePrimaryKeys::class), $imp, true)
                    && false === in_array(strtolower(iRestSinglePrimaryKey::class), $imp, true)
                    && false === in_array(strtolower(iRestNoPrimaryKey::class), $imp, true)
                ) {

                    throw new PrivateAlert('Rest error, class/table exists in the restful generation folder which does not implement the correct interfaces. Please re-run rest generation.');

                }

                if (false === is_array($stmt)) {

                    throw new PrivateAlert("Rest error in the join stmt, the value of $JoiningClass is not an array.");

                }

            }

            foreach ($tables as $class => $stmt) {

                $class = ucwords($class, '_');

                if (false === class_exists($JoiningClass = static::CLASS_NAMESPACE . $class)
                    && false === class_exists($JoiningClass = static::CLASS_NAMESPACE
                        . preg_replace('/^' . preg_quote(static::TABLE_PREFIX, '/') . '/i', '', $class))
                ) {

                    throw new PrivateAlert('A table (' . $JoiningClass . ') provided in the Restful join request was not found in the system. This may mean you need to auto generate your restful tables in the CLI.');
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
     * @throws PrivateAlert
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

                    throw new PrivateAlert('An empty array was passed as a leaf member of a condition. Nested arrays recursively flip the AND vs OR joining conditional. Multiple conditions are not required as only one condition is needed. Two may be asserted equal implicitly by placing them in positions one and two of the array. An explicit definition may see the second $position[1] equal one of  (' . implode('|', $supportedOperators) . ')');

                case 1:

                    $key = array_key_first($set);

                    if (is_array($set[$key])) {

                        $set[$key] = json_encode($set[$key]);

                    }

                    throw new PrivateAlert("The single value ({$set[$key]}) has the numeric array key [$key] which could imply a conditional equality; however, numeric keys (implicit or explicit) to imply equality are not allowed in C6. Please reorder the condition to have a string key and numeric value.");

                case 2:

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], self::EQUAL, $set[1]);

                    break;

                case 3:

                    if (false === in_array($set[1], $supportedOperators, true)) { // ie #^=|>=|<=$#

                        throw new PrivateAlert("Restful column joins may only use one of the following (" . implode('|', $supportedOperators) . ") the value ({$set[1]}) is not supported. ({$set[0]}) ({$set[1]}) ({$set[2]})");

                    }

                    $sql .= self::addSingleConditionToWhereOrJoin($set[0], $set[1], $set[2]);

                    break;

                default:

                    throw new PrivateAlert('An array containing all numeric keys was found with (' . count($set) . ') values. Only exactly two or three values maybe given.');

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

                        throw new PrivateAlert("Rest boolean condition builder expected ($value) to be an array as it has a numeric key in the set :: " . json_encode($set));

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
                    case 2:

                        if (false === in_array($value[0], $supportedOperators, true)) { // ie #^=|>=|<=$#

                            throw new PrivateAlert("Table (" . static::class . ") restful column joins may only use one of the following supported operators (" . implode('|', $supportedOperators) . "). Value ({$value[0]}) is not supported. (" . json_encode($value) . ")");

                        }

                        $sql .= self::addSingleConditionToWhereOrJoin($column, $value[0], $value[1]);

                        break;

                    case 1:

                        // this would be odd syntax but I will allow it
                        if (true === array_key_exists(0, $value)
                            && true === is_array($value[0])
                            && true === self::isAggregateArray($value[0])) {

                            $sql .= self::addSingleConditionToWhereOrJoin($column, self::EQUAL, $value[0]);

                            break;

                        }

                    // fall through
                    default:


                        throw new PrivateAlert("A rest key column ($column) value (" . json_encode($value) . ") was set to an array with ($count) "
                            . "values. Boolean comparisons can use one of the following operators "
                            . "(" . implode('|', $supportedOperators) . "). The same comparison can be made with an empty (aka default numeric) key "
                            . "and three array entries :: [  Column,  Operator, (Operand|Column2) ]. Both ways are made equally "
                            . "for conditions which might require multiple of the same key in the same array; as this would be invalid syntax in php.");


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

            static::startRest(self::GET, self::$REST_REQUEST_PARAMETERS, $argv, $primary, true);

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
     * @throws PrivateAlert
     */
    protected static function addSingleConditionToWhereOrJoin($valueOne, string $operator, $valueTwo): string
    {
        // js ajax get will automatically add _ where spaces exist
        $operator = str_replace('_', ' ', $operator);

        if (is_array($valueOne)) {

            if (self::isAggregateArray($valueOne)) {

                $valueOne = self::buildAggregate($valueOne);

            } else {

                throw new PrivateAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate ($operator). (" . implode(',', $valueOne) . ")");

            }

            $key_is_custom = false;

        } else {

            $key_is_custom = false === self::validateInternalColumn($valueOne, $operator, $valueTwo);

        }

        if (self::EQUAL === $operator && null === $valueTwo) {

            $operator = self::IS;

        }

        switch ($operator) {

            case iRest::IN:
            case str_replace('_', ' ', iRest::NOT_IN):

                if ($key_is_custom) {

                    throw new PrivateAlert("A non-internal column key was used in conjunction with the IN or NOT IN aggregate. addSingleConditionToWhereOrJoin was given (" . implode(',', func_get_args()) . "). Possible allowed columns are (" . implode(',', self::$compiled_valid_columns) . ").");

                }

                return self::inAggergaation($valueOne, $operator, $valueTwo);

            case iRest::IS:
            case str_replace('_', ' ', iRest::IS_NOT):


                if ($key_is_custom) {

                    throw new PrivateAlert("A non-internal column key was used in conjunction with the IS aggregate. addSingleConditionToWhereOrJoin was given (" . implode(',', func_get_args()) . ").");

                }

                return self::isAggregate($valueOne, $operator, $valueTwo);

        }


        if (is_array($valueTwo)) {

            if (self::isAggregateArray($valueTwo)) {

                $valueTwo = self::buildAggregate($valueTwo);

            } else {

                throw new PrivateAlert("Restful error! While trying to add a single condition an array was encountered which was not a valid Aggregate (" . implode(',', func_get_args()) . ")");

            }

            $value_is_custom = false;

        } else {

            $value_is_custom = false === self::validateInternalColumn($valueTwo, $operator, $valueOne);

        }

        if ($key_is_custom && $value_is_custom) {

            throw new PrivateAlert("Rest failed in as you have custom columns ($valueOne) &| ($valueTwo). This may mean you need to regenerate your rest tables, have misspellings in your request, have incorrect aggregation, or join conditions."
                . " Possible allowed columns are (" . implode(',', self::$compiled_valid_columns) . ").");

        }

        if (false === $key_is_custom && false === $value_is_custom) {

            return "$valueOne $operator $valueTwo";

        }

        if ($value_is_custom) {

            if (self::$allowSubSelectQueries && str_starts_with($valueTwo, '(SELECT ')) {

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

