<?php

const COMPOSER = 'Data' . DS . 'vendor' . DS;
const TEMPLATE =  COMPOSER . 'almasaeed2010' . DS . 'adminlte' . DS;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= SITE_TITLE ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- PJAX Content Control -->
    <meta http-equiv="x-pjax-version" content="<?= $_SESSION['X_PJAX_Version'] ?>">
    <!-- REQUIRED STYLE SHEETS -->
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?= $this->versionControl( TEMPLATE . "bower_components/bootstrap/dist/css/bootstrap.min.css" ) ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $this->versionControl( TEMPLATE ."dist/css/AdminLTE.min.css" ) ?>">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
        folder instead of downloading all of them to reduce the load. -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."dist/css/skins/_all-skins.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- DataTables.Bootstrap -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <!-- iCheck -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."plugins/iCheck/square/blue.css" ); ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Ionicons -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/Ionicons/css/ionicons.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Back color -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."dist/css/skins/skin-green.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Multiple input dynamic form -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/select2/dist/css/select2.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Check Ratio Box -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."plugins/iCheck/flat/blue.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- I dont know but keep it -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/morris.js/morris.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- fun ajax refresh -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE . "plugins/pace/pace.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Jquery -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/jvectormap/jquery-jvectormap.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- datepicker -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">

    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/bootstrap-daterangepicker/daterangepicker.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."plugins/timepicker/bootstrap-timepicker.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Wysihtml -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <!-- Font Awesome -->
    <link rel="preload" href="<?= $this->versionControl( TEMPLATE ."bower_components/font-awesome/css/font-awesome.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">

    <link rel="preload" href="<?= $this->versionControl( "Public/css/documents.css" ) ?>" as="style" onload="this.rel='stylesheet'">

    <script>
        var AdminLTEOptions = {
            //Add slimscroll to navbar menus
            //This requires you to load the slimscroll plugin
            //in every page before app.js
            navbarMenuSlimscroll: true,
            navbarMenuSlimscrollWidth: "3px", //The width of the scroll bar
            navbarMenuHeight: "200px", //The height of the inner menu
            //General animation speed for JS animated elements such as box collapse/expand and
            //sidebar treeview slide up/down. This options accepts an integer as milliseconds,
            //'fast', 'normal', or 'slow'
            animationSpeed: 'normal',
            //Sidebar push menu toggle button selector
            sidebarToggleSelector: "[data-toggle='push-menu']",
            //Activate sidebar push menu
            sidebarPushMenu: false,
            //Activate sidebar slimscroll if the fixed layout is set (requires SlimScroll Plugin)
            sidebarSlimScroll: true,
            //Enable sidebar expand on hover effect for sidebar mini
            //This option is forced to true if both the fixed layout and sidebar mini
            //are used together
            sidebarExpandOnHover: false,
            //BoxRefresh Plugin
            enableBoxRefresh: true,
            //Bootstrap.js tooltip
            enableBSToppltip: true,
            BSTooltipSelector: "[data-toggle='tooltip']",
            //Enable Fast Click. Fastclick.js creates a more
            //native touch experience with touch devices. If you
            //choose to enable the plugin, make sure you load the script
            //before AdminLTE's app.js
            enableFastclick: true,
            //Control Sidebar Options
            enableControlSidebar: true,
            controlSidebarOptions: {
                //Which button should trigger the open/close event
                toggleBtnSelector: "[data-toggle='control-sidebar']",
                //The sidebar selector
                selector: ".control-sidebar",
                //Enable slide over content
                slide: true
            },
            //Box Widget Plugin. Enable this plugin
            //to allow boxes to be collapsed and/or removed
            enableBoxWidget: true,
            //Box Widget plugin options
            boxWidgetOptions: {
                boxWidgetIcons: {
                    //Collapse icon
                    collapse: 'fa-minus',
                    //Open icon
                    open: 'fa-plus',
                    //Remove icon
                    remove: 'fa-times'
                },
                boxWidgetSelectors: {
                    //Remove button selector
                    remove: '[data-widget="remove"]',
                    //Collapse button selector
                    collapse: '[data-widget="collapse"]'
                }
            },
            //Direct Chat plugin options
            directChat: {
                //Enable direct chat by default
                enable: true,
                //The button to open and close the chat contacts pane
                contactToggleSelector: '[data-widget="chat-pane-toggle"]'
            },
            //Define the set of colors to use globally around the website
            colors: {
                lightBlue: "#3c8dbc",
                red: "#f56954",
                green: "#006a31",
                aqua: "#00c0ef",
                yellow: "#f39c12",
                blue: "#0073b7",
                navy: "#001F3F",
                teal: "#39CCCC",
                olive: "#3D9970",
                lime: "#01FF70",
                orange: "#FF851B",
                fuchsia: "#F012BE",
                purple: "#8E24AA",
                maroon: "#D81B60",
                black: "#222222",
                gray: "#d2d6de"
            },
            //The standard screen sizes that bootstrap uses.
            //If you change these in the variables.less file, change
            //them here too.
            screenSizes: {
                xs: 480,
                sm: 768,
                md: 992,
                lg: 1200
            }
        };


        /*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
        !function (a) {
            "use strict";
            var b = function (b, c, d) {
                function e(a) {
                    return h.body ? a() : void setTimeout(function () {
                        e(a)
                    })
                }

                function f() {
                    i.addEventListener && i.removeEventListener("load", f), i.media = d || "all"
                }

                var g, h = a.document, i = h.createElement("link");
                if (c) g = c; else {
                    var j = (h.body || h.getElementsByTagName("head")[0]).childNodes;
                    g = j[j.length - 1]
                }
                var k = h.styleSheets;
                i.rel = "stylesheet", i.href = b, i.media = "only x", e(function () {
                    g.parentNode.insertBefore(i, c ? g : g.nextSibling)
                });
                var l = function (a) {
                    for (var b = i.href, c = k.length; c--;) if (k[c].href === b) return a();
                    setTimeout(function () {
                        l(a)
                    })
                };
                return i.addEventListener && i.addEventListener("load", f), i.onloadcssdefined = l, l(f), i
            };
            "undefined" != typeof exports ? exports.loadCSS = b : a.loadCSS = b
        }("undefined" != typeof global ? global : this);

        /*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
        !function (a) {
            if (a.loadCSS) {
                var b = loadCSS.relpreload = {};
                if (b.support = function () {
                        try {
                            return a.document.createElement("link").relList.supports("preload")
                        } catch (b) {
                            return !1
                        }
                    }, b.poly = function () {
                        for (var b = a.document.getElementsByTagName("link"), c = 0; c < b.length; c++) {
                            var d = b[c];
                            "preload" === d.rel && "style" === d.getAttribute("as") && (a.loadCSS(d.href, d, d.getAttribute("media")), d.rel = null)
                        }
                    }, !b.support()) {
                    b.poly();
                    var c = a.setInterval(b.poly, 300);
                    a.addEventListener && a.addEventListener("load", function () {
                        b.poly(), a.clearInterval(c)
                    }), a.attachEvent && a.attachEvent("onload", function () {
                        a.clearInterval(c)
                    })
                }
            }
        }(this);

        /*! loadJS: load a JS file asynchronously. [c]2014 @scottjehl, Filament Group, Inc. (Based on http://goo.gl/REQGQ by Paul Irish). Licensed MIT */
        (function (w) {
            var loadJS = function (src, cb) {
                "use strict";
                var ref = w.document.getElementsByTagName("script")[0];
                var script = w.document.createElement("script");
                script.src = src;
                script.async = true;
                ref.parentNode.insertBefore(script, ref);
                if (cb && typeof(cb) === "function") {
                    script.onload = cb;
                }
                return script;
            };
            // commonjs
            if (typeof module !== "undefined") {
                module.exports = loadJS;
            }
            else {
                w.loadJS = loadJS;
            }
        }(typeof global !== "undefined" ? global : this));// Hierarchical PJAX Request


        function IsJsonString(str) {
            try {
                return JSON.parse(str);
            } catch (e) {
                return 0;
            }
        }

        function MustacheWidgets(data, url = '') {
            if (data !== null) {
                if (typeof data === "string") data = IsJsonString(data);
                if (data.hasOwnProperty('Mustache') && data.hasOwnProperty('widget')) {
                    console.log('Valid Mustache $(' + data.widget + ')\n');
                    $.get(data.Mustache, function (template) {
                        Mustache.parse(template);
                        $(data.widget).html(Mustache.render(template, data));
                        if (data.hasOwnProperty('scroll')) {
                            $(data.scroll).slimscroll({start: data.scrollTo});
                        }
                    })
                } else {
                    console.log("Bad Trimmers :: ");
                    console.log(data);
                }
            } else {
                console.log('Bad Handlebar :: ' + data);
                if (typeof data === "object")
                    if (url !== '') {
                        console.log('Attempting Socket');
                        setTimeout(function () {            // wait 2 seconds
                            $.fn.startApplication(url);
                        }, 2000);
                    }
            }
        }

    </script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- ./wrapper -->

