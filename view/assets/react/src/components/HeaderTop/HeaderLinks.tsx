/*eslint-disable*/
import React from "react";
// react components for routing our app without refresh
import {Link} from "react-router-dom";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
// @material-ui/icons
import {Add, Apps, CloudDownload, NightsStay, Remove, WbSunny} from "@material-ui/icons";
// core components
import CustomDropdown from "components/CustomDropdown/CustomDropdown.jsx";
import Button from "components/CustomButtons/Button.jsx";

import headerLinksStyle from "assets/jss/material-kit-react/components/headerLinksStyle.jsx";

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
    const { classes, versions} = this.props;

    console.info(versions);

    return (
      <List className={classes.list}>
        <ListItem className={classes.listItem}>
          <Button
            onClick={() => this.zoom('+')}
            color="transparent"
            target="_blank"
            className={classes.navLink}
            style={{ color: (this.props.darkMode ?  "white" : "black") }}
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
            style={{ color: (this.props.darkMode ?  "white" : "black") }}
          >
            <Remove className={classes.icons}/>
          </Button>
        </ListItem>
        <ListItem className={classes.listItem}>
          <Button
            onClick={() => this.props.switchDarkAndLightTheme()}
            color="transparent"
            target="_blank"
            className={classes.navLink}
            style={{ color: (this.props.darkMode ?  "white" : "black") }}
          >
            {this.props.darkMode
              ? <><WbSunny className={classes.icons}/></>
              : <><NightsStay className={classes.icons}/></>
            }
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
            darkMode={this.props.darkMode}
            buttonIcon={Apps}
            dropdownList={versions && versions.map(version =>
              <a href={'/view/releases/' + version} target="_blank" className={classes.dropdownLink}>
                Version {version}
              </a>,
            )}
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
            darkMode={this.props.darkMode}
            buttonIcon={Apps}
            dropdownList={[
              <Link to="/UI/Material-Kit"
                    target="_blank"
                    className={classes.dropdownLink}>
                Material Kit
              </Link>,
              <Link to="/UI/Material-Dashboard"
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
            style={{ color: (this.props.darkMode ?  "white" : "black") }}
          >
            <CloudDownload className={classes.icons}/> GitHub
          </Button>
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
