/*!

=========================================================
* Material Kit React - v1.7.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-kit-react
* Copyright 2019 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://github.com/creativetimofficial/material-kit-react/blob/master/LICENSE.md)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/
import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import Slide from "@material-ui/core/Slide";
import Dialog from "@material-ui/core/Dialog";

// core components
import popupStyles from "assets/jss/material-kit-react/popupStyles";
import {WithStyles} from "@material-ui/styles";
import {TransitionProps} from "@material-ui/core/transitions/transition";

const Transition = React.forwardRef(function Transition(
    props: TransitionProps,
    ref
) {
    return <Slide direction="down" ref={ref} {...props} />;
});

Transition.displayName = "Transition";

interface ISectionJavascriptProps extends WithStyles<typeof popupStyles> {
    open: boolean;
    handleClose: any;
    fullScreen?: boolean;
    fullWidth?: boolean;
}

class Popup extends React.Component<ISectionJavascriptProps, {}> {
    render() {
        const {handleClose, open, children, classes, ...rest} = this.props;
        return (
            <Dialog
                classes={{
                    root: classes.center,
                    paper: classes.modal
                }}
                open={open}
                TransitionComponent={Transition}
                keepMounted
                onClose={handleClose}
                aria-labelledby="classic-modal-slide-title"
                aria-describedby="classic-modal-slide-description"
                {...rest}
            >
                {children}
            </Dialog>
        );
    }
}

export default withStyles(popupStyles)(Popup);
