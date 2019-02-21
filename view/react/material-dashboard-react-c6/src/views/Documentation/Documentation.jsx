import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// react components for routing our app without refresh

import {Link} from "react-router-dom";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
// core components
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
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

import Navbar from "views/Documentation/Navbar";

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

        const {classes, ...rest} = this.props;

        let publicDocumentationRoutes = [
            {
                path: "/5.0/SectionNavbars",    // I'm leaving this here for the time being as an example
                name: "Section Navbars",    // This should be loaded under a different wrapper
                component: SectionNavbars
            },
            {
                path: "/5.0SectionBasics",
                name: "Section Basics",
                component: SectionBasics
            },
            {
                path: "/5.0/SectionTabs",
                name: "Section Tabs",
                component: SectionTabs
            },
            {
                path: "/5.0/SectionPills",
                name: "Section Pills",
                component: SectionPills
            },
            {
                path: "/5.0/SectionNotifications",
                name: "Section Notifications",
                component: SectionNotifications
            },
            {
                path: "/5.0/SectionTypography",
                name: "Section Typography",
                component: SectionTypography
            },
            {
                path: "/5.0/SectionJavascript",
                name: "Section Javascript",
                component: SectionJavascript
            },
            {
                path: "/5.0/SectionCompletedExamples",
                name: "Section Completed Examples",
                component: SectionCompletedExamples
            },
            {
                path: "/5.0/SectionLogin",
                name: "Section Login",
                component: SectionLogin
            },
            {
                path: "/5.0/ViewLoginPage",
                name: "View Login Page",
                component: (<GridItem md={12} className={classes.textCenter}>
                    <Link to={"/5.0/login-page"} className={classes.link}>
                        <Button color="primary" size="lg" simple>
                            View Login Page
                        </Button>
                    </Link>
                </GridItem>)
            },
            {
                path: "/5.0/SectionExamples",
                name: "Section Examples",
                component: SectionExamples
            },
            {
                path: "/5.0/SectionDownload",
                name: "Section Download",
                component: SectionDownload
            },
            {
                redirect: true,
                path: "/5.0",
                pathTo: "/5.0/SectionExamples",
                name: "SectionExamples"
            },
            {
                redirect: true,
                path: "/",
                pathTo: "/5.0/SectionExamples",
                name: "SectionExamples"
            }
        ];

        // let root = '/5.0';
        //
        // publicDocumentationRoutes = publicDocumentationRoutes.map(o => {
        //     if ('path' in o) {
        //         o.path = root + o.path;
        //     }
        //     if ('pathTo' in o) {
        //         o.pathTo = root + o.pathTo;
        //     }
        //     return o;
        // });

        return (
            <div>
                <Navbar routes={publicDocumentationRoutes}/>
                <div className={classNames(classes.main, classes.mainRaised)}>
                    {this.props.subRoutingSwitch(publicDocumentationRoutes, rest)}
                </div>
            </div>
        );
    }
}

export default withStyles(componentsStyle)(Documentation);
