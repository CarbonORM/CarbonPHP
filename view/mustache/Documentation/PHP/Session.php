<!-- Content Header (Page header) -->
<div class="box box-solid box-default" style="margin-top: 20px">
    <div class="box-header">
        <h1 style="margin: 0">
            Session Management
        </h1>
    </div>
    <!-- Main content -->
    <div class="box-body">
        <ol class="breadcrumb">
            <li><a href="<?= SITE ?>"><i class="fa fa-dashboard"></i> APP_ROOT</a></li>
            <li class="active"> Session</li>
        </ol>
        <p class="lead">
            Session management is a difficult thing to get right. PHP defaults to storing sessions locally
            in a php.ini specified directory. Using the built in PHP function <code>session_set_save_handler()</code>
            we can modify how the persistent user data is stored. CarbonPHP chooses to use our `session` table to
            store and retrieve this information. We find this ideal for large scale applications that rely on edge
            technology, or deployment containers that do not allow local runtime file storage.
        </p>

        <p>You can disable this feature using the <a href="<?=SITE?>Options">options array</a> passed to CarbonPHP.
            Setting the <code>['SESSION'] = false;</code> will stop CarbonPHP from starting the session. This is
            not recommended as carbonphp wants start our session.</p>

        <!-- ============================================================= -->
    </div><!-- /.content -->
</div>

<div class="box box-solid box-default">
    <div class="box-header">
        <h1 style="margin: 0">Session.php</h1> <small>keep it classy ;)</small></div>
    <div class="box-body">
        <ol class="breadcrumb">
            <li><a href="<?= SITE ?>"><i class="fa fa-dashboard"></i> APP_ROOT</a></li>
            <li> Data</li>
            <li> Vendors</li>
            <li> richardtmiles</li>
            <li> carbonphp</li>
            <li> Structure</li>
            <li class="active"> Session.php</li>
        </ol>
        <pre><code><?=highlight(APP_ROOT.'Data/Vendors/richardtmiles/carbonphp/Structure/Session.php')?></code></pre>
    </div>
</div>