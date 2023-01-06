<?php


namespace CarbonPHP;

use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Restful\RestLifeCycle;
use CarbonPHP\Tables\Carbons;
use PDO;
use Throwable;

abstract class Rest extends RestLifeCycle
{

    protected static function remove(array &$remove, array $argv, array $primary = null): bool
    {

        do {

            try {

                self::startRest(self::DELETE, $remove, $argv, $primary);

                $pdo = self::database(false);

                $emptyPrimary = null === $primary || [] === $primary;

                if (static::CARBON_CARBONS_PRIMARY_KEY && false === $emptyPrimary) {

                    $primary = is_string(static::PRIMARY)
                        ? $primary[static::PRIMARY]
                        : $primary;

                    return Carbons::Delete($remove, $primary, $argv);

                }

                if (false === self::$allowFullTableDeletes && true === $emptyPrimary && array() === $argv) {

                    return self::signalError('When deleting from restful tables a primary key or where query must be provided.');

                }

                $query_database_name = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';

                $table_name = $query_database_name . static::TABLE_NAME;

                if (static::CARBON_CARBONS_PRIMARY_KEY) {

                    if (is_array(static::PRIMARY)) {

                        throw new PublicAlert('Tables which use carbon for indexes should not have composite primary keys.');

                    }

                    $table_prefix = static::TABLE_PREFIX === Carbons::TABLE_PREFIX ? '' : static::TABLE_PREFIX;

                    $sql = self::DELETE . ' c FROM ' . $query_database_name . $table_prefix . 'carbon_carbons c JOIN ' . $table_name . ' on c.entity_pk = ' . static::PRIMARY;

                    if (false === self::$allowFullTableDeletes
                        || !empty($argv)) {

                        $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($argv);
                    }

                } else {

                    $sql = self::DELETE . ' FROM ' . $table_name . ' ';

                    if (false === self::$allowFullTableDeletes
                        && $emptyPrimary
                        && empty($argv)) {

                        return self::signalError('When deleting from restful tables a primary key or where query must be provided. This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                    }

                    // todo - loosen logic
                    if (is_array(static::PRIMARY)) {

                        $primaryIntersect = count(array_intersect(array_keys($primary), static::PRIMARY));

                        $primaryCount = count($primary);

                        $actualPrimaryCount = count(static::PRIMARY);

                        if ($primaryCount !== $primaryIntersect) {

                            return self::signalError('The keys provided to table ' . $table_name . ' was not a subset of (' . implode(', ', static::PRIMARY) . '). Only primary keys associated with the root table requested, thus not joined tables, are allowed.');

                        }

                        // todo - complex join logic
                        if (false === self::$allowFullTableDeletes
                            && $actualPrimaryCount !== $primaryIntersect
                            && $actualPrimaryCount !== count(array_intersect(array_keys($argv[self::WHERE] ?? $argv), static::PRIMARY))) {

                            return self::signalError('You must provide all primary keys ('
                                . implode(', ', static::PRIMARY)
                                . '). This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                        }

                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $argv = array_merge($argv, $primary ?? []);
                        // todo - this is a good point. were looping and running and array merge..

                    } elseif (is_string(static::PRIMARY) && !$emptyPrimary) {

                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $argv = array_merge($argv, $primary);

                    }

                    $where = self::buildBooleanJoinedConditions($argv);

                    $emptyWhere = empty($where);

                    if ($emptyWhere && false === self::$allowFullTableDeletes) {
                        return self::signalError('The where condition provided appears invalid.');
                    }

                    if (false === $emptyWhere) {
                        $sql .= ' WHERE ' . $where;
                    }
                }

                if (!$pdo->inTransaction()) {

                    $pdo->beginTransaction();

                }

                $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

                self::postpreprocessRestRequest($sql);

                $stmt = $pdo->prepare($sql);

                self::bind($stmt);

                if (!$stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute with error :: '
                        . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

                }

                if (is_callable($moreReporting)) {

                    $moreReporting = $moreReporting($stmt);

                }

                $remove = [];

                self::prepostprocessRestRequest($remove);

                if (self::$commit) {

                    if (false === Database::commit()) {


                        return self::signalError('Failed to store commit transaction on table {{TableName}}');

                    }

                    if (is_callable($moreReporting)) {

                        $moreReporting();

                    }

                }

                self::postprocessRestRequest($remove);

                self::completeRest();

                return true;

            } catch (Throwable $e) {

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    protected static function select(array &$return, array $argv, array $primary = null): bool
    {

        static $selectSQLs = [];

        do {

            try {

                self::startRest(self::GET, $return, $argv, $primary);

                $isLock = array_key_exists(self::LOCK, $argv);

                // If we need use table or row level locks we should use the main writer instance
                $pdo = self::database(false === $isLock);

                if ($isLock && false === $pdo->inTransaction() && false === $pdo->beginTransaction()) {

                    throw new PublicAlert('Failed to start transaction for select lock.');

                }

                if (null !== $primary && false === is_array($primary)) {

                    throw new PublicAlert('Looks like your restful validations changed the primary value to an invalid state.'
                        . ' The $primary field should be null or an array with the following syntax :: [ Table::EXAMPLE_COLUMN => "primary_key_string" ] '
                        . ' The value (' . json_encode($primary) . ') was instead received. ');

                }

                if (false === is_array($argv)) {

                    throw new PublicAlert('Looks like your restful validations changed the $argv value to an invalid state.'
                        . ' The $argv was not an array. Received :: (' . json_encode($argv) . ')');

                }

                $sql = self::buildSelectQuery($primary, $argv);

                self::jsonSQLReporting(func_get_args(), $sql);

                self::postpreprocessRestRequest($sql);

                $selectSQLs [] = $sql;

                $stmt = $pdo->prepare($sql);

                self::bind($stmt);

                if (!$stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute with error :: '
                        . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

                }

                $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (is_array(static::PRIMARY)) {

                    if ((null !== $primary && [] !== $primary)
                        || (isset($argv[self::PAGINATION][self::LIMIT])
                            && $argv[self::PAGINATION][self::LIMIT] === 1
                            && count($return) === 1)) {
                        $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
                    }

                } elseif (is_string(static::PRIMARY)) {

                    if ((null !== $primary && '' !== $primary)
                        || (isset($argv[self::PAGINATION][self::LIMIT])
                            && $argv[self::PAGINATION][self::LIMIT] === 1
                            && count($return) === 1)) {

                        $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;

                    }

                } else if (isset($argv[self::PAGINATION][self::LIMIT])
                    && $argv[self::PAGINATION][self::LIMIT] === 1
                    && count($return) === 1) {

                    $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;

                }

                foreach (static::JSON_COLUMNS as $key) {

                    if (array_key_exists($key, $return)) {

                        $return[$key] = json_decode($return[$key], true);

                    }

                }

                self::postprocessRestRequest($return);

                self::completeRest();

                return true;

            } catch (Throwable $e) {

                //print json_encode($selectSQLs, JSON_THROW_ON_ERROR); die;

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    protected static function updateReplace(array &$returnUpdated, array $argv = [], array $primary = null): bool
    {

        try {

            self::startRest(self::PUT, $returnUpdated, $argv, $primary);

            $replace = false;

            $where = [];

            if (array_key_exists(self::WHERE, $argv)) {

                $where = $argv[self::WHERE];

                unset($argv[self::WHERE]);
            }

            if (array_key_exists(self::REPLACE, $argv)) {

                $replace = true;

                $argv = $argv[self::REPLACE];

            } else if (array_key_exists(self::UPDATE, $argv)) {

                $argv = $argv[self::UPDATE];

            }

            if (null === static::PRIMARY) {

                if (false === self::$allowFullTableUpdates && [] === $where) {

                    return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No WHERE attribute given. To bypass this set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events, or just directly before this request.');

                }

                // todo - more validations on payload not empty
                if (empty($argv)) {

                    return self::signalError('Restful tables which have no primary key must be updated using conditions given to \$argv[self::WHERE] and values to be updated given to \$argv[self::UPDATE]. No UPDATE attribute given.');

                }

            } else {

                $emptyPrimary = null === $primary || [] === $primary;

                if (false === $replace
                    && false === self::$allowFullTableUpdates
                    && $emptyPrimary) {

                    return self::signalError('Restful tables which have a primary key must be updated by its primary key. To bypass this set you may set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events.');

                }

                if (is_array(static::PRIMARY)) {

                    if (false === self::$allowFullTableUpdates
                        || false === $emptyPrimary) {

                        $primary ??= [];

                        $primaryCount = count(static::PRIMARY);

                        $explicitPrimaryCount = count(array_intersect(array_keys($primary), static::PRIMARY));

                        if ($explicitPrimaryCount !== $primaryCount) {

                            $implicitPrimaryCount = count(array_intersect(array_keys($primary), static::PRIMARY));

                            if (($explicitPrimaryCount + $implicitPrimaryCount) !== $primaryCount) {

                                return self::signalError('You must provide all primary keys (' . implode(', ', static::PRIMARY) . ').');

                            }
                        }

                        $where = array_merge($argv, $primary);

                    }

                } elseif (!$emptyPrimary) {

                    $where = $primary;

                }
            }

            foreach ($argv as $key => &$value) {

                if (false === array_key_exists($key, self::$compiled_PDO_validations)) {

                    return self::signalError("Restful table could not update column $key, because it does not appear to exist. Please re-run RestBuilder if you believe this is incorrect.");

                }

                $op = self::EQUAL;

                if (false === self::validateInternalColumn($key, $op, $value)) {

                    return self::signalError("Your custom restful api validations caused the request to fail on column ($key).");

                }

            }
            unset($value);

            $update_or_replace = $replace ? self::REPLACE : self::UPDATE;

            $sql = $update_or_replace . ' '
                . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                . static::TABLE_NAME . ' SET ';

            $set = '';

            foreach ($argv as $fullName => $value) {

                $shortName = static::COLUMNS[$fullName];

                $set .= " $fullName = " .
                    ('binary' === self::$compiled_PDO_validations[$fullName][self::MYSQL_TYPE]
                        ? "UNHEX(:$shortName) ,"
                        : ":$shortName ,");

            }

            $sql .= substr($set, 0, -1);

            $pdo = self::database(false);

            if (false === $pdo->inTransaction() &&
                false === $pdo->beginTransaction()) {

                throw new PublicAlert('Failed to start a PDO transaction for the restful Put request!');

            }

            if (true === $replace) {

                if (!empty($where)) {

                    return self::signalError('Replace queries may not be given a where clause. Use Put instead.');

                }

            } else if (false === self::$allowFullTableUpdates || !empty($where)) {

                if (empty($where)) {
                    throw new PublicAlert('The where clause is required but has been detected as empty. Arguments were :: ' . json_encode(func_get_args(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
                }

                $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($where);

            }

            $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

            self::postpreprocessRestRequest($sql);

            $stmt = $pdo->prepare($sql);

            if (false === $stmt) {

                return self::signalError("PDO failed to prepare the sql generated! ($sql)");

            }

            foreach (static::COLUMNS as $fullName => $shortName) {

                if (array_key_exists($fullName, $argv)) {

                    $op = self::EQUAL;

                    if (false === self::validateInternalColumn($fullName, $op, $value)) {

                        return self::signalError("Your custom restful api validations caused the request to fail on column ($fullName).");

                    }

                    if ('' === static::PDO_VALIDATION[$fullName][self::MAX_LENGTH]) { // does length exist

                        $value = static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] === 'json'
                            ? json_encode($argv[$fullName])
                            : $argv[$fullName];


                        if (false === $stmt->bindValue(":$shortName", $value, static::PDO_VALIDATION[$fullName][self::PDO_TYPE])) {

                            return self::signalError("Failed to bind (:$shortName) with value ($value)");

                        }

                    } else if (false === $stmt->bindParam(":$shortName", $argv[$fullName],
                            static::PDO_VALIDATION[$fullName][self::PDO_TYPE],
                            (int)static::PDO_VALIDATION[$fullName][self::MAX_LENGTH])) {

                        return self::signalError("Failed to bind (:$shortName) with value ({$argv[$fullName]})");

                    }


                }

            }

            self::bind($stmt);

            if (false === $stmt->execute()) {

                self::completeRest();

                return self::signalError('The REST generated PDOStatement failed to execute with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            }

            if (is_callable($moreReporting)) {

                $moreReporting = $moreReporting($stmt);

            }

            $rowCount = $stmt->rowCount();

            if (0 === $rowCount) {

                return self::signalError("MySQL failed to find the target row during ($update_or_replace) on "
                    . 'table (' . static::TABLE_NAME . ") while executing query ($sql). By default CarbonPHP passes "
                    . 'PDO::MYSQL_ATTR_FOUND_ROWS => false, to the PDO driver; aka return the number of affected rows, '
                    . 'not the number of changed rows. Thus; if you have not manually updated these options, your issue is '
                    . 'the target row not existing.');

            }

            $argv = array_combine(
                array_map(
                    static fn($k) => str_replace(static::TABLE_NAME . '.', '', $k),
                    array_keys($argv)
                ),
                array_values($argv)
            );

            $returnUpdated = array_merge($returnUpdated, $argv);

            self::prepostprocessRestRequest($returnUpdated);

            if (self::$commit) {

                if (false === Database::commit()) {


                    return self::signalError('Failed to store commit transaction on table {{TableName}}');

                }

                if (is_callable($moreReporting)) {

                    $moreReporting();

                }

            }

            self::postprocessRestRequest($returnUpdated);

            self::completeRest();

            return true;

        } catch (Throwable $e) {

            self::handleRestException($e);

        }

        return false;

    }

    protected static function insert(array &$postRequestBody = [])
    {
        do {
            try {

                self::startRest(self::POST, [], $postRequestBody);

                // format the data as it multiple rows are to be posted at the same time
                if ([] !== $postRequestBody && true === self::has_string_keys($postRequestBody)) {

                    $postRequestBody = [

                        $postRequestBody

                    ];

                }

                // loop through each row of new values
                foreach ($postRequestBody as $iValue) {

                    // loop throw and validate each of the values // column names
                    foreach ($iValue as $columnName => &$postValue) {

                        if (false === array_key_exists($columnName, static::COLUMNS)) {

                            return self::signalError("Restful table (" . static::class . ") would not post column ($columnName), because the column does not appear to exist.");

                        }

                        if (true === static::AUTO_ESCAPE_POST_HTML_SPECIAL_CHARS) {

                                $postValue = is_string($postValue) ? htmlspecialchars($postValue, ENT_QUOTES | ENT_HTML5) : $postValue;

                        }

                    }
                    unset($postValue);

                }

                $keys = '';

                $pdo_values = $bound_values = [];

                $rowsToInsert = count($postRequestBody);

                $totalKeys = $i = 0;

                $firstKey = array_key_first($postRequestBody) ?? 0;

                $firstRowKeys = array_keys($postRequestBody[$firstKey] ?? []);

                do {

                    $pdo_values[$i] = '';

                    foreach (static::COLUMNS as $fullName => $shortName) {

                        $canSkip = static::PDO_VALIDATION[$fullName][self::SKIP_COLUMN_IN_POST] ?? false;

                        if (true === $canSkip
                            && false === in_array($fullName, $firstRowKeys, true)) {

                            continue;

                        }

                        if ($i === 0) {

                            $totalKeys++;

                            $keys .= "$shortName, ";

                        }

                        $shortName .= $i;

                        $bound_values[] = $shortName;

                        $pdo_values[$i] .= 'binary' === static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE] ? "UNHEX(:$shortName), " : ":$shortName, ";

                    }

                    $pdo_values[$i] = rtrim($pdo_values[$i], ', ');

                    ++$i;

                } while ($i < $rowsToInsert);

                if (0 === $totalKeys) {

                    return self::signalError('An unexpected error has occurred, please open a support ticket at https://github.com/RichardTMiles/CarbonPHP/issues.');

                }

                $sql = self::INSERT . ' INTO '
                    . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                    . static::TABLE_NAME . ' ('
                    . rtrim($keys, ', ')
                    . ') VALUES ('
                    . implode('), (', $pdo_values) . ')';

                $primaryBinary = is_string(static::PRIMARY) && 'binary' === static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE] ?? false;

                if ($primaryBinary) {

                    $pdo = self::database(false);

                    if (false === $pdo->inTransaction()) {

                        $pdo->beginTransaction();

                    }

                }

                $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

                self::postpreprocessRestRequest($sql);

                $pdo ??= self::database(false);

                $stmt = $pdo->prepare($sql);

                $op = self::EQUAL;

                $i = 0;

                do {

                    $postRequestBody[$i] ??= [];

                    $iValue = &$postRequestBody[$i];   // this allows you to get your binary keys if they were C6 enabled.

                    foreach (static::PDO_VALIDATION as $fullName => $info) {

                        $canSkip = $info[self::SKIP_COLUMN_IN_POST] ?? false;

                        if ($canSkip) {

                            $isExplicitlySet = array_key_exists($fullName, $iValue);

                            if (false === $isExplicitlySet) {

                                continue;

                            }

                            if (self::CURRENT_TIMESTAMP === ($info[self::DEFAULT_POST_VALUE] ?? '')) {

                                return self::signalError("The column ($fullName) is set to default to CURRENT_TIMESTAMP. The Rest API does not allow POST requests with columns explicitly set whose default is CURRENT_TIMESTAMP. You can remove to the default in MySQL or the column ($fullName) from the request.");

                            }

                        }

                        $shortName = static::COLUMNS[$fullName] . $i;

                        if (false === $key = array_search($shortName, $bound_values, true)) {

                            return self::signalError("An internal rest error has occurred where rest attempted binding ($shortName) which was in in the prepared sql ($sql)");

                        }

                        unset($bound_values[$key]);

                        if ($fullName === static::PRIMARY
                            || (is_array(static::PRIMARY)
                                && array_key_exists($fullName, static::PRIMARY))) {

                            $iValue[$fullName] ??= false;

                            if ($iValue[$fullName] === false) {

                                $iValue[$fullName] = static::CARBON_CARBONS_PRIMARY_KEY
                                    ? self::beginTransaction(self::class, $iValue[self::DEPENDANT_ON_ENTITY] ?? null)     // clusters should really use this
                                    : self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];

                            } else if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PublicAlert("The column value of ($fullName) caused custom restful api validations for (" . static::class . ") primary key to fail (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            /**
                             * I'm fairly confident the length attribute does nothing.
                             * @todo - hex / unhex length conversion on any binary data
                             * @link https://stackoverflow.com/questions/28251144/inserting-and-selecting-uuids-as-binary16
                             * @link https://www.php.net/ChangeLog-8.php
                             * @notice PDO type validation has a bug until 8
                             **/
                            $maxLength = $info[self::MAX_LENGTH] === '' ? null : (int)$info[self::MAX_LENGTH];

                            $stmt->bindParam(":$shortName",
                                $iValue[$fullName],
                                $info[self::PDO_TYPE],
                                $maxLength);

                        } elseif ('json' === $info[self::MYSQL_TYPE]) {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Table ('" . static::class . "') column ($fullName) is set to not null and has no default value. It must exist in the request and was not found in the one sent.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PublicAlert("Your tables ('" . static::class . "'), or joining tables, custom restful api validations caused the request to fail on json column ($fullName). Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            if (false === is_string($iValue[$fullName])) {

                                $json = json_encode($iValue[$fullName]);  // todo - is this over-validating?

                                if (false === $json && $iValue[$fullName] !== false) {

                                    return self::signalError("The column ($fullName) failed to be json encoded.");

                                }

                                $iValue[$fullName] = $json;

                                unset($json);

                            }

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } elseif (array_key_exists(self::DEFAULT_POST_VALUE, $info)) {

                            $iValue[$fullName] ??= $info[self::DEFAULT_POST_VALUE];

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE])) {

                                return self::signalError("Your custom restful table ('" . static::class . "') api validations caused the request to fail on column ($fullName)");

                            }

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } else {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Required argument ($fullName) is missing from the request to ('" . static::class . "') and has no default value.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], array_key_exists(self::DEFAULT_POST_VALUE, $info) ? $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE] : false)) {

                                return self::signalError("Your custom restful api validations for ('" . static::class . "') caused the request to fail on required column ($fullName).");

                            }

                            $stmt->bindParam(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        }
                        // end foreach bind
                    }

                    ++$i;

                } while ($i < $rowsToInsert);

                if ([] !== $bound_values) {

                    // todo - link support forums
                    return self::signalError("The insert query ($sql) did not receive values for (" . implode(', ', $bound_values) . '). This is not expected, please open a ticket so we can fix this at (' . Documentation::GIT_SUPPORT . ').');

                }

                if (false === $stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute for (' . static::class . '), with error :: ' . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR));

                }

                if (is_callable($moreReporting)) {

                    $moreReporting = $moreReporting($stmt);

                }

                # https://dev.mysql.com/doc/refman/5.6/en/information-functions.html#function_last-insert-id
                if (!(static::AUTO_INCREMENT_PRIMARY_KEY || $primaryBinary)) {

                    self::prepostprocessRestRequest();

                    if (self::$commit) {

                        if (false === Database::commit()) {


                            return self::signalError('Failed to store commit transaction on table {{TableName}}');

                        }

                        if (is_callable($moreReporting)) {

                            $moreReporting();

                        }

                    }

                    self::postprocessRestRequest();

                    self::completeRest();

                    return true;

                }

                if (static::AUTO_INCREMENT_PRIMARY_KEY) {

                    $postRequestBody[0][static::PRIMARY] = $id = $pdo->lastInsertId();

                    if (1 < $rowsToInsert) {

                        $GLOBALS['json'] ??= [];

                        $GLOBALS['json']['AUTO_INCREMENT_PRIMARY_KEY_WARNING'] = [
                            'background' => [
                                'Auto increment keys used indiscriminately are a waste of the primary key access which is always the fastest way to get to a row in a table.',
                                'Auto increment locks can and do impact concurrency and scalability of your database.',
                                'In an Highly Available replication environment using standard Async Replication, auto increment risks orphan rows during a master failure event.',
                                'If you are lucky enough to need to scale writes beyond a single server and end up having to shard auto increment no longer produces unique keys.',
                                '@quote @author John Schulz',
                                '@source @reference @link https://blog.pythian.com/case-auto-increment-mysql/',
                            ],
                            'C6' => 'CarbonPHP offers a scalable primary key solution using UUIDs. Please refer to the documentation.'
                        ];

                        $GLOBALS['json']['LAST_INSERT_ID_WARNING'] ??= [];

                        $GLOBALS['json']['LAST_INSERT_ID_WARNING'][] = "The first key ($id) is the primary key id of the first row inserted in the request. Please understand implications of sharded environments. Refer to link :: https://dev.mysql.com/doc/refman/5.6/en/information-functions.html#function_last-insert-id";

                    }

                } else {

                    $id = $postRequestBody[0][static::PRIMARY];

                }

                if (null === $id) {

                    return self::signalError("Failed to parse the id of the first inserted element after running ($sql); (" . json_encode($postRequestBody) . ')');

                }

                self::prepostprocessRestRequest($id);

                if (self::$commit) {

                    if (false === Database::commit()) {


                        return self::signalError('Failed to store commit transaction on table {{TableName}}');

                    }

                    if (is_callable($moreReporting)) {

                        $moreReporting();

                    }

                }

                self::postprocessRestRequest($id);

                self::completeRest();

                return $id;


            } catch (Throwable $e) {

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

}
