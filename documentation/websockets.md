# The Websocket Protocol

Websockets offer realtime persistent communication. CarbonPHP offers a realtime WebSocket server that can be Overridden to support your needs. View [how to setup CarbonPHP at CarbonORM.dev](http://localhost:3000/#/documentation/CarbonPHP/).

You can start a websocket server with default configurations using the command. Be sure CarbonPHP is invoked in your `index.php` file.

```bash
php index.php websocket
```

## Configuration and Command Line Arguments 

Refer to [CarbonORM.dev](CarbonORM.dev](http://localhost:3000/#/documentation/CarbonPHP/) for full setup configuration.

```php
public static function configuration(): array
{
    return [ 
        // reduced for documentation
        CarbonPHP::SOCKET => [
            CarbonPHP::PORT => 8888,
            CarbonPHP::DEV => true,
            CarbonPHP::SSL => [
                CarbonPHP::KEY => '',
                CarbonPHP::CERT => ''
            ]
        ],
    ];
}
```

The SSL cofiguration option currently are in BETA. We reccomend using your server to Upgrade WebSocket requests using a Proxy directive. This will automatically enable SSL (WebSocket Secure, WSS) if you have it actively configured through Apache.

## Apache Configuration

Add the following directive in your `.htaccess` file. This [Apache configuration file](https://httpd.apache.org/docs/2.4/howto/htaccess.html) should be in the root of your web directory. In the example below 

```htaccess
RewriteCond %{HTTP:Connection} Upgrade [NC]
RewriteCond %{HTTP:Upgrade} websocket [NC]
RewriteRule ^/?(.*) ws://127.0.0.1:8888/$1  [P,L,E=noconntimeout:1,E=noabort:1]
```

## Customization and Overrides

The PHP code snippet you provided defines two static properties in a class, both of which are intended to hold callbacks – functions that can be called at a later point in the execution of the program. Let's break down each property:

1) public static mixed $startApplicationCallback = null;
  - **Type:** callable|null
  - **Description:** This property is intended to store a callback function that is likely to be executed to start or initialize an application. The callable type hint suggests that this property should either be a function, a method reference, or a closure. The mixed keyword in PHP indicates that the property can hold multiple (but not all) types, which includes callable. The property is initialized to null, implying that it doesn't have a callback set by default.
  - **Usage Context:** Typically, such a callback might be used in scenarios where the class needs to perform some initialization routines that are not known at the time of the class's implementation and are meant to be defined by the user of the class.
  ```php
  WebSocket::$startApplicationCallback ??= static fn($uri) => startApplication($uri);
  ```
  - **Runtime Context:** The callable provided will be executed in a seperate process and its return will be automatically returned to the main server. This allows developers to create session context and run other functions which may cause isses if run multiple times throughout the life of a single php process. The method below will fork the server process and execute the application callable. Any echo/print output, `stdout`, will be send directly to the users browser. 
  ```php
      /**
     * @param string $uri
     * @param WsUserConnectionRelationship $information
     * @param resource $connection
     */
    public static function forkStartApplication(string $uri, WsUserConnectionRelationship $information, &$connection): void
    {
      try {

          switch (pcntl_fork()) {
              case -1:
                  throw new PrivateAlert('Failed to fork in (' . __METHOD__ . ')');
              case 1:
                  return;
              case 0:
          }

          ob_start();

          if (!isset(CarbonPHP::$user_ip)) {

              CarbonPHP::$user_ip = $information->ip;

          }

          if (session_status() === PHP_SESSION_NONE) {

              Session::resume($information->sessionId);

          }

          $_SERVER['REQUEST_URI'] = $uri;

          WebSocket::$startApplicationCallback ??= static fn($uri) => startApplication($uri);

          $startApplication = WebSocket::$startApplicationCallback;

          if (false === is_callable($startApplication)) {

              throw new PrivateAlert('The startApplication callback is not callable.');

          }

          $startApplication($uri, $information);

          $buff = trim(ob_get_clean());

          if (empty($buff)) {

              if (Route::$matched) {

                  $buff = 'A route was matched but did not signal any output to stdout.';

              } else {

                  $buff = "No route was matched in the Application for startApplication(\$uri = $uri). Please remember 
                  your sockets application is in cached state and must be restart the socket server to see changes.";

              }

          }

          self::sendToResource($buff, $connection);

      } catch (Throwable $e) {

          ThrowableHandler::generateLog($e);

      }

      exit(0); // 0 = success

  }
  ```
2) public static mixed $validateUserCallback = null;
  - **Type:** callable|null
  - **Description:** Similar to the first property, this one is expected to store a callback function, but specifically for the purpose of validating a user. This could be used in contexts where user authentication or validation is required, and the specific logic of validation is to be provided at runtime or by the class's consumer. As with the previous property, it's set to null by default, indicating no validation logic is provided initially.
  - **Usage Context:** An example use case could be in a web application where different instances of the application might have different criteria for validating users (e.g., checking credentials against a database, validating tokens, etc.), and this property allows injecting that specific logic into the class.
  ```php
  WebSocket::$validateUserCallback ??= static function (string $ip, int $port): int {

      ColorCode::colorCode("User Session Validation Function Not Set. Using Default for ($ip:$port).", iColorCode::YELLOW);

      new Session(CarbonPHP::$configuration[CarbonPHP::SESSION][CarbonPHP::REMOTE] ?? false); // session start

      $userId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? session_id();

      ColorCode::colorCode("User ($userId). \$validateUserCallback complete.", iColorCode::YELLOW);

      return $userId;

  };
  ```
  - **Runtime Context:** The callable provided will be executed in a seperate process and its return will be automatically returned to the main server. This allows developers to create session context and run other functions which may cause isses if run multiple times throughout the life of a single php process. 
    ```php
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
    ```


In both cases, the use of public static implies these properties are meant to be accessible and modifiable from outside the class without needing an instance of the class. This approach is common in designs where a class provides a service or functionality that can be customized via callbacks.

## Source
Please refer to the [actual source here](https://github.com/CarbonORM/CarbonPHP/blob/lts/carbonphp/programs/WebSocket.php) for the most up-to-date code.

```php
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
```
