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

        if (this.props.groupFields) {
            return (<div>
                {
                    this.props.groupFields.map((pdata, j) => {
                        return (<div id={j} key={j}>{pdata.field_id}</div>)
                    })
                }
            </div>)
        }
        return <Spinner animation="border" variant="primary" />;
    }
}

export default PatientDataTabBlock;