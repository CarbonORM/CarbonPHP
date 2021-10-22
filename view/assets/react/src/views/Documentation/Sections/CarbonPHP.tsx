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
import {WithStyles} from "@material-ui/core/styles";
import {AxiosInstance} from "axios";
// pages
import SequenceDiagram from "assets/img/invertSD.png";
import FileStructure from "./FileStructure";
import AccessControl from "../../AccessControl/AccessControl";
import swal from '@sweetalert/with-react';
import Button from "../../../components/CustomButtons/Button";
import {C6, iFeatures, iGroups, iUsers} from "../../../variables/C6";
import {CODE_EXAMPLES} from "Code";


const HelloWorld = CODE_EXAMPLES.HelloWorld;
const RoutingEx1 = CODE_EXAMPLES.RoutingEx1;
const iConfig = CODE_EXAMPLES.iConfig;
const iConfigPHPDOC = CODE_EXAMPLES.iConfigPHPDOC;
const InstantChat = CODE_EXAMPLES.InstantChat;
const RegexMatch = CODE_EXAMPLES.RegexMatch;
const CarbonPHPConfig = CODE_EXAMPLES.CarbonPHPConfig;
const StatsCoach = CODE_EXAMPLES.StatsCoach;
const CacheControl = CODE_EXAMPLES.CacheControl;
const Minification = CODE_EXAMPLES.Minification;
const minimalRestExample = CODE_EXAMPLES.minimalRestExample;
const restTypeScriptEx1 = CODE_EXAMPLES.restTypeScriptEx1;
const restTest = CODE_EXAMPLES.restTest;
const restUserTest = CODE_EXAMPLES.restUserTest;
const iRest = CODE_EXAMPLES.iRest;
const iRestfulReferences = CODE_EXAMPLES.iRestfulReferences;
const CarbonUsersTable = CODE_EXAMPLES.CarbonUsersTable;
const forksCode = CODE_EXAMPLES.forksCode;
const websocketCode = CODE_EXAMPLES.websocketCode;

const composerCode = CODE_EXAMPLES.composerCode;

const JS_ORM_EXAMPLE_1 = ``;

const JS_ORM_EXAMPLE_2 = ``;

const JS_ORM_EXAMPLE_3 = ``;


interface iCarbonPHP extends WithStyles<typeof dashboardStyle> {
  id: string,
  axios: AxiosInstance;
  testRestfulPostPutDeleteResponse: Function;
  codeBlock: (markdown: String, highlight ?: String, language ?: String, dark ?: boolean) => any;
}

interface UserAccessControl extends iUsers {
  group_name?: string,
  feature_code?: string
}

interface iGroupFeatures extends iGroups, iFeatures {
  allowed_to_grant_group_id?: string;
}

