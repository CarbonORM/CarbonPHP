<?php

declare(strict_types=1);

namespace CarbonPHP\Traits;

use CarbonPHP\Classes\Configurations\DatabasePools;
use CarbonPHP\Classes\Configurations\WebSocketConfiguration;
use CarbonPHP\Classes\Configurations\ApplicationConfiguration;
use CarbonPHP\Classes\Configurations\CliConfiguration;
use CarbonPHP\Classes\Configurations\DatabaseHost;
use CarbonPHP\Classes\Configurations\DatabaseHosts;
use CarbonPHP\Classes\Configurations\DatabasePool;
use CarbonPHP\Classes\Configurations\ErrorConfiguration;
use CarbonPHP\Classes\Database;
use CarbonPHP\Classes\Programs\CLI;
use CarbonPHP\Classes\Session;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iConfiguration;
use CarbonPHP\Throwables\PublicAlert;
use Error;
use InvalidArgumentException;
use Throwable;

trait Configurations
{
    public ?ApplicationConfiguration $applicationConfiguration = null {
        set => self::checkInitialized($this->applicationConfiguration, $value, ApplicationConfiguration::class);
    }

    public CliConfiguration $cliConfiguration {
        set => self::checkInitialized($this->cliConfiguration, $value, CLI::class);
    }

    public ErrorConfiguration $errorConfiguration {
        set => self::checkInitialized($this->errorConfiguration, $value, Error::class);
    }

    public DatabasePools $databasePools {
        set {
            self::checkInitialized($this->databasePools, $value, Database::class . '[]');

            // Validate that $value is an array
            if (!is_array($value)) {
                throw new InvalidArgumentException('Expected an array.');
            }

            // Validate each item in the array
            foreach ($value as $item) {
                if (!$item instanceof Database) {
                    throw new InvalidArgumentException('Expected instance of ' . Database::class . ', got ' . (is_object($item) ? get_class($item) : gettype($item)));
                }
            }

            // Assign the validated array to the backing property
            $this->databasePools = $value;
        }
    }

    public Session $sessionConfiguration {
        set => self::checkInitialized($this->sessionConfiguration, $value, Session::class);
    }

    public WebSocketConfiguration $webSocketConfiguration {
        set => self::checkInitialized($this->webSocketConfiguration, $value, WebSocketConfiguration::class);
    }

    private static function checkInitialized(mixed $initialValue, $value, string $class): mixed
    {
        try {
            if (!isset($initialValue)) {
                $errorMessage = 'The ' . ApplicationConfiguration::class . ' may not be overridden after being set!';
                throw new PublicAlert($errorMessage);
            }

            return $value;
        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    public static function defaultConfigurations(): iConfiguration
    {
        return new class implements iConfiguration {
            use Configurations;

            public function __construct()
            {
                $this->applicationConfiguration = new ApplicationConfiguration();

                $this->cliConfiguration = new CliConfiguration(CLI::class);

                $pool = new DatabasePool(
                    writers: new DatabaseHosts(new DatabaseHost)
                );

                $this->databasePools =  new DatabasePools($pool);

                $this->errorConfiguration = new ErrorConfiguration(
                    pool: $pool
                );

                $this->sessionConfiguration = new Session($pool);

                $this->webSocketConfiguration = new WebSocketConfiguration();

            }
        };
    }
}
