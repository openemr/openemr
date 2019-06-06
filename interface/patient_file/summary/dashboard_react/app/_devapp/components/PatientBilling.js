import React from "react";
import agent from "../utils/agent"
import ToggleButton from './global/ToggleButton.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container} from 'react-bootstrap';
import BillingSummary from "./billing/BillingSummary";

class PatientBilling extends React.Component {
    constructor(props) {

        super(props);

        this.state = {
            patientData: [],
            billing: [],
            insurance: [],
            isOpen: false,
            debtExists: false
        };
    }

    localToggle() {
        this.setState({isOpen: !this.state.isOpen});
    }

    /**
     * Fetch billing of logged in patient on component did mount
     */
    componentDidMount() {
        let getPatientData = agent.PatientDataAgent.patientExtend(this.props.pid);
        getPatientData.then((response) => {
            this.setState({
                patientData:response
            });
        })
        //console.log(this.state.patientData)

        let getBilling = agent.PatientDataAgent.patientApi('GET', this.props.pid, 'billing');
        getBilling.then(res => res.json()).then((response) => {
            this.setState({
                billing:response
            });
        })

        let getInsurance = agent.PatientDataAgent.patientApi('GET', this.props.pid, 'insurance', 'primary');
        getInsurance.then(res => res.json()).then((response) => {
            let getInsuranceProvider = agent.InsuranceDataAgent.insuranceCompaniesApi('GET', response.provider);
            getInsuranceProvider.then(res2 => res2.json()).then((response2) => {
                response.name = response2.name;
                this.setState({
                    insurance:response
                });
            });
        })
    }

    render() {
        // create dynamic title
        let rightTextButton = 'Billing';
        if (this.state.debtExists === true) {
            rightTextButton +=  ' [PLACEHOLDER FOR DEBT EXISTS ICON]';
        }
        // only if widget is opened add the children components with all data.
        let billingSummaryWidget = '';
        if (this.state.isOpen) {
            //console.log('is open');
            billingSummaryWidget = <BillingSummary billing={this.state.billing}
                                                   patientData={this.state.patientData}
                                                   insurance={this.state.insurance}/>
        }

        return (
            <div className="card" variant="dark">
                <div className="card-header">

                    <ToggleButton isOpen={this.state.isOpen}
                                  onClick={() => this.localToggle()}
                                  rightText={rightTextButton}/>
                </div>
                <div className="cardBody">
                    {billingSummaryWidget}
                </div>

            </div>
        );
    }

}

export default {
    view: (props) => (
        <PatientBilling pid={props.pid} title={props.element_title}/>
    )
};