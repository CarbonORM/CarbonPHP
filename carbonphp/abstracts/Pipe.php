<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 8/23/17
 * Time: 11:00 AM
 */

namespace CarbonPHP\Abstracts;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;

/**
 * Class Pipe
 * @package Carbon\Helpers
 *
 * In both method in this class I have commented out changing the
 * user / owner of the named pipe. Some environments MAY require that
 * you change permissions. Over the past few years I've migrated to
 * many different hosting solutions and I believe this
 * was a fix on one. But im not positive...
 */
abstract class Pipe
{

    public static string $fifoDelimiter = PHP_EOL . __CLASS__ . ':' . __LINE__ . PHP_EOL;

    public static string $channelDirectory = 'tmp' . DIRECTORY_SEPARATOR . 'fifo' . DIRECTORY_SEPARATOR;

    public static function createFifoChannel(string $channelName)
    {
        $channelName = self::safePipeName($channelName);

        $channelPath = CarbonPHP::$app_root . self::$channelDirectory . $channelName . '.fifo';

        ColorCode::colorCode("Creating fifo channel ($channelPath)", iColorCode::MAGENTA);

        return self::named($channelPath);
    }


    /**
     * @param string $channelName - this can be just a user id
     * @param mixed $data - must be JSON_ENCODE-ABLE
     */
    public static function sendToFifoChannel(string $channelName, string $data): void
    {
        $updateCount = 0;

        $channelName = self::safePipeName($channelName);

        $channelPath = CarbonPHP::$app_root . self::$channelDirectory . $channelName . '*.fifo';

        $channelFIFOS = glob($channelPath);

        foreach ($channelFIFOS as $resourceConnection) {

            try {

                self::send($data, $resourceConnection);

            } catch (Throwable $e) {

                ThrowableHandler::generateLog($e);

            } finally {

                $updateCount++;
            }

        }

        if (!CarbonPHP::$test) {

            ColorCode::colorCode("The channel ($channelName) saw ($updateCount) browsers updated.", iColorCode::MAGENTA);

        }

    }


    /** This will open a named pipe on our server. This is used for sending
     * information between two active processes on the server. Generally,
     * each user can send data real time to each other using this method.
     * Keep in mind that the fifo file must be unique to the users listening
     * on it.
     *
     * @param string $fifoPath is the location of the fifo file.
     * @return bool|resource
     */
    public static function named(string $fifoPath)  // Arbitrary)
    {

        try {

            if (file_exists($fifoPath)) {

                unlink($fifoPath);          // We are always the creator

            }

            umask(0000);

            Files::mkdir($fifoPath);

            if (!posix_mkfifo($fifoPath, 0666)) {

                ColorCode::colorCode("Failed to create named pipe ($fifoPath)", iColorCode::RED);

                return false;

            }                   # create a named pipe 0644

            // the websocket should be running under the same user the webserver is run under
            #$user = get_current_user();                            // get current process user
            #exec("chown -R {$user} $fifoPath");                    // We need to modify the permissions so users can write to it

            $fifoFile = fopen($fifoPath, 'rb+');      // Now we open the named pipe we Already created

            if (false === $fifoFile) {

                return false;

            }

            stream_set_blocking($fifoFile, false);    // setting to true (resource heavy) activates the handshake feature, aka timeout

            ColorCode::colorCode("Named pipe created ($fifoPath).", iColorCode::BLUE);

            return $fifoFile;                                       // File descriptor

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    public static function safePipeName(string $name): string
    {
        return str_replace(".", '_', preg_replace('#/#', ':', $name));
    }

    /** Attempt to send a string to a named pipe. This is normally done
     * after forking so error are logged via error catcher.
     *
     * your http user // group should match that of the process or
     * ws socket server, for example, so the files may work as intended.
     *
     * Sometimes this can be overwritten by the exec function
     *
     * @param string $value
     * @param string $fifoPath
     * @return bool
     */
    public static function send(string $value, string $fifoPath): bool
    {

        try {

            if (!file_exists($fifoPath)) {

                return false;

            }

            umask(0000);

            posix_mkfifo($fifoPath, 0666);

            // this would be needed should you be switching branches
            # $user = get_current_user();                    // get current process user

            #exec("chown -R {$user}:{$user} $fifoPath");    // We need to modify the permissions so users can write to it

            #sortDump(substr(sprintf('%o', fileperms($fifoPath)), -4) . PHP_EOL);  //-- file permissions

            // to safely send, data needs to end with delimiter
            $value .= self::$fifoDelimiter;

            $fifo = fopen($fifoPath, 'wb+');

            fwrite($fifo, $value, \strlen($value) + 1);

            fclose($fifo);
        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

        return true;

    }


}



