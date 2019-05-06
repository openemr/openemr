import React from "react";
import agent from '../utils/agent.js';

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
            isOpen: false,
            isLoaded: false
        }
    }

    componentWillMount() {
        this.setState.patientId = helpers.getPatientId();
        var groupsList = Promise.all([agent.PatientDataAgent.groups(), agent.PatientDataAgent.patient(this.setState.patientId )]);
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
// console.log(this.state.data);
        var rightTextButton = "Patient Data";
        return (
            isLoaded &&
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
                                                            <Nav.Item key={j}>
                                                                <Nav.Link eventKey={jt}>{group.grp_title}</Nav.Link>
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
                                                            <Tab.Pane eventKey={it} key={i}>{group.grp_title}</Tab.Pane>
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