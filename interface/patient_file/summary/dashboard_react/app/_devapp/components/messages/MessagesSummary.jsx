import React from 'react';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container, ListGroup, ListGroupItem} from 'react-bootstrap';
import MessagesRow from "./MessageRow"

/**
 * This component is wrapper of the summary of all the messages
 */
class MessagesSummary extends React.Component {

    constructor(props) {
        super(props);
        this.showAll = this.showAll.bind(this);
        this.state = {
            countShown:1,
            messagesDone: []
        };
        this.onMessageDone = this.onMessageDone.bind(this);
        // console.log(this.props)
    }

    showAll() {
        this.setState({
            countShown:'all'
        });
    }

    onMessageDone(id) {
        this.props.onMessageDone();
        this.setState({ messagesDone: this.state.messagesDone.concat([id]) });
    }

    render() {
        let messagesRows = '';
        let messagesCount = 0;
        let countShown = 0;

        if( this.props.messages ) {
            messagesRows = this.props.messages
                .filter(message => this.state.messagesDone.indexOf(message.id) === -1)
                .map((message, index) => {
                if (this.state.countShown === 'all' || index + 1 <= this.state.countShown) {
                   // if (message.message_status !== 'Done') {
                        messagesCount++;
                        return <MessagesRow onMessageDone={this.onMessageDone} key={index} message={message}></MessagesRow>
                  //  }
                }
            });
            countShown  = this.state.countShown;
        }

        return (
            <ListGroup variant="flush">
                <ListGroup.Item  variant="secondary">
                    <Row>
                        <Col md={2} sm={2}>To</Col>
                        <Col md={2} sm={2}>From</Col>
                        <Col md={2} sm={2}>Date</Col>
                        <Col md={4} sm={4}>Message content</Col>
                        <Col md={2} sm={2}></Col>
                    </Row>
                </ListGroup.Item>
                {messagesRows}
                <ListGroup.Item  variant="secondary">
                    <Row>
                        <Col>Shown {this.state.countShown === 'all' ? messagesCount : countShown} from {messagesCount}. <a onClick={this.showAll}><button>All messages</button></a></Col>
                    </Row>
                </ListGroup.Item>
            </ListGroup>

            )

    }
}


export default MessagesSummary;