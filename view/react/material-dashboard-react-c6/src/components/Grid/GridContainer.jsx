import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";

// The Typography seems to be off when 0 is set. 15 looks better on mobile
const style = {
  grid: {
    margin: "0 35px 0 15px !important",
    width: "unset"
  }
};

function GridContainer(props) {
  const { classes, children, ...rest } = props;
  return (
    <Grid container {...rest} className={classes.grid}>
      {children}
    </Grid>
  );
}

export default withStyles(style)(GridContainer);
