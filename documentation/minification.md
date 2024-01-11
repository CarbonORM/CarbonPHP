# Minification is supported for Javascript and CSS

CarbonPHP must be invoked in you index.php file for CLI commands to work. See the [documentation at CarbonORM.dev](https://carbonorm.dev) for setup instrucations. The [source code for the minify cli program](https://github.com/CarbonORM/CarbonPHP/blob/lts/carbonphp/programs/Minify.php) can be found in the path `carbonphp/programs/Minify.php`. The minify command is considered **legacy and may possibly become deprecated**. It uses thrid-pary composer plugins to essentially concatinate the files to be minified. It is recommended to use a better tool like Gulp or a runtime like our React Template which minifies and compiles your code by default.

```bash
php index.php minify
```

## External Dependencies

These are marked as Development Dependencies in CarbonPHP's composer.json file. They are not required for CarbonPHP to run, but are required for the minify command to work. 

```bash

```json lines
{
    "require": {
        "matthiasmullie/minify": "^1.3",
        "patchwork/jsqueeze": "^2.0"
    }
}
```

## Configuration

The example below sets a custom output filename and specifies what files to minify.

```php
<?php

namespace MilesSystems\Configuration;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iConfig;

class Configuration implements iConfig {

    public static function make(): void
    {
        CarbonPHP::make(__CLASS__, SERVER_ROOT);
    }

    public static function configuration(): array
    {

        return [
            // reduced for documentation
            CarbonPHP::MINIFY => [
                CarbonPHP::CSS => [
                    CarbonPHP::OUT => APP_ROOT . 'Application/View/CSS/style.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/AdminLTE.min.css',
                    APP_ROOT . 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                    APP_ROOT . 'node_modules/admin-lte/plugins/iCheck/all.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.cdss',
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
                    APP_ROOT . 'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.css',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fullcalendar/dist/fullcalendar.min.css'
                ],
                'JS' => [
                    'OUT' => APP_ROOT . 'Application/View/JS/scripts.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js',
                    APP_ROOT . 'node_modules/jquery-pjax/jquery.pjax.js',
                    CarbonPHP::CARBON_ROOT . 'helpers/Carbon.js',
                    APP_ROOT . 'vendor/richardtmiles/carbonphp/view/mustache/Layout/mustache.js',  // todo - this feels like it needs spring cleaning
                    CarbonPHP::CARBON_ROOT . 'helpers/asynchronous.js',
                    APP_ROOT . 'node_modules/jquery-form/src/jquery.form.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/moment/moment.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/fastclick/lib/fastclick.js',
                    APP_ROOT . 'node_modules/admin-lte/dist/js/adminlte.js',
                    APP_ROOT . 'node_modules/admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
                    APP_ROOT . 'src/view/bower-asset/jquery-backstretch/jquery.backstretch.js',
                    APP_ROOT . 'node_modules/admin-lte/bower_components/PACE/pace.min.js',
                ]
            ]
        ];

    }
}
```

