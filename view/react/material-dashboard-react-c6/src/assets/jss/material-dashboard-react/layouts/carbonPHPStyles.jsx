// ##############################
// // // App styles
// #############################

import {
  transition,
} from "assets/jss/material-dashboard-react.jsx";

const appStyle = theme => ({
  wrapper: {
    position: "relative",
    top: "0",
    height: "100vh",
    "&:after": {
      display: "table",
      clear: "both",
      content: '" "'
    }
  },
  mainPanel: {
    transitionProperty: "top, bottom, width",
    transitionDuration: ".2s, .2s, .35s",
    transitionTimingFunction: "linear, linear, ease",
    // [theme.breakpoints.up("md")]: {
    //   width: `calc(100% - ${drawerWidth}px)`
    // },
    overflow: "auto",
    position: "relative",
    float: "right",
    ...transition,
    maxHeight: "100%",
    width: "100%",
    overflowScrolling: "touch"
  },
  content: {
    marginTop: "95px",
    padding: "30px 15px",
    minHeight: "calc(100vh - 123px)"
  },
  map: {
    marginTop: "70px"
  },
  mainPanelWithPerfectScrollbar: {
    overflow: "hidden !important"
  }
});

export default appStyle;
