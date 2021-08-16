import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// nodejs library to set properties for components
import PropTypes from "prop-types";

import SwipeableViews from "react-swipeable-views";
import { bindKeyboard } from 'react-swipeable-views-utils';


// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";

import navPillsStyle from "assets/jss/material-kit-react/components/navPillsStyle.jsx";


const BindKeyboardSwipeableViews = bindKeyboard(SwipeableViews);


class NavPills extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: props.active
    };
  }
  handleChange = (event, active) => {
    this.setState({ active });
  };
  handleChangeIndex = index => {
    this.setState({ active: index });
  };
  render() {
    const {
      classes,
      tabs,
      direction,
      color,
      horizontal,
      alignCenter,
      scrollButtons
    } = this.props;


    const flexContainerClasses = classNames({
      [classes.flexContainer]: true,
      [classes.horizontalDisplay]: horizontal !== undefined,
    });

    const tabButtons = (
      <Tabs
        scrollButtons={scrollButtons}
        variant={'scrollable'}
        classes={{
          root: classes.root,
          fixed: classes.fixed,
          flexContainer: flexContainerClasses,
          indicator: classes.displayNone
        }}
        value={this.state.active}
        onChange={this.handleChange}
        centered={alignCenter}
      >
        {tabs.map((prop, key) => {
          var icon = {};
          if (prop.tabIcon !== undefined) {
            icon["icon"] = <prop.tabIcon className={classes.tabIcon} />;
          }
          const pillsClasses = classNames({
            [classes.pills]: true,
            [classes.horizontalPills]: horizontal !== undefined,
            [classes.pillsWithIcons]: prop.tabIcon !== undefined
          });
          return (
            <Tab
              label={prop.tabButton}
              key={key}
              {...icon}
              classes={{
                root: pillsClasses,
                selected: classes[color]
              }}
            />
          );
        })}
      </Tabs>
    );

    const SlideComponent = horizontal ? SwipeableViews : BindKeyboardSwipeableViews;

    const tabContent = (
      <div className={classes.contentWrapper}>
        <SlideComponent
          axis={direction === "rtl" ? "x-reverse" : "x"}
          enableMouseEvents={true}
          index={this.state.active}
          onChangeIndex={this.handleChangeIndex}
        >
          {tabs.map((prop, key) => {
            return (
              <div className={classes.tabContent} key={key}>
                {key === this.state.active ? prop.tabContent : ""}
              </div>
            );
          })}
        </SlideComponent>
      </div>
    );
    return horizontal !== undefined ? (
      <GridContainer>
        <GridItem {...horizontal.tabsGrid}>{tabButtons}</GridItem>
        <GridItem {...horizontal.contentGrid}>{tabContent}</GridItem>
      </GridContainer>
    ) : (
      <>
        {tabButtons}
        {tabContent}
      </>
    );
  }
}

NavPills.defaultProps = {
  active: 0,
  color: "primary",
  scrollButtons: 'auto'
};

NavPills.propTypes = {
  scrollButtons: PropTypes.oneOf(['auto', 'desktop', 'on', 'off']),
  classes: PropTypes.object.isRequired,
  // index of the default active pill
  active: PropTypes.number,
  tabs: PropTypes.arrayOf(
    PropTypes.shape({
      tabButton: PropTypes.string,
      tabIcon: PropTypes.any,
      tabContent: PropTypes.node
    })
  ).isRequired,
  color: PropTypes.oneOf([
    "primary",
    "warning",
    "danger",
    "success",
    "info",
    "rose"
  ]),
  direction: PropTypes.string,
  horizontal: PropTypes.shape({
    tabsGrid: PropTypes.object,
    contentGrid: PropTypes.object
  }),
  alignCenter: PropTypes.bool
};

export default withStyles(navPillsStyle)(NavPills);
