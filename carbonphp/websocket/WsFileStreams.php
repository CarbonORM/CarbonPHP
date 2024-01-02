<?php

namespace CarbonPHP\WebSocket;


use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Route;
use CarbonPHP\Session;
use Throwable;

abstract class WsFileStreams extends WsBinaryStreams
{

    /**
     * @var Resource[]
     */
    public static array $userResourceConnections = [];

    public static function sendToResource(string $data, &$connection, int $opCode = self::TEXT): bool
    {

        try {

            $socket = socket_import_stream($connection);

            if (false === $socket) {

                ColorCode::colorCode('The function socket_import_stream failed', iColorCode::RED);

                return false;

            }

            $data = self::encode($data, $opCode);

            $length = strlen($data);

            $sentLength = socket_send($socket, $data, $length, 0);

            if (false === $sentLength) {

                ColorCode::colorCode('The function socket_send failed', iColorCode::RED);

                return false;

            }

            if ($length !== $sentLength) {

                ColorCode::colorCode("The function socket_send did not send the entire message ($length !== $sentLength).", iColorCode::RED);

                return false;

            }

            return true;

        } catch (Throwable $e) {

            ColorCode::colorCode($e->getMessage(), iColorCode::RED);

            return false;

        }

    }

    public static function sendToAllExternalResources(string $data, $opCode = self::TEXT): void
    {
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

                Session::resume($information['session_id']);

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

    public static function readFromFifo(&$fifoFile, WsUserConnectionRelationship $information): void
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $data = fread($fifoFile, $bytes = 1024);

        if (false === $data) {

            ColorCode::colorCode("\nFailed to preform read for fifo file\n", iColorCode::RED);

            return;

        }

        $data = explode(PHP_EOL, $data);

        foreach ($data as $id => $uri) {

            if (empty($uri)) {

                continue;

            }

            self::forkStartApplication(trim($uri), $information, $fifoFile);

        }

    }

}