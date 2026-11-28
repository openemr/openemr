/**
 * @jest-environment jsdom
 */

/**
 * Flow Board (library/js/flowBoard.js): dates and times shown once, the To date kept on or
 * after the From date, the status and room menus, and clicks that open the appointment dialog.
 *
 * Run with: npm run test:js -- tests/js/flow-board.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const board = new Function(
    fs.readFileSync(path.resolve(__dirname, '../../library/js/flowBoard.js'), 'utf8') +
    '\nreturn { flowBoardMarkRepeats, flowBoardDateKey, flowBoardKeepRange, flowBoardRedrawChoice };'
)();

function row(date, time, hidden) {
    return `<tr${hidden ? ' style="display: none"' : ''}>
        <td data-flb-repeat="${date}"><span class="flb-when">${date}</span></td>
        <td data-flb-repeat="${date} ${time}"><span class="flb-when">${time}</span></td>
    </tr>`;
}

function shown(table) {
    return [...table.querySelectorAll('tbody tr')].map((tr) =>
        [...tr.querySelectorAll('[data-flb-repeat]')]
            .map((td) => (td.classList.contains('flb-repeat') ? '' : td.textContent.trim()))
            .join('|')
    );
}

describe('flowBoardMarkRepeats', () => {
    test('a date shows once per day and a time once per time', () => {
        document.body.innerHTML = `<table id="t"><tbody>
            ${row('2026-11-30', '08:00')}${row('2026-11-30', '08:00')}${row('2026-11-30', '08:30')}
            ${row('2026-12-01', '08:30')}${row('2026-12-01', '08:30')}
        </tbody></table>`;
        const table = document.getElementById('t');

        board.flowBoardMarkRepeats(table);

        expect(shown(table)).toEqual(['2026-11-30|08:00', '|', '|08:30', '2026-12-01|08:30', '|']);
    });

    test('rows hidden by a filter are skipped, and marks are cleared when they no longer repeat', () => {
        document.body.innerHTML = `<table id="t"><tbody>
            ${row('2026-11-30', '08:00')}${row('2026-11-30', '08:00')}
        </tbody></table>`;
        const table = document.getElementById('t');
        board.flowBoardMarkRepeats(table);

        table.querySelector('tr').style.display = 'none';
        board.flowBoardMarkRepeats(table);

        expect(shown(table)[1]).toBe('2026-11-30|08:00');
    });
});

describe('flowBoardDateKey and flowBoardKeepRange', () => {
    test.each([
        ['2026-11-30', 0, '20261130'],
        ['11/30/2026', 1, '20261130'],
        ['30/11/2026', 2, '20261130'],
        ['3/9/2026', 1, '20260309'],
        ['', 0, null],
        ['2026-11', 0, null],
    ])('%s in format %i', (value, format, expected) => {
        expect(board.flowBoardDateKey(value, format)).toBe(expected);
    });

    function inputs(from, to) {
        document.body.innerHTML = `<input id="f" value="${from}"><input id="t" value="${to}">`;
        return [document.getElementById('f'), document.getElementById('t')];
    }

    test('a To date before the From date becomes the From date', () => {
        const [from, to] = inputs('12/01/2026', '11/30/2026');

        expect(board.flowBoardKeepRange(from, to, 1)).toBe(true);
        expect(to.value).toBe('12/01/2026');
    });

    test.each([
        ['11/30/2026', '12/02/2026'],
        ['11/30/2026', '11/30/2026'],
        ['11/30/2026', ''],
    ])('From %s and To %s are left alone', (fromValue, toValue) => {
        const [from, to] = inputs(fromValue, toValue);

        expect(board.flowBoardKeepRange(from, to, 1)).toBe(false);
        expect(to.value).toBe(toValue);
    });

    test('changing either date keeps the range', () => {
        window.flowBoardConfig = { dateFormat: 0, menus: {}, text: { menuLabel: {} } };
        document.body.innerHTML = '<input id="form_from_date" value="2026-11-30"><input id="form_to_date" value="2026-11-30">';
        const from = document.getElementById('form_from_date');
        const to = document.getElementById('form_to_date');

        from.value = '2026-12-03';
        from.dispatchEvent(new Event('change', { bubbles: true }));
        expect(to.value).toBe('2026-12-03');

        to.value = '2026-12-01';
        to.dispatchEvent(new Event('change', { bubbles: true }));
        expect(to.value).toBe('2026-12-03');
    });
});

describe('menus and clicks', () => {
    beforeEach(() => {
        window.flowBoardConfig = {
            url: '/flow_board_change.php',
            csrfToken: 'token',
            dateFormat: 0,
            menus: {
                status: [{ id: '-', title: 'None' }, { id: '@', title: 'Arrived' }],
                room: [{ id: '', title: 'No room' }, { id: 1, title: 'Room 1' }],
            },
            text: { menuLabel: { status: 'Change status', room: 'Change room' }, failed: 'Not saved.' },
        };
        window.refreshMe = jest.fn();
        window.calendarpopup = jest.fn();
        window.alert = jest.fn();
        global.fetch = jest.fn(() => Promise.resolve({ ok: true, status: 200 }));
        document.body.innerHTML = `<table id="flb_table"><tbody><tr>
            <td class="flb-appt" data-flb-eid="768" data-flb-date="20261130"><span class="flb-when">08:00</span></td>
            <td><button class="flb-badge" data-flb-choice="status" data-flb-eid="768" data-flb-value="-">None</button></td>
            <td><button class="flb-badge" data-flb-choice="room" data-flb-eid="768" data-flb-value="">–</button></td>
        </tr></tbody></table>`;
    });

    afterEach(() => {
        document.querySelectorAll('.flb-menu').forEach((menu) => menu.remove());
        document.body.click();
    });

    function badge(kind) {
        return document.querySelector(`[data-flb-choice="${kind}"]`);
    }

    function items() {
        return [...document.querySelectorAll('.flb-menu .dropdown-item')];
    }

    test('a badge opens its menu with the current value marked, and closes it on a second click', () => {
        badge('status').click();

        expect(items().map((item) => item.textContent)).toEqual(['None', 'Arrived']);
        expect(document.querySelector('.flb-menu .active').textContent).toBe('None');
        expect(document.activeElement).toBe(items()[0]);
        expect(badge('status').getAttribute('aria-expanded')).toBe('true');

        badge('status').click();
        expect(items()).toEqual([]);
        expect(badge('status').getAttribute('aria-expanded')).toBe('false');
    });

    test('choosing a value saves it and redraws the board', async () => {
        badge('room').click();
        items()[1].click();
        await Promise.resolve();
        await Promise.resolve();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        const [url, options] = global.fetch.mock.calls[0];
        expect(url).toBe('/flow_board_change.php');
        expect(Object.fromEntries(options.body)).toEqual({ csrf_token_form: 'token', eid: '768', room: '1' });
        expect(window.refreshMe).toHaveBeenCalled();
        expect(items()).toEqual([]);
    });

    test('choosing the current value saves nothing', () => {
        badge('status').click();
        items()[0].click();

        expect(global.fetch).not.toHaveBeenCalled();
    });

    test('a failed save says so and leaves the board', async () => {
        global.fetch = jest.fn(() => Promise.resolve({ ok: false, status: 409 }));
        badge('status').click();
        items()[1].click();
        await new Promise((resolve) => setTimeout(resolve, 0));

        expect(window.alert).toHaveBeenCalledWith('Not saved.');
        expect(window.refreshMe).not.toHaveBeenCalled();
    });

    test('Escape closes the menu and returns to the badge; the arrows move between items', () => {
        badge('status').click();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown' }));
        expect(document.activeElement).toBe(items()[1]);
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown' }));
        expect(document.activeElement).toBe(items()[0]);

        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        expect(items()).toEqual([]);
        expect(document.activeElement).toBe(badge('status'));
    });

    test('a date, time or order cell opens the appointment dialog', () => {
        document.querySelector('.flb-when').click();

        expect(window.calendarpopup).toHaveBeenCalledWith('768', '20261130');
    });

    test('a redraw closes a menu whose badge is gone', () => {
        badge('status').click();
        document.getElementById('flb_table').innerHTML = '<tbody></tbody>';

        window.flowBoardRedrawn(document.getElementById('flb_table'));

        expect(items()).toEqual([]);
    });
});

describe('filter menus', () => {
    test('a changed filter menu is drawn clipped for one frame, then with its "…" again', () => {
        const frames = [];
        window.requestAnimationFrame = jest.fn((callback) => frames.push(callback));
        document.body.innerHTML = `<div class="flb-filters"><select id="form_apptcat">
            <option value="43">Initial psychiatric interview with mental status and evaluation (procedure)</option>
            <option value="1">No Show</option>
        </select></div><select id="elsewhere"><option>A</option></select>`;
        const menu = document.getElementById('form_apptcat');

        menu.value = '1';
        menu.dispatchEvent(new Event('change', { bubbles: true }));
        expect(menu.classList.contains('flb-redraw')).toBe(true);

        frames.forEach((callback) => callback());
        expect(menu.classList.contains('flb-redraw')).toBe(false);

        document.getElementById('elsewhere').dispatchEvent(new Event('change', { bubbles: true }));
        expect(frames).toHaveLength(1);
    });
});
