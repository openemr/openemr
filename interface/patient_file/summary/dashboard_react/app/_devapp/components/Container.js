


import React, {Component} from 'react';
import PropTypes from 'prop-types';
import Card from "react-bootstrap/Card";
import {LazyLoadModule} from "../lazy";
import Button from "react-bootstrap/Button";



class Container extends Component {
    componentDidMount() {
        //////debugger;
        this.setState({gadgets : this.getElement()});
    }
    constructor(props) {
        super(props);
        this.state={
            children:props.children,
            gadgets:null,
            patientId:props.pid,
            title:props.element_title}
    }
    getElement (){

        try {
            ////debugger;
            let items = [];
            var result = [];
            let patientId = this.state.patientId;
            this.state.children.map((v,i) => {

                //console.log(v);
                //console.log(i);
                let item = v;

             if( item.activity != 0) {
                   //debugger;
                    // //console.log('-------');
                    let element = item.element;
                    let title = item.element_title;
                    let element_component = item.element_component;
                    // //console.log(element_component);
                    items.push(<LazyLoadModule key={element_component + i}
                                               resolve={() => import("./"+ element_component)}
                                               element={element}
                                               element_title={title}
                                               pid={ this.state.patientId}/>);
                }
            })

            return items;
        }
        catch (e)
        {
            //console.log(e);
        }
    }

    render() {
        return (
            <div>
            <div>
                <Card>
                    <Card.Header>
                        {this.state.title}
                    </Card.Header>
                    <Card.Body>
                        {this.state.gadgets}
                    </Card.Body>
                </Card>

            </div>
            </div>
        );
    }
}

Container.propTypes = {};

export default {
    view: (props) => (
        <Container pid={props.pid} element={props.element} element_title={props.element_title} children={props.children}/>
    )
};
