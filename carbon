#!/usr/bin/env php
<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 5/14/18
 * Time: 11:37 PM
 */

$executable = __FILE__;

$help = function () use ($executable) {
    return <<<END
\n\n
\t.$executable [ :path? ] [ :command [ :args? ] ]\n
\tHelp
\tWebsocketd
\n\n
END;
};

########################## check args
if ($argc === 1) {
    print $help() and die;
}

########################## change working dir from shebang
if (is_dir($argv[1])) {

    print $argv[1] . PHP_EOL;

    chdir($argv[1]);
} else {
    chdir(__DIR__);
}

########################## configure composer
if (false === (include 'autoload.php')) {   // Load the autoload() for composer dependencies located in the Services folder
    print 'Loading Composer Failed. See Carbonphp.com for documentation.' . $help();
    die;
    // Composer autoload
}

switch ($argv) {
    case 'server':
        #################   SOCKET AND SYNC    #######################
        if (!SOCKET && ($PHP['SOCKET'] ?? false) && !getservbyport($PHP['SOCKET']['PORT'] ?? 8888, 'tcp')) {

            if (($PHP['SOCKET']['WEBSOCKETD'] ?? false) === true) {
                $CMD = '/usr/bin/websocketd --port=' . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
                    (($PHP['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
                    (($PHP['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey={$PHP['SOCKET']['SSL']['KEY']} --sslcert={$PHP['SOCKET']['SSL']['CERT']} " : ' ') .
                    'php ' . CARBON_ROOT . 'Extras' . DS . 'Websocketd.php ' . SERVER_ROOT . ' ' . ($PHP['SITE']['CONFIG'] ?? SERVER_ROOT) . ' 2>&1';
            } else {
                $CMD = 'php ' . CARBON_ROOT . 'Structure' . DS . 'Server.php ' . ($PHP['SITE']['CONFIG'] ?? SERVER_ROOT);
            }

            Helpers\Fork::become_daemon(function () use ($CMD) {
                `$CMD`;
            });

            print $CMD;
            die;
        }


    case 'Help':
    default:
}









