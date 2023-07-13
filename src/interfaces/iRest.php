<?php


namespace CarbonPHP\Interfaces;


use CarbonPHP\Route;

interface iRest
{

    # mysql restful identifiers alphabetical
    public const ADDDATE = 'ADDDATE';
    public const ADDTIME = 'ADDTIME';
    public const AS = 'AS';
    public const ASC = 'ASC';

    public const BETWEEN = 'BETWEEN';

    public const CONCAT = 'CONCAT';
    public const CONSTRAINT_NAME = 'CONSTRAINT_NAME';
    public const CONVERT_TZ = 'CONVERT_TZ';
    public const COUNT = 'COUNT';
    public const COUNT_ALL = 'COUNT_ALL';
    public const COMMENT = 'COMMENT';
    public const CURRENT_DATE = 'CURRENT_DATE';
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    public const DAY = 'DAY';
    public const DAY_HOUR = 'DAY_HOUR';
    public const DAY_MICROSECOND = 'DAY_MICROSECOND';
    public const DAY_MINUTE = 'DAY_MINUTE';
    public const DAY_SECOND = 'DAY_SECOND';
    public const DAYNAME = 'DAYNAME';
    public const DAYOFMONTH = 'DAYOFMONTH';
    public const DAYOFWEEK = 'DAYOFWEEK';
    public const DAYOFYEAR = 'DAYOFYEAR';
    public const DATE = 'DATE';
    public const DATE_ADD = 'DATE_ADD';
    public const DATEDIFF = 'DATEDIFF';
    public const DATE_SUB = 'DATE_SUB';
    public const DATE_FORMAT = 'DATE_FORMAT';
    public const DELETE_RULE = 'DELETE_RULE'; // not case sensitive but helpful for reporting to remain uppercase
    public const DESC = 'DESC'; // not case sensitive but helpful for reporting to remain uppercase
    public const DISTINCT = 'DISTINCT';

    public const EXTRACT = 'EXTRACT';
    public const EQUAL = '=';
    public const EQUAL_NULL_SAFE = '<=>';

    public const FALSE = 'FALSE';
    public const FULL_OUTER = 'FULL_OUTER';
    public const FROM_DAYS = 'FROM_DAYS';
    public const FROM_UNIXTIME = 'FROM_UNIXTIME';

    public const GET_FORMAT = 'GET_FORMAT';
    public const GREATER_THAN = '>';
    public const GROUP_BY = 'GROUP_BY';             // js // http get will convert the space so _ explicitly
    public const GROUP_CONCAT = 'GROUP_CONCAT';
    public const GREATER_THAN_OR_EQUAL_TO = '>=';

    public const HAVING = 'HAVING';
    public const HEX = 'HEX';
    public const HOUR = 'HOUR';
    public const HOUR_MICROSECOND = 'HOUR_MICROSECOND';
    public const HOUR_SECOND = 'HOUR_SECOND';
    public const HOUR_MINUTE = 'HOUR_MINUTE';

    public const IGNORE = 'IGNORE';
    public const IN = 'IN';
    public const IS = 'IS';
    public const IS_NOT = 'IS_NOT';
    public const INNER = 'INNER';
    public const INTERVAL = 'INTERVAL';
    public const INSERT = 'INSERT';

    public const JOIN = 'JOIN';

    public const LEFT = 'LEFT';
    public const LEFT_OUTER = 'LEFT_OUTER';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL_TO = '<=';
    public const LIKE = 'LIKE';
    public const LIMIT = 'LIMIT';
    public const LOCALTIME = 'LOCALTIME';
    public const LOCALTIMESTAMP = 'LOCALTIMESTAMP';

    public const MAKEDATE = 'MAKEDATE';
    public const MAKETIME = 'MAKETIME';
    public const MONTHNAME = 'MONTHNAME';
    public const MICROSECOND = 'MICROSECOND';
    public const MINUTE = 'MINUTE';
    public const MINUTE_MICROSECOND = 'MINUTE_MICROSECOND';
    public const MINUTE_SECOND = 'MINUTE_SECOND';
    public const MIN = 'MIN';
    public const MAX = 'MAX';
    public const MONTH = 'MONTH';

