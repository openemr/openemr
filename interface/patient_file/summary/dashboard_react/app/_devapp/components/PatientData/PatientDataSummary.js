import React from "react";
import {Nav, Tab} from "react-bootstrap";
import PatientDataTabBlock from "./PatientDataTabBlock";

class PatientDataSummary extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        let tabContents = this.props.groups.map((group, i) => {
            var it = "tab-" + i;
            return (
                <Tab.Pane eventKey={it} key={i}>
                    <PatientDataTabBlock groupId={group.grp_group_id} groupFields={this.props.groupFields}/>
                </Tab.Pane>
            )
        });

        return (
            <>
            {tabContents}
            </>
        );
    }
}

export default PatientDataSummary;