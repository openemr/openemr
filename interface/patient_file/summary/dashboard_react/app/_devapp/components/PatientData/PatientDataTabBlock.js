import React from 'react';
import agent from "../../utils/agent";
import ReactPlaceholder from 'react-placeholder';
import "react-placeholder/lib/reactPlaceholder.css";

// https://github.com/buildo/react-placeholder - documentation for react placeholder

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
        return (
            <>
                <ReactPlaceholder type='text' showLoadingAnimation={true} rows={7}
                                  ready={this.props.groupFields.length > 0}>
                    {this.props.groupFields.map((pdata, j) => {
                        return (
                            <div id={j} key={j}>{pdata.field_id} - {this.props.data[0][pdata.field_id]}</div>
                        )
                    })}
                </ReactPlaceholder>
            </>
        )
    }
}

export default PatientDataTabBlock;