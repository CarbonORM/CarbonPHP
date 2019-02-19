function CarbonPHP(selector, address, options) {
    this.address = address;
    this.selector = selector
    this.options = options;
    this.statsSocket = '';
    this.alerting = [];
    this.JSLoaded = new Set();

    this.js = (sc, cb) => {
        function load(src, cb) {
            "use strict";
            let ref = window.document.getElementsByTagName("script")[0];
            let script = window.document.createElement("script");
            script.src = src;
            script.async = true;
            ref.parentNode.insertBefore(script, ref);
            if (cb && typeof(cb) === "function")
                script.onload = cb;
            return script;
        }

        return (!this.JSLoaded.has(sc) ? load(sc, cb) : cb());
    };

/*-- milliseconds --*/
    this.sleep = (milliseconds) => {
        let start = new Date().getTime();
        for (let i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds) {
                break;
            }
        }
    };

/*-- I need php*/
    this.isset = (v) => {
        return (v !== '' && v !== null && v !== undefined);
    };

/*-- $().exists returns bool */
    this.exists = () => {
        return this.length !== 0;
    };

/*-- Json, no beef --*/
    this.isJson = (str) => {
        try {
            return JSON.parse(str)
        } catch (e) {
            return false
        }
    };

    this.app = this.start = this.startApplication = (url) => {
        if (url.charAt(0) !== '/') {
            url = '/' + url;
        }
        console.log('JavaScript startApplication(' + url + ')');
        if (this.defaultOnSocket && this.trySocket) {           /*defaultOnSocket && */
            console.log('Socket::' + url);
            this.statsSocket.send(JSON.stringify(url));
        } else {
            $.pjax({
                type: "GET",
                url: url,
                container: this.selector,
                timeout: 2000,
                accepts: {
                    mustacheTemplate: "html"
                },
                // deserialize a custom type
                converters: {
                    '* mustacheTemplate': this.handlebars,
                },
                dataType: "mustacheTemplate",
                success: function (data) {
                    alert(data);
                },
                error: function (data) {
                    alert(data);

                }
            });
            /*$.get(url, (data) => this.MustacheWidgets(data, url));*/
        }
    };

    this.alerts = (a) => {
        for (let key in a) {
            /* skip loop if the property is from prototype */
            if (!a.hasOwnProperty(key)) continue;
            this.bootstrapAlert(a[key], key);
        }
        a = null;
    };

