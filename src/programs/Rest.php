<?php /** @noinspection ForgottenDebugOutputInspection */

namespace CarbonPHP\Programs;


use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Tables\Carbons;
use PDO;
use ReflectionException;
use ReflectionMethod;
use function count;
use function in_array;
use function random_int;


class Rest implements iCommand
{
    use ColorCode, Composer, Background, MySQL {
        ColorCode::colorCode insteadof Background;
        ColorCode::colorCode insteadof Composer;
        __construct as setup;
        cleanUp as removeFiles;
    }

    private string $schema;
    private string $user;
    private string $password;
    private bool $cleanUp = false;

    public function cleanUp(): void
    {
        $this->cleanUp and $this->removeFiles();
    }

    public function usage(): void
    {
        print <<<END
\n
\t           Question Marks Denote Optional Parameters
\t           Order does not matter.
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage::
\t  php index.php rest  

\t       -help                        - this dialogue 

\t       -h [HOST]                    - IP address

\t       -s [SCHEMA]                  - Its that tables schema!!!!
\t                                              Defaults to DB_NAME in config file passed to CarbonPHP
\t                                              Currently: "$this->schema"

\t       -u [USER]                    - mysql username
\t                                              Defaults to DB_USER in config file passed to CarbonPHP
\t                                              Currently: "$this->user"

\t       -p [PASSWORD]                - if ya got one
\t                                              Defaults to DB_PASS in config file passed to CarbonPHP
\t                                              Currently: "$this->password"

\t       -autoTarget                   - Use composer.json's ['autoload']['psr-4']['Tables\\'] value under CarbonPHP::\$app_root

\t       -target [rest_dir_path]       - the dir to store the rest generated api
\t                                              Defaults to CarbonPHP::\$app_root . 'tables/'

\t       -namespace [full_namespace]   - the namespace to assign each table to. 
\t                                              Defaults to 'Tables\\'

\t       -prefix                       - prefix to remove from class names. Defaults to none ''.

\t       -json                         - enable global json reporting (recommended)

\t       -r                            - specify that a primary key is required for generation

\t       -l [tableName(s),[...?,[]]]  - comma separated list of specific tables to capture

\t       -v [?debug]                   - Verbose output, if === debug follows this tag even more output is given

\t       -f [file_of_Tables]           - file of tables names separated by eol

\t       -x                            - Stops the file clean up files created for build

\t       -mysqldump [executable]       - path to mysqldump command

\t       -mysql  [executable]          - path to mysql command

\t       -dump [dump]                  - path to a mysqldump sql export

\t       -cnf [cnf_path]               - path to a mysql cnf file

\t       -trigger                      - build triggers and history tables for binary primary keys

\t       -react [Dir_Path]             - creates a smart reference for you rest ops                     
\n
END;
        exit(1);
    }

    /**
     * Rest constructor.
     * @param $CONFIG
     */
    public function __construct($CONFIG)
    {
        ini_set('memory_limit', '2048M');  // TODO - make this a config variable
        $this->setup($CONFIG);
        [$CONFIG] = $CONFIG;
        $this->schema = $CONFIG['DATABASE']['DB_NAME'] ?? '';
        $this->user = $CONFIG['DATABASE']['DB_USER'] ?? '';
        $this->password = $CONFIG['DATABASE']['DB_PASS'] ?? '';
    }

