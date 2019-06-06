import React from "react";
import agent from "../utils/agent"
import ToggleButton from './global/ToggleButton.js';
import Table from 'react-bootstrap/Table';
import Collapse from 'react-bootstrap/Collapse';
import Col from 'react-bootstrap/Col';
import Row from 'react-bootstrap/Row';
import Card from 'react-bootstrap/Card';


import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEdit } from '@fortawesome/free-solid-svg-icons'
library.add( faEdit )




/**
 * Main component of the prescriptions gadget
 */
class Prescriptions extends React.Component {
    constructor(props) {

        super(props);

        this.state = {
            prescriptions: [],
            isOpen: false,
            sumOfPrescriptions: null
        };
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }


    /**
     * Fetch all the prescriptions of logged in patient on component did mount
     */
    componentDidMount() {
        let getPrescriptions = agent.PatientDataAgent.patientApi('GET', this.props.pid, 'prescription');
        getPrescriptions.then(res => res.json()).then((response) => {
            this.setState({
                prescriptions: response,
                sumOfPrescriptions: response === null ? null : response.length
            });
        })
    }

    rxs = () => {
        if (Array.isArray(this.state.prescriptions)) {
            return this.state.prescriptions.map((rx, i) => {
                return <tr key={i}>
                    <td>{rx.drug}</td>
                </tr>
            })
        } else {
            return <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        }
    }

    goToPrescriptions = () => {
        window.location.href = "../../../../controller.php?prescription&list&id=" + this.props.pid;
    }

    render() {
        let rightTextButton = 'Prescriptions';
        if (this.state.sumOfPrescriptions !== null) {
            rightTextButton += ' (' + this.state.sumOfPrescriptions + ')';
        }

        return (

            <Card variant="flush">
                <Card.Header>
                    <Row>
                        <Col>
                            <ToggleButton isOpen={this.state.isOpen}
                                          onClick={() => this.localToggle()}
                                          rightText={rightTextButton}/>

                        </Col>
                        <Col>
                            <div onClick={() => this.goToPrescriptions()}>
                                <FontAwesomeIcon icon='edit' className={"prescription"}/>
                            </div>

                        </Col>
                    </Row>
                </Card.Header>
                <Collapse in={this.state.isOpen}>
                    <Card.Body>
                        <div id="example-collapse-text">
                            <Table>
                                <tbody>{this.rxs()}</tbody>
                            </Table>
                        </div>
                    </Card.Body>
                </Collapse>
            </Card>
        )

    }
}

export default {
    view: (props) => (
        <Prescriptions pid={props.pid} title={props.element_title}/>
    )
};

