import React, { Component, Fragment } from 'react';
import { render } from 'react-dom';
import { asyncComponent } from 'react-async-component';

/** We are importing our index.php my app Vairaible */
import myApp from 'myApp';

/* globals __webpack_public_path__ */
__webpack_public_path__ = `${window.STATIC_URL}/app/assets/bundle/`;

const Header = asyncComponent({
    resolve: () => new Promise(resolve =>
        require.ensure([], require => {
            resolve(require('./Header'));
        },
        'Header')
    )
});

class Myapp extends Component {
    render() {

        const { user : { name, email }, logged } = myApp;

        return (
            <Fragment>
                <Header/>
                <div className="dashboard">
                    {logged &&
                        <h2 className="status">Logged In</h2>
                    }
                    <h1 className="name"> {name}</h1>
                    <p className="email">{email}</p>

                    <p>API host variable {__API_HOST__}</p>
                </div>
            </Fragment>
        )
    }
}

render(<Myapp/>, document.getElementById('app'));