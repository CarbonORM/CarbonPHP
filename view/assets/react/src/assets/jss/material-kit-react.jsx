/*!

 =========================================================
 * Material Kit React - v1.3.0 based on Material Kit - v2.0.2
 =========================================================

 * Product Page: https://www.creative-tim.com/product/material-kit-react
 * Copyright 2018 Creative Tim (https://www.creative-tim.com)
 * Licensed under MIT (https://github.com/creativetimofficial/material-kit-react/blob/master/LICENSE.md)

 =========================================================

 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

 */

// ##############################
// // // Variables - Styles that are used on more than one component
// #############################

export const drawerWidth = 260;

export const transition = {
  transition: "all 0.33s cubic-bezier(0.685, 0.0473, 0.346, 1)"
};

export const conatinerFluid = {
  paddingRight: "15px",
  paddingLeft: "15px",
  marginRight: "auto",
  marginLeft: "auto",
  width: "100%",
};
export const container = {
  ...conatinerFluid,
  "@media (min-width: 576px)": {
    maxWidth: "540px"
  },
  "@media (min-width: 768px)": {
    maxWidth: "720px"
  },
  "@media (min-width: 992px)": {
    maxWidth: "960px"
  },
  "@media (min-width: 1200px)": {
    maxWidth: "1140px"
  }
};
export const largeContainer = {
  ...conatinerFluid,
  "@media (min-width: 576px)": {
    maxWidth: "540px"
  },
  "@media (min-width: 768px)": {
    maxWidth: "720px"
  },
  "@media (min-width: 992px)": {
    maxWidth: "960px"
  },
  "@media (min-width: 1200px)": {
    maxWidth: "1140px"
  },
  "@media (min-width: 1300px)": {
    maxWidth: "1242px"
  },
  "@media (min-width: 1400px)": {
    maxWidth: "1342px"
  },
  "@media (min-width: 1500px)": {
    maxWidth: "1442px"
  },
  "@media (min-width: 1600px)": {
    maxWidth: "1542px"
  },
};


export const mlAuto = {
  marginLeft: "auto"
};

export const mrAuto = {
  marginRight: "auto"
};

export const boxShadow = {
  boxShadow:
    "0 10px 30px -12px rgba(0, 0, 0, 0.42), 0 4px 25px 0px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2)"
};

export const card = {
  display: "inline-block",
  position: "relative",
  width: "100%",
  margin: "25px 0",
  boxShadow: "0 1px 4px 0 rgba(0, 0, 0, 0.14)",
  borderRadius: "3px",
  color: "rgb(188,201,218)",
  background: "#fff"
};

export const defaultFont = {
  fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
  fontWeight: 300,
  lineHeight: "1.5em"
};

export const primaryColor = "#9c27b0";
export const warningColor = "#ff9800";
export const dangerColor = "#f44336";
export const successColor = "#4caf50";
export const infoColor = "#00acc1";
export const roseColor = "#e91e63";
export const grayColor = "#999999";
export const whiteColor = "#FFFFFF";


export const primaryBoxShadow = {
  boxShadow:
    "0 12px 20px -10px rgba(156, 39, 176, 0.28), 0 4px 20px 0px rgba(0, 0, 0, 0.12), 0 7px 8px -5px rgba(156, 39, 176, 0.2)"
};
export const infoBoxShadow = {
  boxShadow:
    "0 12px 20px -10px rgba(0, 188, 212, 0.28), 0 4px 20px 0px rgba(0, 0, 0, 0.12), 0 7px 8px -5px rgba(0, 188, 212, 0.2)"
};
export const successBoxShadow = {
  boxShadow:
    "0 12px 20px -10px rgba(76, 175, 80, 0.28), 0 4px 20px 0px rgba(0, 0, 0, 0.12), 0 7px 8px -5px rgba(76, 175, 80, 0.2)"
};
export const warningBoxShadow = {
  boxShadow:
    "0 12px 20px -10px rgba(255, 152, 0, 0.28), 0 4px 20px 0px rgba(0, 0, 0, 0.12), 0 7px 8px -5px rgba(255, 152, 0, 0.2)"
};
export const dangerBoxShadow = {
  boxShadow:
    "0 12px 20px -10px rgba(244, 67, 54, 0.28), 0 4px 20px 0px rgba(0, 0, 0, 0.12), 0 7px 8px -5px rgba(244, 67, 54, 0.2)"
};
export const roseBoxShadow = {
  boxShadow:
    "0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(233, 30, 99, 0.4)"
};

export const warningCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #ffa726, #fb8c00)",
  ...warningBoxShadow
};
export const successCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #66bb6a, #43a047)",
  ...successBoxShadow
};
export const dangerCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #ef5350, #e53935)",
  ...dangerBoxShadow
};
export const infoCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #26c6da, #00acc1)",
  ...infoBoxShadow
};
export const primaryCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #ab47bc, #8e24aa)",
  ...primaryBoxShadow
};
export const roseCardHeader = {
  color: "#fff",
  background: "linear-gradient(60deg, #ec407a, #d81b60)",
  ...roseBoxShadow
};
export const cardActions = {
  margin: "0 20px 10px",
  paddingTop: "10px",
  borderTop: "1px solid #eeeeee",
  height: "auto",
  ...defaultFont
};

export const cardHeader = {
  margin: "-30px 15px 0",
  borderRadius: "3px",
  padding: "15px"
};

export const defaultBoxShadow = {
  border: "0",
  borderRadius: "3px",
  boxShadow:
    "0 10px 20px -12px rgba(0, 0, 0, 0.42), 0 3px 20px 0px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2)",
  padding: "10px 0",
  transition: "all 150ms ease 0s"
};

export const title = {
  color: "rgb(188,201,218)",
  margin: "1.75rem 0 0.875rem",
  textDecoration: "none",
  fontWeight: 700,
  fontFamily: `"Roboto Slab", "Times New Roman", serif`
};

export const cardTitle = {
  ...title,
  marginTop: ".625rem"
};

export const cardLink = {
  "& + $cardLink": {
    marginLeft: "1.25rem"
  }
};

export const cardSubtitle = {
  marginBottom: "0",
  marginTop: "-.375rem"
};


export const textCenter = {
  textAlign: "center"
};



export const section = {
  backgroundPosition: "50%",
  backgroundSize: "cover"
};

export const sectionDark = {
  backgroundColor: "#343434",
  background: "radial-gradient(ellipse at center,#585858 0,#232323 100%)"
};

export const sectionDescription = {
  marginTop: "130px"
};

export const description = {
  color: "#999"
};
