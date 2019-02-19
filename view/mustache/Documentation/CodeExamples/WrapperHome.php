<html>
<head>
    <title>Site Title</title>

    <!-- PJAX Content Control -->
    <meta http-equiv="x-pjax-version" content="X_PJAX_Version">

    <script>
        /*! loadJS: load a JS file asynchronously. [c]2014 @scottjehl, Filament Group, Inc. (Based on http://goo.gl/REQGQ by Paul Irish). Licensed MIT */
        (function (w) {
            let loadJS;
            loadJS = function (src, cb) {
                "use strict";
                let ref = w.document.getElementsByTagName("script")[0];
                let script = w.document.createElement("script");
                script.src = src;
                script.async = true;
                ref.parentNode.insertBefore(script, ref);
                if (cb && typeof(cb) === "function")
                    script.onload = cb;

                return script;
            }; // commonjs
            if (typeof module !== "undefined") module.exports = loadJS;
            else w.loadJS = loadJS;
        }(typeof global !== "undefined" ? global : this));// Hierarchical PJAX Request

        // Document ready => jQuery => PJAX => CarbonPHP = loaded
        function OneTimeEvent(ev, cb) {
            return document.addEventListener(ev, function fn(event) {
                document.removeEventListener(ev, fn);
                return cb(event);
            });
        }

        function Carbon(cb) {
            return OneTimeEvent("Carbon", cb)
        }
    </script>
</head>
<body>
<!-- content -->
<div class="col-md-offset-1 col-md-10">
    <div id="pjax-content">
        <!-- Page content here -->
    </div>
</div>
<!-- /.content -->
<noscript id="deferred-styles">
    <!-- REQUIRED STYLE SHEETS -->
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" type="text/css"
          href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" type="text/css" href="/dist/css/AdminLTE.min.css">
    .....
</noscript>

<script>
    // Google
    let loadDeferredStyles = function () {
        let addStylesNode = document.getElementById("deferred-styles");
        let replacement = document.createElement("div");
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement)
        addStylesNode.parentElement.removeChild(addStylesNode);
    };
    let raf = requestAnimationFrame || mozRequestAnimationFrame ||
        webkitRequestAnimationFrame || msRequestAnimationFrame;
    if (raf) raf(function () {
        window.setTimeout(loadDeferredStyles, 0);
    });
    else window.addEventListener('load', loadDeferredStyles);

    // C6
    let JSLoaded = new Set();

    //-- JQuery -->
    loadJS("/bower_components/jquery/dist/jquery.min.js' ?>", () => {


        //-- Jquery Form -->
        loadJS('/bower-asset/jquery-form/src/jquery.form.js');

        //-- Slim Scroll -->
        loadJS("/bower_components/jquery-slimscroll/jquery.slimscroll.min.js' ?>");


        //-- Bootstrap -->
        loadJS("/bower_components/bootstrap/dist/js/bootstrap.min.js", () => {

            //-- Fastclick -->
            loadJS("/bower_components/fastclick/lib/fastclick.js", () => {
                //-- Admin LTE -->
                loadJS("/dist/js/adminlte.min.js");

            });

            //-- AJAX Pace -->
            loadJS("/bower_components/PACE/pace.js", () => $(document).ajaxStart(() => Pace.restart()));

            $.fn.CarbonJS = (sc, cb) => (!JSLoaded.has(sc) ? loadJS(sc, cb) : cb());


            $.fn.load_backStreach = (img, selector) =>
                $.fn.CarbonJS("/bower-asset/jquery-backstretch/jquery.backstretch.js", () =>
                    $(selector).length ? $(selector).backstretch(img) : $.backstretch(img));


            loadJS("/bower-asset/jquery-backstretch/jquery.backstretch.min.js", () => {
                $.backstretch('/Img/Carbon-green.png');
            });


            //-- Select 2 -->
            $.fn.load_select2 = (select2) =>
                $.fn.CarbonJS("/bower_components/select2/dist/js/select2.full.min.js", () =>
                    $(select2).select2());

            //-- Data tables -->
            $.fn.load_datatables = (table) =>
                $.fn.CarbonJS("/bower_components/datatables.net-bs/js/dataTables.bootstrap.js", () => {
                    try {
                        return $(table).DataTable()
                    } catch (err) {
                        return false
                    }
                });

            //-- iCheak -->
            $.fn.load_iCheck = (input) => {
                $.fn.CarbonJS("/plugins/iCheck/icheck.min.js", () => {
                    $(input).iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue',
                        increaseArea: '20%' // optional
                    });
                });
            };

            //-- Input Mask -->
            $.fn.load_inputmask = (mask) =>
                $.fn.CarbonJS("/plugins/input-mask/jquery.inputmask.js", () => {
                    loadJS("/plugins/input-mask/jquery.inputmask.date.extensions.js",
                        () => $(mask).inputmask());
                    loadJS("plugins/input-mask/jquery.inputmask.extensions.js",
                        () => $(mask).inputmask());
                }, () => $(mask).inputmask());


            //-- Bootstrap Time Picker -->
            $.fn.load_timepicker = (timepicker) => {
                $.fn.CarbonJS("/plugins/timepicker/bootstrap-timepicker.min.js", () => {
                    $(timepicker).timepicker({showInputs: false});
                });
            };

            //--Bootstrap Datepicker -->
            $.fn.load_datepicker = (datepicker) =>
                $.fn.CarbonJS("/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js", () =>
                    $(datepicker).datepicker({autoclose: true}));

            //--Bootstrap Color Picker -->
            $.fn.load_colorpicker = (colorpicker) =>
                $.fn.CarbonJS("/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js' ?>", () =>
                    $(colorpicker).colorpicker());

            //-- PJAX-->
            loadJS("/bower-asset/jquery-pjax/jquery.pjax.js", () =>
                loadJS("/bower-asset/mustache.js/mustache.js' ?>", () =>
                    loadJS("/richardtmiles/carbonphp/Helpers/Carbon.js", () =>
                        CarbonJS('#pjax-content', 'wss://example.com:8888/', false))));

        });
    });

    <!-- Global site tag (gtag.js) - Google Analytics -->
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'UA-100885582-1');
</script>
</body>
</html>