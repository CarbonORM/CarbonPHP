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

namespace CarbonPHP\Classes;

use CarbonPHP\Abstracts\Classes\Request;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Files;
use CarbonPHP\Abstracts\Rest;
use CarbonPHP\Abstracts\Serialized;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Classes\Configurations\DatabasePool;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Tables\User_Sessions;
use CarbonPHP\Throwables\PrivateAlert;
use CarbonPHP\Throwables\PublicAlert;
use CarbonPHP\Traits\Cookie;
use Closure;
use SessionHandlerInterface;
use Throwable;
use function is_array;
use function is_callable;


// most important line - session_set_save_handler($this, false)
class Session implements SessionHandlerInterface
{
    use Cookie;

    public const string DATABASE_CLOSED_AND_COMMITTED = 'DATABASE_CLOSED_AND_COMMITTED';

    protected ?iRestSinglePrimaryKey $session_table = null;
    protected string $sessionData = '';

    // TODO - why doesnt $sessionUpdated work? It seems even static variables are not shared between outside classes
    // I think this is only is session store context as the constructor assigns values correctly
    // the global scope is not shared between the two contexts either
    protected bool $sessionUpdated = false;
    protected bool $sessionContinued = false;

    /**
     * @var null|string - if we need to close or pause the session in the middle of execution,
     * this will persistently hold our session_id.
     */
    public ?string $session_id;

    /**
     * @var null|string $user_id - After a session is closed the session data is serialized and removed
     * from the global (accessible) scope.
     */
    public ?string $user_id = "0";

    public string $path {
        set {

        }
    }


    public function __construct(
        public DatabasePool|false $databasePool = false,
        public ?Closure           $callback = null,
        public string             $folder = '',
        public array              $serializeClasses = [],
        ?int                      $lifetime = null,
        ?string                   $path = null,
        ?string                   $domain = null,
        ?bool                     $secure = null,
        ?bool                     $httponly = null,
        ?bool                     $samesite = null,
    )
    {
        // Get default session cookie params
        $sessionParams = session_get_cookie_params();

        // Assign only if null (not passed)
        $this->lifetime = $lifetime ?? $sessionParams['lifetime'];
        $this->path = $path ?? $sessionParams['path'] ?? CarbonPHP::$app_root . 'tmp' . DS . 'sessions' . DS;
        $this->domain = $domain ?? $sessionParams['domain'];
        $this->secure = $secure ?? $sessionParams['secure'];
        $this->httponly = $httponly ?? $sessionParams['httponly'];
        $this->samesite = $samesite ?? ($sessionParams['samesite'] ?? ''); // Handle PHP versions before 7.3

        $this->start();
    }

