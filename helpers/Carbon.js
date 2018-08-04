function CarbonPHP() {
    let self = this;
    let JSLoaded = new Set();

    self.js = (sc, cb) => {
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
        return (!JSLoaded.has(sc) ? load(sc, cb) : cb());
    };

//-- milliseconds -->
    self.sleep = (milliseconds) => {
        let start = new Date().getTime();
        for (let i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds) {
                break;
            }
        }
    };

//-- I need php
    self.isset = (v) => {
        return (v !== '' && v !== null && v !== undefined);
    };

//-- $().exists returns bool
    $.fn.exists = () => {
        return this.length !== 0;
    };

//-- Json, no beef -->
    self.isJson = (str) => {
        try {
            return JSON.parse(str)
        } catch (e) {
            return false
        }
    };

    self.app = self.start = self.startApplication = (url) => {
        if (url.charAt(0) !== '/') {
            url = '/' + url;
        }
        console.log('JavaScript startApplication(' + url + ')');
        if (self.defaultOnSocket && self.trySocket) {           //defaultOnSocket &&
            console.log('Socket::' + url);
            self.statsSocket.send(JSON.stringify(url));
        } else $.get(url, (data) => self.MustacheWidgets(data, url));
        //$.pjax.reload(self.selector, {url: url})
    };

    self.alerts = (a) => {
        for (let key in a) {
            // skip loop if the property is from prototype
            if (!a.hasOwnProperty(key)) continue;
            bootstrapAlert(a[key], key);
        }
        a = null;
    };

