import React from "react";

import Table from 'react-bootstrap/Table';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Collapse from 'react-bootstrap/Collapse';
import Alert from "react-bootstrap/Alert";
import helpers from '../utils/helpers.js';





class MedicalProblems extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: []
        }
    }

    getMedicalProblems() {
        this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {
            fetch("../../../../apis/api/patient/" + this.setState.patientId+"/medical_problem", {
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
                            return{
                                render() {

                                   < Alert > No
                                    Data
                                    Found </Alert>
                                    }
                                }
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
        return this.state.data.map((mp,i) => {
            return <tr key={i}><td>{mp.title}</td><td>{mp.diagnosis}</td></tr>
        })
    }


    medProbCount= ()=>{
     return this.state.data.length;
    }

    render() {
        const { open } = this.state;
        return(     <Card   variant="flush">
                    <Card.Body>
                    <Card.Title>
                    <Button   onClick={() => this.setState({ open: !open })}  aria-controls="example-collapse-text" aria-expanded={open} > -  </Button> Medical issues ({this.medProbCount()})
                    </Card.Title>
                    <Collapse in={this.state.open}>
                    <Card.Text id="example-collapse-text">
                    <Table>
                    <tbody>{this.medProb()}</tbody>
                    </Table>
                    </Card.Text>
                    </Collapse>
                    </Card.Body>
                    </Card>
            )

    }



}

export default MedicalProblems;

