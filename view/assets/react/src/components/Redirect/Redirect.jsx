import React from "react";

export class Redirect extends React.Component {
  constructor(props) {
    super();
    this.state = { ...props };
  }
  componentDidMount() {
    // I could do a backwards walk through the array
    window.location = this.state.to;
  }
  render() {
    return <section>Redirecting...</section>;
  }
}

export default Redirect;
