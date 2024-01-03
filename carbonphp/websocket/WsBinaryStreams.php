<?php

namespace CarbonPHP\WebSocket;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Interfaces\iColorCode;
use Socket;

abstract class WsBinaryStreams
{
    /**
     * @var Socket|resource|null $socket https://www.php.net/manual/en/function.stream-set-timeout.php#100676
     */
    public static mixed $socket = null; // still a resource @link https://stackoverflow.com/questions/66871564/php-8-assign-resource-as-property-parameter-or-return-type
    public const CONTINUE = 0x0;

    public const TEXT = 0x1;

    public const BINARY = 0x2;

    public const CLOSE = 0x8;

    public const PING = 0x9;

    public const PONG = 0xa;

    # https://stackoverflow.com/questions/4812686/closing-websocket-correctly-html5-javascript
    public const CLOSE_DATA_FRAME = 0x88; // todo - maybe, it ends up being equivalent to close.?

    public static int $port = 8888;

    public static bool $ssl = false;

    public static string $host = 'localhost';

    public static string $cert = '/cert.pem';

    public static string $pass = 'Smokey';
    public static function handshake($socket, array &$headers = []): bool
    {
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

        // in the spirit of using actual header values
        // @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
        // well use warning to store general information
        $headers['Warning'] = $lines[0] ?? '';

        $_SERVER['HTTP_COOKIE'] = $headers['Cookie'] ?? [];

        $_SERVER['User_Agent'] = $headers['User-Agent'] ?? '';

        $_SERVER['Host'] = $headers['Host'] ?? '';

        $secKey = $headers['Sec-WebSocket-Key'];

        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        $response = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            'WebSocket-Origin: ' . self::$host . "\r\n" .
            'WebSocket-Location: ws://' . self::$host . ':' . self::$port . "/\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";

        try {

            return fwrite($socket, $response);

        } catch (\Exception $e) {

            return false;

        }

    }

    public static function encode($message, $opCode = self::TEXT): string
    {

        $rsv1 = 0x0;

        $rsv2 = 0x0;

        $rsv3 = 0x0;

        $message = is_string($message) ? $message : json_encode($message);

        $length = strlen($message);

        $out = chr((0x1 << 7) | ($rsv1 << 6) | ($rsv2 << 5) | ($rsv3 << 4) | $opCode);

        if (0xffff < $length) {

            $out .= chr(0x7f) . pack('NN', 0, $length);

        } elseif (0x7d < $length) {

            $out .= chr(0x7e) . pack('n', $length);

        } else {

            $out .= chr($length);

        }

        return $out . $message;

    }

