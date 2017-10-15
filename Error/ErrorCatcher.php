<?php

// http://php.net/manual/en/function.debug-backtrace.php

namespace Carbon\Error;

use Carbon\Singleton;

class ErrorCatcher
{
    use Singleton;                              // todo - put notes also why singleton is used in each class

    private $defaultLocation;
    private $printToScreen;

    public static function start( string $logLocation, bool $printToScreen )
    {
        ini_set( 'display_errors', 1 );
        ini_set( 'track_errors', 1 );
        error_reporting(E_ALL);
        $closure = function (...$argv) use ($logLocation, $printToScreen) {
            $error = new ErrorCatcher();
            $error->defaultLocation = SERVER_ROOT . $logLocation;
            $error->printToScreen = $printToScreen;
            $error->generateLog($argv);
            //View::contents('error','500error');
            exit(1);                                // TODO - Uncomment
        };
        set_error_handler($closure);
        set_exception_handler($closure);
    }

    public static function generateErrorLog($argv = array())
    {
        $self = static::getInstance();
        $self->generateLog($argv);
    }

    public function generateLog($argv = array())
    {
        ob_start( );
        print PHP_EOL. date( 'D, d M Y H:i:s' , time());
        print PHP_EOL. PHP_EOL .'Printout of Function Stack: ' . PHP_EOL;
        print $this->generateCallTrace( ) . PHP_EOL;
        if (count( $argv ) >=4 ){
        echo 'Message: ' . $argv[1] . PHP_EOL;
        echo 'line: ' . $argv[2] .'('. $argv[3] .')';
        } else var_dump( $argv );
        $output = ob_get_contents( );
        ob_end_clean( );

        if ($this->printToScreen)
            print "<pre>$output</pre>";    // dev

        // Write the contents to server
        $file = (defined('ERROR_LOG' ) ? ERROR_LOG : $this->defaultLocation);

        $this->storeFile($file, $output);


    }

    private function generateCallTrace()
    {
        $e = new \Exception( );
        ob_start( );
        $trace = explode( "\n", $e->getTraceAsString() );
        // reverse array to make steps line up chronologically
        $trace = array_reverse( $trace );
        array_shift( $trace ); // remove {main}
        array_pop( $trace ); // remove call to this method
        array_pop( $trace ); // remove call to this method
        $length = count( $trace );
        $result = array( );

        for ( $i = 0; $i < $length; $i++ ) {
            $result[] = ($i + 1) . ') ' . substr(substr( $trace[$i], strpos( $trace[$i], ' ' ) ), 35) . PHP_EOL;
            print PHP_EOL; // replace '#someNum' with '$i)', set the right ordering
        }

        print "\t" . implode( "\n\t", $result );

        $output = ob_get_contents( );
        ob_end_clean( );
        return $output;
    }


    private function storeFile($file, $output)
    {
        if(!file_exists(dirname($file)))
            mkdir(dirname($file));
        $file = fopen($file , "w");
        fwrite( $file, $output );
        fclose( $file );
    }

    
}



