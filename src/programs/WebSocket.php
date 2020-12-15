<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Helpers\Pipe;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Request;
use CarbonPHP\Route;
use CarbonPHP\Session;
use function in_array;
use function is_resource;
use function ord;

/**
 *
 *  Todo - the minimize we need to check the user id option
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
 */
class WebSocket extends Request implements iCommand
{
    use Background;

    /**
     * @var $socket resource
     */
    private $socket;


    public static bool $isWebsocketD = false;
    public static bool $minimiseResources = false;
    public static bool $verifyIP = true;

    private const TEXT = 0x1;
    private const BINARY = 0x2;
    private const CLOSE = 0x8;
    private const PING = 0x9;
    private const PONG = 0xa;

    # https://stackoverflow.com/questions/4812686/closing-websocket-correctly-html5-javascript
    private const CLOSE_DATA_FRAME = 0x88; // todo - maybe, it ends up being equivalent to close.?

    public static int $port = 8888;
    public static bool $ssl = false;

    public static string $host = 'localhost';
    public static string $cert = '/cert.pem';
    public static string $pass = 'Smokey';


    public static array $userResourceConnections = [];

    /**
     * @var $user resource
     */
    public $user;

    /**
     * @var string $user_ip has to be set runtime
     */
    public string $user_ip;

    /**
     * @var string $user_port has to be set runtime
     */
    public string $user_port;
    /**
     * @var bool $singleProcess - this reduces opened files on the system, but at the cost of individual user not
     */
    public static bool $singleProcess = false;
    /**
     * @var string $user_id is a uuid so '' is impossible, this knowledge speeds up comparison
     */
    public static string $user_id = '';

