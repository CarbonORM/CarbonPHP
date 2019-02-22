import React from "react";
import PropTypes from "prop-types";
import cx from "classnames";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";

// core components
import publicRoutes from "routes/publicRoutes";

import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import navbarsStyle from "assets/jss/material-kit-react/views/componentsSections/navbarsStyle.jsx";
import sweetAlertStyle from "assets/jss/material-dashboard-react/views/sweetAlertStyle";
import appStyle from "assets/jss/material-dashboard-react/layouts/carbonPHPStyles";



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

        const {classes, ...rest} = this.props;

        console.log('PUBLIC JSX RENDER');
        if (!this.state.isLoaded) {
            return null;
        }

        return this.props.subRoutingSwitch(publicRoutes, rest);
    }
}

Public.propTypes = {
    classes: PropTypes.object.isRequired
};

export default withStyles(styles)(Public);
