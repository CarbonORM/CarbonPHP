<?php


if (false === include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php') {
    // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Try running <code>>> composer run rei</code></h1>';
    die(1);
}


