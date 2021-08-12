import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";
import Button from "@material-ui/core/Button/Button";

// TODO - <GridContainer justify="center"> ?????
class Template extends React.Component {
    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={0} sm={0} md={2}> </GridItem>
                        <GridItem xs={12} sm={12} md={8}>
                            <h1>Support </h1>
                            <p>
                                CarbonPHP is free software made for love of a better web.
                                Please say thanks to any all who help you in the forums. To raise an
                                <a href={"https://github.com/RichardTMiles/CarbonPHP/issues"}
                                   rel="noopener noreferrer"
                                   target="_blank"> issue on Github</a>. If
                                priority should
                                be desired please reach out to <a href={"https://miles.systems/Mail"}
                                                                  rel="noopener noreferrer"
                                                                  target="_blank">our team here</a>.
                            </p>
                            <div className={classes.justifyContentCenter}>
                                <Button
                                    color="secondary"
                                    size="lg"
                                    href="https://opensource.guide/"
                                    rel="noopener noreferrer"
                                    target="_blank">
                                    Open Source Guides
                                </Button>
                            </div>
                            <p>
                                CarbonPHP is an open source project that is licensed under the MIT license. This allows
                                you to do pretty much anything you want as long as you include the copyright in "all
                                copies or substantial portions of the Software." Attribution is not required (though
                                very much appreciated).
                            </p>
                        </GridItem>
                    </GridContainer>
                </div>
            </div>
        )
    }
}

export default withStyles(completedStyle)(Template);
