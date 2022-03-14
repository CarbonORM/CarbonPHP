<?php

/**
 * I actually learned something about php's internal server with this file.
 * If you return false for a file that exists, php will default to
 * resolve it an return it.
 */

$root = $_SERVER['DOCUMENT_ROOT'];

$path = '/' . ltrim(parse_url(urldecode($_SERVER['REQUEST_URI']))['path'], '/');

if (file_exists($root . $path)) {

    // Enforces trailing slash, keeping links tidy in the admin
    if (is_dir($root . $path)
        && substr($path, -1) !== '/') {

        header("Location: $path/");

        exit;

    }

    // Runs PHP file if it exists
    if (strpos($path, '.php') !== false) {

        chdir(dirname($root . $path));

        if (false === include $root . $path) {

            /** @noinspection ForgottenDebugOutputInspection */

            error_log('Failed including file :: ' . $root . $path . PHP_EOL);

            exit(1);

        }

        return true;

    }


    if (0 === strpos($path, '/wp-admin')) {

        return false;

    }

}

// Otherwise, run `index.php`
chdir($root);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
