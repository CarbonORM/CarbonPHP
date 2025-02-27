<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

class WsUserConnection
{
    public function __construct(
        public string $userId,
        public mixed $userPipe,
        public mixed $userSocket,
        public string $sessionId,
        public array $headers,
        public string $port,
        public string $ip,
    ) {
    }
}
