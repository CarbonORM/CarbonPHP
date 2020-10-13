<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/25/18
 * Time: 3:29 AM
 */

namespace App;

use CarbonPHP\Application;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Helpers\Pipe;
use CarbonPHP\Session;
use CarbonPHP\View;
use Controller\User;


class StatsCoach extends Application
{
    /**
     * Bootstrap constructor. Places basic variables
     * in our json response that will be needed by many pages.
     * @param null $structure
     * @throws PublicAlert
     */
    public function __construct($structure = null)
    {
        global $json, $user;

        if (!is_array($json)) {
            $json = array();
        }

        $json['user'] = &$user;
        $json['SITE'] = SITE;
        $json['POST'] = $_POST;
        $json['HTTP'] = HTTP;
        $json['HTTPS'] = HTTPS;
        $json['SOCKET'] = SOCKET;
        $json['AJAX'] = AJAX;
        $json['PJAX'] = PJAX;
        $json['SITE_TITLE'] = SITE_TITLE;
        $json['SITE_VERSION'] = SITE_VERSION;
        $json['APP_VIEW'] = APP_VIEW;
        $json['APP_LOCAL'] = (bool)APP_LOCAL;     // mainly for ws vs wss
        $json['TEMPLATE'] = TEMPLATE;
        $json['COMPOSER'] = COMPOSER;
        $json['X_PJAX_Version'] = &$_SESSION['X_PJAX_Version'];
        $json['FACEBOOK_APP_ID'] = FACEBOOK_APP_ID;

        parent::__construct($structure);
    }

    /**
     * @return mixed
     * @throws PublicAlert
     */
    public function defaultRoute()
    {
        // Sockets will not execute this function
        View::$forceWrapper = true; // this will hard refresh the wrapper

        // Even if the user is not logged in we need to update template info
        $this->userSettings(); // template settings

        if (!$_SESSION['id']):
            return $this->MVC()('User', 'login');
        else:
            return $this->MVC()('Golf', 'golf');
        endif;
    }

