<?php

declare(strict_types=1);

namespace CarbonPHP\Abstracts;

use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Interfaces\iConfiguration;

abstract class Command extends Composer implements iCommand
{
    public iConfiguration $configuration;
}
