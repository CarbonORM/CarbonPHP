<!-- Main content -->
<div class="content container-fluid" style="margin-top: 20px">
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4>C6 Caveats</h4>
        <p>To use the `composer` command you must have <a href="https://getcomposer.org/doc/00-intro.md" target="_blank">Composer
                installed globally.</a>
            The C6 framework uses resources from the <a href="https://bower.io" target="_blank">Bower</a> package manager, which
            downloads components that contain HTML, CSS, JavaScript, fonts or even image files.
            To use the Bower directly from Composer you must install the "fxp/composer-asset-plugin:~1.3" asset before
            running <b>composer [ require, create-project, or update ]</b>.
            The full install command is shown below.</p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <section class="content-max-width">
                <section id="installation">
                    <div class="box" style="border-top-color: rgb(96,94,166)">
                        <div class="box-header with-border bg-gray-active">
                            <h1 style="margin:0;">C6
                                <small>is for new projects</small>
                            </h1>
                        </div>

                        <div class="box-body">
                            <p class="lead">You must run <code>composer update</code> after installing with
                                either composer or git. This requires Composer be <a href="https://getcomposer.org/doc/00-intro.md#globally">installed globally</a>
                                and <code>composer global require "fxp/composer-asset-plugin:~1.3"</code> to have been run at least once. Github may prompt you to
                                create a GitHub OAuth token before continuing. If this is the case, follow the link in the dialog associated.
                            </p>


                            <h2><a href="https://github.com/RichardTMiles/C6" class="text-purple" target="_blank">#Github</a>
                                <a href="https://github.com/RichardTMiles/C6" class="btn bg-purple"
                                   target="_blank">
                                    Direct Download
                                </a></h2>
                            <p>Move to your new websites root directory in a shell. We recommend starting with an empty
                                directory. The path <b>[/var/www/html]</b> as seen below should be replaced with your
                                desired website root. </p>
                            <br>

                            <code>git clone https://github.com/RichardTMiles/C6.git /var/www/html </code>
                            <br>

                            <h2><a href="https://getcomposer.org" class="text-purple" target="_blank">#Composer</a> <a
                                        href="https://packagist.org/packages/richardtmiles/c6"
                                        class="btn bg-purple"
                                        target="_blank">View on Packagist</a></h2>
                            <p>To install and update dependancies using composer you must have the following asset
                                installed.</p>
                            <code>composer global require "fxp/composer-asset-plugin:~1.3"</code>
                            <br>
                            <code>composer create-project richardtmiles/c6 . --prefer-dist --stability dev</code>
                            <br><br>
                        </div>
                    </div>
                    <br>
                    <div class="box" style="border-top-color: rgb(96,94,166)">
                        <div class="box-header with-border bg-gray-active">
                            <h1 style="margin:0;">CarbonPHP
                                <small>is for existing projects</small>
                            </h1>
                        </div>

                        <div class="box-body">

                            <h2 class="text-purple"><a href="https://github.com/RichardTMiles/CarbonPHP"
                                                       class="text-purple"
                                                       target="_blank">#Github</a>
                                <a href="https://github.com/RichardTMiles/C6" class="btn bg-purple"
                                   target="_blank">
                                    Direct Download
                                </a>
                            </h2>
                            <p>This will also install the "psr/log" repository. Read more about <a
                                        href="http://www.php-fig.org/psr/" class="text-purple">PHP Standards
                                    Recommendations here.</a></p>
                            <br>
                            <code>git clone https://github.com/RichardTMiles/CarbonPHP.git</code>
                            <br><br>
                            <h2><a href="https://getcomposer.org" class="text-purple">#Composer</a> <a
                                        class="btn bg-purple"
                                        href="https://packagist.org/packages/richardtmiles/carbonphp"
                                        target="_blank">View on Packagist</a>
                            </h2>
                            <code>
                                composer require “richardtmiles/carbonphp:dev-master"</code>
                            <br><br>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </div>
    <!-- /.content -->
</div>