/** Provider-owned, zone-specific reusable drawing images. */
(function ($, window, document) {
    'use strict';
    var activeDialog = null;
    var operation = Object.create(null);
    function config(zone) {
        var node = document.querySelector('.eye-drawing-library-config[data-zone="' + zone + '"]');
        if (!node) throw new Error('This drawing is unavailable.');
        return node;
    }
    function options(zone) { return JSON.parse(config(zone).getAttribute('data-options') || '[]'); }
    function overlay(zone) { return (window.eyeDrawingOverlays || {})[zone]; }
    function update(zone, result, remember) {
        config(zone).setAttribute('data-options', JSON.stringify(result.options));
        if (remember) config(zone).setAttribute('data-default', result.key);
    }
    function request(zone, action, fields) {
        var node = config(zone);
        var body = new FormData();
        body.append('action', action);
        body.append('zone', zone);
        body.append('csrf_token_form', node.getAttribute('data-csrf'));
        Object.keys(fields || {}).forEach(function (key) { body.append(key, fields[key]); });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        return fetch(node.getAttribute('data-url'), {method: 'POST', credentials: 'same-origin', body: body}).then(function (response) {
            return response.json().then(function (result) {
                if (!response.ok || !result.success) throw new Error(result.error || 'The image could not be saved.');
                return result;
            });
        });
    }
    function dialog(className, title) {
        if (activeDialog) activeDialog.close();
        var element = document.createElement('dialog');
        element.className = 'eye-image-modal ' + className;
        var heading = document.createElement('div');
        heading.className = 'eye-image-modal-heading';
        var label = document.createElement('h3');
        label.textContent = title;
        label.id = 'eye-image-dialog-title';
        element.setAttribute('aria-labelledby', label.id);
        var close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('data-modal-close', '');
        close.setAttribute('aria-label', 'Close');
        close.textContent = '×';
        heading.append(label, close);
        element.appendChild(heading);
        var body = document.createElement('div');
        body.className = 'eye-image-modal-body';
        element.appendChild(body);
        var status = document.createElement('p');
        status.className = 'eye-image-modal-status';
        status.setAttribute('role', 'status');
        element.appendChild(status);
        document.body.appendChild(element);
        activeDialog = element;
        var previousFocus = document.activeElement;
        close.onclick = function () { element.close(); };
        element.addEventListener('close', function () {
            if (activeDialog === element) activeDialog = null;
            element.remove();
            if (previousFocus && previousFocus.isConnected) previousFocus.focus();
        });
        element.showModal();
        return {element: element, body: body, status: status};
    }
    function button(text, action) {
        var node = document.createElement('button');
        node.type = 'button';
        node.textContent = text;
        if (action) node.setAttribute('data-library-action', action);
        return node;
    }
    function busy(modal, value) {
        modal.element.setAttribute('aria-busy', value ? 'true' : 'false');
        modal.element.querySelectorAll('button,input').forEach(function (node) {
            if (!node.hasAttribute('data-modal-close')) node.disabled = value;
        });
    }
    function replaceBackground(zone, item) {
        if (operation[zone]) return Promise.reject(new Error('Please wait for this drawing to finish loading.'));
        operation[zone] = true;
        return window.eyeDrawingPrepareBackground(item).then(function (image) {
            if (!window.eyeDrawingBackgroundIsFresh(zone) && !window.confirm('Replace the current drawing and annotations with this background?')) return false;
            if (!window.eyeDrawingUseChosenBackground(zone, item.key)) throw new Error('This image is no longer available.');
            var layer = overlay(zone);
            if (layer && layer.clear) layer.clear();
            var canvas = document.getElementById('myCanvas_' + zone);
            $(canvas).one('eye:image-loaded', function () {
                if (typeof window.submit_canvas === 'function') window.submit_canvas(zone, true);
            });
            window.cReload(zone, image);
            return true;
        }).finally(function () { operation[zone] = false; });
    }
    function startNew(zone) {
        var item = options(zone).find(function (item) { return item.key === config(zone).getAttribute('data-default'); });
        if (!item) return;
        var control = document.getElementById('Clear_Canvas_' + zone);
        if (control) control.disabled = true;
        return replaceBackground(zone, item).catch(function (error) { window.alert(error.message); })
            .finally(function () { if (control) control.disabled = false; });
    }
    function openLibrary(zone) {
        var modal = dialog('eye-drawing-library-modal', 'Saved overlays · ' + zone);
        var selection = null;
        var tab = 'saved';
        var tabs = document.createElement('div');
        tabs.className = 'eye-library-tabs';
        var savedTab = button('Saved overlays');
        var backgroundTab = button('Backgrounds');
        savedTab.setAttribute('data-library-tab', 'saved');
        backgroundTab.setAttribute('data-library-tab', 'backgrounds');
        tabs.append(savedTab, backgroundTab);
        var grid = document.createElement('div');
        grid.className = 'eye-library-grid';
        var actions = document.createElement('div');
        actions.className = 'eye-library-actions';
        var insert = button('Insert overlay', 'insert');
        var background = button('Use as background', 'background');
        var makeDefault = button('Set my default', 'default');
        actions.append(insert, background, makeDefault);
        var renameKey = null;
        var renameForm = document.createElement('form');
        renameForm.className = 'eye-library-rename';
        renameForm.hidden = true;
        var renameLabel = document.createElement('label');
        renameLabel.textContent = 'Rename saved overlay';
        var renameInput = document.createElement('input');
        renameInput.type = 'text';
        renameInput.name = 'rename_overlay';
        renameInput.maxLength = 80;
        renameInput.required = true;
        renameLabel.appendChild(renameInput);
        var renameButtons = document.createElement('div');
        renameButtons.className = 'eye-library-actions';
        var renameSave = button('Save name');
        renameSave.type = 'submit';
        renameSave.setAttribute('data-library-rename-save', '');
        var renameCancel = button('Cancel');
        renameCancel.setAttribute('data-library-rename-cancel', '');
        renameButtons.append(renameSave, renameCancel);
        renameForm.append(renameLabel, renameButtons);
        function closeRename() { renameKey = null; renameForm.hidden = true; }
        function refreshActions() {
            insert.disabled = !selection || selection.saved !== true;
            background.disabled = !selection;
            makeDefault.disabled = !selection;
        }
        renameCancel.onclick = function () {
            var key = renameKey;
            closeRename();
            var origin = grid.querySelector('[data-library-rename="' + key + '"]');
            if (origin) origin.focus();
        };
        renameForm.onsubmit = function (event) {
            event.preventDefault();
            if (!renameKey) return;
            if (!renameInput.value.trim()) { modal.status.textContent = 'Enter a name for this overlay.'; renameInput.focus(); return; }
            var key = renameKey;
            busy(modal, true);
            request(zone, 'rename', {key: key, name: renameInput.value.trim()}).then(function (result) {
                update(zone, result, true);
                render(key);
                modal.status.textContent = 'Overlay renamed.';
            }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); refreshActions(); });
        };
        function deleteItem(item) {
            if (!window.confirm('Delete “' + item.label + '” from Saved overlays?')) return;
            var selectedKey = selection && selection.key;
            busy(modal, true);
            request(zone, 'delete', {key: item.key}).then(function (result) {
                update(zone, result, true);
                render(selectedKey);
                modal.status.textContent = 'Overlay deleted.';
            }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); refreshActions(); });
        }
        var upload = document.createElement('details');
        upload.className = 'eye-library-upload';
        var summary = document.createElement('summary');
        summary.textContent = 'Add an image';
        var file = document.createElement('input');
        file.type = 'file';
        file.name = 'library_upload';
        file.accept = 'image/png,image/jpeg';
        file.setAttribute('aria-label', 'JPEG or PNG image, up to 2 MB');
        var uploadName = document.createElement('input');
        uploadName.name = 'upload_name';
        uploadName.maxLength = 80;
        uploadName.placeholder = 'Image name';
        uploadName.setAttribute('aria-label', 'Image name');
        var uploadButton = button('Save image');
        uploadButton.setAttribute('data-library-upload', '');
        upload.append(summary, file, uploadName, uploadButton);
        modal.body.append(tabs, grid, renameForm, actions, upload);
        function render(selectedKey) {
            closeRename();
            grid.replaceChildren();
            selection = null;
            savedTab.setAttribute('aria-pressed', String(tab === 'saved'));
            backgroundTab.setAttribute('aria-pressed', String(tab !== 'saved'));
            var entries = options(zone).filter(function (item) { return tab === 'saved' ? item.saved === true : item.saved !== true; });
            entries.forEach(function (item) {
                var tile = document.createElement('div');
                tile.className = 'eye-library-tile';
                var card = button('');
                card.className = 'eye-library-card';
                card.setAttribute('data-library-key', item.key);
                card.setAttribute('aria-pressed', 'false');
                var thumbnail = document.createElement('img');
                thumbnail.src = item.url;
                thumbnail.alt = '';
                var name = document.createElement('span');
                name.textContent = item.label;
                card.append(thumbnail, name);
                if (item.key === config(zone).getAttribute('data-default')) {
                    var badge = document.createElement('small');
                    badge.textContent = 'My default';
                    card.appendChild(badge);
                }
                card.onclick = function () {
                    closeRename();
                    selection = item;
                    grid.querySelectorAll('[data-library-key]').forEach(function (node) { node.setAttribute('aria-pressed', String(node === card)); });
                    insert.disabled = item.saved !== true;
                    background.disabled = false;
                    makeDefault.disabled = false;
                };
                tile.appendChild(card);
                if (item.saved === true) {
                    var remove = button('×');
                    remove.className = 'eye-library-delete';
                    remove.setAttribute('data-library-delete', item.key);
                    remove.setAttribute('aria-label', 'Delete ' + item.label);
                    remove.title = 'Delete saved overlay';
                    remove.onclick = function () { deleteItem(item); };
                    var rename = button('Rename');
                    rename.className = 'eye-library-rename-button';
                    rename.setAttribute('data-library-rename', item.key);
                    rename.setAttribute('aria-label', 'Rename ' + item.label);
                    rename.onclick = function () {
                        card.click();
                        renameKey = item.key;
                        renameInput.value = item.label;
                        renameForm.hidden = false;
                        modal.status.textContent = '';
                        renameInput.focus();
                        renameInput.select();
                    };
                    tile.append(remove, rename);
                }
                grid.appendChild(tile);
                if (item.key === selectedKey) card.click();
            });
            if (!entries.length) {
                var empty = document.createElement('p');
                empty.className = 'eye-library-empty';
                empty.textContent = 'Use Save As below the canvas to save an overlay here.';
                grid.appendChild(empty);
            }
            refreshActions();
        }
        savedTab.onclick = function () { tab = 'saved'; render(); };
        backgroundTab.onclick = function () { tab = 'backgrounds'; render(); };
        insert.onclick = function () {
            if (!selection) return;
            var layer = overlay(zone);
            if (!layer || !layer.insertImage) { modal.status.textContent = 'Image annotations are unavailable.'; return; }
            busy(modal, true);
            layer.insertImage({url: selection.url, key: selection.key, name: selection.label}).then(function () {
                modal.element.close();
            }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); });
        };
        background.onclick = function () {
            if (!selection) return;
            busy(modal, true);
            replaceBackground(zone, selection).then(function (changed) { if (changed) modal.element.close(); })
                .catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); });
        };
        makeDefault.onclick = function () {
            if (!selection) return;
            var selectedKey = selection.key;
            busy(modal, true);
            request(zone, 'select', {key: selectedKey}).then(function (result) {
                update(zone, result, true);
                modal.status.textContent = 'Your default is saved for future drawings in this zone.';
                render(selectedKey);
            }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); render(selectedKey); });
        };
        uploadButton.onclick = function () {
            if (!file.files[0] || !uploadName.value.trim()) { modal.status.textContent = 'Choose an image and enter its name.'; return; }
            busy(modal, true);
            request(zone, 'save', {name: uploadName.value.trim(), image_file: file.files[0]}).then(function (result) {
                update(zone, result, false);
                tab = 'saved';
                file.value = ''; uploadName.value = ''; upload.open = false;
                render(result.key);
                modal.status.textContent = 'Image saved.';
            }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); render(selection && selection.key); });
        };
        render();
        busy(modal, true);
        modal.status.textContent = 'Loading…';
        request(zone, 'list').then(function (result) {
            update(zone, result, true);
            modal.status.textContent = '';
        }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () { busy(modal, false); render(); });
    }
    function toBlob(canvas) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) { if (blob) resolve(blob); else reject(new Error('The image could not be prepared.')); }, 'image/png');
        });
    }
    function openSaveAs(zone, ready) {
        if (!ready && typeof window.eyeDrawingInkReady === 'function') {
            return window.eyeDrawingInkReady(zone).then(function () { openSaveAs(zone, true); })
                .catch(function (error) { window.alert(error.message); });
        }
        var modal = dialog('eye-drawing-save-modal', 'Save As · ' + zone);
        var base = document.getElementById('myCanvas_' + zone);
        var layer = overlay(zone);
        var preview = document.createElement('img');
        preview.className = 'eye-save-preview';
        preview.alt = 'Image to save';
        var cropStage = document.createElement('div');
        cropStage.className = 'eye-save-crop-stage';
        cropStage.setAttribute('aria-label', 'Crop preview. Arrow keys move the selection; Shift and arrow keys resize it; Enter applies; Escape cancels.');
        var cropSelection = document.createElement('div');
        cropSelection.className = 'eye-save-crop-selection';
        cropSelection.hidden = true;
        cropStage.append(preview, cropSelection);
        var cropTools = document.createElement('div');
        cropTools.className = 'eye-library-actions';
        var cropButton = button('Crop');
        cropButton.setAttribute('data-library-crop', '');
        cropButton.setAttribute('aria-pressed', 'false');
        var cropReset = button('Reset crop');
        cropReset.setAttribute('data-library-crop-reset', '');
        cropReset.disabled = true;
        cropTools.append(cropButton, cropReset);
        var cropHint = document.createElement('small');
        cropHint.className = 'eye-save-crop-hint';
        cropHint.hidden = true;
        cropHint.textContent = 'Drag to select an area, then Apply crop.';
        var nameLabel = document.createElement('label');
        nameLabel.textContent = 'Name';
        var name = document.createElement('input');
        name.type = 'text'; name.name = 'library_name'; name.maxLength = 80; name.required = true;
        nameLabel.appendChild(name);
        var includeLabel = document.createElement('label');
        var include = document.createElement('input');
        include.type = 'checkbox'; include.name = 'include_background';
        includeLabel.append(include, document.createTextNode(' Include background'));
        var selectedLabel = document.createElement('label');
        var selected = document.createElement('input');
        selected.type = 'checkbox'; selected.name = 'selected_only';
        selected.checked = !!(layer && layer.hasSelected && layer.hasSelected());
        selectedLabel.append(selected, document.createTextNode(' Selected annotation only'));
        selectedLabel.hidden = !selected.checked;
        var note = document.createElement('small');
        note.className = 'eye-save-note';
        var save = button('Save As');
        save.setAttribute('data-library-save', '');
        modal.body.append(cropStage, cropTools, cropHint, nameLabel, selectedLabel, includeLabel, note, save);
        var prepared = null;
        var originalPreview = null;
        var cropRect = null;
        var cropActive = false;
        var dragStart = null;
        function showPreview(canvas) {
            cropStage.style.width = 'min(100%, ' + Math.max(1, 300 * canvas.width / canvas.height) + 'px)';
            preview.src = canvas.toDataURL('image/png');
        }
        function showCropSelection() {
            if (!cropRect || !prepared) return;
            cropSelection.style.left = (100 * cropRect.x / prepared.width) + '%';
            cropSelection.style.top = (100 * cropRect.y / prepared.height) + '%';
            cropSelection.style.width = (100 * cropRect.width / prepared.width) + '%';
            cropSelection.style.height = (100 * cropRect.height / prepared.height) + '%';
            cropSelection.hidden = false;
        }
        function endCrop() {
            cropActive = false;
            dragStart = null;
            cropSelection.hidden = true;
            cropStage.classList.remove('eye-crop-active');
            cropStage.removeAttribute('tabindex');
            cropHint.hidden = true;
            cropButton.textContent = 'Crop';
            cropButton.setAttribute('aria-pressed', 'false');
            cropReset.disabled = !prepared || prepared === originalPreview;
        }
        function applyCrop() {
            if (!prepared || !cropRect) return;
            var x = Math.max(0, Math.min(prepared.width - 1, Math.floor(cropRect.x)));
            var y = Math.max(0, Math.min(prepared.height - 1, Math.floor(cropRect.y)));
            var width = Math.max(1, Math.min(prepared.width - x, Math.ceil(cropRect.width)));
            var height = Math.max(1, Math.min(prepared.height - y, Math.ceil(cropRect.height)));
            var cropped = document.createElement('canvas');
            cropped.width = width; cropped.height = height;
            cropped.getContext('2d').drawImage(prepared, x, y, width, height, 0, 0, width, height);
            prepared = cropped;
            showPreview(cropped);
            cropReset.disabled = false;
            endCrop();
        }
        cropButton.onclick = function () {
            if (!prepared) return;
            if (cropActive) { applyCrop(); return; }
            cropActive = true;
            cropRect = {x: 0, y: 0, width: prepared.width, height: prepared.height};
            cropStage.classList.add('eye-crop-active');
            cropStage.tabIndex = 0;
            cropHint.hidden = false;
            cropButton.textContent = 'Apply crop';
            cropButton.setAttribute('aria-pressed', 'true');
            cropReset.disabled = false;
            showCropSelection();
            cropStage.focus();
        };
        cropReset.onclick = function () {
            if (!originalPreview) return;
            prepared = originalPreview;
            showPreview(prepared);
            cropRect = null;
            endCrop();
            cropReset.disabled = true;
        };
        function cropPoint(event) {
            var bounds = preview.getBoundingClientRect();
            return {
                x: Math.max(0, Math.min(prepared.width, (event.clientX - bounds.left) * prepared.width / bounds.width)),
                y: Math.max(0, Math.min(prepared.height, (event.clientY - bounds.top) * prepared.height / bounds.height))
            };
        }
        cropStage.addEventListener('pointerdown', function (event) {
            if (!cropActive || !prepared || event.button !== 0) return;
            event.preventDefault();
            dragStart = cropPoint(event);
            cropStage.setPointerCapture(event.pointerId);
        });
        cropStage.addEventListener('pointermove', function (event) {
            if (!dragStart || !cropActive) return;
            var point = cropPoint(event);
            cropRect = {x: Math.min(dragStart.x, point.x), y: Math.min(dragStart.y, point.y),
                width: Math.max(1, Math.abs(point.x - dragStart.x)), height: Math.max(1, Math.abs(point.y - dragStart.y))};
            showCropSelection();
        });
        cropStage.addEventListener('pointerup', function (event) {
            if (!dragStart) return;
            dragStart = null;
            if (cropStage.hasPointerCapture(event.pointerId)) cropStage.releasePointerCapture(event.pointerId);
        });
        cropStage.addEventListener('pointercancel', function () { dragStart = null; });
        cropStage.addEventListener('keydown', function (event) {
            if (!cropActive || !cropRect) return;
            if (event.key === 'Enter') { event.preventDefault(); applyCrop(); cropButton.focus(); return; }
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); endCrop(); cropButton.focus(); return; }
            if (!/^Arrow(Left|Right|Up|Down)$/.test(event.key)) return;
            event.preventDefault();
            var delta = event.key === 'ArrowLeft' || event.key === 'ArrowUp' ? -1 : 1;
            var horizontal = event.key === 'ArrowLeft' || event.key === 'ArrowRight';
            if (event.shiftKey) {
                if (horizontal) cropRect.width = Math.max(1, Math.min(prepared.width - cropRect.x, cropRect.width + delta));
                else cropRect.height = Math.max(1, Math.min(prepared.height - cropRect.y, cropRect.height + delta));
            } else {
                if (horizontal) cropRect.x = Math.max(0, Math.min(prepared.width - cropRect.width, cropRect.x + delta));
                else cropRect.y = Math.max(0, Math.min(prepared.height - cropRect.height, cropRect.y + delta));
            }
            showCropSelection();
        });
        var revision = 0;
        var flattened = typeof window.eyeDrawingHasFlattenedBase === 'function' && window.eyeDrawingHasFlattenedBase(zone);
        function renderPreview() {
            var current = ++revision;
            prepared = null;
            originalPreview = null;
            cropRect = null;
            endCrop();
            cropButton.disabled = true;
            cropReset.disabled = true;
            save.disabled = true;
            var selectedOnly = selected.checked;
            include.disabled = selectedOnly || flattened;
            if (selectedOnly) include.checked = false;
            else if (flattened) include.checked = true;
            note.textContent = flattened && !selectedOnly ? 'This saved canvas includes its background. Select an annotation to save it separately.' : '';
            var result;
            if (selectedOnly && layer) result = layer.exportReusable({selectedOnly: true});
            else if (include.checked && layer) result = layer.exportCompositeCanvas(base);
            else {
                var canvas = document.createElement('canvas');
                canvas.width = base.width; canvas.height = base.height;
                var context = canvas.getContext('2d');
                if (include.checked) context.drawImage(base, 0, 0);
                else if (typeof window.eyeDrawingInkCanvas === 'function' && window.eyeDrawingInkCanvas(zone)) context.drawImage(window.eyeDrawingInkCanvas(zone), 0, 0);
                result = layer ? layer.exportReusable({selectedOnly: false}).then(function (annotations) { context.drawImage(annotations, 0, 0); return canvas; }) : Promise.resolve(canvas);
            }
            Promise.resolve(result).then(function (canvas) {
                if (current !== revision || !modal.element.isConnected) return;
                prepared = canvas;
                originalPreview = canvas;
                showPreview(canvas);
                cropButton.disabled = false;
                save.disabled = false;
            }).catch(function (error) { if (current === revision) modal.status.textContent = error.message || 'The preview could not be prepared.'; });
        }
        include.onchange = renderPreview;
        selected.onchange = renderPreview;
        save.onclick = function () {
            if (!prepared) return;
            if (!name.value.trim()) { modal.status.textContent = 'Enter a name for this image.'; name.focus(); return; }
            if (cropActive) applyCrop();
            busy(modal, true);
            var canvas = prepared;
            toBlob(canvas).then(function (blob) { return request(zone, 'save', {name: name.value.trim(), image_file: new File([blob], 'drawing.png', {type: 'image/png'})}); })
                .then(function (result) {
                    update(zone, result, false);
                    modal.element.close();
                }).catch(function (error) { modal.status.textContent = error.message; }).finally(function () {
                    busy(modal, false);
                    include.disabled = selected.checked || flattened;
                    cropButton.disabled = !prepared;
                    cropReset.disabled = !prepared || prepared === originalPreview;
                });
        };
        renderPreview();
        name.focus();
    }
    window.eyeDrawingLibrary = {open: openLibrary, saveAs: openSaveAs, startNew: startNew};
    $(function () {
        $(document).on('click', '[data-konva-action="library"]', function (event) {
            event.preventDefault();
            openLibrary(this.getAttribute('data-zone') || $(this).closest('[data-zone]').attr('data-zone'));
        });
        $(document).on('click', '[data-drawing-save-as]', function (event) { event.preventDefault(); openSaveAs(this.getAttribute('data-drawing-save-as')); });
    });
})(jQuery, window, document);
