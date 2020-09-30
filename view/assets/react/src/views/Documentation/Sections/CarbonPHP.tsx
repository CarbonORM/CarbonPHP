import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
import {
  AccountTree,
  AllInclusive,
  Announcement,
  Dashboard,
  Exposure,
  Looks3,
  Looks4,
  Looks5,
  Looks6,
  LooksOne,
  LooksTwo,
  Power,
  RecentActors,
  Restaurant,
  RestorePage,
  Storage,
  Timeline,
  ViewComfy
} from "@material-ui/icons";
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import NavPills from "components/NavPills/NavPills";
import dashboardStyle from "assets/jss/material-dashboard-react/views/dashboardStyle";
// wip
import raw from "raw.macro";
import {WithStyles} from "@material-ui/styles";
import {AxiosInstance} from "axios";
// pages
import SequenceDiagram from "assets/img/SD.png";
import FileStructure from "./FileStructure";


const HelloWorld = raw("../../../assets/examples/HelloWorld.php");
const RoutingEx1 = raw("../../../assets/examples/RoutingEx1.php");
const iConfig = raw("../../../assets/examples/iConfig.php");
const iConfigPHPDOC = raw("../../../assets/examples/iConfigPHPDOC.php");
const InstantChat = raw("../../../assets/examples/InstantChat.php");
const RegexMatch = raw("../../../assets/examples/RegexMatch.php");
const CarbonPHPConfig = raw("../../../assets/examples/CarbonPHPConfig.php");
const StatsCoach = raw("../../../assets/examples/StatsCoach.php");
const CacheControl = raw("../../../assets/examples/CacheControl.php");
const Minification = raw("../../../assets/examples/Minification.php");

interface iCarbonPHP extends WithStyles<typeof dashboardStyle> {
  axios: AxiosInstance;
  testRestfulPostResponse: Function;
  codeBlock: (markdown: String, highlight ?: String, language ?: String, dark ?: boolean) => any;
}