//-- Bootstrap Alert -->
    self.alert = self.bootstrapAlert = (message, level) => {
        if (!self.isset(level)) {
            level = 'info';
        }
        let container, node = document.createElement("DIV"), text;
        text = level.charAt(0).toUpperCase() + level.slice(1);
        container = self.selector + " div#alert";

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

// PJAX Forum Request
    self.handlebars = (data) => {
        console.log('handlebars', data);

        let template = undefined, json = undefined;

        if (!self.isset(data)) {
            console.log('No Json to Handlebars');
            return null;
        }

        json = (typeof data === "string" ? self.isJson(data) : false);

        if (json) {
            console.log(json);

            if (json.hasOwnProperty('Mustache')) {

                if (!json.hasOwnProperty('Widget')) {
                    json.Widget = self.selector;
                }

                console.log('Valid Mustache $( ' + json.Widget + ' ).render( ' + json.Mustache + ', ... ); \n');

                $.ajax({
                    async: false,
                    //cache: false,
                    url: json.Mustache,
                }).done((mustache) => {

                    Mustache.parse(mustache);                                   // cache

                    template = Mustache.render(mustache, json);       // render json with mustache lib

                    if (json.hasOwnProperty('ALERT') && self.isset(json.ALERT)) {
                        self.alerting = json.ALERT;
                    }

                    if (json.hasOwnProperty('scroll')) {                        // use slim scroll to move to bottom of chats (lifo)
                        $(json.scroll).slimscroll({start: json.scrollTo});
                    }

                });
                return template;

            } else {
                console.log("JSON RESPONSE :: ");                    // log ( string )
                console.log(json);                              // log ( object ) - seperating them will print nicely

                if (json.hasOwnProperty('ALERT') && self.isset(json.ALERT)) {
                    bootstrapAlert(json.ALERT);
                }
                return '';
            }

        }
        return data;
    };

    self.event=self.runEvent = (ev) => {
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

    self.trySocket = () => {

        while (!navigator.onLine) {
        }
        // TODO - This blocks a full cpu if the wifi drops.. we should non-block

        if (address === null || address === '' || self.statsSocket.readyState === 0)
            return 0;

        if (self.statsSocket.readyState === 1)
            return 1;

        let count = 0;
        console.log('Attempting Reconnect');
        do {
            if (self.statsSocket !== null && typeof self.statsSocket === 'object' && self.statsSocket.readyState === 1)
                break;            // help avoid race
            self.statsSocket = new WebSocket(address);
        } while (self.statsSocket.readyState === 3 && ++count <= 3);  // 6 seconds 3 attempts
        if (self.statsSocket.readyState === 3)
            console.log = "Could not reconnect to socket. Connection aborted.";
        return (self.statsSocket.readyState === 1);
    };

    self.MustacheWidgets = (data, url) => {
        if (data !== null) {
            let json = (typeof data === "string" ? self.isJson(data) : data);

            console.log('MustacheWidgets');
            console.log(json);

            if (json && json.hasOwnProperty('Mustache')) {

                if (!json.hasOwnProperty('Widget')) {
                    json.Widget = selector;
                }

                console.log('Valid Mustache $( ' + json.Widget + ' ).render( ' + json.Mustache + ', ... ); \n');

                $.get(json.Mustache, (template) => {

                    //console.log('HBS-Template::');                            // log

                    //console.log(template);                                    // TODO - comment out

                    Mustache.parse(template);                                   // cache

                    template = Mustache.render(template, json);

                    $(json.Widget).html(template);       // render json with mustache lib

                    if (json.hasOwnProperty('ALERT') && self.isset(json.ALERT)) {
                        self.alerts(json.ALERT);
                    }

                    if (json.hasOwnProperty('scroll')) {                        // use slim scroll to move to bottom of chats (lifo)
                        $(json.scroll).slimscroll({start: json.scrollTo});
                    }
                });
            } else if (json) {
                console.log("JSON (NO MUSTACHE):: ");                    // log ( string )
                console.log(json);                              // log ( object ) - seperating them will print nicely

                if (json.hasOwnProperty('ALERT') && self.isset(json.ALERT)) {
                    self.alerts(json.ALERT);
                }

            } else {
                if (data === "" || data === undefined) {
                    console.log("BAD STASH :: EMPTY STASH");
                } else {
                    console.log("BAD STASH :: ", data);
                    $("body").html(data);                           //
                }
            }
        } else {
            console.log('RECEIVED NOTHING ?? ' + data);
            if (typeof data === "object" && url !== '') {
                console.log('Re-attempting Connection');
                setTimeout(() => startApplication(url), 2000); // wait 2 seconds
            }
        }
        self.runEvent("Carbon");
    };

    self.invoke = (selector, address, options) => {

        self.selector = selector;

        self.address = address;

        self.alerting = {};

        // Google's loadDeferredStyles
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

        // PJAX content now with json (mustache) support
        $(document).on('submit', 'form', function (event) {        // TODO - remove this pos
            $.pjax.submit(event, selector, {
                async: false,
                push: false,
                accepts: {
                    mustacheTemplate: "html"
                },
                // deserialize a custom type
                converters: {
                    '* mustacheTemplate': self.handlebars,
                },
                dataType: "mustacheTemplate",
            });
        });

        // All links will be sent with ajax
        $(document).pjax('a', selector, {
            async: false,
            accepts: {
                mustacheTemplate: "html"
            },
            // deserialize a custom type
            converters: {
                '* mustacheTemplate': self.handlebars,
            },
            dataType: "mustacheTemplate",

        });

        $(document).on('pjax:success', () => {

            console.log(self.alerting);

            self.alerts(self.alerting);

            console.log("Successfully loaded " + window.location.href)
        });

        $(document).on('pjax:timeout', (event) => event.preventDefault());

        $(document).on('pjax:error', (xhr, textStatus, error, options) => {
            console.log("Could not load " + window.location.href);
            console.log(xhr, textStatus, error, options);
            // TODO - this is a very bad quick fix
            //$.pjax.reload(selector);
        });

        $(document).on('pjax:complete', function () {
            // Set up Box Annotations
            self.runEvent("Carbon");
        });

        $(document).on('pjax:popstate', () => $.pjax.reload(selector)); // refresh our state always!!

        // Socket Connection
        self.defaultOnSocket = false;
        let statsSocket = self.statsSocket = undefined;

        if (self.isset(address)) {
            if (self.isset(options)) {
                self.defaultOnSocket = options;
            }
            statsSocket = new WebSocket(address);
        }

        if (self.isset(address)) {
            statsSocket.onmessage = (data) => {
                console.log('Socket Sent An Update');
                (self.isJson(data.data) ? self.MustacheWidgets(JSON.parse(data.data)) : console.log('Not Json', data.data));
            };
            statsSocket.onerror = () => console.log('Web Socket Error');
            statsSocket.onopen = () => {
                console.log('Socket Started');
                statsSocket.onclose = () => {                 // prevent the race condition
                    console.log('Closed Socket');
                    self.trySocket();
                };
            };
        }
        return self;
    };
    return self;
}