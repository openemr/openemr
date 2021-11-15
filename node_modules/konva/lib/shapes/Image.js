import { Util } from '../Util.js';
import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Image extends Shape {
    constructor(attrs) {
        super(attrs);
        this.on('imageChange.konva', () => {
            this._setImageLoad();
        });
        this._setImageLoad();
    }
    _setImageLoad() {
        const image = this.image();
        if (image && image.complete) {
            return;
        }
        if (image && image.readyState === 4) {
            return;
        }
        if (image && image['addEventListener']) {
            image['addEventListener']('load', () => {
                this._requestDraw();
            });
        }
    }
    _useBufferCanvas() {
        return super._useBufferCanvas(true);
    }
    _sceneFunc(context) {
        const width = this.getWidth();
        const height = this.getHeight();
        const image = this.attrs.image;
        let params;
        if (image) {
            const cropWidth = this.attrs.cropWidth;
            const cropHeight = this.attrs.cropHeight;
            if (cropWidth && cropHeight) {
                params = [
                    image,
                    this.cropX(),
                    this.cropY(),
                    cropWidth,
                    cropHeight,
                    0,
                    0,
                    width,
                    height,
                ];
            }
            else {
                params = [image, 0, 0, width, height];
            }
        }
        if (this.hasFill() || this.hasStroke()) {
            context.beginPath();
            context.rect(0, 0, width, height);
            context.closePath();
            context.fillStrokeShape(this);
        }
        if (image) {
            context.drawImage.apply(context, params);
        }
    }
    _hitFunc(context) {
        var width = this.width(), height = this.height();
        context.beginPath();
        context.rect(0, 0, width, height);
        context.closePath();
        context.fillStrokeShape(this);
    }
    getWidth() {
        var _a, _b;
        return (_a = this.attrs.width) !== null && _a !== void 0 ? _a : (_b = this.image()) === null || _b === void 0 ? void 0 : _b.width;
    }
    getHeight() {
        var _a, _b;
        return (_a = this.attrs.height) !== null && _a !== void 0 ? _a : (_b = this.image()) === null || _b === void 0 ? void 0 : _b.height;
    }
    static fromURL(url, callback) {
        var img = Util.createImageElement();
        img.onload = function () {
            var image = new Image({
                image: img,
            });
            callback(image);
        };
        img.crossOrigin = 'Anonymous';
        img.src = url;
    }
}
Image.prototype.className = 'Image';
_registerNode(Image);
Factory.addGetterSetter(Image, 'image');
Factory.addComponentsGetterSetter(Image, 'crop', ['x', 'y', 'width', 'height']);
Factory.addGetterSetter(Image, 'cropX', 0, getNumberValidator());
Factory.addGetterSetter(Image, 'cropY', 0, getNumberValidator());
Factory.addGetterSetter(Image, 'cropWidth', 0, getNumberValidator());
Factory.addGetterSetter(Image, 'cropHeight', 0, getNumberValidator());
