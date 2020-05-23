#!/usr/bin/php
<?php declare(ticks=1);             // so we can catch exit signals ,

//if we wait to send output until we fork we can preserve our socket and session with the database
const SOCKET = true;                // faster than define

if (!($argv[1] ?? false)) {
    print "This file should not be executed statically.\n" and die;
}

if (!is_dir($argv[1])) {
    print "The SERVER_ROOT should be a valid line argument.\n" and die;
}


if (!file_exists($argv[1] . 'index.php')) {
    print 'The APP_ROOT should be the directory your index is located. Currently there is no index.php in '. APP_ROOT;
    exit(1);
}

if (false === (include $argv[1] . 'index.php'))
{   // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Loading Your Index Failed! See Carbonphp.com for documentation.</h1>';
    exit(1); // Composer autoload
}

if (!($_SESSION ?? false)) {
    print "You must be logged in to use this API\n";
    exit(1);
}

error_reporting(E_ALL);     // Reported to console

set_time_limit(0);       //  No timeout

ob_implicit_flush();             // send on freaking print!!!

if (!extension_loaded('pcntl')) {
    print "Sorry websockets require the PCNTL library be installed. Please see CarbonPHP.com for documentation.\n" and die;
}

# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
pcntl_signal( SIGTERM, 'signalHandler' ); // Termination ('kill' was called')
pcntl_signal( SIGHUP, 'signalHandler' );  // Terminal log-out
pcntl_signal( SIGINT, 'signalHandler' );  // Interrupted ( Ctrl-C is pressed)

$fifoFile = \CarbonPHP\helpers\Pipe::named( APP_ROOT . 'data/sessions/' . $_SESSION['id'] . '.fifo');     // other users can notify us to update our application through this file

$fifoFile or die;

$stdin = fopen( 'php://stdin', 'b' );

$request = new class extends CarbonPHP\Request
{
    public function is_json($string)
    {
        json_decode( $string );
        return json_last_error() === JSON_ERROR_NONE;
    }
};

\CarbonPHP\Session::pause();           // Close the current session
\CarbonPHP\Database::setDatabase();    // This will clear the connection

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

                $string = $request->set( fgets( $stdin ) )->noHTML(true);      // I think were going to make this a search function

                if ($string === 'exit') {
                    print "Application closed socket \n" and die;

                } elseif (!empty( $string ) && pcntl_fork() === 0) {     // Fork

                    \CarbonPHP\Session::resume();      // resume session

                    $_SERVER['REQUEST_URI'] = $string = trim(preg_replace('/\s+/', ' ', $string));

                    print "startApplication('$string')\n";

                    startApplication( $string );

                    exit( 1 );
                } $handshake++;

            } elseif ($fd === $fifoFile) {

                $data = fread( $fifoFile, $bytes = 1024 );

                $data = explode(PHP_EOL,$data);

                foreach ($data as $id => $uri) {

                    if (!empty($uri)) {

                        if (pcntl_fork() === 0) {

                            \CarbonPHP\Session::resume();

                            $_SERVER['REQUEST_URI'] = $string = trim(preg_replace('/\s+/', ' ', $uri));

                            print "Update startApplication('$uri')\n";

                            startApplication( $uri );

                            exit( 1 );  // but if we decide to change that...  (we decided to change that!)
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
    if (is_resource( $fp )) {
        @fclose( $fp );
    }
    if (file_exists( $fifoPath )) {
        @unlink( $fifoPath );
    }
    print "Safe Exit \n\n";
    exit( 1 );
}