<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= SITE_TITLE ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <?php include CONTENT_ROOT . 'img/icons/icons.php';
    /** @noinspection Annotator */
    //include SERVER_ROOT . 'Application.php/View/Helpers/htmlLogo.php';
    ?>
    <!-- PJAX Content Control -->
    <meta http-equiv="x-pjax-version" content="<?= $_SESSION['X_PJAX_Version'] ?>">
    <!-- REQUIRED STYLE SHEETS -->
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?= $this->versionControl( "bower_components/bootstrap/dist/css/bootstrap.min.css" ) ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $this->versionControl( "dist/css/AdminLTE.min.css" ) ?>">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
        folder instead of downloading all of them to reduce the load. -->
    <link rel="preload" href="<?= $this->versionControl( "dist/css/skins/_all-skins.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- DataTables.Bootstrap -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <!-- iCheck -->
    <link rel="preload" href="<?= $this->versionControl( 'plugins/iCheck/square/blue.css' ); ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Ionicons -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/Ionicons/css/ionicons.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Back color -->
    <link rel="preload" href="<?= $this->versionControl( "dist/css/skins/skin-green.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Multiple input dynamic form -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/select2/dist/css/select2.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Check Ratio Box -->
    <link rel="preload" href="<?= $this->versionControl( "plugins/iCheck/flat/blue.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- I dont know but keep it -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/morris.js/morris.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- fun ajax refresh -->
    <link rel="preload" href="<?= $this->versionControl( "plugins/pace/pace.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Jquery -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/jvectormap/jquery-jvectormap.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- datepicker -->
    <link rel="preload" href="<?= $this->versionControl( "bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">

    <link rel="preload" href="<?= $this->versionControl( "bower_components/bootstrap-daterangepicker/daterangepicker.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <link rel="preload" href="<?= $this->versionControl( "plugins/timepicker/bootstrap-timepicker.css" ) ?>" as="style" onload="this.rel='stylesheet'">
    <!-- Wysihtml -->
    <link rel="preload" href="<?= $this->versionControl( "plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" ) ?>" as="style"
          onload="this.rel='stylesheet'">
    <!-- Font Awesome -->
    <link rel="preload" href="<?= $this->versionControl( "components/font-awesome/css/font-awesome.min.css" ) ?>" as="style" onload="this.rel='stylesheet'">

    <!-- Docs V2 -->
    <script>
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
                            $.fn.sendEvent(url);
                        }, 2000);
                    }
            }
        }

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
        opacity: .7;

        background: url('<?=SITE?>/Public/StatsCoach/img/final.jpg') no-repeat fixed;

        background-position: center; /* Chrome */

        scroll-x /* Ensure the html element always takes up the full height of the browser window */ min-height: 100%;
        /* The Magic */
        background-size: cover;
    }

    body {
        background-color: black;
    }
</style>
<?php ob_start(); ?>
<!-- Full Width Column -->
<div class="content-wrapper">
    <div data-pjax-container class="container" id="ajax-content" style=""></div>
    <!-- /.container -->
</div>
<!-- /.content-wrapper -->
<footer class="main-footer" style="">
    <div class="container">
        <div class="pull-right hidden-xs">
            <a href="<?= SITE ?>Privacy/">Privacy Policy</a> <b>Version</b> <?= SITE_VERSION ?>
        </div>
        <strong>Copyright &copy; 2014-2017 <a href="http://lilRichard.com">Stats Coach</a>.</strong>

        <!--script type="text/javascript" src="https://cdn.ywxi.net/js/1.js" async></script-->
    </div>
    <!-- /.container -->
</footer>
</div>

<?php $wrapper = ob_get_clean(); ?>




<?php
if (!empty( $_SESSION['id'] ) && is_object( $this->user[$_SESSION['id']] )) {

    $me = $this->user[$_SESSION['id']];

    $fullName = $me->user_first_name . ' ' . $me->user_last_name;

    if (($this->user[$_SESSION['id']]->user_type ?? false) == 'Coach') {
        echo '<body class="skin-green fixed sidebar-mini sidebar-collapse"><div class="wrapper">';
        require_once CONTENT_ROOT . 'AdminLTE/CoachLayout.php';
        echo $wrapper;
    } elseif (($this->user[$_SESSION['id']]->user_type ?? false) == 'Athlete') {
        echo '<body class="hold-transition skin-green layout-top-nav"><div class="wrapper">';
        require_once CONTENT_ROOT . 'AdminLTE/AthleteLayout.php';
        echo $wrapper;
    }
} elseif (array_key_exists( 'id', $_SESSION ) && !$_SESSION['id']) {
    echo '<body class="stats-wrap"><div class="container" id="ajax-content" style=""></div>';
} else {
    session_destroy();
    #session_regenerate_id( TRUE );
    echo '<script type="text/javascript"> window.location = "' . SITE . '" </script>';
    // TODO - how often does this happen?
} ?>


