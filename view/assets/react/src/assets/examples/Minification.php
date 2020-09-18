<?php

class Config implements iConfig
{
    public static function configuration(): array
    {
        return [
            'MINIFY' => [
                'CSS' => [
                    'OUT' => APP_ROOT . 'view/css/style.css',
                    APP_ROOT . 'view/css/stats.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/AdminLTE.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/iCheck/all.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/bootstrap-slider/slider.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/skins/skin-green.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/select2/dist/css/select2.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/iCheck/flat/blue.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/morris.js/morris.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/pace/pace.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jvectormap/jquery-jvectormap.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/timepicker/bootstrap-timepicker.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
                ],
                'JS' => [
                    'OUT' => APP_ROOT . 'view/js/javascript.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js',
                    APP_ROOT . 'node_modules/jquery-pjax/jquery.pjax.js',
                    APP_ROOT . 'vendor/richardtmiles/carbonphp/view/mustache/Layout/mustache.js',
                    CARBON_ROOT . 'helpers/Carbon.js',
                    CARBON_ROOT . 'helpers/asynchronous.js',
                    APP_ROOT . 'node_modules/jquery-form/src/jquery.form.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/moment/moment.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    APP_ROOT . 'node_modules/admin-lte/dist/js/adminlte.js',

                ],
            ]
        ];
    }
}