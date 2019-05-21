import React from "react";
import OEApi from "../utils/api";
import PropTypes from 'prop-types';
import helpers from "../utils/helpers";
import Table from 'react-bootstrap/Table';
import Card from "react-bootstrap/Card";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import ToggleButton from "./global/ToggleButton";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import { library } from '@fortawesome/fontawesome-svg-core';
import { faEdit } from '@fortawesome/free-solid-svg-icons';

import Collapse from "react-bootstrap/Collapse";
class Appointments extends React.Component  {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            groups: [],
            groupFields: [],
            isOpen: false,
            isLoaded: false,
            renderElement:false,
            element_title:"Appointments"
        }

    }


    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }

            render(){
            const {isOpen} = this.state;
            const {isLoaded} = this.state;
            const {data} = this.state;
                const { open } = this.state;
                const { renderElement } = this.state;
                return(
                renderElement &&
                <Card   variant="flush">

                    <Card.Header>

                        <Row>
                            <Col>

                                <ToggleButton isOpen={this.state.isOpen}
                                              onClick={() => this.localToggle()}
                                              rightText={this.state.element_title  + " " + (this.apptCount())}/>

                            </Col>
                            <Col>
                                <div  onClick={() => this.goToMedicalIssue()} >
                                    <FontAwesomeIcon icon='edit'   className={"medical_issues"}  />
                                </div>

                            </Col>
                        </Row>

                    </Card.Header>
                    <Collapse in={this.state.isOpen}>
                        <Card.Body>

                            <div id="example-collapse-text">
                                <Table>
        <thead>
        <tr>
        <th>Event Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Facility Name</th>
        <th>Billing Location</th>
        </tr>
        </thead>
        <tbody>
        {this.drawApp()}
        </tbody>
    </Table>
                            </div>

                        </Card.Body>
                    </Collapse>
                </Card>

        );
    }
    apptCount = () => {
        return Array.isArray(this.state.data) ? this.state.data.length : null;
    }

    drawApp = () => {
        if (Array.isArray(this.state.data)) {
            return this.state.data.map((mp, i) => {
                return <tr key={i}>
                    <td>{mp.pc_eventDate}</td>
                    <td>{mp.pc_startTime}</td>
                    <td>{mp.pc_endTime}</td>
                   {/* <td>{mp.pc_facility}</td>
                    <td>{mp.pc_billing_location}</td>*/}
                    <td>{mp.facility_name}</td>
                    <td>{mp.billing_location_name}</td>
               </tr>
            })
        }
    }
    componentDidMount() {
        this.getAppointments();
    }
    getAppointments() {
        this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {

            OEApi.PatientApi.fetch(this.setState.patientId,"appointment")
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.setState({data: result});

                        if(result==null)
                        {
                            console.log("NO DATA WAS FETCHED");
                        }
                        else {
                            this.setState({renderElement: true})
                        }

                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });

                        console.log(error);
                    }
                )
        } else {
            console.log("Appointment not found");
        }
    }
}


export default {
    view: (props) => (
        <Appointments />
    )
};