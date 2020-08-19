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
import { conatinerFluid } from "../../../material-kit-react";

import imagesStyle from "../../imagesStyles";
import { createStyles } from "@material-ui/core";

const exampleStyle = createStyles({
  section: {
    padding: "70px 0"
  },
  container: {
    ...conatinerFluid,
    textAlign: "center"
  },
  ...imagesStyle,
  link: {
    textDecoration: "none"
  }
});

export default exampleStyle;
