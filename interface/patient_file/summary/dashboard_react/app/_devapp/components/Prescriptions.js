import React from "react";
import agent from "../utils/agent"
import ToggleButton from './global/ToggleButton.js';
import Table from 'react-bootstrap/Table';
import Collapse from 'react-bootstrap/Collapse';



/**
 * Main component of the prescriptions gadget
 */
class Prescriptions extends React.Component {
    constructor(props) {

        super(props);

        this.state = {
            prescriptions:[],
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
                prescriptions:response,
            sumOfPrescriptions: response === null ? null: response.length
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

    render() {
        // create dynamic title
        let rightTextButton = 'Prescriptions';
        if (this.state.sumOfPrescriptions !== null) {
            rightTextButton += ' (' + this.state.sumOfPrescriptions + ')';
        }

        return ( <div className="card" variant="dark">
                <div className="card-header">

                    <ToggleButton isOpen={this.state.isOpen}
                                  onClick={() => this.localToggle()}
                                  rightText={rightTextButton}/>
                </div>
                <Collapse in={this.state.isOpen}>
                <div className="cardBody">
                    <Table>
                        <tbody>{this.rxs()}</tbody>
                    </Table>
                </div>
                </Collapse>

            </div>
        );
    }
}

export default {
    view: (props) => (
        <Prescriptions pid={props.pid} title={props.element_title}/>
    )
};

