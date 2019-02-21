import React from "react";
import PropTypes from "prop-types";
import cx from "classnames";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";

// core components
import Footer from "components/Footer/Footer";
import pagesRoutes from "routes/publicRoutes";

import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import navbarsStyle from "assets/jss/material-kit-react/views/componentsSections/navbarsStyle.jsx";
import sweetAlertStyle from "assets/jss/material-dashboard-react/views/sweetAlertStyle";
import appStyle from "assets/jss/material-dashboard-react/layouts/carbonPHPStyles";

import Documentation from "views/Documentation/Documentation.jsx";

import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks.jsx";
// import Sections from "views/Documentation/Sections/Sections";
// import publicRoutes from "../routes/publicRoutes";
import Parallax from "components/Parallax/Parallax";
import GridContainer from "components/Grid/GridContainer";
import GridItem from "components/Grid/GridItem";

// var ps;
const styles = theme => ({
    ...appStyle(theme),
    ...sweetAlertStyle,
    ...navbarsStyle(theme),
    ...componentsStyle
});


class Public extends React.Component {
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
        console.log("Public JSX RENDER");

        const {classes, ...rest} = this.props;
        const mainPanel =
            classes.mainPanel +
            " " +
            cx({
                [classes.mainPanelSidebarMini]: false,
                [classes.mainPanelWithPerfectScrollbar]:
                navigator.platform.indexOf("Win") > -1
            });
        // console.log('PUBLIC JSX RENDER');
        if (!this.state.isLoaded) {
            return null;
        }

        // transparent here seems to work 50% the time, replace with dark if trouble persists
        return (
            <div>
                <div className={classes.wrapper} ref="wrapper">
                    <HeaderTop
                        brand="CarbonPHP.com"
                        rightLinks={<HeaderLinks/>}
                        fixed
                        color="dark"
                        changeColorOnScroll={{
                            height: 400,
                            color: "dark"
                        }}
                        routes={pagesRoutes}
                        {...rest}
                    />
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
                    <div className={mainPanel} ref="mainPanel">
                        <Documentation {...rest} />
                    </div>
                    <Footer fluid/>
                </div>
            </div>
        );
    }
}

Public.propTypes = {
    classes: PropTypes.object.isRequired
};

export default withStyles(styles)(Public);
