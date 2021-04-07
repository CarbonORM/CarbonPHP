import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
import cx from "classnames";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons
// core components
import GridItem from "components/Grid/GridItem.jsx";
// sections for this page
import SectionBasics from "./Sections/SectionBasics.jsx";
import SectionNavbars from "./Sections/SectionNavbars.jsx";
import SectionTabs from "./Sections/SectionTabs.jsx";
import SectionPills from "./Sections/SectionPills.jsx";
import SectionNotifications from "./Sections/SectionNotifications.jsx";
import SectionTypography from "./Sections/SectionTypography.jsx";
import SectionJavascript from "./Sections/SectionJavascript.jsx";
import SectionCompletedExamples from "./Sections/SectionCompletedExamples.jsx";
import SectionLogin from "./Sections/SectionLogin.jsx";
import SectionDownload from "./Sections/SectionDownload.jsx";

import componentsStyle from "assets/jss/material-kit-react/views/components.jsx";

import Navbar from "views/Documentation/Navbar";
import Parallax from "../../components/Parallax/Parallax";
import GridContainer from "../../components/Grid/GridContainer";
import Footer from "../../components/Footer/Footer";


import HeaderTop from "components/HeaderTop/HeaderTop";
import HeaderLinks from "components/HeaderTop/HeaderLinks";
import ProfilePage from "../ProfilePage/ProfilePage";
import LandingPage from "../LandingPage/LandingPage";
import PropTypes from "prop-types";

// react components for routing our app without refresh


class Documentation extends React.Component {
  constructor() {
    super();
    this.state = {
      isLoaded: false
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

    const mainPanel =
      classes.mainPanel +
      " " +
      cx({
        [classes.mainPanelSidebarMini]: false,
        [classes.mainPanelWithPerfectScrollbar]:
        navigator.platform.indexOf("Win") > -1
      });

    let publicDocumentationRoutes = [
      {
        path: "/SectionNavbars",    // I'm leaving this here for the time being as an example
        name: "Navbars",    // This should be loaded under a different wrapper
        component: SectionNavbars
      },
      {
        path: "/SectionBasics",
        name: "Basics",
        component: SectionBasics
      },
      // I havent been able to get this to work
      // {
      //     path: "/SectionCarousel",
      //     name: "Carousel",
      //     component: SectionCarousel
      // },
      {
        path: "/SectionTabs",
        name: "Tabs",
        component: SectionTabs
      },
      {
        path: "/SectionPills",
        name: "Pills",
        component: SectionPills
      },
      {
        path: "/SectionNotifications",
        name: "Notifications",
        component: SectionNotifications
      },
      {
        path: "/SectionTypography",
        name: "Typography",
        component: SectionTypography
      },
      {
        path: "/SectionJavascript",
        name: "Javascript",
        component: SectionJavascript
      },
      {
        path: "/SectionCompletedExamples",
        name: "Text",
        component: SectionCompletedExamples
      },
      {
        path: "/SectionLogin",
        name: "Login",
        component: SectionLogin
      }, {
        path: "/Profile",
        name: "Profile",
        component: ProfilePage
      },
      {
        path: "/Landing",
        name: "Landing",
        component: LandingPage
      },
      {
        path: "/SectionDownload",
        name: "Download",
        component: SectionDownload
      },
      {
        redirect: true,
        path: "/",
        pathTo: "/SectionNavbars",
        name: "Navbars"
      }
    ];

    let root = '/UI/Material-Kit';

    publicDocumentationRoutes = publicDocumentationRoutes.map(o => {
      if ('path' in o) {
        o.path = root + o.path;
      }
      if ('pathTo' in o) {
        o.pathTo = root + o.pathTo;
      }
      return o;
    });

    // transparent here seems to work 50% the time, replace with dark if trouble persists
    return (
      <div>
        <div className={classes.wrapper}>
          <HeaderTop
            brand="CarbonPHP.com"
            rightLinks={<HeaderLinks/>}
            fixed
            color="dark"
            changeColorOnScroll={{
              height: 400,
              color: "dark"
            }}
            {...rest}
          />
          <Parallax image={require("assets/img/Carbon-purple-180.png")}>
            <div className={classes.container}>
              <GridContainer>
                <GridItem>
                  <div className={classes.brand}>
                    <h1 className={classes.title}>CarbonPHP [C6]</h1>
                    <h3 className={classes.subtitle}>
                      Build full scale applications in minutes.
                    </h3>
                  </div>
                </GridItem>
              </GridContainer>
            </div>
          </Parallax>
          <div className={mainPanel}>
            <div>
              <Navbar brand="Material Kit UI" color="danger" routes={publicDocumentationRoutes}/>
              <div className={classNames(classes.main, classes.mainRaised)}>
                {this.props.subRoutingSwitch(publicDocumentationRoutes, rest)}
              </div>
            </div>
          </div>
          <Footer fluid/>
        </div>
      </div>
    );
  }
}


Documentation.propTypes = {
  classes: PropTypes.object.isRequired,
  subRoutingSwitch: PropTypes.func.isRequired,
};

export default withStyles(componentsStyle)(Documentation);