<script>
    //-- Stats Coach Bootstrap Alert -->
    loadJS("<?= $this->versionControl( 'alert/alerts.js' ) ?>");

    // JQuery
    //components/jquery/jquery.min.js
    // bower_components/jquery/dist/jquery.min.js
    loadJS("<?= $this->versionControl( 'bower_components/jquery/dist/jquery.min.js' ) ?>", function () {
        // Element exists function
        jQuery.fn.exists = function () {
            return this.length > 0;
        };

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
        loadJS('<?= $this->versionControl( 'Public/Jquery-Form/jquery.form.js' )?>');

        //-- Background Stretch -->
        loadJS("<?= $this->versionControl( 'Public/Jquery-Backstretch/jquery.backstretch.min.js' ) ?>");

        //-- Slim Scroll -->
        loadJS("<?= $this->versionControl( 'bower_components/jquery-slimscroll/jquery.slimscroll.min.js' ) ?>");

        //-- Fastclick -->
        loadJS("<?= $this->versionControl( 'bower_components/fastclick/lib/fastclick.js' ) ?>", function () {
            //-- Admin LTE -->
            loadJS("<?= $this->versionControl( 'dist/js/adminlte.min.js' ) ?>");
        });


        //-- Bootstrap -->
        loadJS("<?= $this->versionControl( 'bower_components/bootstrap/dist/js/bootstrap.min.js' ) ?>", function () {

            //-- AJAX Pace -->
            loadJS("<?= $this->versionControl( 'bower_components/PACE/pace.js' ) ?>", function () {
                $(document).ajaxStart(function () {
                    Pace.restart();
                });
            });

            //-- Select 2 -->
            loadJS("<?= $this->versionControl( 'bower_components/select2/dist/js/select2.full.min.js' ) ?>");

            //-- iCheck -->
            loadJS("<?= $this->versionControl( 'plugins/iCheck/icheck.min.js' )?>");

            //-- Input Mask -->
            loadJS("<?= $this->versionControl( 'plugins/input-mask/jquery.inputmask.js' ) ?>", function () {
                loadJS("<?= $this->versionControl( 'plugins/input-mask/jquery.inputmask.date.extensions.js' ) ?>");
                loadJS("<?= $this->versionControl( 'plugins/input-mask/jquery.inputmask.extensions.js' ) ?>");
            });

            //-- jQuery Knob -->
            loadJS("<?= $this->versionControl( 'bower_components/jquery-knob/js/jquery.knob.js' ) ?>");

            //-- Bootstrap Time Picker -->
            loadJS("<?= $this->versionControl( 'plugins/timepicker/bootstrap-timepicker.min.js' ) ?>");

            //--Bootstrap Datepicker -->
            loadJS("<?= $this->versionControl( 'bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js' ) ?>");

            //--Bootstrap Color Picker -->
            loadJS("<?= $this->versionControl( 'bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js' ) ?>");

            //-- PJAX-->
            loadJS("<?= $this->versionControl( 'Public/Jquery-Pjax/jquery.pjax.js' ) ?>", function () {
                loadJS("<?= $this->versionControl( 'Public/Mustache/mustache.js' ) ?>", function () {

                    /*$(document).on('pjax:start', function () {
                        console.log("PJAX");
                    });*/

                    $(document).on('pjax:end', function () {
                        // PJAX Forum Request
                        $(document).on('submit', 'form[data-pjax]', function (event) {
                            $('#ajax-content').hide();
                            $.pjax.submit(event, '#ajax-content')
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
                    $(document).pjax('a', '#ajax-content');

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
                    $.pjax.reload('#ajax-content');

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

                    $.fn.sendEvent = function (url) {
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
                        $.fn.sendEvent('<?= SITE . 'Messages/' ?>');
                        $.fn.sendEvent('<?= SITE . 'Notifications/' ?>');
                        $.fn.sendEvent('<?= SITE . 'Tasks/' ?>');
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