class CarbonPHP extends React.Component<iCarbonPHP, any> {
  render() {
    const { classes, codeBlock } = this.props;
    return (
      <GridContainer justify="center">
        <GridItem xs={12} sm={12} md={8}>
          <div className={classes.textCenter}>
            <h2><b>CarbonPHP is [C6]</b></h2>
            <h4>
              This documentation is for C6, a PHP &gt;=7.4 application tool kit & framework.
            </h4>
          </div>
        </GridItem>
        <GridItem xs={12} sm={12} md={12}>
          <NavPills
            color="info"
            horizontal={{
              tabsGrid: { xs: 12, sm: 2, md: 2 },
              contentGrid: { xs: 12, sm: 10, md: 10 }
            }}
            tabs={[
              {
                tabIcon: Dashboard,
                tabButton: "Introduction",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                      <b>Version 6.1 of C6</b> is <b>production ready</b>.
                    </h3>
                    The PHP language is written and compiled from C code. Most Operating
                    systems now a day come pre-packaged with PHP. During my undergrad, while
                    learning the foundations of C and C++, I started to recognise the
                    parallels between the programming languages. Each day after class I
                    would work on those parallels php the C level and lower theory in mind.
                    With a web application in
                    mind and
                    the desire to learn I created a php framework with personal
                    implementations of the best
                    features from each leading framework. Four years later C6 has a little
                    over 100 custom coded files
                    full of features. Many are completely unique to CarbonPHP designed to
                    give you the best
                    development experience possible.
                    <br/><br/>
                    C6 was developed around the latest version of php, which gives it
                    significant advantage over other PHP frameworks. The framework
                    complements and uses the PHP internals rather than over obfuscating.
                    A good example of this is the super global $_SESSION.
                    CarbonPHP uses builtin php function to override
                    the standard file save method to use a server based session solution.
                    This grater increases the chance of developers finding relevant examples
                    online. It also improves the odds that PHP.net will be a reliable
                    resource for code in CarbonPHP's context.
                    <br/><br/>
                    Features can be modified or replaced to suit your development.
                    Here are some modules for quick reference.
                    <br/><br/>

                    <br/>
                    <h2>The Skinny</h2>
                    <h4>/index.php</h4>
                    <p>In this minimal index.php example the <b>Config\Config::class</b> extends the
                      class <b>CarbonPHP\Application </b>
                      and implements the interface <b>CarbonPHP\Interfaces\iConfig</b>. The Config\Config class will not
                      be instantiated
                      until CarbonPHP is invoked. For the N00B's this is the ending '();' on line 3. This means all C6
                      configuration will be available for the Config's constructor.
                    </p>
                    {codeBlock("const APP_ROOT = __DIR__ . DIRECTORY_SEPARATOR;\ninclude 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'\n(new CarbonPHP\\CarbonPHP( Config\\Config::class ))();", "", "php", true)}
                    <br/>
                    <p>In this example the <b>Config\Config::class</b> implements the
                      interface <b>CarbonPHP\Interfaces\iConfig</b>.
                      The <b>Application\Application::class</b> extends the abstract class <b>CarbonPHP\Application.</b>
                      The Config class will never be instanced in the below example and will only call the static
                      configuration method.</p>
                    {codeBlock("(new CarbonPHP\\CarbonPHP( Config\\Config::class ))( Application\\Application::class  );", "", "php", true)}
                    <br/>
                    <p>
                      Other ways to build with C6 get increasingly verbose, start with our Hello World example below.
                    </p>
                    <h2>Increasingly Verbose</h2>
                    <NavPills
                      color="info"
                      tabs={[
                        {
                          tabButton: "Hello World",
                          tabIcon: LooksOne,
                          tabContent: (
                            <>
                              <p>The code below is just one example of initiating CarbonPHP.
                                Each method comes with its own advantages and caveat's. Unlike 'The Skinny' version
                                above which references a configuration class in another file, this Hello World example
                                initializes the class before it is passed to C6. This means your configuration array
                                and constructor occur before C6 has set constants, started the users session, defined
                                custom error handling, ect.
                              </p>
                              <h4>/index.php</h4>
                              {codeBlock(HelloWorld)}
                            </>

                          )
                        },
                        {
                          tabButton: "Routing",
                          tabIcon: LooksTwo,
                          tabContent: (
                            <>
                              <h3>What is Routing?</h3>
                              <p>The goal: Determine what the user wants by looking at the URI first and route to </p>
                              <p>C6 solution: Use regular expression on the Request URI sent to the Server.</p>
                              {codeBlock("$this->uri = trim(urldecode(parse_url(trim(preg_replace('/\\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)));\n")}
                              <p>* ps, the URL contains your domain name, this, the URI, does not.</p>
                              <p>
                                The below examples are designed to highlight the <b>$this-&gt;structure( (callback)
                                ) </b>
                                method which is defined in <b>CarbonPHP\Route::class </b> extended through the
                                <b> CarbonPHP\Application::class </b> which you are required to extend.
                              </p>
                              {codeBlock("public function structure(callable $struct = null): Route;")}
                              <br/>
                              <p>
                                This method, signatured above, is to be used in conjunction with
                                the <b>regexMatch </b> method.
                                In the 'Hello World' example on the prior tab, we explored the regexMatch method which
                                used a callback (or function passed as a variable) to be executed <u>if and only if</u>
                                ('iff') the regex matches.
                              </p>
                              {codeBlock(RegexMatch)}
                              <p>
                                In this example we see callbacks defined by the <b>structure </b> method. The extra
                                arguments that are given to the <b>regexMatch </b> method will get passed as the first
                                arguments to the callback provided to the structure. If any regex matching
                                groups <b>() </b>
                                are defined, such matches will be passed to the structure following the, if any, extra
                                parameters given to the <b>regexMatch </b> method.
                              </p>
                              <br/>
                              <p>
                                * To pass a callable as an argument to a procedure given to structure method, it must
                                not be
                                the 2nd argument of the <b>regexMatch </b> method. This would imply that the callback
                                should be
                                executed when and <u>iff</u> the regex is matched.
                              </p>
                              {codeBlock(RoutingEx1)}
                            </>
                          )
                        },
                        {
                          tabButton: "iConfig",
                          tabIcon: Looks3,
                          tabContent: (
                            <>
                              <p>
                                The All C6 configurable options are as listed here. Note that many cli functions use
                                these
                                configuration options and may not function correctly with out them. Failure is expected
                                when we work. We hope and work to ensure individual mistakes never impact our customer
                                or end users. In a developer pushes code which may cause an error, we need to make sure
                                no stack trace will reveal the code in our configuration file. This could mean Database
                                Credentials, Docker Secrets, ect. The method used here for, and everywhere we use the
                                configuration is scalable and secure in this regard. It is valid to use an empty array
                                <b> [ ] </b>which would opt out of the C6 features. This may mean you only wish to use
                                the
                                routing feature.
                              </p>
                              <p>
                                The following is an excerpt of the PHPDoc for the expected input to the C6 setup method.
                                It is syntactically formatted to show type then the default option if any.
                              </p>
                              {codeBlock(iConfigPHPDOC, "", "PHP", false)}
                              <p>
                                * Deprecation notice :: we support passing the configuration file as an absolute path to
                                a php file which returns an array. This feature will be removed in the next major
                                release.
                              </p>
                              <p>
                                These specific options are taken from https://Stats.Coach/. The configuration for this
                                documentation is shown on the next "https://CarbonPHP.com/" tab.
                              </p>
                              <h3>/config/Config.php</h3>
                              {codeBlock(iConfig)}

                            </>
                          )
                        },
                        {
                          tabButton: "Instant Chat",
                          tabIcon: Looks4,
                          tabContent: (
                            <>
                              {codeBlock(InstantChat)}
                            </>
                          )
                        },
                        {
                          tabButton: "https://CarbonPHP.com/",
                          tabIcon: Looks5,
                          tabContent: (
                            <>
                              This, C6, website's configuration reflects that of the first example in 'The Skinny'.
                              Recall the following instantiation pattern. Then read the configuration with this website
                              in mind.
                              <br/>
                              <br/>
                              {codeBlock("(new CarbonPHP\\CarbonPHP( Config\\Config::class ))();")}
                              <h2>/config/Config.php</h2>
                              {codeBlock(CarbonPHPConfig)}
                            </>
                          )
                        },
                        {
                          tabButton: "https://Stats.Coach/",
                          tabIcon: Looks6,
                          tabContent: (
                            <>
                              <h2>/StatsCoach.php</h2>
                              {codeBlock(StatsCoach)}
                            </>
                          )
                        },
                      ]}
                    />


                  </p>
                )
              },
              {
                tabIcon: Timeline,
                tabButton: "Overview",
                tabContent: (
                  <>
                    <p>
                      The sequence diagram below and description even further make up a brief low level
                      outline of the C6 internals. The diagram can be thought of as a road map to
                      how code spreads out over multiple files and functions, stacks together, and complete
                      the task at hand. Each block represents the major files that makeup C6. While all
                      code is indeed important, it is sometimes useful to understand that something exist
                      without going into to much detail. The most important takeaways are: <b>dynamic
                      routes conforming to C6 standards will use an MVC. Controllers
                      validate all user input, model layers update and insert data into the database,
                      and views strictly print data to the user. </b>
                    </p>
                    <p>
                      If user input is taken it must be validated to protect against cross site scripting
                      attacks. The folks over at <a href="https://owasp.org/www-project-top-ten/">OWASP </a>
                      do a good job explaining the complexity of protecting
                      yourself against a XSS attack. Simply put, if the user is capable of modifying the
                      information, a variable we need in a routine, it must be validated. The MVC pattern
                      is simplistic in that its separation of concerns is conducive to good validation
                      practices.
                    </p>
                    <p>
                      The bootstrap is where you define your application. Most bootstraps are named after the
                      website they are running. This website uses <b>CarboPHP/C6::class</b> and
                      https://Stats.Coach/ uses <b>StatsCoach::class</b>. It's probably worth noting each
                      class should be in a file named the same name of the class, and if you're lost you
                      should
                      check out the <b>N00B Guid for beginners</b>.
                      This bootstrap typically contains little to no business logic and only maps urls to
                      other methods.
                      In a pure C6 implementation the first step after a uri is matched is the controller.
                    </p>
                    <b>{'$this->structure($this->MVC());'}</b>
                    <br/>
                    <b>{'$this->match(\'Recover/{user_email?}/{user_generated_string?}\', \'User\', \'recover\')()'}</b>
                    <p>
                      We would expect to find the above code in the bootstrap. This would move to
                      the <b>Controller/User </b>
                      class mapped by composers psr4 standard.
                      C6 also features a runtime psr4 auto loading feature, though it is not recommended over
                      composer's.
                      For legacy reasons it is remains a permanent fixture.
                      More on this later, but lets take a look at whats inside this file.
                    </p>


                    <p>
                      or url mapping file, we phase any
                      url parameters and send them to the Controller. This is not the only data that must
                      be validated. All form data is received in the $_POST[], $_GET[], $_FILES[], $_COOKIE[],
                      ect.. super globals predefined by PHP must also be validated.
                    </p>

                    <GridContainer justify="center">
                      <GridItem xs={12} sm={12}>
                        <img
                          style={{
                            flex: 1,
                            width: '100%',
                            height: undefined,
                          }}
                          src={SequenceDiagram}
                          alt="Sequence Diagram"
                        />
                      </GridItem>
                    </GridContainer>
                    <br/><br/>
                    <h2>title C6 MVC Structure</h2>
                    <p>
                      C6 MVC Structure
                    </p>
                    <br/>
                    <b>Browser-&gt;+Index: 1</b>
                    <br/>
                    <h3>1) A 'user' request is received by our server<br/></h3>
                    <br/>
                    <br/>
                    <h3>2) Send relative path to configuration file as string<br/></h3>
                    <br/>
                    <h3>3) Setup with option and define global helper functions<br/></h3>
                    <br/>
                    <h3>4) Returns the C6 Instance<br/></h3>
                    <br/>
                    <h3>5) Pass a class that extends <b>CarbonPHP/Application::class</b>.</h3>
                    <br/>
                    The above implies that the following to abstractions are present in your routing
                    class::<br/><br/>
                    <b>abstract public function startApplication($uri = null) : bool;</b><br/>
                    <small>Defined in the <b>CarbonPHP/Application::class</b></small>
                    <br/><br/>
                    <b>abstract public function defaultRoute();</b><br/>
                    <small>Defined in the <b>CarbonPHP/Route::class</b> which is extend by the
                      <b>Application::class</b>
                    </small>
                    <br/><br/><br/>
                    <b>C6-&gt;+Bootstrap: 6</b><br/>
                    <h3>6) Runs the global function <b>startApplication( YouRoutingClass::class )</b>.
                    </h3><br/>
                    <small>
                      This will ultimately run <br/>
                      <b>(new YourRoutingClass::class)-&gt;startApplication( $uri )</b><br/>
                      defined in your route class. <b>startApplication</b> is designed to allow
                      recursive program flow.<br/>
                      So between steps 6-15
                      you may run <b>startApplication</b> again, thereby repeating 6-15 within
                      6-15 then continuing execution
                      where you called <b>startApplication</b>.<br/>
                      The first invocation of the global <b>startApplication</b> function will
                      statically store the routing classes definition.<br/><br/>
                      With each successive call to <b>startApplication( $uri )</b>, you should path
                      the desired
                      route to re-match. Keep in mind that the first call this function is done
                      automatically with
                      the invocation of the CarbonPHP class object.<br/><br/>
                      <b>startApplication( '/profile' )</b>
                      <br/><br/>
                      The '/' page, or home page, will always run the <br/>
                      <b>YourRoutingClass-&gt;defaultRoute();</b><br/>
                      then return to the index.

                    </small>
                    <br/><br/>
                    <b>Bootstrap-&gt;Bootstrap: 7</b>
                    <h3>7) Set <b>$this-&gt;structure( $this-&gt;MVC() );</b> as the method to use is a match
                      is
                      found.</h3>
                    <b>Bootstrap-&gt;+Controller: 8</b>
                    8) Passes provided arguments to match followed by url variables. The
                    controllers job is to strictly validate data. This could mean database
                    requests, but typically does not. By design, no database modification
                    should be made in this step.
                    <b>Controller--&gt;-Bootstrap: 9</b>
                    9) The responce to validation.
                    If false is returned from the controller, the program execution will effectively
                    stop.
                    The stack will be returned to the index and safely exit with no responce.
                    If <b>null</b> is
                    returned from the controller the model layer will be skipped and the view/responce
                    will invoke next.
                    If a value is returned from the controller (effectively equating to true), the value
                    will be passed as a function argument to the
                    model. If an array is returned from the controller, the list will be unpacked and
                    values will be
                    passed as individual arguments to the model.

                    <b>
                      opt</b>
                    <br/><b>
                    opt</b>
                    <b>
                      Bootstrap-&gt;+Model: 10</b>
                    10) The Bootstrap will logically decide what file and function should be executed
                    next. If a value other than null or false is returned from the controller, the model
                    will run.
                    All data is this step is considered validated. This step is generally reserved for
                    most database requests.
                    If a database Post or Update is required, this is the only place it should be done.

                    <b>
                      Model--&gt;-Bootstrap: 11
                    </b>
                    11) The model can still cancel the view from sending by returning false. This
                    returns the stack to the index
                    and safely exits.
                    end
                    <br/>
                    opt
                    Bootstrap-&gt;+View: 12
                    12) The view is typically handled by CarbonPHP's built-in internals. You can choose
                    to render Mustache Templates or PHP files from the <b>View::content()</b> method.
                    The method will decide which to use based off the files extension.
                    note over View,Browser: 13
                    13) Print and send the content. This could be a JSON, HTML, or any other vector of
                    responce.

                    View--&gt;-Bootstrap: 14
                    14) Safely returning
                    end<br/>
                    <br/>
                    end

                    Bootstrap--&gt;-C6: 15
                    15) Safely returning
                    C6--&gt;-Index: 16
                    16) Safely returning
                    Index-&gt;-Browser: 17
                    17) All code is finished and the connection is closed.
                  </>
                )
              },
              {
                tabIcon: ViewComfy,
                tabButton: "Frontend",
                tabContent: (
                  <div>
                    <div id="navigation-pills">
                      <div className={classes.title}>
                        <h3 className={classes.textCenter}>
                          How should I choose a UI?
                        </h3>
                        <p>
                          While C6 is not actually dependant on a theme,
                          the documentation ships with three leading open source
                          repositories to demonstrate the robust use-cases C6
                          can handle.
                        </p>

                      </div>
                      <div>
                        <h5 className={classes.textCenter}>
                          <b>AdminLTE</b> is <b>HTML5</b> and required <b>Minimal</b> to no <b>Javascript</b> Knowledge
                        </h5>
                        <br/><p>
                        Special thanks to Abdullah Almsaeed and all the ladies and gents working on
                        AdminLTE.
                      </p><br/>
                        <p>
                          AdminLTE is a popular open source WebApp template for admin dashboards and
                          control panels.
                          It is a responsive HTML template that is based on the CSS framework Bootstrap
                          3.
                          It utilizes all of the Bootstrap components in its design and re-styles many
                          other
                          commonly used plugins to create a consistent design that can be used as a user
                          interface
                          for backend applications. AdminLTE is based on a modular design, which allows
                          it to be
                          easily customized and built upon.
                        </p>
                        <p>
                          C6 expands on the AdminLTE UI by implementing Defunkt's <b>jQuery-PJAX</b>,
                          a global alert system, and the Mustache template engine in php and Javascript.
                          CarbonPHP also has Cli support for JS and CSS minification.
                        </p>
                        <p>
                          AdminLTE is a full scale template system that can handle the UI for most
                          applications
                          needs.
                        </p>

                      </div>
                      <div>
                        <h5 className={classes.textCenter}><b>REACT</b> is <b>Javascript, Fast, Cost Effective</b></h5>
                        <br/><p>
                        Special thanks to Creative Tim and all the ladies and gents contributing to the
                        open source Material Kit and Material Dashboard.
                      </p><br/>
                        <p>
                          Creative Tim's Material series implementation Material Bootstrap 4 Admin with
                          a fresh,
                          new design inspired by Google's Material Design. It is based on the popular
                          Bootstrap
                          4 framework and comes packed with multiple third-party plugins. All components
                          are
                          built to fit perfectly with each other, while aligning to the material
                          concepts.
                        </p>
                        <br/>
                        <p>
                          CarbonPHP expands on Tim's UI by adding the first major <b>login context
                          switch</b>,
                          global sweet alert system, a global axios implementation, and routing chains
                          for
                          easy property scoping.
                        </p>
                        <p>
                          REACT features a live development server that compiles and refreshes the
                          browser realtime
                          with edits made to the code. I'm my honest opinion, it is a very pleasing
                          development
                          experience that leads to faster overall development process.
                        </p>
                      </div>
                    </div>
                  </div>
                )
              },
              {
                tabIcon: Storage,
                tabButton: "ORM",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: RecentActors,
                tabButton: "Session",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: Exposure,
                tabButton: "Minification",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                    {codeBlock(Minification)}
                  </p>
                )
              },
              {
                tabIcon: RestorePage,
                tabButton: "Caching",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                    {codeBlock(CacheControl)}
                  </p>
                )
              },
              {
                tabIcon: AllInclusive,
                tabButton: "Autoloading",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: Announcement,
                tabButton: "Alerts",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                      https://sweetalert.js.org/guides/
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: AccountTree,
                tabButton: "MVC",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                    <FileStructure />
                  </p>
                )
              },
              {
                tabIcon: Restaurant,
                tabButton: "Forks",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: Power,
                tabButton: "Websockets",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
                  </p>
                )
              }
            ]}
          />
        </GridItem>
      </GridContainer>
    );
  }
}

export default withStyles(dashboardStyle)(CarbonPHP);
