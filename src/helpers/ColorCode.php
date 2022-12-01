<?php

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Throwable;

trait ColorCode
{

    public static string $generalWarning = 'As seen in comments of (@link https://www.php.net/manual/en/function.ini-set.php); I have experienced on some systems that ini_set() will fail and return a false, when trying to set a setting that was set inside php.ini inside a per-host setting. This would also include php-fpm configuration (php_admin_value[error_log] and php_admin_flag[log_errors]). You should comment out these lines if they exist. Beware of this.';

    public static bool $colorCodeBool = true;

    public static bool $changingLocationsFailed = false;


    /**
     * Logs with an arbitrary level; psr defined explicitly in LoggerTrait
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     *
     * @throws InvalidArgumentException
     * @link https://www.php-fig.org/psr/psr-3/
     */
    public function log($level, $message, array $context = []): void
    {
        // these refer ot the functions in trait LoggerTrait
        self::colorCode($message, match ($level) {
            LogLevel::ERROR => iColorCode::RED,
            LogLevel::CRITICAL, LogLevel::EMERGENCY => iColorCode::BACKGROUND_RED,
            LogLevel::ALERT => iColorCode::BLUE,
            LogLevel::WARNING => iColorCode::BACKGROUND_YELLOW,
            LogLevel::NOTICE => iColorCode::YELLOW,
            LogLevel::INFO => iColorCode::CYAN,
            LogLevel::DEBUG => iColorCode::MAGENTA,
            default => iColorCode::BACKGROUND_GREEN,
        });
    }

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @link https://www.php.net/manual/en/function.syslog.php
     * @noinspection ForgottenDebugOutputInspection
     */
    public static function colorCode(string $message, string $color = iColorCode::GREEN): void
    {

        try {

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

            if (true === self::$changingLocationsFailed) {

                return;

            }

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

                        throw new PublicAlert('Failed to set the log location to (' . ErrorCatcher::$defaultLocation . '). ' . self::$generalWarning);

                    } // log to file too

                    break;

                case ErrorCatcher::$defaultLocation:

                    if (false === CarbonPHP::$cli) {

                        return;

                    }

                    if (false === ini_set('error_log', '')) {

                        throw new PublicAlert('Detected cli but failed to print color coded logging to standard out. ' . self::$generalWarning);

                    }   // default output // cli stdout

                    break;

                default:

                    $additional = sprintf($colors[$color], "\n\nThe error_log location set ($location) did not match the CarbonPHP ColorCode enabled error log path ErrorCatcher::\$defaultLocation = (" . ErrorCatcher::$defaultLocation . "); or was not set to an empty string which enables cli output.\n\n", iColorCode::YELLOW);

                    /** @noinspection ForgottenDebugOutputInspection */
                    if (false === error_log($additional)) {

                        throw new PublicAlert("Failed to log! This is directly after successfully logging to the same file. Please investigate.");

                    }  // do not double quote args passed here

                    $message .= $additional; // for old log location

                    $lastLoggingLocation = ini_set('error_log', ErrorCatcher::$defaultLocation);

                    if (false === $lastLoggingLocation) {

                        throw new PublicAlert('All color coded enabled logs must print to (' . ErrorCatcher::$defaultLocation . ") but switching from ($location) failed. "  . self::$generalWarning);

                    }

                    /** @noinspection ForgottenDebugOutputInspection */
                    if (false === error_log($message)) {

                        throw new PublicAlert('Failed writing to error log after location switch. This is unexpected.');

                    }  // do not double quote args passed here

                    if (false === ini_set('error_log', '')) {

                        throw new PublicAlert('Failed to change from c6 standard log file to stdout!');

                    }           // default output // cli stdout

            }

            /** @noinspection ForgottenDebugOutputInspection */
            if (false === error_log($message)) {

                throw new PublicAlert('Our secondary logging mechanism failed, please review log.');

            }  // do not double quote args passed here

            if (false === ini_set('error_log', $location)) {

                throw new PublicAlert("Failed changing logging location back to ($location), this is unexpected.");

            }    // back to what it was before this function

        } catch (Throwable $e) {

            self::$changingLocationsFailed = true;

            ErrorCatcher::generateLog($e);

            exit(1);

        }

    }

}
