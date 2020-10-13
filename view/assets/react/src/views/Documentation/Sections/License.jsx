import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";


// TODO - <GridContainer justify="center"> ?????
class License extends React.Component {
    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={12} sm={12} md={8}>
                            <h1>License </h1>
                            <p>
                                CarbonPHP is an open source project that is licensed under the MIT license. This allows
                                you to do pretty much anything you want as long as you include the copyright in "all
                                copies or substantial portions of the Software." Attribution is not required (though
                                very much appreciated).
                            </p>
                            <h2>What You Are <b>Allowed</b> To Do With C6 &amp; CarbonPHP </h2>
                            <ul>
                                <li>Use in commercial projects.</li>
                                <li>Use in personal/private projects.</li>
                                <li>Modify and change the work.</li>
                                <li>Distribute the code.</li>
                                <li>Sublicense: incorporate the work into something that has a more restrictive license.
                                </li>
                            </ul>
                            <h2>What You Are <b>Not Allowed</b> To Do With C6 &amp; CarbonPHP </h2>
                            <ul>
                                <li>The work is provided "as is". You may not hold the author liable.</li>
                            </ul>
                            <h2>What You <b>Must</b> Do When Using C6 &amp; CarbonPHP </h2>
                            <ul>
                                <li>Include the license notice in all copies of the work.</li>
                                <li>Include all 3rd party license notice(s) in accordance to its own license agreement.</li>
                                <li>
                                    Include the copyright notice in all copies of the work.
                                    This applies to everything except the notice in the footer of the HTML example
                                    pages.
                                </li>
                            </ul>
                        </GridItem>
                    </GridContainer>
                </div>
            </div>
        )
    }
}

export default withStyles(completedStyle)(License);
