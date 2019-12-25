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
    print '<h1>Loading Composer Failed. See Carbonphp.com for documentation.</h1>';
    die(1);
}

// The app can exit here if a configuration failure exists
CarbonPHP\CarbonPHP::make('config' . DS . 'Config.php');

/* At one point I returned the invocation of $app to show that
 * the application will not exit on completion, but rather return
 * back to this index file. This means you can still execute code
 * after $app(); I stopped returning the __invoke() because if false
 * is returned, the index will re-execute. This turns very bad quickly.
 */

CarbonPHP\CarbonPHP::run( CarbonPHP\C6::class);

return true;


