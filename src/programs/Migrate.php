<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Programs\Background;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Programs\MySQL;
use CarbonPHP\Route;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileObject;
use Throwable;

class Migrate implements iCommand
{
    public static string $migrationUrl = 'migration';

    public static float $currentTime;

    public static ?string $license = null;

    public static ?string $url = null;

    public static ?string $directories = null;

    public static bool $throttle = false;

    // todo - turn to false
    public static bool $verbose = false;

    public const SKIP_MYSQL_DATA_DUMP_FLAG = '--no-dump-data';

    public static bool $MySQLDataDump = true;

    public static function unlinkMigrationFiles(): void
    {

        $updateCount = 0;

        $migrationFiles = glob(CarbonPHP::$app_root . "tmp/*migration*");

        foreach ($migrationFiles as $file) {

            try {

                unlink($file);

                self::$verbose and ColorCode::colorCode('unlinked (' . $file . ')');

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

        ColorCode::colorCode('Oh Ya! MigrateMySQL Has Started!');

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0, $argc = count($argv); $i < $argc; $i++) {

            switch ($argv[$i]) {
                case '--verbose':

                    self::$verbose = true;

                    break;

                case '--license':

                    self::$license = $argv[++$i] ?? '';

                    break;

                case '--url':

                    self::$url = $argv[++$i] ?? '';

                    self::$verbose and ColorCode::colorCode('CLI found flag set for URL (' . self::$url . ')');

                    break;

                case self::SKIP_MYSQL_DATA_DUMP_FLAG:

                    self::$MySQLDataDump = false;

                    break;

                case '--directories':

                    self::$directories = $argv[++$i] ?? '';

                    self::$verbose and ColorCode::colorCode('CLI found request directories flag (' . self::$directories . ')');

                    break;

                default:

                    ColorCode::colorCode("Unrecognized cli argument ($argv[$i]) failing.", iColorCode::BACKGROUND_RED);

                    exit(1);

            }
        }

        self::unlinkMigrationFiles();

        $postData = [
            'license' => self::$license,
            'url' => self::$url
        ];

        if (null !== self::$directories) {

            $postData += [
                'directories' => self::$directories
            ];

        }

        if (false === self::$MySQLDataDump) {

            $postData += [
                self::SKIP_MYSQL_DATA_DUMP_FLAG => true
            ];

        }

        $localManifestPath = CarbonPHP::$app_root . 'tmp/local_migration_manifest.txt';

        self::largeHttpPostRequestsToFile(self::$url . self::$migrationUrl, $localManifestPath, $postData);

        $manifest = file_get_contents($localManifestPath);

        if (0 === strpos($manifest, '<html lang="en">')) {

            throw new PublicAlert("The manifest download detected an html document ($localManifestPath). A new line delimited list of files is expected. This is an error.");

        }

        if (empty($manifest)) {

            throw new PublicAlert('Failed to get the file manifest from the remote server!');

        }

        ColorCode::colorCode("Manifest\n" . $manifest);

        $explodeManifest = explode(PHP_EOL, $manifest);

        $manifest = [];

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

                self::largeHttpGetRequestsToFile(self::$url . $uri . '?license=' . self::$license, $importManifestFilePath);

                $manifest[$uri] = $importManifestFilePath;

            }

        }

        ColorCode::colorCode("Beginning Media Migration!", iColorCode::CYAN);

        sleep(2);

        foreach ($manifest as $uri => $importFileAbsolutePath) {

            self::$verbose and ColorCode::colorCode($importFileAbsolutePath, iColorCode::MAGENTA);

            self::importManifestFile($importFileAbsolutePath, $uri);

        }

        ColorCode::colorCode('Success.');

