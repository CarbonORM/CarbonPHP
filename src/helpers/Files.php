<?php

namespace CarbonPHP\Helpers;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Programs\ColorCode;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Files
{
    /**
     * @throws PublicAlert
     */
    public static function createDirectoryIfNotExist($directory) : void {

        if (false === is_dir($directory)
            && false === mkdir($directory, 0755, true)
            && false === is_dir($directory)) {

            throw new PublicAlert("Failed to create directory ($directory)");

        }

    }

    /**
     * @link https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
     * @link https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
     * @throws PublicAlert
     */
    public static function rmRecursively(string $dir): void
    {

        if (false === file_exists($dir)) {

            throw new PublicAlert("Failed to verify file exists ($dir)");

        }

        ColorCode::colorCode("Unlinking (file://$dir)", iColorCode::CYAN);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileinfo) {

            $realName = $fileinfo->getRealPath();

            if ($fileinfo->isDir()) {

                if (false === rmdir($realName)) {

                    throw new PublicAlert("Failed to remove directory ($realName)");

                }

            } else if (false === unlink($realName)) {

                throw new PublicAlert("Failed to unlink file ($realName)");

            } else {

                CarbonPHP::$verbose and ColorCode::colorCode("Unlinked file\nfile://$realName", iColorCode::BACKGROUND_CYAN);

            }

        }

        if (false === rmdir($dir)) {

            throw new PublicAlert("Failed to remove directory ($dir)");

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

        } catch (\Error | \Exception | \ErrorException $e) {    // Seriously, I do not want anything but
            throw new PublicAlert($e->getMessage());
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

        if (false === is_dir($directory) && (false === mkdir($directory, 775, true) || false === is_dir($directory))) {

            throw new PublicAlert('The directory (' . $directory . ') does not exists and failed to be created (' . $location . ') failed.');

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

}
