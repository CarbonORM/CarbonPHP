<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/27/17
 * Time: 10:26 PM
 *
 * This class is desigend to handle the session storage.
 * http://php.net/manual/en/function.session-set-save-handler.php
 *
 * If true is passed to the second parameter of our constructor, our
 * $_SESSION variables will be stored in the database.
 *
 */

namespace CarbonPHP;

use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Serialized;
use CarbonPHP\Programs\Background;
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Tables\Sessions;
use PDOException;
use SessionHandlerInterface;
use Throwable;
use function define;
use function defined;
use function is_array;
use function is_callable;


// most important line - session_set_save_handler($this, false)
class Session implements SessionHandlerInterface
{
    use Background, ColorCode;


    protected static ?Session $singleton = null;
    /**
     * @var null|string - if we need to close or pause the session in the middle of execution,
     * this will persistently hold our session_id.
     */
    public static ?string $session_id;

    /**
     * @var null|string $user_id - After a session is closed the session data is serialized and removed
     * from the global (accessible) scope.
     */
    public static ?string $user_id;

    /**
     * @var callable $callback - if the session is reset using the startApplication function,
     * this callable function will be executed. You can set this variable in the configuration.
     */
    private static $callback;

    /**
     * Session constructor. This
     * @param string|null $ip
     * @param bool $dbStore
     */
    public function __construct(string $ip = null, $dbStore = false)
    {
        static $count = false;

        if (!$count) {
            $count = true;

            self::$singleton = $this;

            session_write_close(); //cancel the session's auto start, important

            headers_sent() or ini_set('session.use_strict_mode', 1);

            if ($dbStore && !headers_sent()) {
                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('session.gc_probability', 1);  // Clear any lingering session data in default locations
                if (!session_set_save_handler($this, false)) {           // set this class as the session handler
                    throw new PublicAlert('Session failed to store remotely');
                }
            }

            if (CarbonPHP::$cli && (!CarbonPHP::$socket || WebSocket::$minimiseResources)) {
                return;
            }
        }

        try {
            // this should not throw an error.. but if it doesnt we will catch and die
            if (false === session_start()) {
                throw new PublicAlert('CarbonPHP failed to start your session');
            }

            static::$session_id = session_id();

            $_SESSION['id'] = array_key_exists('id', $_SESSION ??= []) ? $_SESSION['id'] : false;

        } catch (Throwable $e) {
            ErrorCatcher::generateBrowserReportFromError($e); // This terminates!
        }
    }

    /**
     *   Pauses the current session. This is required if you plan to fork you process and
     *   continue with session manipulation.
     */
    public static function pause(): void
    {
        static::$session_id = session_id();
        session_write_close();
    }

    /**
     *   After a session is stopped with session_write_close() or paused with self::pause()
     *   It maybe resumed assuming the original id was stored in self::$session_id
     * @param string|null $session_id
     * @return Session
     */
    public static function resume(string $session_id = null): self
    {
        if ($session_id !== null) {
            static::$session_id = $session_id;
        }
        session_id(static::$session_id);
        session_start();
        return self::$singleton;
    }


    /**
     * Change the callback run if self::update() is called.
     * @param callable|null $lambda
     */
    public static function updateCallback(callable $lambda = null): void
    {
        self::$callback = $lambda;
    }

    /**
     * This handles our users state. If the user goes form logged-in to logged-out
     * the outer html-wrapper will be sent.
     * @param bool $clear - if true is passed serialized data will be set to null
     */
    public static function update($clear = false): void
    {
        global $user;
        static $count = 0;
        $count++;

        $_SESSION['id'] ??= false;

        if ($clear || !$_SESSION['id']) {
            Serialized::clear();
        }

        if (!is_array($user)) {
            $user = array();
        }

        if (static::$user_id = $_SESSION['id']) {
            $_SESSION['X_PJAX_Version'] = 'v' . CarbonPHP::$site_version . 'u' . $_SESSION['id'];
        } // force reload occurs when X_PJAX_Version changes between requests

        if (!isset($_SESSION['X_PJAX_Version'])) {
            $_SESSION['X_PJAX_Version'] = CarbonPHP::$site_version;     // static::$user_id, keep this static
        }

        Request::setHeader('X-PJAX-Version: ' . $_SESSION['X_PJAX_Version']);

        /* If the session variable changes from the constant we will
         * send the full html page and notify the pjax js to reload
         * everything
         * */

        if (is_callable(self::$callback)) {
            /** @noinspection OnlyWritesOnParameterInspection */
            ($lambda = self::$callback)($clear);    // you must have callable in a variable in fn scope
        }
        if (!defined('X_PJAX_VERSION')) {
            define('X_PJAX_VERSION', $_SESSION['X_PJAX_Version']);
        }
        Request::sendHeaders();  // Send any stored headers
    }

    /**
     * This will remove our session data from our scope and the database
     * @throws Error\PublicAlert
     */
    public static function clear(): void
    {

        $session = Rest::getDynamicRestClass(Sessions::class);

        try {
            $id = session_id();

            $_SESSION = array();

            session_write_close();

            /** @noinspection PhpUndefinedMethodInspection */
            $session::Delete($_SESSION, $id, []);

            session_start();

        } catch (PDOException $e) {
            ErrorCatcher::generateBrowserReportFromError($e);   // this terminates!
        }
    }

