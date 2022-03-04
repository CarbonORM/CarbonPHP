<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Files;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Route;
use DirectoryIterator;
use SplFileObject;
use Throwable;

class Migrate implements iCommand
{
    public static string $migrationUrl = 'c6migration';

    public static float $currentTime;

    public static ?string $license = null;

    public static ?string $localUrl = null;

    public static ?string $remoteUrl = null;

    public static ?string $remoteAbsolutePath = null;

    public static ?string $directories = null;

    public static bool $throttle = false;

    public const SKIP_MYSQL_DATA_DUMP_FLAG = '--no-dump-data';

    public const MIGRATE_DIRECTORIES_FLAG = '--directories';

    public static bool $MySQLDataDump = true;

    public static function unlinkMigrationFiles(): void
    {

        $updateCount = 0;

        $migrationFiles = glob(CarbonPHP::$app_root . "tmp/*migration*");

        foreach ($migrationFiles as $file) {

            try {

                unlink($file);

                CarbonPHP::$verbose and ColorCode::colorCode('unlinked (' . $file . ')');

            } catch (Throwable $e) {

                ErrorCatcher::generateLog($e);

            } finally {

                $updateCount++;

            }

        }

        ColorCode::colorCode('Removed (' . $updateCount . ') old migration files!');

    }

    /**
     * @throws PublicAlert
     */
    public function run(array $argv): void
    {

        self::$currentTime = microtime(true);

        ColorCode::colorCode('Oh Ya! MigrateMySQL Has Started!');

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0, $argc = count($argv); $i < $argc; $i++) {

            switch ($argv[$i]) {
                case '--verbose':

                    CarbonPHP::$verbose = true;

                    break;

                case '--license':

                    self::$license = $argv[++$i] ?? '';

                    break;

                case '--local-url':

                    self::$localUrl = $argv[++$i] ?? '';

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found flag set for local URL (' . self::$localUrl . ')');

                    break;

                case '--remote-url':

                    self::$remoteUrl = $argv[++$i] ?? '';

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found flag set for remote URL (' . self::$remoteUrl . ')');

                    break;

                case self::SKIP_MYSQL_DATA_DUMP_FLAG:

                    self::$MySQLDataDump = false;

                    break;

                case self::MIGRATE_DIRECTORIES_FLAG:

                    self::$directories = $argv[++$i] ?? '';

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found request directories flag (' . self::$directories . ')');

                    break;

                default:

                    ColorCode::colorCode("Unrecognized cli argument ($argv[$i]) failing.", iColorCode::BACKGROUND_RED);

                    exit(1);

            }
        }

        if (null === self::$license) {

            $importedLicense = include self::licenseFilePath();

            if (true === is_string($importedLicense) && '' !== $importedLicense) {

                self::$license = $importedLicense;

            } else {

                throw new PublicAlert("No license passed as argument or exists in (file://$importedLicense)");

            }

        }

        if (null === self::$localUrl || null === self::$remoteUrl) {

            throw new PublicAlert('The local and remote url must be passed to the migration command!');

        }

        self::unlinkMigrationFiles();

        $postData = [
            'license' => self::$license,
            'url' => self::$remoteUrl
        ];


        if (null === self::$directories && false === self::$MySQLDataDump) {

            ColorCode::colorCode("You have specified nothing to migrate! When the flag (" . self::SKIP_MYSQL_DATA_DUMP_FLAG . ') is active you must also include (' . self::MIGRATE_DIRECTORIES_FLAG . ')',
                iColorCode::BACKGROUND_RED);

            exit(1);

        }

        $requestedDirectoriesLocalCopyInfo = [];

        // todo - this is the perfect thing to do in the background
        if (null !== self::$directories) {

            $postData += [
                'directories' => self::$directories
            ];

            $requestedDirectories = explode(',', self::$directories);

            foreach ($requestedDirectories as $media) {

                // create a list of all files the requesting server will need to transfer
                $requestedDirectoriesLocalCopyInfo += self::compileFolderFiles($media);

            }

        }

        # we will do this in parallel in the future


        if (false === self::$MySQLDataDump) {

            $postData += [
                self::SKIP_MYSQL_DATA_DUMP_FLAG => true
            ];

        }

        $localManifestPath = CarbonPHP::$app_root . 'tmp/local_migration_manifest.txt';

        $responseHeaders = [];

        $manifestURL = self::$remoteUrl . self::$migrationUrl;

        ColorCode::colorCode("Attempting to get manifest at url ($manifestURL)");

        self::largeHttpPostRequestsToFile($manifestURL, $localManifestPath, $postData, $responseHeaders);

        ColorCode::colorCode('About to look for ABSPATH header');

        $absolutePathHeader = 'abspath: ';

        foreach ($responseHeaders as $header) {

            if (0 === strpos($header, $absolutePathHeader)) {

                self::$remoteAbsolutePath = trim(substr($header, strlen($absolutePathHeader)));

                break;

            }

        }

