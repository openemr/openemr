import { Util } from './Util.js';
import { SceneContext, HitContext } from './Context.js';
import { Konva } from './Global.js';
import { Factory } from './Factory.js';
import { getNumberValidator } from './Validators.js';
var _pixelRatio;
function getDevicePixelRatio() {
    if (_pixelRatio) {
        return _pixelRatio;
    }
    var canvas = Util.createCanvasElement();
    var context = canvas.getContext('2d');
    _pixelRatio = (function () {
        var devicePixelRatio = Konva._global.devicePixelRatio || 1, backingStoreRatio = context.webkitBackingStorePixelRatio ||
            context.mozBackingStorePixelRatio ||
            context.msBackingStorePixelRatio ||
            context.oBackingStorePixelRatio ||
            context.backingStorePixelRatio ||
            1;
        return devicePixelRatio / backingStoreRatio;
    })();
    return _pixelRatio;
}
export class Canvas {
    constructor(config) {
        this.pixelRatio = 1;
        this.width = 0;
        this.height = 0;
        this.isCache = false;
        var conf = config || {};
        var pixelRatio = conf.pixelRatio || Konva.pixelRatio || getDevicePixelRatio();
        this.pixelRatio = pixelRatio;
        this._canvas = Util.createCanvasElement();
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
                Util.error('Unable to get data URL. ' +
                    err.message +
                    ' For more info read https://konvajs.org/docs/posts/Tainted_Canvas.html.');
                return '';
            }
        }
    }
}
Factory.addGetterSetter(Canvas, 'pixelRatio', undefined, getNumberValidator());
export class SceneCanvas extends Canvas {
    constructor(config = { width: 0, height: 0 }) {
        super(config);
        this.context = new SceneContext(this);
        this.setSize(config.width, config.height);
    }
}
export class HitCanvas extends Canvas {
    constructor(config = { width: 0, height: 0 }) {
        super(config);
        this.hitCanvas = true;
        this.context = new HitContext(this);
        this.setSize(config.width, config.height);
    }
}
