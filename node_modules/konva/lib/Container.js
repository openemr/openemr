import { Factory } from './Factory.js';
import { Node } from './Node.js';
import { getNumberValidator } from './Validators.js';
export class Container extends Node {
    constructor() {
        super(...arguments);
        this.children = [];
    }
    getChildren(filterFunc) {
        if (!filterFunc) {
            return this.children || [];
        }
        const children = this.children || [];
        var results = [];
        children.forEach(function (child) {
            if (filterFunc(child)) {
                results.push(child);
            }
        });
        return results;
    }
    hasChildren() {
        return this.getChildren().length > 0;
    }
    removeChildren() {
        this.getChildren().forEach((child) => {
            child.parent = null;
            child.index = 0;
            child.remove();
        });
        this.children = [];
        this._requestDraw();
        return this;
    }
    destroyChildren() {
        this.getChildren().forEach((child) => {
            child.parent = null;
            child.index = 0;
            child.destroy();
        });
        this.children = [];
        this._requestDraw();
        return this;
    }
    add(...children) {
        if (arguments.length > 1) {
            for (var i = 0; i < arguments.length; i++) {
                this.add(arguments[i]);
            }
            return this;
        }
        var child = children[0];
        if (child.getParent()) {
            child.moveTo(this);
            return this;
        }
        this._validateAdd(child);
        child.index = this.getChildren().length;
        child.parent = this;
        child._clearCaches();
        this.getChildren().push(child);
        this._fire('add', {
            child: child,
        });
        this._requestDraw();
        return this;
    }
    destroy() {
        if (this.hasChildren()) {
            this.destroyChildren();
        }
        super.destroy();
        return this;
    }
    find(selector) {
        return this._generalFind(selector, false);
    }
    findOne(selector) {
        var result = this._generalFind(selector, true);
        return result.length > 0 ? result[0] : undefined;
    }
    _generalFind(selector, findOne) {
        var retArr = [];
        this._descendants((node) => {
            const valid = node._isMatch(selector);
            if (valid) {
                retArr.push(node);
            }
            if (valid && findOne) {
                return true;
            }
            return false;
        });
        return retArr;
    }
    _descendants(fn) {
        let shouldStop = false;
        const children = this.getChildren();
        for (const child of children) {
            shouldStop = fn(child);
            if (shouldStop) {
                return true;
            }
            if (!child.hasChildren()) {
                continue;
            }
            shouldStop = child._descendants(fn);
            if (shouldStop) {
                return true;
            }
        }
        return false;
    }
    toObject() {
        var obj = Node.prototype.toObject.call(this);
        obj.children = [];
        this.getChildren().forEach((child) => {
            obj.children.push(child.toObject());
        });
        return obj;
    }
    isAncestorOf(node) {
        var parent = node.getParent();
        while (parent) {
            if (parent._id === this._id) {
                return true;
            }
            parent = parent.getParent();
        }
        return false;
    }
    clone(obj) {
        var node = Node.prototype.clone.call(this, obj);
        this.getChildren().forEach(function (no) {
            node.add(no.clone());
        });
        return node;
    }
    getAllIntersections(pos) {
        var arr = [];
        this.find('Shape').forEach(function (shape) {
            if (shape.isVisible() && shape.intersects(pos)) {
                arr.push(shape);
            }
        });
        return arr;
    }
    _clearSelfAndDescendantCache(attr) {
        var _a;
        super._clearSelfAndDescendantCache(attr);
        if (this.isCached()) {
            return;
        }
        (_a = this.children) === null || _a === void 0 ? void 0 : _a.forEach(function (node) {
            node._clearSelfAndDescendantCache(attr);
        });
    }
    _setChildrenIndices() {
        var _a;
        (_a = this.children) === null || _a === void 0 ? void 0 : _a.forEach(function (child, n) {
            child.index = n;
        });
        this._requestDraw();
    }
    drawScene(can, top) {
        var layer = this.getLayer(), canvas = can || (layer && layer.getCanvas()), context = canvas && canvas.getContext(), cachedCanvas = this._getCanvasCache(), cachedSceneCanvas = cachedCanvas && cachedCanvas.scene;
        var caching = canvas && canvas.isCache;
        if (!this.isVisible() && !caching) {
            return this;
        }
        if (cachedSceneCanvas) {
            context.save();
            var m = this.getAbsoluteTransform(top).getMatrix();
            context.transform(m[0], m[1], m[2], m[3], m[4], m[5]);
            this._drawCachedSceneCanvas(context);
            context.restore();
        }
        else {
            this._drawChildren('drawScene', canvas, top);
        }
        return this;
    }
    drawHit(can, top) {
        if (!this.shouldDrawHit(top)) {
            return this;
        }
        var layer = this.getLayer(), canvas = can || (layer && layer.hitCanvas), context = canvas && canvas.getContext(), cachedCanvas = this._getCanvasCache(), cachedHitCanvas = cachedCanvas && cachedCanvas.hit;
        if (cachedHitCanvas) {
            context.save();
            var m = this.getAbsoluteTransform(top).getMatrix();
            context.transform(m[0], m[1], m[2], m[3], m[4], m[5]);
            this._drawCachedHitCanvas(context);
            context.restore();
        }
        else {
            this._drawChildren('drawHit', canvas, top);
        }
        return this;
    }
    _drawChildren(drawMethod, canvas, top) {
        var _a;
        var context = canvas && canvas.getContext(), clipWidth = this.clipWidth(), clipHeight = this.clipHeight(), clipFunc = this.clipFunc(), hasClip = (clipWidth && clipHeight) || clipFunc;
        const selfCache = top === this;
        if (hasClip) {
            context.save();
            var transform = this.getAbsoluteTransform(top);
            var m = transform.getMatrix();
            context.transform(m[0], m[1], m[2], m[3], m[4], m[5]);
            context.beginPath();
            if (clipFunc) {
                clipFunc.call(this, context, this);
            }
            else {
                var clipX = this.clipX();
                var clipY = this.clipY();
                context.rect(clipX, clipY, clipWidth, clipHeight);
            }
            context.clip();
            m = transform.copy().invert().getMatrix();
            context.transform(m[0], m[1], m[2], m[3], m[4], m[5]);
        }
        var hasComposition = !selfCache &&
            this.globalCompositeOperation() !== 'source-over' &&
            drawMethod === 'drawScene';
        if (hasComposition) {
            context.save();
            context._applyGlobalCompositeOperation(this);
        }
        (_a = this.children) === null || _a === void 0 ? void 0 : _a.forEach(function (child) {
            child[drawMethod](canvas, top);
        });
        if (hasComposition) {
            context.restore();
        }
        if (hasClip) {
            context.restore();
        }
    }
    getClientRect(config) {
        var _a;
        config = config || {};
        var skipTransform = config.skipTransform;
        var relativeTo = config.relativeTo;
        var minX, minY, maxX, maxY;
        var selfRect = {
            x: Infinity,
            y: Infinity,
            width: 0,
            height: 0,
        };
        var that = this;
        (_a = this.children) === null || _a === void 0 ? void 0 : _a.forEach(function (child) {
            if (!child.visible()) {
                return;
            }
            var rect = child.getClientRect({
                relativeTo: that,
                skipShadow: config.skipShadow,
                skipStroke: config.skipStroke,
            });
            if (rect.width === 0 && rect.height === 0) {
                return;
            }
            if (minX === undefined) {
                minX = rect.x;
                minY = rect.y;
                maxX = rect.x + rect.width;
                maxY = rect.y + rect.height;
            }
            else {
                minX = Math.min(minX, rect.x);
                minY = Math.min(minY, rect.y);
                maxX = Math.max(maxX, rect.x + rect.width);
                maxY = Math.max(maxY, rect.y + rect.height);
            }
        });
        var shapes = this.find('Shape');
        var hasVisible = false;
        for (var i = 0; i < shapes.length; i++) {
            var shape = shapes[i];
            if (shape._isVisible(this)) {
                hasVisible = true;
                break;
            }
        }
        if (hasVisible && minX !== undefined) {
            selfRect = {
                x: minX,
                y: minY,
                width: maxX - minX,
                height: maxY - minY,
            };
        }
        else {
            selfRect = {
                x: 0,
                y: 0,
                width: 0,
                height: 0,
            };
        }
        if (!skipTransform) {
            return this._transformedRect(selfRect, relativeTo);
        }
        return selfRect;
    }
}
Factory.addComponentsGetterSetter(Container, 'clip', [
    'x',
    'y',
    'width',
    'height',
]);
Factory.addGetterSetter(Container, 'clipX', undefined, getNumberValidator());
Factory.addGetterSetter(Container, 'clipY', undefined, getNumberValidator());
Factory.addGetterSetter(Container, 'clipWidth', undefined, getNumberValidator());
Factory.addGetterSetter(Container, 'clipHeight', undefined, getNumberValidator());
Factory.addGetterSetter(Container, 'clipFunc');
