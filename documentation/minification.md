# Minification is supported for Javascript and CSS

This feature is deprecated as of version 20 and will be removed in version 21. Please use webpack or another tool to minify.

```bash
php index.php minify
```

The file below is a bootstrap with only one feature, Minification. The result of running the command above would be a 
file, or two, being created/overwritten in the location dictated by the array returned by the configuration. 
More specifically by setting the field:


```php

    public static function configuration(): array
    {
        return [
            CarbonPHP::MINIFY => [
                CarbonPHP::CSS => [
                    CarbonPHP::OUT => CarbonPHP::$app_root . 'view/assets/css/style.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/AdminLTE.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/iCheck/all.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/bootstrap-slider/slider.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/css/skins/skin-green.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/select2/dist/css/select2.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/iCheck/flat/blue.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/morris.js/morris.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/pace/pace.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jvectormap/jquery-jvectormap.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/timepicker/bootstrap-timepicker.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
                ],
                CarbonPHP::JS => [
                    CarbonPHP::OUT => CarbonPHP::$app_root . 'view/assets/js/javascript.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.js',  // do not use slim version
                    CarbonPHP::$app_root . 'node_modules/jquery-pjax/jquery.pjax.js',
                    CarbonPHP::$app_root . 'node_modules/mustache/mustache.js',
                    CarbonPHP::CARBON_ROOT . 'helpers/Carbon.js',
                    CarbonPHP::CARBON_ROOT . 'helpers/asynchronous.js',
                    CarbonPHP::$app_root . 'node_modules/jquery-form/src/jquery.form.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    CarbonPHP::$app_root . 'node_modules/admin-lte/dist/js/adminlte.js',
                ],
            ]
        ];
    }

```
