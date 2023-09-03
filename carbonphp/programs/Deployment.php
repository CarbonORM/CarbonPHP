<?php


namespace CarbonPHP\Programs;

use CarbonPHP\Abstracts\Background;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Route;

class Deployment implements iCommand
{

    private string $ipAddress;

    private static array $sites = [
        [
            'Domain' => 'carbonphp.com',
            'Composer' => true,
            'Repository' => 'https://github.com/richardtmiles/carbonphp.com',
            'Username' => 'LqKM581y7EQwfJ9m',                                       // github
            'Password' => 'N99s67ugBFD5dJgB',
            'Subdomains' => [
                'www' => [
                    'Username' => 'wqQMDuQ7wWtLaBv1',                               // google domains
                    'Password' => 'h5G38jJHBpIwmJsN'
                ]
            ],
        ],
    ];


    public static function description(): string
    {
        return 'Deploy a CarbonPHP application to a server.';
    }


    public static function github(string $prefix = 'github') : bool {

        // @link https://gist.github.com/gka/4627519
        return Route::regexMatch('#' . preg_quote($prefix, '#') . '#i', static function () {

            $json = file_get_contents('php://input'); // Raw POST date from STDIN

            $hash = hash_hmac('sha1', $json, 'PQWL%7!?KLp-kc%C3!uaTqWy7b6TXb'); // Hash from raw POST data

            $server_hash = !empty($_SERVER['HTTP_X_HUB_SIGNATURE']) ? substr($_SERVER['HTTP_X_HUB_SIGNATURE'], 5) : null;

            if ($server_hash !== $hash) {

                ColorCode::colorCode($message = 'Github Update failed to run verify server hash', iColorCode::RED);

                print $message;

                die(0);

            }

            $json = json_decode($json, true, 512,JSON_THROW_ON_ERROR);

            if ('refs/heads/master' === $json['ref']) {

                print shell_exec('git fetch --all && git reset --hard origin/master');

            } else {

                print 'The branch ' . $json['ref'] . ' was parsed. Nothing to do.';

            }

        });

    }


    public function __construct(array $CONFIG)
    {
        if (array_key_exists('DEPLOYMENT', $CONFIG)) {
            self::$sites = $CONFIG['DEPLOYMENT'];
        }
        $this->ipAddress = trim(`dig +short myip.opendns.com @resolver1.opendns.com`);
        if (!empty($this->ipAddress)) {
            ColorCode::colorCode("IP ADDRESS = {$this->ipAddress} \n\n");
        }
    }

    public function usage(): void
    {
        // TODO - improve documentation
        print 'This is an LAP Build Script on GCP. MySQL is remotely hosted.';
    }

    public function cleanUp(): void
    {
        // todo - remove apache configs
    }

    public static function addComposerToFile($file) : void {
        $filename = $file;
        $index = file_get_contents($filename);
        if (false === $index) {
            ColorCode::colorCode("\nFailed to get file $index.\n\n");
            exit(1);
        }
        $index = self::addComposer($index);
        if (!file_put_contents($filename, $index)) {
            ColorCode::colorCode('Failed to add composer to wordpress.', iColorCode::RED);
            exit(1);
        }
        ColorCode::colorCode('Complete.');
    }

    public static function permissions(): void
    {
        Background::executeAndCheckStatus('sudo chown -R root:c6devteam /var/www');
        Background::executeAndCheckStatus('sudo chmod g+rwX /var/www/ -R');
    }

