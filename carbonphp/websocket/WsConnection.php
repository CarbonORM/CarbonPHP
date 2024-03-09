<?php

namespace CarbonPHP\WebSocket;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Session;
use Closure;
use Error;
use Throwable;

abstract class WsConnection
{


    public static function decodeWebsocket(&$connection)
    {
        $data = WebSocket::decode($connection);

        switch ($data['opcode']) {

            case WsBinaryStreams::CLOSE:

                WsConnection::closeConnection($connection);

                break;

            case WsBinaryStreams::PONG:

                ColorCode::colorCode('Browser sent a response PONG', iColorCode::BACKGROUND_MAGENTA);

                break;

            case WsBinaryStreams::PING:

                ColorCode::colorCode('Browser sent PING', iColorCode::BACKGROUND_MAGENTA);

                if (false === WebSocket::sendToResource('', $connection, WsBinaryStreams::PONG)) {

                    WsConnection::closeConnection($connection);

                }

                break;

            case WsBinaryStreams::TEXT:

                if ('ping' === $data['payload']) {

                    ColorCode::colorCode('Browser sent text ping', iColorCode::BACKGROUND_MAGENTA);

                    if (false === WebSocket::sendToResource('pong', $connection)) {

                        WsConnection::closeConnection($connection);

                    }

                    break;

                }

                // we have to find the relation regardless,
                foreach (WebSocket::$userConnectionRelationships as $information) {

                    if ($information->userSocket === $connection) {

                        if (is_string($data['payload'])) {

                            WebSocket::forkStartApplication($data['payload'], $information, $connection);

                        } else {

                            ColorCode::colorCode("The 'payload' decoded was not a string. This is unexpected.", iColorCode::RED);

                            WsConnection::closeConnection($connection);

                        }

                        break;

                    }

                }

                ColorCode::colorCode("Failed to get the users socket information for the payload.", iColorCode::RED);

                break;

            case WsBinaryStreams::CONTINUE:

                ColorCode::colorCode('CONTINUE FRAME: ' . print_r($data, true), iColorCode::MAGENTA);

                break;

            case WsBinaryStreams::BINARY:

                ColorCode::colorCode('BINARY FRAME:', iColorCode::BACKGROUND_MAGENTA);

                $data = print_r(bindec($data['payload']), true);

                ColorCode::colorCode("bindec = ($data).", iColorCode::YELLOW);

                break;

            default:

                ColorCode::colorCode('ERROR DECODING OPCODE', iColorCode::RED);

                $data = print_r($data, true);

                ColorCode::colorCode("Unknown opcode given to websocket. Ignoring ($data).", iColorCode::YELLOW);

                break;


        }

    }


    public static function garbageCollect(): void
    {

        $convert_memory_get_usage = static fn($size) => round($size / (1024 ** ($i = floor(log($size, 1024)))), 2) . ' ' . array('b', 'kb', 'mb', 'gb', 'tb', 'pb')[$i];

        $int_cycles = gc_collect_cycles();

        ColorCode::colorCode(print_r(gc_status(), true));

        $used_memory = $convert_memory_get_usage(memory_get_usage());

        $allocated_memory = $convert_memory_get_usage(memory_get_usage(true));

        ColorCode::colorCode("WebSocket has allocated ($allocated_memory) and used ($used_memory). PHP GC Collected ($int_cycles) CYCLES.", iColorCode::YELLOW);

    }