    public static function decode($socketResource): array
    {

        if (!$socketResource || !is_resource($socketResource)) {

            return [
                'error' => 'Resource gone away',
                'opcode' => self::CLOSE,
                'payload' => ''
            ];

        }

        $out = [];

        //$read = fread($socketResource, 1);
        // should things be unexpectedly sending with a length of 0 @link https://stackoverflow.com/questions/64855794/proxy-timeout-with-rewriterule
        // @link https://stackoverflow.com/questions/41115870/is-binary-opcode-encoding-and-decoding-implementation-specific-in-websockets
        $read = stream_get_contents($socketResource, 1);

        if (false === $read) {

            $socket = socket_import_stream(self::$socket);

            return [
                'socketStatus' => stream_get_meta_data($socketResource),
                'error' => 'socket read failure',
                'socket_last_error' => $code = socket_last_error($socket),
                'socket_strerror' => socket_strerror($code),
                'opcode' => self::CLOSE,
                'payload' => ''
            ];

        }

        if (empty($read)) {

            ColorCode::colorCode('Empty WS Read', iColorCode::BACKGROUND_RED);

            $socket = socket_import_stream(self::$socket);

            return [
                'stream_get_meta_data' => stream_get_meta_data($socketResource),
                'error' => 'empty socket read, if your proxying this could be a timeout. @link https://stackoverflow.com/questions/64855794/proxy-timeout-with-rewriterule',
                'socket_last_error' => $code = socket_last_error($socket),
                'socket_strerror' => socket_strerror($code),
                'opcode' => self::PING,
                'payload' => ''
            ];

        }

        $handle = ord($read);

        //Get the first byte and & it with 127, the result is your FIN bit
        $out['fin'] = ($handle >> 7) & 0x1; // get the 7th bit in the first byte

        $out['rsv1'] = ($handle >> 6) & 0x1; // get the 6th bit in the first byte

        $out['rsv2'] = ($handle >> 5) & 0x1;

        $out['rsv3'] = ($handle >> 4) & 0x1;

        // Get the first byte and & it with 15, the result is your opcode
        $out['opcode'] = $handle & 0xf; // get the last 4 bits in the first byte

        if (self::CLOSE === $out['opcode']) {

            $out['dataframe'] = $handle & 0xff;

            $out['CLOSE_DATA_FRAME'] = self::CLOSE_DATA_FRAME;

        }

        if (!in_array($out['opcode'], [
            self::CONTINUE,
            self::TEXT,
            self::BINARY,
            self::CLOSE,
            self::PING,
            self::PONG
        ], true)) {
            return $out + [
                    'error' => 'unknown opcode (1003)'
                ];
        }

        $handle = ord(fread($socketResource, 1));

        // Most significant bit of the 2nd byte, tells you if the payload has been masked. A Server must not mask any frame!
        // Get the second byte and & it with 127, if it is 127 you have a masking key
        $out['mask'] = ($handle >> 7) & 0x1;

        // Payload Length (This is where things can get complicated)
        // Take the 2nd byte and read every bit except the Most significant bit
        $out['length'] = $handle & 0x7f;

        // Byte is 125 or fewer that's your length
        $length = &$out['length'];

        if ($out['rsv1'] !== 0x0 || $out['rsv2'] !== 0x0 || $out['rsv3'] !== 0x0) {
            return [
                'opcode' => $out['opcode'],
                'payload' => '',
                'error' => 'protocol error (1002)'
            ];
        }

        // Byte is 126
        // Your length is an uint16 of byte 3 and 4
        if ($length === 0x7e) {

            $handle = unpack('nl', fread($socketResource, 2));

            $length = $handle['l'];

        } elseif ($length === 0x7f) {
            // Byte is 127
            // Your length is a uint64 of byte 3 to 8

            $handle = unpack('N*l', fread($socketResource, 8));

            $length = $handle['l2'] ?? $length;

            if ($length > 0x7fffffffffffffff) {

                ColorCode::colorCode('WS Length > 0x7fffffffffffffff', iColorCode::BACKGROUND_RED);

                return $out + [
                        'payload' => '',
                        'error' => 'content length mismatch'
                    ];

            }

        }

        // Masking key
        // Only exists if the MASK bit is set
        if ($out['mask'] === 0x0) { // (no mask set)

            // Payload can be decoded either as Text (UTF-8) or Binary (Can be any data)
            // The payload needs to be masked if the MASK bit is set

            $msg = '';

            $readLength = 0;

            // This is not the whole payload if the FIN bit is set
            while ($readLength < $length) {

                $toRead = $length - $readLength;

                $msg .= fread($socketResource, $toRead);

                if ($readLength === strlen($msg)) {

                    break;

                }

                $readLength = strlen($msg);

            }

            $out['payload'] = $msg;

            ColorCode::colorCode(print_r($out, true), iColorCode::BACKGROUND_CYAN);

            return $out;

        }


        // Payload
        // The next 4 bytes is the masking key, this key is used to decode the payload

        $maskN = array_map('ord', str_split(fread($socketResource, 4)));

        $maskC = 0;

        $bufferLength = 1024;

        $message = '';

        // This is not the whole payload if the FIN bit is set
        for ($i = 0; $i < $length; $i += $bufferLength) {

            $buffer = min($bufferLength, $length - $i);

            $handle = fread($socketResource, $buffer);

            for ($j = 0, $_length = strlen($handle); $j < $_length; ++$j) {

                $handle[$j] = chr(ord($handle[$j]) ^ $maskN[$maskC]);

                $maskC = ($maskC + 1) % 4;

            }

            $message .= $handle;

        }

        $isJson = json_decode($message, true);

        $out['payload'] = $isJson ?: $message;

        ColorCode::colorCode(print_r($out, true), iColorCode::MAGENTA);

        return $out;

    }

}