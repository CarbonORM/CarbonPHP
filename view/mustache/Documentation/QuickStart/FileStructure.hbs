<!-- Content Header (Page header) -->
<div class="box box-solid box-default" style="margin-top: 20px;">
    <div class="box-header">
        <div class="content-header">
            <h1 class="text-purple" style="margin: 0">
                <i class="fa fa-folder"></i>
                <b>Recommended Project Directory Structure</b>
            </h1>
        </div>
    </div>

    <div class="box-body">
        <!-- Main content -->
        <div class="content body">
            <p>
                We decided <a href="https://framework.zend.com/manual/1.10/en/project-structure.project.html"
                              class="text-purple">Zend
                    Framework</a> has a very
                intuitive and clear file architecture. We're going to use their recommended file hierarchy with a few
                tweaks. We do this because
                The <a href="https://en.wikipedia.org/wiki/Model–view–controller" class="text-purple">Controller ->
                    Model -> View (MVC,
                    rolls
                    off the tong better) coding pattern</a>
                is in alphabetical order. So in most editors you can think of it as top down. I recommend viewing the
                files,
            </p>
            <br>
            <ol>
                <li><h4><b>Controllers - accepts input and converts it to commands for the model or view</b></h4>
                    <ul>
                        <li>If the controller returns false, or nothing, the model code layer will not be run</li>
                        <li>Data returned by will be passed as parameters to the model</li>
                    </ul>
                </li>
                <li><h4><b>Models - may accept data from the controller, but is not required</b></h4>
                    <ul>
                        <li>Models usually run functions provided in the Tables folder then work to prepare it for the
                            view
                        </li>
                        <li>Tables should have a corresponding file of the same name</li>
                    </ul>
                </li>
                <li><h4><b>Tables - preform database operations</b></h4>
                    <ul>
                        <li>Tables should extends 'Entities' and implements 'iTable'</li>
                    </ul>
                </li>
                <li><h4><b>View - holds all html data</b></h4>
                    <ul>
                        <li>All logic in the view should be based on presents of variables</li>
                        <li>Mustache templates are recommend</li>
                    </ul>
                </li>
            </ol>
            <br>
            <div class="col-md-offset-4 col-md-4">
                <a href="Installation">
                    <button type="button" class="btn bg-purple col-md-12" data-toggle="modal" data-target="#modal-success">
                        Setup a new application!
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
<pre class="hierarchy bring-up"><code class="language-bash" data-lang="bash">File Hierarchy of C6

      C6/
      ├── Application/
      │   ├── Controller/
      │   │     └──       Validate user input (type checks)
      │   ├── Model/
      │   │     └──       Validate against database + other database operations
      │   ├── Tables/
      │   │     └──       Every table in you database should implement iEntity
      │   ├── View/
      │   │     └──       HTML goes here (mostly likely a php file)
      │   ├── Route.php
      ├── config/
      │   ├── Config.php
      │   │     └──       CarbonPHP's configuration file
      │   └── setting.yml
      │         └──       Google App Engine Support (ignore if you do not host with google)
      │
      └── Data/
          ├── Indexes/
          │     └──       For lists, like state in the US..
          ├── Sessions/
          │     └──       You can choose where to store sessions in Config.php
          ├── Temp/
          ├── Uploads/
          └── Vendors/    Libraries like CarbonPHP will be stored here by Composer
</code></pre>

<section id="MVC">
    <div class="box box-solid box-default">
        <div class="box-header">
            <h2 style="margin:0;"><b>CarbonPHP</b> uses an MVC structure.</h2>
        </div>
        <div class="box-body">
            <p class="lead">
                The following function is used for many all routes involving <a href="<?=SITE?>" class="text-purple">user
                    input or dynamic view.</a>
            </p>
            <p>It is useful to separate the Controller->Model function for events that
                    only return json data. If the HTML requested is not dynamic than you can directly run the
                    <code>View::Content()</code> static method.
                    <a href="<?=SITE?>" class="text-purple">This will wrap the html in the provided wrapper.</a>
            </p>
            <pre>
        <code><?= highlight('
    /**Stands for Controller -> Model .
     *
     * This will run the controller/$class.$method().
     * If the method returns true the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model.
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed          the returned value from model/$class.$method() or false | void
     * @throws Exception
     */
    function CM(string &$class, string &$method, array &$argv = [])
    {
        $class = ucfirst(strtolower($class));   // Prevent malformed class names
        $controller = "Controller\\$class";     // add namespace for autoloader
        $model = "Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists
        if (!class_exists($controller, true) || !class_exists($model, true)) {
            throw new InvalidArgumentException("Invalid Class {$class} Passed to MVC");
        }
        // the array $argv will be passed as arguments to the method requested, see link above
        $exec = function ($class, $argv) use ($method) {
            return \call_user_func_array([new $class, "$method"], (array)$argv);
        };

        return catchErrors(function () use ($exec, $controller, $model, $argv) {
            if ($argv = $exec($controller, $argv)) {
                return $exec($model, $argv);
            }
            return $argv;
        })();
    }

    /** Stands for Controller -> Model -> View
     *
     * This will run the controller/$class.$method().
     * If the method returns true the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model. Finally the View will
     * be executed. The file should be in the APP_VIEW directory (set in config)
     * with the following naming convention
     *
     *  APP_VIEW / $class / $method . (php | hbs)  - We accept handlebar templates.
     *
     * The view will be processed server-side and returned
     *
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed          the returned value from model/$class.$method() or false | void
     * @throws Exception
     */
    function MVC(string $class, string $method, array &$argv = [])
    {
        CM($class, $method, $argv);  // Controller -> Model

        // This could cache or send
        $file = APP_VIEW . "$class/$method";

        if (!file_exists(SERVER_ROOT . $file . ($ext = \'.php\')) && !file_exists(SERVER_ROOT . $file . ($ext = \'.hbs\'))) {
            $ext = \'\';
        }
        return View::content($file . $ext);  // View
    }') ?>
        </code></pre>
            <br>
        </div>
</section><!-- /#introduction -->


