<?php

declare(strict_types=1);

namespace {                                     // This runs the following code in the global scope
    use CarbonPHP\Abstracts\ColorCode;
    use CarbonPHP\CarbonPHP;
    use CarbonPHP\Interfaces\iColorCode;
    use CarbonPHP\Throwables\PublicAlert;

    /** @noinspection SpellCheckingInspection */
    if (!function_exists('getallheaders')) {
        /** @noinspection SpellCheckingInspection - pollyfill for not only nginx, but websocket requests in apache?? */
        function getallheaders(): array
        {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        }
    }

    //  Displays alerts nicely
    //  Seamlessly include the DOM
    if (!function_exists('JsonAlert')) {
        /**
         * @param $message
         * @param $title
         * @param string $type
         * @param null $icon
         * @param int $status
         * @param bool $intercept
         * @param bool $stack
         */
        function JsonAlert($message, $title = 'danger', $type = 'danger', $icon = null, $status = 400, $intercept = true, $stack = true)
        {
            PublicAlert::JsonAlert($message, $title, $type, $icon, $status, $intercept, $stack);
        }
    }

    if (!function_exists('highlight')) {
        /** This extends the PHP's built-in highlight function to highlight
         *  other file types. Currently java and html are custom colored.
         *  All languages should, to some degree, benefit from this.
         *
         * @see http://php.net/manual/en/function.highlight-string.php
         *
         * @param $argv - if a filepath is given load it from memory,
         *             otherwise highlight the string provided as code
         *
         * adding the following to your css will be essential
         * @param bool $fileExt
         *
         * @return string -- the text highlighted and converted to html
         *
         */
        function highlight($argv, string|bool $fileExt = false, int $startLineNumber = 1): string
        {
            if ($fileExt === 'java') {
                ini_set('highlight.comment', '#008000');
                ini_set('highlight.default', '#000000');
                ini_set('highlight.html', '#808080');
                ini_set('highlight.keyword', '#0000BB; font-weight: bold');
                ini_set('highlight.string', '#DD0000');
            } elseif ($fileExt === 'html') {
                ini_set('highlight.comment', 'orange');
                ini_set('highlight.default', 'green');
                ini_set('highlight.html', 'blue');
                ini_set('highlight.keyword', 'black');
                ini_set('highlight.string', '#0000FF');
            }

            $startLineNumber -= 2;

            if (file_exists($argv)) {
                $text = file_get_contents($argv);

                $lines = implode('<br />', range($startLineNumber, $startLineNumber + count(file($argv))));

                $fileExt = $fileExt ?: pathinfo($argv, PATHINFO_EXTENSION);

                if ($fileExt !== 'php') {
                    $text = ' <?php ' . $text;
                }
            } else {
                $text = ' <?php ' . $argv;

                $lines = implode('<br />', range($startLineNumber, $startLineNumber + count(explode(PHP_EOL, $text))));
            }

            $text = highlight_string($text, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text

            $text = preg_replace('#</?(span|code)(\sstyle="[\w\s\#;:]+")?>#', '', $text, 1);  // remove prefix

            $text = (($pos = strpos($text, $needle = '&lt;?php')) ?
                substr_replace($text, '', $pos, strlen($needle)) :
                $text);

            $text = '<span style="overflow-x: scroll">' . $text . '</span>';

            return "<table style='width: 100%'><tr><td class=\"num\" style='color: whitesmoke'>\n$lines\n</td><td>\n$text\n</td></tr></table>";
        }
    }

    if (!function_exists('alert')) {
        /** Ports the javascript alert function in php.
         * @param string $string
         */
        function alert($string = 'Stay woke')
        {
            static $count = 0;
            ++$count;
            echo "<script>alert('(#$count)  $string')</script>\n";
        }
    }

    if (!function_exists('console_log')) {
        /** Prots the javascript console.log() function
         * @see http://php.net/manual/en/debugger.php
         *
         * @param string $data data to be sent to the browsers console
         */
        function console_log($data)
        {
            echo '<script>console.log(\'' . json_encode($data) . '\')</script>' . PHP_EOL;
        }
    }

    if (!function_exists('dump')) {
        /** Output all parameters given neatly to the screen and continue execution.
         * @param array ...$argv variable length parameters stored as array
         */
        function dump(...$argv)
        {
            if (CarbonPHP::CLI) {
                echo '<pre>';
            }

            var_dump(count($argv) === 1 ? array_shift($argv) : $argv);

            if (CarbonPHP::CLI) {
                echo '</pre>';
            }
        }
    }

    if (!function_exists('sortDump')) {
        /** This is dump()'s big brother, a better dump per say.
         * By default, this outputs the value passed in and exits our execution.
         * This is convent when dealing with requests that would otherwise refresh the session.
         *
         * @param mixed $mixed the variable to output.
         *                     You can output multiple variables by wrapping them in an array
         *                     [$var, $var2, $anotherVar]
         * @param bool $fullReport this outputs a backtrace and zvalues
         * @param bool $die -
         * @param int $debug_backtrace_limit
         *
         * @see http://php.net/manual/en/function.debug-zval-dump.php
         * @see http://php.net/manual/en/function.debug-backtrace.php
         *
         * From personal experience you should not worry about Z-values, as it is almost
         * never ever the issue. I'm 99.9% sure of this, but if you don't trust me you
         * should read this full manual page. Z-values typically only come to play when
         * your using advanced referencing using & and then trying to manually (no GC)
         * unset an allocated reference variable. It should be noted that explicitly
         * passing by reference is slower than to not do such. It should not be your
         * first choice in writing php code, though maybe appropriate*. Thus running
         * into a z-value issues is extremely low.
         *
         * @noinspection ForgottenDebugOutputInspection
         */
        function sortDump($mixed, bool $fullReport = false, bool $die = true, int $debug_backtrace_limit = 50): void
        {
            // Notify that sort dump was executed
            CarbonPHP::CLI or alert(__FUNCTION__);

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $debug_backtrace_limit);

            // Generate Report -- keep in mind were in a buffer here
            $output = static function (bool $cli) use ($mixed, $fullReport, $backtrace, $debug_backtrace_limit): string {
                ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

                echo $cli ? PHP_EOL . PHP_EOL : '<br>';

                echo $cli ? "SortDump Called With (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $debug_backtrace_limit)) From" : "################### SortDump Called With (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $debug_backtrace_limit)) From ################";

                echo $cli ? PHP_EOL . PHP_EOL : '<br><pre>';

                var_dump($backtrace ?? $backtrace[0]);

                echo $cli ? PHP_EOL . PHP_EOL : '<br></pre>';

                echo '####################### VAR DUMP ########################';

                echo $cli ? PHP_EOL . PHP_EOL : '<br><pre>';

                var_dump($mixed);

                echo $cli ? PHP_EOL : '<br>';

                echo '#########################################################';

                if ($fullReport) {
                    echo '####################### MIXED DUMP ########################';

                    $mixed = (\is_array($mixed) && \count($mixed) === 1 ? array_pop($mixed) : $mixed);

                    echo $cli ? PHP_EOL . PHP_EOL : '<br><pre>';

                    debug_zval_dump($mixed ?: $GLOBALS);

                    echo $cli ? PHP_EOL . PHP_EOL : '</pre><br><br>';

                    echo "\n####################### BACK TRACE ########################\n\n<br><pre>";

                    echo $cli ? PHP_EOL . PHP_EOL : '<br><pre>';

                    var_dump(debug_backtrace());

                    echo $cli ? PHP_EOL : '</pre>';
                }

                return ob_get_clean();
            };

            // TODO - re-create a store to file configuration option
            // $file = REPORTS . 'Dumped/Sort_' . time() . '.log';
            // Files::storeContent($file, $report);
            if (!CarbonPHP::CLI) {
                print $output(false);
            } else {
                $report = $output(true);
                ColorCode::colorCode($report . PHP_EOL, iColorCode::RED);
            }

            if ($die) {
                exit(1);
            }
        }
    }

