<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Programs;


use CarbonPHP\Abstracts\Classes\Configurations\WsUserConnection;
use CarbonPHP\Abstracts\Classes\Database;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\Abstracts\WebSocket\WsConnection;
use CarbonPHP\Abstracts\WebSocket\WsFileStreams;
use CarbonPHP\Abstracts\WebSocket\WsSignals;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Enums\ThrowableReportDisplay;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Throwables\PrivateAlert;

/**
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
 * @todo - implement https://hpbn.co/websocket/
 *
 * @see https://hpbn.co/websocket/
 * @see https://tools.ietf.org/id/draft-abarth-thewebsocketprotocol-00.html
 */
class WebSocket extends WsFileStreams implements iCommand
{
    public static bool $verifyIP = true;

    public static int $streamSelectSeconds = 10;

    /**
     * @var callable|null
     */
    public static mixed $startApplicationCallback = null;

    /**
     * @var callable|null
     */
    public static mixed $validateUserCallback = null;

    protected static array $applicationConfiguration = [];

    public static array $allConnectedResources = [];

    /**
     * @var WsUserConnection[]
     */
    public static array $userConnectionRelationships = [];

    /**
     * @var resource|null
     */
    public static mixed $globalPipeFifo = null;

    public static bool $autoAssignOpenPorts = false;

    public static function description(): string
    {
        return 'Start a WebSocket Server. This is a single or multi threaded server capable.';
    }

    public function __construct($config)
    {
        [$config, $argv] = $config;

        self::$applicationConfiguration = $config;

        ThrowableHandler::$storeReport = true;

        ThrowableHandler::$throwableReportDisplay = ThrowableReportDisplay::CLI_MINIMAL;

        $config['SOCKET'] ??= [];

        ColorCode::colorCode('Constructing Socket Class');

        CarbonPHP::$socket = true;

        ini_set('memory_limit', '4G');

        error_reporting(E_ALL);

        set_time_limit(0);

        ob_implicit_flush();

        $_SERVER['SERVER_PORT'] = &self::$port;

        WsSignals::signalHandler(static fn () => WsConnection::garbageCollect());

        $argc = count($argv);

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $argc; ++$i) {
            switch ($argv[$i]) {
                default:
                    ColorCode::colorCode("Unknown Argument: {$argv[$i]}", iColorCode::RED);
                    // no break
                case '-h':
                case '-help':
                case '--help':

                    ColorCode::colorCode('WebSocket Server Help ('.implode(' ', $argv).')', iColorCode::CYAN);

                    $this->usage(); // this exits : never

                    // no break
                case '--autoAssignAnyOpenPort':

                    self::$autoAssignOpenPorts = true;

                    break;

                case '--dontVerifyIP':

                    self::$verifyIP = false;

                    break;
            }
        }

        self::$socket = WsConnection::startTcpServer(self::$ssl, self::$cert, self::$pass, self::$host, self::$port);

