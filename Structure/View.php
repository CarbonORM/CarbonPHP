<?php

namespace Carbon;

/**
 * Class View
 * @package Carbon
 */
/**
 * Class View
 * @package Carbon
 */
class View
{
    /**
     * @var string
     */
    public static $bufferedContent = 'No Content Buffered';
    /**
     * @var bool
     */
    public static $forceWrapper = false;
    /**
     * @var
     */
    public static $wrapper;

    /**
     * @param string $file
     * @return bool
     */
    public static function content(string $file)
    {
        $buffer = catchErrors(function () use ($file) : string {         // closure  $buffer();

            global $alert;              // Buffer contents may not need to be run if AJAX or SOCKET

            ob_start();                 // closure of a buffer is kinda like a double buffer

            if (isset($alert)):
                foreach ($alert as $level => $message)
                    self::bootstrapAlert($message, $level);   // If a public alert is set it will be processed here.
                $alert = null;
            endif;

            if (!file_exists($file) && !file_exists($file = SERVER_ROOT . $file)):
                self::bootstrapAlert("The file ($file)requested could not be found.", 'danger');
            else:
                include $file;
            endif;

            return ob_get_clean();
        });

        if (pathinfo($file, PATHINFO_EXTENSION) == 'hbs'):
            global $json;
            if (SOCKET || (!self::$forceWrapper && !PJAX && AJAX)) {
                $json['Mustache'] = SITE . $file;
                print json_encode($json) . PHP_EOL;
                return true;
            }
            $m = new \Mustache_Engine();
            $buffer = $m->render($buffer(), $json);
        else:
            $buffer = $buffer();
        endif;

        if (!self::$forceWrapper && (PJAX || AJAX)):
            print $buffer;
        else:
            self::$bufferedContent = $buffer;
            include_once WRAPPER;
        endif;

        return true;
    }

    /** This method
     * @param $message
     * @param $level
     */
    private static function bootstrapAlert($message, $level): void
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


    /**
     *  Given a file versioned by file's mtime, i.e. /css/base.1221534296.css.
     *  return the requested file with proper headers, i.e. /css/base.css.
     *
     * @param $uri
     *  file to be loaded.  Must be an absolute path (i.e.
     *                starting with slash).
     * @return mixed  file to be loaded.
     */
    static function unVersion($uri)
    {
        if (!defined('SERVER_ROOT'))
            return DS . $uri;

        if (preg_match('#^(.*)\.[\d]{10}\.(css|js)#', $uri, $matches, PREG_OFFSET_CAPTURE)) {

            $uri = trim($matches[1][0] . '.' . $matches[2][0], '/');

            if (file_exists(SERVER_ROOT . $uri))
                self::sendResource($uri, $matches[2][0]);
        }
        return false;
    }

    /**
     * @param $file
     * @param $ext
     */
    static function sendResource($file, $ext)
    {
        if ($mime = self::mimeType($file, $ext)) {
            header("Content-type: " . $mime . "; charset: UTF-8");
            readfile($file);
            exit(1);                        // exit = die  but implies success
        }
        http_response_code(404);
        die(1);
    }

    /**
     * @param $file
     * @param $ext
     * @return mixed|string
     */
    static function mimeType($file, $ext)   // TODO - Add the full list generator from stack overflow
    {
        /*
        finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        foreach (glob("*") as $filename) {
            echo finfo_file($finfo, $filename) . "\n";
        }
        finfo_close($finfo);
        */

        $mime_types = include_once SERVER_ROOT . 'Data/Indexes/MimeTypes.php';
        if (array_key_exists($ext, $mime_types))
            return $mime_types[$ext];

        if (function_exists('finfo_open')) {
            $file_info = finfo_open(FILEINFO_MIME);
            $mime_types = finfo_file($file_info, $file);
            finfo_close($file_info);
            return $mime_types;
        }
        return 'application/octet-stream';
    }

}

