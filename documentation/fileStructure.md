# File Structure & System Architecture

The [Zend Framework](https://framework.zend.com/manual/1.10/en/project-structure.project.html) has a very intuitive and
clear file architecture. We're going to use their recommended file hierarchy with a few tweaks. We do this because The
[Controller -> Model -> View (aka MVC because it rolls off the tong better)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
coding pattern is in alphabetical order. So in most editors you can think of it as a top-down approach. The following is
our

1) Controller - accept input and validates it for the model or view
    - If the controller returns null the model will be skipped in execution returning only the view. If the controller
      returns false, the model code layer and view will not be executed.
    - Data returned by controllers will be passed as parameters to the model.
2) Model - may accept data from the controller, but is not required
    - Models usually run functions provided in the Tables folder then work to prepare it for the view.
    - Tables should have a corresponding file of the same name as the MySQL table.
3) Tables - Auto-Generated classes used to preform database operations
    - Tables should be generated using the php index.php rest command.
4) View - holds all front end development data
    - React Javascript or Mustache templates are recommend

