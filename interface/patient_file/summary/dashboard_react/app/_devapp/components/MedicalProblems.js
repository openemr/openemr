import React from "react";
import Container from 'react-bootstrap/Container'
import Alert from "react-bootstrap/Alert";
import helpers from '../utils/helpers.js';

import Collapse from 'react-bootstrap/Collapse'
import Button from 'react-bootstrap/Button'
import Table from "react-bootstrap/Table";
import $ from "jquery";



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
                    'Authorization': 'Bearer ' + this.state.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                         this.setState.data = result;
                        //this.setState({data: [result]});
                        if(result==null)
                        {
                            return{
                                render() {
                                <
                                    Alert > No
                                    Data
                                    Found < /Alert>
                                }
                                }
                        }
                        console.log(result);

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
            console.log("Patient not found");
        }
    }

    componentWillMount() {

        this.getMedicalProblems();
    }

    render() {

    }



}

export default MedicalProblems;

