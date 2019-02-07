<!-- Content Header (Page header) -->

<div class="box" style="margin-top: 20px">
    <div class="box-header">
        <h1>
            <b>Wrapping</b> Your Application
            <small>html templates made better</small>
        </h1>
        <ol class="breadcrumb">
            <li><i class="fa fa-code"></i> <code>Wrapper Overview</code></li>

        </ol>
    </div>
    <!-- Main content -->
    <div class="box-body">
            <h2><b>The wrapper is full of greatness at every level.</b></h2><p class="lead">One of our biggest developmental problems
            was asynchronously every script on our page. Don't worry, we did not
            compromise. The <a href="https://github.com/filamentgroup/loadJS">Filament Group</a>
            has an open source function named <code>loadJS()</code> for asynchronously loading
            javascript files in a controlled manner. We combine this with
            <a href="https://pjax.herokuapp.com">PJAX</a> to send all of our requests through ajax.
            This allows us to only reload the sections of the DOM that actually change.</p>
        <h2><b>Whats so hard about that?</b></h2>
        <p class="lead">Glad you asked! See when a document is sent to a browser all javascript and css
            is requested from the servers asap. Some browsers even go as far as waiting to processing the
            original document until each request is filled. This is a hassle on our servers and slows down
            overall performance. Google recommend simple solutions such as <a
                    href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery">
                using deferred style sheets</a> and adding the <code>async</code> tag to javascript files.
            We actually utilize the deferred style sheet technique, but there is a fundamental problem with
            the async command. First and foremost it is not supported by all browsers. Secondly you run into
            a timing problem. For example, what if a file dependant on jQuery comes back to the browsers before jQuery?
        </p>
        <p class="lead">Ya, oh no indeed... Well remember LoadJS solves this but now a new set of problems arise. Our
            document is
            fully sent before anything can block the rendering process. When the final <code>/HTML</code> tag
            is processed Javascript fires the DOMContentLoaded event. That's crap because were no were near loaded.
            It's probably worth reiterating that this process is only done once on the first page load. Every linked
            clicked past that will only refresh the inner content div. Thanks again PJAX. So if all pages
            are stored with there unique javascript how do we know when our page can even process that js?
        </p>
        <h2><b>'ight, The Solution</b></h2>
        <p class="lead">It may have been obvious from the get go, but creating a new event listener to mean out document
            is
            REALLY loaded is the best solution. This is until you realize that javascript loaded in the browser,
            even in a div that gets refreshed stays in the documents memory. Each time you click a link the
            browser pops the state of your html with PJAX, but all of your event listeners are still active.
            So our final solution is to make each request active only once. A one time event listener.</p>

        <p class="lead"><b>Now all of our style sheets and javascript files can be placed under the content fold without
                worry of a asynchronous problems.</b></p>

        <!-- ============================================================= -->

        <ol class="breadcrumb">
            <li><i class="fa fa-code"></i>This file is highly modified and is not for production.
                One major part of this example wrapper is the <code>Carbon()</code> function in the head of the document.
                In the closing script we use the <code>$.fn.load_</code> scripts to ensure java dependancies are only loaded once.
                Google's <code>loadDeferredStyles()</code> function allows us to add our css between the <code>noscript</code> tags under the fold.
            </li>
        </ol>

        <div class="callout callout-warning">This document has all PHP stripped for readability. CarbonPHP normally loads
        the X_PJAX_Version dynamically in the head of our document. This allows us to force refresh the wrapper if we
        ever need to change content within. This is a problem when a change is made to the wrapper on the fly and users
        have cached version changing only the inner content through PJAX. A change to the <code>SITE_VERSION</code> in the
        CarbonPHP options will force refresh all users page to the new version.</div>

        <div class="callout callout-info">The main page content in this example would be loaded between the <class>#pjax-content</class> div.
               The content requested by our user is held in the <?=htmlentities('<?= \Carbon\View::$bufferedContent ?? \'\' ?>')?> and should
            be place in the PJAX container.
            </div></div>

        <pre>
            <?= highlight(SERVER_ROOT . APP_VIEW . 'Documentation/CodeExamples/WrapperHome.php', 'html') ?>
        </pre>


    </div>
</div><!-- /.content -->
