<?php

namespace CarbonPHP;

use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;

/**
 * Class View
 * @package Carbon
 */
class View
{

    public static array $json = [];

    /**
     * @var string
     */
    public static string $bufferedContent = 'No Content Buffered';
    /**
     * @var bool
     */
    public static bool $forceWrapper = false;
    /**
     * @var string
     */
    public static string $wrapper;


    /**
     * @param string $file
     * @param string|null $directoryContext
     * @return bool
     */
    public static function content(string $file, string $directoryContext = null): bool
    {

        global $json;

        if ($directoryContext === null) {

            $directoryContext = CarbonPHP::$app_root;

        }

        $buffer = static function () use ($directoryContext, $file) : string {         // closure  $buffer();

            global $json;              // Buffer contents may not need to be run if AJAX or SOCKET

            ob_start();                 // closure of a buffer is kinda like a double buffer

            if (($json['alert'] ?? false) && \is_array($json['alert']) && !empty($json['alert'])) {
                foreach ($json['alert'] as $level => $stack) {
                    foreach ($stack as $item => $message) {
                        self::bootstrapAlert($message, $level);
                    }
                }   // If a public alert is set it will be processed here.
                $alert = null;
            }

            if (!file_exists($directoryContext . $file)) {
                // It was already handled if it was an hbs, but lets remind people that both are supported
                self::bootstrapAlert("The file ($file) requested could not be found.", 'danger');
            } else if (!CarbonPHP::$socket) {
                /** @noinspection PhpIncludeInspection */
                include $directoryContext . $file;          // TODO - remove socket check?
            } else {
                $json = array_merge([
                    'Mustache' => '/' . str_replace('\\', '/', $file),      // dont change this to SITE, dont add DS
                    'Widget' => '#pjax-content'
                ], $json);
                print PHP_EOL . json_encode($json) . PHP_EOL;
            }
            return ob_get_clean();
        };

        if (CarbonPHP::$socket) {

            print $buffer() . PHP_EOL;

            return true;

        }

        if (pathinfo($file, PATHINFO_EXTENSION) === 'hbs') {

            $mustache = new \Mustache_Engine();

            if (CarbonPHP::$socket || (!self::$forceWrapper && CarbonPHP::$pjax && CarbonPHP::$ajax)) {        // Send JSON To Socket

                $json = array_merge([
                    'Mustache' => CarbonPHP::$site . $file,
                    'Widget' => '#pjax-content'
                ], $json);

                headers_sent() or header('Content-Type: application/json');

                print json_encode($json) . PHP_EOL . PHP_EOL;

                return true;
            }

            $buffer = $mustache->render($buffer(), $json);                  // Render Inner Content
        } else {
            $buffer = $buffer();
        }
        if (!file_exists(self::$wrapper)) {
            throw new PrivateAlert('The content wrapper (' . self::$wrapper . ') was not found. Wrapper does not exist.');
        }
        if (!\is_string($buffer)) {
            $buffer = "<script>Carbon(() => carbon.alert('Content Buffer Failed ($file)', 'danger'))</script>";
        }
        if (!self::$forceWrapper && (CarbonPHP::$pjax || CarbonPHP::$ajax)) {        // Send only inner content?
            print $buffer;
            #################### Send the Outer Wrapper
        } else if (pathinfo(self::$wrapper, PATHINFO_EXTENSION) === 'hbs') {   // Outer Wrapper is Mustache
            $json['content'] = $buffer;
            $mustache = $mustache ?? new \Mustache_Engine();
            print $mustache->render(file_get_contents(self::$wrapper), $json);
        } else {                                                                       // Outer Wrapper is PHP?
            self::$bufferedContent = $buffer;
            /** @noinspection PhpIncludeInspection */
            include_once self::$wrapper;
        }
        return true;    // This should fall, or pop, on the stack to the bootstrap which will return because of a match, then to the index.php
    }

    /** This method
     * @param $message
     * @param $level
     */
    public static function bootstrapAlert($message, $level): void
    {
        $message = htmlentities($message);  // Im not sure how this may be used,
        $level = htmlentities($level);      // for completeness I sanitises
        printf('<script>Carbon(() => carbon.alert("%s", "%s"))</script>', $message, $level);
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

    public static function versionControl($file)
    {
        if (!isset(CarbonPHP::$app_root)) {
            return DS . $file;
        }
        try {
            if (!file_exists($absolute = CarbonPHP::$app_root . $file) || !($time = @filemtime($absolute))) {
                return DS . $file;
            }
            return preg_replace('{\\.([^./]+)$}', '.' . $time . '.\$1', DS . $file);
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
    public static function unVersion($uri)
    {
        if (preg_match('#^(.*)\.[\d]{10}\.(css|js)#', $uri, $matches, PREG_OFFSET_CAPTURE)) {

            $uri = trim($matches[1][0] . '.' . $matches[2][0], '/');

            if (file_exists(CarbonPHP::$app_root . $uri)) {
                self::sendResource($uri, $matches[2][0]);
            }
        }
        return false;
    }

    /**
     * @param $file
     * @param $ext
     */
    public static function sendResource($file, $ext): void
    {
        if ($mime = self::mimeType($file, $ext)) {
            header('Content-type:' . $mime . '; charset: UTF-8');
            readfile($file);
            exit(1);                        // exit = die  but implies success
        }
        http_response_code(404);
        die(1);
    }

    //Josh Sean
    // // TODO - put this into cli program
    public static function generateUpToDateMimeArray(): void
    {
        \defined('APACHE_MIME_TYPES_URL') OR \define('APACHE_MIME_TYPES_URL', 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
        $s = array();
        foreach (@explode("\n", @file_get_contents(APACHE_MIME_TYPES_URL)) as $x) {
            if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1) {
                for ($i = 1; $i < $c; $i++) {
                    $s[] = '&nbsp;&nbsp;&nbsp;\'' . $out[1][$i] . '\' => \'' . $out[1][0] . '\'';
                }
            }
        }
        print @sort($s) ? 'return array(<br />' . implode($s, ',<br />') . '<br />);' : false;
    }


    /**
     *
     * @param $file
     * @param $ext
     * @return string
     */
    public static function mimeType($file, $ext): string
    {
        $mime_types = include CarbonPHP::CARBON_ROOT . 'extras' . DS . 'mimeTypes.php';
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        if (\function_exists('finfo_open')) {
            $file_info = finfo_open(FILEINFO_MIME);
            $mime_types = finfo_file($file_info, $file);
            finfo_close($file_info);
            return $mime_types;
        }
        return 'application/octet-stream';
    }
}