    if (!function_exists('zValue')) {
        /**
         * Will typically be one more than expected as the reference
         * to this function will add one to the total
         *
         * @see https://www.php.net/manual/en/function.debug-zval-dump.php
         *
         * @param $mixed
         *
         * @noinspection ForgottenDebugOutputInspection
         */
        function zValue($mixed)
        {
            echo CarbonPHP::CLI ? PHP_EOL . PHP_EOL : '<br><pre>';
            debug_zval_dump($mixed);
            echo CarbonPHP::CLI ? PHP_EOL . PHP_EOL : '</pre><br><br>';
            exit(1);
        }
    }

    if (!function_exists('array_merge_recursive_distinct')) {
        /**
         * Recursively merge a variable number of arrays, using the left array as base, giving priority to the right array.
         *
         * Difference with native array_merge_recursive(): array_merge_recursive converts values with duplicate keys to arrays
         * rather than overwriting the value in the first array with the duplicate value in the second array.
         * array_merge_recursive_distinct does not change the data types of the values in the arrays. Matching keys' values
         * in the second array overwrite those in the first array, as is the case with array_merge. Freely based on
         * information found on http://www.php.net/manual/en/function.array-merge-recursive.php
         *
         * @return array Merged array
         *
         * @see https://wordpress-seo.wp-a2z.org/oik_api/wpseo_metaarray_merge_recursive_distinct/
         */
        function array_merge_recursive_distinct()
        {
            $arrays = func_get_args();

            if (count($arrays) < 2) {
                if ($arrays === []) {
                    return [];
                }

                return $arrays[0];
            }

            $merged = array_shift($arrays);

            foreach ($arrays as $array) {
                foreach ($array as $key => $value) {
                    if (is_array($value) && (isset($merged[$key]) && is_array($merged[$key]))) {
                        $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                }
            }

            return $merged;
        }
    }

    if (!function_exists('errorLevelToString')) {
        /**
         * @see https://stackoverflow.com/questions/9167548/how-can-i-display-echo-print-the-currently-set-error-reporting-level-in-php
         *
         * @param int|null $errorLevel
         * @param string $separator
         *
         * @return string
         */
        function errorLevelToString(?int $errorLevel = null, string $separator = ' & '): string
        {
            if (null === $errorLevel) {
                $errorLevel = error_reporting();
            }

            $errorLevels = [
                E_ALL => 'E_ALL',
                E_USER_DEPRECATED => 'E_USER_DEPRECATED',
                E_DEPRECATED => 'E_DEPRECATED',
                E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                E_USER_NOTICE => 'E_USER_NOTICE',
                E_USER_WARNING => 'E_USER_WARNING',
                E_USER_ERROR => 'E_USER_ERROR',
                E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                E_CORE_WARNING => 'E_CORE_WARNING',
                E_CORE_ERROR => 'E_CORE_ERROR',
                E_NOTICE => 'E_NOTICE',
                E_PARSE => 'E_PARSE',
                E_WARNING => 'E_WARNING',
                E_ERROR => 'E_ERROR'];

            $result = '';

            foreach ($errorLevels as $number => $name) {
                if (($errorLevel & $number) === $number) {
                    $result .= ($result !== '' ? $separator : '') . $name;
                }
            }

            return $result;
        }
    }
}
