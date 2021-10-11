import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import HeaderTop from "components/HeaderTop/HeaderTop.jsx";
import CustomDropdown from "components/CustomDropdown/CustomDropdown.jsx";

import navbarsStyle from "assets/jss/material-kit-react/views/componentsSections/navbarsStyle.jsx";

import {NavLink} from "react-router-dom";


class Navbar extends React.Component {
    render() {
        const {classes, routes, color, brand} = this.props;

        let tabs = [];

        routes.forEach((o, key) => {
                // 'pathTo' aka not a redirect
                if (!('pathTo' in o)) {
                    // doesn't need a sub menu
                    if (!('views' in o)) {
                        tabs.push(<ListItem className={classes.listItem} key={key}>
                            <NavLink
                                to={o.path}
                                className={classes.navLink + " " + classes.navLinkActive}
                                key={key}
                            >
                                {o.name}
                            </NavLink>
                        </ListItem>)
                    } else {
                        tabs.push(
                            <ListItem className={classes.listItem} key={key}>
                                <CustomDropdown
                                    left
                                    key={key}
                                    caret={true}
                                    hoverColor="info"
                                    dropdownHeader={o.name}
                                    buttonText={o.name}
                                    buttonProps={{
                                        className: classes.navLink + " " + classes.navLinkActive,
                                    }}
                                    dropdownList={o.views.map((m, key2) => {
                                        return <ListItem className={classes.listItem}>
                                            <NavLink
                                                to={m.path}
                                                className={classes.navLink + " " + classes.navLinkActive}
                                                key={key2}
                                            >
                                                {m.name}
                                            </NavLink>
                                        </ListItem>
                                    })}
                                />
                            </ListItem>
                        );
                    }
                }
            }
        );


        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <HeaderTop
                        axios={this.props.axios}
                        darkMode={this.props.darkMode}
                        brand={brand ? brand : "Documentation"}
                        color={color ? color : "dark"}
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


// thur feb 21 1140