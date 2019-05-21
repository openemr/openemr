import React from 'react';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container, ListGroup, ListGroupItem} from 'react-bootstrap';
import FormatDate from '../global/FormatDate';
import agent from "../../utils/agent"

class MessageRow extends React.Component {

    constructor(props) {
        super(props);
        this.state = {};
        this.messageDone = this.messageDone.bind(this);
    }

    messageDone() {
        this.props.message.message_status = 'Done';
       /* let postChange = agent.PatientDataAgent.patientApi('PUT', this.props.message.pid, 'message', this.props.message.id, this.props.message);
        postChange.then(res => res.json()).then((data) => {
            console.log(data)
        })*/

    }

    render() {
         let message = this.props.message;
         console.log(message);
        return (
        <ListGroup.Item>
            <Row>
                <Col md={2} sm={2}>{message.assigned_to}</Col>
                <Col md={2} sm={2}>{message.user}</Col>
                <Col md={2} sm={2}><FormatDate showTime={true} >{message.date}</FormatDate></Col>
                <Col md={4} sm={4}>
                    <Row>
                        <Col>{message.title}</Col>
                    </Row>
                    <Row>
                        <Col>{message.body}</Col>
                    </Row>
                </Col>
                <Col md={2} sm={2}><Button onClick={this.messageDone}>Done</Button></Col>
            </Row>
        </ListGroup.Item>
        )
    }
}


export default MessageRow;