/**
 * @jest-environment jsdom
 */

/**
 * Tests for the insurance editor's prompt to reopen charges (ReopenChargesPanel) and the two
 * InsurancePolicyService calls behind it.
 *
 * Run with: npm run test:js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

// The modules are loaded in the browser with cache-busted dynamic imports; evaluate the classes
// without a bundler, as insurance-policy-sort.test.js does.
function loadClass(file, name) {
    const src = fs.readFileSync(path.resolve(__dirname, '../../library/js/oeUI/insurance/' + file), 'utf8')
        .split('\n')
        .filter((line) => !line.includes('await import('))
        .join('\n')
        .replace('export class ' + name, 'class ' + name);
    return new Function(src + '\nreturn ' + name + ';')();
}

const ReopenChargesPanel = loadClass('ReopenChargesPanel.js', 'ReopenChargesPanel');
const InsurancePolicyService = loadClass('InsurancePolicyService.js', 'InsurancePolicyService');

// The parts of #reopen-charges-panel in insurance_edit.html.twig the panel works with.
const PANEL_HTML = `
<div id="reopen-charges-panel" class="alert alert-warning d-none" data-no-payer="None" data-select-label="Reopen encounter">
    <div class="reopen-charges-offer">
        <table>
            <thead><tr><th><input type="checkbox" class="reopen-charges-all" checked /></th></tr></thead>
            <tbody></tbody>
        </table>
        <p class="reopen-charges-failed d-none">failed</p>
        <button type="button" class="btn-reopen-charges">Reopen Selected</button>
        <button type="button" class="btn-reopen-charges-dismiss">Not Now</button>
    </div>
    <div class="reopen-charges-done d-none">
        <p class="reopen-charges-done-all">all</p>
        <p class="reopen-charges-done-some d-none">some</p>
        <button type="button" class="btn-reopen-charges-dismiss">Close</button>
    </div>
</div>`;

const primary = { uuid: 'uuid-primary', type: 'primary' };
const secondary = { uuid: 'uuid-secondary', type: 'secondary' };
const candidate = (encounter, date = '2026-09-01', last_billed_to = 'OLD PAYER', balance = '100.00') =>
    ({ encounter, date, last_billed_to, balance });

// By default the server reopens every encounter it is sent.
function setUp(candidatesByUuid, reopenCharges = jest.fn((policyUuid, encounters) => Promise.resolve(encounters))) {
    document.body.innerHTML = PANEL_HTML;
    const service = {
        getReopenCandidates: jest.fn((policy) => Promise.resolve(candidatesByUuid[policy.uuid] || [])),
        reopenCharges,
    };
    const element = document.getElementById('reopen-charges-panel');
    return { service, element, panel: new ReopenChargesPanel(service, element) };
}

const cellText = (element) => Array.from(element.querySelectorAll('tbody tr'))
    .map((row) => Array.from(row.querySelectorAll('td')).slice(1).map((cell) => cell.textContent));
// Lets the promise chain behind a click settle.
const settle = () => new Promise((resolve) => setTimeout(resolve, 0));

beforeEach(() => {
    // Local date parts, as the panel builds each date at local midnight.
    const pad = (value) => String(value).padStart(2, '0');
    window.oeFormatters = {
        I18NDateFormat: (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`,
    };
    window.restoreSession = jest.fn();
    jest.spyOn(console, 'error').mockImplementation(() => {});
});

afterEach(() => {
    jest.restoreAllMocks();
});

describe('ReopenChargesPanel.offer', () => {
    test('lists the saved policy\'s visits and shows the panel', async () => {
        const { element, panel } = setUp({
            'uuid-primary': [candidate(12), candidate(15, '2026-09-08', null, '25.00')],
        });

        await panel.offer([primary]);

        expect(element.classList.contains('d-none')).toBe(false);
        expect(cellText(element)).toEqual([
            ['2026-09-01', '12', 'OLD PAYER', '100.00'],
            ['2026-09-08', '15', 'None', '25.00'],
        ]);
        expect(element.querySelector('.btn-reopen-charges').disabled).toBe(false);
    });

    test('stays hidden when there is nothing to reopen', async () => {
        const { element, panel } = setUp({});

        await panel.offer([primary]);

        expect(element.classList.contains('d-none')).toBe(true);
    });

    test('hides an earlier offer when the next save has nothing to reopen', async () => {
        const { service, element, panel } = setUp({ 'uuid-primary': [candidate(12)] });
        await panel.offer([primary]);
        service.getReopenCandidates.mockImplementation(() => Promise.resolve([]));

        await panel.offer([primary]);

        expect(element.classList.contains('d-none')).toBe(true);
    });

    test('lists a visit two policies both claim once, for the first policy', async () => {
        const { element, panel } = setUp({
            'uuid-primary': [candidate(12)],
            'uuid-secondary': [candidate(12), candidate(13)],
        });

        await panel.offer([primary, secondary]);

        const boxes = Array.from(element.querySelectorAll('.reopen-charges-encounter'));
        expect(boxes.map((box) => [box.value, box.dataset.policyUuid])).toEqual([
            ['12', 'uuid-primary'],
            ['13', 'uuid-secondary'],
        ]);
    });

    test('a lookup that answers after a newer offer does not replace it', async () => {
        const { service, element, panel } = setUp({});
        let answerFirst;
        service.getReopenCandidates
            .mockImplementationOnce(() => new Promise((resolve) => { answerFirst = resolve; }))
            .mockImplementationOnce(() => Promise.resolve([candidate(20)]));

        const first = panel.offer([primary]);
        await panel.offer([secondary]);
        answerFirst([candidate(12)]);
        await first;

        expect(cellText(element).map((cells) => cells[1])).toEqual(['20']);
    });

    test('a failed lookup leaves the panel hidden and does not reject', async () => {
        const { service, element, panel } = setUp({});
        service.getReopenCandidates.mockImplementation(() => Promise.reject(new Error('offline')));

        await expect(panel.offer([primary])).resolves.toBeUndefined();

        expect(element.classList.contains('d-none')).toBe(true);
    });

    test('does nothing without the panel markup (a user who may not bill)', async () => {
        const service = { getReopenCandidates: jest.fn(), reopenCharges: jest.fn() };
        const panel = new ReopenChargesPanel(service, null);

        await panel.offer([primary]);
        panel.hide();

        expect(service.getReopenCandidates).not.toHaveBeenCalled();
    });
});

describe('ReopenChargesPanel selection', () => {
    test('the header checkbox selects none or all, and Reopen Selected needs a selection', async () => {
        const { element, panel } = setUp({ 'uuid-primary': [candidate(12), candidate(15)] });
        await panel.offer([primary]);
        const all = element.querySelector('.reopen-charges-all');
        const button = element.querySelector('.btn-reopen-charges');
        const boxes = Array.from(element.querySelectorAll('.reopen-charges-encounter'));

        all.checked = false;
        all.dispatchEvent(new Event('change'));
        expect(boxes.map((box) => box.checked)).toEqual([false, false]);
        expect(button.disabled).toBe(true);

        boxes[1].checked = true;
        boxes[1].dispatchEvent(new Event('change'));
        expect(button.disabled).toBe(false);
    });

    test('Not Now hides the panel without reopening anything', async () => {
        const { service, element, panel } = setUp({ 'uuid-primary': [candidate(12)] });
        await panel.offer([primary]);

        element.querySelector('.reopen-charges-offer .btn-reopen-charges-dismiss').click();

        expect(element.classList.contains('d-none')).toBe(true);
        expect(service.reopenCharges).not.toHaveBeenCalled();
    });
});

describe('ReopenChargesPanel reopen', () => {
    test('sends the checked visits per policy and shows the confirmation', async () => {
        const { service, element, panel } = setUp({
            'uuid-primary': [candidate(12), candidate(15)],
            'uuid-secondary': [candidate(13)],
        });
        await panel.offer([primary, secondary]);
        const boxes = Array.from(element.querySelectorAll('.reopen-charges-encounter'));
        boxes[1].checked = false;

        element.querySelector('.btn-reopen-charges').click();
        await settle();

        expect(service.reopenCharges.mock.calls).toEqual([
            ['uuid-primary', [12]],
            ['uuid-secondary', [13]],
        ]);
        expect(element.querySelector('.reopen-charges-offer').classList.contains('d-none')).toBe(true);
        expect(element.querySelector('.reopen-charges-done').classList.contains('d-none')).toBe(false);
    });

    test('says so when the server reopened only some of the checked visits', async () => {
        const { element, panel } = setUp(
            { 'uuid-primary': [candidate(12), candidate(15)] },
            jest.fn(() => Promise.resolve([12]))
        );
        await panel.offer([primary]);

        element.querySelector('.btn-reopen-charges').click();
        await settle();

        expect(element.querySelector('.reopen-charges-done').classList.contains('d-none')).toBe(false);
        expect(element.querySelector('.reopen-charges-done-all').classList.contains('d-none')).toBe(true);
        expect(element.querySelector('.reopen-charges-done-some').classList.contains('d-none')).toBe(false);
    });

    test('a failure keeps the list and says so', async () => {
        const { element, panel } = setUp(
            { 'uuid-primary': [candidate(12)] },
            jest.fn(() => Promise.reject(new Error('500')))
        );
        await panel.offer([primary]);

        element.querySelector('.btn-reopen-charges').click();
        await settle();

        expect(element.querySelector('.reopen-charges-failed').classList.contains('d-none')).toBe(false);
        expect(element.querySelector('.reopen-charges-offer').classList.contains('d-none')).toBe(false);
        expect(element.querySelector('.btn-reopen-charges').disabled).toBe(false);
    });

    test('when one policy\'s request fails, the visits already reopened leave the list', async () => {
        const { service, element, panel } = setUp(
            { 'uuid-primary': [candidate(12)], 'uuid-secondary': [candidate(13)] },
            jest.fn((policyUuid, encounters) => policyUuid === 'uuid-primary'
                ? Promise.resolve(encounters)
                : Promise.reject(new Error('500')))
        );
        await panel.offer([primary, secondary]);

        element.querySelector('.btn-reopen-charges').click();
        await settle();

        expect(cellText(element).map((cells) => cells[1])).toEqual(['13']);
        expect(element.querySelector('.reopen-charges-failed').classList.contains('d-none')).toBe(false);
        // Trying again sends only what is left.
        element.querySelector('.btn-reopen-charges').click();
        await settle();
        expect(service.reopenCharges.mock.calls[2]).toEqual(['uuid-secondary', [13]]);
    });

    test('a reopen that answers after a newer offer leaves that offer on screen', async () => {
        let answer;
        const { element, panel } = setUp(
            { 'uuid-primary': [candidate(12)], 'uuid-secondary': [candidate(13)] },
            jest.fn(() => new Promise((resolve) => { answer = resolve; }))
        );
        await panel.offer([primary]);
        element.querySelector('.btn-reopen-charges').click();

        await panel.offer([secondary]);
        answer([12]);
        await settle();

        expect(element.querySelector('.reopen-charges-offer').classList.contains('d-none')).toBe(false);
        expect(element.querySelector('.reopen-charges-done').classList.contains('d-none')).toBe(true);
        expect(cellText(element).map((cells) => cells[1])).toEqual(['13']);
    });
});

describe('InsurancePolicyService reopen-charges calls', () => {
    const service = (canReopenCharges) =>
        new InsurancePolicyService('token', '/apis/default/api/', [], ['primary'], 'puuid', canReopenCharges);
    const response = (ok, body) => Promise.resolve({ ok, status: ok ? 200 : 401, json: () => Promise.resolve(body) });

    test('getReopenCandidates asks for the policy\'s visits', async () => {
        window.fetch = jest.fn(() => response(true, { data: { encounters: [candidate(12)] } }));

        await expect(service(true).getReopenCandidates(primary)).resolves.toEqual([candidate(12)]);

        expect(window.fetch.mock.calls[0][0])
            .toBe('/apis/default/api/patient/puuid/insurance/uuid-primary/$reopen-charges');
    });

    test('getReopenCandidates asks nothing for a user who may not bill, or an unsaved policy', async () => {
        window.fetch = jest.fn();

        await expect(service(false).getReopenCandidates(primary)).resolves.toEqual([]);
        await expect(service(true).getReopenCandidates({ uuid: null })).resolves.toEqual([]);

        expect(window.fetch).not.toHaveBeenCalled();
    });

    test('reopenCharges posts the encounters and returns the ones reopened', async () => {
        window.fetch = jest.fn(() => response(true, { data: { reopened: [12] } }));

        await expect(service(true).reopenCharges('uuid-primary', [12, 15])).resolves.toEqual([12]);

        const [url, options] = window.fetch.mock.calls[0];
        expect(url).toBe('/apis/default/api/patient/puuid/insurance/uuid-primary/$reopen-charges');
        expect(options.method).toBe('POST');
        expect(options.headers.apicsrftoken).toBe('token');
        expect(JSON.parse(options.body)).toEqual({ encounters: [12, 15] });
    });

    test('reopenCharges rejects when the server refuses', async () => {
        window.fetch = jest.fn(() => response(false, {}));

        await expect(service(true).reopenCharges('uuid-primary', [12])).rejects.toThrow('status 401');
    });
});
