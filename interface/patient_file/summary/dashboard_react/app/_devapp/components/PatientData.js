import React, {Component} from "react";


class PatientData extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: []
        }
    }

    getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return (false);
    }

    componentDidMount() {
        this.setState.patientId = this.getQueryVariable("set_pid");

        if (this.setState.patientId > 0) {
            fetch("http://localhost/matrix-israel/openemr/apis/api/patient/" + this.state.patientId, {
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
  console.log(JSON.stringify());
        let data = this.setState.data;
        return (
            <div>
                <table border="1">
                    <thead>
                    <tr>
                    <th>ID</th>
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
                </table>
            </div>

        )
    }
}

export default PatientData;