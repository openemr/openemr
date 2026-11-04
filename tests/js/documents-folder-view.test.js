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

describe('page wiring', () => {
    const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

    function setUp() {
        document.body.innerHTML = `
            <div class="container-fluid">
                <button type="button" id="docViewFolders"></button>
                <button type="button" id="docViewClassic"></button>
                <div id="docUploadWrap" hidden></div>
                <div id="documentsFolderView" hidden>
                    <div id="docFolderTree"></div>
                    <button type="button" id="docEmptyToggle"></button>
                    <input type="search" id="docSearch" />
                    <select id="docSort">
                        <option value="date_desc"></option><option value="name"></option>
                    </select>
                    <div id="docCrumb"></div>
                    <table><tbody id="docRows"></tbody></table>
                    <p id="docNoResults" hidden></p>
                    <div id="docPreviewTitle"></div>
                    <div id="docPreviewMeta"></div>
                    <button type="button" id="docOpen" disabled></button>
                    <button type="button" id="docDownload" disabled></button>
                    <div id="docPreviewBody"></div>
                </div>
                <div id="documentsClassic"></div>
            </div>`;
        window.fetch = jest.fn(() => Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ categories, documents })
        }));
        window.localStorage.clear();
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
                noPreview: 'No preview'
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
});
