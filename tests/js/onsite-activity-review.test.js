/**
 * @jest-environment jsdom
 */

const fs = require('fs');
const path = require('path');
const Backbone = require('backbone');

describe('portal dashboard document review', () => {
    let activityHelpers;
    let requests;
    let dialogOptions;
    let modalHide;
    let alert;

    beforeEach(() => {
        requests = [];
        jest.spyOn(Backbone, 'ajax').mockImplementation((options) => {
            requests.push(options);
            return {};
        });
        dialogOptions = null;
        modalHide = jest.fn();
        alert = jest.spyOn(global.window, 'alert').mockImplementation(() => {});

        const OnsitePortalActivityModel = Backbone.Model.extend({
            urlRoot: 'api/onsiteportalactivity',
            defaults: {
                patientId: 123,
                tableArgs: 456,
                status: 'pending',
                pendingAction: 'review'
            }
        });

        const model = {
            OnsiteActivityViewCollection: class {},
            OnsitePortalActivityModel
        };
        const source = fs.readFileSync(
            path.resolve('portal/patient/scripts/app/onsiteactivityviews.js'),
            'utf8'
        );
        new Function(
            'window',
            'model',
            'view',
            'app',
            '$',
            'jQuery',
            'dlgopen',
            'cuser',
            'authUserID',
            source
        )(
            global.window,
            model,
            {},
            {getErrorMessage: () => 'Could not complete review'},
            () => {},
            () => ({closest: () => ({modal: modalHide})}),
            (url, name, width, height, forceNewWindow, title, options) => {
                dialogOptions = options;
            },
            'reviewer.username',
            314
        );
        activityHelpers = global.window.__OE_ONSITE_ACTIVITY_TEST__;
    });

    afterEach(() => {
        Backbone.ajax.mockRestore();
        alert.mockRestore();
        delete global.window.__OE_ONSITE_ACTIVITY_TEST__;
    });

    test('Done marks the activity completed before closing the review dialog', () => {
        const activityView = {
            get: (attribute) => ({
                id: 17,
                patientId: 123,
                tableArgs: 456
            })[attribute]
        };
        const doneButton = {disabled: false};
        const modalContent = {};

        activityHelpers.showDocumentModal(activityView);
        const done = dialogOptions.buttons[1];
        done.click.call(modalContent, {currentTarget: doneButton});

        expect(requests).toHaveLength(1);
        const request = requests[0];
        expect(request.type).toBe('PUT');
        const payload = JSON.parse(request.data);
        expect(payload).toMatchObject({
            status: 'closed',
            pendingAction: 'completed',
            actionUser: 314
        });
        expect(new Date(payload.actionTakenTime).toString()).not.toBe('Invalid Date');
        expect(Object.keys(payload).sort()).toEqual([
            'actionTakenTime',
            'actionUser',
            'pendingAction',
            'status'
        ]);
        expect(doneButton.disabled).toBe(true);

        request.success({});

        expect(modalHide).toHaveBeenCalledWith('hide');
    });

    test('Done reports a failed completion and allows retrying', () => {
        const activityView = {
            get: (attribute) => ({
                id: 17,
                patientId: 123,
                tableArgs: 456
            })[attribute]
        };
        const doneButton = {disabled: false};

        activityHelpers.showDocumentModal(activityView);
        dialogOptions.buttons[1].click.call({}, {currentTarget: doneButton});
        requests[0].error({});

        expect(doneButton.disabled).toBe(false);
        expect(alert).toHaveBeenCalledWith('Could not complete review');
        expect(modalHide).not.toHaveBeenCalled();
    });
});
