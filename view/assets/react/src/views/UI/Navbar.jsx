import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import Icon from "@material-ui/core/Icon";
// @material-ui/icons
import Search from "@material-ui/icons/Search";
import Email from "@material-ui/icons/Email";
import Face from "@material-ui/icons/Face";
import AccountCircle from "@material-ui/icons/AccountCircle";
import Explore from "@material-ui/icons/Explore";
// core components

import HeaderTop from "components/HeaderTop/HeaderTop.jsx";
import CustomInput from "components/CustomInput/CustomInput.jsx";
import CustomDropdown from "components/CustomDropdown/CustomDropdown.jsx";
import Button from "components/CustomButtons/Button.jsx";

import navbarsStyle from "assets/jss/material-kit-react/views/componentsSections/navbarsStyle.jsx";

import {NavLink} from "react-router-dom";


class Navbar extends React.Component {
    render() {
        const {classes, routes} = this.props;

        let tabs = routes;

        tabs = tabs.map((o, key) => {
            return (<ListItem className={classes.listItem}>
                <NavLink
                    to={o.path}
                    className={classes.navLink + " " + classes.navLinkActive}
                    key={key}
                >

                        {o.name}
                </NavLink>
            </ListItem>)
        });

        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <HeaderTop
                        brand="Documentation"
                        color="info"
                        rightLinks={
                            <List className={classes.list}>
                                {tabs}
                            </List>
                        }
                    />

                </div>
            </div>
        );
    }
}

export default withStyles(navbarsStyle)(Navbar);
