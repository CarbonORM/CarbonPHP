// ##############################
// // // SweetAlert view styles
// #############################

import buttonStyle from "assets/jss/material-dashboard-react/components/buttonStyle.jsx";
import { createStyles } from "@material-ui/core";

const sweetAlertStyle = createStyles({
  cardTitle: {
    marginTop: "0",
    marginBottom: "3px",
    color: "rgb(188,201,218)",
    fontSize: "18px"
  },
  center: {
    textAlign: "center"
  },
  right: {
    textAlign: "right"
  },
  left: {
    textAlign: "left"
  },
  ...buttonStyle
});

export default sweetAlertStyle;
