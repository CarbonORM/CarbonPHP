function Carbon(selector, address) {

    //-- Stats Coach Bootstrap Alert -->
    function bootstrapAlert(message, level) {
        if (level == null) level = 'info';
        var container = document.getElementById('alert'),
            node = document.createElement("DIV"), text;

        text = level.charAt(0).toUpperCase() + level.slice(1);

        if (container == null)
            return false;

        node.innerHTML = '<div id="row"><div class="alert alert-' + level + ' alert-dismissible">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
            '<h4><i class="icon fa fa-' + (level == "danger" ? "ban" : (level == "success" ? "check" : level)) + '"></i>' + text + '!</h4>' + message + '</div></div>';

        container.innerHTML = node.innerHTML + container.innerHTML;
    }

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
    /*$(document).on('pjax:start', function () {
        console.log("PJAX");
    });*/

    // Refresh all js processed css in html
    $(document).on('pjax:end', function () {
        // PJAX Forum Request
        $(document).on('submit', 'form', function (event) {
            $(selector).hide();
            $.pjax.submit(event, selector)
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

        // Data tables loadJS("<?= $this->versionControl( 'bower_components/datatables.net-bs/js/dataTables.bootstrap.js' ) ?>//");-->

        // Input Mask -->
        $("[data-mask]").inputmask();  //Money Euro

        // Bootstrap Datepicker -->
        $('#datepicker').datepicker({autoclose: true});

        //-- Bootstrap Time Picker -->
        $('.timepicker').timepicker({showInputs: false});

        //<!-- AdminLTE for demo purposes loadJS("<?= $this->versionControl( 'dist/js/demo.js' ) ?>//");

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
    $(document).pjax('a', selector);

    $(document).on('pjax:click', function () {
        $(selector).hide();
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
        $(selector).fadeIn('fast').removeClass('overlay');
    });

    // Get inner contents already buffered on server
    $.pjax.reload(selector);


    var defaultOnSocket = false,
        statsSocket = new WebSocket(address);

    $.fn.trySocket = function () {
        if (address === null || address === '')
            return 0;
        if (statsSocket.readyState === 1)
            return 1;

        var count = 0;
        console.log('Attempting Reconnect');
        do {
            count++;
            if (statsSocket != null && typeof statsSocket === 'object' && statsSocket.readyState === 1) break;            // help avoid race
            statsSocket = new WebSocket(address);
        } while (statsSocket.readyState === 3 && count <= 3);  // 6 seconds 3 attempts
        if (statsSocket.readyState === 3)
            console.log = "Could not connect to socket. Connection aborted";
        return (statsSocket.readyState === 1);
    };

    function IsJsonString(str) {
        try {
            return JSON.parse(str);
        } catch (e) {
            return false;
        }
    }

    function MustacheWidgets(data, url) {
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
            if (typeof data === "object") {
                if (url !== '') {
                    console.log('Attempting Socket');
                    setTimeout(function () {            // wait 2 seconds
                        $.fn.sendEvent(url);
                    }, 2000);
                }
            }
        }
    }


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

    };

}

