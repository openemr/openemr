/**
 * @jest-environment jsdom
 */

/**
 * Tests for the promise returned by dlgopen() in library/dialog.js
 *
 * Run with: npm run test:js -- tests/js/dialog-resolve-promise.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const src = fs.readFileSync(
    path.resolve(__dirname, '../../library/dialog.js'),
    'utf8'
);

// Sentinel returned by the timeout arm of a Promise.race, marking a promise
// that never settled.
const NEVER_SETTLED = Symbol('never settled');

let dlgopen;

beforeEach(() => {
    document.body.innerHTML = '';

    const jQuery = require('jquery');
    global.jQuery = jQuery;
    global.$ = jQuery;
    window.jQuery = jQuery;

    // Bootstrap's modal plugin is not available under jsdom. dlgopen only needs
    // it to exist and to be chainable; the promise is resolved before .modal()
    // is ever called.
    jQuery.fn.modal = function () {
        return this;
    };

    // Globals dlgopen reads off the top window. Under jsdom, top === window.
    window.webroot_url = '';
    window.set_opener = () => {};
    // Real browsers always define window.opener (null when there is no opener);
    // jsdom does not, and dialog.js reads it bare at load time.
    window.opener = null;

    // Supplied by library/js/utility.js on a real page. dialog.js uses it to
    // lazy-load jQuery and Bootstrap when they are missing.
    global.includeScript = () => Promise.resolve();
    window.includeScript = global.includeScript;

    // dialog.js declares dlgopen as a top-level function rather than attaching
    // it to window, so hand it back out of the loader explicitly.
    dlgopen = new Function(`${src}\nreturn dlgopen;`)();
});

describe('dlgopen promise resolution', () => {

    test('resolves when the caller does not pass resolvePromiseOn', async () => {
        const promise = dlgopen(
            'about:blank',
            'defaultResolveDlg',
            400,
            300,
            false,
            'Default',
            {type: 'iframe'}
        );

        // Raced rather than awaited directly: when the default is broken the
        // promise never settles, and a race reports that as a clear assertion
        // failure instead of a test-runner timeout.
        const dialog = await Promise.race([
            promise,
            new Promise((resolve) => setTimeout(() => resolve(NEVER_SETTLED), 100)),
        ]);

        expect(dialog).not.toBe(NEVER_SETTLED);
    });

    test('resolves when the caller opts into init explicitly', async () => {
        const dialog = await dlgopen(
            'about:blank',
            'explicitInitDlg',
            400,
            300,
            false,
            'Explicit',
            {type: 'iframe', resolvePromiseOn: 'init'}
        );

        expect(dialog).toBeDefined();
    });

    test('does not resolve on init when the caller selects a later event', async () => {
        const promise = dlgopen(
            'about:blank',
            'closeResolveDlg',
            400,
            300,
            false,
            'Close',
            {type: 'iframe', resolvePromiseOn: 'close'}
        );

        const raced = await Promise.race([
            promise,
            new Promise((resolve) => setTimeout(() => resolve(NEVER_SETTLED), 50)),
        ]);

        expect(raced).toBe(NEVER_SETTLED);
    });
});
