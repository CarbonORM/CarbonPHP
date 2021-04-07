import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import GridItem from "components/Grid/GridItem.jsx";
import CarbonPHP from "./Sections/CarbonPHP";
import Dependencies from "./Sections/Dependencies";
import Implementations from "./Sections/Implementations";
// FileStructure OptionsIndex Bootstrap Wrapper ParallelProcessing
import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import Navbar from "views/Documentation/Navbar";
import Parallax from "../../components/Parallax/Parallax";
import GridContainer from "../../components/Grid/GridContainer";
import Footer from "../../components/Footer/Footer";


import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks";
import Changelog from "./Sections/Changelog";

import Support from "./Sections/Support";
import License from "./Sections/License";
import carbonphp from "variables/carbonphp"
import PropTypes from "prop-types";


// react components for routing our app without refresh
// @material-ui/icons
// core components

// sections for this page
// import Sections from "views/Documentation/Sections/Sections";


class Documentation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoaded: false,
    }
  }

  componentDidMount() {
    this.setState({
      isLoaded: true
    });
  }

  render() {

    let documentationVersion = carbonphp.documentationVersionURI;

    console.log("Documentation JSX RENDER");

    console.log(this.props);

    const { classes, ...rest } = this.props;

    let publicDocumentationRoutes = [
      {
        path: "/" + documentationVersion + "/Documentation/CarbonPHP",     // I'm leaving this here for the time being as an example
        name: "Documentation",          // This should be loaded under a different wrapper
        component: CarbonPHP
      },
      {
        path: "/" + documentationVersion + "/Documentation/Dependencies",
        name: "Dependencies",
        component: Dependencies
      },
      {
        path: "/" + documentationVersion + "/Documentation/BrowserOSSupport",
        name: "Changelog",
        component: Changelog
      },
      {
        path: "/" + documentationVersion + "/Documentation/Implementations",
        name: "Implementations",
        component: Implementations
      },
      {
        path: "/" + documentationVersion + "/Documentation/Support",
        name: "Support",
        component: Support
      },
      {
        path: "/" + documentationVersion + "/Documentation/License",
        name: "License",
        component: License
      },
      {
        redirect: true,
        path: "/5.0",
        pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
        name: "Examples"
      },
      {
        redirect: true,
        path: "/" + documentationVersion + "/",
        pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
        name: "Examples"
      },
      {
        redirect: true,
        path: "/",
        pathTo: "/" + documentationVersion + "/Documentation/CarbonPHP",
        name: "Examples"
      }
    ];


    // transparent here seems to work 50% the time, replace with dark if trouble persists
    return (
      <div>
        <HeaderTop
          brand="CarbonPHP.com"
          rightLinks={<HeaderLinks/>}
          fixed
          color="transparent"
          changeColorOnScroll={{
            height: 400,
            color: "dark"
          }}
          {...rest}
        />
        <Parallax image={require("assets/img/Carbon-teal-180.png")}>
          <div className={classes.container}>
            <GridContainer>
              <GridItem>
                <div className={classes.brand}>
                  <h1 className={classes.title}>CarbonPHP [C6]</h1>
                  <h3 className={classes.subtitle}>
                    Build full scale applications.
                  </h3>
                </div>
              </GridItem>
            </GridContainer>
          </div>
        </Parallax>
        <div>
          <div>
            <Navbar className={classNames(classes.main, classes.mainRaised)}
                    routes={publicDocumentationRoutes}/>
            <div className={classNames(classes.main, classes.mainRaised)}>
              {this.props.subRoutingSwitch(publicDocumentationRoutes, rest)}
            </div>
          </div>
        </div>
        <Footer fluid/>
      </div>
    );
  }
}

Documentation.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default withStyles(componentsStyle)(Documentation);
