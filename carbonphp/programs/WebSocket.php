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

        ColorCode::colorCode("Constructing Socket Class");

        CarbonPHP::$socket = true;

        ini_set('memory_limit', '4G');

        error_reporting(E_ALL);

        set_time_limit(0);

        ob_implicit_flush();

        $_SERVER['SERVER_PORT'] = &self::$port;

        WsSignals::signalHandler(static fn() => WsConnection::garbageCollect());

        $argc = count($argv);

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $argc; $i++) {

            switch ($argv[$i]) {
                default:
                    ColorCode::colorCode("Unknown Argument: {$argv[$i]}", iColorCode::RED);
                case '-h':
                case '-help':
                case '--help':

                    ColorCode::colorCode("WebSocket Server Help (" . implode(' ', $argv) . ')', iColorCode::CYAN);

                    $this->usage(); // this exits : never

                case '--autoAssignAnyOpenPort':

                    self::$autoAssignOpenPorts = true;

                    break;

                case '--dontVerifyIP':

                    self::$verifyIP = false;

                    break;

            }

        }

        self::$socket = WsConnection::startTcpServer(self::$ssl, self::$cert, self::$pass, self::$host, self::$port);

        self::updateHtaccessWebSocketPort(self::$port);

        ColorCode::colorCode("Stream Socket Server Created on ws" . (self::$ssl ? 's' : '') . '://' . self::$host . ':' . self::$port . '/ ');

    }

    public static function updateHtaccessWebSocketPort(int $port, string $path = 'carbonorm/websocket'): void
    {
        $path = trim($path, '/');

        $startWebSocketHtaccessComment = '# START CarbonORM WebSockets';

        // This is the proxy for the WebSocket - we want apache to forward all requests to the WebSocket server
        $connectionProxy = <<<HTACCESS
            $startWebSocketHtaccessComment
            <IfModule mod_rewrite.c>
                RewriteEngine On
                RewriteCond %{HTTP:Connection} Upgrade [NC]
                RewriteCond %{HTTP:Upgrade} websocket [NC]
                RewriteRule ^/$path/?(.*) ws://127.0.0.1:$port/$path/$1  [P,L,E=noconntimeout:1,E=noabort:1]
            </IfModule>
            # END CarbonORM WebSockets
            HTACCESS;

        // Attempt to open the .htaccess file in read-write mode
        $htaccessFile = ABSPATH . '/.htaccess';

        $fileResource = fopen($htaccessFile, 'cb+');

        if ($fileResource === false) {
            ColorCode::colorCode('Failed to open .htaccess file. Please check permissions.', iColorCode::RED);
            exit(1);
        }

        // Acquire an exclusive lock
        if (!flock($fileResource, LOCK_EX)) {
            ColorCode::colorCode('Failed to lock .htaccess file for writing.', iColorCode::RED);
            fclose($fileResource); // Always release the resource
            exit(1);
        }

        // Read the current contents of the file
        $htaccess = stream_get_contents($fileResource);

        // Check if the connection proxy exists or needs to be updated
        if (str_contains($htaccess, $connectionProxy)) {
            ColorCode::colorCode('The .htaccess file already contains the WebSocket proxy. No changes were made.');
        } elseif (str_contains($htaccess, $startWebSocketHtaccessComment)) {
            ColorCode::colorCode('The .htaccess file already contains the WebSocket proxy. Updating to new port.', iColorCode::CYAN);
            $htaccess = preg_replace('#' . preg_quote($startWebSocketHtaccessComment, '#') . '.*?#s', $connectionProxy, $htaccess);
        } else {
            $htaccess = $connectionProxy . PHP_EOL . $htaccess;
        }

        // Move the file pointer to the beginning of the file and truncate the file to zero length
        ftruncate($fileResource, 0);
        rewind($fileResource);

        // Write the modified contents back to the file
        if (fwrite($fileResource, $htaccess) === false) {
            ColorCode::colorCode('Failed to write to .htaccess file. Please check permissions.', iColorCode::RED);
            flock($fileResource, LOCK_UN); // Release the lock
            fclose($fileResource); // Always release the resource
            exit(1);
        }

        // Release the lock and close the file
        flock($fileResource, LOCK_UN);
        fclose($fileResource);
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

