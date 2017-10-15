<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/16/17
 * Time: 5:19 PM
 */

namespace Carbon\Error;


/**
 * Class PublicAlert
 * @package Modules\Helpers\Error
 * 
 * danger
 * warning
 * info
 * success
 *
 */


class PublicAlert extends CustomException {

    private static function alert($message, $code) {
        if ($code != 'success' && $code != 'info') $message .= ' Contact us if problem persists.';
        $GLOBALS['alert'][$code] = $message;
    }

    public static function success($message)
    {
        $GLOBALS['alert']['success'] = $message;
    }
    public static function info($message)
    {
        $GLOBALS['alert']['info'] = $message;

    }
    public static function danger($message)
    {
        $GLOBALS['alert']['danger'] = $message;

    }
    public static function warning($message)
    {
        $GLOBALS['alert']['warning'] = $message;
    }
    
    public function __construct($message = null, $code = 'warning')
    {
        if (!empty($message)) static::alert( $message, $code );
        parent::__construct($message, 0);
    }

    public function __call($code = null, $message)
    {
        static::alert( $message[0], $code );
    }

    public static function __callStatic($code = null, $message)
    {
        static::alert( $message[0], $code );
    }


}
