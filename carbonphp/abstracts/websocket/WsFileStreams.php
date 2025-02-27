<?php

declare(strict_types=1);

namespace CarbonPHP\Abstracts\WebSocket;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Pipe;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Abstracts\Classes\Configurations\WsUserConnection;
use CarbonPHP\Abstracts\Classes\Session;
use CarbonPHP\Abstracts\Classes\ThrowableHandler;
use CarbonPHP\Abstracts\Interfaces\iColorCode;
use CarbonPHP\Abstracts\Classes\Programs\WebSocket;
use CarbonPHP\Abstracts\Route;
use CarbonPHP\Abstracts\Throwables\PrivateAlert;

abstract class WsFileStreams extends WsBinaryStreams
{
    public const string FIFO_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

    /**
     * @var resource[]
     */
    public static array $userResourceConnections = [];

    public static function sendToResource(string $data, $connection, int $opCode = self::TEXT): bool
    {
        try {
            $data = self::encode($data, $opCode);

            $length = strlen($data);

            if (get_resource_type($connection) === 'stream') {
                $socket = socket_import_stream($connection);
            } else {
                $socket = $connection;
            }

            if (false === $socket) {
                throw new PrivateAlert('The function socket_import_stream failed', iColorCode::RED);
            }

            $sentLength = socket_send($socket, $data, $length, 0);

            if (false === $sentLength) {
                throw new PrivateAlert('The function socket_send failed', iColorCode::RED);
            }

            if ($length !== $sentLength) {
                throw new PrivateAlert("The function socket_send did not send the entire message ($length !== $sentLength).", iColorCode::RED);
            }

            return true;
        } catch (\Throwable $e) {
            ThrowableHandler::generateLog($e, true);

            return false;
        }
    }

    private static function sendToWebSocketGlobalPipe(string $data): void
    {
        Pipe::sendToFifoChannel('global_pipe', $data);
    }

    public static function sendToAllWebsSocketConnections(string $data, $opCode = self::TEXT): void
    {
        if (null === WebSocket::$socket) {
            self::sendToWebSocketGlobalPipe($data);

            return;
        }

        if (CarbonPHP::$verbose) {
            ColorCode::colorCode("Sending to all websocket connections ($data)", iColorCode::BLUE);
        }

        foreach (self::$userResourceConnections as $key => $resourceConnection) {
            if (is_resource($resourceConnection)) {
                self::sendToResource($data, $resourceConnection, $opCode);
            } else {
                unset(self::$userResourceConnections[$key]);
            }
        }
    }

    /**
     * @param string $uri
     * @param WsUserConnection $information
     * @param resource $connection
     */
    public static function forkStartApplication(string $uri, WsUserConnection $information, &$connection): void
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
        } catch (\Throwable $e) {
            ThrowableHandler::generateLog($e);
        }

        exit(0); // 0 = success
    }

    public static function readFromFifo(&$fifoFile, callable $handlePipeInfo): void
    {
        $data = '';

        // @link https://stackoverflow.com/questions/20652194/websocket-frame-size-limitation
        // the limitation is not the frame size, but php's memory limit. PHP's max int is not even 2^63
        while (!feof($fifoFile)) {
            $fread = fgetc($fifoFile);

            if (false === $fread) {
                break;
            }

            $data .= $fread;
        }

        $data = explode(Pipe::$fifoDelimiter, $data);

        foreach ($data as $lineItem) {
            if (empty($lineItem)) {
                continue;
            }

            $handlePipeInfo($lineItem);
        }
    }
}
