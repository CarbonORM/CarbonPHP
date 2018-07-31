<?php

$argv = $_SERVER['argv'];

$argc = count($argv);

if (!is_dir(APP_ROOT . 'table')) {
    mkdir(APP_ROOT . 'table');
}
// Check command line args, password is optional

print "\tBuilding Rest Api!\n";


$trigger = function ($table, $columns, $binary, $dependencies, $primary) {

    $json_mysql = function ($op = 'NEW') use ($columns, $binary) {
        $mid = "DECLARE json text;\n SET json = '{';";
        foreach ($columns as $key => &$column) {
            $column = in_array($column, $binary)
                ? <<<END
\nSET json = CONCAT(json,'"$column":"', HEX($op.$column), '"');
END
                : <<<END
\nSET json = CONCAT(json,'"$column":"', COALESCE($op.$column,''), '"');
END;
        }

        $mid .= implode("\nSET json = CONCAT(json, ',');", $columns);

        $mid .= <<<END
SET json = CONCAT(json, '}');
END;
        return $mid;
    };
    $history_sql = function ($operation_type = 'POST') use ($table, $primary) {
        $relative_time = $operation_type === "POST" ? 'NEW' : $operation_type === 'PUT' ? 'NEW' : 'OLD';
        return <<<END
INSERT INTO resource_history_logs (`uuid`, `resource_type`, `resource_uuid`, `operation_type`, `data`) 
VALUES (UNHEX(REPLACE(UUID() COLLATE utf8_unicode_ci,'-','')), '$table', $relative_time.$primary , '$operation_type', json);
END;
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

    return <<<END
    
DROP TRIGGER IF EXISTS `trigger_{$table}_b_d`;;
CREATE TRIGGER `trigger_{$table}_b_d` BEFORE DELETE ON `$table` FOR EACH ROW
BEGIN
{$json_mysql('OLD')}    
      -- Insert record into audit table
{$history_sql('DELETE')}
      -- Delete Children 
{$delete_children()}

END;;

DROP TRIGGER IF EXISTS `trigger_{$table}_a_u`;;
CREATE TRIGGER `trigger_{$table}_a_u` AFTER UPDATE ON `$table` FOR EACH ROW
BEGIN

{$json_mysql()}    
      -- Insert record into audit table
{$history_sql('PUT')}

END;;

DROP TRIGGER IF EXISTS `trigger_{$table}_a_i`;;
CREATE TRIGGER `trigger_{$table}_a_i` AFTER INSERT ON `$table` FOR EACH ROW
BEGIN

{$json_mysql()}    
      -- Insert record into audit table
{$history_sql('POST')}

END;;

END;
};

$usage = function () use ($argv) {
    print <<<END
\n
\t           Question Marks Denote Optional Parameters  
\t           Order does not matter. 
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage:: 
\t php index.php rest  
\t       -help                         - this dialogue 
\t       -h [?HOST]                    - IP address
\t       -d                            - delete dump
\t       -s [?SCHEMA]                  - Its that table schema!!!! 
\t       -u [?USER]                    - mysql username
\t       -p [?PASSWORD]                - if ya got one
\t       -r                            - specify that a primary key is required for generation
\t       -l [?tableName(s),[...?,[]]]  - comma separated list of specific tables to capture  
\t       -v ?debug                     - Verbose output, if === debug follows this tag even more output is given 
\t       -f [?file_of_Tables]          - file of table names separated by eol
\t       -mysqldump [?executable]      - path to mysqldump command 
\t       -mysql  [?executable]         - path to mysql command 
\t       -dump [?dump]                 - path to a mysqldump sql export
\t       -cnf [?cnf_path]              - path to a mysql cnf file
\t       -trigger                      - build triggers and history table for binary primary keys 
\n
END;
    exit(1);
};

// quick if stmt
$argc < 2 and $usage();
// These are PDO const types, so we'll eliminate one complexity by evaluating them before inserting into the template
$PDO = [0 => PDO::PARAM_NULL, 1 => PDO::PARAM_BOOL, 2 => PDO::PARAM_INT, 3 => PDO::PARAM_STR];
// set default values
$rest = [];
$pass = '';
$only_these_tables = $schema = $history_table_query = $mysqldump = null;
$verbose = $debug = $primary_required = $delete_dump = $carbon_namespace = $skipTable = false;
$host = '127.0.0.1';
$user = 'root';

for ($i = 0; $i < $argc; $i++) {
    switch ($argv[$i]) {
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
CREATE TABLE IF NOT EXISTS resource_history_logs
(
  uuid binary(16) null,
  resource_type varchar(10) null,
  resource_uuid binary(16) null,
  operation_type varchar(16) null comment 'POST|PUT|DELETE',
  data blob null,
  timestamp datetime default CURRENT_TIMESTAMP not null,
  modified_by int(16) null
);
QUERY;
            file_put_contents('triggers.sql', $query);
            break;
        case '-help':
            $usage();
            break;          // unneeded but my editor complains
        case '-d':
            $delete_dump = true;
            break;
        case '-h':
            $host = $argv[++$i];
            break;
        case '-s':
            $schema = $argv[++$i];
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
            if (empty($file = file_get_contents("{$argv[++$i]}"))) {
                print "Could not open file [ " . $argv[$i] . " ] for input\n\n";
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


if (empty($schema)) {
    print 'You must specify the table schema!';
    exit(1);
}

// We're going to use this function to execute mysql from the command line
$buildCNF = function ($query, $type = 'mysql') use ($host, $verbose, $schema, $user, $pass, $usage, &$cnfFile) {
    if (empty($host) || empty($user)) $usage();

    if (empty($cnfFile)) {
        // Mysql needs this to access the server
        $cnf = ['[client]', "user = $user", "password = $pass", "host = $host"];
        file_put_contents('mysqldump.cnf', implode(PHP_EOL, $cnf));
        $cnfFile = 'mysqldump.cnf';
    }

    if ($type === 'mysqldump') {
        $runMe = (empty($mysqldump) ? 'mysqldump' : "\"$mysqldump\"") . ' --defaults-extra-file="' . $cnfFile . '" --no-data ' . $schema . ' > ./mysqldump.sql';
    } else if ($type === 'mysql') {
        $runMe = (empty($mysql) ? 'mysql' : "\"$mysql\"") . ' --defaults-extra-file="' . $cnfFile . '" ' . $schema . ' < "' . $query . '"';
    } else {
        print "\nThe value of $type must be 'mysql' or 'mysql' exclusively.";
        die(1);
    }

    $verbose and print $runMe . PHP_EOL;

    `$runMe`;   // execute mysqldump
};

// Was the history table flag set?
if ($history_table_query) {
    $buildCNF('triggers.sql');
}

// check if dump file was given
if (empty($dump)) {
    if (empty($host) || empty($schema) || empty($user)) $usage();

    $buildCNF('', 'mysqldump');

    if (!file_exists('./mysqldump.sql')) {
        print 'Could not load mysql dump file!' . PHP_EOL;
        return;
    }

    if (empty($dump = file_get_contents('mysqldump.sql'))) {
        print "Build Failed";
        exit(1);
    }
}

// This is our mustache template engine implemented in php, used for rendering user content
$mustache = function (array $rest) {
    $mustache = new \Mustache_Engine();
    if (empty($handlebars = file_get_contents(__DIR__ . '/rest.mustache'))) {
        print "Could not find rest mustache template. Searching in directory\n" .
            __DIR__ . "/est.mustache";
        exit(1);
    }
    return $mustache->render($handlebars, $rest);
};

$verbose and var_dump($dump);

// match all tables from a mysql dump
preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=InnoDB)#', $dump, $matches);

// I just want the list of matches, nothing more.
$matches = $matches[0];


// Every CREATE TABLE as table
foreach ($matches as $table) {
    if (isset($foreign_key)) {
        unset($foreign_key);
    }

    // Separate each insert line by new line feed \n
    $table = explode(PHP_EOL, $table);
    $binary = $skipping_col = $primary = [];
    $tableName = '';
    $column = 0;

    // Every line in table insert
    foreach ($table as $words_in_insert_stmt) {
        // binary column default values are handled by mysql.
        $cast_binary_default = false;

        // Separate each line in the tables creation by spaces
        $words_in_insert_stmt = explode(' ', trim($words_in_insert_stmt));

        // We can assume that this is the first line of the table insert
        if ($words_in_insert_stmt[0] === 'CREATE') {
            $tableName = trim($words_in_insert_stmt[2], '`');               // Table Name

            // TRY to load previous validation functions

            $rest[$tableName] = [
                'binary_primary' => false,
                'carbon_namespace' => $carbon_namespace,
                'carbon_table' => false,
                'database' => $schema,
                // We need to catch circular dependencies
                'dependencies' => isset($rest[$tableName]) && isset($rest[$tableName]['dependencies']) ?
                    $rest[$tableName]['dependencies'] :
                    [],
                'TableName' => $tableName,
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
            if (!empty($only_these_tables) && !in_array($tableName, $only_these_tables)) {
                // Break from this loop (every line in the create) and the parent loop (the table)
                $verbose and print 'Skipping ' . $tableName . PHP_EOL;
                // this is our condition to check right after this table is executed
                // TODO - should I parse the whole thing to make the tree?
                $skipTable = true;
                // We may need to analyse for foreign keys, we will still break after this foreach loop
                if (!$history_table_query) {
                    break;
                }
            }

            if ($verbose) {
                print "\tGenerating {$tableName}\n";
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
            if (count($argv = explode('(', $type)) > 1) {
                $type = $argv[0];
                $length = trim($argv[1], '),');
                // This being set determines what type of PDO stmt we use
                $rest[$tableName]['explode'][$column]['length'] = $length;
            }

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
            $key = array_search('DEFAULT', $words_in_insert_stmt);
            if ($key !== false) {
                ++$key; // move from the word default to the default value
                $default = rtrim($words_in_insert_stmt[$key], ',');

                if ($default == 'CURRENT_TIMESTAMP') {
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
            $auto_inc = count($words_in_insert_stmt) - 1;
            if (isset($words_in_insert_stmt[$auto_inc]) && $words_in_insert_stmt[$auto_inc] === 'AUTO_INCREMENT,') {
                $skipping_col[] = $name;
                $rest[$tableName]['explode'][$column]['skip'] = true;
                $verbose and print "\tThe Table '$tableName' contains an AUTO_INCREMENT column. This is bad for scaling.
                \tConsider switching to binary(16) and letting this rest API manage column uniqueness.\n";
            }

            $column++;
        } else if ($words_in_insert_stmt[0] === 'PRIMARY') {
            // Composite Primary Keys are a thing,  TODO - optimise the template for none vs single vs double key
            $primary = explode('`,`', trim($words_in_insert_stmt[2], "(`),"));

            /* // TODO - color code verbose output
             *
             * if (count($primary) > 1) {
                print $tableName . PHP_EOL;
            }*/

            // Build the insert stmt
            $sql = [];
            foreach ($primary as $key) {
                if (in_array($key, $binary)) {
                    // binary data is expected as hex @ rest call (GET,PUT,DELETE)
                    $sql[] = ' ' . $key . '=UNHEX(\' . $primary .\')';
                } else {
                    // otherwise just create the stmt normally
                    $sql[] = ' ' . $key . '=\' . $primary .\'';
                }
                $rest[$tableName]['primary'][] = ['name' => $key];
            }
            $rest[$tableName]['primary'][] = ['sql' => '$sql .= \' WHERE ' . implode($sql, ' OR ') . "';"];

        } else if ($words_in_insert_stmt[0] === 'CONSTRAINT') {

//            if (count($words_in_insert_stmt) !== 8) {
//                print  PHP_EOL . $tableName  . PHP_EOL and die;
//            }

            $foreign_key = trim($words_in_insert_stmt[4], '()`');
            $references_table = trim($words_in_insert_stmt[6], '`');
            $references_column = trim($words_in_insert_stmt[7], '()`,');

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


    // We need to break from this table too if the table is not in ( -l -f )
    if ($skipTable) {
        $skipTable = false; // This is so we can stop analysing a full table
        continue;
    }

    // Make sure we didn't specify a flag that could cause us to move on...
    if (empty($rest[$tableName]['primary'])) {
        $verbose and print "The table {$rest[$tableName]['TableName']} does not have a primary key.\n";
        if ($primary_required) {
            print " \tSkipping...\n ";
            continue;
        }
    } else {
        foreach ($rest[$tableName]['explode'] as &$value) {
            if (in_array($value, ['pageSize', 'pageNumber'])) {
                print $rest[$tableName]['TableName'] . " uses reserved 'REST' keywords as a column identifier => $value\n\tRest Failed";
                die(1);
            }

            if (false !== in_array($value['name'], $primary)) {
                $value['primary'] = true;
                if (isset($value['binary'])) {
                    $value['primary_binary'] = true;
                    $rest[$tableName]['binary_primary'] = true;
                }
            }
        }
    }

    // Listed is located in our POST method
    $rest[$tableName]['listed'] = '';
    $rest[$tableName]['implode'] = $rest[$tableName]['columns'];
    // The final value of implode is only used in the POST method
    foreach ($rest[$tableName]['implode'] as $key => &$value) {

        if (!in_array($value, $skipping_col)) {

            // This suffixes an extra comma
            $rest[$tableName]['listed'] .= $value . ', ';

            if (in_array($value, $binary)) {
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


    // Listed is located in our POST stmt, remove trailing comma
    $rest[$tableName]['listed'] = rtrim($rest[$tableName]['listed'], ', ');

    // Remove unneeded comma at begging of string
    $rest[$tableName]['implode'] = implode(',', $rest[$tableName]['implode']);


    file_put_contents(APP_ROOT . '/table/' . $rest[$tableName]['TableName'] . '.php', $mustache($rest[$tableName]));
}


/**
 * Now that the full dump has been parsed, we need to build our triggers
 * using the foreign key analysis
 */

if ($history_table_query) {
    print "\tBuilding Triggers!\n";
    $triggers = '';
    foreach ($rest as $table) {
        if ($table['binary_primary'] && ($only_these_tables === null || in_array($table['TableName'], $only_these_tables))) {
            $triggers .= $trigger($table['TableName'], $table['columns'], isset($table['binary_trigger']) ? $table['binary_trigger'] : [], $table['dependencies'], $table['primary'][0]['name']);
        }
    }

    file_put_contents('triggers.sql', 'DELIMITER ;;' . PHP_EOL . $triggers . PHP_EOL . 'DELIMITER ;');

    $buildCNF('triggers.sql');
}

//if ($rest[$tableName]['binary_primary']) {
//    // TODO - change how multiple primary keys are handled
//    $rest[$tableName]['trigger'] = $trigger($rest[$tableName]['TableName'], $rest[$tableName]['columns'], $rest[$references_table]['dependencies'],$rest[$tableName]['primary'][0]['name']);
//    $debug and print $rest[$tableName]['trigger'];
//}


// debug is a subset of the verbose flag
$debug and var_dump($rest['clients']);

print "\tSuccess!\n\n";

unlink('./mysqldump.sql');
unlink($cnfFile);

//ncurses_end();

return 0;



