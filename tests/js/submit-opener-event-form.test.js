/**
 * @jest-environment jsdom
 */

/**
 * Runs submitOpenerEventForm() so an empty form_action cannot save.
 *
 * Run with: npm run test:js -- tests/js/submit-opener-event-form.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const src = fs.readFileSync(
    path.resolve(__dirname, '../../interface/main/calendar/js/submit_opener_event_form.js'),
    'utf8'
);

function loadHelper() {
    // Production file declares top-level functions; return them from the loader
    // the same way tests/js/dialog-resolve-promise.test.js loads dlgopen.
    return new Function(`${src}\nreturn { openerEventFormHasAction, submitOpenerEventForm };`)();
}

function mockOpenerForm(actionValue) {
    document.body.innerHTML = `<form><input name="form_action" id="form_action" value="${actionValue}" /></form>`;
    const form = document.forms[0];
    form.submit = jest.fn();
    global.opener = {
        closed: false,
        document,
        top: { restoreSession: jest.fn() },
    };
    window.opener = global.opener;
    global.dlgclose = jest.fn();
    window.dlgclose = global.dlgclose;
    return form;
}

test('openerEventFormHasAction is false for a missing or empty action', () => {
    const { openerEventFormHasAction } = loadHelper();
    expect(openerEventFormHasAction(null)).toBe(false);
    expect(openerEventFormHasAction({})).toBe(false);
    expect(openerEventFormHasAction({ form_action: { value: '' } })).toBe(false);
    expect(openerEventFormHasAction({ form_action: { value: 'save' } })).toBe(true);
});

test('submitOpenerEventForm closes without submitting when form_action is empty', () => {
    const { submitOpenerEventForm } = loadHelper();
    const form = mockOpenerForm('');
    expect(submitOpenerEventForm()).toBe(false);
    expect(form.submit).not.toHaveBeenCalled();
    expect(global.dlgclose).toHaveBeenCalled();
    expect(global.opener.top.restoreSession).not.toHaveBeenCalled();
});

test('submitOpenerEventForm submits when form_action is save', () => {
    const { submitOpenerEventForm } = loadHelper();
    const form = mockOpenerForm('save');
    expect(submitOpenerEventForm()).toBe(true);
    expect(global.opener.top.restoreSession).toHaveBeenCalled();
    expect(form.submit).toHaveBeenCalled();
    expect(global.dlgclose).toHaveBeenCalled();
});

test.each([null, { closed: true }, { closed: false, document: { forms: [] } }])(
    'unavailable opener does not restore the session or submit', (openerWindow) => {
        const { submitOpenerEventForm } = loadHelper();
        global.opener = window.opener = openerWindow;
        global.dlgclose = jest.fn();
        expect(submitOpenerEventForm()).toBe(false);
        expect(global.dlgclose).not.toHaveBeenCalled();
    }
);

test('session restoration precedes submission and the popup closes afterward', () => {
    const { submitOpenerEventForm } = loadHelper();
    const form = mockOpenerForm('save');
    submitOpenerEventForm();
    expect(global.opener.top.restoreSession.mock.invocationCallOrder[0])
        .toBeLessThan(form.submit.mock.invocationCallOrder[0]);
    expect(form.submit.mock.invocationCallOrder[0])
        .toBeLessThan(global.dlgclose.mock.invocationCallOrder[0]);
});
