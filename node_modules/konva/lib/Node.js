import { Util, Transform } from './Util.js';
import { Factory } from './Factory.js';
import { SceneCanvas, HitCanvas } from './Canvas.js';
import { Konva } from './Global.js';
import { DD } from './DragAndDrop.js';
import { getNumberValidator, getStringValidator, getBooleanValidator, } from './Validators.js';
var ABSOLUTE_OPACITY = 'absoluteOpacity', ALL_LISTENERS = 'allEventListeners', ABSOLUTE_TRANSFORM = 'absoluteTransform', ABSOLUTE_SCALE = 'absoluteScale', CANVAS = 'canvas', CHANGE = 'Change', CHILDREN = 'children', KONVA = 'konva', LISTENING = 'listening', MOUSEENTER = 'mouseenter', MOUSELEAVE = 'mouseleave', NAME = 'name', SET = 'set', SHAPE = 'Shape', SPACE = ' ', STAGE = 'stage', TRANSFORM = 'transform', UPPER_STAGE = 'Stage', VISIBLE = 'visible', TRANSFORM_CHANGE_STR = [
    'xChange.konva',
    'yChange.konva',
    'scaleXChange.konva',
    'scaleYChange.konva',
    'skewXChange.konva',
    'skewYChange.konva',
    'rotationChange.konva',
    'offsetXChange.konva',
    'offsetYChange.konva',
    'transformsEnabledChange.konva',
].join(SPACE);
let idCounter = 1;
export class Node {
    constructor(config) {
        this._id = idCounter++;
        this.eventListeners = {};
        this.attrs = {};
        this.index = 0;
        this._allEventListeners = null;
        this.parent = null;
        this._cache = new Map();
        this._attachedDepsListeners = new Map();
        this._lastPos = null;
        this._batchingTransformChange = false;
        this._needClearTransformCache = false;
        this._filterUpToDate = false;
        this._isUnderCache = false;
        this._dragEventId = null;
        this._shouldFireChangeEvents = false;
        this.setAttrs(config);
        this._shouldFireChangeEvents = true;
    }
    hasChildren() {
        return false;
    }
    _clearCache(attr) {
        if ((attr === TRANSFORM || attr === ABSOLUTE_TRANSFORM) &&
            this._cache.get(attr)) {
            this._cache.get(attr).dirty = true;
        }
        else if (attr) {
            this._cache.delete(attr);
        }
        else {
            this._cache.clear();
        }
    }
    _getCache(attr, privateGetter) {
        var cache = this._cache.get(attr);
        var isTransform = attr === TRANSFORM || attr === ABSOLUTE_TRANSFORM;
        var invalid = cache === undefined || (isTransform && cache.dirty === true);
        if (invalid) {
            cache = privateGetter.call(this);
            this._cache.set(attr, cache);
        }
        return cache;
    }
    _calculate(name, deps, getter) {
        if (!this._attachedDepsListeners.get(name)) {
            const depsString = deps.map((dep) => dep + 'Change.konva').join(SPACE);
            this.on(depsString, () => {
                this._clearCache(name);
            });
            this._attachedDepsListeners.set(name, true);
        }
        return this._getCache(name, getter);
    }
    _getCanvasCache() {
        return this._cache.get(CANVAS);
    }
    _clearSelfAndDescendantCache(attr) {
        this._clearCache(attr);
        if (attr === ABSOLUTE_TRANSFORM) {
            this.fire('absoluteTransformChange');
        }
    }
    clearCache() {
        this._cache.delete(CANVAS);
        this._clearSelfAndDescendantCache();
        this._requestDraw();
        return this;
    }
    cache(config) {
        var conf = config || {};
        var rect = {};
        if (conf.x === undefined ||
            conf.y === undefined ||
            conf.width === undefined ||
            conf.height === undefined) {
            rect = this.getClientRect({
                skipTransform: true,
                relativeTo: this.getParent(),
            });
        }
        var width = Math.ceil(conf.width || rect.width), height = Math.ceil(conf.height || rect.height), pixelRatio = conf.pixelRatio, x = conf.x === undefined ? rect.x : conf.x, y = conf.y === undefined ? rect.y : conf.y, offset = conf.offset || 0, drawBorder = conf.drawBorder || false, hitCanvasPixelRatio = conf.hitCanvasPixelRatio || 1;
        if (!width || !height) {
            Util.error('Can not cache the node. Width or height of the node equals 0. Caching is skipped.');
            return;
        }
        width += offset * 2;
        height += offset * 2;
        x -= offset;
        y -= offset;
        var cachedSceneCanvas = new SceneCanvas({
            pixelRatio: pixelRatio,
            width: width,
            height: height,
        }), cachedFilterCanvas = new SceneCanvas({
            pixelRatio: pixelRatio,
            width: 0,
            height: 0,
        }), cachedHitCanvas = new HitCanvas({
            pixelRatio: hitCanvasPixelRatio,
            width: width,
            height: height,
        }), sceneContext = cachedSceneCanvas.getContext(), hitContext = cachedHitCanvas.getContext();
        cachedHitCanvas.isCache = true;
        cachedSceneCanvas.isCache = true;
        this._cache.delete(CANVAS);
        this._filterUpToDate = false;
        if (conf.imageSmoothingEnabled === false) {
            cachedSceneCanvas.getContext()._context.imageSmoothingEnabled = false;
            cachedFilterCanvas.getContext()._context.imageSmoothingEnabled = false;
        }
        sceneContext.save();
        hitContext.save();
        sceneContext.translate(-x, -y);
        hitContext.translate(-x, -y);
        this._isUnderCache = true;
        this._clearSelfAndDescendantCache(ABSOLUTE_OPACITY);
        this._clearSelfAndDescendantCache(ABSOLUTE_SCALE);
        this.drawScene(cachedSceneCanvas, this);
        this.drawHit(cachedHitCanvas, this);
        this._isUnderCache = false;
        sceneContext.restore();
        hitContext.restore();
        if (drawBorder) {
            sceneContext.save();
            sceneContext.beginPath();
            sceneContext.rect(0, 0, width, height);
            sceneContext.closePath();
            sceneContext.setAttr('strokeStyle', 'red');
            sceneContext.setAttr('lineWidth', 5);
            sceneContext.stroke();
            sceneContext.restore();
        }
        this._cache.set(CANVAS, {
            scene: cachedSceneCanvas,
            filter: cachedFilterCanvas,
            hit: cachedHitCanvas,
            x: x,
            y: y,
        });
        this._requestDraw();
        return this;
    }
    isCached() {
        return this._cache.has(CANVAS);
    }
    getClientRect(config) {
        throw new Error('abstract "getClientRect" method call');
    }
    _transformedRect(rect, top) {
        var points = [
            { x: rect.x, y: rect.y },
            { x: rect.x + rect.width, y: rect.y },
            { x: rect.x + rect.width, y: rect.y + rect.height },
            { x: rect.x, y: rect.y + rect.height },
        ];
        var minX, minY, maxX, maxY;
        var trans = this.getAbsoluteTransform(top);
        points.forEach(function (point) {
            var transformed = trans.point(point);
            if (minX === undefined) {
                minX = maxX = transformed.x;
                minY = maxY = transformed.y;
            }
            minX = Math.min(minX, transformed.x);
            minY = Math.min(minY, transformed.y);
            maxX = Math.max(maxX, transformed.x);
            maxY = Math.max(maxY, transformed.y);
        });
        return {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - minY,
        };
    }
    _drawCachedSceneCanvas(context) {
        context.save();
        context._applyOpacity(this);
        context._applyGlobalCompositeOperation(this);
        const canvasCache = this._getCanvasCache();
        context.translate(canvasCache.x, canvasCache.y);
        var cacheCanvas = this._getCachedSceneCanvas();
        var ratio = cacheCanvas.pixelRatio;
        context.drawImage(cacheCanvas._canvas, 0, 0, cacheCanvas.width / ratio, cacheCanvas.height / ratio);
        context.restore();
    }
    _drawCachedHitCanvas(context) {
        var canvasCache = this._getCanvasCache(), hitCanvas = canvasCache.hit;
        context.save();
        context.translate(canvasCache.x, canvasCache.y);
        context.drawImage(hitCanvas._canvas, 0, 0, hitCanvas.width / hitCanvas.pixelRatio, hitCanvas.height / hitCanvas.pixelRatio);
        context.restore();
    }
    _getCachedSceneCanvas() {
        var filters = this.filters(), cachedCanvas = this._getCanvasCache(), sceneCanvas = cachedCanvas.scene, filterCanvas = cachedCanvas.filter, filterContext = filterCanvas.getContext(), len, imageData, n, filter;
        if (filters) {
            if (!this._filterUpToDate) {
                var ratio = sceneCanvas.pixelRatio;
                filterCanvas.setSize(sceneCanvas.width / sceneCanvas.pixelRatio, sceneCanvas.height / sceneCanvas.pixelRatio);
                try {
                    len = filters.length;
                    filterContext.clear();
                    filterContext.drawImage(sceneCanvas._canvas, 0, 0, sceneCanvas.getWidth() / ratio, sceneCanvas.getHeight() / ratio);
                    imageData = filterContext.getImageData(0, 0, filterCanvas.getWidth(), filterCanvas.getHeight());
                    for (n = 0; n < len; n++) {
                        filter = filters[n];
                        if (typeof filter !== 'function') {
                            Util.error('Filter should be type of function, but got ' +
                                typeof filter +
                                ' instead. Please check correct filters');
                            continue;
                        }
                        filter.call(this, imageData);
                        filterContext.putImageData(imageData, 0, 0);
                    }
                }
                catch (e) {
                    Util.error('Unable to apply filter. ' +
                        e.message +
                        ' This post my help you https://konvajs.org/docs/posts/Tainted_Canvas.html.');
                }
                this._filterUpToDate = true;
            }
            return filterCanvas;
        }
        return sceneCanvas;
    }
    on(evtStr, handler) {
        this._cache && this._cache.delete(ALL_LISTENERS);
        if (arguments.length === 3) {
            return this._delegate.apply(this, arguments);
        }
        var events = evtStr.split(SPACE), len = events.length, n, event, parts, baseEvent, name;
        for (n = 0; n < len; n++) {
            event = events[n];
            parts = event.split('.');
            baseEvent = parts[0];
            name = parts[1] || '';
            if (!this.eventListeners[baseEvent]) {
                this.eventListeners[baseEvent] = [];
            }
            this.eventListeners[baseEvent].push({
                name: name,
                handler: handler,
            });
        }
        return this;
    }
    off(evtStr, callback) {
        var events = (evtStr || '').split(SPACE), len = events.length, n, t, event, parts, baseEvent, name;
        this._cache && this._cache.delete(ALL_LISTENERS);
        if (!evtStr) {
            for (t in this.eventListeners) {
                this._off(t);
            }
        }
        for (n = 0; n < len; n++) {
            event = events[n];
            parts = event.split('.');
            baseEvent = parts[0];
            name = parts[1];
            if (baseEvent) {
                if (this.eventListeners[baseEvent]) {
                    this._off(baseEvent, name, callback);
                }
            }
            else {
                for (t in this.eventListeners) {
                    this._off(t, name, callback);
                }
            }
        }
        return this;
    }
    dispatchEvent(evt) {
        var e = {
            target: this,
            type: evt.type,
            evt: evt,
        };
        this.fire(evt.type, e);
        return this;
    }
    addEventListener(type, handler) {
        this.on(type, function (evt) {
            handler.call(this, evt.evt);
        });
        return this;
    }
    removeEventListener(type) {
        this.off(type);
        return this;
    }
    _delegate(event, selector, handler) {
        var stopNode = this;
        this.on(event, function (evt) {
            var targets = evt.target.findAncestors(selector, true, stopNode);
            for (var i = 0; i < targets.length; i++) {
                evt = Util.cloneObject(evt);
                evt.currentTarget = targets[i];
                handler.call(targets[i], evt);
            }
        });
    }
    remove() {
        if (this.isDragging()) {
            this.stopDrag();
        }
        DD._dragElements.delete(this._id);
        this._remove();
        return this;
    }
    _clearCaches() {
        this._clearSelfAndDescendantCache(ABSOLUTE_TRANSFORM);
        this._clearSelfAndDescendantCache(ABSOLUTE_OPACITY);
        this._clearSelfAndDescendantCache(ABSOLUTE_SCALE);
        this._clearSelfAndDescendantCache(STAGE);
        this._clearSelfAndDescendantCache(VISIBLE);
        this._clearSelfAndDescendantCache(LISTENING);
    }
    _remove() {
        this._clearCaches();
        var parent = this.getParent();
        if (parent && parent.children) {
            parent.children.splice(this.index, 1);
            parent._setChildrenIndices();
            this.parent = null;
        }
    }
    destroy() {
        this.remove();
        return this;
    }
    getAttr(attr) {
        var method = 'get' + Util._capitalize(attr);
        if (Util._isFunction(this[method])) {
            return this[method]();
        }
        return this.attrs[attr];
    }
    getAncestors() {
        var parent = this.getParent(), ancestors = [];
        while (parent) {
            ancestors.push(parent);
            parent = parent.getParent();
        }
        return ancestors;
    }
    getAttrs() {
        return this.attrs || {};
    }
    setAttrs(config) {
        this._batchTransformChanges(() => {
            var key, method;
            if (!config) {
                return this;
            }
            for (key in config) {
                if (key === CHILDREN) {
                    continue;
                }
                method = SET + Util._capitalize(key);
                if (Util._isFunction(this[method])) {
                    this[method](config[key]);
                }
                else {
                    this._setAttr(key, config[key]);
                }
            }
        });
        return this;
    }
    isListening() {
        return this._getCache(LISTENING, this._isListening);
    }
    _isListening(relativeTo) {
        const listening = this.listening();
        if (!listening) {
            return false;
        }
        const parent = this.getParent();
        if (parent && parent !== relativeTo && this !== relativeTo) {
            return parent._isListening(relativeTo);
        }
        else {
            return true;
        }
    }
    isVisible() {
        return this._getCache(VISIBLE, this._isVisible);
    }
    _isVisible(relativeTo) {
        const visible = this.visible();
        if (!visible) {
            return false;
        }
        const parent = this.getParent();
        if (parent && parent !== relativeTo && this !== relativeTo) {
            return parent._isVisible(relativeTo);
        }
        else {
            return true;
        }
    }
    shouldDrawHit(top, skipDragCheck = false) {
        if (top) {
            return this._isVisible(top) && this._isListening(top);
        }
        var layer = this.getLayer();
        var layerUnderDrag = false;
        DD._dragElements.forEach((elem) => {
            if (elem.dragStatus !== 'dragging') {
                return;
            }
            else if (elem.node.nodeType === 'Stage') {
                layerUnderDrag = true;
            }
            else if (elem.node.getLayer() === layer) {
                layerUnderDrag = true;
            }
        });
        var dragSkip = !skipDragCheck && !Konva.hitOnDragEnabled && layerUnderDrag;
        return this.isListening() && this.isVisible() && !dragSkip;
    }
    show() {
        this.visible(true);
        return this;
    }
    hide() {
        this.visible(false);
        return this;
    }
    getZIndex() {
        return this.index || 0;
    }
    getAbsoluteZIndex() {
        var depth = this.getDepth(), that = this, index = 0, nodes, len, n, child;
        function addChildren(children) {
            nodes = [];
            len = children.length;
            for (n = 0; n < len; n++) {
                child = children[n];
                index++;
                if (child.nodeType !== SHAPE) {
                    nodes = nodes.concat(child.getChildren().slice());
                }
                if (child._id === that._id) {
                    n = len;
                }
            }
            if (nodes.length > 0 && nodes[0].getDepth() <= depth) {
                addChildren(nodes);
            }
        }
        if (that.nodeType !== UPPER_STAGE) {
            addChildren(that.getStage().getChildren());
        }
        return index;
    }
    getDepth() {
        var depth = 0, parent = this.parent;
        while (parent) {
            depth++;
            parent = parent.parent;
        }
        return depth;
    }
    _batchTransformChanges(func) {
        this._batchingTransformChange = true;
        func();
        this._batchingTransformChange = false;
        if (this._needClearTransformCache) {
            this._clearCache(TRANSFORM);
            this._clearSelfAndDescendantCache(ABSOLUTE_TRANSFORM);
        }
        this._needClearTransformCache = false;
    }
    setPosition(pos) {
        this._batchTransformChanges(() => {
            this.x(pos.x);
            this.y(pos.y);
        });
        return this;
    }
    getPosition() {
        return {
            x: this.x(),
            y: this.y(),
        };
    }
    getRelativePointerPosition() {
        if (!this.getStage()) {
            return null;
        }
        var pos = this.getStage().getPointerPosition();
        if (!pos) {
            return null;
        }
        var transform = this.getAbsoluteTransform().copy();
        transform.invert();
        return transform.point(pos);
    }
    getAbsolutePosition(top) {
        let haveCachedParent = false;
        let parent = this.parent;
        while (parent) {
            if (parent.isCached()) {
                haveCachedParent = true;
                break;
            }
            parent = parent.parent;
        }
        if (haveCachedParent && !top) {
            top = true;
        }
        var absoluteMatrix = this.getAbsoluteTransform(top).getMatrix(), absoluteTransform = new Transform(), offset = this.offset();
        absoluteTransform.m = absoluteMatrix.slice();
        absoluteTransform.translate(offset.x, offset.y);
        return absoluteTransform.getTranslation();
    }
    setAbsolutePosition(pos) {
        var origTrans = this._clearTransform();
        this.attrs.x = origTrans.x;
        this.attrs.y = origTrans.y;
        delete origTrans.x;
        delete origTrans.y;
        this._clearCache(TRANSFORM);
        var it = this._getAbsoluteTransform().copy();
        it.invert();
        it.translate(pos.x, pos.y);
        pos = {
            x: this.attrs.x + it.getTranslation().x,
            y: this.attrs.y + it.getTranslation().y,
        };
        this._setTransform(origTrans);
        this.setPosition({ x: pos.x, y: pos.y });
        this._clearCache(TRANSFORM);
        this._clearSelfAndDescendantCache(ABSOLUTE_TRANSFORM);
        return this;
    }
    _setTransform(trans) {
        var key;
        for (key in trans) {
            this.attrs[key] = trans[key];
        }
    }
    _clearTransform() {
        var trans = {
            x: this.x(),
            y: this.y(),
            rotation: this.rotation(),
            scaleX: this.scaleX(),
            scaleY: this.scaleY(),
            offsetX: this.offsetX(),
            offsetY: this.offsetY(),
            skewX: this.skewX(),
            skewY: this.skewY(),
        };
        this.attrs.x = 0;
        this.attrs.y = 0;
        this.attrs.rotation = 0;
        this.attrs.scaleX = 1;
        this.attrs.scaleY = 1;
        this.attrs.offsetX = 0;
        this.attrs.offsetY = 0;
        this.attrs.skewX = 0;
        this.attrs.skewY = 0;
        return trans;
    }
    move(change) {
        var changeX = change.x, changeY = change.y, x = this.x(), y = this.y();
        if (changeX !== undefined) {
            x += changeX;
        }
        if (changeY !== undefined) {
            y += changeY;
        }
        this.setPosition({ x: x, y: y });
        return this;
    }
    _eachAncestorReverse(func, top) {
        var family = [], parent = this.getParent(), len, n;
        if (top && top._id === this._id) {
            return;
        }
        family.unshift(this);
        while (parent && (!top || parent._id !== top._id)) {
            family.unshift(parent);
            parent = parent.parent;
        }
        len = family.length;
        for (n = 0; n < len; n++) {
            func(family[n]);
        }
    }
    rotate(theta) {
        this.rotation(this.rotation() + theta);
        return this;
    }
    moveToTop() {
        if (!this.parent) {
            Util.warn('Node has no parent. moveToTop function is ignored.');
            return false;
        }
        var index = this.index;
        this.parent.children.splice(index, 1);
        this.parent.children.push(this);
        this.parent._setChildrenIndices();
        return true;
    }
    moveUp() {
        if (!this.parent) {
            Util.warn('Node has no parent. moveUp function is ignored.');
            return false;
        }
        var index = this.index, len = this.parent.getChildren().length;
        if (index < len - 1) {
            this.parent.children.splice(index, 1);
            this.parent.children.splice(index + 1, 0, this);
            this.parent._setChildrenIndices();
            return true;
        }
        return false;
    }
    moveDown() {
        if (!this.parent) {
            Util.warn('Node has no parent. moveDown function is ignored.');
            return false;
        }
        var index = this.index;
        if (index > 0) {
            this.parent.children.splice(index, 1);
            this.parent.children.splice(index - 1, 0, this);
            this.parent._setChildrenIndices();
            return true;
        }
        return false;
    }
    moveToBottom() {
        if (!this.parent) {
            Util.warn('Node has no parent. moveToBottom function is ignored.');
            return false;
        }
        var index = this.index;
        if (index > 0) {
            this.parent.children.splice(index, 1);
            this.parent.children.unshift(this);
            this.parent._setChildrenIndices();
            return true;
        }
        return false;
    }
    setZIndex(zIndex) {
        if (!this.parent) {
            Util.warn('Node has no parent. zIndex parameter is ignored.');
            return this;
        }
        if (zIndex < 0 || zIndex >= this.parent.children.length) {
            Util.warn('Unexpected value ' +
                zIndex +
                ' for zIndex property. zIndex is just index of a node in children of its parent. Expected value is from 0 to ' +
                (this.parent.children.length - 1) +
                '.');
        }
        var index = this.index;
        this.parent.children.splice(index, 1);
        this.parent.children.splice(zIndex, 0, this);
        this.parent._setChildrenIndices();
        return this;
    }
    getAbsoluteOpacity() {
        return this._getCache(ABSOLUTE_OPACITY, this._getAbsoluteOpacity);
    }
    _getAbsoluteOpacity() {
        var absOpacity = this.opacity();
        var parent = this.getParent();
        if (parent && !parent._isUnderCache) {
            absOpacity *= parent.getAbsoluteOpacity();
        }
        return absOpacity;
    }
    moveTo(newContainer) {
        if (this.getParent() !== newContainer) {
            this._remove();
            newContainer.add(this);
        }
        return this;
    }
    toObject() {
        var obj = {}, attrs = this.getAttrs(), key, val, getter, defaultValue, nonPlainObject;
        obj.attrs = {};
        for (key in attrs) {
            val = attrs[key];
            nonPlainObject =
                Util.isObject(val) && !Util._isPlainObject(val) && !Util._isArray(val);
            if (nonPlainObject) {
                continue;
            }
            getter = typeof this[key] === 'function' && this[key];
            delete attrs[key];
            defaultValue = getter ? getter.call(this) : null;
            attrs[key] = val;
            if (defaultValue !== val) {
                obj.attrs[key] = val;
            }
        }
        obj.className = this.getClassName();
        return Util._prepareToStringify(obj);
    }
    toJSON() {
        return JSON.stringify(this.toObject());
    }
    getParent() {
        return this.parent;
    }
    findAncestors(selector, includeSelf, stopNode) {
        var res = [];
        if (includeSelf && this._isMatch(selector)) {
            res.push(this);
        }
        var ancestor = this.parent;
        while (ancestor) {
            if (ancestor === stopNode) {
                return res;
            }
            if (ancestor._isMatch(selector)) {
                res.push(ancestor);
            }
            ancestor = ancestor.parent;
        }
        return res;
    }
    isAncestorOf(node) {
        return false;
    }
    findAncestor(selector, includeSelf, stopNode) {
        return this.findAncestors(selector, includeSelf, stopNode)[0];
    }
    _isMatch(selector) {
        if (!selector) {
            return false;
        }
        if (typeof selector === 'function') {
            return selector(this);
        }
        var selectorArr = selector.replace(/ /g, '').split(','), len = selectorArr.length, n, sel;
        for (n = 0; n < len; n++) {
            sel = selectorArr[n];
            if (!Util.isValidSelector(sel)) {
                Util.warn('Selector "' +
                    sel +
                    '" is invalid. Allowed selectors examples are "#foo", ".bar" or "Group".');
                Util.warn('If you have a custom shape with such className, please change it to start with upper letter like "Triangle".');
                Util.warn('Konva is awesome, right?');
            }
            if (sel.charAt(0) === '#') {
                if (this.id() === sel.slice(1)) {
                    return true;
                }
            }
            else if (sel.charAt(0) === '.') {
                if (this.hasName(sel.slice(1))) {
                    return true;
                }
            }
            else if (this.className === sel || this.nodeType === sel) {
                return true;
            }
        }
        return false;
    }
    getLayer() {
        var parent = this.getParent();
        return parent ? parent.getLayer() : null;
    }
    getStage() {
        return this._getCache(STAGE, this._getStage);
    }
    _getStage() {
        var parent = this.getParent();
        if (parent) {
            return parent.getStage();
        }
        else {
            return undefined;
        }
    }
    fire(eventType, evt = {}, bubble) {
        evt.target = evt.target || this;
        if (bubble) {
            this._fireAndBubble(eventType, evt);
        }
        else {
            this._fire(eventType, evt);
        }
        return this;
    }
    getAbsoluteTransform(top) {
        if (top) {
            return this._getAbsoluteTransform(top);
        }
        else {
            return this._getCache(ABSOLUTE_TRANSFORM, this._getAbsoluteTransform);
        }
    }
    _getAbsoluteTransform(top) {
        var at;
        if (top) {
            at = new Transform();
            this._eachAncestorReverse(function (node) {
                var transformsEnabled = node.transformsEnabled();
                if (transformsEnabled === 'all') {
                    at.multiply(node.getTransform());
                }
                else if (transformsEnabled === 'position') {
                    at.translate(node.x() - node.offsetX(), node.y() - node.offsetY());
                }
            }, top);
            return at;
        }
        else {
            at = this._cache.get(ABSOLUTE_TRANSFORM) || new Transform();
            if (this.parent) {
                this.parent.getAbsoluteTransform().copyInto(at);
            }
            else {
                at.reset();
            }
            var transformsEnabled = this.transformsEnabled();
            if (transformsEnabled === 'all') {
                at.multiply(this.getTransform());
            }
            else if (transformsEnabled === 'position') {
                const x = this.attrs.x || 0;
                const y = this.attrs.y || 0;
                const offsetX = this.attrs.offsetX || 0;
                const offsetY = this.attrs.offsetY || 0;
                at.translate(x - offsetX, y - offsetY);
            }
            at.dirty = false;
            return at;
        }
    }
    getAbsoluteScale(top) {
        var parent = this;
        while (parent) {
            if (parent._isUnderCache) {
                top = parent;
            }
            parent = parent.getParent();
        }
        const transform = this.getAbsoluteTransform(top);
        const attrs = transform.decompose();
        return {
            x: attrs.scaleX,
            y: attrs.scaleY,
        };
    }
    getAbsoluteRotation() {
        return this.getAbsoluteTransform().decompose().rotation;
    }
    getTransform() {
        return this._getCache(TRANSFORM, this._getTransform);
    }
    _getTransform() {
        var _a, _b;
        var m = this._cache.get(TRANSFORM) || new Transform();
        m.reset();
        var x = this.x(), y = this.y(), rotation = Konva.getAngle(this.rotation()), scaleX = (_a = this.attrs.scaleX) !== null && _a !== void 0 ? _a : 1, scaleY = (_b = this.attrs.scaleY) !== null && _b !== void 0 ? _b : 1, skewX = this.attrs.skewX || 0, skewY = this.attrs.skewY || 0, offsetX = this.attrs.offsetX || 0, offsetY = this.attrs.offsetY || 0;
        if (x !== 0 || y !== 0) {
            m.translate(x, y);
        }
        if (rotation !== 0) {
            m.rotate(rotation);
        }
        if (skewX !== 0 || skewY !== 0) {
            m.skew(skewX, skewY);
        }
        if (scaleX !== 1 || scaleY !== 1) {
            m.scale(scaleX, scaleY);
        }
        if (offsetX !== 0 || offsetY !== 0) {
            m.translate(-1 * offsetX, -1 * offsetY);
        }
        m.dirty = false;
        return m;
    }
    clone(obj) {
        var attrs = Util.cloneObject(this.attrs), key, allListeners, len, n, listener;
        for (key in obj) {
            attrs[key] = obj[key];
        }
        var node = new this.constructor(attrs);
        for (key in this.eventListeners) {
            allListeners = this.eventListeners[key];
            len = allListeners.length;
            for (n = 0; n < len; n++) {
                listener = allListeners[n];
                if (listener.name.indexOf(KONVA) < 0) {
                    if (!node.eventListeners[key]) {
                        node.eventListeners[key] = [];
                    }
                    node.eventListeners[key].push(listener);
                }
            }
        }
        return node;
    }
    _toKonvaCanvas(config) {
        config = config || {};
        var box = this.getClientRect();
        var stage = this.getStage(), x = config.x !== undefined ? config.x : box.x, y = config.y !== undefined ? config.y : box.y, pixelRatio = config.pixelRatio || 1, canvas = new SceneCanvas({
            width: config.width || box.width || (stage ? stage.width() : 0),
            height: config.height || box.height || (stage ? stage.height() : 0),
            pixelRatio: pixelRatio,
        }), context = canvas.getContext();
        context.save();
        if (x || y) {
            context.translate(-1 * x, -1 * y);
        }
        this.drawScene(canvas);
        context.restore();
        return canvas;
    }
    toCanvas(config) {
        return this._toKonvaCanvas(config)._canvas;
    }
    toDataURL(config) {
        config = config || {};
        var mimeType = config.mimeType || null, quality = config.quality || null;
        var url = this._toKonvaCanvas(config).toDataURL(mimeType, quality);
        if (config.callback) {
            config.callback(url);
        }
        return url;
    }
    toImage(config) {
        if (!config || !config.callback) {
            throw 'callback required for toImage method config argument';
        }
        var callback = config.callback;
        delete config.callback;
        Util._urlToImage(this.toDataURL(config), function (img) {
            callback(img);
        });
    }
    setSize(size) {
        this.width(size.width);
        this.height(size.height);
        return this;
    }
    getSize() {
        return {
            width: this.width(),
            height: this.height(),
        };
    }
    getClassName() {
        return this.className || this.nodeType;
    }
    getType() {
        return this.nodeType;
    }
    getDragDistance() {
        if (this.attrs.dragDistance !== undefined) {
            return this.attrs.dragDistance;
        }
        else if (this.parent) {
            return this.parent.getDragDistance();
        }
        else {
            return Konva.dragDistance;
        }
    }
    _off(type, name, callback) {
        var evtListeners = this.eventListeners[type], i, evtName, handler;
        for (i = 0; i < evtListeners.length; i++) {
            evtName = evtListeners[i].name;
            handler = evtListeners[i].handler;
            if ((evtName !== 'konva' || name === 'konva') &&
                (!name || evtName === name) &&
                (!callback || callback === handler)) {
                evtListeners.splice(i, 1);
                if (evtListeners.length === 0) {
                    delete this.eventListeners[type];
                    break;
                }
                i--;
            }
        }
    }
    _fireChangeEvent(attr, oldVal, newVal) {
        this._fire(attr + CHANGE, {
            oldVal: oldVal,
            newVal: newVal,
        });
    }
    addName(name) {
        if (!this.hasName(name)) {
            var oldName = this.name();
            var newName = oldName ? oldName + ' ' + name : name;
            this.name(newName);
        }
        return this;
    }
    hasName(name) {
        if (!name) {
            return false;
        }
        const fullName = this.name();
        if (!fullName) {
            return false;
        }
        var names = (fullName || '').split(/\s/g);
        return names.indexOf(name) !== -1;
    }
    removeName(name) {
        var names = (this.name() || '').split(/\s/g);
        var index = names.indexOf(name);
        if (index !== -1) {
            names.splice(index, 1);
            this.name(names.join(' '));
        }
        return this;
    }
    setAttr(attr, val) {
        var func = this[SET + Util._capitalize(attr)];
        if (Util._isFunction(func)) {
            func.call(this, val);
        }
        else {
            this._setAttr(attr, val);
        }
        return this;
    }
    _requestDraw() {
        if (Konva.autoDrawEnabled) {
            const drawNode = this.getLayer() || this.getStage();
            drawNode === null || drawNode === void 0 ? void 0 : drawNode.batchDraw();
        }
    }
    _setAttr(key, val) {
        var oldVal = this.attrs[key];
        if (oldVal === val && !Util.isObject(val)) {
            return;
        }
        if (val === undefined || val === null) {
            delete this.attrs[key];
        }
        else {
            this.attrs[key] = val;
        }
        if (this._shouldFireChangeEvents) {
            this._fireChangeEvent(key, oldVal, val);
        }
        this._requestDraw();
    }
    _setComponentAttr(key, component, val) {
        var oldVal;
        if (val !== undefined) {
            oldVal = this.attrs[key];
            if (!oldVal) {
                this.attrs[key] = this.getAttr(key);
            }
            this.attrs[key][component] = val;
            this._fireChangeEvent(key, oldVal, val);
        }
    }
    _fireAndBubble(eventType, evt, compareShape) {
        if (evt && this.nodeType === SHAPE) {
            evt.target = this;
        }
        var shouldStop = (eventType === MOUSEENTER || eventType === MOUSELEAVE) &&
            ((compareShape &&
                (this === compareShape ||
                    (this.isAncestorOf && this.isAncestorOf(compareShape)))) ||
                (this.nodeType === 'Stage' && !compareShape));
        if (!shouldStop) {
            this._fire(eventType, evt);
            var stopBubble = (eventType === MOUSEENTER || eventType === MOUSELEAVE) &&
                compareShape &&
                compareShape.isAncestorOf &&
                compareShape.isAncestorOf(this) &&
                !compareShape.isAncestorOf(this.parent);
            if (((evt && !evt.cancelBubble) || !evt) &&
                this.parent &&
                this.parent.isListening() &&
                !stopBubble) {
                if (compareShape && compareShape.parent) {
                    this._fireAndBubble.call(this.parent, eventType, evt, compareShape);
                }
                else {
                    this._fireAndBubble.call(this.parent, eventType, evt);
                }
            }
        }
    }
    _getProtoListeners(eventType) {
        let listeners = this._cache.get(ALL_LISTENERS);
        if (!listeners) {
            listeners = {};
            let obj = Object.getPrototypeOf(this);
            while (obj) {
                if (!obj.eventListeners) {
                    obj = Object.getPrototypeOf(obj);
                    continue;
                }
                for (var event in obj.eventListeners) {
                    const newEvents = obj.eventListeners[event];
                    const oldEvents = listeners[event] || [];
                    listeners[event] = newEvents.concat(oldEvents);
                }
                obj = Object.getPrototypeOf(obj);
            }
            this._cache.set(ALL_LISTENERS, listeners);
        }
        return listeners[eventType];
    }
    _fire(eventType, evt) {
        evt = evt || {};
        evt.currentTarget = this;
        evt.type = eventType;
        const topListeners = this._getProtoListeners(eventType);
        if (topListeners) {
            for (var i = 0; i < topListeners.length; i++) {
                topListeners[i].handler.call(this, evt);
            }
        }
        const selfListeners = this.eventListeners[eventType];
        if (selfListeners) {
            for (var i = 0; i < selfListeners.length; i++) {
                selfListeners[i].handler.call(this, evt);
            }
        }
    }
    draw() {
        this.drawScene();
        this.drawHit();
        return this;
    }
    _createDragElement(evt) {
        var pointerId = evt ? evt.pointerId : undefined;
        var stage = this.getStage();
        var ap = this.getAbsolutePosition();
        var pos = stage._getPointerById(pointerId) ||
            stage._changedPointerPositions[0] ||
            ap;
        DD._dragElements.set(this._id, {
            node: this,
            startPointerPos: pos,
            offset: {
                x: pos.x - ap.x,
                y: pos.y - ap.y,
            },
            dragStatus: 'ready',
            pointerId,
        });
    }
    startDrag(evt, bubbleEvent = true) {
        if (!DD._dragElements.has(this._id)) {
            this._createDragElement(evt);
        }
        const elem = DD._dragElements.get(this._id);
        elem.dragStatus = 'dragging';
        this.fire('dragstart', {
            type: 'dragstart',
            target: this,
            evt: evt && evt.evt,
        }, bubbleEvent);
    }
    _setDragPosition(evt, elem) {
        const pos = this.getStage()._getPointerById(elem.pointerId);
        if (!pos) {
            return;
        }
        var newNodePos = {
            x: pos.x - elem.offset.x,
            y: pos.y - elem.offset.y,
        };
        var dbf = this.dragBoundFunc();
        if (dbf !== undefined) {
            const bounded = dbf.call(this, newNodePos, evt);
            if (!bounded) {
                Util.warn('dragBoundFunc did not return any value. That is unexpected behavior. You must return new absolute position from dragBoundFunc.');
            }
            else {
                newNodePos = bounded;
            }
        }
        if (!this._lastPos ||
            this._lastPos.x !== newNodePos.x ||
            this._lastPos.y !== newNodePos.y) {
            this.setAbsolutePosition(newNodePos);
            this._requestDraw();
        }
        this._lastPos = newNodePos;
    }
    stopDrag(evt) {
        const elem = DD._dragElements.get(this._id);
        if (elem) {
            elem.dragStatus = 'stopped';
        }
        DD._endDragBefore(evt);
        DD._endDragAfter(evt);
    }
    setDraggable(draggable) {
        this._setAttr('draggable', draggable);
        this._dragChange();
    }
    isDragging() {
        const elem = DD._dragElements.get(this._id);
        return elem ? elem.dragStatus === 'dragging' : false;
    }
    _listenDrag() {
        this._dragCleanup();
        this.on('mousedown.konva touchstart.konva', function (evt) {
            var shouldCheckButton = evt.evt['button'] !== undefined;
            var canDrag = !shouldCheckButton || Konva.dragButtons.indexOf(evt.evt['button']) >= 0;
            if (!canDrag) {
                return;
            }
            if (this.isDragging()) {
                return;
            }
            var hasDraggingChild = false;
            DD._dragElements.forEach((elem) => {
                if (this.isAncestorOf(elem.node)) {
                    hasDraggingChild = true;
                }
            });
            if (!hasDraggingChild) {
                this._createDragElement(evt);
            }
        });
    }
    _dragChange() {
        if (this.attrs.draggable) {
            this._listenDrag();
        }
        else {
            this._dragCleanup();
            var stage = this.getStage();
            if (!stage) {
                return;
            }
            const dragElement = DD._dragElements.get(this._id);
            const isDragging = dragElement && dragElement.dragStatus === 'dragging';
            const isReady = dragElement && dragElement.dragStatus === 'ready';
            if (isDragging) {
                this.stopDrag();
            }
            else if (isReady) {
                DD._dragElements.delete(this._id);
            }
        }
    }
    _dragCleanup() {
        this.off('mousedown.konva');
        this.off('touchstart.konva');
    }
    isClientRectOnScreen(margin = { x: 0, y: 0 }) {
        const stage = this.getStage();
        if (!stage) {
            return false;
        }
        const screenRect = {
            x: -margin.x,
            y: -margin.y,
            width: stage.width() + margin.x,
            height: stage.height() + margin.y,
        };
        return Util.haveIntersection(screenRect, this.getClientRect());
    }
    static create(data, container) {
        if (Util._isString(data)) {
            data = JSON.parse(data);
        }
        return this._createNode(data, container);
    }
    static _createNode(obj, container) {
        var className = Node.prototype.getClassName.call(obj), children = obj.children, no, len, n;
        if (container) {
            obj.attrs.container = container;
        }
        if (!Konva[className]) {
            Util.warn('Can not find a node with class name "' +
                className +
                '". Fallback to "Shape".');
            className = 'Shape';
        }
        const Class = Konva[className];
        no = new Class(obj.attrs);
        if (children) {
            len = children.length;
            for (n = 0; n < len; n++) {
                no.add(Node._createNode(children[n]));
            }
        }
        return no;
    }
}
Node.prototype.nodeType = 'Node';
Node.prototype._attrsAffectingSize = [];
Node.prototype.eventListeners = {};
Node.prototype.on.call(Node.prototype, TRANSFORM_CHANGE_STR, function () {
    if (this._batchingTransformChange) {
        this._needClearTransformCache = true;
        return;
    }
    this._clearCache(TRANSFORM);
    this._clearSelfAndDescendantCache(ABSOLUTE_TRANSFORM);
});
Node.prototype.on.call(Node.prototype, 'visibleChange.konva', function () {
    this._clearSelfAndDescendantCache(VISIBLE);
});
Node.prototype.on.call(Node.prototype, 'listeningChange.konva', function () {
    this._clearSelfAndDescendantCache(LISTENING);
});
Node.prototype.on.call(Node.prototype, 'opacityChange.konva', function () {
    this._clearSelfAndDescendantCache(ABSOLUTE_OPACITY);
});
const addGetterSetter = Factory.addGetterSetter;
addGetterSetter(Node, 'zIndex');
addGetterSetter(Node, 'absolutePosition');
addGetterSetter(Node, 'position');
addGetterSetter(Node, 'x', 0, getNumberValidator());
addGetterSetter(Node, 'y', 0, getNumberValidator());
addGetterSetter(Node, 'globalCompositeOperation', 'source-over', getStringValidator());
addGetterSetter(Node, 'opacity', 1, getNumberValidator());
addGetterSetter(Node, 'name', '', getStringValidator());
addGetterSetter(Node, 'id', '', getStringValidator());
addGetterSetter(Node, 'rotation', 0, getNumberValidator());
Factory.addComponentsGetterSetter(Node, 'scale', ['x', 'y']);
addGetterSetter(Node, 'scaleX', 1, getNumberValidator());
addGetterSetter(Node, 'scaleY', 1, getNumberValidator());
Factory.addComponentsGetterSetter(Node, 'skew', ['x', 'y']);
addGetterSetter(Node, 'skewX', 0, getNumberValidator());
addGetterSetter(Node, 'skewY', 0, getNumberValidator());
Factory.addComponentsGetterSetter(Node, 'offset', ['x', 'y']);
addGetterSetter(Node, 'offsetX', 0, getNumberValidator());
addGetterSetter(Node, 'offsetY', 0, getNumberValidator());
addGetterSetter(Node, 'dragDistance', null, getNumberValidator());
addGetterSetter(Node, 'width', 0, getNumberValidator());
addGetterSetter(Node, 'height', 0, getNumberValidator());
addGetterSetter(Node, 'listening', true, getBooleanValidator());
addGetterSetter(Node, 'preventDefault', true, getBooleanValidator());
addGetterSetter(Node, 'filters', null, function (val) {
    this._filterUpToDate = false;
    return val;
});
addGetterSetter(Node, 'visible', true, getBooleanValidator());
addGetterSetter(Node, 'transformsEnabled', 'all', getStringValidator());
addGetterSetter(Node, 'size');
addGetterSetter(Node, 'dragBoundFunc');
addGetterSetter(Node, 'draggable', false, getBooleanValidator());
Factory.backCompat(Node, {
    rotateDeg: 'rotate',
    setRotationDeg: 'setRotation',
    getRotationDeg: 'getRotation',
});
