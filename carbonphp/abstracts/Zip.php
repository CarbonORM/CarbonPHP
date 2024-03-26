<?php

namespace CarbonPHP\Abstracts;

use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\ThrowableHandler;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Zip
{
    public static function compress(string $source, string $destination): void
    {
        try {


            if (!extension_loaded('zip') || !file_exists($source)) {
                throw new PrivateAlert("Zip extension is not loaded or the source file/folder does not exist.");
            }

            $zip = new ZipArchive();

            if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {

                throw new PrivateAlert("Could not create zip file.");

            }

            $source = str_replace('\\', '/', realpath($source));

            if (is_dir($source) === true) {

                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);

                    // Ignore "." and ".." folders
                    if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {

                        continue;

                    }

                    $file = realpath($file);

                    if (is_dir($file) === true) {
                        if (!$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'))) {
                            throw new PrivateAlert("Could not add empty directory to zip file.");
                        }
                    } else if (is_file($file) === true) {
                        if (!$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file))) {
                            throw new PrivateAlert("Could not add file to zip file.");
                        }
                    }
                }

            } else if (is_file($source) === true) {

                if (!$zip->addFromString(basename($source), file_get_contents($source))) {

                    throw new PrivateAlert("Could not add single file to zip file.");

                }

            }

            if (!$zip->close()) {
                throw new PrivateAlert("Could not close zip file.");
            }

        } catch (PrivateAlert $e) {

            ThrowableHandler::generateLog($e);

        }

    }

}