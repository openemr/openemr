import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React, { Component } from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MenuDashboard from "./MenuDashboard";
import Card from "react-bootstrap/Card";
import Button from "react-bootstrap/Button";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {LazyLoadModule} from "../lazy";
/*
import FetchList from "./FetchList";

window['fetchList'] = new FetchList();
*/
import agent from '../utils/agent.js';

class Dashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            gadgets:"",
            moudle:'MenuDashboard',

        };

        this.state.patientId = helpers.getPatientId();

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

        const a = new Promise((resolve, reject) => {
             agent.Lists.fetchList(listName);

            console.log('fetchData started');

            return resolve("done fetching");

        });



        return await Promise.all([a]) ? true : false;
    }


   componentWillMount() {


       this.fetchData("dashboard").then(()=>{
           let dashboardComponents = agent.Lists.getList("dashboard");

           this.setState({'gadgets':dashboardComponents});
       });


   }

    componentDidMount() {

        this.fetchData("dashboard").then((result) => {

            if(result) {
                debugger;
                var list = agent.Lists.getList("dashboard");
                this.setState({'gadgets': list});


            }
        });

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
                                                 element_title={title}/>);
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
