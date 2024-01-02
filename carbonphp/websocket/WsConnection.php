<?php

namespace CarbonPHP\WebSocket;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Session;
use Closure;
use Error;

abstract class WsConnection
{

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
        int    $port = 0,
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

        $socket = stream_socket_server("$protocol://" . ($config['SOCKET']['HOST'] ?? $host) . ':' . ($config['SOCKET']['PORT'] ?? $port), $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

        if (!$socket) {

            ColorCode::colorCode("$errorString ($errorNumber)", iColorCode::RED);

            die(1);

        }

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

            if ($information['user_pipe'] === $connection) {

                ColorCode::colorCode("User connected named pipe closed before socket.", iColorCode::RED);

                exit(1);

            }

            if ($information['user_socket'] === $connection) {

                ColorCode::colorCode('Socket Closed.', iColorCode::RED);

                $pipeToDeleteKey = array_search($information['user_pipe'], WebSocket::$allConnectedResources, true);

                if (!is_resource($information['user_pipe'])) {

                    ColorCode::colorCode('Pipe not resource. This is unexpected.', iColorCode::RED);

                } else {

                    @fclose($information['user_pipe']);

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

        ColorCode::colorCode("Handshake Successful", iColorCode::BLUE);

        // use regex to split peer name
        preg_match('/^(.+):(\d+)$/', $peerName, $matches);

        [, $ip, $port] = $matches;

        ColorCode::colorCode("Connected ($ip:$port)", iColorCode::CYAN);

        session_write_close();

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

        // this is a really expensive foreach
        WebSocket::$userConnectionRelationships[$userId] ??= null;

        $pipeRelation = &WebSocket::$userConnectionRelationships[$userId];

        if ($pipeRelation !== null) {

            ColorCode::colorCode("Found Multiple Session Connections For :: $userId", iColorCode::BLUE);

            if ($pipeRelation['user_socket'] === $connection) {

                ColorCode::colorCode('User Socket Connecting Through Same Resource :)');

            } else {

                ColorCode::colorCode('User Socket Connecting Through Different Resource. Closing Old Connection.', 'blue');

                $userToUpdateKey = array_search($pipeRelation['user_socket'], WebSocket::$userResourceConnections, true);

                $userToUpdateKey2 = array_search($pipeRelation['user_socket'], WebSocket::$allConnectedResources, true);

                @fclose($pipeRelation['user_socket']);  // todo - allow multiple browsers

                unset(WebSocket::$userResourceConnections[$userToUpdateKey], WebSocket::$allConnectedResources[$userToUpdateKey2]);

                $pipeRelation['user_socket'] = &$connection; // todo - this would 'potentially' overwrite a connection

            }

            if (!is_resource($pipeRelation['user_pipe'])) {

                ColorCode::colorCode("The pipe went barron, this should never happen. Attempting to refresh.", iColorCode::RED);

                @fclose($pipeRelation['user_pipe']);

                $pipe = Pipe::named(CarbonPHP::$app_root . 'temp/' . $userId . '.fifo');     // other users can notify us to update our application through this file

                if ($pipe === false) {

                    ColorCode::colorCode("Pipe failed to be created.", 'red');

                    return true;

                }

                ColorCode::colorCode("Pipe refreshed.", 'blue');

                $pipeRelation['user_pipe'] = &$pipe;

            }

            return true;

        }

        $fifoPath = CarbonPHP::$app_root . 'temp/' . $userId . '.fifo';

        $pipe = Pipe::named($fifoPath);     // other users can notify us to update our application through this file

        if ($pipe === false) {

            ColorCode::colorCode("Pipe failed to be created.", 'red');

            return true;

        }

        ColorCode::colorCode("Pipe created. ($fifoPath)", 'blue');

        // add our new connection to the master list after we've checked for duplicates
        WebSocket::$allConnectedResources[] = &$connection;

        WebSocket::$allConnectedResources[] = &$pipe;

        WebSocket::$userResourceConnections[] = &$connection;

        WebSocket::$userConnectionRelationships[$userId] = new WsUserConnectionRelationship(
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