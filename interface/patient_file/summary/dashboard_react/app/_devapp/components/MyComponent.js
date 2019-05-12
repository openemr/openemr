
import React, { Component } from 'react';

import MedicalProblems from "./MedicalProblems";
import PatientData from "./PatientData";
import PatientMessages from "./PatientMessages";

class MyComponent extends Component {
    constructor(props) {
        super(props);
    }
    components = {
        PatientMessages: PatientMessages,
        PatientData: PatientData
    };
    render() {
        console.log(this.props)
        const TagName = this.components[this.props.tag.name];

        return <TagName key={this.props.tag.id}  pid={this.props.pid} />
    }
}
export default MyComponent;