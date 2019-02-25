import React from "react";
import PropTypes from "prop-types";


// core components
import publicRoutes from "routes/publicRoutes";


class Public extends React.Component {
    render() {
        console.log('PUBLIC JSX RENDER');
        return this.props.subRoutingSwitch(publicRoutes, this.props);
    }
}

Public.propTypes = {
    classes: PropTypes.object.isRequired
};

export default Public;
