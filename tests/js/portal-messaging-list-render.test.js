/**
 * @jest-environment jsdom
 */

/**
 * DOM-level tests for renderMessageList() in portal/messaging/js/messages.js.
 * Regression for #15264: a checkbox must reflect item._deleted so a selection
 * survives the row rebuild that happens on every page change. The pure-helper
 * tests cannot catch this because they never render the list into a DOM.
 *
 * Run: npm run test:js -- tests/js/portal-messaging-list-render.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const DOMPurify = require('dompurify');
global.window.DOMPurify = DOMPurify(global.window);
global.DOMPurify = global.window.DOMPurify;

function loadModule(relativePath) {
    const src = fs.readFileSync(path.resolve(__dirname, '../../', relativePath), 'utf8');
    new Function('window', 'document', 'DOMPurify', src)(
        global.window,
        global.window.document,
        global.window.DOMPurify
    );
}

global.window.OE_MESSAGES_CONFIG = {
    inboxItems: [],
    userproper: 'Test User',
    isPortal: 1,
    isDashboard: false,
    authrecips: [],
    csrf: 'test-csrf-token',
    strings: {
        forwardedRe: 'Fwd: ', confirmOne: 'Archive?', confirmAll: 'Archive selected?',
        sendingToSelf: 'Sending to yourself!', conversationFrom: 'Conversation from',
        regarding: 'regarding', onPrep: 'on', archiveTitle: 'Archive this',
        composeReplyTitle: 'Reply', composeNewTitle: 'Compose'
    }
};
global.window.$ = () => ({ on: () => {}, modal: () => {}, summernote: () => {} });
global.$ = global.window.$;
global.window.Audio = function () { return {}; };

loadModule('portal/messaging/js/messages.js');
const { renderMessageList, state } = global.window.__OE_MESSAGES_TEST__;

function makeItems(n) {
    return Array.from({ length: n }, (_, i) => ({
        id: String(i + 1), mail_chain: String(i + 1), message_status: 'Read',
        date: '2026-01-01', sender_name: 'Sender', recipient_name: 'Recip',
        title: 'T' + i, body: '<p>body</p>'
    }));
}

function renderList(pagedItems, currentPage) {
    state.selected = null;
    state.pagedItems = pagedItems;
    state.currentPage = currentPage;
    renderMessageList();
    return document.querySelector('#messageListTable tbody');
}

beforeEach(() => {
    document.body.innerHTML = '<div id="messageListTable"><table><tbody></tbody></table></div>';
});

describe('messages.js renderMessageList checkbox state (#15264)', () => {
    test('renders an unchecked box for an unselected item', () => {
        const tbody = renderList([makeItems(3)], 0);
        const boxes = tbody.querySelectorAll('.item-checkbox');
        expect(boxes).toHaveLength(3);
        expect(boxes[0].checked).toBe(false);
    });

    test('the change handler records the selection on the item', () => {
        const items = makeItems(3);
        const tbody = renderList([items], 0);
        const box = tbody.querySelectorAll('.item-checkbox')[1];
        box.checked = true;
        box.dispatchEvent(new Event('change'));
        expect(items[1]._deleted).toBe(true);
        box.checked = false;
        box.dispatchEvent(new Event('change'));
        expect(items[1]._deleted).toBe(false);
    });

    test('a selection survives the page-change rebuild (the #15264 repro)', () => {
        const items = makeItems(25);
        const pages = [items.slice(0, 20), items.slice(20)];
        let tbody = renderList(pages, 0);
        const box = tbody.querySelectorAll('.item-checkbox')[3];
        box.checked = true;
        box.dispatchEvent(new Event('change'));
        expect(items[3]._deleted).toBe(true);

        renderList(pages, 1);              // next page rebuilds the rows
        tbody = renderList(pages, 0);      // come back
        expect(tbody.querySelectorAll('.item-checkbox')[3].checked).toBe(true);
    });

    test('only selected items render checked after a rebuild', () => {
        const items = makeItems(3);
        items[0]._deleted = true;
        const tbody = renderList([items], 0);
        const boxes = tbody.querySelectorAll('.item-checkbox');
        expect(boxes[0].checked).toBe(true);
        expect(boxes[1].checked).toBe(false);
        expect(boxes[2].checked).toBe(false);
    });
});