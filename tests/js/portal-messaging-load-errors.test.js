/**
 * @jest-environment jsdom
 */

/**
 * Secure messaging load-failure surfacing (#15266).
 *
 * The three loaders swallowed errors, so initData()'s catch was unreachable
 * and state.errorLoadingMessages was never read: a failed load left an empty
 * inbox with no error. deleteItem() also lacked a .catch while deleteMessage()
 * alerts and rethrows. These drive the real DOMContentLoaded path.
 *
 * Run: npm run test:js -- tests/js/portal-messaging-load-errors.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../portal/messaging/js/messages.js'),
    'utf8'
);

const MSG = {
    id: '1', mail_chain: '1', message_status: 'Read', date: '2026-01-01',
    sender_name: 'A', recipient_name: 'B', title: 'T', body: '<p>b</p>',
    sender_id: 'other', reply_mail_chain: '1'
};

function buildDom() {
    document.body.innerHTML = `
        <div id="loadingRow"></div>
        <div id="loadErrorRow" class="d-none"><button id="btnRetryLoad"></button></div>
        <div id="contentRow" class="d-none">
            <a id="navInbox"></a><a id="navSent"></a><a id="navAll"></a><a id="navTrash"></a>
            <button id="btnReadAll"></button><button id="btnBatchDelete"></button>
            <button id="btnPrev"></button><button id="btnNext"></button>
            <select id="selSendto"></select><select id="selForwardto"></select>
            <div id="messageListTable"><table><tbody></tbody></table></div>
            <div id="messageDetail"><div id="detailHeader"></div><table><tbody id="chainTbody"></tbody></table></div>
            <div id="pagingControls"><span id="pagingInfo"></span><div id="pagingButtons"></div></div>
        </div>`;
}

function setConfig(inbox) {
    window.OE_MESSAGES_CONFIG = {
        inboxItems: inbox, userproper: 'T', isPortal: '', isDashboard: 'admin',
        authrecips: [], csrf: 'tok',
        strings: {
            forwardedRe: 'Fwd: ', confirmOne: 'Archive?', confirmAll: 'Archive selected?',
            sendingToSelf: 'Self!', conversationFrom: 'From', regarding: 're', onPrep: 'on',
            archiveTitle: 'Archive', composeReplyTitle: 'Reply', composeNewTitle: 'Compose'
        }
    };
}

let fetchImpl;
function initModule() {
    const DOMPurify = require('dompurify');
    window.DOMPurify = DOMPurify(window);
    global.DOMPurify = window.DOMPurify;
    const $ = () => ({ on: () => {}, modal: () => {}, summernote: () => {}, data: () => {} });
    $.param = (o) => Object.keys(o).map((k) => k + '=' + o[k]).join('&');
    window.$ = $; global.$ = $;
    window.fetch = (u, o) => fetchImpl(u, o); global.fetch = window.fetch;
    window.alert = jest.fn(); window.confirm = jest.fn(() => true);
    window.eval(source);
    document.dispatchEvent(new Event('DOMContentLoaded'));
}

const flush = async () => {
    for (let i = 0; i < 8; i++) { await Promise.resolve(); }
    await new Promise((r) => setTimeout(r, 0));
};
const ok = (data) => Promise.resolve({ ok: true, text: () => Promise.resolve(JSON.stringify(data)) });
const hidden = (id) => document.getElementById(id).classList.contains('d-none');

describe('secure messaging load failures (#15266)', () => {
    beforeEach(() => {
        jest.spyOn(console, 'error').mockImplementation(() => {});
        buildDom();
        setConfig([]);
    });
    afterEach(() => { jest.restoreAllMocks(); });

    test('a failed load surfaces the error row and keeps content hidden', async () => {
        fetchImpl = () => Promise.reject(new Error('boom'));
        initModule();
        await flush();
        expect(hidden('loadErrorRow')).toBe(false);
        expect(hidden('contentRow')).toBe(true);
        expect(hidden('loadingRow')).toBe(true);
    });

    test('a successful load shows content and hides the error row', async () => {
        fetchImpl = () => ok([]);
        initModule();
        await flush();
        expect(hidden('contentRow')).toBe(false);
        expect(hidden('loadErrorRow')).toBe(true);
    });

    test('Retry re-runs the load and clears the error', async () => {
        fetchImpl = () => Promise.reject(new Error('boom'));
        initModule();
        await flush();
        expect(hidden('loadErrorRow')).toBe(false);
        fetchImpl = () => ok([]);
        document.getElementById('btnRetryLoad').dispatchEvent(new Event('click', { bubbles: true }));
        await flush();
        expect(hidden('loadErrorRow')).toBe(true);
        expect(hidden('contentRow')).toBe(false);
    });

    test('a failed archive alerts and leaves the message in the list', async () => {
        setConfig([MSG]);
        fetchImpl = (u, o) => (String(o.body).indexOf('task=delete') >= 0
            ? Promise.reject(new Error('nope'))
            : ok([MSG]));
        initModule();
        await flush();
        document.querySelector('#messageListTable .msg-click')
            .dispatchEvent(new Event('click', { bubbles: true }));
        await flush();
        const btn = document.querySelector('#chainTbody .btn-delete-item');
        expect(btn).not.toBeNull();
        btn.dispatchEvent(new Event('click', { bubbles: true }));
        await flush();
        expect(window.alert).toHaveBeenCalled();
        expect(document.querySelectorAll('#messageListTable .item-checkbox')).toHaveLength(1);
    });
});