<?php

namespace Carbon;

/* The function View is auto loaded on the initial view class call.
    the view class will only point to the 'current-master' template
            Which in this case is the class AdminLTE
*/

class View
{
    use Singleton;
    const Singleton = true;

    public $currentPage;
    private $carryErrors;
    private $forceStoreContent;

    // sockets cannot possibly invoke the wake up function
    public function __wakeup()
    {
        if (!AJAX):      // an HTTP request
            $_POST = [];
            $this->__construct();                       // and reprocess the dependencies, wrapper is a global closure
        elseif (!empty( $this->currentPage )):          // Implies AJAX && a page has already been rendered and stored
            echo base64_decode( $this->currentPage );   // The ajax page will be 64encoded before we store on the server
            $this->currentPage = false;
            self::clearInstance();                      // Remove stored information on the server and delete its self reference
            exit( 1 );                                  // This is for the second inner AJAX request on first page load
        endif;                                          // We're requesting our second page through ajax ie not initial page request
    }

    public function __construct($forceWrapper = false)   // Send the content wrapper
    {
        if (SOCKET) return null;

        #if (AJAX)
        # $closure = AJAX_SIGNED_OUT;

        if (!WRAPPING_REQUIRES_LOGIN ?: $_SESSION['id']) {
            if (!($forceWrapper || ($_SESSION['X_PJAX_Version'] != X_PJAX_VERSION)) && AJAX)
                return null;
            $_POST = [];

            ob_start();

            require(CONTENT_WRAPPER);   // Return the Template

            $template = ob_get_clean();

            echo (MINIFY_CONTENTS && (@include_once CARBON_ROOT . "Extras/minify.php")) ? minify_html( $template ) : $template;


            if ($forceWrapper):
                if (!empty( $GLOBALS['alert'] )) $this->carryErrors = $GLOBALS['alert']; // exit(1);
                $this->forceStoreContent = true;
            endif;
        } // elseif (AJAX && is_callable($closure)) $closure();  // This would only be executed it wrapper_requires_login = true and user logged out, this can be helpful for making sure the user doesnt back into a state
        // if there it is an ajax request, the user must be logged in, or container must be true
    }

    public static function contents(...$argv)
    {
        $self = static::getInstance();
        call_user_func_array( [$self, 'content'], $argv );
    }

    public function content(...$argv) : void // Must be called through Singleton, must be private
    {

        switch (count( $argv )) {
            case 2:
                $file = CONTENT_ROOT . strtolower( $argv[0] ) . DS . strtolower( $argv[1] ) . '.php';   //($this->user->user_id ? '.tpl.php' : '.php'));
                break;
            case 1:
                $file = @file_exists( $argv[0] ) ? $argv[0] : CONTENT_ROOT . $argv[0];
                break;
            default:
                throw new \InvalidArgumentException();
        }

        if (file_exists( $file )) {
            if (SOCKET) {
                include $file;          // we not need compression / buffering for sockets
                return;
            }

            ob_start();

            if (empty( $GLOBALS['alert'] ) && !empty( $GLOBALS['alert'] = $this->carryErrors ))
                $this->carryErrors = null;

            if (isset($this->alert)) {
                if (isset($this->alert['danger'])) $this->bootstrapAlert($this->alert['danger'], 'danger');
                if (isset($this->alert['info'])) $this->bootstrapAlert($this->alert['info'], 'info');
                if (isset($this->alert['warning'])) $this->bootstrapAlert($this->alert['warning'], 'warning');
                if (isset($this->alert['success'])) $this->bootstrapAlert($this->alert['success'], 'success');
                $this->alert = null;
            }

            include $file;

            $file = ob_get_clean();

            if (MINIFY_CONTENTS && (@include_once CARBON_ROOT . "Extras/minify.php"))
                $file = minify_html( $file );


            if ($this->forceStoreContent || (!AJAX && (!WRAPPING_REQUIRES_LOGIN ?: $_SESSION['id']))) {
                # $this->forceStoreContent = false;
                $this->currentPage = base64_encode( $file );
                exit(1);
            } else echo $file;

        } else throw new \Exception( "$file does not exist" );  // TODO - throw 404 error

    }

    /**
     *  Given a file, i.e. /css/base.css, replaces it with a string containing the
     *  file's mtime, i.e. /css/base.1221534296.css.
     *
     * @param $file
     *  file to be loaded.  Must be an absolute path (i.e.
     *                starting with slash).
     * @return mixed  file to be loaded.
     */


    public function bootstrapAlert($message, $level) : void
    {
        $message = htmlentities( $message );
        echo "<script>bootstrapAlert(\"$message\", '$level')</script>";
    }


    public function versionControl($file)
    {
        if (!file_exists( $absolute = SERVER_ROOT . $file ) || !($time = filemtime($absolute)))
                return $file;
        return preg_replace( '{\\.([^./]+)$}', "." . $time . ".\$1", SERVER_ROOT . $file );
    }

    public function __get($variable)
    {
        return (isset( $GLOBALS[$variable] ) ? $GLOBALS[$variable] : null);
    }


}

