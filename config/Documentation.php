<?php

namespace Config;


use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\Deployment;
use CarbonPHP\Programs\WebSocket;
use CarbonPHP\Request;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Carbon_Users;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\View;

class Documentation extends Application implements iConfig
{

    // these are all relative to the /view/ directory
    private const REACT = '60/index.html';

    private array $version2Dot0 = [
        'Home' => '20/Home.hbs',
        'CarbonPHP' => '20/Introduction.hbs',
        'Installation' => '20/Installation.hbs',
        'Implementations' => '20/Implementations.hbs',
        'Dependencies' => '20/Dependencies.hbs',
        'FileStructure' => '20/QuickStart/FileStructure.hbs',
        'Environment' => '20/QuickStart/Environment.hbs',
        'Options' => '20/QuickStart/Options.hbs',
        'Bootstrap' => '20/QuickStart/Bootstrap.hbs',
        'Wrapper' => '20/QuickStart/Wrapper.hbs',
        'Parallel' => '20/QuickStart/ParallelProcessing.hbs',
        'Overview' => '20/PHP/Overview.hbs',
        'Entities' => '20/PHP/Entities.hbs',
        'Request' => '20/PHP/Request.hbs',
        'Route' => '20/PHP/Route.hbs',
        'Server' => '20/PHP/Server.hbs',
        'Session' => '20/PHP/Session.hbs',
        'Singleton' => '20/PHP/Singleton.hbs',
        'View' => '20/PHP/View.hbs',
        'OSSupport' => '20/PlatformSupport.hbs',
        'UpgradeGuide' => '20/PlatformSupport.hbs',
        'Support' => '20/Support.hbs',
        'License' => '20/License.hbs',
        'AdminLTE' => '20/AdminLTE.hbs',
        'N00B' => '20/N00B.hbs'
    ];

    public const TEMPLATE = 'node_modules/admin-lte/';


