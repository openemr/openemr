import React, {Component} from "react";
import helpers from '../utils/helpers.js';
import ToggleButton from './global/ToggleButton.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container} from 'react-bootstrap';

class PatientData extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            isOpen: false,
            isLoaded: false
        }
    }


    componentDidMount() {
        this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {
            fetch("../../../../apis/api/patient/extended/" + this.setState.patientId, {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        if (result != null && JSON.stringify(result)) {
                            this.setState({data: [result], isLoaded: true});
                        }
                    },
                    (error) => {
                        // this.setState({
                        //     isLoaded: true,
                        //     error
                        // });
                    }
                );
        } else {
            console.log("Patient not found");
        }
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }

    componentDidUpdate() {
        // console.log("Query:" + JSON.stringify(this.state.data));
        this.state.data.map((data, i) => {
            parent.left_nav.setPatient(data.fname + " " + data.lname, data.pid, data.pubpid, '', data.str_dob);
        });

    }

    render() {
        const {isOpen} = this.state;
        const {isLoaded} = this.state;
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

                                    <Tab.Container id="left-tabs-example" defaultActiveKey="tab-1">
                                        <Row>
                                            <Col sm={3}>
                                                <Nav variant="pills" className="flex-column">
                                                    <Nav.Item>
                                                        <Nav.Link eventKey="tab-1">Short Patient Form</Nav.Link>
                                                    </Nav.Item>
                                                    <Nav.Item>
                                                        <Nav.Link eventKey="tab-2">Contacts</Nav.Link>
                                                    </Nav.Item>
                                                    <Nav.Item>
                                                        <Nav.Link eventKey="tab-3">Employer</Nav.Link>
                                                    </Nav.Item>
                                                    <Nav.Item>
                                                        <Nav.Link eventKey="tab-4">Misc</Nav.Link>
                                                    </Nav.Item>
                                                    <Nav.Item>
                                                        <Nav.Link eventKey="tab-5">Insurance</Nav.Link>
                                                    </Nav.Item>
                                                </Nav>
                                            </Col>
                                            <Col sm={9}>
                                                <Tab.Content>
                                                    <Tab.Pane eventKey="tab-1">
                                                        {this.state.data.map((data, i) => {
                                                            return (
                                                                <div key={i}>
                                                                    <Container>
                                                                        <Row>
                                                                            <Col>First Name:</Col>
                                                                            <Col>{data.fname}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>Last Name:</Col>
                                                                            <Col>{data.lname}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>English full name:</Col>
                                                                            <Col>{data.title}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>Dob:</Col>
                                                                            <Col>{data.DOB}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>S.S.:</Col>
                                                                            <Col>{data.ss}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>Sex:</Col>
                                                                            <Col>{data.sex}</Col>
                                                                        </Row>
                                                                        <Row>
                                                                            <Col>Birth country:</Col>
                                                                            <Col>{data.county}</Col>
                                                                        </Row>
                                                                    </Container>
                                                                </div>
                                                            )
                                                        })}
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-2">
                                                        Contacts
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-3">
                                                        Employer
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-4">
                                                        Misc
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-5">
                                                        Insurance
                                                    </Tab.Pane>
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