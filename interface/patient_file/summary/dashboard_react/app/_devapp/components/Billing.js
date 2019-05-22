import React from "react";
import agent from "../utils/agent"
import ToggleButton from './global/ToggleButton.js';
import {Button, Table, Card, Collapse, Tab, Row, Col, Nav, Container} from 'react-bootstrap';

class Billing extends React.Component {
    constructor(props) {

        super(props);

        this.state = {
        };
    }
}

export default {
    view: (props) => (
        <Billing pid={props.pid} title={props.element_title}/>
    )
};