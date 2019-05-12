import React from 'react';
import agent from "../../utils/agent";

class PatientDataTabBlock extends React.Component {
    constructor(props) {
        super(props);
        const groupId = props.groupId;
        this.state = {
            patientData: [],
            isLoaded: false
        }
    }

    componentDidUpdate() {
        // console.log("props A: " + this.props.groupId);
        // console.log(this.props.groupFields);
        // console.log("props B");
    }

    //     console.log(this.props.isLoad);
    //     if(this.props.isLoad ) {
    //         Promise.all([agent.PatientDataAgent.byGroupId("DEM", this.props.groupId)]).then(
    //             result => {
    //                 result.map((a) => {
    //                     this.setState({patientData: a});
    //                 });
    //                 // console.log(result);
    //             }
    //         );
    //     }
    //     // console.log("AA");
    //     // console.log(fieldsList);
    //     // console.log("BB");
    // }

    render() {
        // console.log("DDD");
        if (this.state.patientData.length > 0) {
            return (<div>
                {
                    this.state.patientData.map((pdata, j) => {
                        return (<div id={j}>{pdata.field_id}</div>)
                    })
                }
            </div>)
        }
        return <div>Loading...</div>;
    }

    // {this.props.data.map((data, i) => {
    //         return (
    //             <div key={i}>
    //                 <Container>
    //                     <Row>
    //                         <Col>First Name:</Col>
    //                         <Col>{data.fname}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>Last Name:</Col>
    //                         <Col>{data.lname}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>English full name:</Col>
    //                         <Col>{data.title}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>Dob:</Col>
    //                         <Col>{data.DOB}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>S.S.:</Col>
    //                         <Col>{data.ss}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>Sex:</Col>
    //                         <Col>{data.sex}</Col>
    //                     </Row>
    //                     <Row>
    //                         <Col>Birth country:</Col>
    //                         <Col>{data.county}</Col>
    //                     </Row>
    //                 </Container>
    //             </div>
    //         )
    //     })}
}

export default PatientDataTabBlock;