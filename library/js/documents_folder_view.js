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

    function countEmptyFolders(nodes) {
        var empty = 0;
        nodes.forEach(function (node) {
            if (node.count === 0) {
                empty += 1;
            }
            empty += countEmptyFolders(node.children);
        });
        return empty;
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

    root.OeDocumentsFolderView = {
        buildFolderTree: buildFolderTree,
        countEmptyFolders: countEmptyFolders,
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
            search: el('docSearch'),
            sort: el('docSort'),
            crumb: el('docCrumb'),
            rows: el('docRows'),
            noResults: el('docNoResults'),
            previewTitle: el('docPreviewTitle'),
            previewMeta: el('docPreviewMeta'),
            previewBody: el('docPreviewBody'),
            open: el('docOpen'),
            download: el('docDownload')
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
            var addButton = function (label, count, depth, active, onClick) {
                var button = make('button', 'btn btn-sm btn-block text-left d-flex justify-content-between align-items-center mb-1 ' + (active ? 'btn-primary' : 'btn-light'));
                button.type = 'button';
                button.style.paddingLeft = (0.5 + depth * 1) + 'rem';
                if (active) {
                    button.setAttribute('aria-current', 'true');
                }
                button.appendChild(make('span', 'text-break', label));
                button.appendChild(make('span', 'badge badge-secondary ml-2', String(count)));
                button.addEventListener('click', onClick);
                view.tree.appendChild(button);
            };
            addButton(i18n.allDocuments, state.documents.length, 0, state.folder === null, function () {
                state.folder = null;
                render();
            });
            var walk = function (nodes, depth) {
                nodes.forEach(function (node) {
                    if (node.count === 0 && !state.showEmpty) {
                        return;
                    }
                    addButton(node.name, node.count, depth, state.folder === node, function () {
                        state.folder = node;
                        render();
                    });
                    walk(node.children, depth + 1);
                });
            };
            walk(state.tree, 1);
            var empty = countEmptyFolders(state.tree);
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
                tr.appendChild(make('td', 'text-break doc-name', item.name));
                tr.appendChild(make('td', 'text-break', item.path));
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
            if (state.folder) {
                up.folder.value = String(state.folder.id);
            }
            try {
                if (root.jQuery && root.jQuery.fn && typeof root.jQuery.fn.select2 === 'function') {
                    root.jQuery(up.folder).select2({dropdownParent: root.jQuery(up.popover), width: '100%'});
                }
            } catch (e) {
                // a plain select is fine when select2 cannot start
            }
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

        function submitUpload() {
            var category = up.folder.value;
            var dicomFiles = up.dicom.files || [];
            if (pending.length === 0 && dicomFiles.length === 0) {
                setStatus(i18n.chooseFiles, true);
                return;
            }
            if (!category) {
                setStatus(i18n.chooseFolder, true);
                return;
            }
            var form = new root.FormData();
            form.append('MAX_FILE_SIZE', '64000000');
            form.append('patient_id', cfg.patientId);
            form.append('category_id', category);
            form.append('process', 'true');
            form.append('csrf_token_form', cfg.csrfToken);
            pending.forEach(function (file) {
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
            up.submit.disabled = true;
            setStatus(i18n.uploading, false);
            restoreSession();
            root.fetch(cfg.webroot + '/controller.php?document&upload&patient_id=' + encodeURIComponent(cfg.patientId)
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
                    var error = heading ? heading.textContent.trim() : '';
                    if (error !== '') {
                        setStatus(error, true);
                        return null;
                    }
                    pending = [];
                    up.files.value = '';
                    up.dicom.value = '';
                    up.destination.value = '';
                    showPending();
                    setStatus(i18n.uploaded, false);
                    return load().then(closeUpload);
                })
                .catch(function () {
                    setStatus(i18n.uploadFailed, true);
                })
                .then(function () {
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

        // ---- wiring ----
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
        var wanted = cfg.forceClassic ? 'classic' : (storedView() || cfg.defaultView);
        setView(wanted === 'folders' ? 'folders' : 'classic', false);
    }

    if (root.document && root.oeDocumentsFolderViewConfig) {
        var start = function () {
            init(root.document, root.oeDocumentsFolderViewConfig);
        };
        if (root.document.readyState === 'loading') {
            root.document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
    }
})(typeof window !== 'undefined' ? window : globalThis);
