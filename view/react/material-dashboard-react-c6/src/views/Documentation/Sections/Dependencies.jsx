import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";

class Dependencies extends React.Component {
    render() {
        const { classes } = this.props;
        return (
            <div className={classes.section}>
              <div className={classes.container}>
                <GridContainer justify="center">
                  <GridItem xs={12} sm={12} md={8}>
                    <h2>Dependencies & Plugins</h2>
                    <h4>
                      CarbonPHP is a standalone library that depends on the PHP 7.1,
                      the PHP Standard Recommendation Logger, and MySQL's INNODB driver.
                      To install CarbonPHP you must have Composer.
                    </h4>
                    <h3>REACT</h3>
                    <h4>
                      <b>REACT tag line here Analysis</b><br/>
                      "... 40% of [customers] will wait no more than three seconds before abandoning a retail or travel site."
                      We analysed trending frameworks in every language to provide a semantically pleasing, powerful, and portable library. On average, CarbonPHP's application framework can render content in under a hundredth of second.
                    </h4>
                    <h3>Less than a second</h3>
                    <h4>
                      <b>AdminLTE is our preferred user interface which is maintained by over 20,000 github users.</b><br/>
                      The AdminLTE suite makes use of the following plugins.
                      For documentation, updates or license information, please visit the provided links.</h4>
                  </GridItem>
                </GridContainer>
              </div>
            </div>
        );
    }
}

export default withStyles(completedStyle)(Dependencies);
