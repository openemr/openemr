import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MedicalProblems from "./MedicalProblems";
import PatientData from "./PatientData";
import MenuDashboard from "./MenuDashboard";


class Dashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {

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
            fetch("../../../../apis/api/menus", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    // 'Authorization': 'Bearer ' + this.setState.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.setState({gadgets: result});
                        console.log("Dashboard Items:" + JSON.stringify(result));
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
                        <MedicalProblems />

                    </div>
                </div>
            </div>

        );
    }


}

export default Dashboard;
