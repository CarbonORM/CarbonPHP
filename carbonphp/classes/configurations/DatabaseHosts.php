<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

class DatabaseHosts
{
    private(set) array $hosts;

    public function __construct(
        DatabaseHost ...$restDatabaseConfigurations,
    ) {
        $this->hosts = $restDatabaseConfigurations;
    }
}