        if (false === file_exists($localManifestPath)) {

            ColorCode::colorCode("Failed to get manifest from remote server!\n(file://$localManifestPath)", iColorCode::BACKGROUND_RED);

            exit(1);

        }

        // todo - this could be  bottle neck and should be processed one at a time
        $manifest = file_get_contents($localManifestPath);

        if (0 === strpos($manifest, '<html lang="en">')) {

            if (false === rename($localManifestPath, $localManifestPath . '.html')) {

                ColorCode::colorCode("Failed to rename ($localManifestPath) to have .html suffix",
                    iColorCode::BACKGROUND_RED);

            }

            throw new PublicAlert("The manifest download detected an html document (file://$localManifestPath.html). A new line delimited list of files is expected. This is an error. View log file for more details");

        }

        if (null === self::$remoteAbsolutePath) {

            throw new PublicAlert('Failed to parse the absolute path header from the remote server! (' . print_r($responseHeaders, true) . ')');

        }

        if (empty($manifest)) {

            throw new PublicAlert('Failed to get the file manifest from the remote server!');

        }

        ColorCode::colorCode("Manifest\n" . $manifest);

        $explodeManifest = explode(PHP_EOL, $manifest);

        $manifest = [];

        # You have to run the process under root for this to happen.. this is never a good idea when loading external
        # dependencies, aka us; We should looking
        # proc_nice(-19);

        // Client
        // a list of instructional manifest files has been stored on the peer.. lets retrieve this info
        foreach ($explodeManifest as $uri) {

            if (false === empty($uri)) {

                $importManifestFilePath = $uri;

                $prefix = 'tmp/';

                if (strpos($importManifestFilePath, $prefix) === 0) {

                    $importManifestFilePath = substr($uri, strlen($prefix));

                }

                $importManifestFilePath = CarbonPHP::$app_root . 'tmp/import_' . $importManifestFilePath;

                $importManifestFilePath = rtrim($importManifestFilePath, '.ph');

                self::largeHttpGetRequestsToFile(self::$remoteUrl . $uri . '?license=' . self::$license, $importManifestFilePath);

                $manifest[$uri] = $importManifestFilePath;

            }

        }

        // todo - we need to NOT download zips unless needed
        #now do something with each of the files we've imported...
        foreach ($manifest as $uri => $importFileAbsolutePath) {

            CarbonPHP::$verbose and ColorCode::colorCode($importFileAbsolutePath, iColorCode::MAGENTA);

            self::importManifestFile($importFileAbsolutePath, $uri, $requestedDirectoriesLocalCopyInfo);

        }

        ColorCode::colorCode('Complete!');

