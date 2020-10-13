<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\interfaces\iCommand;

class ApacheConfig implements iCommand
{
    use Background;

    private array $CONFIG;

    public function __construct($CONFIG)
    {
        [$this->CONFIG] = $CONFIG;
    }

    public function usage(): void
    {
        // TODO - improve documentation
        print "\n\n\tThis program will copy the .htaccess C6 configuration file to you \n\n";
        exit(1);
    }

    public function cleanUp(): void
    {
        // do nothing
    }

    public function run($argv): void
    {
        $C6 = CarbonPHP::CARBON_ROOT === CarbonPHP::$app_root . 'src' . DS;
        $argc = count($argv);
        $serverAdmin = $C6 ? 'Support@Miles.Systems' : '';
        $url = $this->CONFIG['SITE']['URL'] ?? 'example.com';
        $port = $this->CONFIG['SOCKET']['PORT'] ?? 'example.com';
        for ($i = 0; $i < $argc; $i++) {
            switch ($argv[$i]) {
                case '-socketPort':
                    $port = $argv[++$i];
                    break;
                case '-url':
                    $url = $argv[++$i];
                    break;
                case '-email':
                    $serverAdmin = $argv[++$i];
                    break;
                case '-help':
                default:
                    if ($C6) {
                        self::colorCode("\tYou da bomb :)\n", 'blue');
                        break;
                    }
                    $this->usage();
                    break;

            }
        }

        if (!file_put_contents($dest = CarbonPHP::$app_root . '.htaccess', $this->getConfiguration($serverAdmin, $url, $port))) {
            print "\n\n\tFailed to copy the Apache configuration file.\n\n";
            exit(1);
        }
        print "\n\n\t Copied .htaccess to ($dest) successfully.\n\n";
    }

    public function getConfiguration($serverAdmin, $url, $port): string
    {
        return <<<CONFIG

#Fix Rewrite
Options -Multiviews

RewriteEngine on

# enable symbolic links
Options +FollowSymLinks

# Disable directory browsing
Options All -Indexes

# Set the directory index
DirectoryIndex index.php

# protect against DOS attacks by limiting file upload size [bytes]
LimitRequestBody 10240000

# God forbit an uncaught error - Display contact method
ServerSignature EMail
SetEnv SERVER_ADMIN $serverAdmin

# Remove www.
RewriteBase /
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/ [R=301]

# set the default language
DefaultLanguage en-US

# pass the default character set
AddDefaultCharset utf-8

# this needs to be somewhere
# LoadModule http2_module modules/mod_http2.so
ProtocolsHonorOrder On
Protocols h2 http/1.1

# Enable compression
<ifModule mod_gzip.c>
    mod_gzip_on Yes
    mod_gzip_dechunk Yes
    mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
    mod_gzip_item_include handler ^cgi-script$
    mod_gzip_item_include mime ^text/.*
    mod_gzip_item_include mime ^application/x-javascript.*
    mod_gzip_item_exclude mime ^image/.*
    mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</ifModule>

<IfModule mod_speling.c>
	CheckSpelling On
</IfModule>

# Error Redirects
ErrorDocument 404 https://$url/404/

# Version Control for Dependancies
RewriteRule ^(.*)\.[\d]{10}\.(css|js|html)$ $1.$2 [L]

#prevent hotlinking
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https://$url/.*$ [NC]
RewriteRule \.(ico|pdf|flv|jpg|jpeg|png|gif|swf|xml|txt|css|html|htm|php|hbs|js)$ - [F]

<FilesMatch "\.(ico|pdf|flv)$"> # 1 YEAR - 29030400; 1 WEEK - 604800; 2 DAYS - 172800; 1 MIN  - 60
    Header set Cache-Control "max-age=29030400, public"
</FilesMatch>

<FilesMatch "\.(jpg|jpeg|png|gif|swf|xml|txt|css)$">
    Header set Cache-Control "max-age=604800, public"
</FilesMatch>

<FilesMatch "\.(html|htm|php|hbs|js)$"> # TODO - Eventually we should cache mustache files
    Header set Cache-Control "max-age=0, private, public" # normally 60
</FilesMatch>


# deny access to evil robots site rippers offline browsers and other nasty scum
RewriteBase /
RewriteCond %{HTTP_USER_AGENT} ^Anarchie [OR]
RewriteCond %{HTTP_USER_AGENT} ^ASPSeek [OR]
RewriteCond %{HTTP_USER_AGENT} ^attach [OR]
RewriteCond %{HTTP_USER_AGENT} ^autoemailspider [OR]
RewriteCond %{HTTP_USER_AGENT} ^Xaldon\ WebSpider [OR]
RewriteCond %{HTTP_USER_AGENT} ^Xenu [OR]
RewriteCond %{HTTP_USER_AGENT} ^Zeus.*Webster [OR]
RewriteCond %{HTTP_USER_AGENT} ^Zeus
RewriteRule ^.*$ http://www.cnn.com [R,L]
# send em to a hellish website of your choice

# https://serverfault.com/questions/940923/apache2-with-http-2-serves-some-content-with-h2-some-with-http-1-1

RewriteCond %{HTTP:Connection} Upgrade [NC]
RewriteCond %{HTTP:Upgrade} websocket [NC]
RewriteRule /(.*) ws://127.0.0.1:$port/$1 [P,L]


# Redirect anything that's not of the following file types to the index
RewriteCond %{REQUEST_URI} !(\.png|\.jpg|\.gif|\.svg|\.json|\.jpeg|\.bmp|\.icon|\.js|\.css|\.woff|.\woff2|\.map|\.hbs)$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d   -- We dont want to redirect into direcotries
RewriteRule ^(.*)$ /index.php [L,QSA]

CONFIG;

    }


}
