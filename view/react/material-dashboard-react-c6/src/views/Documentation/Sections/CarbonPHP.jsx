import React from "react";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import CustomTabs from "components/CustomTabs/CustomTabs.jsx";
import completedStyle from "assets/jss/material-kit-react/views/componentsSections/completedStyle.jsx";

class CarbonPHP extends React.Component {
    render() {
        const { classes } = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <GridContainer justify="center">
                        <GridItem xs={12} sm={12} md={8}>
                            <h2>CarbonPHP [C6] is in [Alpha]</h2>
                            <h4>
                                This documentation is for CarbonPHP & C6 (Carbon-6), a PHP 7.1+ application framework.
                                If you are looking for AdminLTE documentation, AKA this user interface and layout, click here.
                            </h4>
                            <h3>Less than a second</h3>
                            <h4>
                                <b>Google's Analysis</b><br/>
                                "... 40% of [customers] will wait no more than three seconds before abandoning a retail or travel site."
                                We analysed trending frameworks in every language to provide a semantically pleasing, powerful, and portable library. On average, CarbonPHP's application framework can render content in under a hundredth of second.
                            </h4>
                        </GridItem>
                        <GridItem xs={12} sm={12} md={12}>
                            <h3>
                                <small>CarbonPHP is a open source library for quickly creating web applications.</small>
                            </h3>
                            <CustomTabs
                                plainTabs
                                headerColor="info"
                                tabs={[
                                    {
                                        tabName: "Why?",
                                        tabContent: (
                                            <p className={classes.textCenter}>
                                                We feature PSR-4 Accolading, Larval style URL mapping,
                                                Zend style file structure, PHP PDO Databases, and real-time
                                                communication using Named Pipes & Sockets. We also provide a
                                                beautiful feature to create seemingly-stateless PHP class
                                                objects. PJAX, a javascript library for inner content
                                                refreshing, is supported in the configuration however you must
                                                manually include their JS. This is only the case if you choose
                                                to use CarbonPHP independently of C6. CarbonPHP has many other
                                                features.
                                                <br />
                                                C6 (Carbon-6) is a production ready web-app that fully incorporates
                                                CarbonPHP, AdminLTE, PJAX, and jQuery. In fact, this website is C6.
                                                If you create a new project using composer you will be greeted with
                                                this page! C6 allows you to exclusively develop in PHP and HTML. We
                                                also support and recommend Google Cloud App Engine to store and serve
                                                your next application.
                                            </p>
                                        )
                                    },
                                    {
                                        tabName: "Features",
                                        tabContent: (
                                            <p className={classes.textCenter}>
                                                CarbonPHP's core objective is to load all content in a controlled asynchronous fashion.
                                                <br />
                                                Features can be modified or replaced to suit your development.
                                                Here are some modules for quick reference.
                                                <br />
                                                Session
                                                Database
                                                Autoloading
                                                View
                                                Request
                                                Route
                                                Forks
                                                Files
                                                Servers & Sockets
                                                Serialized
                                                Skeleton
                                                Entities

                                                HTTP and HTTPS requests will always return the outer HTML presentation layer.
                                                If the Site Version changes on the server during an active user session, the layout
                                                will be reloaded. data and links will automatically be sent through AJAX.
                                                Post data is cleared from all other connections other that ajax. Content can be
                                                requested through HTTP, HTTPS, AJAX, and SOCKETS.
                                            </p>
                                        )
                                    },
                                    {
                                        tabName: "History",
                                        tabContent: (
                                            <p className={classes.textCenter}>
                                                Less than a second

                                                Google's Analysis

                                                "... 40% of [customers] will wait no more than three seconds before abandoning a retail or travel site."
                                                We analysed trending frameworks in every language to provide a semantically pleasing, powerful, and portable library. On average, CarbonPHP's application framework can render content in under a hundredth of second.
                                            </p>
                                        )
                                    }
                                ]}
                            />
                        </GridItem>
                    </GridContainer>
                </div>
            </div>
        );
    }
}

export default withStyles(completedStyle)(CarbonPHP);
