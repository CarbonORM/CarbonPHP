<?php

declare(strict_types=1);

namespace CarbonPHP\Interfaces;

use CarbonPHP\Classes\Configurations\ApplicationConfiguration;
use CarbonPHP\Classes\Configurations\CliConfiguration;
use CarbonPHP\Classes\Configurations\DatabasePools;
use CarbonPHP\Classes\Configurations\ErrorConfiguration;
use CarbonPHP\Classes\Configurations\WebSocketConfiguration;
use CarbonPHP\Classes\Session;

interface iConfiguration
{
    public ApplicationConfiguration $applicationConfiguration {
        set;
    }

    public CliConfiguration $cliConfiguration {
        set;
    }

    public ErrorConfiguration $errorConfiguration {
        set;
    }

    public DatabasePools $databasePools {
        set;
    }

    public Session $sessionConfiguration {
        set;
    }

    public WebSocketConfiguration $webSocketConfiguration {
        set;
    }
}
