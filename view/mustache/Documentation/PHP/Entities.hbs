<!-- Content Header (Page header) -->
<div class="row" style="margin-top: 20px">
    <div class="col-md-6">
        <div class="box box-solid box-default">
            <div class="box-header">
                <h1 style="margin:0">
                    Entities
                    <small>many-to-may</small>
                </h1>
            </div>

            <!-- Main content -->
            <div class="box-body">
                <p class="lead">
                    CarbonPHP uses a many-to-many relational database model.</p>
                <p>We implement this by
                    having a <a href="<?=SITE?>Entities#Transactions" class="text-purple">master table</a> that contains only primary keys. This proves helpful when
                    managing fully dynamic websites where a column may depend on multiple other tables.
                    For example, if we wanted to built a comment box that allowed any number of sub comments,
                    images to be posted on comments, and reactions (such as liking) to comments than we would
                    need a way to relate all of these items to the original post. A relational database helps
                    us achieve this by means of Cascading, Deleting, Setting to Null, Setting to Default, and
                    No Action directives. These methods describe what should happen to a column entry if it's
                    parent should be deleted. To continue on the example above, if the article where all of these
                    posts are located is deleted than every post, image, and reaction needs to be deleted. This
                    is the DELETE directive built into InnoDB.</p>

            </div>
        </div>
        <div class="box box-solid box-info" id="Transactions">
            <div class="box-header">
                <h1 style="margin: 0"> Transactions</h1>
            </div>
            <div class="box-body">
                <p class="lead">The master table</p>
                <img class="img-responsive" src="<?= SITE . APP_VIEW ?>Img/documentation/carbon-db.png">
                <br>

                <p>The table structure, carbon, described above is how our application will manage all primary keys.
                    The primary key must be inserted and committed before other columns may reference it. This
                    is problematic should your insertion query fail. We solve this timing problem by using a
                    transaction system modeled off PHP's PDO class.</p>

                <pre><code>Carbon\Entities::beginTransaction();</code></pre>

                <p>The method above will insert a new key to the Carbon table then return its value as a string.
                    You should use this method for all primary key transactions and use its return for your foreign
                    key column. After your query has successfully run you must finish by committing the transaction
                    to memory.</p>

                <pre><code>Carbon\Entities::commit();</code></pre>

                <p>We strictly follow the <a href="<?= SITE ?>FileStructure">MVC patten</a> in C6. This means the
                    Controller and Model are wrapped in a Try // Catch block. We attach a Finally Clause meaning
                    no matter what state we are in the following code will be run.</p>

                <pre><code>Carbon\Entities:verify();</code></pre>

                <p>If the verify function is reached and a transaction has not been committed, or threw an error,
                    All keys created by <code>beginTransaction()</code> will be removed. It should be noted that
                    Begin transaction can be run multiple times; however, <code>commit()</code> will finalize all
                    keys.</p>

                <p></p>
                <!-- ============================================================= -->

            </div><!-- /.content -->
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid box-success">
            <div class="box-header">
                <h1 style="margin: 0">Predefined Tables</h1>
            </div>
            <div class="box-body">
                <div class="callout callout-warning">If you plan to using an existing database be sure to name it
                    correctly before deployment. CarbonPHP automatically builds a new database if it does not already exist.
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p>The <u>CarbonPHP library</u> will automatically set up the following tables:</p>
                        <ul>
                            <li>carbon</li>
                            <li>carbon_reports</li>
                            <li>carbon_sessions</li>
                            <li>carbon_tags</li>
                            <li>carbon_tag</li>
                            <li>sessions</li>
                        </ul>
                        <p style="margin-top:5px">The <u>C6 framework</u> uses the <a href="<?=SITE?>Options">['DATABASE']['DB_BUILD'] option</a> to construct its own set
                        of tables after CarbonPHP's build is complete. You should add all new tables to this build file.</p>

                        <ul>
                            <li>carbon_users</li>
                            <li>carbon_locations</li>
                            <li>carbon_comments</li>
                            <li>carbon_photos</li>
                            <li>user_followers</li>
                            <li>user_messages</li>
                            <li>user_tasks</li>
                        </ul>

                    </div>
                    <img class="img-responsive col-md-6"
                         src="<?= SITE . APP_VIEW ?>Img/documentation/carbon-tables.png">
                </div>
                <div class="col-md-12">
                    <p class="lead">Each time you create a new table you should immediately think to do two thing:</p>
                    <ol>
                        <li>Add a class with the exact same name as your table suffix. If your table has a foreign key
                            dependency on another table exclusively the table should be
                            prefixed with the parent tables name.
                            <ul>
                                <li>This class should be placed in the "Application/Table/" folder.</li>
                                <li>It must implement the "Carbon\Interfaces\iTable" contract</li>
                                <li>It should extend the "Carbon\Entities" class</li>
                            </ul>
                        </li>
                        <li>Add the table structure to your auto database builder.
                            <ul>
                                <li>This file should be modeled off our buildDatabase file</li>
                                <li>You can define the location of your builder in the options</li>
                            </ul>
                        </li>
                    </ol>
                    <p>It's important to add the table to your build file, this ensures portability among
                        team members and the application itself must it ever need to change servers.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="box box-solid box-default">
    <div class="box-header">
        <h1 style="margin:0">The Entities Class</h1>
    </div>
    <div class="box-body">
        <ol class="breadcrumb">
            <li><i class="fa fa-code"></i> APP_ROOT</li>
            <li>Data</li>
            <li>Vendors</li>
            <li>richardtmiles</li>
            <li>Structure</li>
            <li>Entities.php</li>
        </ol>
        <p class="lead">This is designed to streamline sql queries. Using the <a href="http://php.net/manual/en/book.pdo.php">PHP Data Object (PDO)
                is our biggest defence against XSS attacks</a>.</p>
        <pre><code><?=highlight(APP_ROOT.'Data/Vendors/richardtmiles/carbonphp/Structure/Entities.php')?></code></pre>
    </div>
</div>