    /** @noinspection SubStrUsedAsStrPosInspection */
    public function run(array $argv): void
    {
        // Check command line args, password is optional
        self::colorCode("\tBuilding Rest Api!\n", 'blue');

        // C syntax
        $argc = count($argv);

        // These are PDO const types, so we'll eliminate one complexity by evaluating them before inserting into the template
        $PDO = [0 => PDO::PARAM_NULL, 1 => PDO::PARAM_BOOL, 2 => PDO::PARAM_INT, 3 => PDO::PARAM_STR];
        // set default values
        $rest = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        $clean = true;
        $json = $carbon_namespace = CarbonPHP::$app_root . 'src' . DS === CarbonPHP::CARBON_ROOT;
        $targetDir = CarbonPHP::$app_root . ($carbon_namespace ? 'src/tables/' : 'tables/');
        $only_these_tables = $history_table_query = $mysql = null;
        $verbose = $debug = $primary_required = $delete_dump = $skipTable = $logClasses = false;
        $target_namespace = 'Tables\\';
        $prefix = '';
        $exclude_these_tables = [];


        $react = $carbon_namespace ? CarbonPHP::$app_root . 'view/assets/react/src/variables/' : false;

        // TODO - we shouldn't open ourselfs for sql injection, was this a bandage
        try {
            $subQuery = 'C6SUB' . random_int(0, 1000);
        } catch (\Exception $e) {
            $subQuery = 'C6SUBTX2';
        }


        /** @noinspection ForeachInvariantsInspection - as we need $i++ */
        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                case '-react':
                    if ($carbon_namespace) {
                        self::colorCode("\tReact directory hardcoded for C6, unnecessary flag.\n", 'blue');
                        break;
                    }
                    $react = $argv[++$i];
                    break;
                case '-prefix':
                    $prefix = $argv[++$i];
                    break;
                case '-namespace':
                    $target_namespace = $argv[++$i];

                    $target_namespace_array = explode('\\', $target_namespace);

                    $target_namespace_array = array_filter($target_namespace_array);

                    $target_namespace = implode('\\', $target_namespace_array);

                    if (count($target_namespace_array) === 1) {
                        switch (strtolower(readline("Does the namespace ($target_namespace) look correct? [Y,n]"))) {
                            default:
                                break;
                            case 'no':
                            case 'n':
                                /** @noinspection PhpUnhandledExceptionInspection */
                            self::colorCode('You may need to add more escaping "\\" depending on how may contexts the string goes through. We will try to fix over escaped namespaces.', 'red', true);
                        }
                    }

                    break;
                case '-json':
                    $json = true;
                    break;
                case '-autoTarget':
                    if ($carbon_namespace) {
                        break;
                    }
                    $composer = self::getComposerConfig();
                    $composer = $composer['autoload']['psr-4']["Tables\\"] ?? false;
                    if (!$composer) {
                        print "\n\nFailed to find an entry for ['autoload']['psr-4']['Tables\\'] in your composer.json\n" .
                            "\tThe -autoTarget flag failed the build.";
                        exit(1);
                    }
                    $targetDir = CarbonPHP::$app_root . $composer;
                    unset($composer);
                    break;
                case '-target':
                    $targetDir = $argv[++$i];
                    break;
                case '-subPrefix':
                    $subQuery = $argv[++$i];
                    break;
                case '-x':
                    $this->cleanUp = true;
                    break;
                case '-v':
                    if (isset($argv[++$i]) && strtolower($argv[$i]) === 'debug') {
                        print "\tDebug mode is best when paired with the optional (-l or -f) flags. Use -help for more information.\n";
                        $debug = true;
                    } else {
                        --$i;
                    }
                    $verbose = true;
                    break;
                case '-carbon':
                    $carbon_namespace = true;
                    break;
                case '-trigger':
                    $history_table_query = true;
                    $query = <<<QUERY
CREATE TABLE IF NOT EXISTS carbon_history_logs
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
                case '-h':
                    $this->CONFIG['DATABASE']['DB_HOST'] = $argv[++$i];
                    break;
                case '-s':
                    $this->schema = $argv[++$i];
                    break;
                case '-r':
                    $primary_required = true;
                    break;
                case '-u':
                    $this->CONFIG['DATABASE']['DB_USER'] = $argv[++$i];
                    break;
                case '-p':
                    $this->CONFIG['DATABASE']['DB_PASS'] = $argv[++$i];
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
                    unset($file);
                    break;
                case '-excludeFile':
                    if (empty($file = file_get_contents((string)$argv[++$i]))) {
                        print 'Could not open file [ ' . $argv[$i] . " ] for input\n\n";
                        exit(1);
                    }
                    $exclude_these_tables = explode(PHP_EOL, $file);
                    unset($file);
                    break;
                case '-mysqldump':
                    // the path to the mysqldump executable
                    $mysqldump = $argv[++$i];
                    break;
                case '-mysql':
                    // the path to the mysql executable
                    $mysql = $argv[++$i];
                    break;
                case '-dump':
                    // path to an sql dump file
                    $dump = $argv[++$i];
                    break;
                case '-cnf':
                    // path to an sql cnf pass file
                    $this->buildCNF($argv[++$i]);
                    break;
                case '-logClasses':
                    $logClasses = true;
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
            print 'Directory does not exist, attempting to create it.' . PHP_EOL;
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                print 'The target directory appears invalid "' . $targetDir . '"' . PHP_EOL;
                exit(1);
            }
        } else if ('/' !== substr($targetDir, -1)) {
            $targetDir .= DS;
        }

        if (empty($this->schema) || $this->schema === '') {
            print 'You must specify the table schema!' . PHP_EOL;
            exit(1);
        }

        $this->mysqldump = $dumpFilePath = $dump ?? $this->MySQLDump($mysqldump ?? null);

        if (!file_exists($this->mysqldump)) {
            print 'Could not load mysql dump file!' . PHP_EOL;
            exit(1);
        }

        if (empty($this->mysqldump = file_get_contents($this->mysqldump))) {
            print 'Contents of the mysql dump file appears empty. Build Failed!';
            exit(1);
        }

        // This is our mustache template engine implemented in php, used for rendering user content
        $mustache = new \Mustache_Engine();

        $verbose and var_dump($this->mysqldump);

        // match all tables from a mysql dump
        preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=)#', $this->mysqldump, $matches);

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

                switch ($words_in_insert_stmt[0]) {
                    case 'CREATE':
                        $tableName = trim($words_in_insert_stmt[2], '`');               // Table Name

                        // TRY to load previous validation functions

                        $rest[$tableName] = [
                            'subQuery' => $subQuery,
                            'subQueryLength' => strlen($subQuery),
                            'json' => $json,
                            'binary_primary' => false,
                            'carbon_namespace' => $carbon_namespace,
                            'namespace' => $carbon_namespace ? 'CarbonPHP\Tables' : $target_namespace,
                            'carbon_table' => false,
                            'database' => $this->schema,
                            // We need to catch circular dependencies
                            'dependencies' => $rest[$tableName]['dependencies'] ?? [],
                            'TableName' => $tableName,
                            'ucEachTableName' => implode('_', array_map('ucfirst', explode('_', preg_replace("/^$prefix/", '', $tableName)))),
                            'primarySort' => '',
                            'custom_methods' => '',
                            'primary' => [],
                        ];

                        if (file_exists($validation = $targetDir . $tableName . '.php')) {
                            $validation = file_get_contents($validation);

                            preg_match_all('#public const REGEX_VALIDATION\s?=\s? \[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {
                                $rest[$tableName]['regex_validation'] = $matches[0][0];
                            }

                            preg_match_all('#public const PHP_VALIDATION\s?=\s? \[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {
                                $rest[$tableName]['php_validation'] = $matches[0][0];
                            }

                            $restStaticNameSpaces = $this->restTemplateStaticNameSpace();

                            array_splice($restStaticNameSpaces, 2, 0, [
                                'use CarbonPHP\Interfaces\iRest;',
                                'use CarbonPHP\Interfaces\iRestfulReferences;',
                            ]);

                            $matches = [];

                            // the second half of this regex is from google which matches
                            if (false === preg_match_all('#\n(use (?:function)? ?(?:(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\]*[a-zA-Z0-9_\x7f-\xff]+)|[a-zA-Z_\x80-\xff][\\\a-zA-Z0-9_\x80-\xff]+);)#i', $validation, $matches)) {
                                print 'An unexpected regex error occurred during the namespace matching/cache';
                                exit(1);
                            }

                            $userCustomImports = array_diff(
                                $matches[1],
                                $restStaticNameSpaces);


                            $rest[$tableName]['CustomImports'] = implode(PHP_EOL, $userCustomImports);

                            // methods
                            $fullTableClassName = $rest[$tableName]['namespace'] . '\\' . $rest[$tableName]['ucEachTableName'];


                            if (!class_exists($fullTableClassName)) {
                                print "We failed to load the class methods for the table '$fullTableClassName'. It was located in $targetDir, but cannot be auto-loaded. Please add the location to Composers PSR-4.\n\n";
                                exit(1);
                            }

                            $generatedClassCustomMethods = get_class_methods($fullTableClassName);

                            if ($generatedClassCustomMethods === null) {
                                print 'An unexpected error occurred when using get_class_methods.';
                                exit(1);
                            }

                            $methods = array_diff(
                                $generatedClassCustomMethods,
                                get_class_methods(Carbons::class));         // or null.. smh


                            foreach ($methods as $method) {

                                try {
                                    $func = new ReflectionMethod($fullTableClassName, $method);

                                    $comment = $func->getDocComment();

                                } catch (ReflectionException $e) {
                                    print 'Failed to load custom functions defined in restful class using ReflectionMethod.';
                                    exit(1);
                                }

                                $f = $func->getFileName();

                                $start_line = $func->getStartLine() - 1;

                                $end_line = $func->getEndLine();

                                $length = $end_line - $start_line;

                                $source = file_get_contents($f);

                                $source = preg_split('/' . PHP_EOL . '/', $source);

                                $body = implode(PHP_EOL, array_slice($source, $start_line, $length));

                                $rest[$tableName]['custom_methods'] .= ($comment ? "    $comment\n" : '') . $body . PHP_EOL . PHP_EOL;
                            }
                        }


                        // 'only these tables' is specified in the command line arguments (via file or comma list)
                        if ((!empty($exclude_these_tables) && in_array($tableName, $exclude_these_tables, true))
                            || (!empty($only_these_tables) && !in_array($tableName, $only_these_tables, true))) {
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
                            self::colorCode("\tGenerating {$tableName}\n", 'blue');
                            $debug and var_dump($table);
                        }
                        break;

                    case 'PRIMARY':
                        // Composite Primary Keys are a thing,  TODO - optimise the template for none vs single vs double key
                        $primary = explode('`,`', trim($words_in_insert_stmt[2], '(`),'));

                        $rest[$tableName]['primarySort'] = implode(',', $primary);

                        // Build the insert stmt - used in put rn / exported in abstract rest
                        $sql = [];
                        foreach ($primary as $key) {
                            if (in_array($key, $binary, true)) {
                                // binary data is expected as hex @ rest call (GET,PUT,DELETE)
                                $sql[] = ' ' . $key . '=UNHEX(\'.self::addInjection($primary, $pdo).\')';
                            } else {
                                // otherwise just create the stmt normally
                                $sql[] = ' ' . $key . '=\'.self::addInjection($primary, $pdo).\'';
                            }
                            $rest[$tableName]['primary'][] = ['name' => $key];
                        }
                        $rest[$tableName]['primary'][] = ['sql' => '$sql .= \' WHERE ' . implode(' OR ', $sql) . '\';'];
                        // end - soon to deprecate
                        break;

                    case 'CONSTRAINT':

                        //  if (count($words_in_insert_stmt) !== 8) {
                        //      print  PHP_EOL . $tableName  . PHP_EOL and die;
                        //  }

                        $foreign_key = trim($words_in_insert_stmt[4], '()`');
                        $references_table = trim($words_in_insert_stmt[6], '`');
                        $references_column = trim($words_in_insert_stmt[7], '()`,');

                        if ($references_table === 'carbons' && in_array($foreign_key, $primary, true)) {
                            $rest[$tableName]['carbon_table'] = $tableName !== 'carbons';
                        }

                        // We need to catch circular dependencies as mysql dumps print schemas alphabetically
                        if (!isset($rest[$references_table])) {
                            $rest[$references_table] = ['dependencies' => []];
                        } else if (!isset($rest[$references_table]['dependencies'])) {
                            $rest[$references_table]['dependencies'] = [];
                        }

                        $verbose and self::colorCode("\nreference found ::\t$tableName([$foreign_key => $references_column])\n", 'magenta');

                        $rest[$references_table]['dependencies'][] = [$tableName => [$foreign_key => $references_column]];
                        break;


                    default:

                        if ($words_in_insert_stmt[0][0] === '`') {

                            // This is expected to be the second condition run in foreach
                            // columns is just a list of column
                            $name = $rest[$tableName]['columns'][] = trim($words_in_insert_stmt[0], '`');

                            // Explode hold all information about column
                            $rest[$tableName]['explode'][$column]['name'] = $name;
                            $rest[$tableName]['explode'][$column]['caps'] = strtoupper($name);

                            $type = strtolower($words_in_insert_stmt[1]);

                            // exploding strings like 'mediumint(9)' and 'binary(16)'
                            if (count($argv = explode('(', $type)) > 1) {
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

                                $default = '';

                                // todo - the negitive case  && substr($words_in_insert_stmt[$key], -w) === '\\\\''

                                // if it ends in '  aka '0'
                                if (substr($words_in_insert_stmt[$key], -1) === '\'') {
                                    $default = $words_in_insert_stmt[$key];
                                    // if it ends with ',  as '0',
                                } else if (substr($words_in_insert_stmt[$key], -2) === '\',') {
                                    $default = trim($words_in_insert_stmt[$key], ',');
                                    // if it doesnt start with '  as CURRENT_TIMESTAMP
                                } else if (substr($words_in_insert_stmt[$key], 0, 1) !== '\''){
                                    $default = rtrim($words_in_insert_stmt[$key], ',');
                                } else { // the first index does start in ' and doesnt end in '
                                    do {
                                        if ($key > 10) {
                                            sortDump($words_in_insert_stmt);
                                        }
                                        $default .= ' ' . $words_in_insert_stmt[$key];
                                        $key++;
                                    } while (substr($words_in_insert_stmt[$key], -1) !== '\''
                                    && substr($words_in_insert_stmt[$key], -2) !== '\',');
                                    $default .= ' ' . $words_in_insert_stmt[$key];
                                    $default = trim($default, ', ');
                                }


                                if ($default === 'CURRENT_TIMESTAMP') {
                                    // Were going to skip columns with this set as the default value
                                    // Trying to insert this condition w/ PDO is problematic
                                    $skipping_col[] = $name;
                                    $rest[$tableName]['explode'][$column]['skip'] = true;
                                } else if (strpos($default, '\'') !== 0) {
                                    // We need to escape values for php
                                    $default = "'$default'";
                                }
                                /** @noinspection NestedTernaryOperatorInspection */
                                $rest[$tableName]['explode'][$column]['default'] = ($default === "'NULL'" ? 'null' : ($cast_binary_default ? 'null' : $default));
                            }

                            // As far as I can tell the AUTO_INCREMENT condition the last possible word in the query
                            $auto_inc = count($words_in_insert_stmt) - 1;
                            if (isset($words_in_insert_stmt[$auto_inc]) && $words_in_insert_stmt[$auto_inc] === 'AUTO_INCREMENT,') {
                                $skipping_col[] = $name;
                                $rest[$tableName]['explode'][$column]['skip'] = true;
                                $verbose and self::colorCode("\tThe Table '$tableName' contains an AUTO_INCREMENT column. This is bad for scaling.
                                                                        \tConsider switching to binary(16) and letting this rest API manage column uniqueness.\n", 'red');
                            }

                            $column++;
                        }
                        break;

                }
                // END SWITCH
            }
            // END PARSE

            // We need to break from this tables too if the tables is not in ( -l -f )
            if ($skipTable) {
                $skipTable = false; // This is so we can stop analysing a full tables
                continue;
            }

            $rest[$tableName]['primaryExists'] = !empty($rest[$tableName]['primary']);

            // Make sure we didn't specify a flag that could cause us to move on...
            if (empty($rest[$tableName]['primary'])) {
                $verbose and self::colorCode("\n\nThe tables {$rest[$tableName]['TableName']} does not have a primary key.\n", 'yellow');
                if ($primary_required) {
                    self::colorCode(" \tSkipping...\n ",);
                    continue;
                }
            } else {
                foreach ($rest[$tableName]['explode'] as &$value) {
                    if (in_array($value, [
                        'pageSize',
                        'pageNumber'
                    ])) {
                        self::colorCode($rest[$tableName]['TableName'] . " uses reserved C6 RESTFULL keywords as a column identifier => $value\n\tRest Failed", 'red');
                        die(1);
                    }

                    if (false !== in_array($value['name'], $primary, true)) {
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

                if (!in_array($value, $skipping_col, true)) {

                    // This suffixes an extra comma
                    $rest[$tableName]['listed'] .= $value . ', ';

                    if (in_array($value, $binary, true)) {
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

            $rest[$tableName]['custom_methods'] = rtrim($rest[$tableName]['custom_methods'], PHP_EOL);

            $logClasses && print $rest[$tableName]['TableName'] . ', ';

            file_put_contents($targetDir . $rest[$tableName]['ucEachTableName'] . '.php', $mustache->render($this->restTemplate(), $rest[$tableName]));
        }

        foreach ($rest as $tableName => $parsed) {
            if (empty($rest[$tableName]['explode'])) {
                self::colorCode("\nYou have a reference with wasn't resolved in the dump. Please search for '$tableName' in your "
                    . "mysqldump.sql file. This typically occurs when resolving to an outside schema, which typically indicates and error.\n", 'red');
            }
        }

        if ($react) {
            [$restAccessors, $interfaces] = $this->reactTemplate();
            $references_tsx = $interfaces_tsx = $global_column_tsx = '';
            $all_interface_types = [];
            foreach ($rest as $tableName => $parsed) {
                if (empty($rest[$tableName]['explode'])) {
                    continue;
                }

                if (!class_exists($table = ($carbon_namespace ? 'CarbonPHP\Tables\\' : $target_namespace) . $tableName)) {
                    $verbose and self::colorCode("\n\nCouldn't locate class '$table' for react validations. This may indicate a new table.\n", 'yellow');
                    continue;
                }

                if (!is_subclass_of($table, \CarbonPHP\Rest::class)) {
                    continue;
                }

                $imp = array_map('strtolower', array_keys(class_implements($table)));

                if (!in_array(strtolower(iRest::class), $imp, true) &&
                    !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                    continue;
                }

                if (defined("$table::REGEX_VALIDATION")) {
                    $regex_validations = constant("$table::REGEX_VALIDATION");
                    if (!is_array($regex_validations)) {
                        self::colorCode("\nRegex validations for $table must be an array!", 'red');
                        exit(1);
                    }
                    $parsed['regex_validation'] = [];

                    if (!empty($regex_validations)) {

                        $str_lreplace = static function (string $search, string $replace, string $subject) {
                            $pos = strrpos($subject, $search);

                            if ($pos !== false) {
                                $subject = substr_replace($subject, $replace, $pos, strlen($search));
                            }

                            return $subject;
                        };

                        foreach ($regex_validations as $columnName => $regex_validation) {
                            $regex_validation = $str_lreplace($regex_validation[0], '/', $regex_validation);
                            $regex_validation[0] = '/';
                            $parsed['regex_validation'][] = [
                                'name' => $columnName,
                                'validation' => $regex_validation
                            ];
                        }
                    } else {
                        $parsed['regex_validation'] = [];
                    }
                }

                $references_tsx .= PHP_EOL . $mustache->render($restAccessors, $parsed);
                $interfaces_tsx .= PHP_EOL . $mustache->render($interfaces, $parsed);
                $global_column_tsx .= PHP_EOL . $mustache->render(/** @lang Handlebars */ "{{#explode}}'{{TableName}}.{{name}}':'{{name}}',\n    {{/explode}}", $parsed);
                $all_interface_types[] = 'i' . $rest[$tableName]['ucEachTableName'];
                $all_table_names_types[] = $rest[$tableName]['TableName'];
            }

            if (empty($all_interface_types) || empty($all_table_names_types)) {
                self::colorCode('The value of $all_interface_types must not be empty. Rest Failed.', 'red');
                exit(1);
            }

            $all_interface_types = implode(' | ', $all_interface_types);

            // $all_table_names_types = implode(PHP_EOL . '" | "', $all_table_names_types);

            $export = /** @lang TypeScript JSX */
                "

export const C6 = {

    SELECT: '" . \CarbonPHP\Rest::SELECT . "',
    UPDATE: '" . \CarbonPHP\Rest::UPDATE . "',
    WHERE: '" . \CarbonPHP\Rest::WHERE . "',
    LIMIT: '" . \CarbonPHP\Rest::LIMIT . "',
    PAGINATION: '" . \CarbonPHP\Rest::PAGINATION . "',
    ORDER: '" . \CarbonPHP\Rest::ORDER . "',
    DESC: '" . \CarbonPHP\Rest::DESC . "',
    ASC: '" . \CarbonPHP\Rest::ASC . "',
    JOIN: '" . \CarbonPHP\Rest::JOIN . "',
    INNER: '" . \CarbonPHP\Rest::INNER . "',
    LEFT: '" . \CarbonPHP\Rest::LEFT . "',
    RIGHT: '" . \CarbonPHP\Rest::RIGHT . "',
    DISTINCT: '" . \CarbonPHP\Rest::DISTINCT . "',
    COUNT: '" . \CarbonPHP\Rest::COUNT . "',
    SUM: '" . \CarbonPHP\Rest::SUM . "',
    MIN: '" . \CarbonPHP\Rest::MIN . "',
    MAX: '" . \CarbonPHP\Rest::MAX . "',
    GROUP_CONCAT: '" . \CarbonPHP\Rest::GROUP_CONCAT . "',
    
    $references_tsx
    
};

$interfaces_tsx

export const COLUMNS = {
      $global_column_tsx
};

//export type RestTables = \"\$all_table_names_types\";

export type RestTableInterfaces = $all_interface_types;

export const convertForRequestBody = function(restfulObject: RestTableInterfaces, tableName: string) {
  let payload = {};
  Object.keys(restfulObject).map(value => {
    let exactReference = value.toUpperCase();
    if (exactReference in C6[tableName]) {
      payload[C6[tableName][exactReference]] = restfulObject[value]
    }
    return true;
  });
  return payload;
};

";
            file_put_contents($react . 'C6.tsx', $export);
        }

        // todo - log classes
        $logClasses && print "\n";

        self::colorCode("\tFinished Building REST ORM!\n\n");


        // TODO - validate the methods defined in table space

        /**
         * Now that the full dump has been parsed, we need to build our triggers
         * using the foreign key analysis
         */

        if ($history_table_query) {
            print "\tBuilding Triggers!\n";

            $triggers = '';
            foreach ($rest as $table) {
                if (in_array($table['TableName'], ['sys_resource_creation_logs', 'sys_resource_history_logs'])) {
                    continue;
                }
                if ($table['binary_primary'] && ($only_these_tables === null || in_array($table['TableName'], $only_these_tables, true))) {
                    $triggers .= self::trigger($table['TableName'], $table['columns'], $table['binary_trigger'] ?? [], $table['dependencies'], $table['primary'][0]['name']);
                }
            }

            file_put_contents('triggers.sql', 'DELIMITER ;;' . PHP_EOL . $triggers . PHP_EOL . 'DELIMITER ;');

            $this->MySQLSource($verbose, 'triggers.sql', $mysql ?? null);
        }

        // debug is a subset of the verbose flag
        /** @noinspection ForgottenDebugOutputInspection */
        $debug and var_dump($rest['clients']);

        self::colorCode("\tSuccess!\n\n");

    }

    /**
     * @param $table
     * @param $columns
     * @param $binary
     * @param $dependencies
     * @param $primary
     * @return string
     */
    public static function trigger($table, $columns, $binary, $dependencies, $primary): string
    {
        $json_mysql = static function ($op = 'NEW') use ($columns, $binary) {
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

        $history_sql = static function ($operation_type = 'POST') use ($table, $primary) {
            $query = '';
            $relative_time = $operation_type === 'POST' ? 'NEW' : ($operation_type === 'PUT' ? 'NEW' : 'OLD');
            switch ($operation_type) {
                case 'POST':
                    // todo - triggers? logs? idk.. i dont remember
                    /** @noinspection SqlResolve */
                    $query = "INSERT INTO carbon_creation_logs (`uuid`, `resource_type`, `resource_uuid`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary);\n";
                case 'PUT':
                case 'DELETE':
                    /** @noinspection SqlResolve */
                    $query .= "INSERT INTO carbon_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`)
            VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary , '$operation_type', json);";
                    break;
                case 'GET':
                default:
                    break;
            }

            return $query;
        };

        $delete_children = static function () use ($dependencies) {
            $sql = '';
            if (!empty($dependencies)) {
                foreach ($dependencies as $array) {
                    foreach ($array as $child => $relation) {
                        foreach ($relation as $c => $keys) {
                            /** @noinspection SqlResolve */
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

    /**
     * @return array
     */
    private function reactTemplate(): array
    {
        return [/** @lang Handlebars */ "
  {{TableName}}: {
    TABLE_NAME:'{{TableName}}',
    {{#explode}}
    {{caps}}: '{{TableName}}.{{name}}',
    {{/explode}}
    PRIMARY: [
        {{#primary}}{{#name}}'{{TableName}}.{{name}}',{{/name}}
        {{/primary}}
    ],
    COLUMNS: {
      {{#explode}}'{{TableName}}.{{name}}':'{{name}}',
      {{/explode}}
    },
    REGEX_VALIDATION: {
        {{#regex_validation}}
        '{{name}}': {{validation}},
        {{/regex_validation}}
    }

  },", /** @lang Handlebars */ "
export interface  i{{ucEachTableName}}{
      {{#explode}}'{{name}}'?: string;
      {{/explode}}
}
  "];
    }


    private function restTemplateStaticNameSpace(): array
    {
        return [
            'use PDO;',
            'use CarbonPHP\Rest;',
            'use CarbonPHP\Error\PublicAlert;',
            'use function array_key_exists;',
            'use function count;',
            'use function func_get_args;',
            'use function is_array;'
        ];
    }

    private function restTemplate(): string
    {
        $staticNamespaces = $this->restTemplateStaticNameSpace();

        array_splice($staticNamespaces, 2, 0, [
            'use CarbonPHP\Interfaces\\{{#primaryExists}}iRest{{/primaryExists}}{{^primaryExists}}iRestfulReferences{{/primaryExists}};',
        ]);

        $staticNamespaces = implode(PHP_EOL, $staticNamespaces);

        return /** @lang Handlebars */ <<<STRING
<?php 

namespace {{namespace}};

// Restful defaults
$staticNamespaces

// Custom User Imports
{{#CustomImports}}{{{CustomImports}}}{{/CustomImports}}

class {{ucEachTableName}} extends Rest implements {{#primaryExists}}iRest{{/primaryExists}}{{^primaryExists}}iRestfulReferences{{/primaryExists}}
{
    
    public const TABLE_NAME = '{{TableName}}';
    {{#explode}}
    public const {{caps}} = '{{TableName}}.{{name}}'; 
    {{/explode}}

    public const PRIMARY = [
        {{#primary}}{{#name}}'{{TableName}}.{{name}}',{{/name}}{{/primary}}
    ];

    public const COLUMNS = [
        {{#explode}}'{{TableName}}.{{name}}' => '{{name}}',{{/explode}}
    ];

    public const PDO_VALIDATION = [
        {{#explode}}'{{TableName}}.{{name}}' => ['{{mysql_type}}', '{{type}}', '{{length}}'],{{/explode}}
    ];
    
    /**
     * PHP validations works as follows:
     *  The first index '0' of PHP_VALIDATIONS will run after REGEX_VALIDATION's but
     *  before every other validation method described here below.
     *  The other index positions are respective to the request method calling the ORM
     *  or column which maybe present in the request.
     *  Column names using the 1 to 1 constants in the class maybe used for global
     *  specific methods when under PHP_VALIDATION, or method specific operations when under
     *  its respective request method, which only run when the column is requested or acted on.
     *  Global functions and method specific functions will receive the full request which
     *  maybe acted on by reference. All column specific validation methods will only receive
     *  the associated value given in the request which may also be received by reference.
     *  All methods MUST be declaired as static.
     */
    {{^php_validation}}
    public const PHP_VALIDATION = [ 
        [self::DISALLOW_PUBLIC_ACCESS],
        self::GET => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::POST => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::PUT => [ self::DISALLOW_PUBLIC_ACCESS ],    
        self::DELETE => [ self::DISALLOW_PUBLIC_ACCESS ],    
    ];{{/php_validation}} 
    {{#php_validation}} 
    {{{php_validation}}} 
    {{/php_validation}}
    {{^regex_validation}}
    
    /**
     * REGEX_VALIDATION
     * Regular Expression validations are run before and recommended over PHP_VALIDATION.
     * It is a 1 to 1 column regex relation with fully regex for preg_match_all().
     * Table generated column constants must be used.
     * @link https://php.net/manual/en/function.preg-match-all.php
     */
    public const REGEX_VALIDATION = [];{{/regex_validation}} 
    {{#regex_validation}}
    {{{regex_validation}}} 
    {{/regex_validation}}
    
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
    *              'order' => ['*column name*'=> '(ASC|DESC)'],  // i.e.  'username' => 'ASC'
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
    * @throws PublicAlert
    * @return bool
    */
    public static function Get(array &\$return, {{#primaryExists}}string \$primary = null, {{/primaryExists}}array \$argv = []): bool
    {
        \$pdo = self::database();

        \$sql = self::buildSelectQuery({{#primaryExists}}\$primary{{/primaryExists}}{{^primaryExists}}null{{/primaryExists}}, \$argv, {{^carbon_namespace}}'{{database}}'{{/carbon_namespace}}{{#carbon_namespace}}''{{/carbon_namespace}}, \$pdo);{{#json}}
        
        self::jsonSQLReporting(func_get_args(), \$sql);{{/json}}
        
        \$stmt = \$pdo->prepare(\$sql);

        self::bind(\$stmt);

        if (!\$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on {{ucEachTableName}}.', 'danger');
        }

        \$return = \$stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        {{#primary}}{{#sql}}
        if (\$primary !== null || (isset(\$argv[self::PAGINATION][self::LIMIT]) && \$argv[self::PAGINATION][self::LIMIT] === 1 && count(\$return) === 1)) {
            \$return = isset(\$return[0]) && is_array(\$return[0]) ? \$return[0] : \$return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        {{#explode}}{{#json}}if (array_key_exists('{{TableName}}.{{name}}', \$return)) {
                \$return['{{name}}'] = json_decode(\$return['{{name}}'], true);
            }
        {{/json}}{{/explode}}
        }{{/sql}}{{/primary}}

        return true;
    }

    /**
     * @param array \$argv
     * @param string|null \$dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     */
    public static function Post(array \$argv, string \$dependantEntityId = null){{^primaryExists}}: bool{{/primaryExists}}
    {   
        foreach (\$argv as \$columnName => \$postValue) {
            if (!array_key_exists(\$columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column \$columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        \$sql = 'INSERT INTO {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ({{listed}}) VALUES ({{{implode}}})';

        {{#json}}self::jsonSQLReporting(func_get_args(), \$sql);{{/json}}

        \$stmt = self::database()->prepare(\$sql);

    {{#explode}}{{#primary_binary}}{{^carbon_table}}
        \${{name}} = \$id = \$argv['{{TableName}}.{{name}}'] ?? self::fetchColumn('SELECT (REPLACE(UUID() COLLATE utf8_unicode_ci,"-",""))')[0];
        \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
    {{/carbon_table}}{{#carbon_table}}
        \${{name}} = \$id = \$argv['{{TableName}}.{{name}}'] ?? self::beginTransaction(self::class, \$dependantEntityId);
        \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
    {{/carbon_table}}{{/primary_binary}}{{^primary_binary}}{{^skip}}{{^length}}
        \$stmt->bindValue(':{{name}}',{{#json}}json_encode(\$argv['{{TableName}}.{{name}}']){{/json}}{{^json}}{{^default}}\$argv['{{TableName}}.{{name}}']{{/default}}{{#default}}array_key_exists('{{TableName}}.{{name}}',\$argv) ? \$argv['{{TableName}}.{{name}}'] : {{default}}{{/default}}{{/json}}, {{type}});{{/length}}
    {{#length}}{{^default}}
        if (!array_key_exists('{{TableName}}.{{name}}', \$argv)) {
            throw new PublicAlert('Required argument "{{TableName}}.{{name}}" is missing from the request.', 'danger');
        }{{/default}}
        \${{name}} = {{^default}}\$argv['{{TableName}}.{{name}}']{{/default}}{{#default}} \$argv['{{TableName}}.{{name}}'] ?? {{{default}}}{{/default}};
        \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
    {{/length}}{{/skip}}{{/primary_binary}}{{/explode}}


    {{#binary_primary}}
        return \$stmt->execute() ? \$id : false;{{/binary_primary}}
    {{^binary_primary}}{{^carbon_table}}
        return \$stmt->execute();
    {{/carbon_table}}{{/binary_primary}}
    }
    
    /**
    * @param array \$return
    {{#primaryExists}}* @param string \$primary{{/primaryExists}}
    * @param array \$argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &\$return, {{#primaryExists}}string \$primary,{{/primaryExists}} array \$argv) : bool
    {
        {{#primaryExists}}
        if (empty(\$primary)) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.', 'danger');
        }
        
        if (array_key_exists(self::UPDATE, \$argv)) {
            \$argv = \$argv[self::UPDATE];
        }
        {{/primaryExists}}
        {{^primaryExists}}
        \$where = \$argv[self::WHERE];

        \$argv = \$argv[self::UPDATE];

        if (empty(\$where) || empty(\$argv)) {
            throw new PublicAlert('Restful tables which have no primary key must be updated specific where conditions.', 'danger');
        }
        {{/primaryExists}}
        
        foreach (\$argv as \$key => \$value) {
            if (!array_key_exists(\$key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column \$key, because it does not appear to exist.', 'danger');
            }
        }

        \$sql = 'UPDATE {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ' . ' SET '; // intellij cant handle this otherwise

        \$set = '';

        {{#explode}}
        if (array_key_exists('{{TableName}}.{{name}}', \$argv)) {
            \$set .= '{{name}}={{#binary}}UNHEX(:{{name}}){{/binary}}{{^binary}}:{{name}}{{/binary}},';
        }
        {{/explode}}
        
        \$sql .= substr(\$set, 0, -1);

        \$pdo = self::database();

        {{#primary}}{{{sql}}}{{/primary}}
        {{^primary}}\$sql .= ' WHERE ' . self::buildWhere(\$where, \$pdo, '{{TableName}}', self::PDO_VALIDATION);{{/primary}}

        {{#json}}self::jsonSQLReporting(func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        {{#explode}}
        if (array_key_exists('{{TableName}}.{{name}}', \$argv)) {
        {{^length}}
            \$stmt->bindValue(':{{name}}',{{#json}}json_encode(\$argv['{{TableName}}.{{name}}']){{/json}}{{^json}}\$argv['{{TableName}}.{{name}}']{{/json}}, {{type}});
        {{/length}}
        {{#length}}
            \${{name}} = \$argv['{{TableName}}.{{name}}'];
            \$stmt->bindParam(':{{name}}',\${{name}}, {{type}}, {{length}});
        {{/length}}
        }
        {{/explode}}

        self::bind(\$stmt);

        if (!\$stmt->execute()) {
            throw new PublicAlert('Restful table {{ucEachTableName}} failed to execute the update query.', 'danger');
        }
        
        if (!\$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        \$argv = array_combine(
            array_map(
                static function(\$k) { return str_replace('{{TableName}}.', '', \$k); },
                array_keys(\$argv)
            ),
            array_values(\$argv)
        );

        \$return = array_merge(\$return, \$argv);

        return true;

    }

    /**
    * @param array \$remove
    * @param string|null \$primary
    * @param array \$argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Delete(array &\$remove, {{#primaryExists}}string \$primary = null, {{/primaryExists}}array \$argv = []) : bool
    {
    {{#carbon_table}}
        if (null !== \$primary) {
            return Carbons::Delete(\$remove, \$primary, \$argv);
        }

        /**
         *   While useful, we've decided to disallow full
         *   table deletions through the rest api. For the
         *   n00bs and future self, "I got chu."
         */
        if (empty(\$argv)) {
            throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
        }
        
        \$sql = 'DELETE c FROM {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}carbons c 
                JOIN {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} on c.entity_pk = {{#primary}}{{#name}}{{TableName}}.{{name}}{{/name}}{{/primary}}';

        \$pdo = self::database();

        \$sql .= ' WHERE ' . self::buildWhere(\$argv, \$pdo, '{{TableName}}', self::PDO_VALIDATION);{{#json}}
        
        self::jsonSQLReporting(func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        self::bind(\$stmt);

        \$r = \$stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        \$r and \$remove = [];

        return \$r;
    {{/carbon_table}}
    {{^carbon_table}}
        \$sql = 'DELETE FROM {{^carbon_namespace}}{{database}}.{{/carbon_namespace}}{{TableName}} ';

        \$pdo = self::database();
        {{#primary}}{{#name}}
        if (null === \$primary) {
           /**
            *   While useful, we've decided to disallow full
            *   table deletions through the rest api. For the
            *   n00bs and future self, "I got chu."
            */
            if (empty(\$argv)) {
                throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
            }
            
            \$where = self::buildWhere(\$argv, \$pdo, '{{TableName}}', self::PDO_VALIDATION);
            
            if (empty(\$where)) {
                throw new PublicAlert('The where condition provided appears invalid.', 'danger');
            }

            \$sql .= ' WHERE ' . \$where;
        } {{/name}}{{#sql}}else {
            {{{sql}}}
        }{{/sql}}{{/primary}}
        {{^primary}}
        if (empty(\$argv)) {
            throw new PublicAlert('When deleting from restful tables with out a primary key additional arguments must be provided.', 'danger');
        } 
         
        \$sql .= ' WHERE ' . self::buildWhere(\$argv, \$pdo, '{{TableName}}', self::PDO_VALIDATION);{{/primary}}

        {{#json}}self::jsonSQLReporting(func_get_args(), \$sql);{{/json}}

        \$stmt = \$pdo->prepare(\$sql);

        self::bind(\$stmt);

        \$r = \$stmt->execute();

        \$r and \$remove = [];

        return \$r;
    {{/carbon_table}}
    }
     
{{{custom_methods}}}
    
}

STRING;

    }
}

