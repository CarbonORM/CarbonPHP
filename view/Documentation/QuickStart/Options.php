

<div class="col-md-12">
    <div class="box box-solid box-default" style="margin-top: 20px">
        <div class="box-header with-border">
            <h1 style="margin:0;">Configuration Options <small>Config.php</small></h1>
        </div>
        <div class="box-body">
            <p class="lead"><a href="">Carbonphp.com's config file</a> does not utilize all options CarbonPHP provides.
                Unneeded options can be removed. CarbonPHP can
                be configured without parameters passed. This however
                is discouraged and further requests may cause unexpected behavior.
                The file below has all available options.
            </p>
            <br>
            <pre style="overflow-x: scroll"><?= highlight(SERVER_ROOT . 'Application/View/Documentation/CodeExamples/Configuration.php') ?></pre>
        </div>
    </div>
    <div class="box box-solid box-warning" style="margin-top: 20px">
        <div class="box-header with-border">
            <h1 style="margin:0;">index.php does it all</h1>
        </div>
        <div class="box-body" style="overflow-x: scroll">
            <p class="lead">When you download C6 you get an exact copy of this website.
            The index file and options passed can be modified to suit your unique development.
            CarbonPHP will handle all requests passed to the index, including serving static files
                with appropriate headers and mime type.</p>
            <br>
            ####################### SERVER_ROOT . index.php
            <pre style="overflow-x: scroll"><?= highlight(SERVER_ROOT . 'index.php') ?>
            </pre>
        </div>
    </div>

    <div class="box box-solid box-default">
        <div class="box-header">
            <h1 style="margin:0">Carbonphp.com's Configuration</h1>
        </div>
        <div class="box-body">
            <pre><code><?=highlight(SERVER_ROOT.'Application/Config/Config.php')?></code></pre>
        </div>
    </div>


</div>