<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/27/17
 * Time: 10:26 PM
 *
 *
 * http://php.net/manual/en/function.session-set-save-handler.php
 *
 */

namespace Carbon;

use Carbon\Helpers\Serialized;

class Session implements \SessionHandlerInterface
{

    private static $user_id;

    private static $callback;

    public function __construct($ip = null, $dbStore = false)
    {
        session_write_close(); //cancel the session's auto start, important

        if ($ip === false)
            print 'Carbon has detected ip spoofing.' and die;

        if ($dbStore) {
            ini_set( 'session.gc_probability', 1 );  // Clear any lingering session data in default locations
            if (!session_set_save_handler( $this, false ))
                print 'Session failed to store remotely' and die(1);                // Comment this out to stop storing session on the server
        }

        if (SOCKET) $this->verifySocket($ip);

        if (false == @session_start())
            throw new \Exception('Session Failed');

    }

    static function runCallback($argv)
    {
        if (is_callable(self::$callback)) ($lambda = self::$callback)($argv);
    }

    static function updateCallback(callable $lambda = null)
    {
        self::$callback = $lambda;
    }

    static function update($clear = false)
    {
        global $user;
        static $count = 0;
        $count++;

        if ($clear || !($_SESSION['id'] ?? false))
            Serialized::clear();

        if (!is_array($user))
            $user = array();

        if (SOCKET) Database::resetConnection();        // TODO - $dbStore __const

        if ((static::$user_id = $_SESSION['id'] = ($_SESSION['id'] ?? false)))
            $_SESSION['X_PJAX_Version'] = 'v' . SITE_VERSION . 'u' . $_SESSION['id']; // force reload occurs when X_PJAX_Version changes between requests

        if (!isset( $_SESSION['X_PJAX_Version'] ))
            $_SESSION['X_PJAX_Version'] = SITE_VERSION;     // static::$user_id, keep this static

        Request::setHeader( 'X-PJAX-Version: ' . $_SESSION['X_PJAX_Version'] );

        /* If the session variable changes from the constant we will
         * send the full html page and notify the pjax js to reload
         * everything
         * */

        self::runCallback($clear);

        if (!defined( 'X_PJAX_VERSION' ))
            define( 'X_PJAX_VERSION', $_SESSION['X_PJAX_Version'] );

        Request::sendHeaders();  // Send any stored headers
    }

    private function verifySocket($ip)
    {
        if ($ip) $_SERVER['REMOTE_ADDR'] = $ip;
        $db = Database::getConnection();
        $sql = "SELECT session_id FROM StatsCoach.carbon_session WHERE user_ip = ?";
        $stmt = $db->prepare( $sql );
        $stmt->execute( [$_SERVER['REMOTE_ADDR']] );
        $session = $stmt->fetchColumn();
        if (empty( $session )) {
            if (SOCKET) echo "BAD ADDRESS :: " . $_SERVER['REMOTE_ADDR'] . "\n\n";
            exit( 0 );
        }
        session_id( $session );
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        register_shutdown_function('session_write_close');
        return true;
    }

    public function read($id)
    {
        $stmt = (Database::getConnection())->prepare( 'SELECT session_data FROM carbon_session WHERE session_id = ?' );
        $stmt->execute( [$id] );
        return $stmt->fetchColumn() ?: '';
    }

    public function write($id, $data)
    {
        $db = Database::getConnection();
        if (empty(self::$user_id)) self::$user_id = $_SESSION['id'] ?? false;
        $NewDateTime = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d H:i:s' ) . ' + 1 day' ) );  // so from time of last write and whenever the gc_collector hits
        return ($db->prepare( 'REPLACE INTO carbon_session SET session_id = ?, user_id = ?, user_ip = ?,  session_expires = ?, session_data = ?' )->execute( [
            $id, static::$user_id, $_SERVER['REMOTE_ADDR'], $NewDateTime, $data] )) ?
            true : false;
    }

    public function destroy($id)
    {
        $db = Database::getConnection();
        return ($db->prepare( 'DELETE FROM carbon_session WHERE user_id = ?' )->execute( [self::$user_id] )) ?
            true : false;
    }

    public function gc($maxLife)
    {
        $db = Database::getConnection();
        return ($db->prepare( 'DELETE FROM carbon_session WHERE (UNIX_TIMESTAMP(session_expires) + ? ) < UNIX_TIMESTAMP(?)' )->execute( [$maxLife, date( 'Y-m-d H:i:s' )] )) ?
            true : false;
    }
}
