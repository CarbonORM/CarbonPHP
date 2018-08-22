#!/usr/bin/env php
<?php

/**
 * >> php json.php -c "/c/Users/rmiles/Documents/GitHub/Armatus/src/praesidium/armatus/variables/context.jsx"
 *
 * Where the context file in the '-c' flag is relative to your computer
 *
 *
 * #CAVEATS --- You must end this program by typing 'exit' in the terminal. This is to avoid
 *              lingering process bound to ports // opened and unused/unreferenced file descriptors.
 *              If you forget to type 'exit' explicitly, and find things blew up next invocation
 *
 *              You'll have to manually kill the process bound the the ports needed.
 *
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/22/2018
 * Time: 10:26 AM
 *  * User: rmiles
 * Date: 8/11/2018
 * Time: 1:28 AM
 *
 */

ignore_user_abort(true);  // I don't like killing unfinished processes (who's reporting my be telling)

ob_implicit_flush(true);   // Output buffers suck, unless you use them... #

error_reporting(E_ALL);    // Reported to console

set_time_limit(0);      //  No timeout for our process

// The json server
if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    if ('/' !== $_SERVER['REQUEST_URI']) {

        if (!file_exists($path = __DIR__ . '/json' . $_SERVER['REQUEST_URI'] . '.php')) {
            print json_encode([
                'error' => 'Could not find the file requested.',
                'status' => 404,
                'location' => $path
            ]);
            die(1);
        }
        print json_encode(include $path);
    } else {
        print json_encode(["Hey Dude" => "You requested the root via ajax. Why? - Dick"]);
    }

    error_log("\n\tRequested :: {$_SERVER['REQUEST_URI']}\n\n");
    sleep(1);
    exit(0);
}


$usage = function () use ($argv) {
    print <<<END
\n
\t           Question Marks Denote Optional Parameters  
\t           Order does not matter. 
\t           Flags do not stack ie. not -efgh, 
\t              but this -e -f -g -h
\t Usage:: 
\t php index.php rest  
\t       -help          - this dialogue
\t       -v             - Verbose Output
\t       -c             - Path to the context.jsx file  
\t       -p             - Port to start the local server
\n
END;
    exit(1);
};


$verbose = false;

$argc < 2 and $usage();

$port = 8888;                   // default to something 'hopefully' open

for ($i = 1; $i < $argc; $i++) {
    switch ($argv[$i]) {
        case '-v':
            $verbose = true;
            break;
        case '-p':
            $port = $argv[++$i];
            break;
        case '-c':
            if (!array_key_exists(++$i, $argv)) {
                print "\n\tThe -p argument requires the path to 'context.jsx'\n\n";
                $usage();
                exit(1);
            }
            $contextPath = $argv[$i];
            if (false === $oldContext = file_get_contents($contextPath)) {
                print "\n\nCould not open file [ " . $argv[$i] . " ] for read/write operations\n\n";
                exit(1);
            }
            break;
        default:
            print "\n\n\t" . $argv[$i] . " is not a valid argument\n\n";
            $usage();
    }
}

if (empty($contextPath)) {
    print "Sorry, you must supply a context path\n\n";
    $usage();
    exit(1);
}

$configuration = function () use (&$port) {
    $host = $port ? "window.location.protocol + '//' + window.location.hostname + ':$port'" : '""';
    return <<<END
/**
* This file is used to parse the routes provided in the routing folder (material-ui)
* This affects the URI of the browser for the routes but not the files they reference
* To change the 'root' context for resource files you must edit the `homepage` variable
* in package.json
*
* running `npm start` will allow you dev on localhost with a root dir of `/`
*
*/

let root = (window.location.hostname === 'localhost' && '3000' === window.location.port ? '' : '/armatusUser/admin/r');

    function context(o){
    if ('path' in o) {
        o.path = root + o.path;
    }
    if ('pathTo' in o) {
        o.pathTo = root + o.pathTo;
    }
        return o;
    }

export default {
    contextRoot: context,
    contextHost: $host
};

END;
};

$verbose and print $configuration();

if (false === @file_put_contents($contextPath, $configuration())) {
    print 'Could not replace configuration file' . PHP_EOL;
    exit(1);
}


$phpServer = "php -S localhost:$port " . __FILE__;
$npmServer = "npm start";


$stdin = fopen('php://stdin', 'b');        // So we can exit safely
$stdout = fopen('php://stdout', 'b');      // So we can redirect input while nonblocking

$spec = array(
    0 => $stdout,  // stdin is a pipe that the child will read from
    1 => $stdout,  // stdout is a pipe that the child will write to
    2 => $stdout // stderr is a file to write to
);


$verbose and print $phpServer . PHP_EOL;

$php = proc_open($phpServer, $spec, $PHPpipes);

if (!is_resource($php)) {
    print 'Could not execute PHP server' . PHP_EOL;
    die(1);
}

$verbose and print $npmServer . PHP_EOL;

$npm = proc_open($npmServer, $spec, $NPMpipes);

if (!is_resource($npm)) {
    print 'Could not execute Node server' . PHP_EOL;
    die(1);
}


do {
    $cmd = fgets($stdin);
    sleep(1);
} while (trim($cmd) !== 'exit');

print "\n\n\tThanks for being totally awesome!\n\n";

function kill($process) {
    if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
        $status = proc_get_status($process);
        return exec('taskkill /F /T /PID '.$status['pid']);
    } else {
        return proc_terminate($process);
    }
}

fclose($stdin);     // Close File Descriptors
fclose($stdout);

kill($npm);
kill($php);

proc_close($php);
proc_close($npm);

sleep(4);

$port = false;
if (false === @file_put_contents($contextPath, $configuration())) {     // replace configuration to run with laravel
    print 'Could not replace configuration file' . PHP_EOL;
    exit(1);
}

exit(0);



