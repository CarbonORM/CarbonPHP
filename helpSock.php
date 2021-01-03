#!/usr/bin/php
<?php declare(ticks=1);


define('DEBUG', false);



colorCode('here');

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
# https://www.leaseweb.com/labs/2013/08/catching-signals-in-php-scripts/
pcntl_signal(SIGTERM, 'signalHandler'); // Termination ('kill' was called')
pcntl_signal(SIGHUP, 'signalHandler'); // Terminal log-out
pcntl_signal(SIGINT, 'signalHandler'); // Interrupted ( Ctrl-C is pressed)

if (isset($_SERVER['REQUEST_URI']) && preg_match('/^\/([^\/]+)\/(.*)/', $_SERVER['REQUEST_URI'], $m)) {
    define('STREAMID', $m[1]);
    define('TICKETSALEID', $m[2]);
} else {
    exit(1);
    define('STREAMID', 'gig47');
    define('TICKETSALEID', 'loggedout');
}



colorCode('there ');


// TODO : Make sure the UserID is authorized to send setup options

function colorCode(string $message, string $color = 'green', bool $exit = false, int $priority = LOG_INFO): void
{

    $colors = array(
        // styles
        // italic and blink may not work depending of your terminal
        'bold' => "\033[1m%s\033[0m",
        'dark' => "\033[2m%s\033[0m",
        'italic' => "\033[3m%s\033[0m",
        'underline' => "\033[4m%s\033[0m",
        'blink' => "\033[5m%s\033[0m",
        'reverse' => "\033[7m%s\033[0m",
        'concealed' => "\033[8m%s\033[0m",
        // foreground colors
        'black' => "\033[30m%s\033[0m",
        'red' => "\033[31m%s\033[0m",
        'green' => "\033[32m%s\033[0m",
        'yellow' => "\033[33m%s\033[0m",
        'blue' => "\033[34m%s\033[0m",
        'magenta' => "\033[35m%s\033[0m",
        'cyan' => "\033[36m%s\033[0m",
        'white' => "\033[37m%s\033[0m",
        // background colors
        'background_black' => "\033[40m%s\033[0m",
        'background_red' => "\033[41m%s\033[0m",
        'background_green' => "\033[42m%s\033[0m",
        'background_yellow' => "\033[43m%s\033[0m",
        'background_blue' => "\033[44m%s\033[0m",
        'background_magenta' => "\033[45m%s\033[0m",
        'background_cyan' => "\033[46m%s\033[0m",
        'background_white' => "\033[47m%s\033[0m",
    );

    if (!array_key_exists($color, $colors)) {
        $color = 'red';
        colorCode("Color provided to color code ($color) is invalid, message caught '$message'", 'red');
    }

    $colorCodex = sprintf($colors[$color], $message);

    /** @noinspection ForgottenDebugOutputInspection */
    error_log($colorCodex);    // do not double quote args passed here

    if ($exit) {
        exit($message);
    }
}

//print "<b>PROCESS : ".$argv[1]."\n";
$wspath = "/tmp/websockets";

if (!is_dir($wspath) && !mkdir($wspath) && !is_dir($wspath)) {
// An error occured. Disconnect.
    print "Could not create $wspath\n";
    exit();
}


colorCode('none');



const STREAMID = 1;
const TICKETSALEID = 1;

if (!is_dir($wspath . "/" . STREAMID) && !mkdir($wspath . "/" . STREAMID) && !is_dir($wspath . "/" . STREAMID)) {
// An error occured. Disconnect.
    print "Could not create $wspath/" . STREAMID . "\n";
    exit();
}

$fifoPath = $wspath . "/" . STREAMID . '/' . TICKETSALEID . '.fifo';

//print "FIFO Path: ".$fifoPath."\n\n";
wslog("StreamID : " . STREAMID);
wslog("TicketSaleID : " . TICKETSALEID);
wslog("FIFO Path: $fifoPath");