        ColorCode::colorCode('Stream Socket Server Created on ws'.(self::$ssl ? 's' : '').'://'.self::$host.':'.self::$port.'/ ');
    }

    public static function handleSingleUserConnections(): never
    {
        // get all headers has a polyfill in our function.php
        $headers = getallheaders();

        // Now we start the buffer and write to it using standard io (print, echo, print_r,..) and encode it as one block until we flush it.
        $flush = self::outputBufferWebSocketEncoder();

        self::handshake(\STDOUT, $headers);

        ColorCode::colorCode('Handshake complete, starting WebSocket server.');

        echo posix_getpid().PHP_EOL;

        $flush();

        // Here you can handle the WebSocket upgrade logic
        /** @noinspection PhpUndefinedFunctionInspection  - Proposed RFC */
        $websocket = apache_connection_stream();

        stream_set_blocking($websocket, false);

        if (!\is_resource($websocket)) {
            throw new \Error('INPUT is not a valid resource');
        }

        $fifoPath = self::FIFO_DIRECTORY.session_id().'.fifo';

        $myFifo = Pipe::named($fifoPath);

        $loop = 0;

        while (true) {
            ColorCode::colorCode("Loop: $loop");

            try {
                ++$loop;

                echo "Loop: $loop\n";

                $flush();

                sleep(1);

                if (!\is_resource($websocket)) {
                    throw new \Error('STDIN is not a valid resource');
                }

                $flush();

                $read = [$websocket, $myFifo];

                // 3 should be set to a reasonably high value for your application, lower is better for debugging
                $number = stream_select($read, $write, $error, 3);

                if ($number === 0) {
                    ColorCode::colorCode("No streams are requesting to be processed. (loop: $loop )", iColorCode::CYAN);

                    continue;
                }

                ColorCode::colorCode("$number, stream(s) are requesting to be processed.");

                foreach ($read as $connection) {
                    switch ($connection) {
                        case $websocket:
                            $data = self::decode($connection);
                            switch ($data['opcode']) {
                                default:
                                case self::BINARY:
                                case self::CLOSE:
                                    exit(0);

                                case self::PING :
                                    @fwrite($connection, self::encode('', self::PONG));
                                    break;

                                case self::TEXT:
                                    $PrintPayload = print_r($data['payload'], true);

                                    ColorCode::colorCode("The following was received ($PrintPayload)");

                                    echo $data['payload']['name'].', has sent :: '.$data['payload']['message'].PHP_EOL;

                                    $flush();

                                    if (!is_string($data)) {
                                        $data = json_encode($data, JSON_THROW_ON_ERROR).PHP_EOL;
                                        echo $data;
                                        $flush();
                                    }

                                    $fifoFiles = glob(self::FIFO_DIRECTORY.'*.fifo');

                                    foreach ($fifoFiles as $fifoPath) {
                                        // Process each .fifo file

                                        if (str_ends_with($fifoPath, session_id().'.fifo')) {
                                            // no need to update our own fifo with info we already have
                                            continue;
                                        }

                                        // Open the FIFO for writing
                                        $fifo = fopen($fifoPath, 'wb');

                                        if ($fifo === false) {
                                            echo 'Failed to open FIFO for writing';
                                            exit(2);
                                        }

                                        @fwrite($fifo, $data);

                                        @fclose($fifo);
                                    }

                                    $flush();

                                    break;
                            }

                            break;
                        case $myFifo:
                            // Read from the FIFO until the buffer is empty
                            $data = fread($myFifo, 4096); // Read up to 4096 bytes at a time
                            echo $data;
                            $flush();
                            break;
                        default:
                            print 'Unknown read connection!';
                            exit(1);
                    }
                }
            } catch (\Throwable $e) {
                ColorCode::colorCode(print_r($e, true), iColorCode::BACKGROUND_RED);
            }
        }
    }

    public static function outputBufferWebSocketEncoder(): callable
    {
        // @note - https://www.php.net/manual/en/function.ob-get-level.php comments
        // my error handler is set to stop at 1, but here I believe clearing all is the only way.
        // Php may start with an output buffer enabled but we need to clear that to in oder to send real time data.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start(new class {
            public function __invoke($part, $flag): string
            {
                $flag_sent = match ($flag) {
                    PHP_OUTPUT_HANDLER_START => "PHP_OUTPUT_HANDLER_START ($flag)",
                    PHP_OUTPUT_HANDLER_CONT  => "PHP_OUTPUT_HANDLER_CONT ($flag)",
                    PHP_OUTPUT_HANDLER_END   => "PHP_OUTPUT_HANDLER_END ($flag)",
                    default                  => "Flag is not a constant ($flag)",
                };

                ColorCode::colorCode('('.__METHOD__.") Output Handler: $flag_sent");

                return WebSocket::encode($part.PHP_EOL);
            }

            public function __destruct()
            {
                ColorCode::colorCode('Ending WebSocket Encoding Buffer.');
            }
        });

        ob_implicit_flush();

        // these function calls are dynamic to whatever the current buffer is.
        return static function () {
            if (0 === ob_get_length()) {
                return;
            }

            // this will also remove the buffer, but IS NEEDED.
            // ob_flush will not guarantee the buffer runs through the ob_start callback.
            if (!ob_get_flush()) {
                throw new PrivateAlert('Failed to flush the output buffer.');
            }

            // my first thought was to return this method call, but it is not needed.
            self::outputBufferWebSocketEncoder();
        };
    }

    public function run(array $argv): void
    {
        ColorCode::colorCode('Handle All Resource Stream Selects On Single Thread');

        self::handleAllResourceStreamSelectOnSingleThread();
    }

    public static function handleAllResourceStreamSelectOnSingleThread(): never
    {
        static $cycles = 0;

        self::$globalPipeFifo = Pipe::createFifoChannel('global_pipe');

        self::$allConnectedResources = [self::$socket, self::$globalPipeFifo];

        // help manage and kill zombie children
        $serverPID = getmypid();

        if (session_status() === PHP_SESSION_ACTIVE) {
            ColorCode::colorCode('Session is active in the parent socket server process. This is not allowed. Closing.', iColorCode::RED);

            session_write_close();
        }

        while (true) {
            try {
                Database::close();

                Database::close(true);

                ++$cycles;

                if ($cycles === PHP_INT_MAX) {
                    ColorCode::colorCode('Cycles have reached PHP_INT_MAX = ('.PHP_INT_MAX.'). Resetting to 0.', iColorCode::RED);

                    $cycles = 0;
                }

                if (session_status() === PHP_SESSION_ACTIVE) {
                    throw new PrivateAlert('Session is active in the parent socket server process. This should not be possible.', iColorCode::BACKGROUND_RED);
                }

                if ($serverPID !== getmypid()) {
                    throw new PrivateAlert('Failed stop child process from returning to the main loop. This is a critical mistake.');
                }

                $read = self::$allConnectedResources;

                $number = stream_select($read, $write, $error, self::$streamSelectSeconds);

                if ($number === 0) {
                    if ($cycles % 100 === 0) {
                        ColorCode::colorCode('Running manual garbage collection and gathering server stats.');

                        WsConnection::garbageCollect();
                    } else {
                        ColorCode::colorCode("No streams are requesting to be processed. (cycle: $cycles; users: ".count(self::$userResourceConnections).') ', iColorCode::CYAN);
                    }

                    continue;
                }

                ColorCode::colorCode("$number, stream(s) are requesting to be processed.");

                foreach ($read as $connection) {
                    // this will check if
                    if (WsConnection::acceptNewConnection($connection)) {
                        continue;
                    }

                    if (self::$globalPipeFifo === $connection) {
                        ColorCode::colorCode('Reading from global pipe');

                        WsFileStreams::readFromFifo($connection, static fn (string $data) => self::sendToAllWebsSocketConnections($data));

                        continue; // foreach read as connection
                    }

                    // we have to find the relation regardless,
                    foreach (self::$userConnectionRelationships as $information) {
                        if ($information->userPipe === $connection) {
                            WsFileStreams::readFromFifo(
                                $connection,
                                static fn (string $data) => self::forkStartApplication($data, $information, $connection)
                            );

                            continue 2; // foreach read as connection
                        }

                        if ($information->userSocket === $connection) {
                            WsConnection::decodeWebsocket($connection);

                            continue 2; // foreach read as connection
                        }
                    }
                }
            } catch (\Throwable $e) {
                ThrowableHandler::generateLogAndExit($e);
            }
        }
    }

    public function cleanUp(): void
    {
    }

    public function usage(): never // todo - update
    {
        echo <<<END
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
