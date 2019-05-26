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

/**
 * Dashboard is the parent component of this app
 * This component load the activity gadgets from dashboard list and print the in selected section.
 */
class Dashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            gadgets:[],
            /* sections */
            header:[],
            right:[],
            left:[],
            moudle:'MenuDashboard',
            patientId: null
        };

    }

    /**
     * Fetch current pid and activity component on component did mount
     */
    componentDidMount() {
        let patientId = helpers.getPatientId();

        this.setState({
            patientId: patientId
        });

        //TEMP SOLUTION - create pid session
        agent.PatientDataAgent.setGlobalPatientId(patientId);

        this.fetchData("dashboard").then(result => {

                let list = agent.Lists.getList("dashboard");
                // console.log(list)
                this.setState({'gadgets': list}, () => {
                    this.updateGadgets();
                });
            }

        );

    }


    /**
     * fetch list from database using 'agent.Lists' and result boolean when list was saved in the agent.Lists
     * @param listName
     * @returns {Promise.<boolean>}
     */
    fetchData = async (listName) => {

        var result = await agent.Lists.fetchList(listName);

        return result ? true : false;
    }

    /**
     * Run after the gadgets were returned from database.
     * Update every state of section with your gadgets
     */
    updateGadgets () {
        this.setState({
            header:this.getElement('header'),
            left:this.getElement('left'),
            right:this.getElement('right')
        })
    }

    /**
     * return array of components that loaded using lazy load components
     * @param id of section position - header/right/left
     * @returns {Array}
     */
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
                      let children = item.notes.children;
                      let settings = item.notes.settings ? item.notes.settings : null;
                      items.push(<LazyLoadModule key={element_component + i}
                                                 resolve={() => import(/* webpackMode: "eager" */"./"+ element_component)}
                                                 element={element}
                                                 element_title={title}
                                                 pid={this.state.patientId}
                                                 children={children}
                                                 settings={settings}

                      />);
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

                            {this.state.right}

                        </div>

                </div>
            </div>
                }
            </div>
        );
    }


}

export default Dashboard;
