import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
import {
  AllInclusive,
  Announcement,
  Dashboard,
  Exposure,
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
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";
import NavPills from "components/NavPills/NavPills";
import dashboardStyle from "assets/jss/material-dashboard-react/views/dashboardStyle.jsx";
// wip
import {CodeBlock, googlecode} from "react-code-blocks";
import raw from "raw.macro";

const markdown = raw("../../../assets/examples/HelloWorld.php");


let style = {
  ...completedStyle,
  ...dashboardStyle
};

class CarbonPHP extends React.Component {
  render() {
    const { classes } = this.props;
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
                      <b>Version 6.^ of C6</b>, is the <b>first production ready </b>
                      release.</h3>
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
                    <CodeBlock
                      text={"const APP_ROOT = __DIR__ . DIRECTORY_SEPARATOR;\ninclude 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'\n(new CarbonPHP\\CarbonPHP(Config\\Config::class))();"}
                      language={"php"}
                      showLineNumbers={true}
                      //theme={dracula}
                      //highlight={"3"}
                    />

                    <br/>

                    <CodeBlock
                      text={markdown}
                      language={"php"}
                      showLineNumbers={true}
                      theme={googlecode}
                      highlight={"11,14,19,23,32,40"}
                    />


                  </p>
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
                    </h3>
                  </p>
                )
              },
              {
                tabIcon: Timeline,
                tabButton: "Routing",
                tabContent: (
                  <p>
                    <h3 className={classes.textCenter}>
                    </h3>
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

export default withStyles(style)(CarbonPHP);
