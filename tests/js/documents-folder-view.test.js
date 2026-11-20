/**
 * @jest-environment jsdom
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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

describe('type tags, hidden folder names and first-open folders', () => {
    const api = load({});

    test('the type tag comes from the extension, then from the mime type', () => {
        expect(api.typeLabel({ name: 'Chest X-ray.zip', mimetype: 'application/dicom+zip' })).toBe('ZIP');
        expect(api.typeLabel({ name: 'slice.DCM', mimetype: '' })).toBe('DCM');
        expect(api.typeLabel({ name: 'Letter.pdf', mimetype: 'application/pdf' })).toBe('PDF');
        expect(api.typeLabel({ name: 'photo.jpeg', mimetype: 'image/jpeg' })).toBe('JPG');
        expect(api.typeLabel({ name: 'IM0001', mimetype: 'application/dicom' })).toBe('DCM');
        expect(api.typeLabel({ name: 'scan', mimetype: 'image/png' })).toBe('PNG');
        expect(api.typeLabel({ name: 'noextension', mimetype: 'text/plain' })).toBe('FILE');
        expect(api.typeLabel({ name: 'archive.verylongextension', mimetype: 'application/pdf' })).toBe('PDF');
    });

    test('the tag colour class follows the kind of file', () => {
        expect(api.typeClass('PDF')).toBe('doc-type doc-type-pdf');
        expect(api.typeClass('DCM')).toBe('doc-type doc-type-dicom');
        expect(api.typeClass('ZIP')).toBe('doc-type doc-type-archive');
        expect(api.typeClass('JPG')).toBe('doc-type doc-type-image');
        expect(api.typeClass('XYZ')).toBe('doc-type doc-type-other');
    });

    test('the move picker indents a folder by its depth', () => {
        expect(api.indentedName({ name: 'Studies', path: 'Studies' })).toBe('Studies');
        expect(api.indentedName({ name: 'Lab Report', path: 'Studies › Lab Report' })).toBe('\u00A0\u00A0\u00A0Lab Report');
        expect(api.indentedName({ name: 'CCD', path: 'Medical Record › Data Exchange › CCD' })).toBe('\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0CCD');
    });

    test('hidden folder names only hide folders that are empty', () => {
        const tree = api.buildFolderTree(categories, documents);
        const hidden = api.parseHiddenNames(' Photographs , studies ,, ');
        expect(hidden).toEqual(['photographs', 'studies']);
        expect(api.isHiddenWhenEmpty(api.findFolder(tree, 7), hidden)).toBe(true);
        expect(api.isHiddenWhenEmpty(api.findFolder(tree, 2), hidden)).toBe(false);
        expect(api.countEmptyFolders(tree)).toBe(1);
        expect(api.countEmptyFolders(tree, hidden)).toBe(0);
    });

    test('the folders on the way to documents are open at first', () => {
        const tree = api.buildFolderTree(categories, documents);
        expect(Object.keys(api.foldersToOpen(tree)).sort()).toEqual(['2', '5']);
        expect(Object.keys(api.foldersWithChildren(tree)).sort()).toEqual(['2', '5']);
    });
});

describe('small screens', () => {
    const template = fs.readFileSync(path.resolve(__dirname, '../../templates/documents/general_list.html'), 'utf8');

    test('the page stylesheet stacks the panes and keeps the page from scrolling sideways on a tablet and a phone', () => {
        expect(template).toContain('@media (max-width: 991.98px)');
        expect(template).toContain('@media (max-width: 767.98px)');
        expect(template).toContain('@media (max-width: 575.98px)');
        expect(template).toMatch(/#docClassicTreeCol,\s*#docClassicActionsCol\s*\{\s*flex: 0 0 100%/);
        expect(template).toMatch(/\.doc-table \{\s*min-width: 36rem/);
        expect(template).toMatch(/\.doc-preview-actions \{\s*display: flex;\s*flex-wrap: wrap/);
    });

    test('the Folders markup keeps its columns responsive (stack below the large breakpoint)', () => {
        expect(template).toContain('id="docFolderCol"');
        expect(template).toMatch(/class="col-lg-3 col-xl-2 mb-3" id="docFolderCol"/);
        expect(template).toMatch(/class="col-lg-9 col-xl-10 mb-3" id="docMainCol"/);
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

    function setUp(stored = {}, cfgOverride = {}) {
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
                        <button type="button" id="docExpandAll"></button>
                        <button type="button" id="docCollapseAll"></button>
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
                                <table>
                                    <colgroup><col /><col /><col /><col /><col /></colgroup>
                                    <thead><tr>
                                        <th>Name<span class="doc-col-grip"></span></th><th>Folder<span class="doc-col-grip"></span></th>
                                        <th>Date<span class="doc-col-grip"></span></th><th>Size<span class="doc-col-grip"></span></th>
                                        <th><span class="doc-col-grip"></span></th>
                                    </tr></thead>
                                    <tbody id="docRows"></tbody>
                                </table>
                                <p id="docNoResults" hidden></p>
                            </div>
                            <div id="docResize" role="separator" aria-orientation="horizontal" tabindex="0"></div>
                        </div>
                        <div id="docPreviewCard">
                            <button type="button" id="docOpen" disabled></button>
                            <button type="button" id="docDownload" disabled></button>
                            <button type="button" id="docMove" disabled hidden></button>
                            <button type="button" id="docDelete" disabled hidden></button>
                            <div id="docMovePopover" hidden>
                                <select id="docMoveFolder"></select>
                                <button type="button" id="docMoveGo"></button>
                                <button type="button" id="docMoveCancel"></button>
                                <div id="docMoveStatus"></div>
                            </div>
                            <button type="button" id="docMaximize" aria-pressed="false"></button>
                            <button type="button" id="docMaxBack" hidden></button>
                            <div id="docPreviewTitle"></div>
                            <div id="docPreviewMeta"></div>
                            <div id="docPreviewBody"></div>
                        </div>
                    </div>
                </div>
                <div id="documentsClassic">
                    <div id="documents_list">
                        <a href="/folder">Studies</a>
                        <img id="icon_objTreeMenu_1_node_5" src="/public/images/file3.png" /><a href="/view1">11/04/2026 Chest X-ray.zip-3</a>
                        <img id="icon_objTreeMenu_1_node_6" src="/public/images/file3.png" /><a href="/view2">11/03/2026 Letter.pdf-9</a>
                        <img id="icon_objTreeMenu_1_node_7" src="/public/images/file3.png" /><a href="/view3">11/03/2026 Slice-12</a>
                    </div>
                </div>
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
            canMove: true,
            canDelete: true,
            forceClassic: false,
            i18n: {
                allDocuments: 'All documents',
                expand: 'Expand',
                collapse: 'Collapse',
                popupHint: 'Double click to pop up.',
                chooseFolder: 'Choose a folder.',
                currentFolder: 'current folder',
                moving: 'Moving...',
                moveFailed: 'The document could not be moved.',
                pickFolder: 'Choose a folder...',
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
        Object.assign(window.oeDocumentsFolderViewConfig, cfgOverride);
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
        const labels = [...document.querySelectorAll('#docFolderTree .doc-folder-btn')].map((b) => b.textContent);
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
        const click = (text) => [...document.querySelectorAll('#docFolderTree .doc-folder-btn')].find((b) => b.textContent.startsWith(text)).click();
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
        expect([...document.querySelectorAll('#docFolderTree .doc-folder-btn')].map((b) => b.textContent)).toContain('Photographs0');
        expect(document.getElementById('docEmptyToggle').textContent).toBe('Hide empty folders');

        const firstRow = document.querySelector('#docRows tr');
        firstRow.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
        expect(document.getElementById('docPreviewTitle').textContent).toBe('Chest X-ray.zip');
        expect(document.querySelector('#docPreviewBody iframe').src).toContain('/openemr/library/dicom_frame.php?csrf_token_form=tok');
        expect(document.getElementById('docOpen').disabled).toBe(false);
        document.querySelectorAll('#docRows tr')[1].click();
        expect(document.querySelector('#docPreviewBody img').getAttribute('src')).toBe('/r/5&as_file=false');
    });

    test('folders can be collapsed, opened again and all opened or closed at once', async () => {
        setUp();
        await flush();
        await flush();
        const names = () => [...document.querySelectorAll('#docFolderTree .doc-folder-btn')].map((b) => b.querySelector('.doc-folder-name').textContent);
        expect(names()).toEqual(['All documents', 'Studies', 'Lab Report', 'Imaging', 'Medical Record', 'Outside Records']);
        document.getElementById('docCollapseAll').click();
        expect(names()).toEqual(['All documents', 'Studies', 'Medical Record']);
        const studies = document.querySelectorAll('#docFolderTree button.doc-twisty')[0];
        expect(studies.getAttribute('aria-expanded')).toBe('false');
        studies.click();
        expect(names()).toEqual(['All documents', 'Studies', 'Lab Report', 'Imaging', 'Medical Record']);
        document.getElementById('docExpandAll').click();
        expect(names()).toContain('Outside Records');
    });

    test('folders named in the setting stay hidden while they are empty, even when empty folders are shown', async () => {
        setUp({}, { hideEmptyNames: 'photographs' });
        await flush();
        await flush();
        expect(document.getElementById('docEmptyToggle').hidden).toBe(true);
        expect(document.querySelector('#docFolderTree').textContent).not.toContain('Photographs');
    });

    test('rows show a type tag and the full name and type on hover', async () => {
        setUp();
        await flush();
        await flush();
        const first = document.querySelector('#docRows td.doc-name');
        expect(first.querySelector('.doc-type').textContent).toBe('ZIP');
        expect(first.querySelector('.doc-type').className).toContain('doc-type-archive');
        expect(first.title).toBe('Chest X-ray.zip (ZIP)');
    });

    test('the classic tree gets type tags instead of the picture icon and keeps the pop-up on double click', async () => {
        setUp();
        await flush();
        const tags = [...document.querySelectorAll('#documents_list .doc-type')].map((n) => n.textContent);
        expect(tags).toEqual(['ZIP', 'PDF', 'FILE']);
        const icon = document.getElementById('icon_objTreeMenu_1_node_5');
        expect(icon.style.display).toBe('none');
        expect(icon.nextElementSibling.title).toBe('11/04/2026 Chest X-ray.zip-3 (ZIP)');
        const popped = jest.fn();
        window.jQuery = () => ({ trigger: popped });
        document.querySelector('#documents_list .doc-type').dispatchEvent(new Event('dblclick', { bubbles: true, cancelable: true }));
        expect(popped).toHaveBeenCalledWith('dblclick');
        delete window.jQuery;
        expect(document.querySelector('#documents_list a').title).toBe('Studies');
    });

    test('list columns can be resized and the widths are remembered', async () => {
        setUp();
        await flush();
        const grip = document.querySelector('#docListBox .doc-col-grip');
        const heads = [...document.querySelectorAll('#docListBox thead th')];
        heads.forEach((th) => { th.getBoundingClientRect = () => ({ width: 100 }); });
        grip.dispatchEvent(Object.assign(new Event('pointerdown', { bubbles: true, cancelable: true }), { clientX: 10, pointerId: 1 }));
        grip.dispatchEvent(Object.assign(new Event('pointermove'), { clientX: 60 }));
        expect(document.querySelector('#docListBox col').style.width).toBe('150px');
        expect(document.querySelector('#docListBox table').style.width).toBe('550px');
        grip.dispatchEvent(new Event('pointerup'));
        expect(JSON.parse(window.localStorage.getItem('oeDocumentsView:test:columns'))).toHaveLength(5);
        grip.dispatchEvent(new Event('dblclick'));
        expect(document.querySelector('#docListBox col').style.width).toBe('');
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
            expect(cell.title.startsWith(row.getAttribute('data-document-name'))).toBe(true);
            expect(cell.querySelector('.doc-type')).not.toBeNull();
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
        document.getElementById('docUploadFolder').value = '3';
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

    test('an upload reply without the uploader (expired session) counts as a failure and keeps the file', async () => {
        setUp();
        await flush();
        window.fetch = jest.fn((url) => {
            if (String(url).indexOf('document&upload') !== -1) {
                return Promise.resolve({ ok: true, text: () => Promise.resolve('<script>top.location="login.php"</script>') });
            }
            return Promise.resolve({ ok: true, json: () => Promise.resolve({ categories, documents }) });
        });
        const input = document.getElementById('docFileInput');
        Object.defineProperty(input, 'files', { configurable: true, value: [new File(['x'], 'a.pdf')] });
        input.dispatchEvent(new Event('change'));
        document.getElementById('docUploadFolder').value = '3';
        document.getElementById('docUploadSubmit').click();
        for (let i = 0; i < 10; i++) {
            await flush();
        }
        const status = document.getElementById('docUploadStatus');
        expect(status.textContent).toContain('Upload failed');
        expect(status.className).toContain('text-danger');
        expect(document.getElementById('docFileNames').textContent).toBe('1 file(s) selected');
    });

    test('the destination field is sent only with DICOM uploads', async () => {
        setUp();
        await flush();
        const bodies = [];
        window.fetch = jest.fn((url, options) => {
            if (String(url).indexOf('document&upload') !== -1) {
                bodies.push(options.body);
                return Promise.resolve({ ok: true, text: () => Promise.resolve('<div id="documents_actions"></div>') });
            }
            return Promise.resolve({ ok: true, json: () => Promise.resolve({ categories, documents }) });
        });
        document.getElementById('docDestination').value = 'Study A';
        const input = document.getElementById('docFileInput');
        Object.defineProperty(input, 'files', { configurable: true, value: [new File(['x'], 'a.pdf')] });
        input.dispatchEvent(new Event('change'));
        document.getElementById('docUploadFolder').value = '3';
        document.getElementById('docUploadSubmit').click();
        for (let i = 0; i < 10; i++) {
            await flush();
        }
        expect(bodies.length).toBe(1);
        expect(bodies[0].has('destination')).toBe(false);

        document.getElementById('docDestination').value = 'Study A'; // the form clears it after a successful upload
        const dicom = document.getElementById('docDicomInput');
        Object.defineProperty(dicom, 'files', { configurable: true, value: [new File(['d'], 'IM1.dcm')] });
        document.getElementById('docUploadFolder').value = '3';
        document.getElementById('docUploadSubmit').click();
        for (let i = 0; i < 10; i++) {
            await flush();
        }
        const dicomBody = bodies[bodies.length - 1];
        expect(dicomBody.get('destination')).toBe('Study A');
    });

    test('no folder is preselected for an upload: the user is asked', async () => {
        setUp();
        await flush();
        await flush();
        const folder = document.getElementById('docUploadFolder');
        expect(folder.value).toBe('');
        expect(folder.options[0].textContent).toBe('Choose a folder...');
        const calls = [];
        window.fetch = jest.fn((url) => {
            calls.push(String(url));
            return Promise.resolve({ ok: true, text: () => Promise.resolve(''), json: () => Promise.resolve({ categories, documents }) });
        });
        const input = document.getElementById('docFileInput');
        Object.defineProperty(input, 'files', { configurable: true, value: [new File(['x'], 'letter.pdf')] });
        input.dispatchEvent(new Event('change'));
        document.getElementById('docUploadSubmit').click();
        await flush();
        expect(document.getElementById('docUploadStatus').textContent).toBe('Choose a folder.');
        expect(calls.filter((url) => url.indexOf('document&upload') !== -1)).toEqual([]);
    });

    test('a DICOM file is not filed anywhere by itself: it also asks for a folder', async () => {
        setUp();
        await flush();
        await flush();
        const calls = [];
        window.fetch = jest.fn((url) => {
            calls.push(String(url));
            return Promise.resolve({ ok: true, text: () => Promise.resolve(''), json: () => Promise.resolve({ categories, documents }) });
        });
        const input = document.getElementById('docFileInput');
        Object.defineProperty(input, 'files', { configurable: true, value: [new File(['x'], 'IM0001.dcm')] });
        input.dispatchEvent(new Event('change'));
        document.getElementById('docUploadSubmit').click();
        await flush();
        expect(document.getElementById('docUploadStatus').textContent).toBe('Choose a folder.');
        expect(calls.filter((url) => url.indexOf('document&upload') !== -1)).toEqual([]);
    });

    test('Move and Delete wait for a selection and are hidden without the rights', async () => {
        setUp();
        await flush();
        await flush();
        expect(document.getElementById('docMove').hidden).toBe(false);
        expect(document.getElementById('docMove').disabled).toBe(true);
        expect(document.getElementById('docDelete').disabled).toBe(true);
        document.querySelector('#docRows tr').click();
        expect(document.getElementById('docMove').disabled).toBe(false);
        expect(document.getElementById('docDelete').disabled).toBe(false);
        setUp({}, { canMove: false, canDelete: false });
        await flush();
        expect(document.getElementById('docMove').hidden).toBe(true);
        expect(document.getElementById('docDelete').hidden).toBe(true);
    });

    function mockMove(result) {
        const posts = [];
        window.fetch = jest.fn((url, options) => {
            if (String(url).indexOf('document&move') !== -1) {
                posts.push({ url: String(url), category: options.body.get('new_category_id'), token: options.body.get('csrf_token_form') });
                return Promise.resolve({ ok: result !== 403, text: () => Promise.resolve('') });
            }
            const moved = documents.map((d) => (d.id === 3 && result === 'moved' ? { ...d, category_id: 3, path: 'Studies › Lab Report' } : d));
            return Promise.resolve({ ok: true, json: () => Promise.resolve({ categories, documents: moved }) });
        });
        return posts;
    }

    test('Move offers an indented folder list, marks the current folder and keeps the folder selected', async () => {
        setUp();
        await flush();
        await flush();
        [...document.querySelectorAll('#docFolderTree .doc-folder-btn')].find((b) => b.textContent.startsWith('Imaging')).click();
        document.querySelector('#docRows tr').click();
        const posts = mockMove('moved');
        document.getElementById('docMove').click();
        expect(document.getElementById('docMovePopover').hidden).toBe(false);
        const options = [...document.getElementById('docMoveFolder').options];
        expect(options.map((o) => o.textContent)).toContain('\u00A0\u00A0\u00A0Imaging (current folder)');
        expect(options.find((o) => o.value === '4').disabled).toBe(true);
        expect(options.find((o) => o.textContent.indexOf('Lab Report') !== -1).textContent.startsWith('\u00A0')).toBe(true);
        document.getElementById('docMoveFolder').value = '3';
        document.getElementById('docMoveGo').click();
        for (let i = 0; i < 6; i++) {
            await flush();
        }
        expect(posts).toEqual([{ url: '/openemr/controller.php?document&move&patient_id=7&document_id=3&process=true', category: '3', token: 'tok' }]);
        expect(document.getElementById('docMovePopover').hidden).toBe(true);
        expect(document.getElementById('docCrumb').textContent).toContain('Studies › Imaging');
        expect(document.querySelectorAll('#docRows tr').length).toBe(1);
    });

    test('a move that did not happen shows an error and keeps the picker open', async () => {
        setUp();
        await flush();
        await flush();
        document.querySelector('#docRows tr').click();
        mockMove('unchanged');
        document.getElementById('docMove').click();
        document.getElementById('docMoveFolder').value = '3';
        document.getElementById('docMoveGo').click();
        for (let i = 0; i < 6; i++) {
            await flush();
        }
        expect(document.getElementById('docMoveStatus').textContent).toBe('The document could not be moved.');
        expect(document.getElementById('docMoveStatus').className).toContain('text-danger');
        expect(document.getElementById('docMovePopover').hidden).toBe(false);
        expect(document.getElementById('docMoveGo').disabled).toBe(false);
    });

    test('a refused move (HTTP 403) shows the error', async () => {
        setUp();
        await flush();
        await flush();
        document.querySelector('#docRows tr').click();
        mockMove(403);
        document.getElementById('docMove').click();
        document.getElementById('docMoveFolder').value = '3';
        document.getElementById('docMoveGo').click();
        for (let i = 0; i < 6; i++) {
            await flush();
        }
        expect(document.getElementById('docMoveStatus').textContent).toBe('The document could not be moved.');
    });

    test('Delete opens the stock delete dialog and the list refreshes when it reports success', async () => {
        setUp();
        await flush();
        await flush();
        [...document.querySelectorAll('#docFolderTree .doc-folder-btn')].find((b) => b.textContent.startsWith('Imaging')).click();
        document.querySelector('#docRows tr').click();
        window.dlgopen = jest.fn();
        document.getElementById('docDelete').click();
        expect(window.dlgopen).toHaveBeenCalledTimes(1);
        expect(window.dlgopen.mock.calls[0][0]).toBe('/openemr/interface/patient_file/deleter.php?document=3&document_pid=7&csrf_token_form=tok');
        window.fetch.mockClear();
        window.imdeleted();
        await flush();
        await flush();
        expect(window.fetch.mock.calls[0][0]).toContain('list_data&patient_id=7');
        expect(document.getElementById('docCrumb').textContent).toContain('Studies › Imaging');
        delete window.dlgopen;
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
