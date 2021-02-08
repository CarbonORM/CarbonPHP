<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 8/23/17
 * Time: 11:00 AM
 */

namespace CarbonPHP\Helpers;

use CarbonPHP\Error\ErrorCatcher;

/**
 * Class Pipe
 * @package Carbon\Helpers
 *
 * In both method in this class I have commented out changing the
 * user / owner of the named pipe. Some environments MAY require that
 * you change permissions. Over the past few years I've migrated to
 * many diffrent hosting solutions and I believe this
 * was a fix on one. But im not positive...
 */
class Pipe
{
    /** This will open a named pipe on our server. This is used for sending
     * information between two active processes on the server. Generally,
     * each user can send data real time to each other using this method.
     * Keep in mind that the fifo file must be unique to the users listening
     * on it.
     *
     *** You need to run the Websocket server under a user who is not root.
     * This most likely means copying Websocketd to the root of the web directory
     * as well as any pem or key files
     *
     * @param string $fifoPath is the location of the fifo file.
     * @return bool|resource
     */
    public static function named(string $fifoPath)  // Arbitrary)
    {

        if (file_exists($fifoPath)) {
            unlink($fifoPath);          // We are always the master, hopefully we'll catch the kill this time
        }

        umask(0000);

        if (!posix_mkfifo($fifoPath, 0666)) {
            print 'Failed to create named Pipe' . PHP_EOL;
            return false;
        }                   # create a named pipe 0644

        // future self, please read the function doc above before wondering why this is commented out

        #$user = get_current_user();                            // get current process user

        #exec("chown -R {$user} $fifoPath");                    // We need to modify the permissions so users can write to it

        $fifoFile = fopen($fifoPath, 'rb+');              // Now we open the named pipe we Already created

        if (false === $fifoFile) {
            return false;
        }

        stream_set_blocking($fifoFile, false);           // setting to true (resource heavy) activates the handshake feature, aka timeout

        return $fifoFile;                                       // File descriptor
    }


    /** Attempt to send a string to a named pipe. This is normally done
     * after forking so error are ungraciously ignored. If you require
     * further error checking you will need to Try {} Catch this..
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

            posix_mkfifo($fifoPath, 0666);              // TODO - don't be a dumbass, start sockets in config

            # $user = get_current_user();                    // get current process user

            #exec("chown -R {$user}:{$user} $fifoPath");    // We need to modify the permissions so users can write to it

            #sortDump(substr(sprintf('%o', fileperms($fifoPath)), -4) . PHP_EOL);  //-- file permissions

            $value .= PHP_EOL; // safely send, dada needs to end in null

            $fifo = fopen($fifoPath, 'wb+');

            fwrite($fifo, $value, \strlen($value) + 1);

            fclose($fifo);
        } catch (\Exception $e) {
            ErrorCatcher::generateLog($e);
        }
        return true;
    }
}



