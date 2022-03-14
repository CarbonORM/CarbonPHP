import React from "react";

// nodejs library that concatenates classes
import classNames from "classnames";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import GridItem from "components/Grid/GridItem.jsx";
import CarbonPHP from "./Sections/CarbonPHP";
import Dependencies from "./Sections/Dependencies";
import Implementations from "./Sections/Implementations";

// FileStructure OptionsIndex Bootstrap Wrapper ParallelProcessing
import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import Navbar from "views/Documentation/Navbar";
import Parallax from "../../components/Parallax/Parallax";
import GridContainer from "../../components/Grid/GridContainer";
import Footer from "../../components/Footer/Footer";


import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks";
import Changelog from "./Sections/Changelog";

import Support from "./Sections/Support";
import License from "./Sections/License";
import carbonphp from "variables/carbonphp"
import PropTypes from "prop-types";


// react components for routing our app without refresh
// @material-ui/icons
// core components

// sections for this page
// import Sections from "views/Documentation/Sections/Sections";


class Documentation extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaded: false,
        }
    }

    componentDidMount() {
        this.setState({
            isLoaded: true,
        });
    }


    render() {

        let documentationVersion = carbonphp.documentationVersionURI;

        console.log("Documentation JSX RENDER");

        console.log(this.props);

        const {classes, ...rest} = this.props;

        let publicDocumentationRoutes = [
            {
                path: "/" + documentationVersion + "/Documentation/CarbonPHP",     // I'm leaving this here for the time being as an example
                name: "Documentation",          // This should be loaded under a different wrapper
                component: CarbonPHP
            },
            {
                path: "/" + documentationVersion + "/Documentation/Dependencies",
                name: "Dependencies",
                component: Dependencies
            },
            {
                path: "/" + documentationVersion + "/Documentation/BrowserOSSupport",
                name: "Changelog",
                component: Changelog
            },
            {
                path: "/" + documentationVersion + "/Documentation/Implementations",
                name: "Implementations",
                component: Implementations
            },
            {
                path: "/" + documentationVersion + "/Documentation/Support",
                name: "Support",
                component: Support
            },
            {
                path: "/" + documentationVersion + "/Documentation/License",
                name: "License",
                component: License
            },
            {
                redirect: true,
                path: "/" + documentationVersion,
                pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
                name: "Examples"
            },
            {
                redirect: true,
                path: "/" + documentationVersion + "/",
                pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
                name: "Examples"
            },
            {
                redirect: true,
                path: "/",
                pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
                name: "Examples"
            }
        ];

        if (!this.props.pureWordpressPluginConfigured) {

            publicDocumentationRoutes.push({
                path: "/" + documentationVersion + "/Documentation/Wordpress",     // I'm leaving this here for the time being as an example
                name: "Wordpress",          // This should be loaded under a different wrapper
                component: Support
            });

        }

        // todo - if we were to merge version ia PHP ^7.4 application tool kit & frameworkng with code it would be here {this.props.subRoutingSwitch(publicDocumentationRoutes, rest)}
        return <>
            <HeaderTop
                fixed
                {...rest}
                brand="CarbonPHP.com"
                darkMode={this.props.darkMode}
                rightLinks={<HeaderLinks
                    axios={this.props.axios}
                    versions={this.props.versions}
                    darkMode={this.props.darkMode}
                    switchDarkAndLightTheme={this.props.switchDarkAndLightTheme}
                />}
                color={window.pageYOffset < 400 ? "transparent" : (this.props.darkMode ? "transparent" : "info")}
                changeColorOnScroll={{
                    height: 400,
                    color: this.props.darkMode ? "dark" : "info"
                }}
            />
            <Parallax
                image={this.props.darkMode ? "/view/assets/img/Carbon-teal-180.png" : "/view/assets/img/Full-Carbon-Teal-White-1920x1080.png"}>
                <div className={classes.container}>
                    <GridContainer>
                        <GridItem>
                            <div className={classes.brand}>
                                <h1 className={classes.title}
                                    style={{color: (this.props.darkMode ? "white" : "black")}}>CarbonPHP
                                    [C6]</h1>
                                <h3 className={classes.subtitle}
                                    style={{color: (this.props.darkMode ? "white" : "black")}}>
                                    A PHP 7.4.* Library
                                </h3>
                            </div>
                        </GridItem>
                    </GridContainer>
                </div>
            </Parallax>
            <div>
                <div>

                    <Navbar
                        axios={this.props.axios}
                        color={this.props.darkMode ? "dark" : "info"}
                        darkMode={this.props.darkMode}
                        className={classNames(classes.main, classes.mainRaised)}
                        routes={publicDocumentationRoutes}/>

                    <div className={classNames(classes.main, classes.mainRaised)} style={
                        {
                            backgroundColor: (this.props.darkMode ? "black" : "white"),
                            color: (this.props.darkMode ? "white" : "black"),
                            fontSize: "+1.2em",
                            lineHeight: "+1.8em"
                        }
                    }>
                        {this.props.subRoutingSwitch(publicDocumentationRoutes, rest)}
                    </div>

                </div>

            </div>
            <Footer fluid/>
        </>
            ;
    }
}

Documentation.propTypes = {
    classes: PropTypes.object.isRequired,
};

export default withStyles(componentsStyle)(Documentation);
