/**
 * @jest-environment jsdom
 */

/**
 * Tests for the portal/patient API client (portal/patient/scripts/portal-api.js) and the shared
 * page helpers (portal/patient/scripts/portal-page.js), which replace Backbone and underscore.
 *
 * The requests must match what the Backbone models sent, because the server and outside callers
 * depend on them: relative paths, query values sent empty when missing, the CSRF token on writes
 * only, and a save body that is the record merged with the changes.
 *
 * Run with: npm run test:js -- tests/js/portal-patient-api.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

function load(file, names) {
    const src = fs.readFileSync(path.resolve(__dirname, '../../portal/patient/scripts', file), 'utf8');
    return new Function(`${src}\nreturn {${names.join(', ')}};`)();
}

const {model} = load('portal-api.js', ['model']);
const {app, view} = load('portal-page.js', ['app', 'view']);

/**
 * A fetch stand-in that answers each call with the next response and records the calls.
 */
function mockFetch(...responses) {
    const calls = [];
    global.fetch = jest.fn(async (url, init) => {
        calls.push({url, init});
        const {status = 200, body = {}} = responses.shift() ?? {};
        const text = typeof body === 'string' ? body : JSON.stringify(body);
        return {ok: status >= 200 && status < 300, status, text: async () => text};
    });
    return calls;
}

beforeEach(() => {
    window.portalApiCsrfToken = 'token-123';
    localStorage.clear();
    global.jsText = (s) => s;
});

afterEach(() => {
    delete global.fetch;
});

describe('model.request', () => {
    test('a read sends no CSRF token and no body', async () => {
        const calls = mockFetch({body: {id: '1'}});

        const result = await model.request('GET', 'api/onsitedocument/1');

        expect(result).toEqual({id: '1'});
        expect(calls[0].url).toBe('api/onsitedocument/1');
        expect(calls[0].init.method).toBe('GET');
        expect(calls[0].init.headers['X-CSRF-Token']).toBeUndefined();
        expect(calls[0].init.body).toBeUndefined();
    });

    test('a write sends the CSRF token and a JSON body', async () => {
        const calls = mockFetch({body: {}});

        await model.request('PUT', 'api/onsitedocument/1', {body: {denialReason: 'Open'}});

        expect(calls[0].init.headers['X-CSRF-Token']).toBe('token-123');
        expect(calls[0].init.headers['Content-Type']).toBe('application/json');
        expect(JSON.parse(calls[0].init.body)).toEqual({denialReason: 'Open'});
    });

    test('a write without a token on the page sends an empty one', async () => {
        delete window.portalApiCsrfToken;
        const calls = mockFetch({body: {}});

        await model.request('DELETE', 'api/onsitedocument/1');

        expect(calls[0].init.headers['X-CSRF-Token']).toBe('');
    });

    test('query values are encoded, and a missing value is sent empty', async () => {
        const calls = mockFetch({body: []});

        await model.request('GET', 'api/onsitedocuments', {
            params: {filter: 'a&b c', page: 1, showActive: false, recid: null, doc: undefined},
        });

        expect(calls[0].url).toBe('api/onsitedocuments?filter=a%26b+c&page=1&showActive=false&recid=&doc=');
    });

    test('a refused call rejects with the status and the raw body', async () => {
        mockFetch({status: 401, body: '{"success":false,"message":"No"}'});

        const error = await model.request('GET', 'api/patient/1').catch((e) => e);

        expect(error).toBeInstanceOf(model.PortalApiError);
        expect(error.status).toBe(401);
        expect(error.responseText).toBe('{"success":false,"message":"No"}');
        expect(app.getErrorMessage(error)).toBe('{"success":false,"message":"No"}');
    });

    test('a body that is not JSON rejects, even with status 200', async () => {
        mockFetch({status: 200, body: '<b>Fatal error</b>'});

        const error = await model.request('GET', 'api/patient/1').catch((e) => e);

        expect(error).toBeInstanceOf(model.PortalApiError);
        expect(error.responseText).toBe('<b>Fatal error</b>');
    });
});

