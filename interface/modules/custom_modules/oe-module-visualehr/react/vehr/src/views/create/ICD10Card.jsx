import React, { Component } from "react";
class Card extends Component {
  constructor(props) {
    super(props);

    this.state = {
      isSelected: false,
    };
  }
  componentDidMount() {
    this.setState({
      isSelected: this.props.icd_10_selected.includes(this.props.icd),
    });
  }
  handleICDSelected = (data) => {
    this.setState({ isSelected: !this.state.isSelected });
    this.props.handleICDSelected(data);
  };
  render() {
    return (
      <>
        <div
          onClick={() => {
            if (this.props.isProblem) {
              if (this.props.disabled) {
                this.handleICDSelected(this.props.icd);
              }
            } else {
              this.handleICDSelected(this.props.icd);
            }
          }}
          className={`flex w-full bg-gray-200 cursor-pointer border ${
            !this.props.icd_10_selected.includes(this.props.icd)
              ? "border-black text-black hover:bg-gray-300"
              : "border-white text-white bg-gray-700"
          } italic rounded-lg px-1 grid grid-cols-2 my-1`}
        >
          <div>{this.props.data.formatted_dx_code}</div>
          <div>{this.props.data.short_desc}</div>
        </div>
      </>
    );
  }
}

export default Card;
