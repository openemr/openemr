import React from 'react';
import agent from "../../utils/agent";
import {Spinner} from "react-bootstrap";

class PatientDataTabBlock extends React.Component {
    constructor(props) {
        super(props);
        const groupId = props.groupId;
        this.state = {
            patientData: [],
            isLoaded: false
        }
    }

    render() {
        let html;
        if (this.props.groupFields.length > 0) {
            html = this.props.groupFields.map((pdata, j) => {
                return (<div id={j} key={j}>{pdata.field_id} - {this.props.data[0][pdata.field_id]}</div>)
            })

        } else {
            html = <Spinner animation="border" variant="primary"/>
        }


        return (
                <>{html}</>
               )
    }
}

export default PatientDataTabBlock;