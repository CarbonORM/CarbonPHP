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
import {
  roseColor,
  primaryColor,
  infoColor,
  successColor,
  warningColor,
  dangerColor
} from "../../material-kit-react";
import { createStyles, Theme } from "@material-ui/core";

const navPillsStyle = (theme: Theme) =>
  createStyles({
    root: {
      marginTop: "20px",
      paddingLeft: "0",
      marginBottom: "0",
      overflow: "visible !important",
      lineHeight: "24px",
      textTransform: "uppercase",
      fontSize: "12px",
      fontWeight: 400,
      position: "relative",
      display: "block",
      color: "inherit"
    },
    flexContainer: {
      [theme.breakpoints.down("xs")]: {
        display: "flex",
        flexWrap: "wrap"
      }
    },
    displayNone: {
      display: "none"
    },
    fixed: {
      overflow: "visible"
    },
    horizontalDisplay: {
      display: "block"
    },
    pills: {
      float: "left",
      position: "relative",
      display: "block",
      borderRadius: "30px",
      minWidth: "100px",
      textAlign: "center",
      transition: "all .3s",
      padding: "10px 15px",
      color: "#555555",
      height: "auto",
      opacity: 1,
      maxWidth: "100%",
      margin: "0 5px"
    },
    pillsWithIcons: {
      borderRadius: "4px"
    },
    tabIcon: {
      width: "30px",
      height: "30px",
      display: "block",
      margin: "15px 0 !important",
      "&, & *": {
        letterSpacing: "normal"
      }
    },
    horizontalPills: {
      width: "100%",
      float: "none",
      "& + button": {
        margin: "10px 0"
      }
    },
    contentWrapper: {
      marginTop: "20px"
    },
    primary: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: primaryColor,
        boxShadow:
          "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(156, 39, 176, 0.4)"
      }
    },
    info: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: infoColor,
        boxShadow:
          "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(76, 175, 80, 0.4)"
      }
    },
    success: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: successColor,
        boxShadow:
          "0 2px 2px 0 rgba(76, 175, 80, 0.14), 0 3px 1px -2px rgba(76, 175, 80, 0.2), 0 1px 5px 0 rgba(76, 175, 80, 0.12)"
      }
    },
    warning: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: warningColor,
        boxShadow:
          "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4)"
      }
    },
    danger: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: dangerColor,
        boxShadow:
          "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4)"
      }
    },
    rose: {
      "&,&:hover": {
        color: "#FFFFFF",
        backgroundColor: roseColor,
        boxShadow:
          "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(233, 30, 99, 0.4)"
      }
    },
    alignCenter: {
      alignItems: "center",
      justifyContent: "center"
    },
    tabWrapper: {
      color: "inherit",
      position: "relative",
      fontSize: "12px",
      lineHeight: "24px",
      fontWeight: 500,
      textTransform: "uppercase",
      "&,& *": {
        letterSpacing: "normal"
      }
    }
  });

export default navPillsStyle;
