import React from "react";
// react components for routing our app without refresh
import {Link} from "react-router-dom";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
import exampleStyle from "assets/jss/material-kit-react/views/componentsSections/exampleStyle.jsx";

import landing from "assets/img/Carbon-teal.png";
import profile from "assets/img/Carbon-green.png";

import Quote from "../../../components/Typography/Quote";
import CustomTabs from "../../../components/CustomTabs/CustomTabs";
import Code from "@material-ui/icons/Code";
import Checklist from "../../../components/Checklist/Checklist";

class Implementations extends React.Component {
    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={12} sm={12} md={6}>
                          <a href="https://github.com/RichardTMiles/CarbonPHP">
                            <img
                                src={landing}
                                alt="..."
                                className={
                                    classes.imgRaised +
                                    " " +
                                    classes.imgRounded +
                                    " " +
                                    classes.imgFluid
                                }
                            />
                          </a>
                            <Button color="primary" size="lg" href="https://github.com/RichardTMiles/CarbonPHP"
                                    target="_blank"
                                    className={classes.link} simple>
                                CarbonPHP [C6]
                            </Button>
                        </GridItem>
                        <GridItem xs={12} sm={12} md={6}>
                            <a href={"https://github.com/RichardTMiles/Stats.Coach"} className={classes.link}>
                                <img
                                    src={profile}
                                    alt="..."
                                    className={
                                        classes.imgRaised +
                                        " " +
                                        classes.imgRounded +
                                        " " +
                                        classes.imgFluid
                                    }
                                />
                                <Button color="primary" size="lg" href="https://github.com/RichardTMiles/Stats.Coach"
                                        target="_blank"
                                        simple>
                                    Stats Coach
                                </Button>
                            </a>
                        </GridItem>
                        <GridContainer className={classes.textCenter} justify="center">
                            <GridItem xs={12} sm={12} md={8}>
                                <h2>Quick Start</h2>
                                <p>If you want to add C6 to an existing project you may use the following<br/>
                                    <code>composer require “richardtmiles/carbonphp:dev-master"</code></p>

                                <h4>
                                    I have launched{" "}
                                    <a
                                        href="https://github.com/RichardTMiles/CarbonPHP"
                                        rel="noopener noreferrer"
                                        target="_blank"
                                    >
                                        CarbonPHP{" "}
                                    </a> as a tool for your developement needs. It has a huge number of components and a
                                    fair amount of documentation. Stats Coach is an open source for profit project that highlights.
                                    the uses of C6. I recommend using this as an example to guid your development. Downloading C6
                                    will get you this documentation website in HTML 5 and REACT, whereas downloading Stats Coach
                                    will give you a database connected application in only HTML 5.
                                </h4>
                                <p className="lead">
                                    Before you can run the website/webapp on the enviroment you choose, you'll need to run a two commands.
                                    <br/>
                                    <code>composer install</code>
                                    <br/>
                                    <code>npm install</code>
                                    <br/><br/>
                                    As listed in the dependancies, you'll need to have composer and npm installed globally for
                                    the above commands to work.<br/><br/>

                                    You'll need to edit the database configurations in the <b>/cofig/Config.php</b> file.
                                    <br/><br/>If you've downloaded the files locally, you can use the following command<br/>
                                    <code>php -S localhost:80 index.php</code><br/><br/>
                                    Then navigate to <b>localhost</b> in the browser to see the website in action.
                                </p>
                            </GridItem>
                        </GridContainer>
                    </GridContainer>
                    <div className={classes.section}>
                        <div className={classes.container}>
                            <GridContainer className={classes.textCenter} justify="center">
                                <GridItem xs={12} sm={12} md={12}>

                                    <div className={classes.typo}>
                                        <h2>Test Locally!</h2>
                                        <Quote
                                          text={
                                              <p>Setting up a production environment allows your app to be seen by the outside
                                                  world.
                                                  Our
                                                  framework is capable of running on your local machine using the development
                                                  server
                                                  built
                                                  into PHP. After downloading the C6 framework, you can view it (this website)
                                                  locally
                                                  using the following command.
                                              </p>}
                                          author={<p><code>php -S localhost:8080 index.php</code></p>}
                                        />
                                    </div>

