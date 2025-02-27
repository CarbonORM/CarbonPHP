<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Configurations;

use CarbonPHP\Throwables\PublicAlert;

class ApplicationConfiguration
{
    public function __construct(
        public string $app_root = '',
        public string $composer_root = '',
        public string $timezone = 'America/Phoenix' {
            set {
                if ('' === $value) {
                    if (!date_default_timezone_set($value)) {
                        throw new PublicAlert('The internal function date_default_timezone_set() failed. You can explicitly set an empty string "" to disable setting the timezone with CarbonPHP.');
                    }
                }
                $this->timezone = $value;
            }
        },
        public string $version = '',
        public string $sendToEmail = '',
        public string $replyToEmail = '',
        public bool   $httpsOnly = true, // allow traffic on port 80
        public array  $cacheControl = [
            'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
            'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
            'html|htm|hbs|js' => 'Cache-Control: max-age=0, private, public',   // It is not recommended to add php as an extension as explicitly hitting the .php would output its contents without compilation.
        ],
        public bool   $ipTest = true,
    )
    {
        $this->version = '' !== $version
            ? $version : trim(shell_exec('git tag --sort=-v:refname | head -n 1') ?? '0.0.0');
    }
}