    public const NOT_LIKE = 'NOT_LIKE';
    public const NOT_EQUAL = '<>';
    public const NOT_IN = 'NOT_IN';
    public const NOT_NULL = 'NOT_NULL';
    public const NOW = 'NOW';
    public const NULL = 'NULL';

    public const ORDER = 'ORDER';

    public const PAGE = 'PAGE';
    public const PAGINATION = 'PAGINATION';
    public const PERIOD_DIFF = 'PERIOD_DIFF';
    public const REPLACE = 'REPLACE INTO';
    public const RIGHT = 'RIGHT';
    public const RIGHT_OUTER = 'RIGHT_OUTER';

    public const SECOND = 'SECOND';
    public const SECOND_MICROSECOND = 'SECOND_MICROSECOND';
    public const SELECT = 'SELECT';
    public const STR_TO_DATE = 'STR_TO_DATE';
    public const SUBDATE = 'SUBDATE';
    public const SUBTIME = 'SUBTIME';
    public const SUM = 'SUM';
    public const SYSDATE = 'SYSDATE';

    public const TIME = 'TIME';
    public const TIME_FORMAT = 'TIME_FORMAT';
    public const TIME_TO_SEC = 'TIME_TO_SEC';
    public const TIMEDIFF = 'TIMEDIFF';
    public const TIMESTAMP = 'TIMESTAMP';
    public const TIMESTAMPADD = 'TIMESTAMPADD';
    public const TIMESTAMPDIFF = 'TIMESTAMPDIFF';
    public const TO_DAYS = 'TO_DAYS';
    public const TO_SECONDS = 'TO_SECONDS';
    public const TRANSACTION_TIMESTAMP = 'TRANSACTION_TIMESTAMP';
    public const TRUE = 'TRUE';

    public const UNIX_TIMESTAMP = 'UNIX_TIMESTAMP';
    public const UNKNOWN = 'UNKNOWN';
    public const UPDATE = 'UPDATE';
    public const UPDATE_RULE = 'UPDATE_RULE';
    public const COLUMN_CONSTRAINTS = 'COLUMN_CONSTRAINTS';
    public const UTC_DATE = 'UTC_DATE';
    public const UTC_TIME = 'UTC_TIME';
    public const UTC_TIMESTAMP = 'UTC_TIMESTAMP';

    public const WEEKDAY = 'WEEKDAY';
    public const WEEKOFYEAR = 'WEEKOFYEAR';

    public const YEARWEEK = 'YEARWEEK';

    public const UNHEX = 'UNHEX';

    public const WHERE = 'WHERE';

    public const QUARTER = 'QUARTER';

    public const WEEK = 'WEEK';

    public const YEAR = 'YEAR';
    public const YEAR_MONTH = 'YEAR_MONTH';

    # carbon identifiers
    public const DEPENDANT_ON_ENTITY = 'DEPENDANT_ON_ENTITY';

    # HTTP Methods (case sensitive dont touch)
    public const OPTIONS = 'OPTIONS';
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';           // can also preform REPLACE INTO operations
    public const DELETE = 'DELETE';

    // Only for php
    public const MYSQL_TYPE = 'MYSQL_TYPE';
    public const PDO_TYPE = 'PDO_TYPE';
    public const MAX_LENGTH = 'MAX_LENGTH';
    public const AUTO_INCREMENT = 'AUTO_INCREMENT';
    public const SKIP_COLUMN_IN_POST = 'SKIP_COLUMN_IN_POST';
    public const DEFAULT_POST_VALUE = 'DEFAULT_POST_VALUE';
    public const REST_REQUEST_PRECOMMIT_CALLBACKS = 'PRECOMMIT';
    public const PRECOMMIT = self::REST_REQUEST_PRECOMMIT_CALLBACKS;
    public const REST_REQUEST_PREPROCESS_CALLBACKS = 'PREPROCESS';  // had to change from 0 so we could array merge recursively.
    public const PREPROCESS = self::REST_REQUEST_PREPROCESS_CALLBACKS;
    public const REST_REQUEST_FINNISH_CALLBACKS = 'FINISH';
    public const FINISH = self::REST_REQUEST_FINNISH_CALLBACKS;
    public const VALIDATE_C6_ENTITY_ID_REGEX = '#^' . Route::MATCH_C6_ENTITY_ID_REGEX . '$#';