                                    <GridContainer>
                                        <GridItem xs={12} sm={12} md={12}>
                                            <h3>Google Compute Engine <br/><small> virtual machines</small></h3>
                                            <CustomTabs
                                              headerColor="primary"
                                              tabs={[
                                                  {
                                                      tabName: "#Recommended Environment",
                                                      tabIcon: Code,
                                                      tabContent: (

                                                        <p className={classes.textCenter}>
                                                            Google Compute Engine is a virtual machine, or VM, configurable
                                                            to scale automatically as traffic spikes on your servers. The
                                                            platform allows you to have root access and open multiple ports
                                                            so features like Websockets will are compatible. You may use any
                                                            os provided in the Compute interface, but it is recommended you
                                                            start from a new instance. This tutorial will ensure you VM is
                                                            cost effective. If you are an experienced linux user comfortable
                                                            with using a bash command line you can move forward by stetting
                                                            up a new VM.
                                                        </p>
                                                      )
                                                  },
                                                  {
                                                      tabName: "New Virtual Machine",
                                                      tabIcon: Code,
                                                      tabContent: (
                                                        <p className={classes.textCenter}>
                                                            <h5>Web development is easier with <a
                                                              href="https://www.jetbrains.com/idea/?fromMenu"
                                                              target="_blank">IntelliJ</a>.
                                                                Students with a .edu can <a
                                                                  href="https://www.jetbrains.com/student/"
                                                                  target="_blank">create a free account here.</a></h5>

                                                            <Checklist
                                                              checkedIndexes={[]}
                                                              tasksIndexes={[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]}
                                                              tasks={[
                                                                  <a href="https://cloud.google.com" target="_blank">Start
                                                                      a new project on Google Cloud</a>,
                                                                  <p>Select Compute engine from the left side menu</p>,
                                                                  <p>Select Create, then name your new
                                                                      instance <small>Import options are coming
                                                                          soon</small></p>,
                                                                  <p>Under machine type you should see <b>\'1
                                                                      vCPU\'</b><br/>
                                                                      <small>You may adjust this to \'small\' to save on
                                                                          cost
                                                                      </small>
                                                                  </p>,
                                                                  <p>Under identity and API access select <b>'Allow full
                                                                      access to all Cloud
                                                                      APIs'</b></p>,
                                                                  <p>Under Firewall select <b>HTTP and/or HTTPS</b></p>,
                                                                  <p>Select Create!</p>,
                                                                  <p>While this is setting up we need to install the <a
                                                                    href="https://cloud.google.com/sdk/downloads"
                                                                    target="_blank">gcloud sdk</a>
                                                                      <small>This is required to move onto the next
                                                                          section
                                                                      </small>
                                                                  </p>

                                                              ]}
                                                            />
                                                        </p>
                                                      )
                                                  }
                                              ]}
                                            />
                                        </GridItem>
                                        <GridItem xs={12} sm={12} md={12}>
                                            <h3>
                                                Google App Engine<br/>
                                                <small>.yml configurations</small>
                                            </h3>
                                            <CustomTabs
                                              plainTabs
                                              headerColor="danger"
                                              tabs={[
                                                  {
                                                      tabName: "Caveats",
                                                      tabContent: (
                                                        <p className={classes.textCenter}>
                                                            Google Compute App engine allows you to quickly deploy in a
                                                            container environment. This
                                                            means that the installation // setup process is automated. The
                                                            app engine currently does
                                                            not allow for multiple ports to be opened on an instance.
                                                            Moreover, it does not allow
                                                            for
                                                            incoming socket connections so real-time communication with our
                                                            framework will not be
                                                            possible. For static websites or websites that do not require
                                                            instant updates, like a
                                                            messaging system, this environment would be ideal.
                                                        </p>
                                                      )
                                                  },
                                                  {
                                                      tabName: "App Engine Setup",
                                                      tabContent: (
                                                        <p className={classes.textCenter}>
                                                            <ol>
                                                                <li>Create a new <a href="cloud.google.com">Cloud
                                                                    Account</a> if you haven't already
                                                                </li>
                                                                <li>Select SQL from the left side menu</li>
                                                                <li>Select Create Instance
                                                                    <ul>
                                                                        <li>Choose MySQL and select next</li>
                                                                        <li>Select 'Choose Second Generation'</li>
                                                                    </ul>
                                                                </li>
                                                                <li>Name you instance and give a password before clicking
                                                                    next
                                                                    <ul>
                                                                        <li>The price will scale with work load.</li>
                                                                    </ul>
                                                                </li>
                                                                <li>Copy the Instance connection name for later use</li>
                                                                <li>In the search bar type <b>Credentials</b> and select the
                                                                    first suggestion
                                                                </li>
                                                                <li>Select the OAuth Consent Screen tab in the top menu
                                                                    <ul>
                                                                        <li>Fill out the required fields</li>
                                                                    </ul>
                                                                </li>
                                                                <li>Select the Credentials Tab in the top navigation</li>
                                                                <li>Select the Create credentials dropdown
                                                                    <ol>
                                                                        <li>Choose OAuth Client Id</li>
                                                                        <li>Then choose Web application and select create
                                                                        </li>
                                                                    </ol>
                                                                </li>
                                                                <li>Save your Client ID and Client Secret</li>
                                                            </ol>
                                                            <p>You now have a database (Instance connection name) and
                                                                password as well as
                                                                an authorization token for your app to connect to the google
                                                                sql server.
                                                                Now we need to Configure our .yml files.
                                                            </p>
                                                        </p>
                                                      )
                                                  },
                                                  {
                                                      tabName: "History",
                                                      tabContent: (
                                                        <p className={classes.textCenter}>
                                                            The app.yml and settings.yml must be edited before we push to
                                                            the App Engine.
                                                            Lets start with the <b>app.yml file.</b>

                                                            <pre><code>beta_settings: cloud_sql_instances: ""</code></pre>

                                                            You should see the line above in your app.yml file. If you
                                                            have not downloaded C6 yet, <a href="<?= SITE ?>Installation">click
                                                            here.</a>
                                                            Your instance connection name should go between the double
                                                            quotes. We also need
                                                            to add our connection name to the environmental variables below
                                                            this entry in
                                                            the app.yml file.
                                                            The <code>MYSQL_USER</code> and <code>MYSQL_PASSWORD</code> should
                                                            be set
                                                            with the applicable values. Our <code>MYSQL_DSN</code> has the
                                                            following format.


                                                            <pre><code>{'MYSQL_DSN: mysql:dbname=*;unix_socket=/cloudsql/*;'}</code></pre>

                                                            The first asterisk should be replaced with your desired database
                                                            name. <b>You are not
                                                            required to create the database prior to launch as CarbonPHP
                                                            will set it up
                                                            automatically.</b>
                                                            The second asterisk should be replaced with your instance
                                                            connection name.

                                                            <h4>settings.yml</h4>

                                                            The settings file will hold our OAuth information as well as out
                                                            database
                                                            information.
                                                            The <code>google_client_id</code> and <code>google_client_secret</code> should
                                                            be
                                                            filled
                                                            with you OAuth credentials respectively.
                                                            The <code>google_project_id</code> will
                                                            contain
                                                            the id in the first section of our instance connection name.
                                                            Again, this is the
                                                            value directly
                                                            before the first colon ':' in out instance connection name. All
                                                            values left should
                                                            match those
                                                            entered in our app.yml file.
                                                        </p>
                                                      )
                                                  }
                                              ]}
                                            />
                                        </GridItem>
                                    </GridContainer>
                                </GridItem>
                            </GridContainer>
                            <br/>
                            <br/>
                            <GridContainer className={classes.textCenter}>
                                <GridItem xs={12} sm={12} md={8}>
                                    <h2>Apache Server Configuration</h2>
                                    <p>
                                        After downloading the C6 framework you'll need to edit the <b>.htaccess</b> file if you
                                        are using
                                        an apache build. There are two places where <b>example.com</b> is used which should be
                                        replaced to your
                                        respective domain name. If you use HTTPS you should uncomment the section noted with you
                                        operating system,
                                        as
                                        it is an OS dependant command. The most important line is the RewriteCond that maps
                                        all requests to the index file.</p>

                                    <code>{'RewriteCond %{REQUEST_FILENAME} !-f'}
                                        {'RewriteRule ^(.*)$ /index.php [NC,L,QSA]'}</code>
                                    <p>*Google App engine does not use this file.</p>
                                </GridItem>
                            </GridContainer>
                        </div>
                    </div>

                </div>
            </div>
        );
    }
}

export default withStyles(exampleStyle)(Implementations);
