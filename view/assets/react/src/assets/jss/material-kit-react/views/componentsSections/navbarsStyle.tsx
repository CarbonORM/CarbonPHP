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
import { container, title } from "../../../material-kit-react";
import headerLinksStyle from "../../components/headerLinksStyle";
import { createStyles, Theme } from "@material-ui/core";

const navbarsStyle = (theme: Theme) =>
  createStyles({
    mrAuto: {
      marginRight: "auto"
    },
    mlAuto: {
      marginLeft: "auto"
    },
    section: {
      padding: "70px 0",
      paddingTop: "0"
    },
    container,
    title: {
      ...title,
      marginTop: "30px",
      minHeight: "32px",
      textDecoration: "none"
    },
    navbar: {
      marginBottom: "-20px",
      zIndex: 100,
      position: "relative",
      overflow: "hidden",
      "& header": {
        borderRadius: "0"
      }
    },
    navigation: {
      backgroundPosition: "center center",
      backgroundSize: "cover",
      marginTop: "0",
      minHeight: "740px"
    },
    formControl: {
      margin: "0 !important",
      paddingTop: "0"
    },
    inputRootCustomClasses: {
      margin: "0!important"
    },
    searchIcon: {
      width: "20px",
      height: "20px",
      color: "inherit"
    },
    searchInput: {},
    socialIconsButton: {},
    ...headerLinksStyle(theme),
    img: {
      width: "40px",
      height: "40px",
      borderRadius: "50%"
    },
    imageDropdownButton: {
      padding: "0px",
      top: "4px",
      borderRadius: "50%",
      marginLeft: "5px"
    }
  });

export default navbarsStyle;
