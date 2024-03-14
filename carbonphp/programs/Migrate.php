<?php

namespace CarbonPHP\Programs;

use CarbonPHP\Abstracts\Background;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Cryptography;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Route;
use CurlHandle;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileObject;
use Throwable;

class Migrate implements iCommand
{
    public static string $migrationUrl = 'c6migration';

    public static string $migrationFolder = 'tmp';

    public static string $migrationFolderPrefix = 'migration_';

    public static float $currentTime;

    public static ?float $remoteServerTime = null;

    public static ?string $license = null;

    public static ?string $localUrl = null;

    public static ?string $remoteUrl = null;

    public static ?string $remoteAbsolutePath = null;

    public static ?string $directories = null;

    public static bool $throttle = false;

    public const SKIP_MYSQL_DATA_DUMP_FLAG = '--no-dump-data';

    public const MIGRATE_DIRECTORIES_FLAG = '--directories';

    public static bool $MySQLDataDump = true;

    public static int $timeout = 180;

    public static int $maxFolderSizeForCompressionInMb = 500;

    public static array $childProcessIds = [];


    public static function description(): string
    {
        return 'Migrate your project database and files from one server, or location, to another.';
    }

    /**
     * @throws PublicAlert
     */
    public static function directorySizeLessThan(string $path, int $megabytes): bool
    {

        $bytesMax = 1000000 * $megabytes;

        $bytesTotal = 0;

        $path = realpath($path);

        if ($path === false || false === is_dir($path)) {

            throw new PrivateAlert("Failed to verify that dir (file://$path) exists!");

        }

        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));

        foreach ($dir as $object) {

            $bytesTotal += $object->getSize();

            if ($bytesMax < $bytesTotal) {

                ColorCode::colorCode("The directory (file://$path) is to large (over $megabytes mb), moving to subdirectory to ZIP!");

                return false;

            }

        }

        return true;

    }

    public static function unlinkMigrationFiles(): void
    {
        self::$currentTime ??= microtime(true);

        $updateCount = 0;

        $migrationFiles = glob(CarbonPHP::$app_root . "cache/*migration*");

        foreach ($migrationFiles as $migrationFolder) {

            if (false === is_dir($migrationFolder)) {

                continue;

            }

            $migrationFolderExploded = explode(DIRECTORY_SEPARATOR, $migrationFolder);

            $migrationFolderName = end($migrationFolderExploded);

            $migrationTime = (float)substr($migrationFolderName, strlen(self::$migrationFolderPrefix));

            if (self::$currentTime - $migrationTime < 86400) {

                continue; // less than 24 hours old

            }

            try {

                Background::executeAndCheckStatus("rm -rf $migrationFolder");

                CarbonPHP::$verbose and ColorCode::colorCode('unlinked (' . $migrationFolder . ')');

            } catch (Throwable $e) {

                ThrowableHandler::generateLog($e);

            } finally {

                $updateCount++;

            }

        }

        ColorCode::colorCode('Removed (' . $updateCount . ') old migration files!');

    }

    public static function secondsToReadable(int $init): string
    {

        $hours = floor($init / 3600);

        $minutes = floor(($init / 60) % 60);

        $seconds = $init % 60;

        return "$hours:$minutes:$seconds";

    }

    /**
     * @throws PublicAlert
     * @throws \JsonException
     */
    public function run(array $argv): void
    {

        self::$currentTime = microtime(true);

        ColorCode::colorCode('Oh Ya! MigrateMySQL Has Started!');

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0, $argc = count($argv); $i < $argc; $i++) {

            switch ($argv[$i]) {
                case '--timeout':

                    self::$timeout = $argv[++$i];

                    break;

                case '--max-folder-size-to-compress-mb':

                    self::$maxFolderSizeForCompressionInMb = $argv[++$i];

                    break;

                case '--verbose':

                    CarbonPHP::$verbose = true;

                    break;

                case '--license':

                    self::$license = $argv[++$i] ?? '';

                    break;

                case '--local-url':

                    self::$localUrl = $argv[++$i] ?? '';

                    $pattern = '#^http(s)?://.*/$#';

                    if (1 !== preg_match($pattern, self::$localUrl)) {

                        throw new PrivateAlert("The url failed to match the regx ($pattern) with given --local-url argument. (" . self::$localUrl . ") given.");

                    }

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found flag set for local URL (' . self::$localUrl . ')');

                    break;

                case '--remote-url':

                    self::$remoteUrl = $argv[++$i] ?? '';

                    $pattern = '#^http(s)?://.*/$#';

                    if (1 !== preg_match($pattern, self::$remoteUrl)) {

                        throw new PrivateAlert("The url failed to match the regx ($pattern) with given --remote-url argument; (" . self::$remoteUrl . ") given.");

                    }

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

                    $this->usage();

                    exit(1);

            }
        }

        $this->getLicense();

        if (null === self::$localUrl || null === self::$remoteUrl) {

            $this->usage();

            ColorCode::colorCode('The local and remote url must be passed to the migration command!', iColorCode::BACKGROUND_RED);

            exit(2);

        }

        self::unlinkMigrationFiles();

        $postData = [];


        if (null === self::$directories && false === self::$MySQLDataDump) {

            ColorCode::colorCode("You have specified nothing to migrate! When the flag (" . self::SKIP_MYSQL_DATA_DUMP_FLAG . ') is active you must also include (' . self::MIGRATE_DIRECTORIES_FLAG . ')',
                iColorCode::BACKGROUND_RED);

            exit(1);

        }

        $noMedia = null === self::$directories;

        if (false === $noMedia) {

            $postData['directories'] = self::$directories;

        }

        $requestedDirectoriesLocalCopyInfo = [];

        // todo - this is the perfect thing to do in the background
        if (null !== self::$directories) {

            $requestedDirectories = explode(',', self::$directories);

            foreach ($requestedDirectories as $media) {

                // todo - did this deprecate on accident?
                // create a list of all files the requesting server will need to transfer
                $requestedDirectoriesLocalCopyInfo += self::compileFolderFiles($media);

            }

        }

        if (false === self::$MySQLDataDump) {

            $postData += [
                self::SKIP_MYSQL_DATA_DUMP_FLAG => true
            ];

        }

        $localManifestPath = CarbonPHP::$app_root . self::$migrationFolder . DS . 'local_migration_manifest.txt';

        $responseHeaders = [];

        $manifestURL = self::$remoteUrl . self::$migrationUrl;

        ColorCode::colorCode("Attempting to get manifest at url ($manifestURL)");

        self::largeHttpPostRequestsToFile($manifestURL, $localManifestPath, $postData, $responseHeaders);

        ColorCode::colorCode('About to look for ABSPATH header');

        $absolutePathHeader = 'abspath: ';

        foreach ($responseHeaders as $header) {

            if (str_starts_with($header, $absolutePathHeader)) {

                self::$remoteAbsolutePath = trim(substr($header, strlen($absolutePathHeader)));

                break;

            }

        }

        if (false === file_exists($localManifestPath)) {

            ColorCode::colorCode("Failed to get manifest from remote server!\n(file://$localManifestPath)", iColorCode::BACKGROUND_RED);

            exit(7);

        }

        $manifest = fopen($localManifestPath, 'rb');

        $firstImport = fgets($manifest);

        $position = strpos($firstImport, self::$migrationFolderPrefix);

        self::$remoteServerTime = (float)substr($firstImport, $position + strlen(self::$migrationFolderPrefix), strlen((string)microtime(true)));

        if (null === self::$remoteServerTime) {

            ColorCode::colorCode("Failed to parse remote server time from headers!\n" . print_r($header, true), iColorCode::BACKGROUND_RED);

            exit(8);

        }

        $importFolderLocation = CarbonPHP::$app_root . self::$migrationFolder . DS . self::$migrationFolderPrefix . self::$remoteServerTime . DS;

        Files::createDirectoryIfNotExist($importFolderLocation);

        $newLocalManifestPath = $importFolderLocation . 'local_migration_manifest.txt';

        if (false === rename($localManifestPath, $newLocalManifestPath)) {

            throw new PrivateAlert("Failed to rename local manifest file ($localManifestPath) to ($newLocalManifestPath)");

        }

        $localManifestPath = $newLocalManifestPath;

        $manifestLineCount = self::getLineCount($localManifestPath);

        // todo - this could be  bottle neck and should be processed one at a time
        $manifest = fopen($localManifestPath, 'rb');

        if (false === $manifest) {

            throw new PrivateAlert("Failed to open file pointer to ($localManifestPath)");

        }

        echo "Manifest Line Count: $manifestLineCount\nFirst line: " . fgets($manifest) . "\n";

        if (null === self::$remoteAbsolutePath) {

            throw new PrivateAlert('Failed to parse the absolute path header from the remote server! (' . print_r($responseHeaders, true) . ')');

        }

        if (empty($manifest)) {

            throw new PrivateAlert('Failed to get the file manifest from the remote server!');

        }

        rewind($manifest);

        $manifestArray = [];

        $done = 0;

        // Client
        // a list of instructional manifest files has been stored on the peer.. lets retrieve this info
        // todo - if one was to make this parallel this loop would be the place to do so
        // todo - note network io is a limiting factor in this loop
        // @link https://stackoverflow.com/questions/10198844/waiting-for-all-pids-to-exit-in-php

        while (false === feof($manifest)) {

            $uri = trim(fgets($manifest));

            if (false === empty($uri)) {

                $importManifestFilePath = $uri;

                $prefix = 'cache/';

                if (str_starts_with($importManifestFilePath, $prefix)) {

                    $importManifestFilePath = substr($uri, strlen($prefix));

                }

                $importManifestFilePath = CarbonPHP::$app_root . 'cache/' . $importManifestFilePath;

                $importManifestFilePath = rtrim($importManifestFilePath, '.ph');

                self::largeHttpPostRequestsToFile(self::$remoteUrl . $uri, $importManifestFilePath, []);

                self::showStatus(++$done, $manifestLineCount);

                $manifestArray[$uri] = $importManifestFilePath;

            } else {

                --$manifestLineCount;

            }

        }

        // todo - we need to NOT download zips unless needed
        $done = 0;

        $manifestArrayCount = count($manifestArray);

        foreach ($manifestArray as $uri => $importFileAbsolutePath) {

            self::showStatus(++$done, $manifestArrayCount);

            CarbonPHP::$verbose and ColorCode::colorCode($importFileAbsolutePath, iColorCode::MAGENTA);

            self::importManifestFile($importFileAbsolutePath, $uri);

        }

        ColorCode::colorCode('Completed in ' . (microtime(true) - self::$currentTime) . ' sec');

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

                throw new PrivateAlert("Failed to locate migration import ($file)");

            }

            $lineCount = self::getLineCount($file);

            $fp = fopen($file, 'rb');

            if (false === $fp) {

                throw new PrivateAlert("Failed to open file pointer to ($file)");

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

                $localPath = CarbonPHP::$app_root . $mediaFile;

                // todo - if media file is a directory then we need to recursively create said directory.. it will remain empty
                if (DS === $mediaFile[-1]) {

                    Files::createDirectoryIfNotExist($localPath);

                    continue;

                }

                if (true === self::$throttle) {

                    $getMetaUrl = self::$remoteUrl . self::$migrationUrl . '/' . base64_encode($mediaFile) . '?license=' . self::$license;

                } else {

                    $getMetaUrl = self::$remoteUrl . $uri . '?license=' . self::$license . '&file=' . base64_encode($mediaFile);

                }

                if (true === file_exists($localPath)) {

                    $hash = md5_file($localPath);

                    $url = "$getMetaUrl&md5=$hash";

                    ColorCode::colorCode("local copy\n(file://$localPath) exists, testing hash ($hash) with url\n($url)!",
                        iColorCode::BACKGROUND_WHITE);

                    $updateStatus = file_get_contents($url);

                    $updateStatus = trim($updateStatus);

                    if ('true' === $updateStatus) {

                        ColorCode::colorCode("No updates need for (file://$localPath)");

                        continue;

                    }

                    CarbonPHP::$verbose and ColorCode::colorCode("MD5 remote server check status ($updateStatus)", iColorCode::BACKGROUND_YELLOW);

                }

                if (CarbonPHP::$verbose) {

                    ColorCode::colorCode("Updates needed <$hash>($localPath)", iColorCode::BACKGROUND_CYAN);

                    ColorCode::colorCode($mediaFile, $color ? iColorCode::BACKGROUND_GREEN : iColorCode::BACKGROUND_CYAN);

                }

                $color = !$color;

                $localUpdates[] = 'file://' . $localPath;

                $networkCount = 3;

                $failed = false;

                while ($networkCount--) {

                    if ($networkCount < 2) {

                        ColorCode::colorCode("Retrying \n($getMetaUrl) to local path\n(file://$localPath)",
                            iColorCode::BACKGROUND_YELLOW);

                    }

                    self::largeHttpPostRequestsToFile($getMetaUrl, $localPath);

                    if (1 === preg_match('/zip$/', $localPath)) {

                        $zipFileName = basename($localPath);

                        if (CarbonPHP::$verbose) {

                            ColorCode::colorCode("Exploding ($localPath)", iColorCode::YELLOW);

                        }

                        [, $path, $md5] = explode('_', $zipFileName);

                        [$md5,] = explode('.', $md5);   // remove the .zip suffix

                        $unzipToPath = base64_decode($path);

                        $downloadedMd5 = md5_file($localPath);

                        if ($downloadedMd5 !== $md5) {

                            $failed = true;

                            ColorCode::colorCode("The md5 ($downloadedMd5 !== $md5) doesn't match :(",
                                iColorCode::BACKGROUND_RED);

                            continue;

                        }

                        if (CarbonPHP::$verbose) {

                            ColorCode::colorCode("Unzipping to path ($unzipToPath)",
                                iColorCode::YELLOW);

                        }

                        $unzipToPath = CarbonPHP::$app_root . $unzipToPath;

                        if (is_dir($unzipToPath)) {

                            Files::rmRecursively($unzipToPath);

                        } else {

                            Files::createDirectoryIfNotExist($unzipToPath);

                        }

                        $unzip = "unzip '$localPath' -d '$unzipToPath'";

                        Background::executeAndCheckStatus($unzip);

                        $failed = false;

                        break;

                    }

                    break;

                }

                if (true === $failed) {

                    throw new PrivateAlert("Failed to download file ($file) after three attempts!");

                }

            }

            // show status will typically clear all prior messages in its buffer.
            // Let print the important things again!
            self::showStatus($count, $lineCount);

            if ([] === $localUpdates) {

                if (CarbonPHP::$verbose) {

                    ColorCode::colorCode("\nfile://$file\nThe file above was parsed and found no updates needed :)");

                }

            } else {

                $localUpdatesString = implode(PHP_EOL, $localUpdates);

                ColorCode::colorCode("All updated files ::\n$localUpdatesString", iColorCode::BACKGROUND_CYAN);

            }

            fclose($fp);

            ColorCode::colorCode('Migration Complete.');

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(1);

        }

    }

    /**
     * @throws PrivateAlert
     */
    public static function importManifestFile(string $file, string $uri): void
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

                    ColorCode::colorCode("Doing an update to Mysql, do not exit!!!\nfile://$file",
                        iColorCode::BACKGROUND_YELLOW);

                    MySQL::MySQLSource($file);

                    break;

                }

                throw new PrivateAlert("A MySQL dump file ($file) was found though the " . self::SKIP_MYSQL_DATA_DUMP_FLAG . " was set.");

        }

    }


    /**
     * @todo - I could make sed replace multiple at a time, but would this be worth the debugging..?
     */
    public static function replaceInFile(string $replace, string $replacement, string $absoluteFilePath): void
    {
        static $hasChangedPermissions = false;

        ColorCode::colorCode("Checking to replace ($replace) with replacement ($replacement) in file (file://$absoluteFilePath)", iColorCode::BACKGROUND_MAGENTA);

        $replaceDelimited = preg_quote($replace, '/');

        $replacementDelimited = preg_quote($replacement, '/');

        $replaceExecutable = CarbonPHP::CARBON_ROOT . 'extras/replaceInFileSerializeSafe.sh';

        $replaceBashCmd = '';

        if (false === $hasChangedPermissions) {

            $replaceBashCmd .= "chmod +x $replaceExecutable && ";

            $hasChangedPermissions = true;

        }

        // @link https://stackoverflow.com/questions/29902647/sed-match-replace-url-and-update-serialized-array-count
        $replaceBashCmd = "$replaceBashCmd $replaceExecutable '$absoluteFilePath' '$replaceDelimited' '$replace' '$replacementDelimited' '$replacement'";

        Background::executeAndCheckStatus($replaceBashCmd, true, $output);

        print  "Output: (" . implode(PHP_EOL, $output) . ")\n";

    }

    public static function captureBalancedParenthesis(string $subject): string
    {

        if (preg_match_all($pattern = '#\((?:[^)(]+|(?R))*+\),#', $subject, $matches)) {


            return $matches;

        }

        throw new PrivateAlert("Failed to capture balanced parenthesis group from string ($subject) using pattern ($pattern)");

    }

    public static function selfHidingFile(): string
    {

        $license = self::$license;

        if (empty($license)) {

            throw new PrivateAlert('License is empty!');

        }

        return <<<HALT
<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");

\$_POST['license'] ??= '';

\$_POST['file'] ??= '';

\$_POST['md5'] ??= '';

if ('$license' !== \$_POST['license']) {

    http_response_code(401); // Unauthorized
        
    exit(1);

}

\$fp = fopen(__FILE__, 'rb');

// seek file pointer to data 
fseek(\$fp, __COMPILER_HALT_OFFSET__);

if ('' !== \$_POST['file']) {
  
    \$_POST['file'] = base64_decode(\$_POST['file']);

    \$valid = false; // init as false
    
    while (false === feof(\$fp)) {
    
        \$buffer = fgets(\$fp);
    
        if (strpos(\$buffer, \$_POST['file']) !== false) {
    
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
    
    if ('' !== \$_POST['md5']) {
    
        \$localHash = md5_file( \$rootDir . DIRECTORY_SEPARATOR . \$_POST['file'] );
    
        print \$localHash === \$_POST['md5'] ? 'true' : \$localHash;
        
        exit(0);
    
    }
    
    \$absolutePath = \$rootDir . DIRECTORY_SEPARATOR . \$_POST['file'];
            
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

if ('' !== \$_POST['unlink']) {

    unlink(__FILE__);

}


__HALT_COMPILER(); 

HALT;


    }

    /**
     * @throws PrivateAlert
     */
    public static function largeHttpPostRequestsToFile(string $url, string $toLocalFilePath, array $post = [], array &$responseHeaders = []): void
    {
        try {

            $post += [
                'license' => self::$license,
                'url' => self::$remoteUrl
            ];

            $attempt = 0;

            do {


                $serverSentMd5 = '';

                $serverSentSha1 = '';

                $attempt++;

                $failed = false;

                $bytesSent = false;

                $ch = curl_init();

                ColorCode::colorCode("Attempt ($attempt) to get possibly large POST response\n$url\nStoring to (file://$toLocalFilePath)\n" . print_r($post, true));

                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_POST, 1);

                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

                $timeout = self::$timeout;

                ColorCode::colorCode("Setting the post ($url) timeout to ($timeout) <" . self::secondsToReadable($timeout) . '>', iColorCode::YELLOW);

                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

                // Receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                Files::createDirectoryIfNotExist(dirname($toLocalFilePath));

                if (false === touch($toLocalFilePath)) {

                    throw new PrivateAlert("Failed to run touch($toLocalFilePath). Please very correct permission are set on the directory!");

                }

                if (false === file_put_contents($toLocalFilePath, '')) {

                    throw new PrivateAlert("Failed to empty the file using file_put_contents ($toLocalFilePath)");

                }

                self::curlProgress($ch);

                self::curlReturnFileAppend($ch, $toLocalFilePath, $bytesSent);

                self::curlGetResponseHeaders($ch, $responseHeaders);

                $removePrefixSetVar = static function (string $header, string $prefix, string &$setVarToHeaderValue): bool {

                    if (str_starts_with($header, $prefix)) {

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

                    if (('' !== $serverSentMd5)
                        && $removePrefixSetVar($header, 'md5: ', $serverSentMd5)) {

                        continue;

                    }

                    if ('' !== $serverSentSha1) {

                        $removePrefixSetVar($header, 'sha1: ', $serverSentSha1);

                    }

                }

                curl_exec($ch);

                curl_close($ch);

                ColorCode::colorCode("Stored to local tmp file (file://$toLocalFilePath)", iColorCode::BACKGROUND_RED);

                $md5 = md5_file($toLocalFilePath);

                if ('' !== $serverSentMd5 && $serverSentMd5 !== $md5) {

                    $currentLocalMD5 = md5_file($toLocalFilePath);

                    throw new PrivateAlert("Failed to verify the md5 hash received <$md5> === expected <$serverSentMd5>, file received hashed to ($md5) on tmp file ($toLocalFilePath)! The local copy at ($toLocalFilePath) has ($currentLocalMD5)");

                }

                $sha1 = sha1_file($toLocalFilePath);

                if ('' !== $serverSentSha1 && $serverSentSha1 !== $sha1) {

                    throw new PrivateAlert("Failed to verify the sha1 ($sha1) equals server sent ($serverSentSha1) for file ($toLocalFilePath)");

                }

                if (false === $bytesSent) {

                    ColorCode::colorCode("The method (" . __METHOD__ . ") failed to CURL url \n($url) and save it to path\n(file://$toLocalFilePath)",
                        iColorCode::BACKGROUND_RED);

                    $failed = true;

                    continue;

                }

                $downloadFilePointer = fopen($toLocalFilePath, 'rb');

                if (false === $downloadFilePointer) {

                    throw new PrivateAlert("Failed to open file pointer to ($toLocalFilePath)");

                }

                $firstLine = fgets($downloadFilePointer);

                fclose($downloadFilePointer);

                if (str_starts_with($firstLine, '<html')
                    || str_starts_with($firstLine, '<!DOCTYPE html')) {

                    if (false === rename($toLocalFilePath, $toLocalFilePath . '.html')) {

                        ColorCode::colorCode("Failed to rename ($toLocalFilePath) to have .html suffix",
                            iColorCode::BACKGROUND_RED);

                    }

                    throw new PrivateAlert("The curl download detected an html document (file://$toLocalFilePath.html) using `strpos(\$firstLine, '<html')`, this is an unexpected error possibly thrown on the remote host. View downloaded file content above for (potentially) more details.");

                }

                if (str_ends_with($toLocalFilePath, '.sql')) {

                    if (15 === Background::executeAndCheckStatus("[[ \"$( cat '$toLocalFilePath' | grep -o 'Dump completed' | wc -l )\" == *\"1\"* ]] && exit 0 || exit 15", false)) {

                        $failed = true;

                    } else {

                        print PHP_EOL;

                        $urlNoProtocol = static fn($url) => preg_replace('#http(?:s)?://(.*)/#', '$1', $url);

                        if (CarbonPHP::$app_root !== self::$remoteAbsolutePath) {

                            // todo - windows -> linux support
                            self::replaceInFile(rtrim(self::$remoteAbsolutePath, DS), rtrim(CarbonPHP::$app_root, DS), $toLocalFilePath);

                        } else if (CarbonPHP::$verbose) {

                            ColorCode::colorCode('App absolute path is the same on both servers.', iColorCode::YELLOW);

                        }

                        if (self::$localUrl !== self::$remoteUrl) {

                            // todo - make these b2b replaceInFile() into one sed execution
                            self::replaceInFile(rtrim(self::$remoteUrl, '/'), rtrim(self::$localUrl, '/'), $toLocalFilePath);

                            self::replaceInFile($urlNoProtocol(self::$remoteUrl), $urlNoProtocol(self::$localUrl), $toLocalFilePath);

                        } else if (CarbonPHP::$verbose) {

                            ColorCode::colorCode("Both servers point the same url.", iColorCode::YELLOW);

                        }

                    }

                }

            } while (true === $failed && $attempt < 3);

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(8);

        }

        if (true === $failed) {

            throw new PrivateAlert("Failed to download file ($url) to ($toLocalFilePath) after ($attempt) attempts");

        }

    }


    /**
     * @throws PrivateAlert
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
     * @throws PrivateAlert
     */
    public static function curlReturnFileAppend($ch, string $tmpPath, bool &$bytesSent): void
    {
        self::testCurlResource($ch);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION,
            static function ($ch, $text) use ($tmpPath, &$bytesSent) {

                $bytesSent = true;

                if (false === file_put_contents($tmpPath, $text, FILE_APPEND)) {

                    throw new PrivateAlert("file_put_contents failed to append to ($tmpPath), ($text)", iColorCode::RED);

                }

                return strlen($text);

            });
    }

    /**
     * @throws PrivateAlert
     */
    public static function testCurlResource($ch): void
    {
        if (false === $ch instanceof CurlHandle) {

            throw new PrivateAlert('The first argument passed to curlReturnFileAppend must be a curl_init resource connection.' . print_r($ch, true));

        }
    }


    /**
     * @throws PrivateAlert
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

    /**
     * show a status bar in the console
     *
     * @link https://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php
     * @param int|null $done items completed
     * @param int|null $total total items
     * @param int|null $size optional size of the status bar
     * @return  void
     * @throws PrivateAlert
     */
    public static function showStatus(int $done = null, int $total = null, int $size = null): void
    {
        static $skipStatus = null;

        if ($skipStatus) {

            return;

        }

        if (0 === $done) {

            throw new PrivateAlert("showStatus can have 0 passed for done!");

        }

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

        $currentColumns = exec('tput cols 2> /dev/null', $output, $resultCode);

        if (is_array($output)) {

            $output = implode(' ', $output);

        }

        if (str_contains($output, 'No such device or address')) {

            // I believe this to mean no output is being captured?
            $skipStatus = true;

            return;

        }

        if (0 !== $resultCode || false === $currentColumns) {

            $currentColumns = 80;

        }

        $currentLines = exec('tput lines 2> /dev/null', $output, $resultCode);

        if (0 !== $resultCode || false === $currentLines) {

            $currentLines = 24;

        }

        if ($currentColumns !== $shellColumns) {

            $shellColumns = $currentColumns;

            if (null === $size) {

                $size = (int)$currentColumns;

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

                throw new PrivateAlert('Failed to init curl.');

            }

            $fp = fopen($path, 'rb');

            if (false === $fp) {

                throw new PrivateAlert("Could not open fopen($path, 'rb');");

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

                return fread($fh, $length);

            });

            $ret = curl_exec($ch);

            ColorCode::colorCode("The return status of the file transfer was ($ret)");

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(0);

        }

    }

    /**
     * @throws PrivateAlert
     */
    public static function dumpAll(string $pathHaltPHP): void
    {

        $currentTime = self::$currentTime;

        $tables = Database::fetchColumn('SHOW TABLES');

        $migrationPath = self::$migrationFolder . DS . self::$migrationFolderPrefix . $currentTime . DS;

        Files::createDirectoryIfNotExist(CarbonPHP::$app_root . $migrationPath);

        foreach ($tables as $table) {

            $dumpFileName = "$migrationPath{$table}.sql";

            $absolutePath = CarbonPHP::$app_root . $dumpFileName;

            MySQL::MySQLDump(null, true, true, $absolutePath, '', $table);

            Background::executeAndCheckStatus("cat '$pathHaltPHP' '$absolutePath' > '$absolutePath.php'");

            ColorCode::colorCode("Stored schemas to :: ($dumpFileName)", iColorCode::BLUE);

            print $dumpFileName . '.php' . PHP_EOL;

            flush();

            if (false === unlink($absolutePath)) {

                ColorCode::colorCode("Failed to unlink ($absolutePath). This could cause a serious security hole.", iColorCode::BACKGROUND_RED);

            }

        }

    }

    /**
     * @throws PrivateAlert
     */
    public static function zipFolder(string $relativeFolderPath): string
    {

        $zipFolderRelative = self::$migrationFolder . DS . 'zip' . DS;

        $zipFolder = CarbonPHP::$app_root . $zipFolderRelative;

        Files::createDirectoryIfNotExist($zipFolder);

        $rootPath = realpath($relativeFolderPath);

        if (CarbonPHP::$verbose) {

            ColorCode::colorCode("zipping\nfile://$rootPath", iColorCode::MAGENTA);

        }

        $zipPathHash = base64_encode($relativeFolderPath);

        $zipFilename = $zipPathHash . '.zip';

        $zipFile = $zipFolder . $zipFilename;

        Background::executeAndCheckStatus("cd '$rootPath' && zip -r '$zipFile' *");

        $md5Zip = md5_file($zipFile);

        $folderName = basename($relativeFolderPath);

        // order of the name matters for destructuring
        $finalZipFileName = (CarbonPHP::$cli ? 'local_' : '') . "migration_{$zipPathHash}_{$md5Zip}_{$folderName}.zip";

        $zipFileWithMd5 = $zipFolder . $finalZipFileName;

        if (false === rename($zipFile, $zipFileWithMd5)) {

            throw new PrivateAlert("Failed to rename($zipFile, $zipFileWithMd5)");

        }

        if (CarbonPHP::$verbose) {

            ColorCode::colorCode("zipped\nfile://$zipFileWithMd5\n\n\n", iColorCode::CYAN);

        }

        return $zipFolderRelative . $finalZipFileName;

    }

    /**
     * This would be the Parent server sending a set of resources as a manifest <map> to the child peer
     * @param Route $route
     * @param array $allowedDirectories
     * @return Route
     * @throws PrivateAlert
     * @link https://stackoverflow.com/questions/27309773/is-there-a-limit-of-the-size-of-response-i-can-read-over-http
     */
    public static function enablePull(array $allowedDirectories): bool
    {

        return Route::regexMatch('#^' . self::$migrationUrl . '/?(.*)?#i',
            static function (string $getPath = '') use ($allowedDirectories) {

                self::unlinkMigrationFiles();

                self::$currentTime = self::$remoteServerTime = microtime(true);

                ColorCode::colorCode("Migration Request " . print_r($_POST, true), iColorCode::CYAN);

                $requestedDirectoriesString = $_POST['directories'] ?? '';

                self::$license = $_POST['license'] ?? '';

                if ('' === self::$license) {

                    throw new PrivateAlert('License is empty!');

                }

                self::$remoteUrl = $_POST['url'] ?? '';

                ColorCode::colorCode('Running checkLicense');

                self::checkLicense(self::$license);

                ColorCode::colorCode('checkLicense Passed');

                header("abspath: " . CarbonPHP::$app_root);

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

                                if (str_starts_with($allowedDirectory, $directory)) {

                                    ColorCode::colorCode("The requested directory ($directory) was found as a subset, or subdirectory, of allowed directory ($allowedDirectory).", iColorCode::CYAN);

                                    $allowed = true;

                                    break;

                                }

                            }

                            if (false === $allowed) {

                                throw new PrivateAlert("Failed to verify requested ($directory) is allowed to transfer.");

                            }

                        }

                        ColorCode::colorCode("The requested ($requestedDirectoriesString) had directories not allowed by this server. Allowed values :: " . print_r($allowedDirectories, true));

                        // omit publicly logging what is allowed
                        throw new PrivateAlert("One or more directories you have requested are not listed as available! ($requestedDirectoriesString)");

                    }

                    ColorCode::colorCode('No media directories requested.');

                } else if (false === self::$MySQLDataDump) {

                    throw new PrivateAlert('Request failed as no migration directories were provided and no mysql data was explicitly requests. Nothing to do.');

                }

                $haltPHP = self::selfHidingFile();

                $pathHaltPHP = CarbonPHP::$app_root . 'cache/haltPHP.php';

                Files::createDirectoryIfNotExist(dirname($pathHaltPHP));

                if (false === file_put_contents($pathHaltPHP, $haltPHP)) {

                    throw new PrivateAlert("Failed to store halt file (file://$pathHaltPHP) to disk. Please check permissions.");

                }

                if (self::$MySQLDataDump) {

                    ColorCode::colorCode('About to dump mysql schemas <' . Database::$carbonDatabaseName . '> to file.',
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

                $zipDirectory = CarbonPHP::$app_root . self::$migrationFolder . DS . 'zip' . DS;

                if (true === is_dir($zipDirectory)) {

                    self::clearDirectory($zipDirectory);

                } else {

                    Files::createDirectoryIfNotExist($zipDirectory);

                }

                # server needs to compile directories
                foreach ($requestedDirectories as $media) {

                    if (false === is_string($media)) {

                        throw new PrivateAlert('An argument passed in the array $directories was not of type string ' . print_r($allowedDirectories, true));

                    }

                    // create a list of all files the requesting server will need to transfer
                    print self::manifestDirectory($media) . PHP_EOL;    // do not remove the newline

                    flush();

                }

                ColorCode::colorCode('Completed Migration Request!');

                exit(0);

            });

    }


    /**
     * @throws PrivateAlert
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

    public function getLicense(): void
    {

        if (null !== self::$license) {

            return;

        }

        $licenseFile = self::licenseFilePath();

        if (false === file_exists($licenseFile)) {

            $this->createLicenseFile($licenseFile);

        }

        $importedLicense = include $licenseFile;

        $importedLicense = trim($importedLicense);

        if ('' === $importedLicense) {

            ColorCode::colorCode("The license file (file://$licenseFile) provided returned an empty string. Please correct this.", iColorCode::BACKGROUND_RED);

            $this->usage();

            exit(4);

        }

        self::$license = $importedLicense;

    }


    public static function createLicenseFile(string $licensePHPFilePath): void
    {

        $createLicense = uniqid('migration_', true) . Cryptography::genRandomHex(200);

        if (false === file_put_contents($licensePHPFilePath,
                <<<CODE
                        <?php
                        
                        return '$createLicense';                  
                        
                        CODE
            )) {

            ColorCode::colorCode("Failed to store license file to (file://$licensePHPFilePath)", iColorCode::BACKGROUND_RED);

            exit(5);

        }

        ColorCode::colorCode("No license was detected. We have created a new one and stored it to (file://$licensePHPFilePath).", iColorCode::BACKGROUND_YELLOW);

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

                self::createLicenseFile($licensePHPFilePath);

                ColorCode::colorCode("No license was detected. We have created a new one and stored it to ($licensePHPFilePath).", iColorCode::BACKGROUND_RED);

                exit(6);

            }

            $realLicense = include $licensePHPFilePath;


            if ($realLicense !== $checkLicense) {

                ColorCode::colorCode("The license ($checkLicense) provided did not match the expected.", iColorCode::BACKGROUND_RED);

                exit(7);

            }

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(0);

        }

    }

    /**
     * @throws PrivateAlert
     */
    public static function compileFolderFiles(string $path): array
    {

        $files = [];

        Files::createDirectoryIfNotExist($path);

        $directory = new DirectoryIterator($path);

        foreach ($directory as $file) {

            $filePath = $file->getPathname();

            if ($file->isDot()) {

                continue;

            }

            if (false === $file->isDir()) {

                $files[] = $filePath;

            } else if ($file->isDir()) {

                if (false === self::directorySizeLessThan($filePath, self::$maxFolderSizeForCompressionInMb)) {

                    // recursive, logically simple; runtime expensive
                    $files += self::compileFolderFiles($filePath);

                    continue;

                }

                $isDirEmpty = !(new FilesystemIterator($filePath))->valid();

                if ($isDirEmpty) {

                    $files[] = $filePath . DS;

                    continue;

                }

                $files[] = self::zipFolder($filePath);

            }

        }

        return $files;

    }

    public static function manifestDirectory(string $path): string
    {

        try {

            $hash = base64_encode($path);

            $relativePath = self::$migrationFolder . DS . self::$migrationFolderPrefix . self::$currentTime . DS . 'media_' . $hash . '_' . self::$currentTime . '.txt.php';

            $storeToFile = CarbonPHP::$app_root . $relativePath;

            $files = self::compileFolderFiles($path);   // array

            $php = self::selfHidingFile();

            $allFilesCSV = $php . PHP_EOL . implode(PHP_EOL, $files);

            if (false === file_put_contents($storeToFile, $allFilesCSV)) {

                throw new PrivateAlert("Failed to store the RecursiveDirectoryIterator contents to file ($storeToFile)");

            }

            return $relativePath;

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            exit(4);

        }

    }


    public function usage(): void
    {
        ColorCode::colorCode(<<<HELP
            Usage: command [options]
            
            Options:
              --timeout <value>                        Set the timeout duration. Value should be in seconds.
            
              --max-folder-size-to-compress-mb <value> Set the maximum folder size allowed for compression, in megabytes (MB).
            
              --verbose                                Enable verbose mode. Prints more information during execution.
            
              --license <value>                        Specify the license. The value is a string representing the license.
            
              --local-url <value>                      Set the local URL. The URL must match the pattern "^http(s)?://.*/$". This is used to specify the local base URL.
            
              --remote-url <value>                     Set the remote URL. The URL must match the pattern "^http(s)?://.*/$". This is used to specify the remote base URL.
            
              --skip-mysql-data-dump                   Skip the MySQL data dump process. This flag disables the dumping of MySQL data.
            
              --migrate-directories <value>            Specify directories to be migrated. The value is a string representing the directories.
            
            Notes:
              - If '--local-url' or '--remote-url' is set, the URL provided must end with a slash (/) and start with http(s)://.
              - The '--verbose' flag enables detailed output, making it easier to follow what the script is doing.
              - When using '--license', '--local-url', '--remote-url', or '--migrate-directories', ensure to provide a value immediately after the flag.
              - Use '--skip-mysql-data-dump' to prevent MySQL data from being dumped. This is useful for migrations where data dumping is not required.
              - If an unrecognized cli argument is provided, the script will terminate with an error message indicating the unrecognized argument.
            
            Example:
              command --verbose --timeout 30 --max-folder-size-to-compress-mb 500 --local-url http://localhost/ --remote-url http://example.com/ --license migrate_23430.21432
            HELP, iColorCode::BLUE);

    }

    public function cleanUp(): void
    {
        self::unlinkMigrationFiles();
    }

}
