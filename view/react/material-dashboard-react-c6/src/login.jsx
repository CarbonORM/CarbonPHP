import React from "react";
import {Redirect, Route, Switch} from "react-router-dom";

import SweetAlert from "react-bootstrap-sweetalert";

import appStyle from "assets/jss/material-dashboard-react/layouts/carbonPHPStyles";
import sweetAlertStyle from "assets/jss/material-dashboard-react/views/sweetAlertStyle";

import context from "variables/carbonphp";

import Public from "layouts/Public";
import Private from "layouts/Private";
import PageNotFound from "views/Errors/PageNotFound";

// This is our ajax class
import withStyles from "@material-ui/core/styles/withStyles";

const styles = theme => ({
    ...appStyle(theme),
    ...sweetAlertStyle
});

class login extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            carbon: context.CarbonPHP,
            authenticate: "/carbon/authenticated",
            authenticated: null,
            error: null
        };
        this.handleResponceCodes = this.handleResponceCodes.bind(this);
        this.authenticate = this.authenticate.bind(this);
        this.subRoutingSwitch = this.subRoutingSwitch.bind(this);
    }

    changeLoggedInStatus = () => {
        this.setState({authenticated: !this.state.authenticated});
    };

    subRoutingSwitch = (route, rest) => {
        if (rest === undefined) {
            rest = [];
        }
        return <Switch>
            {route.map((prop, key) => {
                if (prop.redirect) {
                    if (!prop.pathTo) {
                        console.log('bad route redirect,', prop);
                        return;
                    }
                    return <Redirect
                        exact
                        from={prop.path}
                        to={prop.pathTo}
                        key={key}/>;
                }
                if (prop.views) {
                    return prop.views.map((x, key) => {
                        return (
                            <Route
                                exact
                                path={x.path}
                                render={y => (
                                    <x.component
                                        {...x}
                                        {...y}
                                        {...rest} />
                                )}
                                key={key}/>
                        );
                    });
                }
                return <Route
                    path={prop.path}
                    render={props => (
                        <prop.component
                            {...prop}
                            {...props}
                            {...rest} />
                    )}
                    key={key}/>;
            })}
            <Route component={PageNotFound}/>
        </Switch>
    };

    authenticate = () => {
        this.state.carbon.get(this.state.authenticate).then(res => {
            console.log("authenticate data: ", res);
            this.setState({
                authenticated:
                    res !== undefined &&
                    res !== null &&
                    "data" in res &&
                    res.data !== null &&
                    "success" in res.data &&
                    res.data.success,
                isLoaded: true
            });
        });
    };


    handleResponceCodes = data => {
        console.log("handleResponceCodes data", data);
        let alert = a => {
            let message,
                type,
                title,
                obj,
                stack = a.slice();

            if (Array.isArray(stack)) {
                if (!stack.length) {
                    // recursive ending condition
                    return null;
                }
                do {
                    obj = stack.shift();
                } while (stack.length && (obj && obj.intercept));
                stack = alert(stack);
            } else {
                obj = stack;
            }
            if ("object" === typeof obj) {
                if ("intercept" in obj && !obj.intercept) return null;
                if ("message" in obj) message = obj.message;
                if ("title" in obj) title = obj.title;
                if ("type" in obj) type = obj.type;
            } else if ("string" === typeof obj) {
                type = "success";
                message = obj;
            } else {
                console.log("Could not handle alert", obj);
            }
            if (message === undefined) {
                message = "An alert was encountered, but no message could be parsed.";
            }
            if (type === undefined) {
                type = "danger";
            }
            if (title === undefined) {
                title = "Danger! You didn't set a title in your react alert.";
            }

            return (
                <SweetAlert
                    type={type}
                    style={{
                        display: "block",
                        marginTop: "-200px"
                    }}
                    title={title}
                    onConfirm={() => this.setState({alert: stack})}
                    confirmBtnCssClass={
                        this.props.classes.button + " " + this.props.classes.success
                    }
                >
                    {message}
                </SweetAlert>
            );
        };

        if (
            "object" === typeof data &&
            data.hasOwnProperty("data") &&
            data.data !== null &&
            "object" === typeof data.data &&
            "alert" in data.data
        ) {
            // console.log("handleResponceCodes ∈ Armatus");
            this.setState({alert: alert(data.data.alert)});
        }
    };

    componentDidMount() {
        this.state.carbon.interceptors.response.use(
            response => {
                // Do something with response data
                console.log(
                    "Every Axios response is logged in login.jsx :: ",
                    response
                );
                if (
                    response.hasOwnProperty("data") &&
                    "object" === typeof response.data &&
                    response.data !== null &&
                    "alert" in response.data
                ) {
                    console.log("alert ∈ response");
                    this.handleResponceCodes(response);
                }
                return response;
            },
            error => {
                // Do something with response error
                this.handleResponceCodes(error.response);
                console.log("Carbon Axios Caught A Responce Error response :: ", error.response);
                // return Promise.reject(error);
                return error.response;
            }
        );

        this.authenticate();
    }

    render() {
        console.log("LOGIN JSX RENDER");

        const {error, isLoaded, authenticated, carbon, alert} = this.state;

        if (error) {
            return <div>Error: {error.message}</div>;
        } else if (!isLoaded) {
            return <div>Loading...</div>;
        } else {
            //DO NOT DELETE; WILL USE LATER;
            // get the first element in the uri /{first}/
            let path = this.props.location.pathname;

            // // Remove the context root from the uri
            path = path.substr(context.contextHost.length, path.length).split("/")[1];

            // Routes that belong to the public and private sector
            let Routes = [];

            let RouteType = authenticated ? Private : Public;

            // const Route
            Routes = Routes.concat([
                key => (
                    <Route
                        key={key}
                        path="/"
                        render={props => (
                            <RouteType
                                carbon={carbon}
                                subRoutingSwitch={this.subRoutingSwitch}
                                authenticated={authenticated}
                                authenticate={this.authenticate}
                                changeLoggedInStatus={this.changeLoggedInStatus}
                                path={path}
                                {...props}
                            />
                        )}
                    />
                )
            ]);

            return (
                <div>
                    {alert}
                    {Routes.map((closure, key) => closure(key))}
                </div>
            );
        }
    }
}

export default withStyles(styles)(login);