    /** This function was created to make sure all socket request come from
     * an existing user who has a session stored in our database. If database
     * session storage is turned off this method will fail and exit.
     *
     * @param $ip - the ip address to look up from our database.
     * @return bool
     */
    public static function verifySocket($ip): bool
    {
        self::colorCode('Verify Socket');

        if ($ip) {
            $_SERVER['REMOTE_ADDR'] = $ip;
        }

        $_SERVER['HTTP_COOKIE'] ??= '';

        self::colorCode('User sent Cookie(s) :: ' . $_SERVER['HTTP_COOKIE'] . "\n\n");

        if (false === @preg_match('#PHPSESSID=([^;\s]+)#', $_SERVER['HTTP_COOKIE'], $array, PREG_OFFSET_CAPTURE)) {
            self::colorCode('Failed to verify socket IP address.', 'red');
            return false;
        }

        self::colorCode('Parsed Session ID Correctly');

        $session_id = $array[1][0] ?? false;

        if (false === $session_id) {
            self::colorCode("\nCould not parse session id\n", 'red');
            return false;
        }

        $db = Database::database();

        $session = Rest::getDynamicRestClass(Sessions::class);

        $sql = 'SELECT count(*) FROM ' . $session::TABLE_NAME . ' WHERE ' . $session::USER_IP . ' = ? AND ' . $session::SESSION_ID . ' = ? LIMIT 1';

        $stmt = $db->prepare($sql);

        $stmt->execute([CarbonPHP::$server_ip, $session_id]);

        $session = $stmt->fetchColumn();

        if (!$session) {
            self::colorCode('BAD ADDRESS :: ' . $_SERVER['REMOTE_ADDR'] . "\n\n", 'red');
            return false;
        }

        self::$session_id = $session_id;    // this

        session_id($session_id);

        return true;
    }

    /** This is required for the session save handler interface.
     *  Do no change.
     *
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        $session = Rest::getDynamicRestClass(Sessions::class);

        try {
            Database::database()->prepare('SELECT count(*) FROM ' . $session::TABLE_NAME . ' LIMIT 1')->execute();
        } catch (PDOException $e) {
            if ($e->getCode()) {
                print "<h1>Setting up database {$e->getCode()}</h1>";
                Database::setUp();
                exit(1);
            }
        }
        return true;
    }


    public static function writeCloseClean(): void
    {
        session_write_close();
        self::$session_id = null;
        self::$user_id = null;
    }

    /** Make user our session data gets stored in the db.
     * @return bool
     */
    public function close(): bool
    {
        register_shutdown_function('session_write_close');
        return true;
    }

    /** read
     * @param string $id
     * @return string
     */
    public function read($id): string
    {
        $session = Rest::getDynamicRestClass(Sessions::class);
        // TODO - if ip has changed and session id hasn't invalidated // assume man in the middle not cell phone tower change
        $stmt = Database::database()->prepare('SELECT ' . $session::SESSION_DATA . ' FROM ' . $session::TABLE_NAME . ' WHERE ' . $session::SESSION_ID . ' = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: '';
    }

    /** This function should never be called by you directly. It can be invoked using
     * session_write_close().
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data): bool
    {
        $db = Database::database();
        $session = Rest::getDynamicRestClass(Sessions::class);
        if (empty(self::$user_id)) {
            self::$user_id = $_SESSION['id'] ??= false;
        }
        $NewDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + 1 d,lay'));  // so from time of last write and whenever the gc_collector hits

        try {
            $db->prepare('REPLACE INTO ' . $session::TABLE_NAME . ' SET ' . $session::SESSION_ID . ' = ?, ' . $session::USER_ID . ' = UNHEX(?), ' . $session::USER_IP . ' = ?,  ' . $session::SESSION_EXPIRES . ' = ?, ' . $session::SESSION_DATA . ' = ?')->execute([
                $id,
                static::$user_id,
                CarbonPHP::$server_ip,
                $NewDateTime,
                $data
            ]);
        } catch (PDOException $e) {
            sortDump($e); // todo - error catching
        }
        return true;
    }

    /** This method can be run explicit or through
     *      session_destroy()
     * @param string $id
     * @return bool
     */
    public function destroy($id): bool
    {
        $db = Database::database();
        $session = Rest::getDynamicRestClass(Sessions::class);
        return $db->prepare('DELETE FROM ' . $session::TABLE_NAME . ' WHERE ' . $session::USER_ID . ' = UNHEX(?) OR ' . $session::SESSION_ID . ' = ?')->execute([self::$user_id, $id]) ?
            true : false;
    }

    /** This is our garbage collector. If a session is expired attempt to remove it.
     * This function is executed via a probability. See link for more details.
     * @link http://php.net/manual/en/features.gc.php
     * @param int $maxLife
     * @return bool
     */
    public function gc($maxLife): bool
    {
        $db = Database::database();
        $session = Rest::getDynamicRestClass(Sessions::class);
        return $db->prepare('DELETE FROM ' . $session::TABLE_NAME . ' WHERE (UNIX_TIMESTAMP(' . $session::SESSION_EXPIRES . ') + ? ) < UNIX_TIMESTAMP(?)')->execute([$maxLife, date('Y-m-d H:i:s')]) ?
            true : false;
    }
}
