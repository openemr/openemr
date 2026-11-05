/**
 * @jest-environment jsdom
 *
 * Tests for the Folders view of the Documents page (library/js/documents_folder_view.js):
 * the folder counts, filtering and sorting, and the page wiring against the markup of
 * templates/documents/general_list.html.
 *
 * Run with: npm run test:js
 */

const fs = require('fs');
const path = require('path');

const src = fs.readFileSync(path.resolve(__dirname, '../../library/js/documents_folder_view.js'), 'utf8');

const categories = [
    { id: 2, name: 'Studies', parent: 0, path: 'Studies' },
    { id: 3, name: 'Lab Report', parent: 2, path: 'Studies › Lab Report' },
    { id: 4, name: 'Imaging', parent: 2, path: 'Studies › Imaging' },
    { id: 5, name: 'Medical Record', parent: 0, path: 'Medical Record' },
    { id: 6, name: 'Outside Records', parent: 5, path: 'Medical Record › Outside Records' },
    { id: 7, name: 'Photographs', parent: 0, path: 'Photographs' }
];

const doc = (id, name, category_id, date, mimetype = 'application/pdf', size = 2048) => ({
    id,
    name,
    category_id,
    path: categories.find((c) => c.id === category_id).path,
    date,
    size,
    mimetype,
    view_url: '/v/' + id,
    retrieve_url: '/r/' + id + '&as_file=false',
    download_url: '/r/' + id + '&as_file=true'
});

const documents = [
    doc(1, 'CBC panel.pdf', 3, '2026-10-22'),
    doc(2, 'Lipid panel.pdf', 3, '2026-08-14'),
    doc(3, 'Chest X-ray.zip', 4, '2026-11-02', 'application/dicom+zip', 18400000),
    doc(4, 'Discharge summary with a very long file name that must stay fully readable in the list.pdf', 6, '2026-10-19'),
    doc(5, 'Rash.jpg', 4, '2026-10-30', 'image/jpeg', 2100000)
];

function load(win) {
    new Function('window', src)(win);
    return win.OeDocumentsFolderView;
}

describe('pure helpers', () => {
    const api = load({});

    test('folder counts include subfolders and empty folders are counted', () => {
        const tree = api.buildFolderTree(categories, documents);
        expect(tree.map((n) => [n.name, n.own, n.count])).toEqual([
            ['Studies', 0, 4],
            ['Medical Record', 0, 1],
            ['Photographs', 0, 0]
        ]);
        expect(tree[0].children.map((n) => [n.name, n.count])).toEqual([['Lab Report', 2], ['Imaging', 2]]);
        expect(api.countEmptyFolders(tree)).toBe(1);
    });

    test('a folder selects itself and its subfolders', () => {
        const tree = api.buildFolderTree(categories, documents);
        expect(Object.keys(api.folderIds(api.findFolder(tree, 2))).sort()).toEqual(['2', '3', '4']);
        expect(api.findFolder(tree, 99)).toBeNull();
    });

    test('filtering by folder and by search text', () => {
        const tree = api.buildFolderTree(categories, documents);
        const studies = api.folderIds(api.findFolder(tree, 2));
        expect(api.filterDocuments(documents, studies, '').map((d) => d.id)).toEqual([1, 2, 3, 5]);
        expect(api.filterDocuments(documents, null, 'LIPID').map((d) => d.id)).toEqual([2]);
        expect(api.filterDocuments(documents, null, 'outside records').map((d) => d.id)).toEqual([4]);
        expect(api.filterDocuments(documents, null, '  ').length).toBe(5);
    });

    test('sorting by date, name, folder and type', () => {
        const ids = (key) => api.sortDocuments(documents, key).map((d) => d.id);
        expect(ids('date_desc')).toEqual([3, 5, 1, 4, 2]);
        expect(ids('date_asc')).toEqual([2, 4, 1, 5, 3]);
        expect(ids('name')).toEqual([1, 3, 4, 2, 5]);
        expect(ids('folder')).toEqual([4, 3, 5, 1, 2]);
        expect(ids('type')).toEqual([5, 1, 4, 2, 3]);
        expect(documents.map((d) => d.id)).toEqual([1, 2, 3, 4, 5]);
    });

    test('preview kind and size text', () => {
        expect(api.previewKind(documents[0])).toBe('pdf');
        expect(api.previewKind(documents[2])).toBe('dicom');
        expect(api.previewKind(documents[4])).toBe('image');
        expect(api.previewKind({ name: 'scan.tif', mimetype: 'image/tiff' })).toBe('other');
        expect(api.previewKind({ name: 'a.DCM', mimetype: '' })).toBe('dicom');
        expect(api.formatSize(500)).toBe('500 B');
        expect(api.formatSize(2048)).toBe('2 KB');
        expect(api.formatSize(18400000)).toBe('17.5 MB');
    });
});

