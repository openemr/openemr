class Dashboard extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            gadgets: [],
            token:[]

        };
    }
    authorizeFirst() {
        fetch("http://localhost/openemr-community-react/apis/api/auth", {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                grant_type: 'password',
                username: 'adminadminadmin',
                password: 'admin123456789',
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
            fetch("http://localhost/openemr-community-react/apis/api/list/dashboard", {
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

export default Dashboard;