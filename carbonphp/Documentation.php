<?php

namespace CarbonPHP;


use CarbonPHP\Abstracts\Application;
use CarbonPHP\Abstracts\Rest;
use CarbonPHP\Classes\Exceptions\PrivateAlert;
use CarbonPHP\Classes\Exceptions\PublicAlert;
use CarbonPHP\Classes\Programs\CLI;
use CarbonPHP\Classes\Programs\Deployment;
use CarbonPHP\Classes\Programs\Migrate;
use CarbonPHP\Classes\Programs\WebSocket;
use CarbonPHP\Interfaces;
use CarbonPHP\Interfaces\iConfig;

class Documentation extends Application implements iConfig
{

    public const GIT_SUPPORT = 'https://github.com/CarbonORM/CarbonPHP/issues';
    /**
     * Sockets will not execute this
     * @return mixed|void
     * @throws PublicAlert
     */
    public function defaultRoute(): void
    {

        self::getUser();

        if (CarbonPHP::$app_local
            && 'GET' === $_SERVER['REQUEST_METHOD']) {

            throw new PrivateAlert('You should run the live version on <a id="staticSite" href="http://local.carbonphp.com:3000/" style="color:#ff0084">port 3000</a> with the command<br/><b>>> npm start </b>.');

        }

        print self::inlineReact();

    }

    public static function inlineReact(): string
    {

        /** @noinspection JSUnresolvedVariable */
        return <<<HTML
                <div id="root" style="height: 100%;">
                </div>
                <script>
                    fetch('https://carbonorm.dev/asset-manifest.json')
                        .then(response => response.json())
                        .then(data => {
                            const entryPoints = data?.entrypoints || [];
                            entryPoints.forEach(value => {
                                if (value.endsWith('.js')) {
                                    // Load JavaScript files dynamically
                                    const script = document.createElement('script');
                                    script.src = manifestURI + value;
                                    document.head.appendChild(script);
                                } else {
                                    // Load stylesheets dynamically
                                    const link = document.createElement('link');
                                    link.rel = 'stylesheet';
                                    link.type = 'text/css';
                                    link.href = manifestURI + value;
                                    document.head.appendChild(link);
                                }
                            });
                
                        });
                </script>
                HTML;
    }


    /**
     * todo - document well what can go into constructor and when.
     * way this will throw an error is if you do
     * not define a url using sockets.
     */
    public function __construct()
    {

        if (CarbonPHP::$safelyExit) {
            return;
        }

        global $json;

        if (!is_array($json)) {

            $json = array();

        }

        $json += [
            'SITE' => CarbonPHP::$site,
            'POST' => $_POST,
            'GET' => $_GET,
            'HTTP' => CarbonPHP::$http,
            'HTTPS' => CarbonPHP::$https,
            'SOCKET' => CarbonPHP::$socket,
            'AJAX' => CarbonPHP::$ajax,
            'SITE_TITLE' => CarbonPHP::$site_title,
            'CarbonPHP::$app_view' => CarbonPHP::$app_view,
            'COMPOSER' => CarbonPHP::CARBON_ROOT,
            'FACEBOOK_APP_ID' => ''
        ];

        parent::__construct();
    }


    /**
     * this should always be public and not static
     *
     * @param string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): void
    {
        global $json;

        $json['APP_LOCAL'] = CarbonPHP::$app_local;

        if (Deployment::github()
            || Migrate::enablePull([CarbonPHP::VIEW])) {

            return;

        }

        self::getUser();

        if (CarbonPHP::$socket
            && self::regexMatch('#echo/([a-z0-9]+)#i',
                static function ($echo) use ($uri) {
                    WebSocket::sendToAllExternalResources("Echo Server On URI ($uri) :: \$echo = $echo");
                })) {

            return;

        }

        if (Rest::MatchRestfulRequests('', Carbons::CLASS_NAMESPACE)) {
            return;
        }

        if (self::regexMatch('#inlineReact#',
            static fn() => self::inlineReact())) {

            return;

        }

        if (self::regexMatch('#(!?ws|wss)#i',
            static function () {

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
    let wsUri = "ws://local.carbonphp.com:8888/ws";
    let websocket = new WebSocket(wsUri);
    
    

    websocket.onopen = function (ev) { // connection is open
      $('#message_box').append("<div class=\"system_msg\"><p class=\"innerText\">Connected!</p></div>"); //notify user
    };
    
    const onClickCallback = function () { //use clicks message send button
       
      let myMessage = $('#message').val();  //get message text
        
      if (myMessage === "") { 
        //empty message?
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
      }, 2.0.000);
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

            })) {

            return;

        }

        Rest::MatchRestfulRequests('', Carbons::CLASS_NAMESPACE);
    }


    public static function configuration(): array
    {
        if (CarbonPHP::$app_root === '/home/runner/work/carbonphp/carbonphp') {

            CarbonPHP::$test = true;

            $databasePassword = 'password';

        } else if (CarbonPHP::$app_root === '/var/www/carbonphp.com/') {

            CarbonPHP::$is_running_production = true;

            $databasePassword = 'goldteamrules';

        } else {

            $databasePassword = 'password';

        }

        return [
            CarbonPHP::REST => [
                //CarbonPHP::NAMESPACE => Carbons::CLASS_NAMESPACE,
                //CarbonPHP::TABLE_PREFIX => Carbons::TABLE_PREFIX
            ],
            CarbonPHP::DATABASE => [
                CarbonPHP::DB_HOST => CarbonPHP::$is_running_production ? '35.224.229.250' : '127.0.0.1',
                CarbonPHP::DB_PORT => '3306',
                CarbonPHP::DB_NAME => 'CarbonPHP',                       // Schema
                CarbonPHP::DB_USER => 'root',                            // User
                CarbonPHP::DB_PASS => $databasePassword,                          // Password
                CarbonPHP::REBUILD => false
            ],
            CarbonPHP::SITE => [
                CarbonPHP::PROGRAM_DIRECTORIES => [
                    CLI::class
                ],
                CarbonPHP::URL => CarbonPHP::$app_local ? 'local.carbonphp.com' : 'carbonphp.com',    /* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */
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
                CarbonPHP::HTTP => true, //CarbonPHP::$app_local
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
                CarbonPHP::LOCATION => CarbonPHP::$app_root . 'logs' . DIRECTORY_SEPARATOR,
                CarbonPHP::LEVEL => E_ALL | E_STRICT,  // php ini level
                CarbonPHP::STORE => false,      // Database if specified and / or File 'LOCATION' in your system
                CarbonPHP::SHOW => true,       // Show errors on browser
                CarbonPHP::FULL => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
            ],
            CarbonPHP::VIEW => [
                // TODO - THIS IS USED AS A URL AND DIRECTORY PATH. THIS IS BAD. WE NEED DS
                CarbonPHP::VIEW => 'view/',  // This is where the MVC() function will map the HTML.PHP and HTML.HBS . See Carbonphp.com/mvc
                CarbonPHP::WRAPPER => '2.0.0/Wrapper.hbs',     // View::content() will produce this
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