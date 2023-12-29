<?php

namespace CarbonPHP\Abstracts\Restful;

use CarbonPHP\Classes\Database;
use CarbonPHP\Interfaces\iRest;

abstract class RestSettings extends Database implements iRest {

    public static array $activeQueryStates = [];

    public static ?string $REST_REQUEST_METHOD = null;

    /**
     * @var mixed
     */
    public static mixed $REST_REQUEST_PRIMARY_KEY = null;       // this is set with the request payload

    public const REST_REQUEST_PRIMARY_KEY = 'REST_REQUEST_PRIMARY_KEY';       // this is set with the request payload

    /**
     * @var mixed
     */
    public static mixed $REST_REQUEST_PARAMETERS = [];            // this is set with the request payload

    /**
     * @var mixed
     */
    public static mixed $REST_REQUEST_RETURN_DATA = [];           // this is set with the request payload
    public static array $VALIDATED_REST_COLUMNS = [];
    public static array $compiled_valid_columns = [];
    public static array $compiled_PDO_validations = [];
    public static array $compiled_PHP_validations = [];
    public static array $compiled_regex_validations = [];
    public static array $join_tables = [];
    public static array $injection = [];                    // this increments well regardless of state, amount of rest calls ect.

    // validating across joins for rest is hard enough. I'm not going to allow user/FED provided sub queries
    public static bool $commit = true;
    public static bool $allowInternalMysqlFunctions = true;
    public static bool $allowSubSelectQueries = false;
    public static bool $externalRestfulRequestsAPI = false;
    public static bool $jsonReport = true;


    public static bool $aggregateSelectEncountered = false;
    public static bool $columnSelectEncountered = false;


    // False for external and internal requests by default. If a primary key exists you should always attempt to use it.
    public static bool $allowFullTableUpdates = false;
    public static bool $allowFullTableDeletes = false;

    // many other requirements must be met for this to apply, see how method signalError is defined
    public static bool $suppressErrorsAndReturnFalse = false;



}

