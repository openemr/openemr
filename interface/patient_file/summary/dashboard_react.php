<?php
/**
 * Created by PhpStorm.
 * User: yuriyge
 * Date: 4/21/19
 * Time: 10:28 AM
 *
 *
 */

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Hello World</title>
    <script src="https://unpkg.com/react@16/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js"></script>

    <!-- Don't use this in production: -->
    <script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>
</head>
<body>
<div id="root"></div>
<script type="text/babel">

    class WidgetBill extends React.Component {
        constructor(props) {
            super(props);
            // This binding is necessary to make `this` work in the callback
            this.handleClick = this.handleClick.bind(this);
            this.handleFacilityListClick = this.handleFacilityListClick.bind(this);
            this.state = {
                error: null,
                isLoaded: false,
                facilityList:[],
                token: []
            }
        }

        handleClick() {
            fetch("http://<?php echo $_SERVER["SERVER_NAME"]?>/oem/openemr/apis/api/auth", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    grant_type: 'password',
                    username: 'admin',
                    password: 'pass',
                    scope: 'default'
                })
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.setState({token: result.access_token, isLoaded: true});
                        console.log("Token: " + result.access_token);
                    },
                    (error) => {
                        this.state({
                            isLoaded: true,
                            error
                        });
                    }
                )
        }

        handleFacilityListClick() {
            if(this.state.isLoaded) {
                fetch("http://<?php echo $_SERVER["SERVER_NAME"]?>/oem/openemr/apis/api/facility", {
                    method: "GET",
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.state.token
                    }
                })
                    .then((res) => res.json())
                    .then(
                        (result) => {
                            this.setState({facilityList: result, isLoaded: true});
                            console.log("FacilityList:" + JSON.stringify(this.state.facilityList) );
                        },
                        (error) => {
                            this.setState({
                                isLoaded: true,
                                error
                            });
                        }
                    )
            }

        }

        render() {
            return (
                [
                    <div key='1'><button id="auth" onClick={this.handleClick}>Authorize</button></div>,
                    <div key='2'><button id="faci" onClick={this.handleFacilityListClick}>Facility List</button></div>
                ]
            )
                ;
        }
    }

    ReactDOM.render(
        <WidgetBill/>,
        document.getElementById('root')
    );


</script>
<!--
  Note: this page is a great way to try React but it's not suitable for production.
  It slowly compiles JSX with Babel in the browser and uses a large development build of React.

  Read this section for a production-ready setup with JSX:
  https://reactjs.org/docs/add-react-to-a-website.html#add-jsx-to-a-project

  In a larger project, you can use an integrated toolchain that includes JSX instead:
  https://reactjs.org/docs/create-a-new-react-app.html

  You can also use React without JSX, in which case you can remove Babel:
  https://reactjs.org/docs/react-without-jsx.html
-->
</body>

</html>
