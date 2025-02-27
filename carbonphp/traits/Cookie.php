<?php

declare(strict_types=1);

namespace CarbonPHP\Traits;

trait Cookie
{
    public int $lifetime;

    public string $path;

    public string $domain;

    public bool $secure;

    public bool $httponly;

    public bool $samesite;
}
