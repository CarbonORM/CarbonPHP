<?php


namespace CarbonPHP\Restful;

use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Rest;
use CarbonPHP\Session;
use CarbonPHP\Tables\History_Logs;
use CarbonPHP\WebSocket\WsFileStreams;
use PDO;
use Throwable;

abstract class RestfulValidations
{

    //EXTERNAL_REQUEST_VALID_SQL_CHECK
    public static bool $validateExternalRequestsGeneratedSql = true;
    public static string $historyLogId = '';
    public static string $historyQuery = '';

    /**
     * @var callable|null
     */
    public static mixed $defaultRestAccessOverride = null;

    protected static ?iRestSinglePrimaryKey $history_table = null;


    /**
     * @return iRestSinglePrimaryKey
     */
    public static function getHistoryTable(): iRestSinglePrimaryKey
    {

        if (null === self::$history_table) {

            $table_name = Rest::getDynamicRestClass(History_Logs::class);    // all because custom prefixes and callbacks exist

            self::$history_table = new $table_name(); // This is only for referencing and is not actually needed as an instance.

        }

        return self::$history_table;

    }
    public static function postHistoryLog($selfClass): callable
    {

        static $hasRun = false;

        return static function ($request) use ($selfClass, &$hasRun): void {

            // this would run for all queries which will cause a recursive loop
            if (false === RestQueryValidation::$externalRestfulRequestsAPI) {

                return;

            }

            if (true === $hasRun) {

                return;

            }

            $hasRun = true;

            $sessionStatus = session_status();

            $postHistoryLog = [
                History_Logs::HISTORY_URI => $_SERVER['REQUEST_URI'],
                History_Logs::HISTORY_TABLE => $selfClass,
                History_Logs::HISTORY_QUERY => 'N/A',
                History_Logs::HISTORY_TYPE => $_SERVER['REQUEST_METHOD'],
                History_Logs::HISTORY_REQUEST => $request,
            ];

            $dynamicHistoryTable = self::getHistoryTable();

            self::$historyLogId = $dynamicHistoryTable::post($postHistoryLog);

            if (empty(self::$historyLogId)) {

                throw new PublicAlert('Failed to add history log!');

            }

            if (PHP_SESSION_ACTIVE === $sessionStatus && session_status() !== PHP_SESSION_ACTIVE) {

                new Session();

            }

        };

    }

    public static function putHistoryLog(): callable
    {

        static $hasRun = false;

        return static function ($response) use (&$hasRun): void {

            if (false === RestQueryValidation::$externalRestfulRequestsAPI) {

                return;

            }

            /** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection */
            if (true === $hasRun) {

                return;

            }

            $hasRun = true;

            if (empty(self::$historyLogId) || empty(self::$historyQuery)) {

                throw new PublicAlert('Failed to complete history log. (' . print_r([
                        '$historyLogId' => self::$historyLogId,
                        '$historyQuery' => self::$historyQuery
                    ], true) . ')');

            }

            $returnUpdated = [];

            // transaction active?
            if (session_status() === PHP_SESSION_ACTIVE) {

                session_write_close(); // todo - do I like this?

            }

            $dynamicHistoryTable = self::getHistoryTable();

            if (false === $dynamicHistoryTable::put($returnUpdated, self::$historyLogId, [
                    History_Logs::HISTORY_RESPONSE => (object)$response,
                    History_Logs::HISTORY_QUERY => self::$historyQuery
                ])) {
                throw new PublicAlert('Failed to update general log.');
            }

        };

    }

