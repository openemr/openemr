/**
 * @jest-environment jsdom
 */

/**
 * The Patient Finder heading's search box (dynamic_finder.js) against a DataTables stub that
 * holds each request until the test answers it, to control the order replies arrive in.
 *
 * Run with: npm run test:js -- tests/js/patient-finder-search.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const $ = require('jquery');
global.$ = global.jQuery = window.$ = window.jQuery = $;

const SEARCH_DELAY_MS = 300;
const source = fs.readFileSync(
    path.resolve(__dirname, '../../interface/main/finder/dynamic_finder.js'),
    'utf8'
);

// Each table the finder makes, with the term of every draw it asked for. A draw's reply
// arrives only when the test calls answer(); like DataTables, a reply then sets the rows.
function stubDataTables() {
    const tables = [];
    $.fn.dataTable = function (options) {
        const table = {searches: []};
        const ask = (term) => {
            options.fnServerParams([]);
            table.searches.push(term);
        };
        const api = {
            pending: '',
            search(term) {
                api.pending = term;
                return api;
            },
            draw() {
                ask(api.pending);
                return api;
            }
        };
        table.api = () => api;
        table.answer = () => options.drawCallback();
        // DataTables asks for the first page as soon as the table is made.
        ask(options.search.search);
        tables.push(table);
        return table;
    };
    return tables;
}

function setUpPage() {
    document.body.innerHTML = `
        <div id="finder-search">
            <input id="finder-search-input">
        </div>
        <div id="finder-length-slot"></div>
        <button id="listTab"></button>
        <button id="recentTab"></button>
        <input type="checkbox" id="setting_search_type">
        <input type="checkbox" id="form_show_inactive">
        <table id="pt_table"><thead><tr id="advanced_search"><td><input></td></tr></thead></table>
        <table id="recent_table"><thead><tr id="recent_advanced_search"><td><input></td></tr></thead></table>
    `;
    window.finderConfig = {columns: [{key: 'lname', sortDir: 'asc'}]};
    delete window.finderCriteria;
}

// Load the finder and run its start-up, then return its Patient List table.
function startFinder() {
    const tables = stubDataTables();
    new Function('window', source)(window);
    jest.runOnlyPendingTimers();
    return tables[0];
}

// Type into the heading's search box and wait out the pause before the list reloads.
function typeSearch(term) {
    const input = document.getElementById('finder-search-input');
    input.value = term;
    input.dispatchEvent(new Event('input'));
    jest.advanceTimersByTime(SEARCH_DELAY_MS);
}

// jQuery marks the document ready on a timer of its own; let that happen before the fake timers.
beforeAll(() => new Promise((resolve) => {
    $(resolve);
}));

beforeEach(() => {
    jest.useFakeTimers();
    setUpPage();
});

afterEach(() => {
    jest.useRealTimers();
});

test('typing a new search asks for it once the typing pauses', () => {
    const list = startFinder();
    list.answer();

    typeSearch('smi');

    expect(list.searches).toEqual(['', 'smi']);
});

test('a search the rows already show is not asked for again', () => {
    const list = startFinder();
    list.answer();
    typeSearch('smi');
    list.answer();

    // Typed on and back again within the pause: the box ends where the rows already are.
    const input = document.getElementById('finder-search-input');
    input.value = 'smit';
    input.dispatchEvent(new Event('input'));
    typeSearch('smi');

    expect(list.searches).toEqual(['', 'smi']);
});

test('going back to the shown search while another is on its way asks for it again', () => {
    const list = startFinder();
    list.answer();
    typeSearch('smi');
    list.answer();

    // "smit" is asked for and its reply has not arrived when the box goes back to "smi".
    typeSearch('smit');
    typeSearch('smi');

    // Asking again makes the "smit" reply the older draw, which DataTables drops.
    expect(list.searches).toEqual(['', 'smi', 'smit', 'smi']);
});

test('after a failed request the same search can be asked for again', () => {
    const list = startFinder();
    list.answer();
    typeSearch('smi');
    list.answer();

    // The "smit" request fails, so no reply sets the rows, which still show "smi".
    typeSearch('smit');
    typeSearch('smit');

    expect(list.searches).toEqual(['', 'smi', 'smit', 'smit']);
});