describe('list height', () => {
    test('is clamped to the allowed range and falls back for bad values', () => {
        const api = load(window);
        const limits = api.listHeightLimits;
        expect(api.clampListHeight(limits.min - 500)).toBe(limits.min);
        expect(api.clampListHeight(limits.max + 500)).toBe(limits.max);
        expect(api.clampListHeight(300)).toBe(300);
        expect(api.clampListHeight('abc')).toBe(limits.fallback);
    });
});

describe('page wiring', () => {
    const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

    function setUp(stored = {}) {
        document.body.innerHTML = `
            <div class="container-fluid">
                <button type="button" id="docViewFolders"></button>
                <button type="button" id="docViewClassic"></button>
                <div id="docUploadWrap" hidden>
                    <button type="button" id="docUploadBtn"></button>
                    <div id="docUploadPopover" hidden>
                        <select id="docUploadFolder"><option value="2">Lab Report</option></select>
                        <div id="docDropArea"></div>
                        <input type="file" id="docFileInput" multiple />
                        <div id="docFileNames"></div>
                        <input type="file" id="docDicomInput" />
                        <input type="text" id="docDestination" />
                        <button type="button" id="docUploadCancel"></button>
                        <button type="button" id="docUploadSubmit"></button>
                        <div id="docUploadStatus"></div>
                    </div>
                </div>
                <div id="documentsFolderView" hidden>
                    <div id="docFolderCol" class="col-lg-3">
                        <div id="docFolderTree"></div>
                        <button type="button" id="docEmptyToggle"></button>
                    </div>
                    <div id="docMainCol" class="col-lg-9">
                        <div id="docListPane">
                            <button type="button" id="docFolderToggle" aria-expanded="true"></button>
                            <input type="search" id="docSearch" />
                            <select id="docSort">
                                <option value="date_desc"></option><option value="name"></option>
                            </select>
                            <div id="docCrumb"></div>
                            <div id="docListBox">
                                <table><tbody id="docRows"></tbody></table>
                                <p id="docNoResults" hidden></p>
                            </div>
                            <div id="docResize" role="separator" aria-orientation="horizontal" tabindex="0"></div>
                        </div>
                        <div id="docPreviewCard">
                            <button type="button" id="docOpen" disabled></button>
                            <button type="button" id="docDownload" disabled></button>
                            <button type="button" id="docMaximize" aria-pressed="false"></button>
                            <button type="button" id="docMaxBack" hidden></button>
                            <div id="docPreviewTitle"></div>
                            <div id="docPreviewMeta"></div>
                            <div id="docPreviewBody"></div>
                        </div>
                    </div>
                </div>
                <div id="documentsClassic"></div>
            </div>`;
        window.fetch = jest.fn(() => Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ categories, documents })
        }));
        window.localStorage.clear();
        Object.keys(stored).forEach((key) => window.localStorage.setItem(key, stored[key]));
        window.oeDocumentsFolderViewConfig = {
            webroot: '/openemr',
            patientId: '7',
            csrfToken: 'tok',
            dataUrl: '/openemr/controller.php?document&list_data&patient_id=7',
            storageKey: 'oeDocumentsView:test',
            defaultView: 'folders',
            forceClassic: false,
            i18n: {
                allDocuments: 'All documents',
                showEmpty: 'Show %d empty folders',
                hideEmpty: 'Hide empty folders',
                selectDocument: 'Select a document',
                noPreview: 'No preview',
                filesSelected: '%d file(s) selected',
                uploadFailed: 'Upload failed',
                uploaded: 'Uploaded',
                hideFolders: 'Hide folders',
                showFolders: 'Show folders'
            }
        };
        new Function('window', src)(window);
    }

    test('opens in the Folders view, lists only folders in use and fires the render event', async () => {
        const rendered = jest.fn();
        document.addEventListener('documents:list-rendered', rendered);
        setUp();
        await flush();
        await flush();
        expect(window.fetch.mock.calls[0][0]).toContain('list_data&patient_id=7');
        expect(document.getElementById('documentsFolderView').hidden).toBe(false);
        expect(document.getElementById('documentsClassic').hidden).toBe(true);
        const labels = [...document.querySelectorAll('#docFolderTree button')].map((b) => b.textContent);
        expect(labels).toEqual(['All documents5', 'Studies4', 'Lab Report2', 'Imaging2', 'Medical Record1', 'Outside Records1']);
        expect(document.getElementById('docEmptyToggle').textContent).toBe('Show 1 empty folders');
        const rows = document.querySelectorAll('#docRows tr');
        expect(rows.length).toBe(5);
        expect(rows[0].getAttribute('data-document-id')).toBe('3');
        expect(rows[0].getAttribute('data-document-mime')).toBe('application/dicom+zip');
        expect(rows[0].querySelector('td.doc-row-actions')).not.toBeNull();
        expect(document.querySelectorAll('#docRows td.doc-name')[3].textContent).toContain('very long file name that must stay fully readable');
        expect(rendered).toHaveBeenCalled();
        document.removeEventListener('documents:list-rendered', rendered);
    });

    test('folder click, search, empty folders and selection', async () => {
        setUp();
        await flush();
        await flush();
        const click = (text) => [...document.querySelectorAll('#docFolderTree button')].find((b) => b.textContent.startsWith(text)).click();
        click('Imaging');
        expect(document.querySelectorAll('#docRows tr').length).toBe(2);
        expect(document.getElementById('docCrumb').textContent).toBe('Studies › Imaging (2)');
        click('All documents');
        const search = document.getElementById('docSearch');
        search.value = 'cbc';
        search.dispatchEvent(new Event('input'));
        expect(document.querySelectorAll('#docRows tr').length).toBe(1);
        search.value = 'nothing matches';
        search.dispatchEvent(new Event('input'));
        expect(document.getElementById('docNoResults').hidden).toBe(false);
        search.value = '';
        search.dispatchEvent(new Event('input'));

        document.getElementById('docEmptyToggle').click();
        expect([...document.querySelectorAll('#docFolderTree button')].map((b) => b.textContent)).toContain('Photographs0');
        expect(document.getElementById('docEmptyToggle').textContent).toBe('Hide empty folders');

        const firstRow = document.querySelector('#docRows tr');
        firstRow.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
        expect(document.getElementById('docPreviewTitle').textContent).toBe('Chest X-ray.zip');
        expect(document.querySelector('#docPreviewBody iframe').src).toContain('/openemr/library/dicom_frame.php?csrf_token_form=tok');
        expect(document.getElementById('docOpen').disabled).toBe(false);
        document.querySelectorAll('#docRows tr')[1].click();
        expect(document.querySelector('#docPreviewBody img').getAttribute('src')).toBe('/r/5&as_file=false');
    });

    test('the choice is only remembered when the user picks a view', async () => {
        setUp();
        await flush();
        expect(window.localStorage.getItem('oeDocumentsView:test')).toBeNull();
        document.getElementById('docViewClassic').click();
        expect(window.localStorage.getItem('oeDocumentsView:test')).toBe('classic');
        expect(document.getElementById('documentsClassic').hidden).toBe(false);
        expect(document.getElementById('docUploadWrap').hidden).toBe(true);
    });

    test('a classic action page stays classic', async () => {
        setUp();
        window.oeDocumentsFolderViewConfig.forceClassic = true;
        document.getElementById('documentsFolderView').hidden = true;
        new Function('window', src)(window);
        await flush();
        expect(document.getElementById('documentsClassic').hidden).toBe(false);
    });

    test('starts when the page defines its configuration after the script tag', async () => {
        setUp();
        await flush();
        const config = window.oeDocumentsFolderViewConfig;
        window.fetch.mockClear();
        delete window.oeDocumentsFolderViewConfig;
        Object.defineProperty(document, 'readyState', { configurable: true, get: () => 'loading' });
        new Function('window', src)(window);
        expect(window.fetch).not.toHaveBeenCalled();
        window.oeDocumentsFolderViewConfig = config;
        document.dispatchEvent(new Event('DOMContentLoaded'));
        await flush();
        delete document.readyState;
        expect(window.fetch).toHaveBeenCalledTimes(1);
    });

    test('the folder column can be hidden and the choice is remembered', async () => {
        setUp();
        await flush();
        const toggle = document.getElementById('docFolderToggle');
        expect(toggle.getAttribute('aria-expanded')).toBe('true');
        toggle.click();
        expect(document.getElementById('docFolderCol').hidden).toBe(true);
        expect(document.getElementById('docMainCol').className).toContain('col-lg-12');
        expect(toggle.getAttribute('aria-expanded')).toBe('false');
        expect(toggle.textContent).toBe('Show folders');
        expect(window.localStorage.getItem('oeDocumentsView:test:folders')).toBe('hidden');
        toggle.click();
        expect(document.getElementById('docFolderCol').hidden).toBe(false);
        expect(document.getElementById('docMainCol').className).toContain('col-lg-9');
        expect(window.localStorage.getItem('oeDocumentsView:test:folders')).toBe('shown');
    });

    test('a remembered hidden folder column is applied when the page loads', async () => {
        setUp({ 'oeDocumentsView:test:folders': 'hidden' });
        await flush();
        expect(document.getElementById('docFolderCol').hidden).toBe(true);
        expect(document.getElementById('docMainCol').className).toContain('col-lg-12');
        expect(document.getElementById('docFolderToggle').textContent).toBe('Show folders');
    });

    test('the list height follows the keyboard, is clamped and is remembered', async () => {
        setUp();
        await flush();
        const handle = document.getElementById('docResize');
        const limits = window.OeDocumentsFolderView.listHeightLimits;
        const press = (key) => handle.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true }));
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.fallback);
        press('ArrowDown');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.fallback + limits.step);
        press('ArrowUp');
        press('ArrowUp');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.fallback - limits.step);
        press('Home');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.min);
        press('ArrowUp');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.min);
        press('End');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.max);
        press('ArrowDown');
        expect(Number(handle.getAttribute('aria-valuenow'))).toBe(limits.max);
        expect(document.getElementById('docListBox').style.getPropertyValue('--doc-list-height')).toBe(limits.max + 'px');
        expect(window.localStorage.getItem('oeDocumentsView:test:listHeight')).toBe(String(limits.max));
    });

    test('maximizing the preview hides the list and folders, Back and Escape restore', async () => {
        setUp();
        await flush();
        const maximize = document.getElementById('docMaximize');
        const back = document.getElementById('docMaxBack');
        maximize.click();
        expect(document.getElementById('docListPane').hidden).toBe(true);
        expect(document.getElementById('docFolderCol').hidden).toBe(true);
        expect(maximize.hidden).toBe(true);
        expect(back.hidden).toBe(false);
        expect(maximize.getAttribute('aria-pressed')).toBe('true');
        back.click();
        expect(document.getElementById('docListPane').hidden).toBe(false);
        expect(document.getElementById('docFolderCol').hidden).toBe(false);
        expect(maximize.hidden).toBe(false);
        expect(back.hidden).toBe(true);
        maximize.click();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
        expect(document.getElementById('docListPane').hidden).toBe(false);
        expect(back.hidden).toBe(true);
    });

    test('hidden folders stay hidden after leaving the maximized preview', async () => {
        setUp();
        await flush();
        document.getElementById('docFolderToggle').click();
        document.getElementById('docMaximize').click();
        document.getElementById('docMaxBack').click();
        expect(document.getElementById('docFolderCol').hidden).toBe(true);
        expect(document.getElementById('docMainCol').className).toContain('col-lg-12');
    });

    test('long names are cut with an ellipsis and keep the full name in the title', async () => {
        setUp();
        await flush();
        const rows = Array.from(document.querySelectorAll('#docRows tr'));
        expect(rows.length).toBeGreaterThan(0);
        rows.forEach((row) => {
            const cell = row.querySelector('td.doc-name');
            expect(cell).not.toBeNull();
            expect(cell.title).toBe(row.getAttribute('data-document-name'));
            expect(row.querySelector('td.doc-folder').title).not.toBe('');
        });
    });

    test('sizes the preview frame to the space left in the window', async () => {
        setUp();
        await flush();
        window.innerHeight = 900;
        document.querySelector('#docRows tr').click();
        const frame = document.querySelector('#docPreviewBody .doc-preview-frame');
        expect(frame).not.toBeNull();
        expect(frame.style.height).toBe('884px');
        window.innerHeight = 300;
        window.dispatchEvent(new Event('resize'));
        expect(frame.style.height).toBe('420px');
    });

    test('uploads one file per request and keeps the failed file with its name', async () => {
        setUp();
        await flush();
        const uploads = [];
        window.fetch = jest.fn((url, options) => {
            if (String(url).indexOf('document&upload') !== -1) {
                uploads.push(options.body.getAll('file[]').map((f) => f.name));
                const failing = uploads.length === 1;
                const html = '<div id="documents_actions">' + (failing ? '<h3>Error number: 1 while uploading a.pdf</h3>' : '') + '</div>';
                return Promise.resolve({ ok: true, text: () => Promise.resolve(html) });
            }
            return Promise.resolve({ ok: true, json: () => Promise.resolve({ categories, documents }) });
        });
        const input = document.getElementById('docFileInput');
        Object.defineProperty(input, 'files', {
            configurable: true,
            value: [new File(['x'], 'a.pdf'), new File(['y'], 'b.pdf')]
        });
        input.dispatchEvent(new Event('change'));
        document.getElementById('docUploadSubmit').click();
        for (let i = 0; i < 10; i++) {
            await flush();
        }
        expect(uploads).toEqual([['a.pdf'], ['b.pdf']]);
        const status = document.getElementById('docUploadStatus');
        expect(status.textContent).toContain('a.pdf');
        expect(status.className).toContain('text-danger');
        expect(document.getElementById('docFileNames').textContent).toBe('1 file(s) selected');
    });

    test('refits the preview frame when the Folders view is shown again', async () => {
        setUp();
        await flush();
        window.innerHeight = 900;
        document.querySelector('#docRows tr').click();
        const frame = document.querySelector('#docPreviewBody .doc-preview-frame');
        document.getElementById('docViewClassic').click();
        window.innerHeight = 700;
        window.dispatchEvent(new Event('resize'));
        expect(frame.style.height).toBe('884px');
        document.getElementById('docViewFolders').click();
        expect(frame.style.height).toBe('684px');
    });
});
