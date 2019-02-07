<!-- Content Header (Page header) -->
<div class="callout callout-info" style="margin-top: 20px">
    <h4>Test Locally!</h4>

    <p>Setting up a production environment allows your app to be seen by the outside world.
        Our framework is capable of running on your local machine using the development server built into PHP.
        After downloading the C6 framework, you can view it (this website) locally using the following command.
    </p>
    <code>php -S localhost:8080 index.php</code>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-default">
            <div class="box-header bg-gray-active">
                <h1 style="margin: 0">
                    Google Compute Engine
                    <small>virtual machines</small>
                </h1>
            </div>

            <!-- Main content -->
            <div class="box-body bg-gray-light">
                <!-- ============================================================= -->
                <div class="box-group" id="accordion">
                    <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                    <div class="panel box box-solid box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordion"
                                   href="#collapseOne">
                                    #Recommended Environment
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse in">
                            <div class="box-body">
                                Google Compute Engine is a virtual machine, or VM, configurable to scale
                                automatically as traffic spikes on your servers. The platform allows
                                you to have root access and open multiple ports so features like Websockets
                                will are compatible. You may use any os
                                provided in the Compute interface, but it is recommended you start from
                                a new instance. This tutorial will ensure you VM is cost effective.
                                If you are an experienced linux user comfortable with using a bash command
                                line you can move forward by stetting up a new VM.
                            </div>
                        </div>
                    </div>
                    <div class="panel box box-solid box-info">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordion"
                                   href="#collapseTwo">
                                    #Setting up and connecting to a new VM
                                </a>
                            </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse">
                            <div class="box-body">
                                <h4>These instructions have been <a
                                            href="https://cloud.google.com/community/tutorials/setting-up-lamp">modified
                                        from google.com</a></h4>

                                <h5>Web development is easier with <a href="https://www.jetbrains.com/idea/?fromMenu"
                                                                      target="_blank">IntelliJ</a>.
                                    Students with a .edu can <a href="https://www.jetbrains.com/student/"
                                                                target="_blank">create a free account here.</a></h5>
                                <ol>
                                    <li>Start a new project on <a href="https://cloud.google.com" target="_blank">Google
                                            Cloud</a></li>
                                    <li>Select Compute engine from the left side menu</li>
                                    <li>Select Create, then name your new instance
                                        <ul>
                                            <li>Import options are coming soon</li>
                                        </ul>
                                    </li>
                                    <li>Under machine type you should see <b>'1 vCPU'</b>
                                        <ul>
                                            <li>You may adjust this to 'small' to save on cost</li>
                                        </ul>
                                    </li>
                                    <li>Under identity and API access select <b>'Allow full access to all Cloud
                                            APIs'</b></li>
                                    <li>Under Firewall select <b>HTTP and/or HTTPS</b></li>
                                    <li>Select Create!</li>
                                    <li>While this is setting up we need to install the <a
                                                href="https://cloud.google.com/sdk/downloads" target="_blank">gcloud
                                            sdk</a>
                                        <ul>
                                            <li>This is required to move onto the next section</li>
                                        </ul>
                                    </li>
                                </ol>
                                <h5><b>On your commuter open terminal (a shell prompt)</b></h5>
                                <ol>
                                    <li>Move to the directory where your project is located.</li>
                                    <li>Run the following command to configure your project
                                        <ul>
                                            <li><code>gcloud init</code></li>
                                            <li>You may be prompted to select a region and other options</li>
                                            <li>These unique to you so select what you want</li>
                                        </ul>
                                    </li>
                                    <li>Type the following command but <b>replace instance-1</b> with the name you
                                        choose
                                        in step 3 above.
                                        <ul>
                                            <li><code>gcloud compute ssh instance-1</code></li>
                                            <li>You should now be connected to your VM</li>
                                        </ul>
                                    </li>
                                    <li>Type in <code>whoami</code> to findout the current username associated
                                        with your google ssh account
                                        <ul>
                                            <li>This will be our username in the next step</li>
                                        </ul>
                                    </li>
                                    <li>Open an sftp connection using your preferred file transfer program. You'll
                                        need to provide the ssh key the previous command downloaded. For Mac users
                                        it was downloaded in your .ssh folder like below.
                                        <ul>
                                            <li><code>/Users/[your_username_here]/.ssh/Google_cloud_rsa</code></li>
                                        </ul>
                                    </li>
                                </ol>
                                <h5><b>Now that we can transfer files and run commands from our personal
                                        computer, we can move on to installing a LAMP stack.</b></h5>
                            </div>
                        </div>
                    </div>
                    <div class="panel box box-solid box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordion"
                                   href="#collapseThree">
                                    #Installing the LAMP Stack and PHP 7.1
                                </a>
                            </h4>
                        </div>
                        <div id="collapseThree" class="panel-collapse collapse">
                            <div class="box-body">
                                <h4>Install Apache</h4>
                                <p>The following commands should be run on our VM using SSH.
                                    Throughout this install process you may be prompted with</p>
                                <code>Do you want to continue? [Y/n]</code>
                                <p>Should this happen, type 'Y' and hit enter.</p>
                                <ol>
                                    <li><code>sudo apt-get update</code></li>
                                    <li><code>sudo apt-get install apache2</code></li>
                                    <li><code>sudo a2enmod headers</code></li>
                                    <li><code>sudo a2enmod rewrite</code></li>
                                    <li>Restart Apache
                                        <ul>
                                            <li><code>sudo service apache2 restart</code></li>
                                        </ul>
                                    </li>
                                </ol>
                                <h4>Install MySql</h4>
                                <ol>
                                    <li><code>sudo apt-get install mysql-server php-pear</code></li>
                                    <li><code>sudo mysql_secure_installation</code>
                                        <ul>
                                            <li>You'll be prompted for the following</li>
                                        </ul>
                                        <ol>
                                            <li>Set a root password</li>
                                            <li>Remove the anonymous user</li>
                                            <li>Disallow root login remotely</li>
                                            <li>Remove test database</li>
                                            <li>Reload privilege tables</li>
                                        </ol>
                                    </li>
                                    <li>We need to create a user other than root to connect with. The following commands
                                        will
                                        create a user named phpmyadmin with the password some_pass. You should change
                                        this to your
                                        desired options.
                                        <ol>
                                            <li><code>sudo mysql --user=root mysql</code></li>
                                            <li><code>CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY
                                                    'some_pass';</code></li>
                                            <li><code>GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost' WITH GRANT
                                                    OPTION;</code></li>
                                            <li><code>FLUSH PRIVILEGES;</code></li>
                                            <li><code>exit</code></li>
                                        </ol>
                                    </li>
                                </ol>
                                <h4>Install PHP 7.1</h4>
                                <ol>
                                    <li><code>sudo apt-get install software-properties-common</code></cod></li>
                                    <li><code>sudo apt-get install dirmngr</code></li>
                                    <li><code>sudo add-apt-repository ppa:ondrej/php</code>
                                        <ul>
                                            <li>This time you'll just need to hit enter</li>
                                        </ul>
                                    </li>
                                    <li><code>sudo add-apt-repository ppa:ondrej/apache2</code></li>
                                    <li><code>sudo apt install apt-transport-https lsb-release ca-certificates</code>
                                    </li>
                                    <li><code>sudo wget -O /etc/apt/trusted.gpg.d/php.gpg
                                            https://packages.sury.org/php/apt.gpg</code></li>
                                    <li><code>sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc)
                                            main" > /etc/apt/sources.list.d/php.list'</code></li>
                                    <li><code>sudo wget -O /etc/apt/trusted.gpg.d/php.gpg
                                            https://packages.sury.org/php/apt.gpg</code></li>
                                    <li><code>sudo apt-get update</code></li>
                                    <li><code>sudo apt-get install php7.1 php7.1-common</code></li>
                                    <li><code>sudo apt-get install php7.1-curl php7.1-xml php7.1-zip php7.1-gd
                                            php7.1-mysql php7.1-mbstring</code></li>
                                    <li><code>sudo service apache2 reload</code></li>
                                </ol>
                                <h4>Turn on .htaccess support</h4>
                                <ol>
                                    <li><code>sudo vim etc/apache2/apache2.conf</code></li>
                                    <li>Scroll to this line in the document <code>&lt;Directory /var/www/&gt;</code>
                                        <ul>
                                            <li>Change allow Override to <code>All</code></li>
                                        </ul>
                                    </li>
                                </ol>

                                <h4>Install Github's Commandline Tool</h4>
                                <ol>
                                    <li><code>sudo apt-get install git-core</code></li>
                                    <ul>
                                        <li>That was easy!</li>
                                    </ul>
                                </ol>

                                <h4>Change Website Directory Permission</h4>
                                <ol>
                                    <li>Lets add ourselves to the Admin 'sudo' Group. Type the
                                        following in your ssh command prompt.
                                        <ol>
                                            <li><code>cd /var/www/</code></li>
                                            <li><code>whoami</code>
                                                <ul>
                                                    <li>This will output your username</li>
                                                </ul>
                                            </li>
                                            <li><code>sudo chown -R username html/</code>
                                                <ul>
                                                    <li>Replace username with the user outputted
                                                        by the whoami function
                                                    </li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </li>
                                    <li>We need to change the directory permission so sFTP will work properly
                                        <ul>
                                            <li>Again, replace username with the user outputted
                                                by the whoami function
                                            </li>
                                        </ul>
                                        <ol>
                                            <li><code>sudo chown -R username html/</code>

                                            </li>
                                            <li><code>sudo chown username html/index.html</code></li>
                                        </ol>
                                    </li>
                                </ol>

                                <h4>Test installation!</h4>
                                <ol>
                                    <li>In the Cloud Platform online select Compute Engine from the side menu</li>
                                    <li>Find and select the External IP address of your VM
                                        <ul>
                                            <li>You should see "Apache2 Debian Default Page" popup!</li>
                                        </ul>
                                    </li>
                                    <li>Type <code>php -v</code> in your VM's SSH connection
                                        <ul>
                                            <li>You should be greeted with <code>PHP 7.1.13</code></li>
                                        </ul>
                                    </li>
                                    <li>Navigate to your website root
                                        <ul>
                                            <li><code>cd /var/www/html</code></li>
                                        </ul>
                                    </li>
                                    <li>Remove the index.html file
                                        <ul>
                                            <li><code>rm index.html</code></li>
                                            <li>Refreshing your browser should now give you a blank index listing</li>
                                        </ul>
                                    </li>
                                    <li>Open a new document named index.php
                                        <ol>
                                            <li><code>vim index.php</code>
                                                <ul>
                                                    <li>vim is a file editor built into linux</li>
                                                </ul>
                                            </li>
                                            <li>Hit 'i' to start inserting</li>
                                            <li>Type <code><?= htmlspecialchars('<?php phpinfo();') ?></code></li>
                                            <li>Save and Exit
                                                <ol>
                                                    <li>Press the escape key to stop inseting</li>
                                                    <li>Press the colon key ':' to run a command in vim</li>
                                                    <li>Type 'wq' without quotes and hit enter. This means write and
                                                        quit.
                                                    </li>
                                                </ol>
                                            </li>
                                        </ol>
                                    </li>
                                    <li>Refresh your browser, if you see the PHP logo than your done!</li>
                                </ol>
                                <h4><a href="<?= SITE ?>Installation">Now lets download C6 in our web directory!</a>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>


            </div><!-- /.content-->
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-solid box-default">
            <div class="box-header bg-gray-active">
                <h1 style="margin: 0">
                    Google App Engine
                    <small>.yml configurations</small>
                </h1>
            </div>

            <!-- Main content -->
            <div class="box-body bg-gray-light">
                <!-- ============================================================= -->
                <div class="box-group" id="accordionTwo">
                    <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                    <div class="panel box box-solid box-info">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordionTwo"
                                   href="#oneCollapse">
                                    #Caveats
                                </a>
                            </h4>
                        </div>
                        <div id="oneCollapse" class="panel-collapse collapse in">
                            <div class="box-body">
                                Google Compute App engine allows you to quickly deploy in a container environment. This
                                means that the installation // setup process is automated. The app engine currently does
                                not allow for multiple ports to be opened on an instance. Moreover, it does not allow
                                for
                                incoming socket connections so real-time communication with our framework will not be
                                possible. For static websites or websites that do not require instant updates, like a
                                messaging system, this environment would be ideal.
                            </div>
                        </div>
                    </div>
                    <div class="panel box box-solid box-default">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordionTwo"
                                   href="#twoCollapse">
                                    #App Engine Setup
                                </a>
                            </h4>
                        </div>
                        <div id="twoCollapse" class="panel-collapse collapse">
                            <div class="box-body">
                                <ol>
                                    <li>Create a new <a href="cloud.google.com">Cloud Account</a> if you haven't already
                                    </li>
                                    <li>Select SQL from the left side menu</li>
                                    <li>Select Create Instance
                                        <ul>
                                            <li>Choose MySQL and select next</li>
                                            <li>Select 'Choose Second Generation'</li>
                                        </ul>
                                    </li>
                                    <li>Name you instance and give a password before clicking next
                                        <ul>
                                            <li>The price will scale with work load.</li>
                                        </ul>
                                    </li>
                                    <li>Copy the Instance connection name for later use</li>
                                    <li>In the search bar type <b>Credentials</b> and select the first suggestion</li>
                                    <li>Select the OAuth Consent Screen tab in the top menu
                                        <ul>
                                            <li>Fill out the required fields</li>
                                        </ul>
                                    </li>
                                    <li>Select the Credentials Tab in the top navigation</li>
                                    <li>Select the Create credentials dropdown
                                        <ol>
                                            <li>Choose OAuth Client Id</li>
                                            <li>Then choose Web application and select create</li>
                                        </ol>
                                    </li>
                                    <li>Save your Client ID and Client Secret</li>
                                </ol>
                                <p>You now have a database (Instance connection name) and password as well as
                                    an authorization token for your app to connect to the google sql server.
                                    Now we need to Configure our .yml files.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel box box-solid box-info">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a class="text-black" data-toggle="collapse" data-parent="#accordionTwo"
                                   href="#threeCollapse">
                                    #Configuring the .yml
                                </a>
                            </h4>
                        </div>
                        <div id="threeCollapse" class="panel-collapse collapse">
                            <div class="box-body">
                                <p>
                                    The app.yml and settings.yml must be edited before we push to the App Engine.
                                    Lets start with the <b>app.yml file.</b></p>

                                <pre><code>beta_settings: cloud_sql_instances: ""</code></pre>

                                <p>You should see the line above in your app.yml file. If you
                                    have not downloaded C6 yet, <a href="<?= SITE ?>Installation">click here.</a>
                                    Your instance connection name should go between the double quotes. We also need
                                    to add our connection name to the environmental variables below this entry in
                                    the app.yml file.
                                    The <code>MYSQL_USER</code> and <code>MYSQL_PASSWORD</code> should be set
                                    with the applicable values. Our <code>MYSQL_DSN</code> has the following format.
                                </p>

                                <pre><code>MYSQL_DSN: mysql:dbname=*;unix_socket=/cloudsql/*;</code></pre>

                                <p>The first asterisk should be replaced with your desired database name. <b>You are not
                                        required to create the database prior to launch as CarbonPHP will set it up
                                        automatically.</b>
                                    The second asterisk should be replaced with your instance connection name.</p>

                                <h4>settings.yml</h4>

                                <p>The settings file will hold our OAuth information as well as out database
                                    information.
                                    The <code>google_client_id</code> and <code>google_client_secret</code> should be
                                    filled
                                    with you OAuth credentials respectively. The <code>google_project_id</code> will
                                    contain
                                    the id in the first section of our instance connection name. Again, this is the
                                    value directly
                                    before the first colon ':' in out instance connection name. All values left should
                                    match those
                                    entered in our app.yml file.</p>

                            </div>
                        </div>
                    </div>
                </div>


            </div><!-- /.content-->
        </div>
    </div>
</div>


<div class="box box-solid box-default">
    <div class="box-header">
        <h1 style="margin: 0">
            Apache Server Configuration
            <small>.htaccess file</small>
        </h1>
    </div>

    <!-- Main content -->
    <div class="box-body">
        <p class="lead">
            Apache configurations are very touchy and may cause unexpected errors.
        </p>
        <p>
            After downloading the C6 framework you'll need to edit the <b>.htaccess</b> file if you are using
            an apache build. There are two places where <b>example.com</b> is used which should be replaced to your
            respective domain name. If you use HTTPS you should uncomment the section noted with you operating system,
            as
            it is an OS dependant command. The most important line is the RewriteCond that maps
            all requests to the index file.</p>
        <pre>
            <code>RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ /index.php [NC,L,QSA]</code></pre>

        <p>If all else fails, attempt using only the two lines above.
            <b>Google App engine does not use this file.</b></p>


        <!-- ============================================================= -->

    </div><!-- /.content-->
</div>