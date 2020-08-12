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
import {AxiosInstance} from "axios";
import qs from 'qs';


const styles = {
  ...appStyle,
  ...sweetAlertStyle
};

class bootstrap extends React.Component<any, {
  axios: AxiosInstance,
  authenticate: string,
  authenticated?: boolean,
  alert?: any,
  operationActive: boolean,
  isLoaded: boolean,
  alertsWaiting: Array<any>
}> {
  constructor(props) {
    super(props);
    this.state = {
      axios: context.axios,
      authenticate: "/carbon/authenticated",
      authenticated: null,
      alert: null,
      operationActive: false,
      isLoaded: false,
      alertsWaiting: []
    };
    this.handleResponseCodes = this.handleResponseCodes.bind(this);
    this.authenticate = this.authenticate.bind(this);
    this.subRoutingSwitch = this.subRoutingSwitch.bind(this);
    this.semaphoreLock = this.semaphoreLock.bind(this);
  }

  semaphoreLock = <T extends React.Component>(context ?: T): Function =>
    (callback: Function, localLock: boolean = false): Function => (opt ?: any): boolean => {

      const criticalSection = async (): Promise<void> => {
        console.time("Critical Section");
        try {
          if (context === undefined) {
            await callback(opt);
          } else {
            console.log('opActive: true');
            await context.setState({ operationActive: true }, async () => {
              await callback(opt);
              console.log('opActive: false');
              context.setState({
                operationActive: false
              })
            })
          }
        } finally {
          console.timeEnd("Critical Section")
        }
        if (!localLock) {
          this.setState({
            operationActive: false
          })
        }
      };

      const lockError = () => {
        this.setState({
          alert: <SweetAlert
            warning
            title="Oh no!" onConfirm={() => this.setState({ alert: null })}>
            An issue with out system has occurred.
          </SweetAlert>
        })
      };

      if (!this.state.operationActive) {
        if (!localLock) {
          this.setState({ operationActive: true },
            () => criticalSection().catch(lockError))
        } else {
          criticalSection().catch(lockError)
        }
        return true;
      }
      return false;
    };


  changeLoggedInStatus = () => {
    this.setState({ authenticated: !this.state.authenticated });
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
            return "";
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
                    axios={this.state.axios}
                    subRoutingSwitch={this.subRoutingSwitch}
                    authenticated={this.state.authenticated}
                    authenticate={this.authenticate}
                    changeLoggedInStatus={this.changeLoggedInStatus}
                    path={prop.path}
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
              axios={this.state.axios}
              subRoutingSwitch={this.subRoutingSwitch}
              authenticated={this.state.authenticated}
              authenticate={this.authenticate}
              changeLoggedInStatus={this.changeLoggedInStatus}
              path={prop.path}
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
    this.state.axios.get(this.state.authenticate).then(res => {
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


  handleResponseCodes = data => {
    console.log("handleResponseCodes data", data);

    let alert = a => {
      let message,
        type,
        title,
        obj,
        stack = a;

      if (Array.isArray(stack)) {
        if (!stack.length) {
          // recursive ending condition
          return null;
        }
        do {
          obj = stack.shift();
        } while (stack.length && (obj && !obj.intercept));  // only catch alerts marked for intercept
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

      let alertsWaiting = this.state.alertsWaiting;

      // @ts-ignore
      return (
        <SweetAlert
          type={type}
          title={title}
          onConfirm={() => this.setState({
            alert: stack ? stack : alertsWaiting.pop(),
            alertsWaiting: alertsWaiting
          })}
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
      console.log("handleResponseCodes ∈ Bootstrap");

      let a = data.data.alert, stack = [];

      // C6 Public Alerts
      if (typeof a === 'object' && a !== null) {
        ['info', 'success', 'warning', 'danger'].map(value => {
          if (value in a) {
            a[value].map(message => {
              stack.push({
                'intercept': true,
                'message': message,
                'title': value,
                'type': value,
              })
              return null;
            });
            console.log("stack", Object.assign({}, stack));
          }
          return null;
        })
      } else {
        console.log('failed to decode the alert');
      }

      console.log(this.state.alert, this.state.alert !== null, this.state.alert !== undefined)

      if (this.state.alert === null || this.state.alert === undefined) {
        this.setState({
          alert: alert(stack)
        });
      } else {
        let alertsQ = this.state.alertsWaiting;
        alertsQ.push(alert(stack));
        this.setState({
          alertsWaiting: alertsQ
        });
      }
    }
  };

  componentDidMount() {
    this.state.axios.interceptors.request.use(req => {
        if (req.method === 'get') {
          req.params = JSON.stringify(req.params)
        }
        return req;
      }, error => {
        return Promise.reject(error);
      }
    );
    this.state.axios.interceptors.response.use(
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
          this.handleResponseCodes(response);
        }
        return response;
      },
      error => {
        // Do something with response error
        this.handleResponseCodes(error.response);
        console.log("Carbon Axios Caught A Response Error response :: ", error.response);
        // return Promise.reject(error);
        return error.response;
      }
    );

    this.authenticate();
  }

  render() {
    console.log("LOGIN JSX RENDER");

    const { isLoaded, authenticated, alert } = this.state;

    if (!isLoaded) {
      return <h2>Loading...</h2>;
    } else {
      //DO NOT DELETE; WILL USE LATER;
      // get the first element in the uri /{first}/
      let path = this.props.location.pathname;

      // // Remove the context root from the uri
      path = path.substr(context.contextHost.length, path.length).split("/")[1];

      // Routes that belong to the public and private sector
      let Routes = [];

      // const Route
      // @ts-ignore
      Routes = Routes.concat([
        key => (
          <Route
            key={key}
            path="/"
            render={props => (authenticated ?
                <Private
                  axios={this.state.axios}
                  subRoutingSwitch={this.subRoutingSwitch}
                  authenticated={authenticated}
                  authenticate={this.authenticate}
                  changeLoggedInStatus={this.changeLoggedInStatus}
                  path={path}
                  {...props}
                /> :
                <Public
                  axios={this.state.axios}
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

export default withStyles(styles)(bootstrap);
