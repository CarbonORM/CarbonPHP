import React from "react";
import PropTypes from "prop-types";
import cx from "classnames";
import { Switch, Route, Redirect } from "react-router-dom";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";

// core components
// import PagesHeader from "praesidium/common/components/layouts/Header/PagesHeader.jsx";
import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks.jsx";

import Footer from "components/Footer/Footer";
import pagesRoutes from "routes/publicRoutes";

import sweetAlertStyle from "assets/jss/material-dashboard-react/views/sweetAlertStyle";
import appStyle from "assets/jss/material-dashboard-react/layouts/carbonPHPStyles";


// import bgImage from "assets/img/register.jpeg";
import PageNotFound from "views/Errors/PageNotFound";

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
    const { classes, ...rest } = this.props;
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
    return (
      <div>
        <HeaderTop
            brand="CarbonPHP.com"
            rightLinks={<HeaderLinks />}
            fixed
            color="transparent"
            changeColorOnScroll={{
                height: 400,
                color: "rose"
            }}
            {...rest}
        />
        <div className={classes.wrapper} ref="wrapper">
          <div className={mainPanel} ref="mainPanel">
            <Switch>
              {pagesRoutes.map((prop, key) => {
                if (prop.collapse) {
                  return null;
                }
                if (prop.redirect) {
                  return (
                    <Redirect
                      exact
                      from={prop.path}
                      to={prop.pathTo}
                      key={key}
                    />
                  );
                }
                return (
                  <Route
                    path={prop.path}
                    exact
                    render={props => (
                      <prop.component {...prop} {...props} {...rest} />
                    )}
                    key={key}
                  />
                );
              })}
              <Route component={PageNotFound} />
            </Switch>
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
