/**
 * @jest-environment jsdom
 */

/**
 * Calendar Visit Lists (library/js/calendarVisitList.js): a block keeps the height of its
 * time range, shows the patients that fit and "+N more", and opens the whole list over the
 * grid without running past its bottom. jsdom has no layout, so each block here gets a
 * simple one: the header is 24px and each shown row (or marker row) 20px.
 *
 * Run with: npm run test:js -- tests/js/calendar-visit-list.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const visitList = new Function(
    fs.readFileSync(path.resolve(__dirname, '../../library/js/calendarVisitList.js'), 'utf8') +
    '\nreturn { pcFitVisitList, pcSetUpVisitLists, pcSetUpVisitListsForPrint };'
)();

const HEADER = 24;
const ROW = 20;
const LABELS = { more: '+%s more', fewer: 'Show fewer' };

// The rows and marker that take up room: hidden rows don't, unless the list is open.
function contentHeight(block) {
    const open = block.classList.contains('visit-list-expanded');
    const rows = [...block.querySelectorAll('.visit-list-entries > li')].filter(
        (li) => open || !li.classList.contains('visit-list-overflow')
    );
    return HEADER + rows.length * ROW;
}

// A block as _visit_list.html.twig draws it, with its time range's height and position.
function block({ rows, height, top = 0, eid = '40', print = false }) {
    const el = document.createElement('div');
    el.className = 'event_visit_list event';
    el.dataset.eid = eid;
    el.style.top = top + 'px';
    el.style.height = height + 'px';
    el.innerHTML = '<div class="visit-list-header"><span class="visit-list-title">Visit List</span>' +
        (print ? '' : '<button type="button" class="visit-list-add">+</button>') + '</div>' +
        '<ol class="visit-list-entries">' +
        Array.from({ length: rows }, (_, i) => `<li class="visit-list-entry">#${i + 1}</li>`).join('') +
        '</ol>';
    let clientHeight = height;
    Object.defineProperty(el, 'clientHeight', { get: () => clientHeight });
    Object.defineProperty(el, 'scrollHeight', { get: () => contentHeight(el) });
    Object.defineProperty(el, 'offsetTop', { get: () => parseFloat(el.style.top) || 0 });
    el.getBoundingClientRect = () => {
        const shownTop = parseFloat(el.style.top) || 0;
        let shownHeight = el.style.height === 'auto' ? contentHeight(el) : parseFloat(el.style.height);
        if (el.style.maxHeight !== '') {
            shownHeight = Math.min(shownHeight, parseFloat(el.style.maxHeight));
        }
        return { top: shownTop, bottom: shownTop + shownHeight };
    };
    el.setClientHeight = (value) => {
        clientHeight = value;
    };
    return el;
}

// The grid that clips an opened list, from 0 to its height.
function grid(height, ...blocks) {
    const el = document.createElement('div');
    el.id = 'bigCal';
    el.getBoundingClientRect = () => ({ top: 0, bottom: height });
    blocks.forEach((b) => el.appendChild(b));
    document.body.appendChild(el);
    return el;
}

const hiddenRows = (b) => b.querySelectorAll('.visit-list-overflow').length;
const moreButton = (b) => b.querySelector('.visit-list-more-button');

afterEach(() => {
    document.body.innerHTML = '';
    delete global.ResizeObserver;
});

describe('fitting a list into its block', () => {
    test('a list that fits is left as it is', () => {
        const b = block({ rows: 2, height: 80 });
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(hiddenRows(b)).toBe(0);
        expect(moreButton(b)).toBeNull();
        expect(b.classList.contains('visit-list-fitted')).toBe(false);
    });

    test('shows the rows that fit and +N more for the rest', () => {
        const b = block({ rows: 5, height: HEADER + 2 * ROW });
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(hiddenRows(b)).toBe(4);
        expect(moreButton(b).textContent).toBe('+4 more');
        expect(moreButton(b).closest('.visit-list-entries')).not.toBeNull();
        expect(b.classList.contains('visit-list-fitted')).toBe(true);
        expect(b.scrollHeight).toBeLessThanOrEqual(b.clientHeight);
    });

    test('keeps a row that reaches only into the bottom padding (plus 1px of rounding)', () => {
        const b = block({ rows: 5, height: HEADER + 3 * ROW - 3 });
        b.style.paddingBottom = '2px';
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(hiddenRows(b)).toBe(3);
        expect(moreButton(b).textContent).toBe('+3 more');
        expect(b.classList.contains('visit-list-fitted')).toBe(true);
    });

    test('hides a row that runs past the bottom padding and the 1px allowance', () => {
        const b = block({ rows: 5, height: HEADER + 3 * ROW - 4 });
        b.style.paddingBottom = '2px';
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(hiddenRows(b)).toBe(4);
    });

    test('puts +N more in the header when not even one row fits', () => {
        const b = block({ rows: 5, height: HEADER + ROW - 4 });
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(hiddenRows(b)).toBe(5);
        const button = moreButton(b);
        expect(button.textContent).toBe('+5 more');
        expect(button.parentElement.classList.contains('visit-list-header')).toBe(true);
        expect(button.nextElementSibling.classList.contains('visit-list-add')).toBe(true);
        expect(b.querySelector('.visit-list-more')).toBeNull();
        expect(b.classList.contains('visit-list-fitted')).toBe(true);
    });

    test('keeps the scrollbar on a block too short even for its header', () => {
        const b = block({ rows: 3, height: 18 });
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        expect(moreButton(b).textContent).toBe('+3 more');
        expect(b.classList.contains('visit-list-fitted')).toBe(false);
    });

    test('fits a calendar loaded in a hidden tab once it is shown', () => {
        let observed = null;
        global.ResizeObserver = class {
            constructor(callback) {
                this.callback = callback;
                this.disconnected = false;
            }
            observe() {
                observed = this;
            }
            disconnect() {
                this.disconnected = true;
            }
        };
        const b = block({ rows: 5, height: HEADER + 2 * ROW });
        b.setClientHeight(0);
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);
        expect(hiddenRows(b)).toBe(0);

        b.setClientHeight(HEADER + 2 * ROW);
        observed.callback();

        expect(hiddenRows(b)).toBe(4);
        expect(observed.disconnected).toBe(true);
    });
});

describe('opening the whole list', () => {
    test('+N more opens it and Show fewer, Escape or a click elsewhere closes it', () => {
        const b = block({ rows: 5, height: HEADER + 2 * ROW, top: 40 });
        grid(400, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        moreButton(b).click();
        expect(b.classList.contains('visit-list-expanded')).toBe(true);
        expect(b.style.height).toBe('auto');
        expect(moreButton(b).getAttribute('aria-expanded')).toBe('true');
        expect(moreButton(b).textContent).toBe('Show fewer');

        moreButton(b).click();
        expect(b.classList.contains('visit-list-expanded')).toBe(false);
        expect(b.style.height).toBe(HEADER + 2 * ROW + 'px');
        expect(moreButton(b).textContent).toBe('+4 more');

        moreButton(b).click();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        expect(b.classList.contains('visit-list-expanded')).toBe(false);

        moreButton(b).click();
        document.body.click();
        expect(b.classList.contains('visit-list-expanded')).toBe(false);
    });

    test('a click another control stops still closes an open list', () => {
        const first = block({ rows: 5, height: HEADER + 2 * ROW, eid: '40' });
        const second = block({ rows: 5, height: HEADER + 2 * ROW, top: 100, eid: '50' });
        grid(400, first, second);
        visitList.pcSetUpVisitLists(document, LABELS);
        // As the calendar's header and patient controls do.
        second.querySelector('.visit-list-title').addEventListener('click', (e) => e.stopPropagation());

        moreButton(first).click();
        second.querySelector('.visit-list-title').click();
        expect(first.classList.contains('visit-list-expanded')).toBe(false);
    });

    test('opening one list closes another', () => {
        const first = block({ rows: 5, height: HEADER + 2 * ROW, eid: '40' });
        const second = block({ rows: 5, height: HEADER + 2 * ROW, top: 100, eid: '50' });
        grid(400, first, second);
        visitList.pcSetUpVisitLists(document, LABELS);

        moreButton(first).click();
        moreButton(second).click();
        expect(first.classList.contains('visit-list-expanded')).toBe(false);
        expect(second.classList.contains('visit-list-expanded')).toBe(true);
    });

    test('moves up rather than run past the bottom of the grid', () => {
        // Open, the header, 5 rows and Show fewer take 144px; from 150px that would end at
        // 294px in a 200px grid, so it moves up 94px.
        const b = block({ rows: 5, height: HEADER + 2 * ROW, top: 150 });
        grid(200, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        moreButton(b).click();
        expect(b.style.top).toBe('56px');
        expect(b.style.maxHeight).toBe('');

        moreButton(b).click();
        expect(b.style.top).toBe('150px');
    });

    test('scrolls inside when taller than the grid', () => {
        const b = block({ rows: 5, height: HEADER + 2 * ROW, top: 50 });
        grid(100, b);
        visitList.pcSetUpVisitLists(document, LABELS);

        moreButton(b).click();
        expect(b.style.top).toBe('0px');
        expect(b.style.maxHeight).toBe('100px');

        moreButton(b).click();
        expect(b.style.top).toBe('50px');
        expect(b.style.maxHeight).toBe('');
    });
});

describe('day print', () => {
    function fullList(eid) {
        const el = document.createElement('div');
        el.className = 'visit-list-full event_visit_list';
        el.dataset.eid = eid;
        return el;
    }

    test('notes where a list continues and keeps every full list', () => {
        const short = block({ rows: 5, height: HEADER + 2 * ROW, eid: '40', print: true });
        const long = block({ rows: 2, height: 200, eid: '50', print: true });
        grid(400, short, long);
        const section = document.createElement('div');
        section.className = 'visit-list-continued';
        section.append(fullList('40'), fullList('50'));
        document.body.appendChild(section);

        visitList.pcSetUpVisitListsForPrint(document, '+%s · Continued below the schedule');

        expect(short.querySelector('.visit-list-more-label').textContent).toBe('+4 · Continued below the schedule');
        expect(long.querySelector('.visit-list-more-label')).toBeNull();
        expect([...section.querySelectorAll('.visit-list-full')].map((el) => el.dataset.eid)).toEqual(['40', '50']);
    });
});
