import React from "react";

// core components
import publicRoutes from "routes/publicRoutes";


class Public extends React.Component {
    render() {
        console.log('PUBLIC JSX RENDER');
        return this.props.subRoutingSwitch(publicRoutes, this.props);
    }
}

export default Public;
