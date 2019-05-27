import React from "react";
import agent from '../utils/agent.js';
import PatientDataTabBlock from './PatientData/PatientDataTabBlock';
import PatientDataSummary from './PatientData/PatientDataSummary';

import helpers from '../utils/helpers.js';
import ToggleButton from './global/ToggleButton.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container} from 'react-bootstrap';

const Promise = global.Promise;

class PatientData extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            groups: [],
            groupFields: [],
            currentGroupId: 0,
            isOpen: false,
            isLoaded: false
        }
        this.clickSelect = this.clickSelect.bind(this);
    }

    componentDidMount() {
        var groupsList = Promise.all([agent.PatientDataAgent.groups("DEM"), agent.PatientDataAgent.patientExtend(this.props.pid)]);
        groupsList.then(([listGroups, patientData]) => {
            this.setState({groups: [listGroups][0], data: [patientData], isLoaded: true});
        });
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
        const {isLoaded} = this.state;
        const {currentGroupId} = this.state;
        const {isOpen} = this.state;
        if(isLoaded && !isOpen) {
             this.clickSelect(currentGroupId > 0 ? currentGroupId : this.props.settings['defaultGroupId']);
        }
    }


    clickSelect(groupId) {
        this.setState({
            groupFields: [],
            currentGroupId: groupId
        });

        Promise.all([agent.PatientDataAgent.byGroupId("DEM", groupId)]).then(
            result => {
                //result.map((a) => {
                //if(!this.state.groupFields){
                this.setState({
                    groupFields: result[0]
                });

                // }
                //});
            }
        );
    }

    componentDidUpdate() {
        this.state.data.map((data, i) => {
            parent.left_nav.setPatient(data.fname + " " + data.lname, data.pid, data.pubpid, '', data.str_dob);
        });

    }

    render() {
        const {isLoaded} = this.state;
        //TODO
        //Make a variable for save a last opened tab
        let defaultActiveTab = "tab-" + this.props.settings['defaultGroupId'];

        return (
            isLoaded && this.state.groups &&
            <div className="card" variant="dark">
                <div className="card-header">

                    <ToggleButton isOpen={this.state.isOpen}
                                  onClick={() => this.localToggle()}
                                  rightText={this.props.title}/>

                    <Collapse in={this.state.isOpen}>

                        <Card>
                            <Card.Body>
                                <div id="example-fade-text">
                                    <Tab.Container id="left-tabs-example" defaultActiveKey={defaultActiveTab} >
                                        <Row>
                                            <Col sm={3}>
                                                <Nav variant="pills" className="flex-column">
                                                    {this.state.groups.map((group, j) => {
                                                        var jt = "tab-" + group.grp_group_id;
                                                        return (
                                                            <Nav.Item key={j}
                                                                      onClick={() => this.clickSelect(group.grp_group_id)}>
                                                                <Nav.Link eventKey={jt}
                                                                          key={j}>{group.grp_title}</Nav.Link>
                                                            </Nav.Item>
                                                        )
                                                    })}
                                                </Nav>
                                            </Col>
                                            <Col sm={9}>
                                                <Tab.Content>
                                                    <PatientDataSummary groups={this.state.groups}
                                                                        groupFields={this.state.groupFields}
                                                                        data={this.state.data}/>
                                                </Tab.Content>
                                            </Col>
                                        </Row>
                                    </Tab.Container>

                                </div>

                            </Card.Body>
                        </Card>

                    </Collapse>
                </div>
            </div>
        )
    }
}


export default {
    view: (props) => (
        <PatientData pid={props.pid} title={props.element_title} settings={props.settings}/>
    )
};