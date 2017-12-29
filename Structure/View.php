<?php

namespace Carbon;

class View
{
    use Singleton;              // were still in need of a force wrapper

    public $forceWrapper = false;

    public static function contents($argv)
    {
        $self = static::getInstance();
        return call_user_func([$self, 'content'], $argv);
    }

    public function content($file)
    {
        $buffer = catchErrors(function () use ($file) {         // closure  $buffer();

            global $alert;           // Buffer contents may not need to be run if AJAX or SOCKET

            // so a closure of a buffer is kinda like a double buffer

            ob_start();

            if (isset($alert)):
                foreach ($alert as $level => $message)
                    $this->bootstrapAlert($message, $level);   // If a public alert is set it will be processed here.
                $alert = null;
            endif;

            if (!file_exists($file) && !file_exists($file = SERVER_ROOT . $file)):
                $this->bootstrapAlert("The file ($file)requested could not be found.", 'danger');
            else:
                include $file;
            endif;

            return ob_get_clean();
        });

        if (pathinfo($file, PATHINFO_EXTENSION) == 'hbs'):
            global $json;
            if (SOCKET || (!$this->forceWrapper && !PJAX && AJAX)) {
                $json['Mustache'] = SITE . $file;
                print json_encode($json) . PHP_EOL;
                return true;
            }
            $m = new \Mustache_Engine();
            $buffer = $m->render($buffer(), $json);
        else:
            $buffer = $buffer();
        endif;

        if (!$this->forceWrapper && (PJAX || AJAX)):
            print $buffer;
        else:
            $this->bufferedContent = $buffer;
            include_once WRAPPER;
        endif;

        return true;
    }

    private function bootstrapAlert($message, $level): void
    {
        $message = htmlentities($message);  // Im not sure how this may be used,
        $level = htmlentities($level);      // for completeness I sanitise
        print "<script>Carbon(() => $.fn.bootstrapAlert(\"$message\", \"$level\"))</script>";
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

    static function versionControl($file)
    {
        if (!defined('SERVER_ROOT'))
            return DS . $file;
        try {
            if (!file_exists($absolute = SERVER_ROOT . $file) || !($time = @filemtime($absolute)))
                return DS . $file;
            return preg_replace('{\\.([^./]+)$}', "." . $time . ".\$1", DS . $file);
        } catch (\ErrorException $e) {
            return DS . $file;
        }
    }

    static function unVersion($uri)
    {
        if (!defined('SERVER_ROOT'))
            return DS . $uri;
        if ((new Request())->set($uri)->regex('^(.*)\.[\d]{10}\.(css|js|html)')){
            $uri = preg_match('#^(.*)\.[\d]{10}\.(css|js|html)#', $uri, $matches, PREG_OFFSET_CAPTURE);
            $uri = "$uri[1].$uri[2]";
            if (file_exists(SERVER_ROOT . $uri))
                include $uri and exit(1);
        }
        return false;
    }

    public function __get($variable)
    {
        return (isset($GLOBALS[$variable]) ? $GLOBALS[$variable] : null);
    }

}

