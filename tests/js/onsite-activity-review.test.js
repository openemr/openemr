/**
 * @jest-environment jsdom
 */

const fs = require('fs');
const path = require('path');

describe('portal dashboard document review', () => {
    let activityHelpers;
    let save;
    let dialogOptions;
    let modalHide;
    let alert;

    beforeEach(() => {
        save = jest.fn();
        dialogOptions = null;
        modalHide = jest.fn();
        alert = jest.spyOn(global.window, 'alert').mockImplementation(() => {});

        class OnsitePortalActivityModel {
            constructor(attributes) {
                this.attributes = attributes;
            }

            save(attributes, options) {
                save(this.attributes, attributes, options);
            }
        }

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
            '42'
        );
        activityHelpers = global.window.__OE_ONSITE_ACTIVITY_TEST__;
    });

    afterEach(() => {
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

        expect(save).toHaveBeenCalledTimes(1);
        expect(save.mock.calls[0][0]).toEqual({id: 17});
        expect(save.mock.calls[0][1]).toMatchObject({
            status: 'closed',
            pendingAction: 'completed',
            actionUser: '42'
        });
        expect(save.mock.calls[0][1].actionTakenTime).toBeInstanceOf(Date);
        expect(save.mock.calls[0][2].wait).toBe(true);
        expect(doneButton.disabled).toBe(true);

        save.mock.calls[0][2].success();

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
        save.mock.calls[0][2].error(null, {});

        expect(doneButton.disabled).toBe(false);
        expect(alert).toHaveBeenCalledWith('Could not complete review');
        expect(modalHide).not.toHaveBeenCalled();
    });
});
