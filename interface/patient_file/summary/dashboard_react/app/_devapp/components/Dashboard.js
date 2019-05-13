import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React, { Component } from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MenuDashboard from "./MenuDashboard";
import Card from "react-bootstrap/Card";
import Button from "react-bootstrap/Button";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {LazyLoadModule} from "../lazy";

import agent from '../utils/agent.js';

class Dashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            gadgets:[],
            header:[],
            right:[],
            left:[],
            moudle:'MenuDashboard',
            patientId: null
        };

    }

    setGlobalPatientId() {

        fetch("../../../../apis/api/patient/extended/" + this.state.patientId, {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({

            })
        })
            .then((res) => res.json())
            .then(
                (result) => {

                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
    }

    fetchData = async (listName) => {

       // const a = new Promise((resolve, reject) => {
        var result = await agent.Lists.fetchList(listName);

        return result ? true : false;
    }


    componentWillMount() {

        this.setState({
            patientId:helpers.getPatientId()
        });


        var result = this.fetchData("dashboard").then( result => {

                console.log(result)
                var list = agent.Lists.getList("dashboard");
                console.log(list)
                this.setState({'gadgets': list}, () => {
                    this.updateGadgets();
                });
            }

        );

    }

    updateGadgets () {
        this.setState({
            header:this.getElement('header'),
            left:this.getElement('left'),
            right:this.getElement('right')
        })
    }

    getElement (id){

        try {

            let items = [];
           // let elementObject = this.state.gadgets;

            var result = [];

            this.state.gadgets.map((v,i) => {
                let item = v;

                  if(item.notes.id == id && item.activity != 0) {
                      // debugger;
                      // console.log('-------');
                      let element = item.notes.element;
                      let title = item.title;
                      let element_component = item.notes.element_component;
                      // console.log(element_component);
                      items.push(<LazyLoadModule key={element_component + i}
                                                 resolve={() => import(/* webpackMode: "eager" */"./"+ element_component)}
                                                 element={element}
                                                 element_title={title}
                                                 pid={this.state.patientId}/>);
                  }
            })

            return items;
        }
        catch (e)
            {
                console.log(e);
            }
    }



    render() {

        return (

            <div>
                {
            this.state && <div className="container-fluid">

                <div className="row">
                    <div className="col-md-12">
                        {this.state.header}
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-8">
                        <Card>
                            <Card.Header>
                                Practical information
                            </Card.Header>
                            <Card.Body>
                                {this.state.left}
                            </Card.Body>
                        </Card>

                    </div>
                    <div className="col-md-4">
                        <Card>
                        <Card.Header>
                            Medical Highlights
                        </Card.Header>
                        <Card.Body>
                            {this.state.right}
                        </Card.Body>
                        </Card>

                        </div>
                </div>
            </div>
                }
            </div>
        );
    }


}

export default Dashboard;
