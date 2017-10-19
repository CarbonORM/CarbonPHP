<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 9/2/17
 * Time: 8:54 AM
 */

namespace Carbon\Helpers;


class Fork
{
    private static function build()
    {
        define('FORK', TRUE);
    }

    public static function become_daemon()          // do not use this unless you know what you are doing
    {
        if (!extension_loaded('pcntl'))
            print 'You must have the PCNTL extencion installed. See Carbon PHP for documentation.' and die;

        if ($pid = pcntl_fork()) exit(1);     // Parent
        elseif ($pid < 0) throw new \Exception('Failed to fork');
        self::build();


        /* child becomes our daemon */
        posix_setsid();

        chdir('/');   // What does this do ?

        umask(0);       // bump..

        register_shutdown_function(function () {
            session_abort();
            posix_kill(posix_getpid(), SIGHUP);
            exit(1);
        });

        return posix_getpid();
    }


    public static function safe(callable $closure = null)
    {
        if (!extension_loaded('pcntl')){
            if ($closure != null) $closure();
            return 0;
        }
        if ($pid = pcntl_fork())    // return child id for parent and 0 for child
            return 0;     // Parent
        elseif ($pid < 0) throw new \Exception('Failed to fork');
        self::build();

        if ($closure != null) $closure();
        // Database::resetConnection();
        // fclose(STDIN); -- unset
        register_shutdown_function(function () { session_abort(); posix_kill(posix_getpid(), SIGHUP); exit(1); });

        exit;
    }

}