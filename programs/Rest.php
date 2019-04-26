<?php

namespace CarbonPHP\Programs;

use \CarbonPHP\Interfaces\iCommand;

/**
 * @property string schema
 */
class Rest implements iCommand
{
    use MySQL {
        /** @noinspection ImplicitMagicMethodCallInspection */
        __construct as setup;
    }

    private $schema;
    private $user;
    private $password;

    public function usage(): void
    {
        print <<<END
\n
\t           Question Marks Denote Optional Parameters
\t           Order does not matter.
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage::
\t  php index.php rest  
\t       -help                         - this dialogue 
\t       -h [?HOST]                    - IP address
\t       -d                            - delete dump
\t       -s [?SCHEMA]                  - Its that tables schema!!!!
\t                                              Defaults to DB_NAME in config file passed to CarbonPHP
\t                                              Currently: "$this->schema"
\t       -u [?USER]                    - mysql username
\t                                              Defaults to DB_USER in config file passed to CarbonPHP
\t                                              Currently: "$this->user"
\t       -p [?PASSWORD]                - if ya got one
\t                                              Defaults to DB_PASS in config file passed to CarbonPHP
\t                                              Currently: "$this->password"
\t       -target                       - the dir to store the rest generated api
\t                                              Defaults to APP_ROOT . 'tables/'
\t       -json                         - enable global json reporting 
\t       -r                            - specify that a primary key is required for generation
\t       -l [?tableName(s),[...?,[]]]  - comma separated list of specific tables to capture
\t       -v ?debug                     - Verbose output, if === debug follows this tag even more output is given
\t       -f [?file_of_Tables]          - file of tables names separated by eol
\t       -x                            - Don't clean up files created for build
\t       -mysqldump [?executable]      - path to mysqldump command
\t       -mysql  [?executable]         - path to mysql command
\t       -dump [?dump]                 - path to a mysqldump sql export
\t       -cnf [?cnf_path]              - path to a mysql cnf file
\t       -trigger                      - build triggers and history tables for binary primary keys
\n
END;
        exit(1);
    }

    public function __construct($CONFIG)
    {
        $this->setup($CONFIG);
        $this->schema = $CONFIG['DATABASE']['DB_NAME'];
        $this->user = $CONFIG['DATABASE']['DB_USER'];
        $this->password = $CONFIG['DATABASE']['DB_PASS'];
    }

    public function run($argv): int
    {
        $argc = \count($argv);

        if (!is_dir($concurrentDirectory = APP_ROOT . 'table') && !mkdir($concurrentDirectory) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        // Check command line args, password is optional
        print "\tBuilding Rest Api!\n";

        // These are PDO const types, so we'll eliminate one complexity by evaluating them before inserting into the template
        $PDO = [0 => \PDO::PARAM_NULL, 1 => \PDO::PARAM_BOOL, 2 => \PDO::PARAM_INT, 3 => \PDO::PARAM_STR];
        // set default values
        $rest = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        $clean = true;
        $targetDir = APP_ROOT . 'tables/';
        $carbon_namespace = APP_ROOT === CARBON_ROOT;
        $only_these_tables = $history_table_query = $mysql = null;
        $verbose = $debug = $json = $primary_required = $delete_dump = $skipTable = false;

        /** @noinspection ForeachInvariantsInspection - as we need $i++ */
        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                case '-json':
                    $json = true;
                    break;
                case '-target':
                    $targetDir = $argv[++$i];
                    break;
                case '-x':
                    $clean = false;
                    break;
                case '-v':
                    if (isset($argv[++$i]) && strtolower($argv[$i]) === 'debug') {
                        print "\tDebug mode is best when paired with the optional (-l or -f) flags. Use -help for more information.\n";
                        $debug = true;
                    } else --$i;
                    $verbose = true;
                    break;
                case '-carbon':
                    $carbon_namespace = true;
                    break;
                case '-trigger':
                    $history_table_query = true;
                    $query = <<<QUERY
CREATE TABLE IF NOT EXISTS sys_resource_history_logs
(
  uuid BINARY(16) NULL,
  resource_type VARCHAR(10) NULL,
  resource_uuid BINARY(16) NULL,
  operation_type VARCHAR(16) NULL COMMENT 'POST|PUT|DELETE',
  data BLOB NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
  modified_by INT(16) NULL
);
QUERY;
                    file_put_contents('triggers.sql', $query);
                    break;
                case '-help':
                    $this->usage();
                    break;          // unneeded but my editor complains
                case '-d':
                    $delete_dump = true;
                    break;
                case '-h':
                    $host = $argv[++$i];
                    break;
                case '-s':
                    $this->schema = $argv[++$i];
                    break;
                case '-r':
                    $primary_required = true;
                    break;
                case '-u':
                    $user = $argv[++$i];
                    break;
                case '-p':
                    $pass = $argv[++$i];
                    break;
                case '-l':
                    // This argument is for specifying the
                    $only_these_tables = explode(',', $argv[++$i]);
                    break;
                case '-f':
                    if (empty($file = file_get_contents((string)$argv[++$i]))) {
                        print 'Could not open file [ ' . $argv[$i] . " ] for input\n\n";
                        exit(1);
                    }
                    $only_these_tables = explode(PHP_EOL, $file);
                    break;
                case '-mysqldump':
                    // the path to the mysqldump executable
                    $mysqldump = $argv[++$i];
                    break;
                case '-mysql':
                    // the path to the mysqldump executable
                    $mysql = $argv[++$i];
                    break;
                case '-dump':
                    // path to an sql dump file
                    $dump = $argv[++$i];
                    break;
                case '-cnf':
                    // path to an sql dump file
                    $cnfFile = $argv[++$i];
                    break;
                default:
                    print "\tInvalid flag " . $argv[$i] . PHP_EOL;
                    print <<<END
\n\n\t
\t      "You are young
\t      and life is long
\t      and there is time
\t      to kill today.
\t      And then one day you find
\t      ten years have got behind you.
\t      No one told you when to run,
\t      you missed the starting gun!"
\t
\t      - 'Time' Pink Floyd
\n\n
END;
                    exit(1);
            }
        }

