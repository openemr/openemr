import React from "react";
import Alert from "react-bootstrap/Alert";
import helpers from '../utils/helpers.js';
import ReactDOM from "react-dom";

import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import NavDropdown from "react-bootstrap/NavDropdown";



class MenuDashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            patientId: props.patientId,
            menu:[]

        }
    }

    getPatientFileMenu() {
        var url="../../../../apis/api/menus/1/patient_file";
        fetch(url, {
            method: "GET",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
            .then((res) => res.json())
            .then(
                (result) => {
                    this.setState({menu : this.drawPatientFileMenu(result)});

                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                    console.log("fail");
                }
            )
    }

    drawPatientFileMenu(menu_json) {

        function topRestoreSession() {
            top.restoreSession();
            console.log("restoreSession");
        }

        var main_titles=[];
        var parsed_menu=JSON.parse(menu_json);

        for (var property in parsed_menu) {
            main_titles.push(parsed_menu[property]);
        }

        var listElements = main_titles
            .filter(function(main_titles) { return main_titles.href; })
            .map(function(main_titles) {
                return  React.createElement(Nav.Link, { 'onClick': topRestoreSession, 'href': main_titles.href }, main_titles.label);
            });

        return  React.createElement('div', { 'className': 'row' },
            React.createElement('div', { 'className': 'col-sm-12' },


                React.createElement(Navbar, {'bg': 'light', 'expand': 'lg'},
                    React.createElement(Navbar.Toggle, { 'aria-controls': 'basic-navbar-nav' }),
                    React.createElement(Navbar.Collapse, { 'id': 'basic-navbar-nav' },
                        React.createElement(Nav, { 'className': 'mr-auto' },listElements)
                    ))


            ));
    };

    componentDidMount() {
        this.getPatientFileMenu();
    }
    render() {
        return(

            <div>
                {this.state.menu}
            </div>
        )
    }
}

export default MenuDashboard;