    public const COLUMN = 'COLUMN';
    public const GLOBAL_COLUMN_VALIDATION = 'GLOBAL_COLUMN_VALIDATION';


    public const SQL_VERSION_PREG_REPLACE = [
        /** @lang PhpRegExp */
        '#bigint\(\d+\)#' => 'bigint',
        /** @lang PhpRegExp */
        '#int\(\d+\)#' => 'int',
        /** @lang PhpRegExp */
        '#CHARACTER\sSET\s[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#COLLATE\s[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#datetime\sDEFAULT\sNULL#' => 'datetime',
        /** @lang PhpRegExp */
        '#\sON\sDELETE\sNO\sACTION#' => '',
        /** @lang PhpRegExp */
        '#AUTO_INCREMENT=\d+#' => '',
        /** @lang PhpRegExp */
        '#COLLATE=[A-Za-z0-9_]+#' => '',
        /** @lang PhpRegExp */
        '#CREATE\sTABLE\s`#' => 'CREATE TABLE IF NOT EXISTS `',
        /** @lang PhpRegExp */
        '#DEFAULT CHARSET=[A-Za-z0-9_]+#' => '',   // todo - I feel like this makes sense to flag but Actions
        /** @lang PhpRegExp */
        '#ON DELETE NO ACTION#' => ' ',
        /** @lang PhpRegExp */
        '#ON UPDATE NO ACTION#' => ' ',   // delete and update are the default and mysql dump my choose to optionally print them
        /** @lang PhpRegExp */
        '#\s{2,}#' => ' ',
        /** @lang PhpRegExp */
        '#([,;])$#' => '',
        /** @lang PhpRegExp */
        '#(\s*)$#' => '',
    ];

    public const SQL_IRRELEVANT_REPLACEMENTS = [ // todo - make these relevant and auto correct
        /** @lang PhpRegExp */
        '#KEY `[^`]*`#' => 'Key',
        /** @lang PhpRegExp */
        '#CONSTRAINT `[^`]*`#' => 'CONSTRAINT',
    ];

    // https://dev.mysql.com/doc/refman/8.0/en/aggregate-functions.html
    public const AGGREGATES = [
        self::ADDDATE,
        self::ADDTIME,
        self::DATE_ADD,
        self::DAYNAME,
        self::CONVERT_TZ,
        self::DAYOFMONTH,
        self::MAX,
        self::MIN,
        self::SUM,
        self::HEX,
        self::UNHEX,
        self::DISTINCT,
        self::NOW,
        self::GROUP_CONCAT,
        self::CONCAT,
        self::COUNT,
        self::AS,                // just in case were using  $column => [ self::AS, '' ]  syntax
        self::INTERVAL,
        self::CURRENT_DATE,
    ];

    public const OPERATORS = [
        self::GREATER_THAN_OR_EQUAL_TO,
        self::GREATER_THAN,
        self::LESS_THAN_OR_EQUAL_TO,
        self::LESS_THAN,
        self::EQUAL,
        self::EQUAL_NULL_SAFE,
        self::NOT_EQUAL,
        self::IN,
        self::NOT_IN,
        self::LIKE,
        self::NOT_LIKE,
        self::IS,
        self::IS_NOT,
    ];


    // https://dev.mysql.com/worklog/task/?id=3597
    // SELECT * FROM tab1 WHERE col1 = 1 FOR UPDATE NOWAIT;
    // SELECT * FROM tab1 WHERE col1 = 1 FOR UPDATE NOWAIT SKIP LOCKED;
    public const LOCK = 'LOCK';
    public const FOR_SHARE = 'FOR_SHARE';
    public const FOR_UPDATE = 'FOR_UPDATE';
    public const NOWAIT = 'NOWAIT';
    public const SKIP_LOCKED = 'SKIP_LOCKED';


    public const AGGREGATES_WITH_NO_PARAMETERS = [
        'COUNT(*)',
        self::COUNT_ALL,
        self::NOW,
        self::CURRENT_DATE,
    ];

}
