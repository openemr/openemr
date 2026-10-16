/**
 * @jest-environment jsdom
 */

/**
 * Tests for portal/patient/scripts/app.js.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const appSource = fs.readFileSync(
    path.resolve(__dirname, '../../portal/patient/scripts/app.js'),
    'utf8'
);

function loadApp() {
    const location = {replace: jest.fn()};
    const parent = {parent: {location}};
    const top = {webroot_url: 'https://example.test/openemr'};
    const asyncAlertMsg = jest.fn();
    const app = new Function('parent', 'top', 'asyncAlertMsg', `${appSource}\nreturn app;`)(
        parent,
        top,
        asyncAlertMsg
    );
    return {app, asyncAlertMsg, location};
}

describe('portal app alerts', () => {
    test('redirects to portal verification when the session is unauthorized', () => {
        const {app, asyncAlertMsg, location} = loadApp();

        app.appendAlert('', 'danger', 15000, 'modelAlert', {status: 401});

        expect(location.replace).toHaveBeenCalledWith(
            'https://example.test/openemr/portal/verify_session.php?w&u'
        );
        expect(asyncAlertMsg).not.toHaveBeenCalled();
    });

    test('does not redirect for an unrelated bad request', () => {
        const {app, location} = loadApp();

        app.appendAlert('', 'danger', 15000, 'modelAlert', {status: 400});

        expect(location.replace).not.toHaveBeenCalled();
    });

    test('continues showing ordinary response errors', () => {
        const {app, asyncAlertMsg, location} = loadApp();

        app.appendAlert('Unable to load data', 'danger', 15000, 'modelAlert', {status: 500});

        expect(location.replace).not.toHaveBeenCalled();
        expect(asyncAlertMsg).toHaveBeenCalledWith('Unable to load data', 15000, 'danger');
    });
});
