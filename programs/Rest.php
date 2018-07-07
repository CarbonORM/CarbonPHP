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
\t       -f [?file_of_Tables]          - file of table names separated by eol
\t       -e [?executable]              - path to mysqldump command 
\t       -dump [?dump]                 - path to a mysqldump sql export 
\n
END;
    exit(1);
};

$argc < 3 and $usage();    // quick if stmt

$pass = '';
$onlyThese = null;
$verbose = $primary_required = $delete_dump = $carbon_namespace = false;

for ($i = 0; $i < $argc; $i++) {
    switch ($argv[$i]) {
        case '-v':
            $verbose = true;
            break;
        case '-carbon':
            $carbon_namespace = true;
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
            if (empty($file = file_get_contents("{$argv[++$i]}"))) {
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


if (empty($schema)) {
    print 'You must specify the table schema!';
    exit(1);
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
        'primary' => [],
        'database' => $schema,
        'carbon_table' => false,
        'carbon_namespace' => $carbon_namespace
    ];
    $skipping_col = [];
    $primary = [];

    // Every line in table insert
    foreach ($insert as $query) {                                                  // Create Columns
        $cast_default = false;
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
            $primary = explode('`,`', trim($query[2], "(`),"));

            $sql = [];
            foreach ($primary as $key) {
                if (in_array($key, $binary)) {
                    $sql[] = ' ' . $key . '=UNHEX(\' . $primary .\')';
                } else {
                    $sql[] = ' ' . $key . '=\' . $primary .\'';
                }
                $rest['primary'][] = ['name' => $key];
            }
            $rest['primary'][] = ['sql' => '$sql .= \' WHERE ' . implode($sql, ' OR ') . "';" ];

        } else if ($query[0] === 'CONSTRAINT' && $query[6] === '`carbon`' && isset($rest['primary'])) {
            if (in_array($fk = trim($query[4], "()`"), $rest['primary'])) {
                $foreign_key = $fk;
                $rest['carbon_table'] = $rest['TableName'] !== 'carbon';
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
                    $length = trim($argv[1], '),');
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
                        // looks like this wasn't needed
                        #$length *= 2;           // so php can check the right string length that mysql will convert
                        $binary[] = $name;
                        $rest['binary_list'][] = ['name' => $name];
                        $rest['explode'][$column]['binary'] = true;
                        $cast_default = true;
                    case 'varchar':
                    default:
                        $type = $PDO[3];
                }
            }

            $query_default = count($query) - 2;

            $key = array_search('DEFAULT', $query);
            if ($key !== false) {
                $default = rtrim($query[++$key], ',');
                if ($default == 'CURRENT_TIMESTAMP') {
                    $skipping_col[] = $name;
                    $rest['explode'][$column]['skip'] = true;
                } else if ($default[0] !== '\'') {
                    $default = "'$default'";
                }
            } else {
                unset($default);
            }

            $auto_inc = count($query) - 1;
            if (isset($query[$auto_inc]) && $query[$auto_inc] === 'AUTO_INCREMENT,') {
                $skipping_col[] = $name;
                $rest['explode'][$column]['skip'] = true;

                #var_dump($auto_inc_col);

            }

            $rest['explode'][$column]['name'] = $name;
            $rest['explode'][$column]['type'] = $type;

            if (isset($length)) {
                $rest['explode'][$column]['length'] = $length;
            }

            if (isset($default)) {
                // nested ternary, sorry guys
                $rest['explode'][$column]['default'] = $default === "'NULL'" ? 'null' : $cast_default ? 'null' : $default;
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
            if (false !== in_array($value['name'], $primary)) {
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

    $rest['listed'] = $implode = '';

    foreach ($rest['implode'] as &$value) {
        if (!in_array($value, $skipping_col)) {

            $rest['listed'] .= $value . ', ';

            if (in_array($value, $binary) && in_array($value,$primary)) {
                if (isset($foreign_key) && $rest['TableName'] !== 'carbon' && $value === $foreign_key) {
                    $implode .= ', UNHEX(:' . $value . ')';
                } else {
                    $implode .= ', UNHEX(:' . $value . ')';
                    //$implode .= ', (UNHEX(REPLACE(UUID(),"-","")))';
                }
            } else {
                $implode .= ', :' . $value;
            }
        }
    }

    $rest['listed'] = trim($rest['listed'], ', ');

    $rest['implode'] = substr($implode, 1);

    $verbose and var_dump($rest);

    file_put_contents(APP_ROOT . 'table/' . $rest['TableName'] . '.php', $mustache($rest));
}

print "\tDone\n\n";

$delete_dump and unlink('./config/mysqldump.sql');

//ncurses_end();

return 0;



