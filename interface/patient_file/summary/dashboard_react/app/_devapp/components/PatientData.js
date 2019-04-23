import React, {Component} from "react";
import helpers from '../utils/helpers.js';
import Dashboard from "./Dashboard";
import Button from "react-bootstrap/Button";
import Table from "react-bootstrap/Table";
import Card from "react-bootstrap/Card";
import Collapse from "react-bootstrap/Collapse";
import Tab from "react-bootstrap/Tab";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import Nav from "react-bootstrap/Nav";


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
        const divStyle = {
            // border: '1px solid gray',
            display: 'block',

        };
        const { open } = this.state;

        let data = this.setState.data;
        return (
            <Card style={{ width: '48rem' }}>
                <Card.Body>
                    <Card.Title>
                        <Button
                            onClick={() => this.setState({open: !open})}
                            aria-controls="example-collapse-text"
                            aria-expanded={open}
                        > - </Button> Patient Data
                    </Card.Title>

                        <Collapse in={this.state.open}>
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
                        </Collapse>

                </Card.Body>
            </Card>

        )
    }
}

export default PatientData;