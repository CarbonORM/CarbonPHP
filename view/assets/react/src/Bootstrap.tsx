import React from 'react';
import {Redirect, Route, Switch} from 'react-router-dom';

import swal from '@sweetalert/with-react';

import context from 'variables/carbonphp';

import Public from 'layouts/Public';
import Private from 'layouts/Private';
import PageNotFound from 'views/Errors/PageNotFound';
// This is our ajax class
import {CodeBlock, dracula, googlecode} from 'react-code-blocks';

class bootstrap extends React.Component<any, {
    axios: import("axios").AxiosInstance,
    authenticate: string,
    authenticated?: boolean,
    pureWordpressPluginConfigured?: boolean,
    alert?: boolean,
    operationActive: boolean,
    isLoaded: boolean,
    darkMode: boolean,
    alertsWaiting: Array<any>,
    versions: Array<any>,
    id: string
}> {
    constructor(props) {
        super(props);
        this.state = {
            axios: context.axios,
            authenticate: '/carbon/authenticated',
            authenticated: null,
            pureWordpressPluginConfigured: false,
            alert: false,
            operationActive: false,
            isLoaded: false,
            alertsWaiting: [],
            darkMode: true,
            versions: [],
            id: ''
        };

        this.switchDarkAndLightTheme = this.switchDarkAndLightTheme.bind(this);
        this.handleResponseCodes = this.handleResponseCodes.bind(this);
        this.authenticate = this.authenticate.bind(this);
        this.subRoutingSwitch = this.subRoutingSwitch.bind(this);
        this.semaphoreLock = this.semaphoreLock.bind(this);
        this.testRestfulPostPutDeleteResponse = this.testRestfulPostPutDeleteResponse.bind(this);
        this.codeBlock = this.codeBlock.bind(this);
    }

    codeBlock = (markdown: String, highlight: String = "", language: String = "php", dark: boolean = true) => {
        return <CodeBlock
            text={markdown}
            language={language}
            showLineNumbers={true}
            theme={dark ? dracula : googlecode}
            highlight={highlight}
        />
    };

    switchDarkAndLightTheme = () => {
        this.setState({
            darkMode: !this.state.darkMode
        });
    };

    semaphoreLock = <T extends React.Component>(context ?: T): Function =>

        (callback: Function, localLock: boolean = false): Function => (opt ?: any): boolean => {

            const criticalSection = async (): Promise<void> => {
                console.time("Critical Section");
                try {
                    if (context === undefined) {
                        await callback(opt);
                    } else {
                        console.log('opActive: true');
                        await context.setState({operationActive: true}, async () => {
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
                swal({
                    text: 'An issue with out system has occurred.',
                    buttons: {
                        cancel: "Close",
                    }
                })
            };

            if (!this.state.operationActive) {
                if (!localLock) {
                    this.setState({operationActive: true},
                        () => criticalSection().catch(lockError))
                } else {
                    criticalSection().catch(lockError)
                }
                return true;
            }
            return false;
        };


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
                                        id={this.state.id}
                                        axios={this.state.axios}
                                        subRoutingSwitch={this.subRoutingSwitch}
                                        authenticated={this.state.authenticated}
                                        authenticate={this.authenticate}
                                        changeLoggedInStatus={this.changeLoggedInStatus}
                                        testRestfulPostPutDeleteResponse={this.testRestfulPostPutDeleteResponse}
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
                            id={this.state.id}
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
                id: res?.data?.id || '',
                pureWordpressPluginConfigured: res?.data?.pureWordpressPluginConfigured || false,
                authenticated: res?.data?.success || false,
                versions: Object.values(res?.data?.versions || {}).sort((v1: string, v2: string) => {
                    let lexicographical = false,
                        zeroExtend = false,
                        v1parts = v1.split('.'),
                        v2parts = v2.split('.');

                    function isValidPart(x) {
                        return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
                    }

                    if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
                        return NaN;
                    }

                    if (zeroExtend) {
                        while (v1parts.length < v2parts.length) v1parts.push("0");
                        while (v2parts.length < v1parts.length) v2parts.push("0");
                    }

                    for (let i = 0; i < v1parts.length; ++i) {
                        if (v2parts.length === i) {
                            return 1;
                        }

                        if (v1parts[i] === v2parts[i]) {
                            // noinspection UnnecessaryContinueJS - clarity call
                            continue;
                        } else if (v1parts[i] > v2parts[i]) {
                            return 1;
                        } else {
                            return -1;
                        }
                    }

                    if (v1parts.length !== v2parts.length) {
                        return -1;
                    }

                    return 0;

                }).reverse(),
                isLoaded: true
            });
        });
    };

    testRestfulPostPutDeleteResponse = (response, success, error) => {
        if (('data' in response) && ('rest' in response.data) &&
            (('created' in response.data.rest) ||
                ('updated' in response.data.rest) ||
                ('deleted' in response.data.rest))
        ) {
            if (typeof success === 'function') {
                return success(response);
            }
            if (success === null || typeof success === 'string') {
                swal("Success!", success, "success");
            }

            return response.data.rest?.created ?? response.data.rest?.updated ?? response.data.rest?.deleted ?? true;
        }

        if (typeof error === 'function') {
            return error(response);
        }

        if (error === null || typeof error === 'string') {
            swal("Whoops!", error, "error");
        }

        return false;
    };

    handleResponseCodes = data => {
        console.log("handleResponseCodes data", data);

        interface iAlert {
            intercept?: boolean,
            message?: string,
            title?: string,
            type?: string,
        }

        let handleAlert = (alert: iAlert): void => {

            console.log("alert", Object.assign({}, alert));

            if (alert.intercept === false) {
                return null; // recursive ending condition
            }

            swal({
                title: alert.title || 'Danger! You didn\'t set a title in your react alert.',
                text: alert.message || 'An alert was encountered, but no message could be parsed.',
                icon: alert.type || 'error',
            }).then(() => {
                let alertsWaiting = this.state.alertsWaiting;
                let nextAlert = alertsWaiting?.pop();
                this.setState({
                    alert: nextAlert !== undefined,
                    alertsWaiting: alertsWaiting
                }, () => nextAlert !== undefined && handleAlert(nextAlert));     // this is another means to end. note: doesn't hurt
            });

            //
        };

        if (data?.data?.alert) {
            console.log("handleResponseCodes ∈ Bootstrap");

            let a: iAlert = data.data.alert, stack: Array<iAlert> = [];

            // C6 Public Alerts

            ['info', 'success', 'warning', 'danger'].map(value => {
                if (value in a) {
                    a[value].map(message => {
                        stack.push({
                            'intercept': true,    // for now lets intercept all
                            'message': message,
                            'title': value,
                            'type': value,
                        });
                        return null;
                    });
                    console.log("stack", Object.assign({}, stack));
                }
                return false; // free up memory through a map
            });

            if (stack.length === 0) {
                return null;
            }

            if (this.state.alert === true) {
                let alertsWaiting = this.state.alertsWaiting;
                alertsWaiting.push(stack);
                this.setState({
                    alertsWaiting: alertsWaiting
                });
                return null;
            }

            let alert = stack.pop();

            console.log("alert", Object.assign({}, alert));

            this.setState({
                alert: true,
                alertsWaiting: stack
            });

            handleAlert(alert);
        }
    };

    componentDidMount() {
        this.state.axios.interceptors.request.use(req => {
                if (req.method === 'get' && req.url.match(/^\/rest\/.*$/)) {
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
                if (response?.data?.alert) {
                    console.log("alert ∈ response");
                    this.handleResponseCodes(response);
                    return (response?.data?.alert?.error || response?.data?.alert?.danger) ?
                        Promise.reject(response) :
                        response;
                }
                return response;
            },
            error => {
                /* Do something with response error
                   this changes from project to project depending on how your server uses response codes.
                   when you can control all errors universally from a single api, return Promise.reject(error);
                   is the way to go.
                */
                this.handleResponseCodes(error.response);
                console.log("Carbon Axios Caught A Response Error response :: ", error.response);
                return Promise.reject(error);
                // return error.response;
            }
        );

        this.authenticate();
    }

    render() {
        console.log("LOGIN JSX RENDER");

        const {isLoaded, authenticated, alert} = this.state;

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
                                    darkMode={this.state.darkMode}
                                    versions={this.state.versions}
                                    switchDarkAndLightTheme={this.switchDarkAndLightTheme}
                                    codeBlock={this.codeBlock}
                                    axios={this.state.axios}
                                    subRoutingSwitch={this.subRoutingSwitch}
                                    authenticated={authenticated}
                                    authenticate={this.authenticate}
                                    changeLoggedInStatus={this.changeLoggedInStatus}
                                    testRestfulPostPutDeleteResponse={this.testRestfulPostPutDeleteResponse}
                                    path={path}
                                    {...props}
                                /> :
                                <Public
                                    darkMode={this.state.darkMode}
                                    versions={this.state.versions}
                                    switchDarkAndLightTheme={this.switchDarkAndLightTheme}
                                    codeBlock={this.codeBlock}
                                    axios={this.state.axios}
                                    subRoutingSwitch={this.subRoutingSwitch}
                                    authenticated={authenticated}
                                    authenticate={this.authenticate}
                                    changeLoggedInStatus={this.changeLoggedInStatus}
                                    testRestfulPostPutDeleteResponse={this.testRestfulPostPutDeleteResponse}
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

export default bootstrap;
