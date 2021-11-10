<?php

class Config implements iConfig
{
    public static function configuration(): array
    {
        return [
            'SITE' => [
                'URL' => APP_LOCAL ? 'local.carbonphp.com' : 'carbonphp.com',    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
                'ROOT' => APP_ROOT,          /* This was defined in our ../index.php */
                'CACHE_CONTROL' => [
                    'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                    'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
                    'html|htm|php|hbs|js' => 'Cache-Control: max-age=0, private, public',
                ],
                'CONFIG' => __FILE__,               // Send to sockets
                'TIMEZONE' => 'America/Phoenix',    //  Current timezone
                'TITLE' => 'CarbonPHP • C6',        // Website title
                'VERSION' => '4.9.0',               // Add link to semantic versioning
                'SEND_EMAIL' => 'richard@miles.systems',
                'REPLY_EMAIL' => 'richard@miles.systems',
                'HTTP' => APP_LOCAL
            ]
        ];
    }
}