    public static function startTcpServer(
        bool   $ssl = false,
        string $cert = '',
        string $pass = '',
        string $host = '',
        int    &$port = 0,
    )
    {

        $config = CarbonPHP::$configuration;

        if ($ssl) {

            $context = stream_context_create([
                'ssl' => [
                    //'cafile' => __DIR__ . DIRECTORY_SEPARATOR . 'certificate.crt',
                    'local_cert' => $cert,
                    'passphrase' => $pass,
                    //'peer_fingerprint' => PEER_FINGERPRINT,
                    'CURLOPT_VERBOSE' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => false,
                    'allow_self_signed' => false,
                    'verify_depth' => 5,
                    //'CN_match'      => 'carbonphp.com',
                    'disable_compression' => true,
                    'SNI_enabled' => true,
                    'ciphers' => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:ECDHE-RSA-RC4-SHA:ECDHE-ECDSA-RC4-SHA:AES128:AES256:RC4-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!3DES:!MD5:!PSK'
                ],
            ]);

            $protocol = 'ssl';

        } else {

            $context = stream_context_create();

            $protocol = 'tcp';

        }

        ColorCode::colorCode("Stream Context Created");

        $port = $config['SOCKET']['PORT'] ??= $port;

        $host = $config['SOCKET']['HOST'] ??= $host;


        $portIsBoundError = static fn() => ColorCode::colorCode("The port ($port) is already in use. Please select a different port. You can use flag (--autoAssignOpenPorts true) to search for an empty port.", iColorCode::RED);

        do {

            ColorCode::colorCode("Attempting to bind port ($port)", iColorCode::YELLOW);

            try {

                $socket = stream_socket_server("$protocol://$host:$port", $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

            } catch (Throwable $e) {

                ThrowableHandler::generateLogAndExit($e);

            }

            if (!$socket) {

                if ($errorNumber === 0 || $errorString === 'Address already in use') {

                    if (WebSocket::$autoAssignOpenPorts) {

                        continue;

                    }

                    $portIsBoundError();

                    exit(18);

                }

                ColorCode::colorCode("$errorNumber) $errorString file://" . __FILE__ . ':' . __LINE__, iColorCode::RED);

                exit(19);

            }

        } while ($socket === false && 1000 + $config['SOCKET']['PORT'] > $port++);

        if (!is_resource($socket)) {

            ColorCode::colorCode("The socket creation failed unexpectedly.", 'red');

            die(1);

        }

        ColorCode::colorCode("TCP server started on $protocol://" . ($config['SOCKET']['HOST'] ?? $host) . ':' . ($config['SOCKET']['PORT'] ?? $port));

        return $socket;

    }

    public static function closeConnection(&$connection): void
    {

        ColorCode::colorCode("Close Connection Requested.", iColorCode::MAGENTA);

        $resourceToDelete = array_search($connection, WebSocket::$allConnectedResources, true);

        foreach (WebSocket::$userConnectionRelationships as $key => &$information) {

            if ($information->userPipe === $connection) {

                ColorCode::colorCode("User connected named pipe closed before socket.", iColorCode::RED);

                exit(1);

            }

            if ($information->userSocket === $connection) {

                ColorCode::colorCode('Socket Closed.', iColorCode::RED);

                $pipeToDeleteKey = array_search($information->userPipe, WebSocket::$allConnectedResources, true);

                if (!is_resource($information->userPipe)) {

                    ColorCode::colorCode('Pipe not resource. This is unexpected.', iColorCode::RED);

                } else {

                    @fclose($information->userPipe);

                }

                unset(WebSocket::$allConnectedResources[$pipeToDeleteKey], WebSocket::$userConnectionRelationships[$key]); // unset 1

                break;

            }

        }
        unset($information);

        $key = array_search($connection, WebSocket::$userResourceConnections, true);

        @fclose($connection);

        unset(WebSocket::$userResourceConnections[$key],                                     // unset 2, 3 (2 values 3 pointers)
            WebSocket::$allConnectedResources[$resourceToDelete]);

        $connection = null;

    }

    public static function acceptNewConnection($connection): bool
    {

        if ($connection !== WebSocket::$socket) { // accepting a new connection?
            return false;
        }

        $timeout = ini_get('default_socket_timeout');

        $connection = stream_socket_accept($connection,
            $timeout,
            $peerName);

        // this is where we get the ip and port of the user
        if ($connection === false) {

            ColorCode::colorCode("Stream Socket Accept Failed", 'red');

            return true;

        }

        $headers = [];

        if (WsBinaryStreams::handshake($connection, $headers) === false) {  // attempt to send ws(s) validation

            @fclose($connection);

            ColorCode::colorCode("Stream Handshake Failed", 'red');

            return true;

        }

        if (!array_key_exists('Cookie', $headers)) {

            ColorCode::colorCode("No 'Cookie' Header was sent to WebSocket server! Closing connection", iColorCode::RED);

            WebSocket::sendToResource('', $connection, WsBinaryStreams::CLOSE);

            @fclose($connection);

            return true;

        }

        ColorCode::colorCode("Handshake Successful", iColorCode::BLUE);

        // use regex to split peer name
        preg_match('/^(.+):(\d+)$/', $peerName, $matches);

        [, $ip, $port] = $matches;

        ColorCode::colorCode("Connected ($ip:$port)", iColorCode::CYAN);

        WebSocket::$validateUserCallback ??= static function (string $ip, int $port): int {

            ColorCode::colorCode("User Session Validation Function Not Set. Using Default for ($ip:$port).", iColorCode::YELLOW);

            new Session(CarbonPHP::$configuration[CarbonPHP::SESSION][CarbonPHP::REMOTE] ?? false); // session start

            $userId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? session_id();

            ColorCode::colorCode("User ($userId). \$validateUserCallback complete.", iColorCode::YELLOW);

            return $userId;

        };

        // this can effectively be run async
        $userValidationCallback = self::userSessionValidation($ip, (int)$port);

        $userId = $userValidationCallback();

        ColorCode::colorCode("User Session Verified ($userId)", iColorCode::CYAN);

        $uniqueUserWsSession = "$userId:$ip:$port";

        // this is a really expensive foreach
        WebSocket::$userConnectionRelationships[$uniqueUserWsSession] ??= null;

        $pipeRelation = &WebSocket::$userConnectionRelationships[$uniqueUserWsSession];

        if ($pipeRelation !== null) {

            ColorCode::colorCode("Found Multiple Session Connections For :: $uniqueUserWsSession", iColorCode::BLUE);

            if ($pipeRelation->userSocket === $connection) {

                ColorCode::colorCode('User Socket Connecting Through Same Resource :)');

            } else {

                ColorCode::colorCode('User Socket Connecting Through Different Resource. Closing Old Connection.', iColorCode::BLUE);

                $userToUpdateKey = array_search($pipeRelation->userSocket, WebSocket::$userResourceConnections, true);

                $userToUpdateKey2 = array_search($pipeRelation->userSocket, WebSocket::$allConnectedResources, true);

                @fclose($pipeRelation->userSocket);  // todo - allow multiple browsers

                unset(WebSocket::$userResourceConnections[$userToUpdateKey], WebSocket::$allConnectedResources[$userToUpdateKey2]);

                $pipeRelation->userSocket = &$connection; // todo - this would 'potentially' overwrite a connection

            }

            if (!is_resource($pipeRelation->userPipe)) {

                ColorCode::colorCode("The pipe went barron, this should never happen. Attempting to refresh.", iColorCode::RED);

                @fclose($pipeRelation->userPipe);

                $pipe = Pipe::createFifoChannel($uniqueUserWsSession);     // other users can notify us to update our application through this file

                if ($pipe === false) {

                    ColorCode::colorCode("Pipe failed to be created.", iColorCode::RED);

                    return true;

                }

                ColorCode::colorCode("Pipe refreshed.", iColorCode::BLUE);

                $pipeRelation->userPipe = &$pipe;

            }

            return true;

        }

        // other users can notify us to update our application through this file
        $pipe = Pipe::createFifoChannel($uniqueUserWsSession);

        if ($pipe === false) {

            ColorCode::colorCode("Pipe failed to be created.", iColorCode::RED);

            return true;

        }

        // add our new connection to the master list after we've checked for duplicates
        WebSocket::$allConnectedResources[] = &$connection;

        WebSocket::$allConnectedResources[] = &$pipe;

        WebSocket::$userResourceConnections[] = &$connection;

        WebSocket::$userConnectionRelationships[$uniqueUserWsSession] = new WsUserConnectionRelationship(
            userId: $userId,
            userPipe: $pipe,
            userSocket: $connection,
            sessionId: $userId,
            headers: $headers,
            port: $port,
            ip: $ip
        );

        return true;

    }


    public static function userSessionValidation(string $ip, int $port): Closure
    {

        $ary = array();

        // @link https://stackoverflow.com/questions/24590818/what-is-the-difference-between-ipproto-ip-and-ipproto-raw
        if (socket_create_pair(AF_UNIX, SOCK_STREAM, IPPROTO_IP, $ary) === false) {

            throw new Error("socket_create_pair() failed. Reason: " . socket_strerror(socket_last_error()));

        }

        ColorCode::colorCode("socket_create_pair() successful.", iColorCode::BACKGROUND_CYAN);

        $pid = pcntl_fork();

        if ($pid === -1) {

            throw new Error('Could not fork Process.');

        }

        ColorCode::colorCode("pcntl_fork() successful.", iColorCode::BACKGROUND_CYAN);

        if ($pid !== 0) {

            socket_close($ary[0]);

            // lets return this so we can get benefits from this
            return static function () use (&$ary): int {

                ColorCode::colorCode("socket_read() waiting for the user id to be returned!", iColorCode::BACKGROUND_CYAN);

                /** @noinspection PhpRedundantOptionalArgumentInspection */
                $userId = socket_read($ary[1], 1024, PHP_BINARY_READ);

                if (false === $userId) {

                    ColorCode::colorCode("socket_read() returned false. Will use userId = 0; Reason: " . socket_strerror(socket_last_error($ary[1])), iColorCode::BACKGROUND_RED);

                    $userId = 0;

                } else {

                    ColorCode::colorCode("\$userId = socket_read(..); // received ($userId)", iColorCode::BACKGROUND_CYAN);

                }

                socket_close($ary[1]);

                ColorCode::colorCode("socket_close() successful.", iColorCode::BACKGROUND_CYAN);

                return $userId;

            };

        }

        /*child*/
        socket_close($ary[1]);

        $validateUserCallback = WebSocket::$validateUserCallback;

        if (false === is_callable($validateUserCallback)) {

            throw new PrivateAlert('The user validation callback is not callable. This is a critical mistake.');

        }

        ColorCode::colorCode("About to run user provided validateUserCallback().", iColorCode::BACKGROUND_CYAN);

        $id = $validateUserCallback($ip, $port);

        $id = (string)$id;

        ColorCode::colorCode("User provided validateUserCallback() complete. \$userId = ($id)", iColorCode::BACKGROUND_CYAN);

        if (socket_write($ary[0], $id, strlen($id)) === false) {

            ColorCode::colorCode("socket_write() failed. <$id> Reason: " . socket_strerror(socket_last_error($ary[0])), iColorCode::BACKGROUND_RED);

        }

        ColorCode::colorCode("socket_write() successful. Successfully passed the Session information back to the parent process.", iColorCode::BACKGROUND_CYAN);

        socket_close($ary[0]);

        exit(0);

    }


}