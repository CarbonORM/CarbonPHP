<?php
/*
 * Plugin Name: CarbonPHP
 * Plugin URI: https://www.carbonphp.com/
 * Description: CarbonPHP
 * Author: Richard Tyler Miles
 */


$throwError = static function() {

    http_response_code(400);

    print '<h1>CarbonPHP is an opensource library. It looks like you have accessed the WordPress bootstrap file, or in 
            n00b terminology what allows us to be compatible with WordPress as a plugin. You should not try to access
            any file directly as classes are PSR-4 compliant. This means all file include operations will be dynamic using
            composer. To lean more about how to use CarbonPHP please refer to 
            <a href="https://www.carbonphp.com/">https://CarbonPHP.com/</a></h1>';

};

if (false === defined('ABSPATH')) {

    $throwError();

    exit(1);

}

if (true === file_exists(ABSPATH . 'carbonphp/helpers/WordpressPluginConfiguration.php')) {

    require_once ABSPATH . 'carbonphp/helpers/WordpressPluginConfiguration.php';

} elseif (true === file_exists(ABSPATH . 'vendor/carbonorm/carbonphp/carbonphp/helpers/WordpressPluginConfiguration.php')) {

    require_once ABSPATH . 'vendor/carbonorm/carbonphp/carbonphp/helpers/WordpressPluginConfiguration.php';

} else {

    $throwError();

}



