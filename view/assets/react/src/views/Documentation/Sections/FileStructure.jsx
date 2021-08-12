import React from "react";
// react plugin for creating date-time-picker
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import javascriptStyles from "assets/jss/material-kit-react/views/componentsSections/javascriptStyles.jsx";

class SectionJavascript extends React.Component {
    anchorElLeft = null;
    anchorElTop = null;
    anchorElBottom = null;
    anchorElRight = null;

    constructor(props) {
        super(props);
        this.state = {
            classicModal: false,
            openLeft: false,
            openTop: false,
            openBottom: false,
            openRight: false
        };
    }

    handleClickOpen(modal) {
        var x = [];
        x[modal] = true;
        this.setState(x);
    }

    handleClose(modal) {
        var x = [];
        x[modal] = false;
        this.setState(x);
    }

    handleClosePopover(state) {
        this.setState({
            [state]: false
        });
    }

    handleClickButton(state) {
        this.setState({
            [state]: true
        });
    }

    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={0} sm={0} md={1}> </GridItem>
                        <GridItem xs={12} sm={12} md={8}>
                            <div className={[classes.title, classes.textCenter]}>
                                <h2>File Structure & System Architecture</h2>
                            </div>
                            <p>
                                The <a
                                href="https://framework.zend.com/manual/1.10/en/project-structure.project.html"
                                className="text-purple">Zend
                                Framework</a> has a very
                                intuitive and clear file architecture. We're going to use their recommended file
                                hierarchy with a few
                                tweaks. We do this because
                                The <a href="https://en.wikipedia.org/wiki/Model–view–controller"
                                       className="text-purple">Controller -&gt;
                                Model -&gt; View (aka MVC because it
                                rolls
                                off the tong better) coding pattern</a>
                                is in alphabetical order. So in most editors you can think of it as a top down approach.
                                The following is our
                            </p>

                            <ol>
                                <li><h4><b>Controller - accept input and validates it for the model or
                                    view</b></h4>
                                    <ul>
                                        <li>If the controller returns null the model will be skipped in execution
                                            returning only the view.
                                            If the controller returns false neither the model code layer or view will
                                            not be executed.
                                        </li>
                                        <li>Data returned by controllers will be passed as parameters to the model.</li>
                                    </ul>
                                </li>
                                <li><h4><b>Model - may accept data from the controller, but is not required</b></h4>
                                    <ul>
                                        <li>Models usually run functions provided in the Tables folder then work to
                                            prepare it for the
                                            view.
                                        </li>
                                        <li>Tables should have a corresponding file of the same name as the MySQL
                                            table.
                                        </li>
                                    </ul>
                                </li>
                                <li><h4><b>Tables - Auto-Generated classes used to preform database operations</b></h4>
                                    <ul>
                                        <li>Tables should generated using the <code>php index.php rest</code> command.
                                        </li>
                                        <li>For more information <code>php index.php rest -json</code>.</li>
                                    </ul>
                                </li>
                                <li><h4><b>View - holds all front end development data</b></h4>
                                    <ul>
                                        <li>All logic in the view should be based on presents of variables</li>
                                        <li>React Javascript or Mustache templates are recommend</li>
                                    </ul>
                                </li>
                            </ol>
                            <p>
                                <h4><b>File hierarchy of C6 applications</b></h4>
                                <ul>
                                    <li><h5>config/</h5>
                                        <ul>
                                            <li>This folder houses the Config.php file, this will most likely need to
                                                edited for you database credentials.
                                            </li>
                                            <li>buildDatabase.php aslo exists in this directory. This is automatically
                                                generated and should not be directly edited.
                                                See C6 Cli programs for more information.
                                            </li>
                                        </ul>
                                    </li>
                                    <li><h5>controller/</h5>
                                        <ul>
                                            <li>Validate user input (type checks)</li>
                                        </ul>
                                    </li>
                                    <li><h5>model/</h5>
                                        <ul>
                                            <li>Validate against database + other database operations</li>
                                        </ul>
                                    </li>
                                    <li><h5>tables/</h5>
                                        <ul>
                                            <li>Table classes are automatically generated with REST</li>
                                        </ul>
                                    </li>
                                    <li><h5>view/</h5>
                                        <ul>
                                            <li>All things related to the view go here</li>
                                            <li>This could be HTML or React based templating</li>
                                        </ul>
                                    </li>
                                    <li><h5>index.php</h5>
                                        <ul>
                                            <li>This starts composer, initiates CarbonPHP, then runs the websites
                                                routing file.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <h5>[website].php</h5>
                                        <ul>
                                            <li>This is typically named after the domain name and is the routing
                                                bootstrap file.
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </p>
                        </GridItem>
                    </GridContainer>
                    <br/>
                    <br/>
                    <br/>
                </div>
            </div>
        );
    }
}

export default withStyles(javascriptStyles)(SectionJavascript);
