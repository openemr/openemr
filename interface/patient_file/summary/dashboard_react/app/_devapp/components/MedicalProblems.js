import React from "react";

import Table from 'react-bootstrap/Table';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Collapse from 'react-bootstrap/Collapse';
import helpers from '../utils/helpers.js';
import Col from 'react-bootstrap/Col';
import Row from 'react-bootstrap/Row';
import ToggleButton from "./global/ToggleButton";

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEdit } from '@fortawesome/free-solid-svg-icons'
library.add( faEdit )



class MedicalProblems extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            element:props.element,
            element_title:props.element_title,
            renderElement:false,
            isOpen:false
        }
    }
    setElement(  element,element_title){
        this.setState.element=element;
        this.setState.element_title=element_title;
    }
    getMedicalProblems() {
        this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {
            let urlToFetch = ""
            switch (this.state.element)
            {
                case "Allergies":
                    urlToFetch="allergy";
                    break;
                case "MedicalProblems":
                    urlToFetch="medical_problem";
                    break;
                case "Medications":
                    urlToFetch="medication";
                    break;
            }

            fetch("../../../../apis/api/patient/" + this.setState.patientId+"/"+urlToFetch, {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
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
            console.log("Medical Problem not found");
        }
    }

    componentWillMount() {

        this.getMedicalProblems();
    }

    medProb = () => {
        if (Array.isArray(this.state.data)) {
            return this.state.data.map((mp, i) => {
                return <tr key={i}>
                    <td>{mp.title}</td>
                    {this.state.element=="Allergies"? <td>{mp.reaction}</td> :<td></td>}
                </tr>
            })
        } else {
            return <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        }
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }

    medProbCount = () => {
        return Array.isArray(this.state.data) ? this.state.data.length : null;
    }

    goToMedicalIssue = () => {
      /*  debugger;*/
        if(typeof(this.state.element)!="undefined") {

            let urlToFetch = "";
            switch (this.state.element) {
                case "Allergies":
                    urlToFetch = "allergy";
                    break;
                case "MedicalProblems":
                    urlToFetch = "medical_problem";
                    break;
                case "Medications":
                    urlToFetch = "medication";
                    break;
            }

            window.location.href="../../../patient_file/summary/stats_full.php?active=all&category="+urlToFetch;
        }
    }

    render() {
        const { open } = this.state;
        const { renderElement } = this.state;

        return(
                    renderElement &&
                    <Card   variant="flush">

                    <Card.Header>

                        <Row>
                            <Col>
                    {/*<Button   onClick={() => {this.setState({ open: !open }  ); }}  aria-controls="example-collapse-text" aria-expanded={open} >*/}
                    {/*    {!open ?  <FontAwesomeIcon icon='plus'/> :  <FontAwesomeIcon icon='minus'/>    }*/}

                    {/*</Button> {this.state.element_title}*/}

                                <ToggleButton isOpen={this.state.isOpen}
                                              onClick={() => this.localToggle()}
                                              rightText={this.state.element_title  + " " + (this.medProbCount())}/>

                            </Col>
                            <Col>
                        <div  onClick={() => this.goToMedicalIssue()} >
                        <FontAwesomeIcon icon='edit' size='1px' className={"medical_issues"}  />
                        </div>

                            </Col>
                        </Row>

                    </Card.Header>
                        <Collapse in={this.state.isOpen}>
                        <Card.Body>

                    <div id="example-collapse-text">
                    <Table>
                    <tbody>{this.medProb()}</tbody>
                    </Table>
                    </div>

                    </Card.Body>
                        </Collapse>
                    </Card>
            )

    }



}



export default {
    view: (props) => (

         <MedicalProblems pid={props.pid} element={props.element} element_title={props.element_title} />
    )
};
