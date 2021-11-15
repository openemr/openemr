"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.HitCanvas = exports.SceneCanvas = exports.Canvas = void 0;
const Util_1 = require("./Util");
const Context_1 = require("./Context");
const Global_1 = require("./Global");
const Factory_1 = require("./Factory");
const Validators_1 = require("./Validators");
var _pixelRatio;
function getDevicePixelRatio() {
    if (_pixelRatio) {
        return _pixelRatio;
    }
    var canvas = Util_1.Util.createCanvasElement();
    var context = canvas.getContext('2d');
    _pixelRatio = (function () {
        var devicePixelRatio = Global_1.Konva._global.devicePixelRatio || 1, backingStoreRatio = context.webkitBackingStorePixelRatio ||
            context.mozBackingStorePixelRatio ||
            context.msBackingStorePixelRatio ||
            context.oBackingStorePixelRatio ||
            context.backingStorePixelRatio ||
            1;
        return devicePixelRatio / backingStoreRatio;
    })();
    return _pixelRatio;
}
class Canvas {
    constructor(config) {
        this.pixelRatio = 1;
        this.width = 0;
        this.height = 0;
        this.isCache = false;
        var conf = config || {};
        var pixelRatio = conf.pixelRatio || Global_1.Konva.pixelRatio || getDevicePixelRatio();
        this.pixelRatio = pixelRatio;
        this._canvas = Util_1.Util.createCanvasElement();
        this._canvas.style.padding = '0';
        this._canvas.style.margin = '0';
        this._canvas.style.border = '0';
        this._canvas.style.background = 'transparent';
        this._canvas.style.position = 'absolute';
        this._canvas.style.top = '0';
        this._canvas.style.left = '0';
    }
    getContext() {
        return this.context;
    }
    getPixelRatio() {
        return this.pixelRatio;
    }
    setPixelRatio(pixelRatio) {
        var previousRatio = this.pixelRatio;
        this.pixelRatio = pixelRatio;
        this.setSize(this.getWidth() / previousRatio, this.getHeight() / previousRatio);
    }
    setWidth(width) {
        this.width = this._canvas.width = width * this.pixelRatio;
        this._canvas.style.width = width + 'px';
        var pixelRatio = this.pixelRatio, _context = this.getContext()._context;
        _context.scale(pixelRatio, pixelRatio);
    }
    setHeight(height) {
        this.height = this._canvas.height = height * this.pixelRatio;
        this._canvas.style.height = height + 'px';
        var pixelRatio = this.pixelRatio, _context = this.getContext()._context;
        _context.scale(pixelRatio, pixelRatio);
    }
    getWidth() {
        return this.width;
    }
    getHeight() {
        return this.height;
    }
    setSize(width, height) {
        this.setWidth(width || 0);
        this.setHeight(height || 0);
    }
    toDataURL(mimeType, quality) {
        try {
            return this._canvas.toDataURL(mimeType, quality);
        }
        catch (e) {
            try {
                return this._canvas.toDataURL();
            }
            catch (err) {
                Util_1.Util.error('Unable to get data URL. ' +
                    err.message +
                    ' For more info read https://konvajs.org/docs/posts/Tainted_Canvas.html.');
                return '';
            }
        }
    }
}
exports.Canvas = Canvas;
Factory_1.Factory.addGetterSetter(Canvas, 'pixelRatio', undefined, (0, Validators_1.getNumberValidator)());
class SceneCanvas extends Canvas {
    constructor(config = { width: 0, height: 0 }) {
        super(config);
        this.context = new Context_1.SceneContext(this);
        this.setSize(config.width, config.height);
    }
}
exports.SceneCanvas = SceneCanvas;
class HitCanvas extends Canvas {
    constructor(config = { width: 0, height: 0 }) {
        super(config);
        this.hitCanvas = true;
        this.context = new Context_1.HitContext(this);
        this.setSize(config.width, config.height);
    }
}
exports.HitCanvas = HitCanvas;
