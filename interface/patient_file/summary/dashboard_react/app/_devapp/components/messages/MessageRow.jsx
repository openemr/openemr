import React from 'react';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container, ListGroup, ListGroupItem} from 'react-bootstrap';
import FormatDate from '../global/FormatDate';

class MessageRow extends React.Component {

    constructor(props) {
        super(props);
        this.state = {};

    }


    render() {
         let message = this.props.message;
        return (
        <ListGroup.Item>
            <Row>
                <Col md={2} sm={2}>{message.assigned_to}</Col>
                <Col md={2} sm={2}>{message.user}</Col>
                <Col md={2} sm={2}><FormatDate>{message.date}</FormatDate></Col>
                <Col md={4} sm={4}>
                    <Row>
                        <Col>{message.title}</Col>
                    </Row>
                    <Row>
                        <Col>{message.body}</Col>
                    </Row>
                </Col>
                <Col md={2} sm={2}></Col>
            </Row>
        </ListGroup.Item>
        )
    }
}


export default MessageRow;