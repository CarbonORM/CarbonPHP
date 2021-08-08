<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Interfaces\iColorCode;
use DropInGaming\DropVariables;

trait ColorCode
{

    public static bool $colorCodeBool = true;

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @param int $priority
     * @link https://www.php.net/manual/en/function.syslog.php
     */
    public static function colorCode(string $message, string $color = 'green', bool $exit = false, int $priority = LOG_INFO): void
    {
        if (!self::$colorCodeBool) {
            print $message;
            if ($exit) {
                exit($message);
            }
            return;
        }

        $colors = iColorCode::PRINTF_ANSI_COLOR;

        if (is_string($color) && !array_key_exists($color, $colors)) {
            self::colorCode("Color provided to color code ($color) is invalid, message caught '$message'", iColorCode::RED);
            return;
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

        } else {
            $old_location = null;

            if (ErrorCatcher::$defaultLocation !== null) {

                $old_location = ini_set('error_log', ErrorCatcher::$defaultLocation);

            }

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($colorCodex);    // do not double quote args passed here

            if (null !== $old_location) {

                $colorCodex = sprintf($colors[$color], "The error_log location set ($old_location) did not match the CarbonPHP ColorCode enabled error log path ErrorCatcher::\$defaultLocation = (" . ErrorCatcher::$defaultLocation . ');. We\'ve temporarily logged to the ladder.', iColorCode::YELLOW);

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($colorCodex);    // do not double quote args passed here

                /** @noinspection PhpExpressionResultUnusedInspection */
                ini_set('error_log', $old_location);

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($colorCodex);    // do not double quote args passed here

            }

        }

        if ($exit) {

            exit($message);

        }

    }

}