<?php

declare(strict_types=1);

namespace CarbonPHP\Classes\Programs;

use CarbonPHP\Abstracts\Background;
use CarbonPHP\Abstracts\Classes\Database;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Cryptography;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\Fork;
use CarbonPHP\Abstracts\MySQL;
use CarbonPHP\Abstracts\Zip;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Abstracts\Route;
use CarbonPHP\Throwables\PrivateAlert;
use CarbonPHP\Throwables\PublicAlert;
use Throwable;

class Migrate implements iCommand
{
    public static string $migrationUrl = 'c6migration';

    public static string $migrationFolder = 'tmp'.DIRECTORY_SEPARATOR.'c6migrations';

    public static string $migrationFolderPrefix = 'migration_';

    public const MIGRATION_COMPLETE = "Migration complete\n";

    public static float $currentTime;

    public static ?float $remoteServerTime = null;

    public static ?string $license = null;

    public static ?string $localUrl = null;

    public static ?string $remoteUrl = null;

    public static ?string $remoteAbsolutePath = null;

    public static ?string $localIp = null;

    public static ?string $remoteLocalIp = null;

    public static ?string $publicIp = null;

    public static ?string $remotePublicIp = null;

    public static ?string $directories = null;

    public const SKIP_MYSQL_DATA_DUMP_FLAG = '--no-dump-data';

    public const MIGRATE_DIRECTORIES_FLAG = '--directories';

    public static bool $MySQLDataDump = true;

    public static int $timeout = 600;

    public static int $maxFolderSizeForCompressionInMb = 500;

