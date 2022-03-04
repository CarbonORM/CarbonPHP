<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use Throwable;

trait ColorCode
{

    public static bool $colorCodeBool = true;

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @throws PublicAlert
     * @link https://www.php.net/manual/en/function.syslog.php
     * @noinspection ForgottenDebugOutputInspection
     */
    public static function colorCode(string $message, string $color = iColorCode::GREEN): void
    {

        $pid = getmypid(); // this shouldn't be cached

        if (false === $pid) {

            $message = 'The php internal function getmypid() has failed' . PHP_EOL . $message;

        } else {

            $message = "<pid::$pid> $message";

        }

        if (false === self::$colorCodeBool) {

            print $message;

            return;

        }

        $colors = iColorCode::PRINTF_ANSI_COLOR;

        if (is_string($color) && !array_key_exists($color, $colors)) {

            $message = "Color provided to color code ($color) is invalid, message caught '$message'";

            $color = iColorCode::RED;

        }

        $location = ini_get('error_log');

        $message = sprintf($colors[$color], $message);

        $status = error_log($message);

        if (false === $location) {

            throw new PublicAlert("Failed to get current log location!");

        }

        if (false === $status) {

            throw new PublicAlert("Failed to write to error log ($location)");

        }    // do not double quote args passed here


        if (null === ErrorCatcher::$defaultLocation || '' === ErrorCatcher::$defaultLocation) {

            return;

        }


        ErrorCatcher::checkCreateLogFile($message);

        switch ($location) {
            case '':
                if (false === ini_set('error_log', ErrorCatcher::$defaultLocation)) {

                    throw new PublicAlert('Failed to set the log location to (' . ErrorCatcher::$defaultLocation . ')');

                } // log to file too

                break;

            case ErrorCatcher::$defaultLocation:

                if (false === CarbonPHP::$cli) {

                    return;

                }

                if (false === ini_set('error_log', '')) {

                    throw new PublicAlert('Detected cli but failed to print color coded logging to standard out.');

                }   // default output // cli stdout

                break;

            default:

                $additional = sprintf($colors[$color], "\n\nThe error_log location set ($location) did not match the CarbonPHP ColorCode enabled error log path ErrorCatcher::\$defaultLocation = (" . ErrorCatcher::$defaultLocation . "); or was not set to an empty string which enables cli output.\n\n", iColorCode::YELLOW);

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($additional);    // do not double quote args passed here

                $message .= $additional; // for old log location

                $lastLoggingLocation = ini_set('error_log', ErrorCatcher::$defaultLocation);

                if (false === $lastLoggingLocation) {

                    throw new PublicAlert('All color coded enabled logs must print to (' . ErrorCatcher::$defaultLocation. ') but switching failed.');

                }

                /** @noinspection ForgottenDebugOutputInspection */
                error_log($message);    // do not double quote args passed here

                if (false === ini_set('error_log', '')) {

                    throw new PublicAlert('Failed to change from c6 standard log file to stdout');

                }           // default output // cli stdout

        }

        /** @noinspection ForgottenDebugOutputInspection */
        if (false === error_log($message)) {

            throw new PublicAlert('Our secondary logging mechanism failed, please review log.');

        }  // do not double quote args passed here

        if (false === ini_set('error_log', $location)) {

            throw new PublicAlert("Failed changing logging location back to ($location), this is unexpected.");

        }    // back to what it was before this function

    }

}
