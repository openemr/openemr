/**
 * @jest-environment jsdom
 */

/**
 * Calendar Visit Lists, appointment editor (library/js/calendarVisitListBlock.js): asking
 * before a Visit List block with patients is deleted or given another category.
 *
 * Run with: npm run test:js -- tests/js/calendar-visit-list-block.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const block = new Function(
    fs.readFileSync(path.resolve(__dirname, '../../library/js/calendarVisitListBlock.js'), 'utf8') +
    '\nreturn { pcVisitListBlockAsksLater, pcVisitListBlockConfirm };'
)();

const CONFIG = {
    categoryId: 21,
    messages: { all: '2 patients from today on.', future: '1 patient on 3/9 and later.' },
    deleteText: 'Deleting this event cannot be undone.',
};

function form(action, category, scope) {
    document.body.innerHTML = `
        <form>
            <input name="form_action" value="${action}">
            <select name="form_category"><option value="21">Visit List</option><option value="9">Office</option></select>
            <input name="recurr_affect" value="${scope}">
            <input name="form_save" type="button" disabled>
        </form>`;
    const f = document.forms[0];
    f.elements.form_category.value = category;
    return f;
}

describe('pcVisitListBlockConfirm', () => {
    let confirmSpy;

    beforeEach(() => {
        confirmSpy = jest.spyOn(window, 'confirm');
    });

    afterEach(() => {
        confirmSpy.mockRestore();
    });

    test('a block with no patients goes on without asking', () => {
        expect(block.pcVisitListBlockConfirm([], form('delete', '21', ''))).toBe(true);
        expect(block.pcVisitListBlockConfirm(undefined, form('delete', '21', ''))).toBe(true);
        expect(confirmSpy).not.toHaveBeenCalled();
    });

    test('a save that keeps the category goes on without asking', () => {
        expect(block.pcVisitListBlockConfirm(CONFIG, form('save', '21', 'all'))).toBe(true);
        expect(confirmSpy).not.toHaveBeenCalled();
    });

    test('a duplicate with another category goes on without asking', () => {
        expect(block.pcVisitListBlockConfirm(CONFIG, form('duplicate', '9', ''))).toBe(true);
        expect(confirmSpy).not.toHaveBeenCalled();
    });

    test('a delete of a block that does not repeat asks with the message for all dates', () => {
        confirmSpy.mockReturnValue(true);

        expect(block.pcVisitListBlockConfirm(CONFIG, form('delete', '21', ''))).toBe(true);
        expect(confirmSpy).toHaveBeenCalledWith('2 patients from today on.');
    });

    test('a category change asks with the message for the chosen scope', () => {
        confirmSpy.mockReturnValue(true);

        expect(block.pcVisitListBlockConfirm(CONFIG, form('save', '9', 'future'))).toBe(true);
        expect(confirmSpy).toHaveBeenCalledWith('1 patient on 3/9 and later.');
    });

    test('a delete of a scope with no patients asks the usual question', () => {
        confirmSpy.mockReturnValue(true);

        expect(block.pcVisitListBlockConfirm(CONFIG, form('delete', '21', 'current'))).toBe(true);
        expect(confirmSpy).toHaveBeenCalledWith('Deleting this event cannot be undone.');
    });

    test('a category change of a scope with no patients goes on without asking', () => {
        expect(block.pcVisitListBlockConfirm(CONFIG, form('save', '9', 'current'))).toBe(true);
        expect(confirmSpy).not.toHaveBeenCalled();
    });

    test('cancelling sets the form back', () => {
        confirmSpy.mockReturnValue(false);
        window.EnableForm = jest.fn();
        window.HideRecurrPopup = jest.fn();
        const f = form('delete', '21', 'future');

        expect(block.pcVisitListBlockConfirm(CONFIG, f)).toBe(false);
        expect(f.elements.recurr_affect.value).toBe('');
        expect(f.elements.form_action.value).toBe('');
        expect(f.elements.form_save.disabled).toBe(false);
        expect(window.EnableForm).toHaveBeenCalled();
        expect(window.HideRecurrPopup).toHaveBeenCalled();
        delete window.EnableForm;
        delete window.HideRecurrPopup;
    });

    test('cancelling works without the editor functions', () => {
        confirmSpy.mockReturnValue(false);

        expect(block.pcVisitListBlockConfirm(CONFIG, form('delete', '21', ''))).toBe(false);
    });
});

describe('pcVisitListBlockAsksLater', () => {
    test('only a block with patients asks later', () => {
        expect(block.pcVisitListBlockAsksLater(CONFIG)).toBe(true);
        expect(block.pcVisitListBlockAsksLater([])).toBe(false);
        expect(block.pcVisitListBlockAsksLater({ messages: {} })).toBe(false);
    });
});
