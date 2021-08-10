<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Interfaces\iColorCode;

trait ColorCode
{

    public static bool $colorCodeBool = true;

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @link https://www.php.net/manual/en/function.syslog.php
     */
    public static function colorCode(string $message, string $color = 'green'): void
    {
        if (!self::$colorCodeBool) {

            print $message;

            return;

        }

        $colors = iColorCode::PRINTF_ANSI_COLOR;

        if (is_string($color) && !array_key_exists($color, $colors)) {

            $message = "Color provided to color code ($color) is invalid, message caught '$message'";

            $color = iColorCode::RED;

        }

        $colorCodex = sprintf($colors[$color], $message);

        if (CarbonPHP::$test) {

            /**
             * The code below doesn't seem to hold. print is needed to pass tests
             * @link https://stackoverflow.com/questions/21784240/is-there-any-way-to-expect-output-to-error-log-in-phpunit-tests
             * @noinspection ForgottenDebugOutputInspection
             * $current = ini_set('error_log', '/dev/stdout'); // use this rather than const as const is not defined in all run time envs
             * error_log($colorCodex);
             * ini_set('error_log', $current);
             */
            print $colorCodex . PHP_EOL;

            return;
        }

        /** @noinspection ForgottenDebugOutputInspection */
        error_log($colorCodex);    // do not double quote args passed here

        if (null === ErrorCatcher::$defaultLocation || '' === ErrorCatcher::$defaultLocation) {

            return;

        }

        $location = ini_get('error_log');

        switch ($location) {
            case '':

                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('error_log', ErrorCatcher::$defaultLocation); // log to file too

                break;

            case ErrorCatcher::$defaultLocation:

                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('error_log', '');   // default output // cli stdout

                break;

            default:

                $additional = sprintf($colors[$color], "\n\nThe error_log location set ($location) did not match the CarbonPHP ColorCode enabled error log path ErrorCatcher::\$defaultLocation = (" . ErrorCatcher::$defaultLocation . "); or was not set to an empty string which enables cli output.\n\n", iColorCode::YELLOW);

                $colorCodex .= $additional; // for old log location

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($additional);    // do not double quote args passed here

                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('error_log', ErrorCatcher::$defaultLocation);

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($additional);    // do not double quote args passed here

                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('error_log', '');           // default output // cli stdout
        }


        /** @noinspection ForgottenDebugOutputInspection */
        error_log($colorCodex);    // do not double quote args passed here

        /** @noinspection PhpExpressionResultUnusedInspection */
        ini_set('error_log', $location);    // back to what it was before this function

    }

}
