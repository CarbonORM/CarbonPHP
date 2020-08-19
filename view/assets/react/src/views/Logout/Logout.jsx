import React from "react";
import { withRouter } from "react-router-dom";
import Redirect from "components/Redirect/Redirect.jsx";

// This is my action creator function
// It takes three parameters: hours, minutes, seconds

class Logout extends React.Component {
    render() {
        let url = "/logout";
        return <Redirect to={url} />;
    }
}

export default withRouter(Logout);
