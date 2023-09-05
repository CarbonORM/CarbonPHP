<?php

namespace CarbonPHP\WebSocket;



use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Route;
use CarbonPHP\Session;
use Throwable;

abstract class WsFileStreams extends WsBinaryStreams
{

    public static array $userResourceConnections = [];

    public static function sendToResource(string $data, &$connection, int $opCode = self::TEXT): bool
    {

        try {

            $socket = socket_import_stream($connection);

            $data = self::encode($data, $opCode);

            $length = strlen($data);

            return $length !== socket_send($socket, $data, $length, 0);

        } catch (Throwable) {

            return false;

        }

    }

    public static function sendToAllExternalResources(string $data, $opCode = self::TEXT): void
    {
        foreach (self::$userResourceConnections as $resourceConnection) {

            if (is_resource($resourceConnection)) {

                self::sendToResource($data, $resourceConnection, $opCode);

            } else {

                unset(self::$userResourceConnections[$resourceConnection]);

            }

        }

    }

    /**
     * @param string $uri
     * @param array $information
     * @param resource $connection
     */
    public static function forkStartApplication(string $uri, array $information, &$connection): void
    {

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

            self::sendToResource($buff, $connection);

            exit(0); // 0 = success

        }

    }

    public function readFromFifo(&$fifoFile, array $information)
    {

        $data = fread($fifoFile, $bytes = 1024);

        if (false === $data) {

            ColorCode::colorCode("\nFailed to preform read for fifo file\n", 'red');

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