    // https://support.google.com/domains/answer/6147083?hl=en
    public static function updateGoogleDynamicDNS($ip): void
    {
        Background::executeAndCheckStatus('sudo systemctl restart apache2');

        $dynamicDNS = static function (string $USERNAME, string $PASSWORD, string $HOSTNAME, string $IP) {
            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, "https://$USERNAME:$PASSWORD@domains.google.com/nic/update?hostname=$HOSTNAME&myip=$IP");

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $output = curl_exec($ch);

            $info = curl_getinfo($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            if ($output === true || $output === false) {
                $output = $output ? 'true' : 'false';
            }

            $output = trim(trim($output, $IP)); // could be way better, but eh.. then it got worse and oh well

            // https://github.com/StevenWeathers/google-domains-ddns-updater/issues/7
            // https://www.powershellgallery.com/packages/GoogleDynamicDNSTools/3.0/Content/Functions%5CUpdate-GoogleDynamicDNS.ps1
            // $output contains the output string
            ColorCode::colorCode("DNS Update for $HOSTNAME returned code ({$info['http_code']}) with response ::\n\t $output \n\n");

            switch ($output) {
                case 'badauth':
                    ColorCode::colorCode('The username/password you provided was not valid for the specified host.', 'red');
                    break;
                case 'nohost':
                    ColorCode::colorCode('The hostname you provided does not exist, or dynamic DNS is not enabled.', 'red');
                    break;
                case 'notfqdn':
                    ColorCode::colorCode('The supplied hostname is not a valid fully-qualified domain name.', 'red');
                    break;
                case 'badagent':
                    ColorCode::colorCode('You are making bad agent requests, or are making a request with IPV6 address (not supported).', 'red');
                    break;
                case 'abuse':
                    ColorCode::colorCode('Dynamic DNS access for the hostname has been blocked due to failure to interperet previous responses correctly.', 'red');
                    break;
                case '911':
                    ColorCode::colorCode('An error happened on Google\'s end; wait 5 minutes and try again.', 'red');
                    break;
                default:
                    if (str_starts_with($output, 'nochg')) {
                        ColorCode::colorCode("No change to IP for $HOSTNAME (already set to $IP).");
                    } else if (str_starts_with($output, 'good')) {
                        ColorCode::colorCode("IP successfully updated for $HOSTNAME to $IP.");
                    } else {
                        ColorCode::colorCode('Could not parse results from Google Domains.', 'red');
                        exit(1);
                    }
                    break;
            }
        };

        foreach (self::$sites as $info) {
            $name = strtolower($info['Domain']);

            if (!empty($info['Username'] ?? false) && !empty($info['Password'] ?? false)) {
                $dynamicDNS($info['Username'], $info['Password'], $name, $ip);
            }
            if (!empty($info['Subdomains'] ?? false)) {
                foreach ($info['Subdomains'] as $subdomain => $credentials) {
                    $dynamicDNS($credentials['Username'], $credentials['Password'], "$subdomain.$name", $ip);
                }
            }
        }

        ColorCode::colorCode("\n\n\tDone updating DNS.\n\n", 'blue');
    }


    private static function apache($ip, $reset = false): void
    {
        $dirMods = <<<CONF

<IfModule mod_dir.c> 
        DirectoryIndex index.php
</IfModule> 
# vim: syntax=apache ts=4 sw=4 sts=4 sr noet

CONF;

        if (!file_put_contents($filename = __DIR__ . '/dir.conf', $dirMods)) {
            ColorCode::colorCode("\n\nFailed to create files in current repository?\n\n", 'red');
            exit(1);
        }

        Background::executeAndCheckStatus("sudo mv $filename /etc/apache2/mods-enabled/dir.conf");

        $apacheConf = self::configFile();

        if (!file_put_contents($filename = __DIR__ . '/apache2.conf', $apacheConf)) {
            ColorCode::colorCode("\n\nFailed to create files in current repository?\n\n", 'red');
            exit(1);
        }

        Background::executeAndCheckStatus("sudo mv $filename /etc/apache2/apache2.conf");


        // Background::executeAndCheckStatus('sudo ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/headers.load');

        // Background::executeAndCheckStatus('sudo systemctl restart apache2');

        ColorCode::colorCode("\n\n\tYou must add the IP address '$ip' to your Public IP Authorized networks @\n\thttps://console.cloud.google.com/sql/\n\n\tPress enter when done to continue!!", 'background_blue');

        $handle = fopen('php://stdin', 'rb');
        fgets($handle);
        fclose($handle);

        ColorCode::colorCode("\n\n\tStarting Config For Each Website\n\n", 'blue');

        $apacheConfigDir = '/etc/apache2/sites-available/';

        foreach (self::$sites as $info) {
            $name = strtolower($info['Domain']);

            if (!is_string($info['Repository'] ?? false)) {
                ColorCode::colorCode("\n\n\tRepository was not set.\n\n", 'yellow');
            } else if (($exists = file_exists("/var/www/$name")) && !$reset) {
                ColorCode::colorCode("\n\n\tRepository already exists.\n\n", 'green');
            } else if ($reset && $exists) {
                ColorCode::colorCode("\n\n\tAttempting to update repository.\n\n", 'blue');
                Background::executeAndCheckStatus("sudo git -C '/var/www/$name' pull");
            } else {
                Background::executeAndCheckStatus("sudo git clone {$info['Repository']} /var/www/$name");

                self::permissions();                                                    // todo double check working

                if (false === file_put_contents($htaccessDir = "/var/www/$name/.htaccess", $htaccessData = self::htaccess($name))) {
                    ColorCode::colorCode("\n\n\tFailed to write .htaccess. in ($htaccessDir).\n\n", 'red');
                    ColorCode::colorCode("\n\n\n\n$htaccessData\n\n\n\n", 'red');
                }

                self::permissions();

                print PHP_EOL;
            }

            $confFile = $apacheConfigDir . $name . '.conf';

            if (!$reset && !file_exists($confFile)) {
                $buffer = self::apacheConfig($name);
                if (!is_dir($concurrentDirectory = __DIR__ . DS . 'apache-configs') &&
                    !mkdir(__DIR__ . DS . 'apache-configs', 0777, true) &&
                    !is_dir($concurrentDirectory)) {
                    print "Failed to Create Cache Apache Folder ($name)";
                    exit(1);
                }

                $tempFile = __DIR__ . $name . '.conf';

                if (false === file_put_contents($tempFile, $buffer)) {
                    print "Failed to create new file in apache cache dir. Likely that user permissions not correct. ($name)";
                    exit(1);
                }

                Background::executeAndCheckStatus("sudo mv \"$tempFile\" \"$confFile\";");
            }
        }

        ColorCode::colorCode("\n\n\tDone Running Apache setup\n\n", 'blue');

    }

