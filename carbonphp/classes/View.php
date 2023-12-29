<?php

namespace CarbonPHP\Classes;

use CarbonPHP\CarbonPHP;

/**
 * Class View
 * @package Carbon
 */
class View
{

    public static array $json = [];


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
        print @sort($s) ? 'return array(<br />' . implode( ',<br />', $s) . '<br />);' : false;
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


