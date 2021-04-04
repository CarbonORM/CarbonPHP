import React from "react";

// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";

// @material-ui/icons

// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import Small from "components/Typography/Small.jsx";
import Danger from "components/Typography/Danger.jsx";
import Warning from "components/Typography/Warning.jsx";
import Success from "components/Typography/Success.jsx";
import Info from "components/Typography/Info.jsx";
import Primary from "components/Typography/Primary.jsx";
import Muted from "components/Typography/Muted.jsx";
import Quote from "components/Typography/Quote.jsx";
import typographyStyle from "assets/jss/material-kit-react/views/componentsSections/typographyStyle.jsx";
import {Link} from "react-router-dom";


class Changelog extends React.Component {
  render() {
    const { classes } = this.props;
    return (
      <div className={classes.section}>
        <div className={classes.container}>
          <div id="typography">
            <div className={classes.title}>
              <h2>Changelog </h2>
              <Small>The release names that follow are arbitrary</Small>
            </div>
            <GridContainer>
              <div className={classes.typo}>
                <div className={classes.note}><Info>8.2.^</Info></div>
                <h1 className={classes.title}><info>Razor</info></h1>
                <p>
                  <Danger>Major breaking change: by default MySQL will be started with the
                    PDO::MYSQL_ATTR_FOUND_ROWS flag. </Danger> This is in response to an preformance request
                    in which the generated Put(...) restful method requests would fail if the updated data matched
                    that of the data on the server.
                  <br/>
                  From the PHP online manual::<br/>
                  <Link to={"https://www.php.net/manual/en/ref.pdo-mysql.php"}>
                    Return the number of found (matched) rows, not the number of changed rows. </Link>
                  <br/><br/>
                  <Warning>Transations are now
                    handled completely through PDO(MySQL) and will have no additional abstration in C6. Commiting
                    to the database is determined by a boolean static variable in database class. This is set to
                    true by default, thus table operations in REST will commit automattically before return.
                  </Warning>
                  <br/>
                  Fixed issues with primary keys equal to '0'.

                  <Success>Updated rest to set AUTOINCREMENT=0. This will result in less merge conflicts.</Success>

                </p>
              </div>
              <div className={classes.typo}>
                <div className={classes.note}><Info>8.1.^</Info></div>
                <h1 className={classes.title}><info>Bolder</info></h1>
                <p>
                  <Warning>Minor bug fix with set_error_handler found.</Warning>
                  <br/>
                  <Success>ErrorCatcher now comes with a static stop method which will allow developers using C6 with
                    other error cathers to safly update and revert its own handler.</Success>
                  <br/>
                  Rest bug for Tinyint was fixed. Important tests add to Rest.
                  As it's features become more verbose to handle multiple MySQL queries test will be given a high
                  priority.
                </p>
              </div>
              <div className={classes.typo}>
                <div className={classes.note}><Info>8.0.^</Info></div>
                <h1 className={classes.title}><info>Rock</info></h1>
                <p>
                  <Danger>Version 8.0 brings major breaking changes to our rest validation logic.</Danger>
                  Validations across tables are preserved in join statements. Aggrogate Logic was
                  extended to work with subQueries. PHPDoc's reflect all changes made. Auto Increment
                  Primary Keys now return like the Binary counterpart. CLI handeling now prettier when
                  passing through to another program.
                  IP resolution bug was fixed.
                  The releases minor version bumps brought updates and additions to our Rest Tests.
                  ColorCode now has constants to help with IDE optional suggestions.
                </p>
              </div>
            </GridContainer>
          </div>
          <div className={classes.space50} />
        </div>
      </div>
    );
  }
}

export default withStyles(typographyStyle)(Changelog);
