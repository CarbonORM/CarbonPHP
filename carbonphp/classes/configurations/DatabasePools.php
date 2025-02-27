<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

class DatabasePools
{
    private(set) array $pools;

    public function __construct(
        DatabasePool ...$restDatabaseConfigurations,
    ) {
        $this->pools = $restDatabaseConfigurations;
    }
}
