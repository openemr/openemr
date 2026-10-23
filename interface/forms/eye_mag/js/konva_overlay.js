/**
 * Editable drawing annotations backed by OpenEMR's shared Konva asset.
 * The object JSON remains editable; save.php separately stores a flattened JPEG.
 */
(function ($, window) {
    'use strict';

    $(function () {
        if (typeof window.Konva === 'undefined') {
            return;
        }
        window.eyeDrawingOverlays = window.eyeDrawingOverlays || {};

        $('.eye-konva-stage').each(function () {
        var container = this;
        var zone = String($(container).data('zone') || '');
        var baseCanvas = document.getElementById('myCanvas_' + zone);
        if (!zone || !baseCanvas) {
            return;
        }

        var width = parseInt(baseCanvas.getAttribute('width'), 10) || 450;
        var height = parseInt(baseCanvas.getAttribute('height'), 10) || 250;
        var stage = new window.Konva.Stage({ container: container, width: width, height: height });
        var objectLayer = new window.Konva.Layer();
        var transformerLayer = new window.Konva.Layer();
        var transformer = new window.Konva.Transformer({
            rotateEnabled: true,
            keepRatio: false,
            enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
            boundBoxFunc: function (oldBox, newBox) {
                return (Math.abs(newBox.width) < 12 || Math.abs(newBox.height) < 12) ? oldBox : newBox;
            }
        });
        var arrowStartHandle = new window.Konva.Circle({
            radius: 5, fill: '#cdebd3', stroke: '#18324f', strokeWidth: 1,
            draggable: true, hitStrokeWidth: 18, visible: false
        });
        var arrowEndHandle = arrowStartHandle.clone({ visible: false });
        var selected = null;
        var initialJson = $('#' + zone + '_KONVA_JSON').val() || '[]';
        var saveTimer = null;
        var toolbar = $('#' + zone + '_KONVA_TOOLS');
        var toolSaveQueue = Promise.resolve();

        // Undo/redo stacks store serialized JSON snapshots
        var undoStack = [];
        var redoStack = [];
        var MAX_UNDO_STACK = 50;

        function captureSnapshot() {
            return serialize();
        }

        function pushSnapshot() {
            var snap = captureSnapshot();
            // Don't push duplicate consecutive snapshots
            if (undoStack.length > 0 && undoStack[undoStack.length - 1] === snap) {
                return;
            }
            undoStack.push(snap);
            if (undoStack.length > MAX_UNDO_STACK) {
                undoStack.shift();
            }
            // Clear redo stack on new action
            redoStack = [];
        }

        function undo() {
            var current = captureSnapshot();
            if (!undoStack.length || (undoStack.length === 1 && current === undoStack[0])) {
                return;
            }
            if (current === undoStack[undoStack.length - 1]) {
                undoStack.pop();
            }
            redoStack.push(current);
            if (redoStack.length > MAX_UNDO_STACK) {
                redoStack.shift();
            }
            loadJson(undoStack[undoStack.length - 1]);
            scheduleSave();
        }

        function redo() {
            if (!redoStack.length) {
                return;
            }
            var next = redoStack.pop();
            loadJson(next);
            undoStack.push(next);
            if (undoStack.length > MAX_UNDO_STACK) {
                undoStack.shift();
            }
            scheduleSave();
        }

        stage.add(objectLayer);
        transformerLayer.add(transformer);
        transformerLayer.add(arrowStartHandle, arrowEndHandle);
        stage.add(transformerLayer);

        function positionArrowHandles(arrow) {
            if (!arrow || arrow.getClassName() !== 'Arrow') {
                arrowStartHandle.hide();
                arrowEndHandle.hide();
                return;
            }
            var points = arrow.points();
            var transform = arrow.getAbsoluteTransform();
            var start = transform.point({ x: points[0], y: points[1] });
            var end = transform.point({ x: points[points.length - 2], y: points[points.length - 1] });
            arrowStartHandle.position(start);
            arrowEndHandle.position(end);
            arrowStartHandle.show();
            arrowEndHandle.show();
        }

        function moveArrowEndpoint(handle, isStart) {
            if (!selected || selected.getClassName() !== 'Arrow') {
                return;
            }
            var local = selected.getAbsoluteTransform().copy().invert().point(handle.position());
            var points = selected.points().slice();
            var index = isStart ? 0 : points.length - 2;
            points[index] = local.x;
            points[index + 1] = local.y;
            selected.points(points);
            objectLayer.batchDraw();
        }

        function annotationRoot(node) {
            if (!node || node === stage) {
                return null;
            }
            if (node.hasName && node.hasName('eye-overlay-object')) {
                return node;
            }
            return node.findAncestor ? node.findAncestor('.eye-overlay-object', true) : null;
        }

        function selectNode(node) {
            selected = node || null;
            var isArrow = selected && selected.getClassName() === 'Arrow';
            transformer.enabledAnchors(selected && selected.getClassName() === 'Label'
                ? ['top-left', 'top-right', 'middle-left', 'middle-right', 'bottom-left', 'bottom-right']
                : ['top-left', 'top-right', 'bottom-left', 'bottom-right']);
            transformer.nodes(selected && !isArrow ? [selected] : []);
            positionArrowHandles(isArrow ? selected : null);
            transformerLayer.batchDraw();
        }

        function serialize() {
            var objects = [];
            objectLayer.getChildren().forEach(function (node) {
                if (node.hasName('eye-overlay-object')) {
                    objects.push(node.toObject());
                }
            });
            var json = JSON.stringify(objects);
            $('#' + zone + '_KONVA_JSON').val(json);
            return json;
        }

        function changed() {
            pushSnapshot();
            scheduleSave();
        }

        function scheduleSave() {
            serialize();
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(function () {
                if (typeof window.submit_canvas === 'function') {
                    window.submit_canvas(zone, true);
                }
            }, 500);
        }

        function bindNode(node) {
            node.name('eye-overlay-object');
            node.draggable(true);
            if (node.getClassName() === 'Label') {
                var tag = node.findOne('Tag');
                if (tag) {
                    // Selection handles provide the outline; never bake one into text.
                    tag.strokeEnabled(false);
                }
            }
            node.on('dragmove transform', function () {
                if (node === selected && node.getClassName() === 'Arrow') {
                    positionArrowHandles(node);
                    transformerLayer.batchDraw();
                }
            });
            node.on('dragend transformend', changed);
            node.on('dblclick dbltap', function () {
                if (node.getClassName() === 'Label') {
                    editText(node);
                }
            });
            return node;
        }

        function isImageObject(node) {
            return node && node.getClassName() === 'Group' && !!node.getAttr('eyeImageUrl');
        }

        function libraryUrl(value) {
            var url;
            try {
                url = new URL(String(value || ''), window.location.href);
            } catch (error) {
                throw new Error('The saved image URL is invalid.');
            }
            // Library images are stable local files. Never restore a data URL,
            // temporary upload URL, remote image, or a patient document URL.
            if (url.origin !== window.location.origin || url.username || url.password || url.search || url.hash ||
                !/^(?:\/[a-z0-9_.~-]+)*\/sites\/[a-z0-9_.~-]+\/images\/eye_mag\/providers\/[1-9][0-9]*\/(HPI|PMH|EXT|ANTSEG|RETINA|SDRETINA|NEURO|IMPPLAN)\/(library\/[a-f0-9]{16}\.png|[a-f0-9]+\.jpe?g)$/i.test(url.pathname)) {
                throw new Error('Choose an image from the saved drawing library.');
            }
            return url.pathname;
        }

        function isLiveImage(node) {
            return node.getLayer() === objectLayer;
        }

        function imageObject(data, savedAttrs) {
            var attrs = savedAttrs || {};
            var imageAttrs = {
                eyeImageUrl: libraryUrl(data.url),
                eyeImageKey: String(data.key || '').slice(0, 200),
                eyeImageName: String(data.name || '').slice(0, 200),
                eyeImageOpaque: attrs.eyeImageOpaque === true,
                width: 100, height: 100, opacity: 1
            };
            ['x', 'y', 'scaleX', 'scaleY', 'rotation', 'offsetX', 'offsetY', 'skewX', 'skewY'].forEach(function (key) {
                if (typeof attrs[key] === 'number' && isFinite(attrs[key])) {
                    imageAttrs[key] = attrs[key];
                }
            });
            ['width', 'height'].forEach(function (key) {
                if (typeof attrs[key] === 'number' && isFinite(attrs[key]) && attrs[key] > 0 && attrs[key] <= 32768) {
                    imageAttrs[key] = attrs[key];
                }
            });
            var node = new window.Konva.Group(imageAttrs);
            node.add(new window.Konva.Rect({
                name: 'eye-image-backing', x: 0, y: 0,
                width: imageAttrs.width, height: imageAttrs.height,
                fill: '#ffffff', visible: imageAttrs.eyeImageOpaque, listening: false
            }));
            node.add(new window.Konva.Image({
                name: 'eye-image-content', x: 0, y: 0,
                width: imageAttrs.width, height: imageAttrs.height
            }));
            return bindNode(node);
        }

        function hydrateImage(node, fitToStage) {
            var image = new Image();
            var settled = false;
            var ready = new Promise(function (resolve, reject) {
                function finish(error, cancelled) {
                    if (settled) {
                        return;
                    }
                    settled = true;
                    image.onload = null;
                    image.onerror = null;
                    node._eyeImageCancel = null;
                    if (error && isLiveImage(node)) {
                        reject(error);
                    } else {
                        resolve(!cancelled && isLiveImage(node) ? node : null);
                    }
                }
                node._eyeImageCancel = function () {
                    finish(null, true);
                    image.src = '';
                };
                image.onload = function () {
                    if (!isLiveImage(node)) {
                        finish();
                        return;
                    }
                    if (fitToStage) {
                        var ratio = Math.min(1, width * 0.8 / image.naturalWidth, height * 0.8 / image.naturalHeight);
                        node.width(Math.max(1, image.naturalWidth * ratio));
                        node.height(Math.max(1, image.naturalHeight * ratio));
                        node.position({ x: (width - node.width()) / 2, y: (height - node.height()) / 2 });
                    }
                    node.findOne('.eye-image-backing').size({ width: node.width(), height: node.height() });
                    node.findOne('.eye-image-content').size({ width: node.width(), height: node.height() }).image(image);
                    objectLayer.batchDraw();
                    if (selected === node) {
                        transformer.forceUpdate();
                        transformerLayer.batchDraw();
                    }
                    finish();
                };
                image.onerror = function () {
                    finish(new Error('The saved drawing image could not be opened.'));
                };
                image.src = node.getAttr('eyeImageUrl');
            });
            node._eyeImageReady = ready;
            // Initial restoration has no caller to catch failures. Keep the
            // rejection available to exports without an unhandled rejection.
            ready.catch(function () {});
            return ready;
        }

        function imageObjects() {
            return objectLayer.getChildren().filter(isImageObject);
        }

        function waitForImages() {
            var images = imageObjects();
            return Promise.all(images.map(function (node) {
                return node._eyeImageReady.catch(function (error) {
                    if (isLiveImage(node)) {
                        throw error;
                    }
                });
            })).then(function () {
                var current = imageObjects();
                if (current.length !== images.length || current.some(function (node, index) { return node !== images[index]; })) {
                    return waitForImages();
                }
            });
        }

        function insertImage(data) {
            var node;
            try {
                node = imageObject(data || {});
            } catch (error) {
                return Promise.reject(error);
            }
            objectLayer.add(node);
            setMode('select', false);
            selectNode(node);
            return hydrateImage(node, true).then(function (result) {
                if (result && isLiveImage(node)) {
                    changed();
                }
                return result;
            }).catch(function (error) {
                if (isLiveImage(node)) {
                    if (selected === node) {
                        selectNode(null);
                    }
                    node.destroy();
                    objectLayer.batchDraw();
                    serialize();
                }
                throw error;
            });
        }

        function cancelImage(node) {
            if (node._eyeImageCancel) {
                node._eyeImageCancel();
            }
        }

        function clearObjects(silent) {
            selectNode(null);
            objectLayer.getChildren().slice().forEach(function (node) {
                if (node.hasName('eye-overlay-object')) {
                    cancelImage(node);
                    node.destroy();
                }
            });
            objectLayer.draw();
            if (silent) {
                serialize();
            } else {
                changed();
            }
        }

        function loadJson(json) {
            clearObjects(true);
            var objects;
            try {
                objects = JSON.parse(json || '[]');
            } catch (error) {
                objects = [];
            }
            if (!Array.isArray(objects)) {
                objects = [];
            }
            objects.forEach(function (objectData) {
                try {
                    var attrs = objectData.attrs || {};
                    if (objectData.className === 'Group' && attrs.eyeImageUrl) {
                        var imageNode = imageObject({ url: attrs.eyeImageUrl, key: attrs.eyeImageKey, name: attrs.eyeImageName }, attrs);
                        objectLayer.add(imageNode);
                        hydrateImage(imageNode, false);
                    } else {
                        var node = window.Konva.Node.create(JSON.stringify(objectData));
                        objectLayer.add(bindNode(node));
                    }
                } catch (error) {
                    // Preserve the rest of the drawing if one legacy object is malformed.
                }
            });
            objectLayer.draw();
            serialize();
        }

        function currentColor() {
            return $('#selColor_' + zone).val() || '#18324f';
        }

        function applyTextColor() {
            if (!selected || selected.getClassName() !== 'Label') {
                return;
            }
            var textNode = selected.findOne('Text');
            if (textNode && textNode.fill() !== currentColor()) {
                textNode.fill(currentColor());
                objectLayer.batchDraw();
                changed();
            }
        }

        function addText() {
            var label = new window.Konva.Label({ x: 175, y: 105 });
            label.add(new window.Konva.Tag({
                fill: '#ffffff', opacity: 1, strokeEnabled: false, cornerRadius: 2
            }));
            label.add(new window.Konva.Text({
                text: 'Text', fill: currentColor(), fontFamily: 'Arial', fontSize: 18, padding: 5
            }));
            objectLayer.add(bindNode(label));
            selectNode(label);
            objectLayer.draw();
            changed();
            editText(label);
        }

        function addShape(type) {
            var common = {
                x: 185, y: 90, stroke: currentColor(), strokeWidth: 3,
                fill: '#ffffff', opacity: 1
            };
            var node;
            if (type === 'ellipse') {
                node = new window.Konva.Ellipse($.extend(common, { radiusX: 48, radiusY: 32 }));
            } else if (type === 'arrow') {
                node = new window.Konva.Arrow({
                    x: 165, y: 125, points: [0, 0, 105, 0], pointerLength: 12,
                    pointerWidth: 12, stroke: currentColor(), fill: currentColor(), strokeWidth: 3
                });
            } else {
                node = new window.Konva.Rect($.extend(common, { width: 100, height: 65, cornerRadius: 2 }));
            }
            objectLayer.add(bindNode(node));
            selectNode(node);
            objectLayer.draw();
            changed();
        }

        function editText(label) {
            var textNode = label.findOne('Text');
            if (!textNode) {
                return;
            }
            var stageRect = stage.container().getBoundingClientRect();
            var position = label.getAbsolutePosition();
            var scaleX = stageRect.width / width;
            var scaleY = stageRect.height / height;
            var textScaleX = scaleX * Math.abs(label.scaleX());
            var textScaleY = scaleY * Math.abs(label.scaleY());
            var textarea = document.createElement('textarea');
            textarea.value = textNode.text();
            textarea.setAttribute('aria-label', 'Edit image text');
            document.body.appendChild(textarea);
            textarea.style.position = 'fixed';
            textarea.style.zIndex = '10000001';
            textarea.style.left = (stageRect.left + position.x * scaleX) + 'px';
            textarea.style.top = (stageRect.top + position.y * scaleY) + 'px';
            textarea.style.boxSizing = 'border-box';
            textarea.style.resize = 'both';
            textarea.style.width = Math.max(90, textNode.width() * textScaleX) + 'px';
            textarea.style.height = Math.max(34, textNode.height() * textScaleY) + 'px';
            textarea.style.font = (textNode.fontSize() * textScaleY) + 'px Arial';
            textarea.style.color = textNode.fill();
            textarea.style.padding = '4px';
            textarea.style.border = '1px solid #18324f';
            textarea.style.background = '#fff';
            var initialEditorRect = textarea.getBoundingClientRect();
            textarea.focus();
            textarea.select();

            function finish(save) {
                if (!textarea.parentNode) {
                    return;
                }
                if (save) {
                    var editorRect = textarea.getBoundingClientRect();
                    if (Math.abs(editorRect.width - initialEditorRect.width) > 0.5) {
                        textNode.width(Math.max(12, editorRect.width / textScaleX));
                    }
                    if (Math.abs(editorRect.height - initialEditorRect.height) > 0.5) {
                        textNode.height(Math.max(12, editorRect.height / textScaleY));
                    }
                    textNode.text(textarea.value || 'Text');
                    objectLayer.draw();
                    transformer.forceUpdate();
                    changed();
                }
                textarea.parentNode.removeChild(textarea);
            }
            textarea.addEventListener('blur', function () { finish(true); });
            textarea.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    finish(false);
                } else if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    finish(true);
                }
            });
        }

        function setMode(mode, remember) {
            mode = mode === 'draw' ? 'draw' : 'select';
            var drawMode = mode === 'draw';
            $(container).toggleClass('is-draw-mode', drawMode);
            $('#' + zone + '_KONVA_TOOLS [data-konva-tool]').removeClass('is-active');
            $('#' + zone + '_KONVA_TOOLS [data-konva-tool="' + mode + '"]').addClass('is-active');
            if (drawMode) {
                selectNode(null);
            }
            if (remember !== false) {
                // Serialize per-zone writes so a quick draw/select toggle cannot
                // leave the earlier preference as the last database update.
                toolSaveQueue = toolSaveQueue.then(function () {
                    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
                    return $.ajax({
                        type: 'POST', url: toolbar.attr('data-preference-url'), dataType: 'json',
                        data: {action: 'drawing_tool_preference', zone: zone, tool: mode,
                            csrf_token_form: toolbar.attr('data-csrf')}
                    });
                }).catch(function () {
                    window.console.warn('Could not save the drawing tool preference for ' + zone);
                });
            }
        }

        function toggleTransparency() {
            if (!selected) {
                return;
            }
            if (isImageObject(selected)) {
                var opaque = !selected.getAttr('eyeImageOpaque');
                selected.setAttr('eyeImageOpaque', opaque);
                selected.opacity(1);
                selected.findOne('.eye-image-backing').visible(opaque);
                selected.findOne('.eye-image-content').opacity(1);
            } else if (selected.getClassName() === 'Label') {
                var tag = selected.findOne('Tag');
                if (tag) {
                    // Older saved labels used zero opacity for a clear background.
                    var wasTransparent = !tag.fillEnabled() || tag.opacity() === 0;
                    selected.opacity(1);
                    tag.opacity(1);
                    tag.fillEnabled(wasTransparent);
                }
            } else if (selected.getClassName() !== 'Arrow') {
                selected.opacity(1);
                selected.fillEnabled(!selected.fillEnabled());
            }
            objectLayer.draw();
            changed();
        }

        function deleteSelected() {
            if (!selected) {
                return;
            }
            var node = selected;
            selectNode(null);
            cancelImage(node);
            node.destroy();
            objectLayer.draw();
            changed();
        }

        function svgCanvas() {
            return new Promise(function (resolve) {
                var svg = document.getElementById(zone + '_structured_overlay');
                if (!svg || $(svg).closest('.eye-drawing-stage').attr('data-structured-background') === '0') {
                    resolve(null);
                    return;
                }
                var clone = svg.cloneNode(true);
                clone.classList.remove('nodisplay');
                clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                clone.setAttribute('width', width);
                clone.setAttribute('height', height);
                Array.prototype.forEach.call(clone.querySelectorAll('.neuro-overlay-value'), function (node) {
                    node.setAttribute('style', 'fill:#111;font-family:Arial,sans-serif;font-size:16px;font-weight:600');
                    if (!node.textContent) {
                        node.setAttribute('display', 'none');
                    }
                });
                Array.prototype.forEach.call(clone.querySelectorAll('.neuro-overlay-mark'), function (node) {
                    node.setAttribute('style', 'fill:none;stroke:#d41414;stroke-width:2.5px');
                    if (!node.classList.contains('is-visible')) {
                        node.setAttribute('display', 'none');
                    }
                });
                var blob = new Blob([new XMLSerializer().serializeToString(clone)], { type: 'image/svg+xml' });
                var url = URL.createObjectURL(blob);
                var image = new Image();
                image.onload = function () {
                    var result = document.createElement('canvas');
                    result.width = width;
                    result.height = height;
                    result.getContext('2d').drawImage(image, 0, 0, width, height);
                    URL.revokeObjectURL(url);
                    resolve(result);
                };
                image.onerror = function () {
                    URL.revokeObjectURL(url);
                    resolve(null);
                };
                image.src = url;
            });
        }

        function exportReusable(options) {
            options = options || {};
            return waitForImages().then(function () {
                serialize();
                if (options.selectedOnly) {
                    if (!selected || selected.getLayer() !== objectLayer) {
                        throw new Error('Select an object to save.');
                    }
                    var bounds = selected.getClientRect({ relativeTo: objectLayer });
                    var x = Math.floor(bounds.x);
                    var y = Math.floor(bounds.y);
                    return selected.toCanvas({
                        x: x, y: y,
                        width: Math.max(1, Math.ceil(bounds.x + bounds.width) - x),
                        height: Math.max(1, Math.ceil(bounds.y + bounds.height) - y), pixelRatio: 1
                    });
                }
                // The transformer lives on another layer, so exports never
                // disturb or include selection handles.
                return objectLayer.toCanvas({ x: 0, y: 0, width: width, height: height, pixelRatio: 1 });
            });
        }

        function exportCompositeCanvas(sourceCanvas) {
            return Promise.all([waitForImages(), svgCanvas()]).then(function (results) {
                serialize();
                var composite = document.createElement('canvas');
                composite.width = width;
                composite.height = height;
                var context = composite.getContext('2d');
                context.drawImage(sourceCanvas || baseCanvas, 0, 0, width, height);
                if (results[1]) {
                    context.drawImage(results[1], 0, 0, width, height);
                }
                context.drawImage(objectLayer.toCanvas({ x: 0, y: 0, width: width, height: height, pixelRatio: 1 }), 0, 0, width, height);
                return composite;
            });
        }

        function exportComposite(sourceCanvas) {
            return exportCompositeCanvas(sourceCanvas).then(function (composite) {
                return composite.toDataURL('image/jpeg', 1);
            });
        }

        arrowStartHandle.on('dragmove', function () {
            moveArrowEndpoint(arrowStartHandle, true);
        });
        arrowEndHandle.on('dragmove', function () {
            moveArrowEndpoint(arrowEndHandle, false);
        });
        arrowStartHandle.on('dragend', changed);
        arrowEndHandle.on('dragend', changed);

        stage.on('click tap', function (event) {
            selectNode(annotationRoot(event.target));
        });
        // Pencil onclick handlers set the zone's color before this bubbled event.
        $('[id^="sketch_tools_' + zone + '_"]').parent().on('click', '[id^="sketch_tools_' + zone + '_"]', applyTextColor);
        $('#selColor_' + zone).on('input change', applyTextColor);
        $('#' + zone + '_KONVA_TOOLS').on('click', '[data-konva-tool]', function () {
            setMode($(this).data('konva-tool'));
        }).on('click', '[data-konva-add]', function () {
            setMode('select');
            var type = $(this).data('konva-add');
            if (type === 'text') {
                addText();
            } else {
                addShape(type);
            }
        }).on('click', '[data-konva-action="transparency"]', toggleTransparency)
            .on('click', '[data-konva-action="delete"]', deleteSelected)
            .on('click', '[data-konva-action="undo"]', undo)
            .on('click', '[data-konva-action="redo"]', redo);

        document.addEventListener('keydown', function (event) {
            if (document.querySelector('.eye-image-modal[open]')) {
                return;
            }
            if ((event.key === 'Delete' || event.key === 'Backspace') && selected && !/INPUT|TEXTAREA/.test(event.target.tagName)) {
                event.preventDefault();
                deleteSelected();
            }
            // Ctrl+Z / Cmd+Z undo
            if ((event.key === 'z' || event.key === 'Z') && (event.ctrlKey || event.metaKey) && !event.shiftKey && !/INPUT|TEXTAREA/.test(event.target.tagName)) {
                event.preventDefault();
                undo();
            }
            // Ctrl+Y / Cmd+Y redo (or Ctrl+Shift+Z)
            if ((event.key === 'y' || event.key === 'Y' || event.key === 'z' || event.key === 'Z') &&
                ((event.ctrlKey || event.metaKey) && event.shiftKey) && !/INPUT|TEXTAREA/.test(event.target.tagName)) {
                event.preventDefault();
                redo();
            }
        });
        var revertButton = document.getElementById('Revert_Canvas_' + zone);
        if (revertButton) {
            revertButton.addEventListener('click', function () { loadJson(initialJson); }, true);
        }

        loadJson(initialJson);
        pushSnapshot();
        setMode(toolbar.attr('data-tool'), false);
        var overlayApi = {
            exportComposite: exportComposite,
            exportCompositeCanvas: exportCompositeCanvas,
            exportReusable: exportReusable,
            insertImage: insertImage,
            ready: waitForImages,
            hasSelected: function () { return !!selected && selected.getLayer() === objectLayer; },
            hasContent: function () { return objectLayer.getChildren().some(function (node) { return node.hasName('eye-overlay-object'); }); },
            getJSON: serialize,
            clear: function () {
                window.clearTimeout(saveTimer);
                clearObjects(true);
                pushSnapshot();
            },
            markSaved: function () {
                initialJson = serialize();
            },
            revert: function () {
                loadJson(initialJson);
            },
            undo: undo,
            redo: redo
        };
        window.eyeDrawingOverlays[zone] = overlayApi;
        if (zone === 'NEURO') {
            window.eyeNeuroOverlay = overlayApi;
        }
        });
    });
})(window.jQuery, window);
