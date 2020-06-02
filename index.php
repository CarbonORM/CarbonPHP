<?php
#phpinfo() and exit;

// All folder constants end in a trailing slash /
const DS = DIRECTORY_SEPARATOR;

// Set our root folder for the application
define('SERVER_ROOT', __DIR__ . DS);

// I would like to change to only using app_root soon
const APP_ROOT = SERVER_ROOT;

// Composer autoload
if (false === (include  'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die(1);
}

// The app can exit here if a configuration failure exists
(new CarbonPHP\CarbonPHP('config' . DS . 'Config.php'))(new CarbonPHP\Documentation);

// If false is returned, the index will re-execute. This turns very bad quickly.
return true;


