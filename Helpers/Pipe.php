<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 8/23/17
 * Time: 11:00 AM
 */

namespace Carbon\Helpers;

use Carbon\Error\ErrorCatcher;

class Pipe
{
    public static function named($fifoPath)  // Arbitrary)
    {

        if (file_exists($fifoPath)) unlink($fifoPath);          // We are always the master, hopefully we'll catch the kill this time

        posix_mkfifo($fifoPath, 0644);                    // create a named pipe

        //$user = get_current_user();                           // get current process user

        //exec("chown -R {$user}:{$user} $fifoPath");           // We need to modify the permissions so users can write to it

        $fifoFile = fopen($fifoPath, 'r+');              // Now we open the named pipe we Already created

        stream_set_blocking($fifoFile, false);           // setting to true (resource heavy) activates the handshake feature, aka timeout

        return $fifoFile;                                       // File descriptor
    }


    public static function send(string $value, string $fifoPath)
    {
        try {
            if (!file_exists($fifoPath))
                return false; //sortDump('Fuck me!'); // if it does

            posix_mkfifo($fifoPath, 0644);

            //$user = get_current_user();                                // get current process user

            //exec("chown -R {$user}:{$user} $fifoPath");    // We need to modify the permissions so users can write to it

            # sortDump(substr(sprintf('%o', fileperms($fifoPath)), -4) . PHP_EOL);  -- file permissions

            $value = $value . PHP_EOL; // safely send, dada needs to end in null

            $fifo = fopen($fifoPath, 'r+');

            fwrite($fifo, $value, strlen($value) + 1);

            fclose($fifo);
        } catch (\Exception $e) {
            ErrorCatcher::generateLog($e);
        }
    }
}