    public function setSessionCookieParams(): void
    {
        if (false === headers_sent()) {
            ini_set('session.use_strict_mode', 1);
        }

        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
        ]);
    }

    public function savePath()
    {
        Files::createDirectoryIfNotExist($this->path);
        session_save_path($this->path);   // Manually Set where the Users Session Data is stored
    }

    public function start()
    {
        static $count = false;

        try {
            $GLOBALS['json'][self::class]['storeSessionToDatabase'] = false !== $this->databasePool;

            if (!$count) {
                $count = true;

                if (PHP_SESSION_ACTIVE === session_status()
                    && false === session_write_close()) {
                    throw new PrivateAlert('Failed to close previously opened session');
                }

                $this->setSessionCookieParams();

                $willSetSaveHandler = $this->databasePool && !headers_sent();

                if ($willSetSaveHandler) {
                    ini_set('session.gc_probability', 1);  // Clear any lingering session data in default locations
                    if (false === session_set_save_handler($this, false)) {           // set this class as the session handler
                        throw new PublicAlert('Session failed to store remotely; session_set_save_handler(...) returned false.');
                    }
                }

                if (CarbonPHP::CLI) {
                    if ($willSetSaveHandler) {
                        ColorCode::colorCode('Session handler initialized (' . __FILE__ . '), but not started in CLI mode. Running (new ' . self::class . ') will start a session in CLI.');
                    }
                    return;
                }
            } else {
                ColorCode::colorCode('Session handler is already initialized (' . __FILE__ . ':' . __LINE__ . ') will start/resume session.');
            }

            if (false === register_shutdown_function(static fn() => session_write_close())) {
                throw new PublicAlert('Failed to register shutdown function');
            }

            if (true === headers_sent($file, $line)) {
                throw new PublicAlert('Headers already sent; cannot start session in (' . $file . ') on line (' . $line . ')');
            }

            ColorCode::colorCode('Starting Session');

            // this should not throw an error.. but if it doesnt we will catch and die
            if (false === session_start()) {
                ColorCode::colorCode('Failed to start session', iColorCode::RED);
                throw new PublicAlert('PHP failed to start your session; session_start() failed.');
            }

            $this->session_id = session_id();
            $GLOBALS['session_id'] = $this->session_id;
            $_SESSION['id'] = array_key_exists('id', $_SESSION ??= []) ? $_SESSION['id'] : 0;

        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e); // This terminates!
        }
    }

    /**
     *   Pauses the current session. This is required if you plan to fork you process and
     *   continue with session manipulation.
     */
    public function pause(): void
    {
        $this->session_id = session_id();
        session_write_close();
    }

    /**
     *   After a session is stopped with session_write_close() or paused with self::pause()
     *   It maybe resumed assuming the original id was stored in self::$session_id
     * @param string|null $session_id
     * @return Session
     */
    public function resume(string|null $session_id = null): self
    {
        if ($session_id !== null) {
            $this->session_id = $session_id;
        }
        session_id($this->session_id);
        session_start();
        return $this;
    }

    /**
     * This handles our users state. If the user goes form logged-in to logged-out
     * the outer html-wrapper will be sent.
     * @param bool $clear - if true is passed serialized data will be set to null
     */
    public function update($clear = false): void
    {
        global $user;
        static $count = 0;
        $count++;
        $_SESSION['id'] ??= 0;

        if ($clear || !$_SESSION['id']) {
            Serialized::clear();
        }

        if (!is_array($user)) {
            $user = array();
        }

        $lambda = $this->callback;

        if (is_callable($lambda)) {
            /** @noinspection OnlyWritesOnParameterInspection */
            $lambda($clear);    // you must have callable in a variable in fn scope
        }
        Request::sendHeaders();  // Send any stored headers
    }

    public function clear(): void
    {
        try {
            $session_class_name = Rest::getDynamicRestClass(User_Sessions::class);
            $this->session_table ??= new $session_class_name;
            $id = session_id();
            $_SESSION = [];
            session_write_close();
            $this->session_table::Delete($_SESSION, $id, []);   // in theory this will not throw anything
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
    public function verifySocket($ip): bool
    {

        ColorCode::colorCode('Verify Socket');

        if ($ip) {
            $_SERVER['REMOTE_ADDR'] = $ip;
        }

        $_SERVER['HTTP_COOKIE'] ??= '';

        ColorCode::colorCode('User sent Cookie(s) :: ' . print_r($_SERVER['HTTP_COOKIE'], true) . "\n\n");

        // $_SERVER['HTTP_COOKIE'] should be a string, but
        if (is_array($_SERVER['HTTP_COOKIE'])) {
            $session_id = $_SERVER['HTTP_COOKIE']['PHPSESSID'] ?? false;
        } else {
            if (false === @preg_match('#PHPSESSID=([^;\s]+)#', $_SERVER['HTTP_COOKIE'], $array, PREG_OFFSET_CAPTURE)) {
                ColorCode::colorCode('Failed to verify socket IP address.', 'red');
                return false;
            }

            ColorCode::colorCode('Parsed Session ID Correctly');
            $session_id = $array[1][0] ?? false;
        }

        if (false === $session_id) {
            ColorCode::colorCode("\nCould not parse session id\n", 'red');
            return false;
        }

        $session = Rest::getDynamicRestClass(User_Sessions::class);
        $sql = 'SELECT count(*) FROM ' . $session::TABLE_NAME . ' WHERE ' . $session::USER_IP . ' = ? AND ' . $session::SESSION_ID . ' = ? LIMIT 1';
        $stmt = $this->databasePool->prepare($sql);
        $stmt->execute([$ip, $session_id]);
        $session = $stmt->fetchColumn();

        if (!$session) {
            ColorCode::colorCode("BAD ADDRESS :: ($ip)\n\n", iColorCode::RED);
            return false;
        }

        $this->session_id = $session_id;    // this
        session_id($session_id);
        return true;
    }

    /**
     * @return iRestSinglePrimaryKey
     */
    public function getSessionTable(): iRestSinglePrimaryKey
    {

        if (null === $this->session_table) {
            $table_name = Rest::getDynamicRestClass(User_Sessions::class);    // all because custom prefixes and callbacks exist
            $this->session_table = new $table_name(); // This is only for referencing and is not actually needed as an instance.
        }

        return $this->session_table;

    }


    /** This is required for the session save handler interface.
     *  Do no change.
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    public function open($path, $name): bool
    {
        return true;
    }


    public function writeCloseClean(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->session_id = null;
        $this->user_id = null;
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
            iRest::IGNORE => [
                $session_table::SESSION_ID => $this->session_id = $id,
                $session_table::USER_ID => $this->user_id,
                $session_table::USER_IP => CarbonPHP::$server_ip,
                $session_table::SESSION_EXPIRES => $newDateTime,
                $session_table::SESSION_DATA => $this->sessionData
            ]
        ];


        $session_table::post($insertIgnore);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if (false === $session_table::get($session_table_row, $id, [
                iRest::SELECT => [
                    $session_table::SESSION_DATA
                ],
                iRest::LOCK => iRest::FOR_UPDATE
            ])) {

            throw new PublicAlert('Session not found. If problem persists please contact support.');

        }

        $sessionKey = $session_table::COLUMNS[$session_table::SESSION_DATA];

        if (array_key_exists($sessionKey, $session_table_row)) {

            $this->sessionContinued = true;

            $this->sessionData = $session_table_row[$sessionKey];

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
        // this only runs at the end of the session?
        $this->sessionUpdated = true;
        $this->sessionData = $data;
        return true;
    }

    private function updateSession(): void
    {

        if (false === $this->sessionUpdated) {

            return;

        }

        $this->sessionUpdated = false; // reset

        if (empty($this->user_id)) {

            $this->user_id = $_SESSION['id'] ??= false;

        }

        // d,lay mean
        $newDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + 1 day'));  // so from time of last write and whenever the gc_collector hits

        try {

            $session_table_row = [];

            $session_table = self::getSessionTable();

            $preCommitValue = Rest::$commit;

            Rest::$commit = false;

            $successful = $session_table::put($session_table_row, null, [
                iRest::REPLACE => [
                    $session_table::USER_ID => $this->user_id,
                    $session_table::USER_IP => CarbonPHP::$server_ip,
                    $session_table::SESSION_EXPIRES => $newDateTime,
                    $session_table::SESSION_DATA => $this->sessionData,
                    $session_table::SESSION_ID => $this->session_id,
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

    // @note you cannot effect the application state (even $GLOBAL) in this function
    public function close(): bool
    {
        self::updateSession();

        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                throw new PublicAlert('attempted session close with no PHP_SESSION_ACTIVE');
            }

            $db = $this->databasePool->searchConfigurations()->database;

            if (false === $db->inTransaction()) {
                throw new PublicAlert('Database not in transaction.');
            }

            if (false === $db->commit()) {
                throw new PublicAlert('Database commit failed.');
            }

            return true;

        } catch (Throwable $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    /** This method can be run explicit or through
     *      session_destroy()
     * @param $id
     * @return bool
     */
    public function destroy($id): bool
    {
        try {
            $session_table_row = [];

            $session_table = self::getSessionTable();

            return $session_table::Delete($session_table_row, null, [
                iRest::WHERE => [
                    [
                        $session_table::USER_ID => $this->user_id,
                        $session_table::SESSION_ID => $id
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
        $session = Rest::getDynamicRestClass(User_Sessions::class);

        $sql = 'DELETE FROM ' . $session::TABLE_NAME . ' WHERE (UNIX_TIMESTAMP(' . $session::SESSION_EXPIRES . ') + ? ) < UNIX_TIMESTAMP(?)';

        return $this->databasePool
            ->prepare($sql)
            ->execute([$max_lifetime, date('Y-m-d H:i:s')]);

    }

}
