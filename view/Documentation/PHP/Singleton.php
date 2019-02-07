<!-- Content Header (Page header) -->
<div class="box box-solid box-default" style="margin-top: 20px">
    <div class="box-header">
        <h1 style="margin:0">
            Singleton
            <small>its for javascript natives...</small>
        </h1>
    </div>

    <!-- Main content -->
    <div class="box-body">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            It should be noted that while Singleton uses a
            <a href="https://en.wikipedia.org/wiki/Singleton_pattern">singleton pattern</a> you should not use this
            feature
            within a <b>single execution session</b>. Doing so would be considered an anti-pattern. If you're think of
            doing
            this, conciser making your class static and public.
        </div>
        <p class="lead">
            Singleton is a <a href="http://php.net/manual/en/language.oop5.traits.php">mega trait</a> designed to give
            you
            a new semantic style that resembles javascript and allows you to maintain object state between server requests.
        </p>
        <h2>When should I use Singleton?</h2>
        <p>You can attach this trait to any class that does not already have Singleton present in class parents (i.e.
            extended classes).
            The Singleton pattern does not automatically invoke or construct. You must add the <code>const Singleton = true;</code>
            constant and invoke the class object with <code>ExampleClass::getInstance();</code> to achieve this.
        </p>
        <p>Private is more like public in a singleton, this gains in portability
            defined private are accessible to the global scope and overridabillity
            at call time. You may have just cringed if unfamiliar with the php language. However,
            <a href="https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/">I recommend you read this article.</p>

        <h2>Usage</h2>
        <div class="box-body bg-gray-light">
            <!-- ============================================================= -->
            <div class="box-group" id="accordion">
                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                <div class="panel box box-solid box-default">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            <a class="text-black collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false">
                                #ExampleClass.php
                            </a>
                        </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in">
                        <div class="box-body">
                            <p>The <code>Carbon\Carbon();</code> class wants to start our session <a href="<?=SITE?>Session">and will do it by default</a>. This is initiated in our index file so you do not need
                            to run the <code>session_start();</code> function described below. Your session must be active to use the <code>const Singleton = true;</code> directive.
                            When our class destructs its variables will be stored until the next request. Consider the following class definition and how it changes in each preceding
                                file.</p>
                            <pre><code><?=highlight(APP_ROOT.'Application/View/Documentation/CodeExamples/Singleton/example1.txt')?></code></pre>
                        </div>
                    </div>
                </div>
                <div class="panel box box-solid box-info">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            <a class="text-black" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true">
                                #File1.php
                            </a>
                        </h4>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse">
                        <div class="box-body">
                            <p>So as an example, the first request to our server will run this file.</p>
                            <pre><code><?=highlight(APP_ROOT.'Application/View/Documentation/CodeExamples/Singleton/example2.txt')?></code></pre>
                        </div>
                    </div>
                </div>
                <div class="panel box box-solid box-default">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            <a class="text-black" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="true">
                                #File2.php
                            </a>
                        </h4>
                    </div>
                    <div id="collapseThree" class="panel-collapse collapse">
                        <div class="box-body">
                            <p>The second request to our server will run this file.</p>
                            <pre><code><?=highlight(APP_ROOT.'Application/View/Documentation/CodeExamples/Singleton/example3.txt')?></code></pre>
                        </div>
                    </div>
                </div>
                <div class="panel box box-solid box-info">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            <a class="text-black" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="true">
                                #File3.php
                            </a>
                        </h4>
                    </div>
                    <div id="collapseFour" class="panel-collapse collapse">
                        <div class="box-body">
                            <p>Finally, the third request to our server will run this file.</p>
                            <pre><code><?=highlight(APP_ROOT.'Application/View/Documentation/CodeExamples/Singleton/example4.txt')?></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="box box-solid box-default">
    <div class="box-header">
        <h1 style="margin: 0">The Code
            <small>get it?</small>
        </h1>
    </div>
    <div class="box-body">
        <ol class="breadcrumb">
            <li><a href="<?= SITE ?>"><i class="fa fa-dashboard"></i> APP_ROOT</a></li>
            <li> Data</li>
            <li> Vendors</li>
            <li> richardtmiles</li>
            <li> carbonphp</li>
            <li> Structure</li>
            <li class="active"> Singleton.php</li>
        </ol>
        <pre><code><?= highlight(APP_ROOT . 'Data/Vendors/richardtmiles/carbonphp/Structure/Singleton.php') ?></code></pre>
    </div>
</div>
