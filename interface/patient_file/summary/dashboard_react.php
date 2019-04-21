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

    class Dashboard extends React.Component {
        constructor(props) {
            super(props);

            this.state = {
                gadgets: [],
                token:[]

            };
        }
        authorizeFirst() {
            fetch("http://<?php echo $_SERVER["SERVER_NAME"]?>/matrix-israel/openemr/apis/api/auth", {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    grant_type: 'password',
                    username: 'adminadminadmin',
                    password: 'pass123456789',
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
        getDashboardList() {
            if (this.state.token) {
                fetch("http://<?php echo $_SERVER["SERVER_NAME"]?>/matrix-israel/openemr/apis/api/list/dashboard", {
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
                            this.setState({gadgets: result});
                            console.log("Dashboard Items:" + JSON.stringify(this.state.gadgets));
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

        componentWillMount() {
            this.authorizeFirst();
            this.getDashboardList();
        }

        render() {
            return (
                    <div>
                        {JSON.stringify(this.state.gadgets)}
                        <PatientData />
                    </div>
            );
        }


    }

    class PatientData extends React.Component {
        render(){
            return (
                <div>kjhdskfjh</div>
            )
        }
    }

    ReactDOM.render(<Dashboard />,document.getElementById('root'));





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
