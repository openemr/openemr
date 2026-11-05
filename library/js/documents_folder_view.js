/**
 * "Folders" view of the Documents page: a list of the folders that hold documents, a searchable
 * and sortable document list and a preview pane, next to the classic folder tree.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (root) {
    'use strict';

    // ---- pure helpers (no DOM), also exercised by tests/js/documents-folder-view.test.js ----

    /**
     * Builds the folder tree from the flat category list. Every node carries `own` (documents filed
     * directly in it) and `count` (documents in it and in all of its subfolders).
     */
    function buildFolderTree(categories, documents) {
        var byId = {};
        var order = [];
        categories.forEach(function (category) {
            byId[String(category.id)] = {
                id: category.id,
                name: category.name,
                path: category.path,
                parent: category.parent,
                children: [],
                own: 0,
                count: 0
            };
            order.push(String(category.id));
        });
        documents.forEach(function (doc) {
            var node = byId[String(doc.category_id)];
            if (node) {
                node.own += 1;
            }
        });
        var top = [];
        order.forEach(function (id) {
            var node = byId[id];
            var parent = byId[String(node.parent)];
            if (parent) {
                parent.children.push(node);
            } else {
                top.push(node);
            }
        });
        var total = function (node) {
            var sum = node.own;
            node.children.forEach(function (child) {
                sum += total(child);
            });
            node.count = sum;
            return sum;
        };
        top.forEach(total);
        return top;
    }

    /** Lower-case folder names from a comma separated setting. */
    function parseHiddenNames(value) {
        return String(value || '').split(',').map(function (part) {
            return part.trim().toLowerCase();
        }).filter(function (part) {
            return part !== '';
        });
    }

    /** True for an empty folder whose name is on the "hide while empty" list. */
    function isHiddenWhenEmpty(node, hidden) {
        return node.count === 0 && Array.isArray(hidden) && hidden.indexOf(String(node.name).toLowerCase()) !== -1;
    }

    /** Empty folders the user can still reveal (those on the hide list never show while empty). */
    function countEmptyFolders(nodes, hidden) {
        var empty = 0;
        nodes.forEach(function (node) {
            if (node.count === 0 && !isHiddenWhenEmpty(node, hidden)) {
                empty += 1;
            }
            if (!isHiddenWhenEmpty(node, hidden)) {
                empty += countEmptyFolders(node.children, hidden);
            }
        });
        return empty;
    }

    /** Ids (as strings) of the folders to open at first: every folder on the way to a document. */
    function foldersToOpen(nodes) {
        var open = {};
        var walk = function (node) {
            var below = false;
            node.children.forEach(function (child) {
                if (walk(child)) {
                    below = true;
                }
            });
            if (below) {
                open[String(node.id)] = true;
            }
            return node.count > 0;
        };
        nodes.forEach(walk);
        return open;
    }

    /** Ids (as strings) of every folder that has subfolders. */
    function foldersWithChildren(nodes) {
        var ids = {};
        var walk = function (node) {
            if (node.children.length > 0) {
                ids[String(node.id)] = true;
            }
            node.children.forEach(walk);
        };
        nodes.forEach(walk);
        return ids;
    }

    function findFolder(nodes, id) {
        for (var i = 0; i < nodes.length; i++) {
            if (String(nodes[i].id) === String(id)) {
                return nodes[i];
            }
            var found = findFolder(nodes[i].children, id);
            if (found) {
                return found;
            }
        }
        return null;
    }

    /** Ids (as strings) of a folder and all of its subfolders. */
    function folderIds(node) {
        var ids = {};
        var walk = function (current) {
            ids[String(current.id)] = true;
            current.children.forEach(walk);
        };
        walk(node);
        return ids;
    }

    function filterDocuments(documents, ids, query) {
        var needle = String(query || '').trim().toLowerCase();
        return documents.filter(function (doc) {
            if (ids && !ids[String(doc.category_id)]) {
                return false;
            }
            if (needle === '') {
                return true;
            }
            return doc.name.toLowerCase().indexOf(needle) !== -1 || doc.path.toLowerCase().indexOf(needle) !== -1;
        });
    }

    function extensionOf(doc) {
        var dot = doc.name.lastIndexOf('.');
        if (dot !== -1 && dot < doc.name.length - 1) {
            return doc.name.slice(dot + 1).toLowerCase();
        }
        return String(doc.mimetype || '').toLowerCase();
    }

    var TYPE_GROUPS = {
        pdf: ['pdf'],
        dicom: ['dcm', 'dicom'],
        archive: ['zip', '7z', 'rar', 'tar', 'gz'],
        image: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff', 'webp', 'heic', 'svg'],
        text: ['doc', 'docx', 'rtf', 'txt', 'odt', 'xml', 'csv', 'json', 'html', 'htm'],
        sheet: ['xls', 'xlsx', 'ods']
    };

    /** Short upper-case file type for the list, e.g. PDF, ZIP, DCM, JPG. */
    function typeLabel(doc) {
        var name = String(doc.name || '');
        var dot = name.lastIndexOf('.');
        var ext = dot !== -1 && dot < name.length - 1 ? name.slice(dot + 1).toLowerCase() : '';
        if (ext !== '' && ext.length <= 5 && /^[a-z0-9]+$/.test(ext)) {
            return ext === 'jpeg' ? 'JPG' : ext.toUpperCase();
        }
        var mime = String(doc.mimetype || '').toLowerCase();
        if (mime === 'application/dicom' || mime === 'application/dicom+zip') {
            return 'DCM';
        }
        if (mime === 'application/pdf') {
            return 'PDF';
        }
        if (mime.indexOf('image/') === 0) {
            var sub = mime.slice(6).replace(/[^a-z0-9].*$/, '');
            return (sub === 'jpeg' ? 'jpg' : sub).toUpperCase().slice(0, 5) || 'FILE';
        }
        return 'FILE';
    }

    /** Style class of the type tag, by kind of file. */
    function typeClass(label) {
        var lower = String(label).toLowerCase();
        var kind = 'other';
        Object.keys(TYPE_GROUPS).forEach(function (group) {
            if (TYPE_GROUPS[group].indexOf(lower) !== -1) {
                kind = group;
            }
        });
        return 'doc-type doc-type-' + kind;
    }

    function compareText(a, b) {
        return String(a).localeCompare(String(b), undefined, {numeric: true, sensitivity: 'base'});
    }

    /** Returns a sorted copy. Keys: date_desc, date_asc, name, folder, type. */
    function sortDocuments(documents, key) {
        var sorted = documents.slice();
        sorted.sort(function (a, b) {
            var result = 0;
            switch (key) {
                case 'date_asc':
                    result = compareText(a.date, b.date);
                    break;
                case 'name':
                    result = compareText(a.name, b.name);
                    break;
                case 'folder':
                    result = compareText(a.path, b.path) || compareText(a.name, b.name);
                    break;
                case 'type':
                    result = compareText(extensionOf(a), extensionOf(b)) || compareText(a.name, b.name);
                    break;
                default:
                    result = compareText(b.date, a.date);
                    break;
            }
            return result || (b.id - a.id);
        });
        return sorted;
    }

    function formatSize(bytes) {
        var size = Number(bytes) || 0;
        if (size < 1024) {
            return size + ' B';
        }
        if (size < 1024 * 1024) {
            return Math.round(size / 1024) + ' KB';
        }
        return (size / (1024 * 1024)).toFixed(1) + ' MB';
    }

    /** How a document is previewed: image, pdf, dicom or other (download or open in a new window). */
    function previewKind(doc) {
        var mime = String(doc.mimetype || '').toLowerCase();
        var ext = extensionOf(doc);
        if (mime === 'application/dicom' || mime === 'application/dicom+zip' || ext === 'dcm') {
            return 'dicom';
        }
        if (mime === 'application/pdf') {
            return 'pdf';
        }
        if (mime.indexOf('image/') === 0 && mime !== 'image/tiff') {
            return 'image';
        }
        return 'other';
    }

    var LIST_MIN = 120;

    var PREVIEW_MIN = 420;

    var PREVIEW_GAP = 16;
    var LIST_MAX = 640;
    var LIST_DEFAULT = 280;
    var LIST_STEP = 40;

    function clampListHeight(value) {
        var number = Number(value);
        if (!isFinite(number)) {
            return LIST_DEFAULT;
        }
        return Math.min(LIST_MAX, Math.max(LIST_MIN, Math.round(number)));
    }

    root.OeDocumentsFolderView = {
        clampListHeight: clampListHeight,
        listHeightLimits: {min: LIST_MIN, max: LIST_MAX, step: LIST_STEP, fallback: LIST_DEFAULT},
        buildFolderTree: buildFolderTree,
        countEmptyFolders: countEmptyFolders,
        parseHiddenNames: parseHiddenNames,
        isHiddenWhenEmpty: isHiddenWhenEmpty,
        foldersToOpen: foldersToOpen,
        foldersWithChildren: foldersWithChildren,
        typeLabel: typeLabel,
        typeClass: typeClass,
        findFolder: findFolder,
        folderIds: folderIds,
        filterDocuments: filterDocuments,
        sortDocuments: sortDocuments,
        extensionOf: extensionOf,
        formatSize: formatSize,
        previewKind: previewKind
    };

    // ---- page behaviour ----

    function init(doc, cfg) {
        var el = function (id) {
            return doc.getElementById(id);
        };
        var view = {
            classic: el('documentsClassic'),
            folders: el('documentsFolderView'),
            btnFolders: el('docViewFolders'),
            btnClassic: el('docViewClassic'),
            uploadWrap: el('docUploadWrap'),
            tree: el('docFolderTree'),
            emptyToggle: el('docEmptyToggle'),
            expandAll: el('docExpandAll'),
            collapseAll: el('docCollapseAll'),
            search: el('docSearch'),
            sort: el('docSort'),
            crumb: el('docCrumb'),
            rows: el('docRows'),
            noResults: el('docNoResults'),
            previewTitle: el('docPreviewTitle'),
            previewMeta: el('docPreviewMeta'),
            previewBody: el('docPreviewBody'),
            open: el('docOpen'),
            download: el('docDownload'),
            folderCol: el('docFolderCol'),
            mainCol: el('docMainCol'),
            folderToggle: el('docFolderToggle'),
            listPane: el('docListPane'),
            listBox: el('docListBox'),
            resize: el('docResize'),
            previewCard: el('docPreviewCard'),
            maximize: el('docMaximize'),
            maxBack: el('docMaxBack')
        };
        if (!view.classic || !view.folders || !view.tree || !view.rows) {
            return;
        }
        var i18n = cfg.i18n || {};
        var state = {
            tree: [],
            categories: [],
            documents: [],
            folder: null,
            expanded: {},
            hidden: parseHiddenNames(cfg.hideEmptyNames),
            showEmpty: false,
            selected: null,
            loaded: false
        };

        function restoreSession() {
            try {
                if (root.top && typeof root.top.restoreSession === 'function') {
                    root.top.restoreSession();
                }
            } catch (e) {
                // cross-frame access can fail; the session keeps working without it
            }
        }

        function storedView() {
            try {
                return root.localStorage.getItem(cfg.storageKey);
            } catch (e) {
                return null;
            }
        }

        function storeView(value) {
            try {
                root.localStorage.setItem(cfg.storageKey, value);
            } catch (e) {
                // storage can be blocked; the choice is then simply not remembered
            }
        }

        function make(tag, className, text) {
            var node = doc.createElement(tag);
            if (className) {
                node.className = className;
            }
            if (text !== undefined) {
                node.textContent = text;
            }
            return node;
        }

        function withCount(template, count) {
            return String(template || '').replace('%d', String(count));
        }

        function currentRows() {
            var ids = state.folder === null ? null : folderIds(state.folder);
            var filtered = filterDocuments(state.documents, ids, view.search.value);
            return sortDocuments(filtered, view.sort.value);
        }

        function renderTree() {
            view.tree.textContent = '';
            var addRow = function (node, depth, label, count, active, hasChildren, onSelect) {
                var row = make('div', 'doc-folder-row' + (active ? ' active' : ''));
                row.style.paddingLeft = (depth * 0.85) + 'rem';
                if (hasChildren) {
                    var open = !!state.expanded[String(node.id)];
                    var twisty = make('button', 'doc-twisty', open ? '\u25BE' : '\u25B8');
                    twisty.type = 'button';
                    twisty.setAttribute('aria-expanded', open ? 'true' : 'false');
                    twisty.setAttribute('aria-label', (open ? i18n.collapse : i18n.expand) + ': ' + label);
                    twisty.addEventListener('click', function () {
                        state.expanded[String(node.id)] = !open;
                        renderTree();
                    });
                    row.appendChild(twisty);
                } else {
                    row.appendChild(make('span', 'doc-twisty doc-twisty-gap'));
                }
                var button = make('button', 'doc-folder-btn');
                button.type = 'button';
                button.title = label + ' (' + count + ')';
                if (active) {
                    button.setAttribute('aria-current', 'true');
                }
                button.appendChild(make('span', 'doc-folder-name', label));
                button.appendChild(make('span', 'doc-folder-count', String(count)));
                button.addEventListener('click', onSelect);
                row.appendChild(button);
                view.tree.appendChild(row);
            };
            addRow({id: 'all'}, 0, i18n.allDocuments, state.documents.length, state.folder === null, false, function () {
                state.folder = null;
                render();
            });
            var shown = function (node) {
                if (isHiddenWhenEmpty(node, state.hidden)) {
                    return false;
                }
                return node.count > 0 || state.showEmpty;
            };
            var walk = function (nodes, depth) {
                nodes.forEach(function (node) {
                    if (!shown(node)) {
                        return;
                    }
                    var kids = node.children.filter(shown);
                    addRow(node, depth, node.name, node.count, state.folder === node, kids.length > 0, function () {
                        state.folder = node;
                        if (kids.length > 0) {
                            state.expanded[String(node.id)] = true;
                        }
                        render();
                    });
                    if (kids.length > 0 && state.expanded[String(node.id)]) {
                        walk(node.children, depth + 1);
                    }
                });
            };
            walk(state.tree, 1);
            var empty = countEmptyFolders(state.tree, state.hidden);
            if (empty === 0 && !state.showEmpty) {
                view.emptyToggle.hidden = true;
            } else {
                view.emptyToggle.hidden = false;
                view.emptyToggle.textContent = state.showEmpty ? i18n.hideEmpty : withCount(i18n.showEmpty, empty);
            }
        }

        function renderRows() {
            var rows = currentRows();
            view.rows.textContent = '';
            rows.forEach(function (item) {
                var tr = make('tr');
                tr.tabIndex = 0;
                tr.setAttribute('data-document-id', String(item.id));
                tr.setAttribute('data-document-name', item.name);
                tr.setAttribute('data-document-mime', item.mimetype);
                if (state.selected && state.selected.id === item.id) {
                    tr.className = 'table-active';
                    tr.setAttribute('aria-selected', 'true');
                }
                var label = typeLabel(item);
                var nameCell = make('td', 'doc-name');
                var wrap = make('div', 'doc-name-wrap');
                wrap.appendChild(make('span', typeClass(label), label));
                wrap.appendChild(make('span', 'doc-name-text', item.name));
                nameCell.appendChild(wrap);
                nameCell.title = item.name + ' (' + label + ')';
                tr.appendChild(nameCell);
                var folderCell = make('td', 'doc-folder', item.path);
                folderCell.title = item.path;
                tr.appendChild(folderCell);
                tr.appendChild(make('td', 'text-nowrap', item.date));
                tr.appendChild(make('td', 'text-nowrap', formatSize(item.size)));
                tr.appendChild(make('td', 'doc-row-actions'));
                var choose = function () {
                    select(item);
                };
                tr.addEventListener('click', choose);
                tr.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        choose();
                    }
                });
                view.rows.appendChild(tr);
            });
            view.noResults.hidden = rows.length > 0;
            view.crumb.textContent = (state.folder === null ? i18n.allDocuments : state.folder.path) + ' (' + rows.length + ')';
            doc.dispatchEvent(new root.CustomEvent('documents:list-rendered', {detail: {count: rows.length}}));
        }

        function dicomUrl(item) {
            return cfg.webroot + '/library/dicom_frame.php?csrf_token_form=' + encodeURIComponent(cfg.csrfToken)
                + '&web_path=' + encodeURIComponent(cfg.webroot + '/controller.php?document')
                + '&patient_id=' + encodeURIComponent(cfg.patientId) + '&document_id=' + encodeURIComponent(item.id);
        }

        function openUrl(item) {
            return previewKind(item) === 'dicom' ? dicomUrl(item) : item.retrieve_url;
        }

        // Size the preview frame to the space left in the window, so the page itself does not scroll
        // below it. The stylesheet height stays as the fallback.
        function fitPreviewFrame() {
            var frame = view.previewBody ? view.previewBody.querySelector('.doc-preview-frame') : null;
            if (!frame) {
                return;
            }
            if (view.folders && view.folders.hidden) {
                return;
            }
            var top = frame.getBoundingClientRect().top;
            frame.style.height = Math.max(PREVIEW_MIN, Math.floor(root.innerHeight - top - PREVIEW_GAP)) + 'px';
        }

        function renderPreview() {
            var item = state.selected;
            view.previewBody.textContent = '';
            if (!item) {
                view.previewTitle.textContent = i18n.selectDocument;
                view.previewMeta.textContent = '';
                view.open.disabled = true;
                view.download.disabled = true;
                return;
            }
            view.previewTitle.textContent = item.name;
            view.previewMeta.textContent = item.path + ' · ' + item.date + ' · ' + formatSize(item.size);
            view.open.disabled = false;
            view.download.disabled = false;
            var kind = previewKind(item);
            if (kind === 'image') {
                var img = make('img', 'img-fluid');
                img.src = item.retrieve_url;
                img.alt = item.name;
                view.previewBody.appendChild(img);
            } else if (kind === 'pdf' || kind === 'dicom') {
                var frame = make('iframe', 'doc-preview-frame');
                frame.title = item.name;
                frame.src = kind === 'dicom' ? dicomUrl(item) : item.retrieve_url;
                view.previewBody.appendChild(frame);
                fitPreviewFrame();
            } else {
                view.previewBody.appendChild(make('p', 'text-muted', i18n.noPreview));
            }
        }

        function select(item) {
            state.selected = item;
            restoreSession();
            renderRows();
            renderPreview();
        }

        function render() {
            renderTree();
            renderRows();
            renderPreview();
        }

        function load() {
            restoreSession();
            return root.fetch(cfg.dataUrl, {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    state.categories = data.categories || [];
                    state.documents = data.documents || [];
                    state.tree = buildFolderTree(state.categories, state.documents);
                    state.expanded = foldersToOpen(state.tree);
                    state.folder = null;
                    state.selected = null;
                    state.loaded = true;
                    render();
                    fillUploadFolders();
                })
                .catch(function () {
                    view.crumb.textContent = i18n.loadFailed;
                });
        }

        function setView(name, remember) {
            var folders = name === 'folders';
            view.folders.hidden = !folders;
            view.classic.hidden = folders;
            view.uploadWrap.hidden = !folders;
            view.btnFolders.setAttribute('aria-pressed', folders ? 'true' : 'false');
            view.btnClassic.setAttribute('aria-pressed', folders ? 'false' : 'true');
            view.btnFolders.classList.toggle('active', folders);
            view.btnClassic.classList.toggle('active', !folders);
            if (remember) {
                storeView(name);
            }
            if (folders && !state.loaded) {
                load();
            }
            if (folders) {
                fitPreviewFrame();
            }
        }

        // ---- layout: folder column, list height, maximized preview ----
        function storedValue(suffix) {
            try {
                return root.localStorage.getItem(cfg.storageKey + suffix);
            } catch (e) {
                return null;
            }
        }

        function storeValue(suffix, value) {
            try {
                root.localStorage.setItem(cfg.storageKey + suffix, value);
            } catch (e) {
                // storage can be blocked; the choice is then simply not remembered
            }
        }

        function foldersWanted() {
            return !view.folderToggle || view.folderToggle.getAttribute('aria-expanded') !== 'false';
        }

        function applyColumns(maximized) {
            var showFolders = foldersWanted() && !maximized;
            if (view.folderCol) {
                view.folderCol.hidden = !showFolders;
            }
            if (view.mainCol) {
                view.mainCol.className = showFolders ? 'col-lg-9 col-xl-10 mb-3' : 'col-lg-12 mb-3';
            }
        }

        function setFoldersShown(shown, remember) {
            if (view.folderToggle) {
                view.folderToggle.setAttribute('aria-expanded', shown ? 'true' : 'false');
                view.folderToggle.textContent = shown ? i18n.hideFolders : i18n.showFolders;
            }
            applyColumns(isMaximized());
            if (remember) {
                storeValue(':folders', shown ? 'shown' : 'hidden');
            }
        }

        function setListHeight(value, remember) {
            if (!view.listBox) {
                return LIST_DEFAULT;
            }
            var height = clampListHeight(value);
            view.listBox.style.setProperty('--doc-list-height', height + 'px');
            if (view.resize) {
                view.resize.setAttribute('aria-valuemin', String(LIST_MIN));
                view.resize.setAttribute('aria-valuemax', String(LIST_MAX));
                view.resize.setAttribute('aria-valuenow', String(height));
            }
            if (remember) {
                storeValue(':listHeight', String(height));
            }
            fitPreviewFrame();
            return height;
        }

        function currentListHeight() {
            var now = view.resize ? Number(view.resize.getAttribute('aria-valuenow')) : LIST_DEFAULT;
            return isFinite(now) && now > 0 ? now : LIST_DEFAULT;
        }

        function isMaximized() {
            return view.maxBack ? !view.maxBack.hidden : false;
        }

        function setMaximized(on) {
            if (!view.previewCard || !view.maximize || !view.maxBack) {
                return;
            }
            view.folders.classList.toggle('doc-maximized', on);
            if (view.listPane) {
                view.listPane.hidden = on;
            }
            view.maximize.hidden = on;
            view.maximize.setAttribute('aria-pressed', on ? 'true' : 'false');
            view.maxBack.hidden = !on;
            applyColumns(on);
            fitPreviewFrame();
        }

        function wireLayout() {
            root.addEventListener('resize', fitPreviewFrame);
            setFoldersShown(storedValue(':folders') !== 'hidden', false);
            setListHeight(storedValue(':listHeight') === null ? LIST_DEFAULT : storedValue(':listHeight'), false);
            if (view.folderToggle) {
                view.folderToggle.addEventListener('click', function () {
                    setFoldersShown(!foldersWanted(), true);
                });
            }
            if (view.maximize) {
                view.maximize.addEventListener('click', function () {
                    setMaximized(true);
                });
            }
            if (view.maxBack) {
                view.maxBack.addEventListener('click', function () {
                    setMaximized(false);
                });
            }
            doc.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && isMaximized()) {
                    setMaximized(false);
                }
            });
            if (view.resize) {
                view.resize.addEventListener('keydown', function (event) {
                    var height = currentListHeight();
                    if (event.key === 'ArrowUp') {
                        height -= LIST_STEP;
                    } else if (event.key === 'ArrowDown') {
                        height += LIST_STEP;
                    } else if (event.key === 'Home') {
                        height = LIST_MIN;
                    } else if (event.key === 'End') {
                        height = LIST_MAX;
                    } else {
                        return;
                    }
                    event.preventDefault();
                    setListHeight(height, true);
                });
                view.resize.addEventListener('pointerdown', function (event) {
                    var startY = event.clientY;
                    var startHeight = currentListHeight();
                    event.preventDefault();
                    if (typeof view.resize.setPointerCapture === 'function' && event.pointerId !== undefined) {
                        try {
                            view.resize.setPointerCapture(event.pointerId);
                        } catch (e) {
                            // capture is optional; moves are still seen while the pointer stays over the handle
                        }
                    }
                    var move = function (moveEvent) {
                        setListHeight(startHeight + (moveEvent.clientY - startY), false);
                    };
                    var stop = function () {
                        view.resize.removeEventListener('pointermove', move);
                        view.resize.removeEventListener('pointerup', stop);
                        view.resize.removeEventListener('pointercancel', stop);
                        storeValue(':listHeight', String(currentListHeight()));
                    };
                    view.resize.addEventListener('pointermove', move);
                    view.resize.addEventListener('pointerup', stop);
                    view.resize.addEventListener('pointercancel', stop);
                });
            }
        }

        // ---- upload popover ----
        var up = {
            button: el('docUploadBtn'),
            popover: el('docUploadPopover'),
            folder: el('docUploadFolder'),
            drop: el('docDropArea'),
            files: el('docFileInput'),
            dicom: el('docDicomInput'),
            destination: el('docDestination'),
            encrypted: el('docEncrypted'),
            passphrase: el('docPassphrase'),
            names: el('docFileNames'),
            status: el('docUploadStatus'),
            submit: el('docUploadSubmit'),
            cancel: el('docUploadCancel')
        };
        var pending = [];

        function fillUploadFolders() {
            if (!up.folder) {
                return;
            }
            up.folder.textContent = '';
            var placeholder = doc.createElement('option');
            placeholder.value = '';
            placeholder.textContent = i18n.pickFolder || '';
            up.folder.appendChild(placeholder);
            var used = state.categories.filter(function (category) {
                var node = findFolder(state.tree, category.id);
                return node && node.own > 0;
            });
            var others = state.categories.filter(function (category) {
                return used.indexOf(category) === -1;
            });
            var addGroup = function (label, list) {
                if (list.length === 0) {
                    return;
                }
                var group = doc.createElement('optgroup');
                group.label = label;
                list.forEach(function (category) {
                    var option = doc.createElement('option');
                    option.value = String(category.id);
                    option.textContent = category.path;
                    group.appendChild(option);
                });
                up.folder.appendChild(group);
            };
            addGroup(i18n.commonFolders, used);
            addGroup(i18n.moreFolders, others);
            up.folder.value = state.folder ? String(state.folder.id) : '';
            try {
                if (root.jQuery && root.jQuery.fn && typeof root.jQuery.fn.select2 === 'function') {
                    root.jQuery(up.folder).select2({dropdownParent: root.jQuery(up.popover), width: '100%'});
                }
            } catch (e) {
                // a plain select is fine when select2 cannot start
            }
        }

        function radiologyFolderId() {
            var found = state.categories.filter(function (category) {
                var name = String(category.name).toLowerCase();
                return name === 'radiology' || name === 'x-ray & imaging';
            });
            return found.length === 1 ? String(found[0].id) : '';
        }

        function onlyDicom(dicomFiles) {
            if (dicomFiles.length > 0) {
                return pending.length === 0;
            }
            return pending.length > 0 && pending.every(function (file) {
                return /\.dcm$/i.test(file.name);
            });
        }

        function showPending() {
            up.names.textContent = pending.length === 0 ? '' : withCount(i18n.filesSelected, pending.length);
        }

        function addFiles(list) {
            for (var i = 0; i < list.length; i++) {
                pending.push(list[i]);
            }
            showPending();
        }

        function setStatus(message, isError) {
            up.status.textContent = message;
            up.status.className = 'small mt-2 ' + (isError ? 'text-danger' : 'text-success');
        }

        function closeUpload() {
            up.popover.hidden = true;
            up.button.setAttribute('aria-expanded', 'false');
        }

        // The upload action reports only the error of the last file of a request, so every file goes
        // in a request of its own and each failure is reported by file name.
        function postUpload(category, files, dicomFiles) {
            var form = new root.FormData();
            form.append('MAX_FILE_SIZE', '64000000');
            form.append('patient_id', cfg.patientId);
            form.append('category_id', category);
            form.append('process', 'true');
            form.append('csrf_token_form', cfg.csrfToken);
            files.forEach(function (file) {
                form.append('file[]', file, file.name);
            });
            for (var i = 0; i < dicomFiles.length; i++) {
                form.append('dicom_folder[]', dicomFiles[i], dicomFiles[i].name);
            }
            form.append('destination', up.destination.value);
            if (up.encrypted && up.encrypted.checked) {
                form.append('encrypted', 'on');
                form.append('passphrase', up.passphrase.value);
            }
            return root.fetch(cfg.webroot + '/controller.php?document&upload&patient_id=' + encodeURIComponent(cfg.patientId)
                + '&parent_id=' + encodeURIComponent(category), {method: 'POST', body: form, credentials: 'same-origin'})
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then(function (html) {
                    // The upload page shows a failure as the heading of its upload form.
                    var parsed = new root.DOMParser().parseFromString(html, 'text/html');
                    var heading = parsed.querySelector('#documents_actions h3');
                    return heading ? heading.textContent.trim() : '';
                })
                .catch(function () {
                    return i18n.uploadFailed;
                });
        }

        function submitUpload() {
            var category = up.folder.value;
            var dicomFiles = Array.prototype.slice.call(up.dicom.files || []);
            if (pending.length === 0 && dicomFiles.length === 0) {
                setStatus(i18n.chooseFiles, true);
                return;
            }
            if (!category && onlyDicom(dicomFiles)) {
                // The server files image slices in the radiology folder whatever folder is sent.
                category = radiologyFolderId();
            }
            if (!category) {
                setStatus(i18n.chooseFolder, true);
                return;
            }
            var jobs = pending.map(function (file) {
                return {label: file.name, files: [file], dicom: []};
            });
            if (dicomFiles.length > 0) {
                jobs.push({label: dicomFiles[0].name, files: [], dicom: dicomFiles});
            }
            var failed = [];
            var failedFiles = [];
            var dicomFailed = false;
            up.submit.disabled = true;
            setStatus(i18n.uploading, false);
            restoreSession();
            jobs.reduce(function (chain, job) {
                return chain.then(function () {
                    return postUpload(category, job.files, job.dicom).then(function (error) {
                        if (error !== '') {
                            failed.push(job.label + ': ' + error);
                            failedFiles = failedFiles.concat(job.files);
                            dicomFailed = dicomFailed || job.dicom.length > 0;
                        }
                    });
                });
            }, root.Promise.resolve()).then(function () {
                pending = failedFiles;
                up.files.value = '';
                if (!dicomFailed) {
                    up.dicom.value = '';
                    up.destination.value = '';
                }
                showPending();
                if (failed.length > 0) {
                    setStatus(failed.join(' '), true);
                    return load();
                }
                setStatus(i18n.uploaded, false);
                return load().then(closeUpload);
            }).then(function () {
                up.submit.disabled = false;
            });
        }

        if (up.button && up.popover) {
            up.button.addEventListener('click', function () {
                var open = up.popover.hidden;
                up.popover.hidden = !open;
                up.button.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open && state.folder && up.folder) {
                    up.folder.value = String(state.folder.id);
                    if (root.jQuery) {
                        root.jQuery(up.folder).trigger('change.select2');
                    }
                }
            });
            up.cancel.addEventListener('click', closeUpload);
            up.submit.addEventListener('click', submitUpload);
            up.files.addEventListener('change', function () {
                addFiles(up.files.files);
                up.files.value = '';
            });
            up.drop.addEventListener('click', function () {
                up.files.click();
            });
            up.drop.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    up.files.click();
                }
            });
            ['dragenter', 'dragover'].forEach(function (name) {
                up.drop.addEventListener(name, function (event) {
                    event.preventDefault();
                    up.drop.classList.add('border-primary');
                });
            });
            ['dragleave', 'drop'].forEach(function (name) {
                up.drop.addEventListener(name, function (event) {
                    event.preventDefault();
                    up.drop.classList.remove('border-primary');
                });
            });
            up.drop.addEventListener('drop', function (event) {
                if (event.dataTransfer && event.dataTransfer.files) {
                    addFiles(event.dataTransfer.files);
                }
            });
        }

        // ---- resizable list columns ----
        function wireColumns() {
            var box = view.listBox;
            var table = box ? box.querySelector('table') : null;
            var cols = table ? table.querySelectorAll('col') : [];
            var heads = table ? table.querySelectorAll('thead th') : [];
            var grips = table ? table.querySelectorAll('.doc-col-grip') : [];
            if (!table || cols.length === 0 || cols.length !== heads.length) {
                return;
            }
            var MIN_COLUMN = 48;
            var applyWidths = function (widths) {
                var sum = 0;
                widths.forEach(function (width, index) {
                    cols[index].style.width = width + 'px';
                    sum += width;
                });
                table.style.width = sum + 'px';
            };
            var measure = function () {
                return Array.prototype.map.call(heads, function (head) {
                    return Math.round(head.getBoundingClientRect().width);
                });
            };
            try {
                var saved = JSON.parse(storedValue(':columns') || 'null');
                if (Array.isArray(saved) && saved.length === cols.length && saved.every(function (n) {
                    return typeof n === 'number' && n >= MIN_COLUMN && n < 5000;
                })) {
                    applyWidths(saved);
                }
            } catch (e) {
                // a bad saved value is ignored and the default widths stay
            }
            Array.prototype.forEach.call(grips, function (grip, index) {
                grip.addEventListener('dblclick', function () {
                    Array.prototype.forEach.call(cols, function (col) {
                        col.style.width = '';
                    });
                    table.style.width = '';
                    storeValue(':columns', '');
                });
                grip.addEventListener('pointerdown', function (event) {
                    var widths = measure();
                    if (widths.every(function (width) {
                        return width === 0;
                    })) {
                        return;
                    }
                    var startX = event.clientX;
                    var startWidth = widths[index];
                    event.preventDefault();
                    event.stopPropagation();
                    if (typeof grip.setPointerCapture === 'function' && event.pointerId !== undefined) {
                        try {
                            grip.setPointerCapture(event.pointerId);
                        } catch (e) {
                            // capture is optional
                        }
                    }
                    var move = function (moveEvent) {
                        var next = widths.slice();
                        next[index] = Math.max(MIN_COLUMN, Math.round(startWidth + moveEvent.clientX - startX));
                        applyWidths(next);
                    };
                    var stop = function () {
                        grip.removeEventListener('pointermove', move);
                        grip.removeEventListener('pointerup', stop);
                        grip.removeEventListener('pointercancel', stop);
                        storeValue(':columns', JSON.stringify(measure()));
                    };
                    grip.addEventListener('pointermove', move);
                    grip.addEventListener('pointerup', stop);
                    grip.addEventListener('pointercancel', stop);
                });
            });
        }

        // ---- classic tree: file type tags and full names on hover ----
        function tagClassicTree() {
            var tree = doc.getElementById('documents_list');
            if (!tree) {
                return;
            }
            Array.prototype.forEach.call(tree.querySelectorAll('img[src*="file3.png"]'), function (icon) {
                var link = icon.nextElementSibling;
                if (!link || link.tagName !== 'A' || icon.hasAttribute('data-doc-tagged')) {
                    return;
                }
                // The tree text is "<date> <file name>-<document id>".
                var text = (link.textContent || '').trim();
                var match = /\.([A-Za-z0-9]{1,5})-\d+$/.exec(text);
                var label = match ? (match[1].toLowerCase() === 'jpeg' ? 'JPG' : match[1].toUpperCase()) : 'FILE';
                var tag = make('span', typeClass(label), label);
                tag.title = (i18n.popupHint ? i18n.popupHint + ' ' : '') + text + ' (' + label + ')';
                tag.addEventListener('dblclick', function (event) {
                    event.preventDefault();
                    if (root.jQuery) {
                        root.jQuery(icon).trigger('dblclick');
                    }
                });
                icon.setAttribute('data-doc-tagged', 'true');
                icon.style.display = 'none';
                icon.parentNode.insertBefore(tag, icon);
                link.title = text + ' (' + label + ')';
            });
            Array.prototype.forEach.call(tree.querySelectorAll('a'), function (link) {
                if (!link.title && link.textContent.trim() !== '') {
                    link.title = link.textContent.trim();
                }
            });
        }

        // ---- wiring ----
        if (view.expandAll) {
            view.expandAll.addEventListener('click', function () {
                state.expanded = foldersWithChildren(state.tree);
                renderTree();
            });
        }
        if (view.collapseAll) {
            view.collapseAll.addEventListener('click', function () {
                state.expanded = {};
                renderTree();
            });
        }
        view.search.addEventListener('input', renderRows);
        view.sort.addEventListener('change', renderRows);
        view.emptyToggle.addEventListener('click', function () {
            state.showEmpty = !state.showEmpty;
            renderTree();
        });
        view.open.addEventListener('click', function () {
            if (state.selected) {
                root.open(openUrl(state.selected), '_blank', 'noopener');
            }
        });
        view.download.addEventListener('click', function () {
            if (state.selected) {
                root.location.assign(state.selected.download_url);
            }
        });
        view.btnFolders.addEventListener('click', function () {
            setView('folders', true);
        });
        view.btnClassic.addEventListener('click', function () {
            setView('classic', true);
        });

        // A forced classic page is the result of a classic action (upload or view), not a choice,
        // so it never overwrites what the user picked.
        wireLayout();
        wireColumns();
        tagClassicTree();
        var wanted = cfg.forceClassic ? 'classic' : (storedView() || cfg.defaultView);
        setView(wanted === 'folders' ? 'folders' : 'classic', false);
    }

    if (root.document) {
        // The page defines the configuration after this script tag, so it is read when the page
        // is ready and never at script load time.
        var start = function () {
            if (root.oeDocumentsFolderViewConfig) {
                init(root.document, root.oeDocumentsFolderViewConfig);
            }
        };
        if (root.document.readyState === 'loading') {
            root.document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
    }
})(typeof window !== 'undefined' ? window : globalThis);
