<?php

$argv = $_SERVER['argv'];

$argc = count($argv);

// Check command line args, password is optional
print PHP_EOL . "\tBuilding Rest Api!" . PHP_EOL;

if (!is_dir(APP_ROOT . 'table')) {
    mkdir(APP_ROOT . 'table');
}

$usage = function () use ($argv) {
    print <<<END
\n
\t           Question Marks Denote Optional Parameters  
\t           Order does not matter. 
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage:: 
\t $argv[0] 
\t       -help                         - this dialogue 
\t       -h [?HOST]                    - IP address
\t       -d                            - delete dump
\t       -s [?SCHEMA]                  - Its that table schema!!!! 
\t       -u [?USER]                    - mysql username
\t       -p [?PASSWORD]                - if ya got one
\t       -r                            - specify that a primary key is required for generation
\t       -l [?tableName(s),[...?,[]]]  - comma separated list of specific tables to capture  
\t       -v                            - Verbose output 
\t       -f [?file_of_Tables]          - file of table names separated by eol ("\n")
\t       -e [?executable]              - path to mysqldump command 
\t       -dump [?dump]                 - path to a mysqldump sql export 
\n
END;
    exit(1);
};

$argc < 3 and $usage();    // quick if stmt

$pass = '';
$onlyThese = null;
$verbose = $primary_required = $delete_dump = false;

for ($i = 0; $i < $argc; $i++) {
    switch ($argv[$i]) {
        case '-v':
            $verbose = true;
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
            $onlyThese = explode(',', $argv[++$i]);
            break;
        case '-f':
            if (empty($file = file_get_contents($argv[++$i]))) {
                print "Could not open file [ " . $argv[$i] . " ] for input\n\n";
                exit(1);
            }
            $onlyThese = explode(PHP_EOL, $file);
            break;
        case '-e':
            // the path to the mysqldump executable
            $executable = $argv[++$i];
            break;
        case '-dump':
            // path to an sql dump file
            $dump = $argv[++$i];
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
    }
}


if (empty($dump)) {
    if (empty($host) || empty($schema) || empty($user)) $usage();

    // Mysql needs this to access the server
    $cnf = [
        '[client]',
        "user = $user",
        "password = $pass",
        "host = $host"
    ];

    file_put_contents('mysqldump.cnf', implode(PHP_EOL, $cnf));


    $runMe = (empty($executable) ? 'mysqldump' : "\"$executable\"") . ' --defaults-extra-file="./mysqldump.cnf" --no-data ' . $schema . ' > ./config/mysqldump.sql';
    // BASH QUERY
    $verbose and print $runMe . PHP_EOL;

    `$runMe`;

    unlink('mysqldump.cnf');


    if (!file_exists(APP_ROOT . '/config/mysqldump.sql')) {
        print 'Could not load mysql dump file!' . PHP_EOL;
        return;
    }

    if (empty($dump = file_get_contents(APP_ROOT . '/config/mysqldump.sql'))) {
        print "Build Failed";
        exit(1);
    }
}

$mustache = function (array $rest) {      // This is our mustache template engine implemented in php, used for rendering user content
    $mustache = new \Mustache_Engine();

    // and output it
    $handlebars = file_get_contents(__DIR__ . '/rest.mustache');

    return $mustache->render($handlebars, $rest);
};


// match all tables from a mysql dump
preg_match_all('#CREATE\s+TABLE(.|\s)+?(?=ENGINE=InnoDB)#', $dump, $matches);

$matches = $matches[0];

$rest = [];
$PDO = [                                            // I guess this is it ?
    0 => 'PDO::PARAM_NULL',
    1 => 'PDO::PARAM_BOOL',
    2 => 'PDO::PARAM_INT',
    3 => 'PDO::PARAM_STR',
];

// Every table insert

$skipTable = false;

