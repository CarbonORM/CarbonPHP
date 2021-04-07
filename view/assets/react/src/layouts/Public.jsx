import React from "react";

// core components
import publicRoutes from "routes/publicRoutes";
import PropTypes from "prop-types";


class Public extends React.Component {
    render() {
        console.log('PUBLIC JSX RENDER');
        return this.props.subRoutingSwitch(publicRoutes, this.props);
    }
}


Public.propTypes = {
    subRoutingSwitch: PropTypes.func
};

export default Public;