class CarbonPHP extends React.Component<iCarbonPHP, {
  users?: Array<UserAccessControl>,
  features?: Array<iFeatures>,
  groups?: Array<iGroupFeatures>,
  exampleCode: string,
  jsonStringOutput: string,
  exampleCodeAPI: string,
  exampleInterface: string,
  expandUsersRestTable: boolean,
  mobile: boolean,
}> {

  constructor(props) {
    super(props);
    this.state = {
      expandUsersRestTable: false,
      exampleCodeAPI: '',
      exampleInterface: '',
      exampleCode: '',
      jsonStringOutput: '',
      groups: [],
      features: [],
      users: [],
      mobile: false
    };
  }

  componentDidMount() {
    window.addEventListener("resize", this.resize.bind(this));
    this.resize();
  }

  resize() {
    let currentHideNav = (window.innerWidth <= 900);
    if (currentHideNav !== this.state.mobile) {
      this.setState({ mobile: currentHideNav });
    }
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.resize.bind(this));
  }

  render() {
    const { axios, classes, codeBlock } = this.props;

    const orientation = {
      tabsGrid: { xs: 12, sm: 3, md: 2 },
      contentGrid: { xs: 12, sm: 9, md: 10 }
    };

    return (
      <GridContainer justify="center">
        <GridItem xs={12} sm={12} md={12}>
          <div className={classes.textCenter}>
            <h2><b>CarbonPHP is [C6]</b></h2>
            <h4>
              "The next generation in your PHP development"
            </h4>
          </div>
        </GridItem>
        <GridItem xs={12} sm={12} md={12}>
          <NavPills
            color="info"
            scrollButtons={this.state.mobile ? 'on' : 'off'}
            horizontal={this.state.mobile ? undefined : orientation}
            tabs={[
              {
                tabIcon: Dashboard,
                tabButton: "Introduction",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={11}>
                      <>
                        C6 was developed around the latest versions of php, which gives it
                        significant advantage over other PHP frameworks. The framework
                        complements and uses the PHP internals rather than over obfuscating.
                        A good example of this is the super global $_SESSION.
                        CarbonPHP uses builtin php functions to override
                        the standard file save method to use a server based session solution.
                        This grater increases the chance of developers finding relevant examples
                        online. It also improves the odds that PHP.net will be a reliable
                        resource for code in CarbonPHP's context.
                        <br/><br/>

                        <br/>
                        <h2>Five Minute Introduction</h2>
                        <h4>/index.php</h4>
                        <p>In this minimal index.php example the <b>Config\Config::class</b> extends the
                          class <b>CarbonPHP\Application </b>
                          and implements the interface <b>CarbonPHP\Interfaces\iConfig</b>. The Config\Config class
                          will not
                          be instantiated
                          until CarbonPHP is invoked. For the N00B's this is the ending '();' on line 2. This means
                          all C6
                          configuration will be available for the Config's constructor.
                        </p>
                        {codeBlock("include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'\n(new CarbonPHP\\CarbonPHP( Config\\Config::class ), __DIR__ . DIRECTORY_SEPARATOR)();", "", "php", true)}
                        <br/>
                        <p>In this example the <b>Config\Config::class</b> implements the
                          interface <b>CarbonPHP\Interfaces\iConfig</b>.
                          The <b>Application\Application::class</b> extends the abstract
                          class <b>CarbonPHP\Application.</b>
                          The Config class will never be instanced in the below example and will only call the
                          static
                          configuration method.</p>
                        {codeBlock("(new CarbonPHP\\CarbonPHP( Config\\Config::class ))( Application\\Application::class  );", "", "php", true)}
                        <br/>
                        <p>
                          Other ways to build with C6 get increasingly verbose, start with our Hello World example
                          below.
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
                                    Each method comes with its own advantages and caveat's. Unlike 'The Five Minute'
                                    version
                                    above, which references a configuration class in another file, this Hello World
                                    example
                                    initializes the class before it is passed to C6. This means your configuration
                                    array
                                    and constructor occur before C6 has set constants, started the users session,
                                    defined
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
                                  <p>The goal: Determine what the user wants by looking at the URI first and route
                                    to </p>
                                  <p>C6 solution: Use regular expression on the Request URI sent to the Server.</p>
                                  {codeBlock("$this->uri = trim(urldecode(parse_url(trim(preg_replace('/\\s+/', ' ', $_SERVER['REQUEST_URI'])), PHP_URL_PATH)));\n")}
                                  <p>* ps, the URL contains your domain name, this, the URI, does not.</p>
                                  <p>
                                    The below examples are designed to highlight the <b>$this-&gt;structure(
                                    (callback)
                                    ) </b>
                                    method which is defined in <b>CarbonPHP\Route::class </b> extended through the
                                    <b> CarbonPHP\Application::class </b> which you are required to extend.
                                  </p>
                                  {codeBlock("public function structure(callable $struct = null): Route;")}
                                  <br/>
                                  <p>
                                    This method, signatured above, is to be used in conjunction with
                                    the <b>regexMatch </b> method.
                                    In the 'Hello World' example on the prior tab, we explored the regexMatch method
                                    which
                                    used a callback (or function passed as a variable) to be executed <u>if and only
                                    if</u>
                                    ('iff') the regex matches.
                                  </p>
                                  {codeBlock(RegexMatch)}
                                  <p>
                                    In this example we see callbacks defined by the <b>structure </b> method. The
                                    extra
                                    arguments that are given to the <b>regexMatch </b> method will get passed as the
                                    first
                                    arguments to the callback provided to the structure. If any regex matching
                                    groups <b>() </b>
                                    are defined, such matches will be passed to the structure following the, if any,
                                    extra
                                    parameters given to the <b>regexMatch </b> method.
                                  </p>
                                  <br/>
                                  <p>
                                    * To pass a callable as an argument to a procedure given to structure method, it
                                    must
                                    not be
                                    the 2nd argument of the <b>regexMatch </b> method. This would imply that the
                                    callback
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
                                    The All C6 configurable options are as listed here. Note that many cli functions
                                    use
                                    these
                                    configuration options and may not function correctly with out them. Failure is
                                    expected
                                    when we work. We hope and work to ensure individual mistakes never impact our
                                    customer
                                    or end users. In a developer pushes code which may cause an error, we need to
                                    make sure
                                    no stack trace will reveal the code in our configuration file. This could mean
                                    Database
                                    Credentials, Docker Secrets, ect. The method used here for, and everywhere we
                                    use the
                                    configuration is scalable and secure in this regard. It is valid to use an empty
                                    array
                                    <b> [ ] </b>which would opt out of the C6 features. This may mean you only wish
                                    to use
                                    the
                                    routing feature.
                                  </p>
                                  <p>
                                    The following is an excerpt of the PHPDoc for the expected input to the C6 setup
                                    method.
                                    It is syntactically formatted to show type then the default option if any.
                                  </p>
                                  {codeBlock(iConfigPHPDOC, "", "PHP", true)}
                                  <p>
                                    * Deprecation notice :: we support passing the configuration file as an absolute
                                    path to
                                    a php file which returns an array. This feature will be removed in the next
                                    major
                                    release.
                                  </p>
                                  <p>
                                    These specific options are taken from https://Stats.Coach/. The configuration
                                    for this
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
                                  This, C6, website's configuration reflects that of the first example in 'The
                                  Skinny'.
                                  Recall the following instantiation pattern. Then read the configuration with this
                                  website
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


                      </>
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: Timeline,
                tabButton: "Overview",
                tabContent: (
                  <FileStructure/>
                )
              },
              {
                tabIcon: ViewComfy,
                tabButton: "Frontend",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={10}>
                      <div id="navigation-pills">
                        <div className={classes.title}>
                          <h2>Front End Development (FED)</h2>
                        </div>
                        <p>
                          C6 is not dependant on a theme;
                          the documentation ships with three leading open source
                          repositories to demonstrate the robust use-cases C6
                          can handle. Generally, I recommend using react for all new projects.
                          Existing HTML/PHP sites my find migrating first to PJAX and MUSTACHE template,
                          engine existing in C6 since version 2, be an easier transition to react. Note:
                          going straight to react will bare <i>more reward mo' quickly</i>.
                        </p>
                        <br/>
                        <br/>
                        <div>
                          <h3><b>REACT</b><br/>
                            <b>Fast User Experience, Cost Effective, Mobile Friendly</b></h3>
                          <br/><p>
                          Special thanks to Creative Tim and all the ladies and gents contributing to the
                          open source Material Kit and Material Dashboard. My work here is to further the love.

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
                        <br/>
                        <div>
                          <h3>
                            <b>AdminLTE</b> is <b>HTML5</b> and required <b>Minimal</b> to
                            no <b>Javascript</b> Knowledge
                          </h3>
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
                      </div>
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: Storage,
                tabButton: "ORM",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={12} sm={12} md={12}>
                      <h3 className={classes.textCenter}>
                        C6 is shipped with a custom ORM which generates PHP code and Typescript MYSQL bindings.
                      </h3>
                      <p>Writing sql in code is a long process which is hard to maintain over time. C6 automates that.
                        When a reference no longer exists in MYSQL it will not be generated. Your editor will highlight
                        it
                        as undefined,
                        giving you the opportunity to fix it. With code references generated for you writing your sql is
                        easier than ever. Statement and columns will autocomplete giving you ease of mind every time.
                        Queries
                        generated will be validated automatically using PDO based off table data from the mysql dump.
                        The
                        REST ORM C6 ships with allows gives you a full api with customizable endpoints and validation
                        functions.
                      </p>
                      <br/>
                      <p>The command line interface is used to generate and regenerate bindings.</p>
                      {codeBlock("php index.php rest", "", "php", true)}
                      <small>You may append the <b>"-help"</b> flag to see a full list or options.</small>
                      <br/>
                      <GridContainer justify="center">
                        <GridItem sm={'none'} md={1}> </GridItem>
                        <GridItem xs={12} sm={12} md={10}>
                          <h3>Overview</h3>
                          <ol {...{ 'start': 0 }}>
                            <li>Examples</li>
                            <li>Requirements</li>
                            <li>Restful API</li>
                            <li>Internal API</li>
                            <li>Validation Filters
                              <ul>
                                <li>Restful
                                  <ol>
                                    <li>Column Regexps</li>
                                    <li>Filter Every Request</li>
                                    <li>Filter Specific Request Method</li>
                                    <li>Column Specific Callbacks</li>
                                  </ol>
                                </li>
                                <li>Internal
                                  <ol>
                                    <li>Running Restful Validation Helpers</li>
                                    <li>Using Access Control With Delegated Administration</li>
                                  </ol>
                                </li>
                              </ul>
                            </li>
                            <li>Data Retention with <i>-Triggers</i></li>
                            <li>Entity System</li>
                          </ol>
                        </GridItem>
                      </GridContainer>
                      <br/>
                      <h2>Examples</h2>
                      <p>
                        These are real restful requests which use the autogenerated syntax to communicate with the api.
                        You can see these request in a nice user interface in the 'Sessions' tab. This layout is
                        intended
                        to show the raw response from the server. The following examples are built with
                        the <b>-json </b>
                        flag. Removing such would cause the extra "sql" reporting information to not displayed.
                        <br/>
                        <br/>
                        <Button color="info" round onClick={() => {
                          axios.get('/rest/' + C6.features.TABLE_NAME)
                            .then(response => this.setState({
                              features: (response.data.rest || []),
                              exampleCode: JS_ORM_EXAMPLE_1,
                              jsonStringOutput: JSON.stringify(response.data, null, 2)
                            }));
                        }}>
                          Simple Select All
                        </Button>
                        <Button color="info" round onClick={() => {
                          // noinspection DuplicatedCode
                          axios.get('/rest/' + C6.groups.TABLE_NAME, {
                            params: {
                              [C6.SELECT]: [
                                C6.groups.ENTITY_ID,
                                C6.groups.GROUP_NAME,
                                [C6.GROUP_CONCAT, C6.features.FEATURE_CODE],
                                [C6.GROUP_CONCAT, C6.group_references.ALLOWED_TO_GRANT_GROUP_ID]
                              ],
                              [C6.JOIN]: {
                                [C6.LEFT]: {
                                  [C6.group_references.TABLE_NAME]: [
                                    C6.group_references.GROUP_ID,
                                    C6.groups.ENTITY_ID
                                  ],
                                  [C6.feature_group_references.TABLE_NAME]: [
                                    C6.groups.ENTITY_ID,
                                    C6.feature_group_references.GROUP_ENTITY_ID
                                  ],
                                  [C6.features.TABLE_NAME]: [
                                    C6.features.FEATURE_ENTITY_ID,
                                    C6.feature_group_references.FEATURE_ENTITY_ID
                                  ]
                                }
                              },
                              [C6.GROUP_BY]: [
                                C6.groups.ENTITY_ID
                              ],
                              [C6.PAGINATION]: {
                                [C6.LIMIT]: 100
                              }
                            }
                          }).then(response => this.setState({
                            groups: response.data.rest,
                            exampleCode: JS_ORM_EXAMPLE_2,
                            jsonStringOutput: JSON.stringify(response.data, null, 2)
                          }))
                        }}>
                          Table Joins
                        </Button>
                        <Button color="info" round onClick={() => {
                          // noinspection DuplicatedCode
                          axios.get('/rest/' + C6.users.TABLE_NAME, {
                            params: {
                              [C6.SELECT]: [
                                C6.users.USER_USERNAME,
                                C6.users.USER_FIRST_NAME,
                                C6.users.USER_LAST_NAME,
                                C6.users.USER_ID,
                                [C6.GROUP_CONCAT, C6.features.FEATURE_CODE],
                                [C6.GROUP_CONCAT, C6.groups.GROUP_NAME]
                              ],
                              [C6.JOIN]: {
                                [C6.LEFT]: {
                                  [C6.user_groups.TABLE_NAME]: [
                                    C6.users.USER_ID,
                                    C6.user_groups.USER_ID
                                  ],
                                  [C6.groups.TABLE_NAME]: [
                                    C6.user_groups.GROUP_ID,
                                    C6.groups.ENTITY_ID
                                  ],
                                  [C6.feature_group_references.TABLE_NAME]: [
                                    C6.groups.ENTITY_ID,
                                    C6.feature_group_references.GROUP_ENTITY_ID
                                  ],
                                  [C6.features.TABLE_NAME]: [
                                    C6.features.FEATURE_ENTITY_ID,
                                    C6.feature_group_references.FEATURE_ENTITY_ID
                                  ]
                                }
                              },
                              [C6.GROUP_BY]: [
                                C6.users.USER_ID,
                              ],
                              [C6.PAGINATION]: {
                                [C6.LIMIT]: 100
                              }
                            }
                          }).then(response => this.setState({
                            users: (response.data.rest || []),
                            exampleCode: JS_ORM_EXAMPLE_3,
                            jsonStringOutput: JSON.stringify(response.data, null, 2)
                          }));
                        }}>
                          MySQL Aggregate Functions
                        </Button>
                        <Button color="info" round onClick={() => this.setState({
                          exampleCode: restTypeScriptEx1,
                          jsonStringOutput: ''
                        })}>
                          Example #4 TypeScript Generation
                        </Button>
                        <Button color="success" round onClick={() => this.setState({
                          exampleCode: '',
                          jsonStringOutput: ''
                        })}>
                          Reset Examples
                        </Button>
                      </p>

                      <div>
                        {this.state.jsonStringOutput !== '' ? <h5><b>Part 1, the response.</b></h5> : ''}
                        <pre>
                        {this.state.jsonStringOutput !== '' ? this.state.jsonStringOutput : ''}
                      </pre>
                        {this.state.jsonStringOutput !== '' ? <h5><b>Part 2, frontend request.</b></h5> : ''}
                        {this.state.exampleCode !== '' ? codeBlock(this.state.exampleCode, '', 'javascript') : ''}
                      </div>
                      <br/>
                      <h2>Requirements</h2>
                      <p>
                        By default the rest program uses <b>mysqldump</b> which should be in your environments $PATH.
                        This
                        will be the
                        case should mysql be installed on your system. You may use the <b>-mysqldump</b> flag to specify
                        the executable location.
                        For some systems this is not possible, so the flag <b>-dump</b> exists to specify the location
                        of
                        the dump generated.
                        This dump should be created using the <b>--no-data</b> flag for the mysqldump program. Not doing
                        such may cause unexpected
                        results. Should a dump file be provided, no database access or credentials are required. The
                        following code example
                        is our minimal rest example, which would give full rest access to any generated tables.
                      </p>
                      <h5><small>Minimum working example:</small></h5>
                      {codeBlock(minimalRestExample)}
                      <br/><br/>
                      <h2>Restful API</h2>
                      <p>
                        There are two possible contracts ('Interfaces') that the auto generated php class may follow.
                        <br/><br/>
                        <Button color="info" round onClick={() => this.setState({
                          exampleInterface: iRest,
                        })}>
                          Table with primary key
                        </Button>
                        <Button color="info" round onClick={() => this.setState({
                          exampleInterface: iRestfulReferences,
                        })}>
                          Table without primary key
                        </Button>
                        <Button color="success" round onClick={() => this.setState({
                          exampleInterface: '',
                        })}>
                          Table without primary key
                        </Button>
                        <br/>
                        <br/>
                        <div>
                          {this.state.exampleInterface !== '' ? codeBlock(this.state.exampleInterface, '', 'php') : ''}
                        </div>
                      </p>
                      <h2>Internal API</h2>
                      <p>
                        I believe the best way to display an api is through the tests which act as contracts of
                        existence.
                        <br/><br/>
                        <Button color="info" round onClick={() => this.setState({
                          exampleCodeAPI: restTest,
                        })}>
                          Rest Test
                        </Button>
                        <Button color="info" round onClick={() => this.setState({
                          exampleCodeAPI: restUserTest,
                        })}>
                          User Test
                        </Button>
                        <Button color="success" round onClick={() => this.setState({
                          exampleCodeAPI: '',
                        })}>
                          Reset Examples
                        </Button>
                        <br/>
                        <br/>
                        <div>
                          {this.state.exampleCodeAPI !== '' ? codeBlock(this.state.exampleCodeAPI, '', 'php') : ''}
                        </div>

                      </p>
                      <h2>Validation Filters</h2>
                      <p>
                        When data is accessed or posted it needs to be sanitized and controlled for access privileges.
                        The generated files C6 ORM creates are parsed between each run. The script looks for new methods
                        and validations then attempts to preserve it to the output class. This has proven very effective
                        in file management and pragmatic flow. Table specific validations should go into its respective
                        orm class. Generated files should be tracked on GitHub or other version control systems.
                        In the example below pay close attention to lines 60 through 95.
                        <br/><br/>
                        Validations will be run in the following order: <br/>
                        <ol>
                          <li>Regular Expressions</li>
                          <li>Custom Methods
                            <ol>
                              <li>Global Request Method Callbacks</li>
                              <li>REST Specific Callbacks</li>
                              <li>Column Specific Global Request Method Callbacks</li>
                              <li>REST & Column Specific Callbacks</li>
                            </ol>
                          </li>
                        </ol>
                        <br/>
                        <Button color={this.state.expandUsersRestTable ? "success" : "info"} round
                                onClick={() => this.setState({
                                  expandUsersRestTable: !this.state.expandUsersRestTable
                                })}>
                          {this.state.expandUsersRestTable ? "Collapse Example Code" : "Expand Fully Generated Restful ORM"}
                        </Button>
                        <br/><br/>
                        {this.state.expandUsersRestTable === true ? codeBlock(CarbonUsersTable) : ''}
                        <br/><br/>
                      </p>
                      <h2>Data Retention with <i>-Triggers</i></h2>
                      <p>
                        C6 uses triggers to help keep data for official reporting from tax records to state documents.
                        When
                        rest is run with the <b>-triggers</b> flag each table will have a custom generated script
                        attached
                        which tracks all changes in a singular history table in a json format. This is important because
                        of
                        our heavy use of cascade delete.
                      </p>
                      <h2>Entity System</h2>
                      <p>
                        Popular in game development the entity system is C6's bread and butter. In short: it allows us
                        to
                        relate any table to any other table in a meaningful way where cascade delete will still work. To
                        clarify, if you have a locations table, you might want to use that for user images uploaded, and
                        shipping address for your customer. When that photo, or user, gets deleted you would want wall
                        relations
                        to that entity (the user or picture) to be delete. Another example would be a 'like button'.
                        This
                        could be stuck to any entity. I like the person, location, organization, photo, ect...
                      </p>
                      <p>
                        The way C6 achieves this system in mysql is simple. We have a master table called 'carbons'
                        which
                        contains every primary key in the whole schema. Actually every primary key will be generated
                        with
                        this table and then only referenced through foreign key relations. Tables will still have
                        primary
                        keys, and indexes will not change, but every relation will stem from a singular table. The
                        'carbons'
                        table contains three columns: entity_pk, entity_fk, entity_tag. The entries to this table are
                        entirely managed by the ORM generated code. The keys are binary(16) fields for maximum speed in
                        searching.
                        All tables with primary keys Must have cascade delete enabled for those relations.
                        Keys are generated in mysql using the <b>uuid()</b> function then automatically hexed and
                        unhexed
                        for you through the api. To clarify there is no need to use the hex and unhex aggregate function
                        on
                        binary content as it it done for you in the API.
                      </p>
                      <p>
                        In our written example above we discussed the idea of user, photos, and likes. Lets look at what
                        those would look like in the database. Users are almost always the top level entity in our
                        system.
                        I would argue that while many companies hold a reasonable technical flow that users belonging to
                        an
                        organization, their is always at least one user who should manage it. For this reason when the
                        user
                        gets created the reference in the 'carbons' table has entity_pk filled and entity_fk set to
                        null.
                        The entity_tag will always be the table's name that created the reference. From here out our
                        user
                        who creates the entity would have their own users entity_pk equal the entity_fk of entities they
                        created.
                        Exceptions to this rule exist such as when a users content they posted would not there after
                        belong to them.
                      </p>
                      <p>
                        It is a good idea to create reference tables. When two tables need to be related together and
                        because of the entity system are referenced, it is a good idea to export this. By this I mean
                        have
                        a table contain two columns, both of which point to 'carbons.entity_pk' with the cascade delete
                        foreign keys. This helps reduce the searches in carbons, and shrinks the volume of your
                        searches.
                        When a reference be made, say for example: a known popular location is tagged to a photo, this
                        type
                        of relation could be used. It would be fair to assume in some systems that photo's become open
                        source and locations are other entities which do not belong to users. These could, in theory, be
                        related together in the entity system. I Typically would recommend giving each table a primary
                        key,
                        with it pointing to carbons.entity_pk. Over time you will notice when pk will not be use due to
                        your
                        own systems needs and relations. It doesn't hurt to have it in development, however overtime
                        it's
                        best to optimise where possible.
                      </p>
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: RecentActors,
                tabButton: "IAM",
                tabContent:
                  <GridContainer justify="center">
                  <GridItem xs={false} sm={false} md={1}> </GridItem>
                  <GridItem xs={12} sm={12} md={10}>
                    {codeBlock("php index.php rest", "", "bash", true)}
                    <AccessControl
                      id={this.props.id}
                      testRestfulPostPutDeleteResponse={this.props.testRestfulPostPutDeleteResponse}
                      axios={this.props.axios}
                    />
                  </GridItem>
                </GridContainer>
              },
              {
                tabIcon: Exposure,
                tabButton: "Minification",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={10}>
                      <h3 className={classes.textCenter}>
                        Minification is supported for Javascript and CSS
                      </h3>
                      <p>This process shrinks load time by reducing file sizes.</p>
                      <br/><br/>
                      {codeBlock("php index.php minify", "", "php", true)}
                      <small>Use the command above built into the CLI to execute this routing. It is recommended to add
                        this to your build routine.</small>
                      <br/><br/>
                      <p>The file below is a bootstrap with only one feature, Minification. The result of running the
                        command above
                        would be a file, or two, being created/overwritten in the location dictated by the array
                        returned
                        by the configuration.
                        More specifically by the
                        field: {codeBlock("$return['MINIFY']['CSS']['OUT']\n$return['MINIFY']['JS']['OUT']", "", "php", true)}
                      </p>

                      {codeBlock(Minification)}
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: RestorePage,
                tabButton: "Caching",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={11}>
                      <h3 className={classes.textCenter}>
                      </h3>
                      {codeBlock(CacheControl)}
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: AllInclusive,
                tabButton: "Autoloading",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={8}>
                      <h3 className={classes.textCenter}>
                        Autoloading
                      </h3>
                      <p>
                        It is recommended to use composers built in PSR-4 autoloader. It is required to load C6 and
                        works
                        well. The <a href={"https://www.php-fig.org/psr/psr-4/"}>autoloader shipped with C6</a> was
                        deprecated and removed.
                      </p>
                      <br/>
                      {codeBlock(composerCode)}
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: Announcement,
                tabButton: "Alerts",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={8}>
                      <h3>
                        https://sweetalert.js.org/guides/
                      </h3>
                      <Button color={'info'} round onClick={() => swal({
                        text: 'Search for a movie. e.g. "La La Land".',
                        content: "input",
                        button: {
                          text: "Search!",
                          closeModal: false,
                        },
                      })
                        .then(name => {
                          if (!name) {
                            throw new Error('')
                          }

                          return fetch(`https://itunes.apple.com/search?term=${name}&entity=movie`);
                        })
                        .then(results => {
                          return results.json();
                        })
                        .then(json => {
                          const movie = json.results[0];

                          if (!movie) {
                            return swal("No movie was found!");
                          }

                          const name = movie.trackName;
                          const imageURL = movie.artworkUrl100;

                          swal({
                            title: "Top result:",
                            text: name,
                            icon: imageURL,
                          });
                        })
                        .catch(err => {
                          if (err) {
                            swal("Oh noes!", "The AJAX request failed!", "error");
                          } else {
                            swal.stopLoading();
                            swal.close();
                          }
                        })}>
                        Click here for an example!
                      </Button>
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: AccountTree,
                tabButton: "MVC",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={11}>
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
                      {codeBlock("$this->structure($this->MVC());", "", "php", true)}
                      <br/>
                      {codeBlock('$this->match(\'Recover/{user_email?}/{user_generated_string?}\', \'User\', \'recover\')()', "", "php", true)}

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
                      <h3>8) Passes provided arguments to match followed by url variables.</h3>
                      The
                      controllers job is to strictly validate data. This could mean database
                      requests, but typically does not. By design, no database modification
                      should be made in this step.
                      <b>Controller--&gt;-Bootstrap: 9</b>
                      <h3>9) The responce to validation.</h3>
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
                      <h3>10) The Bootstrap will logically decide what file and function should be executed
                        next. </h3>If a value other than null or false is returned from the controller, the model
                      will run.
                      All data is this step is considered validated. This step is generally reserved for
                      most database requests.
                      If a database Post or Update is required, this is the only place it should be done.

                      <b>
                        Model--&gt;-Bootstrap: 11
                      </b>
                      <h3>11) The model can still cancel the view from sending by returning false.</h3> This

                      returns the stack to the index
                      and safely exits.
                      end
                      <br/>
                      opt
                      Bootstrap-&gt;+View: 12

                      <h3>12) The view is typically handled by CarbonPHP's built-in internals.</h3> You can choose
                      to render Mustache Templates or PHP files from the <b>View::content()</b> method.
                      The method will decide which to use based off the files extension.
                      note over View,Browser: 13
                      <h3>13) Print and send the content. This could be a JSON, HTML, or any other vector of
                        response.</h3>

                      View--&gt;-Bootstrap: 14
                      <h3>14) Safely returning
                        end</h3>
                      <br/>

                      <br/>
                      end

                      Bootstrap--&gt;-C6: 15
                      15) Safely returning
                      C6--&gt;-Index: 16
                      16) Safely returning
                      Index-&gt;-Browser: 17
                      17) All code is finished and the connection is closed.
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: Restaurant,
                tabButton: "Forks",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={11}>
                      <h3 className={classes.textCenter}>
                        Forks
                      </h3>
                      <p>I plan to write a pnctl library for Windows one day. If anyone would like to help that would be
                        much appreciated. Contact me at <b>Richard@Miles.Systems</b> and thank you in advance. Until
                        then
                        forking will only be available for linux and osx users. You can use
                        <b> Fork::safe()</b> to help avoid cross platform issues. So programs simply require the
                        library,
                        such as websockets.</p>
                      {codeBlock(forksCode)}
                    </GridItem>
                  </GridContainer>
                )
              },
              {
                tabIcon: Power,
                tabButton: "Websockets",
                tabContent: (
                  <GridContainer justify="center">
                    <GridItem xs={false} sm={false} md={1}> </GridItem>
                    <GridItem xs={12} sm={12} md={11}>
                      <h3 className={classes.textCenter}>
                        The Websocket Protocol
                      </h3>
                      <small>Websockets all for realtime persistent communication.</small>
                      <br/><br/>
                      {codeBlock("php index.php websocket", "", "php", true)}
                      <br/><br/>
                      {codeBlock(websocketCode)}
                    </GridItem>
                  </GridContainer>
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
