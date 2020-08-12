/*!

=========================================================
* Material Kit React - v1.7.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-kit-react
* Copyright 2019 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://github.com/creativetimofficial/material-kit-react/blob/master/LICENSE.md)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/
import React from "react";
// nodejs library to set properties for components
// nodejs library that concatenates classes
// @material-ui/core components
// core components
import GridContainer from "../../components/Grid/GridContainer";
import GridItem from "../../components/Grid/GridItem";
import Button from "../../components/CustomButtons/Button";

import landingPageStyle from "../../assets/jss/material-kit-react/views/landingPage";
// Sections for this page
import {WithStyles} from "@material-ui/styles";


import Card from "../../components/Card/Card";
import CardHeader from "../../components/Card/CardHeader";
import Popup from "../../components/Popup/Popup";
import CustomTabs from "../../components/CustomTabs/CustomTabs";
import CardBody from "../../components/Card/CardBody";
import Table from "../../components/Table/Table";
import {AxiosInstance} from "axios";
import {C6, COLUMNS, iCarbon_Features, iCarbon_Groups, iCarbon_Users} from "variables/C6";
import {withStyles} from "@material-ui/core";
import SweetAlert from "react-bootstrap-sweetalert";
import {SweetAlertRenderProps} from "react-bootstrap-sweetalert/dist/types";


// @ts-ignore
interface ILandingPage extends WithStyles<typeof landingPageStyle> {
  axios: AxiosInstance
}

class AccessControl extends React.Component<ILandingPage, {
  currency: 'USD' | 'GBP' | 'EUR' | 'PLN',
  grantRolesModalOpen: boolean,
  createRolesAndAssignModalOpen: boolean,
  users?: Array<iCarbon_Users>,
  features?: Array<iCarbon_Features>,
  groups?: Array<iCarbon_Groups>,
  alert?: any,
  role?: string,
  feature?: iCarbon_Features,
  group?: iCarbon_Groups,
  user?: iCarbon_Users,
}> {
  constructor(props) {
    super(props);
    this.state = {
      currency: 'EUR',
      grantRolesModalOpen: false,
      createRolesAndAssignModalOpen: true,
      users: [],
      alert: null,
      features: [],
      groups: []
    };

    this.newFeature = this.newFeature.bind(this);
    this.newGroup = this.newGroup.bind(this);
  }

  handleChange = event => {
    this.setState({
      currency: event.target.value
    });
  };

  handleModalChange = () => {
    this.setState({
      grantRolesModalOpen: !this.state.grantRolesModalOpen
    })
  };

  handleNatModalChange = () => {
    this.setState({
      createRolesAndAssignModalOpen: !this.state.createRolesAndAssignModalOpen
    })
  };

  componentDidMount() {
    const { axios } = this.props;

    axios.get('/rest/' + C6.carbon_users.TABLE_NAME, {
      params: ({  // qs.stringify
        [C6.SELECT]: [
          C6.carbon_users.USER_USERNAME,
          C6.carbon_users.USER_FIRST_NAME,
          C6.carbon_users.USER_LAST_NAME,
          C6.carbon_users.USER_ID,
          [C6.DISTINCT, C6.carbon_features.FEATURE_CODE]
        ],
        [C6.JOIN]: {
          [C6.LEFT]: {
            [C6.carbon_user_groups.TABLE_NAME]: [
              C6.carbon_users.USER_ID,
              C6.carbon_user_groups.USER_ID
            ],
            [C6.carbon_groups.TABLE_NAME]: [
              C6.carbon_user_groups.GROUP_ID,
              C6.carbon_groups.ENTITY_ID
            ],
            [C6.carbon_feature_group_references.TABLE_NAME]: [
              C6.carbon_groups.ENTITY_ID,
              C6.carbon_feature_group_references.GROUP_ENTITY_ID
            ],
            [C6.carbon_features.TABLE_NAME]: [
              C6.carbon_features.FEATURE_ENTITY_ID,
              C6.carbon_feature_group_references.FEATURE_ENTITY_ID
            ]
          }
        },
        [C6.PAGINATION]: {
          [C6.LIMIT]: 10
        }
      })
    }).then(response => {
      this.setState({
        users: response.data.rest
      })
    });



   /* axios.get('/rest/' + C6.carbon_features.TABLE_NAME).then(response => {
      this.setState({
        features: response.data.rest
      })
    });


    axios.get('/rest/' + C6.carbon_groups.TABLE_NAME, {
      params: {
        [C6.PAGINATION]: {
          [C6.LIMIT]: 2
        }
      }
    })
      .then(response => {
        this.setState({
          groups: response.data.rest
        })
      })
*/
  }

  newFeature() {
    const { axios } = this.props;
    axios.post('/rest/' + C6.carbon_features.TABLE_NAME, {
      [C6.carbon_features.FEATURE_CODE]: this.state.feature.feature_code,
    }).then(response => { // todo check status
      this.setState({ alert: null });
    })
  }


  newGroup() {
    const { axios } = this.props;
    axios.post('/rest/' + C6.carbon_groups.TABLE_NAME, {
      [C6.carbon_groups.GROUP_NAME]: this.state.group.group_name,
    }).then(response => {
      console.log(
        response.data.rest
      )
    })
  }

  featureButtonMappings(code) {
    return this.state.features.map(() => <Button color="default">disabled</Button>);
  }

  render() {
    const { alert, features } = this.state;
    const { classes } = this.props;

    function humanize(str) {
      let i, frags = str.split('_');
      for (i = 0; i < frags.length; i++) {
        frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
      }
      return frags.join(' ');
    }

    const featureCodes: Array<string> = features.map(value => humanize(value.feature_code));

    return (
      <div>
        <GridItem lg={12} md={12} sm={12}>
          {alert}
          <GridContainer>
            <GridItem xs={12} sm={12} md={12}>
              <Card className={classes.whiteOpacity}>
                <CardHeader color="success">
                  <h4 className={classes.cardTitleWhite}>Assignable Roles Table</h4>
                  <p className={classes.cardCategoryWhite}>
                    The roles created here can be assigned to users you manage
                  </p>
                  <Button
                    color="default"
                    onClick={() => this.setState({
                      alert: <SweetAlert
                        title={<>Create a new feature flag <br/> (Site Admin Only)</>}
                        customButtons={<>
                          <Button color="default" onClick={() => this.setState({ alert: null })}>Cancel</Button>
                          <Button color="info" onClick={this.newFeature}>Submit</Button></>}
                        onConfirm={() => this.setState({ alert: null })}
                        onCancel={() => this.setState({ alert: null })}
                        dependencies={[this.state.user]}
                      >
                        {(renderProps: SweetAlertRenderProps) => (
                          <>
                            Your New Feature Name:
                            <hr/>
                            <input
                              type={'text'}
                              ref={renderProps.setAutoFocusInputRef}
                              className="form-control"
                              value={this.state.feature.feature_code}
                              onKeyDown={renderProps.onEnterKeyDownConfirm}
                              onChange={(e) => this.setState({
                                feature: {
                                  ...this.state.feature,
                                  feature_code: e.target.value
                                }
                              })}
                              placeholder={'Feature Flag Name'}
                            />
                            <br/>
                            <hr/>
                          </>
                        )}
                      </SweetAlert>
                    })}
                  >
                    Start New Feature
                  </Button>
                  <Button color="warning"
                          onClick={() => this.setState({
                            alert: <SweetAlert
                              title={<>Create a permission group</>}
                              customButtons={<>
                                <Button color="default" onClick={() => this.setState({ alert: null })}>Cancel</Button>
                                <Button color="info" onClick={this.newGroup}>Submit</Button></>}
                              onConfirm={() => this.setState({ alert: null })}
                              onCancel={() => this.setState({ alert: null })}
                              dependencies={[this.state.user]}
                            >
                              {(renderProps: SweetAlertRenderProps) => (
                                <>
                                  Your New Group Name:
                                  <hr/>
                                  <input
                                    type={'text'}
                                    ref={renderProps.setAutoFocusInputRef}
                                    className="form-control"
                                    value={this.state.group.group_name}
                                    onKeyDown={renderProps.onEnterKeyDownConfirm}
                                    onChange={(e) => this.setState({
                                      group: {
                                        ...this.state.group,
                                        group_name: e.target.value
                                      }
                                    })}
                                    placeholder={'Group Permissions Name'}
                                  />
                                  <br/>
                                  <hr/>
                                </>
                              )}</SweetAlert>
                          })}>Create New Group</Button>
                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="success"
                    tableHead={["Role Name", ...featureCodes, "Admin"]}
                    tableData={
                      this.state.groups.map(group => {
                        const name = humanize(group.group_name);
                        return [
                          <p onClick={this.handleNatModalChange}>{name}</p>,
                          ...this.featureButtonMappings(name),
                          <Button onClick={this.handleModalChange} color="danger">Admin</Button>
                        ]
                      })
                    }
                  />
                </CardBody>
              </Card>
            </GridItem>
            <GridItem xs={12} sm={12} md={12}>
              <Card className={classes.whiteOpacity}>
                <CardHeader color="success">
                  <h4 className={classes.cardTitleWhite}>
                    Create New Users
                  </h4>
                  <p className={classes.cardCategoryWhite}>
                    Create and assign new users access to connect features
                  </p>

                  <Button
                    color="default"
                    onClick={() => this.setState({
                      alert: <SweetAlert
                        title={<>Create a new User<br/> (Site Admin Only)</>}
                        customButtons={<>
                          <Button color="default" onClick={() => this.setState({ alert: null })}>Cancel</Button>
                          <Button color="info">Submit</Button></>}
                        onConfirm={() => this.setState({ alert: null })}
                        onCancel={() => this.setState({ alert: null })}
                        dependencies={[this.state.user]}
                      >
                        {(renderProps: SweetAlertRenderProps) => (
                          <form>
                            New user's name
                            is: {this.state.user ? this.state.user[C6.carbon_users.USER_FIRST_NAME] : ""}{" "}
                            {this.state.user ? this.state.user[C6.carbon_users.USER_LAST_NAME] : ""}
                            <hr/>
                            <input
                              type={'text'}
                              ref={renderProps.setAutoFocusInputRef}
                              className="form-control"
                              value={this.state.user && this.state.user[C6.carbon_users.USER_FIRST_NAME]}
                              onKeyDown={renderProps.onEnterKeyDownConfirm}
                              onChange={(e) => this.setState({
                                user: {
                                  ...this.state.user,
                                  [C6.carbon_users.USER_FIRST_NAME]: e.target.value
                                },
                              })}
                              placeholder={'First name'}
                            />
                            <br/>
                            <input
                              type={'text'}
                              className="form-control"
                              value={this.state.user && this.state.user[COLUMNS[C6.carbon_users.USER_LAST_NAME]]}
                              onKeyDown={renderProps.onEnterKeyDownConfirm}
                              onChange={(e) => this.setState({
                                user: {
                                  ...this.state.user,
                                  [C6.carbon_users.USER_LAST_NAME]: e.target.value
                                },
                              })}
                              placeholder={'Last name'}
                            />
                            <hr/>
                          </form>
                        )}
                      </SweetAlert>
                    })}
                  >
                    Start New Feature
                  </Button>

                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="info"
                    tableHead={["User ID", "User Name", "Some Role A", "Some Role B", "Some Role C"]}
                    tableData={
                      this.state.users.map((user, key) => [
                        user.user_id,
                        user.user_username,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>])
                    }
                  />
                </CardBody>
              </Card>
            </GridItem>
          </GridContainer>
        </GridItem>
        <Popup
          open={this.state.grantRolesModalOpen}
          handleClose={this.handleModalChange}>
          <CustomTabs
            headerColor="warning"
            tabs={[
              {
                tabName: "Warning",
                tabContent: (
                  <div>
                    <h5><b>You will be affecting 9 users already assigned to this role.</b></h5>
                    <br/>
                    <p>Moving to the Grant Ability.... will allow this user to... create and manage
                      other users...</p>
                  </div>
                )
              },
              {
                tabName: "Grant Grantability Status",
                tabContent: (
                  <GridContainer>
                    <GridItem xs={12} sm={12} md={12}>
                      <Table
                        tableHeaderColor="info"
                        tableHead={["Role Name", "Grantability Status"]}
                        tableData={[
                          ["Pharmacists", <Button color="success">Granted</Button>],
                          ["Store Manager", <Button color="success">Granted</Button>],
                          ["Order People", <Button color="info">Assign</Button>],
                          ["Role 1", <Button color="info">Assign</Button>],
                          ["Role 2 < insert Buzz Word Here >",
                            <Button color="info">Assign</Button>],
                          ["Role CSOS", <Button color="info">Assign</Button>],
                          ["R2D2", <Button color="success">Assign</Button>],
                        ]}
                      />
                    </GridItem>
                  </GridContainer>
                )
              },
            ]}
          />
        </Popup>
      </div>
    );
  }
}


export default withStyles(landingPageStyle)(AccessControl);
