import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";
import PropTypes from "prop-types";

// The Typography seems to be off when 0 is set. 15 looks better on mobile
const style = {
  grid: {
    margin: "0 35px 0 15px !important",
    width: "unset"
  }
};

function GridContainer(props) {
  const { classes, children, justify, ...rest } = props;
  return (
    <Grid container justify {...rest} className={classes.grid}>
      {children}
    </Grid>
  );
}


GridContainer.propTypes = {
  style: PropTypes.any,
  styles: PropTypes.any,
  children: PropTypes.any,
  justify: PropTypes.any,
  classes: PropTypes.object.isRequired,
};

export default withStyles(style)(GridContainer);
