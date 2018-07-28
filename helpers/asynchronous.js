(function ($) {

    function load_backStretch(img, selector) {
        carbon.js(APP_VIEW + "Layout/jquery.backstretch.js", () => {
            $(selector).length ? $(selector).backstretch(img) : $.backstretch(img)
        })
    }

//-- Select 2 -->
    function load_select2() {
        carbon.js(TEMPLATE + "bower_components/select2/dist/js/select2.full.min.js", () =>
            $(this).select2());
    }

//-- Data tables -->
    function load_datatables() {
        carbon.js(TEMPLATE + "bower_components/datatables.net-bs/js/dataTables.bootstrap.js", () => {
            try {
                return $(this).DataTable()
            } catch (err) {
                return false
            }
        });
    }

//-- iCheak -->
    function load_iCheck() {
        carbon.js(TEMPLATE + "plugins/iCheck/icheck.min.js", () => {
            $(this).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    }


//-- WYSIHTML5 -->
    function load_wysihtml5() {
        carbon.js(TEMPLATE + "plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js", () => {
            $(this).wysihtml5();
        });
    }

//-- Input Mask -->
    function load_inputmask() {
        carbon.js(TEMPLATE + "plugins/input-mask/jquery.inputmask.js", () => {
            carbon.js(TEMPLATE + "plugins/input-mask/jquery.inputmask.date.extensions.js",
                () => $(this).inputmask());
            carbon.js(TEMPLATE + "plugins/input-mask/jquery.inputmask.extensions.js",
                () => $(this).inputmask());
        }, () => $(this).inputmask());
    }

//-- jQuery Knob -->
    function load_knob() {
        carbon.js(TEMPLATE+"bower_components/jquery-knob/js/jquery.knob.js", () => {
            $(this).knob({
                draw: function () {
                    // "tron" case
                    if (this.$.data('skin') === 'tron') {

                        let a = this.angle(this.cv)  // Angle
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
        });
    }

//-- Bootstrap Time Picker -->
    function load_timepicker() {
        carbon.js(TEMPLATE + "plugins/timepicker/bootstrap-timepicker.min.js", () => {
            $(this).timepicker({showInputs: false});
        });
    }

//--Bootstrap Datepicker -->
    function load_datepicker() {
        carbon.js(TEMPLATE + "bower_components/bootstrap-datepicker/js/bootstrap-datepicker.js", () => {
            $(this).datepicker({autoclose: true});
        });
    }

//--Bootstrap Color Picker -->
    function load_colorpicker() {
        carbon.js(TEMPLATE + "bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js", () =>
            $(this).colorpicker());
    }

    //-- Minimize our resources per request
    //-- The $.fn. methods will be removed in the next major version, this is to prevent deprecation
    $.fn.load_backStretch = $.load_backStretch = load_backStretch;
    $.fn.load_select2  = $.load_select2 = load_select2;
    $.fn.load_datatables = $.load_datatables = load_datatables;
    $.fn.load_datatables = $.load_iCheck = load_iCheck;
    $.fn.load_wysihtml5 = $.load_wysihtml5 = load_wysihtml5;
    $.fn.load_inputmask = $.load_inputmask = load_inputmask;
    $.fn.load_knob = $.load_knob = load_knob;
    $.fn.load_timepicker = $.load_timepicker = load_timepicker;
    $.fn.load_datepicker = $.load_datepicker = load_datepicker;
    $.fn.load_colorpicker = $.load_colorpicker = load_colorpicker;
}(jQuery));
