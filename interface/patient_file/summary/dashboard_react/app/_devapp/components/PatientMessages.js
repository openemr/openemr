import React from "react";
import OEApi from "../utils/api"
import ToggleButton from './global/ToggleButton.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container} from 'react-bootstrap';
import MessagesSummary from "./messages/MessagesSummary"

class PatientMessages extends React.Component {
    constructor(props) {

        super(props);

        this.state = {
            messages:[],
            isOpen: false,
            sumOfMessages:null
        };
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }

    componentDidMount() {

        let getMassage = OEApi.PatientApi.fetch(this.props.pid, 'message');
        getMassage.then(res => res.json()).then((response) => {
            this.setState({
                messages:response,
                sumOfMessages:response.length
            });
        })
    }

    render() {
        console.log(this.props);
        let rightTextButton = 'Patient messages';
        if (this.state.sumOfMessages !== null) {
            rightTextButton += ' (' + this.state.sumOfMessages + ')';
        }

        let messageSummaryWidget = '';
        if (this.state.isOpen) {
            messageSummaryWidget = <MessagesSummary messages={this.state.messages} />
        }

        return (
            <div className="card" variant="dark">
                <div className="card-header">

                    <ToggleButton isOpen={this.state.isOpen}
                                  onClick={() => this.localToggle()}
                                  rightText={rightTextButton}/>
                </div>
                <div className="cardBody">
                    {messageSummaryWidget}
                </div>

            </div>
        );
    }
}

export default {
    view: (props) => (
        <PatientMessages pid={props.pid} title={props.element_title}/>
    )
};

