<?php

namespace Carbon;

class View
{
    use Singleton;
    const Singleton = true;

    public $currentPage;
    public $forceStoreContent = false;

    public function __construct()
    {
        if ((PJAX || AJAX) && !empty($this->currentPage)) {
            echo base64_decode($this->currentPage);
            $this->currentPage = null;
            exit(1);
        }
    }

    public function wrapper($forceWrapper = false)   // Send the content wrapper
    {
        if (!SOCKET && (HTTP || HTTPS || $forceWrapper)) {            // The user logging out should force content wrapper refresh
            $this->forceStoreContent = $forceWrapper;
            if (!defined('WRAPPER')|| !file_exists(WRAPPER))
                print 'A valid wrapper must be provided. Please see CarbonPHP.com for documentation.' and die;

            require WRAPPER;   // Return the Template, this file should have your user logic in it
        }
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

            if (isset($alert)) {
                foreach ($alert as $level => $message)
                    $this->bootstrapAlert($message, $level);
                $alert = null;
            }

            if (!file_exists($file))
                $this->bootstrapAlert('The file requested could not be found.', 'danger');
            else
                include $file;

            $file = ob_get_clean();

            if ($this->forceStoreContent || HTTP || HTTPS) {
                $this->currentPage = base64_encode($file);
            } else echo $file;
        } else throw new \Exception("$file does not exist");  // TODO - throw 404 error
    }

    public function bootstrapAlert($message, $level): void
    {
        $message = htmlentities($message);
        echo "<script>$.fn.bootstrapAlert(\"$message\", '$level')</script>";
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

