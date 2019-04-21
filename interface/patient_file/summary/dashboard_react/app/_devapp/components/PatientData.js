import React from "react";


class PatientData extends React.Component {
    constructor(props){
        super(props);
        this.state = {
            patientId: props.patientId
        }
    }

    patientFile() {
        console.log(this.state.patientId);
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
                    //this.setState({facilityList: result, isLoaded: true});
                    console.log("PatientData:" + JSON.stringify(result) );
                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
    }

    render() {
        return (
            <div>{this.patientFile()}</div>
        )
    }
}

export default PatientData;