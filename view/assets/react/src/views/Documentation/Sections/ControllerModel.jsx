import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
import Info from "@material-ui/icons/Info";
// core components
import SnackbarContent from "components/Snackbar/SnackbarContent.jsx";
import Clearfix from "components/Clearfix/Clearfix.jsx";
import notificationsStyles from "assets/jss/material-kit-react/views/componentsSections/notificationsStyles.jsx";

class ControllerModel extends React.Component {
    render() {
        return (
            <div>
                <SnackbarContent
                    message={
                        <span>
              <b>INFO ALERT:</b> You've got some friends nearby, stop looking at
              your phone and find them...
            </span>
                    }
                    close
                    color="info"
                    icon={Info}
                />
                <Clearfix/>
            </div>
        );
    }
}

export default withStyles(notificationsStyles)(ControllerModel);
