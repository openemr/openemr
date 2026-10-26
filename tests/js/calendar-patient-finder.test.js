/**
 * @jest-environment jsdom
 */

/* global __dirname */

const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../library/js/calendar-patient-finder.js'),
    'utf8'
);

function loadHelper(targetWindow) {
    new Function('window', source)(targetWindow);
    return targetWindow.OpenEMRCalendarPatientFinder;
}

describe('calendar patient finder Add Patient navigation', () => {
    test('opens and activates Add Patient in the application patient tab', () => {
        const restoreSession = jest.fn();
        const activateTabByName = jest.fn();
        const navigateTab = jest.fn((url, name, afterLoad) => afterLoad());
        const context = {top: {restoreSession, navigateTab, activateTabByName}};
        const closeFinder = jest.fn();
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/openemr/interface/new/new.php', closeFinder);

        expect(restoreSession).toHaveBeenCalledTimes(1);
        expect(navigateTab).toHaveBeenCalledWith(
            '/openemr/interface/new/new.php',
            'pat',
            expect.any(Function)
        );
        expect(activateTabByName).toHaveBeenCalledWith('pat', true);
        expect(closeFinder).toHaveBeenCalledTimes(1);
    });

    test('retains the application window until a delayed tab callback activates the patient tab', () => {
        const activateTabByName = jest.fn();
        let afterLoad;
        const applicationWindow = {
            navigateTab: jest.fn((url, name, callback) => {
                afterLoad = callback;
            }),
            activateTabByName
        };
        const context = {top: applicationWindow};
        const closeFinder = jest.fn();
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/interface/new/new.php', closeFinder);

        expect(closeFinder).toHaveBeenCalledTimes(1);
        expect(activateTabByName).not.toHaveBeenCalled();

        afterLoad();

        expect(activateTabByName).toHaveBeenCalledWith('pat', true);
    });

    test('does not submit or close the appointment while closing the finder', () => {
        const appointmentForm = {submit: jest.fn()};
        const appointmentWindow = {document: {theform: appointmentForm}};
        const context = {
            top: {navigateTab: jest.fn((url, name, afterLoad) => afterLoad())},
            opener: appointmentWindow
        };
        const closeFinder = jest.fn();
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/interface/new/new.php', closeFinder);

        expect(appointmentForm.submit).not.toHaveBeenCalled();
        expect(closeFinder).toHaveBeenCalledTimes(1);
    });

    test('uses the application behind a native appointment window', () => {
        const applicationWindow = {
            navigateTab: jest.fn((url, name, afterLoad) => afterLoad()),
            activateTabByName: jest.fn()
        };
        const context = {top: {}, opener: {closed: false, top: applicationWindow}};
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/interface/new/new.php', jest.fn());

        expect(applicationWindow.navigateTab).toHaveBeenCalled();
        expect(applicationWindow.activateTabByName).toHaveBeenCalledWith('pat', true);
    });

    test('finds the application behind a finder iframe in a native appointment', () => {
        const applicationWindow = {
            navigateTab: jest.fn((url, name, afterLoad) => afterLoad()),
            activateTabByName: jest.fn()
        };
        const context = {top: {opener: {closed: false, top: applicationWindow}}};
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/interface/new/new.php', jest.fn());

        expect(applicationWindow.navigateTab).toHaveBeenCalled();
        expect(applicationWindow.activateTabByName).toHaveBeenCalledWith('pat', true);
    });

    test('continues to the appointment opener when the top window is inaccessible', () => {
        const applicationWindow = {navigateTab: jest.fn()};
        const context = {
            get top() {
                throw new DOMException('Blocked', 'SecurityError');
            },
            opener: {closed: false, top: applicationWindow}
        };
        const helper = loadHelper({});

        expect(helper.findApplicationWindow(context)).toBe(applicationWindow);
    });

    test('continues when a candidate navigateTab property is inaccessible', () => {
        const inaccessibleWindow = {
            get navigateTab() {
                throw new DOMException('Blocked', 'SecurityError');
            }
        };
        const applicationWindow = {navigateTab: jest.fn()};
        const context = {
            top: inaccessibleWindow,
            opener: {closed: false, top: applicationWindow}
        };
        const helper = loadHelper({});

        expect(helper.findApplicationWindow(context)).toBe(applicationWindow);
    });

    test('continues to the top-level opener when the direct opener is inaccessible', () => {
        const applicationWindow = {navigateTab: jest.fn()};
        const context = {
            top: {opener: {closed: false, top: applicationWindow}},
            get opener() {
                throw new DOMException('Blocked', 'SecurityError');
            }
        };
        const helper = loadHelper({});

        expect(helper.findApplicationWindow(context)).toBe(applicationWindow);
    });

    test('falls back to the finder when every application candidate is inaccessible', () => {
        const context = {
            get top() {
                throw new DOMException('Blocked', 'SecurityError');
            },
            get opener() {
                throw new DOMException('Severed', 'SecurityError');
            },
            location: {assign: jest.fn()},
            restoreSession: jest.fn()
        };
        const closeFinder = jest.fn();
        const helper = loadHelper({});

        helper.openAddPatient(context, '/interface/new/new.php', closeFinder);

        expect(context.location.assign).toHaveBeenCalledWith('/interface/new/new.php');
        expect(context.restoreSession).toHaveBeenCalledTimes(1);
        expect(closeFinder).not.toHaveBeenCalled();
    });

    test('reuses the current dialog when no application tabs API exists', () => {
        const context = {
            top: {},
            location: {assign: jest.fn()},
            restoreSession: jest.fn()
        };
        const closeFinder = jest.fn();
        const helper = loadHelper(context);

        helper.openAddPatient(context, '/interface/new/new.php', closeFinder);

        expect(context.location.assign).toHaveBeenCalledWith('/interface/new/new.php');
        expect(context.restoreSession).toHaveBeenCalledTimes(1);
        expect(closeFinder).not.toHaveBeenCalled();
    });
});

describe('calendar patient finder legacy redirect removal', () => {
    test('appointment and finder no longer contain the opener submit/redirect chain', () => {
        const calendarDirectory = path.resolve(__dirname, '../../interface/main/calendar');
        const appointmentSource = fs.readFileSync(path.join(calendarDirectory, 'add_edit_event.php'), 'utf8');
        const finderSource = fs.readFileSync(path.join(calendarDirectory, 'find_patient_popup.php'), 'utf8');

        expect(appointmentSource).not.toContain('resname');
        expect(appointmentSource).not.toContain('opener.document.location');
        expect(finderSource).not.toContain('opener.document.theform');
    });
});
