import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MedicalProblems from "./MedicalProblems";
import PatientData from "./PatientData";
import MenuDashboard from "./MenuDashboard";
import Card from "react-bootstrap/Card";
import Button from "react-bootstrap/Button";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";


class Dashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            gadgets:''
        };
    }

    setGlobalPatientId() {
        this.setState.patientId = helpers.getPatientId();
        fetch("../../../../apis/api/patient/extended/" + this.setState.patientId, {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({

            })
        })
            .then((res) => res.json())
            .then(
                (result) => {

                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
    }


    //list/dashboard
    getDashboardList() {
        //if (this.setState.token) {
            fetch("../../../../apis/api/list/dashboard", {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    // 'Authorization': 'Bearer ' + this.setState.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        console.log("Got dashboard items");
                        this.setState({gadgets: result});
                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });
                        console.log("fail");
                    }
                )
    }

    componentDidMount() {
        this.setGlobalPatientId();
        this.getDashboardList();
    }


    render() {
        return (
            <div className="container-fluid">
                <div className="row">
                    <div className="col-md-12">
                        <MenuDashboard />
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-8">
                        <PatientData />

                    </div>
                    <div className="col-md-4">
                        <Card>

                        <Card.Header>
                            Medical Highlights
                        </Card.Header>
                        <Card.Body>
                            <MedicalProblems element="MedicalProblems" element_title="Medical Problems"/>
                            <MedicalProblems element="Allergies"  element_title="Allergies"/>
                            <MedicalProblems element="Medications"  element_title="Medications"/>
                        </Card.Body>
                        </Card>

                        </div>
                </div>
            </div>

        );
    }


}

export default Dashboard;
