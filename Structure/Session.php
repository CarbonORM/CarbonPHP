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

namespace Carbon;

use Carbon\Helpers\Serialized;

class Session implements \SessionHandlerInterface
{
    /**
     * @var string - if we need to close or pause the session in the middle of execution,
     * this will persistently hold our session_id.
     */
    protected static $session_id;

    /**
     * @var $user_id - After a session is closed the session data is serialized and removed
     * from the global (accessible) scope.
     */
    protected static $user_id;

    /**
     * @var $callback - if the session is reset using the startApplication function,
     * this callable function will be executed. You can set this variable in the configuration.
     */
    protected static $callback;

    /**
     * Session constructor. This
     * @param null $ip
     * @param bool $dbStore
     */
    public function __construct($ip = null, $dbStore = false)
    {
        session_write_close(); //cancel the session's auto start, important

        ini_set('session.use_strict_mode', 1);

        if ($ip === false) {
            print 'Carbon has detected ip spoofing.';
            die(0);
        }

        if ($dbStore) {
            ini_set('session.gc_probability', 1);  // Clear any lingering session data in default locations
            if (!session_set_save_handler($this, false)) {
                print 'Session failed to store remotely';
                die(1);
            }
        }

        if (SOCKET) {
            $this->verifySocket($ip);
        }

        if (false === @session_start()) {
            print 'Carbon failed to start your session';
            die(2);
        }
    }

    /**
     *   Pauses the current session. This is required if you plan to fork you process and
     *   continue with session manipulation.
     */
    public static function pause()
    {
        static::$session_id = session_id();
        session_write_close();
    }

    /**
     *   After a session is stopped with session_write_close() or paused with self::pause()
     *   It maybe resumed assuming the original id was stored in self::$session_id
     */
    public static function resume()
    {
        session_id(static::$session_id);
        session_start();
    }


    /**
     * Change the callback run if self::update() is called.
     * @param callable|null $lambda
     */
    public static function updateCallback(callable $lambda = null)
    {
        self::$callback = $lambda;
    }

    /**
     * This handles our users state. If the user goes form logged-in to logged-out
     * the outer html-wrapper will be sent.
     * @param bool $clear - if true is passed serialized data will be set to null
     */
    public static function update($clear = false)
    {
        global $user;
        static $count = 0;
        $count++;

        if ($clear || !($_SESSION['id'] ?? false)) {
            Serialized::clear();
        }

        if (!\is_array($user)) {
            $user = array();
        }

        if (static::$user_id = $_SESSION['id'] = $_SESSION['id'] ?? false) {
            $_SESSION['X_PJAX_Version'] = 'v' . SITE_VERSION . 'u' . $_SESSION['id'];
        } // force reload occurs when X_PJAX_Version changes between requests

        if (!isset($_SESSION['X_PJAX_Version'])) {
            $_SESSION['X_PJAX_Version'] = SITE_VERSION;     // static::$user_id, keep this static
        }

        Request::setHeader('X-PJAX-Version: ' . $_SESSION['X_PJAX_Version']);

        /* If the session variable changes from the constant we will
         * send the full html page and notify the pjax js to reload
         * everything
         * */

        if (\is_callable(self::$callback)) {
            ($lambda = self::$callback)($clear);    // you must have callable in a variable in fn scope
        }
        if (!\defined('X_PJAX_VERSION')) {
            \define('X_PJAX_VERSION', $_SESSION['X_PJAX_Version']);
        }
        Request::sendHeaders();  // Send any stored headers
    }

    /**
     * This will remove our session data from our scope and the database
     */
    public static function clear()
    {
        try {
            $id = session_id();
            $_SESSION = array();
            session_write_close();
            $db = Database::database();
            $db->prepare('DELETE FROM carbon_session WHERE session_id = ?')->execute([$id]);
            session_start();
        } catch (\PDOException $e) {
            sortDump($e);
        }
    }

    /** This function was created to make sure all socket request come from
     * an existing user who has a session stored in our database. If database
     * session storage is turned off this method will fail and exit.
     *
     * @param $ip - the ip address to look up from our database.
     */
    private function verifySocket($ip)
    {
        if ($ip) {
            $_SERVER['REMOTE_ADDR'] = $ip;
        }
        $db = Database::database();
        $sql = 'SELECT session_id FROM carbon_session WHERE user_ip = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SERVER['REMOTE_ADDR']]);
        $session = $stmt->fetchColumn();
        if (empty($session)) {
            if (SOCKET) {
                print 'BAD ADDRESS :: ' . $_SERVER['REMOTE_ADDR'] . "\n\n";
            }
            exit(0);
        }
        session_id($session);
    }

    /** This is required for the session save handler interface.
     *  Do no change.
     *
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /** Make user our session data gets stored in the db.
     * @return bool
     */
    public function close()
    {
        register_shutdown_function('session_write_close');
        return true;
    }

    /** read
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        //TODO - if ip has changed and session id hasn't invalidate
        $stmt = Database::database()->prepare('SELECT session_data FROM carbon_session WHERE session_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: '';
    }

    /** This function should never be called by you directly. It can be invoked using
     * session_write_close().
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $db = Database::database();
        if (empty(self::$user_id)) self::$user_id = $_SESSION['id'] ?? false;
        $NewDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + 1 d,lay'));  // so from time of last write and whenever the gc_collector hits

        try {
            $db->prepare('REPLACE INTO carbon_session SET session_id = ?, user_id = ?, user_ip = ?,  session_expires = ?, session_data = ?')->execute([
                $id, static::$user_id, $_SERVER['REMOTE_ADDR'], $NewDateTime, $data]);
        } catch (\PDOException $e) {
            sortDump($e);
        }
        return true;
    }

    /** This method can be run explicit or through
     *      session_destroy()
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $db = Database::database();
        return $db->prepare('DELETE FROM carbon_session WHERE user_id = ? OR session_id = ?')->execute([self::$user_id, $id]) ?
            true : false;
    }

    /** This is our garbage collector. If a session is expired attempt to remove it.
     * This function is executed via a probability. See link for more details.
     * @link http://php.net/manual/en/features.gc.php
     * @param int $maxLife
     * @return bool
     */
    public function gc($maxLife)
    {
        $db = Database::database();
        return $db->prepare('DELETE FROM carbon_session WHERE (UNIX_TIMESTAMP(session_expires) + ? ) < UNIX_TIMESTAMP(?)')->execute([$maxLife, date('Y-m-d H:i:s')]) ?
            true : false;
    }
}
