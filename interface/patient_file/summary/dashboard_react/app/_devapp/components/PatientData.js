import React, {Component} from "react";
import helpers from '../utils/helpers.js';
import Dashboard from "./Dashboard";
import Button from "react-bootstrap/Button";
import Table from "react-bootstrap/Table";


class PatientData extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: []
        }
    }



    componentDidMount() {
       this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {
            fetch("../../../../apis/api/patient/" + this.setState.patientId, {
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
                        // this.setState.data = result;
                        this.setState({data: [result]});
                    },
                    (error) => {
                        // this.setState({
                        //     isLoaded: true,
                        //     error
                        // });
                    }
                )
        } else {
            console.log("Patient not found");
        }
    }

    render() {

        let data = this.setState.data;
        return (
            <div>
                <Button>asdas</Button>

                <Table>
                    <thead>
                    <tr>
                    <th>ID </th>
                    <th>PID</th>
                    <th>FName</th>
                    <th>LName</th>
                    <th>Title</th>
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
                            </tr>
                        )
                    })}
                    </tbody>
                </Table>
            </div>

        )
    }
}

export default PatientData;