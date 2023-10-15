<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 9/2/17
 * Time: 8:54 AM
 */

namespace CarbonPHP\Abstracts;

use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;

abstract class Fork
{

    /** If a callable function is passes the interpreter will attempt to
     * fork using the pncl library and then execute the desired closure.
     * If no arguments are passed than the current execution environment
     * will become "Demonized". All masks will be set to 0 and the new
     * working environment will be the root dir. This can be exceptionally
     * dangerous and should only be used if your absolutely
     * sure you know whats going on.
     * @param callable|null $call
     * @return int
     * @throws \Exception
     */
    public static function become_daemon(callable $call = null) : int        // do not use this unless you know what you are doing
    {
        if (!\extension_loaded('pcntl')) {
            throw new PrivateAlert('You must have the PCNTL extension installed. See Carbon PHP for documentation.');
        }

        if ($pid = pcntl_fork()) {  // Parent
            if (\is_callable($call)) {
                return $pid;
            }
            exit;
        }
        if ($pid < 0) {
            throw new PrivateAlert('Failed to fork');
        }

        \define('FORK', TRUE);

        /* child becomes our daemon */
        posix_setsid();

        chdir('/');   // What does this do ?

        umask(0);        // Give access to nothing

        register_shutdown_function(function () {
            session_abort();
            posix_kill(posix_getpid(), SIGHUP);
            exit(1);
        });

        if (\is_callable($call)) {
            $call();
            exit(1);
        }

        return posix_getpid();
    }


    /** This will safely execute a passed closure if the pncl library in not
     * found in the environment. This should only be used when the callable
     * function does not access or modify the database or session. Carbon uses
     * this when realtime communication using named pipe is requested. It only
     * speeds up execution, however it is not required and will never throw an
     * error.
     * @param callable|null $closure
     * @return int
     * @throws \Exception
     */
    public static function safe(callable $closure = null) : int
    {
        if (!\extension_loaded('pcntl')) {
            if ($closure !== null) {
                $closure();
            }
            return 0;
        }
        if ($pid = pcntl_fork()) {    // return child id for parent and 0 for child
            return $pid;             // Parent
        }
        if ($pid < 0) {
            throw new \RuntimeException('Failed to fork');
        }
        \define('FORK', true);
        // Database::resetConnection();
        // fclose(STDIN); -- unset
        register_shutdown_function(function () {
            session_abort();
            posix_kill(posix_getpid(), SIGHUP);
            exit(1);
        });

        if ($closure !== null) {
            $closure();
        }
        exit;
    }

}