<?php

/* @author Richard Tyler Miles
 *
 *      Special thanks to the following people//resources
 *
 * @link https://gist.github.com/pbojinov/8965299
 */


/**
 *  Use Curl to proxy requests so the browser thinks we own the content in the iFrame
 *
 *
 * You must have the curl extension enabled in php
 * @link http://www.tomjepson.co.uk/enabling-curl-in-php-php-ini-wamp-xamp-ubuntu/
 * @param $ext
 * @return bool|mixed
 */

function mimeType($ext)
{
    $mime_types = include 'extras/mimeTypes.php';

    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    }
    return false;

}

function get_page($url)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    if (false === $data) {
        print "\n\n\tCurl error: \t" . curl_error($ch);
        die;
    }

    curl_close($ch);

    if (preg_match('#^(.*)\.(css|js)#', $url, $matches, PREG_OFFSET_CAPTURE)) {

        if ($mime = mimeType($matches[2][0])) {
            header('Content-type:' . $mime . '; charset: UTF-8');
        }
    }

    return $data;
}

$uri = trim(urldecode(parse_url(trim(preg_replace('/\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)), '/');

switch ($uri) {
    case 'inject.js':
        // TODO - send correct headers
        print /** @lang JavaScript */
            <<<JAVASCRIPT
window.onbeforeunload = () => {
    return 'random text that does nothing in chrome... TODO ';
}


// Parent frame communication
// addEventListener support for IE8
function bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener) {
        element.addEventListener(eventName, eventHandler, false);
    } else { // noinspection JSUnresolvedVariable
        if (element.attachEvent) {
            element.attachEvent('on' + eventName, eventHandler);
        }
    }
}

// Send a message to the parent
let sendMessage = function (msg) {
    // Make sure you are sending a string, and to stringify JSON
    window.parent.postMessage(msg, '*');
};

// Listen to messages from parent window
bindEvent(window, 'message', function (e) {
    alert(e.data);
    e.stopPropagation();
    e.preventDefault();
});


$(() => {
    let all = $("*");
    // You can't shorthand this ()=>{ ...
    all.click(function (e) {
        console.log(e);
        let link = $(this).closest("a");
        console.log(link)
        if (link.attr("href")) {
            alert(link.attr("href"))
        }
        e.stopPropagation();
        e.preventDefault();

        let random = Math.random();
        sendMessage('' + random);
    });
    all.mouseover(function (e) {
        $(this).css("background-color", "teal");
        $(this).css("opacity", ".8");
        e.stopPropagation();
    }).mouseout(function (e) {
        $(this).css("background-color", "transparent");
        $(this).css("opacity", "1");
        e.stopPropagation();
    });
});
JAVASCRIPT;

        exit(0);

    case 'index.php':

        header("Access-Control-Allow-Origin: *");

        $domain = $_GET['url'] ?? 'http://www.carbonphp.com';

        print get_page($domain);

        $domainFile = fopen("domain.txt", "w");

        if (false === $domainFile) {
            die("Unable to open domain.txt file!");
        }

        if (false === fwrite($domainFile, $domain)) {
            die('Failed to write to domain file');
        }

        fclose($domainFile);

        exit(0);
    default:
        if (empty($uri)) {
            break;
        }

        $domainFile = fopen("domain.txt", "r");

        $urlFile = fopen("urls.txt", "a+");

        if (false === $domainFile) {
            die("Unable to open urls.txt file!");
        }

        if (false === $urlFile) {
            die("Unable to open urls.txt file!");
        }

        $domain = ltrim(trim(fread($domainFile, filesize('domain.txt')), " /\t\n\r\0\v"), "\n");

        if (false === fwrite($urlFile, PHP_EOL . $url = $domain . '/' . $uri)) {
            die('Failed to write to file!');
        }

        if (false === fclose($domainFile)) {
            die('Failed to close the file!');
        }

        if (false === fclose($urlFile)) {
            die('Failed to close the file!');
        }

        print get_page($url);
        exit(0);
}


?>

<!DOCTYPE html>
<html lang="utf-8">
<head>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <style>
        #iframe {
            width: 100%;
            height: 100%;
            min-height: 700px;
            border-color: black;
            display: none;
        }

        #loading {
            color: darkred;
        }

        #toolWindow {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0, 0, 0); /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
        }

        /* Modal Content/Box */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        /* The Close Button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        h1, h3, h4, h5 {
            color: white;
        }

        h5 {
            width: 40%;
            margin: 0;
            display: inline-grid;
        }

        input {
            width: 30%;
        }

        button {
            width: 20%;
            margin: 0;
        }

        body {
            background-color: black;
            opacity: .8;
        }
    </style>
    <title>The Test Automator</title>
</head>
<body>
<h1>Test Automatorix <b id="loading">Loading</b></h1>
<!-- What did it just say? lol -->
<h5>All relative urls will be proxied back to the intended host</h5>
<label>
    <input value="www.carbonphp.com">
</label>
<button id="fetch">GO</button>
<iframe id="iframe" src=""></iframe>

<!-- Modal content -->
<div id="toolWindow">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Parent Popup Window</p>
        <p>Send Message:
            <button id="message_button">Hi there iframe</button>
        </p>
        <p>Got Message:</p>
        <div id="results"></div>
        <br/>
    </div>
</div>

<!--suppress JSUnresolvedVariable -->
<script>
    function getURL(url) {
        document.getElementById('iframe').src = '/index.php?url=' + encodeURIComponent(url) + '/';
    }

    function injectScript(url) {
        let appendScript = document.createElement('script');
        appendScript.type = 'text/javascript';
        appendScript.src = url;
        frames[0].document.getElementsByTagName("head")[0].appendChild(appendScript);
    }

    $(() => {
        let $frame = $('iframe'),
            $load = $('#loading');

        document.getElementById('iframe').onload = () => {
            $load.hide();
            injectScript('https://code.jquery.com/jquery-3.2.1.min.js');
            injectScript('inject.js');
        };

        $('#fetch').on('click', () => {
            $load.show();
            $frame.show();
            getURL($('input').val());
        });
        $load.hide();
        $frame.hide();
    });

    // addEventListener support for IE8
    function bindEvent(element, eventName, eventHandler) {
        if (element.addEventListener) {
            element.addEventListener(eventName, eventHandler, false);
        } else if (element.attachEvent) {
            element.attachEvent('on' + eventName, eventHandler);
        }
    }

    // Send a message to the child iframe
    let $toolWindow = $('#toolWindow'),
        iframeEl = document.getElementById('iframe'),
        messageButton = document.getElementById('message_button'),
        results = document.getElementById('results');

    // Send a message to the child iframe
    let sendMessage = function (msg) {
        // Make sure you are sending a string, and to stringify JSON
        iframeEl.contentWindow.postMessage(msg, '*');
    };

    // Send random message data on every button click
    bindEvent(messageButton, 'click', function (e) {
        let random = Math.random();
        sendMessage('' + random);
    });

    // Listen to message from child window
    bindEvent(window, 'message', function (e) {
        $toolWindow.show();
        results.innerHTML = e.data;
    });

    $('.close').on('click', () => {
        $toolWindow.hide();
    })
</script>
</body>
</html>





