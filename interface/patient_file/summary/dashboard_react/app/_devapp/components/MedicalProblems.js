import React from "react";
import Card from 'react-bootstrap/Container'
import Alert from "react-bootstrap/Alert";
import helpers from '../utils/helpers.js';

import Collapse from 'react-bootstrap/Collapse';



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
            return <li>{mp.title}</li>
        })
    }

    render() {

        return(
            <ul>
                {this.medProb()}
            </ul>
        )
    }



}

export default MedicalProblems;

