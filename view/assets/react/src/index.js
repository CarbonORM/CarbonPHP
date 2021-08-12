// noinspection NpmUsedModulesInstalled
import React from "react";
// noinspection NpmUsedModulesInstalled
import ReactDOM from "react-dom";
import {createBrowserHistory} from "history";
import {Router, Switch, Route} from "react-router-dom";

// Custom CarbonPHP Context Switch
import Bootstrap from "Bootstrap.tsx";
import PageNotFound from "views/Errors/PageNotFound";

import "assets/css/material-dashboard-react.css?v=1.5.0";

const hist = createBrowserHistory();

const APP_ROOT = process.cwd();

ReactDOM.render(
    <Router history={hist}>
        <Switch>
            <Bootstrap/>
            <Route component={PageNotFound} />
        </Switch>
    </Router>,
    document.getElementById("root")
);
