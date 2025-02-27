<?php

declare(strict_types=1);

/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 9/2/17
 * Time: 8:54 AM
 */

namespace CarbonPHP\Abstracts;

use CarbonPHP\Abstracts\Error\PrivateAlert;
use CarbonPHP\Abstracts\Interfaces\iColorCode;

abstract class Fork
{
    /**
     * Wait for any child process to change status and return its PID and status.
     *
     * @param array $childPids array of child process IDs to monitor
     *
     * @return array|null returns an array with 'pid' and 'status' of the exited child, or null if none have exited
     */
    public static function waitForAnyChildProcess(array &$childPids): ?array
    {
        foreach ($childPids as $index => $pid) {
            $status = 0;

            // Check if this child process has exited without blocking
            $result = pcntl_waitpid($pid, $status, WNOHANG);

            if ($result > 0) {
                // A child process has exited, remove it from the list
                unset($childPids[$index]);

                return ['pid' => $result, 'status' => $status];
            }

            if ($result < 0) {
                // Error, remove the PID as it is no longer valid
                unset($childPids[$index]);
            }
        }

        // No child process has changed status
        return null;
    }

    /**
     * Execute multiple callables each in its own child process.
     *
     * @param array $tasks array of callables to be executed in child processes
     */
    public static function executeInChildProcesses(array $tasks, ?callable $returnHandler = null): void
    {
        $childPids = [];

        foreach ($tasks as $task) {
            $pid = self::safe($task);

            // zero indicates pcntl_fork is not available and the task was executed in the current process
            if (0 !== $pid) {
                $childPids[] = $pid;
            }
        }

        // Parent waits for all child processes to finish
        while (count($childPids) > 0) {
            sleep(1);

            $exitStatus = self::waitForAnyChildProcess($childPids);

            if (null !== $exitStatus) {
                ColorCode::colorCode("Child process ({$exitStatus['pid']}) exited with status ({$exitStatus['status']})");

                $returnHandler($exitStatus['pid'], $exitStatus['status']);
            }
        }
    }

    /** If a callable function is passes the interpreter will attempt to
     * fork using the pncl library and then execute the desired closure.
     * If no arguments are passed than the current execution environment
     * will become "Demonized". All masks will be set to 0 and the new
     * working environment will be the root dir. This can be exceptionally
     * dangerous and should only be used if your absolutely
     * sure you know whats going on.
     *
     * @param callable|null $call
     *
     * @return int
     *
     * @throws \Exception
     */
    public static function become_daemon(callable $call): int        // do not use this unless you know what you are doing
    {
        if (!extension_loaded('pcntl')) {
            throw new PrivateAlert('You must have the PCNTL extension installed. See Carbon PHP for documentation.');
        }

        if ($pid = pcntl_fork()) {  // Parent
            return $pid;
        }

        if ($pid < 0) {
            throw new PrivateAlert('Failed to fork');
        }

        define('FORK', true);

        /* child becomes our daemon */
        posix_setsid();

        chdir('/');   // What does this do ?

        umask(0);        // Give access to nothing

        register_shutdown_function(function () {
            session_abort();
            posix_kill(posix_getpid(), SIGHUP);
            exit(0);
        });

        if (is_callable($call)) {
            $call();
            exit(0);
        }

        return posix_getpid();
    }

    /** This will safely execute a passed closure if the pncl library in not
     * found in the environment. This should only be used when the callable
     * function does not access or modify the database or session. Carbon uses
     * this when realtime communication using named pipe is requested. It only
     * speeds up execution, however it is not required and will never throw an
     * error.
     *
     * @param callable $closure
     *
     * @return int
     */
    public static function safe(callable $closure): int
    {
        if (!extension_loaded('pcntl')) {
            ColorCode::colorCode('PCNTL extension not found. Executing in current process.', iColorCode::BACKGROUND_YELLOW);

            $closure();

            return 0;
        }

        if ($pid = pcntl_fork()) {    // return child id for parent and 0 for child
            return $pid;             // Parent
        }

        if ($pid < 0) {
            throw new \RuntimeException('Failed to fork');
        }

        define('FORK', true);

        // Database::resetConnection();
        // fclose(STDIN); -- unset
        register_shutdown_function(static function () {
            session_abort();
            posix_kill(posix_getpid(), SIGHUP);
            exit(0);
        });

        $closure();

        exit(0);
    }
}
