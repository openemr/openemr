/**
 * @jest-environment jsdom
 */

/**
 * The Patient Finder's Filter panel (finder_criteria.js): drawing rows from the server's
 * criteria definitions, applying them as chips, the request parameter dynamic_finder.js
 * sends, and keeping a value the server would drop out of the filters in use.
 *
 * Run with: npm run test:js -- tests/js/patient-finder-criteria.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const $ = require('jquery');
global.$ = global.jQuery = window.$ = window.jQuery = $;

const source = fs.readFileSync(
    path.resolve(__dirname, '../../interface/main/finder/finder_criteria.js'),
    'utf8'
);

const operator = (key, label, input = 'none', min = 0, max = 0, unit = '') => ({key, label, input, min, max, unit});

// The shape PatientFinderCriteriaRegistry::definitions() gives the page.
const DEFINITIONS = [
    {
        key: 'sex',
        label: 'Sex',
        options: [{id: 'Female', title: 'Female'}, {id: 'Male', title: 'Male'}, {id: '__none__', title: 'Not recorded'}],
        operators: [operator('is', 'is', 'select')]
    },
    {
        key: 'age',
        label: 'Age',
        options: [],
        operators: [
            operator('between', 'between', 'range', 0, 150),
            operator('at_least', 'at least', 'number', 0, 150),
            operator('at_most', 'at most', 'number', 0, 150)
        ]
    },
    {
        key: 'insurance',
        label: 'Primary insurance',
        options: [],
        operators: [operator('has', 'has a current policy'), operator('none', 'has no current policy')]
    },
    {
        key: 'last_visit',
        label: 'Last visit',
        options: [],
        operators: [
            operator('within', 'within the last', 'number', 1, 3650, 'days'),
            operator('older', 'more than', 'number', 1, 3650, 'days ago'),
            operator('never', 'never')
        ]
    }
];

let hidden;
let redraws;

function startPanel(definitions = DEFINITIONS) {
    document.body.innerHTML = `
        <div class="dropdown" id="finder-filter">
            <button type="button" id="finder-filter-button"><span id="finder-filter-label" data-label="Filter">Filter</span></button>
            <div id="finder-filter-panel" data-add="Add filter" data-clear="Clear all" data-apply="Apply"
                data-remove="Remove" data-invalid="Enter whole numbers within the range shown."></div>
        </div>
        <div id="finder-chips" hidden></div>
    `;
    window.finderConfig = {criteria: definitions};
    hidden = 0;
    $.fn.dropdown = function (command) {
        if (command === 'hide') {
            hidden++;
        }
        return this;
    };
    redraws = 0;
    $(document).off('finder:criteria').on('finder:criteria', () => {
        redraws++;
    });
    new Function('window', source)(window);
    jest.runOnlyPendingTimers();
}

const panel = () => $('#finder-filter-panel');
const rows = () => panel().children('.form-row');
const chips = () => $('#finder-chips .badge').map((i, chip) => $(chip).children('span').first().text()).get();
const button = (text) => panel().find('button').filter((i, el) => $(el).text() === text);
const params = () => window.finderCriteria.params();
const sent = () => JSON.parse(params()[0].value);

function openPanel() {
    $('#finder-filter').trigger('show.bs.dropdown');
}

// The row's field, operator and value controls, in order.
function controls(row) {
    return rows().eq(row).find('select, input');
}

function choose(element, value) {
    $(element).val(value).trigger('change');
}

function type(element, value) {
    element.value = value;
    element.dispatchEvent(new Event('input'));
}

// jQuery marks the document ready on a timer of its own; let that happen before the fake timers.
beforeAll(() => new Promise((resolve) => {
    $(resolve);
}));

beforeEach(() => {
    jest.useFakeTimers();
});

afterEach(() => {
    jest.useRealTimers();
});

test('with no criteria the Filter button is removed and nothing is sent', () => {
    startPanel([]);

    expect($('#finder-filter').length).toBe(0);
    expect(params()).toEqual([]);
});

test('nothing is sent and no chips show until a filter is applied', () => {
    startPanel();

    expect(params()).toEqual([]);
    expect($('#finder-chips').prop('hidden')).toBe(true);
    expect($('#finder-filter-label').text()).toBe('Filter');
});

test('the panel opens on one row for the first criterion with its first choice', () => {
    startPanel();
    openPanel();

    expect(rows().length).toBe(1);
    const [field, op, value] = controls(0).get();
    expect(field.value).toBe('sex');
    expect(op.value).toBe('is');
    expect(value.value).toBe('Female');
});

test('applying a filter sends it, shows its chip and redraws the list', () => {
    startPanel();
    openPanel();
    choose(controls(0).get(2), 'Male');
    button('Apply').trigger('click');

    expect(sent()).toEqual([{key: 'sex', operator: 'is', value: 'Male'}]);
    expect(chips()).toEqual(['Sex is Male']);
    expect($('#finder-chips').prop('hidden')).toBe(false);
    expect($('#finder-filter-label').text()).toBe('Filter (1)');
    expect(redraws).toBe(1);
    expect(hidden).toBe(1);
});

test('Add filter adds the next criterion not in use, until every one is', () => {
    startPanel();
    openPanel();
    button('+ Add filter').trigger('click');

    expect(controls(1).get(0).value).toBe('age');
    expect(controls(1).get(1).value).toBe('between');
    expect(rows().eq(1).find('input[type=number]').length).toBe(2);
    // A row's field menu leaves out the criteria other rows use.
    expect(rows().eq(1).find('select').first().children().map((i, o) => o.value).get())
        .toEqual(['age', 'insurance', 'last_visit']);

    button('+ Add filter').trigger('click');
    button('+ Add filter').trigger('click');
    expect(rows().length).toBe(4);
    expect(button('+ Add filter').prop('disabled')).toBe(true);
});

test('each kind of value describes itself on its chip', () => {
    startPanel();
    openPanel();
    button('+ Add filter').trigger('click');
    button('+ Add filter').trigger('click');
    button('+ Add filter').trigger('click');
    const [low, high] = rows().eq(1).find('input').get();
    type(low, '65');
    type(high, '80');
    choose(controls(3).get(1), 'within');
    type(controls(3).get(2), '90');
    button('Apply').trigger('click');

    expect(chips()).toEqual([
        'Sex is Female',
        'Age between 65–80',
        'Primary insurance has a current policy',
        'Last visit within the last 90 days'
    ]);
    expect(sent()).toEqual([
        {key: 'sex', operator: 'is', value: 'Female'},
        {key: 'age', operator: 'between', value: ['65', '80']},
        {key: 'insurance', operator: 'has', value: ''},
        {key: 'last_visit', operator: 'within', value: '90'}
    ]);
});

test('an operator that takes no value applies without one', () => {
    startPanel();
    openPanel();
    choose(controls(0).get(0), 'last_visit');
    choose(controls(0).get(1), 'never');
    button('Apply').trigger('click');

    expect(rows().length).toBe(1);
    expect(sent()).toEqual([{key: 'last_visit', operator: 'never', value: ''}]);
    expect(chips()).toEqual(['Last visit never']);
});

test('changing the operator resets the value to what the new one takes', () => {
    startPanel();
    openPanel();
    choose(controls(0).get(0), 'age');
    expect(rows().eq(0).find('input[type=number]').length).toBe(2);

    choose(controls(0).get(1), 'at_least');
    expect(rows().eq(0).find('input[type=number]').length).toBe(1);
    expect(rows().eq(0).find('input').get(0).value).toBe('');
});

test('a number outside the range keeps the panel open and applies nothing', () => {
    startPanel();
    openPanel();
    choose(controls(0).get(0), 'age');
    choose(controls(0).get(1), 'at_most');
    type(controls(0).get(2), '151');
    button('Apply').trigger('click');

    expect(panel().find('.alert-danger').text()).toBe('Enter whole numbers within the range shown.');
    expect(params()).toEqual([]);
    expect(redraws).toBe(0);
    expect(hidden).toBe(0);
});

test('a number that is not whole is refused like one out of range', () => {
    startPanel();
    openPanel();
    choose(controls(0).get(0), 'last_visit');
    type(controls(0).get(2), '1.5');
    button('Apply').trigger('click');

    expect(panel().find('.alert-danger').length).toBe(1);
    expect(params()).toEqual([]);
});

test('a row left unfinished is dropped and the rest apply', () => {
    startPanel();
    openPanel();
    button('+ Add filter').trigger('click');
    type(rows().eq(1).find('input').get(0), '18');
    button('Apply').trigger('click');

    expect(sent()).toEqual([{key: 'sex', operator: 'is', value: 'Female'}]);
});

test('reopening the panel edits a copy of the filters in use', () => {
    startPanel();
    openPanel();
    button('Apply').trigger('click');

    openPanel();
    choose(controls(0).get(2), 'Male');
    // Closed without Apply: the filter in use is unchanged.
    expect(sent()).toEqual([{key: 'sex', operator: 'is', value: 'Female'}]);

    openPanel();
    expect(controls(0).get(2).value).toBe('Female');
});

test("a row's remove button and Clear all empty the panel", () => {
    startPanel();
    openPanel();
    button('+ Add filter').trigger('click');
    rows().eq(0).find('button[aria-label=Remove]').trigger('click');
    expect(rows().length).toBe(1);
    expect(controls(0).get(0).value).toBe('age');

    button('Clear all').trigger('click');
    expect(rows().length).toBe(0);
    button('Apply').trigger('click');
    expect(params()).toEqual([]);
});

test("a chip's remove button and the chips' Clear all take filters out of use", () => {
    startPanel();
    openPanel();
    button('+ Add filter').trigger('click');
    choose(controls(1).get(1), 'at_least');
    type(controls(1).get(2), '40');
    button('Apply').trigger('click');
    expect(redraws).toBe(1);

    $('#finder-chips .badge').first().find('button').trigger('click');
    expect(sent()).toEqual([{key: 'age', operator: 'at_least', value: '40'}]);
    expect($('#finder-filter-label').text()).toBe('Filter (1)');
    expect(redraws).toBe(2);

    $('#finder-chips > button').trigger('click');
    expect(params()).toEqual([]);
    expect($('#finder-chips').prop('hidden')).toBe(true);
    expect($('#finder-filter-label').text()).toBe('Filter');
    expect(redraws).toBe(3);
});

test('clicks and arrow keys inside the panel stay in the panel', () => {
    startPanel();
    openPanel();
    let reached = 0;
    $(document.body).on('click keydown', () => {
        reached++;
    });

    panel().trigger('click');
    panel().trigger($.Event('keydown', {key: 'ArrowDown'}));
    panel().trigger($.Event('keydown', {key: 'ArrowUp'}));
    expect(reached).toBe(0);

    panel().trigger($.Event('keydown', {key: 'Escape'}));
    expect(reached).toBe(1);
    $(document.body).off('click keydown');
});
