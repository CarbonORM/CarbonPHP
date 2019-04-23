import React from "react";
// react plugin for creating date-time-picker
import Datetime from "react-datetime";
// @material-ui/core components
import withStyles from "@material-ui/core/styles/withStyles";
import Slide from "@material-ui/core/Slide";
import IconButton from "@material-ui/core/IconButton";
import Dialog from "@material-ui/core/Dialog";
import DialogTitle from "@material-ui/core/DialogTitle";
import DialogContent from "@material-ui/core/DialogContent";
import DialogActions from "@material-ui/core/DialogActions";
import InputLabel from "@material-ui/core/InputLabel";
import FormControl from "@material-ui/core/FormControl";
import Tooltip from "@material-ui/core/Tooltip";
import Popover from "@material-ui/core/Popover";
// @material-ui/icons
import LibraryBooks from "@material-ui/icons/LibraryBooks";
import Close from "@material-ui/icons/Close";
// core components
import GridContainer from "components/Grid/GridContainer.jsx";
import GridItem from "components/Grid/GridItem.jsx";
import Button from "components/CustomButtons/Button.jsx";
import javascriptStyles from "assets/jss/material-kit-react/views/componentsSections/javascriptStyles.jsx";


import renderHTML from 'react-render-html';


const codeSnippit = "<span style=\"overflow-x: scroll\"> <span\n" +
    "                                    style=\"color: #0000BB\">                 <br/></span><span style=\"color: #007700\">return [<br/>    </span><span\n" +
    "                                    style=\"color: #DD0000\">'DATABASE' </span><span style=\"color: #007700\">=> [<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'DB_HOST' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #DD0000\">'127.0.0.1'</span><span\n" +
    "                                    style=\"color: #007700\">,                        </span><span style=\"color: #FF8000\">/* IP */<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'DB_NAME' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #DD0000\">'CarbonPHP'</span><span\n" +
    "                                    style=\"color: #007700\">,                        </span><span style=\"color: #FF8000\">/* Schema */<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'DB_USER' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #DD0000\">'root'</span><span\n" +
    "                                    style=\"color: #007700\">,                        </span><span style=\"color: #FF8000\">/* User*/\n" +
    "                                    <br/><br/>        </span><span style=\"color: #DD0000\">'DB_PASS' </span><span\n" +
    "                                    style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #DD0000\">'Huskies!99'</span><span\n" +
    "                                    style=\"color: #007700\">,                        </span><span style=\"color: #FF8000\">/* Password*/<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'DB_BUILD' </span><span\n" +
    "                                    style=\"color: #007700\">=> </span><span style=\"color: #DD0000\">''</span><span\n" +
    "                                    style=\"color: #007700\">,                       </span><span style=\"color: #FF8000\">/* This framework sets up its-self implicitly */<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'REBUILD' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #0000BB\">false                      </span><span\n" +
    "                                    style=\"color: #FF8000\">/* Initial Setup todo - remove this check*/<br/>    </span><span\n" +
    "                                    style=\"color: #007700\">],<br/><br/>    </span><span\n" +
    "                                    style=\"color: #DD0000\">'SITE' </span><span style=\"color: #007700\">=> [<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'URL' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #DD0000\">'carbonphp.com'</span><span\n" +
    "                                    style=\"color: #007700\">,    </span><span style=\"color: #FF8000\">/* Evaluated and if not the accurate Redirect. Local php server okay. Remove for any domain */<br/><br/>        </span><span\n" +
    "                                    style=\"color: #DD0000\">'ROOT' </span><span style=\"color: #007700\">=> </span><span\n" +
    "                                    style=\"color: #0000BB\">APP_ROOT</span><span\n" +
    "                                    style=\"color: #007700\">,          </span><span style=\"color: #FF8000\">/* This was defined in our ../index.php */<br/></span>\n" +
    "\n" +
    "</span>";



function Transition(props) {
    return <Slide direction="down" {...props} />;
}


class SectionJavascript extends React.Component {
    anchorElLeft = null;
    anchorElTop = null;
    anchorElBottom = null;
    anchorElRight = null;

    constructor(props) {
        super(props);
        this.state = {
            classicModal: false,
            openLeft: false,
            openTop: false,
            openBottom: false,
            openRight: false
        };
    }

    handleClickOpen(modal) {
        var x = [];
        x[modal] = true;
        this.setState(x);
    }

    handleClose(modal) {
        var x = [];
        x[modal] = false;
        this.setState(x);
    }

    handleClosePopover(state) {
        this.setState({
            [state]: false
        });
    }

    handleClickButton(state) {
        this.setState({
            [state]: true
        });
    }

    render() {
        const {classes} = this.props;
        return (
            <div className={classes.section}>
                <div className={classes.container}>
                    <div className={classes.title}>
                        <h2>C6 Configuration Options</h2>
                    </div>
                    <GridContainer>
                        <GridItem xs={12} sm={12} md={8}>
                            <div className={classes.title}>
                                <h3>File Structure</h3>
                            </div>
                            <p>
                                {renderHTML(codeSnippit)}
                            </p>
                        </GridItem>
                    </GridContainer>
                </div>
            </div>
        );
    }
}

export default withStyles(javascriptStyles)(SectionJavascript);
