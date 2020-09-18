<?php

namespace Tests;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use PHPUnit\Framework\TestCase;

/**
 * phpunit likes to rerun code with not explicitly stated to run in separate process
 * add this annotation to each class which extends this class
 * @runTestsInSeparateProcesses
 */
abstract class Config extends TestCase
{
    public const URL = 'http://dev.carbonphp.com/';

    public const ADMIN_USERNAME = 'admin';

    public const ADMIN_PASSWORD = 'adminadmin';

    public static function setupServerVariables(): void   // todo - wut
    {
        CarbonPHP::$safelyExit = true;  // We just want the env to load, not route life :)
        $_SERVER = [
            'REMOTE_ADDR' => '::1',
            'REMOTE_PORT' => '53950',
            'SERVER_SOFTWARE' => 'PHP 7.4.3 Development Server',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/login/',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/',
            'PHP_SELF' => '/index.php/',
            'HTTP_HOST' => 'localhost:80',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'HTTP_REFERER' => 'http://localhost:80/',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_COOKIE' => 'PHPSESSID=gn4amaq3el5giekaboa29q27gp;',
        ];
    }

    public function setUp() : void /* The :void return type declaration that should be here would cause a BC issue */
    {
        if (CarbonPHP::$test) {
            return;
        }

        CarbonPHP::$test = true;

        parent::setUp();

        $_SERVER = [
            'REMOTE_ADDR' => '::1',
            'REMOTE_PORT' => '53950',
            'SERVER_SOFTWARE' => 'PHP 7.2.3 Development Server',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/login/',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/login/',
            'PHP_SELF' => '/index.php/login/',
            'HTTP_HOST' => 'localhost:88',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'HTTP_REFERER' => 'http://dev.carbonphp.com/'
        ];

        // All folder constants end in a trailing slash /
        define('DS', DIRECTORY_SEPARATOR);

        // Set our root folder for the application, this will move up one directory to the c6 root
        define('SERVER_ROOT', dirname(__DIR__) . DS);

        CarbonPHP::$app_root = SERVER_ROOT;

        // Composer autoload
        if (false === (include  CarbonPHP::$app_root . 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
            die(1);
        }

        // The app can exit here if a configuration failure exists
        new CarbonPHP(\Config\Documentation::class);

    }

    public function commit(callable $lambda = null): bool
    {
        $commit = new class extends Database {
            public function testCommit(callable $lambda = null): bool
            {
                /** @noinspection MissUsingParentKeywordInspection */
                return parent::commit($lambda);
            }
        };
        return $commit->testCommit($lambda);
    }

}