        exit(0);

    }

    // @link https://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
    public static function getLineCount($filePath): int
    {

        $file = new SplFileObject($filePath, 'rb');

        $file->seek(PHP_INT_MAX);

        return $file->key();

    }

    /**
     * A list of media files. Folders will have been zipped but expect
     *      ico|pdf|flv|jpg|jpeg|png|gif|swf|xml|txt|css|html|htm|php|hbs|js|pdf|.... etc anything
     * @param string $file
     * @param string $uri
     * @return void
     */
    public static function importMedia(string $file, string $uri): void
    {

        static $color = true;

        try {

            if (false === file_exists($file)) {

                throw new PublicAlert("Failed to locate migration import ($file)");

            }

            $lineCount = self::getLineCount($file);

            $fp = fopen($file, 'rb');

            if (false === $fp) {

                throw new PublicAlert("Failed to open file pointer to ($file)");

            }

            rewind($fp);

            $count = 0;

            $hash = '';

            $localUpdates = [];

            // a new line delimited list of file names to import
            while (false === feof($fp)) {

                self::showStatus(++$count, $lineCount);

                $mediaFile = fgets($fp, 1024);

                $mediaFile = trim($mediaFile);

                if ('' === $mediaFile) {

                    continue;

                }

                if (true === self::$throttle) {

                    $getMetaUrl = self::$remoteUrl . self::$migrationUrl . '/' . base64_encode($mediaFile) . '?license=' . self::$license;

                } else {

                    $getMetaUrl = self::$remoteUrl . $uri . '?license=' . self::$license . '&file=' . base64_encode($mediaFile);

                }

                $localPath = CarbonPHP::$app_root . $mediaFile;

                if (true === file_exists($localPath)) {

                    $hash = md5_file($localPath);

                    $url = "$getMetaUrl&md5=$hash";

                    ColorCode::colorCode("local copy\nfile://$localPath) exists, testing hash ($hash) with ur ($url)!",
                        iColorCode::BACKGROUND_WHITE);

                    $updateStatus = file_get_contents($url);

                    $updateStatus = trim($updateStatus);

                    if ('true' === $updateStatus) {

                        $isZip = preg_match('/zip$/', $localPath);

                        if (false === $isZip
                            || 0 === $isZip) {

                            ColorCode::colorCode("No updates need for (file://$localPath)");

                            continue;

                        }

                        ColorCode::colorCode("Detected the same zip, but downloading anyway to test!", iColorCode::RED);

                    }

                    CarbonPHP::$verbose and ColorCode::colorCode("MD5 remote server check status ($updateStatus)", iColorCode::BACKGROUND_YELLOW);


                }

                ColorCode::colorCode("Updates needed <$hash>($localPath)", iColorCode::BACKGROUND_CYAN);

                ColorCode::colorCode($mediaFile, $color ? iColorCode::BACKGROUND_GREEN : iColorCode::BACKGROUND_CYAN);

                $color = !$color;

                $localUpdates[] = 'file://' . $localPath;

                self::largeHttpGetRequestsToFile($getMetaUrl, $localPath);

                if (false !== preg_match('/zip$/', $localPath)) {

                    $zipFileName = basename($localPath);

                    ColorCode::colorCode("Exploding ($localPath)", iColorCode::YELLOW);

                    [, $path, $md5] = explode('_', $zipFileName);

                    // todo - this could be more expensive than a longer version
                    [$md5,] = explode('.', $md5);   // remove the .zip suffix

                    $unzipToPath = base64_decode($path);

                    $downloadedMd5 = md5_file($localPath);

                    if ($downloadedMd5 !== $md5) {

                        throw new PublicAlert("The md5 doesn't match :(");

                    }

                    ColorCode::colorCode("The next step is unzipping to path ($unzipToPath)",
                        iColorCode::YELLOW);

                    $unzipToPath = CarbonPHP::$app_root . $unzipToPath;

                    if (is_dir($unzipToPath)) {

                        self::clearDirectory($unzipToPath);

                    } elseif (false === mkdir($unzipToPath) && !is_dir($unzipToPath)) {

                        throw new PublicAlert("Failed to makedir($unzipToPath)");

                    }

                    $unzip = "unzip '$localPath' -d '$unzipToPath'";

                    Background::executeAndCheckStatus($unzip);

                }

            }

            // show status will typically clear all prior messages in its buffer.
            // Let print the important things again!
            self::showStatus($count, $lineCount);

            if ([] === $localUpdates) {

                ColorCode::colorCode("\nfile://$file\nThe file above was parsed and found no updates needed :)");

            } else {

                $localUpdatesString = implode(PHP_EOL, $localUpdates);

                ColorCode::colorCode("All updated files ::\n$localUpdatesString", iColorCode::BACKGROUND_CYAN);

            }

            fclose($fp);

            CarbonPHP::$verbose and ColorCode::colorCode('Done.');

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(1);

        }

    }

    /**
     * @throws PublicAlert
     */
    public static function importManifestFile(string $file, string $uri, array $requestedDirectoriesLocalCopyInfo): void
    {

        CarbonPHP::$verbose and ColorCode::colorCode("Importing file ($file)");

        $info = pathinfo($file);

        switch ($info['extension']) {

            case 'txt':

                // Its still valid to transfer files when (CarbonPHP::$app_root === self::$remoteAbsolutePath)
                // when scaling  multiple servers on seperate ebs

                ColorCode::colorCode("Import media manifest\nfile://$file", iColorCode::CYAN);

                self::importMedia($file, $uri);

                break;

            case 'sql':

                if (self::$MySQLDataDump) {

                    ColorCode::colorCode("Doing an update to Mysql, do no exit!!!\nfile://$file",
                        iColorCode::BACKGROUND_YELLOW);

                    if (CarbonPHP::$app_root !== self::$remoteAbsolutePath) {

                        self::replaceInFile(CarbonPHP::$app_root, self::$remoteAbsolutePath, $file);

                    } else {

                        ColorCode::colorCode('App absolute path is the same on both servers.', iColorCode::YELLOW);

                    }

                    if (self::$localUrl !== self::$remoteUrl) {

                        self::replaceInFile(self::$localUrl, self::$remoteUrl, $file);

                    } else {

                        ColorCode::colorCode("Both servers point the same url.", iColorCode::YELLOW);

                    }

                    MySQL::MySQLSource($file);

                    break;

                }

                throw new PublicAlert("A MySQL dump file ($file) was found though the " . self::SKIP_MYSQL_DATA_DUMP_FLAG . " was set.");

        }

        if (CarbonPHP::$verbose) {

            ColorCode::colorCode("The verbose flag will cause the migration file to not be unlinked (deleted)",
                iColorCode::BACKGROUND_RED);

        } else {

            unlink($file);

        }

    }

    /**
     * @throws PublicAlert
     * @todo - I could make sed replace multiple at a time, but would this be worth the debugging..?
     */
    public static function replaceInFile(string $string, string $replacement, string $absoluteFilePath): void
    {

        /**
         * @throws PublicAlert
         */
        $delimited = static function (string $string_before): string {

            $string_after = preg_replace('#/#', "\/", $string_before);

            if (PREG_NO_ERROR !== preg_last_error()) {

                throw new PublicAlert("Regex replace failed on string ($string_before) using preg_replace( '#/#', '\/', ..");

            }

            return $string_after;
        };

        $replace = "sed -e 's/" . $delimited($string)
            . "/" . $delimited($replacement)
            . "/g' $absoluteFilePath > $absoluteFilePath.tmp && rm $absoluteFilePath && mv $absoluteFilePath.tmp $absoluteFilePath";

        $resultCode = 0;

        passthru($replace, $resultCode);

        if (0 !== $resultCode) {

            throw new PublicAlert("Failed to replace strings using command :: ($replace)");

        }

    }


    public static function selfHidingFile(): string
    {

        $license = self::$license;

        return <<<HALT
<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");

\$_GET['license'] ??= '';

\$_GET['file'] ??= '';

\$_GET['md5'] ??= '';

if ('$license' !== \$_GET['license']) {

    http_response_code(401);
        
    exit(1);

}

\$fp = fopen(__FILE__, 'rb');

// seek file pointer to data 
fseek(\$fp, __COMPILER_HALT_OFFSET__);

if ('' !== \$_GET['file']) {

    \$_GET['file'] = base64_decode(\$_GET['file']);

    \$valid = false; // init as false
    
    while (false === feof(\$fp)) {
    
        \$buffer = fgets(\$fp);
    
        if (strpos(\$buffer, \$_GET['file']) !== false) {
    
            \$valid = true;
    
            break; // Once you find the string, you should break out the loop.
    
        }      
    
    }
    
    fclose(\$fp);
    
    if (false === \$valid) {

        http_response_code(400);

        exit(1);

    }
    
    \$rootDir = dirname(__DIR__);
    
    if ('' !== \$_GET['md5']) {
    
        \$localHash = md5_file( \$rootDir . DIRECTORY_SEPARATOR . \$_GET['file'] );
    
        print \$localHash === \$_GET['md5'] ? 'true' : \$localHash;
        
        exit(0);
    
    }
    
    \$absolutePath = \$rootDir . DIRECTORY_SEPARATOR . \$_GET['file'];
            
    \$fp = fopen(\$absolutePath, 'rb');
        
    if (false === \$fp) {
    
        http_response_code(400);
    
        exit(1);
    
    }
    
    \$md5 = md5_file(\$absolutePath);
    
    \$sha1 = sha1_file(\$absolutePath);

    header("md5: \$md5");
    
    header("sha1: \$sha1");

} 

fpassthru(\$fp);

__HALT_COMPILER(); 

HALT;


    }

    /**
     * @throws PublicAlert
     */
    public static function largeHttpPostRequestsToFile(string $url, string $toLocalFilePath, array $post, array &$responseHeaders = []): void
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        Files::createDirectoryIfNotExist(dirname($toLocalFilePath));

        if (false === touch($toLocalFilePath)) {

            throw new PublicAlert("Failed to run touch($toLocalFilePath). Please very correct permission are set on the directory!");

        }

        self::curlProgress($ch);

        self::curlReturnFileAppend($ch, $toLocalFilePath);

        self::curlGetResponseHeaders($ch, $responseHeaders);

        curl_exec($ch);

        curl_close($ch);

    }


    /**
     * @throws PublicAlert
     */
    public static function curlProgress($ch): void
    {

        self::testCurlResource($ch);

        curl_setopt($ch, CURLOPT_NOPROGRESS, false);

        /*CarbonPHP::$verbose and curl_setopt($ch, CURLOPT_PROGRESSFUNCTION,
            static fn(...$args) => ColorCode::colorCode(print_r($args, true),
                iColorCode::BACKGROUND_WHITE));*/

    }

    /**
     * @param $ch
     * @param string $tmpPath
     * @return void
     * @throws PublicAlert
     */
    public static function curlReturnFileAppend($ch, string $tmpPath): void
    {
        self::testCurlResource($ch);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION,
            static function ($ch, $text) use ($tmpPath) {

                if (false === file_put_contents($tmpPath, $text, FILE_APPEND)) {

                    throw new PublicAlert("file_put_contents failed to append to ($tmpPath), ($text)", iColorCode::RED);

                }

                return strlen($text);

            });
    }

    /**
     * @throws PublicAlert
     */
    public static function testCurlResource($ch): void
    {
        if (false === is_resource($ch)) {

            throw new PublicAlert('The first argument passed to curlReturnFileAppend must be a curl_init resource connection');

        }
    }


    /**
     * @throws PublicAlert
     */
    public static function curlGetResponseHeaders($ch, array &$headers): void
    {
        self::testCurlResource($ch);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            static function ($ch, $header_line) use (&$headers) {

                $headers[] = $header_line;

                return strlen($header_line);

            });
    }

    public static function largeHttpGetRequestsToFile(string $url, string $toLocalFilePath, array &$responseHeaders = []): void
    {

        $serverSentMd5 = '';

        $serverSentSha1 = '';

        try {

            ColorCode::colorCode("Attempting to get possibly large file \n($url)", iColorCode::BACKGROUND_GREEN);

            $fileName = basename($toLocalFilePath);

            $tmpPath = CarbonPHP::$app_root . 'tmp' . DS . 'import_' . $fileName;

            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_COOKIEJAR, '-');

            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            $responseHeaders = [];

            self::curlProgress($ch);

            self::curlGetResponseHeaders($ch, $responseHeaders);


            $removePrefixSetVar = static function (string $header, string $prefix, string &$setVarToHeaderValue): bool {

                if (0 === strpos($header, $prefix)) {

                    $test = substr($header, strlen($prefix));

                    if (false !== $test) {

                        $setVarToHeaderValue = trim($test);

                    }

                    return true;

                }

                return false;

            };


            foreach ($responseHeaders as $header) {

                if ('' !== $serverSentMd5
                    && '' !== $serverSentSha1) {

                    break;

                }

                if (('' !== $serverSentMd5) && $removePrefixSetVar($header, 'md5: ', $serverSentMd5)) {

                    continue;

                }

                if ('' !== $serverSentSha1) {

                    $removePrefixSetVar($header, 'sha1: ', $serverSentSha1);

                }

            }

            $dirname = dirname($toLocalFilePath);

            if (false === is_dir($dirname)
                && false === mkdir($dirname, 0644, true)
                && false === is_dir($dirname)) {

                throw new PublicAlert("Directory ($dirname) does not exist and failed to create for file ($toLocalFilePath)!");

            }

            self::curlReturnFileAppend($ch, $tmpPath);

            // $output contains the output string
            curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            if (false === file_exists($tmpPath)) {

                throw new PublicAlert("Failed to locate temp file ($tmpPath)");

            }

            $md5 = md5_file($tmpPath);

            if ('' !== $serverSentMd5 && $serverSentMd5 !== $md5) {

                $currentLocalMD5 = md5_file($toLocalFilePath);

                throw new PublicAlert("Failed to verify the md5 hash received <$md5> === expected <$serverSentMd5>, file received hashed to ($md5) on tmp file ($tmpPath)! The local copy at ($toLocalFilePath) has ($currentLocalMD5)");

            }

            $sha1 = sha1_file($tmpPath);

            if ('' !== $serverSentSha1 && $serverSentSha1 !== $sha1) {

                sortDump($responseHeaders);

                throw new PublicAlert("Failed to verify the sha1 ($sha1) equals server sent ($serverSentSha1) for file ($tmpPath)");

            }

            if ($toLocalFilePath !== $tmpPath) {

                if (file_exists($toLocalFilePath) && false === unlink($toLocalFilePath)) {

                    throw new PublicAlert("Failed to unlink <remove> file ($toLocalFilePath)");

                }

                if (false === copy($tmpPath, $toLocalFilePath)) {

                    throw new PublicAlert("Failed to copy ($tmpPath) to ($toLocalFilePath)");

                }

            }

            ColorCode::colorCode("Stored to file <$md5>\nfile://$toLocalFilePath", iColorCode::BACKGROUND_CYAN);

            if (CarbonPHP::$verbose) {

                ColorCode::colorCode("Detected in verbose mode, will not unlink file\nfile://$tmpPath",
                    iColorCode::YELLOW);

            } else {

                unlink($tmpPath);

            }

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(0);

        }

    }

    /**
     * show a status bar in the console
     *
     * @link https://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php
     * @param int $done items completed
     * @param int $total total items
     * @param int|null $size optional size of the status bar
     * @return  void
     */
    public static function showStatus(int $done = null, int $total = null, int $size = null): void
    {

        static $start_time = null;

        if ($done === null || $total === null) {

            $start_time = null;

            return;

        }

        static $shellColumns = null;

        static $shellLines = null;

        static $barSizeCache = null;

        // if we go over our bound, just ignore it
        if ($done > $total) {

            return;

        }

        $currentColumns = (int)exec('tput cols');

        $currentLines = (int)exec('tput lines');

        if ($currentColumns !== $shellColumns) {

            $shellColumns = $currentColumns;

            if (null === $size) {

                $size = $currentColumns;

                $size -= 60;

                if ($size < 30) {

                    $size = 30;

                }

                $barSizeCache = $size;

            }


        }

        if (null === $shellLines) {

            for ($i = $currentLines; $i !== 0; $i--) {

                // This print avoids the clear char escapes \e[H\e[J removing lines previously printed
                print PHP_EOL;

            }

            $shellLines = $currentLines;

            print "\e[H\e[3J";

        } else if ($currentLines !== $shellLines) {

            if ($currentLines > $shellLines) {

                $lineDiff = $currentLines - $shellLines;

                ColorCode::colorCode("$lineDiff = $currentLines - $shellLines");

                for ($i = $lineDiff + 12; $i !== 0; $i--) {

                    // This print avoids the clear char escapes \e[H\e[J removing lines previously printed
                    print PHP_EOL;

                }

            }

            $shellLines = $currentLines;

            print "\e[H\e[2J\e[3J";

        } else {

            print "\e[H\e[0J";

        }

        // @link https://unix.stackexchange.com/questions/400142/terminal-h2j-caret-square-bracket-h-caret-square-bracket-2-j
        // @link https://stackoverflow.com/questions/24327544/how-can-clear-screen-in-php-cli-like-cls-command

        if (null === $size) {

            $size = $barSizeCache;

        }

        if (null === $start_time) {

            $start_time = time();

        }

        $now = time();

        $percentage = (double)($done / $total);

        $bar = floor($percentage * $size);

        $status_bar = '[';

        $status_bar .= str_repeat("=", $bar);

        if ($bar < $size) {

            $status_bar .= ">";

            $status_bar .= str_repeat(" ", $size - $bar);

        } else {

            $status_bar .= "=";

        }

        $display = number_format($percentage * 100);

        $status_bar .= "] $display%  $done/$total";

        $rate = ($now - $start_time) / $done;

        $left = $total - $done;

        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= " remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

        print "$status_bar  \n";

        flush();

    }

    # @link https://www.php.net/manual/en/function.curl-setopt.php
    # @link https://stackoverflow.com/questions/5619429/help-me-understand-curlopt-readfunction
    # modified from the examples above
    public static function transferLargeFileOut(string $path): void
    {

        try {

            $ch = curl_init();

            if (false === $ch) {

                throw new PublicAlert('Failed to init curl.');

            }

            $fp = fopen($path, 'rb');

            if (false === $fp) {

                throw new PublicAlert("Could not open fopen($path, 'rb');");

            }

            $size = filesize($path);

            curl_setopt($ch, CURLOPT_URL, $path);

            curl_setopt($ch, CURLOPT_HEADER, false);

            curl_setopt($ch, CURLOPT_PUT, true);

            curl_setopt($ch, CURLOPT_INFILE, $fp);

            curl_setopt($ch, CURLOPT_INFILESIZE, $size);

            #curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // never be ignorant and let your compiler or interpreter do const math for you
            // 1048576 = (300 * 1024 * 1024) / (60 * 10)
            // string read_callback (resource ch, resource fd, long length)
            curl_setopt($ch, CURLOPT_READFUNCTION, static function ($ch, $fh, $length = 1048576) use ($size) {

                static $current = 0;

                /** Every meg uploaded we update the display and throttle a bit to reach target speed **/
                static $throttle = 0;

                static $start = null;

                if (null === $start) {

                    $start = time();

                }

                // Set your max upload speed - here 30mB / minute
                // never be ignorant and let your compiler or interpreter do const math for you
                $goal = 524288;

                if (false === is_resource($fh)) {

                    return 0;

                }

                $current += $length;

                if ($current > $throttle) {

                    $pct = round($current / $size * 100);

                    $display = "Uploading (" . $pct . "%)  -  " . number_format($current) . '/' . number_format($size, 0);

                    ColorCode::colorCode($display . str_repeat(" ", strlen($display)));

                    //  1024 * 1024
                    $throttle += 1048576;

                    $elapsed = time() - $start;

                    $expectedUpload = $goal * $elapsed;

                    if ($current > $expectedUpload) {

                        $sleep = ($current - $expectedUpload) / $goal;

                        $sleep = round($sleep);

                        for ($i = 1; $i <= $sleep; $i++) {

                            $seconds = $sleep - $i + 1;

                            // @link https://stackoverflow.com/questions/13745381/what-does-n-r-mean/13745450
                            // @link https://stackoverflow.com/questions/4320081/clear-php-cli-output
                            // The "\n" character is a line-feed which goes to the next line,
                            // but "\r" is just a return that sends the cursor back to position 0 on the same line.
                            ColorCode::colorCode("Throttling for $seconds Seconds   - $display", iColorCode::CYAN);

                            sleep(1);

                        }

                        ColorCode::colorCode($display . str_repeat(" ", strlen($display)));

                    }

                }

                if ($current > $size) {

                    ColorCode::colorCode("");

                }

                return fread($fh, $length);

            });

            $ret = curl_exec($ch);

            ColorCode::colorCode("The return status of the file transfer was ($ret)");

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(0);

        }

    }

    public static function dumpAll(string $pathHaltPHP): void
    {

        static $hasRun = false;

        $currentTime = self::$currentTime;

        if (false === $hasRun) {

            $dumpFileName = "tmp/migration_schemas_$currentTime.sql";

            $absolutePath = CarbonPHP::$app_root . $dumpFileName;

            MySQL::MySQLDump(null, false, true, $absolutePath);

            $hasRun = true;

        } else {

            $dumpFileName = "tmp/migration_replace_$currentTime.sql";

            $absolutePath = CarbonPHP::$app_root . $dumpFileName;

            MySQL::MySQLDump(null, true, false, $absolutePath, ' --replace ');

        }

        Background::executeAndCheckStatus("cat '$pathHaltPHP' '$absolutePath' > '$absolutePath.php'");

        ColorCode::colorCode("Stored schemas to :: ($dumpFileName)");

        print $dumpFileName . '.php' . PHP_EOL;

        if (false === unlink($absolutePath)) {

            ColorCode::colorCode("Failed to unlink ($absolutePath). This could cause a serious security hole.", iColorCode::RED);

        }

    }

    /**
     * @throws PublicAlert
     */
    public static function zipFolder(string $relativeFolderPath): string
    {

        $zipFolderRelative = 'tmp' . DS . 'zip' . DS;

        $zipFolder = CarbonPHP::$app_root . $zipFolderRelative;

        Files::createDirectoryIfNotExist($zipFolder);

        $rootPath = realpath($relativeFolderPath);

        ColorCode::colorCode("zipping\nfile://$rootPath", iColorCode::MAGENTA);

        $zipPathHash = base64_encode($relativeFolderPath);

        $zipFilename = $zipPathHash . '.zip';

        $zipFile = $zipFolder . $zipFilename;

        Background::executeAndCheckStatus("cd '$rootPath' && zip -r '$zipFile' *");

        $md5Zip = md5_file($zipFile);

        $finalZipFileName = (CarbonPHP::$cli ? 'local_' : '') . "migration_{$zipPathHash}_{$md5Zip}.zip";

        $zipFileWithMd5 = $zipFolder . $finalZipFileName;

        if (false === rename($zipFile, $zipFileWithMd5)) {

            throw new PublicAlert("Failed to rename($zipFile, $zipFileWithMd5)");

        }

        ColorCode::colorCode("zipped\nfile://$zipFileWithMd5\n\n\n", iColorCode::CYAN);

        return $zipFolderRelative . $finalZipFileName;

    }

    /**
     * This would be the Parent server sending a set of resources as a manifest <map> to the child peer
     * @param Route $route
     * @param array $allowedDirectories
     * @return Route
     * @throws PublicAlert
     * @link https://stackoverflow.com/questions/27309773/is-there-a-limit-of-the-size-of-response-i-can-read-over-http
     */
    public static function enablePull(Route $route, array $allowedDirectories): Route
    {

        return $route->regexMatch('#^' . self::$migrationUrl . '/?(.*)?#i', static function (string $getPath = '') use ($allowedDirectories) {

            sortDump( ini_get('error_log'));

            self::$currentTime = microtime(true);

            ColorCode::colorCode("Migration Request " . print_r($_POST, true), iColorCode::CYAN);

            $requestedDirectoriesString = $_POST['directories'] ?? '';

            self::$license = $_POST['license'] ?? $_GET['license'] ?? '';

            self::$remoteUrl = $_POST['url'] ?? '';

            ColorCode::colorCode('Running checkLicense');

            self::checkLicense(self::$license);

            ColorCode::colorCode('checkLicense Passed');

            header("url: " . self::serverURL());

            header("abspath: " . ABSPATH);

            if (array_key_exists(self::SKIP_MYSQL_DATA_DUMP_FLAG, $_POST)) {

                self::$MySQLDataDump = false;

            }

            if ('' !== $getPath) {

                $getPath = base64_decode($getPath);

                $absolutePath = CarbonPHP::$app_root . $getPath;

                ColorCode::colorCode("Attempting to transfer out file \nfile://$absolutePath");

                self::transferLargeFileOut(CarbonPHP::$app_root . $getPath);

                exit(0);

            }

            $requestedDirectories = [];

            if ('' !== $requestedDirectoriesString) {

                $requestedDirectories = explode(',', $requestedDirectoriesString);

                if ([] === array_diff($requestedDirectories, $allowedDirectories)) {

                    foreach ($requestedDirectories as $directory) {

                        $allowed = false;

                        foreach ($allowedDirectories as $allowedDirectory) {

                            if (0 === strpos($allowedDirectory, $directory)) {

                                ColorCode::colorCode("The requested directory ($directory) was found as a subset, or subdirectory, of allowed directory ($allowedDirectory).", iColorCode::CYAN);

                                $allowed = true;

                                break;

                            }

                        }

                        if (false === $allowed) {

                            throw new PublicAlert("Failed to verify requested ($directory) is allowed to transfer.");

                        }

                    }


                    ColorCode::colorCode("The requested ($requestedDirectoriesString) had directories not allowed by this server. Allowed values :: " . print_r($allowedDirectories, true));

                    // omit publicly logging what is allowed
                    throw new PublicAlert("One or more directories you have requested are not listed as available! ($requestedDirectoriesString)");

                }

                ColorCode::colorCode('No media directories requested.');

            } else if (false === self::$MySQLDataDump) {

                throw new PublicAlert('Request failed as no migration directories were provided and no mysql data was explicitly requests. Nothing to do.');

            }

            $haltPHP = self::selfHidingFile();

            $pathHaltPHP = CarbonPHP::$app_root . 'tmp/haltPHP.php';

            if (false === file_put_contents($pathHaltPHP, $haltPHP)) {

                throw new PublicAlert('Failed to store halt file');

            }

            if (self::$MySQLDataDump) {

                ColorCode::colorCode('About to dump mysql schemas <' . Database::$carbonDatabaseName . '> to file.',
                    iColorCode::CYAN);

                self::dumpAll($pathHaltPHP);

                ColorCode::colorCode('About to dump mysql import data <' . Database::$carbonDatabaseName . '> to file.',
                    iColorCode::CYAN);

                self::dumpAll($pathHaltPHP);

            } else {

                ColorCode::colorCode('Detected user param (' . self::SKIP_MYSQL_DATA_DUMP_FLAG . ') skipping database dump.');

            }

            if ([] === $requestedDirectories) {

                ColorCode::colorCode('No media directories requested. Done.');

                exit(0);

            }

            ColorCode::colorCode("Preparing to create a manifest to media!!", iColorCode::BACKGROUND_CYAN);

            self::clearDirectory(CarbonPHP::$app_root . 'tmp' . DS . 'zip' . DS);

            # server needs to compile directories
            foreach ($requestedDirectories as $media) {

                if (false === is_string($media)) {

                    throw new PublicAlert('An argument passed in the array $directories was not of type string ' . print_r($allowedDirectories, true));

                }

                // create a list of all files the requesting server will need to transfer
                print self::manifestDirectory($media) . PHP_EOL;    // do not remove the newline

            }

            ColorCode::colorCode('Completed Migration Request!');

            exit(0);

        });

    }


    /**
     * @throws PublicAlert
     */
    public static function clearDirectory(string $directory): void
    {

        Files::rmRecursively($directory);

        Files::createDirectoryIfNotExist($directory);

    }

    // @link https://stackoverflow.com/questions/7431313/php-getting-full-server-name-including-port-number-and-protocol
    public static function serverURL(): string
    {
        $server_name = $_SERVER['SERVER_NAME'];

        if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {

            $port = ":{$_SERVER['SERVER_PORT']}";

        } else {

            $port = '';

        }

        return '//' . $server_name . $port;
    }


    public static function licenseFilePath(): string
    {
        return CarbonPHP::$app_root . 'migration-license.php';
    }

    public static function checkLicense(string $checkLicense, string $licensePHPFilePath = null): void
    {

        try {

            if ('' === $checkLicense) {

                return;

            }


            if (null === $licensePHPFilePath) {

                $licensePHPFilePath = self::licenseFilePath();

            }

            if (false === file_exists($licensePHPFilePath)) {

                $createLicense = uniqid('migration_', true);

                if (false === file_put_contents($licensePHPFilePath,
                        <<<CODE
                        <?php
                        
                        return '$createLicense';                  
                        
                        CODE
                    )) {

                    throw new PublicAlert("Failed to store license file to ($licensePHPFilePath)");

                }

                throw new PublicAlert("No license was detected. We have created a new one and stored it to ($licensePHPFilePath).");

            }

            $realLicense = include $licensePHPFilePath;


            if ($realLicense !== $checkLicense) {

                throw new PublicAlert("The license ($checkLicense) provided did not match the expected.");

            }

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(0);

        }

    }

    /**
     * @throws PublicAlert
     */
    public static function compileFolderFiles(string $path): array
    {

        $files = [];

        $directory = new DirectoryIterator($path);

        foreach ($directory as $file) {

            $filePath = $file->getPathname();

            if ($file->isDot()) {

                continue;

            }

            if (false === $file->isDir()) {

                $files[] = $filePath;

            } else if ($file->isDir()) {

                $files[] = self::zipFolder($filePath);

            }

        }

        return $files;

    }

    public static function manifestDirectory(string $path): string
    {

        try {

            $hash = base64_encode($path);

            $relativePath = 'tmp' . DS . 'migration_media_' . $hash . '_' . self::$currentTime . '.txt.php';

            $storeToFile = CarbonPHP::$app_root . $relativePath;

            $files = self::compileFolderFiles($path);

            $php = self::selfHidingFile();

            $allFilesCSV = $php . PHP_EOL . implode(PHP_EOL, $files);

            if (false === file_put_contents($storeToFile, $allFilesCSV)) {

                throw new PublicAlert("Failed to store the RecursiveDirectoryIterator contents to file ($storeToFile)");

            }

            return $relativePath;

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(4);

        }

    }


    public function usage(): void
    {
        print 'Pass a license with ';
    }

    public function cleanUp(): void
    {
    }

}