describe('model.Record', () => {
    test('a new record has its defaults, no id, and the collection path', () => {
        const doc = new model.OnsiteDocumentModel();

        expect(doc.isNew()).toBe(true);
        expect(doc.id).toBeNull();
        expect(doc.url()).toBe('api/onsitedocument');
        expect(doc.get('denialReason')).toBe('New');
        expect(doc.get('createDate')).toBeInstanceOf(Date);
    });

    test('a record with an id addresses itself by it', () => {
        const doc = new model.OnsiteDocumentModel({id: '12'});

        expect(doc.isNew()).toBe(false);
        expect(doc.url()).toBe('api/onsitedocument/12');
    });

    test('a profile starts with every field empty and today as its dates', () => {
        const today = new Date().toISOString().slice(0, 10);
        const patient = new model.PatientModel();

        expect(patient.get('fname')).toBe('');
        expect(patient.get('date')).toBe(today);
        expect(patient.get('regdate')).toBe(today);
        expect(Object.keys(patient.attributes)).toHaveLength(53);
    });

    test('fetch sends the query and keeps fields the response leaves out', async () => {
        const calls = mockFetch({body: {id: '5', fname: 'Ann'}});
        const patient = new model.PatientModel({id: '5', lname: 'Lee'});

        await patient.fetch({patientId: '5'});

        expect(calls[0].url).toBe('api/patient/5?patientId=5');
        expect(patient.get('fname')).toBe('Ann');
        expect(patient.get('lname')).toBe('Lee');
    });

    test('saving a new record posts it, then takes the server copy', async () => {
        const calls = mockFetch({body: {id: '9', denialReason: 'Open'}});
        const doc = new model.OnsiteDocumentModel({docType: 'Intake'});

        await doc.save({denialReason: 'Editing', pid: '3'});

        expect(calls[0].init.method).toBe('POST');
        expect(calls[0].url).toBe('api/onsitedocument');
        const body = JSON.parse(calls[0].init.body);
        expect(body.docType).toBe('Intake');
        expect(body.denialReason).toBe('Editing');
        expect(body.pid).toBe('3');
        expect(doc.id).toBe('9');
        expect(doc.get('denialReason')).toBe('Open');
        expect(doc.get('pid')).toBe('3');
    });

    test('saving an existing record puts it to its own path', async () => {
        const calls = mockFetch({body: {id: '9'}});
        const doc = new model.OnsiteDocumentModel({id: '9'});

        await doc.save({denialReason: 'In Review'});

        expect(calls[0].init.method).toBe('PUT');
        expect(calls[0].url).toBe('api/onsitedocument/9');
    });

    test('a changed path is used for the save, as the profile review does', async () => {
        const calls = mockFetch({body: {id: '5'}});
        const patient = new model.PatientModel({id: '5'});
        patient.urlRoot = 'api/portalpatient';

        await patient.save({fname: 'Ann'});

        expect(calls[0].url).toBe('api/portalpatient/5');
    });

    test('a refused save leaves the record unchanged', async () => {
        mockFetch({status: 401, body: '{"errors":{"fname":"Required"}}'});
        const patient = new model.PatientModel({id: '5', fname: 'Ann'});

        await expect(patient.save({fname: ''})).rejects.toBeInstanceOf(model.PortalApiError);
        expect(patient.get('fname')).toBe('Ann');
    });

    test('destroy deletes an existing record and skips a new one', async () => {
        const calls = mockFetch({body: {}});

        await new model.OnsiteDocumentModel().destroy();
        await new model.OnsiteDocumentModel({id: '4'}).destroy();

        expect(calls).toHaveLength(1);
        expect(calls[0].init.method).toBe('DELETE');
        expect(calls[0].url).toBe('api/onsitedocument/4');
    });
});

