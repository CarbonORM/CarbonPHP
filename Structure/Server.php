#!/usr/bin/php
<?php

//

declare(ticks=1);

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

const SOCKET = true;

if (!extension_loaded('pcntl'))
    print 'To use CarbonPHP\'s server you must have PCNTL enabled. See CarbonPHP.com for more information.';

if (!(($argv[1] ?? false) && $argv = json_decode($argv[1]) && json_last_error() == JSON_ERROR_NONE))
    print 'This script should not be called directly. See CarbonPHP.com for documentation.' and die;

if (false == (include_once "../Structure/Carbon.php"))
    print 'A file structure error has occurred. Please retry downloading CarbonPHP.' and die;

if (false == file_exists($psr4 = ($argv['DIRECTORY']['VENDOR'] ?? DS . 'autoload.php')))
    print 'You must include a $PHP[\'DIRECTORY\'][\'VENDOR\'] = (string)';


include $psr4;

use Carbon\Helpers\Fork;

use \Carbon\Helpers\Pipe;

use \Carbon\Request;

use \Carbon\Helpers\Socket;

# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/

pcntl_signal( SIGTERM, 'signalHandler' ); // Termination ('kill' was called')

pcntl_signal( SIGHUP, 'signalHandler' );  // Terminal log-out

pcntl_signal( SIGINT, 'signalHandler' );  // Interrupted ( Ctrl-C is pressed)

// Fork::become_daemon();

class Server
{
    private $host;
    private $port;

    public function __construct($host, $port)
    {
        $this->port = $port;
        $this->host = $host;          //localhost
        $this->accept(new Socket($port));
    }

    public function accept(Socket $socket)
    {
        $user = socket_accept($socket->socket); // new socket user

        $socket->perform_handshaking(socket_read($user, 1024), $user, $this->host, $this->port);  //perform websocket handshake

        socket_getpeername($user, $ip);     // get ip address of connected socket

        $this->Serve($socket, $user, $ip);
    }


    public function Serve(Socket $socket, $user, $ip)
    {
        /*
        Fork::safe(function () use ($socket){
            $this->accept($socket) and die;
        });
        */

        // we should verify our connection now
        new \Carbon\Session($ip);             // Pull From Database, manage socket ip

        global $STDOUT;

        fclose(STDOUT);              // output has to be preprocessed for websites and javascript

        fclose($STDOUT);                    // To Json...

        $STDOUT = Pipe::named(SERVER_ROOT . 'Temp/' . $_SESSION['id'] . '.stdout');   // now echo and print will get sent to this file for buffering

        $UPDATE = Pipe::named(SERVER_ROOT . 'Temp/' . $_SESSION['id'] . '.fifo');     // other users can notify us to update our application through this file

        $request = new Request;                     // handles string validation from userIO

        print 'Socket Active' . PHP_EOL;            // This will get sent to the file descriptor, but will not send until ( ** 1 )

        while (true) {  // loop.

            // if the named pipe is blocking we know the user is active with each empty data set received
            // this is the equivalent to the ready state, if the foreach is run and no descriptor is active
            // or has been `hit` via handshake, the socket will assume the user is offline and kill the process.
            // its costly to do this..?
            $miss = 0;
            $handshake = 0;

            $readers = array($UPDATE, $STDOUT, $user);    // This must be reset each loop

            // poll the socket and named pipe for input. The socket is the users browser while the named pipe is our application.
            if (($stream = stream_select($readers, $writers, $except, 0, 15)) === false):
                print "A stream error occurred\n";
                break;

            else :
                // Readers will have only files with input available
                foreach ($readers as $input => $fd) {

                    if ($fd == $STDOUT): //
                        $string = fgets($STDOUT); // valid application output, S'clean. ( ** 1 )
                        if ($string == 'exit'):
                            print "Application closed socket \n";
                            exit(2);
                        elseif (!empty($string) && Fork::safe()):
                            $socket->send_message($user, $string);
                            exit(1);
                        endif;
                        $handshake++;

                    elseif ($fd == $UPDATE):
                        // Application.php sends a request to update via route uri
                        $data = fread($UPDATE, $bytes = 1024);  // This will read multiple lines
                        // we only send uri's to help with validation, and to update the applicable users session data
                        if (!empty($data)):
                            $data = explode("\n", $data); // separate uri's by newline.
                            foreach ($data as $i => $value) {
                                if (empty($value))
                                    continue;
                                if (Fork::safe()):              // fork a request foreach uri
                                    print "Update :: $value \n";
                                    $_SERVER['REQUEST_URI'] = $value;
                                    startApplication($value);
                                    exit(1);
                                endif;
                            }
                        else:
                            print "Handshake \n";
                        endif;
                        $handshake++;

                    elseif ($fd == $user):
                        //check for any incomming data
                        while (socket_recv($user, $buf, 1024, 0) >= 1) {
                            $received_text = $socket->unmask($buf); //unmask data
                            $received_text = $request->set($received_text)->noHTML()->value(); // validate, S'clean.
                            startApplication($received_text);
                            break 2; //exist this loop
                        }
                        $buf = @socket_read($user, 1024, PHP_NORMAL_READ);
                        if ($buf === false) : // check disconnected client
                            exit(1);
                        endif;

                    else :
                        // validate active socket
                        print "Hits => $handshake";
                        if ($handshake != 0):       // clear misses
                            $handshake = 0;
                            $miss = 1;

                        elseif ($miss == 10):       // 10 misses !!?!?
                            exit(2);

                        else: $miss++;              // Nothing active, hu?
                            print "Miss => $miss\n";
                        endif;
                    endif;
                }
                sleep(1);     // Keep it off the processor stack
            endif;
        }

    }

}

$server = new Server('127.0.0.1', '8080');


//Kill Connection
function signalHandler($signal)
{
    switch ($signal) {                      // Attempt to clean up our file descriptors
        case SIGTERM:
        case SIGINT:
            print "Signal :: $signal\n";
            global $fifoPath, $fp;
            if (is_resource($fp))
                @fclose($fp);
            if (file_exists($fifoPath))
                @unlink($fifoPath);
            print "Safe Exit \n\n";
            exit(1);
        case SIGCHLD:
            pcntl_waitpid(-1, $status); // The child processes send SIGCHLD to their parent when they exit but the parent can't handle signals while it's waiting for socket_accept (blocking).
            break;
    }

}