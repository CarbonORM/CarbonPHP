<?php

declare(strict_types=1);

namespace CarbonPHP\Abstracts;

use CarbonPHP\Abstracts\CarbonPHP;
use CarbonPHP\CarbonPHP;
use Error\PrivateAlert;
use Error\ThrowableHandler;
use Interfaces\iColorCode;
use Programs\Migrate;

class Htaccess
{
    public const string ENDING_COMMENT = '# END CarbonPHP .htaccess injection - DO NOT MODIFY GENERATED CODE';

    public static function startHtaccessComment(string $identifier): string
    {
        return "# START CarbonPHP ($identifier) - GENERATED CODE";
    }

    public static function updateHtaccess(string $identifier, string $content): void
    {
        try {
            $startWebSocketHtaccessComment = self::startHtaccessComment($identifier);

            $endingComment = self::ENDING_COMMENT;

            $connectionProxy = <<<HTACCESS
            $startWebSocketHtaccessComment
            
            $content
            
            $endingComment
            
            HTACCESS;

            // Attempt to open the .htaccess file in read-write mode
            $htaccessFile = CarbonPHP::$app_root.'/.htaccess';

            $fileResource = fopen($htaccessFile, 'cb+');

            if ($fileResource === false) {
                ColorCode::colorCode('Failed to open .htaccess file. Please check permissions.', iColorCode::RED);
                exit(1);
            }

            // Acquire an exclusive lock
            if (!flock($fileResource, LOCK_EX)) {
                ColorCode::colorCode('Failed to lock .htaccess file for writing.', iColorCode::RED);
                fclose($fileResource); // Always release the resource
                exit(1);
            }

            // Read the current contents of the file
            $htaccess = stream_get_contents($fileResource);

            // Check if the connection proxy exists or needs to be updated
            if (str_contains($htaccess, $connectionProxy)) {
                ColorCode::colorCode('The .htaccess file already contains the WebSocket proxy. No changes were made.');
            } elseif (str_contains($htaccess, $startWebSocketHtaccessComment)) {
                ColorCode::colorCode('The .htaccess file already contains the WebSocket proxy. Updating to new port.', iColorCode::CYAN);

                $htaccess = preg_replace('#'.preg_quote($startWebSocketHtaccessComment, '#').'.*?#s', $connectionProxy, $htaccess);
            } else {
                $htaccess = $connectionProxy.PHP_EOL.$htaccess;
            }

            // Move the file pointer to the beginning of the file and truncate the file to zero length
            ftruncate($fileResource, 0);

            rewind($fileResource);

            // Write the modified contents back to the file
            if (fwrite($fileResource, $htaccess) === false) {
                ColorCode::colorCode('Failed to write to .htaccess file. Please check permissions.', iColorCode::RED);

                flock($fileResource, LOCK_UN); // Release the lock

                fclose($fileResource); // Always release the resource

                if (!CarbonPHP::CLI) {
                    throw new PrivateAlert('Failed to write to .htaccess file. Please check permissions.');
                }

                exit(1);
            }

            // Release the lock and close the file
            flock($fileResource, LOCK_UN);

            fclose($fileResource);
        } catch (\Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    public static function generalConfigurations(): void
    {
        $migrationFolder = Migrate::$migrationFolder;

        self::updateHtaccess('GENERAL', <<<HTACCESS
                    # protect against DOS attacks by limiting file upload size [bytes]
                    LimitRequestBody 10240000
                    
                    # turn off directory browsing through the web server
                    <IfModule mod_autoindex.c>
                        Options -Indexes
                    </IfModule>
                    # all migration data is served using the php runtime (index.php), apache will not give away sensitive information
                    <IfModule mod_alias.c>
                        RedirectMatch 403 ^/$migrationFolder(/?.*)?$
                    </IfModule>
                    <FilesMatch "(composer\.json)|(\.(htaccess|htpasswd|md|ini|log|sh|inc|bak|sql|yml|yaml|iml|cnf|gz|phar))$">
                        Order Allow,Deny
                        Deny from all
                    </FilesMatch>
                    # deny any file that begins with a . (dot)
                    <FilesMatch "^\.">
                        Order allow,deny
                        Deny from all
                    </FilesMatch>
                    <If "%{REQUEST_URI} =~ m#^/\..*#">
                        Order allow,deny
                        Deny from all
                    </If>
                    
                    # https://stackoverflow.com/questions/14003332/access-control-allow-origin-wildcard-subdomains-ports-and-protocols/27990162#27990162
                    SetEnvIf Origin ^(https?://.*(?::\d{1,5})?)$ CORS_ALLOW_ORIGIN=$1
                    Header always set Access-Control-Allow-Origin %{CORS_ALLOW_ORIGIN}e env=CORS_ALLOW_ORIGIN
                    Header merge Vary "Origin"
                    
                    # todo - allow for customized cache times? (Cached for a day - 86400)
                    # it would be uncommon, but if you knew your api wasn't going to change often (like a weather api?)
                    Header always set Access-Control-Max-Age: 0
                    Header always set Access-Control-Allow-Methods "GET, POST, PATCH, PUT, DELETE, OPTIONS"
                    Header always set Access-Control-Allow-Headers: *
                    
                    <FilesMatch "\.(ico|pdf|flv)$"> # 1 YEAR - 29030400; 1 WEEK - 604800; 2 DAYS - 172800; 1 MIN  - 60
                        Header set Cache-Control "max-age=29030400, public"
                    </FilesMatch>
                    <FilesMatch "\.(jpg|jpeg|png|gif|swf|xml|txt|css|js|svg|webp)$">
                        Header set Cache-Control "max-age=604800, public"
                    </FilesMatch>
                    <FilesMatch "\.(html|htm|php|hbs|json|map)$">
                        Header set Cache-Control "max-age=0, private, public"
                    </FilesMatch>
                    HTACCESS);
    }

    /**
     * This method will update the .htaccess file to include a WebSocket proxy for the specified port.
     *
     * @param int $port the port number to which the WebSocket server is listening
     * @param string $path The path to the WebSocket server. Default is 'carbonorm/websocket'.
     */
    public static function updateHtaccessWebSocketPort(int $port, string $path = 'carbonorm/websocket'): void
    {
        $path = trim($path, '/');

        // This is the proxy for the WebSocket - we want apache to forward all requests to the WebSocket server
        self::updateHtaccess($path, <<<HTACCESS
            <IfModule mod_alias.c>
                RedirectMatch 403 ^/tmp(/?.*)?$
                RedirectMatch 403 ^/tmp/tmp/migration_.*$
            </IfModule>
            
            <IfModule mod_rewrite.c>
                RewriteEngine On
                RewriteCond %{HTTP:Connection} Upgrade [NC]
                RewriteCond %{HTTP:Upgrade} websocket [NC]
                RewriteRule ^/?$path/?(.*)? ws://127.0.0.1:$port/$path/$1  [P,L,E=noconntimeout:1,E=noabort:1]
            </IfModule>
            HTACCESS);
    }
}
