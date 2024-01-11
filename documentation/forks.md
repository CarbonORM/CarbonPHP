# Forks


Methods

1) `public static function become_daemon(callable $call = null) : int`
  -  **Purpose:** This method attempts to fork the current process using the PCNTL extension. If a callable function is passed, it executes the function in the forked process. If no arguments are passed, the current execution environment becomes "demonized" with all masks set to 0, and the working directory set to the root directory. This method should be used with caution.
  - **Parameters:**
    - **callable|null $call:** The callable function to be executed in the forked process.
    - **Returns:** The PID (Process ID) of the forked process.
    - **Exceptions:** Throws a PrivateAlert exception if the PCNTL extension is not loaded or if forking fails.
    - **Usage:** Fork::become_daemon($callableFunction);
2) `public static function safe(callable $closure = null) : int`
  - **Purpose:** Safely executes a passed closure if the PCNTL library is not found in the environment. It should be used when the callable function does not access or modify the database or session. This method is used in CarbonPHP for real-time communication using named pipes.
  - **Parameters:**
    - callable|null $closure: The closure to be executed.
  - **Returns:** The PID of the forked process or 0 if PCNTL is not loaded.
  - **Exceptions:** Throws a RuntimeException if forking fails.
  - **Usage:** Fork::safe($closureFunction);
  - **Additional Information**
    - The become_daemon method is particularly powerful and potentially dangerous, as it changes the process's working environment and permissions. It is crucial to use this method only when fully understanding its implications.


The safe method provides a more cautious approach to forking, ensuring compatibility even when the PCNTL extension is not available.
Both methods use the PCNTL extension for process control, a capability essential for creating multi-process applications in PHP.
These methods are designed to work within the context of the CarbonPHP framework and may require specific environment settings to function correctly.


# Source
View the [latest code here](https://github.com/CarbonORM/CarbonPHP/blob/lts/carbonphp/abstracts/Fork.php).

```php
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
```








I plan to write a pnctl library for Windows one day. If anyone would like to help that would be much appreciated. 
Contact me at Richard@Miles.Systems and thank you in advance. Until then forking will only be available for linux and 
osx users. You can use Fork::safe() to help avoid cross-platform issues. So programs simply require the library, such as 
websockets.

