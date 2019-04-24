import React from "react";

import Table from 'react-bootstrap/Table';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Collapse from 'react-bootstrap/Collapse';
import helpers from '../utils/helpers.js';

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus,faMinus } from '@fortawesome/free-solid-svg-icons'
library.add( faPlus,faMinus )



class MedicalProblems extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            data: [],
            element:props.element,
            element_title:props.element_title
        }
    }

    getMedicalProblems() {
        this.setState.patientId = helpers.getPatientId();
        if (this.setState.patientId >= 0) {
            let urlToFetch = ""
            switch (this.state.element)
            {
                case "Allergies":
                    urlToFetch="allergy";
                    break;
                case "MedicalProblems":
                    urlToFetch="medical_problem";
                    break;
                case "Medications":
                    urlToFetch="medication";
                    break;
            }

            fetch("../../../../apis/api/patient/" + this.setState.patientId+"/"+urlToFetch, {
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

                           console.log("NO DATA WAS FETCHED");
                          this.setState({render:false})
                        }
                        this.setState({render:true})

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

        return(
                    <Card   variant="flush">
                    <Card.Header>
                    <Button   onClick={() => {this.setState({ open: !open }  ); }}  aria-controls="example-collapse-text" aria-expanded={open} >
                        {!open ?  <FontAwesomeIcon icon='plus'/> :  <FontAwesomeIcon icon='minus'/>    }
                    </Button> {this.state.element_title} ({this.medProbCount()})
                    </Card.Header>
                        (!this.state.render?<Card.Body></Card.Body></Card>:
                    <Card.Body>
                    <Collapse in={this.state.open}>
                    <div id="example-collapse-text">
                    <Table stripped>
                    <tbody>{this.medProb()}</tbody>
                    </Table>
                    </div>
                    </Collapse>
                    </Card.Body>
                    </Card>)
            )

    }



}

export default MedicalProblems;