    public function __construct($config)
    {
        [$config, $argv] = $config;

        $config['SOCKET'] ??= [];

        self::colorCode("\nConstructing Socket Class");

        CarbonPHP::$socket = true;

        error_reporting(E_ALL);

        set_time_limit(0);

        ob_implicit_flush();

        $_SERVER['SERVER_PORT'] = self::$port;

        $signal = static function ($signal) {
            self::colorCode("\nSignal Caught, :: $signal\n", 'blue');
            exit(1);
        };

        if (extension_loaded('pcntl')) {
            self::colorCode("\nExtension pcntl loaded, Catching Signals\n", 'blue');

            # https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
            pcntl_signal(SIGTERM, $signal); // Termination ('kill' was called')
            pcntl_signal(SIGHUP, $signal);  // Terminal log-out
            pcntl_signal(SIGINT, $signal);  // Interrupted ( Ctrl-C is pressed)
        } else {
            self::colorCode("\nCarbonPHP Websockets require the PCNTL library. See CarbonPHP.com for more Documentation\n", 'red');
            exit(1);
        }

        $C6 = CarbonPHP::CARBON_ROOT === CarbonPHP::$app_root . 'src' . DS;

        $argc = count($argv);

        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                default:
                case '-help':
                    self::colorCode("\tYou da bomb :)\n", 'blue');
                    $this->usage();
                    exit(1);
                case '-minimiseResources':
                    self::$minimiseResources = true;
                    break;
                case '-dontVerifyIP':
                    self::$verifyIP = false;
                    break;
                case '-go':
                    self::$isWebsocketD = true;
                    return;
                case '-goCommand':
                    $this->WebSocketDStartCommand();
                    exit(0);
                case '-phpCommand':
                    $this->recommendedProductionStartCommand();
                    exit(0);
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

        self::colorCode("\nStream Context Created\n");

        $socket = stream_socket_server("$protocol://" . ($config['SOCKET']['HOST'] ?? self::$host) . ':' . ($config['SOCKET']['PORT'] ?? self::$port), $errorNumber, $errorString, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

        if (!$socket) {
            self::colorCode("\n$errorString ($errorNumber)\n", 'red');
            die(1);
        }

        if (!is_resource($socket)) {
            self::colorCode("\nThe socket creation failed unexpectedly.\n", 'red');
            die(1);
        }

        $this->socket = $socket;

        self::colorCode("\nStream Socket Server Created on " . self::$host . '::' .self::$port. "\n\nws" . (self::$ssl ? 's' : '') . '://'.self::$host.':'.self::$port.'/ ');

        if (!self::$minimiseResources) {
            $this->ServerAcceptNewConnections();      // parent thread will always be in this loop
        }
    }

    public function run(array $argv): void
    {
        if (self::$isWebsocketD) {
            self::colorCode('Starting WebSocketD Method. The GO routine.');
            $this->WebSocketD();
            return;
        }

        if (self::$minimiseResources) {
            self::colorCode('Handle All Resource Stream Selects On Single Thread');
            $this->handleAllResourceStreamSelectOnSingleThread();
            return;
        }

        self::colorCode('Single Thread Per User');

        self::$user_id = session_id();  // set who's logged in so when we fork we can reset

        Session::pause();           // Close the current session

        Database::setDatabase();    // This will clear the connection

        // php doesnt do tail call optimization, recursion doesn't work
        self::colorCode("\nChild Thread Healthy\n");

        $this->serveSingleUserOnSingleThread();   // child thread will fall here and not stop until socket users disconnects
    }

    public function sendToResource(string $data, &$connection, $opCode = self::TEXT): bool
    {
        return 0 <= @fwrite($connection, self::encode($data, $opCode));
    }

    public static function sendToAllExternalResources(string $data, $opCode = self::TEXT)
    {
        foreach (self::$userResourceConnections as $resourceConnection) {
            if (is_resource($resourceConnection)) {
                @fwrite($resourceConnection, self::encode($data, $opCode));
            } else {
                unset(self::$userResourceConnections[$resourceConnection]);
            }
        }
    }

    public function recommendedProductionStartCommand()
    {
        print 'Run this command in your shell ::' . PHP_EOL . '    nohup php index.php websocket &' . PHP_EOL;
    }

    public function WebSocketDStartCommand($argv): void
    {
        $CMD = 'websocketd --port=' . ($PHP['SOCKET']['PORT'] ?? 8888) . ' ' .
            (($this->CONFIG['SOCKET']['DEV'] ?? false) ? '--devconsole ' : '') .
            (($this->CONFIG['SOCKET']['SSL'] ?? false) ? "--ssl --sslkey='{$argv['SOCKET']['SSL']['KEY']}' --sslcert='{$argv['SOCKET']['SSL']['CERT']}' " : ' ') .
            'php index.php WebSocket -go';

        print 'pid == ' . $this->background($CMD, CarbonPHP::$app_root . 'websocketd_log.txt');
        print "\n\n\tWebsocket started in the background, done!\n\n";
        //`$CMD`;
    }

    public function WebSocketD()
    {
        // its linus bc pnctl but i always feel better using DS
        $fifoFile = \CarbonPHP\helpers\Pipe::named(CarbonPHP::$app_root . 'data' . DS . 'sessions' . DS . $_SESSION['id'] . '.fifo');     // other users can notify us to update our application through this file

        $stdin = fopen('php://stdin', 'b');

        Session::pause();           // Close the current session
        Database::setDatabase();    // This will clear the connection

        while (true) {
            $miss = 0;
            $handshake = 0;
            $readers = array($fifoFile, $stdin);

            /** @noinspection OnlyWritesOnParameterInspection */
            if (($stream = stream_select($readers, $writers, $except, 0, 15)) === false):
                print "A stream select error occurred\n" and die;
            else :
                foreach ($readers as $input => $fd) {

                    if ($fd === $stdin) {

                        $string = $this->set(fgets($stdin))->noHTML(true);      // I think were going to make this a search function

                        if ($string === 'exit') {
                            print "Application closed socket \n";
                            die;
                        }

                        /** @noinspection PhpUndefinedFunctionInspection */
                        if (!empty($string) && pcntl_fork() === 0) {     // Fork

                            Session::resume();      // resume session

                            $_SERVER['REQUEST_URI'] = $string = trim(preg_replace('/\s+/', ' ', $string));

                            print "startApplication('$string')\n";

                            startApplication($string);

                            exit(1);
                        }

                        $handshake++;

                    } elseif ($fd === $fifoFile) {

                        $data = fread($fifoFile, $bytes = 1024);

                        $data = explode(PHP_EOL, $data);

                        foreach ($data as $id => $uri) {

                            /** @noinspection PhpUndefinedFunctionInspection */
                            if (!empty($uri) && pcntl_fork() === 0) {

                                Session::resume();

                                $_SERVER['REQUEST_URI'] = $string = trim(preg_replace('/\s+/', ' ', $uri));

                                print "Update startApplication('$uri')\n";

                                startApplication($uri);

                                exit(1);  // but if we decide to change that...  (we decided to change that!)
                            }
                        }
                        $handshake++;
                    } else {
                        print "Hits => $handshake";
                        if ($handshake !== 0):
                            $handshake = 0;
                            $miss = 1;
                        elseif ($miss === 10):
                            exit(1);
                        else: $miss++;
                            print "Misses => $miss\n";
                        endif;
                    }
                }
                sleep(1);
            endif;
        }
    }


    /**
     * @param string $uri
     * @param array $information
     * @param resource $connection
     */
    public function forkStartApplication(string $uri, array $information, &$connection)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        if (pcntl_fork() === 0) {
            ob_start();
            if (!isset(CarbonPHP::$user_ip)) {
                CarbonPHP::$user_ip = $information['ip'];
            }
            if (session_status() === PHP_SESSION_NONE) {
                Session::resume($information['session_id']);
            }
            $_SERVER['REQUEST_URI'] = $uri;
            startApplication($uri);
            $buff = trim(ob_get_clean());
            if (empty($buff)) {
                if (Route::$matched) {
                    $buff = 'A route was matched but did not signal any output to stdout.';
                } else {
                    $buff = "No route was matched in the Application for startApplication(\$uri = $uri). Please remember 
                    your sockets application is in cached state and must be restart the socket server to see changes.";
                }
            }
            $this->sendToResource($buff, $connection);
            exit(0); // 0 = success
        }
    }

