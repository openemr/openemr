import React from "react";
import PatientData from "./PatientData";
import ReactDOM from 'react-dom';
import helpers from '../utils/helpers.js';

class Dashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            gadgets: [],
            token: []

        };
    }

    authorizeFirst() {
        fetch("../../../../apis/api/auth", {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                grant_type: 'password',
                username: 'admin2019admin',
                password: '123456789101112a',
                scope: 'default'
            })
        })
            .then((res) => res.json())
            .then(
                (result) => {
                    this.setState({token: result.access_token, isLoaded: true});
                    console.log("Token: " + result.access_token);
                    this.setState.token=result.access_token;

                    this.getDashboardList();
                    this.getPatientFileMenu();
                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
    }


    //list/dashboard
    getDashboardList() {
        if (this.setState.token) {
            fetch("../../../../apis/api/menus", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.setState.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.setState({gadgets: result});
                        console.log("Dashboard Items:" + JSON.stringify(result));
                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });
                        console.log("fail");
                    }
                )
        }else{
            console.log("no token");
        }
    }


    getPatientFileMenu() {
        if (this.setState.token) {
            fetch("../../../../apis/api/menus", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.setState.token
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.setState({gadgets: result});
                        console.log("Dashboard Items:" + JSON.stringify(result));


                        var menu = this.drawPatientFileMenu(result);

                        var node = document.getElementById("app"),
                            ele = document.createElement("div");
                        ele.id = "menu_container";
                        node.parentNode.insertBefore(ele, node.nextSibling);
                        ReactDOM.render(menu, document.getElementById("menu_container"));
                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });
                        console.log("fail");
                    }
                )
        }else{
            console.log("no token");
        }
    }


    drawPatientFileMenu(menu_json) {

        console.log("drawing menu");

        function onclick1() {
            top.restoreSession();
        }
        
        var main_titles=[];

        var parsed_menu=JSON.parse(menu_json);
        for (var property in parsed_menu) {
            main_titles.push(parsed_menu[property]);
        }

        var listElements = main_titles
            .filter(function(main_titles) { return main_titles.href; })
            .map(function(main_titles) {
                return React.createElement('li', {key: main_titles.id,'className': 'oe-bold-black', 'id': main_titles.id},
                    React.createElement('a', {href: main_titles.href}, main_titles.label)
                )
            });

        return  React.createElement('div', { 'className': 'row' },
            React.createElement('div', { 'className': 'col-sm-12' },
                React.createElement('nav', { 'className': 'navbar navbar-default navbar-color navbar-static-top patient-menu' },
                    React.createElement('div', { 'className': 'container-fluid' },
                        React.createElement('div', { 'className': 'navbar-header' },
                            React.createElement('button', {
                                    'className': 'navbar-toggle',
                                    'data-target': '#myNavbar',
                                    'data-toggle': 'collapse',
                                    'type': 'button'
                                },
                                React.createElement('span', { 'className': 'icon-bar' }),
                                React.createElement('span', { 'className': 'icon-bar' }),
                                React.createElement('span', { 'className': 'icon-bar' }))
                        ), React.createElement('div', {
                                'className': 'collapse navbar-collapse',
                                'id': 'myNavbar'
                            },
                            React.createElement('ul', {'className': 'nav navbar-nav' },listElements)


                        )))));




    };


    componentDidMount() {
       this.authorizeFirst();
    }

    render() {
        return (
            <div>
            {JSON.stringify(this.setState.gadgets)}
            <PatientData />
            </div>
        );
    }


}

export default Dashboard;