        if (empty($targetDir)) {
            print 'You must provide a target directory.' . PHP_EOL;
            $this->usage();
        } else if (!is_dir($targetDir)) {
            print 'The target directory appears invalid "' . $targetDir . '"' . PHP_EOL;
            exit(1);
        } else if ('/' !== substr($targetDir, -1)) {
            $targetDir .= DS;
        }

        if (empty($this->schema)) {
            print 'You must specify the table schema!' . PHP_EOL;
            exit(1);
        }

        $this->mysqldump = $this->MySQLDump($mysqldump ?? null);

        if (!file_exists($this->mysqldump)) {
            print 'Could not load mysql dump file!' . PHP_EOL;
            exit(1);
        }

        if (empty($this->mysqldump  = file_get_contents($this->mysqldump ))) {
            print 'Contents of the mysql dump file appears empty. Build Failed!';
            exit(1);
        }

        /** @noinspection ForgottenDebugOutputInspection */
        $verbose and var_dump($this->mysqldump);

        // match all tables from a mysql dump
        preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=InnoDB)#', $this->mysqldump, $matches);

        // I just want the list of matches, nothing more.
        $matches = $matches[0];

        // Every CREATE TABLE as tables
        foreach ($matches as $table) {
            if (isset($foreign_key)) {
                unset($foreign_key);
            }

            // Separate each insert line by new line feed \n
            $table = explode(PHP_EOL, $table);
            $binary = $skipping_col = $primary = [];
            $tableName = '';
            $column = 0;

            // Every line in tables insert
            foreach ($table as $words_in_insert_stmt) {
                // binary column default values are handled by mysql.
                $cast_binary_default = false;

                // Separate each line in the tables creation by spaces
                $words_in_insert_stmt = explode(' ', trim($words_in_insert_stmt));

                // We can assume that this is the first line of the tables insert
                if ($words_in_insert_stmt[0] === 'CREATE') {
                    $tableName = trim($words_in_insert_stmt[2], '`');               // Table Name

                    // TRY to load previous validation functions

                    $rest[$tableName] = [
                        'json' => $json,
                        'binary_primary' => false,
                        'carbon_namespace' => $carbon_namespace,
                        'carbon_table' => false,
                        'database' => $this->schema,
                        // We need to catch circular dependencies
                        'dependencies' => $rest[$tableName]['dependencies'] ?? [],
                        'TableName' => $tableName,
                        'primarySort' => '',
                        'primary' => [],
                    ];


                    if (file_exists($validation = __DIR__ . '/../app/MVC/Tables/' . $tableName . '.php')) {
                        $validation = file_get_contents($validation);
                        preg_match_all('#const VALIDATION = \[[^;]+;#', $validation, $matches);

                        if (isset($matches[0][0])) {
                            $rest[$tableName]['validation'] = $matches[0][0];
                        }
                    }


                    // 'only these tables' is specified in the command line arguments (via file or comma list)
                    if (!empty($only_these_tables) && !\in_array($tableName, $only_these_tables, true)) {
                        // Break from this loop (every line in the create) and the parent loop (the tables)
                        $verbose and print 'Skipping ' . $tableName . PHP_EOL;
                        // this is our condition to check right after this tables is executed
                        $skipTable = true;
                        // We may need to analyse for foreign keys, we will still break after this foreach loop
                        if (!$history_table_query) {
                            break;
                        }
                    }

                    if ($verbose) {
                        print "\tGenerating {$tableName}\n";
                        /** @noinspection ForgottenDebugOutputInspection */
                        $debug and var_dump($table);
                    }

                } else if ($words_in_insert_stmt[0][0] === '`') {

                    // This is expected to be the second condition run in foreach
                    // columns is just a list of column
                    $name = $rest[$tableName]['columns'][] = trim($words_in_insert_stmt[0], '`');

                    // Explode hold all information about column
                    $rest[$tableName]['explode'][$column]['name'] = $name;

                    $type = strtolower($words_in_insert_stmt[1]);

                    // exploding strings like 'mediumint(9)' and 'binary(16)'
                    if (\count($argv = explode('(', $type)) > 1) {
                        $type = $argv[0];
                        if ($type === 'enum') {
                            $length = '';               // enums define strings where im expecting int length
                        } else {
                            $length = trim($argv[1], '),');
                        }
                        // This being set determines what type of PDO stmt we use
                        $rest[$tableName]['explode'][$column]['length'] = $length;
                    }

                    $rest[$tableName]['explode'][$column]['mysql_type'] = $type;

                    $rest[$tableName]['explode'][$column]['json'] = $type === 'json';

                    switch ($type) {                // Use pdo for what it can actually do
                        case 'tinyint':
                            $type = $PDO[0];
                            break;
                        case 'smallint':
                        case 'mediumint':
                            $type = $PDO[2];
                            break;
                        /** @noinspection PhpMissingBreakStatementInspection */
                        case 'binary':
                            /**
                             * looks like this wasn't needed
                             * were using a pdo length check for varchar
                             * and inserting a 32 char hex. The length values in
                             * the stmt are normally 16, yet PDO does not fail.
                             * This should be further researched
                             **/
                            #$length *= 2;
                            $binary[] = $name;
                            $rest[$tableName]['binary_trigger'][] = $name;
                            $rest[$tableName]['binary_list'][] = ['name' => $name];
                            $rest[$tableName]['explode'][$column]['binary'] = true;
                            $cast_binary_default = true;
                        default:
                        case 'varchar':
                            $type = $PDO[3];
                    }
                    // Explode hold all information about column
                    $rest[$tableName]['explode'][$column]['type'] = $type;

                    // Lets check if a default value is set for column
                    $key = array_search('DEFAULT', $words_in_insert_stmt, true);

                    if ($key !== false) {
                        ++$key; // move from the word default to the default value
                        $default = rtrim($words_in_insert_stmt[$key], ',');

                        if ($default === 'CURRENT_TIMESTAMP') {
                            // Were going to skip columns with this set as the default value
                            // Trying to insert this condition w/ PDO is problematic
                            $skipping_col[] = $name;
                            $rest[$tableName]['explode'][$column]['skip'] = true;
                        } else if ($default[0] !== '\'') {
                            // We need to escape values for php
                            $default = "'$default'";
                        }
                        $rest[$tableName]['explode'][$column]['default'] = $default === "'NULL'" ? 'null' : $cast_binary_default ? 'null' : $default;
                    }

                    // As far as I can tell the AUTO_INCREMENT condition the last possible word in the query
                    $auto_inc = \count($words_in_insert_stmt) - 1;
                    if (isset($words_in_insert_stmt[$auto_inc]) && $words_in_insert_stmt[$auto_inc] === 'AUTO_INCREMENT,') {
                        $skipping_col[] = $name;
                        $rest[$tableName]['explode'][$column]['skip'] = true;
                        $verbose and print "\tThe Table '$tableName' contains an AUTO_INCREMENT column. This is bad for scaling.
                \tConsider switching to binary(16) and letting this rest API manage column uniqueness.\n";
                    }

                    $column++;
                } else if ($words_in_insert_stmt[0] === 'PRIMARY') {
                    // Composite Primary Keys are a thing,  TODO - optimise the template for none vs single vs double key
                    $primary = explode('`,`', trim($words_in_insert_stmt[2], '(`),'));

                    $rest[$tableName]['primarySort'] = implode(',', $primary);

                    // Build the insert stmt
                    $sql = [];
                    foreach ($primary as $key) {
                        if (\in_array($key, $binary, true)) {
                            // binary data is expected as hex @ rest call (GET,PUT,DELETE)
                            $sql[] = ' ' . $key . '=UNHEX(\'.self::addInjection($primary, $pdo).\')';
                        } else {
                            // otherwise just create the stmt normally
                            $sql[] = ' ' . $key . '=\'.self::addInjection($primary, $pdo).\'';
                        }
                        $rest[$tableName]['primary'][] = ['name' => $key];
                    }
                    $rest[$tableName]['primary'][] = ['sql' => '$sql .= \' WHERE ' . implode($sql, ' OR ') . '\';'];

                } else if ($words_in_insert_stmt[0] === 'CONSTRAINT') {

                    //  if (count($words_in_insert_stmt) !== 8) {
                    //      print  PHP_EOL . $tableName  . PHP_EOL and die;
                    //  }

                    $foreign_key = trim($words_in_insert_stmt[4], '()`');
                    $references_table = trim($words_in_insert_stmt[6], '`');
                    $references_column = trim($words_in_insert_stmt[7], '()`,');

                    if ($references_table === 'carbons' && \in_array($foreign_key, $primary, true)) {
                        $rest[$tableName]['carbon_table'] = $tableName !== 'carbons';
                    }

                    // We need to catch circular dependencies as mysql dumps print schemas alphabetically
                    if (!isset($rest[$references_table])) {
                        $verbose and print "\n\t\t$tableName => [$foreign_key => $references_column]\n";
                        $rest[$references_table] = ['dependencies' => []];
                    } else if (!isset($rest[$references_table]['dependencies'])) {
                        $rest[$references_table]['dependencies'] = [];
                    }

                    $rest[$references_table]['dependencies'][] = [$tableName => [$foreign_key => $references_column]];
                }
            }

            // We need to break from this tables too if the tables is not in ( -l -f )
            if ($skipTable) {
                $skipTable = false; // This is so we can stop analysing a full tables
                continue;
            }

            // Make sure we didn't specify a flag that could cause us to move on...
            if (empty($rest[$tableName]['primary'])) {
                $verbose and print "The tables {$rest[$tableName]['TableName']} does not have a primary key.\n";
                if ($primary_required) {
                    print " \tSkipping...\n ";
                    continue;
                }
            } else {
                foreach ($rest[$tableName]['explode'] as &$value) {
                    if (\in_array($value, ['pageSize', 'pageNumber'])) {
                        print $rest[$tableName]['TableName'] . " uses reserved 'REST' keywords as a column identifier => $value\n\tRest Failed";
                        die(1);
                    }

                    if (false !== \in_array($value['name'], $primary, true)) {
                        $value['primary'] = true;
                        if (isset($value['binary'])) {
                            $value['primary_binary'] = true;
                            $rest[$tableName]['binary_primary'] = true;
                        }
                    }
                }
            }
            unset($value);

            // Listed is located in our POST method
            $rest[$tableName]['listed'] = '';
            $rest[$tableName]['implode'] = $rest[$tableName]['columns'];
            // The final value of implode is only used in the POST method
            foreach ($rest[$tableName]['implode'] as $key => &$value) {

                if (!\in_array($value, $skipping_col, true)) {

                    // This suffixes an extra comma
                    $rest[$tableName]['listed'] .= $value . ', ';

                    if (\in_array($value, $binary, true)) {
                        $value = ' UNHEX(:' . $value . ')';
                    } else {
                        $value = ' :' . $value;
                    }
                } else {
                    // unset($value) when &$value failed when implode became a second
                    // generation value. This doesn't seem right, (like how can this be
                    // the case?) to investigate later
                    unset($rest[$tableName]['implode'][$key]);
                }
            }
            unset($value);


            // Listed is located in our POST stmt, remove trailing comma
            $rest[$tableName]['listed'] = rtrim($rest[$tableName]['listed'], ', ');

            // Remove unneeded comma at begging of string
            $rest[$tableName]['implode'] = implode(',', $rest[$tableName]['implode']);

            // This is our mustache template engine implemented in php, used for rendering user content
            $mustache = new \Mustache_Engine();

            file_put_contents($targetDir . $rest[$tableName]['TableName'] . '.php', $mustache->render($this->restTemplate(), $rest[$tableName]));
        }


        /**
         * Now that the full dump has been parsed, we need to build our triggers
         * using the foreign key analysis
         */

        if ($history_table_query) {
            print "\tBuilding Triggers!\n";

            $triggers = '';
            foreach ($rest as $table) {
                if (\in_array($table['TableName'], ['sys_resource_creation_logs', 'sys_resource_history_logs'])) {
                    continue;
                }
                if ($table['binary_primary'] && ($only_these_tables === null || \in_array($table['TableName'], $only_these_tables, true))) {
                    $triggers .= $this->trigger($table['TableName'], $table['columns'], $table['binary_trigger'] ?? [], $table['dependencies'], $table['primary'][0]['name']);
                }
            }

            file_put_contents('triggers.sql', 'DELIMITER ;;' . PHP_EOL . $triggers . PHP_EOL . 'DELIMITER ;');

            $this->MySQLSource('triggers.sql');
        }

        // debug is a subset of the verbose flag
        /** @noinspection ForgottenDebugOutputInspection */
        $debug and var_dump($rest['clients']);

        print "\tSuccess!\n\n";

        return 0;
    }


    public function trigger($table, $columns, $binary, $dependencies, $primary): string
    {

        $json_mysql = function ($op = 'NEW') use ($columns, $binary) {
            $mid = "DECLARE json text;\n SET json = '{';";
            foreach ($columns as $key => &$column) {
                $column = in_array($column, $binary, true)
                    ? <<<END
\nSET json = CONCAT(json,'"$column":"', HEX($op.$column), '"');
END
                    : <<<END
\nSET json = CONCAT(json,'"$column":"', COALESCE($op.$column,''), '"');
END;
            }
            unset($column);

            $mid .= implode("\nSET json = CONCAT(json, ',');", $columns);

            $mid .= <<<END
SET json = CONCAT(json, '}');
END;
            return $mid;
        };

        // sys_resource_creation_logs sys_resource_history_logs

        $history_sql = function ($operation_type = 'POST') use ($table, $primary) {
            $query = '';
            $relative_time = $operation_type === 'POST' ? 'NEW' : $operation_type === 'PUT' ? 'NEW' : 'OLD';
            switch ($operation_type) {
                case 'POST':
                    $query = "INSERT INTO sys_resource_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary);\n";
                case 'PUT':
                    $query .= "INSERT INTO sys_resource_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary , '$operation_type', json);";
                    break;
                case 'DELETE':
                    $query = "INSERT INTO sys_resource_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary , '$operation_type', json);";
                    break;
                case 'GET':
                    break;
                default:
                    break;
            }

            return $query;
        };

        $delete_children = function () use ($dependencies) {
            $sql = '';

            // I agree, this is horribly ugly... don't hate me
            if (!empty($dependencies)) {
                foreach ($dependencies as $array) {
                    foreach ($array as $child => $relation) {
                        foreach ($relation as $c => $keys) {
                            $sql .= "DELETE FROM $child WHERE $c = OLD.$keys;" . PHP_EOL;
                        }
                    }
                }
            }
            return $sql;
        };

        return <<<TRIGGER
DROP TRIGGER IF EXISTS `trigger_{$table}_b_d`;;
CREATE TRIGGER `trigger_{$table}_b_d` BEFORE DELETE ON `$table` FOR EACH ROW
BEGIN
{$json_mysql('OLD')}
      -- Insert record into audit tables
{$history_sql('DELETE')}
      -- Delete Children
{$delete_children()}

END;;

DROP TRIGGER IF EXISTS `trigger_{$table}_a_u`;;
CREATE TRIGGER `trigger_{$table}_a_u` AFTER UPDATE ON `$table` FOR EACH ROW
BEGIN

{$json_mysql()}
      -- Insert record into audit tables
{$history_sql('PUT')}

END;;

DROP TRIGGER IF EXISTS `trigger_{$table}_a_i`;;
CREATE TRIGGER `trigger_{$table}_a_i` AFTER INSERT ON `$table` FOR EACH ROW
BEGIN

{$json_mysql()}
      -- Insert record into audit tables
{$history_sql('POST')}

END;;
TRIGGER;
    }


    private function restTemplate(): string
    {
        return <<<STRING
<?php
{{^carbon_namespace}}namespace Tables;{{/carbon_namespace}}
{{#carbon_namespace}}namespace CarbonPHP\Tables;{{/carbon_namespace}}

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class {{TableName}} extends Database implements iRest
{
    public const PRIMARY = [
    {{#primary}}{{#name}}'{{name}}',{{/name}}{{/primary}}
    ];

    public const COLUMNS = [
        {{#explode}}'{{name}}' => [ '{{mysql_type}}', '{{type}}', '{{length}}' ],{{/explode}}
    ];

    {{^validation}}public const VALIDATION = [];{{/validation}}{{#validation}}{{{validation}}}{{/validation}}


    public static \$injection = [];


    {{#json}}
    public static function jsonSQLReporting(\$argv, \$sql) : void {
        global \$json;
        if (!\is_array(\$json)) {
            \$json = [];
        }
        if (!isset(\$json['sql'])) {
            \$json['sql'] = [];
        }
        \$json['sql'][] = [
            \$argv,
            \$sql
        ];
    }
    {{/json}}

    public static function buildWhere(array \$set, \PDO \$pdo, \$join = 'AND') : string
    {
        \$sql = '(';
        \$bump = false;
        foreach (\$set as \$column => \$value) {
            if (\is_array(\$value)) {
                if (\$bump) {
                    \$sql .= " \$join ";
                }
                \$bump = true;
                \$sql .= self::buildWhere(\$value, \$pdo, \$join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists(\$column, self::COLUMNS)) {
                \$bump = false;
                if (self::COLUMNS[\$column][0] === 'binary') {
                    \$sql .= "(\$column = UNHEX(:" . \$column . ")) \$join ";
                } else {
                    \$sql .= "(\$column = :" . \$column . ") \$join ";
                }
            } else {
                \$bump = false;
                \$sql .= "(\$column = " . self::addInjection(\$value, \$pdo) . ") \$join ";
            }
        }
        return rtrim(\$sql, " \$join") . ')';
    }

    public static function addInjection(\$value, \PDO \$pdo, \$quote = false) : string
    {
        \$inject = ':injection' . \\count(self::\$injection) . 'buildWhere';
        self::\$injection[\$inject] = \$quote ? \$pdo->quote(\$value) : \$value;
        return \$inject;
    }

    public static function bind(\PDOStatement \$stmt, array \$argv) {
   
    \$bind = function (array \$argv) use (&\$bind, &\$stmt) {
            foreach (\$argv as \$key => \$value) {
                
                if (is_array(\$value)) {
                    \$bind(\$value);
                    continue;
                }
                switch (\$key) {
                
            {{#explode}}
                   case '{{name}}':
                    {{^length}}
                        \$stmt->bindValue(':{{name}}',{{#json}}json_encode(\$argv['{{name}}']){{/json}}{{^json}}\$argv['{{name}}']{{/json}}, {{type}});
                    {{/length}}
                    {{#length}}
                        \${{name}} = \$argv['{{name}}'];
                        \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
                    {{/length}}
                    break;
            {{/explode}}
            }
          }
        };
        
        \$bind(\$argv);

        foreach (self::\$injection as \$key => \$value) {
            \$stmt->bindValue(\$key,\$value);
        }

        return \$stmt->execute();
    }


    /**
    *
    *   \$argv = [
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
    * @param array \$return
    * @param string|null \$primary
    * @param array \$argv
    * @return bool
    * @throws \Exception
    */
    public static function Get(array &\$return, string \$primary = null, array \$argv) : bool
    {
        self::\$injection = [];
        \$aggregate = false;
        \$group = \$sql = '';
        \$pdo = self::database();

        \$get = \$argv['select'] ?? array_keys(self::COLUMNS);
        \$where = \$argv['where'] ?? [];

        if (array_key_exists('pagination',\$argv)) {
            if (!empty(\$argv['pagination']) && !\is_array(\$argv['pagination'])) {
                \$argv['pagination'] = json_decode(\$argv['pagination'], true);
            }
            if (array_key_exists('limit',\$argv['pagination']) && \$argv['pagination']['limit'] !== null) {
                \$limit = ' LIMIT ' . \$argv['pagination']['limit'];
            } else {
                \$limit = '';
            }

            \$order = '';
            if (!empty(\$limit)) {

                \$order = ' ORDER BY ';

                if (array_key_exists('order',\$argv['pagination']) && \$argv['pagination']['order'] !== null) {
                    if (\is_array(\$argv['pagination']['order'])) {
                        foreach (\$argv['pagination']['order'] as \$item => \$sort) {
                            \$order .= "\$item \$sort";
                        }
                    } else {
                        \$order .= \$argv['pagination']['order'];
                    }
                } else {
                    \$order .= '{{primarySort}} ASC';
                }
            }
            \$limit = "\$order \$limit";
        } else {
            \$limit = ' ORDER BY {{primarySort}} ASC LIMIT 100';
        }

        foreach(\$get as \$key => \$column){
            if (!empty(\$sql)) {
                \$sql .= ', ';
                if (!empty(\$group)) {
                    \$group .= ', ';
                }
            }
            \$columnExists = array_key_exists(\$column, self::COLUMNS);
            if (\$columnExists && self::COLUMNS[\$column][0] === 'binary') {
                \$sql .= "HEX(\$column) as \$column";
                \$group .= \$column;
            } elseif (\$columnExists) {
                \$sql .= \$column;
                \$group .= \$column;
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| {{#explode}}|{{name}}{{/explode}}))+\)*)+ *(as [a-z]+)?#i', \$column)) {
                    return false;
                }
                \$sql .= \$column;
                \$aggregate = true;
            }
        }

        \$sql = 'SELECT ' .  \$sql . ' FROM {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}}';

        if (null === \$primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty(\$where)) {
                \$sql .= ' WHERE ' . self::buildWhere(\$where, \$pdo);
            }
        } {{#primary}}{{#sql}}else {
        {{{sql}}}
        }{{/sql}}{{/primary}}

        if (\$aggregate  && !empty(\$group)) {
            \$sql .= ' GROUP BY ' . \$group . ' ';
        }

        \$sql .= \$limit;

        {{#json}}self::jsonSQLReporting(\\func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        if (!self::bind(\$stmt, \$argv['where'] ?? [])) {
            return false;
        }

        \$return = \$stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        {{#primary}}{{#sql}}
        if (\$primary !== null || (isset(\$argv['pagination']['limit']) && \$argv['pagination']['limit'] === 1 && \count(\$return) === 1)) {
            \$return = isset(\$return[0]) && \is_array(\$return[0]) ? \$return[0] : \$return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        {{#explode}}{{#json}}if (array_key_exists('{{name}}', \$return)) {
                \$return['{{name}}'] = json_decode(\$return['{{name}}'], true);
            }
        {{/json}}{{/explode}}
        }{{/sql}}{{/primary}}

        return true;
    }

    /**
    * @param array \$argv
    * @return bool|mixed
    */
    public static function Post(array \$argv)
    {
        self::\$injection = [];
        /** @noinspection SqlResolve */
        \$sql = 'INSERT INTO {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ({{listed}}) VALUES ({{{implode}}})';

        {{#json}}self::jsonSQLReporting(\\func_get_args(), \$sql);{{/json}}

        \$stmt = self::database()->prepare(\$sql);

    {{#explode}}
        {{#primary_binary}}
            {{^carbon_table}}
                \${{name}} = \$id = \$argv['{{name}}'] ?? self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];
                \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
            {{/carbon_table}}
            {{#carbon_table}}
                \${{name}} = \$id = \$argv['{{name}}'] ?? self::beginTransaction('{{TableName}}');
                \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
            {{/carbon_table}}
        {{/primary_binary}}
        {{^primary_binary}}
            {{^skip}}
                {{^length}}\$stmt->bindValue(':{{name}}',{{#json}}json_encode(\$argv['{{name}}']){{/json}}{{^json}}{{^default}}\$argv['{{name}}']{{/default}}{{#default}}array_key_exists('{{name}}',\$argv) ? \$argv['{{name}}'] : {{default}}{{/default}}{{/json}}, {{type}});{{/length}}
                {{#length}}
                    \${{name}} = {{^default}}\$argv['{{name}}']{{/default}}{{#default}} \$argv['{{name}}'] ?? {{{default}}}{{/default}};
                    \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
                {{/length}}
            {{/skip}}
        {{/primary_binary}}{{/explode}}


    {{#binary_primary}}
        return \$stmt->execute() ? \$id : false;{{/binary_primary}}
    {{^binary_primary}}
        {{^carbon_table}}
            return \$stmt->execute();{{/carbon_table}}{{/binary_primary}}
    }

    /**
    * @param array \$return
    * @param string \$primary
    * @param array \$argv
    * @return bool
    */
    public static function Put(array &\$return, string \$primary, array \$argv) : bool
    {
        self::\$injection = [];
        if (empty(\$primary)) {
            return false;
        }

        foreach (\$argv as \$key => \$value) {
            if (!\array_key_exists(\$key, self::COLUMNS)){
                return false;
            }
        }

        \$sql = 'UPDATE {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ';

        \$sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        \$set = '';

        {{#explode}}
            if (array_key_exists('{{name}}', \$argv)) {
                \$set .= '{{name}}={{#binary}}UNHEX(:{{name}}){{/binary}}{{^binary}}:{{name}}{{/binary}},';
            }
        {{/explode}}

        if (empty(\$set)){
            return false;
        }

        \$sql .= substr(\$set, 0, -1);

        \$pdo = self::database();

        {{#primary}}{{{sql}}}{{/primary}}

        {{#json}}self::jsonSQLReporting(\\func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        if (!self::bind(\$stmt, \$argv)){
            return false;
        }

        \$return = array_merge(\$return, \$argv);

        return true;

    }

    /**
    * @param array \$remove
    * @param string|null \$primary
    * @param array \$argv
    * @return bool
    */
    public static function Delete(array &\$remove, string \$primary = null, array \$argv) : bool
    {
    {{#carbon_table}}
        if (null !== \$primary) {
            return carbons::Delete(\$remove, \$primary, \$argv);
        }

        /**
         *   While useful, we've decided to disallow full
         *   table deletions through the rest api. For the
         *   n00bs and future self, "I got chu."
         */
        if (empty(\$argv)) {
            return false;
        }

        self::\$injection = [];
        /** @noinspection SqlResolve */
        \$sql = 'DELETE c FROM {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}carbons c 
                JOIN {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} on c.entity_pk = follower_table_id';

        \$pdo = self::database();

        \$sql .= ' WHERE ' . self::buildWhere(\$argv, \$pdo);

        self::jsonSQLReporting(\\func_get_args(), \$sql);

        \$stmt = \$pdo->prepare(\$sql);

        \$r = self::bind(\$stmt, \$argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        \$r and \$remove = null;

        return \$r;
    {{/carbon_table}}
    {{^carbon_table}}
        self::\$injection = [];
        /** @noinspection SqlResolve */
        \$sql = 'DELETE FROM {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ';

        \$pdo = self::database();

        if (null === \$primary) {
        /**
        *   While useful, we've decided to disallow full
        *   table deletions through the rest api. For the
        *   n00bs and future self, "I got chu."
        */
        if (empty(\$argv)) {
            return false;
        }


        \$sql .= ' WHERE ' . self::buildWhere(\$argv, \$pdo);
        } {{#primary}}{{#sql}}else {
        {{{sql}}}
        }{{/sql}}{{/primary}}

        {{#json}}self::jsonSQLReporting(\\func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        \$r = self::bind(\$stmt, \$argv);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        \$r and \$remove = null;

        return \$r;
    {{/carbon_table}}
    }
}

STRING;

    }
}

