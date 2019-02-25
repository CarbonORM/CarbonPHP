/*eslint-disable*/
import React from "react";
// react components for routing our app without refresh
import {Link} from "react-router-dom";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import Tooltip from "@material-ui/core/Tooltip";

// @material-ui/icons
import {Apps, CloudDownload} from "@material-ui/icons";

// core components
import CustomDropdown from "components/CustomDropdown/CustomDropdown.jsx";
import Button from "components/CustomButtons/Button.jsx";

import headerLinksStyle from "assets/jss/material-kit-react/components/headerLinksStyle.jsx";

import context from "variables/carbonphp";

function HeaderLinks({...props}) {
    const {classes} = props;

    return (
        <List className={classes.list}>
            <ListItem className={classes.listItem}>
                <CustomDropdown
                    noLiPadding
                    buttonText="Versions"
                    buttonProps={{
                        className: classes.navLink,
                        color: "transparent"
                    }}
                    buttonIcon={Apps}
                    dropdownList={[
                        <Link to="/Dashboard" className={classes.dropdownLink}>
                            Version 5.0
                        </Link>,
                        <a
                            href={context.contextHost + "/2.0"}
                            target="_blank"
                            className={classes.dropdownLink}
                        >
                            Version 2.0
                        </a>
                    ]}
                />
            </ListItem>
            <ListItem className={classes.listItem}>
                <CustomDropdown
                    noLiPadding
                    buttonText="UI"
                    buttonProps={{
                        className: classes.navLink,
                        color: "transparent"
                    }}
                    buttonIcon={Apps}
                    dropdownList={[
                        <Link to="/5.0/UI/Material-Kit" className={classes.dropdownLink}>
                            Material Kit
                        </Link>,
                        <Link to="/5.0/UI/Material-Dashboard" className={classes.dropdownLink}>
                            Material Dashboard
                        </Link>,
                    ]}
                />
            </ListItem>
            <ListItem className={classes.listItem}>
                <Button
                    href="https://github.com/RichardTMiles/CarbonPHP"
                    color="transparent"
                    target="_blank"
                    className={classes.navLink}
                >
                    <CloudDownload className={classes.icons}/> Download
                </Button>
            </ListItem>
            <ListItem className={classes.listItem}>
                <Tooltip
                    id="instagram-twitter"
                    title="Follow us on twitter"
                    placement={window.innerWidth > 959 ? "top" : "left"}
                    classes={{tooltip: classes.tooltip}}
                >
                    <Button
                        href="https://twitter.com/rootPrerogative"
                        target="_blank"
                        color="transparent"
                        className={classes.navLink}
                    >
                        <i className={classes.socialIcons + " fab fa-twitter"}/> &nbsp;
                    </Button>
                </Tooltip>
            </ListItem>
            <ListItem className={classes.listItem}>
                <Tooltip
                    id="instagram-facebook"
                    title="Follow us on facebook"
                    placement={window.innerWidth > 959 ? "top" : "left"}
                    classes={{tooltip: classes.tooltip}}
                >
                    <Button
                        color="transparent"
                        href="https://www.facebook.com/wookieetyler"
                        target="_blank"
                        className={classes.navLink}
                    >
                        <i className={classes.socialIcons + " fab fa-facebook"}/> &nbsp;
                    </Button>
                </Tooltip>
            </ListItem>
            <ListItem className={classes.listItem}>
                <Tooltip
                    id="instagram-tooltip"
                    title="Follow us on instagram"
                    placement={window.innerWidth > 959 ? "top" : "left"}
                    classes={{tooltip: classes.tooltip}}
                >
                    <Button
                        color="transparent"
                        href="https://www.instagram.com/wookieetyler/"
                        target="_blank"
                        className={classes.navLink}
                    >
                        <i className={classes.socialIcons + " fab fa-instagram"}/> &nbsp;
                    </Button>
                </Tooltip>
            </ListItem>
        </List>
    );
}

export default withStyles(headerLinksStyle)(HeaderLinks);
