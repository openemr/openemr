import React from 'react';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container, ListGroup, ListGroupItem} from 'react-bootstrap';

/**
 * This component is wrapper of the summary of all the messages
 */
class BillingSummary extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        };
        console.log(this.props)
    }



    render() {
        return (
            <ListGroup variant="flush">
                <ListGroup.Item  variant="secondary">
                    <Row>
                        <Col md={3} sm={3}>Patient Balance Due</Col>
                        <Col md={3} sm={3}>Insurance Balance Due</Col>
                        <Col md={3} sm={3}>Total Balance Due</Col>
                        <Col md={3} sm={3}></Col>
                    </Row>
                </ListGroup.Item>
                <ListGroup.Item>
                    <Row>
                        <Col md={3} sm={3}>{this.props.billing.patientBalance}</Col>
                        <Col md={3} sm={3}>{this.props.billing.insuranceBalance}</Col>
                        <Col md={3} sm={3}>{this.props.billing.totalBalance}</Col>
                        <Col md={3} sm={3}><Button>Done</Button></Col>
                    </Row>
                </ListGroup.Item>
            </ListGroup>

        )

    }
}


export default BillingSummary;