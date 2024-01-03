<?php

namespace Tests\Feature;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Enums\ThrowableReportDisplay;
use CarbonPHP\Error\ThrowableHandler;
use PHPUnit\Framework\TestCase;


abstract class Config extends TestCase
{
    public const URL = 'http://local.carbonphp.com/';

    public const ADMIN_USERNAME = 'admin';

    public const ADMIN_PASSWORD = 'adminadmin';

    public static function setupServerVariables(): void   // todo - what is needed
    {

        ThrowableHandler::$throwableReportDisplay = ThrowableReportDisplay::CLI_MINIMAL;

        CarbonPHP::$safelyExit = true;  // We just want the env to load, not route life :)

        $_SERVER = array_merge($_SERVER, $_SERVER = [
            'SCRIPT_FILENAME' => __FILE__,  // added for wp compatibility
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
        ]);

    }

    public function setUp() : void /* The :void return type declaration that should be here would cause a BC issue */
    {
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
            'HTTP_REFERER' => 'http://local.carbonphp.com/'
        ];

    }

}