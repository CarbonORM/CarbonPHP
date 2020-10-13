import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import GridItem from "components/Grid/GridItem.jsx";
import CarbonPHP from "./Sections/CarbonPHP";
import Dependencies from "./Sections/Dependencies";
import Installation from "./Sections/Installation.jsx";
import Bootstrap from "./Sections/Bootstrap.jsx";
// FileStructure OptionsIndex Bootstrap Wrapper ParallelProcessing
import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import Navbar from "views/Documentation/Navbar";
import Parallax from "../../components/Parallax/Parallax";
import GridContainer from "../../components/Grid/GridContainer";
import Footer from "../../components/Footer/Footer";


import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks";
import DashboardIcon from "@material-ui/icons/Dashboard";
import BrowserOSSupport from "./Sections/BrowserOSSupport";
import Support from "./Sections/Support";
import License from "./Sections/License";

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
    console.log("Documentation JSX RENDER");

    console.log(this.props);

    const { classes, ...rest } = this.props;

    let publicDocumentationRoutes = [
      {
        path: "/6.0/Documentation/CarbonPHP",     // I'm leaving this here for the time being as an example
        name: "Documentation",          // This should be loaded under a different wrapper
        icon: DashboardIcon,
        component: CarbonPHP
      },
      {
        path: "/6.0/Documentation/Dependencies",
        name: "Dependencies",
        icon: DashboardIcon,
        component: Dependencies
      },
      {
        path: "/6.0/Documentation/BrowserOSSupport",
        name: "Changelog",
        icon: DashboardIcon,
        component: BrowserOSSupport
      },
      {
        path: "/6.0/Documentation/Implementations",
        name: "Implementations",
        icon: DashboardIcon,
        component: Installation
      },
      {
        path: "/6.0/Documentation/Support",
        name: "Support",
        icon: DashboardIcon,
        component: Support
      },
      {
        path: "/6.0/Documentation/License",
        name: "License",
        icon: DashboardIcon,
        component: License
      },
      {
        redirect: true,
        path: "/5.0",
        pathTo: "/6.0/Documentation/CarbonPHP",
        name: "Examples"
      },
      {
        redirect: true,
        path: "/6.0/",
        pathTo: "/6.0/Documentation/CarbonPHP",
        name: "Examples"
      },
      {
        redirect: true,
        path: "/",
        pathTo: "/6.0/Documentation/CarbonPHP",
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

export default withStyles(componentsStyle)(Documentation);
