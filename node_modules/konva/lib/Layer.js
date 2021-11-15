import { Util } from './Util.js';
import { Container } from './Container.js';
import { Node } from './Node.js';
import { Factory } from './Factory.js';
import { SceneCanvas, HitCanvas } from './Canvas.js';
import { getBooleanValidator } from './Validators.js';
import { shapes } from './Shape.js';
import { _registerNode } from './Global.js';
var HASH = '#', BEFORE_DRAW = 'beforeDraw', DRAW = 'draw', INTERSECTION_OFFSETS = [
    { x: 0, y: 0 },
    { x: -1, y: -1 },
    { x: 1, y: -1 },
    { x: 1, y: 1 },
    { x: -1, y: 1 },
], INTERSECTION_OFFSETS_LEN = INTERSECTION_OFFSETS.length;
export class Layer extends Container {
    constructor(config) {
        super(config);
        this.canvas = new SceneCanvas();
        this.hitCanvas = new HitCanvas({
            pixelRatio: 1,
        });
        this._waitingForDraw = false;
        this.on('visibleChange.konva', this._checkVisibility);
        this._checkVisibility();
        this.on('imageSmoothingEnabledChange.konva', this._setSmoothEnabled);
        this._setSmoothEnabled();
    }
    createPNGStream() {
        const c = this.canvas._canvas;
        return c.createPNGStream();
    }
    getCanvas() {
        return this.canvas;
    }
    getNativeCanvasElement() {
        return this.canvas._canvas;
    }
    getHitCanvas() {
        return this.hitCanvas;
    }
    getContext() {
        return this.getCanvas().getContext();
    }
    clear(bounds) {
        this.getContext().clear(bounds);
        this.getHitCanvas().getContext().clear(bounds);
        return this;
    }
    setZIndex(index) {
        super.setZIndex(index);
        var stage = this.getStage();
        if (stage && stage.content) {
            stage.content.removeChild(this.getNativeCanvasElement());
            if (index < stage.children.length - 1) {
                stage.content.insertBefore(this.getNativeCanvasElement(), stage.children[index + 1].getCanvas()._canvas);
            }
            else {
                stage.content.appendChild(this.getNativeCanvasElement());
            }
        }
        return this;
    }
    moveToTop() {
        Node.prototype.moveToTop.call(this);
        var stage = this.getStage();
        if (stage && stage.content) {
            stage.content.removeChild(this.getNativeCanvasElement());
            stage.content.appendChild(this.getNativeCanvasElement());
        }
        return true;
    }
    moveUp() {
        var moved = Node.prototype.moveUp.call(this);
        if (!moved) {
            return false;
        }
        var stage = this.getStage();
        if (!stage || !stage.content) {
            return false;
        }
        stage.content.removeChild(this.getNativeCanvasElement());
        if (this.index < stage.children.length - 1) {
            stage.content.insertBefore(this.getNativeCanvasElement(), stage.children[this.index + 1].getCanvas()._canvas);
        }
        else {
            stage.content.appendChild(this.getNativeCanvasElement());
        }
        return true;
    }
    moveDown() {
        if (Node.prototype.moveDown.call(this)) {
            var stage = this.getStage();
            if (stage) {
                var children = stage.children;
                if (stage.content) {
                    stage.content.removeChild(this.getNativeCanvasElement());
                    stage.content.insertBefore(this.getNativeCanvasElement(), children[this.index + 1].getCanvas()._canvas);
                }
            }
            return true;
        }
        return false;
    }
    moveToBottom() {
        if (Node.prototype.moveToBottom.call(this)) {
            var stage = this.getStage();
            if (stage) {
                var children = stage.children;
                if (stage.content) {
                    stage.content.removeChild(this.getNativeCanvasElement());
                    stage.content.insertBefore(this.getNativeCanvasElement(), children[1].getCanvas()._canvas);
                }
            }
            return true;
        }
        return false;
    }
    getLayer() {
        return this;
    }
    remove() {
        var _canvas = this.getNativeCanvasElement();
        Node.prototype.remove.call(this);
        if (_canvas && _canvas.parentNode && Util._isInDocument(_canvas)) {
            _canvas.parentNode.removeChild(_canvas);
        }
        return this;
    }
    getStage() {
        return this.parent;
    }
    setSize({ width, height }) {
        this.canvas.setSize(width, height);
        this.hitCanvas.setSize(width, height);
        this._setSmoothEnabled();
        return this;
    }
    _validateAdd(child) {
        var type = child.getType();
        if (type !== 'Group' && type !== 'Shape') {
            Util.throw('You may only add groups and shapes to a layer.');
        }
    }
    _toKonvaCanvas(config) {
        config = config || {};
        config.width = config.width || this.getWidth();
        config.height = config.height || this.getHeight();
        config.x = config.x !== undefined ? config.x : this.x();
        config.y = config.y !== undefined ? config.y : this.y();
        return Node.prototype._toKonvaCanvas.call(this, config);
    }
    _checkVisibility() {
        const visible = this.visible();
        if (visible) {
            this.canvas._canvas.style.display = 'block';
        }
        else {
            this.canvas._canvas.style.display = 'none';
        }
    }
    _setSmoothEnabled() {
        this.getContext()._context.imageSmoothingEnabled =
            this.imageSmoothingEnabled();
    }
    getWidth() {
        if (this.parent) {
            return this.parent.width();
        }
    }
    setWidth() {
        Util.warn('Can not change width of layer. Use "stage.width(value)" function instead.');
    }
    getHeight() {
        if (this.parent) {
            return this.parent.height();
        }
    }
    setHeight() {
        Util.warn('Can not change height of layer. Use "stage.height(value)" function instead.');
    }
    batchDraw() {
        if (!this._waitingForDraw) {
            this._waitingForDraw = true;
            Util.requestAnimFrame(() => {
                this.draw();
                this._waitingForDraw = false;
            });
        }
        return this;
    }
    getIntersection(pos) {
        if (!this.isListening() || !this.isVisible()) {
            return null;
        }
        var spiralSearchDistance = 1;
        var continueSearch = false;
        while (true) {
            for (let i = 0; i < INTERSECTION_OFFSETS_LEN; i++) {
                const intersectionOffset = INTERSECTION_OFFSETS[i];
                const obj = this._getIntersection({
                    x: pos.x + intersectionOffset.x * spiralSearchDistance,
                    y: pos.y + intersectionOffset.y * spiralSearchDistance,
                });
                const shape = obj.shape;
                if (shape) {
                    return shape;
                }
                continueSearch = !!obj.antialiased;
                if (!obj.antialiased) {
                    break;
                }
            }
            if (continueSearch) {
                spiralSearchDistance += 1;
            }
            else {
                return null;
            }
        }
    }
    _getIntersection(pos) {
        const ratio = this.hitCanvas.pixelRatio;
        const p = this.hitCanvas.context.getImageData(Math.round(pos.x * ratio), Math.round(pos.y * ratio), 1, 1).data;
        const p3 = p[3];
        if (p3 === 255) {
            const colorKey = Util._rgbToHex(p[0], p[1], p[2]);
            const shape = shapes[HASH + colorKey];
            if (shape) {
                return {
                    shape: shape,
                };
            }
            return {
                antialiased: true,
            };
        }
        else if (p3 > 0) {
            return {
                antialiased: true,
            };
        }
        return {};
    }
    drawScene(can, top) {
        var layer = this.getLayer(), canvas = can || (layer && layer.getCanvas());
        this._fire(BEFORE_DRAW, {
            node: this,
        });
        if (this.clearBeforeDraw()) {
            canvas.getContext().clear();
        }
        Container.prototype.drawScene.call(this, canvas, top);
        this._fire(DRAW, {
            node: this,
        });
        return this;
    }
    drawHit(can, top) {
        var layer = this.getLayer(), canvas = can || (layer && layer.hitCanvas);
        if (layer && layer.clearBeforeDraw()) {
            layer.getHitCanvas().getContext().clear();
        }
        Container.prototype.drawHit.call(this, canvas, top);
        return this;
    }
    enableHitGraph() {
        this.hitGraphEnabled(true);
        return this;
    }
    disableHitGraph() {
        this.hitGraphEnabled(false);
        return this;
    }
    setHitGraphEnabled(val) {
        Util.warn('hitGraphEnabled method is deprecated. Please use layer.listening() instead.');
        this.listening(val);
    }
    getHitGraphEnabled(val) {
        Util.warn('hitGraphEnabled method is deprecated. Please use layer.listening() instead.');
        return this.listening();
    }
    toggleHitCanvas() {
        if (!this.parent || !this.parent['content']) {
            return;
        }
        var parent = this.parent;
        var added = !!this.hitCanvas._canvas.parentNode;
        if (added) {
            parent.content.removeChild(this.hitCanvas._canvas);
        }
        else {
            parent.content.appendChild(this.hitCanvas._canvas);
        }
    }
}
Layer.prototype.nodeType = 'Layer';
_registerNode(Layer);
Factory.addGetterSetter(Layer, 'imageSmoothingEnabled', true);
Factory.addGetterSetter(Layer, 'clearBeforeDraw', true);
Factory.addGetterSetter(Layer, 'hitGraphEnabled', true, getBooleanValidator());
