import React, {Component} from "react";
import helpers from '../utils/helpers.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav} from 'react-bootstrap';

class PatientData extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            open: false
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
                        //this.setState.data = [result];
                        this.setState({data: [result]});
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

    componentDidUpdate() {
        this.state.data.map((data, i) => {
            parent.left_nav.setPatient(data.fname + " " + data.lname, data.pid, data.pubpid, '', data.str_dob);
        });

    }

    render() {
        const {open} = this.state;
        return (
            <div className="card" variant="dark" style={{width: '48rem'}}>
                <div className="card-header">
                    <Button
                        onClick={() => this.setState({open: !open})}
                        aria-controls="example-collapse-text"
                        aria-expanded={open}
                    > - </Button> Patient Data
                    <Collapse in={this.state.open}>
                        <Card style={{width: '45rem'}}>
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
                                                        <Table>
                                                            <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>PID</th>
                                                                <th>FName</th>
                                                                <th>LName</th>
                                                                <th>Title</th>
                                                                <th>Dob</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            {this.state.data.map((data, i) => {
                                                                return (
                                                                    <tr key={i}>
                                                                        <td align="center">{data.id}</td>
                                                                        <td align="center">{data.pid}</td>
                                                                        <td align="center">{data.fname}</td>
                                                                        <td align="center">{data.lname}</td>
                                                                        <td align="center">{data.title}</td>
                                                                        <td align="center">{data.dob}</td>
                                                                    </tr>
                                                                )
                                                            })}
                                                            </tbody>
                                                        </Table>
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-2">
                                                        qweqweqwe
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-3">
                                                        qweqweqwe
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-4">
                                                        qweqweqwe
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey="tab-5">
                                                        qweqweqwe
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

export default PatientData;