<?php

namespace Carbon;

use Carbon\Error\PublicAlert;

class View
{
    use Singleton;
    const Singleton = true;

    public $currentPage;
    private $carryErrors;
    private $forceStoreContent;

    // sockets cannot possibly invoke the wakeup function
    public function __wakeup()
    {
        if (!AJAX):      // an HTTP request
            $_POST = [];
            $this->__construct();                       // and reprocess the dependencies, wrapper is a global closure
        elseif (!empty($this->currentPage)):          // Implies AJAX && a page has already been rendered and stored
            echo base64_decode($this->currentPage);   // The ajax page will be 64encoded before we store on the server
            $this->currentPage = false;
            self::clearInstance();                      // Remove stored information on the server and delete its self reference
            exit(1);                                  // This is for the second inner AJAX request on first page load
        endif;                                          // We're requesting our second page through ajax ie not initial page request
    }

    public function __construct($forceWrapper = false)   // Send the content wrapper
    {
        if (SOCKET) return null;    // we don't need html -> socket

        #if (AJAX)
        # $closure = AJAX_SIGNED_OUT;

        if (HTTP || HTTPS || $forceWrapper) {

            if (!($forceWrapper || ($_SESSION['X_PJAX_Version'] != X_PJAX_VERSION)) && AJAX) // why was this not documented
                return null;

            ob_start();

            if (!defined('WRAPPER')|| !file_exists(WRAPPER))
                print 'A valid wrapper must be provided. Please see CarbonPHP.com for documentation.' and die;

            require WRAPPER;   // Return the Template

            if ($forceWrapper):
                if (!empty($GLOBALS['alert']))
                    $this->carryErrors = $GLOBALS['alert']; //
                $this->forceStoreContent = true;
            endif;
        } // This would only be executed it wrapper_requires_login = true and user logged out, this can be helpful for making sure the user does not back into a state
        // if there it is an ajax request, the user must be logged in, or container must be true
    }

    public static function contents(...$argv)
    {
        $self = static::getInstance();
        call_user_func_array([$self, 'content'], $argv);
    }

    public function content($file): void // Must be called through Singleton, must be private
    {
        global $alert;  // If a public alert is set it will be here.

        if (file_exists($file)) {
            if (SOCKET) {
                include $file;          // we not need compression / buffering for sockets
                return;
            }

            ob_start();

            if (empty($GLOBALS['alert']) && !empty($GLOBALS['alert'] = $this->carryErrors))
                $this->carryErrors = null;

            if (isset($alert)) {
                if (isset($alert['danger']))  $this->bootstrapAlert($alert['danger'], 'danger');
                if (isset($alert['info']))    $this->bootstrapAlert($alert['info'], 'info');
                if (isset($alert['warning'])) $this->bootstrapAlert($alert['warning'], 'warning');
                if (isset($alert['success'])) $this->bootstrapAlert($alert['success'], 'success');
                $alert = null;
            }

            if (!file_exists($file))
                $this->bootstrapAlert('The file requested could not be found.', 'danger');
            else
                include $file;

            $file = ob_get_clean();

            if ($this->forceStoreContent || (!AJAX && (!$_SESSION['id']))) {
                $this->currentPage .= base64_encode($file);
                exit(1);
            } else echo $file;

        } else throw new \Exception("$file does not exist");  // TODO - throw 404 error
    }

    /**
     *  Given a file, i.e. /css/base.css, replaces it with a string containing the
     *  file's mtime, i.e. /css/base.1221534296.css.
     *
     * @param $file
     *  file to be loaded.  Must be an absolute path (i.e.
     *                starting with slash).
     * @return mixed  file to be loaded.
     */

    public function bootstrapAlert($message, $level): void
    {
        $message = htmlentities($message);
        echo "<script>bootstrapAlert(\"$message\", '$level')</script>";
    }


    public function versionControl($file)
    {
        if (!defined('SERVER_ROOT')) return DS . $file;
        try {
            if (!file_exists($absolute = SERVER_ROOT . $file) || !($time = @filemtime($absolute)))
                return DS . $file;
            return preg_replace('{\\.([^./]+)$}', "." . $time . ".\$1",  DS . $file);
        } catch (\ErrorException $e) {
            return DS . $file;
        }
    }

    public function __get($variable)
    {
        return (isset($GLOBALS[$variable]) ? $GLOBALS[$variable] : null);
    }


}

