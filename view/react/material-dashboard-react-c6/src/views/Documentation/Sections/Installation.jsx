import React from "react";
// react components for routing our app without refresh
import {Link} from "react-router-dom";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
import exampleStyle from "assets/jss/material-kit-react/views/componentsSections/exampleStyle.jsx";

import landing from "assets/img/Carbon-teal.png";
import profile from "assets/img/Carbon-green.png";

class SectionExamples extends React.Component {
    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={12} sm={12} md={6}>
                            <img
                                src={landing}
                                alt="..."
                                className={
                                    classes.imgRaised +
                                    " " +
                                    classes.imgRounded +
                                    " " +
                                    classes.imgFluid
                                }
                            />
                            <Button color="primary" size="lg" href="https://github.com/RichardTMiles/CarbonPHP"
                                    target="_blank"
                                    className={classes.link} simple>
                                CarbonPHP [C6]
                            </Button>
                        </GridItem>
                        <GridItem xs={12} sm={12} md={6}>
                            <Link to="profile-page" className={classes.link}>
                                <img
                                    src={profile}
                                    alt="..."
                                    className={
                                        classes.imgRaised +
                                        " " +
                                        classes.imgRounded +
                                        " " +
                                        classes.imgFluid
                                    }
                                />
                                <Button color="primary" size="lg" href="https://github.com/RichardTMiles/Stats.Coach"
                                        target="_blank"
                                        simple>
                                    Stats Coach
                                </Button>
                            </Link>
                        </GridItem>
                        <GridContainer className={classes.textCenter} justify="center">
                            <GridItem xs={12} sm={12} md={8}>
                                <h2>Quick Start</h2>
                                <p>If you want to add C6 to an existing project you may use the following<br/>
                                    <code>composer require “richardtmiles/carbonphp:dev-master"</code></p>

                                <h4>
                                    I have launched{" "}
                                    <a
                                        href="https://github.com/RichardTMiles/CarbonPHP"
                                        rel="noopener noreferrer"
                                        target="_blank"
                                    >
                                        CarbonPHP{" "}
                                    </a> as a tool for your developement needs. It has a huge number of components and a
                                    fair amount of documentation. Stats Coach is an open source for profit project that highlights.
                                    the uses of C6. I recommend using this as an example to guid your development. Downloading C6
                                    will get you this documentation website in HTML 5 and REACT, whereas downloading Stats Coach
                                    will give you a database connected application in only HTML 5.
                                </h4>
                                <p className="lead">
                                    Before you can run the website/webapp on the enviroment you choose, you'll need to run a two commands.
                                    <br/>
                                    <code>composer install</code>
                                    <br/>
                                    <code>npm install</code>
                                    <br/><br/>
                                    As listed in the dependancies, you'll need to have composer and npm installed globally for
                                    the above commands to work.<br/><br/>

                                    You'll need to edit the database configurations in the <b>/cofig/Config.php</b> file.
                                    <br/><br/>If you've downloaded the files locally, you can use the following command<br/>
                                    <code>php -S localhost:80 index.php</code><br/><br/>
                                    Then navigate to <b>localhost</b> in the browser to see the website in action.
                                </p>
                            </GridItem>
                        </GridContainer>
                    </GridContainer>
                </div>
            </div>
        );
    }
}

export default withStyles(exampleStyle)(SectionExamples);