/*-- Bootstrap Alert --*/
    this.alert = this.bootstrapAlert = (message, level) => {
        if (!this.isset(level)) {
            level = 'info';
        }
        let container, node = document.createElement("DIV"), text;
        text = level.charAt(0).toUpperCase() + level.slice(1);
        container = this.selector + " div#alert";

        if (!$(container).length) {
            if (!$("#alert").length)
                return alert(level + ' : ' + message);
            container = "#alert";
        }

        node.innerHTML = '<div id="row" style="margin-top: 20px"><div class="alert alert-' + level + ' alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
            + '<h4><i class="icon fa fa-' + (level === "danger" ? "ban" : (level === "success" ? "check" : level))
            + '"></i>' + text + '!</h4>' + message + '</div></div>';

        $(container).html(node.innerHTML + $(container).html());
    };

    /* PJAX Forum Request */
    this.handlebars = (data) => {
        let template = undefined, json = undefined;

        console.log('handlebars', data);

        if (!this.isset(data)) {
            console.log('No data to handlebars');
            console.log(data);
            return data;
        }

        json = (typeof data === "string" ? this.isJson(data) : false);

        console.log(json);

        if (json) {
            this.alerting = json.alert;

            if (json.hasOwnProperty('Mustache')) {

                if (json.hasOwnProperty('Widget')) {
                    this.selector = json.Widget;
                }

                console.log('Valid Handlebars $( ' + json.Widget + ' ).render( ' + json.Mustache + ', ... ); \n');


                $.ajax({
                    async: false,
                    //cache: false,
                    url: json.Mustache,
                }).done((mustache) => {

                    Mustache.parse(mustache);                                   /* cache */

                    template = Mustache.render(mustache, json);       /* render json with mustache lib */

                    console.log(json.Widget);

                    if (json.hasOwnProperty('ALERT') && this.isset(json.ALERT)) {
                        this.alerts(json.ALERT);
                    }

                    if (json.hasOwnProperty('scroll')) {                        /* use slim scroll to move to bottom of chats (lifo) */
                        $(json.scroll).slimscroll({start: json.scrollTo});
                    }
                });
                return template;

            } else {
                console.log("JSON RESPONSE :: ");                    /* log ( string ) */
                console.log(json);                              /* log ( object ) - seperating them will print nicely */

                if (json.hasOwnProperty('ALERT') && this.isset(json.ALERT)) {
                    this.bootstrapAlert(json.ALERT);
                }
                return json;
            }
        }
        return data;
    };

    this.event = this.runEvent = (ev) => {
        let event;
        if (document.createEvent) {
            event = document.createEvent("HTMLEvents");
            event.initEvent(ev, true, true)
        } else {
            event = document.createEventObject();
            event.eventType = ev
        }
        event.eventName = ev;
        document.createEvent ? document.dispatchEvent(event) :
            document.fireEvent("on" + event.eventType, event);
    };

    this.trySocket = () => {

        while (!navigator.onLine) {
        }
        /* TODO - This blocks a full cpu if the wifi drops.. we should non-block */

        if (address === null || address === '' || this.statsSocket.readyState === 0)
            return 0;

        if (this.statsSocket.readyState === 1)
            return 1;

        let count = 0;
        console.log('Attempting Reconnect');
        do {
            if (this.statsSocket !== null && typeof this.statsSocket === 'object' && this.statsSocket.readyState === 1)
                break;            /* help avoid race*/
            this.statsSocket = new WebSocket(address);
        } while (this.statsSocket.readyState === 3 && ++count <= 3);  // 6 seconds 3 attempts
        if (this.statsSocket.readyState === 3)
            console.log("Could not reconnect to socket. Connection aborted.");
        return (this.statsSocket.readyState === 1);
    };

    this.MustacheWidgets = (data, url) => {
        if (data !== null) {
            let json = (typeof data === "string" ? this.isJson(data) : data);

            console.log('MustacheWidgets', json);

            if (json && json.hasOwnProperty('Mustache')) {

                if (!json.hasOwnProperty('Widget')) {
                    json.Widget = selector;
                }

                console.log('Valid Mustache $( ' + json.Widget + ' ).render( ' + json.Mustache + ', ... ); \n');

                $.get(json.Mustache, (template) => {

                    console.log('HBS-Template::');

                    console.log(template);                                    /* TODO - comment out */

                    Mustache.parse(template);                                   /* cache */

                    template = Mustache.render(template, json);

                    console.log(json.Widget);

                    $(json.Widget).html(template);       /* render json with mustache lib */

                    if (json.hasOwnProperty('ALERT') && this.isset(json.ALERT)) {
                        this.alerts(json.ALERT);
                    }

                    if (json.hasOwnProperty('scroll')) {                        /*use slim scroll to move to bottom of chats (lifo)*/
                        $(json.scroll).slimscroll({start: json.scrollTo});
                    }
                });
            } else if (json) {
                console.log("JSON (NO MUSTACHE):: ");                    /*log ( string )*/
                console.log(json);                              /* log ( object ) - seperating them will print nicely */

                if (json.hasOwnProperty('ALERT') && this.isset(json.ALERT)) {
                    this.alerts(json.ALERT);
                }
            } else {
                if (data === "" || data === undefined) {
                    console.log("BAD STASH :: EMPTY STASH");
                } else {
                    console.log("FULL STASH :: ", data);
                    $(selector).html(data);
                }
            }
        } else {
            console.log('RECEIVED NOTHING ?? ' + data);
            if (typeof data === "object" && url !== '') {
                console.log('Re-attempting Connection');
                setTimeout(() => this.startApplication(url), 2000); /* wait 2 seconds */
            }
        }
        this.runEvent("Carbon");
    };


    /* Google's loadDeferredStyles */
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

    $(document).on('pjax:error', function (event, xhr, textStatus, errorThrown, options) {
        options.success(xhr.responseText, textStatus, xhr);
        return false;
    });

    /* PJAX content now with json (mustache) support */
    $(document).on('submit', 'form', (event) => {        /* TODO - remove this pos */
        event.preventDefault();


        $.fn.serializeAllArray = function () {
            var obj = {};

            $('input', this).each(function () {
                obj[this.name] = $(this).val();
            });
            $('select', this).each(function () {
                obj[this.name] = $(this).val();
            });
            return obj;
        }

        console.log($(event.target).serializeAllArray())

        /*
        $.pjax.submit(event, selector, {
            //async: false,
            push: false,
            accepts: {
                mustacheTemplate: "html"
            },
            // deserialize a custom type
            converters: {
                '* mustacheTemplate': this.handlebars,
            },
            dataType: "mustacheTemplate",
        });
        */

        $.pjax({
            type: "POST",
            url: $(event.target).attr('action'),
            container: this.selector,
            timeout: 2000,
            //contentType: 'json',
            data: $(event.target).serializeAllArray(),
            accepts: {
                mustacheTemplate: "html"
            },
            // deserialize a custom type
            converters: {
                '* mustacheTemplate': this.handlebars,
            },
            dataType: "mustacheTemplate",
        });

    });

    /* All links will be sent with ajax */
    $(document).pjax('a', selector, {
        async: false,
        accepts: {
            mustacheTemplate: "html"
        },
        /* deserialize a custom type */
        converters: {
            '* mustacheTemplate': this.handlebars
        },
        dataType: "mustacheTemplate",
    });

    $(document).on('pjax:success', () => {
        let container = $(this.selector + " div#alert"),
            alert = $("#alert");

        if (container.length) {
            container.empty();
        }
        if (alert.length) {
            alert.empty();
        } /* else we're defaulting to popup alerts */

        if (this.alerting !== null) {
            console.log(this.alerting);
            this.alerts(this.alerting);
        }
        console.log("Successfully loaded " + window.location.href)
    });

    $(document).on('pjax:timeout', (event) => event.preventDefault());

    $(document).on('pjax:error', (xhr, textStatus, error, options) => {
        console.log("Could not load " + window.location.href);
        console.log(xhr, textStatus, error, options);
        /* TODO - this is a very bad quick fix */
        /* $.pjax.reload(selector); */
    });

    $(document).on('pjax:complete', () => {
        /* Set up Box Annotations */
        this.runEvent("Carbon");
    });

    $(document).on('pjax:popstate', () => $.pjax.reload(selector)); /* refresh our state always!! */

    /* Socket Connection */
    this.defaultOnSocket = false;
    this.statsSocket = undefined;

    if (this.isset(address)) {
        if (this.isset(options)) {
            this.defaultOnSocket = options;
        }
        this.statsSocket = new WebSocket(address);
    }

    if (this.isset(address)) {
        this.statsSocket.onmessage = (data) => {
            console.log('Socket Sent An Update');
            (this.isJson(data.data) ? this.MustacheWidgets(JSON.parse(data.data)) : console.log('Not Json', data.data));
        };
        this.statsSocket.onerror = () => console.log('Web Socket Error');
        this.statsSocket.onopen = () => {
            console.log('Socket Started');
            this.statsSocket.onclose = () => {                 /* prevent the race condition */
                console.log('Closed Socket');
                this.trySocket();
            };
        };
    }
    return this;

}