function Carbon(selector, address) {
    //-- Bootstrap Alert -->
    $.fn.bootstrapAlert = (message, level) => {
        if (level === null) level = 'info';
        let container = document.getElementById('alert'),
            node = document.createElement("DIV"), text;
        text = level.charAt(0).toUpperCase() + level.slice(1);
        if (container === null) return alert(message);
        node.innerHTML = '<div id="row"><div class="alert alert-' + level + ' alert-dismissible">'
            + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
            + '<h4><i class="icon fa fa-' + (level === "danger" ? "ban" : (level === "success" ? "check" : level))
            + '"></i>' + text + '!</h4>' + message + '</div></div>';
        container.innerHTML = node.innerHTML + container.innerHTML;
    };

    // A better closest function
    $.fn.closest_descendant = (filter) => {
        let $found = $(),
            $currentSet = this; // Current place
        while ($currentSet.length) {
            $found = $currentSet.filter(filter);
            if ($found.length) break;  // At least one match: break loop
            // Get all children of the current set
            $currentSet = $currentSet.children();
        }
        return $found.first(); // Return first match of the collection
    };

    $.fn.runEvent = (ev) => {
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

    $.fn.runEvent("Carbon");

    // PJAX Forum Request
    $(document).on('submit', 'form', (event) => {
        $(selector).hide();
        $.pjax.submit(event, selector)
    });

    // All links will be sent with ajax
    $(document).pjax('a', selector);

    $(document).on('pjax:click', () => $(selector).hide());

    $(document).on('pjax:success', () => console.log("Successfully loaded " + window.location.href));

    $(document).on('pjax:timeout', (event) => event.preventDefault());

    $(document).on('pjax:error', (event) => console.log("Could not load " + window.location.href));

    $(document).on('pjax:complete', () => {
        $(selector).fadeIn('fast').removeClass('overlay');
        // Set up Box Annotations
        $.fn.runEvent("Carbon");
        $(".box").boxWidget({
            animationSpeed: 500,
            collapseTrigger: '[data-widget="collapse"]',
            removeTrigger: '[data-widget="remove"]',
            collapseIcon: 'fa-minus',
            expandIcon: 'fa-plus',
            removeIcon: 'fa-times'
        });
        $('#my-box-widget').boxRefresh('load');

    });

    $(document).on('pjax:popstate', () => $.pjax.reload(selector));

    let defaultOnSocket = false, statsSocket;

    if (address !== '') statsSocket = new WebSocket(address);

    $.fn.trySocket = function () {
        if (address === null || address === '')
            return 0;
        if (statsSocket.readyState === 1)
            return 1;

        let count = 0;
        console.log('Attempting Reconnect');
        do {
            if (statsSocket !== null && typeof statsSocket === 'object' && statsSocket.readyState === 1) break;            // help avoid race
            statsSocket = new WebSocket(address);
        } while (statsSocket.readyState === 3 && ++count <= 3);  // 6 seconds 3 attempts
        if (statsSocket.readyState === 3)
            console.log = "Could not reconnect to socket. Connection aborted.";
        return (statsSocket.readyState === 1);
    };

    $.fn.IsJsonString = (str) => {
        try {
            return JSON.parse(str)
        } catch (e) {
            return false
        }
    };

    function MustacheWidgets(data, url) {
        if (data !== null) {
            if (typeof data === "string") data = $.fn.IsJsonString(data);
            if (data.hasOwnProperty('Mustache') && data.hasOwnProperty('widget')) {
                console.log('Valid Mustache $(' + data.widget + ')\n');
                $.get(data.Mustache, (template) => {
                    // Mustache.parse(template);
                    $(data.widget).html(Mustache.render(template, data));
                    if (data.hasOwnProperty('scroll'))
                        $(data.scroll).slimscroll({start: data.scrollTo});
                })
            } else {
                console.log("Bad Trimmers :: ");
                console.log(data);
            }
        } else {
            console.log('Bad Handlebar :: ' + data);
            if (typeof data === "object" && url !== '') {
                console.log('Attempting Socket');
                setTimeout(() => $.fn.startApplication(url), 2000); // wait 2 seconds

            }
        }
    }

    $.fn.startApplication = (url) => {
        if (defaultOnSocket && $.fn.trySocket) {           //defaultOnSocket &&
            console.log('URI ' + url);
            statsSocket.send(url);
        } else $.get(url, (data) => MustacheWidgets(data)); // json
    };


    if (address !== '') {
        statsSocket.onmessage = (data) => ($.fn.IsJsonString(data.data) ? MustacheWidgets(JSON.parse(data.data)) : console.log(data.data));
        statsSocket.onerror = () => console.log('Web Socket Error');
        statsSocket.onopen = () => {
            console.log('Socket Started');
            statsSocket.onclose = () => {                 // prevent the race condition
                console.log('Closed Socket');
                $.fn.trySocket();
            };
        };
    }

    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-100885582-1', 'auto');
    ga('send', 'pageview');
}

