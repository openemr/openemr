import bootstrap from "bootstrap/dist/css/bootstrap.css"
import React from "react";



import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';
import MedicalProblems from "./MedicalProblems";
import PatientData from "./PatientData";
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
    /*
    *      <!--MedicalProblems element="MedicalProblems" element_title="Medical Problems"/>
                            <MedicalProblems element="Allergies"  element_title="Allergies"/>
                            <MedicalProblems element="Medications"  element_title="Medications"/>-->
    * */
    constructor(props) {
        super(props);
        this.state = {
            gadgets:"",
            moudle:'MenuDashboard',
            lists : window['fetchList']
        };



    }

    setGlobalPatientId() {
        this.setState.patientId = helpers.getPatientId();
        fetch("../../../../apis/api/patient/extended/" + this.setState.patientId, {
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

            console.log('one');

            return resolve("done fetching");

        });



        return await Promise.all([a]) ? true : false;
    }

    //list/dashboard
   /* getDashboardList() {

        //if (this.setState.token) {
            fetch("../../../../apis/api/list/dashboard", {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    // 'Authorization': 'Bearer ' + this.setState.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        console.log("Got dashboard items");
                        this.setState({gadgets: result});
                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });
                        console.log("fail");
                    }
                )
    }*/

   componentWillMount() {


      //    console.log( agent.Lists.getAllList())
      //     this.setState({'gadgets': agent.Lists.getList("dashboard")});



       /*
      // this.state.lists.fetchList("dashboard");
       let checkForDashboardListReady = window['fetchList'].state.staticLists['dashboard'];

       if(!checkForDashboardListReady) {
           let timerId = setTimeout(()=>{

               checkForDashboardListReady = window['fetchList'].state.staticLists['dashboard']
               if(checkForDashboardListReady){
                   // debugger;
                   this.setState({'gadgets':checkForDashboardListReady});
                   this.setGlobalPatientId();
                   // console.log(this);
                   clearTimeout(timerId);
               }
           },1000)
       }*/

   }

    componentDidMount() {

        this.fetchData("dashboard").then((result) => {

            if(result) {
                debugger;
                var list = agent.Lists.getList("dashboard");
                this.setState({'gadgets': list});


            }
        });
        /*let dashboardList = lists.getStaticListByName['dashboard'] ;
        this.setState('gadgets', lists.getState('staticLists'));*/
    }

    GetElement (id){

        try {

            let items = [];
            let elementObject = this.state.gadgets;

            var result = [];

                for(var i in elementObject) {

                    let item = elementObject[i];

                    if(item.notes.id == id && item.activity != 0) {
                        // debugger;
                        var element = item.notes.element;
                        var title = item.title;
                        var element_component = item.notes.element_component;

                        items.push(<LazyLoadModule key={item} resolve={() => import("./" + element_component)}
                                                   element={element}
                                                   element_title={title}/>)


                    }
                }
            return items;
        }
        catch
            (e)
            {
                console.log(e);
            }
            // debugger;


    }

    render() {
        console.log(this.state.gadgets);
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
