<?php

use CarbonPHP\CarbonPHP;
use CarbonPHP\Documentation;

// Composer autoload
if (false === (include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {

    print '<h1>Composer Failed. Please run <b>composer install</b> $| refer to <a href="https://carbonphp.com/">CarbonPHP.com</a></h1>';

    die(1);
    /**
     * Front to the WordPress application. This file doesn't do anything, but loads
     * wp-blog-header.php which does and tells WordPress to load the theme.
     *
     * @package WordPress
     */

}
/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

(new CarbonPHP(Documentation::class, __DIR__ . DIRECTORY_SEPARATOR))();

return true;


