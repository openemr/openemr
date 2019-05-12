import React from "react";
import agent from '../utils/agent.js';
import PatientDataTabBlock from './PatientData/PatientDataTabBlock';

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
            isOpen: false,
            isLoaded: false
        }
        this.clickSelect = this.clickSelect.bind(this);
    }

    componentWillMount() {
        this.setState.patientId = helpers.getPatientId();
        var groupsList = Promise.all([agent.PatientDataAgent.groups("DEM"), agent.PatientDataAgent.patient(this.setState.patientId )]);
        groupsList.then( ([listGroups, patientData]) => {
            this.setState({ groups: [listGroups][0], data: [patientData], isLoaded: true });
        });
        // console.log("My name is: " + this.name + ": 1");
    }

    componentDidMount() {
        // console.log("My name is: " + this.name + ": 2");
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }


    clickSelect(groupId) {
            Promise.all([agent.PatientDataAgent.byGroupId("DEM", groupId)]).then(
                result => {
                    result.map((a) => {

                        if(!this.state.groupFields[groupId]){
                            this.setState({
                                groupFields: {
                                    [groupId] : a
                                }
                            });

                        }

                    });
                    // console.log(result);
                }
            );
    }

    componentDidUpdate() {
        // console.log("My name is: " + this.name + ": 3");
        // console.log("Query:" + JSON.stringify(this.state.data));
        this.state.data.map((data, i) => {
            parent.left_nav.setPatient(data.fname + " " + data.lname, data.pid, data.pubpid, '', data.str_dob);
        });

    }

    render() {
        // console.log("My name is: " + this.name + ": 4");
        const {isOpen} = this.state;
        const {isLoaded} = this.state;
        const {data} = this.state;
        var rightTextButton = "Patient Data";
        // console.log("AA: ");
        // console.log(this.state.groupFields);
        // console.log("BB: ");
        return (
            isLoaded && this.state.groups &&
            <div className="card" variant="dark">
                <div className="card-header">

                    <ToggleButton isOpen={this.state.isOpen}
                                    onClick={() => this.localToggle()}
                                    rightText={rightTextButton}/>

                    <Collapse in={this.state.isOpen}>

                        <Card>
                            <Card.Body>
                                <div id="example-fade-text">
                                    <Tab.Container id="left-tabs-example" defaultActiveKey="tab-0">
                                        <Row>
                                            <Col sm={3}>
                                                <Nav variant="pills" className="flex-column">
                                                    {this.state.groups.map((group, j) => {
                                                        var jt = "tab-" + j;
                                                            return (
                                                            <Nav.Item key={j} onClick={()=>this.clickSelect(group.grp_group_id)} >
                                                                <Nav.Link eventKey={jt} key={j} >{group.grp_title}</Nav.Link>
                                                            </Nav.Item>
                                                            )
                                                        })}
                                                </Nav>
                                            </Col>
                                            <Col sm={9}>
                                                <Tab.Content>
                                                    {this.state.groups.map((group, i) => {
                                                        var it = "tab-" + i;
                                                        return (
                                                            <Tab.Pane eventKey={it} key={i}  >
                                                                <PatientDataTabBlock groupId={group.grp_group_id} groups={this.state.groupFields} />
                                                            </Tab.Pane>
                                                        )
                                                    })}
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
    view: () => (
        <PatientData/>
    )
};