<!-- Content Header (Page header) -->

<div class="box" style="margin-top: 20px">
    <div class="box-header">
        <h1>
            <b>Routing</b> Your Application
            <small>Bootstrap</small>
        </h1>
        <ol class="breadcrumb">
            <li><i class="fa fa-code"></i> <code>SERVER_ROOT . 'Application/Route.php'</code></li>

        </ol>
    </div>

    <!-- Main content -->
    <div class="box-body">
        <p class="lead">
            The bootstrap is defined in the <a href="<?=SITE?>Options">Options array you pass CarbonPHP</a>. The path listed above is
            our current bootstrap and is shown below. The value returned from this file will be passed back to the
            <code>$app();</code> method <a href="<?=SITE?>Options">called in our index</a>. The Route class requires
            a <code>defaultRoute()</code> method be defined. If no route is found and matched in this file the default
            route will be executed. If a page request requires validation or database requests, <a href="<?=SITE?>FileStructure#MVC">we strictly use the
                <code>MVC()</code> function.</a> To view the <a class="text-purple" href="<?=SITE?>Route"><b>Full Route Class click here</b></a>.
        </p>

        <!-- ============================================================= -->

        <pre>
            <?=highlight(SERVER_ROOT.'Application/Route.php')?>
        </pre>


    </div>
</div><!-- /.content -->
