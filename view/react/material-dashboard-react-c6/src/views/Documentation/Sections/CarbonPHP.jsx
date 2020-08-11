import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
import Dashboard from "@material-ui/icons/Dashboard";
import Schedule from "@material-ui/icons/Schedule";
import List from "@material-ui/icons/List";

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";
import NavPills from "components/NavPills/NavPills";
import {Button, Card} from "@material-ui/core/index";
import CardBody from "components/Card/CardBody";
import CardFooter from "components/Card/CardFooter";
import dashboardStyle from "assets/jss/material-dashboard-react/views/dashboardStyle.jsx";


//img
import Place from "@material-ui/icons/Place";
import beforeMin from 'assets/img/beforeMin.png';

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
                  This documentation is for C6, a PHP 7.2+ application framework.
                </h4>
              </div>
            </GridItem>
            <GridItem xs={12} sm={12} md={12}>
              <NavPills
                color="info"
                horizontal={{
                  tabsGrid: { xs: 12, sm: 4, md: 4 },
                  contentGrid: { xs: 12, sm: 8, md: 8 }
                }}
                tabs={[
                  {
                    tabIcon: Dashboard,
                    tabButton: "Introduction",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                          <b>Version 6.0 of C6</b>, is the <b>first production ready </b>
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
                        <br/>
                        <h4 className={classes.textCenter}>
                          The <b>first official</b> release was version <b>2.0</b>. This
                          marked
                          the <b>first Alpha
                          release</b>.<br/> With version <b>5.0</b> we moved to <b>Beta</b>.</h4>
                        <br/><br/>

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
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
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
                          <GridContainer>
                            <GridItem xs={12} sm={12} md={8} lg={6}>
                              <NavPills
                                color="info"
                                horizontal={{
                                  tabsGrid: { xs: 12, sm: 4, md: 4 },
                                  contentGrid: { xs: 12, sm: 8, md: 8 }
                                }}
                                tabs={[
                                  {
                                    tabButton: "AdminLTE",
                                    tabIcon: Dashboard,
                                    tabContent: (
                                      <div>
                                        <h5><b>HTML5</b> and <b>Minimal</b> if any <b>Javascript</b> Knowledge Needed
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
                                      </div>
                                    )
                                  },   // TODO - link https://github.com/defunkt/jquery-pjax
                                  {
                                    tabButton: "Speed",
                                    tabIcon: Schedule,
                                    tabContent: (
                                      <div>
                                        <p>
                                          AdminLTE is a full scale template system that can handle the UI for most
                                          applications
                                          needs.
                                        </p>
                                        <br/><br/>
                                        <GridItem xs={12} sm={12} md={12}>
                                          <Card product>
                                            <img
                                              className={
                                                classes.cardImgTop +
                                                " " +
                                                classes.imgRaised +
                                                " " +
                                                classes.imgRounded +
                                                " " +
                                                classes.imgFluid
                                              }
                                              data-src={beforeMin}
                                              alt="100%x180"
                                              style={{
                                                height: "180px",
                                                width: "100%",
                                                display: "block"
                                              }}
                                              src={beforeMin}
                                              data-holder-rendered="true"
                                            />

                                            <CardBody>
                                              <h4
                                                className={classes.cardTitle}>
                                                <a
                                                  href="#pablo"
                                                  onClick={e => e.preventDefault()}>
                                                  Before Resource Minimization
                                                </a>
                                              </h4>
                                              <p>
                                                The place is close to Barceloneta Beach and bus stop just 2
                                                min by walk and near to "Naviglio" where you can enjoy the
                                                main night life in Barcelona.
                                              </p>
                                            </CardBody>
                                            <CardFooter
                                              product>
                                              <div>
                                                <h4>20 mb/sec</h4>
                                              </div>
                                              <div
                                                className={classes.stats}>
                                                <Place/> Barcelona, Spain
                                              </div>
                                            </CardFooter>
                                          </Card>
                                        </GridItem>
                                        <br/><br/>
                                        <GridItem xs={12} sm={12} md={12}>
                                          <Card product>

                                            <img
                                              className={
                                                classes.cardImgTop +
                                                " " +
                                                classes.imgRaised +
                                                " " +
                                                classes.imgRounded +
                                                " " +
                                                classes.imgFluid
                                              }
                                              data-src={beforeMin}
                                              alt="100%x180"
                                              style={{
                                                height: "180px",
                                                width: "100%",
                                                display: "block"
                                              }}
                                              src={beforeMin}
                                              data-holder-rendered="true"
                                            />

                                            <CardBody>
                                              <h4
                                                className={classes.cardTitle}>
                                                <a
                                                  href="#pablo"
                                                  onClick={e => e.preventDefault()}>
                                                  After Resource Minimization
                                                </a>
                                              </h4>
                                              <p>
                                                The place is close to Barceloneta Beach and bus stop just 2
                                                min by walk and near to "Naviglio" where you can enjoy the
                                                main night life in Barcelona.
                                              </p>
                                            </CardBody>
                                            <CardFooter
                                              product>
                                              <div>
                                                <h4>35 mb/sec</h4>
                                              </div>
                                              <div
                                                className={classes.stats}>
                                                <Place/> Barcelona, Spain
                                              </div>
                                            </CardFooter>
                                          </Card>
                                        </GridItem>
                                      </div>
                                    )
                                  },
                                  {
                                    tabButton: "More",
                                    tabIcon: List,
                                    tabContent: (
                                      <div>
                                        <p>
                                          Legacy code bases may find it useful to convert
                                          existing html/xml template systems to the Mustache engine.
                                          The Mustache system is implemented in dozens of languages,
                                          and can render content in PHP and Javascript. This UI choice is a good
                                          stepping stone for react development as it requires
                                          a json based payload system. Those who are coming to
                                          C6 as a PHP migration solution, or trying to learn the
                                          art of web-dev may find this to be the best option.
                                        </p>
                                        <p>
                                          This solution is typically easier for those who are not
                                          familiar with REACT. It also can be ideal for large
                                          applications where the most users are never expected
                                          to see most the content. The PJAX script allows us to
                                          update html snippets rather than a full page refresh.
                                          With json Mustache architecture, script minification,
                                          content caching, and a JS & PHP compatible compiler
                                          speed will be not be an issue with this stack.
                                        </p>
                                        <p>
                                          HTTP and HTTPS requests will always return the outer HTML presentation
                                          layer. If the site version defined in the config file changes on the server
                                          during an active user session the full layout will be reloaded via a full
                                          page refresh. All 'a href=' links and Forms will be captured and automatically
                                          be sent through AJAX. Content can be deligated after C6
                                          initalization with the global constants HTTP, HTTPS, AJAX, and SOCKETS.
                                        </p>
                                        <Button
                                          color="primary"
                                          size="lg"
                                          href="https://www.creative-tim.com/product/material-kit-react"
                                          target="_blank"
                                        >
                                          Free React Download
                                        </Button>
                                      </div>
                                    )
                                  }
                                ]}
                              />
                            </GridItem>
                            <GridItem xs={12} sm={12} md={8} lg={6}>
                              <NavPills
                                color="info"
                                horizontal={{
                                  tabsGrid: { xs: 12, sm: 4, md: 4 },
                                  contentGrid: { xs: 12, sm: 8, md: 8 }
                                }}
                                tabs={[
                                  {
                                    tabButton: "React",
                                    tabIcon: Dashboard,
                                    tabContent: (
                                      <div>
                                        <h5><b>Javascript REACT</b> is <b>Speed</b></h5>
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
                                      </div>
                                    )
                                  },
                                  {
                                    tabButton: "Speed",
                                    tabIcon: Schedule,
                                    tabContent: (
                                      <div>
                                        <p>
                                          REACT features a live development server that compiles and refreshes the
                                          browser realtime
                                          with edits made to the code. I'm my honest opinion, it is a very pleasing
                                          development
                                          experience that leads to faster overall development process.
                                        </p>
                                        <br/>
                                        <p>
                                          The following is a audit profile generated from Google Chrome before
                                          minification
                                          is enabled.
                                        </p>
                                        <br/><br/>
                                        <GridItem xs={12} sm={12} md={12}>
                                          <Card product>

                                            <img
                                              className={
                                                classes.cardImgTop +
                                                " " +
                                                classes.imgRaised +
                                                " " +
                                                classes.imgRounded +
                                                " " +
                                                classes.imgFluid
                                              }
                                              data-src={beforeMin}
                                              alt="100%x180"
                                              style={{
                                                height: "180px",
                                                width: "100%",
                                                display: "block"
                                              }}
                                              src={beforeMin}
                                              data-holder-rendered="true"
                                            />

                                            <CardBody>
                                              <h4
                                                className={classes.cardTitle}>
                                                <a
                                                  href="#pablo"
                                                  onClick={e => e.preventDefault()}>
                                                  Speed Test
                                                </a>
                                              </h4>
                                              <p>
                                                The place is close to Barceloneta Beach and bus stop just 2
                                                min by walk and near to "Naviglio" where you can enjoy the
                                                main night life in Barcelona.
                                              </p>
                                            </CardBody>
                                            <CardFooter
                                              product>
                                              <div>
                                                <h4>20 mb/sec</h4>
                                              </div>
                                              <div
                                                className={classes.stats}>
                                                <Place/> Barcelona, Spain
                                              </div>
                                            </CardFooter>
                                          </Card>
                                        </GridItem>


                                        <p>
                                          load speed here
                                        </p>
                                      </div>
                                    )
                                  },
                                  {
                                    tabButton: "More",
                                    tabIcon: List,
                                    tabContent: (
                                      <div>
                                        <p>
                                          The learning curve for react is steep for new programmers; however,
                                          with the industry moving to react it would be a smart investment of your time.

                                        </p>
                                        <br/>
                                        <p>
                                          Documentation
                                          https://demos.creative-tim.com/material-dashboard-pro-react/#/documentation/tutorial
                                        </p>
                                      </div>
                                    )
                                  }
                                ]}
                              />
                            </GridItem>
                          </GridContainer>
                        </div>
                      </div>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "ORM",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Session",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Minification",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Caching",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Autoloading",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Alerts",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Routing",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
                    tabButton: "Forks",
                    tabContent: (
                      <p>
                        <h3 className={classes.textCenter}>
                        </h3>
                      </p>
                    )
                  },
                  {
                    tabIcon: Dashboard,
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
