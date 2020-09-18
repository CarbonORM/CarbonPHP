import React from "react";
// @material-ui/core components
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";
import SequenceDiagram from "assets/img/SD.png";

// @material-ui/icons


class Overview extends React.Component {
  render() {
    const { classes } = this.props;
    return (
      <>
        <p>
          The sequence diagram below and description even further make up a brief low level
          outline of the C6 internals. The diagram can be thought of as a road map to
          how code spreads out over multiple files and functions, stacks together, and complete
          the task at hand. Each block represents the major files that makeup C6. While all
          code is indeed important, it is sometimes useful to understand that something exist
          without going into to much detail. The most important takeaways are: <b>dynamic
          routes conforming to C6 standards will use an MVC where controllers
          validate all user input, model layers update and insert data into the database,
          and views strictly print data to the user. </b>
        </p>
        <p>
          If user input is taken it must be validated to protect against cross site scripting
          attacks. The guys over at OWASP do a good job explaining the complexity of protecting
          yourself against a XSS attack. Simply put, if the user is capable of modifying the
          information, a variable we need in a routine, it must be validated. The MVC pattern
          is simplistic in that its separation of concerns is conducive to good validation
          practices.
        </p>
        <p>
          The bootstrap is where you define your application. Most bootstraps are named after the
          website they are running. This website uses <b>CarboPHP/C6::class</b> and
          https://Stats.Coach/ uses <b>StatsCoach::class</b>. It's probably worth noting each
          class should be in a file named the same name of the class, and if you're lost you
          should
          check out the <b>N00B Guid for beginners</b>.
          This bootstrap typically contains little to no business logic and only maps urls to
          other methods.
          In a pure C6 implementation the first step after a uri is matched is the controller.
        </p>
        <b>{'$this->structure($this->MVC());'}</b>
        <br/>
        <b>{'$this->match(\'Recover/{user_email?}/{user_generated_string?}\', \'User\', \'recover\')()'}</b>
        <p>
          We would expect to find the above code in the bootstrap. This would move to
          the <b>Controller/User </b>
          class mapped by composers psr4 standard.
          C6 also features a runtime psr4 auto loading feature, though it is not recommended over composer's.
          For legacy reasons it is remains a permanent fixture.
          More on this later, but lets take a look at whats inside this file.
        </p>


        <p>
          or url mapping file, we phase any
          url parameters and send them to the Controller. This is not the only data that must
          be validated. All form data is received in the $_POST[], $_GET[], $_FILES[], $_COOKIE[],
          ect.. super globals predefined by PHP must also be validated.
        </p>

        <GridContainer justify="center">
          <GridItem xs={12} sm={12}>

            <img
              src={SequenceDiagram}
              alt="..."
              className={
                classes.imgRaised +
                " " +
                classes.imgRounded +
                " " +
                classes.imgFluid
              }
            />
          </GridItem>
        </GridContainer>
        <br/><br/>
        <h2>title C6 MVC Structure</h2>
        <p>
          C6 MVC Structure
        </p>
        <br/>
        <b>Browser-&gt;+Index: 1</b>
        <br/>
        <h3>1) A 'user' request is received by our server<br/></h3>
        <br/>
        <br/>
        <h3>2) Send relative path to configuration file as string<br/></h3>
        <br/>
        <h3>3) Setup with option and define global helper functions<br/></h3>
        <br/>
        <h3>4) Returns the C6 Instance<br/></h3>
        <br/>
        <h3>5) Pass a class that extends <b>CarbonPHP/Application::class</b>.</h3>
        <br/>
        The above implies that the following to abstractions are present in your routing
        class::<br/><br/>
        <b>abstract public function startApplication($uri = null) : bool;</b><br/>
        <small>Defined in the <b>CarbonPHP/Application::class</b></small>
        <br/><br/>
        <b>abstract public function defaultRoute();</b><br/>
        <small>Defined in the <b>CarbonPHP/Route::class</b> which is extend by the
          <b>Application::class</b>
        </small>
        <br/><br/><br/>
        <b>C6-&gt;+Bootstrap: 6</b><br/>
        <h3>6) Runs the global function <b>startApplication( YouRoutingClass::class )</b>.
        </h3><br/>
        <small>
          This will ultimately run <br/>
          <b>(new YourRoutingClass::class)-&gt;startApplication( $uri )</b><br/>
          defined in your route class. <b>startApplication</b> is designed to allow
          recursive program flow.<br/>
          So between steps 6-15
          you may run <b>startApplication</b> again, thereby repeating 6-15 within
          6-15 then continuing execution
          where you called <b>startApplication</b>.<br/>
          The first invocation of the global <b>startApplication</b> function will
          statically store the routing classes definition.<br/><br/>
          With each successive call to <b>startApplication( $uri )</b>, you should path
          the desired
          route to re-match. Keep in mind that the first call this function is done
          automatically with
          the invocation of the CarbonPHP class object.<br/><br/>
          <b>startApplication( '/profile' )</b>
          <br/><br/>
          The '/' page, or home page, will always run the <br/>
          <b>YourRoutingClass-&gt;defaultRoute();</b><br/>
          then return to the index.

        </small>
        <br/><br/>
        <b>Bootstrap-&gt;Bootstrap: 7</b>
        <h3>7) Set <b>$this-&gt;structure( $this-&gt;MVC() );</b> as the method to use is a match
          is
          found.</h3>
        <b>Bootstrap-&gt;+Controller: 8</b>
        8) Passes provided arguments to match followed by url variables. The
        controllers job is to strictly validate data. This could mean database
        requests, but typically does not. By design, no database modification
        should be made in this step.
        <b>Controller--&gt;-Bootstrap: 9</b>
        9) The responce to validation.
        If false is returned from the controller, the program execution will effectively
        stop.
        The stack will be returned to the index and safely exit with no responce.
        If <b>null</b> is
        returned from the controller the model layer will be skipped and the view/responce
        will invoke next.
        If a value is returned from the controller (effectively equating to true), the value
        will be passed as a function argument to the
        model. If an array is returned from the controller, the list will be unpacked and
        values will be
        passed as individual arguments to the model.

        <b>
          opt</b>
        <br/><b>
        opt</b>
        <b>
          Bootstrap-&gt;+Model: 10</b>
        10) The Bootstrap will logically decide what file and function should be executed
        next. If a value other than null or false is returned from the controller, the model
        will run.
        All data is this step is considered validated. This step is generally reserved for
        most database requests.
        If a database Post or Update is required, this is the only place it should be done.

        <b>
          Model--&gt;-Bootstrap: 11
        </b>
        11) The model can still cancel the view from sending by returning false. This
        returns the stack to the index
        and safely exits.
        end
        <br/>
        opt
        Bootstrap-&gt;+View: 12
        12) The view is typically handled by CarbonPHP's built-in internals. You can choose
        to render Mustache Templates or PHP files from the <b>View::content()</b> method.
        The method will decide which to use based off the files extension.
        note over View,Browser: 13
        13) Print and send the content. This could be a JSON, HTML, or any other vector of
        responce.

        View--&gt;-Bootstrap: 14
        14) Safely returning
        end<br/>
        <br/>
        end

        Bootstrap--&gt;-C6: 15
        15) Safely returning
        C6--&gt;-Index: 16
        16) Safely returning
        Index-&gt;-Browser: 17
        17) All code is finished and the connection is closed.
      </>
    );
  }
}

export default withStyles({ ...completedStyle, ...typographyStyle })(Overview);
