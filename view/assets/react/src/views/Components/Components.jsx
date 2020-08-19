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
import SectionCarousel from "./Sections/SectionCarousel";

class Components extends React.Component {
    render() {
        const {classes} = this.props;
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
                    <SectionCarousel/>
                    <SectionNavbars/>
                    <SectionBasics/>
                    <SectionTabs/>
                    <SectionPills/>
                    <SectionNotifications/>
                    <SectionTypography/>
                    <SectionJavascript/>
                    <SectionCompletedExamples/>
                    <SectionLogin/>
                    <GridItem md={12} className={classes.textCenter}>
                        <Link to={"/login-page"} className={classes.link}>
                            <Button color="primary" size="lg" simple>
                                View Login Page
                            </Button>
                        </Link>
                    </GridItem>
                    <SectionExamples/>
                    <SectionDownload/>

                </div>
            </div>
        );
    }
}

export default withStyles(componentsStyle)(Components);