    private static function composer(): void
    {
        foreach (self::$sites as $info) {
            $name = strtolower($info['Domain']);

            if (true === ($info['Composer'] ?? false)) {

                # I could make the next two commands more specific, but no reason to
                Background::executeAndCheckStatus('sudo chown -R root:c6devteam /var/www/');

                Background::executeAndCheckStatus('sudo chmod g+rwX /var/www/ -R');

                Background::executeAndCheckStatus("sg c6devteam -c \"composer install --no-suggest -d /var/www/$name\"");

                ColorCode::colorCode("\n\n\tAttempting to run 'composer setup'.\n");

                Background::executeAndCheckStatus("sg c6devteam -c \"composer setup -d /var/www/$name || echo 'Set a setup script in your composer.json to gain more control over your build.'\"");

                Background::executeAndCheckStatus("sudo a2ensite $name");
            }
        }
        print PHP_EOL;
    }
    
    public function run($argv): void
    {
        if (!empty($argv)) {
            switch (strtolower($argv[0])) {
                case 'wordpress:configuration':
                    $db_name = 'CarbonPHP';
                    $db_user = 'root';
                    $db_password = 'password';
                    $db_host = 'localhost';
                    $table_prefix = 'carbon_wp_';
                    $salts = '';

                    array_shift($argv); // aka wordpress:configuration

                    while (null !== ($value = (array_shift($argv)))) {
                        switch ($value) {
                            case '-s':
                            case '--salts_file':
                                $salts = file_get_contents(array_shift($argv));
                            case '-n':
                            case '--db_name':
                                $db_name = array_shift($argv);
                                break;
                            case '-u':
                            case '--db_user':
                                $db_user = array_shift($argv);
                                break;
                            case '-p':
                            case '--db_password':
                                $db_password = array_shift($argv);
                                break;
                            case '-h':
                            case '--db_host':
                                $db_host = array_shift($argv);
                                break;
                            case '--pre':
                            case '--table_prefix':
                                $table_prefix = array_shift($argv);
                                break;
                            default:
                                ColorCode::colorCode("\n\n\tInvalid setup argument supplied to wordpress:configuration '{$value}'.\n\n", 'red');
                                exit(1);
                        }
                    }

                    $wp_config = self::wordpressConfiguration($db_name, $db_user, $db_password, $db_host, $table_prefix, $salts);

                    if (false === file_put_contents('wp-config.php', $wp_config)) {
                        ColorCode::colorCode('Failed to place wp-config.php');
                        exit(1);
                    }
                    ColorCode::colorCode('Command completed successfully. (wp-config.php placed in application root)');
                    exit(0);
                case 'Insert Composer':
                    ColorCode::colorCode("Adding composer php autoload to file {$argv[1]}");
                    self::addComposer($argv[1]);
                    exit(0);
                case 'dns':
                    ColorCode::colorCode("\n\n\tRunning DNS setup\n\n", 'blue');
                    self::updateGoogleDynamicDNS($this->ipAddress);
                    exit(0);
                case 'composer':
                    ColorCode::colorCode("\n\n\tRunning composer setup\n\n", 'blue');
                    self::composer();
                    exit(0);
                case 'apache':
                    ColorCode::colorCode("\n\n\tRunning apache setup\n\n", 'blue');
                    self::apache($this->ipAddress, true);
                    exit(0);
                default:
                    ColorCode::colorCode("\n\n\tInvalid argument '{$argv[0]}'.\n\n", 'red');
                    exit(1);
                    break;
            }
        }

        self::apache($this->ipAddress);

        self::composer();

        self::updateGoogleDynamicDNS($this->ipAddress);

        ColorCode::colorCode("\n\n\tThe php/apache/dns deployment finished, REVIEW logs because IDK what happened.\n\n", 'yellow');
    }

