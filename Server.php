<?php

namespace CarbonPHP\Helpers;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Helpers\Pipe;
use CarbonPHP\Request;
use CarbonPHP\Session;
use CarbonPHP\Database;
use CarbonPHP\CarbonPHP;

define('SOCKET', true);

define('DS', DIRECTORY_SEPARATOR);


if (\count($argv) < 2) {
    print 'The Server should not be started statically';
    die(0);
}

error_reporting(E_ALL);

set_time_limit(0);

ob_implicit_flush();

\define('APP_ROOT', $argv[1]);    // expressions not allowed in const

dir(APP_ROOT);

if (false === (include APP_ROOT . 'vendor/autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Loading Composer Failed. See Carbonphp.com for documentation.</h1>' and die;     // Composer autoload
}

if (!\extension_loaded('pcntl')) {
    print '<h1>CarbonPHP Websockets require the PCNTL library. See CarbonPHP.com for more Documentation</h1>';
}

new CarbonPHP($opts = include $argv[2]);


$signal = function ($signal) {
    print "Signal :: $signal\n";
    global $fifoPath, $fp;
    if (\is_resource($fp)) {
        @fclose($fp);
    }
    if (file_exists($fifoPath)) {
        @unlink($fifoPath);
    }
    exit(1);
};

# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
pcntl_signal(SIGTERM, $signal); // Termination ('kill' was called')
pcntl_signal(SIGHUP, $signal);  // Terminal log-out
pcntl_signal(SIGINT, $signal);  // Interrupted ( Ctrl-C is pressed)


class Server extends Request
{
    private const TEXT = 0x1;
    private const BINARY = 0x2;
    private const CLOSE = 0x8;
    private const PING = 0x9;
    private const PONG = 0xa;
    private const PORT = 8080;
    private const SSL = false;

    private const HOST = 'localhost';
    private const CERT = '/cert.pem';
    private const PASS = 'Smokey';

    public $user;
    public $user_ip;
    public $user_port;


    public function __construct($config)
    {
        \define('URL', $config['SITE']['SITE'] ?? LOCAL_SERVER);

        if (!URL) {
            print 'Your url must be set for Sockets';
            exit(1);
        }

        \define('SITE', URL . DS);

        #\define('URI', '');

        if (self::SSL) {
            $context = stream_context_create([
                'ssl' => [
                    'local_cert' => self::CERT,
                    'passphrase' => self::PASS,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'verify_depth' => 0
                ]
            ]);
            $protocol = 'ssl';
        } else {
            $context = stream_context_create();
            $protocol = 'tcp';
        }
        $socket = stream_socket_server($c = "$protocol://" . ($config['SOCKET']['HOST'] ?? '127.0.0.1') . ':' . $config['SOCKET']['PORT'], $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

        if (!$socket) {
            print "$errorString ($errorNumber)<br />\n";
        }

        $sock_fd = [$socket];

        for (; ;) {                         // should
            do {
                $clientList = $sock_fd;
                $number = stream_select($clientList, $write, $error, 5);
            } while (!$number);

            // Forking can return the pid for parent, 0 for child, and -1 for error
            // we need to pop
            $connection = array_pop($clientList); // This is the users file descriptor linked to a unique process

            if (($connection = stream_socket_accept($connection, ini_get("default_socket_timeout"), $peername)) === false) {
                continue;
            }

            if (!$this->handshake($connection)) {              // attempt to send wss validation
                @fclose($connection);                          // close any accepted connection if failurephp /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/Data/Vendors/richardtmiles/carbonphp/Structure/Server.php /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/ /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/Application/Config/Config.php
                continue;
            }

            if (($pid = pcntl_fork()) > 0) {                     // if parent restart looking for incomming connections
                continue;
            }
            if ($pid < 0) {
                ErrorCatcher::generateCallTrace();             // log errors
            }

            [$this->user_ip, $this->user_port] = explode(':', $peername);

            $this->user = &$connection;

            return;
        }
    }

    public function serve($connection)
    {
        if (!\is_resource($connection)) {
            print 'Failed to handle user connection';
            die(1);
        }

        new Session($this->user_ip, true, false);  // todo - default to storing on db

        $fifoFile = Pipe::named(APP_ROOT . 'Data/Temp/' . session_id() . '.fifo');     // other users can notify us to update our application through this file

        Session::pause();           // Close the current session

        Database::setDatabase();    // This will clear the connection

        $read = [$this->user, $fifoFile];

        $run = function ($url) use (&$connection) {
            ob_start();
            $_SERVER["REQUEST_URI"] = $url;
            startApplication($url);
            $buff = ob_get_clean();
            @fwrite($connection, $this->encode($buff));
        };

        while (true) {
            do {
                $read = [$this->user, $fifoFile];
                $mod_fd = stream_select($read, $_w, $_e, 5);  // returns number of file descriptors modified
            } while (!$mod_fd);

            foreach ($read as $connection) {

                if ($connection === $fifoFile) { // accepting a new connection?

                    $data = fread($fifoFile, $bytes = 1024);

                    $data = explode(PHP_EOL, $data);

                    foreach ($data as $id => $uri) {

                        if (!empty($uri)) {

                            if (pcntl_fork() === 0) {

                                \CarbonPHP\Session::resume();

                                $run(trim($uri));

                                print "Update :: $uri \n";

                                startApplication($uri); // will DO NOT exit in view

                                exit(1);    // but if we decide to change that...  (we decided to change that!)
                            }
                        }
                    }

                } else {
                    $data = $this->decode($connection);

                    switch ($data['opcode']) {
                        case self::CLOSE:
                            @fclose($this->user);
                            exit(1);
                            break;
                        case self::PING :
                            @fwrite($connection, $this->encode('', self::PONG));
                            break;
                        case self::TEXT:
                            if ($data['payload'] = $this->set($data['payload'])->noHTML(true)) {
                                $run($data['payload']);
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    public function handshake($socket): bool
    {
        $headers = [];
        $lines = preg_split("/\r\n/", @fread($socket, 4096));

        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        if (!isset($headers['Sec-WebSocket-Key'])) {
            return false;
        }
        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $response = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: " . self::HOST . "\r\n" .
            "WebSocket-Location: ws://" . self::HOST . ":" . self::PORT . "/\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        try {
            return fwrite($socket, $response);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function encode($message, $opCode = self::TEXT): string
    {
        $rsv1 = 0x0;
        $rsv2 = 0x0;
        $rsv3 = 0x0;
        $message = json_encode($message);
        $length = strlen($message);
        $out = \chr((0x1 << 7) | ($rsv1 << 6) | ($rsv2 << 5) | ($rsv3 << 4) | $opCode);
        if (0xffff < $length) {
            $out .= \chr(0x7f) . pack('NN', 0, $length);
        } elseif (0x7d < $length) {
            $out .= \chr(0x7e) . pack('n', $length);
        } else {
            $out .= \chr($length);
        }
        return $out . $message;
    }

    public function decode($socket): array
    {
        if (!$socket || !\is_resource($socket)) {
            return [
                'opcode' => self::CLOSE,
                'payload' => ''
            ];
        }
        $out = [];
        $read = @fread($socket, 1);

        if (empty($read)) {
            return [
                'opcode' => self::CLOSE,
                'payload' => ''
            ];
        }
        $handle = \ord($read);
        $out['fin'] = ($handle >> 7) & 0x1;
        $out['rsv1'] = ($handle >> 6) & 0x1;
        $out['rsv2'] = ($handle >> 5) & 0x1;
        $out['rsv3'] = ($handle >> 4) & 0x1;
        $out['opcode'] = $handle & 0xf;
        if (!\in_array($out['opcode'], [
            self::TEXT,
            self::BINARY,
            self::CLOSE,
            self::PING,
            self::PONG], true)) {
            return ['opcode' => '', 'payload' => '',
                'error' => 'unknown opcode (1003)'
            ];
        }
        $handle = \ord(fread($socket, 1));
        $out['mask'] = ($handle >> 7) & 0x1;
        $out['length'] = $handle & 0x7f;
        $length = &$out['length'];
        if ($out['rsv1'] !== 0x0 || $out['rsv2'] !== 0x0 || $out['rsv3'] !== 0x0) {
            return [
                'opcode' => $out['opcode'],
                'payload' => '',
                'error' => 'protocol error (1002)'
            ];
        }
        if ($length === 0) {
            $out['payload'] = '';
            return $out;
        }
        if ($length === 0x7e) {
            $handle = unpack('nl', fread($socket, 2));
            $length = $handle['l'];
        } elseif ($length === 0x7f) {
            $handle = unpack('N*l', fread($socket, 8));
            $length = $handle['l2'] ?? $length;
            if ($length > 0x7fffffffffffffff) {
                return [
                    'opcode' => $out['opcode'],
                    'payload' => '',
                    'error' => 'content length mismatch'
                ];
            }
        }
        if ($out['mask'] === 0x0) {
            $msg = '';
            $readLength = 0;
            while ($readLength < $length) {
                $toRead = $length - $readLength;
                $msg .= fread($socket, $toRead);
                if ($readLength === strlen($msg)) {
                    break;
                }
                $readLength = strlen($msg);
            }
            $out['payload'] = $msg;
            return $out;
        }
        $maskN = array_map('ord', str_split(fread($socket, 4)));
        $maskC = 0;
        $bufferLength = 1024;
        $message = '';
        for ($i = 0; $i < $length; $i += $bufferLength) {
            $buffer = min($bufferLength, $length - $i);
            $handle = fread($socket, $buffer);
            for ($j = 0, $_length = strlen($handle); $j < $_length; ++$j) {
                $handle[$j] = chr(ord($handle[$j]) ^ $maskN[$maskC]);
                $maskC = ($maskC + 1) % 4;
            }
            $message .= $handle;
        }
        $out['payload'] = json_decode($message);
        return $out;
    }


}


$socket = new Server($opts);


$socket->serve($socket->user);

/*
 *

if ($pid = pcntl_fork()) {                         // if parent restart looking for incomming connections
                continue;
            }
            if ($pid < 0) {
                ErrorCatcher::generateCallTrace();  // log errors
            }

            [$ip, $port] = explode(':', $peername);

            if (!defined('IP')) {
                define('IP', $ip);
            }
}
 */