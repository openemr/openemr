import React from 'react';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container, ListGroup, ListGroupItem} from 'react-bootstrap';
import FormatDate from "../global/FormatDate";

/**
 * This component is wrapper of the summary of all the messages
 */
class BillingSummary extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        };
        //console.log(this.props)
    }



    render() {
        return (
            <ListGroup variant="flush">
                <ListGroup.Item  variant="secondary">
                    <Row>
                        <Col md={4} sm={4}>Patient Balance Due</Col>
                        <Col md={4} sm={4}>Insurance Balance Due</Col>
                        <Col md={4} sm={4}>Total Balance Due</Col>
                    </Row>
                </ListGroup.Item>
                <ListGroup.Item>
                    <Row>
                        <Col md={4} sm={4}>{this.props.billing.patientBalance}</Col>
                        <Col md={4} sm={4}>{this.props.billing.insuranceBalance}</Col>
                        <Col md={4} sm={4}>{this.props.billing.totalBalance}</Col>
                    </Row>
                </ListGroup.Item>
                {this.props.patientData.billing_note.length > 0 &&
                <ListGroup.Item variant="secondary">
                    <Row>
                        <Col md={12} sm={12}>Billing Note</Col>
                    </Row>
                </ListGroup.Item>
                }
                {this.props.patientData.billing_note.length > 0 &&
                <ListGroup.Item>
                    <Row>
                        <Col md={12} sm={12}>{this.props.patientData.billing_note}</Col>
                    </Row>
                </ListGroup.Item>
                }
                <ListGroup.Item  variant="secondary">
                    <Row>
                        <Col md={4} sm={4}>Primary Insurance</Col>
                        <Col md={4} sm={4}>Copay</Col>
                        <Col md={4} sm={4}>Effecitve Date</Col>
                    </Row>
                </ListGroup.Item>
                <ListGroup.Item>
                    <Row>
                        <Col md={4} sm={4}>{this.props.insurance.name}</Col>
                        <Col md={4} sm={4}>{this.props.insurance.copay}</Col>
                        <Col md={4} sm={4}><FormatDate showTime={false} >{this.props.insurance.effdate}</FormatDate></Col>
                    </Row>
                </ListGroup.Item>


            </ListGroup>

        )

    }
}


export default BillingSummary;