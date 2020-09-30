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
import React, {ChangeEvent} from "react";
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
import CardBody from "../../components/Card/CardBody";
import Table from "../../components/Table/Table";
import {AxiosInstance} from "axios";
import {
  C6,
  convertForRequestBody,
  iCarbon_Feature_Group_References,
  iCarbon_Features,
  iCarbon_Groups,
  iCarbon_User_Groups,
  iCarbon_Users
} from "variables/C6";
import CustomInput from "../../components/CustomInput/CustomInput";
import swal from '@sweetalert/with-react';
import withStyles from "@material-ui/core/styles/withStyles";


interface iAccessControl extends WithStyles<typeof landingPageStyle> {
  axios: AxiosInstance;
  testRestfulPostResponse: Function;
}

interface UserAccessControl extends iCarbon_Users {
  group_name?: string,
  feature_code?: string
}

interface iGroupFeatures extends iCarbon_Groups, iCarbon_Features {
  allowed_to_grant_group_id?: string;
}


class AccessControl extends React.Component<iAccessControl, {
  currency: 'USD' | 'GBP' | 'EUR' | 'PLN',
  grantRolesModalOpen: boolean,
  createRolesAndAssignModalOpen: boolean,
  users?: Array<UserAccessControl>,
  features?: Array<iCarbon_Features>,
  groups?: Array<iGroupFeatures>,
  alert?: any,
  role?: string,
  feature: iCarbon_Features,
  group: iGroupFeatures,
  user: UserAccessControl,
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
      groups: [],
      group: {},
      feature: {},
      user: {}
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
      params: {
        [C6.SELECT]: [
          C6.carbon_users.USER_USERNAME,
          C6.carbon_users.USER_FIRST_NAME,
          C6.carbon_users.USER_LAST_NAME,
          C6.carbon_users.USER_ID,
          [C6.GROUP_CONCAT, C6.carbon_features.FEATURE_CODE],
          [C6.GROUP_CONCAT, C6.carbon_groups.GROUP_NAME]
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
          [C6.LIMIT]: 100
        }
      }
    }).then(response => this.setState({ users: (response.data.rest || []) }));

    axios.get('/rest/' + C6.carbon_features.TABLE_NAME)
      .then(response => this.setState({ features: (response.data.rest || []) }));

    axios.get('/rest/' + C6.carbon_groups.TABLE_NAME, {
      params: {
        [C6.SELECT]: [
          C6.carbon_groups.ENTITY_ID,
          C6.carbon_groups.GROUP_NAME,
          [C6.GROUP_CONCAT, C6.carbon_features.FEATURE_CODE],
          [C6.GROUP_CONCAT, C6.carbon_group_references.ALLOWED_TO_GRANT_GROUP_ID]
        ],
        [C6.JOIN]: {
          [C6.LEFT]: {
            [C6.carbon_group_references.TABLE_NAME]: [
              C6.carbon_group_references.GROUP_ID,
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
          [C6.LIMIT]: 100
        }
      }
    }).then(response => this.setState({ groups: response.data.rest }))

  }

  deleteFeatureFromGroup(groupId: string, featureId: string) {
    this.props.axios.delete('/rest/' + C6.carbon_feature_group_references.TABLE_NAME, {
      data: {
        [C6.WHERE]: {
          [C6.carbon_feature_group_references.FEATURE_ENTITY_ID]: featureId,
          [C6.carbon_feature_group_references.GROUP_ENTITY_ID]: groupId,
        }
      }
    })
      .then(response => this.setState({
        groups: this.state.groups.map(obj => {
          if (obj.entity_id !== groupId) {
            return obj;
          }
          const fullFeature: iCarbon_Features = this.state.features.find((feature: iCarbon_Features) => feature.feature_entity_id === featureId);

          let regex = new RegExp('(^|,)' + fullFeature.feature_code + ',?', 'g');

          obj.feature_code = obj.feature_code.replace(regex, ',');

          return obj;
        })
      }))
  }

  deleteGroupFromUser(userId: string, groupId: string) {
    this.props.axios.delete('/rest/' + C6.carbon_user_groups.TABLE_NAME, {
      data: {
        [C6.WHERE]: {
          [C6.carbon_user_groups.GROUP_ID]: userId,
          [C6.carbon_user_groups.USER_ID]: groupId,
        }
      }
    })
      .then(() => this.setState({
        users: this.state.users.map(obj => {
          if (obj.user_id !== userId) {
            return obj;
          }
          const fullGroup: iGroupFeatures =
            this.state.groups.find((group: iGroupFeatures) => group.entity_id === groupId);

          let regex = new RegExp('(^|,)' + fullGroup.group_name + ',?', 'g');

          obj.group_name = obj.group_name.replace(regex, ',');

          return obj;
        })
      }))
  }

  newGroupGrantabillity(modifyGroupId: string, allowGroupGrantRightsId: string) {
    this.setState({ alert: null }, () =>
      this.props.axios.post('/rest/' + C6.carbon_group_references.TABLE_NAME,{
          [C6.carbon_group_references.GROUP_ID]: modifyGroupId,
          [C6.carbon_group_references.ALLOWED_TO_GRANT_GROUP_ID]: allowGroupGrantRightsId,
        })
        .then(response => (this.props.testRestfulPostResponse(response, 'Successfully Created Feature Code',
          'An unknown issue occurred. We will be looking into this shortly.'))
          && this.setState({
            groups: this.state.groups.map(obj => {
              if (obj.entity_id !== modifyGroupId) {
                return obj;
              }
              obj.allowed_to_grant_group_id += ',' + allowGroupGrantRightsId;
              return obj
            })
          }))
    )
  }

  deleteGroupGrantabillity(modifyGroupId: string, allowGroupGrantRightsId: string) {
    this.props.axios.delete('/rest/' + C6.carbon_group_references.TABLE_NAME, {
      data: {
        [C6.WHERE]: {
          [C6.carbon_group_references.GROUP_ID]: modifyGroupId,
          [C6.carbon_group_references.ALLOWED_TO_GRANT_GROUP_ID]: allowGroupGrantRightsId,
        }
      }
    })
      .then(response => this.setState({
        groups: this.state.groups.map(obj => {
          if (obj.entity_id !== modifyGroupId) {
            return obj;
          }

          let regex = new RegExp('(^|,)' + allowGroupGrantRightsId + ',?', 'g');

          obj.allowed_to_grant_group_id = obj.allowed_to_grant_group_id.replace(regex, ',');

          return obj;
        })
      }))
  }

  newFeature() {
    let id = '';
    this.setState({ alert: null }, () =>
      this.props.axios.post('/rest/' + C6.carbon_features.TABLE_NAME,
        convertForRequestBody(this.state.feature, C6.carbon_features.TABLE_NAME))
        .then(response => (id = this.props.testRestfulPostResponse(response, 'Successfully Created Feature Code',
          'An unknown issue occurred. We will be looking into this shortly.')) && this.setState({
          feature: {
            feature_code: this.state.feature.feature_code,
            feature_entity_id: id
          }
        }, () => this.setState({
          features: [
            ...this.state.features,
            this.state.feature
          ]
        }))))
  }

  newUser() {
    const { axios } = this.props;
    let id = '';
    this.setState({ alert: null }, () =>
      axios.post('/rest/' + C6.carbon_users.TABLE_NAME,
        convertForRequestBody(this.state.user, C6.carbon_users.TABLE_NAME))
        .then(response => (id =
          this.props.testRestfulPostResponse(response,
            'New User Successfully Created',
            'An unknown issue occurred. We will be looking into this shortly.'
          )) && this.setState({
          user: {
            ...this.state.users,
            user_id: id
          }
        }, () => this.setState({
          users: [
            ...this.state.users,
            this.state.user
          ]
        }))))
  }

  newGroup() {
    const { axios } = this.props;
    let id = '';
    this.setState({ alert: null }, () =>
      axios.post('/rest/' + C6.carbon_groups.TABLE_NAME,
        convertForRequestBody(this.state.group, C6.carbon_groups.TABLE_NAME))
        .then(response =>
          (id = this.props.testRestfulPostResponse(response, 'Successfully Created The Group',
            'An unknown issue occurred. We will be looking into this shortly.')) && this.setState({
            group: {
              group_name: this.state.group.group_name,
              entity_id: id
            }
          }, () => this.setState({
            groups: [
              ...this.state.groups,
              this.state.group,
            ]
          }))));
  }

  addFeatureToGroup(featureId: string, groupId: string) {
    const { axios } = this.props;
    const payload: iCarbon_Feature_Group_References = {
      feature_entity_id: featureId,
      group_entity_id: groupId
    };

    this.setState({ alert: null }, () =>
      axios.post('/rest/' + C6.carbon_feature_group_references.TABLE_NAME,
        convertForRequestBody(payload, C6.carbon_feature_group_references.TABLE_NAME))
        .then(response =>
          (this.props.testRestfulPostResponse(response, null,
            'An unknown issue occurred. We will be looking into this shortly.')) && this.setState({
            groups: this.state.groups.map(obj => {
              if (obj.entity_id !== groupId) {
                return obj;
              }
              const fullFeature: iCarbon_Features = this.state.features.find((feature: iCarbon_Features) => feature.feature_entity_id === featureId);
              obj.feature_code += ',' + fullFeature.feature_code;
              return obj
            })
          })));
  }

  addUserToGroup(userId: string, groupId: string) {
    const { axios } = this.props;
    const payload: iCarbon_User_Groups = {
      group_id: groupId,
      user_id: userId
    };
    this.setState({ alert: null }, () =>
      axios.post('/rest/' + C6.carbon_user_groups.TABLE_NAME,
        convertForRequestBody(payload, C6.carbon_user_groups.TABLE_NAME))
        .then(response =>
          (this.props.testRestfulPostResponse(response, null,
            'An unknown issue occurred. We will be looking into this shortly.')) &&
          this.setState({
            users: this.state.users.map(obj => {
              if (obj.user_id !== userId) {
                return obj;
              }
              const fullGroup: iGroupFeatures =
                this.state.groups.find((group: iGroupFeatures) => group.entity_id === groupId);

              obj.group_name += ',' + fullGroup.group_name;

              return obj;
            })
          })
        ));
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
                    onClick={() => swal({
                      buttons: true,
                      content: <div><h2>Create a new feature flag</h2><b>(Site Admin Only)</b><br/><br/>
                        Your New Feature Name:
                        <hr/>
                        <CustomInput
                          success
                          labelText="Feature Flag Name"
                          id="Feature_Flag_Name"
                          formControlProps={{
                            fullWidth: true
                          }}
                          inputProps={{
                            onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                              feature: {
                                ...this.state.feature,
                                feature_code: e.target.value
                              }
                            })
                          }}
                        />
                        <br/>
                        <hr/>
                      </div>
                    }).then(shouldSubmit => shouldSubmit && this.newFeature())}
                  >
                    Start New Feature
                  </Button>
                  <Button color="warning"
                          onClick={() =>
                            swal({
                              buttons: true,
                              content:
                                <div>Create a permission group <br/>
                                  Your New Group Name:
                                  <hr/>
                                  <CustomInput
                                    success
                                    labelText="Permissions Group Name"
                                    id="Permissions_Group_Name"
                                    formControlProps={{
                                      fullWidth: true
                                    }}
                                    inputProps={{
                                      onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                        group: {
                                          ...this.state.group,
                                          group_name: e.target.value
                                        }
                                      })
                                    }}
                                  />
                                  <br/>
                                  <hr/>
                                </div>
                            }).then(shouldSubmit => shouldSubmit && this.newGroup())
                          }>Create New Group</Button>
                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="success"
                    tableHead={["Group Name", ...featureCodes, "Admin"]}
                    tableData={
                      this.state.groups.map(group => {
                        const name = humanize(group.group_name);

                        return [
                          <p onClick={this.handleNatModalChange}>{name}</p>,
                          ...this.state.features.map(feature => {

                            const enabled = group.feature_code?.includes(',' + feature.feature_code + ',')
                              || group.feature_code?.startsWith(feature.feature_code + ',')
                              || group.feature_code?.includes(',' + feature.feature_code)
                              || group.feature_code === feature.feature_code;

                            return <Button color={enabled ? "success" : "default"}
                                           onClick={() => enabled ?
                                             this.deleteFeatureFromGroup(group.entity_id, feature.feature_entity_id) :
                                             this.addFeatureToGroup(feature.feature_entity_id, group.entity_id)}
                            >
                              {enabled ? " Enabled " : "Disabled"}
                            </Button>
                          }),

                          <Button onClick={() => swal(<GridContainer>
                              <GridItem xs={12} sm={12} md={12}>
                                <div>
                                  <h5><b>You will be affecting 9 users already assigned to this role.</b></h5>
                                  <br/>
                                  <p>Moving to the Grant Ability.... will allow this user to... create and manage
                                    other users...</p>
                                </div>
                                <Table
                                  tableHeaderColor="info"
                                  tableHead={["Group #", "Group Name", "Grantability Status"]}
                                  tableData={
                                    this.state.groups.map((SubGroup, key) => {

                                      let regex = new RegExp('(^|,)' + SubGroup.entity_id + ',?', 'g');

                                      let enabled = regex.test(group.allowed_to_grant_group_id);

                                      return [
                                        key,
                                        SubGroup.group_name,
                                        <Button color={enabled ? "success" : "default"}
                                                onClick={() => enabled ?
                                                  this.deleteGroupGrantabillity(group.entity_id, SubGroup.entity_id) :
                                                  this.newGroupGrantabillity(group.entity_id, SubGroup.entity_id)}>
                                          {enabled ? " Can Give Access " : "Can not Grant"}
                                        </Button>
                                      ]
                                    })
                                  }
                                />
                              </GridItem>
                            </GridContainer>
                          )} color="danger">Admin</Button>
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
                    onClick={() => swal({
                      buttons: true,
                      content:
                        <form>
                          <h2>Create a new user</h2><br/>
                          <hr/>
                          <CustomInput
                            success
                            labelText="First Name"
                            id="user_first_name"
                            formControlProps={{
                              fullWidth: true
                            }}
                            inputProps={{
                              onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                user: {
                                  ...this.state.user,
                                  user_first_name: e.target.value
                                }
                              })
                            }}
                          />
                          <CustomInput
                            success
                            labelText="Last Name"
                            id="user_last_name"
                            formControlProps={{
                              fullWidth: true
                            }}
                            inputProps={{
                              onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                user: {
                                  ...this.state.user,
                                  user_last_name: e.target.value
                                }
                              })
                            }}
                          />
                          <CustomInput
                            success
                            labelText="Username"
                            id="user_username"
                            formControlProps={{
                              fullWidth: true
                            }}
                            inputProps={{
                              onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                user: {
                                  ...this.state.user,
                                  user_username: e.target.value
                                }
                              })
                            }}
                          />
                          <CustomInput
                            success
                            labelText="Password"
                            id="user_password"
                            formControlProps={{
                              fullWidth: true
                            }}
                            inputProps={{
                              onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                user: {
                                  ...this.state.user,
                                  user_password: e.target.value
                                }
                              })
                            }}
                          />
                          <CustomInput
                            success
                            labelText="Email"
                            id="user_email"
                            formControlProps={{
                              fullWidth: true
                            }}
                            inputProps={{
                              onChange: (e: ChangeEvent<HTMLInputElement>) => this.setState({
                                user: {
                                  ...this.state.user,
                                  user_email: e.target.value
                                }
                              })
                            }}
                          />
                          <hr/>
                        </form>
                    }).then(shouldSubmit => shouldSubmit && this.newUser())}
                  >
                    Create New User
                  </Button>

                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="info"
                    tableHead={["User ID", "First Name", "Last Name", "Username/Session", ...this.state.groups.map(group => group.group_name)]}
                    tableData={
                      this.state.users.map((user, key) => [
                        user.user_id,
                        user.user_first_name,
                        user.user_last_name,
                        user.user_username,
                        ...this.state.groups.map(group => {

                          const enabled = user.group_name?.includes(',' + group.group_name + ',')
                            || user.group_name?.startsWith(group.group_name + ',')
                            || user.group_name?.includes(',' + group.group_name)
                            || user.group_name === group.group_name;

                          return <Button color={enabled ? "success" : "default"}
                                         onClick={() => enabled ?
                                           this.deleteGroupFromUser(user.user_id, group.entity_id) :
                                           this.addUserToGroup(user.user_id, group.entity_id)}
                          >
                            {enabled ? " Enabled " : "Disabled"}
                          </Button>
                        })
                      ])
                    }
                  />
                </CardBody>
              </Card>
            </GridItem>
          </GridContainer>
        </GridItem>
      </div>
    );
  }
}


export default withStyles(landingPageStyle)(AccessControl);
