import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// react components for routing our app without refresh

import {Link} from "react-router-dom";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
import Parallax from "components/Parallax/Parallax.jsx";
// sections for this page
import SectionBasics from "./Sections/SectionBasics.jsx";
import SectionNavbars from "./Sections/SectionNavbars.jsx";
import SectionTabs from "./Sections/SectionTabs.jsx";
import SectionPills from "./Sections/SectionPills.jsx";
import SectionNotifications from "./Sections/SectionNotifications.jsx";
import SectionTypography from "./Sections/SectionTypography.jsx";
import SectionJavascript from "./Sections/SectionJavascript.jsx";
import SectionCompletedExamples from "./Sections/SectionCompletedExamples.jsx";
import SectionLogin from "./Sections/SectionLogin.jsx";
import SectionExamples from "./Sections/SectionExamples.jsx";
import SectionDownload from "./Sections/SectionDownload.jsx";

import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";



class Documentation extends React.Component {
    constructor() {
        super();
        this.state = {
            isLoaded: false
        }
    }
    componentDidMount() {
        this.setState({
            isLoaded: true
        });
    }
    render() {
        console.log("Documentation JSX RENDER");

        console.log(this.props);

        const {classes, subRoutingSwitch, ...rest} = this.props;

        let publicDocumentationRoutes = [
            {
                path: "/SectionNavbars",    // I'm leaving this here for the time being as an example
                name: "/SectionNavbars",    // This should be loaded under a different wrapper
                navbarName: "/SectionNavbars",
                component: SectionNavbars
            },
            {
                path: "SectionBasics",
                name: "/SectionBasics",
                navbarName: "/SectionBasics",
                component: SectionBasics
            },
            {
                path: "/SectionTabs",
                name: "/SectionTabs",
                navbarName: "/SectionTabs",
                component: SectionTabs
            },
            {
                path: "/SectionPills",
                name: "/SectionPills",
                navbarName: "/SectionPills",
                component: SectionPills
            },
            {
                path: "/SectionNotifications",
                name: "/SectionNotifications",
                navbarName: "/SectionNotifications",
                component: SectionNotifications
            },
            {
                path: "/SectionTypography",
                name: "/SectionTypography",
                navbarName: "/SectionTypography",
                component: SectionTypography
            },
            {
                path: "/SectionJavascript",
                name: "/SectionJavascript",
                navbarName: "/SectionJavascript",
                component: SectionJavascript
            },
            {
                path: "/SectionCompletedExamples",
                name: "/SectionCompletedExamples",
                navbarName: "/SectionCompletedExamples",
                component: SectionCompletedExamples
            },
            {
                path: "/SectionLogin",
                name: "/SectionLogin",
                navbarName: "/SectionLogin",
                component: SectionLogin
            },
            {
                path: "/ViewLoginPage",
                name: "/ViewLoginPage",
                navbarName: "/ViewLoginPage",
                component: (<GridItem md={12} className={classes.textCenter}>
                    <Link to={"/login-page"} className={classes.link}>
                        <Button color="primary" size="lg" simple>
                            View Login Page
                        </Button>
                    </Link>
                </GridItem>)
            },
            {
                path: "/SectionExamples",
                name: "/SectionExamples",
                navbarName: "/SectionExamples",
                component: SectionExamples
            },
            {
                path: "/SectionDownload",
                name: "/SectionDownload",
                navbarName: "/SectionDownload",
                component: SectionDownload
            },
            {
                redirect: true,
                path: "/",
                pathTo: "/SectionNavbars",
                navbarName: "SectionNavbars"
            }
        ];

        let root = '/5.0';

        publicDocumentationRoutes = publicDocumentationRoutes.map(o => {
            if ('path' in o) {
                o.path = root + o.path;
            }
            if ('pathTo' in o) {
                o.pathTo = root + o.pathTo;
            }
            return o;
        });

        return (
            <div>
                <Parallax image={require("assets/img/Carbon-teal-180.png")}>
                    <div className={classes.container}>
                        <GridContainer>
                            <GridItem>
                                <div className={classes.brand}>
                                    <h1 className={classes.title}>CarbonPHP [C6]</h1>
                                    <h3 className={classes.subtitle}>
                                        Build full scale applications in minutes.
                                    </h3>
                                </div>
                            </GridItem>
                        </GridContainer>
                    </div>
                </Parallax>

                <div className={classNames(classes.main, classes.mainRaised)}>
                    {subRoutingSwitch(publicDocumentationRoutes, this.props)}
                </div>
            </div>
        );
    }
}

export default withStyles(componentsStyle)(Documentation);
