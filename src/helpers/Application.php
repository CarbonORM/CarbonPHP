<?php

namespace {                                     // This runs the following code in the global scope
    use CarbonPHP\CarbonPHP;
    use CarbonPHP\Error\PublicAlert;               //  Displays alerts nicely
    use CarbonPHP\View;                            //  Seamlessly include the DOM

    /**
     * @param $message
     * @param $title
     * @param string $type
     * @param null $icon
     * @param int $status
     * @param bool $intercept
     * @param bool $stack
     */
    function JsonAlert($message, $title = 'danger', $type = 'danger', $icon = null, $status = 500, $intercept = true, $stack = true)
    {
        PublicAlert::JsonAlert($message, $title, $type, $icon, $status, $intercept, $stack);
    }

    /** Start application will start a bootstrap file passed to it. It will
     * store that instance in a static variable and reuse it for the proccess life.
     *
     * @param $reset
     * @link
     *
     * @return null|bool - if this is called recursively we want to make sure were not
     * returning true to a controller function, thus causing the model to run when unneeded.
     * So yes this is a self-stupid check..............
     */
    function startApplication($reset = '') : ? bool
    {
        return CarbonPHP::startApplication($reset);
    }


    /** This extends the PHP's built-in highlight function to highlight
     *  other file types. Currently java and html are custom colored.
     *  All languages should, to some degree, benefit from this.
     * @link http://php.net/manual/en/function.highlight-string.php
     * @param $argv - if a filepath is given load it from memory,
     *  otherwise highlight the string provided as code
     *
     * @param bool $fileExt
     * @return string -- the text highlighted and converted to html
     */
    function highlight($argv, $fileExt = false): string
    {
        if ($fileExt === 'java') {
            ini_set('highlight.comment', '#008000');
            ini_set('highlight.default', '#000000');
            ini_set('highlight.html', '#808080');
            ini_set('highlight.keyword', '#0000BB; font-weight: bold');
            ini_set('highlight.string', '#DD0000');
        } else if ($fileExt === 'html') {
            ini_set('highlight.comment', 'orange');
            ini_set('highlight.default', 'green');
            ini_set('highlight.html', 'blue');
            ini_set('highlight.keyword', 'black');
            ini_set('highlight.string', '#0000FF');
        }

        if (file_exists($argv)) {
            $text = file_get_contents($argv);
            $fileExt = $fileExt ?: pathinfo($argv, PATHINFO_EXTENSION);

            if ($fileExt !== 'php') {
                $text = ' <?php ' . $text;
            }
        } else {
            $text = ' <?php ' . $argv;
        }

        $text = highlight_string($text, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text

        $text = substr_replace($text, '', 0, 6);    // this removes the <code>

        $text = preg_replace('#^<span style="[\w\s\#">;:]*#', '', $text, 1);  // remove prefix

        $text = (($pos = strpos($text, $needle = '&lt;?php')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</span>')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = (($pos = strrpos($text, $needle = '</code>')) ?
            substr_replace($text, '', $pos, strlen($needle)) :
            $text);

        $text = '<span style="overflow-x: scroll">' . $text . '</span>';

        return $text;
    }


    /** Ports the javascript alert function in php.
     * @param string $string
     */
    function alert($string = 'Stay woke')
    {
        static $count = 0;
        ++$count;
        print "<script>alert('(#$count)  $string')</script>\n";
    }

    /** Prots the javascript console.log() function
     * @link http://php.net/manual/en/debugger.php
     * @param string $data data to be sent to the browsers console
     */
    function console_log($data)
    {
        print '<script>console.log(\'' . json_encode($data) . '\')</script>' . PHP_EOL;
    }

    /** Output all parameters given neatly to the screen and continue execution.
     * @param array ...$argv variable length parameters stored as array
     */
    function dump(...$argv)
    {
        echo '<pre>';
        var_dump(\count($argv) === 1 ? array_shift($argv) : $argv);
        echo '</pre>';
    }

    /** This is dump()'s big brother, a better dump per say.
     * By default, this outputs the value passed in and exits our execution.
     * This is convent when dealing with requests that would otherwise refresh the session.
     *
     * @param mixed $mixed the variable to output.
     * You can output multiple variables by wrapping them in an array
     *       [$var, $var2, $anotherVar]
     *
     * @param bool $fullReport this outputs a backtrace and zvalues
     * @link http://php.net/manual/en/function.debug-backtrace.php
     *
     * From personal experience you should not worry about Z-values, as it is almost
     * never ever the issue. I'm 99.9% sure of this, but if you don't trust me you
     * should read this full manual page
     *
     * @link http://php.net/manual/en/internals2.php                 -- the hackers guide
     * @link http://php.net/manual/en/function.debug-zval-dump.php
     *
     * @param bool $die -
     */
    function sortDump($mixed, $fullReport = false, $die = true)
    {
        // Notify that sort dump was executed
        CLI or alert(__FUNCTION__);

        // Generate Report
        ob_start();
        print CLI ? PHP_EOL . PHP_EOL : '<br>';
        print TEST ? 'SortDump Called From' : '################### SortDump Called From ################';
        print CLI ? PHP_EOL . PHP_EOL : '<br><pre>';
        /** @noinspection ForgottenDebugOutputInspection */
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($backtrace[1] ?? $backtrace[0]);
        print CLI ? PHP_EOL . PHP_EOL : '<br></pre>';
        print '####################### VAR DUMP ########################';
        print CLI ? PHP_EOL . PHP_EOL : '<br><pre>';
        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($mixed);
        print CLI ? PHP_EOL : '<br>';
        print '#########################################################';
        if ($fullReport) {
            echo '####################### MIXED DUMP ########################';
            $mixed = (\is_array($mixed) && \count($mixed) === 1 ? array_pop($mixed) : $mixed);
            print CLI ? PHP_EOL . PHP_EOL : '<br><pre>';
            /** @noinspection ForgottenDebugOutputInspection */
            debug_zval_dump($mixed ?: $GLOBALS);
            print CLI ? PHP_EOL . PHP_EOL : '</pre><br><br>';
            echo "\n####################### BACK TRACE ########################\n\n<br><pre>";
            print CLI ? PHP_EOL . PHP_EOL : '<br><pre>';
            /** @noinspection ForgottenDebugOutputInspection */
            var_dump(debug_backtrace());
            print CLI ? PHP_EOL : '</pre>';
        }

        $report = ob_get_clean();

        // TODO - re-create a store to file configuration option
        #$file = REPORTS . 'Dumped/Sort_' . time() . '.log';
        #Files::storeContent($file, $report);

        print $report . PHP_EOL;
        // Output to browser
        if (defined('AJAX') && AJAX) {  //
            print $report;
        } else {
            View::$bufferedContent = base64_encode($report);
        }
        if ($die) {
            exit(1);
        }
    }

}