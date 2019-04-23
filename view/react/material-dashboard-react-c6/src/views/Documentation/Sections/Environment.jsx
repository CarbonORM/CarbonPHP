/*eslint-disable*/
import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
import Code from "@material-ui/icons/Code";
import Build from "@material-ui/icons/Build";
import Cloud from "@material-ui/icons/Cloud";
// core components
import downloadStyle from "assets/jss/material-kit-react/views/componentsSections/downloadStyle.jsx";
import Checklist from "components/Checklist/Checklist.jsx";
import CustomTabs from "components/CustomTabs/CustomTabs.jsx";
import BugReport from "@material-ui/core/SvgIcon/SvgIcon";
import {bugs, server, website} from "../../../variables/general";
import SnackbarContent from "components/Snackbar/SnackbarContent.jsx";
import Snackbar from "components/Snackbar/Snackbar.jsx";
import Quote from "components/Typography/Quote.jsx";


class Environment extends React.Component {
    render() {
        const {classes} = this.props;
        return (
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
                                <GridItem xs={12} sm={12} md={6}>
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
                                                        <h4>These instructions have been <a
                                                            target="_blank"
                                                            href="https://cloud.google.com/community/tutorials/setting-up-lamp">modified
                                                            from google.com</a></h4>

                                                        <h5>Web development is easier with <a
                                                            href="https://www.jetbrains.com/idea/?fromMenu"
                                                            target="_blank">IntelliJ</a>.
                                                            Students with a .edu can <a
                                                                href="https://www.jetbrains.com/student/"
                                                                target="_blank">create a free account here.</a></h5>

                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]}
                                                            tasks={[
                                                                <a href="https://cloud.google.com" target="_blank">Start
                                                                    a new project on Google Cloud</a>,
                                                                'Select Compute engine from the left side menu',
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
                                                                </p>,
                                                                <p><b>On your commuter open terminal (a shell
                                                                    prompt)</b></p>,
                                                                <p>Move to the directory where your project is located
                                                                    in your shell.</p>,
                                                                <p>Run the following command to configure your
                                                                    project<br/>
                                                                    <code>gcloud init</code><br/>You may be prompted to
                                                                    select a region and other
                                                                    options.<br/>These unique to you so select what you
                                                                    want.</p>,
                                                                <p>Type the following command but <b>replace
                                                                    instance-1</b> with the name you choose in step 3
                                                                    above.<br/>
                                                                    <code>gcloud compute ssh instance-1</code>
                                                                    <small>You should now be connected to your VM
                                                                    </small>
                                                                </p>,
                                                                <p>Type in <code>whoami</code> to findout the current
                                                                    username associated with your google ssh account
                                                                </p>,
                                                                <p>Open an sftp connection using your preferred file
                                                                    transfer program. You'll need to provide the ssh key
                                                                    the previous command
                                                                    automatically downloaded. For Mac users it was
                                                                    downloaded in your .ssh folder like below.<br/>
                                                                    <code>/Users/[your_username_here]/.ssh/Google_cloud_rsa</code>
                                                                </p>,
                                                                <p><b>Now that we can transfer files and run commands
                                                                    from our
                                                                    personal
                                                                    computer, we can move on to installing a LAMP
                                                                    stack.</b></p>

                                                            ]}
                                                        />
                                                    </p>
                                                )
                                            },
                                            {
                                                tabName: "LAMP Stack",
                                                tabIcon: Build,
                                                tabContent: (
                                                    <p className={classes.textCenter}>
                                                        <h4>These instructions have been <a
                                                            target="_blank"
                                                            href="https://cloud.google.com/community/tutorials/setting-up-lamp">modified
                                                            from google.com</a></h4>

                                                        <h5>Web development is easier with <a
                                                            href="https://www.jetbrains.com/idea/?fromMenu"
                                                            target="_blank">IntelliJ</a>.
                                                            Students with a .edu can <a
                                                                href="https://www.jetbrains.com/student/"
                                                                target="_blank">create a free account here.</a></h5>
                                                        <br/><br/>
                                                        <h4>Install Apache</h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2, 3, 4, 5]}
                                                            tasks={[
                                                                <p>The following commands should be run on our VM using
                                                                    SSH.
                                                                    Throughout this install process you may be prompted
                                                                    with<br/>
                                                                    <code>Do you want to continue? [Y/n]</code><br/>
                                                                    Should this happen, type 'Y' and hit enter.
                                                                </p>,
                                                                <p>Type the following lines in your VM <br/>
                                                                    <code>sudo apt-get update</code></p>,
                                                                <p><code>sudo apt-get install apache2</code></p>,
                                                                <p><code>sudo a2enmod headers</code></p>,
                                                                <p><code>sudo a2enmod rewrite</code></p>,
                                                                <p>Restart Apache<br/><code>sudo service apache2
                                                                    restart</code></p>,
                                                            ]}
                                                        />

                                                        <br/><br/>
                                                        <h4>Install MySql<br/><code>sudo apt-get install
                                                            mysql-server php-pear</code></h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1]}
                                                            tasks={[
                                                                <p><code>sudo mysql_secure_installation</code><br/>
                                                                    You'll be prompted for the following
                                                                    <ol>
                                                                        <li>Set a root password</li>
                                                                        <li>Remove the anonymous user</li>
                                                                        <li>Disallow root login remotely</li>
                                                                        <li>Remove test database</li>
                                                                        <li>Reload privilege tables</li>
                                                                    </ol>
                                                                </p>,
                                                                <p>
                                                                    We need to create a user other than root to connect
                                                                    with. The following commands will
                                                                    create a user named phpmyadmin with the password
                                                                    some_pass. You should change
                                                                    this to your
                                                                    desired options.
                                                                    <ol>
                                                                        <li><code>sudo mysql --user=root mysql</code>
                                                                        </li>
                                                                        <li><code>CREATE USER 'phpmyadmin'@'localhost'
                                                                            IDENTIFIED BY
                                                                            'some_pass';</code></li>
                                                                        <li><code>GRANT ALL PRIVILEGES ON *.* TO
                                                                            'phpmyadmin'@'localhost' WITH GRANT
                                                                            OPTION;</code></li>
                                                                        <li><code>FLUSH PRIVILEGES;</code></li>
                                                                        <li><code>exit</code></li>
                                                                    </ol>
                                                                </p>,
                                                            ]}/>

                                                        <br/><br/>
                                                        <h4>Install PHP 7.1</h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]}
                                                            tasks={[
                                                                <p><code>sudo apt-get install
                                                                    software-properties-common</code></p>,
                                                                <p><code>sudo apt-get install dirmngr</code></p>,
                                                                <p><code>sudo add-apt-repository ppa:ondrej/php</code>
                                                                    <ul>
                                                                        <li>This time you'll just need to hit enter</li>
                                                                    </ul>
                                                                </p>,
                                                                <p><code>sudo add-apt-repository
                                                                    ppa:ondrej/apache2</code>
                                                                    <br/>
                                                                    <code>sudo apt install apt-transport-https
                                                                        lsb-release ca-certificates</code></p>,
                                                                <p><code>sudo wget -O /etc/apt/trusted.gpg.d/php.gpg
                                                                    https://packages.sury.org/php/apt.gpg</code></p>,
                                                                <p><code>sudo sh -c 'echo "deb
                                                                    https://packages.sury.org/php/
                                                                    $(lsb_release -sc)
                                                                    main" > /etc/apt/sources.list.d/php.list'</code>
                                                                </p>,
                                                                <p><code>sudo wget -O /etc/apt/trusted.gpg.d/php.gpg
                                                                    https://packages.sury.org/php/apt.gpg</code></p>,
                                                                <code>sudo apt-get update</code>,
                                                                <code>sudo apt-get install php7.1 php7.1-common</code>,
                                                                <code>sudo apt-get install php7.1-curl php7.1-xml
                                                                    php7.1-zip
                                                                    php7.1-gd
                                                                    php7.1-mysql php7.1-mbstring</code>,
                                                                <code>sudo service apache2 reload</code>,

                                                            ]}
                                                        />
                                                        <br/><br/>
                                                        <h4>Turn on.htaccess support</h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2]}
                                                            tasks={[
                                                                <p>Turn on.htaccess support<br/>
                                                                    <code>sudo vim etc/apache2/apache2.conf</code></p>,
                                                                <p>Scroll to this line in the document <br/>
                                                                    <code>&lt;Directory /var/www/&gt;</code><br/>
                                                                    Change allow Override to <code>All</code><br/>
                                                                </p>,
                                                                <p>Install Github's Commandline Tool<br/>
                                                                    <code>sudo apt-get install git-core</code></p>,
                                                            ]}
                                                        />
                                                        <br/><br/>
                                                        <h4>Change Website Directory Permission</h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2]}
                                                            tasks={[
                                                                <p>Lets add ourselves to the Admin 'sudo'
                                                                    Group. Type the following in your ssh command
                                                                    prompt.<br/>
                                                                    <code>cd /var/www/</code></p>,
                                                                <p>Output your username<br/>
                                                                    <code><code>whoami</code></code><br/>
                                                                    Change allow Override to <code>All</code><br/>
                                                                </p>,
                                                                <p><code>sudo chown -R username html/</code><br/>
                                                                    Replace username with the user
                                                                    from the whoami function</p>,
                                                                <p>We need to change the directory permission
                                                                    so sFTP will work properly<br/>Again, replace
                                                                    username with the
                                                                    user outputted by the whoami function<br/>
                                                                    <code>sudo chown -R username html/</code><br/>
                                                                    <code>sudo chown username html/index.html</code>
                                                                </p>
                                                            ]}
                                                        />
                                                        <br/><br/>
                                                        <h4>Test installation!</h4>
                                                        <Checklist
                                                            checkedIndexes={[]}
                                                            tasksIndexes={[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17]}
                                                            tasks={[
                                                                <p>In the Cloud Platform online select Compute
                                                                    Engine from the side menu</p>,
                                                                <p>Find and select the External IP address of your
                                                                    VM</p>,
                                                                <p>You should see "Apache2 Debian Default Page"
                                                                    popup!</p>,
                                                                <p>Type <code>php -v</code> in your VM's SSH
                                                                    connection</p>,
                                                                <p>You should be greeted with <code>PHP
                                                                    7.1.13</code></p>,
                                                                <p>Navigate to your website root</p>,
                                                                <p><code>cd /var/www/html</code></p>,
                                                                <p>Remove the index.html file<br/><code>rm
                                                                    index.html</code></p>,
                                                                <p>Refreshing your browser should now give you a blank
                                                                    index listing</p>,
                                                                <p>Open a new document named index.php</p>,
                                                                <p><code>vim index.php</code><br/>vim is a file editor
                                                                    built
                                                                    into linux</p>,
                                                                <p>Hit 'i' to start inserting</p>,
                                                                <p>Type <code>{'<?php phpinfo()'}</code></p>,
                                                                <p>Save and Exit</p>,
                                                                <p>Press the escape key to stop inseting</p>,
                                                                <p>Press the colon key ':' to run a command in vim</p>,
                                                                <p>Type 'wq' without quotes and hit enter. <br/>
                                                                    This means write and quit.</p>,
                                                                <p>Refresh your browser, if you see the PHP
                                                                    logo than your done!</p>

                                                            ]}/>


                                                        <h4><a href="/5.0/Installation">Now lets download C6
                                                            in our
                                                            web
                                                            directory!</a>
                                                        </h4>
                                                    </p>
                                                )
                                            }
                                        ]}
                                    />
                                </GridItem>
                                <GridItem xs={12} sm={12} md={6}>
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
                    <GridContainer className={classes.textCenter} justify="center">
                        <GridItem xs={12} sm={12} md={8}>
                            <h2>Apache Server Configuration</h2>
                            <p className="lead">
                                Apache configurations are very touchy and may cause unexpected errors.
                            </p>
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

                            <p>If all else fails, attempt using only the two lines above.
                                <b>Google App engine does not use this file.</b></p>
                        </GridItem>
                    </GridContainer>
                </div>
            </div>
        );
    }
}

export default withStyles(downloadStyle)(Environment);
