<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 10/7/17
 * Time: 11:06 AM
 *
 *
 */

namespace Carbon\Helpers;


const SOCKET = true;

const DS = DIRECTORY_SEPARATOR;

// TODO - add the checks for the input args

error_reporting(E_ALL);

set_time_limit(0);

ob_implicit_flush();

#print_r(get_loaded_extensions()) and die;

if (!\extension_loaded('pcntl')) {
    print '<h1>CarbonPHP Websockets require the PCNTL library. See CarbonPHP.com for more Documentation</h1>';
}

$signal = function($signal)
{
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


class Socket
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

    private $socket;    // hold the connection
    private $stdin;     // input sent from browser
    private $pipe_fd;
    private $sock_fd;


    public function __construct($port = '8080')
    {
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
        $this->socket = stream_socket_server("$protocol://" . self::HOST . ':' . self::PORT, $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

        if (!$this->socket) {
            print "$errorString ($errorNumber)<br />\n";
        } else {
            $this->stdin = fopen('php://stdin', 'b');
            $this();
        }
    }

    public function __invoke()
    {
        $master[] = $this->socket;
        while (true) {
            $read = $master;
            $mod_fd = stream_select($read, $_w, $_e, 5);  // returns number of file descriptors modified
            if ($mod_fd === 0) {
                continue;
            }
            foreach ($read as $connection) {
                if ($connection === $this->socket) { // accepting a new connection?
                    $conn = @stream_socket_accept($this->socket);
                    if (!$this->handshake($conn)) {
                        fclose($conn);
                    } else {
                        // The connection has been opened

                        // TODO - we need to validate the ip ?

                        // We need to open a named pipe

                        fwrite($conn, $this->encode('Hello! The time is ' . date('n/j/Y g:i a') . "\n"));

                        $master[] = $conn;
                    }
                } else {
                    $data = $this->decode($connection);

                    var_dump($data);

                    switch ($data['opcode']) {
                        case self::CLOSE:
                            $key_to_del = array_search($connection, $master, false);
                            @fclose($connection);
                            unset($master[$key_to_del]);
                            break;
                        case self::PING :
                            @fwrite($connection, $this->encode('', self::PONG));
                            break;
                        case self::TEXT:
                            print 'The client has sent :';
                            var_dump($data['payload']);
                            @fwrite($connection, $this->encode([
                                'type' => 'usermsg',
                                'name' => $data['payload']->name,
                                'message' => $data['payload']->message,
                                'color' => $data['payload']->color
                            ]));
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


new Socket;

