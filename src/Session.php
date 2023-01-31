<?php /** @noinspection PhpMissingParamTypeInspection */

/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/27/17
 * Time: 10:26 PM
 *
 * This class is designed to handle the session storage.
 * http://php.net/manual/en/function.session-set-save-handler.php
 *
 * If true is passed to the second parameter of our constructor, our
 * $_SESSION variables will be stored in the database.
 *
 */

namespace CarbonPHP;

use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Background;
use CarbonPHP\Helpers\ColorCode;
use CarbonPHP\Helpers\Serialized;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Restful\RestQueryValidation;
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

    protected static ?Session $singleton = null;

    protected static ?iRestSinglePrimaryKey $session_table = null;

    private static string $sessionData = '';

    private static bool $sessionUpdated = false;

    protected static bool $sessionContinued = false;
    /**
     * @var null|string - if we need to close or pause the session in the middle of execution,
     * this will persistently hold our session_id.
     */
    public static ?string $session_id;

    /**
     * @var null|string $user_id - After a session is closed the session data is serialized and removed
     * from the global (accessible) scope.
     */
    public static ?string $user_id = "0";

    public static bool $endingSession = false;

    /**
     * @var callable $callback - if the session is reset using the startApplication function,
     * this callable function will be executed. You can set this variable in the configuration.
     */
    private static $callback;

    /**
     * Session constructor. This
     * @param bool $dbStore
     */
    public function __construct($dbStore = false)
    {

        static $count = false;

        if (!$count) {

            $count = true;

            self::$singleton = $this;   // I want the destructor to happen at the end of the process life

            session_write_close();      // cancel the session's auto start, important

            if (false === headers_sent()) {

                ini_set('session.use_strict_mode', 1);

            }

            if ($dbStore && !headers_sent()) {

                ini_set('session.gc_probability', 1);  // Clear any lingering session data in default locations

                if (false === session_set_save_handler($this, false)) {           // set this class as the session handler

                    throw new PublicAlert('Session failed to store remotely; session_set_save_handler(...) returned false.');

                }

            }

            if (CarbonPHP::$cli && (!CarbonPHP::$socket || WebSocket::$minimiseResources)) {

                return;

            }

        }

        try {

            if (false === register_shutdown_function(static fn() => session_write_close())) {

                throw new PublicAlert('Failed to register shutdown function');

            }

            // this should not throw an error.. but if it doesnt we will catch and die
            if (false === session_start()) {

                throw new PublicAlert('CarbonPHP failed to start your session; session_start() failed.');

            }

            static::$session_id = session_id();

            $GLOBALS['session_id'] = static::$session_id;

            $_SESSION['id'] = array_key_exists('id', $_SESSION ??= []) ? $_SESSION['id'] : false;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e); // This terminates!

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

        try {

            $session_class_name = Rest::getDynamicRestClass(Sessions::class);

            self::$session_table ??= new $session_class_name;

            $id = session_id();

            $_SESSION = array();

            session_write_close();

            self::$session_table::Delete($_SESSION, $id, []);   // in theory this will not throw anything

            session_start();

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);   // this terminates!

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

        ColorCode::colorCode('Verify Socket');

        if ($ip) {
            $_SERVER['REMOTE_ADDR'] = $ip;
        }

        $_SERVER['HTTP_COOKIE'] ??= '';

        ColorCode::colorCode('User sent Cookie(s) :: ' . $_SERVER['HTTP_COOKIE'] . "\n\n");

        if (false === @preg_match('#PHPSESSID=([^;\s]+)#', $_SERVER['HTTP_COOKIE'], $array, PREG_OFFSET_CAPTURE)) {
            ColorCode::colorCode('Failed to verify socket IP address.', 'red');

            return false;
        }

        ColorCode::colorCode('Parsed Session ID Correctly');

        $session_id = $array[1][0] ?? false;

        if (false === $session_id) {
            ColorCode::colorCode("\nCould not parse session id\n", 'red');

            return false;
        }

        $session = Rest::getDynamicRestClass(Sessions::class);

        $db = Database::database(true);

        $sql = 'SELECT count(*) FROM ' . $session::TABLE_NAME . ' WHERE ' . $session::USER_IP . ' = ? AND ' . $session::SESSION_ID . ' = ? LIMIT 1';

        $stmt = $db->prepare($sql);

        $stmt->execute([CarbonPHP::$server_ip, $session_id]);

        $session = $stmt->fetchColumn();

        if (!$session) {

            ColorCode::colorCode('BAD ADDRESS :: ' . $_SERVER['REMOTE_ADDR'] . "\n\n", 'red');

            return false;

        }

        self::$session_id = $session_id;    // this

        session_id($session_id);

        return true;
    }

    /**
     * @return iRestSinglePrimaryKey
     */
    public static function getSessionTable(): iRestSinglePrimaryKey
    {

        if (null === self::$session_table) {

            $table_name = Rest::getDynamicRestClass(Sessions::class);    // all because custom prefixes and callbacks exist

            self::$session_table = new $table_name(); // This is only for referencing and is not actually needed as an instance.

        }

        return self::$session_table;

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

        try {

            $GLOBALS['json']['session']['database_closed_committed'] = false;

            Database::database(false);

            return true;

        } catch (PDOException $e) {

            Database::TryCatchPDOException($e); // this will terminate 99% of the time

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e); // this will terminate

        }

        return true;

    }


    public static function writeCloseClean(): void
    {

        if (session_status() === PHP_SESSION_ACTIVE) {

            session_write_close();

        }

        self::$session_id = null;

        self::$user_id = null;

    }


    /** read
     * @param string $id
     * @return string
     * @
     */
    public function read($id): string
    {

        $session_table_row = [];

        $session_table = self::getSessionTable();

        $newDateTime = date('Y-m-d H:i:s', strtotime("+1 week"));  // so from time of last write and whenever the gc_collector hits

        $insertIgnore = [
            $session_table::IGNORE => [
                $session_table::SESSION_ID => self::$session_id = $id,
                $session_table::USER_ID => static::$user_id,
                $session_table::USER_IP => CarbonPHP::$server_ip,
                $session_table::SESSION_EXPIRES => $newDateTime,
                $session_table::SESSION_DATA => self::$sessionData
            ]
        ];


        if (false === $session_table::post($insertIgnore)) {

            return false;

        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if (false === $session_table::get($session_table_row, $id, [
                iRest::SELECT => [
                    $session_table::SESSION_DATA
                ],
                iRest::LOCK => iRest::FOR_UPDATE
            ])) {

            return false;

        }

        $sessionKey = $session_table::COLUMNS[$session_table::SESSION_DATA];

        if (array_key_exists($sessionKey, $session_table_row)) {

            self::$sessionContinued = true;

            self::$sessionData = $session_table_row[$sessionKey];

            return $session_table_row[$sessionKey];

        }

        return '';

    }

    /** This function should never be called by you directly. It can be invoked using
     * session_write_close().
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data): bool
    {
        self::$sessionData = $data;
        self::$sessionUpdated = true;
        return true;
    }

    private static function updateSession(): void
    {

        if (false === self::$sessionUpdated) {

            return;

        }

        if (empty(self::$user_id)) {

            self::$user_id = $_SESSION['id'] ??= false;

        }

        $newDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + 1 d,lay'));  // so from time of last write and whenever the gc_collector hits

        try {

            $session_table_row = [];

            $session_table = self::getSessionTable();

            $preCommitValue = Rest::$commit;

            Rest::$commit = false;

            $successful = $session_table::put($session_table_row, null, [
                iRest::REPLACE => [
                    $session_table::USER_ID => static::$user_id,
                    $session_table::USER_IP => CarbonPHP::$server_ip,
                    $session_table::SESSION_EXPIRES => $newDateTime,
                    $session_table::SESSION_DATA => self::$sessionData,
                    $session_table::SESSION_ID => self::$session_id,

                ]
            ]);

            if (false === $successful) {

                throw new PublicAlert('Failed to update session');

            }

            Rest::$commit = $preCommitValue;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    public function close(): bool
    {

        self::updateSession();

        $GLOBALS['json']['session']['@close'] = $_SESSION;

        $GLOBALS['json']['session']['?close'] = 'closing session from (' . __FILE__ . ') from method (' .  __METHOD__ . ') at line (' . __LINE__ . ')';

        try {

            if (session_status() !== PHP_SESSION_ACTIVE) {

                $GLOBALS['json']['session_error'] = 'attempted session close with no PHP_SESSION_ACTIVE';

                return true;

            }

            $db = Database::database(false);

            if (false === $db->inTransaction()) {

                $GLOBALS['json']['session_warning'] = 'no remaining transaction';

                    throw new PublicAlert('Database not in transaction.');

            }

            if (false === $db->commit()) {

                $GLOBALS['json']['session_error'] =' Database commit failed.';

                throw new PublicAlert('Database commit failed.');

            }

            $GLOBALS['json']['session']['database_closed_committed'] = true;

            return true;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    /** This method can be run explicit or through
     *      session_destroy()
     * @param $session_id
     * @return bool
     */
    public function destroy($session_id): bool
    {
        try {
            $session_table_row = [];

            $session_table = self::getSessionTable();

            return $session_table::Delete($session_table_row, $session_id, [
                iRest::WHERE => [
                    [
                        $session_table::USER_ID => self::$user_id,
                        $session_table::SESSION_ID => $session_id
                    ]
                ]
            ]);

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

        }

        return false;
    }

    /** This is our garbage collector. If a session is expired attempt to remove it.
     * This function is executed via a probability. See link for more details.
     * @link http://php.net/manual/en/features.gc.php
     * @param int $max_lifetime
     * @return int|false
     * @throws PublicAlert
     */
    public function gc(int $max_lifetime): int|false
    {

        $session = Rest::getDynamicRestClass(Sessions::class);

        $db = Database::database(false);

        return $db->prepare('DELETE FROM ' . $session::TABLE_NAME . ' WHERE (UNIX_TIMESTAMP(' . $session::SESSION_EXPIRES . ') + ? ) < UNIX_TIMESTAMP(?)')->execute([$max_lifetime, date('Y-m-d H:i:s')]);

    }

}
