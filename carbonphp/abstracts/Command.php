<?php

namespace CarbonPHP\Abstracts;

use CarbonPHP\Interfaces\iCommand;

abstract class Command extends Composer implements iCommand
{

    private array $CONFIG;

    public function __construct(array $config)
    {
        $this->CONFIG = $config;
    }

}