function named($fifoPath)  // Arbitrary)
{

    if (file_exists($fifoPath)) {
        unlink($fifoPath);          // We are always the master, hopefully we'll catch the kill this time
    }

    umask(0000);

    if (!posix_mkfifo($fifoPath, 0666)) {
        print 'Failed to create named Pipe' . PHP_EOL;
        return false;
    }                   # create a named pipe 0644

    // future self, please read the function doc above before wondering why this is commented out

    #$user = get_current_user();                            // get current process user

    #exec("chown -R {$user} $fifoPath");                    // We need to modify the permissions so users can write to it

    $fifoFile = fopen($fifoPath, 'rb+');              // Now we open the named pipe we Already created

    if (false === $fifoFile) {
        return false;
    }

    stream_set_blocking($fifoFile, false);           // setting to true (resource heavy) activates the handshake feature, aka timeout

    return $fifoFile;                                       // File descriptor
}

$fifoFile = named($fifoPath);
$stdin = STDIN;
$writers = [];
$except = [];


//echo "COMMUNICATION STARTED\n PID :: " . getmypid() . "\n\n";
if (DEBUG) {
    print json_encode(['status' => 'connected', 'pid' => getmypid()]) . "\n";
} else {
    print json_encode(['status' => 'connected']) . "\n";
}





while (true) {
    //sleep( 1 );
    usleep(500000);
    if (!$stdin) {
        print json_encode(['status' => 'error', 'error' => 'A stream error occurred']);
        signalHandler('lost stdin');
    } else {
        $readers = array($fifoFile, $stdin);
        if (($stream = stream_select($readers, $writers, $except, 0, 15)) === false) {
            print json_encode(['status' => 'error', 'error' => 'A stream error occurred']);
            signalHandler('stream error');
        } else {
            foreach ($readers as $input => $fd) {
                if ($fd === $stdin) {
                    $line = trim(fgets($stdin));

                    // print "You sent :: $line \n";
                    $deets = json_decode($line);
                    if (isset($deets->to)) {
                        $destinations = [];
                        $deets->ts = time();
                        if ($deets->to === 'all') {
                            // Find all the fifo files in the folder for the stream and add them
                            $rcpts = scandir($wspath . "/" . STREAMID);

                            $destinations = [];
                            foreach ($rcpts as $r) {
                                if (preg_match('/\.fifo$/', $r) && $r != TICKETSALEID . ".fifo") {
                                    $destinations[] = substr($r, 0, -5);
                                }
                            }
                        } else if (is_array($deets->to)) {
                            $destinations = $deets->to;
                        } else $destinations = [$deets->to];
                        foreach ($destinations as $d) {
                            $toFifo = $wspath . '/' . STREAMID . '/' . $d . '.fifo';
                            if (file_exists($toFifo)) {
                                $tfifo = fopen($toFifo, 'w');
                                fwrite($tfifo, json_encode($deets), 1024);
                                fclose($tfifo);
                            }
                        }
                    }
                } else if ($fd == $fifoFile) {
                    $data = fread($fifoFile, $bytes = 1024);
                    if (!empty($data)) print trim($data) . "\n";
                }
            }
        }
    }
}
signalHandler("normal exit");

function signalHandler($signal)
{
    print "Signal :: $signal\n";
    global $fifoPath, $stdin;
    @fclose($stdin);
    @unlink($fifoPath);
// print "Safe Exit \n\n";
    exit(1);
}

function wslog($m)
{
    if (!DEBUG) return false;
    global $logFile;
    if (!isset($logFile)) {
        $pid = getmypid();
        $logFile = fopen('/tmp/websockets/ws-' . $pid . '.log', 'a');
    }

    $t = microtime(true);
    $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
    $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));

    $date = $d->format("Y-m-d H:i:s.u"); // note at point on "u"
    fwrite($logFile, $date . " : " . $m . "\n");

}