    public static bool $parallel = false;

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

        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));

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

        $migrationFiles = glob(CarbonPHP::$app_root.DIRECTORY_SEPARATOR.self::$migrationFolder.DIRECTORY_SEPARATOR.self::$migrationFolderPrefix.'*', GLOB_ONLYDIR);

        foreach ($migrationFiles as $migrationFolder) {
            if (false === is_dir($migrationFolder)) {
                continue;
            }

            $migrationFolderExploded = explode(DIRECTORY_SEPARATOR, $migrationFolder);

            $migrationFolderName = end($migrationFolderExploded);

            $migrationTime = (float) substr($migrationFolderName, strlen(self::$migrationFolderPrefix));

            if (self::$currentTime - $migrationTime < 86400) {
                continue; // less than 24 hours old
            }

            try {
                Background::executeAndCheckStatus("rm -rf $migrationFolder");

                CarbonPHP::$verbose and ColorCode::colorCode('unlinked ('.$migrationFolder.')');
            } catch (Throwable $e) {
                ThrowableHandler::generateLog($e);
            } finally {
                ++$updateCount;
            }
        }

        ColorCode::colorCode('Removed ('.$updateCount.') old migration files!');
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
        for ($i = 0, $argc = count($argv); $i < $argc; ++$i) {
            switch ($argv[$i]) {
                case '--timeout':

                    self::$timeout = $argv[++$i];

                    break;

                case '--excludedTablesRegex':

                    self::$excludedTablesRegex = $argv[++$i];

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
                        throw new PrivateAlert("The url failed to match the regx ($pattern) with given --local-url argument. (".self::$localUrl.') given.');
                    }

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found flag set for local URL ('.self::$localUrl.')');

                    break;

                case '--remote-url':

                    self::$remoteUrl = $argv[++$i] ?? '';

                    $pattern = '#^http(s)?://.*/$#';

                    if (1 !== preg_match($pattern, self::$remoteUrl)) {
                        throw new PrivateAlert("The url failed to match the regx ($pattern) with given --remote-url argument; (".self::$remoteUrl.') given.');
                    }

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found flag set for remote URL ('.self::$remoteUrl.')');

                    break;

                case self::SKIP_MYSQL_DATA_DUMP_FLAG:

                    self::$MySQLDataDump = false;

                    break;

                case self::MIGRATE_DIRECTORIES_FLAG:

                    self::$directories = $argv[++$i] ?? '';

                    CarbonPHP::$verbose and ColorCode::colorCode('CLI found request directories flag ('.self::$directories.')');

                    break;

                default:

                    ColorCode::colorCode("Unrecognized cli argument ($argv[$i]) failing.", iColorCode::BACKGROUND_RED);

                    $this->usage();

                    exit(1);
            }
        }

        ColorCode::colorCode('The default timeout for all external requests is set to ('.self::secondsToReadable(self::$timeout).') H:M:S', iColorCode::BACKGROUND_CYAN);

        $this->getLicense();

        if (null === self::$localUrl || null === self::$remoteUrl) {
            $this->usage();

            ColorCode::colorCode('The local and remote url must be passed to the migration command!', iColorCode::BACKGROUND_RED);

            exit(2);
        }

        self::unlinkMigrationFiles();

        $postData = [];

        if (null === self::$directories && false === self::$MySQLDataDump) {
            ColorCode::colorCode(
                'You have specified nothing to migrate! When the flag ('.self::SKIP_MYSQL_DATA_DUMP_FLAG.') is active you must also include ('.self::MIGRATE_DIRECTORIES_FLAG.')',
                iColorCode::BACKGROUND_RED
            );

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
                // create a list of all files the requesting server will need to transfer
                $requestedDirectoriesLocalCopyInfo += self::compileFolderFiles($media);
            }

            ColorCode::colorCode('Requested directories local copy cache info: '.print_r($requestedDirectoriesLocalCopyInfo, true));
        }

        if (false === self::$MySQLDataDump) {
            $postData += [
                self::SKIP_MYSQL_DATA_DUMP_FLAG => true,
            ];
        }

        $localManifestPath = CarbonPHP::$app_root.self::$migrationFolder.DS.'migration_manifest_'.time().'.txt';

        $responseHeaders = [];

        $manifestURL = self::$remoteUrl.self::$migrationUrl;

        ColorCode::colorCode("Attempting to get manifest at url ($manifestURL)");

        if (null !== self::$excludedTablesRegex) {
            $postData += [
                'excludedTablesRegex' => self::$excludedTablesRegex,
            ];
        }

        // get the master manifest with sql and txt documents
        self::largeHttpPostRequestsToFile($manifestURL, $localManifestPath, $postData, $responseHeaders);

        ColorCode::colorCode('About to look for ABSPATH header');

        $absolutePathHeader = 'abspath: ';

        // these are backup options if the remote server is distributed (load balanced)
        $localIpHeader = 'local_ip: ';

        $publicIpHeader = 'public_ip: ';

        foreach ($responseHeaders as $header) {
            if (str_starts_with($header, $absolutePathHeader)) {
                self::$remoteAbsolutePath = trim(substr($header, strlen($absolutePathHeader)));
            }

            if (str_starts_with($header, $localIpHeader)) {
                self::$remoteLocalIp = trim(substr($header, strlen($localIpHeader)));
            }

            if (str_starts_with($header, $publicIpHeader)) {
                self::$remotePublicIp = trim(substr($header, strlen($publicIpHeader)));
            }
        }

        if (false === file_exists($localManifestPath)) {
            ColorCode::colorCode("Failed to get manifest from remote server!\n(file://$localManifestPath)", iColorCode::BACKGROUND_RED);

            exit(7);
        }

        $manifest = fopen($localManifestPath, 'rb');

        $firstImport = fgets($manifest);

        fclose($manifest); // this will change location

        $position = strpos($firstImport, self::$migrationFolderPrefix);

        self::$remoteServerTime = (float) substr($firstImport, $position + strlen(self::$migrationFolderPrefix), strlen((string) microtime(true)));

        self::$currentTime = self::$remoteServerTime;

        if (null === self::$remoteServerTime) {
            ColorCode::colorCode("Failed to parse remote server time from headers!\n".print_r($header, true), iColorCode::BACKGROUND_RED);

            exit(8);
        }

        $importFolderLocation = CarbonPHP::$app_root.self::$migrationFolder.DS.self::$migrationFolderPrefix.self::$remoteServerTime.DS;

        Files::createDirectoryIfNotExist($importFolderLocation);

        $newLocalManifestPath = $importFolderLocation.'migration_manifest.txt';

        if (false === rename($localManifestPath, $newLocalManifestPath)) {
            throw new PrivateAlert("Failed to rename local manifest file ($localManifestPath) to ($newLocalManifestPath)");
        }

        $localManifestPath = $newLocalManifestPath;

        $manifestLineCount = self::getLineCount($localManifestPath);

        // todo - this could be bottle neck and should? be processed one at a time
        $manifest = fopen($localManifestPath, 'rb');

        if (false === $manifest) {
            throw new PrivateAlert("Failed to open file pointer to ($localManifestPath)");
        }

        echo "Manifest Line Count: $manifestLineCount\nFirst line: ".fgets($manifest)."\n";

        if (null === self::$remoteAbsolutePath) {
            throw new PrivateAlert('Failed to parse the absolute path header from the remote server! ('.print_r($responseHeaders, true).')');
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

        $manifestComplete = false;

        $fileImportCallables = [];

        while (false === feof($manifest)) {
            $uri = trim(fgets($manifest));

            if (empty($uri)) {
                --$manifestLineCount;
                continue;
            }

            if (str_contains($uri, trim(self::MIGRATION_COMPLETE))) {
                $manifestComplete = true;

                ColorCode::colorCode('Manifest was transferred correctly!');

                break;
            }

            // convert the uri we will fetch to a local path to download to
            $importManifestFilePath = $uri;

            // this is a base64 encoded path specific for downloading migration files
            if (str_starts_with($importManifestFilePath, self::$migrationUrl.'/')) {
                $importManifestFilePath = substr($uri, strlen(self::$migrationUrl.'/'));

                if (!self::isBase64($importManifestFilePath)) {
                    throw new PrivateAlert('The import manifest file path was not correctly base64 encoded! ('.$importManifestFilePath.')');
                }

                $importManifestFilePath = base64_decode($importManifestFilePath, true);
            }

            $importManifestFilePath = CarbonPHP::$app_root.$importManifestFilePath;

            // todo - make this a regex or better
            $importManifestFilePath = rtrim($importManifestFilePath, '.ph');

            $fileImportCallables[] = static function () use ($uri, $importManifestFilePath) {
                ColorCode::colorCode("Importing file ($importManifestFilePath)", iColorCode::CYAN);

                // download all the files (sql or txt of zips and arbitrary files)) to the local server
                self::largeHttpPostRequestsToFile(self::$remoteUrl.$uri, $importManifestFilePath, []);
            };

            $manifestArray[$uri] = $importManifestFilePath;
        }

        $handleTasks = static function (array $tasks, callable $returnHandler): void {
            if (self::$parallel) {
                Fork::executeInChildProcesses($tasks, $returnHandler);

                return;
            }

            foreach ($tasks as $task) {
                $task();
                /** @noinspection DisconnectedForeachInstructionInspection */
                $returnHandler(0, 0); // this just changes the progress bar
            }
        };

        $handleTasks($fileImportCallables, static function ($pid, $status) use (&$done, $manifestLineCount) {
            if ($status !== 0) {
                throw new PrivateAlert("Download process ($pid) failed with status ($status)");
            }

            self::showStatus(++$done, $manifestLineCount);
        });

        unset($fileImportCallables);

        if (false === $manifestComplete) {
            throw new PrivateAlert('The manifest was not transferred correctly! '.print_r($manifestArray, true));
        }

        // todo - we need to NOT download zips unless needed
        $done = 0;

        $manifestArrayCount = count($manifestArray);

        $importProcesses = [];

        foreach ($manifestArray as $importFileAbsolutePath) {
            $importProcesses[] = static function () use ($importFileAbsolutePath, $requestedDirectoriesLocalCopyInfo) {
                ColorCode::colorCode("Importing file ($importFileAbsolutePath)", iColorCode::CYAN);

                // using the newly downloaded files, import them based on their extension (sql,txt)
                self::importManifestFile($importFileAbsolutePath, $requestedDirectoriesLocalCopyInfo);
            };
        }

        // todo - add option to run synchronously
        $handleTasks($importProcesses, static function ($pid, $status) use (&$done, $manifestArrayCount) {
            if ($status !== 0) {
                throw new PrivateAlert("Failed to import manifest file ($pid) with status ($status)");
            }

            self::showStatus(++$done, $manifestArrayCount);
        });

        ColorCode::colorCode('Completed in '.(microtime(true) - self::$currentTime).' sec');

        exit(0);
    }

    // @link https://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
    public static function getLineCount($filePath): int
    {
        $file = new \SplFileObject($filePath, 'rb');

        $file->seek(PHP_INT_MAX);

        return $file->key();
    }

    /**
     * A list of media files. Folders will have been zipped but expect
     *      ico|pdf|flv|jpg|jpeg|png|gif|swf|xml|txt|css|html|htm|php|hbs|js|pdf|.... etc anything
     *
     * @param string $file
     * @param string $uri
     *
     * @return void
     */
    public static function importMedia(string $file, array $requestedDirectoriesLocalCopyInfo): void
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
            // these files are not not encoded and are relative paths the the ABSPATH
            // they maybe zips or actual files in the system.
            while (false === feof($fp)) {
                self::showStatus(++$count, $lineCount);

                $mediaFile = fgets($fp, 1024);

                $mediaFile = trim($mediaFile);

                if (str_contains($mediaFile, trim(self::MIGRATION_COMPLETE))) {
                    ColorCode::colorCode("Migration file ($file) imported.");

                    break;
                }

                // check if the folder
                if ('' === $mediaFile) {
                    continue;
                }

                if ($requestedDirectoriesLocalCopyInfo[$mediaFile] ?? false) {
                    ColorCode::colorCode("Skipping file ($mediaFile) as its hash matched a the local version!", iColorCode::BACKGROUND_YELLOW);

                    continue;
                }

                $localPath = CarbonPHP::$app_root.$mediaFile;

                // todo - if media file is a directory then we need to recursively create said directory.. it will remain empty
                if (DS === $mediaFile[-1]) {
                    Files::createDirectoryIfNotExist($localPath);

                    continue;
                }

                $getMetaUrl = self::$remoteUrl.self::$migrationUrl.'/'.base64_encode($mediaFile).'?license='.self::$license;

                // todo - reimplement caching????
                /*if (true === file_exists($localPath)) {

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

                }*/

                ColorCode::colorCode("Updates needed <$hash>($localPath)", iColorCode::BACKGROUND_CYAN);

                ColorCode::colorCode($mediaFile, $color ? iColorCode::BACKGROUND_GREEN : iColorCode::BACKGROUND_CYAN);

                $color = !$color;

                $localUpdates[] = 'file://'.$localPath;

                $networkCount = 3;

                $failed = false;

                while ($networkCount--) {
                    if ($networkCount < 2) {
                        ColorCode::colorCode(
                            "Retrying \n($getMetaUrl) to local path\n(file://$localPath)",
                            iColorCode::BACKGROUND_YELLOW
                        );
                    }

                    self::largeHttpPostRequestsToFile($getMetaUrl, $localPath);

                    if (1 === preg_match('/zip$/', $localPath)) {
                        $zipFileName = basename($localPath);

                        if (CarbonPHP::$verbose) {
                            ColorCode::colorCode("Exploding ($localPath)", iColorCode::YELLOW);
                        }

                        [, $path, $md5] = explode('_', $zipFileName);

                        [$md5] = explode('.', $md5);   // remove the .zip suffix

                        $unzipToPath = base64_decode($path, true);

                        $downloadedMd5 = md5_file($localPath);

                        if ($downloadedMd5 !== $md5) {
                            $failed = true;

                            ColorCode::colorCode(
                                "The md5 ($downloadedMd5 !== $md5) doesn't match :(",
                                iColorCode::BACKGROUND_RED
                            );

                            continue;
                        }

                        if (CarbonPHP::$verbose) {
                            ColorCode::colorCode(
                                "Unzipping to path ($unzipToPath)",
                                iColorCode::YELLOW
                            );
                        }

                        $unzipToPath = CarbonPHP::$app_root.$unzipToPath;

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

            ColorCode::colorCode("Media imported ($file).");
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);

            exit(1);
        }
    }

    public static function isBase64($s): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }

    /**
     * $requestedDirectoriesLocalCopyInfo is and array of directories and zip file paths.
     * The zip files are unique self::zipFolder "migration_{$zipPathHash}_{$md5Zip}_{$folderName}.zip"
     *
     * @throws PrivateAlert
     */
    public static function importManifestFile(string $file, array $requestedDirectoriesLocalCopyInfo): void
    {
        CarbonPHP::$verbose and ColorCode::colorCode("Importing file ($file)");

        $info = pathinfo($file);

        if (empty($info['extension']) && self::isBase64($info['filename'] ?? '')) {
            $file = base64_decode($info['filename'], true);

            $info = pathinfo($file);
        }

        switch ($info['extension'] ?? null) {
            default:

                throw new PrivateAlert('The file extension ('.print_r($info, true).') was not recognized.');
            case 'txt':

                // Its still valid to transfer files when (CarbonPHP::$app_root === self::$remoteAbsolutePath)
                // when scaling  multiple servers on seperate ebs

                ColorCode::colorCode("Import media manifest\nfile://$file", iColorCode::CYAN);

                echo shell_exec("cat $file");

                self::importMedia($file, $requestedDirectoriesLocalCopyInfo);

                break;

            case 'sql':

                if (self::$MySQLDataDump) {
                    ColorCode::colorCode(
                        "Doing an update to Mysql, do not exit!!!\nfile://$file",
                        iColorCode::BACKGROUND_YELLOW
                    );

                    MySQL::MySQLSource($file);

                    break;
                }

                throw new PrivateAlert("A MySQL dump file ($file) was found though the ".self::SKIP_MYSQL_DATA_DUMP_FLAG.' was set.');
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

        $replaceExecutable = CarbonPHP::CARBON_ROOT.'extras/replaceInFileSerializeSafe.sh';

        $replaceBashCmd = '';

        if (false === $hasChangedPermissions) {
            $replaceBashCmd .= "chmod +x $replaceExecutable && ";

            $hasChangedPermissions = true;
        }

        // @link https://stackoverflow.com/questions/29902647/sed-match-replace-url-and-update-serialized-array-count
        $replaceBashCmd = "$replaceBashCmd $replaceExecutable '$absoluteFilePath' '$replaceDelimited' '$replace' '$replacementDelimited' '$replacement'";

        Background::executeAndCheckStatus($replaceBashCmd, true, $output);

        echo 'Output: ('.implode(PHP_EOL, $output).")\n";
    }

    // this could be an extremely large file
    // were trying to bypass load balancers by sending the file directly to the client
    public static function proxyRequest(string $url, ?string $host = null, bool $verifySSL = true): never
    {
        $ch = curl_init();

        // Set the URL to fetch
        curl_setopt($ch, CURLOPT_URL, $url);

        // Disable RETURNTRANSFER to output directly to STDOUT
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        // Keep SSL verification enabled for security
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);

        // Set the Host header if provided
        if ($host !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Host: $host"]);
        }

        // Execute the session, outputs directly to STDOUT
        curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: '.curl_error($ch);
        }

        // Close cURL resource
        curl_close($ch);

        exit(0);
    }

    public static function sendMigrationData(string $file): void
    {
        try {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

            header('Cache-Control: post-check=0, pre-check=0', false);

            header('Pragma: no-cache');

            if (!file_exists($file)) {
                if (str_starts_with($file, ABSPATH)) {
                    throw new PublicAlert("The file requested ($file) must begin with the servers ABSPATH constant (".ABSPATH.')');
                }

                // trim ABSPATH from $file
                $file = substr($file, strlen(CarbonPHP::$app_root));

                if (self::$remoteLocalIp !== self::$localIp) {
                    if (self::sendCheckPing(self::$remoteLocalIp)) {
                        self::proxyRequest('http://'.self::$remoteLocalIp.'/'.$file, $_SERVER['HTTP_HOST'], false);
                    }

                    ColorCode::colorCode('Failed to ping remote local ip ('.self::$remoteLocalIp.'). This is the local ip of the remote server that run the migration. This probably means this server is not on the same local area network, has blocked our request, is no longer running, or other network related events. Will attempt to use the public ip.');
                }

                if (self::$remotePublicIp !== self::$publicIp) {
                    if (self::sendCheckPing(self::$remotePublicIp)) {
                        // this could be an extremely large file
                        // were trying to bypass load balancers by sending the file directly to the client
                        self::proxyRequest('http://'.self::$remotePublicIp.'/'.$file, $_SERVER['HTTP_HOST'], false);
                    }

                    ColorCode::colorCode('Could not reach remote server for migration using public internet ip ('.self::$remotePublicIp.'). This could mean the ip is blocked for a load balancer only, the server is no longer running.');
                }

                throw new PublicAlert('The file requested does not exist on this server, though was supposedly created on this server');
            }

            if (str_ends_with($file, '.txt.php')) {
                $return = include $file;

                echo $return;

                exit(0);
            }

            $info = pathinfo($file);

            // todo - ensure this is a zip?

            $_POST['file'] ??= '';

            $_POST['md5'] ??= '';

            $fp = fopen($file, 'rb');

            if (false === $fp) {
                throw new PrivateAlert("Failed to open file pointer to ($file)");
            }

            if ('' !== $_POST['file']) {
                $_POST['file'] = base64_decode($_POST['file'], true);

                $valid = false; // init as false

                while (false === feof($fp)) {
                    $buffer = fgets($fp);

                    if (str_contains($buffer, $_POST['file'])) {
                        $valid = true;

                        break; // Once you find the string, you should break out the loop.
                    }
                }

                fclose($fp);

                if (false === $valid) {
                    http_response_code(400);

                    exit(1);
                }

                $rootDir = dirname(__DIR__);

                if ('' !== $_POST['md5']) {
                    $localHash = md5_file($rootDir.DIRECTORY_SEPARATOR.$_POST['file']);

                    echo $localHash === $_POST['md5'] ? 'true' : $localHash;

                    exit(0);
                }

                $absolutePath = $rootDir.DIRECTORY_SEPARATOR.$_POST['file'];

                $fp = fopen($absolutePath, 'rb');

                if (false === $fp) {
                    http_response_code(400);

                    exit(1);
                }

                $md5 = md5_file($absolutePath);

                $sha1 = sha1_file($absolutePath);

                header("md5: $md5");

                header("sha1: $sha1");
            }

            // Assuming $fp is your file pointer, opened previously with fopen
            $position = ftell($fp);

            $bytesSent = fpassthru($fp);

            if (0 >= $bytesSent) {
                throw new PrivateAlert("No bytes send from file pointer ($file) starting from position ($position)");
            }

            if (!empty($_POST['unlink'])) {
                unlink($file);
            }
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);

            exit(1);
        }
    }

    public static function selfHidingFile(string $hiddenContents): string
    {
        return <<<HALT
<?php

// unlink(__FILE__);

return <<<SELF_HIDING_FILE
$hiddenContents
SELF_HIDING_FILE;
HALT;
    }

    /**
     * @throws PrivateAlert
     */
    public static function largeHttpPostRequestsToFile(string $url, string $toLocalFilePath, array $post = [], array &$requestResponseHeaders = []): void
    {
        try {
            $post += [
                'license' => self::$license,
                'url'     => self::$remoteUrl,
            ];

            $attempt = 0;

            do {
                $serverSentMd5 = '';

                $serverSentSha1 = '';

                ++$attempt;

                $bytesSent = false;

                $ch = curl_init();

                if (CarbonPHP::$verbose) {
                    ColorCode::colorCode("Attempt ($attempt) to get possibly large POST response\n$url\nStoring to (file://$toLocalFilePath)\n".print_r($post, true));
                }

                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_POST, 1);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $query = http_build_query($post));

                curl_setopt($ch, CURLOPT_HTTPHEADER, $requestResponseHeaders);

                $timeout = self::$timeout;

                if (CarbonPHP::$verbose) {
                    ColorCode::colorCode("Setting the post ($url) timeout to ($timeout) <".self::secondsToReadable($timeout).'> with body ('.$query.')');
                }

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

                self::curlGetResponseHeaders($ch, $requestResponseHeaders);

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

                foreach ($requestResponseHeaders as $header) {
                    if (''    !== $serverSentMd5
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

                // Get the HTTP response code
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                curl_close($ch);

                ColorCode::colorCode("Stored to local tmp file (file://$toLocalFilePath)");

                if (401 === $httpCode) {
                    passthru("cat $toLocalFilePath; rm -rf $toLocalFilePath");

                    ColorCode::colorCode("Server responded with http code 401. Unauthorized to access the remote server ($url) with the given parameters (".$query.'). Please make sure your license is correct!', iColorCode::BACKGROUND_RED);

                    exit(100);
                }

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
                    ColorCode::colorCode(
                        'The method ('.__METHOD__.") failed to CURL url \n($url) and save it to path\n(file://$toLocalFilePath) response code ($httpCode) after ($attempt) attempts",
                        iColorCode::BACKGROUND_RED
                    );

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
                    if (false === rename($toLocalFilePath, $toLocalFilePath.'.html')) {
                        ColorCode::colorCode(
                            "Failed to rename ($toLocalFilePath) to have .html suffix",
                            iColorCode::BACKGROUND_RED
                        );
                    }

                    throw new PrivateAlert("The curl download detected an html document (file://$toLocalFilePath.html) using `strpos(\$firstLine, '<html')`, this is an unexpected error possibly thrown on the remote host. View downloaded file content above for (potentially) more details.");
                }

                if (str_ends_with($toLocalFilePath, '.sql')) {
                    if (15 === Background::executeAndCheckStatus("[[ \"$( cat '$toLocalFilePath' | grep -o 'Dump completed' | wc -l )\" == *\"1\"* ]] && exit 0 || exit 15", false)) {
                        $failed = true;

                        continue;
                    }

                    echo PHP_EOL;

                    $urlNoProtocol = static fn ($url) => preg_replace('#http(?:s)?://(.*)/#', '$1', $url);

                    if (CarbonPHP::$app_root !== self::$remoteAbsolutePath) {
                        // todo - windows -> linux support
                        self::replaceInFile(rtrim(self::$remoteAbsolutePath, DS), rtrim(CarbonPHP::$app_root, DS), $toLocalFilePath);
                    } elseif (CarbonPHP::$verbose) {
                        ColorCode::colorCode('App absolute path is the same on both servers.', iColorCode::YELLOW);
                    }

                    if (self::$localUrl !== self::$remoteUrl) {
                        // todo - make these b2b replaceInFile() into one sed execution
                        self::replaceInFile(rtrim(self::$remoteUrl, '/'), rtrim(self::$localUrl, '/'), $toLocalFilePath);

                        self::replaceInFile($urlNoProtocol(self::$remoteUrl), $urlNoProtocol(self::$localUrl), $toLocalFilePath);
                    } elseif (CarbonPHP::$verbose) {
                        ColorCode::colorCode('Both servers point the same url.', iColorCode::YELLOW);
                    }
                }

                $failed = false;
            } while (true === $failed && $attempt < 3);
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);

            exit(8);
        }

        if (true === $failed) {
            $path = pathinfo($url);

            throw new PrivateAlert('Failed to download file '.(empty($path['extension']) ? base64_decode($path['filename'], true) : '')."($url) to ($toLocalFilePath) after ($attempt) attempts with query ($query)");
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
     *
     * @return void
     *
     * @throws PrivateAlert
     */
    public static function curlReturnFileAppend($ch, string $tmpPath, bool &$bytesSent): void
    {
        self::testCurlResource($ch);

        curl_setopt(
            $ch,
            CURLOPT_WRITEFUNCTION,
            static function ($ch, $text) use ($tmpPath, &$bytesSent) {
                $bytesSent = true;

                if (false === file_put_contents($tmpPath, $text, FILE_APPEND)) {
                    throw new PrivateAlert("file_put_contents failed to append to ($tmpPath), ($text)", iColorCode::RED);
                }

                return strlen($text);
            }
        );
    }

    /**
     * @throws PrivateAlert
     */
    public static function testCurlResource($ch): void
    {
        if (false === $ch instanceof \CurlHandle) {
            throw new PrivateAlert('The first argument passed to curlReturnFileAppend must be a curl_init resource connection.'.print_r($ch, true));
        }
    }

    /**
     * @throws PrivateAlert
     */
    public static function curlGetResponseHeaders($ch, array &$headers): void
    {
        self::testCurlResource($ch);

        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            static function ($ch, $header_line) use (&$headers) {
                $headers[] = $header_line;

                return strlen($header_line);
            }
        );
    }

    /**
     * show a status bar in the console
     *
     * @see https://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php
     *
     * @param int|null $done items completed
     * @param int|null $total total items
     * @param int|null $size optional size of the status bar
     *
     * @return void
     *
     * @throws PrivateAlert
     */
    public static function showStatus(?int $done = null, ?int $total = null, ?int $size = null): void
    {
        static $skipStatus = null;

        if ($skipStatus) {
            return;
        }

        if (0 === $done) {
            throw new PrivateAlert('showStatus can have 0 passed for done!');
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
                $size = (int) $currentColumns;

                $size -= 60;

                if ($size < 30) {
                    $size = 30;
                }

                $barSizeCache = $size;
            }
        }

        if (null === $shellLines) {
            for ($i = $currentLines; $i !== 0; --$i) {
                // This print avoids the clear char escapes \e[H\e[J removing lines previously printed
                echo PHP_EOL;
            }

            $shellLines = $currentLines;

            echo "\e[H\e[3J";
        } elseif ($currentLines !== $shellLines) {
            if ($currentLines > $shellLines) {
                $lineDiff = $currentLines - $shellLines;

                ColorCode::colorCode("$lineDiff = $currentLines - $shellLines");

                for ($i = $lineDiff + 12; $i !== 0; --$i) {
                    // This print avoids the clear char escapes \e[H\e[J removing lines previously printed
                    echo PHP_EOL;
                }
            }

            $shellLines = $currentLines;

            echo "\e[H\e[2J\e[3J";
        } else {
            echo "\e[H\e[0J";
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

        $percentage = (float) ($done / $total);

        $bar = (int) floor($percentage * $size);

        $status_bar = '[';

        $status_bar .= str_repeat('=', $bar);

        if ($bar < $size) {
            $status_bar .= '>';

            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $display = number_format($percentage * 100);

        $status_bar .= "] $display%  $done/$total";

        $rate = ($now - $start_time) / $done;

        $left = $total - $done;

        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= ' remaining: '.number_format($eta).' sec.  elapsed: '.number_format($elapsed).' sec.';

        echo "$status_bar  \n";

        flush();
    }

    /**
     * @throws PrivateAlert
     */
    public static function dumpAll(): void
    {
        $tables = Database::fetchColumn('SHOW TABLES');

        $migrationPath = self::$migrationFolder.DS.self::$migrationFolderPrefix.self::$currentTime.DS;

        Files::createDirectoryIfNotExist(CarbonPHP::$app_root.$migrationPath);

        foreach ($tables as $table) {
            // if table matches self::$excludedTablesRegex then skip
            if (self::$excludedTablesRegex
                && preg_match(self::$excludedTablesRegex, $table)) {
                continue;
            }

            $dumpFileName = "$migrationPath{$table}.sql";

            $absolutePath = CarbonPHP::$app_root.$dumpFileName;

            MySQL::MySQLDump(null, true, true, $absolutePath, '', $table);

            echo self::$migrationUrl.'/'.base64_encode($dumpFileName).PHP_EOL;

            flush();
        }
    }

    /**
     * @throws PrivateAlert
     */
    public static function zipFolder(string $relativeFolderPath): string
    {
        $zipFolderRelative = self::$migrationFolder.DS.self::$migrationFolderPrefix.self::$currentTime.DS;

        $zipFolder = CarbonPHP::$app_root.$zipFolderRelative;

        Files::createDirectoryIfNotExist($zipFolder);

        $rootPath = realpath($relativeFolderPath);

        if (CarbonPHP::$verbose) {
            ColorCode::colorCode("zipping\nfile://$rootPath", iColorCode::MAGENTA);
        }

        $zipPathHash = base64_encode($relativeFolderPath);

        $zipFilename = $zipPathHash.'.zip';

        $zipFile = $zipFolder.$zipFilename;

        // Remove any trailing slashes from the path
        $rootPath = rtrim(realpath($rootPath), DIRECTORY_SEPARATOR);

        $exitCode = Background::executeAndCheckStatus("cd '$rootPath' && zip -r '$zipFile' *", false);

        if ($exitCode !== 0) {
            Zip::compress($rootPath, $zipFile);
        }

        // ensure the file is written to disk
        if (false === file_exists($zipFile)) {
            throw new PrivateAlert("Failed to write zip file ($zipFile) to disk.");
        }

        $md5Zip = md5_file($zipFile);

        $folderName = basename($relativeFolderPath);

        // order of the name matters for destructuring
        $finalZipFileName = (CarbonPHP::CLI ? 'local_' : '')."migration_{$zipPathHash}_{$md5Zip}_{$folderName}.zip";

        $zipFileWithMd5 = $zipFolder.$finalZipFileName;

        if (false === rename($zipFile, $zipFileWithMd5)) {
            throw new PrivateAlert("Failed to rename($zipFile, $zipFileWithMd5)");
        }

        if (CarbonPHP::$verbose) {
            ColorCode::colorCode("zipped\nfile://$zipFileWithMd5\n\n\n", iColorCode::CYAN);
        }

        return $zipFolderRelative.$finalZipFileName;
    }

    public static function getPublicIpAddress()
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        // Port 53 is the standard port used for communication between a DNS client and server, which is used for Domain Name Service (DNS).
        socket_connect($sock, '8.8.8.8', 53);
        // You might want error checking code here based on the value of $res
        socket_getsockname($sock, $addr);
        socket_shutdown($sock);
        socket_close($sock);

        return $addr;
    }

    public static function sendCheckPing(string $domain): bool
    {
        // check that domain is wrapped in http or https

        $domain = str_starts_with($domain, 'http') ? $domain : 'http://'.$domain;

        // ensure domain ends with trailing /
        $domain = rtrim($domain, '/').'/';

        // file get contents acts as a simple curl request, not good for large bodies, but here is appropreate
        $response = file_get_contents($domain.self::$migrationUrl.'/ping');

        return trim($response) === 'pong';
    }

    public static ?string $excludedTablesRegex = null;

    /**
     * This would be the Parent server sending a set of resources as a manifest <map> to the child peer
     *
     * @see https://stackoverflow.com/questions/27309773/is-there-a-limit-of-the-size-of-response-i-can-read-over-http
     */
    public static function enablePull(array $allowedDirectories): bool
    {
        return Route::regexMatch(
            '#^'.self::$migrationUrl.'/?(.*)?#i',
            static function (string $getPath = '') use ($allowedDirectories) {
                if ($getPath === 'ping') {
                    http_response_code(200);

                    echo 'pong';

                    exit(0);
                }

                self::unlinkMigrationFiles();

                self::$currentTime = self::$remoteServerTime = microtime(true);

                ColorCode::colorCode('Migration Request '.print_r($_POST, true), iColorCode::CYAN);

                $requestedDirectoriesString = $_POST['directories'] ?? '';

                self::$license = $_POST['license'] ?? '';

                if ('' === self::$license) {
                    throw new PrivateAlert('License is empty!');
                }

                self::$remoteUrl = $_POST['url'] ?? '';

                if (array_key_exists('excludedTablesRegex', $_POST)) {
                    self::$excludedTablesRegex = $_POST['excludedTablesRegex'];
                }

                ColorCode::colorCode('Running checkLicense');

                self::checkLicense(self::$license);

                ColorCode::colorCode('checkLicense Passed');

                if (headers_sent($file, $line)) {
                    throw new PrivateAlert("Headers already sent in file ($file) on line ($line)! The migrations cannot work if content is sent out of order.");
                }

                header('abspath: '.CarbonPHP::$app_root);

                self::$localIp = gethostbyname(gethostname());

                header(($localIpHeader = 'local_ip').': '.self::$localIp);

                self::$publicIp = self::getPublicIpAddress();

                header(($publicIpHeader = 'public_ip').': '.self::$publicIp);

                if (array_key_exists(self::SKIP_MYSQL_DATA_DUMP_FLAG, $_POST)) {
                    self::$MySQLDataDump = false;
                }

                if ('' !== $getPath) {
                    $requestHeaders = getallheaders();

                    foreach ($requestHeaders as $key => $value) {
                        if (str_starts_with($key, $localIpHeader)) {
                            self::$remoteLocalIp = $value;
                        }

                        if (str_starts_with($key, $publicIpHeader)) {
                            self::$remotePublicIp = $value;
                        }
                    }

                    $getPath = base64_decode($getPath, true);

                    $absolutePath = CarbonPHP::$app_root.$getPath;

                    $absolutePath = realpath($absolutePath);

                    $realpathRoot = realpath(CarbonPHP::$app_root);

                    if (!str_starts_with($absolutePath, $realpathRoot)) {
                        throw new PrivateAlert("The requested path ($absolutePath) is not a subdirectory of the root path ($realpathRoot). This event has been logged.");
                    }

                    self::sendMigrationData($absolutePath);

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

                        ColorCode::colorCode("The requested ($requestedDirectoriesString) had directories not allowed by this server. Allowed values :: ".print_r($allowedDirectories, true));

                        // omit publicly logging what is allowed
                        throw new PrivateAlert("One or more directories you have requested are not listed as available! ($requestedDirectoriesString)");
                    }

                    ColorCode::colorCode('No media directories requested.');
                } elseif (false === self::$MySQLDataDump) {
                    throw new PrivateAlert('Request failed as no migration directories were provided and no mysql data was explicitly requests. Nothing to do.');
                }

                if (self::$MySQLDataDump) {
                    ColorCode::colorCode(
                        'About to dump mysql schemas <'.Database::$carbonDatabaseName.'> to file.',
                        iColorCode::CYAN
                    );

                    self::dumpAll();
                } else {
                    ColorCode::colorCode('Detected user param ('.self::SKIP_MYSQL_DATA_DUMP_FLAG.') skipping database dump.');
                }

                if ([] === $requestedDirectories) {
                    echo self::MIGRATION_COMPLETE.'No media directories requested. Done.';

                    exit(0);
                }

                ColorCode::colorCode('Preparing to create a manifest to media!!', iColorCode::BACKGROUND_CYAN);

                $zipDirectory = CarbonPHP::$app_root.self::$migrationFolder.DS.'zip'.DS;

                if (true === is_dir($zipDirectory)) {
                    self::clearDirectory($zipDirectory);
                } else {
                    Files::createDirectoryIfNotExist($zipDirectory);
                }

                // server needs to compile directories
                foreach ($requestedDirectories as $media) {
                    if (false === is_string($media)) {
                        throw new PrivateAlert('An argument passed in the array $directories was not of type string '.print_r($allowedDirectories, true));
                    }

                    // create a list of all files the requesting server will need to transfer
                    $realpath = self::manifestDirectory($media);    // do not remove the newline

                    echo self::$migrationUrl.'/'.base64_encode($realpath).PHP_EOL;
                }

                echo self::MIGRATION_COMPLETE;

                flush();

                ColorCode::colorCode(self::MIGRATION_COMPLETE);

                exit(0);
            }
        );
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

        if (!in_array($_SERVER['SERVER_PORT'], [80, 443], true)) {
            $port = ":{$_SERVER['SERVER_PORT']}";
        } else {
            $port = '';
        }

        return '//'.$server_name.$port;
    }

    public static function licenseFilePath(): string
    {
        return CarbonPHP::$app_root.'migration-license.php';
    }

    public function getLicense(): void
    {
        if (null !== self::$license) {
            return;
        }

        $licenseFile = self::licenseFilePath();

        if (false === file_exists($licenseFile)) {
            self::createLicenseFile($licenseFile);
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
        $createLicense = uniqid('migration_', true).Cryptography::genRandomHex(4000);

        if (false === file_put_contents(
            $licensePHPFilePath,
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

    public static function checkLicense(string $checkLicense, ?string $licensePHPFilePath = null): void
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

                $msg = "No license was detected. We have created a new one and stored it to ($licensePHPFilePath).";

                ColorCode::colorCode($msg, iColorCode::BACKGROUND_RED);

                http_response_code(401); // Unauthorized

                echo $msg;

                exit(6);
            }

            $realLicense = include $licensePHPFilePath;

            if ($realLicense !== $checkLicense) {
                $msg = "The license ($checkLicense) provided did not match the expected.";

                ColorCode::colorCode($msg, iColorCode::BACKGROUND_RED);

                http_response_code(401); // Unauthorized

                echo $msg;

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
        try {
            $files = [];

            Files::createDirectoryIfNotExist($path);

            $directory = new \DirectoryIterator($path);

            foreach ($directory as $file) {
                $filePath = $file->getPathname();

                if ($file->isDot()) {
                    continue;
                }

                if (false === $file->isDir()) {
                    $files[] = $filePath;
                } else {
                    if (false === self::directorySizeLessThan($filePath, self::$maxFolderSizeForCompressionInMb)) {
                        // recursive, logically simple; runtime expensive
                        $files += self::compileFolderFiles($filePath);

                        continue;
                    }

                    $isDirEmpty = !(new \FilesystemIterator($filePath))->valid();

                    if ($isDirEmpty) {
                        $files[] = $filePath.DS;

                        continue;
                    }

                    $files[] = self::zipFolder($filePath);
                }
            }

            return $files;
        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);

            exit(8);
        }
    }

    public static function manifestDirectory(string $path): string
    {
        try {
            $hash = base64_encode($path);

            $relativePath = self::$migrationFolder.DS.self::$migrationFolderPrefix.self::$currentTime.DS.'media_'.$hash.'_'.self::$currentTime.'.txt.php';

            $storeToFile = CarbonPHP::$app_root.$relativePath;

            $files = self::compileFolderFiles($path);   // array

            $files[] = PHP_EOL.self::MIGRATION_COMPLETE;

            $allFiles = self::selfHidingFile(implode(PHP_EOL, $files));

            if (false === file_put_contents($storeToFile, $allFiles)) {
                throw new PrivateAlert("Failed to store the RecursiveDirectoryIterator contents to file ($storeToFile)");
            }

            return $relativePath;
        } catch (Throwable $e) {
            echo $e->getMessage();

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