describe('model.List', () => {
    test('a paged response sets the pager figures and keeps the server order', async () => {
        const calls = mockFetch({
            body: {
                rows: [{id: '3'}, {id: '1'}],
                totalResults: 12,
                totalPages: 2,
                currentPage: 1,
                pageSize: 10,
                orderBy: 'CreateDate',
                orderDesc: true,
            },
        });
        const docs = new model.OnsiteDocumentCollection();

        await docs.fetch({page: 1});

        expect(calls[0].url).toBe('api/onsitedocuments?page=1');
        expect(docs.models.map((d) => d.id)).toEqual(['3', '1']);
        expect(docs.first()).toBeInstanceOf(model.OnsiteDocumentModel);
        expect([docs.totalResults, docs.totalPages, docs.currentPage, docs.pageSize]).toEqual([12, 2, 1, 10]);
        expect([docs.orderBy, docs.orderDesc]).toEqual(['CreateDate', true]);
    });

    test('a bare array is one page of every row', async () => {
        mockFetch({body: [{id: '1'}, {id: '2'}, {id: '3'}]});
        const views = new model.OnsiteActivityViewCollection();

        await views.fetch();

        expect([views.length, views.totalResults, views.totalPages, views.currentPage]).toEqual([3, 3, 1, 1]);
    });

    test('get finds a record by an id given as a string or a number', async () => {
        mockFetch({body: [{id: 7, docType: 'Intake'}]});
        const docs = new model.OnsiteDocumentCollection();
        await docs.fetch();

        expect(docs.get('7').get('docType')).toBe('Intake');
        expect(docs.get(7).get('docType')).toBe('Intake');
        expect(docs.get('8')).toBeUndefined();
    });

    test('fetching again updates the records already held, and reports a change', async () => {
        mockFetch({body: [{id: '1', denialReason: 'Open'}]}, {body: [{id: '1', denialReason: 'In Review'}]});
        const docs = new model.OnsiteDocumentCollection();
        await docs.fetch();
        const held = docs.get('1');

        await docs.refetch();

        expect(docs.get('1')).toBe(held);
        expect(held.get('denialReason')).toBe('In Review');
        expect(docs.collectionHasChanged).toBe(true);
    });

    test('add appends a record once, and not a second copy of an id it holds', () => {
        const docs = new model.OnsiteDocumentCollection();
        const doc = new model.OnsiteDocumentModel({id: '2'});

        expect(docs.add(doc)).toBe(true);
        expect(docs.add(doc)).toBe(false);
        expect(docs.add(new model.OnsiteDocumentModel({id: '2'}))).toBe(false);
        expect(docs.length).toBe(1);
    });

    test('remove takes out a record it holds', () => {
        const docs = new model.OnsiteDocumentCollection();
        const doc = new model.OnsiteDocumentModel({id: '2'});
        docs.add(doc);

        expect(docs.remove(new model.OnsiteDocumentModel({id: '3'}))).toBe(false);
        expect(docs.remove(doc)).toBe(true);
        expect(docs.length).toBe(0);
    });
});

describe('app', () => {
    test('escapeHtml escapes as underscore did', () => {
        expect(app.escapeHtml(`<a href="x" title='y'>&\`</a>`))
            .toBe('&lt;a href=&quot;x&quot; title=&#x27;y&#x27;&gt;&amp;&#x60;&lt;/a&gt;');
        expect(app.escapeHtml(null)).toBe('');
        expect(app.escapeHtml(undefined)).toBe('');
        expect(app.escapeHtml(0)).toBe('0');
    });

    test('getErrorMessage is empty without a response body', () => {
        expect(app.getErrorMessage(null)).toBe('');
        expect(app.getErrorMessage(new TypeError('Failed to fetch'))).toBe('');
    });
});

describe('view', () => {
    test('a single page has no pager', () => {
        expect(view.pagination({totalPages: 1, currentPage: 1}).childNodes).toHaveLength(0);
    });

    test('the pager marks the current page and pages past ten', () => {
        document.body.replaceChildren(view.pagination({totalPages: 30, currentPage: 12}));
        const buttons = [...document.querySelectorAll('.pageButton')].map((a) => a.id);

        const labels = [...document.querySelectorAll('.pageButton')].map((a) => a.textContent);

        expect(buttons[0]).toBe('page-6');
        expect(labels[0]).toBe('«');
        expect(buttons[1]).toBe('page-7');
        expect(buttons[buttons.length - 1]).toBe('page-17');
        expect(labels[labels.length - 1]).toBe('»');
        expect(document.querySelector('li.active a').id).toBe('page-12');
        expect(document.querySelector('.pagination').className).toBe('pagination float-right');
    });

    test('the top pager is placed above the list', () => {
        document.body.replaceChildren(view.pagination({totalPages: 2, currentPage: 1}, true));

        expect(document.querySelector('.pagination').className).toBe('pagination float-left m-0 mb-1');
    });

    test('pager figures that are not numbers add no markup', () => {
        document.body.replaceChildren(view.pagination({totalPages: '2"><img src=x>', currentPage: 1}));

        expect(document.querySelector('img')).toBeNull();
        expect(document.getElementById('page-2')).not.toBeNull();
    });

    test('renderTemplate replaces the container with a copy of the template', () => {
        document.body.innerHTML = '<template id="t"><p class="x">Hi</p></template><div id="c"><span>old</span></div>';

        view.renderTemplate('t', 'c');
        view.renderTemplate('t', 'c');

        expect(document.getElementById('c').innerHTML).toBe('<p class="x">Hi</p>');
        expect(document.getElementById('t').content.childNodes).toHaveLength(1);
    });

    test('the active filter reflects the saved choice', () => {
        localStorage.setItem('showActive', 'true');

        expect(view.activeFilter(true).querySelector('#active-checkbox').checked).toBe(true);
        expect(view.activeFilter(true).textContent).toContain('(Show Active Status)');
        expect(view.activeFilter(false).querySelector('#active-checkbox').checked).toBe(false);

        localStorage.setItem('showActive', 'false');
        expect(view.activeFilter(false).textContent).toContain('(Show All)');
    });
});