    /**
     * @return mixed|void
     * @throws PublicAlert
     */
    public function defaultRoute(): void // Sockets will not execute this
    {
        self::getUser();

        View::$forceWrapper = true; // this will hard refresh the wrapper

        if (CarbonPHP::$app_local) {
            throw new PublicAlert('You should run the live version on <a href="http://dev.carbonphp.com:3000/" style="color:#ff0084">port 3000</a> with the command<br/><b>>> npm start </b> 
    <br/>To bypass this message <a href="http://dev.carbonphp.com:8080/6.0/" style="color:blue">click here</a>');
        }

        $this->fullPage()(self::REACT);
    }


    /**
     * todo - document well what can go into constructor and when.
     * @param null $structure
     * way this will throw an error is if you do
     * not define a url using sockets.
     * @throws PublicAlert
     */
    public function __construct($structure = null)
    {

        if (CarbonPHP::$safelyExit) {
            return;
        }

        global $json;

        if (!is_array($json)) {
            $json = array();
        }
        $json['SITE'] = CarbonPHP::$site;
        $json['POST'] = $_POST;
        $json['HTTP'] = CarbonPHP::$http ? 'True' : 'False';
        $json['HTTPS'] = CarbonPHP::$https ? 'True' : 'False';
        $json['SOCKET'] = CarbonPHP::$socket ? 'True' : 'False';
        $json['AJAX'] = CarbonPHP::$ajax ? 'True' : 'False';
        $json['PJAX'] = CarbonPHP::$pjax ? 'True' : 'False';
        $json['SITE_TITLE'] = CarbonPHP::$site_title;
        $json['CarbonPHP::$app_view'] = CarbonPHP::$app_view;
        $json['COMPOSER'] = CarbonPHP::CARBON_ROOT;
        $json['X_PJAX_Version'] = &$_SESSION['X_PJAX_Version'];
        $json['FACEBOOK_APP_ID'] = '';

        parent::__construct($structure);
    }


    /**  NOTE:
     *  This is actually overriding the CM function in Carbon/Application.
     *  I do this because the namespace changes for other applications not
     *  in C6 context.
     *
     *  Stands for Controller -> Model
     *
     * This will run the controller/$class.$method().
     * If the method returns !empty() the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model.
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @TODO - I remember once using the return by reference to allow the changing of model from the controller. We now just use the return value of startApplication ie. false
     *       - while I think this is a good use case, I think the obfuscation that this logic holds isn't true to the MVC arch thus shouldn't be done. I think with this in mind
     *       - removing the & would same time in every route.... and I do not see a valid use case for it now (logically could it be used). I think we can remove with a minor version bump
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed the returned value from model/$class.$method() or false | void
     * @noinspection DuplicatedCode                    - intellij needing help (avoiding unnecessary abstraction)
     * @noinspection UnknownInspectionInspection       - intellij not helping intellij
     */
    public static function CM(string $class, string &$method, array &$argv = []): callable
    {
        $class = ucfirst(strtolower($class));   // Prevent malformed class names
        $controller = "CarbonPHP\\Controller\\$class";     // add namespace for autoloader
        $model = "CarbonPHP\\Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists
        if (!class_exists($controller)) {
            print "Invalid Controller ($controller) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        if (!class_exists($model)) {
            print "Invalid Model ($model) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        // the array $argv will be passed as arguments to the method requested, see link above
        $exec = static function &(string $class, array &$argv) use ($method) {
            $argv = call_user_func_array([new $class, $method], $argv);
            return $argv;
        };

        return static function () use ($exec, $controller, $model, &$argv) {
            // execute controller
            $argv = $exec($controller, $argv);

            if (!empty($argv)) {                            // continue to the model?
                if (is_array($argv)) {
                    return $exec($model, $argv);        // array passed
                }
                $controller = [&$argv];                     // single return, allow return by reference TODO - is this still a thing? (not imperative
                return $exec($model, $controller);
            }

            return $argv;
        };
    }


    /** TODO - we dont use this return value for anything
     * @param string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        global $json;

        $json['APP_LOCAL'] = CarbonPHP::$app_local;

        self::getUser();

        if (Deployment::github($this)()) {
            return true;
        }

        if (CarbonPHP::$socket && $this->regexMatch('#echo/([a-z0-9]+)#i',
                static function ($echo) use ($uri) {
                    WebSocket::sendToAllExternalResources("Echo Server On URI ($uri) :: \$echo = $echo");
                })()) {
            return true;
        }


        if ($this->regexMatch('#(!?ws|wss)#i', static function () {
            self::$matched = true;

            $colours = array('007AFF', 'FF7000', 'FF7000', '15E25F', 'CFC700', 'CFC700', 'CF1100', 'CF00BE', 'F00');

            $user_colour = array_rand($colours);

            $session_id = session_id();

            print <<<SOCKET


<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'/>
    <style type="text/css">
        html { 
             background: url("/view/assets/img/Carbon-teal-180.png") no-repeat center center fixed; 
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        textarea code{
            overflow: scroll;
        }
        
        .innerText {
            padding: 10px;
        }
        
       .background-shift {
            background-color: azure;
            width: 100%;
       }
        <!--
        .chat_wrapper {
            width: 75%;
            margin-right: auto;
            margin-left: auto;
            background: rgba(149,149,149,0.51);
            border: 1px solid #999999;
            font: 12px 'lucida grande', tahoma, verdana, arial, sans-serif;
            padding: 15px;
        }

        .chat_wrapper {
             padding: 10px;
        }
        .chat_wrapper .message_box {
            background: ghostwhite;
            height: 500px;
            overflow: auto;
            border: 1px solid #999999;
        }

        .chat_wrapper .panel {
            padding: 2px 2px 2px 5px;
        }

        .system_msg {
        overflow: scroll;
            color: #BDBDBD;
            font-style: italic;
            padding-bottom: 10px;
        }

        .user_name {
            font-weight: bold;
        }

        .user_message {
            color: #88B6E0;
        }

        -->
    </style>
</head>
<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<h1 style="color: whitesmoke">Websocket Console $session_id</h1>
<script language="javascript" type="text/javascript">

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

    let countMessages = 0;
  
$(document).ready(function () {

    //create a new WebSocket object.
    let wsUri = "ws://dev.carbonphp.com:8888/ws";
    let websocket = new WebSocket(wsUri);
    
    

    websocket.onopen = function (ev) { // connection is open
      $('#message_box').append("<div class=\"system_msg\"><p class=\"innerText\">Connected!</p></div>"); //notify user
    };
    
    const onClickCallback = function () { //use clicks message send button
       
      let myMessage = $('#message').val();  //get message text
        
      if (myMessage === "") { //emtpy message?
        alert("Enter Some message Please!");
        return;
      }

      //prepare json data
      let msg = {
        message: myMessage,
        color: '$colours[$user_colour]'
      };
      //convert and send data to server
      websocket.send(JSON.stringify(msg));
      websocket.send(JSON.stringify(myMessage));
    };
    
    $(document).keypress(function(event){
      const keycode = (event.keyCode ? event.keyCode : event.which);
      if(keycode === 13){
            console.log('Enter Pressed');
            onClickCallback();    
      }
    });
    
    $('#send-btn').click(()=>onClickCallback());

    //#### Message received from server?
    websocket.onmessage = function (ev) {
      let changeColor = ++countMessages % 2;
      
      let json = ev.data;
      
      while(IsJsonString(json)) {
        json = JSON.parse(json);
      }
      
      let msg = json, //PHP sends Json data
        type = msg.type, //message type
        umsg = msg.message, //message text
        uname = msg.name, //user name
        ucolor = msg.color; //color
               

      if (type === 'usermsg') {
        $('#message_box').append("<div><span class=\"user_name" + (changeColor?" background-shift":"") + "\" style=\"color:#" + ucolor + "\">" + uname + "</span> : <span class=\"user_message\">" + umsg + "</span></div>");
      } else if (type === 'system') {
        $('#message_box').append("<div class=\"system_msg" + (changeColor?" background-shift":"") + "\"><p class=\"innerText\">" + umsg + "</p></div>");
      } else {
        if (typeof msg === 'object') {
           $('#message_box').append("<div class=\"system_msg" + (changeColor?" background-shift":"") + "\"><pre><code class=\"data\" style=\"color:blue\">"  + JSON.stringify(msg, undefined, 4) + "</code></pre></div>");
        } else {
           $('#message_box').append("<div class=\"system_msg" + (changeColor?" background-shift":"") + "\"><textarea disabled=\"true\" style=\"border: none;padding:10px;background-color:" + (changeColor?" azure":"ghostwhite") + ";width:100%\">"  + msg + "</textarea></div>");
        }
      }
      $('#message').val(''); //reset text
      
      $('#message_box').animate({
        scrollTop: $('#message_box').get(0).scrollHeight
      }, 2000);
    };

   
    websocket.onerror = function (ev) {
      $('#message_box').append("<div class=\"system_error\"><p class=\"innerText\">Error Occurred - " + ev.data + "</p></div>");
    };
    websocket.onclose = function (ev) {
      $('#message_box').append("<div class=\"system_msg\"><p class=\"innerText\">Connection Closed</p></div>");
    };
  });
</script>
<div class="chat_wrapper">
    <div class="message_box" id="message_box"></div>
    <div class="panel">
        <input type="text" name="message" id="message" placeholder="\$uri" maxlength="80" style="width:75%"/>
        <button id="send-btn">startApplication(\$uri)</button>
    </div>
</div>

</body>
</html>



SOCKET;
        })()) {
            return true;
        }

        if (Rest::MatchRestfulRequests($this)()) {
            return true;
        }

        if (CarbonPHP::$app_local && $this->regexMatch('#color#', static function () {
                global $json;

                if (array_key_exists('code', $_POST)) {
                    if (!is_string($_POST['code'])) {
                        throw new PublicAlert('You must submit a string. This could be code or a file path.');
                    }
                    $json['colorCode'] = highlight($_POST['code'], false);
                } else {
                    $json['colorCode'] = '';
                }
                View::$wrapper = CarbonPHP::$app_root . CarbonPHP::$app_view . 'assets/AdminLTE/wrapper.hbs';
                return View::content(CarbonPHP::$app_view . 'color' . DS . 'color.hbs', CarbonPHP::$app_root);
            })()) {
            return true;
        }

        $this->structure($this->wrap());


        ###################################### AdminLTE DOC
        if ($this->regexMatch('#2.0/UIElements/?([A-Za-z]{0,20})#',
            function ($AdminLTE = '') {

                View::$wrapper = CarbonPHP::$app_root . CarbonPHP::$app_view . 'assets/AdminLTE/wrapper.hbs';

                if ($AdminLTE === '' ||
                    !(new Request())->set($AdminLTE)->word() ||
                    !(
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Charts' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Examples' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Forms' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Layout' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Mailbox' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'Tables' . DS . $AdminLTE . '.php') ||
                        file_exists(CarbonPHP::$app_root . CarbonPHP::$app_view . $path = 'assets' . DS . 'AdminLTE' . DS . 'UI' . DS . $AdminLTE . '.php')
                    )) {
                    $path = 'assets' . DS . 'AdminLTE' . DS . 'widgets.php';
                }

                $this->wrap()($path);    // still relative to CarbonPHP::$app_root

            })()) {
            return true;
        }

        if ($version = ($this->uriExplode[0] ?? false)) {
            switch ($version) {
                case '2.0':
                    self::$matched = true;
                    $json['VersionTWO'] = true;

                    $page = $this->uriExplode[1] ?? false;
                    $page or (View::$forceWrapper = true and View::$wrapper = CarbonPHP::$app_root . CarbonPHP::$app_view . 'assets/AdminLTE/wrapper.hbs');

                    if ($page && array_key_exists($page, $this->version2Dot0)) {
                        $this->wrap()($this->version2Dot0[$page]);
                    } else {
                        $this->wrap()($this->version2Dot0['Home']);
                    }
                    return true;
                case '6.0':
                    $this->fullPage()('60/index.html');
                    return true;
                default:
                    break;
            }
        }

        if ($this->regexMatch('#carbon/authenticated#', static function () {
            global $json;

            header('Content-Type: application/json', true, 200); // Send as JSON

            #PublicAlert::JsonAlert('Test', 'Danger', 'success', 'success');

            $json['success'] = !true;

            print json_encode($json, JSON_THROW_ON_ERROR, 512);

            return true;
        })()) {
            return true;
        }

        $this->structure($this->wrap());

        return false;
    }

    /**
     * @param string $id
     * @throws PublicAlert
     */
    public static function getUser(string $id = ''): void
    {
        global $users, $json;

        if (!is_array($users)) {
            $users = [];
        }

        if ($id === '') {
            $id = session_id();
        }

        $me = [];
        if (!Carbon_Users::Get($me, null, [
            REST::SELECT => [
                Carbon_Users::USER_ID,
                Carbon_Users::USER_USERNAME,
                Carbon_Users::USER_PASSWORD,
                Carbon_Users::USER_FIRST_NAME,
                Carbon_Users::USER_LAST_NAME
            ],
            REST::WHERE => [
                [
                    Carbon_Users::USER_USERNAME => $id,
                    Carbon_Users::USER_PASSWORD => $id
                ]
            ],
            REST::PAGINATION => [
                REST::LIMIT => 1
            ]
        ])) {
            PublicAlert::warning('Failed to find the user. Some demos may not function as expected.');
            return;
        }

        if (!empty($me)) {
            $_SESSION['id'] = $json['id'] = $me[Carbon_Users::COLUMNS[Carbon_Users::USER_ID]];
        } else if (is_string($id = Carbon_Users::Post([         // required fields :P
                Carbon_Users::USER_USERNAME => $id,
                Carbon_Users::USER_PASSWORD => $id,
                Carbon_Users::USER_FIRST_NAME => 'Guest',
                Carbon_Users::USER_LAST_NAME => 'Account',
                Carbon_Users::USER_GENDER => 'N/A',
                Carbon_Users::USER_EMAIL => 'N/A',
                Carbon_Users::USER_IP => CarbonPHP::$server_ip
            ])) && Database::commit()) {
            PublicAlert::info('A new session has been established with the id ' . $id);
        } else {
            PublicAlert::warning('Failed to create you a new user. Some demos may not function as expected.');
        }
    }

    public static function configuration(): array
    {
        if (CarbonPHP::$app_root === '/home/runner/work/carbonphp/carbonphp') {
            CarbonPHP::$test = true;
            $databasePassword = 'password';
        } else if (CarbonPHP::$app_local) {
            $databasePassword = 'password';
        } else {
            $databasePassword = 'goldteamrules';
        }

        return [
            CarbonPHP::REST => [
                CarbonPHP::NAMESPACE => Carbons::CLASS_NAMESPACE,
                CarbonPHP::TABLE_PREFIX => 'carbon_' // Carbons::TABLE_PREFIX
            ],
            CarbonPHP::DATABASE => [
                CarbonPHP::DB_HOST => CarbonPHP::$app_local ? '127.0.0.1' : '35.224.229.250',                        // IP
                CarbonPHP::DB_PORT => '3306',
                CarbonPHP::DB_NAME => 'CarbonPHP',                       // Schema
                CarbonPHP::DB_USER => 'root',                            // User
                CarbonPHP::DB_PASS => $databasePassword,                          // Password
                CarbonPHP::REBUILD => false
            ],
            CarbonPHP::SITE => [
                CarbonPHP::URL => CarbonPHP::$app_local ? 'dev.carbonphp.com' : 'carbonphp.com',    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
                CarbonPHP::ROOT => CarbonPHP::$app_root,          /* This was defined in our ../index.php */
                CarbonPHP::CACHE_CONTROL => [
                    'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                    'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
                    'html|htm|hbs|js' => 'Cache-Control: max-age=0, private, public',   // It is not recommended to add php as an extension as explicitly hitting the .php would output its contents without compilation.
                    // This can be a valid use, but for 99% of users it will seem like a bug with apache.
                ],
                CarbonPHP::CONFIG => __FILE__,               // Send to sockets
                CarbonPHP::TIMEZONE => 'America/Phoenix',    //  Current timezone
                CarbonPHP::TITLE => 'CarbonPHP • C6',        // Website title
                CarbonPHP::VERSION => trim(`git tag | tail -n 1`),               // Add link to semantic versioning
                CarbonPHP::SEND_EMAIL => 'richard@miles.systems',
                CarbonPHP::REPLY_EMAIL => 'richard@miles.systems',
                CarbonPHP::HTTP => CarbonPHP::$app_local
            ],
            CarbonPHP::SESSION => [
                CarbonPHP::REMOTE => true,             // Store the session in the SQL database
                # CarbonPHP::SERIALIZE => [ ],           // These global variables will be stored between session
                # CarbonPHP::PATH => ''
                CarbonPHP::CALLBACK => static function () {         // optional variable $reset which would be true if a url is passed to startApplication()
                    // optional variable $reset which would be true if a url is passed to startApplication()
                    // This is a special case used for documentation and should not be used in prod. See repo stats.coach for example
                    /*if ($_SESSION['id'] ??= false) {
                        self::getUser(session_id());       // todo - opted for the run in self::defaultRoute &| self::startApplication
                    }*/
                },
            ],
            CarbonPHP::SOCKET => [
                CarbonPHP::WEBSOCKETD => false,
                CarbonPHP::PORT => 8888,
                CarbonPHP::DEV => true,
                CarbonPHP::SSL => [
                    CarbonPHP::KEY => '',
                    CarbonPHP::CERT => ''
                ]
            ],
            // ERRORS on point
            CarbonPHP::ERROR => [
                CarbonPHP::LOCATION => CarbonPHP::$app_root . 'logs' . DS,
                CarbonPHP::LEVEL => E_ALL | E_STRICT,  // php ini level
                CarbonPHP::STORE => false,      // Database if specified and / or File 'LOCATION' in your system
                CarbonPHP::SHOW => true,       // Show errors on browser
                CarbonPHP::FULL => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
            ],
            CarbonPHP::VIEW => [
                // TODO - THIS IS USED AS A URL AND DIRECTORY PATH. THIS IS BAD. WE NEED DS
                CarbonPHP::VIEW => 'view/',  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
                CarbonPHP::WRAPPER => '20/Wrapper.hbs',     // View::content() will produce this
            ],
            CarbonPHP::MINIFY => [
                CarbonPHP::CSS => [
                    CarbonPHP::OUT => CarbonPHP::$app_root . 'view/assets/css/style.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/AdminLTE.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/iCheck/all.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/bootstrap-slider/slider.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/skins/skin-green.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/select2/dist/css/select2.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/iCheck/flat/blue.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/morris.js/morris.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/pace/pace.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jvectormap/jquery-jvectormap.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/timepicker/bootstrap-timepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
                ],
                CarbonPHP::JS => [
                    CarbonPHP::OUT => CarbonPHP::$app_root . 'view/assets/js/javascript.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.js',  // do not use slim version
                    CarbonPHP::$app_root . 'node_modules/jquery-pjax/jquery.pjax.js',
                    CarbonPHP::$app_root . 'node_modules/mustache/mustache.js',
                    CarbonPHP::CARBON_ROOT . 'helpers/Carbon.js',
                    CarbonPHP::CARBON_ROOT . 'helpers/asynchronous.js',
                    CarbonPHP::$app_root . 'node_modules/jquery-form/src/jquery.form.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/js/adminlte.js',
                ],
            ]
        ];
    }
}