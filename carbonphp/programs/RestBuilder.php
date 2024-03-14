<?php /** @noinspection ForgottenDebugOutputInspection */

namespace CarbonPHP\Programs;


use CarbonPHP\Abstracts\Background;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Composer;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Rest;
use CarbonPHP\Restful\RestLifeCycle;
use CarbonPHP\Restful\RestTemplates;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\Tables\History_Logs;
use ReflectionException;
use ReflectionMethod;
use function count;
use function in_array;


class RestBuilder implements iCommand
{

    // database
    private string $schema;
    private string $user;
    private string $password;


    // rest params
    private string $target_namespace;
    private string $table_prefix;


    private bool $cleanUp = false;

    public function cleanUp(): void
    {
        $this->cleanUp and MySQL::cleanUp();
    }


    public static function description(): string
    {
        return 'Generate REST ORM PHP bindings using the mysql dump output file. This is the core of CarbonORM.';
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

\t       -excludeTablesRegex           - pass a valid php regex with delimiters. If a table name matches the regular expression     
                                                the table will be skipped and thus not generated. 
                                                ex.   
                                                 -excludeTablesRegex '#_migration_.*#i'   

\t       -dontQueryWithDatabaseName    - This will remove the explicit resolution of the database name in queries. This 
                                                if useful when your environments use different database names with the same structure. 
                                                Avoid using this option if possible. 

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

\t       -react [Dir_Path]             - creates a smart reference for you rest tsx generated template output             
\n
\t       -dumpData                     - will create mysqldump_data.sql to project root, 
\t                                              everything except table definitions which are generated by default and 
\t                                              stored in the /mysqldump.sql file.
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
        /** @noinspection PhpExpressionResultUnusedInspection */
        ini_set('memory_limit', '2048M');  // TODO - make this a config variable
        [$CONFIG] = $CONFIG;

        $this->schema = $CONFIG[CarbonPHP::DATABASE][CarbonPHP::DB_NAME] ?? '';
        $this->user = $CONFIG[CarbonPHP::DATABASE][CarbonPHP::DB_USER] ?? '';
        $this->password = $CONFIG[CarbonPHP::DATABASE][CarbonPHP::DB_PASS] ?? '';
        $this->target_namespace = $CONFIG[CarbonPHP::REST][CarbonPHP::NAMESPACE] ?? '';
        $this->table_prefix = $CONFIG[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';


    }

    public function run(array $argv): void
    {
        // Check command line args, password is optional
        ColorCode::colorCode("Building Rest Api!", iColorCode::BLUE);

        // C syntax
        $argc = count($argv);

        // set default values
        $rest = [];
        $QueryWithDatabaseName = true;
        $json = $carbon_namespace = CarbonPHP::isCarbonPHPDocumentation();

        $targetDir = CarbonPHP::$app_root . ($carbon_namespace ? 'carbonphp/tables/' : 'tables/');
        $only_these_tables = $history_table_query = null;
        $verbose = $debug = $primary_required = $skipTable = $logClasses = false;
        $target_namespace = $this->target_namespace ??= 'Tables\\';
        $prefix = $this->table_prefix ??= '';
        $exclude_these_tables = [];
        $excludeTablesRegex = null;

        /** @noinspection ForeachInvariantsInspection - as we need $i++ */
        for ($i = 0; $i < $argc; $i++) {

            switch ($argv[$i]) {
                case '-dumpData':
                case '--dumpData':
                    $dumpData = true;
                    break;
                case '-excludeTablesRegex':
                case '--excludeTablesRegex':
                    $excludeTablesRegex = $argv[++$i];
                    break;
                case '-dontQueryWithDatabaseName':
                case '--dontQueryWithDatabaseName':
                    $QueryWithDatabaseName = false;
                    break;
                case '-prefix':
                case '--prefix':
                    $prefix = $argv[++$i];
                    break;
                case '-namespace':
                case '--namespace':
                    $target_namespace = $argv[++$i];

                    ColorCode::colorCode("Namespace set to ($target_namespace)", iColorCode::BACKGROUND_YELLOW);

                    $target_namespace_array = explode('\\', $target_namespace);

                    $target_namespace_array = array_filter($target_namespace_array);

                    $target_namespace = implode('\\', $target_namespace_array);

                    if (count($target_namespace_array) === 1) {
                        switch (strtolower(readline("Does the namespace ($target_namespace) look correct? [Y,n]"))) {
                            default:
                                ColorCode::colorCode('TTY not active. Skipping namespace double check.', iColorCode::BACKGROUND_YELLOW);
                                break;
                            case 'no':
                            case 'n':
                                /** @noinspection PhpUnhandledExceptionInspection */
                                ColorCode::colorCode('You may need to add more escaping "\\" depending on how may contexts the string goes through. We will try to fix over escaped namespaces.', iColorCode::RED);

                                exit(1);

                        }
                    }

                    break;
                case '-json':
                case '--json':
                    $json = true;
                    break;
                case '-autoTarget':
                case '--autoTarget':
                    if ($carbon_namespace) {
                        break;
                    }
                    $composer = Composer::getComposerConfig();
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
                case '--target':
                    $targetDir = $argv[++$i];
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
                case '-dump':
                    // path to an sql dump file
                    $dump = $argv[++$i];
                    break;
                case '--cnf':
                    // path to an sql cnf pass file
                    MySQL::buildCNF($argv[++$i]);
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


        $determineIfTableShouldBeSkipped = static function ($tableName) use ($exclude_these_tables, $only_these_tables, $history_table_query, $excludeTablesRegex, $verbose): ?bool {
            // 'only these tables' is specified in the command line arguments (via file or comma list)
            if ((!empty($exclude_these_tables) && in_array($tableName, $exclude_these_tables, true))
                || (!empty($only_these_tables) && !in_array($tableName, $only_these_tables, true))
                || ($excludeTablesRegex !== null && preg_match($excludeTablesRegex, $tableName))) {
                // Break from this loop (every line in the create) and the parent loop (the tables)
                if ($verbose) {
                    ColorCode::colorCode('Skipping ' . $tableName . PHP_EOL);
                }
                // We may need to analyse for foreign keys, we will still break after this foreach loop
                if (!$history_table_query) {
                    return true;
                }
                return null;            // the table will be skipped, but parsed to generate the necessary relational mappings
            }
            return false;
        };

        if (empty($targetDir)) {
            print 'You must provide a target directory.' . PHP_EOL;
            $this->usage();
        } else if (!is_dir($targetDir)) {
            print 'Directory does not exist, attempting to create it.' . PHP_EOL;
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                print 'The target directory appears invalid "' . $targetDir . '"' . PHP_EOL;
                exit(1);
            }
        }

        if (!str_ends_with($targetDir, '/')) {
            $targetDir .= DS;
        }

        $targetDirTraits = $targetDir . 'traits' . DS;

        if (!is_dir($targetDirTraits)) {
            print 'Directory does not exist, attempting to create it.' . PHP_EOL;
            if (!mkdir($targetDirTraits, 0755, true) && !is_dir($targetDirTraits)) {
                print 'The target directory appears invalid "' . $targetDirTraits . '"' . PHP_EOL;
                exit(1);
            }
        }

        if (empty($this->schema) || $this->schema === '') {
            print 'You must specify the table schema!' . PHP_EOL;
            exit(1);
        }

        MySQL::$mysqldump = $dump ?? MySQL::mysqldump($mysqldump ?? null, $dumpData ?? false);

        if (!file_exists(MySQL::$mysqldump)) {
            print 'Could not load mysql dump file!' . PHP_EOL;
            exit(1);
        }

        if (empty(MySQL::$mysqldump = file_get_contents(MySQL::$mysqldump))) {
            print 'Contents of the mysql dump file appears empty. Build Failed!';
            exit(1);
        }

        // This is our mustache template engine implemented in php, used for rendering user content
        $mustache = new \Mustache_Engine();

        $verbose and var_dump(MySQL::$mysqldump);

        // match all tables from a mysql dump
        preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=)ENGINE=.+;#', MySQL::$mysqldump, $matches);

        // I just want the list of matches, nothing more.
        $matches = $matches[0];

        // Every CREATE TABLE as tables
        foreach ($matches as $createTableSQL) {

            if (isset($foreign_key)) {
                unset($foreign_key);
            }

            // Separate each insert line by new line feed \n
            $linesInCreateTableStatement = explode(PHP_EOL, $createTableSQL);

            $binary = $primary = [];

            $tableName = '';

            $explodeArrayPosition = 0;

            // Every line in tables insert
            foreach ($linesInCreateTableStatement as $fullLineInCreateTableStatement) {

                // binary column default values are handled by mysql.
                $cast_binary_default = false;

                // Separate each line in the tables creation by spaces
                $wordsInLine = explode(' ', trim($fullLineInCreateTableStatement));

                // We can assume that this is the first line of the tables insert
                switch ($wordsInLine[0]) {
                    case 'CREATE':
                        $tableName = trim($wordsInLine[2], '`');               // Table Name

                        // TRY to load previous validation functions
                        $rest[$tableName] ??= [];

                        $rest[$tableName] += [
                            'prefix' => $prefix,
                            'createTableSQL' => Rest::reformatLoosenedSQL(Rest::parseSchemaSQL($createTableSQL)),
                            'QueryWithDatabaseName' => $QueryWithDatabaseName,
                            'json' => $json,
                            'binary_primary' => false,
                            'carbon_namespace' => $carbon_namespace,
                            'namespace' => $carbon_namespace ? 'CarbonPHP\Tables' : rtrim($target_namespace, '\\'),
                            'carbon_table' => false,
                            'database' => $this->schema,
                            // We need to catch circular dependencies
                            'dependencies' => $rest[$tableName]['dependencies'] ?? [],
                            'TableName' => $tableName,
                            'prefixReplaced' => $noPrefix = preg_replace("/^$prefix/", '', $tableName),
                            'noPrefix' => $noPrefix,
                            'noPrefixReplaced' => str_replace('_', ' ', $noPrefix),
                            'ucEachTableName' => $etn = implode('_', array_map('ucfirst', explode('_', $noPrefix))),
                            'strtolowerNoPrefixTableName' => strtolower($etn),  // its best to leave this like this as opposed to = $noPrefix
                            'primarySort' => '',
                            'custom_methods' => '',
                            'primary' => [],
                        ];

                        // 'only these tables' is specified in the command line arguments (via file or comma list)
                        if ($determineIfTableShouldBeSkipped($tableName) === true) {

                            $skipTable = true;

                            continue 2; /// break out of the foreach and switch

                        }

                        if (file_exists($validation = $targetDir . $etn . '.php')) {

                            $validation = file_get_contents($validation);

                            if (str_starts_with($etn, 'carbon_')) {    // as this would mean the table is prefixed

                                $rest[$tableName]['DONT_VALIDATE_AFTER_REBUILD'] = true;

                            } else {

                                preg_match_all('#\n\s+public const VALIDATE_AFTER_REBUILD\s?=\s?false;#', $validation, $matches);

                                if (isset($matches[0][0])) {


                                    $rest[$tableName]['DONT_VALIDATE_AFTER_REBUILD'] = $matches[0][0];

                                }

                            }

                            preg_match_all('#\n\s+public const REGEX_VALIDATION\s*=\s*\[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['regex_validation'] = trim($matches[0][0]);

                            }

                            preg_match_all('#\n\s+public const AUTO_ESCAPE_POST_HTML_SPECIAL_CHARS\s*=\s*(true|false);#', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['autoEscape'] = trim($matches[1][0]);

                            } else {

                                $rest[$tableName]['autoEscape'] = 'true';

                            }

                            preg_match_all('#\n\s+public const PHP_VALIDATION\s*=\s*\[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#i', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['php_validation'] = trim(trim($matches[0][0]), ' \n');

                            }

                            preg_match_all('#\n\s+public const REFRESH_SCHEMA\s*=\s*\[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['REFRESH_SCHEMA'] = trim($matches[0][0]);

                            }

                            preg_match_all('#\n\s+public array $PHP_VALIDATION\s*=\s*\[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['PHP_VALIDATION_PUBLIC'] = trim($matches[0][0]);

                            }

                            preg_match_all('#\n\s+public array $REFRESH_SCHEMA\s*=\s*\[(.|\n)*?];(?=(\s|\n)+(public|protected|private|/\*))#', $validation, $matches);

                            if (isset($matches[0][0])) {

                                $rest[$tableName]['REFRESH_SCHEMA_PUBLIC'] = trim($matches[0][0]);

                            }

                            $restStaticNameSpaces = $this->restTemplateStaticNameSpace();

                            $columnNamespace = "use " . (str_ends_with($target_namespace, '\\') ? $target_namespace : $target_namespace . '\\') . "Traits\\{$etn}_Columns;";

                            array_splice($restStaticNameSpaces, 2, 0, [
                                'use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;',
                                'use CarbonPHP\Interfaces\iRestNoPrimaryKey;',
                                'use CarbonPHP\Interfaces\iRestSinglePrimaryKey;',
                                $columnNamespace,
                            ]);

                            $matches = [];

                            // the second half of this regex is from google which matches
                            if (false === preg_match_all(
                                    pattern: '#\n(use (?:function)? ?(?:(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\]*[a-zA-Z0-9_\x7f-\xff]+)|[a-zA-Z_\x80-\xff][\\\a-zA-Z0-9_\x80-\xff]+) ?(as (?:(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\]*[a-zA-Z0-9_\x7f-\xff]+)|[a-zA-Z_\x80-\xff][\\\a-zA-Z0-9_\x80-\xff]+))?;)#i',
                                    subject: $validation,
                                    matches: $matches)) {

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

                            $methods = array_udiff(
                                $generatedClassCustomMethods,
                                get_class_methods(Carbons::class),
                                'strcasecmp');         // or null.. smh

                            $constructorDefined = false;

                            if (str_contains($validation, 'public function __construct(array &$return = [])')) {

                                $constructor = '__construct';

                                if (self::grabCodeSnippet(RestLifeCycle::class, $constructor) !== self::grabCodeSnippet($fullTableClassName, $constructor)) {

                                    // todo - add any method we want to allow "overrides for"
                                    $methods[] = '__construct';

                                    $constructorDefined = true;

                                }

                            }

                            // todo - make real method and use a seek method to use less memory
                            $getMethod = static function (ReflectionMethod $method): string {

                                $file = $method->getFileName();

                                $start_line = $method->getStartLine() - 1;

                                $end_line = $method->getEndLine();

                                $length = $end_line - $start_line;

                                $source = file_get_contents($file);

                                $source = preg_split('/' . PHP_EOL . '/', $source);

                                return implode(PHP_EOL, array_slice($source, $start_line, $length));

                            };

                            foreach ($methods as $method) {

                                $abstractClass = null;

                                try {

                                    $func = new ReflectionMethod($fullTableClassName, $method);

                                    $f = $func->getFileName();

                                    if (true === method_exists(Carbons::class, $method)) {

                                        // todo - I do not think we need to re-init here
                                        $abstractClass = new ReflectionMethod(Carbons::class, $method);

                                        if ($f === $abstractClass->getFileName()
                                            || $getMethod($func) === $getMethod($abstractClass)) {

                                            continue;

                                        }

                                    }

                                    $comment = $func->getDocComment();

                                } catch (ReflectionException $e) {

                                    ThrowableHandler::generateLog($e);

                                    exit(1);

                                }

                                $body = $getMethod($func);

                                $rest[$tableName]['custom_methods'] .= ($comment ? "    $comment\n" : '') . $body . PHP_EOL . PHP_EOL;

                                $rest[$tableName]['constructorDefined'] = $constructorDefined;

                            }

                        }

                        if ($verbose) {

                            ColorCode::colorCode("\tGenerating {$tableName}\n", iColorCode::BLUE);

                            $debug and var_dump($linesInCreateTableStatement);

                        }

                        break;

                    case 'PRIMARY':
                        // Composite Primary Keys are a thing,  TODO - optimise the template for none vs single vs double key
                        $primary = explode('`,`', trim($wordsInLine[2], '(`),'));

                        // todo return composite primary key correctly (also, multiple auto increments a thing?)


                        foreach ($primary as $key) {

                            foreach ($rest[$tableName]['explode'] as &$value) {

                                if ($value['name'] !== $key) {

                                    continue;

                                }

                                $value['isPrimary'] = true;

                                if (true === ($value['auto_increment'] ?? false)) {

                                    $rest[$tableName]['auto_increment_return_key'] = true;

                                }

                            }

                            unset($value);

                        }

                        $rest[$tableName]['primarySort'] = implode(',', $primary);


                        foreach ($primary as $key) {

                            // if the user has duplicate constr (which is legal mysql) this will fail on some envs (redhat)
                            $rest[$tableName]['primary'][] = [
                                'name' => $key,
                                'binary' => in_array($key, $binary, true)
                            ];

                        }

                        break;

                    case 'CONSTRAINT':

                        // CONSTRAINT `example` FOREIGN KEY (`entity_fk`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
                        // CONSTRAINT `example` FOREIGN KEY (`created_by`) REFERENCES `users` (`ID`)         ON DELETE SET NULL ON UPDATE SET NULL

                        //  if (count($words_in_insert_stmt) !== 8) {
                        //      print  PHP_EOL . $tableName  . PHP_EOL and die;
                        //  }

                        $constraintName = trim($wordsInLine[1], '`');

                        $foreign_key = trim($wordsInLine[4], '()`');

                        $references_table = trim($wordsInLine[6], '`');

                        $references_column = trim($wordsInLine[7], '()`,');

                        $deleteKey = array_search('DELETE', $wordsInLine, true);

                        $onDelete = false !== $deleteKey
                            ? trim($wordsInLine[$deleteKey + 1], ',')
                            : 'NO';


                        if ($onDelete === 'SET') {

                            $onDelete .= ' ' . $wordsInLine[$deleteKey + 2];

                        }

                        $updateKey = array_search('UPDATE', $wordsInLine, true);

                        $onUpdate = false !== $updateKey
                            ? trim($wordsInLine[$updateKey + 1], ',')
                            : 'NO';

                        if ($onUpdate === 'SET') {

                            $onUpdate .= ' ' . $wordsInLine[$updateKey + 2];

                        }

                        if ('NO' === $onDelete) {
                            $onDelete .= ' ACTION';
                        }

                        if ('NO' === $onUpdate) {
                            $onUpdate .= ' ACTION';
                        }

                        $rest[$tableName]['CARBON_CARBONS_PRIMARY_KEY'] ??= false;

                        $rest[$tableName]['CARBON_CARBONS_PRIMARY_KEY'] =
                            ($rest[$tableName]['CARBON_CARBONS_PRIMARY_KEY'] === true)
                            || (($references_table === 'carbon_carbons'
                                    || $references_table === $prefix . 'carbon_carbons')
                                && in_array($foreign_key, $primary, true)
                                && 'entity_pk' === $references_column);

                        if (($references_table === 'carbon_carbons'
                                || $references_table === $prefix . 'carbon_carbons')
                            && in_array($foreign_key, $primary, true)) {

                            $rest[$tableName]['carbon_table'] =
                                $tableName !== 'carbon_carbons'
                                && $tableName !== $prefix . 'carbon_carbons'; // todo -

                        }

                        $localTable = str_starts_with($tableName, $prefix)
                            ? substr($tableName, strlen($prefix))
                            : $tableName;

                        $localTable = ucwords($localTable, '_');

                        $localTableRef = str_starts_with($references_table, $prefix)
                            ? substr($references_table, strlen($prefix))
                            : $references_table;

                        $localTableRef = ucwords($localTableRef, '_');

                        $isGenerated = $determineIfTableShouldBeSkipped($references_table)
                            ? "'$references_table.$references_column'"
                            : $localTableRef . '::' . strtoupper($references_column);

                        $rest[$tableName]['TABLE_CONSTRAINTS'][] = [
                            'key' => 'self::' . strtoupper($foreign_key),
                            'references' => $isGenerated
                        ];

                        $columnNames = array_column($rest[$tableName]['explode'], 'name');

                        $index = array_search($foreign_key, $columnNames, true);

                        $rest[$tableName]['explode'][$index][iRest::COLUMN_CONSTRAINTS][] = [
                            'key' => $isGenerated,
                            'CONSTRAINT_NAME' => $constraintName,
                            'UPDATE_RULE' => "'$onUpdate'",
                            'DELETE_RULE' => "'$onDelete'",
                        ];

                        // We need to catch circular dependencies as mysql dumps print schemas alphabetically
                        if (!isset($rest[$references_table])) {

                            $rest[$references_table] = ['EXTERNAL_TABLE_CONSTRAINTS' => []];

                        } else if (!isset($rest[$references_table]['EXTERNAL_TABLE_CONSTRAINTS'])) {

                            $rest[$references_table]['EXTERNAL_TABLE_CONSTRAINTS'] = [];

                        }

                        $isGenerated = $skipTable
                            ? "'$tableName.$foreign_key'"
                            : $localTable . '::' . strtoupper($foreign_key);

                        $rest[$references_table]['EXTERNAL_TABLE_CONSTRAINTS'][] = [
                            'key' => $isGenerated,
                            'references' => 'self::' . strtoupper($references_column)
                        ];

                        // todo - DEPRECATED I think we have a warning based off this that very much is helpful
                        // We need to catch circular dependencies as mysql dumps print schemas alphabetically
                        if (!isset($rest[$references_table])) {

                            $rest[$references_table] = ['dependencies' => []];

                        } else if (!isset($rest[$references_table]['dependencies'])) {

                            $rest[$references_table]['dependencies'] = [];

                        }

                        $verbose and ColorCode::colorCode("\nreference found ::\t$tableName([$foreign_key => $references_column])\n", 'magenta');

                        $rest[$references_table]['dependencies'][] = [$tableName => [$foreign_key => $references_column]];
                        //\\ DEPRECATED

                        break;


                    default:

                        if ($wordsInLine[0][0] === '`') {

                            // This is expected to be the second condition run in foreach
                            // columns is just a list of column
                            $name = $rest[$tableName]['columns'][] = trim($wordsInLine[0], '`');

                            // Explode hold all information about column
                            $rest[$tableName]['explode'][$explodeArrayPosition]['name'] = $name;

                            $rest[$tableName]['explode'][$explodeArrayPosition]['caps'] = strtoupper($name);

                            $type = strtolower($wordsInLine[1]);

                            if ('unsigned' === ($wordsInLine[2] ?? '')) {

                                $type .= ' unsigned';

                            }

                            // exploding strings like 'mediumint(9)' and 'binary(16)'
                            if (count($argv = explode('(', $type)) > 1) {

                                $type = $argv[0];

                                if ($type === 'enum') {

                                    $length = '';               // enums define strings where im expecting int length

                                } else {

                                    $length = trim($argv[1], '),');

                                }

                                // This being set determines what type of PDO stmt we use
                                $rest[$tableName]['explode'][$explodeArrayPosition]['length'] = $length;

                            }

                            $simpleType = $type;

                            if (count($argv = explode(' ', $type)) > 1) {

                                $simpleType = rtrim($argv[0], ',');;

                            }

                            $type = rtrim($type, ',');

                            $rest[$tableName]['explode'][$explodeArrayPosition]['mysql_type'] = $type;

                            $rest[$tableName]['explode'][$explodeArrayPosition]['json'] = $type === 'json';

                            // These are PDO const types, so we'll eliminate one complexity by evaluating them before inserting into the template
                            # $PDO = [0 => PDO::PARAM_NULL, 1 => PDO::PARAM_BOOL, 2 => PDO::PARAM_INT, 3 => PDO::PARAM_STR];
                            switch ($simpleType) {                // Use pdo for what it can actually do
                                case 'bigint':
                                case 'tinyint': // @link https://stackoverflow.com/questions/12839927/mysql-tinyint-2-vs-tinyint1-what-is-the-difference
                                case 'int':
                                case 'smallint':
                                case 'mediumint':
                                    $type = 'PDO::PARAM_INT'; // $PDO[2];
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['tsxType'] = 'number';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['phpType'] ??= 'int';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['number'] = true;
                                    break;
                                case 'boolean':
                                    $type = 'PDO::PARAM_BOOL';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['phpType'] = 'bool';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['bool'] = true;
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
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['binary'] = true;
                                    $cast_binary_default = true;
                                case 'decimal':  // @link https://stackoverflow.com/questions/2718628/pdoparam-for-type-decimal
                                case 'precision':
                                case 'float':
                                case 'real':
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['number'] = true;
                                default:
                                case 'varchar':
                                    $type = 'PDO::PARAM_STR';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['phpType'] ??= 'string';
                                    $rest[$tableName]['explode'][$explodeArrayPosition]['string'] ??= true;
                            }
                            // Explode hold all information about column
                            $rest[$tableName]['explode'][$explodeArrayPosition]['type'] = $type;

                            if (str_contains($fullLineInCreateTableStatement, 'NOT NULL')) {


                                $rest[$tableName]['explode'][$explodeArrayPosition][iRest::NOT_NULL] = '\'NOT NULL\'';


                            }

                            // Lets check if a default value is set for column
                            $key = array_search('DEFAULT', $wordsInLine, true);

                            if ($key !== false) {

                                ++$key; // move from the word default to the default value

                                // Were going to skip columns with this set as the default value
                                // Trying to insert this condition w/ PDO is unneeded complexity
                                $rest[$tableName]['explode'][$explodeArrayPosition]['skip'] = true;

                                $default = '';

                                // todo - the negative case  && substr($words_in_insert_stmt[$key], -w) === '\\\\''

                                // if it ends in '  aka '0'
                                if (str_ends_with($wordsInLine[$key], '\'')) {
                                    $default = $wordsInLine[$key];
                                    // if it ends with ',  as '0',
                                } else if (str_ends_with($wordsInLine[$key], '\',')) {
                                    $default = trim($wordsInLine[$key], ',');
                                    // if it doesnt start with '  as CURRENT_TIMESTAMP
                                } else if ($wordsInLine[$key][0] !== '\'') {
                                    $default = rtrim($wordsInLine[$key], ',');
                                } else {
                                    // the first index does start in ' and doesnt end in '
                                    // todo - switch this with a regex
                                    do {
                                        $default .= ' ' . $wordsInLine[$key];
                                        $key++;
                                    } while (!str_ends_with($wordsInLine[$key], '\'')
                                    && !str_ends_with($wordsInLine[$key], '\','));
                                    $default .= ' ' . $wordsInLine[$key];
                                    $default = trim($default, ', ');
                                }

                                if ($default === 'CURRENT_TIMESTAMP') {

                                    $rest[$tableName]['explode'][$explodeArrayPosition]['CURRENT_TIMESTAMP'] = true;

                                } else if (!str_starts_with($default, '\'')) {

                                    // We need to escape values for php
                                    $default = "'$default'";

                                } else {

                                    $default = trim($default, '\'');

                                    $default = "'\"$default\"'";

                                }

                                /** @noinspection NestedTernaryOperatorInspection */
                                $rest[$tableName]['explode'][$explodeArrayPosition]['default'] = ($default === "'NULL'" ? 'null' : ($cast_binary_default ? 'null' : $default));
                            }

                            $key = array_search('COMMENT', $wordsInLine, true);

                            if ($key !== false) {

                                $comment = '';

                                do {
                                    $key++;
                                    $comment .= ' ' . $wordsInLine[$key];
                                } while (
                                    (
                                        !str_ends_with($wordsInLine[$key], '\'')
                                        && !str_ends_with($wordsInLine[$key], '\\\'')
                                    )
                                    && !str_ends_with($wordsInLine[$key], '\',')
                                );

                                $rest[$tableName]['explode'][$explodeArrayPosition][iRest::COMMENT] = rtrim($comment, ',');

                            }

                            // As far as I can tell the AUTO_INCREMENT condition the last possible word in the query
                            // todo - use a regex that ensures you dont write AUTO_INCREMENT in a comment
                            $auto_inc = str_contains($fullLineInCreateTableStatement, 'AUTO_INCREMENT');

                            if ($auto_inc) {

                                $rest[$tableName]['explode'][$explodeArrayPosition]['skip'] = true;

                                $rest[$tableName]['explode'][$explodeArrayPosition]['auto_increment'] = true;

                                $verbose and ColorCode::colorCode("\tThe Table '$tableName' contains an AUTO_INCREMENT column. This is bad for scaling.
                                                                        \tConsider switching to binary(16) and letting this rest API manage column uniqueness.\n", iColorCode::RED);
                            }

                            $explodeArrayPosition++;

                        }

                        break;

                }
                // END SWITCH

            }
            // END PARSE

            #$allRestInfo = json_encode($rest, JSON_PRETTY_PRINT);
            #file_put_contents(CarbonPHP::$app_root . 'test.json', $allRestInfo);



            // We need to break from this tables too if the tables is not in ( -l -f )
            if ($skipTable) {
                $skipTable = false; // This is so we can stop analysing a full tables
                continue;
            }

            $rest[$tableName]['primaryExists'] = !empty($rest[$tableName]['primary']);

            $rest[$tableName]['multiplePrimary'] = 1 < count($rest[$tableName]['primary']);

            // Make sure we didn't specify a flag that could cause us to move on...
            if (empty($rest[$tableName]['primary'])) {

                $verbose and ColorCode::colorCode("\n\nThe tables {$rest[$tableName]['TableName']} does not have a primary key.\n", iColorCode::YELLOW);

                if ($primary_required) {    // todo - this is a legacy option

                    ColorCode::colorCode(" \tSkipping...\n ",);

                    continue;

                }

            } else {
                foreach ($rest[$tableName]['explode'] as &$value) {

                    if (in_array($value, [
                        'pageSize',
                        'pageNumber'
                    ])) {

                        ColorCode::colorCode($rest[$tableName]['TableName'] . " uses reserved C6 RESTFULL keywords as a column identifier => $value\n\tRest Failed", iColorCode::RED);

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

            $rest[$tableName]['custom_methods'] = rtrim($rest[$tableName]['custom_methods'], PHP_EOL);

            $logClasses && print $rest[$tableName]['TableName'] . ', ';

        }

        $staticNamespaces = $this->restTemplateStaticNameSpace();

        $staticNamespaces = implode(PHP_EOL, $staticNamespaces);

        $class = RestTemplates::restTemplate();

        $trait = RestTemplates::restTrait();

        foreach ($rest as $tableName => $parsed) {

            if ($determineIfTableShouldBeSkipped($tableName)) {

                continue;

            }

            $parsed['staticNamespaces'] = $staticNamespaces;

            if (false === file_put_contents($targetDir . $parsed['ucEachTableName'] . '.php', $mustache->render($class, $parsed))) {

                ColorCode::colorCode('PHP internal file_put_contents failed while trying to store :: (' . $targetDir . $parsed['ucEachTableName'] . '.php)', iColorCode::RED);

            }

            if (false === file_put_contents($targetDir . 'traits' . DS . $parsed['ucEachTableName'] . '_Columns.php', $mustache->render($trait, $parsed))) {

                ColorCode::colorCode('PHP internal file_put_contents failed while trying to store :: (' . $targetDir . 'traits' . DS . $parsed['ucEachTableName'] . '.php)', iColorCode::RED);

            }

            if (empty($parsed['explode'])) {

                ColorCode::colorCode("\nYou have a reference with wasn't resolved in the dump. Please search for '$tableName' in your "
                    . "./mysqldump.sql file. This typically occurs when resolving to an outside schema, which probably indicates an error.\n", iColorCode::RED);

            }

        }

        // todo - log classes
        $logClasses && print "\n";

        ColorCode::colorCode("\tFinished Building REST ORM!\n\n");


        // TODO - validate the methods defined in table space, we should do this after each generation and check if can include try {}


        ColorCode::colorCode("\tSuccess!\n\n");

    }

    private function restTemplateStaticNameSpace(): array
    {
        return [
            #'use CarbonPHP\Database;',
            #'use CarbonPHP\Error\PublicAlert;',
            'use CarbonPHP\Restful\RestfulValidations;',
            'use CarbonPHP\Rest;',
            #'use JsonException;',
            'use PDO;',
            #'use PDOException;',
            #'use function array_key_exists;',
            #'use function count;',
            #'use function func_get_args;',
            #'use function is_array;'
        ];
    }


    public static function grabCodeSnippet($className, $methodName): string
    {
        try {

            $func = new ReflectionMethod($className, $methodName);

            $comment = $func->getDocComment();

        } catch (ReflectionException) {

            return '<div>Failed to load code preview in ThrowableHandler class using ReflectionMethod.<div>';

        }

        $f = $func->getFileName(); // stub says string but may also produce false

        if (empty($f)) {
            return '';
        }

        $start_line = $func->getStartLine() - 1;

        $end_line = $func->getEndLine();

        $length = $end_line - $start_line;

        $source = file_get_contents($f);

        $source = preg_split('/' . PHP_EOL . '/', $source);

        if (false === function_exists('highlight')) {

            include_once CarbonPHP::CARBON_ROOT . 'Functions.php';

        }

        return $comment . PHP_EOL . implode(PHP_EOL, array_slice($source, $start_line, $length));

    }

}

