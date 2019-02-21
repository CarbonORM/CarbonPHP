import React from "react";
import PropTypes from "prop-types";
import cx from "classnames";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";

// core components
import Footer from "components/Footer/Footer";
import pagesRoutes from "routes/publicRoutes";

import sweetAlertStyle from "assets/jss/material-dashboard-react/views/sweetAlertStyle";
import appStyle from "assets/jss/material-dashboard-react/layouts/carbonPHPStyles";


import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks.jsx";
import publicRoutes from "../routes/publicRoutes";

// var ps;
const styles = theme => ({
  ...appStyle(theme),
  ...sweetAlertStyle
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

      const { classes, subRoutingSwitch, ...rest } = this.props;
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
        <HeaderTop
            brand="CarbonPHP.com"
            rightLinks={<HeaderLinks />}
            fixed
            color="transparent"
            changeColorOnScroll={{
                height: 400,
                color: "dark"
            }}
            routes={pagesRoutes}
            {...rest}
        />
        <div className={classes.wrapper} ref="wrapper">
          <div className={mainPanel} ref="mainPanel">
              {subRoutingSwitch(publicRoutes, this.props)}
            <Footer fluid />
          </div>
        </div>
      </div>
    );
  }
}

Public.propTypes = {
  classes: PropTypes.object.isRequired
};

export default withStyles(styles)(Public);
