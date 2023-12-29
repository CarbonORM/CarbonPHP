<?php

namespace CarbonPHP\Classes\Programs;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\Abstracts\websocket\WsFileStreams;
use CarbonPHP\Abstracts\websocket\WsSignals;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Classes\Exceptions\PrivateAlert;
use CarbonPHP\Classes\Session;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use JetBrains\PhpStorm\NoReturn;
use Throwable;
use function is_resource;

/**
 *
 * Todo - the minimize we need to check the user id option
 *
 * Class WebSocket
 *
 * Context::
 *
 *  This was three files, now one
 *
 *  The constructor is the common ground
 *
 *  Sessions are only paused in single threaded selects (process one signal at a time until forkresumeresumeable)
 *
 *
 * @package CarbonPHP\Programs
 *
 * @todo - implement https://hpbn.co/websocket/
 * @link https://hpbn.co/websocket/
 * @link https://tools.ietf.org/id/draft-abarth-thewebsocketprotocol-00.html
 */
class WebSocket extends WsFileStreams implements iCommand
{


    public static bool $verifyIP = true;


    protected static array $applicationConfiguration = [];


    public static function description(): string
    {
        return 'Start a WebSocket Server. This is a single or multi threaded server capable.';
    }

    public function __construct($config)
    {

        [$config, $argv] = $config;

        self::$applicationConfiguration = $config;

        $config['SOCKET'] ??= [];

        ColorCode::colorCode("\nConstructing Socket Class");

        CarbonPHP::$socket = true;

        error_reporting(E_ALL);

        set_time_limit(0);

        ob_implicit_flush();

        $_SERVER['SERVER_PORT'] = self::$port;

        WsSignals::signalHandler(static fn() => ColorCode::colorCode('Implement garbage collection', iColorCode::YELLOW));

        $argc = count($argv);

        for ($i = 0; $i < $argc; $i++) {

            switch ($argv[$i]) {
                default:
                case '-help':

                    ColorCode::colorCode("\tYou da bomb :)\n", 'blue');

                    $this->usage();

                    exit(1);


                case '-dontVerifyIP':

                    self::$verifyIP = false;

                    break;

            }

        }

        if (self::$ssl) {

            $context = stream_context_create([
                'ssl' => [
                    //'cafile' => __DIR__ . DIRECTORY_SEPARATOR . 'certificate.crt',
                    'local_cert' => self::$cert,
                    'passphrase' => self::$pass,
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

        ColorCode::colorCode("\nStream Context Created\n");

        $socket = stream_socket_server("$protocol://" . ($config['SOCKET']['HOST'] ?? self::$host) . ':' . ($config['SOCKET']['PORT'] ?? self::$port), $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

        if (!$socket) {

            ColorCode::colorCode("\n$errorString ($errorNumber)\n", iColorCode::RED);

            die(1);

        }

        if (!is_resource($socket)) {

            ColorCode::colorCode("\nThe socket creation failed unexpectedly.\n", 'red');

            die(1);

        }

        self::$socket = $socket;

        ColorCode::colorCode("\nStream Socket Server Created on " . self::$host . '::' . self::$port . "\n\nws" . (self::$ssl ? 's' : '') . '://' . self::$host . ':' . self::$port . '/ ');

    }

    public function run(array $argv): void
    {

        ColorCode::colorCode('Handle All Resource Stream Selects On Single Thread');

        self::handleAllResourceStreamSelectOnSingleThread();

    }


    public static function handleAllResourceStreamSelectOnSingleThread(): never
    {

        $allConnectedResources = [self::$socket];

        $WebsocketToPipeRelations = [];

        $closeConnection = static function (&$connection) use (&$allConnectedResources, &$WebsocketToPipeRelations) {

            ColorCode::colorCode("\nClose Connection Requested.\n", iColorCode::MAGENTA);

            $resourceToDelete = array_search($connection, $allConnectedResources, false);

            foreach ($WebsocketToPipeRelations as $key => &$information) {

                if ($information['user_pipe'] === $connection) {

                    ColorCode::colorCode("\nUser connected named pipe closed before socket.\n", 'red');

                    exit(1);

                }

                if ($information['user_socket'] === $connection) {

                    ColorCode::colorCode('Socket Closed.', iColorCode::RED);

                    $pipeToDeleteKey = array_search($information['user_pipe'], $allConnectedResources, true);

                    if (!is_resource($information['user_pipe'])) {

                        ColorCode::colorCode('Pipe not resource. This is unexpected.', 'red');

                    } else {

                        @fclose($information['user_pipe']);

                    }

                    unset($allConnectedResources[$pipeToDeleteKey], $WebsocketToPipeRelations[$key]); // unset 1

                    break;

                }

            }
            unset($information);

            $key = array_search($connection, self::$userResourceConnections, true);

            @fclose($connection);

            unset(self::$userResourceConnections[$key],                                     // unset 2, 3 (2 values 3 pointers)
                $allConnectedResources[$resourceToDelete]);

            $connection = null;

        };

        // help manage and kill zombie children
        $serverPID = getmypid();

        $convert_memory_get_usage = static fn($size) => round($size / (1024 ** ($i = floor(log($size, 1024)))), 2) . ' ' . array('b', 'kb', 'mb', 'gb', 'tb', 'pb')[$i];

        while (true) {

            try {

                if ($serverPID !== getmypid()) {

                    throw new PrivateAlert('Failed stop child process from returning to the main loop. This is a critical mistake.');

                }

                $read = $allConnectedResources;

                $number = stream_select($read, $write, $error, 10);

                if ($number === 0) {

                    $int_cycles = gc_collect_cycles();

                    ColorCode::colorCode(print_r(gc_status(), true));

                    $used_memory = $convert_memory_get_usage(memory_get_usage());

                    $allocated_memory = $convert_memory_get_usage(memory_get_usage(true));

                    ColorCode::colorCode("WebSocket has allocated ($allocated_memory) and used ($used_memory). PHP GC Collected ($int_cycles) CYCLES; MANUAL GC Cleaned (x) Resources.", iColorCode::YELLOW);

                    continue;

                }

                ColorCode::colorCode("\n$number, stream(s) are requesting to be processed.\n");

                foreach ($read as $connection) {

                    if ($connection === self::$socket) { // accepting a new connection?

                        $timeout = ini_get('default_socket_timeout');

                        $connection = stream_socket_accept($connection,
                            $timeout,
                            $peerName);

                        // this is where we get the ip and port of the user
                        if ($connection === false) {

                            ColorCode::colorCode("\nStream Socket Accept Failed\n", 'red');

                            continue;

                        }

                        $headers = [];

                        if (self::handshake($connection, $headers) === false) {  // attempt to send ws(s) validation

                            @fclose($connection);

                            ColorCode::colorCode("\nStream Handshake Failed\n", 'red');

                            continue;

                        }

                        ColorCode::colorCode("\nHandshake Successful\n", 'blue');

                        [$ip, $port] = explode(':', $peerName);

                        ColorCode::colorCode("\nConnected $ip:$port\n", 'blue');

                        if (self::$verifyIP && !Session::verifySocket($ip)) {

                            ColorCode::colorCode("\nFailed to verify socket ip for session.\n", 'red');

                            continue;

                        }

                        $session = new Session($config[CarbonPHP::SESSION][CarbonPHP::REMOTE] ?? false); // session start

                        $session_id = $session::$session_id;

                        ColorCode::colorCode("\nSession Verified $session_id\n", iColorCode::CYAN);

                        $session::writeCloseClean();  // we have to kill the static user id so were thread safe

                        unset($session);

                        $_SESSION = [];

                        ColorCode::colorCode("\nSession Closed Until Next Request $session_id\n", 'blue');

                        // this is a really expensive foreach
                        $WebsocketToPipeRelations[$session_id] ??= null;

                        $pipeRelation = &$WebsocketToPipeRelations[$session_id];

                        if ($pipeRelation !== null) {

                            ColorCode::colorCode("\nFound Multiple Session Connections For :: $session_id\n", 'blue');

                            if ($pipeRelation['user_socket'] === $connection) {

                                ColorCode::colorCode('User Socket Connecting Through Same Resource :)');

                            } else {

                                ColorCode::colorCode('User Socket Connecting Through Different Resource. Closing Old Connection.', 'blue');

                                $userToUpdateKey = array_search($pipeRelation['user_socket'], self::$userResourceConnections, true);

                                $userToUpdateKey2 = array_search($pipeRelation['user_socket'], $allConnectedResources, true);

                                @fclose($pipeRelation['user_socket']);  // todo - allow multiple browsers

                                unset(self::$userResourceConnections[$userToUpdateKey], $allConnectedResources[$userToUpdateKey2]);

                                $pipeRelation['user_socket'] = &$connection; // todo - this would 'potentially' overwrite a connection

                            }

                            if (!is_resource($pipeRelation['user_pipe'])) {

                                ColorCode::colorCode("\nThe pipe went barron, this should never happen. Attempting to refresh.\n", 'red');

                                @fclose($pipeRelation['user_pipe']);

                                $pipe = Pipe::named(CarbonPHP::$app_root . 'temp/' . $session_id . '.fifo');     // other users can notify us to update our application through this file

                                if ($pipe === false) {

                                    ColorCode::colorCode("\nPipe failed to be created.\n", 'red');

                                    continue;

                                }

                                ColorCode::colorCode("\nPipe refreshed.\n", 'blue');

                                $pipeRelation['user_pipe'] = &$pipe;

                            }

                            continue;

                        }

                        $pipe = Pipe::named(CarbonPHP::$app_root . 'temp/' . $session_id . '.fifo');     // other users can notify us to update our application through this file

                        if ($pipe === false) {

                            ColorCode::colorCode("\nPipe failed to be created.\n", 'red');

                            continue;

                        }

                        ColorCode::colorCode("\nPipe created.\n", 'blue');

                        // add our new connection to the master list after we've checked for duplicates
                        $allConnectedResources[] = &$connection;

                        $allConnectedResources[] = &$pipe;

                        self::$userResourceConnections[] = &$connection;

                        $WebsocketToPipeRelations[$session_id] = [
                            'user_pipe' => &$pipe,
                            'user_socket' => &$connection,
                            'session_id' => $session_id,
                            'port' => $port,
                            'ip' => $ip
                        ];

                        continue;

                    }

                    $data = self::decode($connection);

                    switch ($data['opcode']) {

                        case self::CLOSE:

                            $closeConnection($connection);

                            break;

                        case self::PING :

                            self::sendToResource('', $connection, self::PONG);

                            break;

                        case self::TEXT:

                            // we have to find the relation regardless,
                            foreach ($WebsocketToPipeRelations as $information) {

                                if ($information['user_pipe'] === $connection) {

                                    self::readFromFifo($connection, $information);

                                    break;

                                }

                                if ($information['user_socket'] === $connection) {

                                    if (is_string($data['payload'])) {

                                        self::forkStartApplication($data['payload'], $information, $connection);

                                    }

                                    break;

                                }

                            }

                            break;

                        default:

                            ColorCode::colorCode("\nUnknown opcode given to websocket. Ignoring.\n", 'yellow');

                            break;

                    }

                }

            } catch (Throwable $e) {

                ThrowableHandler::generateLogAndExit($e);

            }

        }

    }


    public function cleanUp(): void
    {

    }

    #[NoReturn] public function usage(): void // todo - update
    {
        print <<<END
\n
\t           Parameters are optional
\t           Order does not matter.
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage::
\t  php index.php WebSocketPHP 

\t       -help                        - this dialogue      
\n
END;
        exit(1);

    }

}

