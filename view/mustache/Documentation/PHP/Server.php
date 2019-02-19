<!-- Content Header (Page header) -->
<div class="box box-solid box-default" style="margin-top: 20px">
    <div class="box-header with-border">
        <h1 style="margin:0;">
            Socket Communication
        </h1>
    </div>

    <!-- Main content -->
    <div class="box-body">
        <p class="lead"><b>HTML 5 Web Sockets</b> allow us to communicate real-time with users. When the user
            connects
            to the browser via HTTP or HTTPS a script is sent back instructing the browser to maintain a
            persistent connection. All network capable devices connect to the internet via a <b>Port System.</b>
            Similar to a shipping or boat dock, this website was transmitted on port 80 to your wireless
            chip.

            <br><br>The request is processed on our servers and the response is sent back to you. All <b>HTTP
                requests
                are sent through port 80 while
                HTTPS requests are usually made on port 443</b>. These Ports are reserved and standardised so every
            computer knows how to communicate with every other computer. The ports you may choose range from 1024 -
            65535
            inclusively. It's worth mentioning that some of these may <a
                    href="https://stackoverflow.com/questions/10476987/best-tcp-port-number-range-for-internal-applications">
                be taken by other applications running on your computer.</a> CarbonPHP will default to port <b>'8080'
                when using Sockets.</b>

            <br>
        </p>
        <br>
    </div>
</div>
<div class="box box-solid box-default">
    <div class="box-header">
        <h1 class="box-title">The Socket Server.php</h1>
    </div>
    <div class="box-body">
        <ol class="breadcrumb">
            <li><a href="<?= SITE ?>"><i class="fa fa-dashboard"></i> APP_ROOT</a></li>
            <li> Data</li>
            <li> Vendors</li>
            <li> richardtmiles</li>
            <li> carbonphp</li>
            <li> Structure</li>
            <li class="active"> Server.php</li>
        </ol>
        <pre><code><?=highlight_file(APP_ROOT.'Data/Vendors/richardtmiles/carbonphp/Structure/Server.php')?></code></pre>
    </div>
</div>