    /**
     * @param null|string $uri
     * @return bool
     * @throws PublicAlert
     */
    public function startApplication(string $uri): bool
    {
        static $count;

        if (empty($count)) {
            $count = 0;
        } else {
            $count++;
        }

        $hole = '([0-9]{1,2})';
        $id = self::MATCH_C6_ENTITY_ID_REGEX;
        $state = '([a-z\s]{4,20})';

        if ('' !== $uri) {
            $this->changeURI($uri);
        } else if (empty($this->uri[0])) {
            if (SOCKET) {
                throw new PublicAlert('$_SERVER["REQUEST_URI"] MUST BE SET IN SOCKET REQUESTS');
            }
            $this->matched = true;
            return $this->defaultRoute();
        }

        $this->userSettings();          // Update the current user, must always be done here.

        $this->structure($this->MVC());

        if ($this->regexMatch('#Contact#i', 'Messages', 'Mail')()) {
            return true;
        }

        ################################### MVC
        if (!$_SESSION['id']) {  // Signed out
            if ($this->regexMatch('#Login.*#i', 'User', 'login')() ||
                $this->regexMatch('#oAuth/([a-zA-z]{0,10})/([a-zA-z]{0,10})#i', 'User', 'oAuth')() ||
                $this->regexMatch('#Register#i', 'User', 'Register')() ||           // Register
                $this->regexMatch('#Recover/([a-zA-Z@\.]){8,40}/([A-Za-z0-9]){4,40})/?#i', 'User', 'recover')()) {     // Recover $userId
                return true;
            }
        } else {
            // Event
            if ((AJAX && PJAX) || SOCKET) {

                // So in this we know we're looking for a json response regardless of the
                // if startApplication(true) is called again with === true passed in, the
                // force wrapper will be set to true

                global $json;

                $json['user-layout'] = 'Json Method Removed';   // TODO - this could break things if we start app and
                $json['body-layout'] = 'Json Method Removed';
                $json['header'] = 'Json Method Removed';

                // Example code for testing socket connections
                if (SOCKET && ($this->regexMatch('#whoami/?#', static function () {
                            print $_SESSION['id'] . PHP_EOL;
                        })() ||
                        $this->match('Send/{user_id}/{message}/', static function ($user_id, $message) {
                            print 'About to send' . PHP_EOL;
                            print 'Did we send? ' . Pipe::send($message, '/tmp/' . $user_id . '.fifo') . PHP_EOL . PHP_EOL;
                        })())) {
                    return true;
                }

                if ($this->regexMatch('#Search/([a-z\s\.]{1,40})#i', 'Search', 'all')() ||
                    $this->regexMatch('#NavigationMessages#i', 'Messages', 'navigation')() ||
                    $this->regexMatch("#Messages/$id#i", 'Messages', 'chat')() ||
                    $this->regexMatch("#Follow/$id#i", 'User', 'follow')() ||
                    $this->regexMatch("#Unfollow/$id#i", 'User', 'unfollow')() ||
                    $this->structure($this->JSON('#NavNotifications'))->regexMatch('#Notifications#i', 'notifications', 'notifications')() ||
                    $this->structure($this->JSON('#NavTasks'))->regexMatch('#tasks#i', 'tasks', 'tasks')() ||
                    $this->structure($this->JSON('.direct-chat'))->regexMatch("#Messages/$id#i", 'Messages', 'chat')()
                ) {
                    return true;         // Event
                }
                if (SOCKET) {
                    return false;
                }
            }


            ################################### Lessons
            $this->structure($this->wrap());

            if ($this->regexMatch('#drills/putting#i', 'golf/putting.hbs')() ||
                $this->regexMatch('#drills/approach#i', 'golf/approach.hbs')() ||
                $this->regexMatch('#drills/accuracy#i', 'golf/accuracy.hbs')() ||
                $this->regexMatch('#drills/distance#i', 'golf/distance.hbs')()
            ) {
                return true;
            }


            ################################### MVC
            $this->structure($this->MVC());


            ################################### Golf Stuff + User

            if ($this->regexMatch("#CoursesByState/$state#i", 'Golf', 'CoursesByState')()) {
                return true;
            }


            if ($this->regexMatch("#PostScore/Basic/?$state?#i", 'Golf', 'PostScoreBasic')() ||
                $this->regexMatch("#PostScore/Color/$id#i", 'Golf', 'PostScoreColor')() ||
                $this->regexMatch("#PostScore/Distance/$id/([a-z]{3,10})#i", 'Golf', 'PostScoreDistance')()) { // id color
                return true;
            }


            if ($this->regexMatch("#AddCourse/Basic/$state#i", 'Golf', 'AddCourseBasic')() ||
                $this->regexMatch("#AddCourse/Color/$id/$hole#i", 'Golf', 'AddCourseColor')() ||
                $this->regexMatch("#AddCourse/Distance/$id/$hole#i", 'Golf', 'AddCourseDistance')()) {
                return true;
            }

            // TODO - flesh out tournament
            if ($this->regexMatch('#NewTournament#i', 'Golf', 'NewTournament')() ||
                $this->regexMatch("#TournamentSettings/$id#i", 'Golf', 'TournamentSettings')() ||
                $this->regexMatch("#Tournament/$id#i", 'Golf', 'Tournament')()) {
                return true;
            }

            if ($this->regexMatch("#profile/$id#i", 'User', 'profile')() ||   // Profile $user
                $this->regexMatch('#messages#i', 'Messages', 'messages')() ||
                $this->regexMatch('#followers#i', 'User', 'listFollowers')() ||
                $this->regexMatch('#following#i', 'User', 'listFollowing')() ||
                $this->regexMatch('#home#i', 'Golf', 'golf')() ||
                $this->regexMatch('#golf#i', 'Golf', 'golf')() ||
                $this->regexMatch("#Team/$id#i", 'Team', 'team')() ||
                $this->regexMatch("#Rounds/$id/#i", 'Golf', 'rounds')() ||
                $this->regexMatch('#JoinTeam#i', 'Team', 'joinTeam')() ||
                $this->regexMatch('#CreateTeam#i', 'Team', 'createTeam')() ||
                $this->regexMatch('#Logout#i', static function () {
                    User::logout();
                })()) {
                return true;          // Logout
            }
        }

        return
            $this->structure($this->MVC())->match('Activate/{email?}/{email_code?}/', 'User', 'activate')() ||  // Activate $email $email_code
            $this->structure($this->wrap())->regexMatch('#Privacy#i', 'policy/privacy.hbs')() ||
            $this->match('404/*', 'error/404error.hbs')() ||
            $this->match('500/*', 'error/500error.hbs')();


    }


    /**
     * App constructor. If no uri is set than
     * the Route constructor will execute the
     * defaultRoute method defined below.
     * @return void
     * @throws PublicAlert
     */

    public function userSettings(): void
    {
        global $user, $json;

        $id = &$_SESSION['id'];

        // If the user is signed in we need to get the

        if ($id ?? false) {

            #sortDump(['damn ok', $id]);


            if (!\is_array($user[$id] ?? false)) {
                Session::update();
            }

            $json['my'] = &$user[$id];

            $json['signedIn'] = true;

            $json['nav-bar'] = '';

            $json['user-layout'] = 'class="wrapper" style="background: rgba(0, 0, 0, 0.7)"';

            $mustache = static function ($path) {      // This is our mustache template engine implemented in php, used for rendering user content
                global $json;
                static $mustache;
                if (empty($mustache)) {
                    $mustache = new \Mustache_Engine();
                }
                if (!file_exists($path)) {
                    print "<script>Carbon(() => carbon.alert('Mustache Content Buffer Failed ($path), Does Not Exist!', 'danger'))</script>";
                }
                return $mustache->render(file_get_contents($path), $json);
            };

            switch ($user[$id]['user_type'] ?? false) {
                case 'Athlete':
                    $json['body-layout'] = 'hold-transition skin-blue layout-top-nav';
                    $json['header'] = $mustache(APP_ROOT . APP_VIEW . 'layout/AthleteLayout.hbs');
                    break;
                case 'Coach':
                    $json['body-layout'] = 'skin-green fixed sidebar-mini sidebar-collapse';
                    $json['header'] = $mustache( APP_ROOT . APP_VIEW . 'layout/CoachLayout.hbs');
                    break;
                default:
                    throw new PublicAlert('No user type found!!!!');
            }
        } else {
            $json['body-layout'] = 'stats-wrap';
            $json['user-layout'] = 'class="container" id="pjax-content"';
        }
    }

}