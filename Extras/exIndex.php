<?php

define( 'DS', DIRECTORY_SEPARATOR);

define('SERVER_ROOT', dirname(__FILE__) . DS);  // Set our root folder for the application

// These files are required for the app to run. You
if (false == (include SERVER_ROOT . 'Data/vendor/autoload.php')) {       // Load the autoload() for composer dependencies located in the Services folder
    echo "Internal Server Error.";                                             // Composer autoload
    exit(3);
}

Carbon\Carbon::Application(include 'Application/Configs/Options.php')();