foreach ($matches as $insert) {// Create Table
    if (isset($foreign_key)) {
        unset($foreign_key);
    }
    $insert = explode(PHP_EOL, $insert);
    $column = 0;
    $binary = [];
    $rest = [
        'database' => $schema
    ];

    // Every line in table insert
    foreach ($insert as $query) {                                                  // Create Columns
        $query = explode(' ', trim($query));

        if ($query[0] === 'CREATE') {
            $rest['TableName'] = trim($query[2], '`');                           // Table Name
            if (!empty($onlyThese) && !in_array($rest['TableName'], $onlyThese)) {      // If this condition = true
                $verbose and print 'Skipping ' . $rest['TableName'] . PHP_EOL;                       // Break from this loop
                $skipTable = true;                                                      // and the parent loop
                break;
            }

            if ($verbose) {
                print 'Generating ' . $rest['TableName'] . PHP_EOL;
                var_dump($insert);
            }

        } else if ($query[0] === 'PRIMARY') {
            $rest['primary'] = substr($query[2], 2, strlen($query[2]) - 5);

        } else if ($query[0] === 'CONSTRAINT' && $query[6] === '`carbon`' && isset($rest['primary'])) {
            if ($rest['primary'] === $fk = trim($query[4], "()`")) {
                $foreign_key = $fk;
                $rest['carbon_table'] = true;
            }

        } else if ($query[0][0] === '`') {

            $rest['implode'][] = $name = trim($query[0], '`');            // Column Names

            if (in_array($name, ['pageSize', 'pageNumber'])) {
                throw new InvalidArgumentException($rest['name'] . " uses reserved 'REST' keywords as a column identifier => $name\n");
            }

            /**
             * Verify bool with the byte (or whatever it is) number attached
             */

            if ('tinyint(1)' === $type = strtolower($query[1])) {            // this is a Bool
                $type = $PDO[0];
                $length = 1;
            } else {
                /**
                 * Else strip the value and keep computing
                 */

                if (count($argv = explode('(', $type)) > 1) {
                    $type = $argv[0];
                    $length = trim($argv[1], ')');
                } else {
                    unset($length);
                }

                switch ($type) {
                    case 'tinyint':
                    case 'smallint':
                    case 'mediumint':
                        $type = $PDO[2];
                        break;
                    case 'binary':
                        $binary[] = $name;
                        $rest['binary_list'][] = ['name' => $name];
                        $rest['explode'][$column]['binary'] = true;
                    case 'varchar':
                    default:
                        $type = $PDO[3];
                }
            }

            $query_default = count($query) - 2;
            if (isset($query[$query_default]) && $query[$query_default] === 'DEFAULT') {
                $default = rtrim($query[++$query_default], ',');
                if ($default[0] !== '\'') {
                    $default = "'$default'";
                }
            }

            $rest['explode'][$column]['name'] = $name;
            $rest['explode'][$column]['type'] = $type;

            if (isset($length)) {
                $rest['explode'][$column]['length'] = $length;
            }

            if (isset($default)) {
                $rest['explode'][$column]['default'] = $default === "'NULL'" ? null : $default;
            }

            $column++;
        }
    }
    if ($skipTable) {                // We need to break from this table too if the table is not in ( -l -f )
        $skipTable = false;         // This is so we can stop analysing a full table
        continue;
    }

    if (!isset($rest['primary'])) {
        $verbose and print "The table {$rest['TableName']} does not have a primary key.\n";
        if ($primary_required) {
            print " \tSkipping...\n ";
            continue;
        }
    } else {
        foreach ($rest['explode'] as &$value) {
            if ($value['name'] === $rest['primary']) {
                $value['primary'] = true;

                if (isset($value['binary'])) {
                    $value['primary_binary'] = true;
                    $rest['binary_primary'] = true;
                }
            }
        }
    }

    $rest['update'] = '';

    foreach ($rest['implode'] as $column) {
        $rest['update'] .= "`$column` = `:$column`,";       // add each column to our POST (UPDATE) in this format
    }
    $rest['update'] = substr($rest['update'], 0, strlen($rest['update']) - 1);  // but remove the last comma

    $rest['listed'] = implode(", ", $rest['implode']);

    $implode = '';
    foreach ($rest['implode'] as &$value) {
        if (in_array($value, $binary) && isset($rest['primary']) && $rest['primary'] === $value) {
            if (isset($foreign_key) && $value === $foreign_key) {
                $implode .= ', ? ';
            } else {
                $implode .= ', (UNHEX(REPLACE(UUID(),"-","")))';
            }
        } else {
            $implode .= ', :' . $value;
        }
    }
    $rest['implode'] = substr($implode, 1);

    $verbose and var_dump($rest);

    file_put_contents(APP_ROOT . 'table/' . $rest['TableName'] . '.php', $mustache($rest));
}

print "\tDone\n\n";

$delete_dump and unlink('./config/mysqldump.sql');

//ncurses_end();

return 0;



