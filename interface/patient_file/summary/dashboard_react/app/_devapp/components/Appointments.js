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
import agent from "../utils/agent";
import {ListGroup} from "react-bootstrap";
class Appointments extends React.Component  {
    constructor(props) {
        debugger;
        super(props);
        this.state = {
            pid: props.pid,
            data: [],
            element:props.element,
            element_title:props.element_title,
            renderElement:false,
            isOpen:false
        }

    }
    selectAppointment(){
        switch(this.state.element){
            case "future":
                return "future_appointments";
                break;
            case "past":
                return "past_appointments";
                break;
            case "appointment_recurrences":
                return "appointment_recurrences"
                break;
        }
    }
    createAppointmentHtml(element,mp,i){
        //debugger
        let counter=i;
        if(mp.length==0)
            return  <></>
        switch(element){
            case "past":
            case "future":

                return <Card  bg="light" border="secondary" key={i}><Card.Body   >
                    When : {mp.pc_eventDate} {mp.pc_startTime} -   {mp.pc_endTime} <br/>
                    Appointment type :{mp.pc_title} <br/>
                    Therapist :{mp.ufname+" "+mp.ulname} <br/>
                    Status : {mp.pc_apptstatus} <br/>
                    Comments : {mp.pc_hometext}  <br/>

                     {/*{mp.facility_name}
                     {mp.billing_location_name}*/}

                </Card.Body>
                </Card>
            break;
            case "appointment_recurrences":
                return <Card bg="light"  border="secondary" variant="dark" key={i}>
                    <Card.Body  >
                <br/>
                When : {mp.pc_recurrspec} <br/>
                Appointment Type : {mp.pc_title} <br/>
                End date : {mp.pc_endDate}
                <br/>

                </Card.Body>
                </Card>
            break;
        }
    }
    componentWillMount() {

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

                renderElement && <>

                        <Card   >

                            <Card.Header>

                                <Row>
                                    <Col-md-10>
                                        <ToggleButton isOpen={this.state.isOpen}
                                                      onClick={() => this.localToggle()}
                                                      rightText={this.state.element_title  + " " + this.apptCount()}/>

                                    </Col-md-10>
                                    <Col>
                                      {/*  <div  onClick={() => this.goToMedicalIssue()} >
                                            <FontAwesomeIcon icon='edit'  className={"medical_issues"}  />
                                        </div>*/}

                                    </Col>
                                </Row>

                            </Card.Header>
                            <Collapse in={this.state.isOpen}>
                                <Card.Body>

                    {this.drawApp()}


                                </Card.Body>
                            </Collapse>
                        </Card>
                    </>



        );
    }
    apptCount = () => {
        return Array.isArray(this.state.data) ? this.state.data.length : null;
    }

    drawApp = () => {




       if(Array.isArray(this.state.data)) {
                return this.state.data.map((mp, i) => {
                    //console.log(mp);
                    return this.createAppointmentHtml(this.state.element,mp,i)
                })
            }
   }

    componentDidMount() {
        this.getAppointments();
    }
    getAppointments() {

////debugger;
        if (this.state.pid >= 0 && typeof this.state.element!="undefined") {
            let url=this.selectAppointment(this.state.element);

            OEApi.PatientApi.fetch(this.state.pid,url)
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
        <Appointments  pid={props.pid} element={props.type} element_title={props.element_title}/>
    )
};