#!/usr/bin/php
<?php declare(ticks=1);             // so we can catch exit signals ,

//if we wait to send output until we fork we can preserve our socket and session with the database
const SOCKET = true;                // faster than define

const DS = DIRECTORY_SEPARATOR;     // shorthand

if (!($argv[1] ?? false))
    print "This file should not be executed statically.\n" and die;

if (!is_dir($argv[1]))
    print "The SERVER_ROOT should be a valid line argument.\n" and die;

define('SERVER_ROOT', $argv[1]);    // expressions not allowed in const

if (!($argv[2] ?? false) && !file_exists($argv[2]))
    print "The SERVER_ROOT should be passed as a command line argument.\n" and die;

if (false === include((dirname(__FILE__, 2) . '/Structure/Carbon.php')))
    print "We failed to find a valid carbon application. You may need to reinstall or repair the carbon file layout. See CarbonPHP.com for documentation.\n\n" and die(0);

$config = include($argv[2]);

new \Carbon\Carbon($config);

if (!defined('SERVER_ROOT'))
    print 'We Failed to load CarbonPHP. Please see CarbonPHP.com for documentation.' . PHP_EOL and die;

error_reporting(E_ALL);     // Reported to console

set_time_limit(0);       //  No timeout

ob_implicit_flush();             // send on freaking print!!!

if (!extension_loaded('pcntl'))
    print "Sorry websockets require the PCNTL library be installed. Please see CarbonPHP.com for documentation.\n" and die;

# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
pcntl_signal( SIGTERM, 'signalHandler' ); // Termination ('kill' was called')
pcntl_signal( SIGHUP, 'signalHandler' );  // Terminal log-out
pcntl_signal( SIGINT, 'signalHandler' );  // Interrupted ( Ctrl-C is pressed)

$fifoFile = \Carbon\Helpers\Pipe::named(SERVER_ROOT . 'Data/Temp/' . $_SESSION['id'] . '.fifo');     // other users can notify us to update our application through this file

$stdin = fopen( 'php://stdin', 'b' );

$request = new class extends Carbon\Request
{
    public function is_json($string)
    {
        json_decode( $string );
        return json_last_error() === JSON_ERROR_NONE;
    }
};

\Carbon\Session::pause();           // Close the current session
\Carbon\Database::setDatabase();    // This will clear the connection

while (true)
{
    $miss = 0;
    $handshake = 0;
    $readers = array($fifoFile, $stdin);

    if (($stream = stream_select( $readers, $writers, $except, 0, 15 )) === false):
        print "A stream error occurred\n" and die;
    else :
        foreach ($readers as $input => $fd) {

            if ($fd === $stdin) {

                $string = $request->set( fgets( $stdin ) )->noHTML()->value();      // I think were going to make this a search function

                if ($string === 'exit') {
                    print "Application closed socket \n" and die;

                } elseif (!empty( $string ) && pcntl_fork() == 0) {     // Fork

                    \Carbon\Session::resume();      // resume session

                    print "Fetch :: $string \n";

                    $_SERVER['REQUEST_URI'] = $string;

                    startApplication( $string );

                    exit( 1 );
                } $handshake++;

            } elseif ($fd === $fifoFile) {

                $data = fread( $fifoFile, $bytes = 1024 );

                $data = explode(PHP_EOL,$data);

                foreach ($data as $id => $uri) {

                    if (!empty($uri)) {

                        if (pcntl_fork() === 0) {

                            \Carbon\Session::resume();

                            $_SERVER['REQUEST_URI'] = $uri = trim($uri);

                            print "Update :: $uri \n";

                            startApplication($uri); // will DO NOT exit in view

                            exit(1);    // but if we decide to change that...  (we decided to change that!)
                        }
                    }
                }
                $handshake++;
            } else {
                print "Hits => $handshake";
                if ($handshake !== 0):
                    $handshake = 0;
                    $miss = 1;
                elseif ($miss === 10):
                    exit( 1 );
                else: $miss++;
                    print "Misses => $miss\n";
                endif;
            }
        }
        sleep( 1 );
    endif;
}
function signalHandler($signal)
{
    print "Signal :: $signal\n";
    global $fifoPath, $fp;
    if (is_resource( $fp ))
        @fclose( $fp );
    if (file_exists( $fifoPath ))
        @unlink( $fifoPath );
    print "Safe Exit \n\n";
    exit( 1 );
}