<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

class CliConfiguration
{
    public function __construct(
        public string $programsDirectory = '',
        public array $programs = [],
    ) {
    }
}
