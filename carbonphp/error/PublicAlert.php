<?php
/**
 * Created by IntelliJ IDEA.
 * User: Miles
 * Date: 7/16/17
 * Time: 5:19 PM
 */

namespace CarbonPHP\Error;

/**
 * Class PublicAlert
 * @package Modules\Helpers\Error
 *
 * The public alert class is designed to be used with the carbon.js extra.
 * If the java script method Carbon() is not defiend in the html document head, or
 * if Carbon.js is not included  PublicAlert  may result in runtime errors.
 * This class prints warning to the browser using this javascript
 *      <script>Carbon(() => $.fn.bootstrapAlert(\"$message\", \"$level\"))</script>
 *
 * Public alerts should only be thrown when wrapped in a try catch block.
 * The alerts will be handled in the view class.
 *
 * I assume you use bootstrap (particularly AdminLTE) and carbon.js for the rest of
 * this file.
 */

class PublicAlert extends CustomException {

    public static function alertSet() : bool {
        global $json;
        return ($json['alert']['danger'] ?? false) ||
            ($json['alert']['error'] ?? false) ||
            ($json['alert']['input'] ?? false);
    }

    public static function JsonAlert($message, $title, $type = 'danger', $icon = null, $status = 400, $intercept = true, $stack = true) : void
    {
        global $json;

        if (!\is_array($json)) {
            $json = [];
        } elseif ($stack) {
            $json = [
                'previous_json' => $json,
                'sql' => [],
                'status' => $status,
            ];
        } else {
            $json['status'] = $status;
        }

        if (!\in_array($type, ['default', 'info', 'success', 'warning', 'danger', 'error', 'input', 'custom'])) {
            $message .= " The React Alert type given `$type` is not supported.";
            $type = 'danger';
        }

        if ($title === null) {
            $title = 'Danger!';
        }

        $json['alert'][] = [
            'message' => $message,
            'type' => $type,
            'title' => $title,
            'icon' => $icon,
            'intercept' => $intercept
        ];
    }

    /** Add an alert to the array. If the view is not executed, CarbonPHP will not
     * handle the global alert.
     * @param string $message the message to be stored in the alert variable
     * @param string $code you may choose between success, info, danger, or warning
     */
    private static function alert(string $message, string $code): void
    {
        global $json;
        if (($message[-1] ?? '') === '.' && $code !== 'success' && $code !== 'info') {
            $message .= ' Contact us if problem persists.';
        }
        $json['alert'][$code][] = $message;
    }

    /**
     * @param string $message results in a green alert box
     */
    public static function success(string $message): void
    {
        global $json;
        $json['alert']['success'][] = $message;
    }

    /**
     * @param string $message results in a blue alert box
     */
    public static function info(string $message): void
    {
        global $json;
        $json['alert']['info'][] = $message;
    }

    /**
     * @param string $message results in a red alert box
     */
    public static function danger(string $message): void
    {
        global $json;
        $json['alert']['danger'][] = $message;

    }

    /**
     * @param string $message results in a yellow alert box
     */
    public static function warning(string $message): void
    {
        global $json;
        $json['alert']['warning'][] = $message;
    }

    /**
     * PublicAlert constructor.
     * @param null $message
     * @param string $code
     */
    public function __construct($message = null, $code = 'warning')
    {

        if (empty($message)) {

            $message = 'Whoa, a Public Alert was thrown without a message attached. This is awful.';

            static::alert($message, $code);

        }

        parent::__construct($message, 0);

    }

    /** Allow for even more customization alerts on runtime.
     * @link http://php.net/manual/en/language.oop5.overloading.php#object.call
     *
     * If a methods is run that does not exist in this scope,
     * pass the method name as our alert level.
     *
     * $this->superDaner($message);
     *
     * would set: $GLOBALS['alert']['superDaner']=$message;
     *
     * @param null $code
     * @param $message
     */
    public function __call($code, $message)
    {
        static::alert( $message[0], $code );
    }

    /** The same as __call() but in a static context.
     * PublicAlert::superDaner($message); ...
     *
     * @link http://php.net/manual/en/language.oop5.overloading.php#object.call
     * @param null $code
     * @param $message
     */
    public static function __callStatic($code, $message)
    {
        static::alert( $message[0], $code );
    }


}
