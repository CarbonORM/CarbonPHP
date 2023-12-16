<?php


namespace CarbonPHP;

use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Restful\RestfulValidations;
use CarbonPHP\Restful\RestLifeCycle;
use CarbonPHP\Tables\Carbons;
use PDO;
use PDOStatement;
use Throwable;

abstract class Rest extends RestLifeCycle
{

    protected static function remove(array &$remove, array $argv, array $primary = null): bool
    {

        do {

            try {

                self::startRest(self::DELETE, $remove, $argv, $primary);

                if (1 <= count($argv)
                    && false === array_key_exists(self::WHERE, $argv)
                    && false === array_key_exists(self::JOIN, $argv)) {

                    $argv = [
                        self::WHERE => $argv
                    ];

                }

                $argv[self::WHERE] ??= [];

                $pdo = self::database(false);

                $noPrimaryKeyProvided = null === $primary || [] === $primary;

                if (false === self::$allowFullTableDeletes && true === $noPrimaryKeyProvided && [] === $argv[self::WHERE]) {

                    return self::signalError('When deleting from restful tables a primary key or where query must be provided.');

                }

                $query_database_name = static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '';

                $table_name = $query_database_name . static::TABLE_NAME;

                // We must not directly delete from the carbon table.
                // We must delete from the restful table to ensure the pk actually exists on the correct table.
                // A small but critical validation.
                if (static::CARBON_CARBONS_PRIMARY_KEY) {

                    if (is_array(static::PRIMARY)) {

                        return self::signalError('Tables which use carbon for indexes should not have composite primary keys.');

                    }

                    $table_prefix = static::TABLE_PREFIX === Carbons::TABLE_PREFIX ? '' : static::TABLE_PREFIX;

                    $sql = self::DELETE . ' c FROM ' . $query_database_name . $table_prefix . 'carbon_carbons c JOIN ' . $table_name . ' on c.entity_pk = ' . static::PRIMARY;

                    // todo - this is done three times in this function depending on if stmt, can we abstract further up?
                    $argv[self::WHERE] = array_merge($argv[self::WHERE], $primary ?? []);

                    if (false === self::$allowFullTableDeletes) {

                        if (empty($argv[self::WHERE])) {

                            return self::signalError('When deleting from restful tables a primary key or where query must be provided. This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                        }

                        $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($argv[self::WHERE]);

                    }

                } else {

                    $sql = self::DELETE . ' FROM ' . $table_name . ' ';

                    if (false === self::$allowFullTableDeletes
                        && $noPrimaryKeyProvided
                        && empty($argv[self::WHERE])) {

                        return self::signalError('When deleting from restful tables a primary key or where query must be provided. This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                    }

                    // todo - loosen logic
                    if (is_array(static::PRIMARY)) {

                        $primary ??= [];

                        $primaryIntersect = count(array_intersect(array_keys($primary), static::PRIMARY));

                        $primaryCount = count($primary);

                        $actualPrimaryCount = count(static::PRIMARY);

                        // todo - is this really needed?
                        if ($primaryCount !== $primaryIntersect) {

                            return self::signalError('The keys provided to table ' . $table_name . ' was not a subset of (' . implode(', ', static::PRIMARY) . '). Only primary keys associated with the root table requested, thus not joined tables, are allowed.');

                        }

                        // todo - complex join logic
                        if (false === self::$allowFullTableDeletes
                            && $actualPrimaryCount !== $primaryIntersect
                            && $actualPrimaryCount !== count(array_intersect(array_keys($argv[self::WHERE]), static::PRIMARY))) {

                            return self::signalError('You must provide all primary keys ('
                                . implode(', ', static::PRIMARY)
                                . '). This can be disabled by setting `self::\$allowFullTableDeletes = true;` during the PREPROCESS events, or just directly before this request.');

                        }

                        $argv[self::WHERE] = array_merge($argv[self::WHERE], $primary ?? []);
                        // todo - this is a good point. were looping and running and array merge..

                    } elseif (is_string(static::PRIMARY) && !$noPrimaryKeyProvided) {

                        $argv[self::WHERE] = array_merge($argv[self::WHERE], $primary);

                    }

                    $where = self::buildBooleanJoinedConditions($argv[self::WHERE]);

                    $emptyWhere = empty($where);

                    if ($emptyWhere && false === self::$allowFullTableDeletes) {
                        return self::signalError('The where condition provided appears invalid.');
                    }

                    if (false === $emptyWhere) {
                        $sql .= ' WHERE ' . $where;
                    }
                }

                self::beginTransaction();

                $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

                RestfulValidations::validateGeneratedExternalSqlRequest($sql);

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

                if (false === self::verifyRowsAffected($stmt, self::$REST_REQUEST_METHOD, $sql, $primary, $argv)) {

                    return false;

                }

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

                $remove = [];

                self::completeRest();

                return true;

            } catch (Throwable $e) {

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    protected static function select(mixed &$return, array $argv, array $primary = null, ...$fetchOptions): bool
    {

        static $selectSQLs = [];

        do {

            try {

                self::startRest(self::GET, $return, $argv, $primary);

                $isLock = array_key_exists(self::LOCK, $argv);

                // If we need use table or row level locks we should use the main writer instance
                $pdo = self::database(false === $isLock);

                if ($isLock) {

                    self::beginTransaction();

                }

                if (null !== $primary && false === is_array($primary)) {

                    throw new PrivateAlert('Looks like your restful validations changed the primary value to an invalid state.'
                        . ' The $primary field should be null or an array with the following syntax :: [ Table::EXAMPLE_COLUMN => "primary_key_string" ] '
                        . ' The value (' . json_encode($primary) . ') was instead received. ');

                }

                if (false === is_array($argv)) {

                    throw new PrivateAlert('Looks like your restful validations changed the $argv value to an invalid state.'
                        . ' The $argv was not an array. Received :: (' . json_encode($argv) . ')');

                }

                $sql = self::buildSelectQuery($primary, $argv);

                $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

                RestfulValidations::validateGeneratedExternalSqlRequest($sql);

                self::postpreprocessRestRequest($sql);

                $selectSQLs [] = $sql;

                $stmt = $pdo->prepare($sql);

                self::bind($stmt);

                if (!$stmt->execute()) {

                    self::completeRest();

                    return self::signalError('The REST generated PDOStatement failed to execute with error :: '
                        . json_encode($stmt->errorInfo(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

                }

                if (is_callable($moreReporting)) {

                    $moreReporting($stmt);

                }

                $fetchOptions = empty($fetchOptions) ? [PDO::FETCH_ASSOC] : $fetchOptions;

                $fetchingIntoObjects = PDO::FETCH_CLASS === $fetchOptions[0];

                $fetch = $stmt->fetchAll(...$fetchOptions);

                if (false === $fetch) {

                    throw new PrivateAlert('Failed to fetchAll() from PDOStatement.');

                }

                $reduce = static fn($array) => isset($array[0])
                && ($fetchingIntoObjects ? is_object($array[0]) : is_array($array[0]))
                    ? $array[0]
                    : $array;


                // the $return = $fetch; is to help return types match the expected
                // since we modify by reference we need to make sure its correctly formatted before we assign it
                if (empty($fetch)) {

                    if ($fetchingIntoObjects) {
                        $return = null;
                    } else {
                        $return = [];
                    }

                } elseif (is_array(static::PRIMARY)) {

                    $return = (null !== $primary && [] !== $primary)
                    || (isset($argv[self::PAGINATION][self::LIMIT])
                        && $argv[self::PAGINATION][self::LIMIT] === 1
                        && count($fetch) === 1)
                        ? $reduce($fetch)
                        : $fetch;

                } elseif (is_string(static::PRIMARY)) {

                    $return = (null !== $primary && '' !== $primary)
                    || (isset($argv[self::PAGINATION][self::LIMIT])
                        && $argv[self::PAGINATION][self::LIMIT] === 1
                        && count($fetch) === 1)
                        ? $reduce($fetch)
                        : $fetch;


                } elseif (isset($argv[self::PAGINATION][self::LIMIT])
                    && $argv[self::PAGINATION][self::LIMIT] === 1
                    && count($fetch) === 1) {

                    $return = $reduce($fetch);

                } else {

                    $return = $fetch;

                }


                foreach (static::JSON_COLUMNS as $key) {

                    if (is_object($return)) {

                        $return->{$key} = null !== $return->{$key}
                            ? json_decode($return->{$key}, true, 512, JSON_THROW_ON_ERROR)
                            : null;

                    }

                    if (array_key_exists($key, $return)) {

                        $return[$key] = null !== $return[$key]
                            ? json_decode($return[$key], true, 512, JSON_THROW_ON_ERROR)
                            : null;

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

                $primaryKeysNeeded = is_array(static::PRIMARY) ? static::PRIMARY : [static::PRIMARY];

                // lets check the root level of our where clause to see if we have all primary keys
                $primary ??= [];

                $where = array_merge($where, $primary);

                $emptyWhere = empty($where);

                foreach ($primaryKeysNeeded as $primaryKey) {

                    if (false === array_key_exists($primaryKey, $emptyWhere ? $argv : $where)) {

                        if (true === self::$allowFullTableUpdates) {
                            continue;
                        }

                        return self::signalError('Restful tables which have a primary key must be updated by its primary key. To bypass this set you may set `self::\$allowFullTableUpdates = true;` during the PREPROCESS events. The primary key (' . $primaryKey . ') was not found.' . (count($primaryKeysNeeded) > 1 ? ' Please make sure all pks (' . print_r($primaryKeysNeeded, true) . ') are in the request.' : ''));

                    }

                    // we use $primary later to check the results
                    $primary[$primaryKey] = $emptyWhere ? $argv[$primaryKey] : $where[$primaryKey];

                    if ($replace) {

                        continue;

                    }

                    if ($emptyWhere) {

                        $where[$primaryKey] = $primary[$primaryKey];

                    }


                    // either way remove it from the update payload if it is unneeded
                    if (($where[$primaryKey] ?? '') === ($argv[$primaryKey] ?? '')) {

                        unset($argv[$primaryKey]);

                    }

                }

                if (false === $replace && empty($where)) {

                    throw new PrivateAlert('The WHERE argument is empty. Arguments were :: ' . json_encode(func_get_args(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

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

            self::beginTransaction();

            if (true === $replace) {

                if (!empty($where)) {

                    return self::signalError('Replace queries may not be given a where clause. Use Put instead.');

                }

            } else if (false === self::$allowFullTableUpdates || !empty($where)) {

                if (empty($where)) {

                    throw new PrivateAlert('The where clause is required but has been detected as empty. Arguments were :: ' . json_encode([$where, $primary], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

                }

                $sql .= ' WHERE ' . self::buildBooleanJoinedConditions($where);

            }

            $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

            RestfulValidations::validateGeneratedExternalSqlRequest($sql);

            self::postpreprocessRestRequest($sql);

            $stmt = $pdo->prepare($sql);

            if (false === $stmt) {

                return self::signalError("PDO failed to prepare the sql generated! ($sql)");

            }

            foreach (static::COLUMNS as $fullName => $shortName) {

                // todo - loop argv and find the column short name, faster iterations
                if (array_key_exists($fullName, $argv)) {

                    $op = self::EQUAL;

                    if (false === self::validateInternalColumn($fullName, $op, $argv[$fullName])) {

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

            if (false === self::verifyRowsAffected( $stmt, $update_or_replace, $sql, $primary, $argv)) {

                return false;

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
        $ignore = '';

        /** @noinspection NotOptimalIfConditionsInspection */
        if (1 === count($postRequestBody) &&
            array_key_exists(self::IGNORE, $postRequestBody)
            && true === is_array($postRequestBody[self::IGNORE])) {

            $postRequestBody = $postRequestBody[self::IGNORE];

            $ignore = ' ' . self::IGNORE . ' ';

        }

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
                foreach ($postRequestBody as &$iValue) {

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
                unset($iValue);

                $keys = '';

                $pdo_values = $bound_values = [];

                $rowsToInsert = count($postRequestBody);

                $totalKeys = $i = 0;

                $firstKey = array_key_first($postRequestBody) ?? 0;

                $firstRowKeys = array_keys($postRequestBody[$firstKey] ?? []);

                do {

                    $pdo_values[$i] = '';

                    $op = self::EQUAL;

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

                        $isBinary = 'binary' === static::PDO_VALIDATION[$fullName][self::MYSQL_TYPE];

                        $pdo_values[$i] .= $isBinary ? "UNHEX(:$shortName), " : ":$shortName, ";

                        self::validateInternalColumn($fullName, $op, $postRequestBody[$i][$fullName]);

                        $postRequestBody[$i] ??= [];

                        $iValue = &$postRequestBody[$i];   // this allows you to get your binary keys if they were C6 enabled.

                        if ($fullName === static::PRIMARY
                            || (is_array(static::PRIMARY)
                                && array_key_exists($fullName, static::PRIMARY))) {

                            $iValue[$fullName] ??= false;

                            if ($iValue[$fullName] === false) {

                                # UUID_TO_BIN - @link https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_uuid-to-bin
                                # the old way - REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""); this is mysql >=8
                                # If swap_flag is 1, the format of the return value differs: The time-low and time-high parts (the first and third groups of hexadecimal digits, respectively) are swapped. This moves the more rapidly varying part to the right and can improve indexing efficiency if the result is stored in an indexed column.
                                $iValue[$fullName] = static::CARBON_CARBONS_PRIMARY_KEY
                                    ? self::newEntity(static::class, $iValue[self::DEPENDANT_ON_ENTITY] ?? null)     // clusters should really use this
                                    : self::fetchColumn('SELECT HEX(UUID_TO_BIN(UUID(), 1))')[0];

                            }

                        }

                    }

                    $pdo_values[$i] = rtrim($pdo_values[$i], ', ');

                    ++$i;

                } while ($i < $rowsToInsert);

                if (0 === $totalKeys) {

                    return self::signalError('An unexpected error has occurred: Zero valid columns were matched to insert.');

                }

                $sql = self::INSERT . $ignore . ' INTO '
                    . (static::QUERY_WITH_DATABASE ? static::DATABASE . '.' : '')
                    . static::TABLE_NAME . ' ('
                    . rtrim($keys, ', ')
                    . ') VALUES ('
                    . implode('), (', $pdo_values) . ')';

                $primaryBinary = is_string(static::PRIMARY) && 'binary' === static::PDO_VALIDATION[static::PRIMARY][self::MYSQL_TYPE] ?? false;

                if ($primaryBinary) {

                    self::beginTransaction();

                }

                $moreReporting = self::jsonSQLReporting(func_get_args(), $sql);

                RestfulValidations::validateGeneratedExternalSqlRequest($sql);

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

                                if (false === static::AUTO_INCREMENT_PRIMARY_KEY) {

                                    # UUID_TO_BIN - @link https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_uuid-to-bin
                                    # the old way - REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""); this is mysql >=8
                                    # If swap_flag is 1, the format of the return value differs: The time-low and time-high parts (the first and third groups of hexadecimal digits, respectively) are swapped. This moves the more rapidly varying part to the right and can improve indexing efficiency if the result is stored in an indexed column.
                                    $iValue[$fullName] = self::fetchColumn('SELECT HEX(UUID_TO_BIN(UUID(), 1))')[0];

                                }

                            } else if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PrivateAlert("The column value of ($fullName) caused custom restful api validations for (" . static::class . ") primary key to fail (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            /**
                             * I'm fairly confident the length attribute does nothing.
                             * @todo - hex / unhex length conversion on any binary data
                             * @link https://stackoverflow.com/questions/28251144/inserting-and-selecting-uuids-as-binary16
                             * @link https://www.php.net/ChangeLog-8.php
                             * @notice PDO type validation has a bug until 8
                             **/
                            $maxLength = $info[self::MAX_LENGTH] === '' ? 0 : (int)$info[self::MAX_LENGTH];

                            $stmt->bindParam(":$shortName",
                                $iValue[$fullName],
                                $info[self::PDO_TYPE],
                                $maxLength);

                        } elseif ('json' === $info[self::MYSQL_TYPE]) {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Table ('" . static::class . "') column ($fullName) is set to not null and has no default value. It must exist in the request and was not found in the one sent.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName])) {

                                throw new PrivateAlert("Your tables ('" . static::class . "'), or joining tables, custom restful api validations caused the request to fail on json column ($fullName). Possible values include (" . json_encode(self::$compiled_valid_columns, JSON_PRETTY_PRINT) . ').');

                            }

                            if (false === is_string($iValue[$fullName])) {

                                $json = json_encode($iValue[$fullName]);  // todo - is this over-validating?

                                if (false === $json && $iValue[$fullName] !== false) {

                                    return self::signalError("The column ($fullName) failed to be json encoded.");

                                }

                                $iValue[$fullName] = $json;

                                unset($json);

                            }

                            $op = iRest::EQUAL;

                            self::runCustomCallables($fullName, $op, $iValue[$fullName]);

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } elseif (array_key_exists(self::DEFAULT_POST_VALUE, $info)) {

                            $iValue[$fullName] ??= $info[self::DEFAULT_POST_VALUE];

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE])) {

                                return self::signalError("Your custom restful table ('" . static::class . "') api validations caused the request to fail on column ($fullName)");

                            }

                            $op = iRest::EQUAL;

                            self::runCustomCallables($fullName, $op, $iValue[$fullName]);

                            $stmt->bindValue(":$shortName", $iValue[$fullName], $info[self::PDO_TYPE]);

                        } else {

                            if (false === array_key_exists($fullName, $iValue)) {

                                return self::signalError("Required argument ($fullName) is missing from the request to ('" . static::class . "') and has no default value.");

                            }

                            if (false === self::validateInternalColumn($fullName, $op, $iValue[$fullName], array_key_exists(self::DEFAULT_POST_VALUE, $info) ? $iValue[$fullName] === $info[self::DEFAULT_POST_VALUE] : false)) {

                                return self::signalError("Your custom restful api validations for ('" . static::class . "') caused the request to fail on required column ($fullName).");

                            }

                            $op = iRest::EQUAL;

                            self::runCustomCallables($fullName, $op, $iValue[$fullName]);

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

                    self::prepostprocessRestRequest($postRequestBody);

                    if (self::$commit) {

                        if (false === Database::commit()) {


                            return self::signalError('Failed to store commit transaction on table {{TableName}}');

                        }

                        if (is_callable($moreReporting)) {

                            $moreReporting();

                        }

                    }

                    self::postprocessRestRequest($postRequestBody);

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
                                '@quote @author John Schulz',
                                '@source @reference @link https://blog.pythian.com/case-auto-increment-mysql/',
                                'Some of his points are fairly outdated, but the general idea is still valid. Auto increment is not a good idea for a primary key.',
                                'Special consideration must be considered when using UUIDS as primary keys to maintain B-Tree innodb efficiently. MySQL 8 has new convience functions to help with this.',
                                '@note swap_flag @link https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_uuid-to-bin',
                                '@source @link https://dev.mysql.com/blog-archive/storing-uuid-values-in-mysql-tables/',
                                '@source @link https://dev.mysql.com/blog-archive/mysql-8-0-uuid-support/',
                                '@source @link https://stitcher.io/blog/optimised-uuids-in-mysql',
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

                self::prepostprocessRestRequest($postRequestBody);

                if (self::$commit) {

                    if (false === Database::commit()) {


                        return self::signalError('Failed to store commit transaction on table {{TableName}}');

                    }

                    if (is_callable($moreReporting)) {

                        $moreReporting();

                    }

                }

                self::postprocessRestRequest($postRequestBody);

                self::completeRest();

                return $id;


            } catch (Throwable $e) {

                self::handleRestException($e);

            }

            $tries ??= 0;   // dont make this static

        } while (3 !== $tries++);

        return false;

    }

    private static function verifyRowsAffected(PDOStatement $stmt, string $method,
                                               string       $sql, array|string|null $primary, array|null $argv): bool
    {

        $allPrimaryKeysGiven = false === empty($primary)
            && ((($receivedPks = count($primary)) === 1 && is_string(static::PRIMARY))
                || ($receivedPks === count(static::PRIMARY)));

        $rowCount = $stmt->rowCount();

        if (self::$externalRestfulRequestsAPI) {

            $GLOBALS['json'] ??= [];

            $GLOBALS['json']['rowCount'] = $rowCount;

        }

        if (0 === $rowCount && true === $allPrimaryKeysGiven) {

            $pdoOptions = Database::getPDOOptions();

            $pdoOptions[PDO::MYSQL_ATTR_FOUND_ROWS] ??= false;

            if ($pdoOptions[PDO::MYSQL_ATTR_FOUND_ROWS] === false) {

                return self::signalError("Zero rows were updated. MySQL failed update any target(s) during ($method) on "
                        . 'table (' . static::TABLE_NAME . ") while executing query ($sql). The arguments passed to rest are ("
                        . print_r($argv, true) . ") and primary key(s) ("
                        . print_r($primary, true) . "). By default PDO does not return the number of rows found "
                        . "([ PDO::MYSQL_ATTR_FOUND_ROWS => false ]), but the number of rows effected. "
                        . 'No changes have been made to this configuration. Your issue may only be '
                        . 'the target not needing updates, or no rows exsisting based on query/table conditions. <br/> ([SQLSTATE, Driver specific error code, Driver specific error message]) :: '
                        . print_r($stmt->errorInfo(), true)) . ')';

            }

            return self::signalError("Zero rows were found for update! MySQL failed to find any target(s) to update during ($method) on "
                    . 'table (' . static::TABLE_NAME . ") while executing query ($sql). The arguments passed to rest are ("
                    . print_r($argv, true) . ") and primary key(s) ("
                    . print_r($primary, true) . "). CarbonPHP has been overridden and passes "
                    . 'PDO::MYSQL_ATTR_FOUND_ROWS => true, to the PDO driver; aka return the number of found rows, '
                    . 'not the number of rows effected <br/> ([SQLSTATE, Driver specific error code, Driver specific error message]) :: '
                    . print_r($stmt->errorInfo(), true)) . ')';


        }

        return true;
    }

}
