import React, {Component, Fragment} from 'react';
import {render} from 'react-dom';
import {asyncComponent} from 'react-async-component';

/** We are importing our index.php my app Vairaible */
;
import Dashboard from './components/Dashboard.js';
import PatientData from './components/PatientData.js';

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


render(<Dashboard/>, document.getElementById('app'));





