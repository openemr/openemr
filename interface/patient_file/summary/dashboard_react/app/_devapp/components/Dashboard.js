import React from "react";
import PatientData from "./PatientData";
import helpers from '../utils/helpers.js';

class Dashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            gadgets: [],
            token: []

        };
    }

    authorizeFirst() {
        fetch("../../../../apis/api/auth", {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                grant_type: 'password',
                username: 'admin',
                password: 'pass',
                scope: 'default'
            })
        })
            .then((res) => res.json())
            .then(
                (result) => {
                    this.setState({token: result.access_token, isLoaded: true});
                    console.log("Token: " + result.access_token);
                    this.setState.token=result.access_token;

                    this.getDashboardList();
                    this.getPatientFileMenu();
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
        if (this.setState.token) {
            fetch("../../../../apis/api/menus", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.setState.token
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
        }else{
            console.log("no token");
        }
    }


    getPatientFileMenu() {
        console.log("drawing menu");
        if (this.setState.token) {
            fetch("../../../../apis/api/menus", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.setState.token
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
        }else{
            console.log("no token");
        }
    }

    componentDidMount() {
       this.authorizeFirst();
    }

    render() {
        return (
            <div>
            {JSON.stringify(this.setState.gadgets)}
            <PatientData />
            </div>
        );
    }


}

export default Dashboard;