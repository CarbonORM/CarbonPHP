<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 10/7/17
 * Time: 11:06 AM
 *
 *
 *
 * TODO - This.
 */

namespace Carbon\Helpers;


class Socket
{
    public $socket;

    public function __construct($port = '8080')
    {
        //Create TCP/IP stream socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        //reusable port
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        //bind socket to specified host
        socket_bind($socket, '127.0.0.1', $port);

        //listen to port
        socket_listen($socket, 100); // 100 connections my be stacked before auto dropping

        // CarbonPHP attempts to never block
        socket_set_nonblock($socket);

        $this->socket = $socket;
    }

    //Unmask incoming framed message
    static function unmask($text)
    {
        $length = \ord($text[1]) & 127;
        if ($length === 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length === 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = '';
        $length = \strlen($data);
        for ($i = 0; $i < $length; ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    //Encode message for transfer to client.
    static function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = \strlen($text);

        $header = '';
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        return $header . $text;
    }

    public function send_message($socketFD, $msg)
    {
        return @socket_write($socketFD, $msg, \strlen($msg));
    }

    //handshake new client.
    function perform_handshaking($receved_header, $client_conn, $host, $port)
    {
        $headers = array();
        $lines = preg_split("/\r\n/", $receved_header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        //hand shaking header
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: ws://$host:$port/demo/shout.php\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        socket_write($client_conn, $upgrade, \strlen($upgrade));
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

}