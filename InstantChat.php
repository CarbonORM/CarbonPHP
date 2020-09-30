<?php

/**
 * Run with the following two command run on separate shells
 *   php -S 127.0.0.1:80 instantChat.php
 *   php instantChat.php websocket
 */




use CarbonPHP\Application;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iConfig;
use CarbonPHP\Programs\WebSocket;

const DS = DIRECTORY_SEPARATOR;

CarbonPHP::$app_root = __DIR__ . DS;

// Composer autoload
if (false === (include 'vendor' . DS . 'autoload.php')) {     // Load the autoload() for composer dependencies located in the Services folder
    print '<h1>Composer Failed</h1>';
    die(1);
}

// I would typically put this is another file, but this is still valid and make the example flow nicely
class InstantChat extends Application implements iConfig {

    /**
     * @param string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        if (CarbonPHP::$socket && $this->regexMatch('#echo/([a-z0-9]+)#i',
                static function ($echo) use ($uri) {
                    WebSocket::sendToAllExternalResources("Echo Server On URI ($uri) :: \$echo = $echo");
                })()) {
            return true;
        }

        if ($this->regexMatch('#.*#', static function () {  // this will match any route.
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

        return true;
    }

    public function defaultRoute()
    {
        // TODO: Implement defaultRoute() method.
    }

    public static function configuration(): array
    {
        // TODO: Implement configuration() method.
        return [];
    }

    public static function view(){
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
    }


}

(new CarbonPHP(InstantChat::class))();

return true;

