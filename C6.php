<?php

namespace CarbonPHP;

use CarbonPHP\Error\PublicAlert;

class C6 extends Application
{
    private static $adminLTE = [
        'Home/' => 'mustache/Documentation/Home.php',
        'CarbonPHP' => 'mustache/Documentation/Introduction.php',
        'Installation' => 'mustache/Documentation/Installation.php',
        'Implementations' => 'mustache/Documentation/Implementations.php',
        'Dependencies' => 'mustache/Documentation/Dependencies.php',
        'FileStructure' => 'mustache/Documentation/QuickStart/FileStructure.php',
        'Environment' => 'mustache/Documentation/QuickStart/Environment.php',
        'Options' => 'mustache/Documentation/QuickStart/Options.php',
        'Bootstrap' => 'mustache/Documentation/QuickStart/Bootstrap.php',
        'Wrapper' => 'mustache/Documentation/QuickStart/Wrapper.php',
        'Parallel' => 'mustache/Documentation/QuickStart/ParallelProcessing.php',
        'Overview' => 'mustache/Documentation/PHP/Overview.php',
        'Entities' => 'mustache/Documentation/PHP/Entities.php',
        'Request' => 'mustache/Documentation/PHP/Request.php',
        'Route' => 'mustache/Documentation/PHP/Route.php',
        'Server' => 'mustache/Documentation/PHP/Server.php',
        'Session' => 'mustache/Documentation/PHP/Session.php',
        'Singleton' => 'mustache/Documentation/PHP/Singleton.php',
        'View' => 'mustache/Documentation/PHP/View.php',
        'OSSupport' => 'mustache/Documentation/PlatformSupport.php',
        'UpgradeGuide' => 'mustache/Documentation/PlatformSupport.php',
        'Support' => 'mustache/Documentation/Support.php',
        'License' => 'mustache/Documentation/License.php',
        'AdminLTE' => 'mustache/Documentation/AdminLTE.php',
        'N00B' => 'mustache/Documentation/N00B.php'
    ];


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
        return $this->wrap()('mustache/Documentation/Home.php');  // don't change how wrap works, I know it looks funny

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
        global $json;

        \defined('TEMPLATE') or \define('TEMPLATE', 'node_modules/admin-lte/');


        if ($version = ($this->uri[0] ?? false)) {
            switch ($version) {
                case '2.0':
                    $json['VersionTWO'] = true;
                    $page = $this->uri[1] ?? false;
                    if ($page && array_key_exists($page, self::$adminLTE)) {
                        $this->matched = true;
                        $this->wrap()(self::$adminLTE[$page]);
                        return true;
                    }
                    break;
                case '5.0':
                    $this->fullPage()('react/material-dashboard-react-c6/build/index.html');
                    return true;
                    break;
                default:
                    break;
            }
        }

        # $this->structure($this->JSON());

        if ($this->match('carbon/authenticated', function () {
            global $json;

            header('Content-Type: application/json', true, 200); // Send as JSON

            # PublicAlert::JsonAlert('Test', 'Success', 'success', 'success');

            $json['success'] = false;

            print json_encode($json);

            return true;
        })()) {
            return true;
        }


        $this->structure($this->wrap());

        ###################################### AdminLTE DOC
        if ($this->match('UIElements/{AdminLTE?}', function ($AdminLTE) use ($uri) {

            $AdminLTE = (new Request())->set($AdminLTE)->word();  // must be validated

            if (!$AdminLTE) {
                View::$forceWrapper = true;
                $AdminLTE = 'widgets';
            }

            if (file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                file_exists(SERVER_ROOT . APP_VIEW . $path = 'mustache' . DS . 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')) {

                View::$wrapper = SERVER_ROOT . APP_VIEW . 'mustache/AdminLTE/wrapper.php';

                $this->wrap()($path);
            }
        })()) {
            return true;
        }

        return false;
    }
}
