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
import withStyles from "@material-ui/core/styles/withStyles";
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
import {C6, iCarbon_Features, iCarbon_Users} from "variables/C6";

// @ts-ignore
interface ILandingPage extends WithStyles<typeof landingPageStyle> {
  axios: AxiosInstance
}

class AccessControl extends React.Component<ILandingPage, {
  currency: 'USD' | 'GBP' | 'EUR' | 'PLN',
  grantRolesModalOpen: boolean,
  createRolesAndAssignModalOpen: boolean,
  users ?: Array<iCarbon_Users>,
  features ?: Array<iCarbon_Features>
}> {
  constructor(props) {
    super(props);
    this.state = {
      currency: 'EUR',
      grantRolesModalOpen: false,
      createRolesAndAssignModalOpen: true,
      users: []
    };
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

  componentDidMount(){
    const {axios} = this.props;

    axios.get('/rest/' + C6.carbon_users.TABLE_NAME + '/', {
      params: {
        [C6.SELECT]: [
          C6.carbon_users.USER_FIRST_NAME,
          C6.carbon_users.USER_LAST_NAME,
          C6.carbon_users.USER_ID,
        ],
        [C6.PAGINATION]: {
          [C6.LIMIT]: 10
        }
      }
    }).then(response => {
        this.setState({
          users: response.data.rest
        })
      })

    axios.get('/rest/' + C6.carbon_features.TABLE_NAME + '/', {
      params: {
        [C6.PAGINATION]: {
          [C6.LIMIT]: 10
        }
      }
    }).then(response => {
      this.setState({
        features: response.data.rest
      })
    })
  }


  newFeature(featureCode : string){
    const {axios} = this.props;
    axios.post('/rest/' + C6.carbon_features.TABLE_NAME + '/', {
        [C6.carbon_features.FEATURE_CODE]: featureCode,
    }).then(response => {
      console.log(
        response.data.rest
      )
    })
  }








  render() {
    const { classes } = this.props;

    return (
      <div>

        <GridItem lg={12} md={12} sm={12}>
          <GridContainer>
            <GridItem xs={12} sm={12} md={12}>
              <Card className={classes.whiteOpacity}>
                <CardHeader color="success">
                  <h4 className={classes.cardTitleWhite}>Assignable Roles Table</h4>
                  <p className={classes.cardCategoryWhite}>
                    The roles created here can be assigned to users you manage
                  </p>
                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="success"
                    tableHead={["Role Name", "Create Order", "Submit Order", "Create New Users", "Can Grant Roles"]}
                    tableData={[
                      [<a onClick={this.handleNatModalChange}>Accept Users</a>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button onClick={this.handleModalChange} color="warning">Granted</Button>],
                      [<a onClick={this.handleNatModalChange}></a>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button onClick={this.handleModalChange}
                                color="warning">Grant</Button>],
                      [<a onClick={this.handleNatModalChange}>Some Role A</a>,
                        <Button color="info">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="info">Granted</Button>,
                        <Button onClick={this.handleModalChange}
                                color="warning">Grant</Button>],
                      [<a onClick={this.handleNatModalChange}>Some Role A</a>,
                        <Button color="info">Granted</Button>,
                        <Button color="info">Granted</Button>,
                        <Button color="info">Granted</Button>,
                        <Button onClick={this.handleModalChange}
                                color="warning">Grant</Button>],
                      [<a onClick={this.handleNatModalChange}>Some Role A</a>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button onClick={this.handleModalChange}
                                color="warning">Granted</Button>],
                      [<a onClick={this.handleNatModalChange}>Some Role A</a>,
                        <Button color="success">Assign</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Assign</Button>,
                        <Button onClick={this.handleModalChange} color="warning">Grant</Button>]
                    ]}
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
                </CardHeader>
                <CardBody>
                  <Table
                    tableHeaderColor="info"
                    tableHead={["User ID", "User Name", "Some Role A", "Some Role B", "Some Role C"]}
                    tableData={[
                      ["1", "Dakota Rice", <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>],
                      ["2", "Minerva Hooper", <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>],
                      ["3", "Sage Rodriguez", <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>,
                        <Button color="success">Granted</Button>],
                    ]}
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
                          ["Role 2 \< insert Buzz Word Here \>",
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
