<?php

namespace CarbonPHP\WebSocket;

class WsUserConnectionRelationship
{

    /**
     * @param string $userId
     * @param resource $userPipe
     * @param resource $userSocket
     * @param string $sessionId
     * @param array $headers
     * @param string $port
     * @param string $ip
     */
    public function __construct(
        public string $userId,
        public mixed $userPipe,
        public mixed $userSocket,
        public string $sessionId,
        public array $headers,
        public string $port,
        public string $ip,
    )
    {
    }

}