</head>
<style>
    body {
        background-color: black;
    }

    .content-wrapper, .stats-wrap {
        /* This image will be displayed fullscreen
        /Public/StatsCoach/img/augusta-master.jpg
        http://site.rockbottomgolf.com/blog_images/Hole%2012%20-%20Imgur.jpg
        */
        /*  opacity: .7;
            min-height: 100%;
            background: url('http://cdn1.vox-cdn.com/imported_assets/2133771/166337111.0.jpg') no-repeat fixed;
            background-position: center; /* Chrome

                scroll-x  Ensure the html element always takes up the full height of the browser window */
        /* The Magic
        background-size: cover;
        */

    }
</style>
<body class="skin-black-light sidebar-mini" style="height: auto; min-height: 100%;">
<div class="wrapper" style="height: auto; min-height: 100%;">

    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="/" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>6</b>C</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>Carbon</b>PHP Docs</span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="btn-group" style="padding-left: 5px">
                <a href="#" class="btn btn-primary navbar-btn dropdown-toggle" data-toggle="dropdown">
                    v1.1.* <b class="caret"></b>
                </a>
                <ul class="dropdown-menu dropdown-menu-xs">
                    <li><a href="#">Just Wait ;)</a></li>
                </ul>
            </div>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li><a href="<?=SITE?>">CarbonPHP</a></li>
                    <li><a href="https://Stats.Coach/">Stats.Coach</a></li>
                </ul>
            </div>
        </nav>
    </header><!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

            <!-- search form -->
            <form method="get" class="sidebar-form" id="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..." id="search-input">
                    <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
                </div>
            </form>
            <!-- /.search form -->

            <!-- Sidebar Menu -->
            <ul class="sidebar-menu tree" data-widget="tree">
                <li class="header">TABLE OF CONTENTS</li>
                <li class="active">
                    <a href="<?=SITE?>CarbonPHP"><i class="fa fa-microchip"></i> <span>CarbonPHP</span></a>
                </li>
                <li>
                    <a href="<?=SITE?>Installation"><i class="fa fa-code-fork"></i> <span>Installation</span></a>
                </li>
                <li>
                    <a href="<?=SITE?>Dependencies"><i class="fa fa-handshake-o"></i>
                        <span>Dependencies</span></a></li>
                <li class="treeview">
                    <a href="#"><i class="fa fa-th"></i> <span>Quick Start</span>
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="FileStructure"><i class="fa fa-circle-o"></i>File Structure</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i>.htaccess</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i>Index &amp; Options</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i>Bootstrap</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i>Wrapper</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i>Parallel Processing</a>
                        </li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#"><i class="fa fa-code"></i> <span>PHP Applications</span>
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Overview</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Database &amp; Entities</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Request</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Route</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Server</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Session</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> Singleton</a>
                        </li>
                        <li>
                            <a href=""><i class="fa fa-circle-o"></i> View</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href=""><i class="fa fa-chrome"></i><span>OS Support</span></a>
                </li>
                <li>
                    <a href="Upgrade Guide"><i class="fa fa-hand-o-up"></i><span>Upgrade Guide</span></a>
                </li>
                <li>
                    <a href="Implementations"><i class="fa fa-bookmark-o"></i><span>Implementations</span></a>
                </li>
                <li>
                    <a href="Support"><i class="fa fa-question-circle-o"></i><span>Support</span></a>
                </li>
                <li>
                    <a href="License"><i class="fa fa-file-text-o"></i> <span>License</span></a>
                </li>
                <li class="bg-green">
                    <a href="AdminLTE"><i class="fa fa-star-o" style="color: rgb(255, 255, 255);"></i><span style="color: rgb(255, 255, 255);">AdminLTE</span></a>
                </li>
            </ul>
            <!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
    </aside>


    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="min-height: 520px;">
        <div id="alert"></div>
        <article>
            <!-- /.content -->
        </article>
        <div class="clearfix"></div>

    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs"><b>Version</b> 1.1.*</div>
        <!-- Default to the left -->
        <strong>Copyright © 2017 <a href="/">Miles.Systems</a>.</strong> All rights reserved.
    </footer></div>
