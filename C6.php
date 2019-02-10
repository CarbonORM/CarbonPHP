<?php

namespace CarbonPHP;

class C6 extends Application
{

    /**
     *  constructor.
     * @param null $structure
     * @throws \CarbonPHP\Error\PublicAlert the only
     * way this will throw an error is if you do
     * not define a url using sockets.
     */
    public function __construct($structure = null)
    {
        parent::__construct($structure);

        #if ($_SESSION['id']) {
        ##View::$wrapper = SERVER_ROOT . APP_VIEW . 'Layout/logged-in-layout.php';
        #}

    }


    public function defaultRoute()  // Sockets will not execute this
    {
        View::$forceWrapper = true; // this will hard refresh the wrapper
        return $this->wrap()('Documentation/Home.php');  // don't change how wrap works, I know it looks funny

        /*
        if (!$_SESSION['id']):
        else:
            return MVC('user', 'profile');
        endif;
        */
    }


    /** we dont use this return value for anything
     * @param null $uri
     * @return bool
     * @throws \CarbonPHP\Error\PublicAlert
     */
    public function startApplication($uri = null): bool
    {

        \defined('TEMPLATE') or \define('TEMPLATE', 'node_modules/admin-lte/');

        $this->structure($this->wrap());

        #################################### CarbonPHP Doc
        if ($this->match('Home/', 'Documentation/Home.php')() ||
            $this->match('CarbonPHP', 'Documentation/Introduction.php')() ||
            $this->match('Installation', 'Documentation/Installation.php')() ||
            $this->match('Implementations', 'Documentation/Implementations.php')() ||
            $this->match('Dependencies', 'Documentation/Dependencies.php')() ||
            $this->match('FileStructure', 'Documentation/QuickStart/FileStructure.php')() ||
            $this->match('Environment', 'Documentation/QuickStart/Environment.php')() ||
            $this->match('Options', 'Documentation/QuickStart/Options.php')() ||
            $this->match('Bootstrap', 'Documentation/QuickStart/Bootstrap.php')() ||
            $this->match('Wrapper', 'Documentation/QuickStart/Wrapper.php')() ||
            $this->match('Parallel', 'Documentation/QuickStart/ParallelProcessing.php')() ||
            $this->match('Overview', 'Documentation/PHP/Overview.php')() ||
            $this->match('Entities', 'Documentation/PHP/Entities.php')() ||
            $this->match('Request', 'Documentation/PHP/Request.php')() ||
            $this->match('Route', 'Documentation/PHP/Route.php')() ||
            $this->match('Server', 'Documentation/PHP/Server.php')() ||
            $this->match('Session', 'Documentation/PHP/Session.php')() ||
            $this->match('Singleton', 'Documentation/PHP/Singleton.php')() ||
            $this->match('View', 'Documentation/PHP/View.php')() ||
            $this->match('OSSupport', 'Documentation/PlatformSupport.php')() ||
            $this->match('UpgradeGuide', 'Documentation/PlatformSupport.php')() ||
            $this->match('Support', 'Documentation/Support.php')() ||
            $this->match('License', 'Documentation/License.php')() ||
            $this->match('AdminLTE', 'Documentation/AdminLTE.php')() ||
            $this->match('N00B', 'Documentation/N00B.php')()) {
            return true;
        }


        ###################################### AdminLTE DOC
        if ($this->match('UIElements/{AdminLTE?}', function ($AdminLTE) use ($uri) {

            $AdminLTE = (new Request())->set($AdminLTE)->word();  // must be validated

            if (!$AdminLTE) {
                View::$forceWrapper = true;
                $AdminLTE = 'widgets';
            }

            if (file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')) {

                View::$wrapper = SERVER_ROOT . APP_VIEW . 'AdminLTE/wrapper.php';

                $this->wrap()($path);
            }
        })()) {
            return true;
        }

        return false;
    }
}
