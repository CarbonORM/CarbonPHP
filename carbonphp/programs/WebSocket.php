<?php

namespace CarbonPHP\Programs;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Enums\ThrowableReportDisplay;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Session;
use CarbonPHP\WebSocket\WsConnection;
use CarbonPHP\WebSocket\WsFileStreams;
use CarbonPHP\WebSocket\WsSignals;
use CarbonPHP\WebSocket\WsUserConnectionRelationship;
use Closure;
use Error;
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
     * @var WsUserConnectionRelationship[]
     */
    public static array $userConnectionRelationships = [];


    /**
     * @var resource|null
     */
    public static mixed $globalPipeFifo = null;


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

        ColorCode::colorCode("Constructing Socket Class");

        CarbonPHP::$socket = true;

        ini_set('memory_limit', '4G');

        error_reporting(E_ALL);

        set_time_limit(0);

        ob_implicit_flush();

        $_SERVER['SERVER_PORT'] = self::$port;

        WsSignals::signalHandler(static fn() => WsConnection::garbageCollect());

        $argc = count($argv);

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $argc; $i++) {

            switch ($argv[$i]) {
                default:
                case '-help':

                    ColorCode::colorCode("\tYou da bomb :)", 'blue');

                    $this->usage();

                case '-dontVerifyIP':

                    self::$verifyIP = false;

                    break;

            }

        }

        self::$socket = WsConnection::startTcpServer(self::$ssl, self::$cert, self::$pass, self::$host, self::$port);

        ColorCode::colorCode("Stream Socket Server Created on ws" . (self::$ssl ? 's' : '') . '://' . self::$host . ':' . self::$port . '/ ');

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

            ColorCode::colorCode("Session is active in the parent socket server process. This is not allowed. Closing.", iColorCode::RED);

            session_write_close();

        }

        while (true) {

            try {

                Database::close();

                Database::close(true);

                ++$cycles;

                if ($cycles === PHP_INT_MAX) {

                    ColorCode::colorCode('Cycles have reached PHP_INT_MAX = (' . PHP_INT_MAX . '). Resetting to 0.', iColorCode::RED);

                    $cycles = 0;

                }

                if (session_status() === PHP_SESSION_ACTIVE) {

                    throw new PrivateAlert("Session is active in the parent socket server process. This should not be possible.", iColorCode::BACKGROUND_RED);

                }

                if ($serverPID !== getmypid()) {

                    throw new PrivateAlert('Failed stop child process from returning to the main loop. This is a critical mistake.');

                }

                $read = self::$allConnectedResources;

                $number = stream_select($read, $write, $error, self::$streamSelectSeconds);

                if ($number === 0) {

                    if ($cycles % 100 === 0) {

                        ColorCode::colorCode("Running manual garbage collection and gathering server stats.");

                        WsConnection::garbageCollect();

                    } else {

                        ColorCode::colorCode("No streams are requesting to be processed. (cycle: $cycles; users: " . count(self::$userResourceConnections) . ") ", iColorCode::CYAN);

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

                        ColorCode::colorCode("Reading from global pipe");

                        WsFileStreams::readFromFifo($connection, static fn(string $data) => self::sendToAllWebsSocketConnections($data));

                        continue; // foreach read as connection

                    }

                    // we have to find the relation regardless,
                    foreach (self::$userConnectionRelationships as $information) {

                        if ($information->userPipe === $connection) {

                            WsFileStreams::readFromFifo($connection,
                                static fn(string $data) => self::forkStartApplication($data, $information, $connection));

                            continue 2; // foreach read as connection

                        }

                        if ($information->userSocket === $connection) {

                            WsConnection::decodeWebsocket($connection);

                            continue 2; // foreach read as connection

                        }

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

    public function usage(): never // todo - update
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

