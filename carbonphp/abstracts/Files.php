<?php

namespace CarbonPHP\Abstracts;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\Migrate;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

abstract class Files
{


    public static function createDirectoryIfNotExist($directory) : void {

        try {

            if (false === is_dir($directory)
                && false === mkdir($directory, 0777, true)
                && false === is_dir($directory)) {

                throw new PrivateAlert("Failed to create directory ($directory)");

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

    }

    /**
     * @link https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
     * @link https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
     */
    public static function rmRecursively(string $dir): void
    {

        if (false === is_dir($dir)) {

            throw new PrivateAlert("Failed to verify directory exists ($dir)");

        }

        ColorCode::colorCode("Unlinking (file://$dir)", iColorCode::CYAN);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileinfo) {

            $realName = $fileinfo->getRealPath();

            if ($fileinfo->isDir()) {

                if (false === rmdir($realName)) {

                    throw new PrivateAlert("Failed to remove directory ($realName)");

                }

            } else if (false === unlink($realName)) {

                throw new PrivateAlert("Failed to unlink file ($realName)");

            } else {

                CarbonPHP::$verbose and ColorCode::colorCode("Unlinked file\nfile://$realName", iColorCode::BACKGROUND_CYAN);

            }

        }

        if (false === rmdir($dir)) {

            throw new PrivateAlert("Failed to remove directory ($dir)");

        }

    }


    /** The following method was originated from the link provided.
     * @link http://php.net/manual/en/features.file-upload.php
     * @param $fileArray
     * @param $location
     * @return string
     * @throws PublicAlert
     */
    public static function uploadFile(array $fileArray, string $location) : ?string
    {
        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($fileArray['error'])) {
                throw new \RuntimeException('Files could not be upl.');                 // changes to catch via error handler
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($fileArray['error']) {
                case UPLOAD_ERR_OK:             // We hope
                    break;
                case UPLOAD_ERR_NO_FILE:
                    # Stats Coach Edit
                    return false;
                #throw new \RuntimeException( 'No file sent.' );
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \RuntimeException('Exceeded filesize limit.');
                default:
                    throw new \RuntimeException('Unknown errors.');
            }

            // You should also check file size here.
            if ($fileArray['size'] > 1000000)
                throw new \RuntimeException('Exceeded filesize limit.');


            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            if (false === $ext = array_search($finfo->file($fileArray['tmp_name']),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',),
                    true)) throw new \RuntimeException('Invalid file format.');


            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from server/user state data.
            $count = 0;

            do {
                $targetPath = $location . $_SESSION['id'] . '_' . time() . '_' . $count++ . '.' . $ext;
            } while (file_exists($targetPath));


            if (!move_uploaded_file($fileArray['tmp_name'], $targetPath)) {
                throw new \RuntimeException('Failed to move uploaded file.');
            }

            return $targetPath;

        } catch (Throwable $e) {    // Seriously, I do not want anything but
            throw new PrivateAlert($e->getMessage());
        }
    }

    /** Attempt to safely make a directory using the current user.
     * @param $location - should end with a DIRECTORY_SEPARATOR or will be parsed with dirname()
     * @return void
     * @throws PublicAlert
     */
    public static function mkdir($location) : void
    {
        if ($location[-1] === DIRECTORY_SEPARATOR) {

            $directory = $location;

        } else {

            $directory = dirname($location);

        }

        if (false === is_dir($directory)
            && false === mkdir($directory, 0775, true)
            && false === is_dir($directory)) {

            throw new PrivateAlert('The directory (' . $directory . ') does not exists and failed to be created (' . $location . ') failed.');

        }

    }

    /** Attempt to store a string to a file. If the file does not exist the
     * method will attempt to create it.
     * @param string $file the absolute file path
     * @param string $output the text to store in the file
     * @return bool failure status
     * @throws \RuntimeException    If the directory does not exist & cannot be created
     */
    public static function storeContent(string $file, string $output) : bool
    {
        $user = get_current_user();                           // get current process user

        $dir = \dirname($file);

        exec("chown -R {$user}:{$user} " . $dir);           // We need to modify the permissions so users can write to it

        if (!file_exists($dir)) {
            static::mkdir($dir);
        }
        if (false === $rs = fopen($file, 'wb')) {
            return false;
        }
        fwrite($rs, $output);
        return fclose($rs);
    }


    public static function largeHttpGetRequestsToFile(string $url, string $toLocalFilePath, array &$responseHeaders = [], int $timeout = 3): void
    {

        $bytesStored = false;

        try {

            $url = trim($url);

            ColorCode::colorCode("Attempting to get possibly large file\n$url\nfile://$toLocalFilePath", iColorCode::BACKGROUND_GREEN);

            $fileName = basename($toLocalFilePath);

            $tmpPath = CarbonPHP::$app_root . 'tmp' . DS . $fileName;

            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            Migrate::curlReturnFileAppend($ch, $tmpPath, $bytesStored);

            curl_setopt($ch, CURLOPT_COOKIEJAR, '-');

            ColorCode::colorCode("Setting the timeout to ($timeout) <" . Migrate::secondsToReadable($timeout) . '>', iColorCode::BACKGROUND_YELLOW);

            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            $responseHeaders = [];

            Migrate::curlProgress($ch);

            Migrate::curlGetResponseHeaders($ch, $responseHeaders);

            $dirname = dirname($toLocalFilePath);

            self::createDirectoryIfNotExist($dirname);

            if (false === touch($tmpPath)) {

                throw new PrivateAlert("Failed to create tmp file (file://$tmpPath)");

            }

            // $output contains the output string
            curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            if (false === file_exists($tmpPath)) {

                throw new PrivateAlert("Failed to locate temp file ($tmpPath)");

            }

            ColorCode::colorCode("Stored to local tmp file (file://$tmpPath)", iColorCode::BACKGROUND_RED);

            if (false === $bytesStored) {

                ColorCode::colorCode("The method (" . __METHOD__ . ") received 0 bytes while fetching url\n($url) and storing to file\n(file://$toLocalFilePath). Empty file created.");

            }

            if ($toLocalFilePath !== $tmpPath) {

                if (file_exists($toLocalFilePath) && false === unlink($toLocalFilePath)) {

                    throw new PrivateAlert("Failed to unlink <remove> file ($toLocalFilePath)");

                }

                if (false === copy($tmpPath, $toLocalFilePath)) {

                    throw new PrivateAlert("Failed to copy ($tmpPath) to ($toLocalFilePath)");

                }

            }

            ColorCode::colorCode("Stored to file \nfile://$toLocalFilePath", iColorCode::BACKGROUND_CYAN);

            if (CarbonPHP::$verbose) {

                ColorCode::colorCode("Detected in verbose mode, will not unlink file\nfile://$tmpPath",
                    iColorCode::YELLOW);

            } elseif ($toLocalFilePath !== $tmpPath) {

                unlink($tmpPath);

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

}