    public static function sendRestfulResponseToWebSocket($selfClass): void
    {

        try {

            if (iRest::GET === Rest::$REST_REQUEST_METHOD
                || (false === CarbonPHP::$cli && false === Rest::$externalRestfulRequestsAPI)) {

                return;

            }

            $GLOBALS['json']['sql'] ??= [];

            $sqlDebugInfo = null;

            $externalRestIdentifier = 'CarbonPHP\Restful\RestSettings::$externalRestfulRequestsAPI';

            foreach ($GLOBALS['json']['sql'] ?? [] as $debugInfo) {

                if (true === ($debugInfo[$externalRestIdentifier] ?? false)) {

                    $sqlDebugInfo = $debugInfo;

                    $debugDumpParams = $sqlDebugInfo['stmt']['debugDumpParams'];

                    unset($sqlDebugInfo['stmt']['debugDumpParams']);

                    $sqlDebugInfo['stmt']['sent'] = $debugDumpParams[1];

                    unset($sqlDebugInfo[$externalRestIdentifier]);

                    break;

                }

            }

            $jsonEncoded = json_encode((object)[
                'REST' => [
                    'TABLE_NAME' => $selfClass::TABLE_NAME,
                    'TABLE_PREFIX' => $selfClass::TABLE_PREFIX,
                    'METHOD' => Rest::$REST_REQUEST_METHOD,
                    'REQUEST' => Rest::$REST_REQUEST_PARAMETERS,
                    'REQUEST_PRIMARY_KEY' => Rest::$REST_REQUEST_PRIMARY_KEY,
                    'SQL' => $sqlDebugInfo,
                    'LOG_ID' => self::$historyLogId ?? null,
                ]
            ], JSON_THROW_ON_ERROR);

            WsFileStreams::sendToAllWebsSocketConnections($jsonEncoded);

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    /**
     * Careful those who journey here, the functions called here can run multiple times for a singe request.
     * This is due to table joins merging the rules multiple times.
     * This method can be overridden by the user to provide custom access rules. Setting the $defaultRestAccessOverride
     * static member of this class to a custom callable will override this method.
     * @param string $selfClass
     * @param array $overrides
     * @return array
     */
    public static function getDefaultRestAccess(string $selfClass, array $overrides = []): array
    {

        try {

            if (null !== self::$defaultRestAccessOverride) {

                $callable = self::$defaultRestAccessOverride;

                if (false === is_callable($callable)) {

                    throw new PrivateAlert('The default rest access callback is not callable.');

                }

                $defaultAccess = $callable($selfClass, $overrides);

                if (false === is_array($defaultAccess)) {

                    throw new PrivateAlert('The default rest access callback must return an array. Refer to the return of this method (' . __METHOD__ . ') for the correct/example format.');

                }

                return $defaultAccess;

            }

            return [
                iRest::COLUMN => [
                    ...($overrides[iRest::COLUMN] ??= [])
                ],
                iRest::PREPROCESS => [
                    iRest::PREPROCESS => [
                        self::postHistoryLog(static::class),
                        ...($overrides[iRest::PREPROCESS][iRest::PREPROCESS] ?? []),
                    ],
                    iRest::FINISH => [
                        static function ($query): void {
                            if (false === RestQueryValidation::$externalRestfulRequestsAPI) {
                                self::$historyQuery = $query[0];
                            }
                        },
                        ...($overrides[iRest::PREPROCESS][iRest::FINISH] ?? [])
                    ]
                ],
                iRest::GET => $overrides[iRest::GET] ?? [
                        iRest::PREPROCESS => [
                            fn() => Rest::disallowPublicAccess($selfClass),
                        ]
                    ],
                iRest::POST => $overrides[iRest::POST] ?? [
                        iRest::PREPROCESS => [
                            fn() => Rest::disallowPublicAccess($selfClass),
                        ]
                    ],
                iRest::PUT => [
                    ...($overrides[iRest::PUT]),
                    iRest::PREPROCESS => [
                        ...($overrides[iRest::PUT][iRest::PREPROCESS] ?? [
                            fn() => Rest::disallowPublicAccess($selfClass),
                        ]),
                        static function () {

                            if (false === Rest::$externalRestfulRequestsAPI) {
                                return;
                            }

                            Database::setPdoOptions([
                                    PDO::MYSQL_ATTR_FOUND_ROWS => true,
                                ] + Database::getDefaultPdoOptions(), false);
                        },
                    ],
                    iRest::FINISH => [
                        ...($overrides[iRest::PUT][iRest::FINISH] ?? []),
                        static function () {

                            if (false === Rest::$externalRestfulRequestsAPI) {
                                return;
                            }

                            Database::close();
                            Database::setPdoOptions(Database::getDefaultPdoOptions(), false);

                        }
                    ]
                ],
                iRest::DELETE => $overrides[iRest::DELETE] ?? [
                        iRest::PREPROCESS => [
                            fn() => Rest::disallowPublicAccess($selfClass),
                        ]
                    ],
                iRest::FINISH => [
                    // add column validation options here as well
                    iRest::PREPROCESS => [
                        ...($overrides[iRest::FINISH][iRest::PREPROCESS] ?? []),
                    ],
                    iRest::FINISH => [
                        self::putHistoryLog(),
                        ...($overrides[iRest::FINISH][iRest::FINISH] ?? [
                            static function () use ($selfClass): void {

                                self::sendRestfulResponseToWebSocket($selfClass);

                            },
                        ])
                    ]
                ]
            ];

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }


    /**
     * Simplify a SQL query for better comparisons and security.
     *
     * @param string|null $sql The SQL query to simplify.
     * @return string|null The simplified SQL query or null on failure.
     */
    private static function generalizeSqlForComparison(?string $sql): ?string
    {
        if ($sql === null) {
            return null;
        }

        return preg_replace(
            [
                // :injection0 -> ?
                '/(:injection\d*)/mi',
                // WHERE user_id IN (0,1,2,3) -> WHERE id IN (?)
                '/(?<= ' . iRest::IN . ' \()([^)]*)(?=\))/mi',
                // LIMIT 100,200 -> LIMIT ?
                '/(?<= ' . iRest::LIMIT . ' )(\d*,\d*|\d*)/mi',

            ],
            '?',    // replacing with ? will actually keep it valid sql (generally)
            $sql
        );
    }

    public static function validateGeneratedExternalSqlRequest(string $sql): void
    {
        global $json;

        $fileName = 'validSQL.json';

        if (false === Rest::$externalRestfulRequestsAPI) {

            // we don't need to verify the sql if we are not using the external api
            return;

        }

        $shouldExit = self::$validateExternalRequestsGeneratedSql;

        $validSqlFile = CarbonPHP::$app_root . $fileName;

        if (false === file_exists($validSqlFile)) {

            $errorMessage = "The file ($validSqlFile) does not exist. Please create a php file that returns an array of values equal to valid sql that can be run from the front end. This is typically autogenerated by the frontend test cases. See the documentation for more information.";

            if (!$shouldExit) {

                $json[$fileName] = $errorMessage;

                return;

            }

            throw new PrivateAlert($errorMessage);

        }

        try {

            $validSqlArray = json_decode(file_get_contents($validSqlFile), true, 512, JSON_THROW_ON_ERROR);

            $validSqlArray = $validSqlArray['validSQL'];

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

        if (false === is_array($validSqlArray)) {

            $errorMessage = "The file ($validSqlFile) does not return an array. Please create a php file that returns an array of values equal to valid sql that can be run from the front end. This is typically autogenerated by the frontend test cases. See the documentation for more information.";

            if (!$shouldExit) {

                $json[$fileName] = $errorMessage;

                return;

            }

            throw new PrivateAlert($errorMessage);

        }

        $possibleSql = [];

        $generalizedSql = self::generalizeSqlForComparison($sql);

        foreach ($validSqlArray as $validObject) {

            $validSql = $validObject['stmt']['sql'] ?? '';

            $possibleSql[] = $validSql;

            if ('' === $validSql) {

                continue;

            }

            if (self::generalizeSqlForComparison($validSql) === $generalizedSql) {

                $json['validSQL'] = $validSql;

                return;

            }

        }

        $json['possibleSQL'] = $possibleSql;

        if (!$shouldExit) {

            $json['invalidSQL'] = [$sql, "The sql is not valid. Please add it to the validSQL.json file."];

            return;

        }

        throw new PrivateAlert("The sql ($sql) is not valid. Please add it to the validSQL.json file. This is typically autogenerated by the frontend test cases. See the documentation for more information.");

    }

    /**
     * @param string $columnValue
     * @param string $className
     * @param string $columnName
     * @throws PrivateAlert
     */
    public static function validateUnique(string $columnValue, string $className, string $columnName): void
    {

        if (!class_exists($className)) {

            throw new PrivateAlert('Rest validation error. Parameters given to validate unique incorrect.');

        }

        $return = [];

        $options = [
            iRestNoPrimaryKey::class,
            iRestSinglePrimaryKey::class,
            iRestMultiplePrimaryKeys::class
        ];

        $imp = array_map('strtolower', array_keys(class_implements($className)));

        $opt = array_map('strtolower', $options);

        // todo - I think this is incorrect
        $intersect = array_intersect($imp, $opt);

        if (empty($intersect)) {
            $imp = implode('|', $options);
            throw new PrivateAlert("Rest validation error. The class ($className) passed must extend ($imp).");
        }

        $noPrimary = in_array(strtolower(iRestNoPrimaryKey::class), $intersect, true);

        $query = [
            iRest::WHERE => [
                $columnName => $columnValue
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ];

        if (false === ($noPrimary ?
                $className::Get($return, $query) :
                $className::Get($return, null, $query))) {       // this will work for single or multiple keys
            throw new PrivateAlert('Rest validation error. Get request failed in validation.');
        }

        if (!empty($return)) {
            throw new PrivateAlert("Oh no! Looks like the value for '$columnName' already exists. Please use a different value and try again.");
        }
    }

    /**
     * @param array $request
     * @param string $column
     * @param string $value
     */
    public static function addToPostRequest(array &$request, string $column, string $value): void
    {
        $request[$column] = $value;
    }

    public static function addIDToPostRequest(array &$request, string $column): void
    {
        $request[$column] = Session::$user_id;
    }

}