    private static function apacheConfig(string $siteName): string
    {
        $documentRoot = strtolower($siteName);

        return <<<config

<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        ServerName $siteName
        ServerAlias www.$siteName
        ServerAdmin Richard@miles.systems
        DocumentRoot /var/www/$documentRoot


        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        ErrorLog \${APACHE_LOG_DIR}/{$siteName}_error.log
        CustomLog \${APACHE_LOG_DIR}/{$siteName}_access.log combined

</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet

config;

    }

    public static function addComposer($original) : string {

        $original = preg_replace('/^.+\n/', '', $original);

        /** @noinspection PhpIncludeInspection */
        return /** @lang InjectablePHP */ <<<PHP
<?php

const DS = DIRECTORY_SEPARATOR;

if (false === include __DIR__ . DS . 'vendor' . DS . 'autoload.php') {
    // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Try running <code>>> composer run rei</code></h1>' and die;
    // Composer autoload
}

$original

PHP;

    }


    public static function htaccess($site): string
    {
        return <<<HTACCESS

#Fix Rewrite
Options -Multiviews

RewriteEngine on

# enable symbolic links
Options +FollowSymLinks

# Disable directory browsing
Options All -Indexes

# Set the directory index
DirectoryIndex index.php

# Redirect Specific Files
# RewriteRule ^favicon.ico Public/StatsCoach/img/icons/favicon.png [L]

# Remove www.
RewriteBase /
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

#prevent hotlinking
RewriteEngine on
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https://Stats.Coach/.*$ [NC]
RewriteRule \.(gif|jpg)$ - [F]

# protect against DOS attacks by limiting file upload size [bytes]
LimitRequestBody 10240000

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

# set the default language
DefaultLanguage en-US

# pass the default character set
AddDefaultCharset utf-8

# Error Redirects
ErrorDocument 404 $site/404/

# God forbid an uncaught error - Display contact method
ServerSignature EMail
SetEnv SERVER_ADMIN Ricard@Miles.Systems
# 1 YEAR - 29030400; 1 WEEK - 604800; 2 DAYS - 172800; 1 MIN  - 60

<FilesMatch "\.(ico|pdf|flv)$">
Header set Cache-Control "max-age=29030400, public"
</FilesMatch>

<FilesMatch "\.(jpg|jpeg|png|gif|swf|xml|txt|css)$">
Header set Cache-Control "max-age=604800, public"
</FilesMatch>

# TODO - Eventually we should cache mustache files
# normally 60
<FilesMatch "\.(html|htm|php|hbs|js)$">
Header set Cache-Control "max-age=0, private, public"
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
RewriteCond %{HTTP:Upgrade} websocket               [NC]
RewriteRule /(.*)           wss://localhost:8004/$1  [P]

# Version Control for Dependencies
RewriteRule ^(.*)\.[\d]{10}\.(css|js|html)$ $1.$2 [L]

# Redirect anything that's not of the following file types to the index
RewriteCond %{REQUEST_URI}  !(\.png|\.jpg|\.gif|\.jpeg|\.bmp|\.icon|\.js|\.css|\.woff|.\woff2|\.map|\.hbs)$
RewriteRule (.*) index.php [QSA]


HTACCESS;

    }


