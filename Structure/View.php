<?php

namespace Carbon;

class View
{
    use Singleton;

    public static function contents(...$argv)
    {
        $self = static::getInstance();
        call_user_func_array([$self, 'content'], $argv);
    }

    public function content($file) // Must be called through Singleton, must be private
    {
        global $mustache, $alert;  // If a public alert is set it will be here.

        if (SOCKET) {
            include $file;          // we not need compression / buffering for sockets
            exit(1);
        }

        ob_start();

        if (isset($alert)):
            foreach ($alert as $level => $message)
                $this->bootstrapAlert($message, $level);
            $alert = null;
        endif;

        if (!file_exists($file)):
            $this->bootstrapAlert('The file requested could not be found.', 'danger');
        else:
            include $file;
        endif;

        $file = ob_get_clean();

        if (pathinfo($file, PATHINFO_EXTENSION) == 'hbs'):
            $m = new Mustache_Engine;
            $file = $m->render($file, $this);
        endif;

        if (PJAX || AJAX):
            print $file;
        else:
            $this->bufferedContent = $file;
            include_once WRAPPER;
        endif;

        exit(1);
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
            return preg_replace('{\\.([^./]+)$}', "." . $time . ".\$1", DS . $file);
        } catch (\ErrorException $e) {
            return DS . $file;
        }
    }

    public function __get($variable)
    {
        return (isset($GLOBALS[$variable]) ? $GLOBALS[$variable] : null);
    }

}

