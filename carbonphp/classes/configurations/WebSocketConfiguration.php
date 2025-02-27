<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

class WebSocketConfiguration
{
    public function __construct(
        public int $port = 8080,
        public bool $dev = true,
        public string $sslKey = '',
        public string $sslCert = '',
    ) {
    }
}