    public static function configFile() : string {

        return <<<APACHE


# This is the main Apache server configuration file.  It contains the
# configuration directives that give the server its instructions.
# See http://httpd.apache.org/docs/2.4/ for detailed information about
# the directives and /usr/share/doc/apache2/README.Debian about Debian specific
# hints.
#
#
# Summary of how the Apache 2 configuration works in Debian:
# The Apache 2 web server configuration in Debian is quite different to
# upstream's suggested way to configure the web server. This is because Debian's
# default Apache2 installation attempts to make adding and removing modules,
# virtual hosts, and extra configuration directives as flexible as possible, in
# order to make automating the changes and administering the server as easy as
# possible.

# It is split into several files forming the configuration hierarchy outlined
# below, all located in the /etc/apache2/ directory:
#
#       /etc/apache2/
#       |-- apache2.conf
#       |       `--  ports.conf
#       |-- mods-enabled
#       |       |-- *.load
#       |       `-- *.conf
#       |-- conf-enabled
#       |       `-- *.conf
#       `-- sites-enabled
#               `-- *.conf
#
#
# * apache2.conf is the main configuration file (this file). It puts the pieces
#   together by including all remaining configuration files when starting up the
#   web server.
#
# * ports.conf is always included from the main configuration file. It is
#   supposed to determine listening ports for incoming connections which can be
#   customized anytime.
#
# * Configuration files in the mods-enabled/, conf-enabled/ and sites-enabled/
#   directories contain particular configuration snippets which manage modules,
#   global configuration fragments, or virtual host configurations,
#   respectively.
#
#   They are activated by symlinking available configuration files from their
#   respective *-available/ counterparts. These should be managed by using our
#   helpers a2enmod/a2dismod, a2ensite/a2dissite and a2enconf/a2disconf. See
#   their respective man pages for detailed information.
#
# * The binary is called apache2. Due to the use of environment variables, in
#   the default configuration, apache2 needs to be started/stopped with
#   /etc/init.d/apache2 or apache2ctl. Calling /usr/bin/apache2 directly will not
#   work with the default configuration.


# Global configuration
#

#
# ServerRoot: The top of the directory tree under which the server's
# configuration, error, and log files are kept.
#
# NOTE!  If you intend to place this on an NFS (or otherwise network)
# mounted filesystem then please read the Mutex documentation (available
# at <URL:http://httpd.apache.org/docs/2.4/mod/core.html#mutex>);
# you will save yourself a lot of trouble.
#
# Do NOT add a slash at the end of the directory path.
#
#ServerRoot "/etc/apache2"

#
# The accept serialization lock file MUST BE STORED ON A LOCAL DISK.
#
#Mutex file:\${APACHE_LOCK_DIR} default

#
# The directory where shm and other runtime files will be stored.
#

DefaultRuntimeDir \${APACHE_RUN_DIR}

#
# PidFile: The file in which the server should record its process
# identification number when it starts.
# This needs to be set in /etc/apache2/envvars
#
PidFile \${APACHE_PID_FILE}

#
# Timeout: The number of seconds before receives and sends time out.
#
Timeout 300

#
# KeepAlive: Whether or not to allow persistent connections (more than
# one request per connection). Set to "Off" to deactivate.
#
KeepAlive On

#
# MaxKeepAliveRequests: The maximum number of requests to allow
# during a persistent connection. Set to 0 to allow an unlimited amount.
# We recommend you leave this number high, for maximum performance.
#
MaxKeepAliveRequests 100

#
# KeepAliveTimeout: Number of seconds to wait for the next request from the
# same client on the same connection.
#
KeepAliveTimeout 5


# These need to be set in /etc/apache2/envvars
User \${APACHE_RUN_USER}
Group \${APACHE_RUN_GROUP}

#
# HostnameLookups: Log the names of clients or just their IP addresses
# e.g., www.apache.org (on) or 204.62.129.132 (off).
# The default is off because it'd be overall better for the net if people
# had to knowingly turn this feature on, since enabling it means that
# each client request will result in AT LEAST one lookup request to the
# nameserver.
#
HostnameLookups Off

# ErrorLog: The location of the error log file.
# If you do not specify an ErrorLog directive within a <VirtualHost>
# container, error messages relating to that virtual host will be
# logged here.  If you *do* define an error logfile for a <VirtualHost>
# container, that host's errors will be logged there and not here.
#
ErrorLog \${APACHE_LOG_DIR}/error.log

#
# LogLevel: Control the severity of messages logged to the error_log.
# Available values: trace8, ..., trace1, debug, info, notice, warn,
# error, crit, alert, emerg.
# It is also possible to configure the log level for particular modules, e.g.
# "LogLevel info ssl:warn"
#
LogLevel warn

# Include module configuration:
IncludeOptional mods-enabled/*.load
IncludeOptional mods-enabled/*.conf

# Include list of ports to listen on
Include ports.conf


# Sets the default security model of the Apache2 HTTPD server. It does
# not allow access to the root filesystem outside of /usr/share and /var/www.
# The former is used by web applications packaged in Debian,
# the latter may be used for local directories served by the web server. If
# your system is serving content from a sub-directory in /srv you must allow
# access here, or in any related virtual host.
<Directory />
        Options FollowSymLinks
        AllowOverride None
        Require all denied
</Directory>

<Directory /usr/share>
        AllowOverride None
        Require all granted
</Directory>

<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>

#<Directory /srv/>
#       Options Indexes FollowSymLinks
#       AllowOverride None
#       Require all granted
#</Directory>




# AccessFileName: The name of the file to look for in each directory
# for additional configuration directives.  See also the AllowOverride
# directive.
#
AccessFileName .htaccess

#
# The following lines prevent .htaccess and .htpasswd files from being
# viewed by Web clients.
#
<FilesMatch "^\.ht">
        Require all denied
</FilesMatch>


#
# The following directives define some format nicknames for use with
# a CustomLog directive.
#
# These deviate from the Common Log Format definitions in that they use %O
# (the actual bytes sent including headers) instead of %b (the size of the
# requested file), because the latter makes it impossible to detect partial
# requests.
#
# Note that the use of %{X-Forwarded-For}i instead of %h is not recommended.
# Use mod_remoteip instead.
#
LogFormat "%v:%p %h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" vhost_combined
LogFormat "%h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\"" combined
LogFormat "%h %l %u %t \"%r\" %>s %O" common
LogFormat "%{Referer}i -> %U" referer
LogFormat "%{User-agent}i" agent

# Include of directories ignores editors' and dpkg's backup files,
# see README.Debian for details.

# Include generic snippets of statements
IncludeOptional conf-enabled/*.conf

# Include the virtual host configurations:
IncludeOptional sites-enabled/*.conf

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet





APACHE;

    }

    public static function wordpressConfiguration($db_name = 'local', $db_user = 'root', $db_password = 'root', $db_host = 'localhost', $table_prefix = 'wp_c6_', $salts = ''): string
    {
        if ($salts === '' && file_exists(CarbonPHP::$app_root . 'wp-config.php')) {
            $matches = [];
            $oldConfig = file_get_contents(CarbonPHP::$app_root . 'wp-config.php');
            if (false === preg_match_all('#define\(\'AUTH_KEY\'(.+|\n){18}#', $oldConfig, $matches) || empty($matches) || !(array_key_exists(0, $matches) && array_key_exists(0, $matches[0]))) {
                $continue = readline("Detected wp-config.php on the root of your application. We were unable to parse your salts. Continuing would most likely cause data corruption. Continue? [N,y]");
                switch ($continue) {
                    case 'n':
                    case 'N':
                        exit(1);
                    default:
                        print "\n\n";
                        exit(1);
                }
            } else {
                $continue = readline("Detected wp-config.php on the root of your application.\n\n\n{$matches[0][0]}\n\n\n Should we keep these salts? [Y,n]");
                switch ($continue) {
                    case 'n':
                    case 'N':
                        switch (readline("Umm okay, so delete current salts and continue (dangerous)? [N,y]")) {
                            case 'y':
                            case 'Y':
                                break;
                            default:
                                exit(0);
                        }
                        break;
                    default:
                        print "\n\n";
                }
            }
        }

        if ($salts === '') {
            $salts = trim(file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/',false,stream_context_create(
                array("http" => array("user_agent" => "any"))
            )));
        }

        if (empty($salts)) {
            ColorCode::colorCode("\nFailed to generate wordpress salts\n\n", 'red');
            exit(1);
        }
        
        return /** @lang InjectablePHP */  <<<HTML
<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '$db_name' );

/** MySQL database username */
define( 'DB_USER', '$db_user' );

/** MySQL database password */
define( 'DB_PASSWORD', '$db_password' );

/** MySQL hostname */
define( 'DB_HOST', '$db_host' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
$salts

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
\$table_prefix = '$table_prefix';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

HTML;

    }

}