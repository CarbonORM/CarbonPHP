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
import {Add, Apps, CloudDownload, Remove} from "@material-ui/icons";
// core components
import CustomDropdown from "components/CustomDropdown/CustomDropdown.jsx";
import Button from "components/CustomButtons/Button.jsx";

import headerLinksStyle from "assets/jss/material-kit-react/components/headerLinksStyle.jsx";

import context from "variables/carbonphp";


class HeaderLinks extends React.Component<any, any> {
  constructor(props) {
    super(props);
    this.state = {
      zoom: 100,
    };
    this.zoom = this.zoom.bind(this);
    document.body.style.overflowX = 'hidden';
  }

  zoom(operator) {
    switch (operator) {
      case "+":
        this.setState({
          zoom: this.state.zoom + 10
        }, () => document.body.style.zoom = this.state.zoom + "%");
        break;
      case "-":
        this.setState({
          zoom: this.state.zoom - 10
        }, () => document.body.style.zoom = this.state.zoom + "%");
        break;
      default:
        document.body.style.zoom = this.state.zoom + "%"
    }
  }

  render() {
    const { classes } = this.props;

    return (
      <List className={classes.list}>
        <ListItem className={classes.listItem}>
          <Button
            onClick={() => this.zoom('+')}
            color="transparent"
            target="_blank"
            className={classes.navLink}
          >
            <Add className={classes.icons}/>
          </Button>
        </ListItem>
        <ListItem className={classes.listItem}>
          <Button
            onClick={() => this.zoom('-')}
            color="transparent"
            target="_blank"
            className={classes.navLink}
          >
            <Remove className={classes.icons}/>
          </Button>
        </ListItem>
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
              <Link to="/8.2" className={classes.dropdownLink}>
                Version 8.^
              </Link>,
              <a
                href={context.contextHost + "/6.0"}
                className={classes.dropdownLink}
              >
                Version 6.^
              </a>,
              <a
                href={context.contextHost + "/2.0"}
                target="_blank"
                className={classes.dropdownLink}
              >
                Version 2.^
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
              <Link to="/6.0/UI/Material-Kit"
                    target="_blank"
                    className={classes.dropdownLink}>
                Material Kit
              </Link>,
              <Link to="/6.0/UI/Material-Dashboard"
                    target="_blank"
                    className={classes.dropdownLink}>
                Material Dashboard
              </Link>,
              <a href="https://carbonphp.com/2.0/UIElements"
                 className={classes.dropdownLink}
                 target="_blank"
              >
                AdminLTE
              </a>,
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
            <CloudDownload className={classes.icons}/> GitHub
          </Button>
        </ListItem>
        <ListItem className={classes.listItem}>
          <Tooltip
            id="instagram-twitter"
            title="Follow us on twitter"
            placement={window.innerWidth > 959 ? "top" : "left"}
            classes={{ tooltip: classes.tooltip }}
          >
            <Button
              href="https://twitter.com/rootPrerogative"
              target="_blank"
              color="transparent"
              className={classes.navLink}
            >
              <i className={classes.socialIcons + " fab fa-twitter"}/> Twitter
            </Button>
          </Tooltip>
        </ListItem>
        <ListItem className={classes.listItem}>
          <Tooltip
            id="instagram-facebook"
            title="Follow us on facebook"
            placement={window.innerWidth > 959 ? "top" : "left"}
            classes={{ tooltip: classes.tooltip }}
          >
            <Button
              color="transparent"
              href="https://www.facebook.com/wookieetyler"
              target="_blank"
              className={classes.navLink}
            >
              <i className={classes.socialIcons + " fab fa-facebook"}/> Facebook
            </Button>
          </Tooltip>
        </ListItem>

      </List>
    );
  }

  /**
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
   **/
}

export default withStyles(headerLinksStyle)(HeaderLinks);
