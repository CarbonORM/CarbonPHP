<script src="/node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/node_modules/jquery-pjax/jquery.pjax.js"></script>
<script src="/node_modules/mustache/mustache.js"></script>
<script src="/helpers/Carbon.js"></script>
<script src="/node_modules/jquery-form/src/jquery.form.js"></script>
<script src="/node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="/node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js"></script>
<script src="/node_modules/admin-lte/dist/js/adminlte.min.js"></script>
<script src="/helpers/asynchronous.js"></script>

<script>
    const TEMPLATE = "/vendor/almasaeed2010/adminlte/",
        APP_VIEW = "/view/",
        COMPOSER = "/vendor/",
        carbon = new CarbonPHP('#pjax-content');

    carbon.event("Carbon");

    $(document).on('pjax:complete', function () {
        // TODO - remove alerts here?

        let boxes = $(".box");

        if (boxes.length) {
            return;
        }

        boxes.boxWidget({
            animationSpeed: 500,
            collapseTrigger: '[data-widget="collapse"]',
            removeTrigger: '[data-widget="remove"]',
            collapseIcon: 'fa-minus',
            expandIcon: 'fa-plus',
            removeIcon: 'fa-times'
        });
        $('#my-box-widget').boxRefresh('load');
    });

    $.load_backStretch(APP_VIEW + 'img/Carbon-White.png');

    $('.sidebar-menu').tree();


    //carbon.js(APP_VIEW + 'AdminLTE/Demo/demo.js');
    //-- AJAX Pace -->
    carbon.js('/node_modules/admin-lte/bower_components/PACE/pace.js', () => $(document).ajaxStart(() => Pace.restart()));


    <!-- Global site tag (gtag.js) - Google Analytics -->
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'UA-100885582-1');

    <!-- Global site tag (gtag.js) - Google Analytics -->
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'UA-100885582-1');
</script>