        exit(0);

    }

    // @link https://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
    public static function getLineCount($filePath): int
    {

        $file = new SplFileObject($filePath, 'rb');

        $file->seek(PHP_INT_MAX);

        return $file->key();

    }

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

            while (false === feof($fp)) {

                self::showStatus(++$count, $lineCount);

                $mediaFile = fgets($fp, 1024);

                $mediaFile = trim($mediaFile);

                if ('' === $mediaFile) {

                    continue;

                }

                if (true === self::$throttle) {

                    $getMetaUrl = self::$url . self::$migrationUrl . '/' . base64_encode($mediaFile) . '?license=' . self::$license;

                } else {

                    $getMetaUrl = self::$url . $uri . '?license=' . self::$license . '&file=' . base64_encode($mediaFile);

                }

                $localPath = CarbonPHP::$app_root . $mediaFile;

                if (true === file_exists($localPath)) {

                    $hash = md5_file($localPath);

                    $url = "$getMetaUrl&md5=$hash";

                    ColorCode::colorCode("Local file ($localPath) exists, testing hash ($hash) with ur ($url)!", iColorCode::BACKGROUND_WHITE);

                    $updateStatus = file_get_contents($url);

                    $updateStatus = trim($updateStatus);

                    if ('true' === $updateStatus) {

                        ColorCode::colorCode("No updates need for ($localPath)");

                        continue;

                    }

                    self::$verbose and ColorCode::colorCode("MD5 remote server check status ($updateStatus)", iColorCode::BACKGROUND_YELLOW);


                }

                ColorCode::colorCode("Updates needed <$hash>($localPath)", iColorCode::BACKGROUND_CYAN);

                ColorCode::colorCode($mediaFile, $color ? iColorCode::BACKGROUND_GREEN : iColorCode::BACKGROUND_CYAN);

                $color = !$color;

                self::largeHttpGetRequestsToFile($getMetaUrl, $localPath);

            }

            self::showStatus($count, $lineCount);

            fclose($fp);

            self::$verbose and ColorCode::colorCode('Done.');

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

            exit(1);

        }

    }

    /**
     * @throws PublicAlert
     */
    public static function importManifestFile(string $file, string $uri): void
    {

        self::$verbose and ColorCode::colorCode("Importing file ($file)");

        $info = pathinfo($file);

        switch ($info['extension']) {

            case 'txt':

                self::$verbose and ColorCode::colorCode("Import manifest media (file://$file)", iColorCode::YELLOW);

                self::importMedia($file, $uri);

                break;

            case 'sql':

                if (self::$MySQLDataDump) {

                    self::$verbose and ColorCode::colorCode("Doing an update to Mysql! (file://$file)");

                    MySQL::MySQLSource($file);

                    break;

                }

                throw new PublicAlert("A MySQL dump file ($file) was found though the " . self::SKIP_MYSQL_DATA_DUMP_FLAG . " was set.");

        }

        if (self::$verbose) {

            ColorCode::colorCode("The verbose flag will cause the migration file to not be unlinked (deleted)",
                iColorCode::BACKGROUND_RED);

        } else {

            unlink($file);

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
    
    while (false === feof(\$fp) && false === \$valid) {
    
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

    header("md5: \$md5");

} 

fpassthru(\$fp);

__HALT_COMPILER(); 

HALT;


    }

    /**
     * @throws PublicAlert
     */
    public static function largeHttpPostRequestsToFile(string $url, string $toLocalFilePath, array $post): void
    {

        //
        // A very simple PHP example that sends a HTTP POST to a remote site
        //
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($post));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        self::curlReturnFileAppend($ch, $toLocalFilePath);

        curl_exec($ch);

        curl_close($ch);


    }

    /**
     * @param $ch
     * @param string $tmpPath
     * @return void
     * @throws PublicAlert
     */
    public static function curlReturnFileAppend($ch, string $tmpPath): void
    {

        if (false === is_resource($ch)) {

            throw new PublicAlert('The first argument passed to curlReturnFileAppend must be a curl_init resource connection');

        }

        curl_setopt($ch, CURLOPT_WRITEFUNCTION,
            static function ($ch, $text) use ($tmpPath) {

                if (false === file_put_contents($tmpPath, $text, FILE_APPEND)) {

                    throw new PublicAlert("file_put_contents failed to append to ($tmpPath), ($text)", iColorCode::RED);

                }

                return strlen($text);

            });
    }


    public static function largeHttpGetRequestsToFile(string $url, string $toLocalFilePath): void
    {

        $serverSentMd5 = '';

        try {

            ColorCode::colorCode("Attempting to get possibly large file \n($url)", iColorCode::BACKGROUND_GREEN);

            $fileName = basename($toLocalFilePath);

            $tmpPath = CarbonPHP::$app_root . 'tmp' . DS . $fileName;

            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                static function ($ch, $header_line) use (&$serverSentMd5) {

                    $prefix = 'md5: ';

                    if (0 === strpos($header_line, $prefix)) {

                        $testMd5 = substr($header_line, strlen($prefix));

                        if (is_string($testMd5)) {

                            $serverSentMd5 = trim($testMd5);

                        }

                    }

                    return strlen($header_line);

                });

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

            $md5 = md5_file($tmpPath);

            if ('' !== $serverSentMd5 && $serverSentMd5 !== $md5) {

                $currentLocalMD5 = md5_file($toLocalFilePath);

                throw new PublicAlert("Failed to verify the md5 hash received <$md5> === expected <$serverSentMd5>, file received hashed to ($md5) on tmp file ($tmpPath)! The local copy at ($toLocalFilePath) has ($currentLocalMD5)");

            }

            if ($toLocalFilePath !== $tmpPath) {

                if (file_exists($toLocalFilePath) && false === unlink($toLocalFilePath)) {

                    throw new PublicAlert("Failed to unlink <remove> file ($toLocalFilePath)");

                }

                if (false === copy($tmpPath, $toLocalFilePath)) {

                    throw new PublicAlert("Failed to copy ($tmpPath) to ($toLocalFilePath)");

                }

            }

            ColorCode::colorCode("Stored to file <$md5>\n(file://$toLocalFilePath)", iColorCode::BACKGROUND_CYAN);

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

        $currentColumns = (int) exec('tput cols');

        $currentLines = (int) exec('tput lines');

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

    public static function verifyRequestedDirectories()
    {

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

            $requestedDirectoriesString = $_POST['directories'] ?? '';

            self::$license = $_POST['license'] ?? $_GET['license'] ?? '';

            self::$url = $_POST['url'] ?? '';

            self::checkLicense(self::$license);

            if (array_key_exists(self::SKIP_MYSQL_DATA_DUMP_FLAG, $_POST)) {

                self::$MySQLDataDump = false;

            }

            if ('' !== $getPath) {

                $getPath = base64_decode($getPath);

                $absolutePath = CarbonPHP::$app_root . $getPath;

                ColorCode::colorCode("Attempting to transfer out file (file://$absolutePath)");

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

                if (false === self::$MySQLDataDump) {

                    throw new PublicAlert('Request failed as no migration directories were provided and no mysql data was explicitly requests. Nothing to do.');

                }

            }

            self::$currentTime = $currentTime = microtime(true);

            $haltPHP = self::selfHidingFile();

            $pathHaltPHP = CarbonPHP::$app_root . 'tmp/haltPHP.php';

            if (false === file_put_contents($pathHaltPHP, $haltPHP)) {

                throw new PublicAlert('Failed to store halt file');

            }

            if (self::$MySQLDataDump) {

                self::dumpAll($pathHaltPHP);

                self::dumpAll($pathHaltPHP);

            }

            if ([] === $requestedDirectories) {

                ColorCode::colorCode('No media directories requested. Done.');

                exit(0);

            }

            foreach ($requestedDirectories as $media) {

                if (false === is_string($media)) {

                    throw new PublicAlert('An argument passed in the array $directories was not of type string ' . print_r($allowedDirectories, true));

                }

                $hash = md5($media);

                $customPathName = 'tmp/migration_media_' . $hash . '_' . $currentTime . '.txt';

                // create a list of all files the requesting server will need to transfer
                self::manifestDirectory($media, $customPathName);

                print $customPathName . PHP_EOL;

            }

            exit(0);

        });

    }

    public static function checkLicense(string $checkLicense, string $licensePHPFilePath = null): void
    {

        try {

            if ('' === $checkLicense) {

                return;

            }


            if (null === $licensePHPFilePath) {

                $licensePHPFilePath = CarbonPHP::$app_root . 'migration-license.php';

            }

            if (false === file_exists($licensePHPFilePath)) {

                $createLicense = uniqid('migration_', true);

                if (false === file_put_contents($licensePHPFilePath,
                        <<<CODE
                        <?php
                        
                        return "$createLicense";                  
                        
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

    public static function manifestDirectory(string $path, string &$storeToFile): void
    {

        try {

            $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

            $iterator = new RecursiveIteratorIterator($directory);

            $files = [];

            foreach ($iterator as $file) {

                if (false === $file->isDir()) {

                    $prefix = CarbonPHP::$app_root;

                    $str = $file->getPathname();

                    if (strpos($str, $prefix) === 0) {

                        $str = substr($str, strlen($prefix));

                    }

                    $files[] = $str;

                }

            }

            $php = self::selfHidingFile();

            $allFilesCSV = $php . PHP_EOL . implode(PHP_EOL, $files);

            $storeToFile .= '.php';

            if (false === file_put_contents(CarbonPHP::$app_root . $storeToFile, $allFilesCSV)) {

                throw new PublicAlert("Failed to store the RecursiveDirectoryIterator contents to file ($storeToFile)");

            }

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

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
