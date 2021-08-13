import React from "react";
// react components for routing our app without refresh
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";


import Button from "components/CustomButtons/Button.jsx";
import exampleStyle from "assets/jss/material-kit-react/views/componentsSections/exampleStyle.jsx";

import landing from "assets/img/Carbon-teal.png";
import profile from "assets/img/Carbon-green.png";
import {WithStyles} from "@material-ui/core/styles";
import {AxiosInstance} from "axios";
import {CODE_EXAMPLES} from "Code"

const gitCloneBlock = `git clone https://github.com/RichardTMiles/CarbonPHP.git
php -r \\"copy('https://getcomposer.org/installer', 'composer-setup.php');\\"
php composer-setup.php
php -r \\"unlink('composer-setup.php');\\"
mv composer.phar /usr/local/bin/composer
composer install`;

const CONFIG = `'DEPLOYMENT': [
            'Domain' => 'carbonphp.com',
            'Composer' => true,
            'Repository' => 'https://github.com/richardtmiles/carbonphp.com',
            'Username' => 'LqKM581y7EQwfJ9m',                                       // github
            'Password' => 'N99s67ugBFD5dJgB',
            'Subdomains' => [
                'www' => [
                    'Username' => 'wqQMDuQ7wWtLaBv1',                               // google domains
                    'Password' => 'h5G38jJHBpIwmJsN'
                ]
            ],
        ],`;

// @material-ui/icons
interface iCarbonPHP extends WithStyles<typeof exampleStyle> {
  axios: AxiosInstance;
  testRestfulPostPutDeleteResponse: Function;
  codeBlock: (markdown: String, highlight ?: String, language ?: String, dark ?: boolean) => any;
}


class Implementations extends React.Component<iCarbonPHP, {
  showCode: boolean
}> {
  constructor(props) {
    super(props);
    this.state = {
      showCode: false
    };
  }

  render() {
    const { classes } = this.props;
    return <GridContainer style={{ paddingTop: '20px' }} justify="center">
      <GridItem xs={12} sm={12} md={6}>
        <a href="https://github.com/RichardTMiles/CarbonPHP">
          <img
            src={landing}
            alt="..."
            className={
              classes.imgRaised +
              " " +
              classes.imgRounded +
              " " +
              classes.imgFluid
            }
          />
        </a>
        <Button color="primary" size="lg" href="https://github.com/RichardTMiles/CarbonPHP"
                target="_blank"
                className={classes.link} simple>
          CarbonPHP [C6]
        </Button>
      </GridItem>
      <GridItem xs={12} sm={12} md={6}>
        <a href={"https://github.com/RichardTMiles/Stats.Coach"} className={classes.link}>
          <img
            src={profile}
            alt="..."
            className={
              classes.imgRaised +
              " " +
              classes.imgRounded +
              " " +
              classes.imgFluid
            }
          />
          <Button color="primary" size="lg" href="https://github.com/RichardTMiles/Stats.Coach"
                  target="_blank"
                  simple>
            Stats Coach
          </Button>
        </a>
      </GridItem>
      <GridItem xs={12} sm={12} md={8}>
        <h2>Quick Start</h2>
        <p>Clone this repository and run this website locally.</p><br/>
        {this.props.codeBlock(gitCloneBlock)}
        <br/>
        <p>Add CarbonPHP to an existing composer enabled project.</p><br/>
        {this.props.codeBlock('composer require “richardtmiles/carbonphp:6.3.3"')}

        <h3>Deploy to the Cloud</h3>
        <p className="lead">
          <p>Linux users may find this script useful for deploying to
            <a href="https://cloud.google.com/sdk/docs/quickstart" target="_blank" rel="noopener noreferrer" >Google Compute Engine</a>. The script is a bash shell
            script which uses <b>#!/usr/bin/env bash</b> as the shebang.</p>
          {this.props.codeBlock('./src/programs/gcpDeployment.sh')}
          It has the following three
          command line options.
          <ul>
            <li>-deploy instance_name (optional)</li>
            <li>-delete instance_name (optional)</li>
            <li>-ubuntu</li>
          </ul>
          <small>*The Ubuntu command is apart of a command chain and is run during the <b>-deploy</b> flag. For this
            reason it is not listed in the programs help menu.</small>
          <br/>
          <br/>
          <h5>Zero to One Hundred</h5>
          <p>This deployment process will completely start and setup a dedicated server for this repository.</p>
          <ol>
            <li>Updates apt</li>
            <li>Installs Apache2</li>
            <li>Installs wget, curl, composer, npm, php, python</li>
            <li>Installs certbot-auto</li>
            <li>Installs Repository Dependencies</li>
            <li>Configures Apache (websockets and h2)</li>
            <li>Configures Google DNS server to point to new server IP</li>
            <li>Installs SSL certificates (free and trusted)</li>
          </ol>
        </p>
        <small>*this will not run out-of-the-box. It would not be a good idea for the public to be able to change my DNS
          records.
          For this reason, your build will fail as the keys are invalid. I plan for
          version 8 of C6 to modularize this process to be used with any repository.</small>
        <br/>
        <br/>
        <p>The following configuration must be available to successfully deploy to gcp.</p>
        {this.props.codeBlock(CONFIG)}
        <br/><br/>
        <br/><br/>
        <Button round color={this.state.showCode ? 'success' : 'info'} onClick={() => this.setState({
          showCode: !this.state.showCode
        })}>
          {!this.state.showCode ? 'Show Google Compute Engine Deployment Script' : 'Hide Code'}
        </Button>
        <br/><br/>
        {this.state.showCode ? this.props.codeBlock(CODE_EXAMPLES.gcpDeployment) : ''}
      </GridItem>
    </GridContainer>

  }
}

// @ts-ignore
export default withStyles(exampleStyle)(Implementations);