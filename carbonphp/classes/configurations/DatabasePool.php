<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

use CarbonPHP\Classes\Database;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iDatabasePool;
use CarbonPHP\Throwables\PublicAlert;
use PDO;
use PDOStatement;
use Throwable;

final class DatabasePool implements iDatabasePool
{
    public function __construct(
        public string         $name = 'carbonphp',
        public string         $user = 'root',
        public string         $password = 'password',
        public string         $diver = 'mysql',
        public ?int           $port = 3306,
        public string         $charset = 'utf8mb4',
        public string         $collation = 'utf8mb4_unicode_ci',
        public ?DatabaseHosts $writers = null,
        public ?DatabaseHosts $readers = null,
    )
    {
        try {
            if (null === $writers && null === $readers) {
                throw new PublicAlert('No hosts were passed to database information');
            }

            $hosts = $this->readers->hosts + $this->writers->hosts;

            foreach ($hosts as $host) {
                $host->databasePool = $this;
            }

        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    public function prepare(
        string $query,
        array  $options = []
    ): PDOStatement|false {
        $isWriteQuery = Database::isWriteQuery($query);
        $databaseHostConfiguration = $this->searchConfigurations($isWriteQuery);
        $PDO = $databaseHostConfiguration->database;
        return $PDO->prepare($query, $options);
    }

    public function searchConfigurations(bool $readerSearch = false): DatabaseHost
    {
        try {
            // Use readers or writers based on the flag
            $hosts = $readerSearch ? $this->readers->hosts : $this->writers->hosts;

            if (empty($hosts)) {
                throw new PublicAlert('No database ' . ($readerSearch ? 'reader' : 'writer') . ' has been configured.');
            }

            // Select a host based on process ID for even distribution
            return $hosts[getmypid() % count($hosts)];
        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    public function searchMostAvailableHost(bool $readerSearch): DatabaseHost
    {
        try {

            $hosts = $readerSearch ? $this->readers : $this->writers;

            if (empty($hosts)) {
                throw new PublicAlert('No database ' . ($readerSearch ? 'reader' : 'writer') . ' has been configured.');
            }

            // Check for the least busy host
            $selectedHost = null;
            $minConnections = PHP_INT_MAX;

            foreach ($hosts as $host) {
                try {
                    $pdo = $host->database;
                    $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result['Value'] < $minConnections) {
                        $minConnections = $result['Value'];
                        $selectedHost = $host;
                    }
                } catch (Throwable) {
                    continue; // Skip if connection fails
                }
            }
            return $selectedHost ?? reset($hosts); // Default to first host if no good one found
        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

}
