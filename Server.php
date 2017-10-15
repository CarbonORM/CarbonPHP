<?php

/**
 *  This file is broken
 *
 *
 *
 */



declare(ticks=1);

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

const SOCKET = true;

include_once 'Carbon.php';    // This will load our configuration + auto-loaders

use Carbon\Helpers\Fork;
use \Carbon\Helpers\Pipe;
use \Carbon\Request;
use \Carbon\Helpers\Socket;

# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/

pcntl_signal(SIGTERM, 'signalHandler'); // Termination ('kill' was called')

pcntl_signal(SIGHUP, 'signalHandler');  // Terminal log-out

pcntl_signal(SIGINT, 'signalHandler');  // Interrupted ( Ctrl-C is pressed)

Fork::become_daemon();


class Server extends Thread
{
    private $mutex;     // Points to this, helpful for my eyes clarity ( C++ mutex , pthread )
    private $socket;
    private $host;      //
    private $port;      // Defaults to 8080


    public function __construct()
    {
        $this->mutex = $this;
        $this->host = 'localhost'; //host
        $this->socket = new Socket();
        $this->socket->start();
    }


    public function run()
    {
        global $STDOUT;
        /** This is uniquely executed  */

        $socket = $this->socket;
        $host = $this->host;
        $port = $this->port;

        $connection = $socket->socket;

        /** cause this thread to wait **/
        $userIO = $this->synchronized(function (Server $thread) use ($connection, $host, $port) {

            $socket_new = socket_accept($connection); // new socket user

            $header = socket_read($socket_new, 1024); //read data sent by the socket

            $thread->socket->perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake

            $thread->done = true;   // This is what the example was at PHP.net, notably arbitrary

            $thread->notify();      // Catch a connection, set up response, repeat.

            return $socket_new;

        }, $this);

        socket_getpeername($userIO, $ip);     // get ip address of connected socket


        // we should verify our connection now
        new \Carbon\Session($ip);             // Pull From Database, manage socket ip


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

            $readers = array($UPDATE, $STDOUT, $userIO);    // This must be reset each loop

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
                            $this->socket->send_message($userIO, $string);
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

                    elseif ($fd == $userIO):
                        //check for any incomming data
                        while (socket_recv($userIO, $buf, 1024, 0) >= 1) {
                            $received_text = $this->socket->unmask($buf); //unmask data
                            $received_text = $request->set($received_text)->noHTML()->value(); // validate, S'clean.
                            startApplication($received_text);
                            break 2; //exist this loop
                        }
                        $buf = @socket_read($userIO, 1024, PHP_NORMAL_READ);
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

$server = new Server();

while (true) {
    $server->start();                           // Fork
    /** cause this thread to wait **/
    $this->synchronized(function ($thread) {    // Wait
        if (!$thread->done) $thread->wait();
    }, $server);
}


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