    public function readFromFifo(&$fifoFile, array $information)
    {
        $data = fread($fifoFile, $bytes = 1024);

        if (false === $data) {
            self::colorCode("\nFailed to preform read for fifo file\n", 'red');
            return;
        }

        $data = explode(PHP_EOL, $data);

        foreach ($data as $id => $uri) {
            if (empty($uri)) {
                continue;
            }
            $this->forkStartApplication(trim($uri), $information, $fifoFile);
        }
    }

    public function handleAllResourceStreamSelectOnSingleThread()
    {
        static $manual_garbage_collection = 0;

        $allConnectedResources = [$this->socket];

        $WebsocketToPipeRelations = [];

        $closeConnection = static function ($connection) use (&$allConnectedResources, &$WebsocketToPipeRelations) {
            self::colorCode("\nClose Connection Requested.\n", 'red');

            $resourceToDelete = array_search($connection, $allConnectedResources, false);

            foreach ($WebsocketToPipeRelations as $key => $information) {
                if ($information['user_pipe'] === $connection) {
                    self::colorCode("\nUser connected named pipe closed before socket.\n", 'red');
                    exit(1);
                    break;
                }
                if ($information['user_socket'] === $connection) {
                    self::colorCode('Socket Closed.', 'red');
                    $pipeToDeleteKey = array_search($information['user_pipe'], $allConnectedResources, true);

                    if (!is_resource($WebsocketToPipeRelations[$key]['user_pipe'])) {
                        self::colorCode('Pipe not resource. This is unexpected.', 'red');
                    } else {
                        @fclose($WebsocketToPipeRelations[$key]['user_pipe']);
                    }
                    unset($allConnectedResources[$pipeToDeleteKey], $WebsocketToPipeRelations[$key]); // unset 1
                    break;
                }
            }

            $key = array_search($connection, self::$userResourceConnections, true);

            @fclose($connection);
            unset(self::$userResourceConnections[$key],                                     // unset 2, 3 (2 values 3 pointers)
                $allConnectedResources[$resourceToDelete]);
            $connection = null;
        };

        while (true) {

            $read = $allConnectedResources;

            $number = stream_select($read, $write, $error, 5);

            if ($number === 0) {
                $manual_garbage_collection++;
                $count = count(self::$userResourceConnections);
                self::colorCode($count . ' user(s) connected. ' . ($count > 0 ? " (gc:$manual_garbage_collection)" : ''), 'cyan');
                if ($manual_garbage_collection > 11) {

                }
                continue;
            }
            $manual_garbage_collection = 0;

            self::colorCode("\n$number, stream(s) are requesting to be processed.\n");

            foreach ($read as $connection) {

                if ($connection === $this->socket) { // accepting a new connection?

                    $connection = stream_socket_accept($connection,
                        ini_get('default_socket_timeout'),
                        $peerName);

                    // this is where we get the ip and port of the user
                    if ($connection === false) {
                        self::colorCode("\nStream Socket Accept Failed\n", 'red');
                        continue;
                    }

                    $headers = [];

                    if ($this->handshake($connection, $headers) === false) {  // attempt to send ws(s) validation
                        @fclose($connection);
                        self::colorCode("\nStream Handshake Failed\n", 'red');
                        continue;
                    }

                    self::colorCode("\nHandshake Successful\n", 'blue');

                    [$ip, $port] = explode(':', $peerName);

                    self::colorCode("\nConnected $ip:$port\n", 'blue');

                    if (self::$verifyIP && !Session::verifySocket($ip)) {
                        self::colorCode("\nFailed to verify socket ip for session.\n", 'red');
                        continue;
                    }

                    $session = new Session($ip, $config['SESSION']['REMOTE'] ?? false); // session start

                    $session_id = $session::$session_id;

                    self::colorCode("\nSession Verified $session_id\n", 'blue');

                    $session::writeCloseClean();  // we have to kill the static user id so were thread safe

                    unset($session);

                    $_SESSION = [];

                    self::colorCode("\nSession Closed Until Next Request $session_id\n", 'blue');

                    $sessionContinued = 0;
                    // this is a really expensive foreach

                    $pipeRelation = &$WebsocketToPipeRelations[$session_id] ?? null;

                    if ($pipeRelation !== null) {

                        self::colorCode("\nFound Multiple Session Connections For :: $session_id\n", 'blue');
                        if ($pipeRelation['user_socket'] === $connection) {
                            self::colorCode('User Socket Connecting Through Same Resource :)');
                        } else {
                            self::colorCode('User Socket Connecting Through Different Resource. Closing Old Connection.', 'blue');
                            $userToUpdateKey = array_search($pipeRelation['user_socket'], self::$userResourceConnections, true);
                            $userToUpdateKey2 = array_search($pipeRelation['user_socket'], $allConnectedResources, true);
                            @fclose($pipeRelation['user_socket']);  // todo - allow multiple browsers
                            unset(self::$userResourceConnections[$userToUpdateKey], $allConnectedResources[$userToUpdateKey2]);
                            $pipeRelation['user_socket'] = &$connection; // todo - this would 'potentially' overwrite a connection
                        }

                        if (!is_resource($pipeRelation['user_pipe'])) {
                            self::colorCode("\nThe pipe went barron, this should never happen. Attempting to refresh.\n", 'red');

                            @fclose($pipeRelation['user_pipe']);

                            $pipe = Pipe::named(CarbonPHP::$app_root . 'temp/' . $session_id . '.fifo');     // other users can notify us to update our application through this file

                            if ($pipe === false) {
                                self::colorCode("\nPipe failed to be created.\n", 'red');
                                continue;
                            }

                            self::colorCode("\nPipe refreshed.\n", 'blue');

                            $pipeRelation['user_pipe'] = &$pipe;
                        }
                        continue;
                    }


                    $pipe = Pipe::named(CarbonPHP::$app_root . 'temp/' . $session_id . '.fifo');     // other users can notify us to update our application through this file

                    if ($pipe === false) {
                        self::colorCode("\nPipe failed to be created.\n", 'red');
                        continue;
                    }

                    self::colorCode("\nPipe created.\n", 'blue');

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

                $data = $this->decode($connection);

                switch ($data['opcode']) {

                    case self::CLOSE:
                        $closeConnection($connection);
                        break;

                    case self::PING :
                        $this->sendToResource('', $connection, self::PONG);
                        break;

                    case self::TEXT:
                        // we have to find the relation regardless,
                        foreach ($WebsocketToPipeRelations as $key => $information) {
                            if ($information['user_pipe'] === $connection) {
                                $this->readFromFifo($connection, $information);
                                break;
                            }
                            if ($information['user_socket'] === $connection) {
                                if (is_string($data['payload'])) {
                                    $this->forkStartApplication($data['payload'], $information, $connection);
                                }
                                break;
                            }
                        }
                        break;

                    default:
                        self::colorCode("\nUnknown opcode given to websocket. Ignoring.\n", 'yellow');
                        break;
                }
            }
        }
    }

    public function ServerAcceptNewConnections()
    {
        static $gc = 0;

        $sock_fd = [$this->socket];

        while (true) {                         // should
            $clientList = $sock_fd;

            $number = stream_select($clientList, $write, $error, 5);

            if ($number === 0) {
                self::colorCode('.', 'green');
                continue;
            }

            // Forking can return the pid for parent, 0 for child, and -1 for error
            // we need to pop
            $connection = array_pop($clientList); // This is the users file descriptor linked to a unique process

            if (($connection = stream_socket_accept($connection, ini_get('default_socket_timeout'), $peerName)) === false) {
                if (++$gc === 10) {
                    $gc = 0;
                    foreach (self::$userResourceConnections as $resource) {
                        if (!is_resource($resource)) {
                            unset(self::$userResourceConnections[$resource]);
                        }
                    }
                }
                continue;
            }

            if (!$this->handshake($connection)) {              // attempt to send wss validation
                @fclose($connection);                          // close any accepted connection if failurephp /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/Data/Vendors/richardtmiles/carbonphp/Structure/Server.php /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/ /Users/richardmiles/Documents/WebServer/BiologyAnswers.org/Application/Config/Config.php
                continue;
            }

            self::$userResourceConnections[] = &$connection;

            /** @noinspection PhpUndefinedFunctionInspection */
            if (($pid = pcntl_fork()) > 0) {                     // if parent restart looking for incoming connections
                continue;
            }
            if ($pid < 0) {
                ErrorCatcher::generateCallTrace();             // log errors
            }

            [$this->user_ip, $this->user_port] = explode(':', $peerName);

            CarbonPHP::$user_ip = $this->user_ip;

            $this->user = &$connection;

            return;
        }
    }

    public function serveSingleUserOnSingleThread(): void
    {
        if (!is_resource($this->user)) {
            self::colorCode("\nFailed to handle user connection\n", 'red');
            die(1);
        }

        if (Session::verifySocket($this->user_ip) === false) {
            exit(1);
        }

        self::colorCode("\nIP address verified\n", 'blue');

        self::colorCode("\nSession Started\n", 'blue');

        $session_id = session_id();

        self::colorCode("\nSession ID Captured $session_id\n", 'blue');

        $fifoFile = Pipe::named(CarbonPHP::$app_root . 'temp' . DS . session_id() . '.fifo');     // other users can notify us to update our application through this file

        if (false === $fifoFile) {
            self::colorCode("\nFailed to create named pipe\n", 'red');
            die(1);
        }

        self::colorCode("\nNamed Pipe Created\n", 'blue');

        Session::pause();           // Close the current session

        Database::setDatabase(null);    // This will clear the connection

        $read = [$this->user, $fifoFile]; // todo I remember this not working when removed.

        while (true) {
            $read = [$this->user, $fifoFile];
            $mod_fd = stream_select($read, $_w, $_e, 5);  // returns number of file descriptors modified
            if ($mod_fd === 0) {
                self::colorCode('..', 'blue');
                continue;
            }
            self::colorCode($mod_fd . ', Stream is waiting to be processed', 'blue');

            foreach ($read as $connection) {
                if ($connection === $fifoFile) { // send a uri from another user to us in the browser
                    $this->readFromFifo($fifoFile, $session_id);
                    continue;
                }

                $data = $this->decode($connection);  // request information via wss... stupid slow lol, dont do this

                switch ($data['opcode']) {
                    case self::CLOSE:
                        self::colorCode('CLOSE', 'blue');
                        @fclose($fifoFile);
                        @fclose($this->user);
                        exit(1);                // kill this child process
                        break;
                    case self::PING:
                        self::colorCode('PING', 'blue');
                        $this->sendToResource('', $connection, self::PONG);
                        break;
                    case self::TEXT:
                        self::colorCode('TEXT', 'blue');
                        if (!is_string($data['payload'])) {
                            self::colorCode('Stream did not send a string', 'blue');
                        } else if ($data['payload'] = $this->set($data['payload'])->noHTML(true)) {
                            self::colorCode('Payload :: ' . $data['payload'], 'blue');
                            $this->forkStartApplication($data['payload'], [
                                'session_id' => Session::$session_id,
                                'ip' => $this->user_ip
                            ], $connection);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }

    public function handshake($socket, array &$headers = []): bool
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
        if (!$socket || !is_resource($socket)) {
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
        $handle = ord($read);
        $out['fin'] = ($handle >> 7) & 0x1;
        $out['rsv1'] = ($handle >> 6) & 0x1;
        $out['rsv2'] = ($handle >> 5) & 0x1;
        $out['rsv3'] = ($handle >> 4) & 0x1;
        $out['opcode'] = $handle & 0xf;
        if (!in_array($out['opcode'], [
            self::TEXT,
            self::BINARY,
            self::CLOSE,
            self::PING,
            self::PONG
        ], true)) {
            return [
                'opcode' => '',
                'payload' => '',
                'error' => 'unknown opcode (1003)'
            ];
        }
        $handle = ord(fread($socket, 1));
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
        $out['payload'] = json_decode($message, true);
        return $out;
    }

    public function cleanUp(): void
    {

    }

    public function usage(): void // todo - update
    {
        print <<<END
\n
\t           Parameters are optional
\t           Order does not matter.
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage::
\t  php index.php WebSocketPHP 

\t       -help                        - this dialogue      

\t       -singleProcess               - use a single process for all websocket connections

\t       -echoInternalContent         - WIP : if this is set, content sent in the pipe will be redirected to the browser 
\t                                         instead of directly executed with startApplication. This is typically faster.
\n
END;
        exit(1);

    }

}