<!-- ./wrapper -->
<script>
    //-- Stats Coach Bootstrap Alert -->
    function bootstrapAlert(message, level) {
        if(level == null) level = 'info';
        var container = document.getElementById('alert'),
            node = document.createElement("DIV"), text;

        text = level.charAt(0).toUpperCase() + level.slice(1);

        if (container == null)
            return false;

        node.innerHTML = '<div id="row"><div class="alert alert-' + level + ' alert-dismissible">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
            '<h4><i class="icon fa fa-' + (level == "danger" ? "ban" : (level == "success" ? "check" : level)) + '"></i>'+text+'!</h4>' + message + '</div></div>';

        container.innerHTML = node.innerHTML + container.innerHTML;
    }
    // JQuery
    //components/jquery/jquery.min.js
    // bower_components/jquery/dist/jquery.min.js
    loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/jquery/dist/Jquery.min.js' ) ?>", function () {
        // A better closest function
        (function ($) {
            $.fn.closest_descendant = function (filter) {
                var $found = $(),
                    $currentSet = this; // Current place
                while ($currentSet.length) {
                    $found = $currentSet.filter(filter);
                    if ($found.length) break;  // At least one match: break loop
                    // Get all children of the current set
                    $currentSet = $currentSet.children();
                }
                return $found.first(); // Return first match of the collection
            }
        })(jQuery);

        //-- Jquery Form -->
        loadJS('<?= $this->versionControl( COMPOSER . 'bower-asset/jquery-form/jquery.form.js' )?>');

        //-- Background Stretch -->
        loadJS("<?= $this->versionControl( TEMPLATE . 'bower-asset/jquery-backstretch/jquery.backstretch.min.js' ) ?>");

        //-- Slim Scroll -->
        loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/jquery-slimscroll/jquery.slimscroll.min.js' ) ?>");

        //-- Fastclick -->
        loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/fastclick/lib/fastclick.js' ) ?>", function () {
            //-- Admin LTE -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'dist/js/adminlte.min.js' ) ?>");
        });


        //-- Bootstrap -->
        loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/bootstrap/dist/js/bootstrap.min.js' ) ?>", function () {

            //-- AJAX Pace -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/PACE/pace.js' ) ?>", function () {
                $(document).ajaxStart(function () {
                    Pace.restart();
                });
            });

            //-- Select 2 -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/select2/dist/js/select2.full.min.js' ) ?>");

            //-- iCheck -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'plugins/iCheck/icheck.min.js' )?>");

            //-- Input Mask -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'plugins/input-mask/jquery.inputmask.js' ) ?>", function () {
                loadJS("<?= $this->versionControl( TEMPLATE . 'plugins/input-mask/jquery.inputmask.date.extensions.js' ) ?>");
                loadJS("<?= $this->versionControl( TEMPLATE . 'plugins/input-mask/jquery.inputmask.extensions.js' ) ?>");
            });

            //-- jQuery Knob -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/jquery-knob/js/jquery.knob.js' ) ?>");

            //-- Bootstrap Time Picker -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'plugins/timepicker/bootstrap-timepicker.min.js' ) ?>");

            //--Bootstrap Datepicker -->
            loadJS("<?= $this->versionControl( TEMPLATE . 'bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js' ) ?>");

            //--Bootstrap Color Picker -->
            loadJS("<?= $this->versionControl( TEMPLATE. 'bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js' ) ?>");

            //-- PJAX-->
            loadJS("<?= $this->versionControl(  COMPOSER .'bower-asset/jquery-pjax/jquery.pjax.js' ) ?>", function () {
                loadJS("<?= $this->versionControl( COMPOSER .'bower-asset/mustache.js/mustache.js' ) ?>", function () {

                    /*$(document).on('pjax:start', function () {
                        console.log("PJAX");
                    });*/

                    $(document).on('pjax:end', function () {
                        // PJAX Forum Request
                        $(document).on('submit', 'form[data-pjax]', function (event) {
                            $('#ajax-content').hide();
                            $.pjax.submit(event, 'article')
                        });


                        // Set up Box Annotations
                        $(".box").boxWidget({
                            animationSpeed: 500,
                            collapseTrigger: '[data-widget="collapse"]',
                            removeTrigger: '[data-widget="remove"]',
                            collapseIcon: 'fa-minus',
                            expandIcon: 'fa-plus',
                            removeIcon: 'fa-times'
                        });


                        //-- iCheck -->
                        $('input').iCheck({
                            checkboxClass: 'icheckbox_square-blue',
                            radioClass: 'iradio_square-blue',
                            increaseArea: '20%' // optional
                        });


                        $('#my-box-widget').boxRefresh('load');

                        // Select 2 -->
                        $(".select2").select2();

                        <?php // Data tables loadJS("<?= $this->versionControl( 'bower_components/datatables.net-bs/js/dataTables.bootstrap.js' ) ?>//");-->

                        // Input Mask -->
                        $("[data-mask]").inputmask();  //Money Euro

                        // Bootstrap Datepicker -->
                        $('#datepicker').datepicker({autoclose: true});

                        //-- Bootstrap Time Picker -->
                        $('.timepicker').timepicker({showInputs: false});

                        <?php //<!-- AdminLTE for demo purposes loadJS("<?= $this->versionControl( 'dist/js/demo.js' ) ?>//");

                        //-- jQuery Knob -->
                        $(".knob").knob({
                            /*change : function (value) {
                             //console.log("change : " + value);
                             },
                             release : function (value) {
                             console.log("release : " + value);
                             },
                             cancel : function () {
                             console.log("cancel : " + this.value);
                             }, */
                            draw: function () {

                                // "tron" case
                                if (this.$.data('skin') == 'tron') {

                                    var a = this.angle(this.cv)  // Angle
                                        , sa = this.startAngle          // Previous start angle
                                        , sat = this.startAngle         // Start angle
                                        , ea                            // Previous end angle
                                        , eat = sat + a                 // End angle
                                        , r = true;

                                    this.g.lineWidth = this.lineWidth;

                                    this.o.cursor
                                    && (sat = eat - 0.3)
                                    && (eat = eat + 0.3);

                                    if (this.o.displayPrevious) {
                                        ea = this.startAngle + this.angle(this.value);
                                        this.o.cursor
                                        && (sa = ea - 0.3)
                                        && (ea = ea + 0.3);
                                        this.g.beginPath();
                                        this.g.strokeStyle = this.previousColor;
                                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                                        this.g.stroke();
                                    }

                                    this.g.beginPath();
                                    this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                                    this.g.stroke();

                                    this.g.lineWidth = 2;
                                    this.g.beginPath();
                                    this.g.strokeStyle = this.o.fgColor;
                                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                                    this.g.stroke();

                                    return false;
                                }
                            }
                        });
                        /* END JQUERY KNOB */

                    });

                    // Set a data mask to force https request
                    $(document).on("click", "a.no-pjax", false);

                    // All links will be sent with ajax
                    $(document).pjax('a', 'article');

                    $(document).on('pjax:click', function () {
                        $('#ajax-content').hide();
                    });

                    $(document).on('pjax:success', function () {
                        console.log("Successfully loaded " + window.location.href);

                    });

                    $(document).on('pjax:timeout', function (event) {
                        // Prevent default timeout redirection behavior, this would cause infinite loop
                        event.preventDefault()
                    });

                    $(document).on('pjax:error', function (event) {
                        console.log("Could not load " + window.location.href);
                    });

                    $(document).on('pjax:complete', function () {
                        $('#ajax-content').fadeIn('fast').removeClass('overlay');
                    });

                    // Get inner contents already buffered on server
                    $.pjax.reload('article');

                    <?php if ($_SESSION['id']): ?>

                    var defaultOnSocket = false,
                        statsSocket = new WebSocket('wss://stats.coach:8080/');

                    $.fn.trySocket = function () {
                        if (statsSocket.readyState === 1) return 1;
                        var count = 0;
                        console.log('Attempting Reconnect');
                        do {
                            count++;
                            if (statsSocket != null && typeof statsSocket === 'object' && statsSocket.readyState === 1) break;            // help avoid race
                            statsSocket = new WebSocket('wss://stats.coach:8080/');
                        } while (statsSocket.readyState === 3 && count <= 3);  // 6 seconds 3 attempts
                        if (statsSocket.readyState === 3)
                            console.log = "Could not connect to socket. TrySocket aborted";
                        return (statsSocket.readyState === 1);
                    };

                    $.fn.startApplication = function (url) {
                        if (defaultOnSocket && $.fn.trySocket) {           //defaultOnSocket &&
                            console.log('URI ' + url);
                            statsSocket.send(url);
                        } else $.get(url, function (data) {
                            MustacheWidgets(data)
                        }); // json
                    };

                    statsSocket.onmessage = function (data) {
                        if (IsJsonString(data.data)) {
                            MustacheWidgets(JSON.parse(data.data));
                        } else console.log(data.data);
                    };

                    statsSocket.onerror = function () {
                        console.log('Web Socket Error');
                    };

                    statsSocket.onopen = function () {
                        console.log('Socket Started');

                        // prevent the race condition
                        statsSocket.onclose = function () {
                            console.log('Closed Socket');
                            $.fn.trySocket();
                        };
                        // Messages in Navigation, faster to initially load over http
                        $.fn.startApplication('<?= SITE . 'Messages/' ?>');
                        $.fn.startApplication('<?= SITE . 'Notifications/' ?>');
                        $.fn.startApplication('<?= SITE . 'Tasks/' ?>');
                    };
                    <?php endif; ?>
                });
            });
        });
    });

    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-100885582-1', 'auto');
    ga('send', 'pageview');

</script>
</body>
</html>
