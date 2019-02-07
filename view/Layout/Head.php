<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= SITE_TITLE ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- PJAX Content Control -->
    <meta http-equiv="x-pjax-version" content="<?= $_SESSION['X_PJAX_Version'] ?>">
    <!-- Google -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-100885582-1"></script>

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

        <?php if (defined('FACEBOOK_APP_ID') && !empty(FACEBOOK_APP_ID)): ?>
        // Facebook Analytics
        window.fbAsyncInit = function () {
            FB.init({
                appId: '<?=FACEBOOK_APP_ID?>',
                xfbml: true,
                version: 'v2.11'
            });
            FB.AppEvents.logPageView();
        };

        (function (d, s, id) {
            let js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        <?php endif; ?>

        // Document ready => jQuery => PJAX => CarbonPHP = loaded
        function OneTimeEvent(ev, cb){
            return document.addEventListener(ev, function fn(event) {
                document.removeEventListener(ev, fn);
                return cb(event);
            });
        }
        function Carbon(cb) {return OneTimeEvent("Carbon", cb)}
    </script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- ./wrapper -->
</head>