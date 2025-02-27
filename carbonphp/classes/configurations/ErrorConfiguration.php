<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Enums\ThrowableReportDisplay;

class ErrorConfiguration
{
    public ThrowableReportDisplay $displayStyle;

    public function __construct(
        public string           $level = E_ALL,
        public bool             $show = true,
        public bool             $store = false,
        public string           $location = '',
        ?ThrowableReportDisplay $displayStyle = null,
        public ?DatabasePool    $pool = null
    )
    {
        $this->displayStyle = $displayStyle ??
            CarbonPHP::CLI
            ? ThrowableReportDisplay::CLI_MINIMAL
            : ThrowableReportDisplay::FULL_DEFAULT;
    }
}
