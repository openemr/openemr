import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React, { Component } from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MenuDashboard from "./MenuDashboard";
import Card from "react-bootstrap/Card";
import Button from "react-bootstrap/Button";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {LazyLoadModule} from "../lazy";
import FetchList from "./FetchList";

window['fetchList'] = new FetchList();

class Dashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            gadgets:null,
            moudle:'MenuDashboard',
            lists : window['fetchList'],
            patientId: null
        };


        this.GetElement = this.GetElement.bind(this);

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


    componentDidMount() {

        this.setState({
            patientId:helpers.getPatientId()
        });

       this.state.lists.fetchList("dashboard");
       let checkForDashboardListReady = window['fetchList'].state.staticLists['dashboard'];

       if(!checkForDashboardListReady) {
           let timerId = setTimeout(()=>{

                checkForDashboardListReady = window['fetchList'].state.staticLists['dashboard']
                if(checkForDashboardListReady){
                   // debugger;
                    this.setState({'gadgets':checkForDashboardListReady});
                    this.setGlobalPatientId();
                    console.log(this);
                    clearTimeout(timerId);
                }
            },1000)
        }

   }



    GetElement (id){

        try {

            let items = [];
           // let elementObject = this.state.gadgets;

            var result = [];

            this.state.gadgets.map((v,i) => {
                let item = v;

                  if(item.notes.id == id && item.activity ) {
                      // debugger;
                      console.log('-------');
                      let element = item.notes.element;
                      let title = item.title;
                      let element_component = item.notes.element_component;
                      console.log(element_component);
                      items.push(<LazyLoadModule key={element_component + i}
                                                 resolve={() => import(/* webpackMode: "eager" */"./"+ element_component)}
                                                 element={element}
                                                 element_title={title}
                                                 pid={this.state.patientId}/>);
                  }
            })

            return items;
        }
        catch
            (e)
            {
                console.log(e);
            }
    }



    render() {

        return (


            <div>
                {
            this.state && this.state.gadgets && <div className="container-fluid">

                <div className="row">
                    <div className="col-md-12">
                        {this.GetElement('header')}
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-8">
                        <Card>
                            <Card.Header>
                                Practical information
                            </Card.Header>
                            <Card.Body>
                                {this.GetElement('left')}
                            </Card.Body>
                        </Card>

                    </div>
                    <div className="col-md-4">
                        <Card>

                        <Card.Header>
                            Medical Highlights
                        </Card.Header>
                        <Card.Body>
                            {this.GetElement('right')}

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
