// noinspection NpmUsedModulesInstalled
import React from "react";
// noinspection NpmUsedModulesInstalled
import ReactDOM from "react-dom";
import {createBrowserHistory} from "history";
import {Router, Switch, Route} from "react-router-dom";

// Custom CarbonPHP Context Switch
import Login from "login";
import PageNotFound from "views/Errors/PageNotFound";

import "assets/css/material-dashboard-react.css?v=1.5.0";

const hist = createBrowserHistory();

ReactDOM.render(
    <Router history={hist}>
        <Switch>
            <Login/>
            <Route component={PageNotFound} />
        </Switch>
    </Router>,
    document.getElementById("root")
);
